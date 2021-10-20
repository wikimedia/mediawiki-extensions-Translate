<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\MessageSync\MessageSourceChange;

/**
 * @group medium
 * @covers ApiQueryManageMessageGroups
 */
class ApiQueryManageMessageGroupsTest extends ApiTestCase {
	/** @var User */
	protected $user;

	protected function setUp(): void {
		parent::setUp();
		$this->setMwGlobals( [
			'wgTranslateCacheDirectory' => $this->getNewTempDirectory()
		] );

		$this->setGroupPermissions( 'translate-admin', 'translate-manage', true );
		$this->user = $this->getTestUser( 'translate-admin' )->getUser();

		$this->setTemporaryHook( 'TranslateInitGroupLoaders', [] );
		$this->setTemporaryHook( 'TranslatePostInitGroups', [ $this, 'getTestGroups' ] );
		$this->setupTestData();

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => new HashBagOStuff() ] ) );
		$mg->recache();
	}

	public function getTestGroups( &$list ) {
		$group = new MockWikiMessageGroup( 'testgroup-api', [] );
		$list['testgroup-api'] = $group;

		return false;
	}

	public function testGetRenames() {
		$data = $this->doApiRequest(
			[
				'action' => 'query',
				'meta' => 'managemessagegroups',
				'mmggroupId' => 'testgroup-api',
				'mmgmessageKey' => 'keyAdded1',
				'mmgchangesetName' => MessageChangeStorage::DEFAULT_NAME,
			], null, false, $this->user
		);

		$apiRespose = $data[0]['query']['managemessagegroups'][0];
		$this->assertCount( 3, $apiRespose, 'rename suggestions lists deleted messages from ' .
			' the source language' );

		$testData = $this->getRenameMessages();
		foreach ( $apiRespose as $index => $msg ) {
			$this->assertEquals( $msg['key'], $testData[ $index ][0],
				'rename suggestion has key set' );
			$this->assertEquals( $msg['content'], $testData[ $index ][1],
				'rename suggestion has content set' );
			$this->assertEquals( $msg['similarity'], $testData[ $index][2],
				'rename suggestion has similarity' );
			$this->assertArrayHasKey( 'link', $msg, 'rename suggestion has link to message' );
			$this->assertArrayHasKey( 'title', $msg, 'rename suggestion has message title' );
		}
	}

	public function getRenameMessages() {
		return [
			[
				'keyDeleted1',
				'keyDeleted1 content',
				1
			],
			[
				'keyDeleted2',
				'keyDeleted2 content',
				0
			],
			[
				'keyDeleted3',
				'keyDeleted3 content',
				0
			]
		];
	}

	private static function getStoragePath() {
		return MessageChangeStorage::getCdbPath( MessageChangeStorage::DEFAULT_NAME );
	}

	private function setupTestData() {
		$sourceChanges = new MessageSourceChange();

		$renameData = $this->getRenameMessages();
		foreach ( $renameData as $rename ) {
			$sourceChanges->addDeletion( 'en', $rename[0], $rename[1] );
		}

		$sourceChanges->addDeletion( 'en-gb', 'keyGbDeleted1', 'keyGbDeleted1 content' );
		$sourceChanges->addAddition( 'en', 'keyAdded1', 'keyDeleted1 content' );

		$changeData = [];
		$changeData['testgroup-api'] = $sourceChanges;

		MessageChangeStorage::writeChanges( $changeData, self::getStoragePath() );
	}
}
