<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use AggregateMessageGroup;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;
use MessageGroupBase;
use Wikimedia\Rdbms\IReadableDatabase;

/**
 * @since 2024.05
 * @license GPL-2.0-or-later
 * @author Niklas Laxström
 */
final class AggregateGroupMessageGroupFactory implements CachedMessageGroupFactory {
	private MessageGroupMetadata $messageGroupMetadata;

	public function __construct( MessageGroupMetadata $messageGroupMetadata ) {
		$this->messageGroupMetadata = $messageGroupMetadata;
	}

	public function getCacheKey(): string {
		return 'aggregate-groups';
	}

	public function getCacheVersion(): int {
		return 1;
	}

	public function getDependencies(): array {
		return [];
	}

	public function getData( IReadableDatabase $db ): array {
		// TODO: Ideally messageGroupMetadata would use the provided connection
		$groupIds = $this->messageGroupMetadata->getGroupsWithSubgroups();
		$this->messageGroupMetadata->preloadGroups( $groupIds, __METHOD__ );

		$groups = [];
		foreach ( $groupIds as $id ) {
			$conf = [];
			$conf['BASIC'] = [
				'id' => $id,
				'label' => $this->messageGroupMetadata->get( $id, 'name' ),
				'description' => $this->messageGroupMetadata->get( $id, 'description' ),
			];
			$sourceLanguage = $this->messageGroupMetadata->get( $id, 'sourcelanguagecode' );
			if ( $sourceLanguage ) {
				$conf['BASIC']['sourcelanguage'] = $sourceLanguage;
			}
			$conf['GROUPS'] = $this->messageGroupMetadata->getSubgroups( $id );
			$groups[$id] = $conf;
		}

		return $groups;
	}

	/** @inheritDoc */
	public function createGroups( $data ): array {
		// Parts that do not vary per group and do not need to be stored in the cache
		$template = [
			'BASIC' => [
				'meta' => 1,
				'class' => AggregateMessageGroup::class,
				'namespace' => NS_TRANSLATIONS,
			]
		];

		$groups = [];
		foreach ( $data as $groupId => $groupData ) {
			$groups[$groupId] = MessageGroupBase::factory(
				array_merge_recursive( $template, $groupData )
			);
		}

		return $groups;
	}
}
