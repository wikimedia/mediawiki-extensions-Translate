<?php
/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @file
 */

/**
 * @ingroup PageTranslation
 * @covers \TPParse
 */
class TPParseTest extends MediaWikiIntegrationTestCase {
	public function testGetTranslationPageText() {
		$title = Title::newFromText( __CLASS__ );
		$page = TranslatablePage::newFromText(
			$title,
			'<translate>Hello <tvar|abc>peter!</></translate>'
		);
		$prefix = $title->getPrefixedDBkey() . '/';
		$parse = $page->getParse();

		$collection = [];
		$expected = 'Hello peter!';

		$actual = $parse->getTranslationPageText( $collection );
		$this->assertStringContainsString(
			$expected,
			$actual,
			'Variable declarations are substituted when no translation'
		);

		foreach ( $parse->sections as $section ) {
			$key = $prefix . $section->id;
			$message = new FatMessage( $key, $section->getText() );
			$message->setTranslation( $section->getText() );
			$collection[$key] = $message;
		}

		$actual = $parse->getTranslationPageText( $collection );
		$this->assertStringContainsString(
			$expected,
			$actual,
			'Variable declarations are substituted in source language'
		);

		foreach ( $parse->sections as $section ) {
			$key = $prefix . $section->id;
			$message = new FatMessage( $key, $section->getText() );
			$message->setTranslation( $section->getTextForTrans() );
			$collection[$key] = $message;
		}
		$actual = $parse->getTranslationPageText( $collection );
		$this->assertStringContainsString(
			$expected,
			$actual,
			'Variable declarations are substituted in translation'
		);
	}

	/**
	 * @dataProvider provideTestSectionWrapping
	 */
	public function testSectionWrapping(
		bool $inline,
		array $messages,
		string $expected,
		string $comment
	) {
		$title = Title::newFromText( __METHOD__ );
		$prefix = $title->getPrefixedDBkey() . '/';

		$sections = $collection = [];
		foreach ( $messages as $id => $m ) {
			/** @var FatMessage $m */
			$section = new TPSection();
			$section->id = $id;
			$section->text = $m->definition();
			$section->setIsInline( $inline );

			$sections[$id] = $section;
			$collection[$prefix . $id] = $m;
		}

		$parse = new TPParse( $title );
		$parse->sections = $sections;

		$glue = $inline ? ' | ' : "\n\n";
		$parse->template = implode( $glue, array_keys( $sections ) );

		$actual = $parse->getTranslationPageText( $collection );
		$this->assertSame(
			$expected,
			$actual,
			$comment
		);
	}

	private const INLINE = true;
	private const BLOCK = false;

	public function provideTestSectionWrapping() {
		$sectionText = 'Hello';
		$fuzzyClass = 'mw-translate-fuzzy';

		$okMessage = new FatMessage( 'ignoredKey', $sectionText );
		$okMessage->setTranslation( 'Hallo' );

		$fuzzyMessage = new FatMessage( 'ignoredKey', $sectionText );
		$fuzzyMessage->setTranslation( 'hallo' );
		$fuzzyMessage->addTag( 'fuzzy' );

		$untranslatedMessage = new FatMessage( 'ignoredKey', $sectionText );

		$identicalMessage = new FatMessage( 'ignoredKey', $sectionText );
		$identicalMessage->setTranslation( $sectionText );

		yield [
			self::INLINE,
			[ $okMessage ],
			'Hallo',
			'OK inline translation is not wrapped'
		];

		yield [
			self::BLOCK,
			[ $okMessage ],
			$okMessage->translation(),
			'OK block translation is not wrapped'
		];

		yield [
			self::INLINE,
			[ $fuzzyMessage ],
			"<span class=\"$fuzzyClass\">hallo</span>",
			'Fuzzy inline translation is wrapped'
		];

		yield [
			self::BLOCK,
			[ $fuzzyMessage ],
			"<div class=\"$fuzzyClass\">\nhallo\n</div>",
			'Fuzzy block translation is wrapped'
		];

		yield [
			self::INLINE,
			[ $untranslatedMessage ],
			"<span lang=\"en\" dir=\"ltr\">Hello</span>",
			'Untranslated inline message is wrapped'
		];

		yield [
			self::BLOCK,
			[ $untranslatedMessage ],
			"<div lang=\"en\" dir=\"ltr\">\nHello\n</div>",
			'Untranslated block message is wrapped'
		];

		yield [
			self::INLINE,
			[ $identicalMessage ],
			'Hello',
			'Identically translated inline message is not wrapped'
		];

		yield [
			self::BLOCK,
			[ $identicalMessage ],
			'Hello',
			'Identically translated block message is not wrapped'
		];

		yield [
			self::INLINE,
			[ $okMessage, $fuzzyMessage, $identicalMessage, $untranslatedMessage ],
			"Hallo | <span class=\"$fuzzyClass\">hallo</span> | Hello | <span lang=\"en\" dir=\"ltr\">Hello</span>",
			'Differents kinds of inline messages together'
		];

		$blockText = <<<WIKITEXT
Hallo

<div class="{$fuzzyClass}">
hallo
</div>

Hello

<div lang="en" dir="ltr">
Hello
</div>
WIKITEXT;

		yield [
			self::BLOCK,
			[ $okMessage, $fuzzyMessage, $identicalMessage, $untranslatedMessage ],
			$blockText,
			'Differents kinds of inline messages together'
		];
	}
}
