<?php
/**
 * Interface to be implemented by Validators.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\MessageValidator;

use TMessage;

/**
 * Interface class built to be implement by validators
 * @since 2019.06
 */
interface Validator {
	public function validate( TMessage $message, $code, array &$notices );
}
