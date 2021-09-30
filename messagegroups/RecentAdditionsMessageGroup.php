<?php
/**
 * This file contains an unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;

/**
 * @since 2012-11-01
 * @ingroup MessageGroup
 */
class RecentAdditionsMessageGroup extends RecentMessageGroup {
	public function getId() {
		return '!additions';
	}

	public function getLabel( IContextSource $context = null ) {
		$msg = wfMessage( 'translate-dynagroup-additions-label' );
		$msg = self::addContext( $msg, $context );

		return $msg->plain();
	}

	public function getDescription( IContextSource $context = null ) {
		$msg = wfMessage( 'translate-dynagroup-additions-desc' );
		$msg = self::addContext( $msg, $context );

		return $msg->plain();
	}

	protected function getQueryConditions() {
		global $wgTranslateMessageNamespaces;
		$db = wfGetDB( DB_REPLICA );
		$conds = [
			'rc_title ' . $db->buildLike( $db->anyString(), '/en' ),
			'rc_namespace' => $wgTranslateMessageNamespaces,
			'rc_type != ' . RC_LOG,
			'rc_id > ' . $this->getRCCutoff(),
			'rc_actor' => FuzzyBot::getUser()->getActorId()
		];

		return $conds;
	}
}
