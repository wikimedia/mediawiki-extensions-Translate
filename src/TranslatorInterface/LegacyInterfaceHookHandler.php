<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use DifferenceEngine;
use MediaWiki\Diff\Hook\ArticleContentOnDiffHook;
use MediaWiki\EditPage\EditPage;
use MediaWiki\Hook\AlternateEditHook;
use MediaWiki\Hook\EditPage__showEditForm_initialHook;
use MediaWiki\Hook\SidebarBeforeOutputHook;
use MediaWiki\Languages\LanguageFactory;
use MediaWiki\Skin\SkinComponentUtils;
use MessageHandle;
use OutputPage;

/**
 * Integration point to MediaWiki for the legacy translation aids.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class LegacyInterfaceHookHandler
	implements
	AlternateEditHook,
	ArticleContentOnDiffHook,
	EditPage__showEditForm_initialHook,
	SidebarBeforeOutputHook
{
	/** @var LanguageFactory */
	private $languageFactory;

	public function __construct( LanguageFactory $languageFactory ) {
		$this->languageFactory = $languageFactory;
	}

	/**
	 * Do not show the usual introductory messages on edit page for messages.
	 * @param EditPage $editPage
	 */
	public function onAlternateEdit( $editPage ): void {
		$handle = new MessageHandle( $editPage->getTitle() );
		if ( $handle->isValid() ) {
			$editPage->suppressIntro = true;
		}
	}

	/**
	 * Enhances the action=edit view for wikitext editor with some translation aids
	 * @param EditPage $editPage
	 * @param OutputPage $out
	 */
	// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
	public function onEditPage__showEditForm_initial( $editPage, $out ): void {
		// phpcs:enable
		$handle = new MessageHandle( $editPage->getTitle() );
		if ( !$handle->isValid() ) {
			return;
		}

		$context = $out->getContext();
		$request = $context->getRequest();

		if ( $editPage->firsttime && !$request->getCheck( 'oldid' ) &&
			!$request->getCheck( 'undo' ) ) {
			if ( $handle->isFuzzy() ) {
				$editPage->textbox1 = MessageHandle::makeFuzzyString( $editPage->textbox1 );
			}
		}

		$th = new LegacyTranslationAids( $handle, $context, $this->languageFactory );
		$editPage->editFormTextTop .= $th->getBoxes();
	}

	/**
	 * Enhances the action=diff view with some translations aids
	 * @param DifferenceEngine $diffEngine
	 * @param OutputPage $output
	 */
	public function onArticleContentOnDiff( $diffEngine, $output ): void {
		$title = $diffEngine->getTitle();
		$handle = new MessageHandle( $title );

		if ( !$handle->isValid() ) {
			return;
		}

		$th = new LegacyTranslationAids( $handle, $diffEngine->getContext(), $this->languageFactory );
		$output->addHTML( $th->getBoxes() );
	}

	/**
	 * Adds toolbox menu item to pages, showing all other
	 * available translations for a message. Only shown when it
	 * actually is a translatable/translated message.
	 *
	 * @inheritDoc
	 */
	public function onSidebarBeforeOutput( $skin, &$sidebar ): void {
		$title = $skin->getTitle();
		$handle = new MessageHandle( $title );

		if ( !$handle->isValid() ) {
			return;
		}

		$message = $title->getNsText() . ':' . $handle->getKey();
		if ( method_exists( SkinComponentUtils::class, 'makeSpecialUrl' ) ) {
			$url = SkinComponentUtils::makeSpecialUrl( 'Translations', [ 'message' => $message ] );
		} else {
			// MW < 1.41
			$url = $skin::makeSpecialUrl( 'Translations', [ 'message' => $message ] );
		}

		// Add the actual toolbox entry.
		$sidebar['TOOLBOX'][ 'alltrans' ] = [
			'href' => $url,
			'id' => 't-alltrans',
			'msg' => 'translate-sidebar-alltrans',
		];
	}
}
