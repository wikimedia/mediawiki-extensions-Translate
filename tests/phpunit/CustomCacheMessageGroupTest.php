<?php
/**
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

class CustomCacheMessageGroupTest extends MediaWikiTestCase {
	protected $cache;

	protected function setUp() {
		parent::setUp();

		global $wgHooks;
		$this->setMwGlobals( [
			'wgHooks' => $wgHooks,
			'wgTranslateTranslationServices' => [],
		] );

		// Don't want any other custom message groups to be fetched.
		unset( $wgHooks['TranslatePostInitGroups'] );
		unset( $wgHooks['TranslateCustomCacheGroups'] );

		$this->setTemporaryHook(
			'TranslateCustomCacheGroups',
			'MockCustomCacheMessageGroup::getCacheInfo'
		);

		$this->cache = new WANObjectCache( [ 'cache' => wfGetCache( 'hash' ) ] );

		$mg = MessageGroups::singleton();
		$mg->setCache( $this->cache );
		$mg->recache();
	}

	public function testCustomCacheHook() {
		global $wgHooks;
		unset( $wgHooks['TranslateCustomCacheGroups'] );

		$mg = MessageGroups::singleton();
		$mg->recache();

		$this->assertCount( 0, MessageGroups::getAllGroups(), 'there are no custom groups once ' .
			'the TranslateCustomCacheGroups hook has been removed.' );

		$this->setTemporaryHook(
			'TranslateCustomCacheGroups',
			'MockCustomCacheMessageGroup::getCacheInfo'
		);

		$mg->recache();
		$this->assertCount( 2, MessageGroups::getAllGroups(), 'there are 2 groups.' );
	}

	public function testCustomCacheValidity() {
		$mg = MessageGroups::singleton();
		$mg->recache();

		$groups = MessageGroups::getAllGroups();

		$this->assertInstanceOf( 'CustomCacheMessageGroup', $groups[ 'mock_theid' ],
			'groups are an instance of CustomCacheMessageGroup' );
		$this->assertInstanceOf( 'CustomCacheMessageGroup', $groups[ 'mock_anotherid' ],
			'groups are an instance of CustomCacheMessageGroup' );
	}

	public function testCustomCacheDeletion() {
		$this->assertNotEmpty(
			$this->cache->get( MessageGroups::getCacheKey(
				MockCustomCacheMessageGroup::getCacheKey(),
				MockCustomCacheMessageGroup::getCacheVersion()
			)
		), 'the cache has the key for the custom message group.' );

		MessageGroups::clearCache();

		$this->assertEmpty(
			$this->cache->get( MessageGroups::getCacheKey(
				MockCustomCacheMessageGroup::getCacheKey(),
				MockCustomCacheMessageGroup::getCacheVersion()
			)
		), 'the cache no longer has the key for the custom message group after clearCache.' );
	}

	public function testDuplicateCacheKey() {
		global $wgHooks;
		$wgHooks['TranslateCustomCacheGroups'][] = 'MockCustomCacheMessageGroup::getCacheInfoDupe';

		$mg = MessageGroups::singleton();
		$this->expectException( \RuntimeException::class );
		$mg->recache();
	}
}
