<?php
/**
 * Tests for SolrTTMServer
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2013, Niklas Laxström
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
			'wgTranslateCC' => array(),
			'wgTranslateMessageIndex' => array( 'DatabaseMessageIndex' ),
			'wgTranslateWorkflowStates' => false,
			'wgEnablePageTranslation' => false,
			'wgTranslateGroupFiles' => array(),
			'wgTranslateTranslationServices' => array(),
		) );
		$wgTranslateTranslationServices['TTMServer'] = $wgTranslateTestTTMServer;

		$wgHooks['TranslatePostInitGroups'] = array( array( $this, 'addGroups' ) );
		MessageGroups::clearCache();
		MessageIndexRebuildJob::newJob()->run();
		// Also clear the "old" value when running multiple tests together
		MessageIndexRebuildJob::newJob()->run();
		self::runJobs();
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
		$page->doEdit( '1', __METHOD__ );

		$select = $solarium->createSelect();
		$select->setRows( 1 );
		$select->setQuery( '*:*' );
		$result = $solarium->select( $select );
		$this->assertEquals( 1, $result->getNumFound() );
		$doc = null;
		foreach ( $result as $doc ) {
			// @todo FIXME Empty foreach statement.
		}
		$this->assertEquals( wfWikiId(), $doc->wiki );
		$this->assertEquals( 'en', $doc->language );
		$this->assertEquals( '1', $doc->content );
		$this->assertEquals( array( 'ttmserver-test' ), $doc->group );

		// Add one translation
		$title = Title::newFromText( 'MediaWiki:one/fi' );
		$page = WikiPage::factory( $title );
		$page->doEdit( 'yksi', __METHOD__ );

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
		$page->doEdit( '1-updated', __METHOD__ );

		$select = $solarium->createSelect();
		$select->setQuery( 'language:en' );
		$result = $solarium->select( $select );
		$this->assertEquals( 2, $result->getNumFound(), 'Old and new definition exists' );

		// Translation is fuzzied
		$title = Title::newFromText( 'MediaWiki:one/fi' );
		$page = WikiPage::factory( $title );
		$page->doEdit( '!!FUZZY!!yksi', __METHOD__ );

		$select = $solarium->createSelect();
		$select->setQuery( 'language:fi' );
		$result = $solarium->select( $select );
		$this->assertEquals( 0, $result->getNumFound() );

		// Translation is udpated
		$title = Title::newFromText( 'MediaWiki:one/fi' );
		$page = WikiPage::factory( $title );
		$page->doEdit( 'yksi-päiv', __METHOD__ );

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
		MessageGroups::clearCache();
		MessageIndexRebuildJob::newJob()->run();
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
			$job = Job::pop();
			if ( !$job ) {
				break;
			}
			$job->run();
		} while ( true );
	}
}
