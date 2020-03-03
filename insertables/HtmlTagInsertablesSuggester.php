<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Suggester for HTML tags
 * @since 2020.03
 */
class HtmlTagInsertablesSuggester implements InsertablesSuggester {
	/** @var InsertablesSuggester */
	private $suggester = null;

	public function __construct() {
		$this->suggester = new RegexInsertablesSuggester( [
			'regex' => '~(?<open><([a-z][a-z0-9]*)\b[^>]*>).*?(?<close></\2>)~',
			'display' => '$open$close',
			'pre' => '$open',
			'post' => '$close',
		] );
	}

	public function getInsertables( $text ) {
		return $this->suggester->getInsertables( $text );
	}
}
