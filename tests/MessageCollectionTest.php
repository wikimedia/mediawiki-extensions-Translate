<?php
/**
 * Tests for MessageCollection.
 * @author Niklas Laxström
 * @file
 * @copyright Copyright © 2013, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
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
		$status = $page->doEdit( 'pupuliini', __METHOD__, false, 0, $user );
		$value = $status->getValue();
		$rev = $value['revision'];
		$revision = $rev->getId();

		$group = MessageGroups::getGroup( 'test-group' );
		$collection = $group->initCollection( 'fi' );
		$collection->loadTranslations();

		$translated = $collection['translated'];
		$this->assertInstanceof( 'TMessage', $translated );
		$this->assertEquals( 'translated', $translated->key() );
		$this->assertEquals( 'bunny', $translated->definition() );
		$this->assertEquals( 'pupuliini', $translated->translation() );
		$this->assertEquals( 'SuperUser', $translated->getProperty( 'last-translator-text' ) );
		$this->assertEquals( 123, $translated->getProperty( 'last-translator-id' ) );
		$this->assertEquals( 'translated', $translated->getProperty( 'status' ), 'message status is translated' );
		$this->assertEquals( $revision, $translated->getProperty( 'revision' ) );

		$untranslated = $collection['untranslated'];
		$this->assertInstanceof( 'TMessage', $untranslated );
		$this->assertEquals( null, $translated->translation(), 'no translation is null' );
		$this->assertEquals( false, $translated->getProperty( 'last-translator-text' ) );
		$this->assertEquals( false, $translated->getProperty( 'last-translator-id' ) );
		$this->assertEquals( 'untranslated', $translated->getProperty( 'status' ), 'message status is untranslated' );
		$this->assertEquals( false, $translated->getProperty( 'revision' ) );
	}
}
