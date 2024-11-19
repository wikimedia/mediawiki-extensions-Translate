<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Linker\LinkTarget;
use MediaWiki\Title\TitleValue;
use MediaWikiUnitTestCase;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\PageTranslation\TranslatablePage
 */
class TranslatablePageTest extends MediaWikiUnitTestCase {
	/** @dataProvider provideTestParseTranslationUnit */
	public function testParseTranslationUnit( LinkTarget $input, array $expected ) {
		$output = TranslatablePage::parseTranslationUnit( $input );
		$this->assertEquals( $expected, $output );
	}

	public static function provideTestParseTranslationUnit() {
		// The namespace constant is not defined in unit tests. But it is ignored anway.
		$ns = 1198;

		yield [
			new TitleValue( $ns, 'Template:Foo/bar/SectionName/LanguageCode' ),
			[
				'sourcepage' => 'Template:Foo/bar',
				'section' => 'SectionName',
				'language' => 'LanguageCode',
			]
		];

		yield [
			new TitleValue( $ns, 'Template:Foo/bar/SectionName' ),
			[
				'sourcepage' => 'Template:Foo',
				'section' => 'bar',
				'language' => 'SectionName',
			]
		];

		yield [
			new TitleValue( $ns, 'Foo' ),
			[
				'sourcepage' => '',
				'section' => '',
				'language' => 'Foo',
			]
		];
	}

	/** @dataProvider provideDetermineStatus */
	public function testDetermineStatus(
		?int $readyRevisionId,
		?int $markRevisionId,
		int $latestRevisionId,
		?int $expectedStatus
	): void {
		$status = TranslatablePage::determineStatus( $readyRevisionId, $markRevisionId, $latestRevisionId );
		if ( $expectedStatus ) {
			$this->assertEquals( $expectedStatus, $status->getId() );
		} else {
			$this->assertNull( $status );
		}
	}

	public static function provideDetermineStatus() {
		$readyRevisionId = 1;
		$markRevisionId = null;
		$latestRevisionId = 1;
		yield 'Proposed pages' => [
			$readyRevisionId,
			$markRevisionId,
			$latestRevisionId,
			TranslatablePageStatus::PROPOSED
		];

		$readyRevisionId = $markRevisionId = $latestRevisionId = 1;
		yield 'Active pages' => [
			$readyRevisionId,
			$markRevisionId,
			$latestRevisionId,
			TranslatablePageStatus::ACTIVE
		];

		$markRevisionId = 1;
		$readyRevisionId = $latestRevisionId = 2;
		yield 'Outdated pages' => [
			$readyRevisionId,
			$markRevisionId,
			$latestRevisionId,
			TranslatablePageStatus::OUTDATED
		];

		$readyRevisionId = $markRevisionId = 1;
		$latestRevisionId = 2;
		yield 'Broken pages' => [
			$readyRevisionId,
			$markRevisionId,
			$latestRevisionId,
			TranslatablePageStatus::BROKEN
		];

		$readyRevisionId = $markRevisionId = null;
		$latestRevisionId = 1;
		yield 'Not a translatable page' => [
			$readyRevisionId,
			$markRevisionId,
			$latestRevisionId,
			null
		];
	}
}
