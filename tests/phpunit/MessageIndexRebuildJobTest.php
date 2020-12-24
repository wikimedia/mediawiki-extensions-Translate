<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * @group Database
 * @group medium
 */
class MessageIndexRebuildJobTest extends MediaWikiIntegrationTestCase {
	protected function setUp(): void {
		parent::setUp();

		$this->setMwGlobals( [
			'wgTranslateTranslationServices' => [],
			'wgTranslateDelayedMessageIndexRebuild' => false
		] );

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => wfGetCache( 'hash' ) ] ) );
		$mg->recache();

		MessageIndex::setInstance( new HashMessageIndex() );
		MessageIndex::singleton()->rebuild();
	}

	public function testInsertImmediate() {
		global $wgTranslateDelayedMessageIndexRebuild;
		$wgTranslateDelayedMessageIndexRebuild = false;
		MessageIndexRebuildJob::newJob()->insertIntoJobQueue();
		$this->assertFalse(
			JobQueueGroup::singleton()->get( 'MessageIndexRebuildJob' )->pop(),
			'There is no job in the JobQueue'
		);
	}

	public function testInsertDelayed() {
		global $wgTranslateDelayedMessageIndexRebuild;
		$wgTranslateDelayedMessageIndexRebuild = true;
		MessageIndexRebuildJob::newJob()->insertIntoJobQueue();
		$job = JobQueueGroup::singleton()->get( 'MessageIndexRebuildJob' )->pop();
		$this->assertInstanceOf(
			'MessageIndexRebuildJob',
			$job,
			'There is a job in the JobQueue'
		);
		$this->assertTrue( $job->run(), 'Job is executed successfully' );
	}
}
