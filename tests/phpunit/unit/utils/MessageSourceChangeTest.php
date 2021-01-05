<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\MessageSync\MessageSourceChange;

/** @covers MediaWiki\Extension\Translate\MessageSync\MessageSourceChange */
class MessageSourceChangeTest extends MediaWikiUnitTestCase {
	/** @var MessageSourceChange */
	protected $change;

	/**
	 * Creates a new MessageSourceChange object before each test.
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->change = new MessageSourceChange();
		$this->change->addAddition( 'en', 'added', 'content-added' );
		$this->change->addChange( 'en', 'changed', 'content-changed' );
		$this->change->addDeletion( 'en', 'deleted', 'content-deleted' );
		$this->change->addRename(
			'en',
			[
				'key' => 'renameAdded',
				'content' => 'added'
			],
			[
				'key' => 'renameDeleted',
				'content' => 'deleted'
			],
			0
		);

		$this->change->addAddition( 'en-gb', 'added', 'en-gb-content-added' );
	}

	public function testAddFindDelete() {
		$modificationType = null;

		$added = $this->change->findMessage( 'en', 'added', [ MessageSourceChange::ADDITION ] );
		$changed = $this->change->findMessage(
			'en', 'changed', [ MessageSourceChange::CHANGE ]
		);
		$deleted = $this->change->findMessage(
			'en', 'deleted', [ MessageSourceChange::DELETION ]
		);
		$rename = $this->change->findMessage(
			'en', 'renameDeleted', [ MessageSourceChange::RENAME ]
		);

		$this->assertArrayHasKey( 'key', $added, 'findMessage returns added message.' );
		$this->assertArrayHasKey( 'key', $changed, 'findMessage returns changed message.' );
		$this->assertArrayHasKey( 'key', $deleted, 'findMessage returns deleted message.' );
		$this->assertArrayHasKey( 'key', $rename, 'findMessage returns deleted rename message.' );

		$modificationAdded = $this->change->findMessage( 'en-gb', 'added', [
			MessageSourceChange::ADDITION,
			MessageSourceChange::CHANGE,
			MessageSourceChange::DELETION
		], $modificationType );
		$this->assertArrayHasKey(
			'key', $modificationAdded,
			'findMessage returns added message correctly when checking multiple types.'
		);
		$this->assertEquals( $modificationType, MessageSourceChange::ADDITION );

		$this->change->removeAdditions( 'en', [ 'added' ] );
		$this->change->removeChanges( 'en', [ 'changed' ] );
		$this->change->removeDeletions( 'en', [ 'deleted' ] );

		$added = $this->change->findMessage( 'en', 'added', [ MessageSourceChange::ADDITION ] );
		$changed = $this->change->findMessage(
			'en', 'changed', [ MessageSourceChange::CHANGE ]
		);
		$deleted = $this->change->findMessage(
			'en', 'deleted', [ MessageSourceChange::DELETION ]
		);

		$this->assertNull( $added, 'findMessage returns null for removed additions.' );
		$this->assertNull( $changed, 'findMessage returns null for removed changes.' );
		$this->assertNull( $deleted, 'findMessage returns null for removed deleted.' );
	}

	public function testBreakRename() {
		$added = $this->change->findMessage(
			'en', 'renameAdded', [ MessageSourceChange::ADDITION ]
		);
		$deleted = $this->change->findMessage(
			'en', 'renameDeleted', [ MessageSourceChange::DELETION ]
		);

		$this->assertNull(
			$added, 'renamed message is removed from additions.'
		);
		$this->assertNull(
			$deleted, 'renamed message is removed from deletions.'
		);

		$this->change->breakRename( 'en', 'renameAdded' );

		$added = $this->change->findMessage(
			'en', 'renameAdded', [ MessageSourceChange::ADDITION ]
		);
		$deleted = $this->change->findMessage(
			'en', 'renameDeleted', [ MessageSourceChange::DELETION ]
		);

		$this->assertArrayHasKey(
			'key', $added, 'broken rename message is added back to additions'
		);
		$this->assertArrayHasKey(
			'key', $deleted, 'broken rename message is added back to deletions'
		);
	}

	public function testPreviousState() {
		$this->change->addRename( 'en-gb', [
			'key' => 'renameAdded',
			'content' => 'added'
		],
		[
			'key' => 'renameDeleted',
			'content' => 'deleted'
		], 0 );

		$this->change->setRenameState( 'en-gb', 'renameDeleted', MessageSourceChange::NONE );

		$changed = $this->change->findMessage(
			'en-gb', 'renameDeleted', [ MessageSourceChange::CHANGE ]
		);
		$this->assertNull(
			$changed, 'findMessage returns null when searching changes for renamed message.'
		);

		$this->change->breakRename( 'en-gb', 'renameAdded' );

		$changed = $this->change->findMessage( 'en-gb', 'renameDeleted', [] );
		$added = $this->change->findMessage(
			'en-gb', 'renameAdded', [ MessageSourceChange::ADDITION ]
		);

		$this->assertNull(
			$changed, 'broken rename message with previous state as NONE is not found in changes'
		);
		$this->assertArrayHasKey(
			'key', $added,
			'broken rename message with previous state as additions is found ' .
			'in the additions list'
		);
	}

	public function testRemoveBasedOnType() {
		$this->change->addAddition( 'en', 'added2', 'content-added' );
		$this->change->removeBasedOnType( 'en', [ 'added', 'added2' ],
			MessageSourceChange::ADDITION );
		$this->change->removeBasedOnType( 'en', [ 'deleted' ], MessageSourceChange::DELETION );

		$this->expectException( InvalidArgumentException::class );
		$this->change->removeBasedOnType(
			'en', [ 'renameDeleted' ], MessageSourceChange::RENAME
		);

		$added = $this->change->findMessage( 'en', 'added', [ MessageSourceChange::ADDITION ] );
		$added2 = $this->change->findMessage( 'en', 'added2', [ MessageSourceChange::ADDITION ] );
		$deleted = $this->change->findMessage( 'en', 'deleted', [ MessageSourceChange::DELETION ] );
		$renames = $this->change->findMessage(
			'en', 'renameDeleted', [ MessageSourceChange::RENAME ]
		);

		$this->assertNull( $added, 'findMessage returns null for removed additions.' );
		$this->assertNull( $added2, 'findMessage returns null for removed changes.' );
		$this->assertNull( $deleted, 'findMessage returns null for removed deleted.' );
		$this->assertArrayHasKey(
			'key', $renames, 'removeBasedOnType does not remove rename messages.'
		);
	}

	public function testIsPreviousState() {
		$isAddedOrChanged = $this->change->isPreviousState( 'en', 'renameDeleted', [
			MessageSourceChange::ADDITION, MessageSourceChange::CHANGE
		] );

		$this->assertFalse(
			$isAddedOrChanged, 'previousState returns false for incorrect previous state'
		);

		$isDeleted = $this->change->isPreviousState( 'en', 'renameDeleted', [
			MessageSourceChange::DELETION
		] );

		$this->assertTrue( $isDeleted,
			'previousState returns true for correct previous state' );
	}

	public function testGetMatchedMessage() {
		$matchedMsg = $this->change->getMatchedMessage( 'en', 'renameAdded' );
		$this->assertEquals(
			'renameDeleted', $matchedMsg['key'], 'getMatchedMessage fetches the proper matched message'
		);

		$matchedMsg = $this->change->getMatchedMessage( 'en', 'renameDeleted' );
		$this->assertEquals(
			'renameAdded', $matchedMsg['key'], 'getMatchedMessage fetches the proper matched message'
		);
	}

	public function testHasOnly() {
		$enGbHasOnly = $this->change->hasOnly( 'en-gb', MessageSourceChange::ADDITION );
		$this->assertTrue( $enGbHasOnly, 'hasOnly ' );

		$enHasOnly = $this->change->hasOnly( 'en-gb', MessageSourceChange::RENAME );
		$this->assertFalse( $enHasOnly, '' );
	}

	public function testGetLanguages() {
		$changeLanguages = $this->change->getLanguages();
		$this->assertCount(
			2, $changeLanguages, 'getLanguages returns all languages that have modifications'
		);
	}
}
