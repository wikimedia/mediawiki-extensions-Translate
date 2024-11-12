<?php

namespace MediaWiki\Extension\Translate\Synchronization;

use MediaWiki\Context\RequestContext;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use MessageGroupTestTrait;
use MockWikiMessageGroup;

/** @group Database */
class MessageWebImporterTest extends MediaWikiIntegrationTestCase {
	use MessageGroupTestTrait;

	private const PAGE = 'MediaWiki:' . __METHOD__ . '_translated';

	protected function setUp(): void {
		parent::setUp();
		$this->setupGroupTestEnvironmentWithGroups( $this, $this->getTestGroups() );

		$this->overrideUserPermissions( RequestContext::getMain()->getUser(), [
			'translate-manage' // needed for MessageWebImporter::doFuzzy for testDoFuzzy
		] );
	}

	public function getTestGroups() {
		$list['test-group'] = new MockWikiMessageGroup( 'test-group', [
			self::PAGE => 'bunny',
		] );
		return $list;
	}

	/** @covers \MediaWiki\Extension\Translate\Synchronization\MessageWebImporter::doFuzzy */
	public function testDoFuzzy() {
		$this->assertStatusGood(
			$this->editPage( self::PAGE . '/en', 'English Original' ),
			'Sanity: Must create English original translation'
		);
		$this->assertStatusGood(
			$this->editPage( self::PAGE . '/fi', 'Finnish Original' ),
			'Sanity: Must create Finnish original translation'
		);

		$result = MessageWebImporter::doFuzzy(
			Title::newFromText( self::PAGE ),
			'English Changed', '', null, RequestContext::getMain()
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
