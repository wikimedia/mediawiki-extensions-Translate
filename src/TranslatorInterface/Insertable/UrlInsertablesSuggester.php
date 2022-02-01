<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Insertable;

/**
 * Suggester for URLs
 * @author Jon Harald Søby
 * @license GPL-2.0-or-later
 * @since 2022.01
 */
class UrlInsertablesSuggester implements InsertablesSuggester {
	/** @var InsertablesSuggester */
	private $suggester;

	public function __construct() {
		$this->suggester = new RegexInsertablesSuggester( [
			'regex' => '~(?<domain>(https?:)?//([\w\-]+\.)+[\w\-]{2,})(?<slug>(/[\w\-/_?&#$%()!?=.:]*|\b))?~',
			'display' => '$domain/',
			'pre' => '$domain$slug',
			'post' => '',
		] );
	}

	public function getInsertables( string $text ): array {
		return $this->suggester->getInsertables( $text );
	}
}
