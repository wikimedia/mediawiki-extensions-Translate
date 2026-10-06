<?php
/**
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @file
 */

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageGroups\WikiPageMessageGroup;
use MediaWiki\Extension\Translate\PageTranslation\TranslationUnit;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Validation\ValidationRunner;
use MediaWiki\Title\Title;

/**
 * @group medium
 * @group Database
 * @covers \MediaWiki\Extension\Translate\MessageGroups\WikiPageMessageGroup
 */
class WikiPageMessageGroupTest extends MediaWikiIntegrationTestCase {
	protected function setUp(): void {
		parent::setUp();
		MessageGroups::singleton()->overrideGroupsForTesting( $this->getTestGroups() );
	}

	public function getTestGroups(): array {
		$anotherPageMessageGroup = new WikiPageMessageGroup( 'anotherpageid', 'mypage' );
		$anotherPageMessageGroup->setLabel( 'thelabel' ); // Example
		$anotherPageMessageGroup->setNamespace( 5 ); // Example

		$list['anotherpageid'] = $anotherPageMessageGroup;
		return $list;
	}

	public function testMessageValidator() {
		$group = MessageGroups::getGroup( 'anotherpageid' );
		$msgValidator = $group->getValidator();

		$this->assertInstanceOf( ValidationRunner::class, $msgValidator,
			'returns a valid object of ValidationRunner class.' );
	}

	public function testGetDefinitionsPreservesOrderVariablesAndCaching(): void {
		$title = $this->insertPage( 'Definition test' )['title'];
		$store = Services::getInstance()->getTranslationUnitStoreFactory()->getWriter( $title );
		$store->store( [
			new TranslationUnit( 'Second <tvar name="link">[[Example]]</tvar>', '2' ),
			new TranslationUnit( 'Display title', 'Page display title' ),
			new TranslationUnit( 'First', '1' ),
		] );
		$group = new WikiPageMessageGroup( 'definition-test', $title->getPrefixedText() );
		$expected = [
			'Definition_test/2' => 'Second $link',
			'Definition_test/Page_display_title' => 'Display title',
			'Definition_test/1' => 'First',
		];
		$this->assertSame( $expected, $group->getDefinitions() );

		$store->store( [ new TranslationUnit( 'Changed', '1' ) ] );
		$this->assertSame( $expected, $group->getDefinitions() );
	}

	public function testGetDefinitionsForMissingPage(): void {
		$group = new WikiPageMessageGroup( 'missing-page', 'Missing definition page' );
		$this->assertSame( [], $group->getDefinitions() );
	}

	public function testGetDefinitionsResolvesTitleWithCachedMissingPageId(): void {
		$title = Title::newFromText( 'New definition page' );
		$this->assertSame( 0, $title->getArticleID() );
		$page = $this->insertPage( 'New definition page' );
		$store = Services::getInstance()->getTranslationUnitStoreFactory()->getWriter( $page['title'] );
		$store->store( [ new TranslationUnit( 'New text', '1' ) ] );

		$group = new WikiPageMessageGroup( 'new-page', $title );
		$this->assertSame( [ 'New_definition_page/1' => 'New text' ], $group->getDefinitions() );
	}
}
