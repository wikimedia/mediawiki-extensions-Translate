<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use FatMessage;
use Language;
use MediaWikiUnitTestCase;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\PageTranslation\TranslationUnit
 */
class TranslationUnitTest extends MediaWikiUnitTestCase {
	public function setUp(): void {
		parent::setUp();

		if ( !defined( 'TRANSLATE_FUZZY' ) ) {
			define( 'TRANSLATE_FUZZY', '!!FUZZY!!' );
		}
	}

	/** @dataProvider providerTestGetMarkedText */
	public function testGetMarkedText(
		string $name, string $text, bool $inline, string $expected
	) {
		$section = new TranslationUnit();
		$section->name = $name;
		$section->text = $text;
		$section->setIsInline( $inline );

		$output = $section->getMarkedText();

		$this->assertEquals( $expected, $output );
	}

	/** @dataProvider providerTestGetTextWithVariables */
	public function testGetTextWithVariables( string $text, string $expected ) {
		$section = new TranslationUnit();
		$section->text = $text;

		$output = $section->getTextWithVariables();

		$this->assertEquals( $expected, $output );
	}

	/** @dataProvider providerTestGetTextForTrans */
	public function testGetTextForTrans( string $text, string $expected ) {
		$section = new TranslationUnit();
		$section->text = $text;

		$output = $section->getTextForTrans();

		$this->assertEquals( $expected, $output );
	}

	public static function providerTestGetMarkedText() {
		$cases = [];

		// Inline syntax
		$cases[] = [
			'name',
			'Hello',
			true,
			'<!--T:name--> Hello',
		];

		// Normal syntax
		$cases[] = [
			'name',
			'Hello',
			false,
			"<!--T:name-->\nHello",
		];

		// Inline should not matter for headings, which have special syntax, but test both values
		$cases[] = [
			'name',
			'== Hello ==',
			true,
			'== Hello == <!--T:name-->',
		];

		$cases[] = [
			'name',
			'====== Hello ======',
			false,
			'====== Hello ====== <!--T:name-->',
		];

		return $cases;
	}

	public static function providerTestGetTextWithVariables() {
		$cases = [];

		// syntax
		$cases[] = [
			"<tvar|abc>Peter\n cat!</>",
			'$abc',
		];

		$cases[] = [
			"<tvar|1>Hello</>\n<tvar|2>Hello</>",
			"$1\n$2",
		];

		return $cases;
	}

	public static function providerTestGetTextForTrans() {
		$cases = [];

		// syntax
		$cases[] = [
			"<tvar|abc>Peter\n cat!</>",
			"Peter\n cat!",
		];

		$cases[] = [
			"<tvar|1>Hello</>\n<tvar|2>Hello</>",
			"Hello\nHello",
		];

		return $cases;
	}

	/** @dataProvider provideTestGetTextForRendering */
	public function testGetTextForRendering(
		string $source,
		?string $translation,
		bool $fuzzy,
		bool $inline,
		string $expected
	 ) {
		$unit = new TranslationUnit();
		$unit->text = $source;
		$unit->setIsInline( $inline );

		$msg = null;
		if ( $translation !== null ) {
			$msg = new FatMessage( '', $unit->getTextWithVariables() );
			$msg->setTranslation( $translation );
			if ( $fuzzy ) {
				$msg->addTag( 'fuzzy' );
			}
		}

		$sourceLanguage = $this->createStub( Language::class );
		$sourceLanguage->method( 'getHtmlCode' )->willReturn( 'en-GB' );
		$sourceLanguage->method( 'getCode' )->willReturn( 'en-gb' );
		$sourceLanguage->method( 'getDir' )->willReturn( 'ltr' );

		$targetLanguage = $this->createStub( Language::class );
		$targetLanguage->method( 'getHtmlCode' )->willReturn( 'ar' );
		$targetLanguage->method( 'getCode' )->willReturn( 'ar' );
		$targetLanguage->method( 'getDir' )->willReturn( 'rtl' );

		$wrapUntranslated = true;
		$actual = $unit->getTextForRendering(
			$msg,
			$sourceLanguage,
			$targetLanguage,
			$wrapUntranslated
		);
		$this->assertEquals( $expected, $actual );
	}

	public function provideTestGetTextForRendering() {
		$fuzzy = true;
		$inline = true;
		$block = false;

		yield [
			'Hello <tvar|abc>peter</>!',
			null,
			!$fuzzy,
			$inline,
			'<span lang="en-GB" dir="ltr" class="mw-content-ltr">Hello peter!</span>'
		];

		yield [
			'Hello <tvar|abc>peter</>!',
			'Hejsan $abc!',
			!$fuzzy,
			$inline,
			'Hejsan peter!'
		];

		yield [
			'Hello <tvar|abc>peter</>!',
			'Hejsan $abc!',
			$fuzzy,
			$inline,
			'<span class="mw-translate-fuzzy">Hejsan peter!</span>'
		];

		yield [
			'Hello <tvar|abc>peter</>!',
			null,
			!$fuzzy,
			$block,
			"<div lang=\"en-GB\" dir=\"ltr\" class=\"mw-content-ltr\">\nHello peter!\n</div>"
		];

		yield [
			'Hello <tvar|abc>peter</>!',
			'Hejsan $abc!',
			!$fuzzy,
			$block,
			'Hejsan peter!'
		];

		yield [
			'Hello <tvar|abc>peter</>!',
			'Hejsan $abc!',
			$fuzzy,
			$block,
			"<div class=\"mw-translate-fuzzy\">\nHejsan peter!\n</div>"
		];

		yield [
			'{{TRANSLATIONLANGUAGE}}',
			null,
			!$fuzzy,
			$inline,
			'<span lang="en-GB" dir="ltr" class="mw-content-ltr">en-gb</span>'
		];

		yield [
			'{{TRANSLATIONLANGUAGE}}',
			'{{TRANSLATIONLANGUAGE}}',
			$fuzzy,
			$inline,
			'<span class="mw-translate-fuzzy">ar</span>'
		];

		yield [
			'Lang: <tvar|code>{{TRANSLATIONLANGUAGE}}</>',
			'Lang: $code',
			!$fuzzy,
			$inline,
			'Lang: ar'
		];
	}
}
