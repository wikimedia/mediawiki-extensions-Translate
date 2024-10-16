<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use DerivativeContext;
use HtmlArmor;
use HTMLForm;
use Language;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Html\Html;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\SpecialPage\IncludableSpecialPage;
use MediaWiki\Title\Title;
use SearchEngineFactory;
use Wikimedia\Rdbms\ILoadBalancer;
use Xml;

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

	protected function getGroupName() {
		return 'translation';
	}

	public function getDescription() {
		return $this->msg( 'translations' );
	}

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
		$message = $request->getText( 'message' );
		$namespace = $request->getInt( 'namespace', NS_MAIN );

		if ( $message !== '' ) {
			$title = Title::newFromText( $message, $namespace );
		} else {
			$title = Title::newFromText( $par, $namespace );
		}

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
	 *
	 * @param Title|null $title (default: null)
	 */
	protected function namespaceMessageForm( Title $title = null ): void {
		$options = [];

		foreach ( $this->getSortedNamespaces() as $text => $index ) {
			$options[ $text ] = $index;
		}

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
				'options' => $options,
				'default' => $title->getNamespace(),
			]
		];

		$context = new DerivativeContext( $this->getContext() );
		$context->setTitle( $this->getPageTitle() ); // Remove subpage

		HTMLForm::factory( 'ooui', $formDescriptor, $context )
			->setMethod( 'get' )
			->setSubmitTextMsg( 'allpagessubmit' )
			->setWrapperLegendMsg( 'translate-translations-fieldset-title' )
			->prepareForm()
			->displayForm( false );
	}

	/**
	 * Returns sorted array of namespaces.
	 *
	 * @return array ( string => int )
	 */
	public function getSortedNamespaces(): array {
		$nslist = [];
		foreach ( $this->getConfig()->get( 'TranslateMessageNamespaces' ) as $ns ) {
			$nslist[$this->contentLanguage->getFormattedNsText( $ns )] = $ns;
		}
		ksort( $nslist );

		return $nslist;
	}

	/**
	 * Builds a table with all translations of $title.
	 *
	 * @param Title $title (default: null)
	 */
	protected function showTranslations( Title $title ): void {
		$handle = new MessageHandle( $title );
		$namespace = $title->getNamespace();
		$message = $handle->getKey();

		if ( !$handle->isValid() ) {
			$this->getOutput()->addWikiMsg( 'translate-translations-no-message', $title->getPrefixedText() );

			return;
		}

		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		$res = $dbr->newSelectQueryBuilder()
			->select( [ 'page_namespace', 'page_title' ] )
			->from( 'page' )
			->where( [
				'page_namespace' => $namespace,
				'page_title ' . $dbr->buildLike( "$message/", $dbr->anyString() ),
			] )
			->caller( __METHOD__ )
			->orderBy( 'page_title' )
			->fetchResultSet();

		if ( !$res->numRows() ) {
			$this->getOutput()->addWikiMsg(
				'translate-translations-no-message',
				$title->getPrefixedText()
			);

			return;
		} else {
			$this->getOutput()->addWikiMsg(
				'translate-translations-count',
				$this->getLanguage()->formatNum( $res->numRows() )
			);
		}

		// Normal output.
		$titles = [];

		foreach ( $res as $s ) {
			$titles[] = $s->page_title;
		}

		$pageInfo = Utilities::getContents( $titles, $namespace );

		$tableheader = Xml::openElement( 'table', [
			'class' => 'mw-sp-translate-table sortable wikitable'
		] );

		$tableheader .= Xml::openElement( 'tr' );
		$tableheader .= Xml::element( 'th', null, $this->msg( 'allmessagesname' )->text() );
		$tableheader .= Xml::element( 'th', null, $this->msg( 'allmessagescurrent' )->text() );
		$tableheader .= Xml::closeElement( 'tr' );

		// Adapted version of Utilities:makeListing() by Nikerabbit.
		$out = $tableheader;

		$historyText = "\u{00A0}<sup>" .
			$this->msg( 'translate-translations-history-short' )->escaped() .
			"</sup>\u{00A0}";
		$separator = $this->msg( 'word-separator' )->plain();

		$tools = [];
		foreach ( $res as $s ) {
			$key = $s->page_title;
			$tTitle = Title::makeTitle( $s->page_namespace, $key );
			$tHandle = new MessageHandle( $tTitle );

			$code = $tHandle->getCode();

			$text = Utilities::getLanguageName( $code, $this->getLanguage()->getCode() );
			$text .= $separator;
			$text .= $this->msg( 'parentheses' )->params( $code )->plain();
			$tools['edit'] = Html::element(
				'a',
				[ 'href' => Utilities::getEditorUrl( $tHandle ) ],
				$text
			);

			$tools['history'] = $this->getLinkRenderer()->makeLink(
				$tTitle,
				new HtmlArmor( $historyText ),
				[
					'title' => $this->msg( 'history-title', $tTitle->getPrefixedDBkey() )->text()
				],
				[ 'action' => 'history' ]
			);

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

			$leftColumn = $tools['history'] . $tools['edit'];
			$out .= Xml::tags( 'tr', [ 'class' => $class ],
				Xml::tags( 'td', null, $leftColumn ) .
					Xml::tags( 'td', $languageAttributes, $formattedContent )
			);
		}

		$out .= Xml::closeElement( 'table' );
		$this->getOutput()->addHTML( $out );
	}
}
