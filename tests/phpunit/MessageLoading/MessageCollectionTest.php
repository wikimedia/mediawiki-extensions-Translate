<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Content\ContentHandler;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use MessageGroupTestTrait;
use MockWikiMessageGroup;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @group Database
 * @group medium
 * @covers \MediaWiki\Extension\Translate\MessageLoading\MessageCollection
 */
class MessageCollectionTest extends MediaWikiIntegrationTestCase {
	use MessageGroupTestTrait;

	protected function setUp(): void {
		parent::setUp();
		$this->setupGroupTestEnvironmentWithGroups( $this, $this->getTestGroups() );
	}

	public function getTestGroups(): array {
		$messages = [
			'translated' => 'bunny',
			'untranslated' => 'fanny',
			'changedtranslated_1' => 'bunny',
			'changedtranslated_2' => 'fanny'
		];
		$list['test-group'] = new MockWikiMessageGroup( 'test-group', $messages );

		return $list;
	}

	public function testMessage(): void {
		$user = $this->getTestSysop()->getUser();
		$title = Title::newFromText( 'MediaWiki:Translated/fi' );
		$pageUpdater = $this->getServiceContainer()
			->getWikiPageFactory()
			->newFromTitle( $title )
			->newPageUpdater( $user );
		$content = ContentHandler::makeContent( 'pupuliini', $title );

		// Create the page
		$commentStoreComment = CommentStoreComment::newUnsavedComment( __METHOD__ );
		$pageUpdater->setContent( SlotRecord::MAIN, $content );
		$pageUpdater->saveRevision( $commentStoreComment );
		$status = $pageUpdater->getStatus();

		$value = $status->getValue();
		$revisionRecord = $value['revision-record'];
		$revisionId = $revisionRecord->getId();

		$group = MessageGroups::getGroup( 'test-group' );
		$collection = $group->initCollection( 'fi' );
		$collection->loadTranslations();

		/** @var Message $translated */
		$translated = $collection['translated'];
		$this->assertInstanceOf( Message::class, $translated );
		$this->assertEquals( 'translated', $translated->key() );
		$this->assertEquals( 'bunny', $translated->definition() );
		$this->assertEquals( 'pupuliini', $translated->translation() );
		$this->assertEquals( $user->getName(), $translated->getProperty( 'last-translator-text' ) );
		$this->assertEquals( $user->getId(), $translated->getProperty( 'last-translator-id' ) );
		$this->assertEquals(
			'translated',
			$translated->getProperty( 'status' ),
			'message status is translated'
		);
		$this->assertEquals( $revisionId, $translated->getProperty( 'revision' ) );

		/** @var Message $untranslated */
		$untranslated = $collection['untranslated'];
		$this->assertInstanceOf( Message::class, $untranslated );
		$this->assertNull( $untranslated->translation(), 'no translation is null' );
		$this->assertNull( $untranslated->getProperty( 'last-translator-text' ) );
		$this->assertNull( $untranslated->getProperty( 'last-translator-id' ) );
		$this->assertEquals(
			'untranslated',
			$untranslated->getProperty( 'status' ),
			'message status is untranslated'
		);
		$this->assertNull( $untranslated->getProperty( 'revision' ) );
	}

	/** @covers \MediaWiki\Extension\Translate\MessageLoading\MessageCollection::filterChanged */
	public function testFilterChanged(): void {
		$this->assertStatusGood(
			$this->editPage( 'MediaWiki:Changedtranslated_1/fi', 'pupuliini_1' )
		);
		$this->assertStatusGood(
			$this->editPage( 'MediaWiki:Changedtranslated_2/fi', 'pupuliini_modified' )
		);
		$group = MessageGroups::getGroup( 'test-group' );
		$collection = $group->initCollection( 'fi' );
		$collection->loadTranslations();
		$this->assertArrayHasKey( 'changedtranslated_1', $collection->keys() );
		$this->assertArrayHasKey( 'changedtranslated_2', $collection->keys() );
		// Trick message collection to think it was loaded from file.
		$collection->setInFile( [
			'changedtranslated_1' => 'pupuliini_1',
			'changedtranslated_2' => 'pupuliini_2'
		] );
		$collection->filter( MessageCollection::FILTER_CHANGED, MessageCollection::EXCLUDE_MATCHING );
		$this->assertContains( 'changedtranslated_2', $collection->getMessageKeys() );
		$this->assertNotContains( 'changedtranslated_1', $collection->getMessageKeys() );
	}
}
