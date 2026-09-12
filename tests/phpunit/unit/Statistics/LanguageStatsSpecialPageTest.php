<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use MediaWiki\Extension\Translate\MessageGroups\AggregateMessageGroup;
use MediaWiki\Extension\Translate\MessageGroups\WikiPageMessageGroup;
use MediaWikiUnitTestCase;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Statistics\LanguageStatsSpecialPage
 */
class LanguageStatsSpecialPageTest extends MediaWikiUnitTestCase {

	/**
	 * Verify that the CSS row class emitted for message groups is the short
	 * class name (no namespace separators), so that the JavaScript selector
	 * `tr.AggregateMessageGroup` continues to work after the namespace
	 * migration.
	 *
	 * @dataProvider provideGroupClasses
	 */
	public function testRowClassIsShortName( string $fqcn, string $expectedShortName ): void {
		$shortName = ( new \ReflectionClass( $fqcn ) )->getShortName();
		$this->assertSame( $expectedShortName, $shortName );
		$this->assertStringNotContainsString( '\\', $shortName,
			'Row class must not contain namespace separators' );
	}

	public static function provideGroupClasses(): iterable {
		yield 'AggregateMessageGroup' => [ AggregateMessageGroup::class, 'AggregateMessageGroup' ];
		yield 'WikiPageMessageGroup' => [ WikiPageMessageGroup::class, 'WikiPageMessageGroup' ];
	}
}
