<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use FileBasedMessageGroup;
use MediaWikiIntegrationTestCase;
use MessageGroupBase;
use MockMessageCollectionForExport;

/**
 * Tests for JSON message file format.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

/** @covers \MediaWiki\Extension\Translate\FileFormatSupport\JsonFormat */
class JsonFormatTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setUp();
		$this->groupConfiguration = [
			'BASIC' => [
				'class' => FileBasedMessageGroup::class,
				'id' => 'test-id',
				'label' => 'Test Label',
				'namespace' => 'NS_MEDIAWIKI',
				'description' => 'Test description',
			],
			'FILES' => [
				'format' => 'Json',
				'sourcePattern' => __DIR__ . '/../data/jsontest_%CODE%.json',
				'targetPattern' => 'jsontest_%CODE%.json',
			],
		];
	}

	private array $groupConfiguration;

	/** @dataProvider jsonProvider */
	public function testParsing( array $messages, array $authors, string $file ): void {
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$jsonFormat = new JsonFormat( $group );

		$parsed = $jsonFormat->readFromVariable( $file );
		$expected = [
			'MESSAGES' => $messages,
			'AUTHORS' => $authors,
			'EXTRA' => [ 'METADATA' => [] ],
		];
		$this->assertEquals( $expected, $parsed );

		if ( $messages === [] ) {
			$this->assertFalse( JsonFormat::isValid( $file ) );
		} else {
			$this->assertTrue( JsonFormat::isValid( $file ) );
		}
	}

	public static function jsonProvider(): array {
		$values = [];

		$values[] = [
			[
				'one' => 'jeden',
				'two' => 'dwa',
				'three' => 'trzy',
			],
			[],
			<<<'JSON'
			{
				"one": "jeden",
				"two": "dwa",
				"three": "trzy"
			}
			JSON
		];

		$values[] = [
			[ 'word' => 'слово' ],
			[ 'Niklas', 'Amir' ],
			<<<'JSON'
			{
				"@metadata": {
					"authors": ["Niklas", "Amir"]
				},
				"word": "слово"
			}
			JSON
		];

		$values[] = [
			[],
			[],
			<<<'JSON'
			<This is not
			Json!>@£0 file
			JSON
		];

		return $values;
	}

	public function testExport(): void {
		$collection = new MockMessageCollectionForExport();
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$jsonFormat = new JsonFormat( $group );
		$data = $jsonFormat->writeIntoVariable( $collection );
		$parsed = $jsonFormat->readFromVariable( $data );

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

		$this->assertEquals(
			'metavalue',
			$parsed['EXTRA']['METADATA']['metakey'],
			'metadata is preserved'
		);
	}
}
