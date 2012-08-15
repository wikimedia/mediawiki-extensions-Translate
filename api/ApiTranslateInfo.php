<?php
/**
 * This file contains a class for handling action=translateinfo API requests
 *
 * @file
 * @author Harry Burt, Yuri Astrakhan
 * @copyright Copyright © 2012 Harry Burt, Yuri Astrakhan
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
class ApiTranslateInfo extends ApiQueryBase {
	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'ti' );
	}

	public function getCacheMode( $params ) {
		return 'public';
	}

	public function execute() {
		$params = $this->extractRequestParams();
		$propsToReturn = array_flip( $params['prop'] );
		$groupNames = explode( '|', $params['groups'] );
		$result = $this->getResult();

		$groups = array_map( 'MessageGroups::getGroup', $groupNames );
		$results = array();
		foreach( $groups as $group ) {
			if( $group === null ) {
				continue;
			}

			$props = array();
			$props['id'] = $group->getId();
			if( isset( $propsToReturn['label'] ) ) {
				$props['label'] = $group->getLabel();
			}
			if( isset( $propsToReturn['description'] ) ) {
				$props['description'] = $group->getDescription();
			}
			if( isset( $propsToReturn['class'] ) ) {
				$props['class'] = get_class( $group );
			}
			if( isset( $propsToReturn['exists'] ) ) {
				$props['exists'] = $group->exists();
			}

			wfRunHooks( 'TranslateProcessTranslateInfoProperties', array( &$props, $propsToReturn, $params, $group ) );
			$results[] = $props;
		}
		$this->getResult()->setIndexedTagName( $results, 'group' );
		$result->addValue( array( 'query' ), 'groups', $results );
	}

	public function getAllowedParams() {
		$params = array(
			'prop' => array(
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_DFLT => 'label|description|class|exists',
				ApiBase::PARAM_TYPE => self::getPropertyNames()
			),
			'groups' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			)
		);
		wfRunHooks( 'TranslateGetTranslateInfoParams', array( &$params ) );
		return $params;
	}

	/**
	 * Returns all possible parameters to iiprop
	 *
	 * @param array $filter List of properties to filter out
	 * @return Array
	 */
	public static function getPropertyNames( $filter = array() ) {
		return array_diff( array_keys( self::getPropertyList() ), $filter );
	}

	/**
	 * Returns array of key value pairs of properties and their descriptions
	 *
	 * @return array
	 */
	private static function getPropertyList() {
		$properties = array(
			'label'       => ' label        - Adds the label of the group to the output',
			'description' => ' description  - Adds the description of the group to the output',
			'class'       => ' class        - Adds the class name of the group to the output',
			'exists'      => ' exists       - Adds the self-calculatedd existence property of the group to the output',
		);
		wfRunHooks( 'TranslateGetTranslateInfoPropertyList', array( &$properties ) );
		return $properties;
	}

	/**
	 * Returns the descriptions for the properties provided by getPropertyNames()
	 *
	 * @param array $filter List of properties to filter out
	 * @return array
	 */
	public static function getPropertyDescriptions( $filter = array() ) {
		return array_merge(
			array( 'What translation-related information to get:' ),
			array_values( array_diff_key( self::getPropertyList(), array_flip( $filter ) ) )
		);
	}

	/**
	 * Return the API documentation for the parameters.
	 * @return Array parameter documentation.
	 */
	public function getParamDescription() {
		$p = $this->getModulePrefix();
		$paramDescs = array(
			'prop' => self::getPropertyDescriptions()
		);
		wfRunHooks( 'TranslateGetTranslateInfoParamDescs', array( &$paramDescs, $p ) );
	}

	public function getDescription() {
		return 'Return information about a message under translation';
	}

	protected function getExamples() {
		return array(
			'api.php?action=translateinfo&tititle=MediaWiki:Example',
		);
	}

	public function getVersion() {
		return __CLASS__ . ': ' . TRANSLATE_VERSION;
	}
}