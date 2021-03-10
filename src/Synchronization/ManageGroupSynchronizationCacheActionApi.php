<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use ApiBase;
use ApiMain;
use Exception;
use FormatJson;
use MediaWiki\Logger\LoggerFactory;
use MessageGroups;

/**
 * Api module for managing group synchronization cache
 * @ingroup API TranslateAPI
 * @since 2021.03
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */
class ManageGroupSynchronizationCacheActionApi extends ApiBase {
	private const RIGHT = 'translate-manage';
	private const VALID_OPS = [ 'resolveMessage', 'resolveGroup' ];
	/** @var GroupSynchronizationCache */
	private $groupSyncCache;

	public function __construct( ApiMain $mainModule, $moduleName, GroupSynchronizationCache $groupSyncCache ) {
		parent::__construct( $mainModule, $moduleName );
		$this->groupSyncCache = $groupSyncCache;
	}

	public function execute() {
		$this->checkUserRightsAny( self::RIGHT );

		$params = $this->extractRequestParams();
		$operation = $params['operation'];
		$groupId = $params['group'];
		$titleStr = $params['title'] ?? null;

		$group = MessageGroups::getGroup( $groupId );
		if ( $group === null ) {
			return $this->dieWithError( 'apierror-translate-invalidgroup', 'invalidgroup' );
		}

		try {
			if ( $operation === 'resolveMessage' ) {
				if ( $titleStr === null ) {
					return $this->dieWithError( [ 'apierror-missingparam', 'title' ] );
				}
				$this->markAsResolved( $groupId, $titleStr );
			} elseif ( $operation === 'resolveGroup' ) {
				$this->markAsResolved( $groupId );
			}
		} catch ( Exception $e ) {
			$data = [
				'requestParams' => $params,
				'exceptionMessage' => $e->getMessage()
			];

			LoggerFactory::getInstance( 'Translate.GroupSynchronization' )->error(
				"Error while running: ManageGroupSynchronizationCacheActionApi::execute. Details: \n" .
				FormatJson::encode( $data, true )
			);

			$this->dieWithError(
				[
					'apierror-translate-operation-error',
					wfEscapeWikiText( $e->getMessage() )
				]
			);
		}
	}

	private function markAsResolved( string $groupId, ?string $messageTitle = null ): void {
		if ( $messageTitle === null ) {
			$currentGroupStatus = $this->groupSyncCache->markGroupAsResolved( $groupId );
		} else {
			$this->groupSyncCache->markMessageAsResolved( $groupId, $messageTitle );
			$currentGroupStatus = $this->groupSyncCache->syncGroupErrors( $groupId );
		}

		$this->getResult()->addValue( null, $this->getModuleName(), [
			'success' => 1,
			'data' => [
				'groupRemainingMessageCount' => count( $currentGroupStatus->getRemainingMessages() )
			]
		] );
	}

	protected function getAllowedParams() {
		return [
			'operation' => [
				ApiBase::PARAM_TYPE => self::VALID_OPS,
				ApiBase::PARAM_ISMULTI => false,
				ApiBase::PARAM_REQUIRED => true,
			],
			'title' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false
			],
			'group' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			]
		];
	}

	public function isInternal() {
		return true;
	}

	public function needsToken() {
		return 'csrf';
	}
}
