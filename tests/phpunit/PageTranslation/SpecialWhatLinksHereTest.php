<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Request\FauxRequest;
use MediaWiki\Specials\Hook\SpecialWhatLinksHereBacklinkQueryHook;
use MediaWiki\Tests\Specials\SpecialPageTestBase;

/**
 * @group Database
 * @covers \MediaWiki\Extension\Translate\PageTranslation\PageTranslationHookHandler
 * @covers \MediaWiki\Extension\Translate\PageTranslation\WhatLinksHereHookHandler
 */
class SpecialWhatLinksHereTest extends SpecialPageTestBase {

	protected function setUp(): void {
		parent::setUp();
		// TODO: Remove once MW 1.48 is the minimum
		if ( !interface_exists( SpecialWhatLinksHereBacklinkQueryHook::class ) ) {
			$this->markTestSkipped( 'Requires MW 1.48' );
		}
	}

	protected function newSpecialPage() {
		return $this->getServiceContainer()->getSpecialPageFactory()->getPage( 'WhatLinksHere' );
	}

	public function addDBDataOnce(): void {
		$this->insertPage( 'Translate WhatLinksHere target' );

		$pageProps = [];
		for ( $i = 1; $i <= 7; $i++ ) {
			$page = $this->insertPage(
				"Translation backlink $i",
				'[[Translate WhatLinksHere target]]'
			);
			$pageProps[] = [
				'pp_page' => $page['id'],
				'pp_propname' => 'translate-is-translation',
				'pp_value' => '',
			];
		}

		for ( $i = 1; $i <= 3; $i++ ) {
			$this->insertPage( "Source backlink $i", '[[Translate WhatLinksHere target]]' );
		}

		$this->getDb()->newInsertQueryBuilder()
			->insertInto( 'page_props' )
			->rows( $pageProps )
			->caller( __METHOD__ )
			->execute();
	}

	public function testTranslationPagesAreFilteredBeforePagination(): void {
		[ $html ] = $this->executeSpecialPage(
			'Translate WhatLinksHere target',
			new FauxRequest( [
				'target' => 'Translate WhatLinksHere target',
				'translate-hidetranslations' => true,
				'limit' => 2,
			] ),
			'en'
		);
		$text = html_entity_decode( preg_replace( '/\s+/', ' ', strip_tags( $html ) ) );

		$this->assertStringContainsString( 'Source backlink 1', $text );
		$this->assertStringContainsString( 'Source backlink 2', $text );
		$this->assertStringNotContainsString( 'Source backlink 3', $text );
		$this->assertStringNotContainsString( 'Translation backlink', $text );
	}
}
