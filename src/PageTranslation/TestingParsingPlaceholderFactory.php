<?php
declare( strict_types = 1 );

namespace MediaWiki\Extensions\Translate\PageTranslation;

use MediaWiki\Extensions\Translate\Utilities\ParsingPlaceholderFactory;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2020.08
 */
class TestingParsingPlaceholderFactory extends ParsingPlaceholderFactory {
	private $i = 0;

	public function make(): string {
		return '<' . $this->i++ . '>';
	}
}
