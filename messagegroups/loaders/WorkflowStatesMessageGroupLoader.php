<?php
/**
 * This file contains a class to load the WorkflowStatesMessageGroup.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

class WorkflowStatesMessageGroupLoader extends MessageGroupLoader {
	/**
	 * Fetches configured WorkflowStatesMessageGroup
	 *
	 * @return WorkflowStatesMessageGroup[]
	 */
	public function getGroups() {
		global $wgTranslateWorkflowStates;
		$groups = [];
		if ( $wgTranslateWorkflowStates ) {
			$groups['translate-workflow-states'] = new WorkflowStatesMessageGroup();
		}

		return $groups;
	}

	/**
	 * Hook: TranslateInitGroupLoaders
	 *
	 * @param array &$groupLoader
	 * @return WorkflowStatesMessageGroupLoader
	 */
	public static function registerLoader( array &$groupLoader ) {
		$groupLoader[] = new self();
	}
}
