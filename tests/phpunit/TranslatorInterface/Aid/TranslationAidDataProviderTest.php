<?php

namespace MediaWiki\Extension\Translate\TranslatorInterface\Aid;

use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use MessageGroupTestTrait;
use MockWikiMessageGroup;

/**
 * @group Database
 * @covers \MediaWiki\Extension\Translate\TranslatorInterface\Aid\TranslationAidDataProvider
 */
class TranslationAidDataProviderTest extends MediaWikiIntegrationTestCase {
	use MessageGroupTestTrait;

	protected function setUp(): void {
		parent::setUp();
		$this->overrideConfigValue( 'TranslateMessageNamespaces', [ NS_MEDIAWIKI ] );
		$this->setupGroupTestEnvironmentWithGroups( $this, $this->getTestGroups() );
	}

	public function getTestGroups() {
		$messages = [
			'TestPage' => 'bunny',
		];
		$list['test-group'] = new MockWikiMessageGroup( 'test-group', $messages );
		return $list;
	}

	/** @covers \MediaWiki\Extension\Translate\TranslatorInterface\Aid\TranslationAidDataProvider::getGoodTranslations */
	public function testGetGoodTranslations() {
		$title = 'MediaWiki:TestPage';
		// Create some translations
		$this->assertStatusGood(
			$this->editPage( $title . '/fi', 'Test Finnish Translation' ),
			'Sanity: must successfully edit ' . $title . '/fi page'
		);
		$this->assertStatusGood(
			$this->editPage( $title . '/ru', 'Test Russian Translation' ),
			'Sanity: must successfully edit ' . $title . '/ru page'
		);

		$messageHandle = new MessageHandle( Title::newFromText( $title ) );
		$this->assertTrue( $messageHandle->isValid(), 'Sanity: MessageHandle must be valid' );
		$dataProvider = new TranslationAidDataProvider( $messageHandle );
		$this->assertEquals( [
			'ru' => 'Test Russian Translation',
			'fi' => 'Test Finnish Translation'
		], $dataProvider->getGoodTranslations() );
	}
}
