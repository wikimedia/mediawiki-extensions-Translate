<?php

namespace MediaWiki\Extension\Translate\Synchronization;

use HashBagOStuff;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use MockWikiMessageGroup;
use RequestContext;
use WANObjectCache;

/** @group Database */
class MessageWebImporterTest extends MediaWikiIntegrationTestCase {
	private const PAGE = 'MediaWiki:' . __METHOD__ . '_translated';

	protected function setUp(): void {
		parent::setUp();
		$this->setTemporaryHook( 'TranslateInitGroupLoaders', HookContainer::NOOP );
		$this->setTemporaryHook( 'TranslatePostInitGroups', [ $this, 'getTestGroups' ] );

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => new HashBagOStuff() ] ) );
		$mg->recache();
		Services::getInstance()->getMessageIndex()->rebuild();
		$this->overrideUserPermissions( RequestContext::getMain()->getUser(), [
			'translate-manage' // needed for MessageWebImporter::doFuzzy for testDoFuzzy
		] );
	}

	public function getTestGroups( &$list ) {
		$list['test-group'] = new MockWikiMessageGroup( 'test-group', [
			self::PAGE => 'bunny',
		] );
		return false;
	}

	/** @covers \MediaWiki\Extension\Translate\Synchronization\MessageWebImporter::doFuzzy */
	public function testDoFuzzy() {
		$this->assertTrue(
			$this->editPage( self::PAGE . '/en', 'English Original' )->isGood(),
			'Sanity: Must create English original translation'
		);
		$this->assertTrue(
			$this->editPage( self::PAGE . '/fi', 'Finnish Original' )->isGood(),
			'Sanity: Must create Finnish original translation'
		);

		$result = MessageWebImporter::doFuzzy(
			Title::newFromText( self::PAGE ),
			'English Changed', '', null
		);
		$this->assertEquals( 'translate-manage-import-fuzzy', $result[0] );
		$this->assertEquals(
			'English Changed',
			$this->getServiceContainer()->getWikiPageFactory()
				->newFromTitle( Title::newFromText( self::PAGE . '/en' ) )->getContent()->serialize(),
			'Must change the content of the English translation'
		);
		$this->assertEquals(
			TRANSLATE_FUZZY . 'Finnish Original',
			$this->getServiceContainer()->getWikiPageFactory()
				->newFromTitle( Title::newFromText( self::PAGE . '/fi' ) )->getContent()->serialize(),
			'Must change the content of the Finnish translation'
		);
	}
}
