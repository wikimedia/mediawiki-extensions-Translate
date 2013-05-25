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
		$user = $this->getUser();
		$requestParams = $this->extractRequestParams();

		$group = MessageGroups::getGroup( $requestParams['group'] );
		$code = $requestParams['language'];

		if ( !$group ) {
			$this->dieUsageMsg( array( 'missingparam', 'group' ) );
		}
		$stateConfig = $group->getMessageGroupStates()->getStates();
		if ( !$stateConfig ) {
			$this->dieUsage( 'Message group review not in use', 'disabled' );
		}

		if ( !$user->isallowed( self::$right ) ) {
			$this->dieUsage( 'Permission denied', 'permissiondenied' );
		}

		$requestParams = $this->extractRequestParams();

		$languages = Language::getLanguageNames( false );
		if ( !isset( $languages[$code] ) ) {
			$this->dieUsageMsg( array( 'missingparam', 'language' ) );
		}

		$targetState = $requestParams['state'];
		if ( !isset( $stateConfig[$targetState] ) ) {
			$this->dieUsage( 'The requested state is invalid', 'invalidstate' );
		}

		if ( is_array( $stateConfig[$targetState] )
			&& isset( $stateConfig[$targetState]['right'] )
			&& !$user->isAllowed( $stateConfig[$targetState]['right'] )
		) {
			$this->dieUsage( 'Permission denied', 'permissiondenied' );
		}

		self::changeState( $group, $code, $targetState, $user );

		$output = array( 'review' => array(
			'group' => $group->getId(),
			'language' => $code,
			'state' => $targetState,
		) );

		$this->getResult()->addValue( null, $this->getModuleName(), $output );
	}

	public static function getState( MessageGroup $group, $code ) {
		$dbw = wfGetDB( DB_MASTER );
		$table = 'translate_groupreviews';

		$field = 'tgr_state';
		$conds = array(
			'tgr_group' => $group->getId(),
			'tgr_lang' => $code
		);

		return $dbw->selectField( $table, $field, $conds, __METHOD__ );
	}

	public static function changeState( MessageGroup $group, $code, $newState, User $user ) {
		$currentState = self::getState( $group, $code );
		if ( $currentState === $newState ) {
			return false;
		}

		$table = 'translate_groupreviews';
		$index = array( 'tgr_group', 'tgr_language' );
		$row = array(
			'tgr_group' => $group->getId(),
			'tgr_lang' => $code,
			'tgr_state' => $newState,
		);

		$dbw = wfGetDB( DB_MASTER );
		$dbw->replace( $table, array( $index ), $row, __METHOD__ );

		$entry = new ManualLogEntry( 'translationreview', 'group' );
		$entry->setPerformer( $user );
		$entry->setTarget( SpecialPage::getTitleFor( 'Translate', $group->getId() ) );
		// @todo
		// $entry->setComment( $comment );
		$entry->setParameters( array(
			'4::language' => $code,
			'5::group-label' => $group->getLabel(),
			'6::old-state' => $currentState,
			'7::new-state' => $newState,
		) );

		$logid = $entry->insert();
		$entry->publish( $logid );

		wfRunHooks( 'TranslateEventMessageGroupStateChange',
			array( $group, $code, $currentState, $newState ) );

		return true;
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
		$group = rawurlencode( $group );

		return array(
			"api.php?action=groupreview&group=$group&language=de&state=ready&token=foo",
		);
	}

	public function getVersion() {
		return __CLASS__ . ': ' . TRANSLATE_VERSION;
	}

	public static function getToken() {
		$user = RequestContext::getMain()->getUser();
		if ( !$user->isAllowed( self::$right ) ) {
			return false;
		}

		return $user->getEditToken( self::$salt );
	}

	public static function injectTokenFunction( &$list ) {
		$list['groupreview'] = array( __CLASS__, 'getToken' );

		return true; // Hooks must return bool
	}

	public static function getRight() {
		return self::$right;
	}
}
