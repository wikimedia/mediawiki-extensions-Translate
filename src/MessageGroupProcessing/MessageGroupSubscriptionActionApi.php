<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiMain;
use StatusValue;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * API module for watching / stop watching a message group
 * @since 2024.04
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @ingroup API TranslateAPI
 */
class MessageGroupSubscriptionActionApi extends ApiBase {
	private MessageGroupSubscription $groupSubscription;

	public function __construct(
		ApiMain $mainModule,
		string $moduleName,
		MessageGroupSubscription $groupSubscription
	) {
		parent::__construct( $mainModule, $moduleName );
		$this->groupSubscription = $groupSubscription;
	}

	public function execute(): void {
		if ( !$this->groupSubscription->isEnabled() ) {
			$this->dieWithError( 'apierror-translate-messagegroupsubscription-disabled' );
		}

		$params = $this->extractRequestParams();

		$groupId = $params['groupId'];
		$operation = $params['operation'];

		$group = MessageGroups::getGroup( $groupId );
		if ( $group === null ) {
			$this->dieWithError( 'apierror-translate-invalidgroup', 'invalidgroup' );
		}

		$user = $this->getUser();
		if ( $operation === 'subscribe' ) {
			$status = $this->groupSubscription->subscribeToGroup( $group, $user );
			if ( !$status->isOK() ) {
				$this->handleSubscriptionFailure( $status );
			}
		} elseif ( $operation === 'unsubscribe' ) {
			$this->groupSubscription->unsubscribeFromGroup( $group, $user );
		}

		$this->getResult()->addValue(
			null,
			$this->getModuleName(),
			[
				'success' => 1,
				'group' => [
					'id' => $groupId,
					'label' => $group->getLabel( $this->getContext() )
				]
			]
		);
	}

	protected function getAllowedParams(): array {
		return [
			'groupId' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'operation' => [
				ParamValidator::PARAM_TYPE => [ 'subscribe', 'unsubscribe' ],
				ParamValidator::PARAM_ISMULTI => false,
				ParamValidator::PARAM_REQUIRED => true,
			]
		];
	}

	public function isInternal(): bool {
		return true;
	}

	public function needsToken(): string {
		return 'csrf';
	}

	private function handleSubscriptionFailure( StatusValue $status ): void {
		if ( $status->hasMessage( MessageGroupSubscription::NOT_ENABLED ) ) {
			$this->dieWithError( 'apierror-translate-messagegroupsubscription-disabled' );
		} elseif ( $status->hasMessage( MessageGroupSubscription::UNNAMED_USER_UNSUPPORTED ) ) {
			$this->dieWithError(
				[ 'apierror-mustbeloggedin', $this->msg( 'action-translate-watch-message-group' ) ]
			);
		} else {
			$this->dieWithError( 'apierror-translate-messagegroupsubscription-dynamic-group-unsupported' );
		}
	}
}
