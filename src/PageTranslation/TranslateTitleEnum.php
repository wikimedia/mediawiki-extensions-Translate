<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

/**
 * Enum for the possible states of the "Translate page titles" checkbox.
 *
 * @author MusikAnimal
 * @license GPL-2.0-or-later
 * @since 2025.10
 */
enum TranslateTitleEnum {
	case DEFAULT_CHECKED;
	case DEFAULT_UNCHECKED;
	case DISABLED;
}
