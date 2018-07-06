<?php
/**
 * TTMServer - The Translate extension translation memory interface
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup TTMServer
 */

use MediaWiki\Logger\LoggerFactory;

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
	 * Flag in the frozen index that indicates that all indices
	 * are frozen (useful only when this service shares the cluster with
	 * CirrusSearch)
	 */
	const ALL_INDEXES_FROZEN_NAME = 'freeze_everything';

	/**
	 * Type used in the frozen index
	 */
	const FROZEN_TYPE = 'frozen';

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

	/**
	 * @var ElasticSuggestionSearcher
	 */
	private $suggestionSearcher;

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
		return $this->getSuggestionSearcher()
			->getSuggestions( $sourceLanguage, $targetLanguage, $text );
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
			$boolQuery->addFilter( new Elastica\Query\Term( [ 'wiki' => wfWikiID() ] ) );
			$boolQuery->addFilter( new Elastica\Query\Term( [ 'language' => $handle->getCode() ] ) );
			$boolQuery->addFilter( new Elastica\Query\Term( [ 'localid' => $localid ] ) );

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
			function () use ( $doc ) {
				$this->getType()->addDocument( $doc );
			},
			function ( $e, $errors ) {
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

		$data = [
			'wiki' => $wiki,
			'uri' => $handle->getTitle()->getCanonicalURL(),
			'localid' => $localid,
			'language' => $language,
			'content' => $text,
			'group' => $handle->getGroupIds(),
		];

		return new \Elastica\Document( $globalid, $data );
	}

	/**
	 * Create index
	 * @param bool $rebuild Deletes index first if already exists
	 */
	public function createIndex( $rebuild ) {
		$indexSettings = [
			'number_of_shards' => $this->getShardCount(),
			'analysis' => [
				'filter' => [
					'prefix_filter' => [
						'type' => 'edge_ngram',
						'min_gram' => 2,
						'max_gram' => 20
					]
				],
				'analyzer' => [
					'prefix' => [
						'type' => 'custom',
						'tokenizer' => 'standard',
						'filter' => [ 'standard', 'lowercase', 'prefix_filter' ]
					],
					'casesensitive' => [
						'tokenizer' => 'standard',
						'filter' => [ 'standard' ]
					]
				]
			]
		];
		$replicas = $this->getReplicaCount();
		if ( strpos( $replicas, '-' ) === false ) {
			$indexSettings['number_of_replicas'] = $replicas;
		} else {
			$indexSettings['auto_expand_replicas'] = $replicas;
		}

		$type = $this->getType();
		$type->getIndex()->create( $indexSettings, $rebuild );
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

		$keywordType = [ 'type' => 'string', 'index' => 'not_analyzed' ];
		$textType = 'string';
		if ( $this->isElastica5() ) {
			$keywordType = [ 'type' => 'keyword' ];
			$textType = 'text';
		}
		$mapping->setProperties( [
			'wiki'     => $keywordType,
			'localid'  => $keywordType,
			'uri'      => $keywordType,
			'language' => $keywordType,
			'group'    => $keywordType,
			'content'  => [
				'type' => $textType,
				'fields' => [
					'content' => [
						'type' => $textType,
						'index' => 'analyzed',
						'term_vector' => 'yes'
					],
					'prefix_complete' => [
						'type' => $textType,
						'analyzer' => 'prefix',
						'search_analyzer' => 'standard',
						'term_vector' => 'yes'
					],
					'case_sensitive' => [
						'type' => $textType,
						'index' => 'analyzed',
						'analyzer' => 'casesensitive',
						'term_vector' => 'yes'
					]
				]
			],
		] );
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
		$docs = [];
		foreach ( $batch as $data ) {
			list( $handle, $sourceLanguage, $text ) = $data;
			$revId = $handle->getTitleForLanguage( $sourceLanguage )->getLatestRevID();
			$docs[] = $this->createDocument( $handle, $text, $revId );
		}

		MWElasticUtils::withRetry( self::BULK_INDEX_RETRY_ATTEMPTS,
			function () use ( $docs ) {
				$this->getType()->addDocuments( $docs );
			},
			function ( $e, $errors ) {
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
		if ( $this->isElastica5() ) {
			$index->forcemerge();
		} else {
			$index->optimize();
		}
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
		return isset( $this->config['use_wikimedia_extra'] ) && $this->config['use_wikimedia_extra'];
	}

	/**
	 * @return string
	 */
	private function getIndexName() {
		if ( isset( $this->config['index'] ) ) {
			return $this->config['index'];
		} else {
			return 'ttmserver';
		}
	}

	public function getType() {
		return $this->getClient()
			->getIndex( $this->getIndexName() )
			->getType( 'message' );
	}

	protected function getShardCount() {
		return isset( $this->config['shards'] ) ? $this->config['shards'] : 1;
	}

	protected function getReplicaCount() {
		return isset( $this->config['replicas'] ) ? $this->config['replicas'] : '0-2';
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
	 * @return bool true if the index is green false otherwise.
	 */
	protected function waitForGreen( $indexName, $timeout ) {
		$startTime = time();
		while ( ( $startTime + $timeout ) > time() ) {
			try {
				$response = $this->getIndexHealth( $indexName );
				$status = isset( $response['status'] ) ? $response['status'] : 'unknown';
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

	/**
	 * Parse query string and build the search query
	 * @param string $queryString
	 * @param array $opts
	 * @return array
	 */
	protected function parseQueryString( $queryString, array $opts ) {
		$fields = $highlights = [];
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
				$highlights[$analyzer] = [
					'number_of_fragments' => 0
				];

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

		return [ $searchQuery, $highlights ];
	}

	/**
	 * Search interface
	 * @param string $queryString
	 * @param array $opts
	 * @param array $highlight
	 * @return array
	 */
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
		$query->setHighlight( [
			// The value must be an object
			'pre_tags' => [ $pre ],
			'post_tags' => [ $post ],
			'fields' => $highlights,
		] );

		try {
			return $this->getType()->getIndex()->search( $query );
		} catch ( \Elastica\Exception\ExceptionInterface $e ) {
			throw new TTMServerException( $e->getMessage() );
		}
	}

	public function getFacets( $resultset ) {
		$aggs = $resultset->getAggregations();

		$ret = [
			'language' => [],
			'group' => []
		];

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
		$ret = [];
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

		$search = new \Elastica\Search( $this->getClient() );
		$search->setQuery( $query );
		$search->addType( $type );
		$search->addIndex( $type->getIndex() );
		$scroll = new \Elastica\Scroll( $search, '15m' );

		foreach ( $scroll as $results ) {
			$ids = [];
			foreach ( $results as $result ) {
				$ids[] = $result->getId();
			}

			if ( $ids === [] ) {
				continue;
			}

			MWElasticUtils::withRetry( $retryAttempts,
				function () use ( $ids, $type ) {
					$type->deleteIds( $ids );
				}
			);
		}
	}

	/**
	 * @return bool
	 */
	public function isFrozen() {
		if ( !isset( $this->config['frozen_index'] ) ) {
			return false;
		}
		$frozenIndex = $this->config['frozen_index'];
		$indices = [ static::ALL_INDEXES_FROZEN_NAME, $this->getIndexName() ];
		$ids = new \Elastica\Query\Ids( null, $indices );

		try {
			$resp = $this->getClient()
				->getIndex( $frozenIndex )
				->getType( static::FROZEN_TYPE )
				->search( \Elastica\Query::create( $ids ) );

			if ( $resp->count() === 0 ) {
				return false;
			} else {
				return true;
			}
		} catch ( Exception $e ) {
			LoggerFactory::getInstance( 'ElasticSearchTTMServer' )->warning(
				'Problem encountered while checking the frozen index.',
				[ 'exception' => $e ]
			);
			return false;
		}
	}

	/**
	 * @return array
	 */
	public function getConfig() {
		return $this->config;
	}

	/**
	 * @return ElasticSuggestionSearcher
	 */
	private function getSuggestionSearcher() {
		if ( $this->suggestionSearcher === null ) {
			$this->suggestionSearcher = ElasticSuggestionSearcherFactory::getSuggestionSearcher( $this );
		}
		return $this->suggestionSearcher;
	}

	/**
	 * @return bool true if running with Elastica 5+
	 */
	public function isElastica5() {
		// Sadly Elastica does not seem to expose its version so we
		// check the inexistence of a class that was removed in the
		// version 5
		return !class_exists( \Elastica\Script::class );
	}
}
