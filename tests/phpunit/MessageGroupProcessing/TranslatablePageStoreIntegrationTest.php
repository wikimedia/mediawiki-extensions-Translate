<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Extension\Translate\PageTranslation\TranslationUnit;
use MediaWiki\Extension\Translate\PageTranslation\WritableTranslationUnitStore;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;

/**
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @since 2026.09
 * @covers \MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatablePageStore
 * @group Database
 */
class TranslatablePageStoreIntegrationTest extends MediaWikiIntegrationTestCase {

	public function testDeleteRemovesTranslationUnits(): void {
		$services = $this->getServiceContainer();
		$pageId = $this->insertPage( 'TranslatablePageStoreTest' )['id'];
		$title = Title::newFromID( $pageId );

		// Seed some translation units via the store directly.
		$db = $services->getConnectionProvider()->getPrimaryDatabase();
		$unitStore = new WritableTranslationUnitStore( $db, $pageId );
		$unitStore->store( [
			new TranslationUnit( 'Hello', '1' ),
			new TranslationUnit( 'World', '2' ),
		] );
		$this->assertCount( 2, $unitStore->getUnits() );

		/** @var TranslatablePageStore $tpPageStore */
		$tpPageStore = $services->get( 'Translate:TranslatablePageStore' );
		$tpPageStore->delete( $title );

		$this->assertSame( [], $unitStore->getUnits() );
	}
}
