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

	public function getLabel() {
		return wfMessage( 'translate-workflowgroup-label' )->text();
	}

	public function getDescription() {
		return wfMessage( 'translate-workflowgroup-desc' )->plain();
	}

	public function getDefinitions() {
		global $wgTranslateWorkflowStates;

		$defs = array();

		foreach ( array_keys( $wgTranslateWorkflowStates ) as $state ) {
			$titleString = "Translate-workflow-state-$state";
			$definitionText = $state;

			// Automatically create pages for workflow states in the original language
			$title = Title::makeTitle( $this->getNamespace(), $titleString );
			if ( !$title->exists() ) {
				$page = new WikiPage( $title );
				$page->doEdit(
					$state /*content*/,
					wfMessage( 'translate-workflow-autocreated-summary', $state )->inContentLanguage()->text(),
					0, /*flags*/
					false, /* base revision id */
					FuzzyBot::getUser()
				);
			} else {
				$definitionText = Revision::newFromTitle( $title )->getText();
			}
			$defs[$titleString] = $definitionText;
		}

		return $defs;
	}
}
