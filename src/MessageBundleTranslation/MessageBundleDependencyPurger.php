<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleFactory;
use MediaWiki\Title\Title;

/**
 * Purges titles dependent on a particular message bundle
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2024.10
 */
class MessageBundleDependencyPurger {
	private TranslatableBundleFactory $bundleFactory;

	public function __construct( TranslatableBundleFactory $bundleFactory ) {
		$this->bundleFactory = $bundleFactory;
	}

	public function purge( Title $messageBundleTitle ): void {
		// Ensure we are dealing with valid message bundle
		$messageBundle = $this->bundleFactory->getValidBundle( $messageBundleTitle );
		$offset = 0;

		do {
			$titlesWithThisMessageBundle = $messageBundle->getTitle()->getTemplateLinksTo(
				[ 'LIMIT' => 500, 'OFFSET' => $offset ]
			);

			foreach ( $titlesWithThisMessageBundle as $title ) {
				$title->invalidateCache();
			}

			$offset += 500;
		} while ( count( $titlesWithThisMessageBundle ) === 500 );
	}
}
