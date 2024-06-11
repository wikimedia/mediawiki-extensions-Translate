<?php
declare( strict_types = 1 );

/**
 * Loads WorkflowStatesMessageGroup, and handles the related cache.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2019.05
 */
class WorkflowStatesMessageGroupLoader implements MessageGroupLoader {
	public function getGroups(): array {
		global $wgTranslateWorkflowStates;
		$groups = [];
		if ( $wgTranslateWorkflowStates ) {
			$groups['translate-workflow-states'] = new WorkflowStatesMessageGroup();
		}

		return $groups;
	}

	/** Hook: TranslateInitGroupLoaders */
	public static function registerLoader( array &$groupLoader, array $deps ): void {
		$groupLoader[] = new self();
	}
}
