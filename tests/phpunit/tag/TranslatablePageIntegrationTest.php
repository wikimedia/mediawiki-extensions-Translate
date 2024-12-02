<?php
declare( strict_types = 1 );

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePage;
use MediaWiki\Extension\Translate\Services;
use Wikimedia\ObjectCache\HashBagOStuff;

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

		Services::getInstance()->getTranslatablePageMarker()->unmarkPage(
			$translatablePage,
			$this->getTestSysop()->getUser(),
			RequestContext::getMain(),
			false
		);
		$this->getServiceContainer()->getMainWANObjectCache()->clearProcessCache();
		$this->setService( 'LocalServerObjectCache', new HashBagOStuff() );

		$this->assertFalse( TranslatablePage::isSourcePage( $translatablePage->getTitle() ) );

		$this->resetServices();
	}
}
