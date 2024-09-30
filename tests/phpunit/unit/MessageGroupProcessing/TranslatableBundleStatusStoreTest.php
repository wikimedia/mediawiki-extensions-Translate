<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use Collation;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePage;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageStatus;
use MediaWiki\Title\Title;
use MediaWikiUnitTestCase;
use Wikimedia\Rdbms\Database;
use Wikimedia\Rdbms\IMaintainableDatabase;
use Wikimedia\Rdbms\ReplaceQueryBuilder;

/**
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleStatusStore
 */
class TranslatableBundleStatusStoreTest extends MediaWikiUnitTestCase {
	public function testSetStatus() {
		$prefixedDbKey = 'TitleKey';
		$db = $this->getDatabaseMock();
		$collation = $this->getCollationMock( $prefixedDbKey );
		$dbMaintenance = $this->getMaintenanceDatabaseMock();
		$title = $this->getTitleMock( $prefixedDbKey );
		$status = new TranslatablePageStatus( TranslatablePageStatus::ACTIVE );

		$bundleStatusStore = new TranslatableBundleStatusStore( $db, $collation, $dbMaintenance );
		$bundleStatusStore->setStatus( $title, $status, TranslatablePage::class );
	}

	private function getDatabaseMock() {
		// Set status should replace the record if it exists, or insert
		$mock = $this->createMock( Database::class );
		$rqb = $this->createMock( ReplaceQueryBuilder::class );
		$rqb->method( $this->logicalOr( 'replaceInto', 'uniqueIndexFields', 'row', 'caller' ) )->willReturnSelf();
		$mock->expects( $this->once() )
			->method( 'newReplaceQueryBuilder' )
			->willReturn( $rqb );

		return $mock;
	}

	private function getTitleMock( string $prefixedDbKey ) {
		// Title::getPrefixedDBKey should be called to perform sorting
		$mock = $this->createMock( Title::class );
		$mock->expects( $this->once() )
			->method( 'getPrefixedDBkey' )
			->willReturn( $prefixedDbKey );

		$mock->method( 'getArticleID' )
			->willReturn( 1 );

		return $mock;
	}

	private function getCollationMock( string $titlePrefixedDbKey ) {
		// Collation::getSortKey should be used to sort the records
		$mock = $this->createMock( Collation::class );
		$mock->expects( $this->once() )
			->method( 'getSortKey' )
			->with( $titlePrefixedDbKey )
			->willReturn( $titlePrefixedDbKey );

		return $mock;
	}

	private function getMaintenanceDatabaseMock() {
		$mock = $this->createMock( IMaintainableDatabase::class );
		$mock->expects( $this->once() )
			->method( 'tableExists' )
			->willReturn( true );

		return $mock;
	}
}
