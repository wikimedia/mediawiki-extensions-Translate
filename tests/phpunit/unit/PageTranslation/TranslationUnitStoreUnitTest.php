<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWikiUnitTestCase;
use Wikimedia\Rdbms\IReadableDatabase;
use Wikimedia\Rdbms\IResultWrapper;
use Wikimedia\Rdbms\SelectQueryBuilder;

/**
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\PageTranslation\TranslationUnitStore
 */
class TranslationUnitStoreUnitTest extends MediaWikiUnitTestCase {

	public function testGetUnitsReturnsKeyedTranslationUnits(): void {
		$row1 = (object)[ 'trs_key' => 'a', 'trs_text' => 'Alpha' ];
		$row2 = (object)[ 'trs_key' => 'b', 'trs_text' => 'Beta' ];

		$resultWrapper = $this->createMock( IResultWrapper::class );
		$resultWrapper->method( 'valid' )->willReturnOnConsecutiveCalls( true, true, false );
		$resultWrapper->method( 'current' )->willReturnOnConsecutiveCalls( $row1, $row2 );

		$selectBuilder = $this->createMock( SelectQueryBuilder::class );
		$selectBuilder->method( 'select' )->willReturnSelf();
		$selectBuilder->method( 'from' )->willReturnSelf();
		$selectBuilder->method( 'where' )->willReturnSelf();
		$selectBuilder->method( 'caller' )->willReturnSelf();
		$selectBuilder->method( 'fetchResultSet' )->willReturn( $resultWrapper );

		$db = $this->createMock( IReadableDatabase::class );
		$db->method( 'newSelectQueryBuilder' )->willReturn( $selectBuilder );

		$units = ( new TranslationUnitStore( $db, 1 ) )->getUnits();

		$this->assertArrayHasKey( 'a', $units );
		$this->assertArrayHasKey( 'b', $units );
		$this->assertInstanceOf( TranslationUnit::class, $units['a'] );
		$this->assertSame( 'Alpha', $units['a']->getText() );
		$this->assertSame( 'Beta', $units['b']->getText() );
	}

	public function testGetUnitsReturnsEmptyArrayWhenNoRows(): void {
		$resultWrapper = $this->createMock( IResultWrapper::class );
		$resultWrapper->method( 'valid' )->willReturn( false );

		$selectBuilder = $this->createMock( SelectQueryBuilder::class );
		$selectBuilder->method( 'select' )->willReturnSelf();
		$selectBuilder->method( 'from' )->willReturnSelf();
		$selectBuilder->method( 'where' )->willReturnSelf();
		$selectBuilder->method( 'caller' )->willReturnSelf();
		$selectBuilder->method( 'fetchResultSet' )->willReturn( $resultWrapper );

		$db = $this->createMock( IReadableDatabase::class );
		$db->method( 'newSelectQueryBuilder' )->willReturn( $selectBuilder );

		$this->assertSame( [], ( new TranslationUnitStore( $db, 1 ) )->getUnits() );
	}

	public function testGetNamesReturnsKeys(): void {
		$selectBuilder = $this->createMock( SelectQueryBuilder::class );
		$selectBuilder->method( 'select' )->willReturnSelf();
		$selectBuilder->method( 'from' )->willReturnSelf();
		$selectBuilder->method( 'where' )->willReturnSelf();
		$selectBuilder->method( 'caller' )->willReturnSelf();
		$selectBuilder->method( 'fetchFieldValues' )->willReturn( [ 'a', 'b' ] );

		$db = $this->createMock( IReadableDatabase::class );
		$db->method( 'newSelectQueryBuilder' )->willReturn( $selectBuilder );

		$this->assertSame( [ 'a', 'b' ], ( new TranslationUnitStore( $db, 1 ) )->getNames() );
	}
}
