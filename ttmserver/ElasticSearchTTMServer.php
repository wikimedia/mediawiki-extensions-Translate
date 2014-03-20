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
 * @since 2014-03-20
 * @ingroup TTMServer
 */
class ElasticSearchTTMServer extends TTMServer implements ReadableTTMServer, WritableTTMServer {
	/**
	 * @var \Elastica\Client
	 */
	protected $client;

	/**
	 * @var int[] Storage for batch processing.
	 */
	protected $revIds;

	/**
	 * @var \Elastica\Document[] Storage for batch processing.
	 */
	protected $docs;

	protected $updates;

	public function __construct( $config ) {
		wfProfileIn( __METHOD__ );
		parent::__construct( $config );
		if ( isset( $config['config'] ) ) {
			$this->client = new \Elastica\Client( $config['config'] );
		} else {
			$this->client = new \Elastica\Client();
		}
		wfProfileOut( __METHOD__ );
	}

	public function isLocalSuggestion( array $suggestion ) {
		return $suggestion['wiki'] === wfWikiId();
	}

	public function expandLocation( array $suggestion ) {
		return $suggestion['uri'];
	}

	public function query( $sourceLanguage, $targetLanguage, $text ) {
		try {
			return $this->doQuery( $sourceLanguage, $targetLanguage, $text );
		// FIXME
		} catch ( Solarium_Exception $e ) {
			throw new TranslationHelperException( 'Solarium exception: ' . $e );
		}
	}

	/// @see ReadableTTMServer::query
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

		$languageQuery = new \Elastica\Query\Term();
		$languageQuery->setTerm( 'language', $sourceLanguage );

		$boolQuery = new \Elastica\Query\Bool();
		$boolQuery->addMust( $languageQuery );
		$boolQuery->addMust( $fuzzyQuery );

		$query->setQuery( $boolQuery );

		/* The interface usually displays three best candidates. These might
		 * come from more than three matches, if the translation is the same.
		 * This might not find all suggestions, if the top N best matching
		 * source texts don't have translations, but worse matches do. We
		 * could loop with start parameter to fetch more until we have enough
		 * suggestions or the quality drops below the cutoff point. */
		$query->setSize( 25 );
		$query->setFields( array( 'globalid', 'content' ) );
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

			$sourceId = preg_replace( '~/[^/]+$~', '', $data['globalid'] );
			$contents[$sourceId] = $data['content'];
			$scores[$sourceId] = $score;
			$terms[] = "$sourceId/$targetLanguage";
		}

		$idQuery = new \Elastica\Query\Terms();
		$idQuery->setTerms( 'globalid', $terms );

		$query = new \Elastica\Query( $idQuery );
		$query->setSize( 25 );
		$query->setFields( array( 'wiki', 'uri', 'content', 'localid', 'globalid' ) );
		$resultset = $this->getType()->search( $query );

		$suggestions = array();
		foreach ( $resultset->getResults() as $result ) {
			$data = $result->getData();

			// Construct the matching source id
			$sourceId = preg_replace( '~/[^/]+$~', '', $data['globalid'] );

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

		var_dump( $suggestions );
		return $suggestions;
	}

	/* Write functions */

	public function update( MessageHandle $handle, $targetText ) {
		if ( $handle->getCode() === '' ) {
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

		$type = $this->getType();
		$title = $handle->getTitle();

		$doDelete = true;
		$sourceLanguage = '';
		if ( $handle->isValid() ) {
			$sourceLanguage = $handle->getGroup()->getSourceLanguage();
			if ( $handle->getCode() === $sourceLanguage ) {
				$doDelete = false;
			}
		}

		if ( $doDelete ) {
			$base = Title::makeTitle( $title->getNamespace(), $handle->getKey() );
			$conds = array(
				'wiki' => wfWikiId(),
				'language' => $handle->getCode(),
				'localid' => $base->getPrefixedText(),
			);
			foreach ( $conds as $key => &$value ) {
				$value = "$key:" . $update->getHelper()->escapePhrase( $value );
			}
			$update->addDeleteQuery( implode( ' AND ', $conds ) );
		}

		if ( $targetText !== null ) {
			if ( $handle->isValid() ) {
				// Of the message definition page
				$targetTitle = $handle->getTitle();
				$sourceTitle = Title::makeTitle(
					$targetTitle->getNamespace(),
					$handle->getKey() . '/' . $sourceLanguage
				);
				$revId = intval( $sourceTitle->getLatestRevID() );
				/* Note: in some cases the source page might not exist, in this case
				 * we use 0 as message version identifier, to differentiate them from
				 * orphan messages */
			} else {
				$revId = 'orphan';
			}

			$doc = $this->createDocument( $handle, $targetText, $revId );
			// Add document and commit within X seconds.
			$type->addDocument( $doc );
		}

		try {
			$type->getIndex()->refresh();
		// FIXME
		} catch ( Solarium_Exception $e ) {
			error_log( "SolrTTMServer update-write failed" );
			wfProfileOut( __METHOD__ );

			return false;
		}

		wfProfileOut( __METHOD__ );

		return true;
	}

	/**
	 * @return \Elastica\Document
	 */
	protected function createDocument( MessageHandle $handle, $text, $revId ) {
		$language = $handle->getCode();

		$localid = Title::makeTitle(
			$handle->getTitle()->getNamespace(),
			$handle->getKey()
		)->getPrefixedText();
		$wiki = wfWikiId();
		$globalid = "$wiki-$localid-$revId/$language";

		$data = array(
			'wiki' => $wiki,
			'uri' => $handle->getTitle()->getCanonicalUrl(),
			'localid' => $localid,
			'globalid' => $globalid,
			'language' => $language,
			'content' => $text,
			'group' => $handle->getGroupIds(),
		);

		return new \Elastica\Document( $globalid, $data );
	}

	public function beginBootstrap() {
		$type = $this->getType();
		$type->getIndex()->create( array(), true );

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
			'globalid' => array( 'type' => 'string', 'index' => 'not_analyzed' ),
			'localid'  => array( 'type' => 'string', 'index' => 'not_analyzed' ),
			'uri'      => array( 'type' => 'string', 'index' => 'not_analyzed' ),
			'language' => array( 'type' => 'string', 'index' => 'not_analyzed' ),
			'group'    => array( 'type' => 'string', 'index' => 'not_analyzed' ),
			'content'  => array( 'type' => 'string', 'index' => 'analyzed', 'term_vector' => 'yes' ),
		) );
		$mapping->send();
	}

	public function beginBatch() {
		$this->revIds = array();
		$this->docs = array();
	}

	public function batchInsertDefinitions( array $batch ) {
		$lb = new LinkBatch();
		foreach ( $batch as $data ) {
			$lb->addObj( $data[0] );
		}
		$lb->execute();

		foreach ( $batch as $key => $data ) {
			$this->revIds[$key] = $data[0]->getLatestRevID();
		}

		$this->batchInsertTranslations( $batch );
	}

	public function batchInsertTranslations( array $batch ) {
		$docs = array();
		foreach ( $batch as $key => $data ) {
			list( $title, , $text ) = $data;
			$handle = new MessageHandle( $title );
			$this->docs[] = $this->createDocument( $handle, $text, $this->revIds[$key] );
		}
	}

	public function endBatch() {
		$this->getType()->addDocuments( $this->docs );
	}

	public function endBootstrap() {
		$index = $this->getType()->getIndex();
		$index->refresh();
		$index->optimize();
		$index->getSettings()->setRefreshInterval( 5 );
	}

	protected function getType() {
		return $this->client->getIndex( 'ttmserver' )->getType( 'message' );
	}
}
