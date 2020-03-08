<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\MessageValidator\Validators;

/**
 * An insertable numerical parameter validator that also acts as an InsertableSuggester
 * @since 2020.03
 */
class NumericalParameterValidator extends InsertableRegexValidator {
	public function __construct() {
		parent::__construct( '/\$\d+/' );
	}
}