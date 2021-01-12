<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TtmServer;

use DatabaseTTMServer;
use MediaWikiUnitTestCase;

/**
 * @since 2021.01
 * @license GPL-2.0-or-later
 * @author Niklas Laxström
 * @covers \MediaWiki\Extension\Translate\TtmServer\TtmServerFactory
 */
class TtmServerFactoryTest extends MediaWikiUnitTestCase {
	public function testGetNames() {
		$factory = new TtmServerFactory( [ 'one' => [], 'two' => [] ] );
		$actual = $factory->getNames();
		$this->assertArrayEquals( [ 'one', 'two' ], $actual );
	}

	public function testHas() {
		$factory = new TtmServerFactory( [ 'exists' => [] ], 'exists' );
		$this->assertFalse( $factory->has( 'unknown' ) );
		$this->assertTrue( $factory->has( 'exists' ) );
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
}
