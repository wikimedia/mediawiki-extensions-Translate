<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\MessageBundleTranslation\MalformedBundle;
use MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundleContent;
use MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundleMetadata;

/** @coversDefaultClass \MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundleContent */
class MessageBundleContentTest extends MediaWikiUnitTestCase {
	/**
	 * @dataProvider provideJsonStructures
	 * @dataProvider provideValidJsonStructure
	 * @covers ::isValid
	 */
	public function testIsValid( string $json, bool $isValid ): void {
		$content = new MessageBundleContent( $json );
		$this->assertEquals( $isValid, $content->isValid() );
	}

	/**
	 * @dataProvider provideJsonStructures
	 * @covers ::validate
	 */
	public function testValidate( string $json, bool $isValid, ?string $exceptionMessage ): void {
		$content = new MessageBundleContent( $json );

		if ( $exceptionMessage ) {
			$this->expectException( MalformedBundle::class );
			$this->expectExceptionMessageMatches( '/' . $exceptionMessage . '/i' );
		}
		$content->validate();
	}

	public static function provideJsonStructures() {
		// Message validation
		yield [
			json_encode( [
				'@invalid' => 'hello',
				'@metadata' => [
					'priorityLanguages' => []
				],
				'k1' => 'a',
				'k2' => 'b'
			] ),
			false,
			'key-invalid-characters'
		];

		yield [
			json_encode( [
				'k1' => [],
				'k2' => 'b'
			] ),
			false,
			'error-invalid-value'
		];

		yield [
			json_encode( [
				'' => 'a',
				'k2' => 'b'
			] ),
			false,
			'error-key-empty'
		];

		yield [
			json_encode( [
				'k1' => '',
				'k2' => 'b'
			] ),
			false,
			'error-empty-value'
		];

		$data = [ 'k2' => 'b' ];
		$data[ str_repeat( 'abcdef', 20 ) ] = 'a';
		yield [ json_encode( $data ), false, 'key-too-long' ];

		// Metadata validation
		yield [
			json_encode(
				[
					'@metadata' => 'hello',
					'k1' => 'a',
					'k2' => 'b'
				]
			),
			false,
			'error-metadata-type'
		];

		yield [
			json_encode( [
				'@metadata' => [
					'invalid' => true
				],
				'k1' => 'a',
				'k2' => 'b',

			] ),
			false,
			'error-invalid-metadata'
		];

		yield [
			json_encode( [
				'@metadata' => [
					'sourceLanguage' => true
				],
				'k1' => 'a',
				'k2' => 'b',

			] ),
			false,
			'error-invalid-sourcelanguage'
		];

		yield [
			json_encode( [
				'@metadata' => [
					'priorityLanguages' => true
				],
				'k1' => 'a',
				'k2' => 'b',

			] ),
			false,
			'error-invalid-prioritylanguage'
		];

		yield [
			json_encode( [
				'@metadata' => [
					'description' => true
				],
				'k1' => 'a',
				'k2' => 'b',
			] ),
			false,
			'translate-messagebundle-error-invalid-description'
		];
	}

	public static function provideValidJsonStructure() {
		// Valid value
		yield [
			json_encode( [
				'@metadata' => [
					'sourceLanguage' => 'fr',
					'priorityLanguages' => [ 'en', 'es' ],
					'description' => 'Hello World!',
					'label' => 'Hello!'
				],
				'k1' => 'a',
				'k2' => 'b',
			] ),
			true
		];
	}

	/** @covers ::getMessages */
	public function testGetMessages(): void {
		$json = json_encode(
			[
				'@metadata' => [
					'sourceLanguage' => 'fr',
					'priorityLanguages' => [ 'en', 'es' ]
				],
				'k1' => 'a',
				'k2' => 'b',
			]
		);

		$content = new MessageBundleContent( $json );

		$this->assertArrayEquals(
			[
				'k1' => 'a',
				'k2' => 'b'
			],
			$content->getMessages()
		);
	}

	/**
	 * @dataProvider provideGetMetadata
	 * @covers ::getMetadata
	 */
	public function testGetMetadata(
		string $json,
		?string $sourceLanguageCode,
		?array $priorityLanguageCodes,
		bool $allowPriorityLanguagesOnly
	): void {
		$content = new MessageBundleContent( $json );
		$metadata = $content->getMetadata();

		$this->assertInstanceOf( MessageBundleMetadata::class, $metadata );
		$this->assertEquals( $sourceLanguageCode, $metadata->getSourceLanguageCode() );
		$this->assertEquals( $priorityLanguageCodes, $metadata->getPriorityLanguages() );
		$this->assertEquals( $allowPriorityLanguagesOnly, $metadata->areOnlyPriorityLanguagesAllowed() );
	}

	public static function provideGetMetadata() {
		yield [
			json_encode(
				[
					'@metadata' => [],
					'k1' => '1',
					'k2' => '2'
				]
			),
			null,
			null,
			false
		];

		yield [
			json_encode(
				[
					'@metadata' => [
						'sourceLanguage' => 'fr',
						'priorityLanguages' => [ 'en', 'es' ],
						'allowOnlyPriorityLanguages' => true
					],
					'k1' => '1',
					'k2' => '2'
				]
			),
			'fr',
			[ 'en', 'es' ],
			true
		];
	}

}
