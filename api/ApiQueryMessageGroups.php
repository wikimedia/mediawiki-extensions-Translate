<?php
/**
 * Api module for querying MessageGroups.
 *
 * @file
 * @author Niklas Laxström
 * @author Harry Burt
 * @copyright Copyright © 2010-2013, Niklas Laxström
 * @copyright Copyright © 2012-2013, Harry Burt
 * @license GPL-2.0+
 */

/**
 * Api module for querying MessageGroups.
 *
 * @ingroup API TranslateAPI
 */
class ApiQueryMessageGroups extends ApiQueryBase {

	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'mg' );
	}

	public function execute() {
		$params = $this->extractRequestParams();
		$filter = $params['filter'];

		$groups = array();

		// Parameter root as all for all pages subgroups
		if ( $params['root'] === 'all' ) {
			$allGroups = MessageGroups::getAllGroups();
			foreach ( $allGroups as $group ) {
				if ( $group instanceof WikiPageMessageGroup ) {
					$groups[] = $group;
				}
			}
		} elseif ( $params['format'] === 'flat' ) {
			if ( $params['root'] !== '' ) {
				$group = MessageGroups::getGroup( $params['root'] );
				if ( $group ) {
					$groups[$params['root']] = $group;
				}
			} else {
				$groups = MessageGroups::getAllGroups();
				foreach ( MessageGroups::getDynamicGroups() as $id => $unused ) {
					$groups[$id] = MessageGroups::getGroup( $id );
				}
			}

			// Not sorted by default, so do it now
			// Work around php bug: https://bugs.php.net/bug.php?id=50688
			wfSuppressWarnings();
			usort( $groups, array( 'MessageGroups', 'groupLabelSort' ) );
			wfRestoreWarnings();
		} elseif ( $params['root'] !== '' ) {
			// format=tree from now on, as it is the only other valid option
			$group = MessageGroups::getGroup( $params['root'] );
			if ( $group instanceof AggregateMessageGroup ) {
				$groups = MessageGroups::subGroups( $group );
				// The parent group is the first, ignore it
				array_shift( $groups );
			}
		} else {
			$groups = MessageGroups::getGroupStructure();
			foreach ( MessageGroups::getDynamicGroups() as $id => $unused ) {
				$groups[$id] = MessageGroups::getGroup( $id );
			}
		}

		// Do not list the sandbox group. The code that knows it
		// exists can access it directly.
		if ( isset( $groups['!sandbox'] ) ) {
			unset( $groups['!sandbox'] );
		}

		$props = array_flip( $params['prop'] );

		$result = $this->getResult();
		$matcher = new StringMatcher( '', $filter );
		/**
		 * @var MessageGroup $mixed
		 */
		foreach ( $groups as $mixed ) {
			if ( $filter !== array() && !$matcher->match( $mixed->getId() ) ) {
				continue;
			}

			$a = $this->formatGroup( $mixed, $props );

			$result->setIndexedTagName( $a, 'group' );

			// @todo Add a continue?
			$fit = $result->addValue( array( 'query', $this->getModuleName() ), null, $a );
			if ( !$fit ) {
				$this->setWarning( 'Could not fit all groups in the resultset.' );
				// Even if we're not going to give a continue, no point carrying on
				// if the result is full
				break;
			}
		}

		if ( defined( 'ApiResult::META_CONTENT' ) ) {
			$result->addIndexedTagName( array( 'query', $this->getModuleName() ), 'group' );
		} else {
			$result->setIndexedTagName_internal( array( 'query', $this->getModuleName() ), 'group' );
		}
	}

	/**
	 * @param array|MessageGroup $mixed
	 * @param array $props List of props as the array keys
	 * @param int $depth
	 * @return array
	 */
	protected function formatGroup( $mixed, $props, $depth = 0 ) {
		$params = $this->extractRequestParams();

		// Default
		$g = $mixed;
		$subgroups = array();

		// Format = tree and has subgroups
		if ( is_array( $mixed ) ) {
			$g = array_shift( $mixed );
			$subgroups = $mixed;
		}

		wfProfileIn( __METHOD__ . '-' . get_class( $g ) );

		$a = array();

		$groupId = $g->getId();

		wfProfileIn( __METHOD__ . '-basic' );
		if ( isset( $props['id'] ) ) {
			$a['id'] = $groupId;
		}

		if ( isset( $props['label'] ) ) {
			$a['label'] = $g->getLabel();
		}

		if ( isset( $props['description'] ) ) {
			$a['description'] = $g->getDescription();
		}

		if ( isset( $props['class'] ) ) {
			$a['class'] = get_class( $g );
		}

		if ( isset( $props['namespace'] ) ) {
			$a['namespace'] = $g->getNamespace();
		}
		wfProfileOut( __METHOD__ . '-basic' );

		wfProfileIn( __METHOD__ . '-exists' );
		if ( isset( $props['exists'] ) ) {
			$a['exists'] = $g->exists();
		}
		wfProfileOut( __METHOD__ . '-exists' );

		wfProfileIn( __METHOD__ . '-icon' );
		if ( isset( $props['icon'] ) ) {
			$formats = TranslateUtils::getIcon( $g, $params['iconsize'] );
			if ( $formats ) {
				$a['icon'] = $formats;
			}
		}
		wfProfileOut( __METHOD__ . '-icon' );

		wfProfileIn( __METHOD__ . '-priority' );
		if ( isset( $props['priority'] ) ) {
			$priority = MessageGroups::getPriority( $g );
			$a['priority'] = $priority ?: 'default';
		}

		if ( isset( $props['prioritylangs'] ) ) {
			$prioritylangs = TranslateMetadata::get( $groupId, 'prioritylangs' );
			$a['prioritylangs'] = $prioritylangs ? explode( ',', $prioritylangs ) : false;
		}

		if ( isset( $props['priorityforce'] ) ) {
			$a['priorityforce'] = ( TranslateMetadata::get( $groupId, 'priorityforce' ) === 'on' );
		}
		wfProfileOut( __METHOD__ . '-priority' );

		wfProfileIn( __METHOD__ . '-workflowstates' );
		if ( isset( $props['workflowstates'] ) ) {
			$a['workflowstates'] = $this->getWorkflowStates( $g );
		}
		wfProfileOut( __METHOD__ . '-workflowstates' );

		wfRunHooks(
			'TranslateProcessAPIMessageGroupsProperties',
			array( &$a, $props, $params, $g )
		);

		wfProfileOut( __METHOD__ . '-' . get_class( $g ) );

		// Depth only applies to tree format
		if ( $depth >= $params['depth'] && $params['format'] === 'tree' ) {
			$a['groupcount'] = count( $subgroups );

			// Prevent going further down in the three
			return $a;
		}

		// Always empty array for flat format, only sometimes for tree format
		if ( $subgroups !== array() ) {
			foreach ( $subgroups as $sg ) {
				$a['groups'][] = $this->formatGroup( $sg, $props );
			}
			$result = $this->getResult();
			$result->setIndexedTagName( $a['groups'], 'group' );
		}

		return $a;
	}

	/**
	 * Get the workflow states applicable to the given message group
	 *
	 * @param MessageGroup $group
	 * @return boolean|array Associative array with states as key and localized state
	 * labels as values
	 */
	protected function getWorkflowStates( MessageGroup $group ) {
		if ( MessageGroups::isDynamic( $group ) ) {
			return false;
		}

		$stateConfig = $group->getMessageGroupStates()->getStates();

		if ( !is_array( $stateConfig ) || $stateConfig === array() ) {
			return false;
		}

		$user = $this->getUser();

		foreach ( $stateConfig as $state => $config ) {
			if ( is_array( $config ) ) {
				// Check if user is allowed to change states generally
				$allowed = $user->isAllowed( 'translate-groupreview' );
				// Check further restrictions
				if ( $allowed && isset( $config['right'] ) ) {
					$allowed = $user->isAllowed( $config['right'] );
				}

				$stateConfig[$state]['_canchange'] = $allowed;
				$stateConfig[$state]['_name'] =
					$this->msg( "translate-workflow-state-$state" )->text();

				// Workaround for http://www.gossamer-threads.com/lists/wiki/wikitech/584489
				ApiResult::setPreserveKeysList( $stateConfig[$state], array( '_canchange', '_name' ) );
			}
		}

		return $stateConfig;
	}

	public function getAllowedParams() {
		$allowedParams = array(
			'depth' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => '100',
			),
			'filter' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
				ApiBase::PARAM_ISMULTI => true,
			),
			'format' => array(
				ApiBase::PARAM_TYPE => array( 'flat', 'tree' ),
				ApiBase::PARAM_DFLT => 'flat',
			),
			'iconsize' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => 64,
			),
			'prop' => array(
				ApiBase::PARAM_TYPE => array_keys( self::getPropertyList() ),
				ApiBase::PARAM_DFLT => 'id|label|description|class|exists',
				ApiBase::PARAM_ISMULTI => true,
			),
			'root' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
			),
		);
		wfRunHooks( 'TranslateGetAPIMessageGroupsParameterList', array( &$allowedParams ) );

		return $allowedParams;
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		$depth = <<<TEXT
When using the tree format, limit the depth to this many levels. Value 0 means
that no subgroups are shown. If the limit is reached, a prop groupcount is
added and it states the number of direct children.
TEXT;
		$root = <<<TEXT
When using the tree format, instead of starting from top level start from the
given message group, which must be an aggregate message group. When using flat
format only the specified group is returned.
TEXT;
		$filter = <<<TEXT
Only return messages with IDs that match one or more of the input(s) given
(case-insensitive, separated by pipes, * wildcard).
TEXT;

		$propIntro = array( 'What translation-related information to get:' );

		$paramDescs = array(
			'depth' => $depth,
			'format' => 'In a tree format message groups can exist multiple places in the tree.',
			'iconsize' => 'Preferred size of rasterised group icon',
			'root' => $root,
			'filter' => $filter,
			'prop' => array_merge( $propIntro, self::getPropertyList() ),
		);

		$p = $this->getModulePrefix(); // Can be useful for documentation
		wfRunHooks( 'TranslateGetAPIMessageGroupsParameterDescs', array( &$paramDescs, $p ) );

		$indent = "\n" . str_repeat( ' ', 24 );
		$wrapWidth = 104 - 24;
		foreach ( $paramDescs as &$val ) {
			if ( is_string( $val ) ) {
				$val = wordwrap( str_replace( "\n", ' ', $val ), $wrapWidth, $indent );
			}
		}

		return $paramDescs;
	}

	/**
	 * Returns array of key value pairs of properties and their descriptions
	 *
	 * @return array
	 */
	protected static function getPropertyList() {
		$properties = array(
			'id'             => ' id             - Include id of the group',
			'label'          => ' label          - Include label of the group',
			'description'    => ' description    - Include description of the group',
			'class'          => ' class          - Include class name of the group',
			'namespace'      =>
				' namespace      - Include namespace of the group. Not all groups belong ' .
					'to a single namespace.',
			'exists'         =>
				' exists         - Include self-calculated existence property of the group',
			'icon'           => ' icon           - Include urls to icon of the group',
			'priority'       => ' priority       - Include priority status like discouraged',
			'prioritylangs'  =>
				' prioritylangs  - Include preferred languages. If not set, this returns false',
			'priorityforce'  =>
				' priorityforce  - Include priority status - is the priority languages ' .
					'setting forced',
			'workflowstates' =>
				' workflowstates - Include the workflow states for the message group',
		);

		wfRunHooks( 'TranslateGetAPIMessageGroupsPropertyDescs', array( &$properties ) );

		return $properties;
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Return information about message groups. Note that uselang parameter ' .
			'affects the output of language dependent parts.';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	protected function getExamples() {
		return array(
			'api.php?action=query&meta=messagegroups',
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=query&meta=messagegroups'
				=> 'apihelp-query+messagegroups-example-1',
		);
	}
}
