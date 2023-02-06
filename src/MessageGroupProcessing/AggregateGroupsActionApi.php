<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use AggregateMessageGroup;
use ApiBase;
use ApiMain;
use JobQueueGroup;
use ManualLogEntry;
use MediaWiki\Logger\LoggerFactory;
use MessageIndexRebuildJob;
use Title;
use TranslateMetadata;
use Wikimedia\ParamValidator\ParamValidator;
use WikiPageMessageGroup;

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
	/** @var JobQueueGroup */
	private $jobQueueGroup;
	/** @var string */
	protected static $right = 'translate-manage';
	private const NO_LANGUAGE_CODE = '-';

	public function __construct(
		ApiMain $main,
		string $action,
		JobQueueGroup $jobQueueGroup
	) {
		parent::__construct( $main, $action );
		$this->jobQueueGroup = $jobQueueGroup;
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
			$subgroups = TranslateMetadata::getSubgroups( $aggregateGroup );
			if ( $subgroups === null ) {
				// For a newly created aggregate group, it may contain no subgroups, but null
				// means the group does not exist or something has gone wrong.

				$this->dieWithError( 'apierror-translate-invalidaggregategroup', 'invalidaggregategroup' );
			}

			$subgroupId = $params['group'];
			$group = MessageGroups::getGroup( $subgroupId );

			// Add or remove from the list
			if ( $action === 'associate' ) {
				if ( !$group instanceof WikiPageMessageGroup ) {
					$this->dieWithError( 'apierror-translate-invalidgroup', 'invalidgroup' );
				}

				$messageGroupLanguage = $group->getSourceLanguage();
				$aggregateGroupLanguage = TranslateMetadata::get( $aggregateGroup, 'sourcelanguagecode' );
				// If source language is not set, user shouldn't be prevented from associating a message group
				if ( $aggregateGroupLanguage !== false && $messageGroupLanguage !== $aggregateGroupLanguage ) {
					$this->dieWithError( [
						'apierror-translate-grouplanguagemismatch',
						$messageGroupLanguage,
						$aggregateGroupLanguage
					] );
				}
				$subgroups[] = $subgroupId;
				$subgroups = array_unique( $subgroups );
			} elseif ( $action === 'dissociate' ) {
				// Allow removal of non-existing groups
				$subgroups = array_flip( $subgroups );
				unset( $subgroups[$subgroupId] );
				$subgroups = array_flip( $subgroups );
			}

			TranslateMetadata::setSubgroups( $aggregateGroup, $subgroups );

			$logParams = [
				'aggregategroup' => TranslateMetadata::get( $aggregateGroup, 'name' ),
				'aggregategroup-id' => $aggregateGroup,
			];

			/* Note that to allow removing no longer existing groups from
			 * aggregate message groups, the message group object $group
			 * might not always be available. In this case we need to fake
			 * some title. */
			$title = $group instanceof WikiPageMessageGroup ?
				$group->getTitle() :
				Title::newFromText( "Special:Translate/$subgroupId" );

			$entry = new ManualLogEntry( 'pagetranslation', $action );
			$entry->setPerformer( $this->getUser() );
			$entry->setTarget( $title );
			// @todo
			// $entry->setComment( $comment );
			$entry->setParameters( $logParams );

			$logid = $entry->insert();
			$entry->publish( $logid );
		} elseif ( $action === 'remove' ) {
			if ( !isset( $params['aggregategroup'] ) ) {
				$this->dieWithError( [ 'apierror-missingparam', 'aggregategroup' ] );
			}

			$aggregateGroupId = $params['aggregategroup'];
			$group = MessageGroups::getGroup( $aggregateGroupId );
			if ( !( $group instanceof AggregateMessageGroup ) ) {
				$this->dieWithError(
					'apierror-translate-invalidaggregategroupname', 'invalidaggregategroupname'
				);
			}

			TranslateMetadata::deleteGroup( $params['aggregategroup'] );
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
					'apierror-translate-invalidaggregategroupname', 'invalidaggregategroupname'
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

			TranslateMetadata::set( $aggregateGroupId, 'name', $name );
			TranslateMetadata::set( $aggregateGroupId, 'description', $desc );
			if ( $sourceLanguageCode !== self::NO_LANGUAGE_CODE ) {
				TranslateMetadata::set( $aggregateGroupId, 'sourcelanguagecode', $sourceLanguageCode );
			}
			TranslateMetadata::setSubgroups( $aggregateGroupId, [] );

			// Once new aggregate group added, we need to show all the pages that can be added to that.
			$output['groups'] = self::getAllPages();
			$output['aggregategroupId'] = $aggregateGroupId;
			// @todo Logging
		} elseif ( $action === 'update' ) {
			if ( !isset( $params['groupname'] ) ) {
				$this->dieWithError( [ 'apierror-missingparam', 'groupname' ] );
			}
			$name = trim( $params['groupname'] );
			if ( strlen( $name ) === 0 ) {
				$this->dieWithError(
					'apierror-translate-invalidaggregategroupname', 'invalidaggregategroupname'
				);
			}
			$desc = trim( $params['groupdescription'] );
			$aggregateGroupId = $params['aggregategroup'];
			$newLanguageCode = trim( $params['groupsourcelanguagecode'] );

			$oldName = TranslateMetadata::get( $aggregateGroupId, 'name' );
			$oldDesc = TranslateMetadata::get( $aggregateGroupId, 'description' );
			$currentLanguageCode = TranslateMetadata::get( $aggregateGroupId, 'sourcelanguagecode' );

			if ( $newLanguageCode !== self::NO_LANGUAGE_CODE && $newLanguageCode !== $currentLanguageCode ) {
				$groupsWithDifferentLanguage =
					$this->getGroupsWithDifferentLanguage( $aggregateGroupId, $newLanguageCode );

				if ( count( $groupsWithDifferentLanguage ) ) {
					$this->dieWithError( [
						'apierror-translate-messagegroup-aggregategrouplanguagemismatch',
						implode( ", ", $groupsWithDifferentLanguage ),
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
			TranslateMetadata::set( $aggregateGroupId, 'name', $name );
			TranslateMetadata::set( $aggregateGroupId, 'description', $desc );
			if ( $newLanguageCode === self::NO_LANGUAGE_CODE ) {
				TranslateMetadata::clearMetadata( $aggregateGroupId, [ 'sourcelanguagecode' ] );
			} else {
				TranslateMetadata::set( $aggregateGroupId, 'sourcelanguagecode', $newLanguageCode );
			}
		}

		// If we got this far, nothing has failed
		$output['result'] = 'ok';
		$this->getResult()->addValue( null, $this->getModuleName(), $output );
		// Cache needs to be cleared after any changes to groups
		MessageGroups::singleton()->recache();
		$this->jobQueueGroup->push( MessageIndexRebuildJob::newJob() );
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
			$subgroups = TranslateMetadata::getSubgroups( $aggregateGroupId );
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

	public static function getAllPages(): array {
		$groups = MessageGroups::getAllGroups();
		$pages = [];
		foreach ( $groups as $group ) {
			if ( $group instanceof WikiPageMessageGroup ) {
				$pages[$group->getId()] = $group->getTitle()->getPrefixedText();
			}
		}

		return $pages;
	}
}
