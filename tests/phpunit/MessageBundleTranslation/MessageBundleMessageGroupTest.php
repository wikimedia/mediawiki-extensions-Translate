<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use Generator;
use LogicException;
use MediaWiki\Content\Content;
use MediaWiki\Content\TextContent;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\RevisionStore;
use MediaWikiIntegrationTestCase;

/** @covers \MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundleMessageGroup */
class MessageBundleMessageGroupTest extends MediaWikiIntegrationTestCase {
	/** @dataProvider provideGetDefinitionCases */
	public function testGetDefinitions(
		array $mockOptions,
		?string $expectedException,
		?string $expectedExceptionMessage,
		?array $expectedDefinition = null
	) {
		$messageBundle = $this->setupMocks(
			$mockOptions['key'],
			$mockOptions['revisionId'],
			$mockOptions['revisionExists'],
			$mockOptions['jsonText'],
			$mockOptions['content'] ?? null
		);

		if ( $expectedException ) {
			$this->expectException( $expectedException );
			$this->expectExceptionMessage( $expectedExceptionMessage );
		}

		$definitions = $messageBundle->getDefinitions();
		if ( !$expectedException ) {
			$this->assertIsArray( $definitions );
			$this->assertArrayNotHasKey( '@metadata', $definitions, 'getDefinitions() removes metadata' );
			$this->assertArrayEquals( $expectedDefinition, $definitions );
		}
	}

	/** Data provider for testGetDefinitions. */
	public static function provideGetDefinitionCases(): Generator {
		yield 'Success case: valid revision and content' => [
			[
				'key' => 'test-mb-1',
				'revisionId' => 1,
				'revisionExists' => true,
				'jsonText' => <<<JSON
					{
						"@metadata": { "sourceLanguage": "fr" },
						"message-key": "This is the correct message!"
					}
				JSON,
			],
			null,
			null,
			[ 'message-key' => 'This is the correct message!' ],
		];

		yield 'Success case: empty content' => [
			[
				'key' => 'test-mb-2',
				'revisionId' => 2,
				'revisionExists' => true,
				'jsonText' => '{}',
			],
			null,
			null,
			[],
		];

		yield 'Failure case: revision does not exist' => [
			[
				'key' => 'test-mb-3',
				'revisionId' => 3,
				'revisionExists' => false,
				'jsonText' => null,
			],
			LogicException::class,
			'Could not find revision id 3',
		];

		yield 'Failure case: wrong content type' => [
			[
				'key' => 'test-mb-4',
				'revisionId' => 4,
				'revisionExists' => true,
				'jsonText' => null,
				'content' => new TextContent( 'not a message bundle' ),
			],
			LogicException::class,
			'Content with revision id 4 has wrong content format',
			null,
		];

		yield 'Failure case: invalid JSON in content' => [
			[
				'key' => 'test-mb-5',
				'revisionId' => 5,
				'revisionExists' => true,
				'jsonText' => '{"key": "value", THIS IS NOT JSON}',
			],
			LogicException::class,
			'Content with revision id 5 is not valid JSON',
		];
	}

	private function setupMocks(
		string $messageBundleKey,
		int $revisionId,
		bool $revisionExists,
		?string $jsonText,
		?Content $content
	) {
		if ( $jsonText !== null ) {
			$content = new MessageBundleContent( $jsonText );
		}

		// Create the RevisionRecord mock
		$mockRevision = null;
		if ( $revisionExists ) {
			$mockRevision = $this->createMock( RevisionRecord::class );
			$mockRevision->method( 'getContent' )
				->willReturn( $content );
		}

		// Create the RevisionStore mock
		$mockRevisionStore = $this->createMock( RevisionStore::class );
		$mockRevisionStore->method( 'getRevisionById' )
			->willReturn( $mockRevision );

		// Temporarily replace the global service locator
		$this->setService( 'RevisionStore', $mockRevisionStore );

		return new MessageBundleMessageGroup(
			$messageBundleKey,
			'Test mb label',
			100,
			$revisionId,
			null,
			null
		);
	}
}
