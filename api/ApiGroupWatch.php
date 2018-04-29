<?php
/**
 * API module to allow users to watch a message group.
 *
 * @ingroup API TranslateAPI
 * @since 2018.07
 */
class ApiGroupWatch extends ApiBase {

	public function execute() {
		$user = $this->getUser();

		if ( !$user->isLoggedIn() ) {
			$this->dieWithError( 'translate-groupwatchlistanontext', 'notloggedin' );
		}

		if ( method_exists( $this, 'checkUserRightsAny' ) ) {
			$this->checkUserRightsAny( 'editmywatchlist' );
		} else {
			if ( !$user->isAllowed( 'editmywatchlist' ) ) {
				$this->dieUsage( 'Permission denied', 'permissiondenied' );
			}
		}

		$requestParams = $this->extractRequestParams();
		$messageGroup = $requestParams['messagegroup'];
		$group = MessageGroups::getGroup( $messageGroup );
		if ( !$group || MessageGroups::isDynamic( $group ) ) {
			$this->dieWithError( [ 'apierror-missingparam', 'group' ] );
		}

		$res = $this->watchMessageGroup( $messageGroup, $user, $requestParams );
		$this->getResult()->addValue( null, $this->getModuleName(), $res );
	}

	private function watchMessageGroup( $messageGroup, User $user, array $params ) {
		if ( $wgTranslateEnableMessageGroupWatchlist === false ) {
			$this->dieWithError( [
				'apierror-groupwatch-disabled',
				'messagegroupswatchlistdisabled'
			] );
		}

		$res = [ 'messagegroup' => $messageGroup ];

		$dbw = wfGetDB( DB_MASTER );
		$table = 'translate_groupwatchlist';

		if ( $params['unwatch'] ) {
			$res[ 'unwatch' ] = true;
			$conds = [
				'tgw_user' => $user->getId(),
				'tgw_group' => $messageGroup,
			];

			$dbw->delete( $table, $conds, __METHOD__ );
		} else {
			$row = [
				'tgw_user' => $user->getId(),
				'tgw_group' => $messageGroup,
				'tgw_notificationtimestamp' => null,
			];

			$dbw->insert( $table, $row, __METHOD__ );
		}

		return $res;
	}

	public function mustBePosted() {
		return true;
	}

	public function isWriteMode() {
		return true;
	}

	public function needsToken() {
		return 'watch';
	}

	public function getAllowedParams() {
		return [
			'unwatch' => false,
			'messagegroup' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
		];
	}

	protected function getExamplesMessages() {
		return [
			'action=groupwatch&format=json&messagegroup=page-Test_page&token=123ABC'
			=> 'apihelp-groupwatch-example-1',
			'action=groupwatch&format=json&messagegroup=page-Main_Page&unwatch=&token=123ABC'
			=> 'apihelp-groupwatch-example-2'
		];
	}
}
