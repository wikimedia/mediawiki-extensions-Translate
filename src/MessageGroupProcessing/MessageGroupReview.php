<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use InvalidArgumentException;
use ManualLogEntry;
use MediaWiki\Extension\Translate\HookRunner;
use MessageGroup;
use SpecialPage;
use User;
use Wikimedia\Rdbms\ILoadBalancer;
use Wikimedia\Rdbms\IResultWrapper;

/**
 * Provides methods to get and change the state of a message group
 * @author Eugene Wang'ombe
 * @since 2022.07
 * @license GPL-2.0-or-later
 */
class MessageGroupReview {
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
				'tgr_group' => $group->getId(),
				'tgr_lang' => $code
			] )->fetchField();
	}

	/** @return bool true if the message group state changed, otherwise false */
	public function changeState( MessageGroup $group, string $code, string $newState, User $user ): bool {
		$currentState = $this->getState( $group, $code );
		if ( $currentState === $newState ) {
			return false;
		}

		$index = [ 'tgr_group', 'tgr_lang' ];
		$row = [
			'tgr_group' => $group->getId(),
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
		return $this->priorityCache[$group] ?? null;
	}

	/** Store priority for message group. Abusing this table that was intended to store message group states */
	public function setGroupPriority( string $groupId, ?string $priority ): void {
		if ( isset( $this->priorityCache ) ) {
			$this->priorityCache[$groupId] = $priority;
		}

		$dbw = $this->loadBalancer->getConnection( DB_PRIMARY );
		$row = [
			'tgr_group' => $groupId,
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
		$result = $this->getWorkflowStates( $groupId, $languageCode );
		return $result->fetchRow()['tgr_state'] ?? null;
	}

	public function getWorkflowStatesForLanguage( string $languageCode ): array {
		$result = $this->getWorkflowStates( null, $languageCode );
		return $this->result2map( $result, 'tgr_group', 'tgr_state' );
	}

	public function getWorkflowStatesForGroup( string $groupId ): array {
		$result = $this->getWorkflowStates( $groupId, null );
		return $this->result2map( $result, 'tgr_lang', 'tgr_state' );
	}

	private function getWorkflowStates( ?string $groupId, ?string $languageCode ): IResultWrapper {
		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		$conditions = array_filter(
			[ 'tgr_group' => $groupId, 'tgr_lang' => $languageCode ],
			static fn( $x ) => $x !== null && $x !== ''
		);

		if ( $conditions === [] ) {
			throw new InvalidArgumentException( 'Either the $groupId or the $languageCode should be provided' );
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
}
