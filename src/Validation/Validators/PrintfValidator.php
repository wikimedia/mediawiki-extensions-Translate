<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

/**
 * A validator that checks for missing and unknown printf formatting characters
 * in translations. Can also be used as an Insertable suggester
 * @license GPL-2.0-or-later
 * @since 2019.12
 */
class PrintfValidator extends InsertableRegexValidator {
	public function __construct() {
		parent::__construct( '/%(\d+\$)?(\.\d+)?[sduf]/U' );
	}
}
