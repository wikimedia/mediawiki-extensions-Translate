<?php
/**
 * Unit tests.
 *
 * @author Niklas Laxström
 * @file
 * @license GPL-2.0-or-later
 */

/**
 * @group Database
 * ^ See AggregateMessageGroup::getGroups -> MessageGroups::getPriority
 */
class MessageGroupsTest extends MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();

		$conf = [
			__DIR__ . '/data/ParentGroups.yaml',
		];

		global $wgHooks;
		$this->setMwGlobals( [
			'wgHooks' => $wgHooks,
			'wgTranslateGroupFiles' => $conf,
			'wgTranslateTranslationServices' => [],
			'wgTranslateMessageNamespaces' => [ NS_MEDIAWIKI ],
		] );
		$wgHooks['TranslatePostInitGroups'] = [ 'MessageGroups::getConfiguredGroups' ];

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
		$cases = [];
		$cases[] = [
			[ [ 'root1' ], [ 'root2' ] ],
			'twoparents'
		];

		$cases[] = [
			[ [ 'root3', 'sub1' ], [ 'root3', 'sub2' ] ],
			'oneparent-twopaths'
		];

		$cases[] = [
			[
				[ 'root4' ],
				[ 'root4', 'nested1' ],
				[ 'root4', 'nested1', 'nested2' ],
				[ 'root4', 'nested2' ],
			],
			'multilevelnested'
		];

		return $cases;
	}

	public function testHaveSingleSourceLanguage() {
		$this->setMwGlobals( [
			'wgTranslateGroupFiles' => [ __DIR__ . '/data/MixedSourceLanguageGroups.yaml' ],
		] );
		MessageGroups::singleton()->recache();

		$enGroup1 = MessageGroups::getGroup( 'EnglishGroup1' );
		$enGroup2 = MessageGroups::getGroup( 'EnglishGroup2' );
		$teGroup1 = MessageGroups::getGroup( 'TeluguGroup1' );

		$this->assertEquals( 'en', MessageGroups::haveSingleSourceLanguage(
			[ $enGroup1, $enGroup2 ] )
		);
		$this->assertEquals( '', MessageGroups::haveSingleSourceLanguage(
			[ $enGroup1, $enGroup2, $teGroup1 ] )
		);
	}
}
