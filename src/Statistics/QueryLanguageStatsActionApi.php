<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use IJobSpecification;
use JobQueueGroup;
use MediaWiki\Api\ApiQuery;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * Api module for querying language stats.
 * @ingroup API TranslateAPI
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class QueryLanguageStatsActionApi extends QueryStatsActionApi {
	public function __construct(
		ApiQuery $query,
		string $moduleName,
		JobQueueGroup $jobQueueGroup
	) {
		parent::__construct( $query, $moduleName, 'ls', $jobQueueGroup );
	}

	// ApiStatsQuery methods

	/** @inheritDoc */
	protected function validateTargetParamater( array $params ): string {
		$requested = $params[ 'language' ];
		if ( !Utilities::isSupportedLanguageCode( $requested ) ) {
			$this->dieWithError( [ 'apierror-translate-invalidlanguage', $requested ] );
		}

		return $requested;
	}

	/** @inheritDoc */
	protected function loadStatistics( string $target, int $flags = 0 ): array {
		$groupId = $this->getParameter( 'group' );
		$group = $groupId !== null ? MessageGroups::getGroup( $groupId ) : null;
		if ( $groupId ) {
			if ( !$group ) {
				$this->dieWithError( [ 'apierror-badparameter', 'group' ] );
			}

			return [ $groupId => MessageGroupStats::forItem( $group->getId(), $target, $flags ) ];
		} else {
			return MessageGroupStats::forLanguage( $target, $flags );
		}
	}

	/** @inheritDoc */
	protected function makeStatsItem( string $item, array $stats ): array {
		$data = $this->makeItem( $stats );
		$data['group'] = $item;

		return $data;
	}

	/** @inheritDoc */
	protected function getCacheRebuildJob( string $target ): IJobSpecification {
		return RebuildMessageGroupStatsJob::newJob( [ 'languagecode' => $target ] );
	}

	// Api methods

	/** @inheritDoc */
	protected function getAllowedParams(): array {
		$params = parent::getAllowedParams();
		$params['language'] = [
			ParamValidator::PARAM_TYPE => 'string',
			ParamValidator::PARAM_REQUIRED => true,
		];

		$params['group'] = [
			ParamValidator::PARAM_TYPE => 'string',
		];

		return $params;
	}

	/** @inheritDoc */
	protected function getExamplesMessages(): array {
		return [
			'action=query&meta=languagestats&lslanguage=fi'
				=> 'apihelp-query+languagestats-example-1',
			'action=query&meta=languagestats&lslanguage=fi&group=A'
				=> 'apihelp-query+languagestats-example-2'
		];
	}
}
