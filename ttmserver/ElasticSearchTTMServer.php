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
	 * @const int number of documents that will be loaded and deleted in a
	 * single operation
	 */
	const BULK_DELETE_CHUNK_SIZE = 100;

	/**
	 * @const int in case a write operation fails during a batch process
	 * this constant controls the number of times we will retry the same
	 * operation.
	 */
	const BULK_INDEX_RETRY_ATTEMPTS = 5;

	/**
	 * @const int time (seconds) to wait for the index to ready before
	 * starting to index. Since we wait for index status it can be relatively
	 * long especially if some nodes are restarted.
	 */
	const WAIT_UNTIL_READY_TIMEOUT = 3600;

	/**
	 * @var \Elastica\Client
	 */
	protected $client;

	/**
	 * Reference to the maintenance script to relay logging output.
	 */
	protected $logger;

	/**
	 * Used for Reindex
	 */
	protected $updateMapping = false;

	public function isLocalSuggestion( array $suggestion ) {
		return $suggestion['wiki'] === wfWikiID();
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
		if ( !$this->useWikimediaExtraPlugin() ) {
			// ElasticTTM is currently not compatible with elasticsearch 2.x
			// It needs FuzzyLikeThis ported via the wmf extra plugin
			throw new \RuntimeException( 'The wikimedia extra plugin is mandatory.' );
		}
		/* Two query system:
		 * 1) Find all strings in source language that match text
		 * 2) Do another query for translations for those strings
		 */
		$connection = $this->getClient()->getConnection();
		$oldTimeout = $connection->getTimeout();
		$connection->setTimeout( 10 );

		$fuzzyQuery = new FuzzyLikeThis();
		$fuzzyQuery->setMinSimilarity( 2 );
		$fuzzyQuery->setLikeText( $text );
		$fuzzyQuery->addFields( array( 'content' ) );

		$boostQuery = new \Elastica\Query\FunctionScore();
		if ( $this->useWikimediaExtraPlugin() ) {
			$boostQuery->addFunction(
				'levenshtein_distance_score',
				array(
					'text' => $text,
					'field' => 'content'
				)
			);
		} else {
			$groovyScript =
<<<GROOVY
import org.apache.lucene.search.spell.*
new LevensteinDistance().getDistance(srctxt, _source['content'])
GROOVY;
			$script = new \Elastica\Script(
				$groovyScript,
				array( 'srctxt' => $text ),
				\Elastica\Script::LANG_GROOVY
			);
			$boostQuery->addScriptScoreFunction( $script );
		}
		$boostQuery->setBoostMode( \Elastica\Query\FunctionScore::BOOST_MODE_REPLACE );

		// Wrap the fuzzy query so it can be used as a filter.
		// This is slightly faster, as ES can throw away the scores by this query.
		$bool = new \Elastica\Query\BoolQuery();
		$bool->addFilter( $fuzzyQuery );
		$bool->addMust( $boostQuery );

		$languageFilter = new \Elastica\Query\Term();
		$languageFilter->setTerm( 'language', $sourceLanguage );
		$bool->addFilter( $languageFilter );

		// The whole query
		$query = new \Elastica\Query();
		$query->setQuery( $bool );

		// The interface usually displays three best candidates. These might
		// come from more than three source things, if the translations are
		// the same. In other words suggestions are grouped by the suggested
		// translation. This algorithm might not find all suggestions, if the
		// top N best matching source texts don't have equivalent translations
		// in the target language, but worse matches which we did not fetch do.
		// This code tries to balance between doing too many or too big queries
		// and not fetching enough results to show all possible suggestions.
		$sizeFirst = 100;
		$sizeSecond = $sizeFirst * 5;

		$query->setFrom( 0 );
		$query->setSize( $sizeFirst );
		$query->setParam( '_source', array( 'content' ) );
		$cutoff = isset( $this->config['cutoff'] ) ? $this->config['cutoff'] : 0.65;
		$query->setParam( 'min_score', $cutoff );
		$query->setSort( array( '_score', '_uid' ) );

		// This query is doing two unrelated things:
		// 1) Collect the message contents and scores so that they can
		//    be accessed later for the translations we found.
		// 2) Build the query string for the query that fetches the translations.
		$contents = $scores = $terms = array();
		do {
			$resultset = $this->getType()->search( $query );

			if ( count( $resultset ) === 0 ) {
				break;
			}

			foreach ( $resultset->getResults() as $result ) {
				$data = $result->getData();
				$score = $result->getScore();

				$sourceId = preg_replace( '~/[^/]+$~', '', $result->getId() );
				$contents[$sourceId] = $data['content'];
				$scores[$sourceId] = $score;
				$terms[] = "$sourceId/$targetLanguage";
			}

			// Check if it looks like that we are hitting the long tail already.
			// Otherwise, we'll do a query to fetch some more to reach a "sane"
			// breaking point, i.e. include all suggestions with same content
			// for reliable used X times statistics.
			if ( count( array_unique( $scores ) ) > 5 ) {
				break;
			}

			// Okay, We are now in second iteration of the loop. We already got
			// lots of suggestions. We will give up for now even if it means we
			// return in some sense incomplete results.
			if ( count( $resultset ) === $sizeSecond ) {
				break;
			}

			// After the first query, the smallest score is the new threshold.
			$query->setParam( 'min_score', $score );
			$query->setFrom( $query->getParam( 'size' ) + $query->getParam( 'from' ) );
			$query->setSize( $sizeSecond );

			// Break if we already got all hits
		} while ( $resultset->getTotalHits() > count( $contents ) );

		$suggestions = array();

		// Skip second query if first query found nothing. Keeping only one return
		// statement in this method to avoid forgetting to reset connection timeout
		if ( $terms !== array() ) {
			$idQuery = new \Elastica\Query\Terms();
			$idQuery->setTerms( '_id', $terms );

			$query = new \Elastica\Query( $idQuery );
			$query->setSize( 25 );
			$query->setParam( '_source', array( 'wiki', 'uri', 'content', 'localid' ) );
			$resultset = $this->getType()->search( $query );

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
		}

		$connection->setTimeout( $oldTimeout );

		return $suggestions;
	}

	/* Write functions */

	public function update( MessageHandle $handle, $targetText ) {
		if ( !$handle->isValid() || $handle->getCode() === '' ) {
			return false;
		}

		/* There are various different cases here:
		 * [new or updated] [fuzzy|non-fuzzy] [translation|definition]
		 * 1) We don't distinguish between new or updated here.
		 * 2) Delete old translation, but not definition
		 * 3) Insert new translation or definition, if non-fuzzy
		 * The definition should never be fuzzied anyway.
		 *
		 * These only apply to known messages.
		 */

		$sourceLanguage = $handle->getGroup()->getSourceLanguage();

		// Do not delete definitions, because the translations are attached to that
		if ( $handle->getCode() !== $sourceLanguage ) {
			$localid = $handle->getTitleForBase()->getPrefixedText();

			$boolQuery = new \Elastica\Query\BoolQuery();
			$boolQuery->addFilter( new Elastica\Query\Term( array( 'wiki' => wfWikiID() ) ) );
			$boolQuery->addFilter( new Elastica\Query\Term( array( 'language' => $handle->getCode() ) ) );
			$boolQuery->addFilter( new Elastica\Query\Term( array( 'localid' => $localid ) ) );

			$query = new \Elastica\Query( $boolQuery );
			$this->deleteByQuery( $this->getType(), $query );
		}

		// If translation was made fuzzy, we do not need to add anything
		if ( $targetText === null ) {
			return true;
		}

		$revId = $handle->getTitleForLanguage( $sourceLanguage )->getLatestRevID();
		$doc = $this->createDocument( $handle, $targetText, $revId );

		MWElasticUtils::withRetry( self::BULK_INDEX_RETRY_ATTEMPTS,
			function() use ( $doc ) {
				$this->getType()->addDocument( $doc );
			},
			function( $e, $errors ) {
				$c = get_class( $e );
				$msg = $e->getMessage();
				error_log( __METHOD__ . ": update failed ($c: $msg); retrying." );
				sleep( 10 );
			}
		);

		return true;
	}

	/**
	 * @param MessageHandle $handle
	 * @param string $text
	 * @param int $revId
	 * @return \Elastica\Document
	 */
	protected function createDocument( MessageHandle $handle, $text, $revId ) {
		$language = $handle->getCode();

		$localid = $handle->getTitleForBase()->getPrefixedText();
		$wiki = wfWikiID();
		$globalid = "$wiki-$localid-$revId/$language";

		$data = array(
			'wiki' => $wiki,
			'uri' => $handle->getTitle()->getCanonicalURL(),
			'localid' => $localid,
			'language' => $language,
			'content' => $text,
			'group' => $handle->getGroupIds(),
		);

		return new \Elastica\Document( $globalid, $data );
	}

	/**
	 * Create index
	 * @param bool $rebuild Deletes index first if already exists
	 */
	public function createIndex( $rebuild ) {
		$type = $this->getType();
		$type->getIndex()->create(
			array(
				'number_of_shards' => $this->getShardCount(),
				'number_of_replicas' => $this->getReplicaCount(),
				'analysis' => array(
					'filter' => array(
						'prefix_filter' => array(
							'type' => 'edge_ngram',
							'min_gram'=> 2,
							'max_gram'=> 20
						)
					),
					'analyzer' => array(
						'prefix' => array(
							'type' => 'custom',
							'tokenizer' => 'standard',
							'filter' => array( 'standard', 'lowercase', 'prefix_filter' )
						),
						'casesensitive' => array(
							'tokenizer' => 'standard',
							'filter' => array( 'standard' )
						)
					)
				)
			),
			$rebuild
		);
	}

	public function beginBootstrap() {
		$type = $this->getType();
		if ( $this->updateMapping ) {
			$this->logOutput( 'Updating the index mappings...' );
			$this->createIndex( true );
		} elseif ( !$type->getIndex()->exists() ) {
			$this->createIndex( false );
		}

		$settings = $type->getIndex()->getSettings();
		$settings->setRefreshInterval( '-1' );

		$term = new Elastica\Query\Term();
		$term->setTerm( 'wiki', wfWikiID() );
		$query = new \Elastica\Query( $term );
		$this->deleteByQuery( $type, $query );

		$mapping = new \Elastica\Type\Mapping();
		$mapping->setType( $type );
		$mapping->setProperties( array(
			'wiki'     => array( 'type' => 'string', 'index' => 'not_analyzed' ),
			'localid'  => array( 'type' => 'string', 'index' => 'not_analyzed' ),
			'uri'      => array( 'type' => 'string', 'index' => 'not_analyzed' ),
			'language' => array( 'type' => 'string', 'index' => 'not_analyzed' ),
			'group'    => array( 'type' => 'string', 'index' => 'not_analyzed' ),
			'content'  => array(
				'type' => 'string',
				'fields' => array(
					'content' => array(
						'type' => 'string',
						'index' => 'analyzed',
						'term_vector' => 'yes'
					),
					'prefix_complete' => array(
						'type' => 'string',
						'analyzer' => 'prefix',
						'search_analyzer' => 'standard',
						'term_vector' => 'yes'
					),
					'case_sensitive' => array(
						'type' => 'string',
						'index' => 'analyzed',
						'analyzer' => 'casesensitive',
						'term_vector' => 'yes'
					)
				)
			),
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

		MWElasticUtils::withRetry( self::BULK_INDEX_RETRY_ATTEMPTS,
			function() use ( $docs ) {
				$this->getType()->addDocuments( $docs );
			},
			function( $e, $errors ) {
				$c = get_class( $e );
				$msg = $e->getMessage();
				$this->logOutput( "Batch failed ($c: $msg), trying again in 10 seconds" );
				sleep( 10 );
			}
		);
	}

	public function endBatch() {
		// I hate the rule that forbids {}
	}

	public function endBootstrap() {
		$index = $this->getType()->getIndex();
		$index->refresh();
		$index->optimize();
		$index->getSettings()->setRefreshInterval( '5s' );
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

	/**
	 * @return true if the backend is configured with the wikimedia extra plugin
	 */
	public function useWikimediaExtraPlugin() {
		return isset ( $this->config['use_wikimedia_extra'] ) && $this->config['use_wikimedia_extra'];
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

	/**
	 * Get index health
	 *
	 * @param string $indexName
	 * @return array the index health status
	 */
	protected function getIndexHealth( $indexName ) {
		$path = "_cluster/health/$indexName";
		$response = $this->getClient()->request( $path );
		if ( $response->hasError() ) {
			throw new \Exception( "Error while fetching index health status: ". $response->getError() );
		}
		return $response->getData();
	}

	/**
	 * Wait for the index to go green
	 *
	 * NOTE: This method has been copied and adjusted from
	 * CirrusSearch/includes/Maintenance/ConfigUtils.php.  Ideally we'd
	 * like to make these utility methods available in the Elastica
	 * extension, but this one requires some refactoring in cirrus first.
	 *
	 * @param string $indexName
	 * @param int $timeout
	 * @return boolean true if the index is green false otherwise.
	 */
	protected function waitForGreen( $indexName, $timeout ) {
		$startTime = time();
		while ( ( $startTime + $timeout ) > time() ) {
			try {
				$response = $this->getIndexHealth( $indexName );
				$status = isset ( $response['status'] ) ? $response['status'] : 'unknown';
				if ( $status === 'green' ) {
					$this->logOutput( "\tGreen!" );
					return true;
				}
				$this->logOutput( "\tIndex is $status retrying..." );
				sleep( 5 );
			} catch ( \Exception $e ) {
				$this->logOutput( "Error while waiting for green ({$e->getMessage()}), retrying..." );
			}
		}
		return false;
	}

	protected function waitUntilReady() {
		$indexName = $this->getType()->getIndex()->getName();
		$this->logOutput( "Waiting for the index to go green..." );
		if ( !$this->waitForGreen( $indexName, self::WAIT_UNTIL_READY_TIMEOUT ) ) {
			die( "Timeout! Please check server logs for {$this->getIndex()->getName()}." );
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

	/**
	 * Force the update of index mappings
	 * @since 2015.03
	 */
	public function doMappingUpdate() {
		$this->updateMapping = true;
	}

	// Parse query string and build the search query
	protected function parseQueryString( $queryString, array $opts ) {
		$fields = $highlights = array();
		$terms = preg_split( '/\s+/', $queryString );
		$match = $opts['match'];
		$case = $opts['case'];

		// Map each word in the query string with its corresponding field
		foreach ( $terms as $term ) {
			$prefix = strstr( $term, '*', true );
			if ( $prefix ) {
				// For wildcard search
				$fields['content.prefix_complete'][] = $prefix;
			} elseif ( $case === '1' ) {
				// For case sensitive search
				$fields['content.case_sensitive'][] = $term;
			} else {
				$fields['content'][] = $term;
			}
		}

		// Allow searching either by message content or message id (page name
		// without language subpage) with exact match only.
		$searchQuery = new \Elastica\Query\BoolQuery();
		foreach ( $fields as $analyzer => $words ) {
			foreach ( $words as $word ) {
				$boolQuery = new \Elastica\Query\BoolQuery();
				$contentQuery = new \Elastica\Query\Match();
				$contentQuery->setFieldQuery( $analyzer, $word );
				$boolQuery->addShould( $contentQuery );
				$messageQuery = new \Elastica\Query\Term();
				$messageQuery->setTerm( 'localid', $word );
				$boolQuery->addShould( $messageQuery );

				if ( $match === 'all' ) {
					$searchQuery->addMust( $boolQuery );
				} else {
					$searchQuery->addShould( $boolQuery );
				}

				// Fields for highlighting
				$highlights[$analyzer] =  array(
					'number_of_fragments' => 0
				);

				// Allow searching by exact message title (page name with
				// language subpage).
				$title = Title::newFromText( $word );
				if ( !$title ) {
					continue;
				}
				$handle = new MessageHandle( $title );
				if ( $handle->isValid() && $handle->getCode() !== '' ) {
					$localid = $handle->getTitleForBase()->getPrefixedText();
					$boolQuery = new \Elastica\Query\BoolQuery();
					$messageId = new \Elastica\Query\Term();
					$messageId->setTerm( 'localid', $localid );
					$boolQuery->addMust( $messageId );
					$searchQuery->addShould( $boolQuery );
				}
			}
		}

		return array( $searchQuery, $highlights );
	}

	// Search interface
	public function search( $queryString, $opts, $highlight ) {
		$query = new \Elastica\Query();

		list( $searchQuery, $highlights ) = $this->parseQueryString( $queryString, $opts );
		$query->setQuery( $searchQuery );

		$language = new \Elastica\Aggregation\Terms( 'language' );
		$language->setField( 'language' );
		$language->setSize( 500 );
		$query->addAggregation( $language );

		$group = new \Elastica\Aggregation\Terms( 'group' );
		$group->setField( 'group' );
		// Would like to prioritize the top level groups and not show subgroups
		// if the top group has only few hits, but that doesn't seem to be possile.
		$group->setSize( 500 );
		$query->addAggregation( $group );

		$query->setSize( $opts['limit'] );
		$query->setFrom( $opts['offset'] );

		// BoolAnd filters are executed in sequence per document. Bool filters with
		// multiple must clauses are executed by converting each filter into a bit
		// field then anding them together. The latter is normally faster if either
		// of the subfilters are reused. May not make a difference in this context.
		$filters = new \Elastica\Query\BoolQuery();

		$language = $opts['language'];
		if ( $language !== '' ) {
			$languageFilter = new \Elastica\Query\Term();
			$languageFilter->setTerm( 'language', $language );
			$filters->addFilter( $languageFilter );
		}

		$group = $opts['group'];
		if ( $group !== '' ) {
			$groupFilter = new \Elastica\Query\Term();
			$groupFilter->setTerm( 'group', $group );
			$filters->addFilter( $groupFilter );
		}

		// Check that we have at least one filter to avoid invalid query errors.
		if ( $language !== '' || $group !== '' ) {
			$query->setPostFilter( $filters );
		}

		list( $pre, $post ) = $highlight;
		$query->setHighlight( array(
			// The value must be an object
			'pre_tags' => array( $pre ),
			'post_tags' => array( $post ),
			'fields' => $highlights,
		) );

		try {
			return $this->getType()->getIndex()->search( $query );
		} catch ( \Elastica\Exception\ExceptionInterface $e ) {
			throw new TTMServerException( $e->getMessage() );
		}
	}

	public function getFacets( $resultset ) {
		$aggs = $resultset->getAggregations();

		$ret = array(
			'language' => array(),
			'group' => array()
		);

		foreach ( $aggs as $type => $info ) {
			foreach ( $info['buckets'] as $row ) {
				$ret[$type][$row['key']] = $row['doc_count'];
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
			if ( isset( $hl['content.prefix_complete'][0] ) ) {
				$data['content'] = $hl['content.prefix_complete'][0];
			} elseif ( isset( $hl['content.case_sensitive'][0] ) ) {
				$data['content'] = $hl['content.case_sensitive'][0];
			} elseif ( isset( $hl['content'][0] ) ) {
				$data['content'] = $hl['content'][0];
			}
			$ret[] = $data;
		}

		return $ret;
	}

	/**
	 * Delete docs by query by using the scroll API.
	 *
	 * @param \Elastica\Type $type the source index
	 * @param \Elastica\Query $query the query
	 */
	private function deleteByQuery( \Elastica\Type $type, \Elastica\Query $query ) {
		$retryAttempts = self::BULK_INDEX_RETRY_ATTEMPTS;
		$scrollOptions = array(
			'search_type' => 'scan',
			'scroll' => '15m',
			'size' => self::BULK_DELETE_CHUNK_SIZE,
		);

		$result = $type->search( $query, $scrollOptions );
		MWElasticUtils::iterateOverScroll( $type->getIndex(),
			$result->getResponse()->getScrollId(), '15m',
			function( $results ) use( $retryAttempts, $type ) {
				$ids = array();
				foreach ( $results as $result ) {
					$ids[] = $result->getId();
				}
				MWElasticUtils::withRetry( $retryAttempts,
					function() use ( $ids, $type ) {
						$type->deleteIds( $ids );
					}
				);
			}, 0, $retryAttempts );
	}
}
