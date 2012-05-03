<?php
/**
 * Unit tests.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Unit tests for MessageIndexRebuildJob class.
 * @group Database
 */
class MessageIndexRebuildJobTest extends MediaWikiTestCase {
	protected $config = array();

	public function setUp() {
		parent::setUp();
		global $wgTranslateMessageIndex, $wgTranslateDelayedMessageIndexRebuild;
		$this->config['class'] = $wgTranslateMessageIndex;
		$this->config['delay'] = $wgTranslateDelayedMessageIndexRebuild;
		$wgTranslateMessageIndex = array( 'DatabaseMessageIndex' );
	}

	public function tearDown() {
		global $wgTranslateMessageIndex, $wgTranslateDelayedMessageIndexRebuild;
		$wgTranslateMessageIndex = $this->config['class'];
		$wgTranslateDelayedMessageIndexRebuild = $this->config['delay'];
	}

	public function testNewJob() {
		$job = MessageIndexRebuildJob::newJob();
		$this->assertInstanceOf( 'MessageIndexRebuildJob', $job, 'Job of correct type is created' );
	}

	public function testInsertImmediate() {
		global $wgTranslateDelayedMessageIndexRebuild;
		$wgTranslateDelayedMessageIndexRebuild = false;
		$job = MessageIndexRebuildJob::newJob();
		$this->assertTrue( $job->insert(), 'Job is executed succesfully' );
		$this->assertFalse( Job::pop_type( 'MessageIndexRebuildJob' ), 'There is no job in the JobQueue' );
	}

	public function testInsertDelayed() {
		global $wgTranslateDelayedMessageIndexRebuild;
		$wgTranslateDelayedMessageIndexRebuild = true;
		$job = MessageIndexRebuildJob::newJob();
		$this->assertTrue( $job->insert(), 'Job is inserted succesfully' );
		$popJob = Job::pop_type( 'MessageIndexRebuildJob' );
		$this->assertInstanceOf( 'MessageIndexRebuildJob', $popJob, 'There is a job in the JobQueue' );
		$this->assertNull( $popJob->run(), 'Job is executed succesfully' );
	}
}