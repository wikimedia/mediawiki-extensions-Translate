<?php
/**
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

class RegexInsertableSuggesterTest extends MediaWikiTestCase {
	/**
	 * @dataProvider getTestRegexInsertableProvider
	 */
	public function testRegexInsertable( $text, $params, $expectedVals ) {
		$insertableSuggester = new RegexInsertableSuggester( $params );
		$insertables = $insertableSuggester->getInsertables( $text );

		$this->assertCount( count( $expectedVals ), $insertables,
			'should return all the expected insertables.' );

		foreach ( $expectedVals as $i => $values ) {
			$this->assertEquals( $values['display'], $insertables[$i]->getDisplayText(),
				'should return the correct display text.' );
			$this->assertEquals( $values['pre'], $insertables[$i]->getPreText(),
				'should return the correct pre text.' );
			$this->assertEquals( $values['post'], $insertables[$i]->getPostText(),
				'should return the correct post text.' );
		}
	}

	public function getTestRegexInsertableProvider() {
		return [
			[
				'Hello $name! I\'m $myname',
				[
					'regex' => '/\$[a-z0-9]+/'
				],
				[
					[ 'display' => '$name', 'pre' => '$name', 'post' => '' ],
					[ 'display' => '$myname', 'pre' => '$myname', 'post' => '' ],
				]
			],
			[
				'<html> <rocks>',
				[
					'regex' => '/(?<display>\<[a-z]+>)/',
					'display' => '$display',
				],
				[
					[ 'display' => '<html>', 'pre' => '<html>', 'post' => '' ],
					[ 'display' => '<rocks>', 'pre' => '<rocks>', 'post' => '' ]
				]
			],
			[
				'[Hello]($1) [World]($2)',
				[
					'regex' => '/(?<pre>\[)[^]]+(?<post>\]\([^)]+\))/',
					'display' => '$pre$post',
					'pre' => '$pre',
					'post' => '$post'
				],
				[
					[ 'display' => '[]($1)', 'pre' => '[', 'post' => ']($1)' ],
					[ 'display' => '[]($2)', 'pre' => '[', 'post' => ']($2)' ]
				]
			]
		];
	}
}
