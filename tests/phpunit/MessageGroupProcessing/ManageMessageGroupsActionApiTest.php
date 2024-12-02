<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use DateInterval;
use DateTime;
use MediaWiki\Api\ApiUsageException;
use MediaWiki\Extension\Translate\MessageSync\MessageSourceChange;
use MediaWiki\Extension\Translate\Synchronization\MessageChangeStorage;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Tests\Api\ApiTestCase;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MessageGroupTestTrait;
use MockWikiMessageGroup;

/**
 * @group medium
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\MessageGroupProcessing\ManageMessageGroupsActionApi
 * @group Database
 */
class ManageMessageGroupsActionApiTest extends ApiTestCase {
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

		// Adding this manually, since this will not be present in the list of changes
		// and will be fetched manually from the database.
		$title = Title::makeTitle( $group->getNamespace(),
			Utilities::title( 'keyDeleted', 'en-gb', $group->getNamespace() ) );
		$this->editPage( $title, 'world 23' );

		return $list;
	}

	public function testRename(): void {
		$filePath = self::getStoragePath();

		$this->doApiRequestWithToken(
			[
				'action' => 'managemessagegroups',
				'groupId' => 'testgroup-api',
				'renameMessageKey' => 'keyDeleted',
				'messageKey' => 'keyAdded',
				'operation' => 'rename',
				'changesetName' => MessageChangeStorage::DEFAULT_NAME,
				'changesetModified' => time(),
			], null, $this->user, 'csrf'
		);

		$sourceChanges = MessageChangeStorage::getGroupChanges( $filePath, 'testgroup-api' );
		$deletedMsg = $sourceChanges->findMessage( 'en', 'keyDeleted',
			[ MessageSourceChange::DELETION ] );

		$this->assertNull( $deletedMsg, 'previously deleted message ' .
			'no longer has the deleted state' );

		$renameDeleted = $sourceChanges->findMessage( 'en', 'keyDeleted',
			[ MessageSourceChange::RENAME ] );

		$this->assertArrayHasKey( 'key', $renameDeleted, 'previously deleted message is ' .
			'added to the renamed state. ' );

		$this->assertTrue( $sourceChanges->isPreviousState( 'en', 'keyDeleted', [
				MessageSourceChange::DELETION ] ),
			'previous state of the deleted message after being added to rename is deleted.' );

		$renameDeleted = $sourceChanges->findMessage( 'en-gb', 'keyDeleted',
			[ MessageSourceChange::RENAME ] );
		$this->assertArrayHasKey( 'key', $renameDeleted, 'non-source language messages are ' .
			'also updated as per the source language changes.' );
	}

	public function testRenameWithPreviousRename(): void {
		$filePath = self::getStoragePath();

		$this->doApiRequestWithToken(
			[
				'action' => 'managemessagegroups',
				'groupId' => 'testgroup-api',
				'renameMessageKey' => 'keyDeleted',
				'messageKey' => 'renameAdded',
				'operation' => 'rename',
				'changesetName' => MessageChangeStorage::DEFAULT_NAME,
				'changesetModified' => time(),
			], null, $this->user, 'csrf'
		);

		$sourceChanges = MessageChangeStorage::getGroupChanges( $filePath, 'testgroup-api' );
		$deletedMsg = $sourceChanges->findMessage( 'en', 'renameDeleted',
			[ MessageSourceChange::DELETION ] );

		$this->assertArrayHasKey( 'key', $deletedMsg, 'previously renamed message is ' .
			'restored to the deleted state when the matched message is renamed with ' .
			'another key.' );

		$renameDeleted = $sourceChanges->findMessage( 'en', 'keyDeleted',
			[ MessageSourceChange::RENAME ] );
		$this->assertArrayHasKey( 'key', $renameDeleted, 'newly renamed message is ' .
			'added to the renamed state.' );
	}

	public function testAddAsNew(): void {
		$filePath = self::getStoragePath();

		$this->doApiRequestWithToken(
			[
				'action' => 'managemessagegroups',
				'groupId' => 'testgroup-api',
				'messageKey' => 'renameAdded',
				'operation' => 'new',
				'changesetName' => MessageChangeStorage::DEFAULT_NAME,
				'changesetModified' => time()
			], null, $this->user, 'csrf'
		);

		$sourceChanges = MessageChangeStorage::getGroupChanges( $filePath, 'testgroup-api' );
		$deletedMsg = $sourceChanges->findMessage( 'en', 'renameDeleted',
			[ MessageSourceChange::DELETION ] );
		$addedMsg = $sourceChanges->findMessage( 'en', 'renameAdded',
			[ MessageSourceChange::ADDITION ] );

		$this->assertArrayHasKey( 'key', $deletedMsg, 'previously renamed message is ' .
			' updated when an add as new operation is performed.' );
		$this->assertEquals( 'renameDeleted', $deletedMsg['key'] );

		$this->assertArrayHasKey( 'key', $addedMsg, 'previously renamed message is ' .
		' updated when an add as new operation is performed.' );
		$this->assertEquals( 'renameAdded', $addedMsg['key'] );

		$deletedMsg = $sourceChanges->findMessage( 'en-gb', 'renameDeleted',
			[ MessageSourceChange::DELETION ] );
		$addedMsg = $sourceChanges->findMessage( 'en-gb', 'renameAdded',
			[ MessageSourceChange::ADDITION ] );

		$this->assertArrayHasKey( 'key', $deletedMsg, 'previously renamed message in ' .
			'non-source language is updated when an add as new operation is performed.' );
		$this->assertArrayHasKey( 'key', $addedMsg, 'previously renamed message in ' .
			'non-source language is updated when an add as new operation is performed.' );
	}

	public function testAjaxAtomicity(): void {
		$date = new DateTime();
		// subtract period of 1 day
		$date->sub( new DateInterval( 'P1D' ) );

		$this->expectException( ApiUsageException::class );
		$this->doApiRequestWithToken(
			[
				'action' => 'managemessagegroups',
				'groupId' => 'testgroup-api',
				'messageKey' => 'renameAdded',
				'operation' => 'new',
				'changesetName' => MessageChangeStorage::DEFAULT_NAME,
				'changesetModified' => $date->getTimestamp(),
			], null, $this->user, 'csrf'
		);
	}

	private static function getStoragePath(): string {
		return MessageChangeStorage::getCdbPath( MessageChangeStorage::DEFAULT_NAME );
	}

	private function setupTestData(): void {
		$sourceChanges = new MessageSourceChange();

		$sourceChanges->addAddition( 'en', 'keyAdded', 'world 12' );
		$sourceChanges->addDeletion( 'en', 'keyDeleted', 'world 23' );
		$sourceChanges->addRename( 'en', [
			'key' => 'renameAdded',
			'content' => 'renameAdded content'
		], [
			'key' => 'renameDeleted',
			'content' => 'renameDeleted content'
		] );

		$sourceChanges->addRename( 'en-gb', [
			'key' => 'renameAdded',
			'content' => 'renameAdded content'
		], [
			'key' => 'renameDeleted',
			'content' => 'renameDeleted content'
		] );
		$sourceChanges->addAddition( 'en-gb', 'keyAdded', 'world 12' );
		$sourceChanges->addDeletion( 'en-gb', 'keyDeleted', 'world 23' );

		$changeData = [];
		$changeData['testgroup-api'] = $sourceChanges;

		MessageChangeStorage::writeChanges( $changeData, self::getStoragePath() );
	}
}
