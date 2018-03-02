<?php
/**
 * Tests for XliffFFS
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

class XliffFFSTest extends MediaWikiTestCase {
	protected $groupConfiguration = [
		'BASIC' => [
			'class' => 'FileBasedMessageGroup',
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		],
		'FILES' => [
			'class' => 'XliffFFS',
			'sourcePattern' => '',
		],
	];

	public function testParsing() {
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new XliffFFS( $group );

		$file = file_get_contents( __DIR__ . '/../data/minimal.xlf' );
		$parsed = $ffs->readFromVariable( $file, 'target' );
		$expected = [
			'1' => 'Hei maailma',
			'2' => TRANSLATE_FUZZY . 'Fuzzy translation',
			'3' => 'Tämä on <g id="1" ctype="bold">paksu</g>.',
		];
		$expected = [ 'MESSAGES' => $expected ];
		$this->assertEquals( $expected, $parsed );

		$parsed = $ffs->readFromVariable( $file, 'source' );
		$expected = [
			'1' => 'Hello world',
			'2' => 'Fuzzy message',
			'3' => 'This is <g id="1" ctype="bold">bold</g>.',
		];
		$expected = [ 'MESSAGES' => $expected ];
		$this->assertEquals( $expected, $parsed );
	}
}
