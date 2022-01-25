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
		] );

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => new HashBagOStuff() ] ) );
		$mg->recache();

		MessageIndex::setInstance( new HashMessageIndex() );
		MessageIndex::singleton()->rebuild();
	}

	public function testInsertDelayed() {
		MessageIndexRebuildJob::newJob()->insertIntoJobQueue();
		$job = TranslateUtils::getJobQueueGroup()->get( 'MessageIndexRebuildJob' )->pop();
		$this->assertInstanceOf(
			'MessageIndexRebuildJob',
			$job,
			'There is a job in the JobQueue'
		);
		$this->assertTrue( $job->run(), 'Job is executed successfully' );
	}
}
