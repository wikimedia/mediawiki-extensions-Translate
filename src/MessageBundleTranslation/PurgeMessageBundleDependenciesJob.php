<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use Job;
use MediaWiki\Extension\Translate\LogNames;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\Page\PageReference;

/**
 * Purge parser cache for pages that use a message bundle
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2024.10
 */
class PurgeMessageBundleDependenciesJob extends Job {
	public static function newJob( PageReference $pageReference ): self {
		return new PurgeMessageBundleDependenciesJob( $pageReference );
	}

	public function __construct( PageReference $pageReference ) {
		parent::__construct( 'PurgeMessageBundleDependencies', $pageReference );
	}

	public function run(): bool {
		$logger = LoggerFactory::getInstance( LogNames::MESSAGE_BUNDLE );
		$dependencyPurger = Services::getInstance()->getMessageBundleDependencyPurger();
		$dependencyPurger->purge( $this->getTitle() );
		$logger->debug(
			'PurgeMessageBundleDependenciesJob: Completed purge for {title}',
			[ 'title' => $this->getTitle() ]
		);

		return true;
	}
}
