<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Extension\Translate\MessageSync\MessageSourceChange;
use MediaWiki\Extension\Translate\Synchronization\MessageChangeStorage;
use MediaWiki\Tests\Api\ApiTestCase;
use MediaWiki\User\User;
use MessageGroupTestTrait;
use MockWikiMessageGroup;

/**
 * @group medium
 * @group Database
 * @covers MediaWiki\Extension\Translate\MessageGroupProcessing\QueryManageMessageGroupsActionApi
 * @license GPL-2.0-or-later
 */
class QueryManageMessageGroupsActionApiTest extends ApiTestCase {
	use MessageGroupTestTrait;

	protected User $user;

	protected function setUp(): void {
		parent::setUp();
		$this->overrideConfigValue( 'TranslateCacheDirectory', $this->getNewTempDirectory() );
		$this->setGroupPermissions( 'translate-admin', 'translate-manage', true );
		$this->user = $this->getTestUser( 'translate-admin' )->getUser();
		$this->setupTestData();

		$this->setupGroupTestEnvironmentWithGroups( $this, $this->getTestGroups() );
	}

	public function getTestGroups(): array {
		$group = new MockWikiMessageGroup( 'testgroup-api', [] );
		$list['testgroup-api'] = $group;

		return $list;
	}

	public function testGetRenames(): void {
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

			$expectedSimilarity = $testData[$index][2] ?? null;
			if ( $expectedSimilarity !== null ) {
				$this->assertEquals(
					$expectedSimilarity,
					$msg['similarity'],
					'rename suggestion similarity matches'
				);
			} else {
				$this->assertArrayHasKey( 'similarity', $msg, 'rename suggestion has similarity' );
			}

			$this->assertArrayHasKey( 'link', $msg, 'rename suggestion has link to message' );
			$this->assertArrayHasKey( 'title', $msg, 'rename suggestion has message title' );
		}
	}

	public function getRenameMessages(): array {
		return [
			[
				'keyDeleted1',
				'keyDeleted1 content',
				1
			],
			[
				'keyDeleted2',
				'keyDeleted2 content'
			],
			[
				'keyDeleted3',
				'keyDeleted3 content'
			]
		];
	}

	private static function getStoragePath(): string {
		return MessageChangeStorage::getCdbPath( MessageChangeStorage::DEFAULT_NAME );
	}

	private function setupTestData(): void {
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
