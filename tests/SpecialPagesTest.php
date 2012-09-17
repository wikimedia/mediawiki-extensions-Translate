<?php
/**
 * General unit tests for special pages.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

require_once( __DIR__ . '/SuperUser.php' );
/**
 * Unit tests for making sure special pages execute
 * @group Database
 */
class SpecialPagesTest extends MediaWikiTestCase {
	public function setUp() {
		global $wgTranslateCacheDirectory, $wgTranslateMessageIndex;
		// Only in 1.20, but who runs tests again older versions anyway?
		$wgTranslateCacheDirectory = $this->getNewTempDirectory();
		$wgTranslateMessageIndex = array( 'DatabaseMessageIndex' );
		// For User::editToken
		global $wgDeprecationReleaseLimit;
		$wgDeprecationReleaseLimit = 1.18;
	}

	public function specialPages() {
		require( __DIR__ . '/../_autoload.php' );
		global $wgSpecialPages;

		$pages = array();
		foreach ( $wgSpecialPages as $name => $class ) {
			if ( isset( $wgAutoloadClasses[$class] )) {
				$pages[] = array( $name );
			}
		}
		return $pages;
	}

	/**
	 * @dataProvider specialPages
	 */
	public function testSpecialPage( $name ) {
		$page = SpecialPageFactory::getPage( $name );
		$title = $page->getTitle();

		$context = RequestContext::newExtraneousContext( $title );
		$page->setContext( $context );

		try {
			$page->run( null );
		} catch ( PermissionsError $e ) {
			// This is okay
		} catch ( ErrorPageError $e ) {
			// This is okay as well
		}

		$this->assertTrue( true, "Special page $name was executed succesfully with anon user" );

		$user = new SuperUser();
		$context->setUser( $user );
		$page->setContext( $context );

		// This should not throw permission errors
		try {
			$page->run( null );
		} catch ( ErrorPageError $e ) {
			// This is okay here
		}
		$this->assertTrue( true, "Special page $name was executed succesfully with super user" );

	}
}

