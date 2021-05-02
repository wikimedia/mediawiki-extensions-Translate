<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use RuntimeException;

/**
 * Represents any kind of failure to parse a translatable page source code.
 *
 * This is an internal exception that includes information to produce translated error messages, but
 * actually displaying them to users is handlded by TPException and MediaWiki core.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2020.08
 */
class ParsingFailure extends RuntimeException {
	private $messageSpec;

	public function __construct( string $message, array $messageSpec ) {
		parent::__construct( $message );
		$this->messageSpec = $messageSpec;
	}

	public function getMessageSpecification(): array {
		return $this->messageSpec;
	}
}
