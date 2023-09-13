<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use ManualLogEntry;
use MediaWiki\Extension\Translate\HookRunner;
use MessageGroup;
use SpecialPage;
use User;
use Wikimedia\Rdbms\ILoadBalancer;

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
}
