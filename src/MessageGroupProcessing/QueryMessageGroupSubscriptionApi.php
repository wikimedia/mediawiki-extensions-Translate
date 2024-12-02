<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Api\ApiQuery;
use MediaWiki\Api\ApiQueryBase;

/**
 * API module to query message groups watched by the current user.
 * @since 2024.06
 * @author Eugene Wang'ombe
 * @license GPL-2.0-or-later
 * @ingroup API TranslateAPI
 */
class QueryMessageGroupSubscriptionApi extends ApiQueryBase {
	private MessageGroupSubscription $groupSubscription;

	public function __construct(
		ApiQuery $queryModule,
		string $moduleName,
		MessageGroupSubscription $groupSubscription
	) {
		parent::__construct( $queryModule, $moduleName, 'qmgs' );
		$this->groupSubscription = $groupSubscription;
	}

	public function execute(): void {
		if ( !$this->groupSubscription->isEnabled() ) {
			$this->dieWithError( 'apierror-translate-messagegroupsubscription-disabled' );
		}
		$watchedMessageGroups = $this->groupSubscription->getUserSubscriptions( $this->getUser() );
		$result = $this->getResult();
		$result->addValue( [ 'query' ], $this->getModuleName(), $watchedMessageGroups );
	}

	public function isInternal(): bool {
		return true;
	}
}
