<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

/**
 * An insertable wiki parameter validator that also acts as an InsertableSuggester
 * @license GPL-2.0-or-later
 * @since 2019.12
 */
class MediaWikiParameterValidator extends InsertableRegexValidator {
	public function __construct() {
		parent::__construct( '/\$[1-9]/' );
	}
}
