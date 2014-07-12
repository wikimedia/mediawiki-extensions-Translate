<?php
/**
 * TTMServer - The Translate extension translation memory interface
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 * @ingroup TTMServer
 */

/**
 * TTMServer backed based on ElasticSearch. Depends on Elastica.
 * @since 2014.04
 * @ingroup TTMServer
 */
class ElasticSearchTTMServer
	extends TTMServer
	implements ReadableTTMServer, WritableTTMServer, SearchableTTMserver
{
	/**
	 * @var \Elastica\Client
	 */
	protected $client;

	/**
	 * Reference to the maintenance script to relay logging output.
	 */
	protected $logger;

	public function isLocalSuggestion( array $suggestion ) {
		return $suggestion['wiki'] === wfWikiId();
	}

	public function expandLocation( array $suggestion ) {
		return $suggestion['uri'];
	}

	public function query( $sourceLanguage, $targetLanguage, $text ) {
		try {
			return $this->doQuery( $sourceLanguage, $targetLanguage, $text );
		} catch ( Exception $e ) {
			throw new TranslationHelperException( 'Elastica exception: ' . $e );
		}
	}

	protected function doQuery( $sourceLanguage, $targetLanguage, $text ) {
		/* Two query system:
		 * 1) Find all strings in source language that match text
		 * 2) Do another query for translations for those strings
		 */
		wfProfileIn( __METHOD__ );

		$query = new \Elastica\Query();

		$fuzzyQuery = new \Elastica\Query\FuzzyLikeThis();
		$fuzzyQuery->setLikeText( $text );
		$fuzzyQuery->addFields( array( 'content' ) );
		$query->setQuery( $fuzzyQuery );

		$languageFilter = new \Elastica\Filter\Term();
		$languageFilter->setTerm( 'language', $sourceLanguage );
		$query->setFilter( $languageFilter );

		/* The interface usually displays three best candidates. These might
		 * come from more than three matches, if the translation is the same.
		 * This might not find all suggestions, if the top N best matching
		 * source texts don't have translations, but worse matches do. We
		 * could loop with start parameter to fetch more until we have enough
		 * suggestions or the quality drops below the cutoff point. */
		$query->setSize( 25 );
		$query->setParam( '_source', array( 'content' ) );
		$resultset = $this->getType()->search( $query );

		/* This query is doing two unrelated things:
		 * 1) Collect the message contents and scores so that they can
		 *    be accessed later for the translations we found.
		 * 2) Build the query string for the query that fetches the
		 *    translations.
		 * This code is a bit uglier than I'd like it to be, since there
		 * there is no field that globally identifies a message (message
		 * definition and translations). */
		$contents = $scores = $terms = array();
		foreach ( $resultset->getResults() as $result ) {
			$data = $result->getData();

			// FIXME: hacked in client side scoring as the search query
			// returns wildly irrelevant results. This is slow.
			$len1 = mb_strlen( $text );
			$len2 = mb_strlen( $data['content'] );
			$dist = self::levenshtein( $text, $data['content'], $len1, $len2 );
			$score = 1 - ( $dist * 0.9 / min( $len1, $len2 ) );
			if ( $score < $this->config['cutoff'] ) {
				continue;
			}

			$sourceId = preg_replace( '~/[^/]+$~', '', $result->getId() );
			$contents[$sourceId] = $data['content'];
			$scores[$sourceId] = $score;
			$terms[] = "$sourceId/$targetLanguage";
		}

		$idQuery = new \Elastica\Query\Terms();
		$idQuery->setTerms( '_id', $terms );

		$query = new \Elastica\Query( $idQuery );
		$query->setSize( 25 );
		$query->setParam( '_source', array( 'wiki', 'uri', 'content', 'localid' ) );
		$resultset = $this->getType()->search( $query );

		$suggestions = array();
		foreach ( $resultset->getResults() as $result ) {
			$data = $result->getData();

			// Construct the matching source id
			$sourceId = preg_replace( '~/[^/]+$~', '', $result->getId() );

			$suggestions[] = array(
				'source' => $contents[$sourceId],
				'target' => $data['content'],
				'context' => $data['localid'],
				'quality' => $scores[$sourceId],
				'wiki' => $data['wiki'],
				'location' => $data['localid'] . '/' . $targetLanguage,
				'uri' => $data['uri'],
			);
		}

		// Ensure reults are in quality order
		uasort( $suggestions, function ( $a, $b ) {
			if ( $a['quality'] === $b['quality'] ) {
				return 0;
			}

			return ( $a['quality'] < $b['quality'] ) ? 1 : -1;
		} );

		wfProfileOut( __METHOD__ );

		return $suggestions;
	}

	/* Write functions */

	public function update( MessageHandle $handle, $targetText ) {
		if ( !$handle->isValid() || $handle->getCode() === '' ) {
			return false;
		}

		wfProfileIn( __METHOD__ );

		/* There are various different cases here:
		 * [new or updated] [fuzzy|non-fuzzy] [translation|definition]
		 * 1) We don't distinguish between new or updated here.
		 * 2) Delete old translation, but not definition
		 * 3) Insert new translation or definition, if non-fuzzy
		 * The definition should never be fuzzied anyway.
		 *
		 * These only apply to known messages.
		 */

		$title = $handle->getTitle();
		$sourceLanguage = $handle->getGroup()->getSourceLanguage();

		// Do not delete definitions, because the translations are attached to that
		if ( $handle->getCode() !== $sourceLanguage ) {
			$localid = $handle->getTitleForBase()->getPrefixedText();

			$boolQuery = new \Elastica\Query\Bool();
			$boolQuery->addMust( new Elastica\Query\Term( array( 'wiki' => wfWikiId() ) ) );
			$boolQuery->addMust( new Elastica\Query\Term( array( 'language' => $handle->getCode() ) ) );
			$boolQuery->addMust( new Elastica\Query\Term( array( 'localid' => $localid ) ) );

			$query = new \Elastica\Query( $boolQuery );
			$this->getType()->deleteByQuery( $query );
		}

		// If translation was made fuzzy, we do not need to add anything
		if ( $targetText === null ) {
			return true;
		}

		$revId = $handle->getTitleForLanguage( $sourceLanguage )->getLatestRevID();
		$doc = $this->createDocument( $handle, $targetText, $revId );

		$retries = 5;
		while ( $retries-- > 0 ) {
			try {
				$this->getType()->addDocument( $doc );
				break;
			} catch ( \Elastica\Exception\ExceptionInterface $e ) {
				if ( $retries === 0 ) {
					wfProfileOut( __METHOD__ );
					throw $e;
				} else {
					$c = get_class( $e );
					$msg = $e->getMessage();
					error_log( __METHOD__ . ": update failed ($c: $msg); retrying." );
					sleep( 10 );
				}
			}
		}

		wfProfileOut( __METHOD__ );

		return true;
	}

	/**
	 * @return \Elastica\Document
	 */
	protected function createDocument( MessageHandle $handle, $text, $revId ) {
		$language = $handle->getCode();

		$localid = $handle->getTitleForBase()->getPrefixedText();
		$wiki = wfWikiId();
		$globalid = "$wiki-$localid-$revId/$language";

		$data = array(
			'wiki' => $wiki,
			'uri' => $handle->getTitle()->getCanonicalUrl(),
			'localid' => $localid,
			'language' => $language,
			'content' => $text,
			'group' => $handle->getGroupIds(),
		);

		return new \Elastica\Document( $globalid, $data );
	}

	public function beginBootstrap() {
		$type = $this->getType();
		$type->getIndex()->create(
			array(
				'number_of_shards' => $this->getShardCount(),
				'number_of_replicas' => $this->getReplicaCount(),
			),
			true
		);

		$settings = $type->getIndex()->getSettings();
		$settings->setRefreshInterval( -1 );

		$term = new Elastica\Query\Term();
		$term->setTerm( 'wiki', wfWikiId() );
		$query = new \Elastica\Query( $term );
		$type->deleteByQuery( $query );

		$mapping = new \Elastica\Type\Mapping();
		$mapping->setType( $type );
		$mapping->setProperties( array(
			'wiki'     => array( 'type' => 'string', 'index' => 'not_analyzed' ),
			'localid'  => array( 'type' => 'string', 'index' => 'not_analyzed' ),
			'uri'      => array( 'type' => 'string', 'index' => 'not_analyzed' ),
			'language' => array( 'type' => 'string', 'index' => 'not_analyzed' ),
			'group'    => array( 'type' => 'string', 'index' => 'not_analyzed' ),
			'content'  => array( 'type' => 'string', 'index' => 'analyzed', 'term_vector' => 'yes' ),
		) );
		$mapping->send();

		$this->waitUntilReady();
	}

	public function beginBatch() {
		// I hate the rule that forbids {}
	}

	public function batchInsertDefinitions( array $batch ) {
		$lb = new LinkBatch();
		foreach ( $batch as $data ) {
			$lb->addObj( $data[0]->getTitle() );
		}
		$lb->execute();

		$this->batchInsertTranslations( $batch );
	}

	public function batchInsertTranslations( array $batch ) {
		$docs = array();
		foreach ( $batch as $data ) {
			list( $handle, $sourceLanguage, $text ) = $data;
			$revId = $handle->getTitleForLanguage( $sourceLanguage )->getLatestRevID();
			$docs[] = $this->createDocument( $handle, $text, $revId );
		}

		$retries = 5;
		while ( $retries-- > 0 ) {
			try {
				$this->getType()->addDocuments( $docs );
				break;
			} catch ( \Elastica\Exception\ExceptionInterface $e ) {
				if ( $retries === 0 ) {
					throw $e;
				} else {
					$c = get_class( $e );
					$msg = $e->getMessage();
					$this->logOutput( "Batch failed ($c: $msg), trying again in 10 seconds" );
					sleep( 10 );
				}
			}
		}
	}

	public function endBatch() {
		// I hate the rule that forbids {}
	}

	public function endBootstrap() {
		$index = $this->getType()->getIndex();
		$index->refresh();
		$index->optimize();
		$index->getSettings()->setRefreshInterval( 5 );
	}

	public function getClient() {
		if ( !$this->client ) {
			if ( isset( $this->config['config'] ) ) {
				$this->client = new \Elastica\Client( $this->config['config'] );
			} else {
				$this->client = new \Elastica\Client();
			}
		}
		return $this->client;
	}

	public function getType() {
		if ( isset( $this->config['index'] ) ) {
			$index = $this->config['index'];
		} else {
			$index = 'ttmserver';
		}
		return $this->getClient()->getIndex( $index )->getType( 'message' );
	}

	protected function getShardCount() {
		return isset( $this->config['shards'] ) ? $this->config['shards'] : 5;
	}

	protected function getReplicaCount() {
		return isset( $this->config['replicas'] ) ? $this->config['replicas'] : 0;
	}

	protected function waitUntilReady() {
		$expectedActive = $this->getShardCount() * ( 1 + $this->getReplicaCount() );
		$indexName = $this->getType()->getIndex()->getName();
		$path = "_cluster/health/$indexName";

		while ( true ) {
			$response = $this->getClient()->request( $path );
			if ( $response->hasError() ) {
				$this->logOutput( 'Error fetching index health. Retrying.' );
				$this->logOutput( 'Message: ' + $response->getError() );
			} else {
				$health = $response->getData();
				$active = $health['active_shards'];
				$this->logOutput(
					"active:$active/$expectedActive ".
					"relocating:{$health['relocating_shards']} " .
					"initializing:{$health['initializing_shards']} ".
					"unassigned:{$health['unassigned_shards']}"
				);
			}

			if ( $active === $expectedActive ) {
				break;
			}

			sleep( 10 );
		}
	}

	public function setLogger( $logger ) {
		$this->logger = $logger;
	}

	// Can it get any uglier?
	protected function logOutput( $text ) {
		if ( $this->logger ) {
			$this->logger->statusLine( "$text\n" );
		}
	}

	// Search interface
	public function search( $queryString, $opts, $highlight ) {
		$query = new \Elastica\Query();

		// Allow searching either by message content or message id (page name
		// without language subpage) with exact match only.
		$serchQuery = new \Elastica\Query\Bool();
		$contentQuery = new \Elastica\Query\Match();
		$contentQuery->setFieldQuery( 'content', $queryString );
		$serchQuery->addShould( $contentQuery );
		$messageQuery = new \Elastica\Query\Term();
		$messageQuery->setTerm( 'localid', $queryString );
		$serchQuery->addShould( $messageQuery );
		$query->setQuery( $serchQuery );

		$language = new \Elastica\Facet\Terms( 'language' );
		$language->setField( 'language' );
		$language->setSize( 500 );
		$query->addFacet( $language );

		$group = new \Elastica\Facet\Terms( 'group' );
		$group->setField( 'group' );
		// Would like to prioritize the top level groups and not show subgroups
		// if the top group has only few hits, but that doesn't seem to be possile.
		$group->setSize( 500 );
		$query->addFacet( $group );

		$query->setSize( $opts->getValue( 'limit' ) );
		$query->setFrom( $opts->getValue( 'offset' ) );

		// BoolAnd filters are executed in sequence per document. Bool filters with
		// multiple must clauses are executed by converting each filter into a bit
		// field then anding them together. The latter is normally faster if either
		// of the subfilters are reused. May not make a difference in this context.
		$filters = new \Elastica\Filter\Bool();

		$language = $opts->getValue( 'language' );
		if ( $language !== '' ) {
			$languageFilter = new \Elastica\Filter\Term();
			$languageFilter->setTerm( 'language', $language );
			$filters->addMust( $languageFilter );
		}

		$group = $opts->getValue( 'group' );
		if ( $group !== '' ) {
			$groupFilter = new \Elastica\Filter\Term();
			$groupFilter->setTerm( 'group', $group );
			$filters->addMust( $groupFilter );
		}

		// Check that we have at least one filter to avoid invalid query errors.
		if ( $language !== '' || $group !== '' ) {
			$query->setFilter( $filters );
		}

		list( $pre, $post ) = $highlight;
		$query->setHighlight( array(
			// The value must be an object
			'fields' => array(
				'content' => array(
					'number_of_fragments' => 0,
				),
			),
			'pre_tags' => array( $pre ),
			'post_tags' => array( $post ),
		) );

		try {
			return $this->getType()->getIndex()->search( $query );
		} catch ( \Elastica\Exception\ExceptionInterface $e ) {
			throw new TTMServerException( $e->getMessage() );
		}
	}

	public function getFacets( $resultset ) {
		$facets = $resultset->getFacets();

		$ret = array(
			'language' => array(),
			'group' => array()
		);

		foreach ( $facets as $type => $facetInfo ) {
			foreach ( $facetInfo['terms'] as $facetRow ) {
				$ret[$type][$facetRow['term']] = $facetRow['count'];
			}
		}

		return $ret;
	}

	public function getTotalHits( $resultset ) {
		return $resultset->getTotalHits();
	}

	public function getDocuments( $resultset ) {
		$ret = array();
		foreach ( $resultset->getResults() as $document ) {
			$data = $document->getData();
			$hl = $document->getHighlights();
			if ( isset( $hl['content'][0] ) ) {
				$data['content'] = $hl['content'][0];
			}
			$ret[] = $data;
		}

		return $ret;
	}
}
