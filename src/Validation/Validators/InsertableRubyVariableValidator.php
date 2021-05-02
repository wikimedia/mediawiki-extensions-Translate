<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

/**
 * An insertable Ruby variable validator that also acts as an InsertableSuggester
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2019.06
 */
class InsertableRubyVariableValidator extends InsertableRegexValidator {
	public function __construct() {
		parent::__construct( '/%{[a-zA-Z_]+}/' );
	}
}
