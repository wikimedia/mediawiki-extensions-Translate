<?php
/**
 * Contains logic for special page Special:Translate.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;

/**
 * Implements the core of Translate extension - a special page which shows
 * a list of messages in a format defined by Tasks.
 *
 * @ingroup SpecialPage TranslateSpecialPage
 */
class SpecialTranslate extends SpecialPage {
	/** @var MessageGroup */
	protected $group;
	protected $defaults;
	protected $nondefaults = [];
	protected $options;

	public function __construct() {
		parent::__construct( 'Translate' );
	}

	public function doesWrites() {
		return true;
	}

	protected function getGroupName() {
		return 'translation';
	}

	/**
	 * Access point for this special page.
	 *
	 * @param null|string $parameters
	 * @throws ErrorPageError
	 */
	public function execute( $parameters ) {
		$out = $this->getOutput();
		$out->addModuleStyles( [
			'ext.translate.special.translate.styles',
			'jquery.uls.grid',
			'mediawiki.ui.button'
		] );

		$this->setHeaders();

		$this->setup( $parameters );

		// Redirect old export URLs to Special:ExportTranslations
		if ( $this->getRequest()->getText( 'taction' ) === 'export' ) {
			$exportPage = SpecialPage::getTitleFor( 'ExportTranslations' );
			$out->redirect( $exportPage->getLocalURL( $this->nondefaults ) );
		}

		$out->addModules( 'ext.translate.special.translate' );
		$out->addJsConfigVars( 'wgTranslateLanguages', TranslateUtils::getLanguageNames( null ) );

		$out->addHTML( Html::openElement( 'div', [
			'class' => 'grid ext-translate-container',
		] ) );

		$out->addHTML( $this->tuxSettingsForm() );
		$out->addHTML( $this->messageSelector() );

		$table = new TuxMessageTable( $this->getContext(), $this->group, $this->options['language'] );
		$output = $table->fullTable();

		$out->addHTML( $output );
		$out->addHTML( Html::closeElement( 'div' ) );
	}

	protected function setup( $parameters ) {
		$request = $this->getRequest();

		$defaults = [
			/* str  */'language' => $this->getLanguage()->getCode(),
			/* str  */'group' => '!additions',
		];

		// Dump everything here
		$nondefaults = [];

		$parameters = array_map( 'trim', explode( ';', $parameters ) );
		$pars = [];

		foreach ( $parameters as $_ ) {
			if ( $_ === '' ) {
				continue;
			}

			if ( strpos( $_, '=' ) !== false ) {
				list( $key, $value ) = array_map( 'trim', explode( '=', $_, 2 ) );
			} else {
				$key = 'group';
				$value = $_;
			}

			$pars[$key] = $value;
		}

		foreach ( $defaults as $v => $t ) {
			if ( is_bool( $t ) ) {
				$r = isset( $pars[$v] ) ? (bool)$pars[$v] : $defaults[$v];
				$r = $request->getBool( $v, $r );
			} elseif ( is_int( $t ) ) {
				$r = isset( $pars[$v] ) ? (int)$pars[$v] : $defaults[$v];
				$r = $request->getInt( $v, $r );
			} elseif ( is_string( $t ) ) {
				$r = isset( $pars[$v] ) ? (string)$pars[$v] : $defaults[$v];
				$r = $request->getText( $v, $r );
			}

			if ( !isset( $r ) ) {
				throw new MWException( '$r was not set' );
			}

			if ( $defaults[$v] !== $r ) {
				$nondefaults[$v] = $r;
			}
		}

		$this->defaults = $defaults;
		$this->nondefaults = $nondefaults;
		Hooks::run( 'TranslateGetSpecialTranslateOptions', [ &$defaults, &$nondefaults ] );

		$this->options = $nondefaults + $defaults;
		$this->group = MessageGroups::getGroup( $this->options['group'] );
		if ( $this->group ) {
			$this->options['group'] = $this->group->getId();
		} else {
			$this->group = MessageGroups::getGroup( $this->defaults['group'] );
		}

		if ( !Language::isKnownLanguageTag( $this->options['language'] ) ) {
			$this->options['language'] = $this->defaults['language'];
		}

		if ( MessageGroups::isDynamic( $this->group ) ) {
			// @phan-suppress-next-line PhanUndeclaredMethod
			$this->group->setLanguage( $this->options['language'] );
		}
	}

	protected function tuxSettingsForm() {
		$nojs = Html::element(
			'div',
			[ 'class' => 'tux-nojs errorbox' ],
			$this->msg( 'tux-nojs' )->plain()
		);

		$attrs = [ 'class' => 'row tux-editor-header' ];
		$selectors = $this->tuxGroupSelector() .
			$this->tuxLanguageSelector() .
			$this->tuxGroupDescription() .
			$this->tuxWorkflowSelector() .
			$this->tuxGroupWarning();

		return Html::rawElement( 'div', $attrs, $selectors ) . $nojs;
	}

	protected function messageSelector() {
		$output = Html::openElement( 'div', [ 'class' => 'row tux-messagetable-header hide' ] );
		$output .= Html::openElement( 'div', [ 'class' => 'nine columns' ] );
		$output .= Html::openElement( 'ul', [ 'class' => 'row tux-message-selector' ] );
		$userId = $this->getUser()->getId();
		$tabs = [
			'all' => '',
			'untranslated' => '!translated',
			'outdated' => 'fuzzy',
			'translated' => 'translated',
			'unproofread' => "translated|!reviewer:$userId|!last-translator:$userId",
		];

		$params = $this->nondefaults;

		foreach ( $tabs as $tab => $filter ) {
			// Possible classes and messages, for grepping:
			// tux-tab-all
			// tux-tab-untranslated
			// tux-tab-outdated
			// tux-tab-translated
			// tux-tab-unproofread
			$tabClass = "tux-tab-$tab";
			$taskParams = [ 'filter' => $filter ] + $params;
			ksort( $taskParams );
			$href = $this->getPageTitle()->getLocalURL( $taskParams );
			$link = Html::element( 'a', [ 'href' => $href ], $this->msg( $tabClass )->text() );
			$output .= Html::rawElement( 'li', [
				'class' => 'column ' . $tabClass,
				'data-filter' => $filter,
				'data-title' => $tab,
			], $link );
		}

		// Check boxes for the "more" tab.
		// The array keys are used as the name attribute of the checkbox.
		// in the id attribute as tux-option-KEY,
		// and and also for the data-filter attribute.
		// The message is shown as the check box's label.
		$options = [
			'optional' => $this->msg( 'tux-message-filter-optional-messages-label' )->text(),
		];

		$container = Html::openElement( 'ul', [ 'class' => 'column tux-message-selector' ] );
		foreach ( $options as $optFilter => $optLabel ) {
			$container .= Html::rawElement( 'li',
				[ 'class' => 'column' ],
				Xml::checkLabel(
					$optLabel,
					$optFilter,
					"tux-option-$optFilter",
					isset( $this->nondefaults[$optFilter] ),
					[ 'data-filter' => $optFilter ]
				)
			);
		}

		$container .= Html::closeElement( 'ul' );

		// @todo FIXME: Hard coded "ellipsis".
		$output .= Html::openElement( 'li', [ 'class' => 'column more' ] ) .
			'...' .
			$container .
			Html::closeElement( 'li' );

		$output .= Html::closeElement( 'ul' );
		$output .= Html::closeElement( 'div' ); // close nine columns
		$output .= Html::openElement( 'div', [ 'class' => 'three columns' ] );
		$output .= Html::rawElement(
			'div',
			[ 'class' => 'tux-message-filter-wrapper' ],
			Html::element( 'input', [
				'class' => 'tux-message-filter-box',
				'type' => 'search',
				'placeholder' => $this->msg( 'tux-message-filter-placeholder' )->text()
			] )
		);

		// close three columns and the row
		$output .= Html::closeElement( 'div' ) . Html::closeElement( 'div' );

		return $output;
	}

	protected function tuxGroupSelector() {
		$groupClass = [ 'grouptitle', 'grouplink' ];
		if ( $this->group instanceof AggregateMessageGroup ) {
			$groupClass[] = 'tux-breadcrumb__item--aggregate';
		}

		// @todo FIXME The selector should have expanded parent-child lists
		$output = Html::openElement( 'div', [
			'class' => 'eight columns tux-breadcrumb',
			'data-language' => $this->options['language'],
		] ) .
			Html::element( 'span',
				[ 'class' => 'grouptitle' ],
				$this->msg( 'translate-msggroupselector-projects' )->text()
			) .
			Html::element( 'span',
				[ 'class' => 'grouptitle grouplink tux-breadcrumb__item--aggregate' ],
				$this->msg( 'translate-msggroupselector-search-all' )->text()
			) .
			Html::element( 'span',
				[
					'class' => $groupClass,
					'data-msggroupid' => $this->group->getId(),
				],
				$this->group->getLabel( $this->getContext() )
			) .
			Html::closeElement( 'div' );

		return $output;
	}

	protected function tuxLanguageSelector() {
		global $wgTranslateDocumentationLanguageCode;

		$mwService = MediaWikiServices::getInstance();
		if ( $this->options['language'] === $wgTranslateDocumentationLanguageCode ) {
			$targetLangName = $this->msg( 'translate-documentation-language' )->text();
			$targetLanguage = $mwService->getContentLanguage();
		} else {

			$targetLanguage = $mwService->getLanguageFactory()->getLanguage( $this->options['language'] );
			$languageNameUtils = $mwService->getLanguageNameUtils();
			$targetLangName = $languageNameUtils->getLanguageName( $this->options['language'] );
		}

		$label = Html::element( 'span', [], $this->msg( 'tux-languageselector' )->text() );

		$languageIcon = Html::element(
			'span',
			[ 'class' => 'ext-translate-language-icon' ]
		);

		$targetLanguageName = Html::element(
			'span',
			[
				'class' => 'ext-translate-target-language',
				'dir' => $targetLanguage->getDir(),
				'lang' => $targetLanguage->getHtmlCode()
			],
			$targetLangName
		);

		$expandIcon = Html::element(
			'span',
			[ 'class' => 'ext-translate-language-selector-expand' ]
		);

		$value = Html::rawElement(
			'span',
			[
				'class' => 'uls mw-ui-button',
				'tabindex' => 0,
				'title' => $this->msg( 'tux-select-target-language' )->text()
			],
			$languageIcon . $targetLanguageName . $expandIcon
		);

		return Html::rawElement(
			'div',
			[ 'class' => 'four columns ext-translate-language-selector' ],
			"$label $value"
		);
	}

	protected function tuxGroupDescription() {
		// Initialize an empty warning box to be filled client-side.
		return Html::rawElement(
			'div',
			[ 'class' => 'twelve columns description' ],
			$this->getGroupDescription( $this->group )
		);
	}

	protected function getGroupDescription( MessageGroup $group ) {
		$description = $group->getDescription( $this->getContext() );
		return $description === null ?
			'' : $this->getOutput()->parseAsInterface( $description );
	}

	protected function tuxGroupWarning() {
		if ( $this->options['group'] === '' ) {
			return Html::rawElement(
				'div',
				[ 'class' => 'twelve columns group-warning' ],
				$this->msg( 'tux-translate-page-no-such-group' )->parse()
			);
		}

		// Initialize an empty warning box to be filled client-side.
		return Html::element(
			'div',
			[ 'class' => 'twelve columns group-warning' ],
			''
		);
	}

	protected function tuxWorkflowSelector() {
		return Html::element( 'div', [ 'class' => 'tux-workflow twelve columns' ] );
	}

	/**
	 * Adds the task-based tabs on Special:Translate and few other special pages.
	 * Hook: SkinTemplateNavigation::SpecialPage
	 * @since 2012-02-10
	 * @param Skin $skin
	 * @param array &$tabs
	 * @return true
	 */
	public static function tabify( Skin $skin, array &$tabs ) {
		$title = $skin->getTitle();
		list( $alias, $sub ) = MediaWikiServices::getInstance()
			->getSpecialPageFactory()->resolveAlias( $title->getText() );

		$pagesInGroup = [ 'Translate', 'LanguageStats', 'MessageGroupStats', 'ExportTranslations' ];
		if ( !in_array( $alias, $pagesInGroup, true ) ) {
			return true;
		}

		// Extract subpage syntax, otherwise the values are not passed forward
		$params = [];
		if ( trim( $sub ) !== '' ) {
			if ( $alias === 'Translate' || $alias === 'MessageGroupStats' ) {
				$params['group'] = $sub;
			} elseif ( $alias === 'LanguageStats' ) {
				// Breaks if additional parameters besides language are code provided
				$params['language'] = $sub;
			}
		}

		$request = $skin->getRequest();
		// However, query string params take precedence
		$params['language'] = $request->getVal( 'language' );
		$params['group'] = $request->getVal( 'group' );

		$translate = SpecialPage::getTitleFor( 'Translate' );
		$languagestats = SpecialPage::getTitleFor( 'LanguageStats' );
		$messagegroupstats = SpecialPage::getTitleFor( 'MessageGroupStats' );

		// Clear the special page tab that might be there already
		$tabs['namespaces'] = [];

		$tabs['namespaces']['translate'] = [
			'text' => wfMessage( 'translate-taction-translate' )->text(),
			'href' => $translate->getLocalURL( $params ),
			'class' => 'tux-tab',
		];

		if ( $alias === 'Translate' ) {
			$tabs['namespaces']['translate']['class'] .= ' selected';
		}

		$tabs['views']['lstats'] = [
			'text' => wfMessage( 'translate-taction-lstats' )->text(),
			'href' => $languagestats->getLocalURL( $params ),
			'class' => 'tux-tab',
		];
		if ( $alias === 'LanguageStats' ) {
			$tabs['views']['lstats']['class'] .= ' selected';
		}

		$tabs['views']['mstats'] = [
			'text' => wfMessage( 'translate-taction-mstats' )->text(),
			'href' => $messagegroupstats->getLocalURL( $params ),
			'class' => 'tux-tab',
		];

		if ( $alias === 'MessageGroupStats' ) {
			$tabs['views']['mstats']['class'] .= ' selected';
		}

		$tabs['views']['export'] = [
			'text' => wfMessage( 'translate-taction-export' )->text(),
			'href' => SpecialPage::getTitleFor( 'ExportTranslations' )->getLocalURL( $params ),
			'class' => 'tux-tab',
		];

		return true;
	}
}
