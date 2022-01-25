<?php
/**
 * A base module for querying message group related stats.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
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

	/**
	 * Implement this to implement input validation and return the name of the target that
	 * is then given to loadStats.
	 * @param array $params
	 * @return string
	 */
	abstract protected function validateTargetParamater( array $params );

	/**
	 * Implement this to load stats.
	 * @param string $target
	 * @param int $flags See MessageGroupStats for possible flags
	 * @return array[]
	 */
	abstract protected function loadStatistics( $target, $flags = 0 );

	public function execute() {
		$params = $this->extractRequestParams();

		$target = $this->validateTargetParamater( $params );
		$cache = $this->loadStatistics( $target, MessageGroupStats::FLAG_CACHE_ONLY );

		$result = $this->getResult();
		$incomplete = false;

		foreach ( $cache as $item => $stats ) {
			if ( $item < $params['offset'] ) {
				continue;
			}

			if ( $stats[MessageGroupStats::TOTAL] === null ) {
				$incomplete = true;
				$this->setContinueEnumParameter( 'offset', $item );
				break;
			}

			$data = $this->makeItem( $item, $stats );
			if ( $data === null ) {
				continue;
			}
			$result->addValue( [ 'query', $this->getModuleName() ], null, $data );
		}

		$result->addIndexedTagName( [ 'query', $this->getModuleName() ], 'stats' );

		if ( $incomplete ) {
			DeferredUpdates::addCallableUpdate( function () use ( $target ): void {
				$jobQueue = TranslateUtils::getJobQueueGroup();
				$jobQueue->push( $this->getCacheRebuildJob( $target ) );
			} );
		}
	}

	protected function makeItem( $item, $stats ) {
		return [
			'total' => $stats[MessageGroupStats::TOTAL],
			'translated' => $stats[MessageGroupStats::TRANSLATED],
			'fuzzy' => $stats[MessageGroupStats::FUZZY],
			'proofread' => $stats[MessageGroupStats::PROOFREAD],
		];
	}

	abstract protected function getCacheRebuildJob( string $target ): IJobSpecification;

	protected function getAllowedParams() {
		return [
			'offset' => [
				ApiBase::PARAM_DFLT => '0',
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_HELP_MSG => 'api-help-param-continue',
			],
		];
	}
}
