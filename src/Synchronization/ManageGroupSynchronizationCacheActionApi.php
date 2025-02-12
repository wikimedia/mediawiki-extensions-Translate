<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use Exception;
use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiMain;
use MediaWiki\Extension\Translate\LogNames;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Json\FormatJson;
use MediaWiki\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;
use Wikimedia\ParamValidator\ParamValidator;

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
	private GroupSynchronizationCache $groupSyncCache;
	private LoggerInterface $groupSyncLog;

	public function __construct(
		ApiMain $mainModule,
		string $moduleName,
		GroupSynchronizationCache $groupSyncCache
	) {
		parent::__construct( $mainModule, $moduleName );
		$this->groupSyncCache = $groupSyncCache;
		$this->groupSyncLog = LoggerFactory::getInstance( LogNames::GROUP_SYNCHRONIZATION );
	}

	public function execute() {
		$this->checkUserRightsAny( self::RIGHT );
		$block = $this->getUser()->getBlock();
		if ( $block && $block->isSitewide() ) {
			$this->dieBlocked( $block );
		}

		$params = $this->extractRequestParams();
		$operation = $params['operation'];
		$groupId = $params['group'];
		$titleStr = $params['title'] ?? null;

		$group = MessageGroups::getGroup( $groupId );
		if ( $group === null ) {
			$this->dieWithError( 'apierror-translate-invalidgroup', 'invalidgroup' );
		}

		try {
			if ( $operation === 'resolveMessage' ) {
				if ( $titleStr === null ) {
					$this->dieWithError( [ 'apierror-missingparam', 'title' ] );
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

			$this->groupSyncLog->error(
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
			$this->groupSyncLog->info(
				'{user} resolved group {groupId}.',
				[
					'user' => $this->getUser()->getName(),
					'groupId' => $groupId
				]
			);
		} else {
			$this->groupSyncCache->markMessageAsResolved( $groupId, $messageTitle );
			$currentGroupStatus = $this->groupSyncCache->syncGroupErrors( $groupId );
			$this->groupSyncLog->info(
				'{user} resolved message {messageTitle} in group {groupId}.',
				[
					'user' => $this->getUser()->getName(),
					'groupId' => $groupId,
					'messageTitle' => $messageTitle
				]
			);
		}

		$this->getResult()->addValue( null, $this->getModuleName(), [
			'success' => 1,
			'data' => [
				'groupRemainingMessageCount' => count( $currentGroupStatus->getRemainingMessages() )
			]
		] );
	}

	protected function getAllowedParams(): array {
		return [
			'operation' => [
				ParamValidator::PARAM_TYPE => self::VALID_OPS,
				ParamValidator::PARAM_ISMULTI => false,
				ParamValidator::PARAM_REQUIRED => true,
			],
			'title' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false
			],
			'group' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true
			]
		];
	}

	/** @inheritDoc */
	public function isInternal() {
		return true;
	}

	/** @inheritDoc */
	public function needsToken() {
		return 'csrf';
	}
}
