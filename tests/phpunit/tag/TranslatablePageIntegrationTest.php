<?php
declare( strict_types = 1 );

use MediaWiki\MediaWikiServices;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \TranslatablePage
 */
class TranslatablePageIntegrationTest extends MediaWikiIntegrationTestCase {
	use TranslatablePageTestTrait;

	protected function setUp(): void {
		parent::setUp();

		$this->setMwGlobals( [
			'wgEnablePageTranslation' => true
		] );
	}

	public function testIsSourcePage() {
		$translatablePage = $this->createMarkedTranslatablePage(
			'Test page', 'Testing page', $this->getTestSysop()->getUser()
		);

		$this->assertTrue(
			TranslatablePage::isSourcePage( $translatablePage->getTitle() )
		);

		$translatablePage->unmarkTranslatablePage();

		MediaWikiServices::getInstance()->getMainWANObjectCache()->clearProcessCache();

		$this->assertFalse(
			TranslatablePage::isSourcePage( $translatablePage->getTitle() )
		);
	}
}
