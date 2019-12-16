<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\MessageValidator\Validators;

/**
 * A validator that checks for missing and unknown printf formatting characters
 * in translations. Can also be used as an Insertable suggester
 * @since 2019.12
 */
class PrintfValidator extends InsertableRegexValidator {
	public function __construct() {
		parent::__construct( '/%(\d+\$)?[dfsu]/' );
	}
}
