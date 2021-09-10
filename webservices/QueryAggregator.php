<?php
/**
 * Web service utility class.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;

/**
 * Runs multiple web service queries asynchronously to save time.
 *
 * @ingroup TranslationWebService
 * @since 2015.02
 */
class QueryAggregator {
	protected $queries = [];
	protected $responses = [];
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

		return TranslationQueryResponse::newFromMultiHttp(
			$this->responses[$id],
			$this->queries[$id]
		);
	}

	/**
	 * Runs all the queries.
	 */
	public function run() {
		global $wgSitename;

		$version = TranslateUtils::getVersion();

		$clientOptions = [
			'reqTimeout' => $this->timeout,
			'connTimeout' => 3,
			// @phan-suppress-next-line PhanPossiblyUndeclaredVariable
			'userAgent' => "MediaWiki Translate extension $version for $wgSitename"
		];

		$httpRequestFactory = MediaWikiServices::getInstance()->getHttpRequestFactory();

		$http = $httpRequestFactory->createMultiClient( $clientOptions );
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
		$converter = static function ( TranslationQuery $q ) {
			return [
				'url' => $q->getUrl(),
				'method' => $q->getMethod(),
				'query' => $q->getQueryParameters(),
				'body' => $q->getBody(),
				'headers' => $q->getHeaders(),
			];
		};

		return array_map( $converter, $queries );
	}
}
