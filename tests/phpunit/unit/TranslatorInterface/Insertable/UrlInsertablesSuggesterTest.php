<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Insertable;

use MediaWikiUnitTestCase;

/**
 * @author Niklas Laxström
 * @author Jon Harald Søby
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\TranslatorInterface\Insertable\UrlInsertablesSuggester
 */
class UrlInsertablesSuggesterTest extends MediaWikiUnitTestCase {
	/** @dataProvider getTestUrlInsertablesSuggesterProvider */
	public function testUrlInsertablesSuggester( $text, $expected, $comment = '' ) {
		$suggester = new UrlInsertablesSuggester();
		$actual = $suggester->getInsertables( $text );

		$this->assertSameSize(
			$expected,
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

	public static function getTestUrlInsertablesSuggesterProvider() {
		yield [
			'Visit http://wikipedia.org',
			[
				[
					'display' => 'http://wikipedia.org/',
					'pre' => 'http://wikipedia.org',
					'post' => ''
				],
			],
			'for simple domain URL'
		];

		yield [
			'See the [https://www.mediawiki.org/wiki/Special:MyLanguage/Help:API documentation page]',
			[
				[
					'display' => 'https://www.mediawiki.org/',
					'pre' => 'https://www.mediawiki.org/wiki/Special:MyLanguage/Help:API',
					'post' => ''
				],
			],
			'for URLs with slugs'
		];

		yield [
			'[//en.wikipedia.org/w/index.php?title=Special:MyTalk&action=edit&section=new Add new section]',
			[
				[
					'display' => '//en.wikipedia.org/',
					'pre' => '//en.wikipedia.org/w/index.php?title=Special:MyTalk&action=edit&section=new',
					'post' => ''
				],
			],
			'for URLs with parameters'
		];

		yield [
			'https://w.wiki/4nCc and https://www.mediawiki.org/wiki/Localisation are the same',
			[
				[
					'display' => 'https://w.wiki/',
					'pre' => 'https://w.wiki/4nCc',
					'post' => ''
				],
				[
					'display' => 'https://www.mediawiki.org/',
					'pre' => 'https://www.mediawiki.org/wiki/Localisation',
					'post' => ''
				]
			],
			'for multiple URLs'
		];
	}
}
