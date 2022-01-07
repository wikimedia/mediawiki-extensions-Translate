<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Cache;

use DateInterval;
use DateTime;
use InvalidArgumentException;
use MediaWiki\MediaWikiServices;
use MediaWikiIntegrationTestCase;

/**
 * @covers \MediaWiki\Extension\Translate\Cache\PersistentDatabaseCache
 * @covers \MediaWiki\Extension\Translate\Cache\PersistentCacheEntry
 */
class PersistentDatabaseCacheTest extends MediaWikiIntegrationTestCase {
	/** @var PersistentDatabaseCache */
	private $persistentCache;

	protected function setUp(): void {
		parent::setUp();
		$mwServices = MediaWikiServices::getInstance();
		$lb = $mwServices->getDBLoadBalancer();
		$jsonCodec = $mwServices->getJsonCodec();
		$this->persistentCache = new PersistentDatabaseCache( $lb, $jsonCodec );
	}

	protected function tearDown(): void {
		$this->persistentCache->clear();
		parent::tearDown();
	}

	/** @dataProvider provideTestSet */
	public function testGetSet(
		string $keyname,
		$value,
		?int $exptime,
		?string $tag
	) {
		$entry = new PersistentCacheEntry(
			$keyname,
			$value,
			$exptime,
			$tag
		);

		$this->persistentCache->set( $entry );

		$getEntries = $this->persistentCache->get( $keyname );
		$this->assertCacheEntryEqual( $entry, $getEntries[0] );
	}

	/** @dataProvider provideTestSetMulti */
	public function testGetSetMulti( array $inputs, int $exptime = null, string $tag = null ) {
		$entries = $this->getEntriesFromInput( $inputs, $exptime, $tag );
		$this->persistentCache->set( ...$entries );

		$getEntries = $this->persistentCache->get( ...array_keys( $inputs ) );

		foreach ( $entries as $index => $entry ) {
			$this->assertCacheEntryEqual(
				$entry,
				$getEntries[$index]
			);
		}
	}

	public function testHas() {
		$entry = new PersistentCacheEntry( 'hello', null, null, null );
		$this->persistentCache->set( $entry );
		$this->assertTrue( $this->persistentCache->has( 'hello' ) );
		$this->assertFalse( $this->persistentCache->has( 'hello2' ) );
	}

	/** @dataProvider provideTestHasEntryWithTag */
	public function testHasEntryWithTag(
		array $input,
		string $tagToSearch,
		bool $expected,
		string $message
	) {
		$entry = new PersistentCacheEntry(
			$input['keyname'],
			$input['value'],
			$input['exptime'],
			$input['tag']
		);
		$this->persistentCache->set( $entry );

		$hasTag = $this->persistentCache->hasEntryWithTag( $tagToSearch );
		$this->assertSame( $expected, $hasTag, $message );
	}

	/** @dataProvider provideTestHasExpired */
	public function testHasExpiredEntry( array $input, bool $expected ) {
		$entry = new PersistentCacheEntry(
			$input['keyname'],
			$input['value'],
			$input['exptime'],
			$input['tag']
		);

		$this->persistentCache->set( $entry );

		$getEntry = $this->persistentCache->get( $input['keyname'] )[0];

		$this->assertSame( $expected, $entry->hasExpired() );
		$this->assertSame( $expected, $getEntry->hasExpired() );
	}

	/** @dataProvider provideTestGetByTag */
	public function testGetByTag(
		array $inputs, ?int $exptime, ?string $inputTag, string $searchTag, int $count
	) {
		$entries = $this->getEntriesFromInput( $inputs, $exptime, $inputTag );

		$this->persistentCache->set( ...$entries );
		$entries = $this->persistentCache->getByTag( $searchTag );

		$this->assertCount( $count, $entries );
	}

	public function testDelete() {
		$testTag = 'test';
		$entry = new PersistentCacheEntry( 'hello', null, null, $testTag );
		$secondEntry = new PersistentCacheEntry( 'hello2', null, null, $testTag );

		$this->persistentCache->set( $entry );
		$this->persistentCache->set( $secondEntry );

		$getEntries = $this->persistentCache->getByTag( $testTag );

		$this->assertCount( 2, $getEntries );

		$this->persistentCache->delete( 'hello', 'hello2' );

		$getEntries = $this->persistentCache->getByTag( $testTag );
		$this->assertEmpty( $getEntries );
	}

	public function testDeleteEntriesWithTag() {
		$testTag = 'test';
		$anotherTestTag = 'test2';
		$entry = new PersistentCacheEntry( 'hello', null, null, $testTag );
		$secondEntry = new PersistentCacheEntry( 'hello2', null, null, $testTag );
		$thirdEntry = new PersistentCacheEntry( 'hello2', null, null, $anotherTestTag );

		$this->persistentCache->set( $entry );
		$this->persistentCache->set( $secondEntry );
		$this->persistentCache->set( $thirdEntry );

		$this->persistentCache->deleteEntriesWithTag( $testTag );

		$testTagEntries = $this->persistentCache->getByTag( $testTag );
		$this->assertEmpty( $testTagEntries );

		$anotherTagEntries = $this->persistentCache->getByTag( $anotherTestTag );
		$this->assertNotEmpty( $anotherTagEntries );
	}

	public function testKeyLength() {
		$longTestKey = str_repeat( 'verylongkey', 30 );

		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessageMatches( '/the length of key/i' );

		new PersistentCacheEntry( $longTestKey, null, null );
	}

	public function testTagLength() {
		$longTestTag = str_repeat( 'verylongtag', 30 );

		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessageMatches( '/the length of tag/i' );

		new PersistentCacheEntry( 'testkey', null, null, $longTestTag );
	}

	public function testExtendGroupExpiryTime() {
		$tomorrow = ( new DateTime() )->add( new DateInterval( 'P1D' ) );
		$key = 'hello';
		$incrementedTime = $tomorrow->getTimestamp() + 100;

		$entry = new PersistentCacheEntry( $key, 'value', $tomorrow->getTimestamp() );

		$this->persistentCache->set( $entry );
		$this->persistentCache->setExpiry( $key, $incrementedTime );

		$cacheEntry = $this->persistentCache->get( $key );
		$this->assertEquals( $incrementedTime, $cacheEntry[0]->exptime() );
	}

	public function provideTestSet() {
		yield [
			'keyname' => 'hello',
			'value' => 'World',
			'exptime' => ( new DateTime() )->getTimestamp(),
			'tag' => null
		];

		yield [
			'keyname' => 'hello',
			'value' => 'World',
			'exptime' => null,
			'tag' => 'test'
		];

		yield [
			'keyname' => 'key',
			'value' => null,
			'exptime' => null,
			'tag' => null
		];
	}

	public function provideTestSetMulti() {
		yield [
			'inputs' => [
				'key' => 'value',
				'key2' => 'value2'
			],
			'exptime' => null,
			'tag' => null
		];

		yield [
			'inputs' => [
				'key5' => 'value5',
				'key6' => null
			],
			'exptime' => ( new DateTime() )->getTimestamp(),
			'tag' => 'test'
		];
	}

	public function provideTestHasEntryWithTag() {
		$testTag = 'test';
		$today = new DateTime();
		$tomorrow = ( new DateTime() )->add( new DateInterval( 'P1D' ) );
		$yesterday = ( new DateTime() )->sub( new DateInterval( 'P1D' ) );

		yield [
			'input' => [
				'keyname' => 'hello',
				'value' => 'World',
				'exptime' => $today->getTimestamp(),
				'tag' => null
			],
			'expectedTag' => $testTag,
			'hasEntryWithTag' => false,
			'testMessage' => 'return false if no tag is present'
		];

		yield [
			'input' => [
				'keyname' => 'hello',
				'value' => 'World',
				'exptime' => $tomorrow->getTimestamp(),
				'tag' => $testTag
			],
			'expectedTag' => $testTag,
			'hasEntryWithTag' => true,
			'testMessage' => 'return true if tag is present'
		];

		yield [
			'input' => [
				'keyname' => 'hello',
				'value' => 'World',
				'exptime' => $yesterday->getTimestamp(),
				'tag' => $testTag
			],
			'expectedTag' => $testTag,
			'hasEntryWithTag' => true,
			'testMessage' => 'return true for entries that have expired'
		];
	}

	public function provideTestHasExpired() {
		$tomorrow = ( new DateTime() )->add( new DateInterval( 'P1D' ) );
		$yesterday = ( new DateTime() )->sub( new DateInterval( 'P1D' ) );

		yield [
			'input' => [
				'keyname' => 'hello',
				'value' => 'World',
				'exptime' => $tomorrow->getTimestamp(),
				'tag' => null
			],
			'hasEntryExpired' => false,
		];

		yield [
			'input' => [
				'keyname' => 'hello',
				'value' => 'World',
				'exptime' => $yesterday->getTimestamp(),
				'tag' => null
			],
			'hasEntryExpired' => true,
		];
	}

	public function provideTestGetByTag() {
		$testTag = 'test';
		yield [
			'inputs' => [
				'key' => 'value',
				'key2' => 'value2'
			],
			'exptime' => null,
			'tag' => $testTag,
			'test2',
			'expectedEntryCount' => 0
		];

		yield [
			'input' => [
				'key5' => 'value5',
				'key6' => null
			],
			'exptime' => ( new DateTime() )->getTimestamp(),
			'tag' => $testTag,
			'expectedTag' => $testTag,
			'expectedEntryCount' => 2
		];
	}

	private function assertCacheEntryEqual(
		PersistentCacheEntry $expected,
		PersistentCacheEntry $actual
	): void {
		$this->assertSame( $expected->key(), $actual->key() );
		$this->assertEquals( $expected->value(), $actual->value() );
		$this->assertSame( $expected->exptime(), $actual->exptime() );
		$this->assertSame( $expected->tag(), $actual->tag() );
	}

	/** @return PersistentCacheEntry[] */
	private function getEntriesFromInput( array $inputs, ?int $exptime, ?string $tag ): array {
		$entries = [];
		foreach ( $inputs as $key => $value ) {
			$entries[] = new PersistentCacheEntry(
				$key,
				$value,
				$exptime,
				$tag
			);
		}

		return $entries;
	}
}
