<?php
/**
 * API module for switching workflow states for message groups
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * API module for switching workflow states for message groups
 *
 * @ingroup API TranslateAPI
 */
class ApiGroupReview extends ApiBase {
	protected static $right = 'translate-groupreview';

	public function execute() {
		$user = $this->getUser();
		$requestParams = $this->extractRequestParams();

		$group = MessageGroups::getGroup( $requestParams['group'] );
		$code = $requestParams['language'];

		if ( !$group || MessageGroups::isDynamic( $group ) ) {
			$this->dieWithError( [ 'apierror-missingparam', 'group' ] );
		}
		$stateConfig = $group->getMessageGroupStates()->getStates();
		if ( !$stateConfig ) {
			$this->dieWithError( 'apierror-translate-groupreviewdisabled', 'disabled' );
		}

		$this->checkUserRightsAny( self::$right );

		if ( $user->isBlocked() ) {
			$this->dieBlocked( $user->getBlock() );
		}

		$requestParams = $this->extractRequestParams();

		$languages = Language::fetchLanguageNames();
		if ( !isset( $languages[$code] ) ) {
			$this->dieWithError( [ 'apierror-missingparam', 'language' ] );
		}

		$targetState = $requestParams['state'];
		if ( !isset( $stateConfig[$targetState] ) ) {
			$this->dieWithError( 'apierror-translate-invalidstate', 'invalidstate' );
		}

		if ( is_array( $stateConfig[$targetState] )
			&& isset( $stateConfig[$targetState]['right'] )
		) {
			$this->checkUserRightsAny( $stateConfig[$targetState]['right'] );
		}

		self::changeState( $group, $code, $targetState, $user );

		$output = [ 'review' => [
			'group' => $group->getId(),
			'language' => $code,
			'state' => $targetState,
		] ];

		$this->getResult()->addValue( null, $this->getModuleName(), $output );
	}

	public static function getState( MessageGroup $group, $code ) {
		$dbw = wfGetDB( DB_MASTER );
		$table = 'translate_groupreviews';

		$field = 'tgr_state';
		$conds = [
			'tgr_group' => $group->getId(),
			'tgr_lang' => $code
		];

		return $dbw->selectField( $table, $field, $conds, __METHOD__ );
	}

	public static function changeState( MessageGroup $group, $code, $newState, User $user ) {
		$currentState = self::getState( $group, $code );
		if ( $currentState === $newState ) {
			return false;
		}

		$table = 'translate_groupreviews';
		$index = [ 'tgr_group', 'tgr_language' ];
		$row = [
			'tgr_group' => $group->getId(),
			'tgr_lang' => $code,
			'tgr_state' => $newState,
		];

		$dbw = wfGetDB( DB_MASTER );
		$dbw->replace( $table, [ $index ], $row, __METHOD__ );

		$entry = new ManualLogEntry( 'translationreview', 'group' );
		$entry->setPerformer( $user );
		$entry->setTarget( SpecialPage::getTitleFor( 'Translate', $group->getId() ) );
		// @todo
		// $entry->setComment( $comment );
		$entry->setParameters( [
			'4::language' => $code,
			'5::group-label' => $group->getLabel(),
			'6::old-state' => $currentState,
			'7::new-state' => $newState,
		] );

		$logid = $entry->insert();
		$entry->publish( $logid );

		Hooks::run( 'TranslateEventMessageGroupStateChange',
			[ $group, $code, $currentState, $newState ] );

		return true;
	}

	public function isWriteMode() {
		return true;
	}

	public function needsToken() {
		return 'csrf';
	}

	public function getAllowedParams() {
		return [
			'group' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
			'language' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => 'en',
			],
			'state' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
			'token' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
		];
	}

	protected function getExamplesMessages() {
		return [
			'action=groupreview&group=page-Example&language=de&state=ready&token=foo'
				=> 'apihelp-groupreview-example-1',
		];
	}
}
