<?php

/**
 * @group Database
 * @group medium
 */
class MessageGroupStatesUpdaterJobTest extends MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();

		global $wgHooks;
		$this->setMwGlobals( array(
			'wgHooks' => $wgHooks,
			'wgTranslateTranslationServices' => array(),
			'wgTranslateMessageNamespaces' => array( NS_MEDIAWIKI ),
		) );
		$wgHooks['TranslatePostInitGroups'] = array( array( $this, 'getTestGroups' ) );

		$mg = MessageGroups::singleton();
		$mg->setCache( wfGetCache( 'hash' ) );
		$mg->recache();
	}

	public function getTestGroups( &$list ) {
		$messages = array( 'key1' => 'msg1', 'key2' => 'msg2' );
		$list['group-trans'] = new MessageGroupWithTransitions( 'group-trans', $messages );
		$list['group-notrans'] = new MessageGroupWithoutTransitions( 'group-notrans', array() );

		return false;
	}

	public function testGetGroupsWithTransitions() {
		$handle = new MockMessageHandle();
		$groups = MessageGroupStatesUpdaterJob::getGroupsWithTransitions( $handle );
		foreach ( $groups as $id => $transitions ) {
			$this->assertEquals( 'group-trans', $id );
		}
	}

	/**
	 * @dataProvider provideStatValues
	 */
	public function testGetStatValue( $type, $expected ) {
		$stats = array(
			MessageGroupStats::TOTAL => 666,
			MessageGroupStats::FUZZY => 111,
			MessageGroupStats::TRANSLATED => 222,
			MessageGroupStats::PROOFREAD => 111,
		);
		$actual = MessageGroupStatesUpdaterJob::getStatValue( $stats, $type );
		$this->assertEquals( $expected, $actual );
	}

	public static function provideStatValues() {
		return array(
			array( 'UNTRANSLATED', 333 ),
			array( 'OUTDATED', 111 ),
			array( 'TRANSLATED', 222 ),
			array( 'PROOFREAD', 111 ),
		);
	}

	/**
	 * @dataProvider provideMatchCondition
	 */
	public function testMatchCondition( $expected, $value, $condition, $max ) {
		$actual = MessageGroupStatesUpdaterJob::matchCondition( $value, $condition, $max );
		$this->assertEquals( $expected, $actual );
	}

	public static function provideMatchCondition() {
		return array(
			array( true, 0, 'ZERO', 666 ),
			array( false, 1, 'ZERO', 666 ),
			array( true, 1, 'NONZERO', 666 ),
			array( false, 0, 'NONZERO', 666 ),
			array( true, 666, 'MAX', 666 ),
			array( false, 0, 'MAX', 666 ),
			array( false, 12, 'MAX', 666 ),
		);
	}

	public function testGetNewState() {
		$group = MessageGroups::getGroup( 'group-trans' );
		$transitions = $group->getMessageGroupStates()->getConditions();

		$stats = array( 5, 0, 0, 0 );
		$newstate = MessageGroupStatesUpdaterJob::getNewState( $stats, $transitions );
		$this->assertEquals( 'unset', $newstate, 'all zero, should be unset' );

		$stats = array( 5, 1, 0, 0 );
		$newstate = MessageGroupStatesUpdaterJob::getNewState( $stats, $transitions );
		$this->assertEquals( 'inprogress', $newstate, 'one translated message' );

		$stats = array( 5, 0, 1, 0 );
		$newstate = MessageGroupStatesUpdaterJob::getNewState( $stats, $transitions );
		$this->assertEquals( 'inprogress', $newstate, 'one outdated message' );

		$stats = array( 5, 1, 1, 0 );
		$newstate = MessageGroupStatesUpdaterJob::getNewState( $stats, $transitions );
		$this->assertEquals( 'inprogress', $newstate, 'one translated and one outdated message' );

		$stats = array( 5, 5, 0, 0 );
		$newstate = MessageGroupStatesUpdaterJob::getNewState( $stats, $transitions );
		$this->assertEquals( 'proofreading', $newstate, 'all translated' );
	}

	/**
	 * @group Broken
	 * This tests fails regularly on WMF CI but haven't been able to reproduce locally.
	 */
	public function testHooks() {
		$user = new MockSuperUser();
		$group = MessageGroups::getGroup( 'group-trans' );

		// In the beginning...
		$currentState = ApiGroupReview::getState( $group, 'fi' );
		$this->assertEquals( false, $currentState, 'groups start from unset state' );

		// First translation
		$title = Title::newFromText( 'MediaWiki:key1/fi' );
		$page = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( 'trans1', $title );

		$status = $page->doEditContent( $content, __METHOD__, 0, false, $user );

		self::runJobs();
		$currentState = ApiGroupReview::getState( $group, 'fi' );
		$this->assertEquals( 'inprogress', $currentState, 'in progress after first translation' );

		// First review
		ApiTranslationReview::doReview( $user, self::getRevision( $status ), __METHOD__ );
		self::runJobs();
		$currentState = ApiGroupReview::getState( $group, 'fi' );
		$this->assertEquals( 'inprogress', $currentState, 'in progress while untranslated messages' );

		// Second translation
		$title = Title::newFromText( 'MediaWiki:key2/fi' );
		$page = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( 'trans2', $title );

		$status = $page->doEditContent( $content, __METHOD__, 0, false, $user );

		self::runJobs();
		$currentState = ApiGroupReview::getState( $group, 'fi' );
		$this->assertEquals( 'proofreading', $currentState, 'proofreading after second translation' );

		// Second review
		ApiTranslationReview::doReview( $user, self::getRevision( $status ), __METHOD__ );
		self::runJobs();
		$currentState = ApiGroupReview::getState( $group, 'fi' );
		$this->assertEquals( 'ready', $currentState, 'ready when all proofread' );

		// Change to translation
		$title = Title::newFromText( 'MediaWiki:key1/fi' );
		$page = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( 'trans1 updated', $title );

		$page->doEditContent( $content, __METHOD__, 0, false, $user );

		self::runJobs();
		$currentState = ApiGroupReview::getState( $group, 'fi' );
		$this->assertEquals(
			'proofreading',
			$currentState,
			'back to proofreading after translation changed'
		);
	}

	protected static function getRevision( Status $s ) {
		$value = $s->getValue();

		return $value['revision'];
	}

	protected static function runJobs() {
		do {
			$job = JobQueueGroup::singleton()->pop();
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

	public function getGroupIds() {
		return array( 'group-trans', 'group-notrans' );
	}
}

class MessageGroupWithoutTransitions extends MockWikiMessageGroup {
	public function getMessageGroupStates() {
		return new MessageGroupStates();
	}
}

class MessageGroupWithTransitions extends MockWikiMessageGroup {
	public function getMessageGroupStates() {
		return new MessageGroupStates( array(
			'state conditions' => array(
				array( 'ready', array( 'PROOFREAD' => 'MAX' ) ),
				array( 'proofreading', array( 'TRANSLATED' => 'MAX' ) ),
				array(
					'unset',
					array(
						'UNTRANSLATED' => 'MAX',
						'OUTDATED' => 'ZERO',
						'TRANSLATED' => 'ZERO'
					)
				),
				array( 'inprogress', array( 'UNTRANSLATED' => 'NONZERO' ) ),
			)
		) );
	}
}
