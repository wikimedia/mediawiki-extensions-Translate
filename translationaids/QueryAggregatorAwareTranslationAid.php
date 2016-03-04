<?php
/**
 * Translation aid helper class.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Helper class for translation aids which use web services.
 *
 * @ingroup TranslationAids
 * @since 2015.02
 */
abstract class QueryAggregatorAwareTranslationAid
	extends TranslationAid
	implements QueryAggregatorAware
{
	private $queries = array();
	private $aggregator;

	// Interface: QueryAggregatorAware
	public function setQueryAggregator( QueryAggregator $aggregator ) {
		$this->aggregator = $aggregator;
	}

	/**
	 * Stores a web service query for later execution.
	 * @param TranslationWebService $service
	 * @param string $from Source language
	 * @param string $to Target language
	 * @param string $text Source text
	 */
	protected function storeQuery( TranslationWebService $service, $from, $to, $text ) {
		$queries = $service->getQueries( $text, $from, $to );
		foreach ( $queries as $query ) {
			$this->queries[] = array(
				'id' => $this->aggregator->addQuery( $query ),
				'language' => $from,
				'text' => $text,
				'service' => $service,
			);
		}
	}

	/**
	 * Returns all stored queries.
	 * @return array Map of executed queries:
	 *  - language: string: source language
	 *  - text: string: source text
	 *  - response: TranslationQueryResponse
	 */
	protected function getQueryData() {
		foreach ( $this->queries as &$queryData ) {
			$queryData['response'] = $this->aggregator->getResponse( $queryData['id'] );
			unset( $queryData['id'] );
		}

		return $this->queries;
	}

	/**
	 * Returns all web services of given type.
	 * @param string $type
	 * @return TranslationWebService[]
	 */
	protected function getWebServices( $type ) {
		global $wgTranslateTranslationServices;

		$services = array();
		foreach ( $wgTranslateTranslationServices as $name => $config ) {
			$service = TranslationWebService::factory( $name, $config );
			if ( !$service || $service->getType() !== $type ) {
				continue;
			}

			$services[$name] = $service;
		}

		return $services;
	}
}
