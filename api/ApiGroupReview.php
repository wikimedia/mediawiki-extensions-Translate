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
			if ( method_exists( $this, 'dieWithError' ) ) {
				$this->dieWithError( [ 'apierror-missingparam', 'group' ] );
			} else {
				$this->dieUsageMsg( [ 'missingparam', 'group' ] );
			}
		}
		$stateConfig = $group->getMessageGroupStates()->getStates();
		if ( !$stateConfig ) {
			if ( method_exists( $this, 'dieWithError' ) ) {
				$this->dieWithError( 'apierror-translate-groupreviewdisabled', 'disabled' );
			} else {
				$this->dieUsage( 'Message group review not in use', 'disabled' );
			}
		}

		if ( method_exists( $this, 'checkUserRightsAny' ) ) {
			$this->checkUserRightsAny( self::$right );
		} else {
			if ( !$user->isAllowed( self::$right ) ) {
				$this->dieUsage( 'Permission denied', 'permissiondenied' );
			}
		}

		if ( $user->isBlocked() ) {
			if ( method_exists( $this, 'dieBlocked' ) ) {
				$this->dieBlocked( $user->getBlock() );
			} else {
				$this->dieUsage( 'You have been blocked', 'blocked' );
			}
		}

		$requestParams = $this->extractRequestParams();

		$languages = Language::fetchLanguageNames();
		if ( !isset( $languages[$code] ) ) {
			if ( method_exists( $this, 'dieWithError' ) ) {
				$this->dieWithError( [ 'apierror-missingparam', 'language' ] );
			} else {
				$this->dieUsageMsg( [ 'missingparam', 'language' ] );
			}
		}

		$targetState = $requestParams['state'];
		if ( !isset( $stateConfig[$targetState] ) ) {
			if ( method_exists( $this, 'dieWithError' ) ) {
				$this->dieWithError( 'apierror-translate-invalidstate', 'invalidstate' );
			} else {
				$this->dieUsage( 'The requested state is invalid', 'invalidstate' );
			}
		}

		if ( is_array( $stateConfig[$targetState] )
			&& isset( $stateConfig[$targetState]['right'] )
		) {
			if ( method_exists( $this, 'checkUserRightsAny' ) ) {
				$this->checkUserRightsAny( $stateConfig[$targetState]['right'] );
			} else {
				if ( !$user->isAllowed( $stateConfig[$targetState]['right'] ) ) {
					$this->dieUsage( 'Permission denied', 'permissiondenied' );
				}
			}
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
