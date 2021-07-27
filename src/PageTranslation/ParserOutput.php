<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use InvalidArgumentException;
use Language;

/**
 * Represents a parsing output produced by TranslatablePageParser.
 *
 * It is required generate translatable and translation page sources or just get the list of
 * translations units.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2020.08
 */
class ParserOutput {
	/** @var string */
	private $template;
	/** @var Section[] */
	private $sectionMap;
	/** @var TranslationUnit[] */
	private $unitMap;

	public function __construct( string $template, array $sectionMap, array $unitMap ) {
		$this->assertContainsOnlyInstancesOf( Section::class, '$sectionMap', $sectionMap );
		$this->assertContainsOnlyInstancesOf( TranslationUnit::class, '$unitMap', $unitMap );

		$this->template = $template;
		$this->sectionMap = $sectionMap;
		$this->unitMap = $unitMap;
	}

	/** Returns template that contains <translate> tags */
	public function sourcePageTemplate(): string {
		$replacements = [];
		foreach ( $this->sectionMap as $ph => $section ) {
			$replacements[$ph] = $section->wrappedContents();
		}

		return strtr( $this->template, $replacements );
	}

	/** Returns template that does not contain <translate> tags */
	public function translationPageTemplate(): string {
		$replacements = [];
		foreach ( $this->sectionMap as $ph => $section ) {
			$replacements[$ph] = $section->contents();
		}

		return strtr( $this->template, $replacements );
	}

	/** @return TranslationUnit[] */
	public function units(): array {
		return $this->unitMap;
	}

	/** Returns the source page wikitext used for rendering the page. */
	public function sourcePageTextForRendering( Language $sourceLanguage ): string {
		$text = $this->translationPageTemplate();

		foreach ( $this->unitMap as $ph => $s ) {
			$t = $s->getTextForRendering( null, $sourceLanguage, $sourceLanguage, false );
			$text = str_replace( $ph, $t, $text );
		}

		return $text;
	}

	/** Returns the source page with translation unit markers. */
	public function sourcePageTextForSaving(): string {
		$text = $this->sourcePageTemplate();

		foreach ( $this->unitMap as $ph => $s ) {
			$text = str_replace( $ph, $s->getMarkedText(), $text );
		}

		return $text;
	}

	/** Returns the page text with translation tags and unit placeholders for easy diffs */
	public function sourcePageTemplateForDiffs(): string {
		$text = $this->sourcePageTemplate();

		foreach ( $this->unitMap as $ph => $s ) {
			$text = str_replace( $ph, "<!--T:{$s->id}-->", $text );
		}

		return $text;
	}

	private function assertContainsOnlyInstancesOf(
		string $expected,
		string $name,
		array $x
	): void {
		foreach ( $x as $item ) {
			if ( !$item instanceof $expected ) {
				$actual = gettype( $item );
				throw new InvalidArgumentException(
					"Parameter $name must only contain instances of class $expected. Got $actual."
				);
			}
		}
	}
}
