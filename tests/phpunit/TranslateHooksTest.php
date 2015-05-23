<?php
/**
 * Test for various code using hooks.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * @group Database
 * @group medium
 */
class TranslateHooksTest extends MediaWikiLangTestCase {
	protected function setUp() {
		parent::setUp();

		global $wgHooks;
		$this->setMwGlobals( array(
			'wgHooks' => $wgHooks,
			'wgTranslateDocumentationLanguageCode' => 'qqq',
			'wgTranslateTranslationServices' => array(),
			'wgTranslateMessageNamespaces' => array( NS_MEDIAWIKI ),
		) );
		$wgHooks['TranslatePostInitGroups'] = array( array( $this, 'getTestGroups' ) );

		$mg = MessageGroups::singleton();
		$mg->setCache( wfGetCache( 'hash' ) );
		$mg->recache();

		MessageIndex::setInstance( new HashMessageIndex() );
		MessageIndex::singleton()->rebuild();
	}

	public function getTestGroups( &$list ) {
		$messages = array(
			'ugakey1' => 'value1',
			'ugakey2' => 'value2',
		);

		$list['testgroup'] = new MockWikiMessageGroup( 'testgroup', $messages );

		return false;
	}

	public function testPreventCategorization() {
		$user = new MockSuperUser();
		$title = Title::makeTitle( NS_MEDIAWIKI, 'ugakey1/fi' );
		$wikipage = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( '[[Category:Shouldnotbe]]', $title );

		$wikipage->doEditContent( $content, __METHOD__, 0, false, $user );
		$this->assertEquals(
			array(),
			$title->getParentCategories(),
			'translation of known message'
		);

		$title = Title::makeTitle( NS_MEDIAWIKI, 'ugakey2/qqq' );
		$wikipage = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( '[[Category:Shouldbe]]', $title );

		$wikipage->doEditContent( $content, __METHOD__, 0, false, $user );
		$this->assertEquals(
			array( 'Category:Shouldbe' => 'MediaWiki:ugakey2/qqq' ),
			$title->getParentCategories(),
			'message docs'
		);

		$title = Title::makeTitle( NS_MEDIAWIKI, 'ugakey3/no' );
		$wikipage = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( '[[Category:Shouldbealso]]', $title );

		$wikipage->doEditContent( $content, __METHOD__, 0, false, $user );
		$this->assertEquals( array(), $title->getParentCategories(), 'unknown message' );
	}

	public function testSearchProfile() {
		$profiles = array(
			'files' => array(),
			'all' => array(),
			'advanced' => array()
		);

		$expected = array( 'files', 'translation', 'all', 'advanced' );

		TranslateHooks::searchProfile( $profiles );

		$this->assertEquals( $expected, array_keys( $profiles ) );
	}

}
