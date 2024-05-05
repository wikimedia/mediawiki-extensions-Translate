<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use InvalidArgumentException;
use MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundle;
use MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundleStore;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePage;
use MediaWiki\Title\Title;

/**
 * Create instances of various classes based on the type of TranslatableBundle
 * @author Abijeet Patro
 * @author Niklas Laxström
 * @since 2022.03
 * @license GPL-2.0-or-later
 */
class TranslatableBundleFactory {
	private TranslatablePageStore $translatablePageStore;
	private MessageBundleStore $messageBundleStore;

	public function __construct(
		TranslatablePageStore $translatablePageStore,
		MessageBundleStore $messageBundleStore
	) {
		$this->translatablePageStore = $translatablePageStore;
		$this->messageBundleStore = $messageBundleStore;
	}

	/** Returns a TranslatableBundle if Title is a valid translatable bundle else returns null */
	public function getBundle( Title $title ): ?TranslatableBundle {
		if ( TranslatablePage::isSourcePage( $title ) ) {
			return TranslatablePage::newFromTitle( $title );
		} elseif ( MessageBundle::isSourcePage( $title ) ) {
			return new MessageBundle( $title );
		}

		return null;
	}

	/** Return a TranslatableBundle from the Title, throwing an error if it is not a TranslatableBundle */
	public function getValidBundle( Title $title ): TranslatableBundle {
		$bundle = $this->getBundle( $title );
		if ( $bundle ) {
			return $bundle;
		}

		throw new InvalidArgumentException( "{$title->getPrefixedText()} is not a TranslatableBundle" );
	}

	public function getBundleFromClass( Title $title, string $bundleType ): TranslatableBundle {
		if ( $bundleType === MessageBundle::class ) {
			return new MessageBundle( $title );
		} else {
			return TranslatablePage::newFromTitle( $title );
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
