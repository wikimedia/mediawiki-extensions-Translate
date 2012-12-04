<?php
/**
 * Api module for querying MessageGroups.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2010, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
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

	public function getCacheMode( $params ) {
		return 'public';
	}

	public function execute() {
		$params = $this->extractRequestParams();
		$filter = $params['filter']; //Defaults to empty string

		$groups = array();
		if ( $params['format'] === 'flat' ) {
			$groups = MessageGroups::getAllGroups();

			// Not sorted by default, so do it now
			// Work around php bug: https://bugs.php.net/bug.php?id=50688
			wfSuppressWarnings();
			usort( $groups, array( 'MessageGroups', 'groupLabelSort' ) );
			wfRestoreWarnings();

		// format=tree from now on, as it is the only other valid option
		} elseif ( $params['root'] !== '' ) {
			$group = MessageGroups::getGroup( $params['root'] );
			if ( $group instanceof AggregateMessageGroup ) {
				$groups = MessageGroups::subGroups( $group );
				// The parent group is the first, ignore it
				array_shift( $groups );
			}
		} else {
			$groups = MessageGroups::getGroupStructure();
		}

		$props = array_flip( $params['prop'] );

		$result = $this->getResult();

		foreach ( $groups as $mixed ) {
			if( strlen( $params['filter'] ) > 0 &&
				stripos( $mixed->getId(), $params['filter'] ) === false ) continue;

			$a = $this->formatGroup( $mixed, $props );

			$result->setIndexedTagName( $a, 'group' );

			// TODO: Add a continue?
			$fit = $result->addValue( array( 'query', $this->getModuleName() ), null, $a );
			if ( !$fit ) {
				$this->setWarning( 'Could not fit all groups in the resultset.' );
				// Even if we're not going to give a continue, no point carrying on if the result is full
				break;
			}
		}

		$result->setIndexedTagName_internal( array( 'query', $this->getModuleName() ), 'group' );
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

		$a = array();

		if ( isset( $props['id'] ) ) {
			$a['id'] = $g->getId();
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

		if( isset( $propsToReturn['namespace'] ) ) {
			$props['namespace'] = $group->getNamespace();
		}

		if ( isset( $props['exists'] ) ) {
			$a['exists'] = $g->exists();
		}

		if ( isset( $props['icon'] ) ) {
			$formats = $this->getIcon( $g, $params['iconsize'] );
			if ( $formats ) {
				$a['icon'] = $formats;
			}
		}

		wfRunHooks( 'TranslateProcessAPIMessageGroupsProperties', array( &$a, $props, $params, $g ) );

		// Depth only applies to tree format
		if ( $depth >= $params['depth'] && $params['format'] === 'tree' ) {
			$a['groupcount'] = count( $subgroups );
			// Prevent going further down in the three
			return $a;
		}

		// Always empty array for flat format, only sometimes for tree format
		if ( $subgroups !== array() ) {
			foreach( $subgroups as $sg ) {
				$a['groups'][] = $this->formatGroup( $sg, $props );
			}
			$result = $this->getResult();
			$result->setIndexedTagName( $a['groups'], 'group' );
		}

		return $a;
	}

	protected function getIcon( MessageGroup $g, $size ) {
		global $wgServer;
		$icon = $g->getIcon();
		if ( substr( $icon, 0, 7 ) !== 'wiki://' ) {
			return null;
		}

		$formats = array();

		$filename = substr( $icon, 7 );
		$file = wfFindFile( $filename );
		if ( !$file ) {
			$this->setWarning( "Unknown file $icon" );
			return null;
		}

		if ( $file->isVectorized() ) {
			$formats['vector'] = $file->getUrl();
		}

		$formats['raster'] = $wgServer . $file->createThumb( $size, $size );

		foreach( $formats as $key => &$url ) {
			$url = wfExpandUrl( $url, PROTO_RELATIVE );
		}

		return $formats;
	}

	public function getAllowedParams() {
		$allowedParams = array(
			'depth' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => '100',
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
			'filter' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
			),
		);
		wfRunHooks( 'TranslateGetAPIMessageGroupsParams', array( &$allowedParams ) );
		return $allowedParams;
	}

	public function getParamDescription() {
		$indent = "\n" . str_repeat( ' ', 24 );
		$wrapWidth = 104 - 24;

		$depth = <<<TEXT
When using the tree format, limit the depth to this many levels. Value 0 means
that no subgroups are shown. If the limit is reached, a prop groupcount is
added and it states the number of direct children.
TEXT;
		$root = <<<TEXT
When using the tree format, instead of starting from top level start from the
given message group, which must be aggregate message group.
TEXT;

		$p = $this->getModulePrefix(); // Can be useful for documentation
		$paramDescs = array(
			'depth' => wordwrap( str_replace( "\n", ' ', $depth ), $wrapWidth, $indent ),
			'format' => 'In a tree format message groups can exist multiple places in the tree.',
			'iconsize' => 'Preferred size of rasterised group icon',
			'root' => wordwrap( str_replace( "\n", ' ', $root ), $wrapWidth, $indent ),
			'filter' => wordwrap( 'Only return messages with IDs that contain the given text (case-insensitive).' , $wrapWidth, $indent ),
			'prop' => array_merge( array( 'What translation-related information to get:' ), self::getPropertyList() ),
		);
		wfRunHooks( 'TranslateGetAPIMessageGroupsParameterList', array( &$paramDescs, $p ) );
		return $paramDescs;
	}

	/**
	 * Returns array of key value pairs of properties and their descriptions
	 *
	 * @return array
	 */
	private static function getPropertyList() {
		$properties = array(
			'id'          => ' id           - Adds the id of the group to the output',
			'label'       => ' label        - Adds the label of the group to the output',
			'description' => ' description  - Adds the description of the group to the output',
			'class'       => ' class        - Adds the class name of the group to the output',
			'namespace'   => ' namespace    - Adds the namespace of the group to the output',
			'exists'      => ' exists       - Adds the self-calculated existence property of the group to the output',
			'icon'        => ' icon         - Adds a (raster) icon of the group to the output',
		);
		wfRunHooks( 'TranslateGetAPIMessageGroupsPropertyList', array( &$properties ) );
		return $properties;
	}

	public function getDescription() {
		return 'Return information about message groups';
	}

	protected function getExamples() {
		return array(
			'api.php?action=query&meta=messagegroups',
		);
	}

	public function getVersion() {
		return __CLASS__ . ': ' . TRANSLATE_VERSION;
	}
}
