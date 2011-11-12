<?php
/**
 * API module for switching workflow states for message groups
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2011, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * API module for switching workflow states for message groups
 *
 * @ingroup API TranslateAPI
 */
class ApiGroupReview extends ApiBase {
	protected static $right = 'translate-groupreview';
	protected static $salt = 'translate-groupreview';

	public function execute() {
		global $wgUser, $wgTranslateWorkflowStates;
		if ( !$wgTranslateWorkflowStates ) {
			$this->dieUsage( 'Message group review not in use', 'disabled' );
		}

		if ( !$wgUser->isallowed( self::$right ) ) {
			$this->dieUsage( 'Permission denied', 'permissiondenied' );
		}

		$params = $this->extractRequestParams();

		$group = MessageGroups::getGroup( $params['group'] );
		if ( !$group ) {
			$this->dieUsageMsg( array( 'missingparam', 'group' ) );
		}

		$languages = Language::getLanguageNames( false );
		if ( !isset( $languages[$params['language']] ) ) {
			$this->dieUsageMsg( array( 'missingparam', 'language' ) );
		}

		$dbw = wfGetDB( DB_MASTER );
		$table = 'translate_groupreviews';
		$row = array(
			'tgr_group' => $group->getId(),
			'tgr_lang' => $params['language'],
			'tgr_state' => $params['state'],
		);
		$index = array( 'tgr_group', 'tgr_language' );
		$res = $dbw->replace( $table, array( $index ), $row, __METHOD__ );

		/* Will be implemented later
		$logger = new LogPage( 'translationreview' );
		$params = array( $revision->getId() );
		$logger->addEntry( 'group', $title, null, $params, $wgUser );
		*/

		$output = array( 'review' => array(
			'group' => $group->getId(),
			'language' => $params['language'],
			'state' => $params['state'],
		) );

		$this->getResult()->addValue( null, $this->getModuleName(), $output );
	}

	public function isWriteMode() {
		return true;
	}

	public function needsToken() {
		return true;
	}

	public function getTokenSalt() {
		return self::$salt;
	}

	public function getAllowedParams() {
		global $wgTranslateWorkflowStates;
		return array(
			'group' => array(
				ApiBase::PARAM_TYPE => array_keys( MessageGroups::getAllGroups() ),
				ApiBase::PARAM_REQUIRED => true,
			),
			'language' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => 'en',
			),
			'state' => array(
				ApiBase::PARAM_TYPE => array_values( (array) $wgTranslateWorkflowStates ),
				ApiBase::PARAM_REQUIRED => true,
			),
			'token' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'group' => 'Message group',
			'language' => 'Language code',
			'state' => 'The new state for the group',
			'token' => 'A token previously acquired with action=query&prop=info&intoken=groupreview',
		);
	}

	public function getDescription() {
		return 'Set message group workflow states';
	}

	public function getPossibleErrors() {
		$right = self::$right;
		return array_merge( parent::getPossibleErrors(), array(
			array( 'code' => 'permissiondenied', 'info' => "You must have $right right" ),
			array( 'code' => 'disabled', 'info' => "Message group workflows are not in use" ),
		) );
	}

	public function getExamples() {
		global $wgTranslateWorkflowStates;
		$groups = MessageGroups::getAllGroups();
		$group = key( $groups );
		$state = current( (array) $wgTranslateWorkflowStates );
		return array(
			"api.php?action=groupreview&group=$group&language=de&state=$state",
		);
	}

	public function getVersion() {
		return __CLASS__ . ': $Id: $';
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

		$cachedToken = $wgUser->editToken( self::$salt );
		return $cachedToken;
	}

	public static function injectTokenFunction( &$list ) {
		$list['groupreview'] = array( __CLASS__, 'getToken' );
		return true; // Hooks must return bool
	}

}
