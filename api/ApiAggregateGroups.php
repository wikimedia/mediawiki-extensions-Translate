<?php
/**
 * API module for managing aggregate message groups
 * @file
 * @author Santhosh Thottingal
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Santhosh Thottingal
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
		$action = $params['do'];
		$output = array();
		if ( $action === 'associate' || $action === 'dissociate' ) {
			// Group is mandatory only for these two actions
			if ( !isset( $params['group'] ) ) {
				$this->dieUsageMsg( array( 'missingparam', 'group' ) );
			}
			if ( !isset( $params['aggregategroup'] ) ) {
				$this->dieUsageMsg( array( 'missingparam', 'aggregategroup' ) );
			}
			$aggregateGroup = $params['aggregategroup'];
			$subgroups = TranslateMetadata::getSubgroups( $aggregateGroup );
			if ( count( $subgroups ) === 0 ) {
				// For newly created groups the subgroups value might be empty,
				// but check that.
				if ( TranslateMetadata::get( $aggregateGroup, 'name' ) === false ) {
					$this->dieUsage( 'Invalid aggregate message group', 'invalidaggregategroup' );
				}
				$subgroups = array();
			}

			$subgroupId = $params['group'];
			$group = MessageGroups::getGroup( $subgroupId );

			// Add or remove from the list
			if ( $action === 'associate' ) {
				if ( !$group instanceof WikiPageMessageGroup ) {
					$this->dieUsage( 'Group does not exist or invalid', 'invalidgroup' );
				}

				$subgroups[] = $subgroupId;
				$subgroups = array_unique( $subgroups );
			} elseif ( $action === 'dissociate' ) {
				// Allow removal of non-existing groups
				$subgroups = array_flip( $subgroups );
				unset( $subgroups[$subgroupId] );
				$subgroups = array_flip( $subgroups );
			}

			TranslateMetadata::setSubgroups( $aggregateGroup, $subgroups );

			$logparams = array(
				'aggregategroup' => TranslateMetadata::get( $aggregateGroup, 'name' ),
				'aggregategroup-id' => $aggregateGroup,
			);

			/* Note that to allow removing no longer existing groups from
			 * aggregate message groups, the message group object $group
			 * might not always be available. In this case we need to fake
			 * some title. */
			$title = $group ? $group->getTitle() : Title::newFromText( "Special:Translate/$subgroupId" );

			$entry = new ManualLogEntry( 'pagetranslation', $action );
			$entry->setPerformer( $wgUser );
			$entry->setTarget( $title );
			// @todo
			// $entry->setComment( $comment );
			$entry->setParameters( $logparams );

			$logid = $entry->insert();
			$entry->publish( $logid );
		} elseif ( $action === 'remove' ) {
			if ( !isset( $params['aggregategroup'] ) ) {
				$this->dieUsageMsg( array( 'missingparam', 'aggregategroup' ) );
			}
			TranslateMetadata::deleteGroup( $params['aggregategroup'] );
			// @todo Logging

		} elseif ( $action === 'add' ) {
			if ( !isset( $params['groupname'] ) ) {
				$this->dieUsageMsg( array( 'missingparam', 'groupname' ) );
			}
			$name = trim( $params['groupname'] );
			if ( strlen( $name ) === 0 ) {
				$this->dieUsage( 'Invalid aggregate message group name', 'invalidaggregategroupname' );
			}

			if ( !isset( $params['groupdescription'] ) ) {
				$this->dieUsageMsg( array( 'missingparam', 'groupdescription' ) );
			}
			$desc = trim( $params['groupdescription'] );

			$aggregateGroupId = self::generateAggregateGroupId( $name );
			$exists = MessageGroups::getGroup( $aggregateGroupId );
			if ( $exists ) {
				$this->dieUsage( 'Message group already exists', 'duplicateaggregategroup' );
			}

			TranslateMetadata::set( $aggregateGroupId, 'name', $name );
			TranslateMetadata::set( $aggregateGroupId, 'description', $desc );
			TranslateMetadata::setSubgroups( $aggregateGroupId, array() );

			// Once new aggregate group added, we need to show all the pages that can be added to that.
			$output['groups'] = self::getAllPages();
			$output['aggregategroupId'] = $aggregateGroupId;
			// @todo Logging

		}

		// If we got this far, nothing has failed
		$output['result'] = 'ok';
		$this->getResult()->addValue( null, $this->getModuleName(), $output );
		// Cache needs to be cleared after any changes to groups
		MessageGroups::clearCache();
		MessageIndexRebuildJob::newJob()->insert();
	}

	protected function generateAggregateGroupId( $aggregateGroupName, $prefix = "agg-" ) {
		// The database field has maximum limit of 200 bytes
		if ( strlen( $aggregateGroupName ) + strlen( $prefix ) >= 200 ) {
			return $prefix . substr( sha1( $aggregateGroupName ), 0, 5 );
		} else {
			return $prefix . preg_replace( '/[\x00-\x1f\x23\x27\x2c\x2e\x3c\x3e\x5b\x5d\x7b\x7c\x7d\x7f\s]+/i', '_', $aggregateGroupName );
		}
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
			),
			'group' => array(
				// Not providing list of values, to allow dissociation of unknown groups
				ApiBase::PARAM_TYPE => 'string',
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
		$action = TranslateUtils::getTokenAction( 'aggregategroups' );

		return array(
			'do' => 'What to do with aggregate message group',
			'group' => 'Message group id',
			'aggregategroup' => 'Aggregate message group id',
			'groupname' => 'Aggregate message group name',
			'groupdescription' => 'Aggregate message group description',
			'token' => "A token previously acquired with $action",
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
		return __CLASS__ . ': ' . TRANSLATE_VERSION;
	}

	public static function getAllPages() {
		$groups = MessageGroups::getAllGroups();
		$pages = array();
		foreach ( $groups as $group ) {
			if ( $group instanceof WikiPageMessageGroup ) {
				$pages[$group->getId()] = $group->getTitle()->getPrefixedText();
			}
		}

		return $pages;
	}

	public static function getToken() {
		global $wgUser;
		if ( !$wgUser->isAllowed( self::$right ) ) {
			return false;
		}

		return $wgUser->getEditToken( self::$salt );
	}

	public static function injectTokenFunction( &$list ) {
		$list['aggregategroups'] = array( __CLASS__, 'getToken' );

		return true; // Hooks must return bool
	}

	public static function getRight() {
		return self::$right;
	}
}
