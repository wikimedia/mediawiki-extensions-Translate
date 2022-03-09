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

	/** From the title create a bundle of the same type as the other TranslatableBundle passed */
	public function getSameAsInstance( TranslatableBundle $instance, Title $title ): TranslatableBundle {
		if ( $instance instanceof TranslatablePage ) {
			return TranslatablePage::newFromTitle( $title );
		}

		throw new InvalidArgumentException(
			'Expected a class implementing TranslatableBundle, got ' . get_class( $instance )
		);
	}
}
