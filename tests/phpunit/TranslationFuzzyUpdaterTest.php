<?php
/**
 * Tests for fuzzy flag change on edits.
 * @author Niklas Laxström
 * @file
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Title\Title;

/**
 * Tests for fuzzy flag change on edits.
 * @group Database
 * @group medium
 * @covers MediaWiki\Extension\Translate\TranslatorInterface\TranslateEditAddons
 */
class TranslationFuzzyUpdaterTest extends MediaWikiIntegrationTestCase {
	use MessageGroupTestTrait;

	protected function setUp(): void {
		parent::setUp();
		$this->setupGroupTestEnvironmentWithGroups( $this, $this->getTestGroups() );
	}

	public function getTestGroups() {
		$messages = [ 'ugakey' => '$1 of $2', ];
		$list['test-group'] = new MockWikiMessageGroup( 'test-group', $messages );

		$otherMessages = [ 'nlkey' => 'Test message' ];
		$list['validation-test-group'] = new MockWikiValidationMessageGroup(
			'validation-test-group', $otherMessages );

		return $list;
	}

	public function testParsing() {
		$user = $this->getTestUser()->getUser();
		$container = $this->getServiceContainer();
		$title = Title::newFromText( 'MediaWiki:Ugakey/nl' );
		$page = $container->getWikiPageFactory()->newFromTitle( $title );
		$content = ContentHandler::makeContent( '$1 van $2', $title );
		$status = $page->doUserEditContent( $content, $user, __METHOD__ );
		$value = $status->getValue();
		/** @var RevisionRecord $revisionRecord */
		$revisionRecord = $value['revision-record'];
		$revisionId = $revisionRecord->getId();

		$dbw = $this->getDb();
		$conds = [
			'rt_page' => $title->getArticleID(),
			'rt_type' => RevTagStore::FUZZY_TAG,
			'rt_revision' => $revisionId
		];

		$index = array_keys( $conds );
		$dbw->replace( 'revtag', [ $index ], $conds, __METHOD__ );

		$handle = new MessageHandle( $title );
		$this->assertTrue( $handle->isValid(), 'Message is known' );
		$this->assertTrue( $handle->isFuzzy(), 'Message is fuzzy after database fuzzying' );

		$permissionManager = $container->getPermissionManager();

		// Update the translation without the fuzzy string as a user without the unfuzzy right
		$permissionManager->overrideUserRightsForTesting( $user, [] );
		$content = ContentHandler::makeContent( '$1 van $2', $title );
		$page->doUserEditContent( $content, $user, __METHOD__ );
		$this->assertTrue( $handle->isFuzzy(), 'Message is still fuzzy after edit without required permissions' );

		// Now add the required rights and try that again
		$rightsCallback = $permissionManager->addTemporaryUserRights( $user,
			[ 'editinterface', 'unfuzzy' ]
		);
		$content = ContentHandler::makeContent( '$1 van $2 new', $title );
		$page->doUserEditContent( $content, $user, __METHOD__ );
		$this->assertFalse( $handle->isFuzzy(), 'Message is no longer fuzzy after editing with required permissions' );

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
		$page = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title );
		$content = ContentHandler::makeContent( 'Test message', $title );
		$page->doUserEditContent( $content, $user, __METHOD__ );

		$handle = new MessageHandle( $title );
		$this->assertTrue( $handle->isValid(), 'Message is known' );
		$this->assertTrue( $handle->isFuzzy(), 'Message is fuzzy due to validation failure' );
	}
}
