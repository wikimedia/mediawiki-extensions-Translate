<?php
/**
 * API module for managing aggregate groups
 * @file
 * @author Santhosh Thottingal
 * @copyright Copyright © 2011, Santhosh Thottingal
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * API module for managing aggregate groups
 *
 * @ingroup API TranslateAPI
 */
class ApiAggregateGroups extends ApiBase {
	protected static $right = 'translate-manage';
	protected static $salt = 'translate-manage';

	public function execute() {
		global $wgUser;

		if ( !$wgUser->isallowed( self::$right ) ) {
			$this->dieUsage( 'Permission denied', 'permissiondenied' );
		}

		$requestParams = $this->extractRequestParams();
		$aggregateGroup = $requestParams['aggregategroup'];
		if ( $requestParams['do'] === 'associate' ) {
			$group = $requestParams['group'];
			$aggregateGroups = TranslateMetadata::get( $aggregateGroup, 'subgroups' );
			if ( trim( $aggregateGroups ) ) {
				$aggregateGroups =  array_map( 'trim',  explode( ',', $aggregateGroups ) );
			}
			else {
				$aggregateGroups = array();
			}
			$aggregateGroups[] = $group;
			$aggregateGroups = array_unique( $aggregateGroups );
			$newSubGroups =  implode( ',', $aggregateGroups );
			TranslateMetadata::set( $aggregateGroup, 'subgroups' , $newSubGroups ) ;
		}
		if ( $requestParams['do'] === 'dissociate' ) {
			$group = $requestParams['group'];
			$aggregateGroups = TranslateMetadata::get( $aggregateGroup, 'subgroups' );
			$aggregateGroups =  array_flip( explode( ',', $aggregateGroups ) ) ;
			if ( isset( $aggregateGroups[$group] ) ) {
				unset( $aggregateGroups[$group] );
			}
			$aggregateGroups = array_flip( $aggregateGroups );
			TranslateMetadata::set( $aggregateGroup, 'subgroups' , implode( ',', $aggregateGroups ) ) ;
		}
		if ( $requestParams['do'] === 'remove' ) {
			TranslateMetadata::set( $aggregateGroup, 'subgroups', false ) ;
			TranslateMetadata::set( $aggregateGroup, 'name', false ) ;
			TranslateMetadata::set( $aggregateGroup, 'description', false ) ;
		}
		if ( $requestParams['do'] === 'add' ) {
			TranslateMetadata::set( $aggregateGroup, 'subgroups' , '' ) ;
			if ( trim( $requestParams['groupname'] ) ) {
				TranslateMetadata::set( $aggregateGroup, 'name' , trim( $requestParams['groupname'] ) ) ;
			}
			if ( trim( $requestParams['groupdescription'] ) ) {
				TranslateMetadata::set( $aggregateGroup, 'description' , trim( $requestParams['groupdescription'] ) ) ;
			}
		}
		$output = array( 'result' => 'ok' );
		$this->getResult()->addValue( null, $this->getModuleName(), $output );
	}

	public function isWriteMode() {
		return true;
	}

	public function getTokenSalt() {
		return self::$salt;
	}
	public function needsToken() {
		return true;
	}

	public function getAllowedParams() {
		global $wgTranslateWorkflowStates;
		return array(
			'do' => array(
				ApiBase::PARAM_TYPE => array( 'associate', 'dissociate', 'remove' , 'add' ),
				ApiBase::PARAM_REQUIRED => true,
			),
			'aggregategroup' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
			'group' => array(
				ApiBase::PARAM_TYPE => array_keys( MessageGroups::getAllGroups() ),
			),
			'groupname' => array(
				ApiBase::PARAM_TYPE => 'string',
			),
			'groupdescription' => array(
				ApiBase::PARAM_TYPE => 'string',
			),
			'token' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'do' => 'Required operation, Either of associate, dissociate, add or remove',
			'group' => 'Message group id',
			'aggregategroup' => 'Aggregate group id',
			'groupname' => 'Aggregate group name',
			'groupdescription' => 'Aggregate group description',
			'token' => 'A token previously acquired with action=query&prop=info&intoken=aggregategroups',
		);
	}

	public static function getAggregateGroups() {
		$dbr = wfGetDB( DB_MASTER );
		$tables = array( 'translate_metadata' );
		$vars = array( 'tmd_group', 'tmd_value' );
		$conds = array(
			'tmd_key' => 'subgroups',
		);
		$options = array(
			'ORDER BY' => 'tmd_group',
		);
		$res = $dbr->select( $tables, $vars, $conds, __METHOD__, $options );
		$aggregateGroups = array();
		foreach ( $res as $r ) {
			$aggregateGroups[$r->tmd_group] = array();
			$aggregateGroups[$r->tmd_group]['id'] = $r->tmd_group;
			$aggregateGroups[$r->tmd_group]['name'] = TranslateMetadata::get( $r->tmd_group, 'name' );
			$aggregateGroups[$r->tmd_group]['description'] = TranslateMetadata::get( $r->tmd_group, 'description' );
			$subGroupsArray = explode( ',', $r->tmd_value ) ;
			$subGroups = array();
			foreach ( $subGroupsArray as $subGroup ) {
				$subGroups[$subGroup] = MessageGroups::getGroup( trim( $subGroup ) );
			}
			$aggregateGroups[$r->tmd_group]['subgroups'] = $subGroups ;
		}
		return $aggregateGroups;
	}

	public function getDescription() {
		return 'Manage aggregate groups';
	}

	public function getPossibleErrors() {
		$right = self::$right;
		return array_merge( parent::getPossibleErrors(), array(
			array( 'code' => 'permissiondenied', 'info' => "You must have $right right" ),
		) );
	}

	public function getExamples() {
		return array(
			"api.php?action=aggregategroups&do=associate&group=groupId&aggregategroup=aggregateGroupId",
		);
	}

	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}

	public static function getToken( $pageid, $title ) {
		global $wgUser;
		if ( !$wgUser->isAllowed( self::$right ) ) {
			return false;
		}

		static $cachedToken = null;
		if ( !is_null( $cachedToken ) ) {
			return $cachedToken;
		}

		$cachedToken = $wgUser->getEditToken( self::$salt );
		return $cachedToken;
	}

	public static function injectTokenFunction( &$list ) {
		$list['aggregategroups'] = array( __CLASS__, 'getToken' );
		return true; // Hooks must return bool
	}

}
