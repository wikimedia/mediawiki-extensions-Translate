<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use FileBasedMessageGroup;
use MediaWikiIntegrationTestCase;
use MessageGroupBase;
use MockMessageCollectionForExport;

/**
 * Tests for the AMD i18n message file format (used by require.js and Dojo).
 *
 * @author Matthias Palmer
 * @copyright Copyright © 2011-2015, MetaSolutions AB
 * @license GPL-2.0-or-later
 *
 * @covers \MediaWiki\Extension\Translate\FileFormatSupport\AmdFormat
 */
class AmdFormatTest extends MediaWikiIntegrationTestCase {

	private const GROUP_CONFIGURATION = [
		'BASIC' => [
			'class' => FileBasedMessageGroup::class,
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		],
		'FILES' => [
			'format' => 'Amd',
			'sourcePattern' => 'fake_reference_not_used_in_practise',
			'targetPattern' => 'fake_reference_not_used_in_practise',
		],
	];

	/** @dataProvider amdProvider */
	public function testParsing( array $messages, array $authors, string $file ): void {
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( self::GROUP_CONFIGURATION );
		$amdFormat = new AmdFormat( $group );
		$parsed = $amdFormat->readFromVariable( $file );
		$expected = [
			'MESSAGES' => $messages,
			'AUTHORS' => $authors,
			'METADATA' => [],
		];
		$this->assertEquals( $expected, $parsed );
	}

	public static function amdProvider(): array {
		$values = [];

		$file1 =
			<<<'JS'
			define({
				"one": "jeden",
				"two": "dwa",
				"three": "trzy"
			});
			JS;

		$values[] = [
			[
				'one' => 'jeden',
				'two' => 'dwa',
				'three' => 'trzy',
			],
			[],
			$file1,
		];

		$file2 =
			<<<'JS'
			/**
			 * Translators:
			 *  - Matthias
			 *  - Hannes
			 */
			define({
			   "root": {
			      "word": "слово"
			   }
			});
			JS;

		$values[] = [
			[ 'word' => 'слово' ],
			[ 'Matthias', 'Hannes' ],
			$file2,
		];

		return $values;
	}

	public function testExport(): void {
		$collection = new MockMessageCollectionForExport();
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( self::GROUP_CONFIGURATION );
		$amdFormat = new AmdFormat( $group );
		$data = $amdFormat->writeIntoVariable( $collection );
		$parsed = $amdFormat->readFromVariable( $data );

		$this->assertEquals(
			[ 'Nike the bunny' ],
			$parsed['AUTHORS'],
			'Authors are exported'
		);

		$this->assertArrayHasKey( 'fuzzymsg', $parsed['MESSAGES'], 'fuzzy message is exported' );
		$this->assertArrayHasKey(
			'translatedmsg',
			$parsed['MESSAGES'],
			'translated message is exported'
		);
		if ( array_key_exists( 'untranslatedmsg', $parsed['MESSAGES'] ) ) {
			$this->fail( 'Untranslated messages should not be exported' );
		}
	}
}
