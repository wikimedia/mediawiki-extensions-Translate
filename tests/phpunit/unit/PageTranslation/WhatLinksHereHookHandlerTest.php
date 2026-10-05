<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Tests\Unit\PageTranslation;

use MediaWiki\Extension\Translate\PageTranslation\WhatLinksHereHookHandler;
use MediaWiki\Specials\Hook\SpecialWhatLinksHereBacklinkQueryData;
use MediaWiki\Specials\Hook\SpecialWhatLinksHereBacklinkQueryHook;
use MediaWikiUnitTestCase;
use Wikimedia\Rdbms\Expression;
use Wikimedia\Rdbms\IReadableDatabase;
use Wikimedia\Rdbms\RawSQLValue;
use Wikimedia\Rdbms\SelectQueryBuilder;

/** @covers \MediaWiki\Extension\Translate\PageTranslation\WhatLinksHereHookHandler */
class WhatLinksHereHookHandlerTest extends MediaWikiUnitTestCase {

	protected function setUp(): void {
		parent::setUp();
		// TODO: Remove once MW 1.48 is the minimum
		if ( !interface_exists( SpecialWhatLinksHereBacklinkQueryHook::class ) ) {
			$this->markTestSkipped( 'Requires MW 1.48' );
		}
	}

	public function testWhatLinksHereFilterUsesSourcePageIdField(): void {
		$expression = $this->createMock( Expression::class );
		$db = $this->createMock( IReadableDatabase::class );
		$db->expects( $this->once() )
			->method( 'expr' )
			->with(
				'translate_pp.pp_page',
				'=',
				new RawSQLValue( 'pl_from' )
			)
			->willReturn( $expression );

		$queryBuilder = $this->createMock( SelectQueryBuilder::class );
		$queryBuilder->expects( $this->once() )
			->method( 'leftJoin' )
			->with(
				'page_props',
				'translate_pp',
				[
					$expression,
					'translate_pp.pp_propname' => 'translate-is-translation',
				]
			)
			->willReturnSelf();
		$queryBuilder->expects( $this->once() )
			->method( 'andWhere' )
			->with( [ 'translate_pp.pp_value' => null ] )
			->willReturnSelf();

		( new WhatLinksHereHookHandler() )->onSpecialWhatLinksHereBacklinkQuery(
			new SpecialWhatLinksHereBacklinkQueryData(
				$db, 'pagelinks', 'pl_from', [ 'translate-hidetranslations' => true ]
			),
			$queryBuilder
		);
	}

	public function testWhatLinksHereFilterIsOptional(): void {
		$db = $this->createMock( IReadableDatabase::class );
		$db->expects( $this->never() )->method( 'expr' );
		$queryBuilder = $this->createMock( SelectQueryBuilder::class );
		$queryBuilder->expects( $this->never() )->method( 'leftJoin' );
		$queryBuilder->expects( $this->never() )->method( 'andWhere' );

		( new WhatLinksHereHookHandler() )->onSpecialWhatLinksHereBacklinkQuery(
			new SpecialWhatLinksHereBacklinkQueryData(
				$db, 'pagelinks', 'pl_from', [ 'translate-hidetranslations' => false ]
			),
			$queryBuilder
		);
	}
}
