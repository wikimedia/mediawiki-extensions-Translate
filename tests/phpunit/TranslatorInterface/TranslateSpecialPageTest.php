<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use MediaWiki\Tests\Specials\SpecialPageTestBase;

/**
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\TranslatorInterface\TranslateSpecialPage
 * @group Database
 */
class TranslateSpecialPageTest extends SpecialPageTestBase {

	protected function newSpecialPage(): TranslateSpecialPage {
		$services = $this->getServiceContainer();
		return new TranslateSpecialPage(
			$services->getContentLanguage(),
			$services->getLanguageFactory(),
			$services->getLanguageNameUtils(),
			$services->get( 'Translate:HookRunner' ),
			$services->getMainConfig()
		);
	}

	public function testMessageSelectorTabsUseSpanNotAnchor(): void {
		[ $html ] = $this->executeSpecialPage( '', null, 'en' );

		// Tab <li> elements must contain <span>, not <a href="#">
		$this->assertStringContainsString( 'tux-message-selector', $html );
		$this->assertStringNotContainsString( '<a href="#">', $html );
	}

	public function testMessageSelectorTabsHaveSpanChildren(): void {
		[ $html ] = $this->executeSpecialPage( '', null, 'en' );

		// Each tab class must be accompanied by a <span> inside its <li>
		$tabClasses = [
			'tux-tab-all', 'tux-tab-untranslated', 'tux-tab-outdated', 'tux-tab-translated', 'tux-tab-unproofread'
		];
		foreach ( $tabClasses as $tabClass ) {
			$this->assertMatchesRegularExpression(
				'/<li[^>]+' . preg_quote( $tabClass, '/' ) . '[^>]*>[^<]*<span/',
				$html,
				"Tab $tabClass should contain a <span> child"
			);
		}
	}
}
