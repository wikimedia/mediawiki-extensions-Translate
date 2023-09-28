<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\WebService;

use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\MediaWikiServices;
use RuntimeException;

/**
 * Web service utility class. Runs multiple web service queries asynchronously to save time.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2015.02
 * @ingroup TranslationWebService
 */
class QueryAggregator {
	protected array $queries = [];
	protected array $responses = [];
	protected float $timeout = 0;
	protected bool $hasRun = false;

	/**
	 * Register a query to be run.
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
	 * @throws RuntimeException if called before run() has been called.
	 */
	public function getResponse( $id ): TranslationQueryResponse {
		if ( !$this->hasRun ) {
			throw new RuntimeException( 'Tried to get response before queries ran' );
		}

		return new TranslationQueryResponse( $this->responses[$id], $this->queries[$id] );
	}

	/** Runs all the queries. */
	public function run(): void {
		global $wgSitename;

		$version = Utilities::getVersion();

		$clientOptions = [
			'reqTimeout' => $this->timeout,
			'connTimeout' => 3,
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
	protected function getMultiHttpQueries( array $queries ): array {
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
