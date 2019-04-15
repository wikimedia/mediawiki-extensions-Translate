<?php
/**
 * Test for general validators.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extensions\Translate\MessageValidator\Validators\BraceBalanceValidator;
use MediaWiki\Extensions\Translate\MessageValidator\Validators\InsertableRubyVariableValidator;
use MediaWiki\Extensions\Translate\MessageValidator\Validators\InsertableRegexValidator;

/**
 * @group TranslationValidators
 */
class TranslateValidatorTest extends PHPUnit\Framework\TestCase {

	/**
	 * @dataProvider getBraceBalanceValidatorProvider
	 */
	public function testBraceBalanceValidator( $key, $definition, $translation,
		$expected, $msg ) {
		$validator = new BraceBalanceValidator();
		$notices = [];
		$message = new FatMessage( $key, $definition );
		$message->setTranslation( $translation );
		$validator->validate( [ $message ], 'en-gb', $notices );
		if ( $expected ) {
			$this->assertCount( $expected, $notices[ $key ], $msg );
		} else {
			$this->assertCount( $expected, $notices, $msg );
		}
	}

	/**
	 * @dataProvider getInsertableRubyValidatorProvider
	 */
	public function testInsertableRubyValidator( $key, $definition,
		$translation, $expected, $msg ) {
		$validator = new InsertableRubyVariableValidator();

		$notices = [];
		$message = new FatMessage( $key, $definition );
		$message->setTranslation( $translation );
		$validator->validate( [ $message ], 'en-gb', $notices );
		if ( $expected ) {
			$this->assertCount( $expected, $notices[ $key ], $msg );
		} else {
			$this->assertCount( $expected, $notices, $msg );
		}
	}

	/**
	 * @dataProvider getInsertableRegexValidatorProvider
	 */
	public function testInsertableRegexValidator( $params, $key, $definition,
		$translation, $expected, $subchecks, $msg ) {
		$validator = new InsertableRegexValidator( $params );
		$notices = [];
		$message = new FatMessage( $key, $definition );
		$message->setTranslation( $translation );
		$validator->validate( [ $message ], 'en-gb', $notices );
		if ( $expected ) {
			$this->assertCount( $expected, $notices[ $key ], $msg );
			foreach ( $subchecks as $i => $subcheck ) {
				$this->assertEquals( $notices[ $key ][ $i ][ 0 ][ 1 ], $subcheck,
					"subcheck $subcheck matches." );
			}
		} else {
			$this->assertCount( $expected, $notices, $msg );
		}
	}

	public function getBraceBalanceValidatorProvider() {
		yield [
			'hello', '{{ Hello }}',
			'{{ Hello }}}', 1,
			'should return a notice for a message containing non-matching braces.'
		];

		yield [
			'hello2', '[[ Hello ]]',
			'[[ Hello ]]', 0,
			'should not set any notice for a valid message.'
		];
	}

	public function getInsertableRubyValidatorProvider() {
		yield [
			'hello', 'Test variable - %{ruby} %{ruby2}',
			'%{hello} - Testing translation',
			2, 'should return proper notices for missing and non-matching variables.'
		];

		yield [
				'hello2', 'Testing variables - %{ruby} %{php}',
				'Another testing - %{ruby} %{ruby2}',
				1, 'should see a notice set when parameter names don\'t match.'
		];

		yield [
				'hello3', 'Testing variables - %{ruby} %{php}',
				'Another testing - %{ruby} %{php}',
				0, 'should not set any notice for a valid message.'
		];
	}

	public function getInsertableRegexValidatorProvider() {
		yield [
			'/\$[a-z0-9]+/', 'msgkey',
			'$contacts is $diff less than that.',
			'$contacts2 as $diff2 less.', 2,
			[ 'missing', 'unknown' ],
			'should correctly identifiy the missing and unknown parameters.'
		];

		yield [
			[ 'regex' => '/\$[a-z0-9]+/' ], 'msgkey2',
			'$contacts is $diff less than that.',
			'$contacts less.', 1,
			[ 'missing' ],
			'should correctly identifiy the missing parameters.'
		];

		yield [
			'/<[a-z]+>/', 'msgkey3',
			'<hello> <world>',
			'<hello> <world> <msg>', 1,
			[ 'unknown' ],
			'should correctly identify the unknown parameters.'
		];
	}
}
