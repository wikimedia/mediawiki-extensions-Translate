<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use FileBasedMessageGroup;
use MediaWikiIntegrationTestCase;
use MessageGroupBase;
use MockMessageCollectionForExport;

/**
 * The IniFFS class is responsible for loading messages from .ini
 * files, which are sometimes used for translations.
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

/** @covers \MediaWiki\Extension\Translate\FileFormatSupport\IniFormat */
class IniFormatTest extends MediaWikiIntegrationTestCase {
	private array $groupConfiguration = [
		'BASIC' => [
			'class' => FileBasedMessageGroup::class,
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		],
		'FILES' => [
			'format' => 'Ini',
			'sourcePattern' => 'ignored',
		],
	];

	public function testParsing(): void {
		$file = file_get_contents( __DIR__ . '/../data/IniFormatTest1.ini' );

		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$iniFormat = new IniFormat( $group );

		$this->assertNotEmpty( $iniFormat->readFromVariable( $file )['MESSAGES'] );
		$parsed = $iniFormat->readFromVariable( $file );
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

		$invalidContent = 'Invalid-Ini-Content';
		$this->assertSame( [], $iniFormat->readFromVariable( $invalidContent )['MESSAGES'] );
	}

	public function testExport(): void {
		global $wgSitename;
		$file = file_get_contents( __DIR__ . '/../data/IniFormatTest2.ini' );
		$file = str_replace( '$wgSitename', $wgSitename, $file );

		$collection = new MockMessageCollectionForExport();
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$iniFormat = new IniFormat( $group );
		$this->assertEquals( $file, $iniFormat->writeIntoVariable( $collection ) );
	}
}
