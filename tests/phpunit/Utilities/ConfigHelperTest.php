<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Tests\Utilities;

use MediaWiki\Api\ApiErrorFormatter;
use MediaWiki\Api\ApiResult;
use MediaWiki\Extension\Translate\MessageGroups\FileBasedMessageGroup;
use MediaWiki\Extension\Translate\MessageGroups\MessageGroup;
use MediaWiki\Extension\Translate\MessageGroups\MessageGroupBase;
use MediaWiki\Extension\Translate\Utilities\ConfigHelper;
use MediaWiki\Permissions\Authority;
use MediaWikiIntegrationTestCase;

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

	/** @dataProvider provideTargetLanguageDisabledStatus */
	public function testGetTargetLanguageDisabledStatus(
		array $disabledLanguages,
		string $groupId,
		?array $translatableLanguages,
		string $languageCode,
		?string $expectedError,
		?string $expectedReason
	): void {
		$this->setMwGlobals( 'wgTranslateDisabledTargetLanguages', $disabledLanguages );

		$group = $this->createMock( MessageGroup::class );
		$group->method( 'getId' )->willReturn( $groupId );
		$group->method( 'getTranslatableLanguages' )->willReturn( $translatableLanguages );

		$status = ( new ConfigHelper() )
			->getTargetLanguageDisabledStatus( $group, $languageCode );

		if ( $expectedError === null ) {
			$this->assertStatusGood( $status );
		} else {
			$this->assertStatusError( $expectedError, $status );
			$params = $status->getMessages( 'error' )[0]->getParams();
			$this->assertCount( $expectedReason === null ? 1 : 2, $params );
			if ( $expectedReason !== null ) {
				$this->assertSame( $expectedReason, $params[1] );
			}
		}
	}

	public static function provideTargetLanguageDisabledStatus(): iterable {
		yield 'disabled via global config with specific group id' => [
			[ 'test-group' => [ 'de' => 'No reason' ] ],
			'test-group', MessageGroup::DEFAULT_LANGUAGES, 'de', 'translate-language-disabled-reason', 'No reason'
		];
		yield 'disabled via global config with partial group id' => [
			[ 'test' => [ 'de' => 'No reason' ] ],
			'test-group', MessageGroup::DEFAULT_LANGUAGES, 'de', 'translate-language-disabled-reason', 'No reason'
		];
		yield 'disabled via global config with wildcard' => [
			[ '*' => [ 'de' => 'No reason' ] ],
			'test-group', MessageGroup::DEFAULT_LANGUAGES, 'de', 'translate-language-disabled-reason', 'No reason'
		];
		yield 'not in group translatable languages' => [
			[], 'test-group', [ 'en' => 'en' ], 'de', 'translate-language-disabled', null
		];
		yield 'not disabled' => [
			[], 'test-group', MessageGroup::DEFAULT_LANGUAGES, 'de', null, null
		];
		yield 'not disabled with group languages' => [
			[], 'test-group', [ 'de' => 'de' ], 'de', null, null
		];
		yield 'globally enabled but disabled via group languages' => [
			[], 'test-group', [ 'en' => 'en' ], 'de', 'translate-language-disabled', null
		];
		yield 'globally disabled but enabled via group languages' => [
			[ '*' => [ 'de' => 'No reason' ] ], 'test-group', [ 'de' => 'de' ], 'de', null, null
		];
		yield 'disabled globally and via group languages' => [
			[ '*' => [ 'de' => 'Global reason' ] ],
			'test-group', [ 'en' => 'en' ], 'de', 'translate-language-disabled-reason', 'Global reason'
		];
	}

	/** @dataProvider provideCanSeeExcludedLanguageStats */
	public function testCanSeeExcludedLanguageStats(
		bool $expected,
		bool $userHasRight,
		int $translated,
		int $fuzzy
	): void {
		$authority = $this->createMock( Authority::class );
		$authority->method( 'isAllowed' )->willReturn( $userHasRight );

		$this->assertSame(
			$expected,
			( new ConfigHelper() )->canSeeExcludedLanguageStats( $authority, $translated, $fuzzy )
		);
	}

	public static function provideCanSeeExcludedLanguageStats(): iterable {
		yield 'user lacks right' => [ false, false, 10, 0 ];
		yield 'no translations exist' => [ false, true, 0, 0 ];
		yield 'has right and translated messages' => [ true, true, 10, 0 ];
		yield 'has right and fuzzy messages only' => [ true, true, 0, 5 ];
		yield 'has right and both translated and fuzzy' => [ true, true, 3, 2 ];
	}

	public function testGlobalDisabledReasonWithGroupLanguageConfiguration(): void {
		$this->setMwGlobals( 'wgTranslateDisabledTargetLanguages', [
			'*' => [ 'de' => 'Use de-formal' ]
		] );
		$group = MessageGroupBase::factory( [
			'BASIC' => [
				'class' => FileBasedMessageGroup::class,
				'id' => 'test-group',
				'namespace' => 'NS_MEDIAWIKI',
			],
			'LANGUAGES' => [ 'exclude' => [ 'fr' ] ],
		] );

		$status = ( new ConfigHelper() )->getTargetLanguageDisabledStatus( $group, 'de' );

		$this->assertStatusError( 'translate-language-disabled-reason', $status );
		$this->assertSame( 'Use de-formal', $status->getMessages( 'error' )[0]->getParams()[1] );
	}

	/** @dataProvider provideDisabledLanguageMessages */
	public function testDisabledLanguageNameUsesRenderingLanguage( ?string $reason ): void {
		$this->setMwGlobals( 'wgTranslateDisabledTargetLanguages',
			$reason === null ? [] : [ '*' => [ 'de' => $reason ] ]
		);
		// Core supplies only autonyms. Supply translated names here, so that
		// the test does not depend on the cldr extension.
		$this->setTemporaryHook(
			'LanguageGetTranslatedLanguageNames',
			static function ( array &$names, string $inLanguage ): void {
				$names += [ 'en' => [ 'de' => 'German' ], 'fr' => [ 'de' => 'allemand' ] ][$inLanguage] ?? [];
			}
		);
		$group = $this->createMock( MessageGroup::class );
		$group->method( 'getId' )->willReturn( 'test-group' );
		$group->method( 'getTranslatableLanguages' )->willReturn( [ 'en' => 'en' ] );
		$status = ( new ConfigHelper() )->getTargetLanguageDisabledStatus( $group, 'de' );
		$name = $status->getMessages( 'error' )[0]->getParams()[0];

		foreach ( [ 'en' => 'German', 'fr' => 'allemand', 'de' => 'Deutsch' ] as $code => $expected ) {
			// Use a core message to test the parameter independently of the error's translations.
			$message = wfMessage( 'rawmessage', $name )->inLanguage( $code );
			$this->assertSame( $expected, $message->text() );
			$this->assertSame( $expected, strip_tags( $message->parse() ) );

			$formatter = new ApiErrorFormatter(
				new ApiResult( 100000 ),
				$this->getServiceContainer()->getLanguageFactory()->getLanguage( $code )
			);
			foreach ( [ 'plaintext', 'wikitext', 'html' ] as $format ) {
				$result = $formatter->formatMessage( $message, $format );
				$this->assertSame( $expected, strip_tags( $result[$format === 'html' ? 'html' : 'text'] ) );
			}
		}
	}

	public static function provideDisabledLanguageMessages(): iterable {
		yield 'group restriction' => [ null ];
		yield 'global restriction with reason' => [ 'Use de-formal' ];
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
