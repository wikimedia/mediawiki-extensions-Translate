<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use InvalidArgumentException;
use ManualLogEntry;
use MediaWiki\Extension\Translate\HookRunner;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\User\User;
use MessageGroup;
use Wikimedia\Rdbms\IConnectionProvider;
use Wikimedia\Rdbms\IResultWrapper;

/**
 * Provides methods to get and change the state of a message group
 * @author Eugene Wang'ombe
 * @license GPL-2.0-or-later
 */
class MessageGroupReviewStore {

	private const TABLE_NAME = 'translate_groupreviews';
	/** Cache for message group priorities: (database group id => value) */
	private ?array $priorityCache = null;

	public function __construct(
		private readonly IConnectionProvider $dbProvider,
		private readonly HookRunner $hookRunner,
	) {
	}

	/** @return mixed|false — The value from the field, or false if nothing was found */
	public function getState( MessageGroup $group, string $code ) {
		$dbw = $this->dbProvider->getPrimaryDatabase();
		return $dbw->newSelectQueryBuilder()
			->select( 'tgr_state' )
			->from( self::TABLE_NAME )
			->where( [
				'tgr_group' => MessageGroupSubscriptionStore::getGroupIdForDatabase( $group ),
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

		$row = [
			'tgr_group' => MessageGroupSubscriptionStore::getGroupIdForDatabase( $group ),
			'tgr_lang' => $code,
			'tgr_state' => $newState,
		];
		$dbw = $this->dbProvider->getPrimaryDatabase();
		$dbw->newReplaceQueryBuilder()
			->replaceInto( self::TABLE_NAME )
			->uniqueIndexFields( [ 'tgr_group', 'tgr_lang' ] )
			->row( $row )
			->caller( __METHOD__ )
			->execute();

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
		$dbGroupId = MessageGroupSubscriptionStore::getGroupIdForDatabase( $group );
		return $this->priorityCache[$dbGroupId] ?? null;
	}

	/** Store priority for message group. Abusing this table that was intended to store message group states */
	public function setGroupPriority( string $groupId, ?string $priority ): void {
		$dbGroupId = MessageGroupSubscriptionStore::getGroupIdForDatabase( $groupId );
		if ( $this->priorityCache !== null ) {
			$this->priorityCache[$dbGroupId] = $priority;
		}

		$dbw = $this->dbProvider->getPrimaryDatabase();
		$row = [
			'tgr_group' => $dbGroupId,
			'tgr_lang' => '*priority',
			'tgr_state' => $priority
		];

		if ( $priority === null ) {
			unset( $row['tgr_state'] );
			$dbw->newDeleteQueryBuilder()
				->deleteFrom( self::TABLE_NAME )
				->where( $row )
				->caller( __METHOD__ )
				->execute();
		} else {
			$dbw->newReplaceQueryBuilder()
				->replaceInto( self::TABLE_NAME )
				->uniqueIndexFields( [ 'tgr_group', 'tgr_lang' ] )
				->row( $row )
				->caller( __METHOD__ )
				->execute();
		}
	}

	private function preloadGroupPriorities( string $caller ): void {
		if ( $this->priorityCache !== null ) {
			return;
		}

		$dbr = $this->dbProvider->getReplicaDatabase();
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
	 * @param MessageGroup $group
	 * @param string $languageCode
	 * @return string|null State id or null.
	 */
	public function getWorkflowState( MessageGroup $group, string $languageCode ): ?string {
		$result = $this->getWorkflowStates( [ $group ], [ $languageCode ] );
		return $result->fetchRow()['tgr_state'] ?? null;
	}

	/**
	 * @param string $languageCode
	 * @param string[] $groupIds
	 */
	public function getWorkflowStatesForLanguage( string $languageCode, array $groupIds ): array {
		$result = $this->getWorkflowStates( $groupIds, [ $languageCode ] );
		$states = $this->result2map( $result, 'tgr_group', 'tgr_state' );

		$finalResult = [];
		foreach ( $groupIds as $groupId ) {
			$dbGroupId = MessageGroupSubscriptionStore::getGroupIdForDatabase( $groupId );
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

	/**
	 * @param array<MessageGroup|string>|null $groups
	 * @param string[]|null $languageCodes
	 */
	private function getWorkflowStates( ?array $groups, ?array $languageCodes ): IResultWrapper {
		if ( $groups === null && $languageCodes === null ) {
			throw new InvalidArgumentException( 'Either the $groupId or the $languageCode should be provided' );
		}

		$conditions = [];
		if ( $groups ) {
			$conditions['tgr_group'] = array_map(
				MessageGroupSubscriptionStore::getGroupIdForDatabase( ... ),
				$groups
			);
		}
		if ( $languageCodes ) {
			$conditions['tgr_lang'] = $languageCodes;
		}

		return $this->dbProvider->getReplicaDatabase()->newSelectQueryBuilder()
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

}
