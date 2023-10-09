<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use LocalizedException;

/**
 * Exception thrown when TranslatablePageMarker is unable to unmark a page for translation
 * @since 2023.10
 */
class TranslatablePageMarkException extends LocalizedException {
}
