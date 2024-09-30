<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use MessageGroupTestTrait;
use MockWikiMessageGroup;

/**
 * Tests for UpdateMessageJob (propagation of fuzzy flag on changes to source text)
 * @group Database
 * @group medium
 * @covers \MediaWiki\Extension\Translate\Synchronization\UpdateMessageJob
 */
class UpdateMessageJobTest extends MediaWikiIntegrationTestCase {
	use MessageGroupTestTrait;

	protected function setUp(): void {
		parent::setUp();
		$this->setupGroupTestEnvironmentWithGroups( $this, $this->getTestGroups() );
	}

	public function getTestGroups(): array {
		$messages = [ 'ugakey' => '$1 of $2', ];
		$list['test-group'] = new MockWikiMessageGroup( 'test-group', $messages );

		return $list;
	}

	public function testUpdate(): void {
		$this->insertPage( "MediaWiki:Ugakey/en", "$1 of $2 old" );
		$this->insertPage( "MediaWiki:Ugakey/nl", "$1 van $2" );
		$srcTitle = Title::newFromText( "MediaWiki:Ugakey/en" );
		// Without invalidation
		$job = UpdateMessageJob::newJob( $srcTitle, "$1 of $2", false );
		$job->run();
		$handle = new MessageHandle( Title::newFromText( "MediaWiki:Ugakey/nl" ) );

		$wikiPageFactory = $this->getServiceContainer()->getWikiPageFactory();
		$newPage = $wikiPageFactory->newFromTitle( $srcTitle );
		// Test the update succeeded
		$this->assertEquals( "$1 of $2", $newPage->getContent()->getText() );
		// Test the page was not marked fuzzy;
		$this->assertFalse(
			$handle->isFuzzy(),
			"Update with invalidation disabled should not have fuzzied unit"
		);

		// Do this twice to test the code that finds deeper reverts
		// if a unit is reverted a->b->a->b->a
		// then MediaWiki will think the second instance of a is being reverted to
		// but we should also unfuzzy any translations whose transver is the first instance of a
		for ( $i = 0; $i < 2; $i++ ) {
			// With invalidation
			$job = UpdateMessageJob::newJob( $srcTitle, "$1 of $2 new", true );
			$job->run();
			$newPage = $wikiPageFactory->newFromTitle( $srcTitle );
			$this->assertEquals( "$1 of $2 new", $newPage->getContent()->getText() );
			$this->assertTrue(
				$handle->isFuzzy(),
				"Update with invalidation enabled should have fuzzied unit"
			);

			// With invalidation but to a revert of an old version
			$job = UpdateMessageJob::newJob( $srcTitle, "$1 of $2 old", true );
			$job->run();
			$newPage = $wikiPageFactory->newFromTitle( $srcTitle );
			$this->assertEquals( "$1 of $2 old", $newPage->getContent()->getText() );
			$this->assertFalse(
				$handle->isFuzzy(),
				"Update reverting to old version should have unfuzzied unit"
			);
		}
	}

	public function testExplicitFuzzy(): void {
		// Setup
		$this->insertPage( "MediaWiki:Ugakey/en", "$1 of $2 old" );
		$this->insertPage( "MediaWiki:Ugakey/nl", "!!FUZZY!!$1 van $2" );
		$srcTitle = Title::newFromText( "MediaWiki:Ugakey/en" );

		$handle = new MessageHandle( Title::newFromText( "MediaWiki:Ugakey/nl" ) );
		$this->assertTrue( $handle->isFuzzy() );

		// Perturb it and back
		$job = UpdateMessageJob::newJob( $srcTitle, "$1 of $2 new", true );
		$job->run();
		$this->assertTrue( $handle->isFuzzy() );

		$job = UpdateMessageJob::newJob( $srcTitle, "$1 of $2", true );
		$job->run();
		$this->assertTrue( $handle->isFuzzy() );
	}

	public function testNullEditCanFuzzy() {
		// Tests for T372994
		// Setup
		$srcTitle = $this->insertPage( "MediaWiki:Ugakey/en", "$1 of $2" )["title"];
		$this->insertPage( "MediaWiki:Ugakey/nl", "$1 van $2" );
		// Run the job
		$job = UpdateMessageJob::newJob( $srcTitle, "$1 of $2", true );
		$job->run();

		$handle = new MessageHandle( Title::newFromText( "MediaWiki:Ugakey/nl" ) );
		$this->assertTrue( $handle->isFuzzy() );
	}

	public function testNoTransver() {
		// Setup
		$srcTitle = $this->insertPage( "MediaWiki:Ugakey/en", "$1 of $2" )["title"];
		$targetTitle = $this->insertPage( "MediaWiki:Ugakey/nl", "$1 van $2" )["title"];

		// In certain circumstances a tp:transver tag won't exist
		// Simulate them by manually deleting it from the database
		$revTagStore = Services::getInstance()->getRevTagStore();
		$revTagStore->removeTags( $targetTitle, RevTagStore::TRANSVER_PROP );

		// If the tunit is not fuzzied, then its transver should be left alone
		$this->assertNull( $revTagStore->getTransver( $targetTitle ) );
		$job = UpdateMessageJob::newJob( $srcTitle, "$1 of $2 new", false );
		$job->run();

		// If it is fuzzied, then the transver should be set to the version of the source page
		// before the fuzzying change
		$expectedTransver = Title::newFromText( "MediaWiki:Ugakey/en" )->getLatestRevId();
		$this->assertNull( $revTagStore->getTransver( $targetTitle ) );
		$job = UpdateMessageJob::newJob( $srcTitle, "$1 of $2 newer", true );
		$job->run();
		$this->assertEquals( $expectedTransver, $revTagStore->getTransver( $targetTitle ) );

		// Delete the transver tag and add a fuzzy tag
		$revTagStore->removeTags( $targetTitle, RevTagStore::TRANSVER_PROP );

		$conds = [
			'rt_page' => $targetTitle->getId(),
			'rt_type' => RevTagStore::FUZZY_TAG,
			'rt_revision' => $targetTitle->getLatestRevId()
		];
		$index = array_keys( $conds );
		$this->getDB()->newReplaceQueryBuilder()
			->replaceInto( 'revtag' )
			->uniqueIndexFields( $index )
			->row( $conds )
			->caller( __METHOD__ )
			->execute();

		// If it's already outdated then further updates don't touch the transver
		$job = UpdateMessageJob::newJob( $srcTitle, "$1 of $2 newest", true );
		$job->run();
		$this->assertNull( $revTagStore->getTransver( $targetTitle ) );
	}
}
