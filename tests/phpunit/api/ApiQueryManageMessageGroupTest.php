<?php
/**
 * @group medium
 */
class ApiQueryManageMessageGroupsTest extends ApiTestCase {
	protected function setUp() {
		parent::setUp();
		$this->setMwGlobals( [
			'wgTranslateCacheDirectory' => __DIR__ . '/../data',
			'wgGroupPermissions' => [
				'sysop' => [
					'translate-manage' => true,
				],
			],
		] );

		$this->setTemporaryHook( 'TranslateInitGroupLoaders', [] );
		$this->setTemporaryHook( 'TranslatePostInitGroups', [ $this, 'getTestGroups' ] );
		$this->setupTestData();

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => wfGetCache( 'hash' ) ] ) );
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
				'mmgmsgKey' => 'keyAdded1',
				'mmggchangesetName' => MessageChangeStorage::DEFAULT_NAME,
			], null, self::getTestSysop()->getUser()
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
				100
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

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		global $wgTranslateCacheDirectory;
		$tmp = $wgTranslateCacheDirectory;
		$wgTranslateCacheDirectory = __DIR__ . '/../data';
		$filePath = self::getStoragePath();
		unlink( $filePath );
		$wgTranslateCacheDirectory = $tmp;
	}
}
