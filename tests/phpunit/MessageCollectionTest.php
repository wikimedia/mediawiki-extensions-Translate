<?php
/**
 * Tests for MessageCollection.
 * @author Niklas Laxström
 * @file
 * @license GPL-2.0+
 */

/**
 * Tests for MessageCollection.
 * @group Database
 * @group medium
 */
class MessageCollectionTest extends MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();

		global $wgHooks;
		$this->setMwGlobals( array(
			'wgHooks' => $wgHooks,
			'wgTranslateTranslationServices' => array(),
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
			'translated' => 'bunny',
			'untranslated' => 'fanny',
		);
		$list['test-group'] = new MockWikiMessageGroup( 'test-group', $messages );

		return false;
	}

	public function testMessage() {
		$user = new MockSuperUser();
		$user->setId( 123 );
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
		$this->assertEquals( 'SuperUser', $translated->getProperty( 'last-translator-text' ) );
		$this->assertEquals( 123, $translated->getProperty( 'last-translator-id' ) );
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
