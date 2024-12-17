<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use AggregateMessageGroup;
use MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundleMessageGroup;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MessageGroup;
use RuntimeException;
use WikiPageMessageGroup;

/**
 * Contains logic to manage aggregate groups and their subgroups
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2024.06
 */
class AggregateGroupManager {
	private TitleFactory $titleFactory;
	private MessageGroupMetadata $messageGroupMetadata;

	private const MESSAGE_GROUP_PREFIX = 'agg-';

	public function __construct(
		TitleFactory $titleFactory,
		MessageGroupMetadata $messageGroupMetadata
	) {
		$this->titleFactory = $titleFactory;
		$this->messageGroupMetadata = $messageGroupMetadata;
	}

	public function supportsAggregation( MessageGroup $group ): bool {
		return $group instanceof WikiPageMessageGroup || $group instanceof MessageBundleMessageGroup;
	}

	public function getTargetTitleByGroupId( string $groupId ): Title {
		$group = MessageGroups::getGroup( $groupId );
		if ( $group ) {
			return $this->getTargetTitleByGroup( $group );
		} else {
			/* To allow removing no longer existing groups from aggregate message groups,
			 * the message group object $group might not always be available.
			 * In this case we need to fake some title. */
			return $this->titleFactory->newFromText( "Special:Translate/$groupId" );
		}
	}

	/** @throws RuntimeException If MessageGroup::getRelatedPage returns null */
	public function getTargetTitleByGroup( MessageGroup $group ): Title {
		$relatedGroupPage = $group->getRelatedPage();
		if ( !$relatedGroupPage ) {
			throw new RuntimeException( "No related page found for group " . $group->getId() );
		}

		return $this->titleFactory->newFromLinkTarget( $relatedGroupPage );
	}

	public function add( string $name, string $description, ?string $languageCode ): string {
		// Throw error if the group already exists
		if ( MessageGroups::labelExists( $name ) ) {
			throw new DuplicateAggregateGroupException( $name );
		}

		$aggregateGroupId = $this->generateAggregateGroupId( $name );

		// FIXME: Each call to set runs a DB query. Make this more efficient.
		$this->messageGroupMetadata->set( $aggregateGroupId, 'name', $name );
		$this->messageGroupMetadata->set( $aggregateGroupId, 'description', $description );
		if ( $languageCode ) {
			// TODO: Add language code validation
			$this->messageGroupMetadata->set( $aggregateGroupId, 'sourcelanguagecode', $languageCode );
		}
		$this->messageGroupMetadata->setSubgroups( $aggregateGroupId, [] );

		return $aggregateGroupId;
	}

	/**
	 * @param string $aggregateGroupId
	 * @param string[] $newSubgroupIds
	 * @return string[] List of subgroup ids that were associated
	 */
	public function associate( string $aggregateGroupId, array $newSubgroupIds ): array {
		$existingSubgroupIds = $this->getSubgroups( $aggregateGroupId );
		$newSubgroups = MessageGroups::getGroupsById( $newSubgroupIds );
		// Identify groups that do not exist
		$missingGroupIds = $this->findMissingGroupIds( $newSubgroups, $newSubgroupIds );
		$invalidGroupIds = [];
		foreach ( $newSubgroups as $subGroup ) {
			if ( !$this->supportsAggregation( $subGroup ) ) {
				$invalidGroupIds[] = $subGroup->getId();
			}
		}

		if ( $missingGroupIds || $invalidGroupIds ) {
			$invalidGroupIds = array_unique( array_merge( $missingGroupIds, $invalidGroupIds ) );
			throw new AggregateGroupAssociationFailure( $invalidGroupIds );
		}

		$aggregateGroupLanguage = $this->messageGroupMetadata->get( $aggregateGroupId, 'sourcelanguagecode' );
		if ( $aggregateGroupLanguage !== false ) {
			// Ensure that the new groups have the same language as the aggregate group.
			$sourceLanguageMismatchGroupIds = [];
			foreach ( $newSubgroups as $subGroup ) {
				if ( $subGroup->getSourceLanguage() !== $aggregateGroupLanguage ) {
					$sourceLanguageMismatchGroupIds[] = $subGroup->getId();
				}
			}

			if ( $sourceLanguageMismatchGroupIds ) {
				throw new AggregateGroupLanguageMismatchException(
					$sourceLanguageMismatchGroupIds,
					$aggregateGroupLanguage
				);
			}
		}

		$allSubgroupIds = array_unique( array_merge( $existingSubgroupIds, $newSubgroupIds ) );
		if ( array_diff( $newSubgroupIds, $existingSubgroupIds ) === [] ) {
			// No new subgroups added
			return [];
		}

		$this->messageGroupMetadata->setSubgroups( $aggregateGroupId, $allSubgroupIds );
		return $newSubgroupIds;
	}

	/**
	 * @param string $aggregateGroupId
	 * @param string[] $subgroupIds
	 * @return string[] List of subgroup ids that were dis-associated
	 */
	public function disassociate( string $aggregateGroupId, array $subgroupIds ): array {
		$existingSubGroupIds = $this->getSubgroups( $aggregateGroupId );
		$remainingSubGroupIds = array_diff( $existingSubGroupIds, $subgroupIds );
		$this->messageGroupMetadata->setSubgroups( $aggregateGroupId, $remainingSubGroupIds );
		return array_diff( $existingSubGroupIds, $remainingSubGroupIds );
	}

	/** Checks if there are any pages that support aggregation */
	public function hasGroupsSupportingAggregation(): bool {
		$groups = MessageGroups::getAllGroups();
		foreach ( $groups as $group ) {
			if ( $this->supportsAggregation( $group ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Return a list of all Aggregate groups
	 * @return AggregateMessageGroup[]
	 */
	public function getAll(): array {
		$groupsPreload = MessageGroups::getGroupsByType( AggregateMessageGroup::class );
		$this->messageGroupMetadata->preloadGroups( array_keys( $groupsPreload ), __METHOD__ );

		$groups = MessageGroups::getAllGroups();
		uasort( $groups, [ MessageGroups::class, 'groupLabelSort' ] );
		$aggregates = [];
		foreach ( $groups as $group ) {
			if ( $group instanceof AggregateMessageGroup ) {
				// Filter out AggregateGroups configured in YAML
				$subgroups = $this->messageGroupMetadata->getSubgroups( $group->getId() );
				if ( $subgroups !== null ) {
					$aggregates[] = $group;
				}
			}
		}

		return $aggregates;
	}

	/** @return string[] */
	private function findMissingGroupIds( array $subGroups, array $subGroupIds ): array {
		$existingIds = array_map( static fn ( $group ) => $group->getId(), $subGroups );
		return array_diff( $subGroupIds, $existingIds );
	}

	private function getSubgroups( string $aggregateGroupId ): array {
		$existingSubGroupIds = $this->messageGroupMetadata->getSubgroups( $aggregateGroupId );
		if ( $existingSubGroupIds !== null ) {
			// For a newly created aggregate group, it may contain no subgroups, but null
			// means the group does not exist or something has gone wrong.
			return $existingSubGroupIds;
		}

		throw new AggregateGroupNotFoundException( $aggregateGroupId );
	}

	private function generateAggregateGroupId( string $name ): string {
		// The database field has a maximum limit of 200 bytes
		if ( strlen( $name ) + strlen( self::MESSAGE_GROUP_PREFIX ) >= 200 ) {
			$aggregateGroupId = self::MESSAGE_GROUP_PREFIX . substr( sha1( $name ), 0, 5 );
		} else {
			$pattern = '/[\x00-\x1f\x23\x27\x2c\x2e\x3c\x3e\x5b\x5d\x7b\x7c\x7d\x7f\s]+/i';
			$aggregateGroupId = self::MESSAGE_GROUP_PREFIX . preg_replace( $pattern, '_', $name );
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

		return $aggregateGroupId;
	}
}
