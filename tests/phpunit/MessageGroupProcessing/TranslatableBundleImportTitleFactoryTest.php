<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Title\ForeignTitle;
use MediaWiki\Title\NamespaceAwareForeignTitleFactory;
use MediaWiki\Title\NamespaceInfo;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWikiIntegrationTestCase;

/** @covers \MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleImportTitleFactory */
class TranslatableBundleImportTitleFactoryTest extends MediaWikiIntegrationTestCase {
	private NamespaceInfo $namespaceInfo;
	private Title $targetPage;
	private TitleFactory $titleFactory;
	private const NAMESPACE_MAPPING = [
		0 => '',
		1 => 'Talk',
		2 => 'User',
		3 => 'User talk',
		104 => 'Custom',
		1198 => 'Translations',
		1199 => 'Translations talk'
	];

	protected function setUp(): void {
		$this->namespaceInfo = $this->createStub( NamespaceInfo::class );
		$this->targetPage = $this->createStub( Title::class );
		$this->titleFactory = $this->createStub( TitleFactory::class );
	}

	/** @dataProvider provideCreateTitleFromForeignTitle */
	public function testCreateTitleFromForeignTitle(
		ForeignTitle $sourceForeignTitle,
		ForeignTitle $foreignTitle,
		Title $targetPage,
		int $expectedNamespace,
		string $expectedTitleText
	): void {
		$this->namespaceInfo->method( 'exists' )->willReturn( true );
		$this->namespaceInfo->method( 'hasSubpages' )->willReturn( true );
		$this->targetPage->method( 'getNamespace' )->willReturn( $targetPage->getNamespace() );
		$this->titleFactory = $this->getServiceContainer()->getTitleFactory();

		$factory = new TranslatableBundleImportTitleFactory( $this->namespaceInfo, $this->titleFactory, $targetPage );

		// Call to set the base title
		$factory->createTitleFromForeignTitle( $sourceForeignTitle );

		$actualTitle = $factory->createTitleFromForeignTitle( $foreignTitle );
		$this->assertEquals( $expectedNamespace, $actualTitle->getNamespace() );
		$this->assertEquals( $expectedTitleText, $actualTitle->getText() );
	}

	public static function provideCreateTitleFromForeignTitle() {
		$foreignTitleFactory = new NamespaceAwareForeignTitleFactory( self::NAMESPACE_MAPPING );
		$baseForeignTitle = $foreignTitleFactory->createForeignTitle( 'WMF Resolutions/2008-09 Budget', NS_MAIN );
		$targetPage = Title::makeTitle( NS_MAIN, 'Resolution:2008-09 Budget' );

		yield 'Subpage imported to a root page' => [
			$baseForeignTitle,
			$baseForeignTitle,
			$targetPage,
			NS_MAIN,
			'Resolution:2008-09 Budget'
		];

		$baseForeignTitle = $foreignTitleFactory->createForeignTitle( 'User:ABC/ImportTest', NS_USER );
		$targetPage = Title::makeTitle( NS_MAIN, 'ImportTest' );
		yield 'Translation unit for a namespaced translatable page is properly renamed' => [
			$baseForeignTitle,
			$foreignTitleFactory
				->createForeignTitle( 'Translations:User:ABC/ImportTest/1/zea', NS_TRANSLATIONS ),
			$targetPage,
			NS_TRANSLATIONS,
			'ImportTest/1/zea'
		];
	}

}
