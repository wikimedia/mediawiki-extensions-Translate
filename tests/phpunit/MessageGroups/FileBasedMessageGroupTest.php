<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroups;

use MediaWiki\Extension\Translate\ConfigNames;
use MediaWikiIntegrationTestCase;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\MessageGroups\FileBasedMessageGroup
 */
class FileBasedMessageGroupTest extends MediaWikiIntegrationTestCase {
	private FileBasedMessageGroup $group;

	protected function setUp(): void {
		parent::setUp();

		$dir = $this->getNewTempDirectory();
		$this->overrideConfigValue( ConfigNames::CacheDirectory, $dir );

		file_put_contents(
			"$dir/en.json",
			json_encode( [
				'test-defaultwiki' => 'lowercase value',
				'test-defaultWiki' => 'camel-case value',
				'test-with_underscore' => 'underscore value',
				// The key with a space comes first, so the loop over all keys finds it first.
				'test-two words' => 'space value',
				'test-two_words' => 'two underscore value',
			] )
		);

		$this->group = MessageGroupBase::factory( [
			'BASIC' => [
				'type' => 'file',
				'id' => 'test-file-group',
				'label' => 'Test',
				'namespace' => 'NS_MEDIAWIKI',
				'sourceLanguage' => 'en',
			],
			'FILES' => [
				'format' => 'Json',
				'sourcePattern' => "$dir/%CODE%.json",
			],
		] );
		$this->group->getMessageGroupCache( 'en' )->create();
	}

	/** @dataProvider provideGetMessage */
	public function testGetMessage( string $key, ?string $expected ): void {
		$this->assertSame( $expected, $this->group->getMessage( $key, 'en' ) );
	}

	public static function provideGetMessage(): iterable {
		yield 'exact key' => [ 'test-defaultWiki', 'camel-case value' ];
		yield 'title key, lowercase' => [ 'Test-defaultwiki', 'lowercase value' ];
		yield 'title key, camel case' => [ 'Test-defaultWiki', 'camel-case value' ];
		yield 'title key with space' => [ 'Test-with underscore', 'underscore value' ];
		yield 'title key prefers the key with the same spelling' => [ 'Test-two_words', 'two underscore value' ];
		yield 'title key with space, key has space' => [ 'Test-two words', 'space value' ];
		yield 'different case after the first letter' => [ 'Test-DefaultWiki', null ];
		yield 'unknown key' => [ 'Test-unknown', null ];
	}
}
