<?php
/**
 * The IniFFS class is responsible for loading messages from .ini
 * files, which are sometimes used for translations.
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */

class IniFFSTest extends MediaWikiTestCase {

	protected $groupConfiguration = array(
		'BASIC' => array(
			'class' => 'FileBasedMessageGroup',
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		),
		'FILES' => array(
			'class' => 'IniFFS',
			'sourcePattern' => 'ignored',
		),
	);

	public function testParsing() {
		$file = file_get_contents( __DIR__ . '/data/IniFFSTest1.ini' );

		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new IniFFS( $group );

		$this->assertTrue( IniFFS::isValid( 'IniFFSTest1.ini', $file ) );

		$parsed = $ffs->readFromVariable( $file );
		$expected = array(
			'hello' => 'Hello',
			'world' => 'World!',
			'all' => 'all = all',
			'foo.bar' => 'bar',
		);
		$expected = array( 'MESSAGES' => $expected, 'AUTHORS' => array( 'The king of very small kingdom' ) );
		$this->assertEquals( $expected, $parsed );
	}

	public function testExport() {
		$file = file_get_contents( __DIR__ . '/data/IniFFSTest2.ini' );

		$collection = new MockMessageCollectionForExport();
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new IniFFS( $group );
		$this->assertEquals( $file, $ffs->writeIntoVariable( $collection ) );
	}
}
