<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use IJobSpecification;
use JobQueueGroup;
use MediaWiki\Api\ApiQuery;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * Api module for querying message group stats.
 * @ingroup API TranslateAPI
 * @author Tim Gerundt
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Tim Gerundt
 * @license GPL-2.0-or-later
 */
class QueryMessageGroupStatsActionApi extends QueryStatsActionApi {
	/** Whether to hide rows which are fully translated. */
	private bool $noComplete = false;
	/** Whether to hide rows which are fully untranslated. */
	private bool $noEmpty = false;

	public function __construct(
		ApiQuery $query,
		string $moduleName,
		JobQueueGroup $jobQueueGroup
	) {
		parent::__construct( $query, $moduleName, 'mgs', $jobQueueGroup );
	}

	// ApiStatsQuery methods

	/** @inheritDoc */
	protected function validateTargetParamater( array $params ): string {
		$group = MessageGroups::getGroup( $params['group'] );
		if ( !$group ) {
			$this->dieWithError( [ 'apierror-badparameter', 'mgsgroup' ] );
		} elseif ( MessageGroups::isDynamic( $group ) ) {
			$this->dieWithError( 'apierror-translate-nodynamicgroups', 'invalidparam' );
		}

		return $group->getId();
	}

	/** @inheritDoc */
	protected function loadStatistics( string $target, int $flags = 0 ): array {
		return MessageGroupStats::forGroup( $target, $flags );
	}

	/** @inheritDoc */
	public function execute() {
		$params = $this->extractRequestParams();

		$this->noComplete = $params['suppresscomplete'];
		$this->noEmpty = $params['suppressempty'];

		parent::execute();
	}

	/** @inheritDoc */
	protected function makeStatsItem( string $item, array $stats ): ?array {
		$data = $this->makeItem( $stats );

		if ( $this->noComplete && $data['fuzzy'] === 0 && $data['translated'] === $data['total'] ) {
			return null;
		}

		if ( $this->noEmpty && $data['translated'] === 0 && $data['fuzzy'] === 0 ) {
			return null;
		}

		// Skip below 2% if "don't show without translations" is checked.
		if ( $this->noEmpty && ( $data['translated'] / $data['total'] ) < 0.02 ) {
			return null;
		}

		$data['code'] = $item; // For BC
		$data['language'] = $item;

		return $data;
	}

	/** @inheritDoc */
	protected function getCacheRebuildJob( string $target ): IJobSpecification {
		return RebuildMessageGroupStatsJob::newJob( [ 'groupid' => $target ] );
	}

	// Api methods

	/** @inheritDoc */
	protected function getAllowedParams(): array {
		$params = parent::getAllowedParams();
		$params['group'] = [
			ParamValidator::PARAM_TYPE => 'string',
			ParamValidator::PARAM_REQUIRED => true,
		];

		$params['suppresscomplete'] = false;
		$params['suppressempty'] = false;

		return $params;
	}

	/** @inheritDoc */
	protected function getExamplesMessages(): array {
		return [
			'action=query&meta=messagegroupstats&mgsgroup=page-Example'
				=> 'apihelp-query+messagegroupstats-example-1',
		];
	}
}
