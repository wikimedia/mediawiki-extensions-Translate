<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

declare( strict_types = 1 );

use MediaWiki\Extensions\Translate\MessageValidator\Validators\EscapeCharacterValidator;
use MediaWiki\Extensions\Translate\Validation\ValidationIssue;

/** @covers \MediaWiki\Extensions\Translate\MessageValidator\Validators\EscapeCharacterValidator */
class EscapeCharacterValidatorTest extends MediaWikiUnitTestCase {
	public static function provideValidate() {
		$message = new FatMessage( 'key', 'Hello' );

		yield [
			'Hello\n',
			[ 'values' => [ '\n', '\\\\' ] ],
			$message,
			null
		];

		yield [
			'Hello\n',
			[ 'values' => [ '\b' ] ],
			$message,
			[ 'type' => 'escape', 'subtype' => 'invalid' ]
		];

		yield [
			'Hello \b',
			[ 'values' => [ '\\\\', '\n' ] ],
			$message,
			[ 'type' => 'escape', 'subtype' => 'invalid' ]
		];

		yield [
			'Hello\\\\',
			[ 'values' => [ '\\\\', '\n' ] ],
			$message,
			null
		];
	}

	/** @dataProvider provideValidate */
	public function testValidate(
		string $translation, array $config, FatMessage $message, array $expected = null
	) {
		$message->setTranslation( $translation );
		$validator = new EscapeCharacterValidator( $config );

		$issues = $validator->getIssues( $message, 'en-gb' );

		if ( $expected === null ) {
			$this->assertCount( 0, $issues, 'no issues' );
		} else {
			$this->assertCount( 1, $issues, 'issue raised' );
			/** @var ValidationIssue $issue */
			$issue = $issues->getIterator()->current();
			$this->assertSame( $expected['type'], $issue->type(), 'issue has correct type' );
			$this->assertSame( $expected['subtype'], $issue->subType(), 'issue has correct subtype' );
		}
	}

	public function testEmptyValues() {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessageMatches( '/no values provided/i' );
		new EscapeCharacterValidator( [
			'values' => [],
		] );
	}

	public function testInvalidEscape() {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessageMatches( '/invalid escape character/i' );
		new EscapeCharacterValidator( [
			'values' => [ '\c' ],
		] );
	}
}
