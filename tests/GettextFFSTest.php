<?php
/**
 * Tests for Gettext message file format.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * @see GettextFFS
 */
class GettextFFSTest extends MediaWikiTestCase {

	/**
	 * @dataProvider provideMangling
	 */
	public function testMangling( $expected, $item, $algo ) {
		$this->assertEquals( $expected, GettextFFS::generateKeyFromItem( $item, $algo ) );
	}

	public static function provideMangling() {
		return array(
			array(
				'3f9999051ce0bc6e98f43224fe6ee1c220e34e49-Hello!_world_loooooooooooooooo',
				array( 'id' => 'Hello! world loooooooooooooooooooooooooooooooooooooooooong', 'ctxt' => 'baa' ),
				'legacy'
			),
			array(
				'3f9999-Hello!_world_loooooooooooooooo',
				array( 'id' => 'Hello! world loooooooooooooooooooooooooooooooooooooooooong', 'ctxt' => 'baa' ),
				'simple'
			),

			array(
				'1437e478b59e220640bf530f7e3bac93950eb8ae-"¤_=FJQ"_¤r_£_ab',
				array( 'id' => '"¤#=FJQ"<>¤r £}[]}%ab', 'ctxt' => '' ),
				'legacy'
			),
			array(
				'1437e4-"¤#=FJQ"<>¤r_£}[]}%ab',
				array( 'id' => '"¤#=FJQ"<>¤r £}[]}%ab', 'ctxt' => '' ),
				'simple'
			),

		);
	}

}
