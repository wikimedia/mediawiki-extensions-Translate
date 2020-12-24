<?php

/**
 * @group Database
 * @covers TranslationAidDataProvider
 */
class TranslationAidDataProviderTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setUp();
		$this->setMwGlobals( [
			'wgTranslateMessageNamespaces' => [ NS_MEDIAWIKI ]
		] );
		$this->setTemporaryHook( 'TranslatePostInitGroups', [ $this, 'getTestGroups' ] );

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => ObjectCache::getInstance( 'hash' ) ] ) );
		$mg->recache();
		MessageIndex::setInstance( new HashMessageIndex() );
		MessageIndex::singleton()->rebuild();
	}

	public function getTestGroups( &$list ) {
		$messages = [
			'TestPage' => 'bunny',
		];
		$list['test-group'] = new MockWikiMessageGroup( 'test-group', $messages );
		return false;
	}

	/**
	 * @covers TranslationAidDataProvider::getGoodTranslations
	 * @throws MWException
	 */
	public function testGetGoodTranslations() {
		$title = 'MediaWiki:TestPage';
		// Create some translations
		$this->assertTrue(
			$this->editPage( $title . '/fi', 'Test Finnish Translation' )->isGood(),
			'Sanity: must successfully edit ' . $title . '/fi page'
		);
		$this->assertTrue(
			$this->editPage( $title . '/ru', 'Test Russian Translation' )->isGood(),
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
