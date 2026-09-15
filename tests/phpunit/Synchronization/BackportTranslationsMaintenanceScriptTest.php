<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use MediaWiki\Extension\Translate\MessageGroups\FileBasedMessageGroup;
use MediaWiki\Extension\Translate\MessageGroups\MessageGroupBase;
use MediaWikiIntegrationTestCase;
use ReflectionMethod;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Synchronization\BackportTranslationsMaintenanceScript
 */
class BackportTranslationsMaintenanceScriptTest extends MediaWikiIntegrationTestCase {
	private string $sourcePath;
	private string $targetPath;
	private FileBasedMessageGroup $group;
	private BackportTranslationsMaintenanceScript $script;

	protected function setUp(): void {
		parent::setUp();

		$this->sourcePath = sys_get_temp_dir() . '/translate-backport-test-source-' . getmypid();
		$this->targetPath = sys_get_temp_dir() . '/translate-backport-test-target-' . getmypid();
		mkdir( $this->sourcePath );
		mkdir( $this->targetPath );

		$this->group = MessageGroupBase::factory( [
			'BASIC' => [
				'type' => 'file',
				'id' => 'test-backport',
				'label' => 'Test',
				'namespace' => 'NS_MEDIAWIKI',
				'description' => 'Test',
				'sourceLanguage' => 'en',
			],
			'FILES' => [
				'format' => 'Json',
				'sourcePattern' => $this->sourcePath . '/test_%CODE%.json',
				'targetPattern' => 'test_%CODE%.json',
			],
		] );

		$this->script = new BackportTranslationsMaintenanceScript();
	}

	protected function tearDown(): void {
		array_map( 'unlink', glob( $this->sourcePath . '/*' ) ?: [] );
		array_map( 'unlink', glob( $this->targetPath . '/*' ) ?: [] );
		rmdir( $this->sourcePath );
		rmdir( $this->targetPath );
		parent::tearDown();
	}

	/** Regression test for T406319: backport must not write a file containing only metadata. */
	public function testBackportDoesNotWriteMetadataOnlyFile(): void {
		// Source (dev branch): one message with an author, but the translation was later reverted
		// so the language file only has metadata/authors and no actual translations.
		file_put_contents(
			$this->sourcePath . '/test_ayh.json',
			json_encode( [ '@metadata' => [ 'authors' => [ 'Marphy 123' ] ] ] ) . "\n"
		);

		// Source language definition (needed to build the key compatibility map)
		file_put_contents(
			$this->sourcePath . '/test_en.json',
			json_encode( [ 'key1' => 'Hello' ] ) . "\n"
		);

		// Target (stable branch): same source language definition
		file_put_contents(
			$this->targetPath . '/test_en.json',
			json_encode( [ 'key1' => 'Hello' ] ) . "\n"
		);

		$keyCompatibilityMap = [ 'key1' => true ];

		$method = new ReflectionMethod( BackportTranslationsMaintenanceScript::class, 'backport' );
		$status = $method->invoke(
			$this->script,
			$this->group,
			$this->sourcePath,
			$this->targetPath,
			$keyCompatibilityMap,
			'ayh'
		);

		$this->assertSame( 'no translations', $status );
		$this->assertFileDoesNotExist( $this->targetPath . '/test_ayh.json' );
	}
}
