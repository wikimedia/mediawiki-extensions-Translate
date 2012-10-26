<?php
/**
 * This file contains a unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2012, Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * @ingroup MessageGroup
 */
class WorkflowStatesMessageGroup extends WikiMessageGroup {
	// id and source are not needed
	public function __construct() {}

	public function getId() {
		return 'translate-workflow-states';
	}

	public function getLabel( IContextSource $context = null ) {
		$msg = wfMessage( 'translate-workflowgroup-label' );
		$msg = self::addContext( $msg, $context );
		return $msg->plain();
	}

	public function getDescription( IContextSource $context = null ) {
		$msg = wfMessage( 'translate-workflowgroup-desc' );
		$msg = self::addContext( $msg, $context );
		return $msg->plain();
	}

	public function getDefinitions() {
		$groups = MessageGroups::getAllGroups();
		$keys = array();

		foreach ( $groups as $g ) {
			$states = $g->getMessageGroupStates()->getStates();
			foreach ( array_keys( $states ) as $state ) {
				$keys["Translate-workflow-state-$state"] = $state;
			}
		}

		$defs = TranslateUtils::getContents( array_keys( $keys ), $this->getNamespace() );
		foreach ( $keys as $key => $state ) {
			if ( !isset( $defs[$key] ) ) {
				// TODO: use jobqueue
				$title = Title::makeTitleSafe( $this->getNamespace(), $key );
				$page = new WikiPage( $title );
				$page->doEdit(
					$state /*content*/,
					wfMessage( 'translate-workflow-autocreated-summary', $state )->inContentLanguage()->text(),
					0, /*flags*/
					false, /* base revision id */
					FuzzyBot::getUser()
				);
			}
		}

		return $defs;
	}
}
