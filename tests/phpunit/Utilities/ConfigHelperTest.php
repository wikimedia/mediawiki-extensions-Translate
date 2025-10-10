<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Tests\Utilities;

use MediaWiki\Extension\Translate\Utilities\ConfigHelper;
use MediaWikiIntegrationTestCase;
use MessageGroup;

/**
 * Tests for ConfigHelper.
 *
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Utilities\ConfigHelper
 */
class ConfigHelperTest extends MediaWikiIntegrationTestCase {
	public function testGetValidationExclusionFile() {
		$this->setMwGlobals( 'wgTranslateValidationExclusionFile', 'some/file.txt' );
		$configHelper = new ConfigHelper();
		$this->assertSame(
			'some/file.txt',
			$configHelper->getValidationExclusionFile(),
			'it should return the configured value'
		);
	}

	public function testGetTranslateAuthorExclusionList() {
		$this->setMwGlobals( 'wgTranslateAuthorExclusionList', [ 'some', 'values' ] );
		$configHelper = new ConfigHelper();
		$this->assertSame(
			[ 'some', 'values' ],
			$configHelper->getTranslateAuthorExclusionList(),
			'it should return the configured value'
		);
	}

	public function testGetDisabledTargetLanguages() {
		$this->setMwGlobals( 'wgTranslateDisabledTargetLanguages', [ 'some' => [ 'values' ] ] );
		$configHelper = new ConfigHelper();
		$this->assertSame(
			[ 'some' => [ 'values' ] ],
			$configHelper->getDisabledTargetLanguages(),
			'it should return the configured value'
		);
	}

	/** @dataProvider provideIsTargetLanguageDisabled */
	public function testIsTargetLanguageDisabled(
		array $disabledLanguages,
		string $groupId,
		?array $translatableLanguages,
		string $languageCode,
		bool $expected,
		?string $expectedReason
	): void {
		$this->setMwGlobals( 'wgTranslateDisabledTargetLanguages', $disabledLanguages );

		$group = $this->createMock( MessageGroup::class );
		$group->method( 'getId' )->willReturn( $groupId );
		$group->method( 'getTranslatableLanguages' )->willReturn( $translatableLanguages );

		$configHelper = new ConfigHelper();
		$reason = null;
		$this->assertSame(
			$expected,
			$configHelper->isTargetLanguageDisabled( $group, $languageCode, $reason ),
			'it should return the correct disabled status'
		);

		$this->assertSame( $expectedReason, $reason, 'it should set the correct reason' );
	}

	public static function provideIsTargetLanguageDisabled(): iterable {
		yield 'disabled via global config with specific group id' => [
			[ 'test-group' => [ 'de' => 'No reason' ] ],
			'test-group',
			MessageGroup::DEFAULT_LANGUAGES,
			'de',
			true,
			'No reason'
		];
		yield 'disabled via global config with partial group id' => [
			[ 'test' => [ 'de' => 'No reason' ] ],
			'test-group',
			MessageGroup::DEFAULT_LANGUAGES,
			'de',
			true,
			'No reason'
		];
		yield 'disabled via global config with wildcard' => [
			[ '*' => [ 'de' => 'No reason' ] ],
			'test-group',
			MessageGroup::DEFAULT_LANGUAGES,
			'de',
			true,
			'No reason'
		];
		yield 'not in group translatable languages' => [
			[],
			'test-group',
			[ 'en' => 'en' ],
			'de',
			true,
			null
		];
		yield 'not disabled' => [
			[],
			'test-group',
			MessageGroup::DEFAULT_LANGUAGES,
			'de',
			false,
			null
		];
		yield 'not disabled with group languages' => [
			[],
			'test-group',
			[ 'de' => 'de' ],
			'de',
			false,
			null
		];
		yield 'globally enabled but disabled via group languages' => [
			[],
			'test-group',
			[ 'en' => 'en' ],
			'de',
			true,
			null
		];
		yield 'globally disabled but enabled via group languages' => [
			[ '*' => [ 'de' => 'No reason' ] ],
			'test-group',
			[ 'de' => 'de' ],
			'de',
			false,
			null
		];
		yield 'disabled globally and via group languages' => [
			[ '*' => [ 'de' => 'Global reason' ] ],
			'test-group',
			[ 'en' => 'en' ],
			'de',
			true,
			'Global reason'
		];
	}

	/** @dataProvider provideIsAuthorExcluded */
	public function testIsAuthorExcluded(
		array $authorExclusionList,
		string $groupId,
		string $languageCode,
		string $username,
		bool $expected
	): void {
		$this->setMwGlobals( 'wgTranslateAuthorExclusionList', $authorExclusionList );

		$configHelper = new ConfigHelper();
		$this->assertSame(
			$expected,
			$configHelper->isAuthorExcluded( $groupId, $languageCode, $username ),
			'it should return the correct exclusion status'
		);
	}

	public static function provideIsAuthorExcluded(): iterable {
		yield 'no rules' => [ [], 'test-group', 'de', 'TestUser', false ];
		yield 'simple exclusion match' => [
			[ [ 'exclude', '/^test-group;de;TestUser$/' ] ],
			'test-group',
			'de',
			'TestUser',
			true
		];
		yield 'simple exclusion no match' => [
			[ [ 'exclude', '/^test-group;de;AnotherUser$/' ] ],
			'test-group',
			'de',
			'TestUser',
			false
		];
		yield 'include overrides exclude' => [
			[
				[ 'exclude', '/^test-group;de;TestUser$/' ],
				[ 'include', '/^test-group;de;TestUser$/' ]
			],
			'test-group',
			'de',
			'TestUser',
			false
		];
		yield 'exclude after include has no effect' => [
			[
				[ 'include', '/^test-group;de;TestUser$/' ],
				[ 'exclude', '/^test-group;de;TestUser$/' ]
			],
			'test-group',
			'de',
			'TestUser',
			false
		];
		yield 'wildcard exclusion' => [
			[ [ 'exclude', '/^test-group;.*;TestUser$/' ] ],
			'test-group',
			'de',
			'TestUser',
			true
		];
		yield 'wildcard exclusion with include' => [
			[
				[ 'exclude', '/^test-group;.*;TestUser$/' ],
				[ 'include', '/^test-group;de;TestUser$/' ]
			],
			'test-group',
			'de',
			'TestUser',
			false
		];
	}
}
