<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use FileBasedMessageGroup;
use Generator;
use InvalidArgumentException;
use MediaWikiUnitTestCase;
use Wikimedia\ObjectFactory\ObjectFactory;

/**
 * @author Abijeet Patro
 * @author Eugene Wang'ombe
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\FileFormatSupport\FileFormatFactory
 */
class FileFormatFactoryTest extends MediaWikiUnitTestCase {
	/** @dataProvider provideTestGetFileFormat */
	public function testGetFileFormat( string $fileFormatId, string $fileFormatClass ) {
		$messageGroupMock = $this->getFileBasedMessageGroupMock();

		$fileFormatFactory = new FileFormatFactory(
			$this->getObjectFactoryMock(
				[ 'class' => $fileFormatClass, 'args' => [ $messageGroupMock ] ],
				new $fileFormatClass( $messageGroupMock )
			)
		);

		$instance = $fileFormatFactory->create( $fileFormatId, $messageGroupMock );
		$this->assertInstanceOf( $fileFormatClass, $instance );
	}

	/** @dataProvider provideTestGetFileFormatException */
	public function testGetFileFormatException( string $fileFormatId, string $fileFormatClass ) {
		$messageGroupMock = $this->getFileBasedMessageGroupMock();

		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage(
			"FileFormatSupport: Unknown file format '$fileFormatId' specified for group '{$messageGroupMock->getId()}'"
		);

		$fileFormatFactory = new FileFormatFactory(
			$this->getObjectFactoryMock(
				[ 'class' => $fileFormatClass, 'args' => [ $messageGroupMock ] ],
				new $fileFormatClass( $messageGroupMock )
			)
		);

		$fileFormatFactory->create( $fileFormatId, $messageGroupMock );
	}

	public static function provideTestGetFileFormat(): Generator {
		yield [ 'Json', JsonFormat::class ];
		yield [ 'AndroidXml', AndroidXmlFormat::class ];
	}

	public static function provideTestGetFileFormatException(): Generator {
		yield [ 'JsonFFS-x', JsonFormat::class ];
	}

	private function getObjectFactoryMock( array $fileFormatClass, $fileFormatObject ) {
		$mock = $this->createMock( ObjectFactory::class );
		$mock->method( 'createObject' )
			->with( $fileFormatClass )
			->willReturn( $fileFormatObject );

		return $mock;
	}

	private function getFileBasedMessageGroupMock() {
		$mock = $this->createMock( FileBasedMessageGroup::class );
		$mock->method( 'getConfiguration' )
			->willReturn( [ 'FILES' => [] ] );
		$mock->method( 'getId' )
			->willReturn( 'groupId' );
		return $mock;
	}
}
