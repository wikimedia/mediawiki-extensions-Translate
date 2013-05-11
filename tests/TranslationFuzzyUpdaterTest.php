<?php
/**
 * Tests for fuzzy flag change on edits.
 * @author Niklas Laxström
 * @file
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Tests for fuzzy flag change on edits.
 * @group Database
 * @group medium
 */
class TranslationFuzzyUpdaterTest extends MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();

		global $wgHooks;
		$this->setMwGlobals( array(
			'wgHooks' => $wgHooks,
			'wgTranslateCC' => array(),
			'wgTranslateMessageIndex' => array( 'DatabaseMessageIndex' ),
			'wgTranslateWorkflowStates' => false,
			'wgTranslateGroupFiles' => array(),
			'wgTranslateTranslationServices' => array(),
		) );
		$wgHooks['TranslatePostInitGroups'] = array( array( $this, 'getTestGroups' ) );
		MessageGroups::clearCache();
		MessageIndexRebuildJob::newJob()->run();
	}

	public function getTestGroups( &$list ) {
		$messages = array( 'ugakey' => '$1 of $2', );
		$list['test-group'] = new MockWikiMessageGroup( 'test-group', $messages );

		return false;
	}

	public function testParsing() {
		$title = Title::newFromText( 'MediaWiki:Ugakey/nl' );
		$page = WikiPage::factory( $title );
		$status = $page->doEdit( '$1 van $2', __METHOD__ );
		$value = $status->getValue();
		/**
		 * @var Revision $rev
		 */
		$rev = $value['revision'];
		$revision = $rev->getId();

		$dbw = wfGetDB( DB_MASTER );
		$conds = array(
			'rt_page' => $title->getArticleID(),
			'rt_type' => RevTag::getType( 'fuzzy' ),
			'rt_revision' => $revision
		);

		$index = array_keys( $conds );
		$dbw->replace( 'revtag', array( $index ), $conds, __METHOD__ );

		$handle = new MessageHandle( $title );
		$this->assertTrue( $handle->isValid(), 'Message is known' );
		$this->assertTrue( $handle->isFuzzy(), 'Message is fuzzy after database fuzzying' );
		// Update the translation without the fuzzy string
		$page->doEdit( '$1 van $2', __METHOD__ );
		$this->assertFalse( $handle->isFuzzy(), 'Message is unfuzzy after edit' );

		$page->doEdit( '!!FUZZY!!$1 van $2', __METHOD__ );
		$this->assertTrue( $handle->isFuzzy(), 'Message is fuzzy after manual fuzzying' );

		// Update the translation without the fuzzy string
		$page->doEdit( '$1 van $2', __METHOD__ );
		$this->assertFalse( $handle->isFuzzy(), 'Message is unfuzzy after edit' );
	}
}
