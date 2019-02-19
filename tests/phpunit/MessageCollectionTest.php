<?php
/**
 * @author Niklas Laxström
 * @file
 * @license GPL-2.0-or-later
 */

/**
 * @group Database
 * @group medium
 */
class MessageCollectionTest extends MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();

		global $wgHooks;
		$this->setMwGlobals( [
			'wgHooks' => $wgHooks,
			'wgTranslateTranslationServices' => [],
		] );
		$wgHooks['TranslatePostInitGroups'] = [ [ $this, 'getTestGroups' ] ];

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => wfGetCache( 'hash' ) ] ) );
		$mg->recache();

		MessageIndex::setInstance( new HashMessageIndex() );
		MessageIndex::singleton()->rebuild();
	}

	public function getTestGroups( &$list ) {
		$messages = [
			'translated' => 'bunny',
			'untranslated' => 'fanny',
		];
		$list['test-group'] = new MockWikiMessageGroup( 'test-group', $messages );

		return false;
	}

	public function testMessage() {
		$user = $this->getTestSysop()->getUser();
		$title = Title::newFromText( 'MediaWiki:translated/fi' );
		$page = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( 'pupuliini', $title );

		$status = $page->doEditContent( $content, __METHOD__, 0, false, $user );

		$value = $status->getValue();
		$rev = $value['revision'];
		$revision = $rev->getId();

		$group = MessageGroups::getGroup( 'test-group' );
		$collection = $group->initCollection( 'fi' );
		$collection->loadTranslations();

		/** @var TMessage $translated */
		$translated = $collection['translated'];
		$this->assertInstanceOf( 'TMessage', $translated );
		$this->assertEquals( 'translated', $translated->key() );
		$this->assertEquals( 'bunny', $translated->definition() );
		$this->assertEquals( 'pupuliini', $translated->translation() );
		$this->assertEquals( $user->getName(), $translated->getProperty( 'last-translator-text' ) );
		$this->assertEquals( $user->getId(), $translated->getProperty( 'last-translator-id' ) );
		$this->assertEquals(
			'translated',
			$translated->getProperty( 'status' ),
			'message status is translated'
		);
		$this->assertEquals( $revision, $translated->getProperty( 'revision' ) );

		/** @var TMessage $untranslated */
		$untranslated = $collection['untranslated'];
		$this->assertInstanceOf( 'TMessage', $untranslated );
		$this->assertEquals( null, $untranslated->translation(), 'no translation is null' );
		$this->assertEquals( false, $untranslated->getProperty( 'last-translator-text' ) );
		$this->assertEquals( false, $untranslated->getProperty( 'last-translator-id' ) );
		$this->assertEquals(
			'untranslated',
			$untranslated->getProperty( 'status' ),
			'message status is untranslated'
		);
		$this->assertEquals( false, $untranslated->getProperty( 'revision' ) );
	}
}
