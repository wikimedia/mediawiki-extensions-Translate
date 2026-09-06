<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\MessageLoading\FatMessage;
use MediaWiki\Extension\Translate\Validation\Validators\MediaWikiGenderValidator;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Validation\Validators\MediaWikiGenderValidator
 * @group Database
 */
class MediaWikiGenderValidatorTest extends MediaWikiIntegrationTestCase {

	private function getTestInstance(): MediaWikiGenderValidator {
		$services = $this->getServiceContainer();
		return new MediaWikiGenderValidator(
			$services->getLanguageFactory(),
			$services->getParserFactory(),
			$services->getUserFactory()
		);
	}

	private function getMessage( string $definition, string $translation ): FatMessage {
		$message = new FatMessage( 'key', $definition );
		$message->setTranslation( $translation );
		return $message;
	}

	/** @dataProvider provideGetGenderForms */
	public function testGetGenderForms( array $expected, string $translation, string $comment ): void {
		$validator = $this->getTestInstance();
		$this->assertSame( $expected, $validator->getGenderForms( $translation ), $comment );
	}

	public static function provideGetGenderForms(): iterable {
		yield [
			[ [ 'sir', 'madam' ] ],
			'Hello, {{GENDER:$1|sir|madam}}!',
			'two-form GENDER is parsed correctly',
		];

		yield [
			[ [ 'sir', 'madam', 'friend' ] ],
			'Hello, {{GENDER:$1|sir|madam|friend}}!',
			'three-form GENDER is parsed correctly',
		];

		yield [
			[ [ 'a', 'b' ], [ 'c', 'd' ] ],
			'{{GENDER:$1|a|b}} {{GENDER:$2|c|d}}',
			'two GENDER invocations are both parsed',
		];

		yield [
			[],
			'No gender here.',
			'string without GENDER returns empty array',
		];
	}

	/** @dataProvider provideIssues */
	public function testIssues(
		int $expectedIssueCount, string $translation, string $comment
	): void {
		$validator = $this->getTestInstance();
		$message = $this->getMessage( 'Hello, {{GENDER:$1|sir|madam}}!', $translation );
		$issues = $validator->getIssues( $message, 'en' );
		$this->assertCount( $expectedIssueCount, $issues, $comment );
	}

	public static function provideIssues(): iterable {
		yield [ 1, 'Hallo, {{GENDER:$1|vriend|vriend}}!', 'two equal forms triggers warning' ];
		yield [ 1, 'Hallo, {{GENDER:$1|meneer|mevrouw|meneer}}!',
			'three forms with first equal to third triggers warning' ];
		yield [ 0, 'Hallo, {{GENDER:$1|meneer|mevrouw}}!', 'two different forms produces no warning' ];
		yield [ 0, 'Hallo, {{GENDER:$1|meneer|mevrouw|vriend}}!', 'three all-different forms produces no warning' ];
		yield [ 0, 'Hallo, {{GENDER:$1|meneer|mevrouw|mevrouw}}!',
			'second equal to third is not redundant' ];
		yield [ 0, 'Hallo vriend!', 'no GENDER produces no warning' ];
	}
}
