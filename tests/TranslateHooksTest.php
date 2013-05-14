<?php
/**
 * Test for various code using hooks.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
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
			'wgTranslateCC' => array(),
			'wgTranslateMessageIndex' => array( 'DatabaseMessageIndex' ),
			'wgTranslateWorkflowStates' => false,
			'wgEnablePageTranslation' => false,
			'wgTranslateGroupFiles' => array(),
			'wgTranslateDocumentationLanguageCode' => 'qqq',
			'wgTranslateTranslationServices' => array(),
		) );
		$wgHooks['TranslatePostInitGroups'] = array( array( $this, 'getTestGroups' ) );
		MessageGroups::clearCache();
		MessageIndexRebuildJob::newJob()->run();
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
		$wikipage->doEdit( '[[Category:Shouldnotbe]]', __METHOD__, 0, false, $user );
		$this->assertEquals( array(), $title->getParentCategories(), 'translation of known message' );

		$title = Title::makeTitle( NS_MEDIAWIKI, 'ugakey2/qqq' );
		$wikipage = WikiPage::factory( $title );
		$wikipage->doEdit( '[[Category:Shouldbe]]', __METHOD__, 0, false, $user );
		$this->assertEquals( array( 'Category:Shouldbe' => 'MediaWiki:ugakey2/qqq' ), $title->getParentCategories(), 'message docs' );

		$title = Title::makeTitle( NS_MEDIAWIKI, 'ugakey3/no' );
		$wikipage = WikiPage::factory( $title );
		$wikipage->doEdit( '[[Category:Shouldbealso]]', __METHOD__, 0, false, $user );
		$this->assertEquals( array(), $title->getParentCategories(), 'unknown message' );
	}
}


