<?php
/**
 * Contains helper class for interface parts that aid translations in doing
 * their thing.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;

/**
 * Provides the nice boxes that aid the translators to do their job.
 * Boxes contain definition, documentation, other languages, translation memory
 * suggestions, highlighted changes etc.
 */
class TranslationHelpers {
	/** @var MessageHandle */
	private $handle;
	/** @var MessageGroup */
	private $group;
	/** @var IContextSource */
	private $context;

	public function __construct( MessageHandle $handle, IContextSource $context ) {
		$this->handle = $handle;
		$this->context = $context;
		$this->group = $handle->getGroup();
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

		$languageFactory = MediaWikiServices::getInstance()->getLanguageFactory();
		$sl = $languageFactory->getLanguage( $this->group->getSourceLanguage() );

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

/**
 * Translation helpers can throw this exception when they cannot do
 * anything useful with the current message. This helps in debugging
 * why some fields are not shown.
 * @since 2012-01-04 (Renamed in 2012-07-24 to fix typo in name)
 */
class TranslationHelperException extends MWException {
}
