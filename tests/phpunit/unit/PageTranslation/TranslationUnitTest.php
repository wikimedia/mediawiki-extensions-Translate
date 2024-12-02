<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Extension\Translate\MessageLoading\FatMessage;
use MediaWiki\Language\Language;
use MediaWiki\Parser\Parser;
use MediaWikiUnitTestCase;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\PageTranslation\TranslationUnit
 */
class TranslationUnitTest extends MediaWikiUnitTestCase {
	protected function setUp(): void {
		parent::setUp();

		if ( !defined( 'TRANSLATE_FUZZY' ) ) {
			define( 'TRANSLATE_FUZZY', '!!FUZZY!!' );
		}
	}

	/** @dataProvider providerTestGetMarkedText */
	public function testGetMarkedText(
		string $name, string $text, bool $inline, string $expected
	) {
		$section = new TranslationUnit( $text, $name );
		$section->setIsInline( $inline );
		$output = $section->getMarkedText();
		$this->assertEquals( $expected, $output );
	}

	/** @dataProvider providerTestGetTextWithVariables */
	public function testGetTextWithVariables( string $text, string $expected ) {
		$section = new TranslationUnit( $text );
		$output = $section->getTextWithVariables();
		$this->assertEquals( $expected, $output );
	}

	/** @dataProvider providerTestGetTextForTrans */
	public function testGetTextForTrans( string $text, string $expected ) {
		$section = new TranslationUnit( $text );
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
		bool $canWrap,
		string $expected
	 ) {
		$unit = new TranslationUnit( $source );
		$unit->setIsInline( $inline );
		$unit->setCanWrap( $canWrap );

		$msg = null;
		if ( $translation !== null ) {
			$msg = new FatMessage( '', $unit->getTextWithVariables() );
			$msg->setTranslation( $translation );
			if ( $fuzzy ) {
				$msg->addTag( 'fuzzy' );
			}
		}

		$sourceLanguage = $this->getLanguageStub( 'en-GB', 'en-gb', 'ltr' );
		$targetLanguage = $this->getLanguageStub( 'ar', 'ar', 'rtl' );

		$parser = $this->createStub( Parser::class );
		$parser->method( 'guessSectionNameFromWikiText' )->willReturnCallback(
			static function ( string $headingText ) {
				if ( $headingText && $headingText[0] === '#' ) {
					return '##headingId';
				}

				return '#headingId';
			}
		);

		$wrapUntranslated = true;
		$actual = $unit->getTextForRendering(
			$msg,
			$sourceLanguage,
			$targetLanguage,
			$wrapUntranslated,
			$parser
		);
		$this->assertEquals( $expected, $actual );
	}

	public static function provideTestGetTextForRendering() {
		$fuzzy = true;
		$inline = true;
		$block = false;
		$wrap = true;

		yield 'language wrapping' => [
			'Hello <tvar|abc>peter</>!',
			null,
			!$fuzzy,
			$inline,
			$wrap,
			'<span lang="en-GB" dir="ltr" class="mw-content-ltr">Hello peter!</span>'
		];

		yield 'old translation variable syntax' => [
			'Hello <tvar|abc>peter</>!',
			'Hejsan $abc!',
			!$fuzzy,
			$inline,
			$wrap,
			'Hejsan peter!'
		];

		yield 'translation variable syntax without quotes' => [
			'Hello <tvar name=abc>peter</tvar>!',
			'Hejsan $abc!',
			!$fuzzy,
			$inline,
			$wrap,
			'Hejsan peter!'
		];

		yield 'translation variable syntax with double quotes' => [
			'Hello <tvar name="abc">peter</tvar>!',
			'Hejsan $abc!',
			!$fuzzy,
			$inline,
			$wrap,
			'Hejsan peter!'
		];

		yield 'translation variable syntax with single quotes' => [
			'Hello <tvar name=\'abc\'>peter</tvar>!',
			'Hejsan $abc!',
			!$fuzzy,
			$inline,
			$wrap,
			'Hejsan peter!'
		];

		yield 'translation variable syntax with spaces' => [
			'Hello <tvar name =  abc   >peter</tvar>!',
			'Hejsan $abc!',
			!$fuzzy,
			$inline,
			$wrap,
			'Hejsan peter!'
		];

		yield 'mixed variable syntax' => [
			'Hello <tvar name=2>peter</tvar> and <tvar|1>peter</>!',
			'Hejsan $1 and $2!',
			!$fuzzy,
			$inline,
			$wrap,
			'Hejsan peter and peter!'
		];

		yield 'special characters in variable name' => [
			'Hello <tvar name=abc_123-АБВ$>peter</tvar>!',
			'Hejsan $abc_123-АБВ$!',
			!$fuzzy,
			$inline,
			$wrap,
			'Hejsan peter!'
		];

		yield 'inline fuzzy wrapping' => [
			'Hello <tvar|abc>peter</>!',
			'Hejsan $abc!',
			$fuzzy,
			$inline,
			$wrap,
			'<span class="mw-translate-fuzzy">Hejsan peter!</span>'
		];

		yield 'block language wrapping' => [
			'Hello <tvar|abc>peter</>!',
			null,
			!$fuzzy,
			$block,
			$wrap,
			"<div lang=\"en-GB\" dir=\"ltr\" class=\"mw-content-ltr\">\nHello peter!\n</div>"
		];

		yield 'block variables' => [
			'Hello <tvar name=abc>peter</tvar>!',
			'Hejsan $abc!',
			!$fuzzy,
			$block,
			$wrap,
			'Hejsan peter!'
		];

		yield 'block fuzzy wrapping' => [
			'Hello <tvar|abc>peter</>!',
			'Hejsan $abc!',
			$fuzzy,
			$block,
			$wrap,
			"<div class=\"mw-translate-fuzzy\">\nHejsan peter!\n</div>"
		];

		yield 'translation language in the source' => [
			'{{TRANSLATIONLANGUAGE}}',
			null,
			!$fuzzy,
			$inline,
			$wrap,
			'<span lang="en-GB" dir="ltr" class="mw-content-ltr">en-gb</span>'
		];

		yield 'translation language in the translation' => [
			'{{TRANSLATIONLANGUAGE}}',
			'{{TRANSLATIONLANGUAGE}}',
			$fuzzy,
			$inline,
			$wrap,
			'<span class="mw-translate-fuzzy">ar</span>'
		];

		yield 'translation language in a variable' => [
			'Lang: <tvar|code>{{TRANSLATIONLANGUAGE}}</>',
			'Lang: $code',
			!$fuzzy,
			$inline,
			$wrap,
			'Lang: ar'
		];

		yield 'anchor for heading with translation' => [
			'== Hello World ==',
			'== Hello World - ES ==',
			!$fuzzy,
			!$inline,
			$wrap,
			"<span id=\"headingId\"></span>\n== Hello World - ES =="
		];

		yield 'anchor for heading with fuzzy translation' => [
			'== Hello World ==',
			'== Hello World - ES ==',
			$fuzzy,
			!$inline,
			$wrap,
			"<span id=\"headingId\"></span>\n<div class=\"mw-translate-fuzzy\">\n== Hello World - ES ==\n</div>"
		];

		yield 'no anchor for heading without translation' => [
			'== Hello World ==',
			null,
			!$fuzzy,
			!$inline,
			$wrap,
			"<div lang=\"en-GB\" dir=\"ltr\" class=\"mw-content-ltr\">\n== Hello World ==\n</div>"
		];

		yield 'anchor is added when source string contains heading even if translation does not' => [
			'== Hello world ==',
			'This is not a heading',
			!$fuzzy,
			!$inline,
			$wrap,
			"<span id=\"headingId\"></span>\nThis is not a heading"
		];

		yield 'anchor is not added when translation contains heading but source string does not' => [
			'This has no heading',
			'== Hello world ==',
			!$fuzzy,
			!$inline,
			$wrap,
			"== Hello world =="
		];

		yield 'anchor is not added for inline translate tags containing "="' => [
			'== Hello world ==',
			'== Hello world ==',
			!$fuzzy,
			$inline,
			$wrap,
			"== Hello world =="
		];

		yield 'anchor is not added when nowrap is set' => [
			'== Hello world ==',
			'This is not a heading',
			!$fuzzy,
			!$inline,
			!$wrap,
			"This is not a heading"
		];

		yield 'anchor id has # when definition has it' => [
			'== #Hello world ==',
			'# Hello',
			!$fuzzy,
			!$inline,
			$wrap,
			"<span id=\"#headingId\"></span>\n# Hello"
		];
	}

	/** @dataProvider providerTestGetIssues */
	public function testGetIssues( $input, $expected ) {
		// FIXME: How to avoid this? It's used by wfEscapeWikitext
		global $wgEnableMagicLinks;
		$wgEnableMagicLinks = [];

		$unit = new TranslationUnit( $input );
		$issues = $unit->getIssues();
		$actual = array_map( static function ( $x ) {
			return $x->getKey();
		}, $issues );
		$this->assertArrayEquals( $expected, $actual );
	}

	public static function providerTestGetIssues() {
		// We are testing the message keys here to document the checks.
		// Severity is left untested to allow changing them easily.
		yield 'no variables - no issues' => [
			'Bunny guarding the garden',
			[],
		];

		yield 'ok variable name - no issues' => [
			'<tvar name=name>Bunny</tvar> guarding the garden',
			[],
		];

		yield 'bad insertable variable name' => [
			'Information about carrots: <tvar name=wp.org>https://en.wikipedia.org/wiki/carrot</tvar>',
			[ 'tpt-validation-not-insertable' ],
		];

		yield 'multiple names get separate issues' => [
			'<tvar name="1/2">first half</tvar><tvar name="2/2">second half</tvar>',
			[ 'tpt-validation-not-insertable', 'tpt-validation-not-insertable' ],
		];

		yield 'single repeated name only has one issue' => [
			'<tvar name="1/1">whole</tvar><tvar name="1/1">whole</tvar>',
			[ 'tpt-validation-not-insertable' ],
		];

		yield 'name reuse okay\'ish with same content' => [
			'The parameter’s value is {{#if:<tvar name="1">{{{param|}}}</tvar>|' .
				'<tvar name="1">{{{param|}}}</tvar>|not specified}}.',
			[],
		];

		yield 'name reuse not okay with different content' => [
			'Allowed values <tvar name=1>snake</tvar> and <tvar name=2>alligator</tvar>. ' .
				'When using <tvar name=1>cobra</tvar> you may hear a hissing sound.',
			[ 'tpt-validation-name-reuse' ],
		];
	}

	/** @dataProvider provideTestHeadingParsing */
	public function testHeadingParsing( string $source, string $translation, ?string $expectedHeadingText ) {
		$unit = new TranslationUnit( $source );
		$msg = new FatMessage( '', $unit->getTextWithVariables() );
		$msg->setTranslation( $translation );

		$parser = $this->createMock( Parser::class );

		if ( $expectedHeadingText === null ) {
			$parser->expects( $this->never() )
				->method( 'guessSectionNameFromWikiText' );
		} else {
			$parser->expects( $this->once() )
				->method( 'guessSectionNameFromWikiText' )
				->willReturn( '#headingId' )
				->with( $expectedHeadingText );
		}

		$wrapUntranslated = true;
		$unit->getTextForRendering(
			$msg,
			$this->getLanguageStub( 'en-GB', 'en-gb', 'ltr' ),
			$this->getLanguageStub( 'ar', 'ar', 'rtl' ),
			$wrapUntranslated,
			$parser
		);
	}

	public static function provideTestHeadingParsing() {
		yield 'parsing of heading text with balanced "="' => [
			'== Hello ==',
			'== Hello - Translated ==',
			'Hello'
		];

		yield 'parsing of heading text unbalanced "="' => [
			'===Hello ==',
			'=== Hello translated ==',
			'=Hello'
		];

		yield 'parsing of text with = but also newline' => [
			"== Heading\n ==",
			"Heading translated",
			null
		];

		yield 'parsing of normal text' => [
			'Heading',
			'Heading',
			null
		];

		yield 'parsing of heading with more than 7 "="' => [
			'======== Heading =======',
			'======== Heading Translate =======',
			'== Heading ='
		];

		yield 'anchor is skipped when translation and definition are same' => [
			'= Heading =',
			'= Heading =',
			null
		];
	}

	private function getLanguageStub( string $htmlCode, string $langCode, string $dir ) {
		$language = $this->createStub( Language::class );
		$language->method( 'getHtmlCode' )->willReturn( $htmlCode );
		$language->method( 'getCode' )->willReturn( $langCode );
		$language->method( 'getDir' )->willReturn( $dir );
		return $language;
	}

	public static function provideTestOnlyTvarsChanged() {
		yield 'no tvars"' => [
			'foo',
			'bar',
			false
		];
		yield 'tvar name changed' => [
			'<tvar name="1">Foo</tvar>',
			'<tvar name="2">Bar</tvar>',
			false
		];
		yield 'tvar content changed' => [
			'<tvar name="1">Foo</tvar>',
			'<tvar name="1">Baz</tvar>',
			true
		];
		yield 'tvar formatting changed' => [
			'<tvar|1>Foo</>',
			'<tvar name="1">Foo</tvar>',
			true
		];
	}

	/** @dataProvider provideTestOnlyTvarsChanged */
	public function testOnlyTvarsChanged( string $old, string $new, bool $expected ) {
		$unit = new TranslationUnit( $new, TranslationUnit::NEW_UNIT_ID, 'changed', $old );
		$this->assertEquals( $expected, $unit->onlyTvarsChanged() );

		// Test it the other way around too just to be sure
		$unit = new TranslationUnit( $old, TranslationUnit::NEW_UNIT_ID, 'changed', $new );
		$this->assertEquals( $expected, $unit->onlyTvarsChanged() );
	}
}
