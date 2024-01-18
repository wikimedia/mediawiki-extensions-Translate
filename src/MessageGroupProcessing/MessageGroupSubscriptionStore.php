<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use Wikimedia\Rdbms\IConnectionProvider;
use Wikimedia\Rdbms\IResultWrapper;

/**
 * Store service for looking up and storing user subscriptions to message group
 * @since 2024.04
 * @license GPL-2.0-or-later
 * @author Abijeet Patro
 */
class MessageGroupSubscriptionStore {
	private const TABLE_NAME = 'translate_message_group_subscriptions';
	/** @var int Match field tmgs_group byte length */
	private const MAX_GROUP_LENGTH = 200;
	private IConnectionProvider $connectionProvider;

	public function __construct( IConnectionProvider $connectionProvider ) {
		$this->connectionProvider = $connectionProvider;
	}

	public function addSubscription( string $groupId, int $userId ): void {
		$this->connectionProvider->getPrimaryDatabase()->replace(
			self::TABLE_NAME,
			[ [ 'tmgs_group', 'tmgs_user_id' ] ],
			[
				'tmgs_group' => self::getGroupIdForDatabase( $groupId ),
				'tmgs_user_id' => $userId,
			],
			__METHOD__
		);
	}

	public function getSubscriptions( ?string $groupId, ?int $userId ): IResultWrapper {
		$queryBuilder = $this->connectionProvider->getReplicaDatabase()
			->newSelectQueryBuilder()
			->select( [ 'tmgs_group', 'tmgs_user_id' ] )
			->from( self::TABLE_NAME )
			->caller( __METHOD__ );

		if ( $groupId !== null ) {
			$queryBuilder->where( [ 'tmgs_group' => self::getGroupIdForDatabase( $groupId ) ] );
		}

		if ( $userId !== null ) {
			$queryBuilder->andWhere( [ 'tmgs_user_id' => $userId ] );
		}

		return $queryBuilder->fetchResultSet();
	}

	public function removeSubscriptions( string $groupId, int $userId ): void {
		$conditions = [
			'tmgs_group' => $groupId,
			'tmgs_user_id' => $userId
		];

		$this->connectionProvider->getPrimaryDatabase()
			->delete(
				self::TABLE_NAME,
				$conditions,
				__METHOD__
			);
	}

	private static function getGroupIdForDatabase( string $groupId ): string {
		// Check if length is more than 200 bytes
		if ( strlen( $groupId ) <= self::MAX_GROUP_LENGTH ) {
			return $groupId;
		}

		$hash = hash( 'md5', $groupId );
		// We take 160 bytes of the original string and append the md5 hash (32 bytes)
		return mb_strcut( $groupId, 0, 160 ) . '||' . $hash;
	}
}
