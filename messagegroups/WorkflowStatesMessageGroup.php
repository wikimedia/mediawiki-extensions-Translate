<?php
/**
 * This file contains an unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0+
 */

/**
 * @ingroup MessageGroup
 */
class WorkflowStatesMessageGroup extends WikiMessageGroup {
	// id and source are not needed
	public function __construct() {
	}

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

		/**
		 * @var $g MessageGroup
		 */
		foreach ( $groups as $g ) {
			$states = $g->getMessageGroupStates()->getStates();
			foreach ( array_keys( $states ) as $state ) {
				$keys["Translate-workflow-state-$state"] = $state;
			}
		}

		$defs = TranslateUtils::getContents( array_keys( $keys ), $this->getNamespace() );
		foreach ( $keys as $key => $state ) {
			if ( !isset( $defs[$key] ) ) {
				// @todo Use jobqueue
				$title = Title::makeTitleSafe( $this->getNamespace(), $key );
				$page = new WikiPage( $title );
				$content = ContentHandler::makeContent( $state, $title );

				$page->doEditContent(
					$content,
					wfMessage( 'translate-workflow-autocreated-summary', $state )->inContentLanguage()->text(),
					0, /*flags*/
					false, /* base revision id */
					FuzzyBot::getUser()
				);
			} else {
				// Use the wiki translation as definition if available.
				// getContents returns array( content, last author )
				list( $content, ) = $defs[$key];
				$keys[$key] = $content;
			}
		}

		return $keys;
	}
}
