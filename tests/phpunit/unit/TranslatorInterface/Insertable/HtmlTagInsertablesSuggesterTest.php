<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extension\Translate\TranslatorInterface\Insertable;

use MediaWikiUnitTestCase;

/** @covers \MediaWiki\Extension\Translate\TranslatorInterface\Insertable\HtmlTagInsertablesSuggester */
class HtmlTagInsertablesSuggesterTest extends MediaWikiUnitTestCase {
	/** @dataProvider getTestHtmlTagInsertablesSuggesterProvider */
	public function testHtmlTagInsertablesSuggester( $text, $expected, $comment = '' ) {
		$suggester = new HtmlTagInsertablesSuggester();
		$actual = $suggester->getInsertables( $text );

		$this->assertCount(
			count( $expected ),
			$actual,
			'should return correct number of insertables ' . $comment
		);

		foreach ( $expected as $i => $values ) {
			$this->assertEquals(
				$values['display'],
				$actual[$i]->getDisplayText(),
				'should return the correct display text ' . $comment
			);
			$this->assertEquals(
				$values['pre'],
				$actual[$i]->getPreText(),
				'should return the correct pre text ' . $comment
			);
			$this->assertEquals(
				$values['post'],
				$actual[$i]->getPostText(),
				'should return the correct post text ' . $comment
			);
		}
	}

	public function getTestHtmlTagInsertablesSuggesterProvider() {
		yield [
			'Hello <b>World</b>',
			[
				[ 'display' => '<b></b>', 'pre' => '<b>', 'post' => '</b>' ],
			],
			'for plain tag'
		];

		yield [
			'<html> <rocks>',
			[],
			'for unclosed tags'
		];

		yield [
			'Hello <b class="shaking">World</b>',
			[
				[ 'display' => '<b class="shaking"></b>', 'pre' => '<b class="shaking">', 'post'
				=> '</b>' ],
			],
			'for tag with attributes'
		];

		// TODO: support nested tags
		yield [
			'Hello <b><i>World</i></b>',
			[
				[ 'display' => '<b></b>', 'pre' => '<b>', 'post' => '</b>' ],
			],
			'for nested tags (currently not supported)'
		];

		// TODO: avoid duplicate insertables. Not a big issue as frontend de-duplicates
		yield [
			'Hello <b>a</b><i>b</i><b>c</b>',
			[
				[ 'display' => '<b></b>', 'pre' => '<b>', 'post' => '</b>' ],
				[ 'display' => '<i></i>', 'pre' => '<i>', 'post' => '</i>' ],
				[ 'display' => '<b></b>', 'pre' => '<b>', 'post' => '</b>' ],
			],
			'for multiple tags'
		];
	}
}
