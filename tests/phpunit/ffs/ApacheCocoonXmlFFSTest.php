<?php
/**
 * Tests for ApacheCocoonXmlFFS
 *
 * @file
 * @author Siebrand Mazeland
 * @license GPL-2.0+
 */

class ApacheCocoonXmlFFSTest extends AndroidXmlFFSTest {
	protected $groupConfiguration = array(
		'BASIC' => array(
			'class' => 'FileBasedMessageGroup',
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		),
		'FILES' => array(
			'class' => 'ApacheCocoonXmlFFS',
			'sourcePattern' => '',
		),
	);

	public function testParsing() {
		$file =
<<<XML
<?xml version="1.0" encoding="utf-8"?>
<catalogue xml:lang="fi">
	<message key="wpt_voicerec">Voice recording</message>
	<message key="wpt_stillimage" fuzzy="true">Picture</message>
</catalogue>
XML;


		/// @var FileBasedMessageGroup $group
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new ApacheCocoonXmlFFS( $group );

		$this->assertTrue( $ffs->isValid( $file ), 'Example file passes validation' );

		$parsed = $ffs->readFromVariable( $file );
		$expected = array(
			'wpt_voicerec' => 'Voice recording',
			'wpt_stillimage' => '!!FUZZY!!Picture',
		);
		$expected = array( 'MESSAGES' => $expected, 'AUTHORS' => array() );
		$this->assertEquals( $expected, $parsed );
	}

	public function testWrite() {
		/// @var FileBasedMessageGroup $group
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new ApacheCocoonXmlFFS( $group );

		$messages = array(
			'ko=26ra' => 'wawe',
			'foobar' => '!!FUZZY!!Kissa kala <koira> "a\'b',
		);
		$collection = new MockMessageCollection( $messages );

		$xml = $ffs->writeIntoVariable( $collection );
		$parsed = $ffs->readFromVariable( $xml );
		$expected = array( 'MESSAGES' => $messages, 'AUTHORS' => array() );
		$this->assertEquals( $expected, $parsed );

		$this->assertTrue( $ffs->isValid( $xml ), 'Generated output passes validation' );
	}
}
