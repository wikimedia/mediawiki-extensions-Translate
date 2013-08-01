<?php
/**
 * A base module for querying message group related stats.
 *
 * @file
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * A base module for querying message group related stats.
 *
 * @ingroup API TranslateAPI
 * @since 2012-11-30
 */
abstract class ApiStatsQuery extends ApiQueryBase {
	public function getCacheMode( $params ) {
		return 'public';
	}

	public function execute() {
		$params = $this->extractRequestParams();
		MessageGroupStats::setTimeLimit( $params['timelimit'] );

		$cache = $this->getData();
		$result = $this->getResult();

		foreach ( $cache as $item => $stats ) {
			if ( $item < $params['offset'] ) {
				continue;
			}

			if ( $stats[MessageGroupStats::TOTAL] === null ) {
				$this->setContinueEnumParameter( 'offset', $item );
				break;
			}

			$data = $this->makeItem( $item, $stats );
			$result->addValue( array( 'query', $this->getModuleName() ), null, $data );
		}

		$result->setIndexedTagName_internal( array( 'query', $this->getModuleName() ), 'stats' );
	}

	protected function makeItem( $item, $stats ) {
		return array(
			'total' => $stats[MessageGroupStats::TOTAL],
			'translated' => $stats[MessageGroupStats::TRANSLATED],
			'fuzzy' => $stats[MessageGroupStats::FUZZY],
			'proofread' => $stats[MessageGroupStats::PROOFREAD],
		);
	}

	public function getAllowedParams() {
		return array(
			'offset' => array(
				ApiBase::PARAM_DFLT => 0,
				ApiBase::PARAM_TYPE => 'string',
			),
			'timelimit' => array(
				ApiBase::PARAM_DFLT => 8,
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_MAX => 10,
				ApiBase::PARAM_MIN => 0,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'offset' => 'If not all stats are calculated, you will get a query-continue parameter for offset you can use to get more.',
			'timelimit' => 'Maximum time to spend calculating missing statistics. If zero, only the cached results from the beginning are returned.',
		);
	}

	public function getVersion() {
		return __CLASS__ . ': ' . TRANSLATE_VERSION;
	}
}
