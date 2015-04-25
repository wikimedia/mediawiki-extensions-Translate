<?php
/**
 * Unit tests.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Unit tests for MessageIndexRebuildJob class.
 * @group Database
 * @group medium
 */
class MessageIndexRebuildJobTest extends MediaWikiTestCase {
	protected $config = array();

	public function setUp() {
		parent::setUp();

		global $wgHooks;
		$this->setMwGlobals( array(
			'wgHooks' => $wgHooks,
			'wgTranslateTranslationServices' => array(),
			'wgTranslateDelayedMessageIndexRebuild' => false
		) );
		$wgHooks['TranslatePostInitGroups'] = array();

		$mg = MessageGroups::singleton();
		$mg->setCache( wfGetCache( 'hash' ) );
		$mg->recache();

		MessageIndex::setInstance( new HashMessageIndex() );
		MessageIndex::singleton()->rebuild();
	}

	public function testInsertImmediate() {
		global $wgTranslateDelayedMessageIndexRebuild;
		$wgTranslateDelayedMessageIndexRebuild = false;
		MessageIndexRebuildJob::newJob()->insert();
		$this->assertFalse(
			JobQueueGroup::singleton()->get( 'MessageIndexRebuildJob' )->pop(),
			'There is no job in the JobQueue'
		);
	}

	public function testInsertDelayed() {
		global $wgTranslateDelayedMessageIndexRebuild;
		$wgTranslateDelayedMessageIndexRebuild = true;
		MessageIndexRebuildJob::newJob()->insert();
		$job = JobQueueGroup::singleton()->get( 'MessageIndexRebuildJob' )->pop();
		$this->assertInstanceOf(
			'MessageIndexRebuildJob',
			$job,
			'There is a job in the JobQueue'
		);
		$this->assertTrue( $job->run(), 'Job is executed succesfully' );
	}
}
