<?php
/**
 * Tests for XliffFFS
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

class XliffFFSTest extends MediaWikiTestCase {
	protected $groupConfiguration = array(
		'BASIC' => array(
			'class' => 'FileBasedMessageGroup',
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		),
		'FILES' => array(
			'class' => 'XliffFFS',
			'sourcePattern' => '',
		),
	);

	public function testParsing() {
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new XliffFFS( $group );

		$file = file_get_contents( __DIR__ . '/../data/minimal.xlf' );
		$parsed = $ffs->readFromVariable( $file, 'target' );
		$expected = array(
			'1' => 'Hei maailma',
			'2' => TRANSLATE_FUZZY . 'Fuzzy translation',
			'3' => 'Tämä on <g id="1" ctype="bold">paksu</g>.',
		);
		$expected = array( 'MESSAGES' => $expected );
		$this->assertEquals( $expected, $parsed );

		$parsed = $ffs->readFromVariable( $file, 'source' );
		$expected = array(
			'1' => 'Hello world',
			'2' => 'Fuzzy message',
			'3' => 'This is <g id="1" ctype="bold">bold</g>.',
		);
		$expected = array( 'MESSAGES' => $expected );
		$this->assertEquals( $expected, $parsed );
	}
}
