<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use InvalidArgumentException;
use MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundle;
use MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundleStore;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePage;
use MediaWiki\Page\PageIdentity;

/**
 * Create instances of various classes based on the type of TranslatableBundle
 * @author Abijeet Patro
 * @author Niklas Laxström
 * @since 2022.03
 * @license GPL-2.0-or-later
 */
class TranslatableBundleFactory {

	public function __construct(
		private readonly TranslatablePageStore $translatablePageStore,
		private readonly MessageBundleStore $messageBundleStore,
	) {
	}

	/** Returns a TranslatableBundle if Title is a valid translatable bundle else returns null */
	public function getBundle( PageIdentity $page ): ?TranslatableBundle {
		if ( TranslatablePage::isSourcePage( $page ) ) {
			return TranslatablePage::newFromTitle( $page );
		} elseif ( MessageBundle::isSourcePage( $page ) ) {
			return new MessageBundle( $page );
		}

		return null;
	}

	/** Return a TranslatableBundle from the Title, throwing an error if it is not a TranslatableBundle */
	public function getValidBundle( PageIdentity $page ): TranslatableBundle {
		$bundle = $this->getBundle( $page );
		if ( $bundle ) {
			return $bundle;
		}

		throw new InvalidArgumentException( "$page is not a TranslatableBundle" );
	}

	public function getBundleFromClass( PageIdentity $page, string $bundleType ): TranslatableBundle {
		if ( $bundleType === MessageBundle::class ) {
			return new MessageBundle( $page );
		} else {
			return TranslatablePage::newFromTitle( $page );
		}
	}

	public function getPageMoveLogger( TranslatableBundle $bundle ): PageMoveLogger {
		if ( $bundle instanceof TranslatablePage ) {
			return new PageMoveLogger( $bundle->getTitle(), 'pagetranslation' );
		} elseif ( $bundle instanceof MessageBundle ) {
			return new PageMoveLogger( $bundle->getTitle(), 'messagebundle' );
		}

		throw new InvalidArgumentException( 'Unknown TranslatableBundle type: ' . get_class( $bundle ) );
	}

	public function getPageDeleteLogger( TranslatableBundle $bundle ): PageDeleteLogger {
		if ( $bundle instanceof TranslatablePage ) {
			return new PageDeleteLogger( $bundle->getTitle(), 'pagetranslation' );
		} elseif ( $bundle instanceof MessageBundle ) {
			return new PageDeleteLogger( $bundle->getTitle(), 'messagebundle' );
		}

		throw new InvalidArgumentException( 'Unknown TranslatableBundle type: ' . get_class( $bundle ) );
	}

	public function getStore( TranslatableBundle $bundle ): TranslatableBundleStore {
		if ( $bundle instanceof TranslatablePage ) {
			return $this->translatablePageStore;
		} elseif ( $bundle instanceof MessageBundle ) {
			return $this->messageBundleStore;
		}

		throw new InvalidArgumentException( "Unknown TranslatableBundle type: " . get_class( $bundle ) );
	}
}
