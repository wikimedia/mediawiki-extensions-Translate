<?php
/**
 * API module for managing aggregate message groups
 * @file
 * @author Santhosh Thottingal
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Santhosh Thottingal
 * @license GPL-2.0+
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

	public function execute() {
		if ( !$this->getUser()->isAllowed( self::$right ) ) {
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

			$logParams = array(
				'aggregategroup' => TranslateMetadata::get( $aggregateGroup, 'name' ),
				'aggregategroup-id' => $aggregateGroup,
			);

			/* Note that to allow removing no longer existing groups from
			 * aggregate message groups, the message group object $group
			 * might not always be available. In this case we need to fake
			 * some title. */
			$title = $group ?
				$group->getTitle() :
				Title::newFromText( "Special:Translate/$subgroupId" );

			$entry = new ManualLogEntry( 'pagetranslation', $action );
			$entry->setPerformer( $this->getUser() );
			$entry->setTarget( $title );
			// @todo
			// $entry->setComment( $comment );
			$entry->setParameters( $logParams );

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

			// Throw error if group already exists
			$nameExists = MessageGroups::labelExists( $name );
			if ( $nameExists ) {
				$this->dieUsage( 'Message group already exists', 'duplicateaggregategroup' );
			}

			// ID already exists- Generate a new ID by adding a number to it.
			$idExists = MessageGroups::getGroup( $aggregateGroupId );
			if ( $idExists ) {
				$i = 1;
				while ( $idExists ) {
					$tempId = $aggregateGroupId . '-' . $i;
					$idExists = MessageGroups::getGroup( $tempId );
					$i++;
				}
				$aggregateGroupId = $tempId;
			}

			TranslateMetadata::set( $aggregateGroupId, 'name', $name );
			TranslateMetadata::set( $aggregateGroupId, 'description', $desc );
			TranslateMetadata::setSubgroups( $aggregateGroupId, array() );

			// Once new aggregate group added, we need to show all the pages that can be added to that.
			$output['groups'] = self::getAllPages();
			$output['aggregategroupId'] = $aggregateGroupId;
			// @todo Logging
		} elseif ( $action === 'update' ) {
			if ( !isset( $params['groupname'] ) ) {
				$this->dieUsageMsg( array( 'missingparam', 'groupname' ) );
			}
			$name = trim( $params['groupname'] );
			if ( strlen( $name ) === 0 ) {
				$this->dieUsage( 'Invalid aggregate message group name', 'invalidaggregategroupname' );
			}
			$desc = trim( $params['groupdescription'] );
			$aggregateGroupId = $params['aggregategroup'];

			$oldName = TranslateMetadata::get( $aggregateGroupId, 'name' );
			$oldDesc = TranslateMetadata::get( $aggregateGroupId, 'description' );

			// Error if the label exists already
			$exists = MessageGroups::labelExists( $name );
			if ( $exists && $oldName !== $name ) {
				$this->dieUsage( 'Message group name already exists', 'duplicateaggregategroup' );
			}

			if ( $oldName === $name && $oldDesc === $desc ) {
				$this->dieUsage( 'Invalid update', 'invalidupdate' );
			}
			TranslateMetadata::set( $aggregateGroupId, 'name', $name );
			TranslateMetadata::set( $aggregateGroupId, 'description', $desc );
		}

		// If we got this far, nothing has failed
		$output['result'] = 'ok';
		$this->getResult()->addValue( null, $this->getModuleName(), $output );
		// Cache needs to be cleared after any changes to groups
		MessageGroups::singleton()->recache();
		MessageIndexRebuildJob::newJob()->insert();
	}

	protected function generateAggregateGroupId( $aggregateGroupName, $prefix = 'agg-' ) {
		// The database field has maximum limit of 200 bytes
		if ( strlen( $aggregateGroupName ) + strlen( $prefix ) >= 200 ) {
			return $prefix . substr( sha1( $aggregateGroupName ), 0, 5 );
		} else {
			$pattern = '/[\x00-\x1f\x23\x27\x2c\x2e\x3c\x3e\x5b\x5d\x7b\x7c\x7d\x7f\s]+/i';
			return $prefix . preg_replace( $pattern, '_', $aggregateGroupName );
		}
	}

	public function isWriteMode() {
		return true;
	}

	public function needsToken() {
		return 'csrf';
	}

	public function getAllowedParams() {
		return array(
			'do' => array(
				ApiBase::PARAM_TYPE => array( 'associate', 'dissociate', 'remove', 'add', 'update' ),
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
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	protected function getExamplesMessages() {
		return array(
			'action=aggregategroups&do=associate&group=groupId&aggregategroup=aggregateGroupId'
				=> 'apihelp-aggregategroups-example-1',
		);
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
}
