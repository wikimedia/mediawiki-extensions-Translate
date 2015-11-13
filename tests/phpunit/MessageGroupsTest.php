<?php
/**
 * Unit tests.
 *
 * @author Niklas Laxström
 * @file
 * @license GPL-2.0+
 */

/**
 * @group Database
 * ^ See AggregateMessageGroup::getGroups -> MessageGroups::getPriority
 */
class MessageGroupsTest extends MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();

		$conf = array(
			__DIR__ . '/data/ParentGroups.yaml',
		);

		global $wgHooks;
		$this->setMwGlobals( array(
			'wgHooks' => $wgHooks,
			'wgTranslateGroupFiles' => $conf,
			'wgTranslateTranslationServices' => array(),
			'wgTranslateMessageNamespaces' => array( NS_MEDIAWIKI ),
		) );
		$wgHooks['TranslatePostInitGroups'] = array( 'MessageGroups::getConfiguredGroups' );

		$mg = MessageGroups::singleton();
		$mg->setCache( wfGetCache( 'hash' ) );
		$mg->recache();

		MessageIndex::setInstance( new HashMessageIndex() );
		MessageIndex::singleton()->rebuild();
	}

	/**
	 * @dataProvider provideGroups
	 */
	public function testGetParentGroups( $expected, $target ) {
		$group = MessageGroups::getGroup( $target );
		$got = MessageGroups::getParentGroups( $group );
		$this->assertEquals( $expected, $got );
	}

	public static function provideGroups() {
		$cases = array();
		$cases[] = array(
			array( array( 'root1' ), array( 'root2' ) ),
			'twoparents'
		);

		$cases[] = array(
			array( array( 'root3', 'sub1' ), array( 'root3', 'sub2' ) ),
			'oneparent-twopaths'
		);

		$cases[] = array(
			array(
				array( 'root4' ),
				array( 'root4', 'nested1' ),
				array( 'root4', 'nested1', 'nested2' ),
				array( 'root4', 'nested2' ),
			),
			'multilevelnested'
		);

		return $cases;
	}

	public function testHaveSingleSourceLanguage() {
		$this->setMwGlobals( array(
			'wgTranslateGroupFiles' => array( __DIR__ . '/data/MixedSourceLanguageGroups.yaml' ),
		) );
		MessageGroups::singleton()->recache();

		$enGroup1 = MessageGroups::getGroup( 'EnglishGroup1' );
		$enGroup2 = MessageGroups::getGroup( 'EnglishGroup2' );
		$teGroup1 = MessageGroups::getGroup( 'TeluguGroup1' );

		$this->assertEquals( 'en', MessageGroups::haveSingleSourceLanguage(
			array( $enGroup1, $enGroup2 ) )
		);
		$this->assertEquals( '', MessageGroups::haveSingleSourceLanguage(
			array( $enGroup1, $enGroup2, $teGroup1 ) )
		);
	}
}
