<?php
/**
 * Interface to be implemented by Validators.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\MessageValidator;

/**
 * Interface class built to be implement by validators
 * @since 2019.05
 */
interface Validator {
	public function validate( $messages, $code, array &$notices );
}
