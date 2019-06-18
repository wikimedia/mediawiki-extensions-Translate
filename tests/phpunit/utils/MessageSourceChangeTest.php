<?php

class MessageSourceChangeTest extends PHPUnit\Framework\TestCase {
	/**
	 * @var MessageSourceChange
	 */
	protected $changes;

	protected function setUp() {
		parent::setUp();

		$this->changes = new MessageSourceChange();
		$this->changes->addAddition( 'en', 'added', 'content-added' );
		$this->changes->addChange( 'en', 'changed', 'content-changed' );
		$this->changes->addDeletion( 'en', 'deleted', 'content-deleted' );
		$this->changes->addRename(
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

		$this->changes->addAddition( 'en-gb', 'added', 'en-gb-content-added' );
	}

	public function testAddFindDelete() {
		$modificationType = null;

		$added = $this->changes->findMessage( 'en', 'added', [ MessageSourceChange::M_ADDITION ] );
		$changed = $this->changes->findMessage( 'en', 'changed',
			[ MessageSourceChange::M_CHANGE ] );
		$deleted = $this->changes->findMessage( 'en', 'deleted',
			[ MessageSourceChange::M_DELETION ] );
		$rename = $this->changes->findMessage( 'en', 'renameDeleted',
			[ MessageSourceChange::M_RENAME ] );

		$this->assertArrayHasKey( 'key', $added, 'findMessage returns added message properly.' );
		$this->assertArrayHasKey( 'key', $changed, 'findMessage returns changed message properly.' );
		$this->assertArrayHasKey( 'key', $deleted, 'findMessage returns deleted message properly.' );
		$this->assertArrayHasKey( 'key', $rename,
			'findMessage returns deleted rename message properly.' );

		$modificationAdded = $this->changes->findMessage( 'en-gb', 'added', [
			MessageSourceChange::M_ADDITION,
			MessageSourceChange::M_CHANGE,
			MessageSourceChange::M_DELETION
		], $modificationType );
		$this->assertArrayHasKey( 'key', $modificationAdded,
			'findMessage returns added message properly when checking multiple types.' );
		$this->assertEquals( $modificationType, MessageSourceChange::M_ADDITION );

		$this->changes->removeAdditions( 'en', [ 'added' ] );
		$this->changes->removeChanges( 'en', [ 'changed' ] );
		$this->changes->removeDeletions( 'en', [ 'deleted' ] );

		$added = $this->changes->findMessage( 'en', 'added', [ MessageSourceChange::M_ADDITION ] );
		$changed = $this->changes->findMessage( 'en', 'changed',
			[ MessageSourceChange::M_CHANGE ] );
		$deleted = $this->changes->findMessage( 'en', 'deleted',
			[ MessageSourceChange::M_DELETION ] );

		$this->assertNull( $added, 'findMessage returns null for removed additions.' );
		$this->assertNull( $changed, 'findMessage returns null for removed changes.' );
		$this->assertNull( $deleted, 'findMessage returns null for removed deleted.' );
	}

	public function testBreakRename() {
		$added = $this->changes->findMessage( 'en', 'renameAdded',
			[ MessageSourceChange::M_ADDITION ] );
		$deleted = $this->changes->findMessage( 'en', 'renameDeleted',
			[ MessageSourceChange::M_DELETION ] );

		$this->assertNull( $added,
			'findMessage returns null when searching addition for renamed message.' );
		$this->assertNull( $deleted,
			'findMessage returns null when searching deletions for renamed message.' );

		$this->changes->breakRename( 'en', 'renameAdded' );

		$added = $this->changes->findMessage( 'en', 'renameAdded',
			[ MessageSourceChange::M_ADDITION ] );
		$deleted = $this->changes->findMessage( 'en', 'renameDeleted',
			[ MessageSourceChange::M_DELETION ] );

		$this->assertArrayHasKey( 'key', $added,
			'findMessage returns null when searching addition for renamed ' .
			' message after breakRename.' );
		$this->assertArrayHasKey( 'key', $deleted,
			'findMessage returns null when searching deletions for renamed ' .
			' message after breakRename.' );
	}

	public function testPreviousState() {
		$this->changes->addRename( 'en-gb', [
			'key' => 'renameAdded',
			'content' => 'added'
		],
		[
			'key' => 'renameDeleted',
			'content' => 'deleted'
		], 0 );

		$this->changes->setRenameState( 'en-gb', 'renameDeleted', MessageSourceChange::M_NONE );

		$changed = $this->changes->findMessage( 'en-gb', 'renameDeleted',
			[ MessageSourceChange::M_CHANGE ] );
		$this->assertNull( $changed,
			'findMessage returns null when searching changes for renamed message.' );

		$this->changes->breakRename( 'en-gb', 'renameAdded' );

		$changed = $this->changes->findMessage( 'en-gb', 'renameDeleted',
			[] );
		$added = $this->changes->findMessage( 'en-gb', 'renameAdded',
			[ MessageSourceChange::M_ADDITION ] );

		$this->assertNull( $changed,
			'findMessage returns null for a message with previous state as NONE, ' .
			'when searching changes for all types of ' .
			'messages after setPreviousState + breakRename.' );
		$this->assertArrayHasKey( 'key', $added,
			'findMessage returns the message when searching additions for renamed ' .
			'message after setPreviousState + breakRename.' );
	}

	public function testRemoveBasedOnType() {
		$this->changes->addAddition( 'en', 'added2', 'content-added' );
		$this->changes->removeBasedOnType( 'en', [ 'added', 'added2' ],
			MessageSourceChange::M_ADDITION );
		$this->changes->removeBasedOnType( 'en', [ 'deleted' ], MessageSourceChange::M_DELETION );

		$this->expectException( InvalidArgumentException::class );
		$this->changes->removeBasedOnType( 'en', [ 'renameDeleted' ],
			MessageSourceChange::M_RENAME );

		$added = $this->changes->findMessage( 'en', 'added',
			[ MessageSourceChange::M_ADDITION ] );
		$added2 = $this->changes->findMessage( 'en', 'added2',
			[ MessageSourceChange::M_ADDITION ] );
		$deleted = $this->changes->findMessage( 'en', 'deleted',
			[ MessageSourceChange::M_DELETION ] );
		$renames = $this->changes->findMessage( 'en', 'renameDeleted',
			[ MessageSourceChange::M_RENAME ] );

		$this->assertNull( $added, 'findMessage returns null for removed additions.' );
		$this->assertNull( $added2, 'findMessage returns null for removed changes.' );
		$this->assertNull( $deleted, 'findMessage returns null for removed deleted.' );
		$this->assertArrayHasKey( 'key', $renames,
			'removeBasedOnType does not remove rename messages.' );
	}

	public function testIsPreviousState() {
		$isAddedOrChanged = $this->changes->isPreviousState( 'en', 'renameDeleted', [
			MessageSourceChange::M_ADDITION, MessageSourceChange::M_CHANGE
		] );

		$this->assertFalse( $isAddedOrChanged,
			'previousState returns false for incorrect previous state' );

		$isDeleted = $this->changes->isPreviousState( 'en', 'renameDeleted', [
			MessageSourceChange::M_DELETION
		] );

		$this->assertTrue( $isDeleted,
			'previousState returns true for correct previous state' );
	}

	public function testGetMatchedMsg() {
		$matchedMsg = $this->changes->getMatchedMsg( 'en', 'renameAdded' );
		$this->assertEquals( 'renameDeleted', $matchedMsg['key'],
			'getMatchedMsg fetches the proper matched message' );

		$matchedMsg = $this->changes->getMatchedMsg( 'en', 'renameDeleted' );
		$this->assertEquals( 'renameAdded', $matchedMsg['key'],
			'getMatchedMsg fetches the proper matched message' );
	}

	public function testHasOnly() {
		$enGbHasOnly = $this->changes->hasOnly( 'en-gb', MessageSourceChange::M_ADDITION );
		$this->assertTrue( $enGbHasOnly, 'hasOnly ' );

		$enHasOnly = $this->changes->hasOnly( 'en-gb', MessageSourceChange::M_RENAME );
		$this->assertFalse( $enHasOnly, '' );
	}

	public function testGetLanguages() {
		$changeLanguages = $this->changes->getLanguages();
		$this->assertCount( 2, $changeLanguages,
			'getLanguages returns all languages that have modifications' );
	}
}
