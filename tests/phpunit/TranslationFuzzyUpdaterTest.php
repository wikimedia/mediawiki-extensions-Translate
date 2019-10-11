<?php
/**
 * Tests for fuzzy flag change on edits.
 * @author Niklas Laxström
 * @file
 * @license GPL-2.0-or-later
 */

/**
 * Tests for fuzzy flag change on edits.
 * @group Database
 * @group medium
 */
class TranslationFuzzyUpdaterTest extends MediaWikiTestCase {
	protected function setUp() : void {
		parent::setUp();

		global $wgHooks;
		$this->setMwGlobals( [
			'wgHooks' => $wgHooks,
			'wgTranslateTranslationServices' => [],
			'wgTranslateMessageNamespaces' => [ NS_MEDIAWIKI ],
		] );
		$wgHooks['TranslatePostInitGroups'] = [ [ $this, 'getTestGroups' ] ];

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => wfGetCache( 'hash' ) ] ) );
		$mg->recache();

		MessageIndex::setInstance( new HashMessageIndex() );
		MessageIndex::singleton()->rebuild();
	}

	public function getTestGroups( &$list ) {
		$messages = [ 'ugakey' => '$1 of $2', ];
		$list['test-group'] = new MockWikiMessageGroup( 'test-group', $messages );

		$otherMessages = [ 'nlkey' => 'Test message' ];
		$list['validation-test-group'] = new MockWikiValidationMessageGroup(
			'validation-test-group', $otherMessages );

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
		$conds = [
			'rt_page' => $title->getArticleID(),
			'rt_type' => RevTag::getType( 'fuzzy' ),
			'rt_revision' => $revision
		];

		$index = array_keys( $conds );
		$dbw->replace( 'revtag', [ $index ], $conds, __METHOD__ );

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

	public function testValidationFuzzy() {
		$title = Title::newFromText( 'MediaWiki:nlkey/en-gb' );
		$page = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( 'Test message', $title );
		$page->doEditContent( $content, __METHOD__ );

		$handle = new MessageHandle( $title );
		$this->assertTrue( $handle->isValid(), 'Message is known' );
		$this->assertTrue( $handle->isFuzzy(), 'Message is fuzzy due to validation failure' );
	}
}
