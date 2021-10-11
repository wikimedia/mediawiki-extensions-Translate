<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use FatMessage;
use Language;
use MediaWikiIntegrationTestCase;
use Title;
use WikiPageMessageGroup;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\PageTranslation\TranslationPage
 */
class TranslationPageTest extends MediaWikiIntegrationTestCase {
	/** @dataProvider provideTestGenerateSourceFromTranslations */
	public function testGenerateSourceFromTranslations(
		bool $inline,
		bool $canWrap,
		bool $wrapUntranslated,
		array $messages,
		string $expected
	) {
		// This test skips all the message loading from database

		$template = '<S>';
		$unitMap = [];
		foreach ( $messages as $id => $m ) {
			/** @var FatMessage $m */
			$unit = new TranslationUnit( $m->definition(), (string)$id );
			$unit->setIsInline( $inline );
			$unit->setCanWrap( $canWrap );

			$unitMap[$unit->id] = $unit;
		}

		// Then create appropriate units in the section. We are using the array keys, which
		// works as long as there are less than ten units.
		$sectionMap = [ '<S>' => new Section( '', implode( ' | ', array_keys( $unitMap ) ), '' ) ];
		$output = new ParserOutput( $template, $sectionMap, $unitMap );

		$translationPage = new TranslationPage(
			$output,
			$this->createMock( WikiPageMessageGroup::class ),
			Language::factory( 'ar' ),
			Language::factory( 'en' ),
			true /*$showOutdated*/,
			$wrapUntranslated,
			Title::newFromText( __METHOD__ )
		);

		$actual = $translationPage->generateSourceFromTranslations( $messages );
		$this->assertSame( $expected, $actual );
	}

	public function provideTestGenerateSourceFromTranslations() {
		$inline = true;
		$block = false;

		$wrap = true;
		$nowrap = false;

		$wrapUntranslated = true;

		$sectionText = 'Hello';

		$okMessage = new FatMessage( 'ignoredKey', $sectionText );
		$okMessage->setTranslation( 'Hallo' );

		$fuzzyMessage = new FatMessage( 'ignoredKey', $sectionText );
		$fuzzyMessage->setTranslation( 'hallo' );
		$fuzzyMessage->addTag( 'fuzzy' );

		$untranslatedMessage = new FatMessage( 'ignoredKey', $sectionText );

		$identicalMessage = new FatMessage( 'ignoredKey', $sectionText );
		$identicalMessage->setTranslation( $sectionText );

		$inlineWrappedOutdated = '<span class="mw-translate-fuzzy">hallo</span>';
		$inlineWrappedUntranslated = '<span lang="en" dir="ltr" class="mw-content-ltr">Hello</span>';
		$blockWrappedOutdated = "<div class=\"mw-translate-fuzzy\">\nhallo\n</div>";
		$blockWrappedUntranslated = "<div lang=\"en\" dir=\"ltr\" class=\"mw-content-ltr\">\nHello\n</div>";

		// Matrix of (inline|block) * (no)wrap * (no)wrapUntranslated
		yield [
			$inline,
			$wrap,
			$wrapUntranslated,
			[ $okMessage, $fuzzyMessage, $identicalMessage, $untranslatedMessage ],
			"Hallo | $inlineWrappedOutdated | Hello | $inlineWrappedUntranslated"
		];

		yield [
			$inline,
			$wrap,
			!$wrapUntranslated,
			[ $okMessage, $fuzzyMessage, $identicalMessage, $untranslatedMessage ],
			"Hallo | $inlineWrappedOutdated | Hello | Hello"
		];

		yield [
			$inline,
			$nowrap,
			$wrapUntranslated,
			[ $okMessage, $fuzzyMessage, $identicalMessage, $untranslatedMessage ],
			'Hallo | hallo | Hello | Hello',
		];

		yield [
			$inline,
			$nowrap,
			!$wrapUntranslated,
			[ $okMessage, $fuzzyMessage, $identicalMessage, $untranslatedMessage ],
			'Hallo | hallo | Hello | Hello',
		];

		yield [
			$block,
			$wrap,
			$wrapUntranslated,
			[ $okMessage, $fuzzyMessage, $identicalMessage, $untranslatedMessage ],
			"Hallo | $blockWrappedOutdated | Hello | $blockWrappedUntranslated"
		];

		yield [
			$block,
			$wrap,
			!$wrapUntranslated,
			[ $okMessage, $fuzzyMessage, $identicalMessage, $untranslatedMessage ],
			"Hallo | $blockWrappedOutdated | Hello | Hello"
		];

		yield [
			$block,
			$nowrap,
			$wrapUntranslated,
			[ $okMessage, $fuzzyMessage, $identicalMessage, $untranslatedMessage ],
			'Hallo | hallo | Hello | Hello',
		];

		yield [
			$block,
			$nowrap,
			!$wrapUntranslated,
			[ $okMessage, $fuzzyMessage, $identicalMessage, $untranslatedMessage ],
			'Hallo | hallo | Hello | Hello',
		];
	}
}
