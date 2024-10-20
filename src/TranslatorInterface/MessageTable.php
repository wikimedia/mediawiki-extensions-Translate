<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use MediaWiki\Context\ContextSource;
use MediaWiki\Context\IContextSource;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MessageGroup;

/**
 * Display messages for translation & review
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class MessageTable extends ContextSource {
	protected MessageGroup $group;
	protected string $language;

	public function __construct( IContextSource $context, MessageGroup $group, string $language ) {
		$this->setContext( $context );
		$this->group = $group;
		if ( MediaWikiServices::getInstance()->getLanguageNameUtils()->isKnownLanguageTag( $language ) ) {
			$this->language = $language;
		} else {
			$this->language = $context->getLanguage()->getCode();
		}
	}

	public function fullTable(): string {
		$modules = [];
		Services::getInstance()->getHookRunner()->onTranslateBeforeAddModules( $modules );
		$this->getOutput()->addModules( $modules );

		$languageFactory = MediaWikiServices::getInstance()->getLanguageFactory();
		$sourceLang = $languageFactory->getLanguage( $this->group->getSourceLanguage() );
		$targetLang = $languageFactory->getLanguage( $this->language );
		$batchSize = 100;

		$list = Html::element( 'div', [
			'class' => 'row tux-messagelist',
			'data-grouptype' => get_class( $this->group ),
			'data-sourcelangcode' => $sourceLang->getCode(),
			'data-sourcelangdir' => $sourceLang->getDir(),
			'data-targetlangcode' => $targetLang->getCode(),
			'data-targetlangdir' => $targetLang->getDir(),
		] );

		$groupId = htmlspecialchars( $this->group->getId() );
		$msg = $this->msg( 'tux-messagetable-loading-messages' )
			->numParams( $batchSize )
			->escaped();

		$loader =
			<<<HTML
			<div class="tux-messagetable-loader hide" data-messagegroup="$groupId" data-pagesize="$batchSize">
				<span class="tux-loading-indicator"></span>
				<div class="tux-messagetable-loader-info">$msg</div>
			</div>
			HTML;

		$hideOwn = $this->msg( 'tux-editor-proofreading-hide-own-translations' )->escaped();
		$clearTranslated = $this->msg( 'tux-editor-clear-translated' )->escaped();
		$modeTranslate = $this->msg( 'tux-editor-translate-mode' )->escaped();
		$modePage = $this->msg( 'tux-editor-page-mode' )->escaped();
		$modeProofread = $this->msg( 'tux-editor-proofreading-mode' )->escaped();

		$actionbar =
			<<<HTML
			<div class="tux-action-bar hide row">
				<div class="three columns tux-message-list-statsbar" data-messagegroup="$groupId"></div>
				<div class="three columns text-center">
					<button class="toggle button tux-proofread-own-translations-button hide">
						$hideOwn
					</button>
					<button class="toggle button tux-editor-clear-translated hide">$clearTranslated</button>
				</div>
				<div class="six columns tux-view-switcher text-center">
					<button class="toggle down translate-mode-button">$modeTranslate
					</button><button class="toggle down page-mode-button">$modePage
					</button><button class="toggle hide proofread-mode-button">$modeProofread
					</button>
				</div>
			</div>
			HTML;

		// Actual message table is fetched and rendered at client side. This just provides
		// the loader and action bar.
		return $list . $loader . $actionbar;
	}
}
