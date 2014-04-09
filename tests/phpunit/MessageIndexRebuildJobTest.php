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

	public function testInsertImmediate() {
		global $wgTranslateDelayedMessageIndexRebuild;
		$wgTranslateDelayedMessageIndexRebuild = false;
		MessageIndexRebuildJob::newJob()->insert();
		$this->assertFalse(
			Job::pop_type( 'MessageIndexRebuildJob' ),
			'There is no job in the JobQueue'
		);
	}

	public function testInsertDelayed() {
		global $wgTranslateDelayedMessageIndexRebuild;
		$wgTranslateDelayedMessageIndexRebuild = true;
		MessageIndexRebuildJob::newJob()->insert();
		$job = Job::pop_type( 'MessageIndexRebuildJob' );
		$this->assertInstanceOf(
			'MessageIndexRebuildJob',
			$job,
			'There is a job in the JobQueue'
		);
		$this->assertTrue( $job->run(), 'Job is executed succesfully' );
	}
}
