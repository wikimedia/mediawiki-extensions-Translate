<?php
/**
 * General unit tests for special pages.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Unit tests for making sure special pages execute
 * @group Nodatabase
 */
class SpecialPagesTest extends MediaWikiTestCase {

	public function specialPages() {
		global $IP;
		require( '../_autoload.php' );
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
		}
		$this->assertTrue( true, "Special page $name was executed succesfully with anon user" );

		$user = new SuperUser();
		$context->setUser( $user );
		$page->setContext( $context );

		// This should not throw permission errors
		$page->run( null );
		$this->assertTrue( true, "Special page $name was executed succesfully with super user" );

	}
}


class SuperUser extends User {
	public function getId() {
		return 666;
	}

	public function getName() {
		return 'SuperUser';
	}

	public function isAllowed( $right = '' ) {
		return true;
	}
}
