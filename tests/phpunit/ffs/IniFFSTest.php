<?php
/**
 * The IniFFS class is responsible for loading messages from .ini
 * files, which are sometimes used for translations.
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 * @file
 */

class IniFFSTest extends MediaWikiTestCase {

	protected $groupConfiguration = [
		'BASIC' => [
			'class' => 'FileBasedMessageGroup',
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		],
		'FILES' => [
			'class' => 'IniFFS',
			'sourcePattern' => 'ignored',
		],
	];

	public function testParsing() {
		$file = file_get_contents( __DIR__ . '/../data/IniFFSTest1.ini' );

		/**
		 * @var FileBasedMessageGroup $group
		 */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new IniFFS( $group );

		$this->assertTrue( IniFFS::isValid( $file ) );

		$parsed = $ffs->readFromVariable( $file );
		$expected = [
			'hello' => 'Hello',
			'world' => 'World!',
			'all' => 'all = all',
			'foo.bar' => 'bar',
			'quote' => "We're having fun?",
		];
		$expected = [
			'MESSAGES' => $expected,
			'AUTHORS' => [ 'The king of very small kingdom' ]
		];
		$this->assertEquals( $expected, $parsed );
	}

	public function testExport() {
		global $wgSitename;
		$file = file_get_contents( __DIR__ . '/../data/IniFFSTest2.ini' );
		$file = str_replace( '$wgSitename', $wgSitename, $file );

		$collection = new MockMessageCollectionForExport();
		/**
		 * @var FileBasedMessageGroup $group
		 */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new IniFFS( $group );
		$this->assertEquals( $file, $ffs->writeIntoVariable( $collection ) );
	}
}
