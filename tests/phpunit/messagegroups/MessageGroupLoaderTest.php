<?php

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;

/**
 * @covers \MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups
 * @group Database
 */
class MessageGroupLoaderTest extends MediaWikiIntegrationTestCase {
	use MessageGroupTestTrait;

	/** @var MockCacheMessageGroupLoader */
	protected $cacheLoaderMock;

	protected function setUp(): void {
		parent::setUp();
		$config = new MessageGroupTestConfig();
		$config->groupInitLoaders = [ [ $this, 'registerLoader' ] ];
		$config->skipMessageIndexRebuild = true;
		$this->cacheLoaderMock = $this->createMock( MockCacheMessageGroupLoader::class );
		$this->setupGroupTestEnvironmentWithConfig( $this, $config );
	}

	public function registerLoader( array &$groupLoader, array $deps ) {
		$groupLoader[] = $this->cacheLoaderMock;
	}

	public function testGroupLoaderGetGroups() {
		$testGroup = new WikiMessageGroup( 'testgroup', 'hello' );
		$this->cacheLoaderMock->expects( $this->once() )
			->method( 'recache' )
			->willReturn( [
				$testGroup
			] );
		MessageGroups::singleton()->recache();
		MessageGroups::singleton()->clearProcessCache();
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
