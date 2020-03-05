<?php
/**
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\MessageValidator\Validators;

/**
 * An insertable python interpolation validator that also acts as an InsertableSuggester
 * @since 2020.02
 */
class PythonInterpolationValidator extends InsertableRegexValidator {
	public function __construct() {
		parent::__construct( '/\%(?:\([a-zA-Z0-9_]*?\))?[diouxXeEfFgGcrs]/U' );
	}
}
