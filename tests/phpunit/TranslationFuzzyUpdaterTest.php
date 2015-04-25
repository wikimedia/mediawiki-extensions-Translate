<?php
/**
 * Tests for fuzzy flag change on edits.
 * @author Niklas Laxström
 * @file
 * @license GPL-2.0+
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
		$messages = array( 'ugakey' => '$1 of $2', );
		$list['test-group'] = new MockWikiMessageGroup( 'test-group', $messages );

		return false;
	}

	public function testParsing() {
		$title = Title::newFromText( 'MediaWiki:Ugakey/nl' );
		$page = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( '$1 van $2', $title );
		$status = $page->doEditContent( $content, __METHOD__ );
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
		$content = ContentHandler::makeContent( '$1 van $2', $title );
		$page->doEditContent( $content, __METHOD__ );
		$this->assertFalse( $handle->isFuzzy(), 'Message is unfuzzy after edit' );

		$content = ContentHandler::makeContent( '!!FUZZY!!$1 van $2', $title );
		$page->doEditContent( $content, __METHOD__ );
		$this->assertTrue( $handle->isFuzzy(), 'Message is fuzzy after manual fuzzying' );

		// Update the translation without the fuzzy string
		$content = ContentHandler::makeContent( '$1 van $2', $title );
		$page->doEditContent( $content, __METHOD__ );
		$this->assertFalse( $handle->isFuzzy(), 'Message is unfuzzy after edit' );
	}
}
