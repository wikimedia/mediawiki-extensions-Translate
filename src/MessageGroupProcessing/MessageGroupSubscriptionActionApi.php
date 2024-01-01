<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use ApiBase;
use ApiMain;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * API module for watching message group
 * @since 2024.04
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @ingroup API TranslateAPI
 */
class MessageGroupSubscriptionActionApi extends ApiBase {
	private MessageGroupSubscription $groupSubscription;

	public function __construct(
		ApiMain $mainModule,
		$moduleName,
		MessageGroupSubscription $groupSubscription
	) {
		parent::__construct( $mainModule, $moduleName );
		$this->groupSubscription = $groupSubscription;
	}

	public function execute(): void {
		$params = $this->extractRequestParams();

		$groupId = $params['groupId'];
		$operation = $params['operation'];

		$group = MessageGroups::getGroup( $groupId );
		if ( $group === null ) {
			$this->dieWithError( 'apierror-translate-invalidgroup', 'invalidgroup' );
		}

		$user = $this->getUser();
		if ( !$user->isNamed() ) {
			$this->dieWithError(
				[ 'apierror-mustbeloggedin', $this->msg( 'action-translate-watch-message-group' ) ]
			);
		}

		if ( $operation === 'subscribe' ) {
			$this->groupSubscription->subscribeToGroup( $group, $user );
		}

		$this->getResult()->addValue( null, $this->getModuleName(), [ 'success' => 1 ] );
	}

	protected function getAllowedParams(): array {
		return [
			'groupId' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'operation' => [
				ParamValidator::PARAM_TYPE => [ 'subscribe' ],
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
}
