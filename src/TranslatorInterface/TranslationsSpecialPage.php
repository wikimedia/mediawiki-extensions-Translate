<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use HtmlArmor;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Html\Html;
use MediaWiki\HTMLForm\HTMLForm;
use MediaWiki\Language\Language;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Message\Message;
use MediaWiki\SpecialPage\IncludableSpecialPage;
use MediaWiki\Title\Title;
use SearchEngineFactory;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * Implements a special page which shows all translations for a message.
 * Bits taken from SpecialPrefixindex.php and TranslateTasks.php
 *
 * @author Siebrand Mazeland
 * @author Niklas Laxstörm
 * @license GPL-2.0-or-later
 * @ingroup SpecialPage TranslateSpecialPage
 */
class TranslationsSpecialPage extends IncludableSpecialPage {
	private Language $contentLanguage;
	private LanguageNameUtils $languageNameUtils;
	private ILoadBalancer $loadBalancer;
	private SearchEngineFactory $searchEngineFactory;

	public function __construct(
		Language $contentLanguage,
		LanguageNameUtils $languageNameUtils,
		ILoadBalancer $loadBalancer,
		SearchEngineFactory $searchEngineFactory
	) {
		parent::__construct( 'Translations' );
		$this->contentLanguage = $contentLanguage;
		$this->languageNameUtils = $languageNameUtils;
		$this->loadBalancer = $loadBalancer;
		$this->searchEngineFactory = $searchEngineFactory;
	}

	protected function getGroupName(): string {
		return 'translation';
	}

	/** @inheritDoc */
	public function getDescription() {
		return $this->msg( 'translations' );
	}

	/** @inheritDoc */
	public function prefixSearchSubpages( $search, $limit, $offset ) {
		return $this->prefixSearchString( $search, $limit, $offset, $this->searchEngineFactory );
	}

	/**
	 * Entry point : initialise variables and call subfunctions.
	 * @param string|null $par Message key. Becomes "MediaWiki:Allmessages" when called like
	 *             Special:Translations/MediaWiki:Allmessages (default null)
	 */
	public function execute( $par ) {
		$this->setHeaders();
		$this->outputHeader();

		$out = $this->getOutput();
		$out->addModuleStyles( 'ext.translate.specialpages.styles' );

		$par = (string)$par;

		if ( $this->including() ) {
			$title = Title::newFromText( $par );
			if ( !$title ) {
				$out->addWikiMsg( 'translate-translations-including-no-param' );
			} else {
				$this->showTranslations( $title );
			}

			return;
		}

		/**
		 * GET values.
		 */
		$request = $this->getRequest();
		$message = $request->getText( 'message', $par );
		$namespace = $request->getInt( 'namespace', NS_MAIN );

		$title = Title::newFromText( $message, $namespace );

		$out->addHelpLink(
			'Help:Extension:Translate/Statistics_and_reporting#Translations_in_all_languages'
		);

		if ( !$title ) {
			$title = Title::makeTitle( NS_MEDIAWIKI, '' );
			$this->namespaceMessageForm( $title );
		} else {
			$this->namespaceMessageForm( $title );
			$out->addHTML( '<br />' );
			$this->showTranslations( $title );
		}
	}

	/**
	 * Message input fieldset
	 */
	private function namespaceMessageForm( Title $title ): void {
		$formDescriptor = [
			'textbox' => [
				'type' => 'text',
				'name' => 'message',
				'id' => 'message',
				'label-message' => 'translate-translations-messagename',
				'size' => 30,
				'default' => $title->getText(),
			],
			'selector' => [
				'type' => 'select',
				'name' => 'namespace',
				'id' => 'namespace',
				'label-message' => 'translate-translations-project',
				'options' => $this->getSortedNamespaces(),
				'default' => $title->getNamespace(),
			]
		];

		HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() )
			->setMethod( 'get' )
			->setTitle( $this->getPageTitle() ) // Remove subpage
			->setSubmitTextMsg( 'allpagessubmit' )
			->setWrapperLegendMsg( 'translate-translations-fieldset-title' )
			->prepareForm()
			->displayForm( false );
	}

	/**
	 * Returns sorted array of namespaces.
	 *
	 * @return array<string,int>
	 */
	private function getSortedNamespaces(): array {
		$nslist = [];
		foreach ( $this->getConfig()->get( 'TranslateMessageNamespaces' ) as $ns ) {
			$nslist[$this->contentLanguage->getFormattedNsText( $ns )] = $ns;
		}
		ksort( $nslist );

		return $nslist;
	}

	/**
	 * Builds a table with all translations of $title.
	 */
	private function showTranslations( Title $title ): void {
		$handle = new MessageHandle( $title );
		$namespace = $title->getNamespace();
		$message = $handle->getKey();

		if ( !$handle->isValid() ) {
			$this->getOutput()->addWikiMsg( 'translate-translations-no-message', $title->getPrefixedText() );

			return;
		}

		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		/** @var string[] */
		$titles = $dbr->newSelectQueryBuilder()
			->select( 'page_title' )
			->from( 'page' )
			->where( [
				'page_namespace' => $namespace,
				'page_title ' . $dbr->buildLike( "$message/", $dbr->anyString() ),
			] )
			->caller( __METHOD__ )
			->orderBy( 'page_title' )
			->fetchFieldValues();

		if ( !$titles ) {
			$this->getOutput()->addWikiMsg(
				'translate-translations-no-message',
				$title->getPrefixedText()
			);

			return;
		} else {
			$this->getOutput()->addWikiMsg(
				'translate-translations-count',
				Message::numParam( count( $titles ) )
			);
		}

		$pageInfo = Utilities::getContents( $titles, $namespace );

		$rows = [
			Html::rawElement(
				'tr',
				[],
				Html::element( 'th', [], $this->msg( 'allmessagesname' )->text() ) .
					Html::element( 'th', [], $this->msg( 'allmessagescurrent' )->text() )
			),
		];

		$historyText = "\u{00A0}<sup>" .
			$this->msg( 'translate-translations-history-short' )->escaped() .
			"</sup>\u{00A0}";
		$separator = $this->msg( 'word-separator' )->plain();

		foreach ( $titles as $key ) {
			$tTitle = Title::makeTitle( $namespace, $key );
			$tHandle = new MessageHandle( $tTitle );

			$code = $tHandle->getCode();

			$text = Utilities::getLanguageName( $code, $this->getLanguage()->getCode() );
			$text .= $separator;
			$text .= $this->msg( 'parentheses' )->params( $code )->plain();
			$tools = [
				'edit' => Html::element(
					'a',
					[ 'href' => Utilities::getEditorUrl( $tHandle ) ],
					$text
				),
				'history' => $this->getLinkRenderer()->makeLink(
					$tTitle,
					new HtmlArmor( $historyText ),
					[
						'title' => $this->msg( 'history-title', $tTitle->getPrefixedDBkey() )->text()
					],
					[ 'action' => 'history' ]
				),
			];

			$class = '';
			if ( MessageHandle::hasFuzzyString( $pageInfo[$key][0] ) || $tHandle->isFuzzy() ) {
				$class = 'mw-sp-translate-fuzzy';
			}

			$languageAttributes = [];
			if ( $this->languageNameUtils->isKnownLanguageTag( $code ) ) {
				$language = $tHandle->getEffectiveLanguage();
				$languageAttributes = [
					'lang' => $language->getHtmlCode(),
					'dir' => $language->getDir(),
				];
			}

			$formattedContent = Utilities::convertWhiteSpaceToHTML( $pageInfo[$key][0] );

			$rows[] = Html::rawElement(
				'tr',
				[ 'class' => $class ],
				Html::rawElement( 'td', [], $tools['history'] . $tools['edit'] ) .
					Html::rawElement( 'td', $languageAttributes, $formattedContent )
			);
		}

		$out = Html::rawElement(
			'table',
			[ 'class' => 'mw-sp-translate-table sortable wikitable' ],
			"\n" . implode( "\n", $rows ) . "\n"
		);
		$this->getOutput()->addHTML( $out );
	}
}
