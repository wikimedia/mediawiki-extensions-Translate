<?php
class MessageGroupLoaderTest extends MediaWikiTestCase {

	protected $cacheDbLoaderMock;

	protected function setUp() {
		parent::setUp();

		$this->cacheDbLoaderMock = $this->createMock( MockDbCacheMessageGroupLoader::class );
		$this->setTemporaryHook( 'TranslateInitGroupLoaders', [ $this, 'registerLoader' ] );

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => wfGetCache( 'hash' ) ] ) );
		$mg->clearCache();
	}

	public function registerLoader( array &$groupLoader ) {
		$groupLoader[] = $this->cacheDbLoaderMock;
	}

	public function testGroupLoaderConfiguration() {
		$this->cacheDbLoaderMock->expects( $this->once() )
			->method( 'setCache' );

		$this->cacheDbLoaderMock->expects( $this->once() )
			->method( 'setDatabase' );

		$this->cacheDbLoaderMock->expects( $this->once() )
			->method( 'getGroups' )
			->willReturn( [] );

		MessageGroups::singleton()->getGroups();
	}

	public function testGroupLoaderRecache() {
		$this->cacheDbLoaderMock->expects( $this->once() )
			->method( 'getGroups' )
			->willReturn( [] );

		$this->cacheDbLoaderMock->expects( $this->once() )
			->method( 'recache' );

		MessageGroups::singleton()->recache();
	}

	public function testGroupLoaderClearCache() {
		$this->cacheDbLoaderMock->expects( $this->once() )
			->method( 'clearCache' );

		MessageGroups::singleton()->clearCache();
	}

	public function testGroupLoaderGetGroups() {
		$testGroup = new WikiMessageGroup( 'testgroup', 'hello' );

		$this->cacheDbLoaderMock->expects( $this->once() )
			->method( 'getGroups' )
			->willReturn( [
				$testGroup
			] );

		$groups = MessageGroups::singleton()->getGroups();

		$this->assertCount( 1, $groups, 'the message group returned bythe loader is present' );
		$this->assertEquals( 'testgroup', $groups[0]->getId(), 'the id of the message group ' .
			'returned by the loader is present in the groups' );
	}
}
