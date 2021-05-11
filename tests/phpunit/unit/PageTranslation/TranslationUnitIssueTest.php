<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use InvalidArgumentException;
use MediaWikiUnitTestCase;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\PageTranslation\TranslationUnitIssue
 */
class TranslationUnitIssueTest extends MediaWikiUnitTestCase {
	public function testConstructor() {
		$actual = new TranslationUnitIssue( TranslationUnitIssue::WARNING, 'key', [ 'param1' ] );
		$this->assertInstanceOf( TranslationUnitIssue::class, $actual );

		$this->expectException( InvalidArgumentException::class );
		new TranslationUnitIssue( 'essay', 'key' );
	}

	public function testGetSeverity() {
		$expected = TranslationUnitIssue::WARNING;
		$issue = new TranslationUnitIssue( $expected, 'key' );
		$this->assertEquals( $expected, $issue->getSeverity() );
	}

	public function testGetKey() {
		$expected = 'key';
		$issue = new TranslationUnitIssue( TranslationUnitIssue::ERROR, $expected );
		$this->assertEquals( $expected, $issue->getKey() );
	}

	public function testGetParams() {
		$expected = [];
		$issue = new TranslationUnitIssue( TranslationUnitIssue::ERROR, 'key' );
		$this->assertEquals( $expected, $issue->getParams(), 'default value' );

		$expected = [ 'param1' ];
		$issue = new TranslationUnitIssue( TranslationUnitIssue::ERROR, 'key', $expected );
		$this->assertEquals( $expected, $issue->getParams() );
	}
}
