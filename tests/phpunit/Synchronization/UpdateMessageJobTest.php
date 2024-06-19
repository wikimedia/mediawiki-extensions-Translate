<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
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
}
