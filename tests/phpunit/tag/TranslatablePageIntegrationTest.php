<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\PageTranslation\TranslatablePage;
use MediaWiki\Extension\Translate\Services;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\PageTranslation\TranslatablePage
 * @covers \MediaWiki\Extension\Translate\PageTranslation\TranslatablePageMarker
 * @group Database
 */
class TranslatablePageIntegrationTest extends MediaWikiIntegrationTestCase {
	use MessageGroupTestTrait;
	use TranslatablePageTestTrait;

	protected function setUp(): void {
		parent::setUp();
		$this->setupGroupTestEnvironment( $this );
	}

	public function testIsSourcePage() {
		$translatablePage = $this->createMarkedTranslatablePage(
			'Test page', 'Testing page', $this->getTestSysop()->getUser()
		);

		$this->assertTrue( TranslatablePage::isSourcePage( $translatablePage->getTitle() ) );

		$cache = $this->getServiceContainer()->getMainWANObjectCache();
		Services::getInstance()->getTranslatablePageMarker()->unmarkPage(
			$translatablePage,
			$this->getTestSysop()->getUser(),
			false
		);

		$cache->clearProcessCache();
		$cache->useInterimHoldOffCaching( false );
		$this->assertFalse( TranslatablePage::isSourcePage( $translatablePage->getTitle() ) );
	}
}
