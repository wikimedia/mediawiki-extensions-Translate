<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Aid;

use QueryAggregator;
use QueryAggregatorAware;
use TranslationWebService;

/**
 * Helper class for translation aids that use web services.
 * @ingroup TranslationAids
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2015.02
 */
abstract class QueryAggregatorAwareTranslationAid
	extends TranslationAid
	implements QueryAggregatorAware
{
	private $queries = [];
	/** @var QueryAggregator */
	private $aggregator;

	public function setQueryAggregator( QueryAggregator $aggregator ): void {
		$this->aggregator = $aggregator;
	}

	/**
	 * Stores a web service query for later execution.
	 * @param TranslationWebService $service
	 * @param string $from
	 * @param string $to
	 * @param string $text
	 * @return void
	 */
	protected function storeQuery(
		TranslationWebService $service,
		string $from,
		string $to,
		string $text
	): void {
		$queries = $service->getQueries( $text, $from, $to );
		foreach ( $queries as $query ) {
			$this->queries[] = [
				'id' => $this->aggregator->addQuery( $query ),
				'language' => $from,
				'text' => $text,
				'service' => $service,
			];
		}
	}

	/**
	 * Returns all stored queries.
	 * @return array Map of executed queries:
	 *  - language: string: source language
	 *  - text: string: source text
	 *  - response: TranslationQueryResponse
	 */
	protected function getQueryData(): array {
		foreach ( $this->queries as &$queryData ) {
			$queryData['response'] = $this->aggregator->getResponse( $queryData['id'] );
			unset( $queryData['id'] );
		}

		return $this->queries;
	}
}
