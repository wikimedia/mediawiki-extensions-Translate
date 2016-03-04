<?php
/**
 * Tests for SolrTTMServer
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * @group large
 * @group Database
 */
class SolrTTMServerTest extends MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();

		global $wgHooks, $wgTranslateTranslationServices, $wgTranslateTestTTMServer;
		$this->setMwGlobals( array(
			'wgHooks' => $wgHooks,
			'wgTranslateTranslationServices' => array(),
		) );
		$wgTranslateTranslationServices['TTMServer'] = $wgTranslateTestTTMServer;

		$wgHooks['TranslatePostInitGroups'] = array( array( $this, 'addGroups' ) );

		$mg = MessageGroups::singleton();
		$mg->setCache( wfGetCache( 'hash' ) );
		$mg->recache();

		MessageIndex::setInstance( new HashMessageIndex() );
		MessageIndex::singleton()->rebuild();
	}

	public function addGroups( &$list ) {
		$list['ttmserver-test'] = new MockWikiMessageGroup( 'ttmserver-test', array(
			'one' => '1', 'two' => 'kaksi', 'three' => '3' ) );

		return true;
	}

	public function testSearchableTTMServer() {
		global $wgTranslateTestTTMServer;
		if ( !$wgTranslateTestTTMServer ) {
			$this->markTestSkipped( 'No test TTMServer available' );
		}

		$server = TTMServer::factory( $wgTranslateTestTTMServer );
		$solarium = $server->getSolarium();

		// Empty it
		$update = $solarium->createUpdate();
		$update->addDeleteQuery( '*:*' );
		$update->addCommit();
		$solarium->update( $update );

		// Check that it is empty indeed
		$select = $solarium->createSelect();
		$select->setRows( 0 );
		$select->setQuery( '*:*' );
		$result = $solarium->select( $select );
		$this->assertEquals( 0, $result->getNumFound() );

		// Add one definition
		$title = Title::newFromText( 'MediaWiki:one/en' );
		$page = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( '1', $title );
		$page->doEditContent( $content, __METHOD__ );

		$select = $solarium->createSelect();
		$select->setRows( 1 );
		$select->setQuery( '*:*' );
		$result = $solarium->select( $select );
		$this->assertEquals( 1, $result->getNumFound() );
		$doc = null;
		foreach ( $result as $doc ) {
			// @todo FIXME Empty foreach statement.
		}
		$this->assertEquals( wfWikiID(), $doc->wiki );
		$this->assertEquals( 'en', $doc->language );
		$this->assertEquals( '1', $doc->content );
		$this->assertEquals( array( 'ttmserver-test' ), $doc->group );

		// Add one translation
		$title = Title::newFromText( 'MediaWiki:one/fi' );
		$page = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( 'yksi', $title );
		$page->doEditContent( $content, __METHOD__ );

		$select = $solarium->createSelect();
		$select->setRows( 1 );
		$select->setQuery( 'language:fi' );
		$result = $solarium->select( $select );
		$this->assertEquals( 1, $result->getNumFound() );
		$doc = null;
		foreach ( $result as $doc ) {
			// @todo FIXME Empty foreach statement.
		}
		$this->assertEquals( 'yksi', $doc->content );
		$this->assertEquals( array( 'ttmserver-test' ), $doc->group );

		// Update definition
		$title = Title::newFromText( 'MediaWiki:one/en' );
		$page = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( '1-updated', $title );
		$page->doEditContent( $content, __METHOD__ );

		$select = $solarium->createSelect();
		$select->setQuery( 'language:en' );
		$result = $solarium->select( $select );
		$this->assertEquals( 2, $result->getNumFound(), 'Old and new definition exists' );

		// Translation is fuzzied
		$title = Title::newFromText( 'MediaWiki:one/fi' );
		$page = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( '!!FUZZY!!yksi', $title );
		$page->doEditContent( $content, __METHOD__ );

		$select = $solarium->createSelect();
		$select->setQuery( 'language:fi' );
		$result = $solarium->select( $select );
		$this->assertEquals( 0, $result->getNumFound() );

		// Translation is udpated
		$title = Title::newFromText( 'MediaWiki:one/fi' );
		$page = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( 'yksi-päiv', $title );
		$page->doEditContent( $content, __METHOD__ );

		$select = $solarium->createSelect();
		$select->setQuery( 'language:fi' );
		$result = $solarium->select( $select );
		$this->assertEquals( 1, $result->getNumFound() );
		$doc = null;
		foreach ( $result as $doc ) {
			// @todo FIXME Empty foreach statement.
		}
		$this->assertEquals( 'yksi-päiv', $doc->content );

		// And now the messages should be orphaned
		global $wgHooks;
		$wgHooks['TranslatePostInitGroups'] = array();
		MessageGroups::singleton()->recache();
		MessageIndex::singleton()->rebuild();
		self::runJobs();

		$select = $solarium->createSelect();
		$select->setQuery( '*:*' );
		$result = $solarium->select( $select );
		$this->assertEquals( 2, $result->getNumFound(), 'One definition and one translation exists' );
		foreach ( $result as $doc ) {
			$this->assertEquals( null, $doc->group, 'Messages are orphaned' );
		}

		// And message deletion
		$title = Title::newFromText( 'MediaWiki:one/fi' );
		$page = WikiPage::factory( $title );
		$page->doDeleteArticle( __METHOD__ );

		$select = $solarium->createSelect();
		$select->setQuery( 'language:fi' );
		$result = $solarium->select( $select );
		$this->assertEquals( 0, $result->getNumFound() );
	}

	protected static function runJobs() {
		do {
			$job = JobQueueGroup::singleton()->pop();
			if ( !$job ) {
				break;
			}
			$job->run();
		} while ( true );
	}
}
