<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Content\ContentHandler;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\Statistics\MessageGroupStats;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Status\Status;
use MediaWiki\Tests\Api\ApiTestCase;
use MediaWiki\Title\Title;
use MessageGroupTestTrait;
use MockWikiMessageGroup;

/**
 * @group Database
 * @group medium
 * @covers \MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupStatesUpdaterJob
 */
class MessageGroupStatesUpdaterJobTest extends ApiTestCase {
	use MessageGroupTestTrait;

	protected function setUp(): void {
		parent::setUp();
		$this->setupGroupTestEnvironmentWithGroups( $this, $this->getTestGroups() );
	}

	public function getTestGroups() {
		$messages = [ 'key1' => 'msg1', 'key2' => 'msg2' ];
		$list['group-trans'] = new MessageGroupWithTransitions( 'group-trans', $messages );
		$list['group-notrans'] = new MessageGroupWithoutTransitions( 'group-notrans', [] );

		return $list;
	}

	public function testGetGroupsWithTransitions() {
		$handle = new MockMessageHandle();
		$groups = MessageGroupStatesUpdaterJob::getGroupsWithTransitions( $handle );
		foreach ( $groups as $id => $transitions ) {
			$this->assertEquals( 'group-trans', $id );
		}
	}

	/** @dataProvider provideStatValues */
	public function testGetStatValue( $type, $expected ) {
		$stats = [
			MessageGroupStats::TOTAL => 666,
			MessageGroupStats::FUZZY => 111,
			MessageGroupStats::TRANSLATED => 222,
			MessageGroupStats::PROOFREAD => 111,
		];
		$actual = MessageGroupStatesUpdaterJob::getStatValue( $stats, $type );
		$this->assertEquals( $expected, $actual );
	}

	public static function provideStatValues() {
		return [
			[ 'UNTRANSLATED', 333 ],
			[ 'OUTDATED', 111 ],
			[ 'TRANSLATED', 222 ],
			[ 'PROOFREAD', 111 ],
		];
	}

	/** @dataProvider provideMatchCondition */
	public function testMatchCondition( $expected, $value, $condition, $max ) {
		$actual = MessageGroupStatesUpdaterJob::matchCondition( $value, $condition, $max );
		$this->assertEquals( $expected, $actual );
	}

	public static function provideMatchCondition() {
		return [
			[ true, 0, 'ZERO', 666 ],
			[ false, 1, 'ZERO', 666 ],
			[ true, 1, 'NONZERO', 666 ],
			[ false, 0, 'NONZERO', 666 ],
			[ true, 666, 'MAX', 666 ],
			[ false, 0, 'MAX', 666 ],
			[ false, 12, 'MAX', 666 ],
		];
	}

	public function testGetNewState() {
		$group = MessageGroups::getGroup( 'group-trans' );
		$transitions = $group->getMessageGroupStates()->getConditions();

		$stats = [ 5, 0, 0, 0 ];
		$newstate = MessageGroupStatesUpdaterJob::getNewState( $stats, $transitions );
		$this->assertEquals( 'unset', $newstate, 'all zero, should be unset' );

		$stats = [ 5, 1, 0, 0 ];
		$newstate = MessageGroupStatesUpdaterJob::getNewState( $stats, $transitions );
		$this->assertEquals( 'inprogress', $newstate, 'one translated message' );

		$stats = [ 5, 0, 1, 0 ];
		$newstate = MessageGroupStatesUpdaterJob::getNewState( $stats, $transitions );
		$this->assertEquals( 'inprogress', $newstate, 'one outdated message' );

		$stats = [ 5, 1, 1, 0 ];
		$newstate = MessageGroupStatesUpdaterJob::getNewState( $stats, $transitions );
		$this->assertEquals( 'inprogress', $newstate, 'one translated and one outdated message' );

		$stats = [ 5, 5, 0, 0 ];
		$newstate = MessageGroupStatesUpdaterJob::getNewState( $stats, $transitions );
		$this->assertEquals( 'proofreading', $newstate, 'all translated' );
	}

	/**
	 * @group Broken
	 * This tests fails regularly on WMF CI but haven't been able to reproduce locally.
	 */
	public function testHooks() {
		$user = $this->getTestSysop()->getUser();
		$group = MessageGroups::getGroup( 'group-trans' );

		// In the beginning...
		$currentState = GroupReviewActionApi::getState( $group, 'fi' );
		$this->assertFalse( $currentState, 'groups start from unset state' );

		// First translation
		$title = Title::newFromText( 'MediaWiki:key1/fi' );
		$content = ContentHandler::makeContent( 'trans1', $title );

		$status = $this->editPage( $title, $content, __METHOD__, NS_MAIN, $user );

		$this->translateRunJobs();
		$currentState = GroupReviewActionApi::getState( $group, 'fi' );
		$this->assertEquals( 'inprogress', $currentState, 'in progress after first translation' );

		// First review
		$this->doApiRequestWithToken( [
			'action' => 'translationreview',
			'revision' => self::getRevisionRecordId( $status )
		], null, $user );

		$this->translateRunJobs();
		$currentState = GroupReviewActionApi::getState( $group, 'fi' );
		$this->assertEquals( 'inprogress', $currentState, 'in progress while untranslated messages' );

		// Second translation
		$title = Title::newFromText( 'MediaWiki:key2/fi' );
		$wikipage = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title );
		$content = ContentHandler::makeContent( 'trans2', $title );

		$updater = $wikipage
			->newPageUpdater( self::getTestSysop()->getUser() )
			->setContent( SlotRecord::MAIN, $content );
		$updater->saveRevision( CommentStoreComment::newUnsavedComment( __METHOD__ ) );

		$this->translateRunJobs();
		$currentState = GroupReviewActionApi::getState( $group, 'fi' );
		$this->assertEquals( 'proofreading', $currentState, 'proofreading after second translation' );

		// Second review
		$this->doApiRequestWithToken( [
			'action' => 'translationreview',
			'revision' => self::getRevisionRecordId( $status )
		], null, $user );
		$this->translateRunJobs();
		$currentState = GroupReviewActionApi::getState( $group, 'fi' );
		$this->assertEquals( 'ready', $currentState, 'ready when all proofread' );

		// Change to translation
		$title = Title::newFromText( 'MediaWiki:key1/fi' );
		$page = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title );
		$content = ContentHandler::makeContent( 'trans1 updated', $title );

		$updater = $wikipage
			->newPageUpdater( self::getTestSysop()->getUser() )
			->setContent( SlotRecord::MAIN, $content );
		$updater->saveRevision( CommentStoreComment::newUnsavedComment( __METHOD__ ) );

		$this->translateRunJobs();
		$currentState = GroupReviewActionApi::getState( $group, 'fi' );
		$this->assertEquals(
			'proofreading',
			$currentState,
			'back to proofreading after translation changed'
		);
	}

	private static function getRevisionRecordId( Status $s ) {
		$value = $s->getValue();

		return $value['revision-record']->getId();
	}

	private function translateRunJobs() {
		$jobQueueGroup = $this->getServiceContainer()->getJobQueueGroup();
		do {
			$job = $jobQueueGroup->pop();
			if ( !$job ) {
				break;
			}
			$job->run();
		} while ( true );
	}
}

class MockMessageHandle extends MessageHandle {
	public function __construct() {
	}

	public function getGroupIds(): array {
		return [ 'group-trans', 'group-notrans' ];
	}
}

class MessageGroupWithoutTransitions extends MockWikiMessageGroup {
	public function getMessageGroupStates() {
		return new MessageGroupStates();
	}
}

class MessageGroupWithTransitions extends MockWikiMessageGroup {
	public function getMessageGroupStates() {
		return new MessageGroupStates( [
			'state conditions' => [
				[ 'ready', [ 'PROOFREAD' => 'MAX' ] ],
				[ 'proofreading', [ 'TRANSLATED' => 'MAX' ] ],
				[
					'unset',
					[
						'UNTRANSLATED' => 'MAX',
						'OUTDATED' => 'ZERO',
						'TRANSLATED' => 'ZERO'
					]
				],
				[ 'inprogress', [ 'UNTRANSLATED' => 'NONZERO' ] ],
			]
		] );
	}
}
