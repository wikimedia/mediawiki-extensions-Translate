<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use MediaWiki\Extension\Translate\ConfigNames;
use MediaWiki\Extension\Translate\MessageGroups\FileBasedMessageGroup;
use MediaWiki\Extension\Translate\MessageGroups\MessageGroupBase;
use MediaWiki\Maintenance\MaintenanceFatalError;
use MediaWikiIntegrationTestCase;
use Wikimedia\TestingAccessWrapper;

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
		TestingAccessWrapper::newFromObject( $this->script )->isTesting = true;
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

		$status = $this->runBackport( 'ayh' );

		$this->assertSame( 'no translations', $status );
		$this->assertFileDoesNotExist( $this->targetPath . '/test_ayh.json' );
	}

	public static function provideExclusionRules(): iterable {
		$plural = '{{PLURAL:$1|a|b|c}}';
		$branchAndLanguageRule = [ 'plural' => [ 'branches' => [ 'REL1_43' ], 'languages' => [ 'fi' ] ] ];

		yield 'no rules' => [ [], null, 'fi', $plural, $plural ];
		yield '#FORMAL: is not special without a rule' => [
			[], null, 'fi', '{{#FORMAL:a|b}}', '{{#FORMAL:a|b}}'
		];
		yield 'matching rule keeps the target value' => [ $branchAndLanguageRule, 'REL1_43', 'fi', $plural, 'Old' ];
		yield 'rule for another branch' => [ $branchAndLanguageRule, 'REL1_45', 'fi', $plural, $plural ];
		yield 'rule for another language' => [ $branchAndLanguageRule, 'REL1_43', 'sv', $plural, $plural ];
		yield 'content rule does not match' => [
			[ 'plural' => [ 'content' => '/\\{\\{PLURAL:/' ] ], null, 'fi', 'New', 'New'
		];
		yield 'removal is not blocked by a matching rule' => [ $branchAndLanguageRule, 'REL1_43', 'fi', null, null ];
	}

	/** @dataProvider provideExclusionRules */
	public function testExclusionRules(
		array $rules,
		?string $targetBranch,
		string $language,
		?string $sourceValue,
		?string $expectedValue
	): void {
		$this->writeDefinitions();
		$this->writeTranslation( $this->sourcePath, $language, $sourceValue );
		$this->writeTranslation( $this->targetPath, $language, 'Old' );

		$this->runBackport( $language, $rules, $targetBranch, [ 'key1' => true, 'key2' => true ] );

		$messages = json_decode( file_get_contents( "$this->targetPath/test_$language.json" ), true );
		$this->assertSame( $expectedValue, $messages['key1'] ?? null );
	}

	public function testSkipCountByRuleName(): void {
		$this->writeDefinitions();
		$this->writeTranslation( $this->sourcePath, 'fi', 'New' );
		$this->writeTranslation( $this->targetPath, 'fi', 'Old' );

		$this->runBackport( 'fi', [ 'finnish' => [ 'languages' => [ 'fi' ] ] ] );

		$this->assertSame(
			[ 'finnish' => 1 ],
			TestingAccessWrapper::newFromObject( $this->script )->skipCounts
		);
	}

	public function testBranchRuleWithoutTargetBranch(): void {
		$this->expectException( MaintenanceFatalError::class );
		$this->expectOutputRegex( '/--target-branch is required/' );
		$this->runBackport( 'fi', [ 'rule' => [ 'branches' => [ 'REL1_43' ] ] ] );
	}

	public function testInvalidRules(): void {
		$this->expectException( MaintenanceFatalError::class );
		$this->expectOutputRegex( '/must have a name/' );
		$this->runBackport( 'fi', [ [ 'languages' => [ 'fi' ] ] ] );
	}

	private function writeDefinitions(): void {
		$this->writeTranslation( $this->sourcePath, 'en', 'Hello' );
		$this->writeTranslation( $this->targetPath, 'en', 'Hello' );
	}

	private function writeTranslation( string $path, string $language, ?string $value ): void {
		$messages = [ '@metadata' => [ 'authors' => [ 'Author' ] ] ];
		if ( $value !== null ) {
			$messages['key1'] = $value;
		}
		// Keeps the file non-empty when key1 is removed
		$messages['key2'] = 'Other';
		file_put_contents( "$path/test_$language.json", json_encode( $messages ) . "\n" );
	}

	private function runBackport(
		string $language,
		array $rules = [],
		?string $targetBranch = null,
		array $keyCompatibilityMap = [ 'key1' => true ]
	): string {
		$this->overrideConfigValue( ConfigNames::BackportExclusionRules, $rules );
		if ( $targetBranch !== null ) {
			$this->script->setOption( 'target-branch', $targetBranch );
		}

		$script = TestingAccessWrapper::newFromObject( $this->script );
		$script->initExclusionRules();
		return $script->backport(
			$this->group,
			$this->sourcePath,
			$this->targetPath,
			$keyCompatibilityMap,
			$language
		);
	}
}
