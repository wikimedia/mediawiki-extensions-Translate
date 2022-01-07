<?php
/**
 * Tests for fuzzy flag change on edits.
 * @author Niklas Laxström
 * @file
 * @license GPL-2.0-or-later
 */

use MediaWiki\Revision\RevisionRecord;

/**
 * Tests for fuzzy flag change on edits.
 * @group Database
 * @group medium
 */
class TranslationFuzzyUpdaterTest extends MediaWikiIntegrationTestCase {
	protected function setUp(): void {
		parent::setUp();

		$this->setMwGlobals( [
			'wgTranslateTranslationServices' => [],
			'wgTranslateMessageNamespaces' => [ NS_MEDIAWIKI ],
		] );
		$this->setTemporaryHook( 'TranslatePostInitGroups', [ $this, 'getTestGroups' ] );

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => new HashBagOStuff() ] ) );
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
		$user = $this->getTestUser()->getUser();
		$title = Title::newFromText( 'MediaWiki:Ugakey/nl' );
		$page = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( '$1 van $2', $title );
		$status = $page->doUserEditContent( $content, $user, __METHOD__ );
		$value = $status->getValue();
		/** @var RevisionRecord $revisionRecord */
		$revisionRecord = $value['revision-record'];
		$revisionId = $revisionRecord->getId();

		$dbw = wfGetDB( DB_PRIMARY );
		$conds = [
			'rt_page' => $title->getArticleID(),
			'rt_type' => RevTag::getType( 'fuzzy' ),
			'rt_revision' => $revisionId
		];

		$index = array_keys( $conds );
		$dbw->replace( 'revtag', [ $index ], $conds, __METHOD__ );

		$handle = new MessageHandle( $title );
		$this->assertTrue( $handle->isValid(), 'Message is known' );
		$this->assertTrue( $handle->isFuzzy(), 'Message is fuzzy after database fuzzying' );
		// Update the translation without the fuzzy string
		$content = ContentHandler::makeContent( '$1 van $2', $title );
		$page->doUserEditContent( $content, $user, __METHOD__ );
		$this->assertFalse( $handle->isFuzzy(), 'Message is unfuzzy after edit' );

		$content = ContentHandler::makeContent( '!!FUZZY!!$1 van $2', $title );
		$page->doUserEditContent( $content, $user, __METHOD__ );
		$this->assertTrue( $handle->isFuzzy(), 'Message is fuzzy after manual fuzzying' );

		// Update the translation without the fuzzy string
		$content = ContentHandler::makeContent( '$1 van $2', $title );
		$page->doUserEditContent( $content, $user, __METHOD__ );
		$this->assertFalse( $handle->isFuzzy(), 'Message is unfuzzy after edit' );
	}

	public function testValidationFuzzy() {
		$user = $this->getTestUser()->getUser();
		$title = Title::newFromText( 'MediaWiki:nlkey/en-gb' );
		$page = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( 'Test message', $title );
		$page->doUserEditContent( $content, $user, __METHOD__ );

		$handle = new MessageHandle( $title );
		$this->assertTrue( $handle->isValid(), 'Message is known' );
		$this->assertTrue( $handle->isFuzzy(), 'Message is fuzzy due to validation failure' );
	}
}
