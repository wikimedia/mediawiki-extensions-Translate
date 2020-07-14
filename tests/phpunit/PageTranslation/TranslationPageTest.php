<?php
declare( strict_types = 1 );

namespace MediaWiki\Extensions\Translate\PageTranslation;

use FatMessage;
use Language;
use MediaWikiTestCase;
use TPSection;
use WikiPageMessageGroup;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extensions\Translate\PageTranslation\TranslationPage
 */
class TranslationPageTest extends MediaWikiTestCase {
	/** @dataProvider provideTestGenerateSourceFromTranslations */
	public function testGenerateSourceFromTranslations(
		bool $inline,
		bool $canWrap,
		array $messages,
		string $expected,
		string $comment
	) {
		// This test skips all the message loading from database

		$template = '<S>';
		$unitMap = [];
		foreach ( $messages as $id => $m ) {
			/** @var FatMessage $m */
			$unit = new TPSection();
			$unit->id = $id;
			$unit->text = $m->definition();
			$unit->setIsInline( $inline );
			$unit->setCanWrap( $canWrap );

			$unitMap[$unit->id] = $unit;
		}

		// Then create appropriate units in the section. We are using the array keys, which
		// works as long as there are less than ten units.
		$glue = $inline ? ' | ' : "\n\n";
		$sectionMap = [ '<S>' => new Section( '', implode( $glue, array_keys( $unitMap ) ), '' ) ];
		$output = new ParserOutput( $template, $sectionMap, $unitMap );

		$translationPage = new TranslationPage(
			$output,
			$this->createMock( WikiPageMessageGroup::class ),
			Language::factory( 'ar' ),
			Language::factory( 'en' ),
			true /*$showOutdated*/,
			false /*$wrapUntranslated*/,
			'' /*$prefix*/
		);

		$actual = $translationPage->generateSourceFromTranslations( $messages );
		$this->assertSame(
			$expected,
			$actual,
			$comment
		);
	}

	public function provideTestGenerateSourceFromTranslations() {
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
