<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

/**
 * Validator for Mozilla Fluent variable references.
 *
 * Fluent variables use the syntax { $variableName } in message patterns.
 * This validator checks that all variables present in the source message
 * are also present in the translation, and that no unknown variables are
 * introduced.
 *
 * @see https://projectfluent.org/fluent/guide/variables.html
 * @license GPL-2.0-or-later
 * @since 2026.08
 */
class FluentVariableValidator extends InsertableRegexValidator {
	public function __construct() {
		parent::__construct( '/\{\s*\$([a-zA-Z][a-zA-Z0-9_-]*)\s*\}/' );
	}
}
