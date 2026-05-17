<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiMain;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\Statistics\RebuildMessageGroupStatsJob;
use MediaWiki\JobQueue\JobQueueGroup;
use MediaWiki\Logging\ManualLogEntry;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * API module to discourage/encourage a page
 *
 * @license GPL-2.0-or-later
 */
class DiscourageActionApi extends ApiBase {

	public function __construct(
		ApiMain $mainModule,
		string $moduleName,
		private readonly JobQueueGroup $jobQueueGroup,
	) {
		parent::__construct( $mainModule, $moduleName );
	}

	public function execute() {
		$this->checkUserRightsAny( 'pagetranslation' );

		$params = $this->extractRequestParams();
		$title = $this->getTitleFromTitleOrPageId( $params );

		if ( TranslatablePage::newFromTitle( $title )->getMarkedTag() === null ) {
			$this->dieWithError( 'apierror-discouragetranslation-invalidtitle' );
		}

		$id = TranslatablePage::getMessageGroupIdFromTitle( $title );

		$newstate = $params['do'] === 'encourage' ? '' : 'discouraged';

		$current = MessageGroups::getPriority( $id );

		if ( $current === $newstate ) {
			$this->dieWithError( 'apierror-discouragetranslation-alreadydone' );
		}

		MessageGroups::setPriority( $id, $newstate );

		$entry = new ManualLogEntry( 'pagetranslation', $params['do'] );
		$entry->setPerformer( $this->getUser() );
		$entry->setTarget( $title );
		if ( ( $params['reason'] ?? '' ) !== '' ) {
			$entry->setComment( $params['reason'] );
		}
		$logId = $entry->insert();
		$entry->publish( $logId );

		// Defer stats purging of parent aggregate groups. Shared groups can contain other
		// groups as well, which we do not need to update. We could filter non-aggregate
		// groups out, or use MessageGroups::getParentGroups, though it has an inconvenient
		// return value format for this use case.
		$group = MessageGroups::getGroup( $id );
		if ( $group ) {
			$sharedGroupIds = MessageGroups::getSharedGroups( $group );
			if ( $sharedGroupIds !== [] ) {
				$job = RebuildMessageGroupStatsJob::newRefreshGroupsJob( $sharedGroupIds );
				$this->jobQueueGroup->push( $job );
			}
		}
	}

	/** @inheritDoc */
	public function isWriteMode() {
		return true;
	}

	/** @inheritDoc */
	public function needsToken() {
		return 'csrf';
	}

	protected function getAllowedParams(): array {
		return [
			'title' => [
				ParamValidator::PARAM_TYPE => 'string',
			],
			'pageid' => [
				ParamValidator::PARAM_TYPE => 'integer',
			],
			'do' => [
				ParamValidator::PARAM_TYPE => [ 'discourage', 'encourage' ],
				ParamValidator::PARAM_REQUIRED => true,
				ApiBase::PARAM_HELP_MSG_PER_VALUE => [],
			],
			'reason' => [
				ParamValidator::PARAM_TYPE => 'string',
			],
		];
	}

}
