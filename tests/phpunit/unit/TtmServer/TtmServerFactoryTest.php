<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TtmServer;

use DatabaseTTMServer;
use FakeTTMServer;
use Generator;
use InvalidArgumentException;
use MediaWikiUnitTestCase;

/**
 * @since 2021.01
 * @license GPL-2.0-or-later
 * @author Niklas Laxström
 * @covers \MediaWiki\Extension\Translate\TtmServer\TtmServerFactory
 */
class TtmServerFactoryTest extends MediaWikiUnitTestCase {
	public function testGetNames() {
		$factory = new TtmServerFactory(
			[
				'one' => [ 'type' => 'ttmserver' ],
				'two' => [ 'type' => 'remote-ttmserver' ],
				'three' => []
			]
		);
		$actual = $factory->getNames();
		$this->assertArrayEquals( [ 'one', 'two' ], $actual );
	}

	public function testHas() {
		$factory = new TtmServerFactory(
			[
				'exists' => [],
				'one' => [ 'type' => 'ttmserver' ],
			],
			'one'
		);
		$this->assertFalse( $factory->has( 'unknown' ) );
		$this->assertFalse( $factory->has( 'exists' ) );
		$this->assertTrue( $factory->has( 'one' ) );
	}

	public function testCreate() {
		$name = '1';
		$factory = new TtmServerFactory(
			[
				$name => [
					'database' => false,
					// Passed to wfGetDB
					'cutoff' => 0.75,
					'type' => 'ttmserver',
					'public' => false,
				],
			],
			$name
		);

		$actual = $factory->create( $name );
		$this->assertInstanceOf( DatabaseTTMServer::class, $actual );
	}

	/** @dataProvider provideCreateFailure */
	public function testCreateFailure( array $input ) {
		$factory = new TtmServerFactory( $input );
		$this->expectException( ServiceCreationFailure::class );
		$factory->create( '' );
	}

	public function provideCreateFailure() {
		yield 'unknown' => [ [] ];
		yield 'malformed' => [ [ '' => 'gibberish' ] ];
		yield 'incomplete config' => [ [ '' => [ 'someoption' => 'somevalue' ] ] ];
	}

	/** @dataProvider provideGetWritable */
	public function testGetWritable( array $servers, ?string $default, ?string $return ): void {
		$ttmFactory = new TtmServerFactory( $servers, $default );
		if ( $return ) {
			$this->assertEquals( $return, array_keys( $ttmFactory->getWritable() )[0] );
		} else {
			$this->assertEquals( [], $ttmFactory->getWritable() );
		}
	}

	public function provideGetWritable(): Generator {
		$dummyTtm = [
			'database' => false,
			// Passed to wfGetDB
			'cutoff' => 0.75,
			'type' => 'ttmserver',
			'public' => false,
		];
		$writableServer = [
			'type' => 'ttmserver',
			'class' => FakeTTMServer::class,
			'writable' => true
		];

		$readableServerWithMirror = [
			'type' => 'ttmserver',
			'class' => FakeReadableTtmServer::class,
			'mirrors' => [ 'write' ]
		];

		yield 'no writable servers' => [
			[ '1' => $dummyTtm ],
			null,
			null
		];

		yield 'writable server is not default' => [
			[
				'writable' => $writableServer,
				'fake' => $dummyTtm
			],
			null,
			'writable'
		];

		yield 'if mirrors are configured with writable, mirrors are ignored' => [
			[
				'read' => $readableServerWithMirror,
				'write' => $writableServer
			],
			'read',
			'write'
		];
	}

	/** @dataProvider provideGetWritableError */
	public function testGetWritableError(
		array $servers,
		?string $default,
		string $exception,
		string $exceptionMessage
	): void {
		$ttmFactory = new TtmServerFactory( $servers, $default );
		$this->expectException( $exception );
		$this->expectExceptionMessageMatches( $exceptionMessage );

		$ttmFactory->getWritable();
	}

	public function provideGetWritableError(): Generator {
		$readableTtmServer = [
			'type' => 'ttmserver',
			'class' => FakeReadableTtmServer::class,
			'writable' => true
		];

		$writableServer = [
			'type' => 'ttmserver',
			'class' => FakeTTMServer::class,
			'writable' => true,
		];

		$writableMirrorServer = [
			'type' => 'ttmserver',
			'class' => FakeTTMServer::class,
			'writable' => true,
			'mirrors' => true
		];

		yield 'readable TtmServer is marked as writable' => [
			[ 'readable' => $readableTtmServer ],
			null,
			InvalidArgumentException::class,
			'/does not implement WritableTtmServer interface/i'
		];

		yield 'writable TtmServer is marked as a mirror' => [
			[ 'writable' => $writableMirrorServer ],
			null,
			InvalidArgumentException::class,
			'/use both writable and mirrors parameter/i'
		];

		yield 'default server is writable' => [
			[ 'writable' => $writableServer ],
			'writable',
			InvalidArgumentException::class,
			'/cannot be write only/i'
		];
	}

	public function provideGetDefaultForRead(): Generator {
		$readableTtmServer = [
			'type' => 'ttmserver',
			'class' => FakeReadableTtmServer::class
		];

		$writableServer = [
			'type' => 'ttmserver',
			'class' => FakeTTMServer::class,
			'writable' => true
		];

		$writeOnlyServer = [
			'type' => 'ttmserver',
			'class' => FakeWritableTtmServer::class
		];

		yield 'default server cannot be write only' => [
			[ 'writable' => $writableServer ],
			'writable',
			InvalidArgumentException::class,
			'/cannot be write only/i'
		];

		yield 'default readable server' => [
			[ 'readable' => $readableTtmServer ],
			'readable',
			null,
			null
		];

		yield 'writable default server without "write" configuration' => [
			[ 'write-only' => $writeOnlyServer ],
			'write-only',
			InvalidArgumentException::class,
			'/must implement ReadableTtmServer/i'
		];
	}

	/** @dataProvider provideGetDefaultForRead */
	public function testGetDefaultForRead(
		array $servers,
		string $default,
		?string $exceptionClass,
		?string $exceptionMessage
	): void {
		$ttmFactory = new TtmServerFactory( $servers, $default );
		if ( $exceptionClass ) {
			$this->expectException( $exceptionClass );
		}

		if ( $exceptionMessage ) {
			$this->expectExceptionMessageMatches( $exceptionMessage );
		}

		$ttmServer = $ttmFactory->getDefaultForQuerying();
		if ( !$exceptionClass ) {
			$this->assertNotNull( $ttmServer );
		}
	}
}
