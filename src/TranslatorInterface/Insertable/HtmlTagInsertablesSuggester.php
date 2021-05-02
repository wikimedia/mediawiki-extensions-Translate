<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Insertable;

/**
 * Suggester for HTML tags
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2020.12
 */
class HtmlTagInsertablesSuggester implements InsertablesSuggester {
	/** @var InsertablesSuggester */
	private $suggester;

	public function __construct() {
		$this->suggester = new RegexInsertablesSuggester( [
			'regex' => '~(?<open><([a-z][a-z0-9]*)\b[^>]*>).*?(?<close></\2>)~',
			'display' => '$open$close',
			'pre' => '$open',
			'post' => '$close',
		] );
	}

	public function getInsertables( string $text ): array {
		return $this->suggester->getInsertables( $text );
	}
}
