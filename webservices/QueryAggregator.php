<?php
/**
 * Web service utility class.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Runs multiple web service queries asynchronously to save time.
 *
 * @ingroup TranslationWebService
 * @since 2015.02
 */
class QueryAggregator {
	protected $queries = array();
	protected $responses = array();
	protected $timeout = 0;
	protected $hasRun = false;

	/**
	 * Register a query to be run.
	 * @param TranslationQuery $query
	 * @return mixed Query id that can be used to fetch results.
	 */
	public function addQuery( TranslationQuery $query ) {
		$this->queries[] = $query;

		$this->timeout = max( $query->getTimeout(), $this->timeout );
		return count( $this->queries ) - 1;
	}

	/**
	 * Returns a response for a query.
	 * @param mixed $id Query id.
	 * @return TranslationQueryResponse
	 * @throws RuntimeException if called before run() has been called.
	 */
	public function getResponse( $id ) {
		if ( !$this->hasRun ) {
			throw new RuntimeException( 'Tried to get response before queries ran' );
		}

		return TranslationQueryResponse::newFromMultiHttp( $this->responses[$id] );
	}

	/**
	 * Runs all the queries.
	 */
	public function run() {
		$http = new MultiHttpClient( array(
			'reqTimeout' => $this->timeout,
			'connTimeout' => 3
		) );
		$responses = $http->runMulti( $this->getMultiHttpQueries( $this->queries ) );
		foreach ( $responses as $index => $response ) {
			$this->responses[$index] = $response;
		}
		$this->hasRun = true;
	}

	/**
	 * Formats queries for format used by MultiHttpClient class.
	 * @param TranslationQuery[] $queries
	 * @return array[]
	 */
	protected function getMultiHttpQueries( $queries ) {
		$converter = function( TranslationQuery $q ) {
			return array(
				'url' => $q->getUrl(),
				'method' => $q->getMethod(),
				'query' => $q->getQueryParameters(),
				'body' => $q->getBody(),
			);
		};

		return array_map( $converter, $queries );
	}
}
