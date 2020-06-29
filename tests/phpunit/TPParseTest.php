<?php
/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @file
 */

/**
 * @ingroup PageTranslation
 */
class TPParseTest extends MediaWikiIntegrationTestCase {
	/** @dataProvider provideTestGetTranslationPageText */
	public function testGetTranslationPageText( string $pageContents, string $expected ) {
		$title = Title::makeTitle( NS_MAIN, __CLASS__ );
		$page = TranslatablePage::newFromText( $title, $pageContents );
		$prefix = $title->getPrefixedDBkey() . '/';
		$parse = $page->getParse();

		$collection = [];
		$actual = $parse->getTranslationPageText( $collection );
		$this->assertEquals(
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
		$this->assertEquals(
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
		$this->assertEquals(
			$expected,
			$actual,
			'Variable declarations are substituted in translation'
		);
	}

	public function provideTestGetTranslationPageText() {
		yield [
			'<translate>Hello <tvar|abc>peter!</></translate>',
			'Hello peter!'
		];

		yield [
			'<translate nowrap>Hello <tvar|abc>peter!</></translate>',
			'Hello peter!'
		];
	}

	/** @dataProvider provideTestSectionWrapping */
	public function testSectionWrapping(
		bool $inline,
		bool $canWrap,
		array $messages,
		string $expected,
		string $comment
	) {
		$title = Title::makeTitle( NS_MAIN, __CLASS__ );
		$prefix = $title->getPrefixedDBkey() . '/';

		$sections = $collection = [];
		foreach ( $messages as $id => $m ) {
			/** @var FatMessage $m */
			$section = new TPSection();
			$section->id = $id;
			$section->text = $m->definition();
			$section->setIsInline( $inline );
			$section->setCanWrap( $canWrap );

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

	public function provideTestSectionWrapping() {
		$inline = true;
		$block = false;

		$wrap = true;
		$nowrap = false;

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
			$inline,
			$wrap,
			[ $okMessage ],
			'Hallo',
			'OK inline translation is not wrapped'
		];

		yield [
			$inline,
			$nowrap,
			[ $okMessage ],
			'Hallo',
			'OK inline translation is not wrapped in nowrap'
		];

		yield [
			$block,
			$wrap,
			[ $okMessage ],
			$okMessage->translation(),
			'OK block translation is not wrapped'
		];

		yield [
			$block,
			$nowrap,
			[ $okMessage ],
			$okMessage->translation(),
			'OK block translation is not wrapped in nowrap'
		];

		yield [
			$inline,
			$wrap,
			[ $fuzzyMessage ],
			"<span class=\"$fuzzyClass\">hallo</span>",
			'Fuzzy inline translation is wrapped'
		];

		yield [
			$inline,
			$nowrap,
			[ $fuzzyMessage ],
			'hallo',
			'Fuzzy inline translation is not wrapped in nowrap'
		];

		yield [
			$block,
			$wrap,
			[ $fuzzyMessage ],
			"<div class=\"$fuzzyClass\">\nhallo\n</div>",
			'Fuzzy block translation is wrapped'
		];

		yield [
			$block,
			$nowrap,
			[ $fuzzyMessage ],
			'hallo',
			'Fuzzy block translation is not wrapped in nowrap'
		];

		yield [
			$inline,
			$wrap,
			[ $identicalMessage ],
			'Hello',
			'Identically translated inline message is not wrapped'
		];

		yield [
			$block,
			$nowrap,
			[ $identicalMessage ],
			'Hello',
			'Identically translated block message is not wrapped in nowrap'
		];

		yield [
			$inline,
			$wrap,
			[ $okMessage, $fuzzyMessage, $identicalMessage, $untranslatedMessage ],
			"Hallo | <span class=\"$fuzzyClass\">hallo</span> | Hello | Hello",
			'Different kinds of inline messages together are appropriately wrapped'
		];

		yield [
			$inline,
			$nowrap,
			[ $okMessage, $fuzzyMessage, $identicalMessage, $untranslatedMessage ],
			'Hallo | hallo | Hello | Hello',
			'Different kinds of inline messages together are not wrapped in nowrap'
		];

		$blockText = <<<WIKITEXT
Hallo

<div class="{$fuzzyClass}">
hallo
</div>

Hello

Hello
WIKITEXT;

		yield [
			$block,
			$wrap,
			[ $okMessage, $fuzzyMessage, $identicalMessage, $untranslatedMessage ],
			$blockText,
			'Different kinds of block messages together are wrapped appropriately'
		];
	}
}
