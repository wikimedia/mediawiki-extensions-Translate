<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

/**
 * An insertable python interpolation validator that also acts as an InsertableSuggester
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2020.02
 */
class PythonInterpolationValidator extends InsertableRegexValidator {
	public function __construct() {
		parent::__construct( '/\%(?:\([a-zA-Z0-9_]*?\))?[diouxXeEfFgGcrs]/U' );
	}
}
