<?php

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;

/**
 * @group Database
 * @covers MessageGroupLoader
 */
class MessageGroupLoaderTest extends MediaWikiIntegrationTestCase {

	protected $cacheLoaderMock;

	protected function setUp(): void {
		parent::setUp();

		$this->cacheLoaderMock = $this->createMock( MockCacheMessageGroupLoader::class );
		$this->setTemporaryHook( 'TranslateInitGroupLoaders', [ $this, 'registerLoader' ] );

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => new HashBagOStuff() ] ) );
		$mg->clearProcessCache();
	}

	public function registerLoader( array &$groupLoader ) {
		$groupLoader[] = $this->cacheLoaderMock;
	}

	public function testGroupLoaderRecache() {
		$this->cacheLoaderMock->expects( $this->once() )
			->method( 'recache' );

		MessageGroups::singleton()->recache();
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
