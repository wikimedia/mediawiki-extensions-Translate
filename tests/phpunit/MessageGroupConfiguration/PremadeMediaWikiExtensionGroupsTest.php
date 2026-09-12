<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupConfiguration;

use MediaWikiIntegrationTestCase;
use RuntimeException;
use UnexpectedValueException;

/**
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\MessageGroupConfiguration\PremadeMediaWikiExtensionGroups
 */
class PremadeMediaWikiExtensionGroupsTest extends MediaWikiIntegrationTestCase {

	private function newGroups( string $definitionFile, string $path = '' ): PremadeMediaWikiExtensionGroups {
		return new PremadeMediaWikiExtensionGroups( $definitionFile, $path );
	}

	public function testSetGroupPrefixIsApplied(): void {
		$groups = $this->newGroups(
			__DIR__ . '/../data/mediawiki-extensions.txt',
			'%GROUPROOT%/mediawiki-extensions/extensions'
		);
		$groups->setGroupPrefix( 'mw-' );

		$list = $deps = [];
		$groups->register( $list, $deps );

		$this->assertArrayHasKey( 'mw-wikimediamessages', $list );
		$this->assertArrayNotHasKey( 'ext-wikimediamessages', $list );
	}

	public function testSetNamespaceIsApplied(): void {
		$groups = $this->newGroups(
			__DIR__ . '/../data/mediawiki-extensions.txt',
			'%GROUPROOT%/mediawiki-extensions/extensions'
		);
		$groups->setNamespace( NS_PROJECT );

		$list = $deps = [];
		$groups->register( $list, $deps );

		$this->assertSame( NS_PROJECT, $list['ext-wikimediamessages']->getNamespace() );
	}

	public function testParseFileThrowsOnDuplicateName(): void {
		$file = $this->getNewTempFile();
		file_put_contents( $file, "Example\nExample\n" );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Trying to define name twice' );
		$list = $deps = [];
		$this->newGroups( $file )->register( $list, $deps );
	}

	public function testParseFileThrowsOnUnknownKey(): void {
		$file = $this->getNewTempFile();
		file_put_contents( $file, "Example\nunknownkey = value\n" );

		$this->expectException( UnexpectedValueException::class );
		$this->expectExceptionMessage( 'Unknown key' );
		$list = $deps = [];
		$this->newGroups( $file )->register( $list, $deps );
	}

	public function testParseFileThrowsOnMissingName(): void {
		$file = $this->getNewTempFile();
		file_put_contents( $file, "file = foo\n" );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Name missing' );
		$list = $deps = [];
		$this->newGroups( $file )->register( $list, $deps );
	}
}
