<?php
/**
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\MessageValidator\Validators;

/**
 * An insertable Ruby variable validator that is also acts as a Suggester
 * @since 2019.04
 */
class InsertableRubyVariableValidator extends InsertableRegexValidator {
	public function __construct() {
		parent::__construct( '/%{[a-zA-Z_]+}/' );
	}
}
