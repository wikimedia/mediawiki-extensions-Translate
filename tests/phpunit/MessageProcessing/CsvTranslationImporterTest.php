<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageProcessing;

use HashBagOStuff;
use HashMessageIndex;
use MediaWiki\Extension\Translate\MessageGroupProcessing\CsvTranslationImporter;
use MediaWiki\Page\WikiPageFactory;
use MediaWikiIntegrationTestCase;
use MessageGroups;
use MessageIndex;
use MockWikiMessageGroup;
use WANObjectCache;

/** @coversDefaultClass \MediaWiki\Extension\Translate\MessageGroupProcessing\CsvTranslationImporter */
class CsvTranslationImporterTest extends MediaWikiIntegrationTestCase {
	protected function setUp(): void {
		parent::setUp();

		$this->setMwGlobals( [
			'wgTranslateCacheDirectory' => $this->getNewTempDirectory(),
			'wgTranslateTranslationServices' => [],
			'wgTranslateMessageNamespaces' => [ NS_MEDIAWIKI ]
		] );

		$this->setTemporaryHook( 'TranslatePostInitGroups', [ $this, 'getTestGroups' ] );

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => new HashBagOStuff() ] ) );
		$mg->recache();

		MessageIndex::setInstance( new HashMessageIndex() );
		MessageIndex::singleton()->rebuild();
	}

	public function getTestGroups( &$list ) {
		$messages = [
			't1' => 'bunny',
			't2' => 'fanny',
			't3' => 'bunny',
			't4' => 'fanny'
		];
		$list['test-group'] =
			new MockWikiMessageGroup( 'test-group', $messages );

		return false;
	}

	/** @dataProvider provideTestParseFile */
	public function testParseFile( string $filepath, array $errors, ?array $csvRows ): void {
		$csvTranslationImporter = new CsvTranslationImporter( $this->createMock( WikiPageFactory::class ) );
		$status = $csvTranslationImporter->parseFile( $filepath );

		foreach ( $errors as $error ) {
			$this->assertStringContainsString( $error, json_encode( $status->getErrors() ) );
		}

		if ( $csvRows ) {
			foreach ( $csvRows as $index => $row ) {
				$this->assertArrayEquals( $csvRows[$index], $status->getValue()[$index] );
			}

		}
	}

	public function provideTestParseFile() {
		yield [
			'filenotexists.csv',
			[ 'not exist, is not readable or is not a file' ],
			null
		];

		yield [
			__DIR__ . '/../data/csv-to-import/invalid-unit.csv',
			[
				'Empty message titles found',
				'Invalid message title(s) found on row(s): 4'
			],
			null
		];

		yield [
			__DIR__ . '/../data/csv-to-import/invalid-code.csv',
			[ 'Invalid language codes detected' ],
			null
		];

		yield [
			__DIR__ . '/../data/csv-to-import/invalid-csv.csv',
			[ 'No languages found for import' ],
			null
		];

		yield [
			__DIR__ . '/../data/csv-to-import/valid.csv',
			[],
			[
				[
					'unitTitle' => 'MediaWiki:t1',
					'translations' => [
						'fr' => 'bunny - fr',
						'es' => 'bunny - es'
					]
				],
				[
					'unitTitle' => 'MediaWiki:t3',
					'translations' => [
						'fr' => 'bunny - fr',
						'es' => null
					]
				],
				[
					'unitTitle' => 'MediaWiki:t4',
					'translations' => [
						'fr' => null,
						'es' => null
					]
				]
			]
		];
	}
}
