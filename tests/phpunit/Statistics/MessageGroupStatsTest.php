<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageGroups\WikiMessageGroup;
use MediaWiki\HookContainer\HookContainer;
use MediaWikiIntegrationTestCase;
use MessageGroupTestTrait;
use MockWikiMessageGroup;
use Wikimedia\ObjectCache\HashBagOStuff;
use Wikimedia\ObjectCache\WANObjectCache;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @group Database
 * @covers \MediaWiki\Extension\Translate\Statistics\MessageGroupStats
 */
class MessageGroupStatsTest extends MediaWikiIntegrationTestCase {
	use MessageGroupTestTrait;

	protected function setUp(): void {
		parent::setUp();
		$this->setupGroupTestEnvironment( $this );

		$this->setTemporaryHook( 'TranslateInitGroupLoaders', HookContainer::NOOP );
		$this->setTemporaryHook(
			'TranslatePostInitGroups',
			static function ( &$list ) {
				$exampleMessageGroup = new WikiMessageGroup( 'theid', 'thesource' );
				$exampleMessageGroup->setLabel( 'thelabel' ); // Example
				$exampleMessageGroup->setNamespace( 5 ); // Example
				$list['theid'] = $exampleMessageGroup;
			}
		);

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => new HashBagOStuff() ] ) );
		$mg->recache();
	}

	public function testGetDatabaseIdForGroupId() {
		$shortId = 'abab';
		$longId = str_repeat( 'ab', 100 );

		$this->assertLessThanOrEqual(
			100,
			strlen( MessageGroupStats::getDatabaseIdForGroupId( $shortId ) ),
			'Short id is <= 100 bytes long'
		);

		$this->assertLessThanOrEqual(
			100,
			strlen( MessageGroupStats::getDatabaseIdForGroupId( $longId ) ),
			'Long id is <= 100 bytes long'
		);

		$longId1 = str_repeat( 'ab', 100 ) . '1';
		$longId2 = str_repeat( 'ab', 100 ) . '2';

		$this->assertNotEquals(
			MessageGroupStats::getDatabaseIdForGroupId( $longId1 ),
			MessageGroupStats::getDatabaseIdForGroupId( $longId2 ),
			'Two long ids with the same prefix do not collide'
		);
	}

	public function testQueueUpdatesWritesStats() {
		// Trigger stats calculation without FLAG_CACHE_ONLY so $updates is populated,
		// then flush immediately with FLAG_IMMEDIATE_WRITES.
		$flags = MessageGroupStats::FLAG_NO_CACHE | MessageGroupStats::FLAG_IMMEDIATE_WRITES;
		MessageGroupStats::forGroup( 'theid', $flags );

		$dbr = $this->getDb();
		$count = $dbr->newSelectQueryBuilder()
			->from( 'translate_groupstats' )
			->where( [ 'tgs_group' => 'theid' ] )
			->caller( __METHOD__ )
			->fetchRowCount();
		$this->assertGreaterThan( 0, $count, 'Stats rows were written (lock was acquired and callback ran)' );
	}

	/** @dataProvider provideStatsCacheInvalidation */
	public function testStatsCacheInvalidation( bool $rollback ): void {
		$invalidated = false;
		$cache = $this->getMockBuilder( WANObjectCache::class )
			->setConstructorArgs( [ [ 'cache' => new HashBagOStuff() ] ] )
			->onlyMethods( [ 'touchCheckKey' ] )->getMock();
		$cache->expects( $rollback ? $this->never() : $this->once() )->method( 'touchCheckKey' )
			->with( 'translate-all-language-stats' )
			->willReturnCallback( static function () use ( &$invalidated ) {
				$invalidated = true;
				return true;
			} );
		$this->setService( 'WANObjectCache', $cache );
		MessageGroups::singleton()->overrideGroupsForTesting( [
			'theid' => new MockWikiMessageGroup( 'theid', [ 'key' => 'Message' ] ),
		] );
		$lbFactory = $this->getServiceContainer()->getDBLoadBalancerFactory();
		$lbFactory->beginPrimaryChanges( __METHOD__ );

		try {
			MessageGroupStats::forItem( 'theid', 'fi',
				MessageGroupStats::FLAG_NO_CACHE | MessageGroupStats::FLAG_IMMEDIATE_WRITES );
			$this->assertFalse( $invalidated, 'Do not invalidate while the stats are uncommitted' );
		} finally {
			if ( $rollback ) {
				$lbFactory->rollbackPrimaryChanges( __METHOD__ );
			} else {
				$lbFactory->commitPrimaryChanges( __METHOD__ );
			}
		}
		$this->assertSame( !$rollback, $invalidated );
	}

	public static function provideStatsCacheInvalidation(): array {
		return [ 'commit' => [ false ], 'rollback' => [ true ] ];
	}

	public function testFunctionReturnFormat() {
		$validLang = MessageGroupStats::forLanguage( 'en', MessageGroupStats::FLAG_CACHE_ONLY );
		$invalidLang = MessageGroupStats::forLanguage( 'ffff', MessageGroupStats::FLAG_CACHE_ONLY );

		$validGroup = MessageGroupStats::forGroup( 'theid', MessageGroupStats::FLAG_CACHE_ONLY );
		$invalidGroup = MessageGroupStats::forGroup( 'invalid-mg-group',
			MessageGroupStats::FLAG_CACHE_ONLY );

		$this->assertIsArray( current( $validLang ),
			'forLanguage returns data in valid format for valid language' );
		$this->assertIsArray( current( $invalidLang ),
			'forLanguage returns data in valid format for invalid language' );

		$this->assertIsArray( current( $validGroup ),
			'forGroup returns data in valid format for valid group' );
		$this->assertIsArray( current( $invalidGroup ),
			'forGroup returns data in valid format for invalid group' );
	}
}
