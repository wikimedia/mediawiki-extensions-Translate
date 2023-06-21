<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use Exception;

/**
 * Exception thrown when a Gettext file could not be parsed, such as when missing required headers.
 * @author Michael Holloway
 * @license GPL-2.0-or-later
 */
class GettextParseException extends Exception {
}
