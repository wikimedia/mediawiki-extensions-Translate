<?php
/**
 * API module for managing aggregate message groups
 * @file
 * @author Santhosh Thottingal
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Santhosh Thottingal
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * API module for managing aggregate message groups
 * Only supports aggregate message groups defined inside the wiki.
 * Aggregate message group defined in YAML configuration cannot be altered.
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

		$params = $this->extractRequestParams();
		$aggregateGroup = $params['aggregategroup'];
		$action = $params['do'];
		$output = array();
		if ( $action === 'associate' || $action === 'dissociate' ) {
			// Group is mandatory only for these two actions
			if ( !isset( $params['group'] ) ) {
				$this->dieUsageMsg( array( 'missingparam', 'group' ) );
			}

			// Get the list of group ids
			$group = $params['group'];
			$subgroups = TranslateMetadata::get( $aggregateGroup, 'subgroups' );
			if ( $subgroups ) {
				$subgroups = array_map( 'trim', explode( ',', $subgroups ) );
			} else {
				// For newly created groups the subgroups value might be empty
				$subgroups = array();
			}

			// @FIXME: check that the group id is a translatable page
			// @FIXME: handle pages with a comma in their name

			// Add or remove from the list
			// @TODO logging
			if ( $action === 'associate' ) {
				$subgroups[] = $group;
				$subgroups = array_unique( $subgroups );
			} elseif ( $action === 'dissociate' ) {
				$subgroups = array_flip( $subgroups ) ;
				unset( $subgroups[$group] );
				$subgroups = array_flip( $subgroups );
			}

			TranslateMetadata::set( $aggregateGroup, 'subgroups', implode( ',', $subgroups ) ) ;
		} elseif ( $action === 'remove' ) {
			TranslateMetadata::set( $aggregateGroup, 'subgroups', false ) ;
			TranslateMetadata::set( $aggregateGroup, 'name', false ) ;
			TranslateMetadata::set( $aggregateGroup, 'description', false ) ;
		} elseif ( $action === 'add' ) {
			if ( TranslateMetadata::get( $aggregateGroup, 'subgroups' ) ) {
				$this->dieUsage( 'Aggregate Group aleady exists', 'duplicateaggregategroup' );
			}
			// @FIXME: check that the group id is valid (like, no commas)
			TranslateMetadata::set( $aggregateGroup, 'subgroups', '' ) ;
			$name = trim( $params['groupname'] );
			$desc = trim( $params['groupdescription'] );

			if ( $name ) {
				TranslateMetadata::set( $aggregateGroup, 'name', $name ) ;
			}
			if ( $desc ) {
				TranslateMetadata::set( $aggregateGroup, 'description', $desc ) ;
			}
			// Once new aggregate group added, we need to show all the pages that can be added to that.
			$output['groups'] = self::getAllPages();
		}

		// If we got this far, nothing has failed
		$output['result'] = 'ok';
		$this->getResult()->addValue( null, $this->getModuleName(), $output );
		// Cache needs to be cleared after any changes to groups
		MessageGroups::clearCache();
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
		return array(
			'do' => array(
				ApiBase::PARAM_TYPE => array( 'associate', 'dissociate', 'remove', 'add' ),
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
				ApiBase::PARAM_REQUIRED => false,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'do' => 'What to do with aggregate message group',
			'group' => 'Message group id',
			'aggregategroup' => 'Aggregate message group id',
			'groupname' => 'Aggregate message group name',
			'groupdescription' => 'Aggregate message group description',
			'token' => 'A token previously acquired with action=query&prop=info&intoken=aggregategroups',
		);
	}


	public function getDescription() {
		return 'Manage aggregate message groups. You can add and remove aggregate message' .
			'groups and associate or dissociate message groups from them (one at a time).';
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

	public static function getAllPages() {
		$groups = MessageGroups::getAllGroups();
		$pages = array();
		foreach ( $groups as  $group ) {
			if ( $group instanceof WikiPageMessageGroup ) {
				$pages[$group->getId()] = $group->getTitle()->getPrefixedText();
			}
		}
		return $pages;
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
