<?php
/**
 * Created by PhpStorm.
 * User: Anna
 * Date: 3/26/2018
 * Time: 13:27
 *
 * API module to allow users to watch a message group.
 *
 * @ingroup API
 */
class ApiGroupWatch extends ApiBase {
	private $mPageSet = null;

	public function execute() {
		$user = $this->getUser();
		if ( !$user->isLoggedIn() ) {
			$this->dieWithError( 'translate-groupwatchlistanontext', 'notloggedin' );
		}

		$this->checkUserRightsAny( 'editmywatchlist' ); //@todo

		$params = $this->extractRequestParams();

		$continuationManager = new ApiContinuationManager( $this, [], [] );
		$this->setContinuationManager( $continuationManager );

//		$pageSet = $this->getPageSet();
//		// by default we use pageset to extract the page to work on.
//		// title is still supported for backward compatibility
//		if ( !isset( $params['title'] ) ) {
//			$pageSet->execute();
//			$res = $pageSet->getInvalidTitlesAndRevisions( [
//				'invalidTitles',
//				'special',
//				'missingIds',
//				'missingRevIds',
//				'interwikiTitles'
//			] );
//
//			foreach ( $pageSet->getMissingTitles() as $title ) {
//				$r = $this->watchTitle( $title, $user, $params );
//				$r['missing'] = true;
//				$res[] = $r;
//			}
//
//			foreach ( $pageSet->getGoodTitles() as $title ) {
//				$r = $this->watchTitle( $title, $user, $params );
//				$res[] = $r;
//			}
//			ApiResult::setIndexedTagName( $res, 'w' );
//		} else {
//			// dont allow use of old title parameter with new pageset parameters.
//			$extraParams = array_keys( array_filter( $pageSet->extractRequestParams(), function ( $x ) {
//				return $x !== null && $x !== false;
//			} ) );
//
//			if ( $extraParams ) {
//				$this->dieWithError(
//					[
//						'apierror-invalidparammix-cannotusewith',
//						$this->encodeParamName( 'title' ),
//						$pageSet->encodeParamName( $extraParams[0] )
//					],
//					'invalidparammix'
//				);
//			}
//
//			$title = Title::newFromText( $params['title'] );
//			if ( !$title || !$title->isWatchable() ) {
//				$this->dieWithError( [ 'invalidtitle', $params['title'] ] );
//			}
//			$res = $this->watchTitle( $title, $user, $params, true );
//		}

        $res = $this->watchMessageGroup( [$params['messagegroups']], $user, $params, true );
		$this->getResult()->addValue( null, $this->getModuleName(), $res );

		$this->setContinuationManager( null );
		$continuationManager->setContinuationIntoResult( $this->getResult() );
	}

	private function watchMessageGroup( array $messageGroups, User $user, array $params, $compatibilityMode = false) {

		$res = [ 'messagegroups' => $messageGroups ];

		if ( $params['unwatch'] ) {
            // Only logged in user can have a watchlist
            if ( $user->isAnon() ) {
                return false;
            }

            $dbw = wfGetDB( DB_MASTER );

		    foreach ( $messageGroups as $messageGroup ) {
                $conds = [
                    'tgw_user' => $user->getId(),
                    'tgw_group' => $messageGroup,
                ];
                $dbw->delete('translate_groupwatchlist', $conds, __METHOD__);
            }
//            $res['watched'] = $status->isOK();
		} else {

            // Only logged in user can have a watchlist
            if ( $user->isAnon() ) {
                return false;
            }

            if ( !$messageGroups ) {
                return true;
            }

            $rows = [];

            foreach ( $messageGroups as $messageGroup ) {
                $rows[] = [
                    'tgw_user' => $user->getId(),
                    'tgw_group' => $messageGroup,
                    'tgw_notificationtimestamp' => null,
                ];
            }

            $dbw = wfGetDB( DB_MASTER );
            $dbw->insert( 'translate_groupwatchlist', $rows, __METHOD__ );

//            $res['watched'] = Status::newGood()->isOK();

            return true;
		}

//		if ( !$status->isOK() ) {
//			if ( $compatibilityMode ) {
//				$this->dieStatus( $status );
//			}
//			$res['errors'] = $this->getErrorFormatter()->arrayFromStatus( $status, 'error' );
//			$res['warnings'] = $this->getErrorFormatter()->arrayFromStatus( $status, 'warning' );
//			if ( !$res['warnings'] ) {
//				unset( $res['warnings'] );
//			}
//		}

		return $res;
	}

//	/**
//	 * Get a cached instance of an ApiPageSet object
//	 * @return ApiPageSet
//	 */
//	private function getPageSet() {
//		if ( $this->mPageSet === null ) {
//			$this->mPageSet = new ApiPageSet( $this );
//		}
//
//		return $this->mPageSet;
//	}

	public function mustBePosted() {
		return true;
	}

	public function isWriteMode() {
		return true;
	}

	public function needsToken() {
		return 'watch';
	}

	public function getAllowedParams( $flags = 0 ) {
		$result = [
			'unwatch' => false,
            'messagegroups' => [
                ApiBase::PARAM_TYPE => 'string',
            ],
			'continue' => [
				ApiBase::PARAM_HELP_MSG => 'api-help-param-continue',
			],
		];
		if ( $flags ) {
//			$result += $this->getPageSet()->getFinalParams( $flags );
		}

		return $result;
	}

	protected function getExamplesMessages() {
		return [
			'action=watch&titles=Main_Page&token=123ABC'
			=> 'apihelp-watch-example-watch',
			'action=watch&titles=Main_Page&unwatch=&token=123ABC'
			=> 'apihelp-watch-example-unwatch',
			'action=watch&generator=allpages&gapnamespace=0&token=123ABC'
			=> 'apihelp-watch-example-generator',
		];
	}

	public function getHelpUrls() {
		return 'https://www.mediawiki.org/wiki/Special:MyLanguage/API:Watch';
	}
}
