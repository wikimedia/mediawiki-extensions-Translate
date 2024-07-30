<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use AggregateMessageGroup;
use ApiBase;
use ApiMain;
use JobQueueGroup;
use ManualLogEntry;
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
	private const NO_LANGUAGE_CODE = '-';
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
			// Group is mandatory only for these two actions
			if ( !isset( $params['group'] ) ) {
				$this->dieWithError( [ 'apierror-missingparam', 'group' ] );
			}
			if ( !isset( $params['aggregategroup'] ) ) {
				$this->dieWithError( [ 'apierror-missingparam', 'aggregategroup' ] );
			}
			$aggregateGroup = $params['aggregategroup'];
			$subgroups = $this->messageGroupMetadata->getSubgroups( $aggregateGroup );
			if ( $subgroups === null ) {
				// For a newly created aggregate group, it may contain no subgroups, but null
				// means the group does not exist or something has gone wrong.

				$this->dieWithError( 'apierror-translate-invalidaggregategroup', 'invalidaggregategroup' );
			}

			$subgroupId = $params['group'];
			$group = MessageGroups::getGroup( $subgroupId );

			$addLogEntry = false;
			// Add or remove from the list
			if ( $action === 'associate' ) {
				if ( !$this->aggregateGroupManager->supportsAggregation( $group ) ) {
					$this->dieWithError( 'apierror-translate-invalidgroup', 'invalidgroup' );
				}

				$messageGroupLanguage = $group->getSourceLanguage();
				$aggregateGroupLanguage = $this->messageGroupMetadata->get( $aggregateGroup, 'sourcelanguagecode' );
				// If source language is not set, user shouldn't be prevented from associating a message group
				if ( $aggregateGroupLanguage !== false && $messageGroupLanguage !== $aggregateGroupLanguage ) {
					$this->dieWithError( [
						'apierror-translate-grouplanguagemismatch',
						$messageGroupLanguage,
						$aggregateGroupLanguage
					] );
				}
				$subgroups[] = $subgroupId;
				$uniqueSubgroups = array_unique( $subgroups );
				if ( $uniqueSubgroups === $subgroups ) {
					// A new group will actually be added, add a log entry
					$addLogEntry = true;
				}
				$subgroups = $uniqueSubgroups;
				$output[ 'groupUrl' ] = $this->aggregateGroupManager->getTargetTitleByGroup( $group )->getFullURL();
			} elseif ( $action === 'dissociate' ) {
				// Allow removal of non-existing groups
				$subgroups = array_flip( $subgroups );
				if ( isset( $subgroups[$subgroupId] ) ) {
					unset( $subgroups[$subgroupId] );
					$addLogEntry = true;
				}
				$subgroups = array_flip( $subgroups );
			}

			$this->messageGroupMetadata->setSubgroups( $aggregateGroup, $subgroups );

			$logParams = [
				'aggregategroup' => $this->messageGroupMetadata->get( $aggregateGroup, 'name' ),
				'aggregategroup-id' => $aggregateGroup,
			];

			if ( $addLogEntry ) {
				/* To allow removing no longer existing groups from aggregate message groups,
				 * the message group object $group might not always be available.
				 * In this case we need to fake some title. */
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
			$logger = LoggerFactory::getInstance( 'Translate' );
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

			if ( !isset( $params['groupdescription'] ) ) {
				$this->dieWithError( [ 'apierror-missingparam', 'groupdescription' ] );
			}
			$desc = trim( $params['groupdescription'] );

			$aggregateGroupId = self::generateAggregateGroupId( $name );

			// Throw error if group already exists
			$nameExists = MessageGroups::labelExists( $name );
			if ( $nameExists ) {
				$this->dieWithError( 'apierror-translate-duplicateaggregategroup', 'duplicateaggregategroup' );
			}

			// ID already exists: Generate a new ID by adding a number to it.
			$idExists = MessageGroups::getGroup( $aggregateGroupId );
			if ( $idExists ) {
				$i = 1;
				do {
					$tempId = $aggregateGroupId . '-' . $i;
					$idExists = MessageGroups::getGroup( $tempId );
					$i++;
				} while ( $idExists );
				$aggregateGroupId = $tempId;
			}
			$sourceLanguageCode = trim( $params['groupsourcelanguagecode'] );

			$this->messageGroupMetadata->set( $aggregateGroupId, 'name', $name );
			$this->messageGroupMetadata->set( $aggregateGroupId, 'description', $desc );
			if ( $sourceLanguageCode !== self::NO_LANGUAGE_CODE ) {
				$this->messageGroupMetadata->set( $aggregateGroupId, 'sourcelanguagecode', $sourceLanguageCode );
			}
			$this->messageGroupMetadata->setSubgroups( $aggregateGroupId, [] );

			// Once new aggregate group added, we need to show all the pages that can be added to that.
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
			$desc = trim( $params['groupdescription'] );
			$aggregateGroupId = $params['aggregategroup'];
			$newLanguageCode = trim( $params['groupsourcelanguagecode'] );

			$oldName = $this->messageGroupMetadata->get( $aggregateGroupId, 'name' );
			$oldDesc = $this->messageGroupMetadata->get( $aggregateGroupId, 'description' );
			$currentLanguageCode = $this->messageGroupMetadata->get( $aggregateGroupId, 'sourcelanguagecode' );

			if ( $newLanguageCode !== self::NO_LANGUAGE_CODE && $newLanguageCode !== $currentLanguageCode ) {
				$groupsWithDifferentLanguage =
					$this->getGroupsWithDifferentLanguage( $aggregateGroupId, $newLanguageCode );

				if ( count( $groupsWithDifferentLanguage ) ) {
					$this->dieWithError( [
						'apierror-translate-messagegroup-aggregategrouplanguagemismatch',
						implode( ', ', $groupsWithDifferentLanguage ),
						$newLanguageCode,
						count( $groupsWithDifferentLanguage )
					] );
				}
			}

			// Error if the label exists already
			$exists = MessageGroups::labelExists( $name );
			if ( $exists && $oldName !== $name ) {
				$this->dieWithError( 'apierror-translate-duplicateaggregategroup', 'duplicateaggregategroup' );
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
			if ( $newLanguageCode === self::NO_LANGUAGE_CODE ) {
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

	protected function generateAggregateGroupId( string $aggregateGroupName, string $prefix = 'agg-' ): string {
		// The database field has a maximum limit of 200 bytes
		if ( strlen( $aggregateGroupName ) + strlen( $prefix ) >= 200 ) {
			return $prefix . substr( sha1( $aggregateGroupName ), 0, 5 );
		} else {
			$pattern = '/[\x00-\x1f\x23\x27\x2c\x2e\x3c\x3e\x5b\x5d\x7b\x7c\x7d\x7f\s]+/i';
			return $prefix . preg_replace( $pattern, '_', $aggregateGroupName );
		}
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
				// Not providing list of values, to allow dissociation of unknown groups
				ParamValidator::PARAM_TYPE => 'string',
			],
			'groupname' => [
				ParamValidator::PARAM_TYPE => 'string',
			],
			'groupdescription' => [
				ParamValidator::PARAM_TYPE => 'string',
			],
			'groupsourcelanguagecode' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_DEFAULT => self::NO_LANGUAGE_CODE,
			],
			'token' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
		];
	}

	protected function getExamplesMessages(): array {
		return [
			'action=aggregategroups&do=associate&group=groupId&aggregategroup=aggregateGroupId'
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
