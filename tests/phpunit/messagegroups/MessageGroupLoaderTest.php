<?php

class MessageGroupLoaderTest extends MediaWikiIntegrationTestCase {

	protected $cacheLoaderMock;

	protected function setUp(): void {
		parent::setUp();

		$this->cacheLoaderMock = $this->createMock( MockCacheMessageGroupLoader::class );
		$this->setTemporaryHook( 'TranslateInitGroupLoaders', [ $this, 'registerLoader' ] );

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => new HashBagOStuff() ] ) );
		$mg->clearCache();
	}

	public function registerLoader( array &$groupLoader ) {
		$groupLoader[] = $this->cacheLoaderMock;
	}

	public function testGroupLoaderRecache() {
		$this->cacheLoaderMock->expects( $this->once() )
			->method( 'getGroups' )
			->willReturn( [] );

		$this->cacheLoaderMock->expects( $this->once() )
			->method( 'recache' );

		MessageGroups::singleton()->recache();
	}

	public function testGroupLoaderClearCache() {
		$this->cacheLoaderMock->expects( $this->once() )
			->method( 'clearCache' );

		MessageGroups::singleton()->clearCache();
	}

	public function testGroupLoaderGetGroups() {
		$testGroup = new WikiMessageGroup( 'testgroup', 'hello' );

		$this->cacheLoaderMock->expects( $this->once() )
			->method( 'getGroups' )
			->willReturn( [
				$testGroup
			] );

		$groups = MessageGroups::singleton()->getGroups();

		$this->assertCount( 1, $groups, 'the message group returned by the loader is present' );
		$this->assertEquals( 'testgroup', $groups[0]->getId(), 'the id of the message group ' .
			'returned by the loader is present in the groups' );
	}
}
