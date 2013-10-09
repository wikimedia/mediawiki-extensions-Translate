<?php
/**
 * The JavaFFS class is responsible for loading messages from .properties
 * files, which are used in many JavaScript and Java projects.
 * These tests check that the message keys are loaded, mangled and unmangled
 * correctly.
 * @author Niklas Laxström
 * @file
 */

class JavaFFSTest extends MediaWikiTestCase {

	protected $groupConfiguration = array(
		'BASIC' => array(
			'class' => 'FileBasedMessageGroup',
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		),
		'FILES' => array(
			'class' => 'JavaFFS',
		),
	);

	public function testParsing() {
		$file =
			<<<PROPERTIES
			# You are reading the ".properties" entry.
! The exclamation mark can also mark text as comments.
website = <nowiki>http://en.wikipedia.org/</nowiki>
language = English
# The backslash below tells the application to continue reading
# the value onto the next line.
message = Welcome to \
          Wikipedia!
# Add spaces to the key
key\ with\ spaces = Value that can be looked up with "key with spaces".
key-with-{curlies} = Value that can be looked up with "key-with-{curlies}".
PROPERTIES;

		/**
		 * @var FileBasedMessageGroup $group
		 */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new JavaFFS( $group );
		$parsed = $ffs->readFromVariable( $file );
		$expected = array(
			'website' => '<nowiki>http://en.wikipedia.org/</nowiki>',
			'language' => 'English',
			'message' => 'Welcome to Wikipedia!',
			'key with spaces' => 'Value that can be looked up with "key with spaces".',
			// We expect this one to be mangled for storage
			'key-with-=7Bcurlies=7D' => 'Value that can be looked up with "key-with-{curlies}".',
		);
		$expected = array( 'MESSAGES' => $expected, 'AUTHORS' => array() );
		$this->assertEquals( $expected, $parsed );
	}

	/**
	 * @dataProvider rowValuesProvider
	 */
	public function testRowRoundtrip( $key, $sep, $value, $comment ) {
		$write = JavaFFS::writeRow( $key, $sep, $value );
		// Trim the trailing newline
		$write = rtrim( $write );
		list( $newkey, $newvalue ) = JavaFFS::readRow( $write, $sep );

		$this->assertSame( $key, $newkey, "Key survives roundtrip in testdata: $comment" );
		$this->assertSame( $value, $newvalue, "Value survives roundtrip in testdata: $comment" );
	}

	public function rowValuesProvider() {
		return array(
			array( 'key', '=', 'value', 'simple row' ),
			array( 'key', ':', 'value', 'row with different sep' ),
			array( 'key', '=', 'val=ue', 'row with sep inside value' ),
			array( 'k=ey', '=', 'value', 'row with sep inside key' ),
			array( '!key', '=', 'value', 'row with ! at the beginning of key' ),
			array( 'k!ey', '=', 'value', 'row with ! inside key' ),
			array( '#key', '=', 'value', 'row with # at the beginning of key' ),
			array( 'k#ey', '=', 'value', 'row with # inside key' ),
			array( 'k{ey}', '=', 'value', 'row with { and } inside key' ),
			array( 'k\\tey', '=', 'value\\', 'row with escapes' ),
			array( '01234', '=', '13.34', 'row with numbers' ),
			array( '\\n\\tкая', '=', 'кая', 'row with annoying characteres' ),
			array( '=', '=', '', 'row with empty value' ),
			array( '#k   e\\=y#', '=', '=v!\\=alue\\ \\\\', 'complex row' ),
		);
	}
}
