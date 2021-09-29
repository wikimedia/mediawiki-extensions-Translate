<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use Html;
use IContextSource;
use MediaWiki\Extension\Translate\TranslatorInterface\Aid\MessageDefinitionAid;
use MediaWiki\Extension\Translate\TranslatorInterface\Aid\TranslationAidDataProvider;
use MediaWiki\Languages\LanguageFactory;
use MessageGroup;
use MessageHandle;
use Title;
use TranslateUtils;

/**
 * Provides minimal translation aids which integrate with the edit page and on diffs for
 * translatable messages.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class LegacyTranslationAids {
	/** @var MessageHandle */
	private $handle;
	/** @var MessageGroup */
	private $group;
	/** @var IContextSource */
	private $context;
	/** @var LanguageFactory */
	private $languageFactory;

	public function __construct(
		MessageHandle $handle,
		IContextSource $context,
		LanguageFactory $languageFactory
	) {
		$this->handle = $handle;
		$this->context = $context;
		$this->group = $handle->getGroup();
		$this->languageFactory = $languageFactory;
	}

	private function getDefinition(): ?string {
		$obj = new MessageDefinitionAid(
			$this->group,
			$this->handle,
			$this->context,
			new TranslationAidDataProvider( $this->handle )
		);

		return $obj->getData()['value'];
	}

	/**
	 * Returns block element HTML snippet that contains the translation aids.
	 * Not all boxes are shown all the time depending on whether they have
	 * any information to show and on configuration variables.
	 * @return string Block level HTML snippet or empty string.
	 */
	public function getBoxes(): string {
		$boxes = [];

		try {
			$boxes[] = $this->getDocumentationBox();
		} catch ( TranslationHelperException $e ) {
			$boxes[] = "<!-- Documentation not available: {$e->getMessage()} -->";
		}

		try {
			$boxes[] = $this->getDefinitionBox();
		} catch ( TranslationHelperException $e ) {
			$boxes[] = "<!-- Definition not available: {$e->getMessage()} -->";
		}

		$this->context->getOutput()->addModuleStyles( 'ext.translate.quickedit' );
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-sp-translate-edit-fields' ],
			implode( "\n\n", $boxes )
		);
	}

	private function getDefinitionBox(): string {
		$definition = $this->getDefinition();
		if ( (string)$definition === '' ) {
			throw new TranslationHelperException( 'Message lacks definition' );
		}

		$linkTag = self::ajaxEditLink( $this->handle->getTitle(), $this->group->getLabel() );
		$label =
			wfMessage( 'translate-edit-definition' )->escaped() .
			wfMessage( 'word-separator' )->escaped() .
			wfMessage( 'parentheses' )->rawParams( $linkTag )->escaped();

		$sl = $this->languageFactory->getLanguage( $this->group->getSourceLanguage() );

		$msg = Html::rawElement( 'div',
			[
				'class' => 'mw-translate-edit-deftext',
				'dir' => $sl->getDir(),
				'lang' => $sl->getHtmlCode(),
			],
			TranslateUtils::convertWhiteSpaceToHTML( $definition )
		);

		$class = [ 'class' => 'mw-sp-translate-edit-definition' ];

		return TranslateUtils::fieldset( $label, $msg, $class );
	}

	private function getDocumentationBox(): string {
		global $wgTranslateDocumentationLanguageCode;

		if ( !$wgTranslateDocumentationLanguageCode ) {
			throw new TranslationHelperException( 'Message documentation language code is not defined' );
		}

		$page = $this->handle->getKey();
		$ns = $this->handle->getTitle()->getNamespace();

		$title = $this->handle->getTitleForLanguage( $wgTranslateDocumentationLanguageCode );
		$edit = $this->ajaxEditLink(
			$title,
			$this->context->msg( 'translate-edit-contribute' )->text()
		);
		$info = TranslateUtils::getMessageContent( $page, $wgTranslateDocumentationLanguageCode, $ns );

		$class = 'mw-sp-translate-edit-info';

		// The information is most likely in English
		$divAttribs = [ 'dir' => 'ltr', 'lang' => 'en', 'class' => 'mw-content-ltr' ];

		if ( (string)$info === '' ) {
			$info = $this->context->msg( 'translate-edit-no-information' )->plain();
			$class = 'mw-sp-translate-edit-noinfo';
			$lang = $this->context->getLanguage();
			// The message saying that there's no info, should be translated
			$divAttribs = [ 'dir' => $lang->getDir(), 'lang' => $lang->getHtmlCode() ];
		}
		$class .= ' mw-sp-translate-message-documentation';

		$contents = $this->context->getOutput()->parseInlineAsInterface( $info );

		return TranslateUtils::fieldset(
			$this->context->msg( 'translate-edit-information' )->rawParams( $edit )->escaped(),
			Html::rawElement( 'div', $divAttribs, $contents ), [ 'class' => $class ]
		);
	}

	private function ajaxEditLink( Title $target, string $linkText ): string {
		$handle = new MessageHandle( $target );
		$uri = TranslateUtils::getEditorUrl( $handle );
		return Html::element(
			'a',
			[ 'href' => $uri ],
			$linkText
		);
	}
}
