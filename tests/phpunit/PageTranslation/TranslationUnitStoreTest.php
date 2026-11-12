<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWikiIntegrationTestCase;

/**
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @since 2026.09
 * @covers \MediaWiki\Extension\Translate\PageTranslation\TranslationUnitStore
 * @covers \MediaWiki\Extension\Translate\PageTranslation\WritableTranslationUnitStore
 * @group Database
 */
class TranslationUnitStoreTest extends MediaWikiIntegrationTestCase {

	private WritableTranslationUnitStore $store;
	private WritableTranslationUnitStore $storeForUnknownPage;
	private int $pageId;

	protected function setUp(): void {
		parent::setUp();
		$db = $this->getServiceContainer()->getConnectionProvider()->getPrimaryDatabase();
		$this->pageId = $this->insertPage( 'TranslationUnitStoreTest' )['id'];
		$this->store = new WritableTranslationUnitStore( $db, $this->pageId );
		$this->storeForUnknownPage = new WritableTranslationUnitStore( $db, 0 );
	}

	public function testGetUnitsReturnsEmptyArrayForUnknownPage(): void {
		$this->assertSame( [], $this->storeForUnknownPage->getUnits() );
	}

	public function testGetNamesReturnsUnitIds(): void {
		$this->store->store( [
			new TranslationUnit( 'Alpha', 'a' ),
			new TranslationUnit( 'Beta', 'b' ),
		] );
		$this->assertArrayEquals( [ 'a', 'b' ], $this->store->getNames() );
	}

	public function testStoreWithEmptyArrayLeavesNoUnits(): void {
		// Seed a unit first so the subsequent empty store() has something to delete.
		$this->store->store( [ new TranslationUnit( 'Some text', '1' ) ] );
		$this->store->store( [] );
		$this->assertSame( [], $this->store->getUnits() );
	}

	public function testStoreReplacesUnitsAndPreservesOrder(): void {
		$this->store->store( [
			new TranslationUnit( 'Old text', 'a' ),
			new TranslationUnit( 'Removed text', 'c' ),
		] );
		$this->store->store( [
			'b' => new TranslationUnit( 'Beta', 'b' ),
			'a' => new TranslationUnit( 'Updated text', 'a' ),
		] );

		$units = $this->store->getUnits();
		$this->assertCount( 2, $units );
		$this->assertSame( 'b', $units['b']->id );
		$this->assertSame( 'Beta', $units['b']->getText() );
		$this->assertSame( 'a', $units['a']->id );
		$this->assertSame( 'Updated text', $units['a']->getText() );

		$db = $this->getServiceContainer()->getConnectionProvider()->getPrimaryDatabase();
		$rows = $db->newSelectQueryBuilder()
			->select( [ 'trs_key', 'trs_order' ] )
			->from( 'translate_sections' )
			->where( [ 'trs_page' => $this->pageId ] )
			->orderBy( 'trs_order' )
			->caller( __METHOD__ )
			->fetchResultSet();
		$orders = [];
		foreach ( $rows as $row ) {
			$orders[$row->trs_key] = (int)$row->trs_order;
		}
		$this->assertSame( [ 'b' => 0, 'a' => 1 ], $orders );
	}

	public function testStoreAndDeleteOnlyAffectTargetPage(): void {
		$db = $this->getServiceContainer()->getConnectionProvider()->getPrimaryDatabase();
		$otherPageId = $this->insertPage( 'OtherTranslationUnitStoreTest' )['id'];
		$otherStore = new WritableTranslationUnitStore( $db, $otherPageId );
		$otherStore->store( [ new TranslationUnit( 'Other page text', '1' ) ] );
		$this->store->store( [ new TranslationUnit( 'Original text', '1' ) ] );
		$this->store->store( [ new TranslationUnit( 'Replacement text', '2' ) ] );
		$this->assertSame( 'Other page text', $otherStore->getUnits()['1']->getText() );

		$this->store->delete();
		$this->assertSame( [], $this->store->getUnits() );
		$this->assertSame( [ '1' ], $otherStore->getNames() );
		$this->assertSame( 'Other page text', $otherStore->getUnits()['1']->getText() );
	}
}
