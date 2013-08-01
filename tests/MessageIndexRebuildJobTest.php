<?php
/**
 * Unit tests.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
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
		$this->setMwGlobals( array(
			'wgTranslateCC' => array(),
			'wgTranslateMessageIndex' => array( 'DatabaseMessageIndex' ),
			'wgTranslateWorkflowStates' => false,
			'wgEnablePageTranslation' => false,
			'wgTranslateGroupFiles' => array(),
			'wgTranslateTranslationServices' => array(),
			'wgTranslateDelayedMessageIndexRebuild' => false
		) );
	}

	public function testNewJob() {
		$job = MessageIndexRebuildJob::newJob();
		$this->assertInstanceOf(
			'MessageIndexRebuildJob',
			$job,
			'Job of correct type is created'
		);
	}

	public function testInsertImmediate() {
		global $wgTranslateDelayedMessageIndexRebuild;
		$wgTranslateDelayedMessageIndexRebuild = false;
		$job = MessageIndexRebuildJob::newJob();
		$this->assertTrue( $job->insert(), 'Job is executed succesfully' );
		$this->assertFalse(
			Job::pop_type( 'MessageIndexRebuildJob' ),
			'There is no job in the JobQueue'
		);
	}

	public function testInsertDelayed() {
		global $wgTranslateDelayedMessageIndexRebuild;
		$wgTranslateDelayedMessageIndexRebuild = true;
		$job = MessageIndexRebuildJob::newJob();
		$this->assertTrue( $job->insert(), 'Job is inserted succesfully' );
		$popJob = Job::pop_type( 'MessageIndexRebuildJob' );
		$this->assertInstanceOf(
			'MessageIndexRebuildJob',
			$popJob,
			'There is a job in the JobQueue'
		);
		$this->assertTrue( $popJob->run(), 'Job is executed succesfully' );
	}
}
