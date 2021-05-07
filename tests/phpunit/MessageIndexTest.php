<?php
/**
 * Tests for different MessageIndex backends.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use Wikimedia\TestingAccessWrapper;

/**
 * @group Database
 * @group large
 */
class MessageIndexTest extends MediaWikiIntegrationTestCase {
	protected function setUp(): void {
		parent::setUp();
		$this->setMwGlobals( [
			'wgTranslateCacheDirectory' => $this->getNewTempDirectory(),
			'wgTranslateTranslationServices' => [],
			'wgTranslateMessageNamespaces' => [ NS_MEDIAWIKI ]
		] );

		$this->setTemporaryHook( 'TranslatePostInitGroups', [ $this, 'getTestGroups' ] );

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => ObjectCache::getInstance( 'hash' ) ] ) );
		$mg->recache();
	}

	public function getTestGroups( &$list ) {
		$messages = [
			'translated' => 'bunny',
			'untranslated' => 'fanny',
			'changedtranslated_1' => 'bunny',
			'changedtranslated_2' => 'fanny'
		];
		$list['test-group'] =
			new MockWikiMessageGroup( 'test-group', $messages );

		return false;
	}

	public function provideTranslateMessageIndexConfig() {
		yield [ 'DatabaseMessageIndex', DatabaseMessageIndex::class ];
		yield [ [ 'DatabaseMessageIndex' ], DatabaseMessageIndex::class ];
		yield [ [ 'SerializedMessageIndex' ], SerializedMessageIndex::class ];
	}

	/** @dataProvider provideTranslateMessageIndexConfig */
	public function testSingleton( $configValue, $expectedClass ) {
		$this->setMwGlobals( [
			'wgTranslateMessageIndex' => $configValue,
		] );
		$wrapIndex = TestingAccessWrapper::newFromClass( MessageIndex::class );
		$wrapIndex->instance = null;

		$object = MessageIndex::singleton();

		$this->assertInstanceOf( $expectedClass, $object );
	}

	/** @dataProvider provideTestGetArrayDiff */
	public function testGetArrayDiff( $expected, $old, $new ) {
		$actual = MessageIndex::getArrayDiff( $old, $new );
		$this->assertEquals( $expected['keys'], $actual['keys'], 'key diff' );
		$this->assertEquals( $expected['values'], $actual['values'], 'value diff' );
	}

	public function provideTestGetArrayDiff() {
		$tests = [];

		// Addition
		$old = [];
		$new = [
			'label' => 'carpet',
		];
		$expected = [
			'keys' => [
				'add' => [
					'label' => [
						[],
						[ 'carpet' ],
					],
				],
				'del' => [],
				'mod' => [],
			],
			'values' => [ 'carpet' ],
		];
		$tests[] = [ $expected, $old, $new ];

		// Deletion
		$old = [
			'bath' => [ 'goal', 'morals', 'coronation' ],
		];
		$new = [];
		$expected = [
			'keys' => [
				'add' => [],
				'del' => [
					'bath' => [
						[ 'goal', 'morals', 'coronation' ],
						[],
					],
				],
				'mod' => [],
			],
			'values' => [ 'goal', 'morals', 'coronation' ],
		];
		$tests[] = [ $expected, $old, $new ];

		// No change
		$old = $new = [
			'label' => 'carpet',
			'salt' => [ 'morals' ],
			'bath' => [ 'goal', 'morals', 'coronation' ],
		];
		$expected = [
			'keys' => [
				'add' => [],
				'del' => [],
				'mod' => [],
			],
			'values' => [],
		];
		$tests[] = [ $expected, $old, $new ];

		// Modification
		$old = [
			'bath' => [ 'goal', 'morals', 'coronation' ],
		];
		$new = [
			'bath' => [ 'goal', 'beliefs', 'coronation', 'showcase' ],
		];
		$expected = [
			'keys' => [
				'add' => [],
				'del' => [],
				'mod' => [
					'bath' => [
						[ 'goal', 'morals', 'coronation' ],
						[ 'goal', 'beliefs', 'coronation', 'showcase' ],
					],
				],
			],
			'values' => [ 'morals', 'beliefs', 'showcase' ],
		];
		$tests[] = [ $expected, $old, $new ];

		return $tests;
	}

	protected static function getTestData() {
		static $data = null;
		if ( $data === null ) {
			$data = unserialize( file_get_contents( __DIR__ . '/data/messageindexdata.ser' ) );
		}

		return $data;
	}

	/** @dataProvider provideMessageIndexImplementation */
	public function testMessageIndexImplementation( $mi ) {
		$data = self::getTestData();
		/** @var TestableDatabaseMessageIndex|TestableCDBMessageIndex|TestableSerializedMessageIndex */
		$diff = MessageIndex::getArrayDiff( [], $data );
		$mi->store( $data, $diff['keys'] );

		$tests = array_rand( $data, 10 );
		foreach ( $tests as $key ) {
			$this->assertSame(
				$data[$key],
				$mi->get( $key ),
				"Values are preserved for random key $key"
			);
		}

		$cached = $mi->retrieve();

		$tests = array_rand( $data, 10 );
		foreach ( $tests as $key ) {
			$this->assertSame(
				$data[$key],
				$mi->get( $key ),
				"Values are preserved after retrieve for random key $key"
			);
		}

		$this->assertCount(
			count( $data ),
			$cached,
			'Cache has same number of elements'
		);
		$this->assertEquals( $data, $cached, 'Cache is preserved' );
	}

	public static function provideMessageIndexImplementation() {
		return [
			[ new TestableDatabaseMessageIndex() ],
			[ new TestableCDBMessageIndex() ],
			[ new TestableSerializedMessageIndex() ],
			[ new TestableHashMessageIndex() ],
			// Not testing CachedMessageIndex because there is no easy way to mockup those.
		];
	}

	public function testInterimCache() {
		$group = MessageGroups::getGroup( 'test-group' );
		MessageIndex::singleton()->storeInterim( $group, [
			'translated_changed',
		] );

		$handle = new MessageHandle(
			Title::makeTitle( $group->getNamespace(), 'translated_changed' )
		);

		$this->assertTrue( $handle->isValid() );
	}
}

class TestableDatabaseMessageIndex extends DatabaseMessageIndex {
	public function store( array $a, array $diff ) {
		parent::store( $a, $diff );
	}

	public function get( $a ) {
		return parent::get( $a );
	}
}

class TestableCDBMessageIndex extends CDBMessageIndex {
	public function store( array $a, array $diff ) {
		parent::store( $a, $diff );
	}

	public function get( $a ) {
		return parent::get( $a );
	}
}

class TestableSerializedMessageIndex extends SerializedMessageIndex {
	public function store( array $a, array $diff ) {
		parent::store( $a, $diff );
	}

	public function get( $a ) {
		return parent::get( $a );
	}
}

class TestableHashMessageIndex extends HashMessageIndex {
	public function store( array $a, array $diff ) {
		parent::store( $a, $diff );
	}

	public function get( $a ) {
		return parent::get( $a );
	}
}
