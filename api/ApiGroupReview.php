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
		global $wgUser;
		$requestParams = $this->extractRequestParams();
		$group = MessageGroups::getGroup( $requestParams['group'] );
		if ( !$group ) {
			$this->dieUsageMsg( array( 'missingparam', 'group' ) );
		}
		$stateConfig = $group->getWorkflowConfiguration();
		if ( !$stateConfig ) {
			$this->dieUsage( 'Message group review not in use', 'disabled' );
		}

		if ( !$wgUser->isallowed( self::$right ) ) {
			$this->dieUsage( 'Permission denied', 'permissiondenied' );
		}

		$requestParams = $this->extractRequestParams();

		$languages = Language::getLanguageNames( false );
		if ( !isset( $languages[$requestParams['language']] ) ) {
			$this->dieUsageMsg( array( 'missingparam', 'language' ) );
		}

		$dbr = wfGetDB( DB_SLAVE );
		$groupid = $group->getId();
		$currentState = $dbr->selectField(
			'translate_groupreviews',
			'tgr_state',
			array( 'tgr_group' => $groupid, 'tgr_lang' => $requestParams['language'] ),
			__METHOD__
		);
		$targetState = $requestParams['state'];
		if ( $currentState === $targetState ) {
			$this->dieUsage( 'The requested state is identical to the current state', 'sameworkflowstate' );
		}

		if ( !isset( $stateConfig[$targetState] ) ) {
			$this->dieUsage( 'The requested state is invalid', 'invalidstate' );
		}

		if ( is_array( $stateConfig[$targetState] )
			&& isset( $stateConfig[$targetState]['right'] )
			&& !$wgUser->isAllowed( $stateConfig[$targetState]['right'] ) )
		{
			$this->dieUsage( 'Permission denied', 'permissiondenied' );
		}


		$dbw = wfGetDB( DB_MASTER );
		$table = 'translate_groupreviews';
		$row = array(
			'tgr_group' => $groupid,
			'tgr_lang' => $requestParams['language'],
			'tgr_state' => $targetState,
		);
		$index = array( 'tgr_group', 'tgr_language' );
		$res = $dbw->replace( $table, array( $index ), $row, __METHOD__ );

		$logger = new LogPage( 'translationreview' );
		$logParams = array(
			$requestParams['language'],
			$group->getLabel(),
			$currentState,
			$targetState,
		);
		$logger->addEntry(
			'group',
			SpecialPage::getTitleFor( 'Translate', $groupid ),
			'', // No comments
			$logParams,
			$wgUser
		);

		$output = array( 'review' => array(
			'group' => $group->getId(),
			'language' => $requestParams['language'],
			'state' => $targetState,
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
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
			'token' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	public function getParamDescription() {
		$action = TranslateUtils::getTokenAction( 'groupreview' );
		return array(
			'group' => 'Message group',
			'language' => 'Language code',
			'state' => 'The new state for the group',
			'token' => "A token previously acquired with $action",
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
			array( 'code' => 'sameworkflowstate', 'info' => "The requested state is identical to the current state" ),
			array( 'code' => 'invalidstate', 'info' => "The requested state is invalid" ),
		) );
	}

	public function getExamples() {
		$groups = MessageGroups::getAllGroups();
		$group = key( $groups );
		return array(
			"api.php?action=groupreview&group=$group&language=de&state=ready",
		);
	}

	public function getVersion() {
		return __CLASS__ . ': ' . TRANSLATE_VERSION;
	}

	public static function getToken() {
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
