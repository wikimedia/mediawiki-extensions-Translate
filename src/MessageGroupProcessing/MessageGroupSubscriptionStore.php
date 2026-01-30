<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MessageGroup;
use Wikimedia\Rdbms\IConnectionProvider;
use Wikimedia\Rdbms\IResultWrapper;

/**
 * Store service for looking up and storing user subscriptions to message group
 * @since 2024.04
 * @license GPL-2.0-or-later
 * @author Abijeet Patro
 */
class MessageGroupSubscriptionStore {
	private const VIRTUAL_DOMAIN = 'virtual-translate';
	private const TABLE_NAME = 'translate_message_group_subscriptions';
	/** @var int Match field tmgs_group byte length */
	private const MAX_GROUP_LENGTH = 200;

	public function __construct(
		private readonly IConnectionProvider $dbProvider,
	) {
	}

	public function addSubscription( string $groupId, int $userId ): void {
		$this->dbProvider->getPrimaryDatabase( self::VIRTUAL_DOMAIN )
			->newReplaceQueryBuilder()
			->replaceInto( self::TABLE_NAME )
			->uniqueIndexFields( [ 'tmgs_group', 'tmgs_user_id' ] )
			->row( [
				'tmgs_group' => self::getGroupIdForDatabase( $groupId ),
				'tmgs_user_id' => $userId,
			] )
			->caller( __METHOD__ )
			->execute();
	}

	public function getSubscriptions( ?array $groupIds, ?int $userId ): IResultWrapper {
		$queryBuilder = $this->dbProvider
			->getReplicaDatabase( self::VIRTUAL_DOMAIN )
			->newSelectQueryBuilder()
			->select( [ 'tmgs_group', 'tmgs_user_id' ] )
			->from( self::TABLE_NAME )
			->caller( __METHOD__ );

		if ( $groupIds !== null ) {
			$dbGroupIds = [];
			foreach ( $groupIds as $groupId ) {
				$dbGroupIds[] = self::getGroupIdForDatabase( $groupId );
			}
			$queryBuilder->where( [ 'tmgs_group' => $dbGroupIds ] );
		}

		if ( $userId !== null ) {
			$queryBuilder->andWhere( [ 'tmgs_user_id' => $userId ] );
		}

		return $queryBuilder->fetchResultSet();
	}

	public function getSubscriptionByGroupUnion( array $groupIds ): array {
		$queryBuilder = $this->dbProvider
			->getReplicaDatabase( self::VIRTUAL_DOMAIN )
			->newSelectQueryBuilder()
			->select( [ 'tmgs_user_id' ] )
			->from( self::TABLE_NAME )
			->where( [ 'tmgs_group' => $groupIds ] )
			->having( 'COUNT(tmgs_group) = ' . count( $groupIds ) )
			->caller( __METHOD__ );

		return $queryBuilder->fetchFieldValues();
	}

	public function removeSubscriptions( string $groupId, int $userId ): void {
		$this->dbProvider->getPrimaryDatabase( self::VIRTUAL_DOMAIN )
			->newDeleteQueryBuilder()
			->deleteFrom( self::TABLE_NAME )
			->where( [
				'tmgs_group' => $groupId,
				'tmgs_user_id' => $userId
			] )
			->caller( __METHOD__ )
			->execute();
	}

	public static function getGroupIdForDatabase( MessageGroup|string $groupId ): string {
		if ( $groupId instanceof MessageGroup ) {
			$groupId = $groupId->getId();
		}

		return strlen( $groupId ) > self::MAX_GROUP_LENGTH ?
			// Note: mb_strcut operates on bytes but leaves multi-byte characters intact
			mb_strcut( $groupId, 0, 160 ) . '||' . md5( $groupId ) :
			$groupId;
	}
}
