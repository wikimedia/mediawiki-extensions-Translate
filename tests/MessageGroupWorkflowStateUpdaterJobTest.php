<?php

/**
 * @ingroup Database
 */
class MessageGroupWorkflowStateUpdaterJobTest extends MediaWikiTestCase {

	public function setUp() {
		global $wgTranslateEC, $wgTranslateEC, $wgTranslateAC, $wgHooks;
		$wgHooks['TranslatePostInitGroups'] = array( array( $this, 'getTestGroups' ) );
		$wgTranslateEC = $wgTranslateEC = $wgTranslateAC = array();
		MessageGroups::clearCache();
	}

	public function tearDown() {
		global $wgHooks;
		unset( $wgHooks['TranslatePostInitGroups'] );
		MessageGroups::clearCache();
		//MessageIndexRebuildJob::newJob()->run();
	}

	public function getTestGroups( &$list ) {
		$list['group-trans'] = new MessageGroupWithTransitions();
		$list['group-notrans'] = new MessageGroupWithoutTransitions();
		return false;
	}

	public function testGetGroupsWithTransitions() {
		$handle = new MockMessageHandle();
		$groups = MessageGroupWorkflowStateUpdaterJob::getGroupsWithTransitions( $handle );
		foreach ( $groups as $id => $transitions ) {
			$this->assertEquals( 'group-trans', $id );
		}
	}

	/**
	 * @dataProvider StatValueProvider
	 */
	public function testGetStatValue( $type, $expected ) {
		$stats = array(
			MessageGroupStats::TOTAL => 666,
			MessageGroupStats::FUZZY => 111,
			MessageGroupStats::TRANSLATED => 222,
		);
		$actual = MessageGroupWorkflowStateUpdaterJob::getStatValue( $stats, $type );
		$this->assertEquals( $expected, $actual );
	}

	public function StatValueProvider() {
		return array(
			array( 'UNTRANSLATED', 333 ),
			array( 'OUTDATED', 111 ),
			array( 'TRANSLATED', 222 ),
		);
	}

	/**
	 * @dataProvider MatchConditionProvider
	 */
	public function testMatchCondition( $expected, $value, $condition, $max) {
		$actual = MessageGroupWorkflowStateUpdaterJob::matchCondition( $value, $condition, $max );
		$this->assertEquals( $expected, $actual );
	}


	public function MatchConditionProvider() {
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
		$states = $group->getWorkflowStates();
		$transitions = $states['transitions'];


		$stats = array( 5, 0, 0, 0 );
		$newstate = MessageGroupWorkflowStateUpdaterJob::getNewState( $stats, $transitions );
		$this->assertEquals( 'unset', $newstate, 'all zero, should be unset' );

		$stats = array( 5, 1, 0, 0 );
		$newstate = MessageGroupWorkflowStateUpdaterJob::getNewState( $stats, $transitions );
		$this->assertEquals( 'inprogress', $newstate, 'one translated message' );

		$stats = array( 5, 0, 1, 0 );
		$newstate = MessageGroupWorkflowStateUpdaterJob::getNewState( $stats, $transitions );
		$this->assertEquals( 'inprogress', $newstate, 'one outdated message' );

		$stats = array( 5, 1, 1, 0 );
		$newstate = MessageGroupWorkflowStateUpdaterJob::getNewState( $stats, $transitions );
		$this->assertEquals( 'inprogress', $newstate, 'one translated and one outdated message' );

		$stats = array( 5, 5, 0, 0 );
		$newstate = MessageGroupWorkflowStateUpdaterJob::getNewState( $stats, $transitions );
		$this->assertEquals( 'proofreading', $newstate, 'all translated' );
	}

}

class MockMessageHandle extends MessageHandle {
	public function __construct() {}

	public function getGroupIds() {
		return array( 'group-trans', 'group-notrans' );
	}
}

class MessageGroupWithoutTransitions extends WikiMessageGroup {
	public function __construct() {}

	public function getWorkflowStates() {
		return array();
	}
}

class MessageGroupWithTransitions extends MessageGroupWithoutTransitions {
	public function getWorkflowStates() {
		return array(
			'transitions' => array(
				array( 'unset', array( 'UNTRANSLATED' => 'MAX', 'OUTDATED' => 'ZERO', 'TRANSLATED' => 'ZERO' ) ),
				array( 'inprogress', array( 'UNTRANSLATED' => 'NONZERO' ) ),
				array( 'proofreading', array( 'TRANSLATED' => 'MAX' ) ),
				array( 'ready', array( 'PROOFREAD' => 'MAX' ) ),
			)
		);
	}
}

