<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use AggregateMessageGroup;
use JobQueueGroup;
use ManualLogEntry;
use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiMain;
use MediaWiki\Extension\Translate\LogNames;
use MediaWiki\Extension\Translate\MessageLoading\RebuildMessageIndexJob;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;
use MediaWiki\Logger\LoggerFactory;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * API module for managing aggregate message groups
 * Only supports aggregate message groups defined inside the wiki.
 * Aggregate message group defined in YAML configuration cannot be altered.
 * @author Santhosh Thottingal
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Santhosh Thottingal
 * @license GPL-2.0-or-later
 * @ingroup API TranslateAPI
 */
class AggregateGroupsActionApi extends ApiBase {
	private JobQueueGroup $jobQueueGroup;
	protected static string $right = 'translate-manage';
	private MessageGroupMetadata $messageGroupMetadata;
	private AggregateGroupManager $aggregateGroupManager;

	public function __construct(
		ApiMain $main,
		string $action,
		JobQueueGroup $jobQueueGroup,
		MessageGroupMetadata $messageGroupMetadata,
		AggregateGroupManager $aggregateGroupManager
	) {
		parent::__construct( $main, $action );
		$this->jobQueueGroup = $jobQueueGroup;
		$this->messageGroupMetadata = $messageGroupMetadata;
		$this->aggregateGroupManager = $aggregateGroupManager;
	}

	public function execute(): void {
		$this->checkUserRightsAny( self::$right );
		$block = $this->getUser()->getBlock();
		if ( $block && $block->isSitewide() ) {
			$this->dieBlocked( $block );
		}

		$params = $this->extractRequestParams();
		$action = $params['do'];
		$output = [];
		if ( $action === 'associate' || $action === 'dissociate' ) {
			// Group or groups is mandatory only for these two actions
			$this->requireOnlyOneParameter( $params, 'group', 'groups' );

			if ( isset( $params['groups'] ) ) {
				$subgroupIds = array_map( 'trim', $params['groups'] );
			} else {
				$subgroupIds = [ $params['group'] ];
			}

			if ( !isset( $params['aggregategroup'] ) ) {
				$this->dieWithError( [ 'apierror-missingparam', 'aggregategroup' ] );
			}

			$aggregateGroupId = $params['aggregategroup'];

			try {
				if ( $action === 'associate' ) {
					// Not all subgroups passed maybe added, as some may already be part of the aggregate group
					$groupIdsToLog = $this->aggregateGroupManager->associate( $aggregateGroupId, $subgroupIds );
					$output[ 'groupUrls'] = [];
					foreach ( $groupIdsToLog as $subgroupId ) {
						$output[ 'groupUrls'][ $subgroupId ] =
							$this->aggregateGroupManager->getTargetTitleByGroupId( $subgroupId )->getFullURL();
					}
				} else {
					$groupIdsToLog = $this->aggregateGroupManager->disassociate( $aggregateGroupId, $subgroupIds );
				}
			} catch (
				AggregateGroupAssociationFailure |
				AggregateGroupLanguageMismatchException |
				AggregateGroupNotFoundException $e
			) {
				$this->dieWithException( $e );
			}

			$logParams = [
				'aggregategroup' => $this->messageGroupMetadata->get( $aggregateGroupId, 'name' ),
				'aggregategroup-id' => $aggregateGroupId,
			];

			/* To allow removing no longer existing groups from aggregate message groups,
			 * the message group object $group might not always be available.
			 * In this case, we need to fake some title. */
			foreach ( $groupIdsToLog as $subgroupId ) {
				$title = $this->aggregateGroupManager->getTargetTitleByGroupId( $subgroupId );
				$entry = new ManualLogEntry( 'pagetranslation', $action );
				$entry->setPerformer( $this->getUser() );
				$entry->setTarget( $title );
				// @todo
				// $entry->setComment( $comment );
				$entry->setParameters( $logParams );

				$logId = $entry->insert();
				$entry->publish( $logId );
			}
		} elseif ( $action === 'remove' ) {
			if ( !isset( $params['aggregategroup'] ) ) {
				$this->dieWithError( [ 'apierror-missingparam', 'aggregategroup' ] );
			}

			$aggregateGroupId = $params['aggregategroup'];
			$group = MessageGroups::getGroup( $aggregateGroupId );
			if ( !( $group instanceof AggregateMessageGroup ) ) {
				$this->dieWithError(
					'apierror-translate-invalidaggregategroupname',
					'invalidaggregategroupname'
				);
			}

			$this->messageGroupMetadata->deleteGroup( $params['aggregategroup'] );
			$logger = LoggerFactory::getInstance( LogNames::MAIN );
			$logger->info(
				'Aggregate group {groupId} has been deleted.',
				[ 'groupId' => $aggregateGroupId ]
			);
		} elseif ( $action === 'add' ) {
			if ( !isset( $params['groupname'] ) ) {
				$this->dieWithError( [ 'apierror-missingparam', 'groupname' ] );
			}
			$name = trim( $params['groupname'] );
			if ( strlen( $name ) === 0 ) {
				$this->dieWithError(
					'apierror-translate-invalidaggregategroupname',
					'invalidaggregategroupname'
				);
			}

			$desc = trim( $params['groupdescription'] );
			$languageCode = trim( $params['groupsourcelanguagecode'] );
			$languageCode = $languageCode === AggregateMessageGroup::UNDETERMINED_LANGUAGE_CODE ?
				null : $languageCode;
			try {
				$aggregateGroupId = $this->aggregateGroupManager->add( $name, $desc, $languageCode );
			} catch ( DuplicateAggregateGroupException $e ) {
				$this->dieWithException( $e );
			}

			// Once a new aggregate group is added, we need to show all the pages that can be added to that.
			$output['groups'] = $this->getIncludableGroups();
			$output['aggregategroupId'] = $aggregateGroupId;
			// @todo Logging
		} elseif ( $action === 'update' ) {
			if ( !isset( $params['groupname'] ) ) {
				$this->dieWithError( [ 'apierror-missingparam', 'groupname' ] );
			}
			$name = trim( $params['groupname'] );
			if ( strlen( $name ) === 0 ) {
				$this->dieWithError(
					'apierror-translate-invalidaggregategroupname',
					'invalidaggregategroupname'
				);
			}

			$aggregateGroupId = $params['aggregategroup'];
			$oldName = $this->messageGroupMetadata->get( $aggregateGroupId, 'name' );

			// Error if the label exists already
			$exists = MessageGroups::labelExists( $name );
			if ( $exists && $oldName !== $name ) {
				$this->dieWithException( new DuplicateAggregateGroupException( $name ) );
			}

			$desc = trim( $params['groupdescription'] );

			$newLanguageCode = trim( $params['groupsourcelanguagecode'] );

			$oldDesc = $this->messageGroupMetadata->get( $aggregateGroupId, 'description' );
			$currentLanguageCode = $this->messageGroupMetadata->get( $aggregateGroupId, 'sourcelanguagecode' );

			if (
				$newLanguageCode !== AggregateMessageGroup::UNDETERMINED_LANGUAGE_CODE &&
				$newLanguageCode !== $currentLanguageCode
			) {
				$groupsWithDifferentLanguage =
					$this->getGroupsWithDifferentLanguage( $aggregateGroupId, $newLanguageCode );

				if ( count( $groupsWithDifferentLanguage ) ) {
					$this->dieWithError( [
						'translate-error-aggregategroup-source-language-mismatch',
						implode( ', ', $groupsWithDifferentLanguage ),
						$newLanguageCode,
						count( $groupsWithDifferentLanguage )
					] );
				}
			}

			if (
				$oldName === $name
				&& $oldDesc === $desc
				&& $newLanguageCode === $currentLanguageCode
			) {
				$this->dieWithError( 'apierror-translate-invalidupdate', 'invalidupdate' );
			}
			$this->messageGroupMetadata->set( $aggregateGroupId, 'name', $name );
			$this->messageGroupMetadata->set( $aggregateGroupId, 'description', $desc );
			if ( $newLanguageCode === AggregateMessageGroup::UNDETERMINED_LANGUAGE_CODE ) {
				$this->messageGroupMetadata->clearMetadata( $aggregateGroupId, [ 'sourcelanguagecode' ] );
			} else {
				$this->messageGroupMetadata->set( $aggregateGroupId, 'sourcelanguagecode', $newLanguageCode );
			}
		}

		// If we got this far, nothing has failed
		$output['result'] = 'ok';
		$this->getResult()->addValue( null, $this->getModuleName(), $output );
		// Cache needs to be cleared after any changes to groups
		MessageGroups::singleton()->recache();
		$this->jobQueueGroup->push( RebuildMessageIndexJob::newJob() );
	}

	/**
	 * Aggregate groups have an explicit source language that should match with
	 * the associated message group source language. Thus, we check which of the subgroups
	 * of an aggregate group don't have a matching source language.
	 */
	private function getGroupsWithDifferentLanguage(
		string $aggregateGroupId,
		string $sourceLanguageCode
	): array {
		$groupsWithDifferentLanguage = [];
		$subgroups = $this->messageGroupMetadata->getSubgroups( $aggregateGroupId );
		foreach ( $subgroups as $group ) {
			$messageGroup = MessageGroups::getGroup( $group );
			$messageGroupLanguage = $messageGroup->getSourceLanguage();
			if ( $messageGroupLanguage !== $sourceLanguageCode ) {
				$groupsWithDifferentLanguage[] = $messageGroup->getLabel();
			}
		}

		return $groupsWithDifferentLanguage;
	}

	public function isWriteMode(): bool {
		return true;
	}

	public function needsToken(): string {
		return 'csrf';
	}

	protected function getAllowedParams(): array {
		return [
			'do' => [
				ParamValidator::PARAM_TYPE => [ 'associate', 'dissociate', 'remove', 'add', 'update' ],
				ParamValidator::PARAM_REQUIRED => true,
			],
			'aggregategroup' => [
				ParamValidator::PARAM_TYPE => 'string',
			],
			'group' => [
				// For backward compatibility
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_DEPRECATED => true,
			],
			'groups' => [
				// Not providing list of values, to allow dissociation of unknown groups
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_ISMULTI => true,
			],
			'groupname' => [
				ParamValidator::PARAM_TYPE => 'string',
			],
			'groupdescription' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_DEFAULT => '',
			],
			'groupsourcelanguagecode' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_DEFAULT => AggregateMessageGroup::UNDETERMINED_LANGUAGE_CODE,
			],
		];
	}

	protected function getExamplesMessages(): array {
		return [
			'action=aggregategroups&do=associate&groups=groupId1|groupId2&aggregategroup=aggregateGroupId'
				=> 'apihelp-aggregategroups-example-1',
		];
	}

	private function getIncludableGroups(): array {
		$groups = MessageGroups::getAllGroups();
		$pages = [];
		foreach ( $groups as $group ) {
			if ( $this->aggregateGroupManager->supportsAggregation( $group ) ) {
				$pages[$group->getId()] = $group->getLabel( $this->getContext() );
			}
		}

		return $pages;
	}
}
