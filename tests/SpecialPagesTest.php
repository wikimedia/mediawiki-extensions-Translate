<?php
/**
 * General unit tests for special pages.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Unit tests for making sure special pages execute
 * @group Database
 * @group large
 */
class SpecialPagesTest extends MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();

		$this->setMwGlobals( array(
			'wgTranslateCacheDirectory' => $this->getNewTempDirectory(),
			'wgTranslateMessageIndex' => array( 'DatabaseMessageIndex' ),
			'wgDeprecationReleaseLimit' => 1.19,
			'wgTranslateTranslationServices' => array(),
		) );
	}

	public static function provideSpecialPages() {
		require __DIR__ . '/../_autoload.php';
		global $wgSpecialPages;

		$pages = array();
		foreach ( $wgSpecialPages as $name => $class ) {
			if ( isset( $wgAutoloadClasses[$class] ) ) {
				$pages[] = array( $name );
			}
		}

		return $pages;
	}

	/**
	 * @dataProvider provideSpecialPages
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

		$user = new MockSuperUser();
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

