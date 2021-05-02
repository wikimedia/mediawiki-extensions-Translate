<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities;

/**
 * Create unique placeholders that can be used when parsing (wiki)text.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2020.07
 */
class ParsingPlaceholderFactory {
	private $i = 0;

	/** Return value is guaranteed to only contain [a-zA-Z0-9\x7f] */
	public function make(): string {
		return "\x7fUNIQ" .
			dechex( mt_rand( 0, 0x7fffffff ) ) .
			dechex( mt_rand( 0, 0x7fffffff ) ) .
			'-' .
			$this->i++;
	}
}
