<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use InvalidArgumentException;
use Title;
use TranslatablePage;

/**
 * Create instances of various classes based on the type of TranslatableBundle
 * @author Abijeet Patro
 * @author Niklas Laxström
 * @since 2022.03
 * @license GPL-2.0-or-later
 */
class TranslatableBundleFactory {
	/** @var TranslatablePageStore */
	private $translatablePageStore;

	public function __construct( TranslatablePageStore $translatablePageStore ) {
		$this->translatablePageStore = $translatablePageStore;
	}

	/** Returns a TranslatableBundle if Title is a valid translatable bundle else returns null */
	public function getBundle( Title $title ): ?TranslatableBundle {
		$translatablePage = TranslatablePage::newFromTitle( $title );
		if ( TranslatablePage::isSourcePage( $title ) ) {
			return $translatablePage;
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

	public function getPageMoveLogger( TranslatableBundle $bundle ): PageMoveLogger {
		if ( $bundle instanceof TranslatablePage ) {
			return new PageMoveLogger( $bundle->getTitle(), 'pagetranslation' );
		}

		throw new InvalidArgumentException( "Unknown TranslatableBundle type: " . get_class( $bundle ) );
	}

	public function getStore( TranslatableBundle $bundle ): TranslatableBundleStore {
		if ( $bundle instanceof TranslatablePage ) {
			return $this->translatablePageStore;
		}

		throw new InvalidArgumentException( "Unknown TranslatableBundle type: " . get_class( $bundle ) );
	}
}
