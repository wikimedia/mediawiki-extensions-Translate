<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use AggregateMessageGroup;
use MediaWiki\Config\Config;
use MediaWiki\Extension\Translate\HookRunner;
use MediaWiki\Extension\Translate\LogNames;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Html\Html;
use MediaWiki\Language\Language;
use MediaWiki\Languages\LanguageFactory;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\SpecialPage\SpecialPage;
use MessageGroup;
use Psr\Log\LoggerInterface;
use Skin;

/**
 * Implements the core of Translate extension - a special page which shows
 * a list of messages in a format defined by Tasks.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @ingroup SpecialPage TranslateSpecialPage
 */
class TranslateSpecialPage extends SpecialPage {
	private ?MessageGroup $group = null;
	private array $options = [];
	private Language $contentLanguage;
	private LanguageFactory $languageFactory;
	private LanguageNameUtils $languageNameUtils;
	private HookRunner $hookRunner;
	private LoggerInterface $logger;
	private bool $isMessageGroupSubscriptionEnabled;

	public function __construct(
		Language $contentLanguage,
		LanguageFactory $languageFactory,
		LanguageNameUtils $languageNameUtils,
		HookRunner $hookRunner,
		Config $config
	) {
		parent::__construct( 'Translate' );
		$this->contentLanguage = $contentLanguage;
		$this->languageFactory = $languageFactory;
		$this->languageNameUtils = $languageNameUtils;
		$this->hookRunner = $hookRunner;
		$this->logger = LoggerFactory::getInstance( LogNames::MAIN );
		$this->isMessageGroupSubscriptionEnabled = $config->get( 'TranslateEnableMessageGroupSubscription' );
	}

	/** @inheritDoc */
	public function doesWrites() {
		return true;
	}

	protected function getGroupName(): string {
		return 'translation';
	}

	/** @inheritDoc */
	public function execute( $parameters ) {
		$out = $this->getOutput();
		$out->addModuleStyles( [
			'ext.translate.special.translate.styles',
			'jquery.uls.grid',
			'mediawiki.ui.button',
			"mediawiki.codex.messagebox.styles",
		] );

		$this->setHeaders();

		$this->setup( $parameters );

		// Redirect old export URLs to Special:ExportTranslations
		if ( $this->getRequest()->getText( 'taction' ) === 'export' ) {
			$exportPage = SpecialPage::getTitleFor( 'ExportTranslations' );
			$out->redirect( $exportPage->getLocalURL( $this->options ) );
		}

		$out->addModules( 'ext.translate.special.translate' );
		$out->addJsConfigVars( [
			'wgTranslateLanguages' => Utilities::getLanguageNames( LanguageNameUtils::AUTONYMS ),
			'wgTranslateEnableMessageGroupSubscription' => $this->isMessageGroupSubscriptionEnabled
		] );

		$out->addHTML( Html::openElement( 'div', [
			// FIXME: Temporary hack. Add better support for dark mode.
			'class' => 'grid ext-translate-container',
		] ) );

		$out->addHTML( $this->tuxSettingsForm() );
		$out->addHTML( $this->messageSelector() );

		if ( $this->group ) {
			$table = new MessageTable( $this->getContext(), $this->group, $this->options['language'] );
			$output = $table->fullTable();

			$out->addHTML( $output );
		}
		$out->addHTML( Html::closeElement( 'div' ) );
	}

	private function setup( ?string $parameters ): void {
		$request = $this->getRequest();

		$defaults = [
			'language' => $this->getLanguage()->getCode(),
			'group' => '!additions',
		];

		// Dump everything here
		$nonDefaults = [];
		$parameters = array_map( 'trim', explode( ';', (string)$parameters ) );

		foreach ( $parameters as $_ ) {
			if ( $_ === '' ) {
				continue;
			}

			if ( str_contains( $_, '=' ) ) {
				[ $key, $value ] = array_map( 'trim', explode( '=', $_, 2 ) );
			} else {
				$key = 'group';
				$value = $_;
			}

			if ( isset( $defaults[$key] ) ) {
				$nonDefaults[$key] = $value;
			}
		}

		foreach ( array_keys( $defaults ) as $key ) {
			$value = $request->getVal( $key );
			if ( is_string( $value ) ) {
				$nonDefaults[$key] = $value;
			}
		}

		$this->hookRunner->onTranslateGetSpecialTranslateOptions( $defaults, $nonDefaults );

		$this->options = $nonDefaults + $defaults;
		$this->group = MessageGroups::getGroup( $this->options['group'] );
		if ( $this->group ) {
			$this->options['group'] = $this->group->getId();
		} else {
			$this->group = MessageGroups::getGroup( $defaults['group'] );
			if (
				isset( $nonDefaults['group'] ) &&
				str_starts_with( $nonDefaults['group'], 'page-' ) &&
				!str_contains( $nonDefaults['group'], '+' )
			) {
				// https://phabricator.wikimedia.org/T320220
				$this->logger->debug(
					"[Special:Translate] Requested group {groupId} doesn't exist.",
					[ 'groupId' => $nonDefaults['group'] ]
				);
			}
		}

		if ( !$this->languageNameUtils->isKnownLanguageTag( $this->options['language'] ) ) {
			$this->options['language'] = $defaults['language'];
		}

		if ( MessageGroups::isDynamic( $this->group ) ) {
			// @phan-suppress-next-line PhanUndeclaredMethod
			$this->group->setLanguage( $this->options['language'] );
		}
	}

	private function tuxSettingsForm(): string {
		$noJs = Html::errorBox(
			$this->msg( 'tux-nojs' )->escaped(),
			'',
			'tux-nojs'
		);

		$attrs = [ 'class' => 'row tux-editor-header' ];
		$selectors = $this->tuxGroupSelector() .
			$this->tuxLanguageSelector() .
			$this->tuxGroupSubscription() .
			$this->tuxGroupDescription() .
			$this->tuxWorkflowSelector() .
			$this->tuxGroupWarning();

		return Html::rawElement( 'div', $attrs, $selectors ) . $noJs;
	}

	private function messageSelector(): string {
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

		foreach ( $tabs as $tab => $filter ) {
			// Possible classes and messages, for grepping:
			// tux-tab-all
			// tux-tab-untranslated
			// tux-tab-outdated
			// tux-tab-translated
			// tux-tab-unproofread
			$tabClass = "tux-tab-$tab";
			$link = Html::element( 'a', [ 'href' => '#' ], $this->msg( $tabClass )->text() );
			$output .= Html::rawElement( 'li', [
				'class' => 'column ' . $tabClass,
				'data-filter' => $filter,
				'data-title' => $tab,
			], $link );
		}

		// Check boxes for the "more" tab.
		$container = Html::openElement( 'ul', [ 'class' => 'column tux-message-selector' ] );
		$container .= Html::rawElement( 'li',
			[ 'class' => 'column' ],
			Html::element( 'input', [
				'type' => 'checkbox', 'name' => 'optional', 'value' => '1',
				'checked' => false,
				'id' => 'tux-option-optional',
				'data-filter' => 'optional'
			] ) . "\u{00A0}" . Html::label(
				$this->msg( 'tux-message-filter-optional-messages-label' )->text(),
				'tux-option-optional'
			)
		);

		$container .= Html::closeElement( 'ul' );
		$output .= Html::openElement( 'li', [ 'class' => 'column more' ] ) .
			$this->msg( 'ellipsis' )->escaped() .
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

	private function tuxGroupSelector(): string {
		$groupClass = [ 'grouptitle', 'grouplink' ];
		$subGroupCount = null;
		if ( $this->group instanceof AggregateMessageGroup ) {
			$groupClass[] = 'tux-breadcrumb__item--aggregate';
			$subGroupCount = count( $this->group->getGroups() );
		}

		// @todo FIXME The selector should have expanded parent-child lists
		return Html::openElement( 'div', [
			'class' => 'eight columns tux-breadcrumb',
			'data-language' => $this->options['language'],
		] ) .
			Html::element( 'span',
				[ 'class' => 'grouptitle grouplink tux-breadcrumb__item--aggregate' ],
				$this->msg( 'translate-msggroupselector-search-all' )->text()
			) .
			Html::element( 'span',
				[
					'class' => $groupClass,
					'data-msggroupid' => $this->group->getId(),
					'data-msggroup-subgroup-count' => $subGroupCount
				],
				$this->group->getLabel( $this->getContext() )
			) .
			Html::closeElement( 'div' );
	}

	private function tuxLanguageSelector(): string {
		if ( $this->options['language'] === $this->getConfig()->get( 'TranslateDocumentationLanguageCode' ) ) {
			$targetLangName = $this->msg( 'translate-documentation-language' )->text();
			$targetLanguage = $this->contentLanguage;
		} else {
			$targetLangName = $this->languageNameUtils->getLanguageName( $this->options['language'] );
			$targetLanguage = $this->languageFactory->getLanguage( $this->options['language'] );
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

	private function tuxGroupSubscription(): string {
		return Html::rawElement(
			'div',
			[ 'class' => 'twelve columns tux-watch-group' ]
		);
	}

	private function tuxGroupDescription(): string {
		// Initialize an empty warning box to be filled client-side.
		return Html::rawElement(
			'div',
			[ 'class' => 'twelve columns description' ],
			$this->group ? $this->getGroupDescription( $this->group ) : ''
		);
	}

	private function getGroupDescription( MessageGroup $group ): string {
		$description = $group->getDescription( $this->getContext() );
		return $description === null ?
			'' : $this->getOutput()->parseAsInterface( $description );
	}

	private function tuxGroupWarning(): string {
		if ( $this->options['group'] === '' ) {
			return Html::warningBox(
				$this->msg( 'tux-translate-page-no-such-group' )->parse(),
				'tux-group-warning twelve column'
			);
		}

		return '';
	}

	private function tuxWorkflowSelector(): string {
		return Html::element( 'div', [ 'class' => 'tux-workflow twelve columns' ] );
	}

	/**
	 * Adds the task-based tabs on Special:Translate and few other special pages.
	 * Hook: SkinTemplateNavigation::Universal
	 */
	public static function tabify( Skin $skin, array &$tabs ): bool {
		$title = $skin->getTitle();
		if ( !$title->isSpecialPage() ) {
			return true;
		}
		[ $alias, $sub ] = MediaWikiServices::getInstance()
			->getSpecialPageFactory()->resolveAlias( $title->getText() );

		$pagesInGroup = [ 'Translate', 'LanguageStats', 'MessageGroupStats', 'ExportTranslations' ];
		if ( !in_array( $alias, $pagesInGroup, true ) ) {
			return true;
		}

		// Extract subpage syntax, otherwise the values are not passed forward
		$params = [];
		if ( $sub !== null && trim( $sub ) !== '' ) {
			if ( $alias === 'Translate' || $alias === 'MessageGroupStats' ) {
				$params['group'] = $sub;
			} elseif ( $alias === 'LanguageStats' ) {
				// Breaks if additional parameters besides language are code provided
				$params['language'] = $sub;
			}
		}

		$request = $skin->getRequest();
		// However, query string params take precedence
		$params['language'] = $request->getRawVal( 'language' ) ?? '';
		$params['group'] = $request->getRawVal( 'group' ) ?? '';

		// Remove empty values from params
		$params = array_filter( $params, static function ( string $param ) {
			return $param !== '';
		} );

		$translate = SpecialPage::getTitleFor( 'Translate' );
		$languageStatistics = SpecialPage::getTitleFor( 'LanguageStats' );
		$messageGroupStatistics = SpecialPage::getTitleFor( 'MessageGroupStats' );

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
			'href' => $languageStatistics->getLocalURL( $params ),
			'class' => 'tux-tab',
		];
		if ( $alias === 'LanguageStats' ) {
			$tabs['views']['lstats']['class'] .= ' selected';
		}

		$tabs['views']['mstats'] = [
			'text' => wfMessage( 'translate-taction-mstats' )->text(),
			'href' => $messageGroupStatistics->getLocalURL( $params ),
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
