<?php
/**
 * Tests for JSON message file format.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * @see JsonFFS
 */
class JsonFFSTest extends MediaWikiTestCase {

	protected $groupConfiguration = array(
		'BASIC' => array(
			'class' => 'FileBasedMessageGroup',
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		),
		'FILES' => array(
			'class' => 'JsonFFS',
		),
	);

	/**
	 * @dataProvider jsonProvider
	 */
	public function testParsing( $messages, $authors, $file ) {
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new JsonFFS( $group );
		$parsed = $ffs->readFromVariable( $file );
		$expected = array( 'MESSAGES' => $messages, 'AUTHORS' => $authors );
		$this->assertEquals( $expected, $parsed );

		if ( $messages === array() ) {
			$this->assertFalse( JsonFFS::isValid( $file ) );
		} else {
			$this->assertTrue( JsonFFS::isValid( $file ) );
		}
	}

	public function jsonProvider() {
		$values = array();

		$file1 =
<<<JSON
{"key": "value"}
JSON;

		$values[] = array(
			array( 'key' => 'value' ),
			array(),
			$file1,
		);

		$file2 =
<<<JSON
{ "@metadata": { "authors": ["A", "B"] },
"key": "value"}
JSON;

		$values[] = array(
			array( 'key' => 'value' ),
			array( 'A', 'B' ),
			$file2,
		);

		$file3 =
<<<JSON
<This is not
Json!>@£0 file
JSON;

		$values[] = array(
			array(),
			array(),
			$file3,
		);

		return $values;
	}
}
