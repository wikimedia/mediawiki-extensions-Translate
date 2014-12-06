<?php
/**
 * This file contains an unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * @since 2012-11-01
 * @ingroup MessageGroup
 */
class RecentAdditionsMessageGroup extends RecentMessageGroup {
	protected $groupInfoCache = array();

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
		$db = wfGetDB( DB_SLAVE );
		$conds = array(
			'rc_title ' . $db->buildLike( $db->anyString(), '/en' ),
			'rc_namespace' => $wgTranslateMessageNamespaces,
			'rc_type != ' . RC_LOG,
			'rc_id > ' . $this->getRCCutoff(),
			'rc_user' => FuzzyBot::getUser()->getId(),
		);

		return $conds;
	}

	/**
	 * Filters out messages that should not be displayed here
	 * as they are not displayed in other places.
	 *
	 * @param MessageHandle $handle
	 * @return boolean
	 */
	protected function matchingMessage( MessageHandle $handle ) {
		return MessageGroups::isTranslatableMessage( $handle );
	}
}
