<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use MWException;

/**
 * Translation helpers can throw this exception when they cannot do
 * anything useful with the current message. This helps in debugging
 * why some fields are not shown.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class TranslationHelperException extends MWException {
}
