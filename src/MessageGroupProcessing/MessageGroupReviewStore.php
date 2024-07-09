<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use InvalidArgumentException;
use ManualLogEntry;
use MediaWiki\Extension\Translate\HookRunner;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\User\User;
use MessageGroup;
use Wikimedia\Rdbms\ILoadBalancer;
use Wikimedia\Rdbms\IResultWrapper;

/**
 * Provides methods to get and change the state of a message group
 * @author Eugene Wang'ombe
 * @license GPL-2.0-or-later
 */
class MessageGroupReviewStore {
	private HookRunner $hookRunner;
	private ILoadBalancer $loadBalancer;
	private const TABLE_NAME = 'translate_groupreviews';
	/** Cache for message group priorities: (database group id => value) */
	private ?array $priorityCache = null;

	public function __construct( ILoadBalancer $loadBalancer, HookRunner $hookRunner ) {
		$this->loadBalancer = $loadBalancer;
		$this->hookRunner = $hookRunner;
	}

	/** @return mixed|false — The value from the field, or false if nothing was found */
	public function getState( MessageGroup $group, string $code ) {
		$dbw = $this->loadBalancer->getConnection( DB_PRIMARY );
		return $dbw->newSelectQueryBuilder()
			->select( 'tgr_state' )
			->from( self::TABLE_NAME )
			->where( [
				'tgr_group' => self::getGroupIdForDatabase( $group->getId() ),
				'tgr_lang' => $code
			] )
			->caller( __METHOD__ )
			->fetchField();
	}

	/** @return bool true if the message group state changed, otherwise false */
	public function changeState( MessageGroup $group, string $code, string $newState, User $user ): bool {
		$currentState = $this->getState( $group, $code );
		if ( $currentState === $newState ) {
			return false;
		}

		$index = [ 'tgr_group', 'tgr_lang' ];
		$row = [
			'tgr_group' => self::getGroupIdForDatabase( $group->getId() ),
			'tgr_lang' => $code,
			'tgr_state' => $newState,
		];
		$dbw = $this->loadBalancer->getConnection( DB_PRIMARY );
		$dbw->replace( self::TABLE_NAME, [ $index ], $row, __METHOD__ );

		$entry = new ManualLogEntry( 'translationreview', 'group' );
		$entry->setPerformer( $user );
		$entry->setTarget( SpecialPage::getTitleFor( 'Translate', $group->getId() ) );
		$entry->setParameters( [
			'4::language' => $code,
			'5::group-label' => $group->getLabel(),
			'6::old-state' => $currentState,
			'7::new-state' => $newState,
		] );
		// @todo
		// $entry->setComment( $comment );

		$logId = $entry->insert();
		$entry->publish( $logId );

		$this->hookRunner->onTranslateEventMessageGroupStateChange( $group, $code, $currentState, $newState );

		return true;
	}

	public function getGroupPriority( string $group ): ?string {
		$this->preloadGroupPriorities( __METHOD__ );
		return $this->priorityCache[self::getGroupIdForDatabase( $group )] ?? null;
	}

	/** Store priority for message group. Abusing this table that was intended to store message group states */
	public function setGroupPriority( string $groupId, ?string $priority ): void {
		$dbGroupId = self::getGroupIdForDatabase( $groupId );
		if ( isset( $this->priorityCache ) ) {
			$this->priorityCache[$dbGroupId] = $priority;
		}

		$dbw = $this->loadBalancer->getConnection( DB_PRIMARY );
		$row = [
			'tgr_group' => $dbGroupId,
			'tgr_lang' => '*priority',
			'tgr_state' => $priority
		];

		if ( $priority === null ) {
			unset( $row['tgr_state'] );
			$dbw->delete( self::TABLE_NAME, $row, __METHOD__ );
		} else {
			$index = [ 'tgr_group', 'tgr_lang' ];
			$dbw->replace( self::TABLE_NAME, [ $index ], $row, __METHOD__ );
		}
	}

	private function preloadGroupPriorities( string $caller ): void {
		if ( isset( $this->priorityCache ) ) {
			return;
		}

		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		$res = $dbr->newSelectQueryBuilder()
			->select( [ 'tgr_group', 'tgr_state' ] )
			->from( self::TABLE_NAME )
			->where( [ 'tgr_lang' => '*priority' ] )
			->caller( $caller )
			->fetchResultSet();

		$this->priorityCache = $this->result2map( $res, 'tgr_group', 'tgr_state' );
	}

	/**
	 * Get the current workflow state for the given message group for the given language
	 * @param string $groupId
	 * @param string $languageCode
	 * @return string|null State id or null.
	 */
	public function getWorkflowState( string $groupId, string $languageCode ): ?string {
		$result = $this->getWorkflowStates( [ $groupId ], [ $languageCode ] );
		return $result->fetchRow()['tgr_state'] ?? null;
	}

	public function getWorkflowStatesForLanguage( string $languageCode, array $groupIds ): array {
		$result = $this->getWorkflowStates( $groupIds, [ $languageCode ] );
		$states = $this->result2map( $result, 'tgr_group', 'tgr_state' );

		$finalResult = [];
		foreach ( $groupIds as $groupId ) {
			$dbGroupId = self::getGroupIdForDatabase( $groupId );
			if ( isset( $states[ $dbGroupId ] ) ) {
				$finalResult[ $groupId ] = $states[ $dbGroupId ];
			}
		}

		return $finalResult;
	}

	public function getWorkflowStatesForGroup( string $groupId ): array {
		$result = $this->getWorkflowStates( [ $groupId ], null );
		return $this->result2map( $result, 'tgr_lang', 'tgr_state' );
	}

	private function getWorkflowStates( ?array $groupIds, ?array $languageCodes ): IResultWrapper {
		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		$conditions = array_filter(
			[ 'tgr_group' => $groupIds, 'tgr_lang' => $languageCodes ],
			static fn ( $x ) => $x !== null && $x !== ''
		);

		if ( $conditions === [] ) {
			throw new InvalidArgumentException( 'Either the $groupId or the $languageCode should be provided' );
		}

		if ( isset( $conditions['tgr_group'] ) ) {
			$conditions['tgr_group'] = array_map( [ self::class, 'getGroupIdForDatabase' ], $groupIds );
		}

		return $dbr->newSelectQueryBuilder()
			->select( [ 'tgr_state', 'tgr_group', 'tgr_lang' ] )
			->from( self::TABLE_NAME )
			->where( $conditions )
			->caller( __METHOD__ )
			->fetchResultSet();
	}

	private function result2map( IResultWrapper $result, string $keyValue, string $valueValue ): array {
		$map = [];
		foreach ( $result as $row ) {
			$map[$row->$keyValue] = $row->$valueValue;
		}

		return $map;
	}

	private static function getGroupIdForDatabase( $groupId ): string {
		$groupId = strval( $groupId );

		// Check if length is more than 200 bytes
		if ( strlen( $groupId ) <= 200 ) {
			return $groupId;
		}

		// We take 160 bytes of the original string and append the md5 hash (32 bytes)
		return mb_strcut( $groupId, 0, 160 ) . '||' . hash( 'md5', $groupId );
	}
}
