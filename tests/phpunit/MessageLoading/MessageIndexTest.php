<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

use Generator;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use MockWikiMessageGroup;
use Wikimedia\ObjectCache\HashBagOStuff;
use Wikimedia\ObjectCache\WANObjectCache;

/**
 * Tests for different MessageIndex backends.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @group Database
 * @group large
 * @covers \MediaWiki\Extension\Translate\MessageLoading\MessageIndex
 */
class MessageIndexTest extends MediaWikiIntegrationTestCase {
	protected function setUp(): void {
		parent::setUp();
		$this->overrideConfigValues( [
			'TranslateCacheDirectory' => $this->getNewTempDirectory(),
			'TranslateTranslationServices' => [],
			'TranslateMessageNamespaces' => [ NS_MEDIAWIKI ],
		] );

		$this->setTemporaryHook( 'TranslateInitGroupLoaders', HookContainer::NOOP );
		$this->setTemporaryHook( 'TranslatePostInitGroups', [ $this, 'getTestGroups' ] );

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => new HashBagOStuff() ] ) );
		$mg->clearProcessCache();
		$mg->recache();
	}

	public function getTestGroups( &$list ): bool {
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

	/** @dataProvider provideTestGetArrayDiff */
	public function testGetArrayDiff( array $expected, array $old, array $new ): void {
		$actual = Services::getInstance()->getMessageIndex()->getArrayDiff( $old, $new );
		$this->assertEquals( $expected['keys'], $actual['keys'], 'key diff' );
		$this->assertEquals( $expected['values'], $actual['values'], 'value diff' );
	}

	public static function provideTestGetArrayDiff(): array {
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
		$data ??= unserialize( file_get_contents( __DIR__ . '/../data/messageindexdata.ser' ) );
		return $data;
	}

	/** @dataProvider provideMessageIndexImplementation */
	public function testMessageIndexImplementation( array $messageIndexSpec ): void {
		$messageIndex = $this->getServiceContainer()->getObjectFactory()->createObject( $messageIndexSpec );
		$data = self::getTestData();
		$diff = Services::getInstance()->getMessageIndex()->getArrayDiff( [], $data );
		$messageIndex->store( $data, $diff['keys'] );

		foreach ( $data as $key => $value ) {
			$this->assertSame(
				$value,
				$messageIndex->get( $key ),
				"Values are preserved for random key $key"
			);
		}

		$cached = $messageIndex->retrieve();

		foreach ( $data as $key => $value ) {
			$this->assertSame(
				$value,
				$messageIndex->get( $key ),
				"Values are preserved after retrieve for random key $key"
			);
		}

		$this->assertSameSize(
			$data,
			$cached,
			'Cache has same number of elements'
		);
		$this->assertEquals( $data, $cached, 'Cache is preserved' );
	}

	public static function provideMessageIndexImplementation(): Generator {
		yield [ 'TestableDatabaseMessageIndex' => [ 'class' => TestableDatabaseMessageIndex::class ] ];
		yield [ 'TestableCDBMessageIndex' => [ 'class' => TestableCDBMessageIndex::class ] ];
		yield [ 'TestableSerializedMessageIndex' => [ 'class' => TestableSerializedMessageIndex::class ] ];
		yield [ 'TestableHashMessageIndex' => [ 'class' => TestableHashMessageIndex::class ] ];
	}

	public function testInterimCache(): void {
		$group = MessageGroups::getGroup( 'test-group' );
		Services::getInstance()->getMessageIndex()->storeInterim( $group, [
			'translated_changed',
		] );

		$handle = new MessageHandle(
			Title::makeTitle( $group->getNamespace(), 'translated_changed' )
		);

		$this->assertTrue( $handle->isValid() );
	}
}

class TestableDatabaseMessageIndex extends DatabaseMessageIndex {
	public function store( array $array, array $diff ): void {
		parent::store( $array, $diff );
	}

	public function get( string $key ) {
		return parent::get( $key );
	}
}

class TestableCDBMessageIndex extends CDBMessageIndex {
	public function store( array $a, array $diff ): void {
		parent::store( $a, $diff );
	}

	public function get( string $key ) {
		return parent::get( $key );
	}
}

class TestableSerializedMessageIndex extends SerializedMessageIndex {
	public function store( array $array, array $diff ): void {
		parent::store( $array, $diff );
	}

	public function get( string $key ) {
		return parent::get( $key );
	}
}

class TestableHashMessageIndex extends HashMessageIndex {
	public function store( array $array, array $diff ): void {
		parent::store( $array, $diff );
	}

	public function get( string $key ) {
		return parent::get( $key );
	}
}
