<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Extension\Translate\Utilities\ParsingPlaceholderFactory;

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
