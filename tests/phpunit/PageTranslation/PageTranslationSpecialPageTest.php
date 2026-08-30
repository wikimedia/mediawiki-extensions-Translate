<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Context\RequestContext;
use MediaWiki\Request\FauxRequest;
use MediaWiki\Tests\Specials\SpecialPageTestBase;
use Wikimedia\Rdbms\IDBAccessObject;

/**
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @since 2026.09
 * @covers \MediaWiki\Extension\Translate\PageTranslation\PageTranslationSpecialPage
 * @group Database
 */
class PageTranslationSpecialPageTest extends SpecialPageTestBase {

	protected function setUp(): void {
		parent::setUp();
		$this->setGroupPermissions( 'sysop', 'pagetranslation', true );
		$this->overrideConfigValue( 'TranslateMessageIndex', 'hash' );
	}

	protected function newSpecialPage(): PageTranslationSpecialPage {
		$services = $this->getServiceContainer();
		return new PageTranslationSpecialPage(
			$services->getLanguageFactory(),
			$services->getLinkBatchFactory(),
			$services->getJobQueueGroup(),
			$services->getPermissionManager(),
			$services->get( 'Translate:TranslatablePageMarker' ),
			$services->get( 'Translate:TranslatablePageParser' ),
			$services->get( 'Translate:MessageGroupMetadata' ),
			$services->get( 'Translate:TranslatablePageView' ),
			$services->get( 'Translate:TranslatablePageStateStore' ),
			$services->getFormatterFactory()
		);
	}

	private function getMarkedPageTitle(): string {
		$title = $this->insertPage( 'TestPageForTranslation', '<translate>Hello world</translate>' )['title'];
		$marker = $this->getServiceContainer()->get( 'Translate:TranslatablePageMarker' );
		$page = $title->toPageRecord( IDBAccessObject::READ_LATEST );
		$operation = $marker->getMarkOperation( $page, null, true );
		$marker->markForTranslation(
			$operation,
			new TranslatablePageSettings( [], false, '', [], true, false, false ),
			RequestContext::getMain(),
			$this->getTestSysop()->getUser()
		);
		return $title->getPrefixedText();
	}

	private function executeMarkRequest( string $pageTitle, array $priorityLangs ): string {
		$user = $this->getTestSysop()->getUser();

		$mainSession = RequestContext::getMain()->getRequest()->getSession();
		$mainSession->setUser( $user );
		$token = (string)$mainSession->getToken();

		$request = new FauxRequest( [
			'do' => 'mark',
			'target' => $pageTitle,
			'prioritylangs' => $priorityLangs,
			'token' => $token,
		], true, $mainSession );

		[ $html ] = $this->executeSpecialPage( '', $request, 'en', $user );
		return $html;
	}

	public function testInvalidPriorityLanguageShowsError(): void {
		$html = $this->executeMarkRequest( $this->getMarkedPageTitle(), [ 'not-a-language' ] );
		$this->assertStringContainsString(
			'Invalid language code specified for priority languages: not-a-language',
			$html
		);
		$this->assertStringNotContainsString( 'has been marked up for translation', $html );
	}

	public function testValidPriorityLanguageSucceeds(): void {
		$html = $this->executeMarkRequest( $this->getMarkedPageTitle(), [ 'de' ] );
		$this->assertStringNotContainsString( 'Invalid language', $html );
		$this->assertStringContainsString( 'has been marked up for translation', $html );
	}
}
