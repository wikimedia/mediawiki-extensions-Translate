<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use IJobSpecification;
use JobQueueGroup;
use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiQuery;
use MediaWiki\Api\ApiQueryBase;
use MediaWiki\Deferred\DeferredUpdates;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * A base module for querying message group related stats.
 *
 * @ingroup API TranslateAPI
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2012-11-30
 */
abstract class QueryStatsActionApi extends ApiQueryBase {
	private JobQueueGroup $jobQueueGroup;

	public function __construct(
		ApiQuery $queryModule,
		string $moduleName,
		string $paramPrefix,
		JobQueueGroup $jobQueueGroup
	) {
		parent::__construct( $queryModule, $moduleName, $paramPrefix );
		$this->jobQueueGroup = $jobQueueGroup;
	}

	/** @inheritDoc */
	public function getCacheMode( $params ): string {
		return 'public';
	}

	/**
	 * Implement this to implement input validation and return the name of the target that
	 * is then given to loadStats.
	 */
	abstract protected function validateTargetParamater( array $params ): string;

	/**
	 * Implement this to load stats.
	 * @param string $target
	 * @param int $flags See MessageGroupStats for possible flags
	 * @return array[]
	 */
	abstract protected function loadStatistics( string $target, int $flags = 0 ): array;

	/** Implement this to load each individual stat item */
	abstract protected function makeStatsItem( string $item, array $stats ): ?array;

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

			$data = $this->makeStatsItem( $item, $stats );
			if ( $data === null ) {
				continue;
			}
			$result->addValue( [ 'query', $this->getModuleName() ], null, $data );
		}

		$result->addIndexedTagName( [ 'query', $this->getModuleName() ], 'stats' );

		if ( $incomplete ) {
			DeferredUpdates::addCallableUpdate( function () use ( $target ): void {
				$this->jobQueueGroup->push( $this->getCacheRebuildJob( $target ) );
			} );
		}
	}

	protected function makeItem( array $stats ): array {
		return [
			'total' => $stats[MessageGroupStats::TOTAL],
			'translated' => $stats[MessageGroupStats::TRANSLATED],
			'fuzzy' => $stats[MessageGroupStats::FUZZY],
			'proofread' => $stats[MessageGroupStats::PROOFREAD],
		];
	}

	abstract protected function getCacheRebuildJob( string $target ): IJobSpecification;

	protected function getAllowedParams(): array {
		return [
			'offset' => [
				ParamValidator::PARAM_DEFAULT => '0',
				ParamValidator::PARAM_TYPE => 'string',
				ApiBase::PARAM_HELP_MSG => 'api-help-param-continue',
			],
		];
	}
}
