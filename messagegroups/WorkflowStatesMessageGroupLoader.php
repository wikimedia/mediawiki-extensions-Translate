<?php
class WorkflowStatesMessageGroupLoader implements MessageGroupLoader {
	public function getGroups() {
		global $wgTranslateWorkflowStates;
		$groups = [];
		if ( $wgTranslateWorkflowStates ) {
			$groups['translate-workflow-states'] = new WorkflowStatesMessageGroup();
		}

		return $groups;
	}

	public static function registerLoader( array &$groupLoaderNames ) {
		$groupLoaderNames[] = self::class;
	}
}
