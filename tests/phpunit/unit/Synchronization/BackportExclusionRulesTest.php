<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use InvalidArgumentException;
use MediaWikiUnitTestCase;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Synchronization\BackportExclusionRules
 */
class BackportExclusionRulesTest extends MediaWikiUnitTestCase {
	private const RULES = [
		'branch' => [ 'branches' => [ 'REL1_43', 'REL1_45' ] ],
		'language' => [ 'languages' => [ 'fi', 'sv' ] ],
		'content' => [ 'content' => '/\{\{PLURAL:/i' ],
		'all' => [
			'branches' => [ 'REL1_46' ],
			'languages' => [ 'ru' ],
			'content' => '/#FORMAL:/',
		],
	];

	/** @dataProvider provideGetMatchingRule */
	public function testGetMatchingRule(
		array $rules,
		?string $branch,
		string $language,
		string $translation,
		?string $expected
	): void {
		$contentPatterns = ( new BackportExclusionRules( $rules ) )->getContentPatterns( $branch, $language );
		$this->assertSame( $expected, BackportExclusionRules::getMatchingRule( $contentPatterns, $translation ) );
	}

	public function testGetContentPatterns(): void {
		$exclusionRules = new BackportExclusionRules( self::RULES );
		$this->assertSame(
			[ 'branch' => null, 'language' => null, 'content' => '/\{\{PLURAL:/i' ],
			$exclusionRules->getContentPatterns( 'REL1_43', 'fi' )
		);
		$this->assertSame(
			[ 'content' => '/\{\{PLURAL:/i', 'all' => '/#FORMAL:/' ],
			$exclusionRules->getContentPatterns( 'REL1_46', 'ru' )
		);
	}

	public static function provideGetMatchingRule(): iterable {
		$branch = [ 'branch' => self::RULES['branch'] ];
		$language = [ 'language' => self::RULES['language'] ];
		$content = [ 'content' => self::RULES['content'] ];
		$all = [ 'all' => self::RULES['all'] ];

		yield 'no rules' => [ [], 'REL1_43', 'fi', 'Text', null ];
		yield 'branch rule, first branch' => [ $branch, 'REL1_43', 'de', 'Text', 'branch' ];
		yield 'branch rule, second branch' => [ $branch, 'REL1_45', 'de', 'Text', 'branch' ];
		yield 'branch rule, other branch' => [ $branch, 'REL1_46', 'de', 'Text', null ];
		yield 'branch rule, no branch given' => [ $branch, null, 'de', 'Text', null ];
		yield 'branch rule, branch must match exactly' => [ $branch, 'rel1_43', 'de', 'Text', null ];
		yield 'language rule, matching language' => [ $language, 'REL1_43', 'sv', 'Text', 'language' ];
		yield 'language rule, other language' => [ $language, 'REL1_43', 'de', 'Text', null ];
		yield 'language rule, no branch given' => [ $language, null, 'fi', 'Text', 'language' ];
		yield 'content rule, matching content' => [ $content, 'REL1_43', 'de', '{{plural:$1|a|b}}', 'content' ];
		yield 'content rule, other content' => [ $content, 'REL1_43', 'de', 'Text', null ];
		yield 'all criteria match' => [ $all, 'REL1_46', 'ru', '{{#FORMAL:a|b}}', 'all' ];
		yield 'all criteria, branch does not match' => [ $all, 'REL1_43', 'ru', '{{#FORMAL:a|b}}', null ];
		yield 'all criteria, language does not match' => [ $all, 'REL1_46', 'fi', '{{#FORMAL:a|b}}', null ];
		yield 'all criteria, content does not match' => [ $all, 'REL1_46', 'ru', 'Text', null ];
		yield 'rule with no criteria' => [ [ 'everything' => [] ], 'REL1_43', 'fi', 'Text', 'everything' ];
		yield 'several rules, one matches' => [ self::RULES, 'REL1_46', 'de', '{{PLURAL:$1|a|b}}', 'content' ];
		yield 'several rules, first match is returned' => [ self::RULES, 'REL1_43', 'fi', 'Text', 'branch' ];
		yield 'several rules, none match' => [ self::RULES, 'REL1_46', 'de', 'Text', null ];
		yield 'content rule, regex failure counts as a match' => [
			[ 'utf8' => [ 'content' => '/x/u' ] ], 'REL1_43', 'de', "\xff", 'utf8'
		];
	}

	public function testHasBranchRules(): void {
		$this->assertFalse( ( new BackportExclusionRules( [] ) )->hasBranchRules() );
		$this->assertFalse(
			( new BackportExclusionRules( [ 'language' => self::RULES['language'] ] ) )->hasBranchRules()
		);
		$this->assertTrue( ( new BackportExclusionRules( self::RULES ) )->hasBranchRules() );
	}

	/** @dataProvider provideInvalidRules */
	public function testInvalidRules( array $rules, string $expectedMessage ): void {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( $expectedMessage );
		new BackportExclusionRules( $rules );
	}

	public static function provideInvalidRules(): iterable {
		yield 'rule without a name' => [ [ [ 'languages' => [ 'fi' ] ] ], 'must have a name' ];
		yield 'rule is not an array' => [ [ 'rule' => 'fi' ], 'must be an array' ];
		yield 'unknown key' => [ [ 'rule' => [ 'language' => [ 'fi' ] ] ], 'unknown keys: language' ];
		yield 'branches is not a list' => [ [ 'rule' => [ 'branches' => 'REL1_43' ] ], "'branches' must be" ];
		yield 'branches is empty' => [ [ 'rule' => [ 'branches' => [] ] ], "'branches' must be" ];
		yield 'branches is null' => [ [ 'rule' => [ 'branches' => null ] ], "'branches' must be" ];
		yield 'languages has a non-string value' => [ [ 'rule' => [ 'languages' => [ 1 ] ] ], "'languages' must be" ];
		yield 'languages is not a list' => [
			[ 'rule' => [ 'languages' => [ 'a' => 'fi' ] ] ],
			"'languages' must be"
		];
		yield 'content is not a string' => [ [ 'rule' => [ 'content' => [ '/a/' ] ] ], "'content' must be" ];
		yield 'content is an invalid regex' => [ [ 'rule' => [ 'content' => '/[/' ] ], "'content' must be" ];
		yield 'content has no delimiters' => [ [ 'rule' => [ 'content' => 'PLURAL' ] ], "'content' must be" ];
	}
}
