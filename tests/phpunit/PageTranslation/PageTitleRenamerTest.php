<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWikiIntegrationTestCase;
use Title;

/**
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\PageTranslation\PageTitleRenamer
 * @covers \MediaWiki\Extension\Translate\PageTranslation\InvalidPageTitleRename
 */
class PageTitleRenamerTest extends MediaWikiIntegrationTestCase {
	/** @dataProvider provideNewPageTitle */
	public function testGetNewTitle( string $source, string $target, array $titlesToCheck ): void {
		$sourceTitle = Title::newFromText( $source );
		$targetTitle = Title::newFromText( $target );

		$pageTitleRenamer = new PageTitleRenamer( $sourceTitle, $targetTitle );
		$this->assertEquals(
			$pageTitleRenamer->getNewTitle( $sourceTitle )->getPrefixedText(),
			$targetTitle->getPrefixedText()
		);

		foreach ( $titlesToCheck as $original => $renamed ) {
			$originalTitle = Title::newFromText( $original );
			$newRenamedTitle = $pageTitleRenamer->getNewTitle( $originalTitle );

			$renamedTitle = Title::newFromText( $renamed );
			$this->assertEquals(
				$renamedTitle->getPrefixedText(),
				$newRenamedTitle->getPrefixedText()
			);
		}
	}

	/** @dataProvider provideNewPageTitleException */
	public function testGetNewTitleException(
		string $source,
		string $target,
		string $titleToRename,
		int $exceptionCode
	): void {
		$sourceTitle = Title::newFromText( $source );
		$targetTitle = Title::newFromText( $target );

		$pageTitleRenamer = new PageTitleRenamer( $sourceTitle, $targetTitle );

		$this->expectException( InvalidPageTitleRename::class );
		$this->expectExceptionCode( $exceptionCode );

		$pageTitleRenamer->getNewTitle( Title::newFromText( $titleToRename ) );
	}

	public function provideNewPageTitle() {
		yield [
			'Main Page',
			'New Main Page',
			[
				// Subpage
				'Main Page/Hello' => 'New Main Page/Hello',
				// Talk page
				'Talk:Main Page' => 'Talk:New Main Page',
				// Translation page
				'Main Page/es' => 'New Main Page/es',
				// Translation page talk page
				'Talk:Main Page/es' => 'Talk:New Main Page/es',
				// Translation
				'Translations:Main Page/1/es' => 'Translations:New Main Page/1/es',
				// Translation talk
				'Translations talk:Main Page/1/es' => 'Translations talk:New Main Page/1/es'
			]
		];

		yield [
			'Help:Foo',
			'Category:Bar',
			[
				// Talk page
				'Help talk:Foo' => 'Category talk:Bar',
				// Sub page
				'Help:Foo/Hello' => 'Category:Bar/Hello',
				// Sub page
				'Help:Foo/Help:Foo' => 'Category:Bar/Help:Foo',
				// Translation page
				'Help:Foo/en-gb' => 'Category:Bar/en-gb',
				// Translation
				'Translations:Help:Foo/1/en-gb' => 'Translations:Category:Bar/1/en-gb',
				// Translation talk
				'Translations talk:Help:Foo/1/en-gb' => 'Translations talk:Category:Bar/1/en-gb'
			]
		];

		yield [
			'Help talk:Foo',
			'Category talk:Bar',
			[
				// Translation page
				'Help talk:Foo/en-gb' => 'Category talk:Bar/en-gb',
				// Translation
				'Translations:Help talk:Foo/1/en-gb' => 'Translations:Category talk:Bar/1/en-gb',
				// Translation talk
				'Translations talk:Help talk:Foo/1/en-gb' => 'Translations talk:Category talk:Bar/1/en-gb'
			]
		];

		yield [
			'Foo/done',
			'Template:Foo/done',
			[
				// Translation page
				'Foo/done/en' => 'Template:Foo/done/en',
				// Translation page
				'Foo/done/ko' => 'Template:Foo/done/ko',
				// Talk page
				'Talk:Foo/done/ko' => 'Template talk:Foo/done/ko',
				// Translation
				'Foo/done/1/ko' => 'Template:Foo/done/1/ko'
			]
		];

		yield [
			'Template:Foo/done',
			'Foo/done',
			[
				// Translation page
				'Template:Foo/done/en' => 'Foo/done/en',
				// Translation page
				'Template:Foo/done/ko' => 'Foo/done/ko',
				// Talk page
				'Template talk:Foo/done/ko' => 'Talk:Foo/done/ko',
				// Translation
				'Template:Foo/done/1/ko' => 'Foo/done/1/ko'
			]
		];
	}

	public function provideNewPageTitleException() {
		yield 'Moving a page not part of translatable page' => [
			'Main Page',
			'New Main Page',
			'Category:Bar',
			PageTitleRenamer::UNKNOWN_PAGE
		];

		yield 'Rename failure because there are no common strings' => [
			'Main Page',
			'Main Page 2',
			'Example',
			PageTitleRenamer::RENAME_FAILED
		];

		yield 'Namespace does not support talkpages' => [
			'Main Page',
			'Special:New Main Page',
			'Talk:Main Page',
			PageTitleRenamer::NS_TALK_UNSUPPORTED
		];
	}
}
