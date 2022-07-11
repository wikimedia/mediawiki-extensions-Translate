<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use ManualLogEntry;
use MediaWiki\HookContainer\HookContainer;
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

	/** @var HookContainer */
	protected $hookContainer;
	/** @var ILoadBalancer */
	protected $loadBalancer;

	public function __construct( ILoadBalancer $loadBalancer, HookContainer $hookContainer ) {
		$this->loadBalancer = $loadBalancer;
		$this->hookContainer = $hookContainer;
	}

	/** @return mixed|false — The value from the field, or false if nothing was found */
	public function getState( MessageGroup $group, string $code ) {
		$dbw = $this->loadBalancer->getMaintenanceConnectionRef( DB_PRIMARY );
		$table = 'translate_groupreviews';

		$field = 'tgr_state';
		$conds = [
			'tgr_group' => $group->getId(),
			'tgr_lang' => $code
		];

		return $dbw->selectField( $table, $field, $conds, __METHOD__ );
	}

	public function changeState( MessageGroup $group, string $code, string $newState, User $user ): bool {
		$currentState = self::getState( $group, $code );
		if ( $currentState === $newState ) {
			return false;
		}

		$table = 'translate_groupreviews';
		$index = [ 'tgr_group', 'tgr_lang' ];
		$row = [
			'tgr_group' => $group->getId(),
			'tgr_lang' => $code,
			'tgr_state' => $newState,
		];
		$dbw = $this->loadBalancer->getMaintenanceConnectionRef( DB_PRIMARY );
		$dbw->replace( $table, [ $index ], $row, __METHOD__ );

		$entry = new ManualLogEntry( 'translationreview', 'group' );
		$entry->setPerformer( $user );
		$entry->setTarget( SpecialPage::getTitleFor( 'Translate', $group->getId() ) );
		// @todo
		// $entry->setComment( $comment );
		$entry->setParameters( [
			'4::language' => $code,
			'5::group-label' => $group->getLabel(),
			'6::old-state' => $currentState,
			'7::new-state' => $newState,
		] );

		$logid = $entry->insert();
		$entry->publish( $logid );

		$this->hookContainer->run( 'TranslateEventMessageGroupStateChange',
			[ $group, $code, $currentState, $newState ] );

		return true;
	}
}
