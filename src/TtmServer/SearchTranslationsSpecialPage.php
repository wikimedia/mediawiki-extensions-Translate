<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TtmServer;

use ErrorPageError;
use MediaWiki\Extension\Translate\LogNames;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\TranslatorInterface\Aid\CurrentTranslationAid;
use MediaWiki\Extension\Translate\TranslatorInterface\Aid\TranslationAidDataProvider;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Html\FormOptions;
use MediaWiki\Html\Html;
use MediaWiki\Json\FormatJson;
use MediaWiki\Languages\LanguageFactory;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MainConfigNames;
use MediaWiki\Message\Message;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;
use MediaWiki\Utils\UrlUtils;
use MediaWiki\WikiMap\WikiMap;
use Psr\Log\LoggerInterface;

/**
 * Contains logic to search for translations
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup SpecialPage TranslateSpecialPage
 */
class SearchTranslationsSpecialPage extends SpecialPage {
	private FormOptions $opts;
	/**
	 * Placeholders used for highlighting. Search backend can mark the beginning and
	 * end but, we need to run htmlspecialchars on the result first and then
	 * replace the placeholders with the html. It is assumed placeholders
	 * don't contain any chars that are escaped in html.
	 */
	private array $hl;
	/** How many search results to display per page */
	protected int $limit = 25;
	private TtmServerFactory $ttmServerFactory;
	private LanguageFactory $languageFactory;
	private UrlUtils $urlUtils;
	private LoggerInterface $logger;

	public function __construct(
		TtmServerFactory $ttmServerFactory,
		LanguageFactory $languageFactory,
		UrlUtils $urlUtils
	) {
		parent::__construct( 'SearchTranslations' );
		$this->hl = [
			Utilities::getPlaceholder(),
			Utilities::getPlaceholder(),
		];

		$this->ttmServerFactory = $ttmServerFactory;
		$this->languageFactory = $languageFactory;
		$this->urlUtils = $urlUtils;
		$this->logger = LoggerFactory::getInstance( LogNames::MAIN );
	}

	/** @inheritDoc */
	public function execute( $subPage ) {
		$this->setHeaders();
		$this->checkPermissions();

		$server = $this->ttmServerFactory->getDefaultForQuerying();
		if ( !$server instanceof SearchableTtmServer ) {
			throw new ErrorPageError( 'tux-sst-nosolr-title', 'tux-sst-nosolr-body' );
		}

		$out = $this->getOutput();
		$out->addModuleStyles( 'jquery.uls.grid' );
		$out->addModuleStyles( 'ext.translate.specialpages.styles' );
		$out->addModuleStyles( 'ext.translate.special.translate.styles' );
		$out->addModuleStyles( 'mediawiki.codex.messagebox.styles' );
		$out->addModuleStyles( [ 'mediawiki.ui.button', 'mediawiki.ui.input', 'mediawiki.ui.checkbox' ] );
		$out->addModules( 'ext.translate.special.searchtranslations' );
		$out->addHelpLink( 'Help:Extension:Translate#searching' );
		$out->addJsConfigVars(
			'wgTranslateLanguages',
			Utilities::getLanguageNames( LanguageNameUtils::AUTONYMS )
		);

		$this->opts = $opts = new FormOptions();
		$opts->add( 'query', '' );
		$opts->add( 'sourcelanguage', $this->getConfig()->get( MainConfigNames::LanguageCode ) );
		$opts->add( 'language', '' );
		$opts->add( 'group', '' );
		$opts->add( 'grouppath', '' );
		$opts->add( 'filter', '' );
		$opts->add( 'match', '' );
		$opts->add( 'case', '' );
		$opts->add( 'limit', $this->limit );
		$opts->add( 'offset', 0 );

		$opts->fetchValuesFromRequest( $this->getRequest() );

		$queryString = $opts->getValue( 'query' );

		if ( $queryString === '' ) {
			$this->showEmptySearch();
			return;
		}

		$search = $this->getSearchInput( $queryString );

		$crossLanguageSearch = false;
		$options = $params = $opts->getAllValues();
		$filter = $opts->getValue( 'filter' );
		try {
			if ( $opts->getValue( 'language' ) === '' ) {
				$options['language'] = $this->getLanguage()->getCode();
			} elseif ( !Utilities::isSupportedLanguageCode( $options['language'] ) ) {
				$this->showSearchError(
					$search,
					$this->msg( 'tux-sst-error-unsupported-language' )->plaintextParams( $options['language'] )
				);
				return;
			}

			$translationSearch = new CrossLanguageTranslationSearchQuery( $options, $server );
			if ( in_array( $filter, $translationSearch->getAvailableFilters() ) ) {
				if ( $options['language'] === $options['sourcelanguage'] ) {
					$this->showSearchError( $search, $this->msg( 'tux-sst-error-language' ) );
					return;
				}

				$opts->setValue( 'language', $options['language'] );
				$documents = $translationSearch->getDocuments();
				$total = $translationSearch->getTotalHits();
				$resultSet = $translationSearch->getResultSet();

				$crossLanguageSearch = true;
			} else {
				$resultSet = $server->search( $queryString, $params, $this->hl );
				$documents = $server->getDocuments( $resultSet );
				$total = $server->getTotalHits( $resultSet );
			}
		} catch ( TtmServerException $e ) {
			$message = $e->getMessage();
			// Known exceptions
			if ( preg_match( '/^Result window is too large/', $message ) ) {
				$this->showSearchError( $search, $this->msg( 'tux-sst-error-offset' ) );
				return;
			}

			// Other exceptions
			$this->logger->error(
				'Translation search server unavailable: {exception}',
				[ 'exception' => $e ]
			);
			throw new ErrorPageError( 'tux-sst-solr-offline-title', 'tux-sst-solr-offline-body' );
		}

		// Part 1: facets
		$facets = $server->getFacets( $resultSet );
		$facetHtml = '';

		if ( $facets['language'] !== [] ) {
			if ( $filter !== '' ) {
				$facets['language'] = array_merge(
					$facets['language'],
					[ $opts->getValue( 'language' ) => $total ]
				);
			}
			$facetHtml = Html::element( 'div',
				[ 'class' => 'row facet languages',
					'data-facets' => FormatJson::encode( $this->getLanguages( $facets['language'] ) ),
					'data-language' => $opts->getValue( 'language' ),
				],
				$this->msg( 'tux-sst-facet-language' )->text()
			);
		}

		if ( $facets['group'] !== [] ) {
			$facetHtml .= Html::element( 'div',
				[ 'class' => 'row facet groups',
					'data-facets' => FormatJson::encode( $this->getGroups( $facets['group'] ) ),
					'data-group' => $opts->getValue( 'group' ) ],
				$this->msg( 'tux-sst-facet-group' )->text()
			);
		}

		// Part 2: results
		$resultsHtml = '';

		$title = Title::newFromText( $queryString );
		if ( $title && !in_array( $filter, $translationSearch->getAvailableFilters() ) ) {
			$handle = new MessageHandle( $title );
			$code = $handle->getCode();
			$language = $opts->getValue( 'language' );
			if ( $code !== '' && $code !== $language && $handle->isValid() ) {
				$dataProvider = new TranslationAidDataProvider( $handle );
				$aid = new CurrentTranslationAid(
					$handle->getGroup(),
					$handle,
					$this->getContext(),
					$dataProvider
				);
				$document = [
					'wiki' => WikiMap::getCurrentWikiId(),
					'localid' => $handle->getTitleForBase()->getPrefixedText(),
					'content' => $aid->getData()['value'],
					'language' => $handle->getCode(),
				];
				array_unshift( $documents, $document );
				$total++;
			}
		}

		foreach ( $documents as $document ) {
			$text = $document['content'];
			if ( $text === null ) {
				continue;
			}
			$text = Utilities::convertWhiteSpaceToHTML( $text );

			[ $pre, $post ] = $this->hl;
			$text = str_replace( $pre, '<strong class="tux-search-highlight">', $text );
			$text = str_replace( $post, '</strong>', $text );

			$titleText = $document['localid'] . '/' . $document['language'];
			$title = Title::newFromText( $titleText );
			if ( !$title ) {
				// Should not ever happen but who knows...
				$this->logger->warning(
					'SearchTranslationsSpecialPage: Invalid title: {title}',
					[ 'title' => $titleText, 'document' => json_encode( $document ) ]
				);
				continue;
			}

			$resultAttribs = [
				'class' => 'row tux-message',
				'data-title' => $title->getPrefixedText(),
				'data-language' => $document['language'],
			];

			$handle = new MessageHandle( $title );

			if ( $handle->isValid() ) {
				$uri = Utilities::getEditorUrl( $handle, 'search' );
				$link = Html::element(
					'a',
					[ 'href' => $uri ],
					$this->msg( 'tux-sst-edit' )->text()
				);
			} else {
				if ( $crossLanguageSearch ) {
					$this->logger->warning(
						'SearchTranslationsSpecialPage: Expected valid handle: {title}',
						[ 'title' => $title->getPrefixedText() ]
					);
					continue;
				}

				$url = $this->urlUtils->parse( $document['uri'] );
				if ( !$url ) {
					continue;
				}
				$domain = $url['host'];
				$link = Html::element(
					'a',
					[ 'href' => $document['uri'] ],
					$this->msg( 'tux-sst-view-foreign', $domain )->text()
				);
			}

			$access = Html::rawElement(
				'div',
				[ 'class' => 'row tux-edit tux-message-item' ],
				$link
			);

			$titleText = $title->getPrefixedText();
			$titleAttribs = [
				'class' => 'row tux-title',
				'dir' => 'ltr',
			];

			$language = $this->languageFactory->getLanguage( $document['language'] );
			$textAttribs = [
				'class' => 'row tux-text',
				'lang' => $language->getHtmlCode(),
				'dir' => $language->getDir(),
			];

			$resultsHtml .= Html::openElement( 'div', $resultAttribs )
				. Html::rawElement( 'div', $textAttribs, $text )
				. Html::element( 'div', $titleAttribs, $titleText )
				. $access
				. Html::closeElement( 'div' );
		}

		$resultsHtml .= Html::rawElement( 'hr', [ 'class' => 'tux-pagination-line' ] );

		$prev = $next = '';
		$offset = $this->opts->getValue( 'offset' );
		$params = $this->opts->getChangedValues();

		if ( $total - $offset > $this->limit ) {
			$newParams = [ 'offset' => $offset + $this->limit ] + $params;
			$attribs = [
				'class' => 'mw-ui-button pager-next',
				'href' => $this->getPageTitle()->getLocalURL( $newParams ),
			];
			$next = Html::element( 'a', $attribs, $this->msg( 'tux-sst-next' )->text() );
		}
		if ( $offset ) {
			$newParams = [ 'offset' => max( 0, $offset - $this->limit ) ] + $params;
			$attribs = [
				'class' => 'mw-ui-button pager-prev',
				'href' => $this->getPageTitle()->getLocalURL( $newParams ),
			];
			$prev = Html::element( 'a', $attribs, $this->msg( 'tux-sst-prev' )->text() );
		}

		$resultsHtml .= Html::rawElement( 'div', [ 'class' => 'tux-pagination-links' ],
			"$prev $next"
		);

		$count = $this->msg( 'tux-sst-count' )->numParams( $total )->escaped();

		$this->showSearch( $search, $count, $facetHtml, $resultsHtml, $total );
	}

	private function getLanguages( array $facet ): array {
		$output = [];

		$nonDefaults = $this->opts->getChangedValues();
		$selected = $this->opts->getValue( 'language' );
		$filter = $this->opts->getValue( 'filter' );

		foreach ( $facet as $key => $value ) {
			if ( $filter !== '' && $key === $selected ) {
				unset( $nonDefaults['language'] );
				unset( $nonDefaults['filter'] );
			} elseif ( $filter !== '' ) {
				$nonDefaults['language'] = $key;
				$nonDefaults['filter'] = $filter;
			} elseif ( $key === $selected ) {
				unset( $nonDefaults['language'] );
			} else {
				$nonDefaults['language'] = $key;
			}

			$url = $this->getPageTitle()->getLocalURL( $nonDefaults );
			$value = $this->getLanguage()->formatNum( $value );

			$output[$key] = [
				'count' => $value,
				'url' => $url
			];
		}

		return $output;
	}

	private function getGroups( array $facet ): array {
		$structure = MessageGroups::getGroupStructure();
		return $this->makeGroupFacetRows( $structure, $facet );
	}

	private function makeGroupFacetRows(
		array $groups,
		array $counts,
		int $level = 0,
		string $pathString = ''
	): array {
		$output = [];

		$nonDefaults = $this->opts->getChangedValues();
		$selected = $this->opts->getValue( 'group' );
		$path = explode( '|', $this->opts->getValue( 'grouppath' ) );

		foreach ( $groups as $mixed ) {
			$subgroups = $group = $mixed;

			if ( is_array( $mixed ) ) {
				$group = array_shift( $subgroups );
			} else {
				$subgroups = [];
			}
			'@phan-var \MessageGroup $group';
			$id = $group->getId();

			if ( $id !== $selected && !isset( $counts[$id] ) ) {
				continue;
			}

			if ( $id === $selected ) {
				unset( $nonDefaults['group'] );
				$nonDefaults['grouppath'] = $pathString;
			} else {
				$nonDefaults['group'] = $id;
				$nonDefaults['grouppath'] = $pathString . $id;
			}

			$value = $counts[$id] ?? 0;

			$output[$id] = [
				'id' => $id,
				'count' => $value,
				'label' => $group->getLabel(),
			];

			if ( isset( $path[$level] ) && $path[$level] === $id ) {
				$output[$id]['groups'] = $this->makeGroupFacetRows(
					$subgroups,
					$counts,
					$level + 1,
					"$pathString$id|"
				);
			}
		}

		return $output;
	}

	private function showSearch(
		string $search,
		string $count,
		string $facets,
		string $results,
		int $total
	): void {
		$messageSelector = $this->messageSelector();
		$this->getOutput()->addHTML(
			<<<HTML
			<div class="grid tux-searchpage">
				<div class="row tux-searchboxform">
					<div class="tux-search-tabs offset-by-three">$messageSelector</div>
					<div class="row tux-search-options">
						<div class="offset-by-three nine columns tux-search-inputs">
							<div class="row searchinput">$search</div>
							<div class="row count">$count</div>
						</div>
					</div>
				</div>
			HTML
		);

		$query = trim( $this->opts->getValue( 'query' ) );
		$hasSpace = preg_match( '/\s/', $query );
		$match = $this->opts->getValue( 'match' );
		$size = 100;
		if ( $total > $size && $match !== 'all' && $hasSpace ) {
			$params = $this->opts->getChangedValues();
			$params = [ 'match' => 'all' ] + $params;
			$linkText = $this->msg( 'tux-sst-link-all-match' )->text();
			$link = $this->getPageTitle()->getFullURL( $params );
			$link = "<span class='plainlinks'>[$link $linkText]</span>";

			$out = $this->getOutput();
			$out->addHTML(
				Html::successBox(
					$out->msg( 'tux-sst-match-message', $link )->parse()
				)
			);
		}

		$this->getOutput()->addHTML(
			<<<HTML
				<div class="row searchcontent">
					<div class="three columns facets">$facets</div>
					<div class="nine columns results">$results</div>
				</div>
			</div>
			HTML
		);
	}

	private function showEmptySearch(): void {
		$search = $this->getSearchInput( '' );
		$this->getOutput()->addHTML(
			<<<HTML
			<div class="grid tux-searchpage">
				<div class="row searchinput">
					<div class="nine columns offset-by-three">$search</div>
				</div>
			</div>
			HTML
		);
	}

	private function showSearchError( string $search, Message $message ): void {
		$messageSelector = $this->messageSelector();
		$messageHTML = Html::errorBox(
			$message->parse(),
			'',
			'row'
		);
		$this->getOutput()->addHTML(
			<<<HTML
			<div class="grid tux-searchpage">
				<div class="row tux-searchboxform">
					<div class="tux-search-tabs offset-by-three">$messageSelector</div>
					<div class="row tux-search-options">
						<div class="offset-by-three nine columns tux-search-inputs">
							<div class="row searchinput">$search</div>
							$messageHTML
						</div>
					</div>
				</div>
			</div>
			HTML
		);
	}

	/** Build ellipsis to select options */
	private function ellipsisSelector( string $key, string $value ): string {
		$nonDefaults = $this->opts->getChangedValues();
		$taskParams = [ 'filter' => $value ] + $nonDefaults;
		ksort( $taskParams );
		$href = $this->getPageTitle()->getLocalURL( $taskParams );
		$link = Html::element( 'a',
			[ 'href' => $href ],
			// Messages for grepping:
			// tux-sst-ellipsis-untranslated
			// tux-sst-ellipsis-outdated
			$this->msg( 'tux-sst-ellipsis-' . $key )->text()
		);

		return Html::rawElement( 'li', [
			'class' => 'column',
			'data-filter' => $value,
			'data-title' => $key,
		], $link );
	}

	/** Design the tabs */
	private function messageSelector(): string {
		$nonDefaults = $this->opts->getChangedValues();
		$output = Html::openElement( 'div', [ 'class' => 'row tux-messagetable-header' ] );
		$output .= Html::openElement( 'div', [ 'class' => 'twelve columns' ] );
		$output .= Html::openElement( 'ul', [ 'class' => 'row tux-message-selector' ] );
		$tabs = [
			'default' => '',
			'translated' => 'translated',
			'untranslated' => 'untranslated'
		];

		$ellipsisOptions = [
			'outdated' => 'fuzzy'
		];

		$selected = $this->opts->getValue( 'filter' );
		if ( in_array( $selected, $ellipsisOptions ) ) {
			$ellipsisOptions = array_slice( $tabs, -1 );

			// Remove the last tab
			array_pop( $tabs );
			$tabs = array_merge( $tabs, [ 'outdated' => $selected ] );
		} elseif ( !in_array( $selected, $tabs ) ) {
			$selected = '';
		}

		$container = Html::openElement( 'ul', [ 'class' => 'column tux-message-selector' ] );
		foreach ( $ellipsisOptions as $optKey => $optValue ) {
			$container .= $this->ellipsisSelector( $optKey, $optValue );
		}

		$sourceLanguage = $this->opts->getValue( 'sourcelanguage' );
		$sourceLanguage = Utilities::getLanguageName( $sourceLanguage );
		foreach ( $tabs as $tab => $filter ) {
			// Messages for grepping:
			// tux-sst-default
			// tux-sst-translated
			// tux-sst-untranslated
			// tux-sst-outdated
			$tabClass = "tux-sst-$tab";
			$taskParams = [ 'filter' => $filter ] + $nonDefaults;
			ksort( $taskParams );
			$href = $this->getPageTitle()->getLocalURL( $taskParams );
			if ( $tab === 'default' ) {
				$link = Html::element(
					'a',
					[ 'href' => $href ],
					$this->msg( $tabClass )->text()
				);
			} else {
				$link = Html::element(
					'a',
					[ 'href' => $href ],
					$this->msg( $tabClass, $sourceLanguage )->text()
				);
			}

			if ( $selected === $filter ) {
				$tabClass .= ' selected';
			}
			$output .= Html::rawElement( 'li', [
				'class' => [ 'column', $tabClass ],
				'data-filter' => $filter,
				'data-title' => $tab,
			], $link );
		}

		// More column
		$output .= Html::rawElement( 'li', [ 'class' => 'column more' ], '...' . $container );
		$output .= Html::closeElement( 'ul' ) . Html::closeElement( 'div' ) . Html::closeElement( 'div' );

		return $output;
	}

	private function getSearchInput( string $query ): string {
		$attribs = [
			'placeholder' => $this->msg( 'tux-sst-search-ph' )->text(),
			'class' => 'searchinputbox mw-ui-input',
			'dir' => $this->getLanguage()->getDir()
		];

		$title = Html::hidden( 'title', $this->getPageTitle()->getPrefixedText() );
		$input = Html::input( 'query', $query, 'text', $attribs );
		$submit = Html::submitButton(
			$this->msg( 'tux-sst-search' )->text(),
			[ 'class' => 'mw-ui-button mw-ui-progressive' ]
		);

		$typeHint = Html::rawElement(
			'div',
			[ 'class' => 'tux-searchinputbox-hint' ],
			$this->msg( 'tux-sst-search-info' )->parse()
		);

		$nonDefaults = $this->opts->getChangedValues();
		$checkLabel = Html::element( 'input', [
			'type' => 'checkbox', 'name' => 'case', 'value' => '1',
			'checked' => isset( $nonDefaults['case'] ),
			'id' => 'tux-case-sensitive',
		] ) . "\u{00A0}" . Html::label(
			$this->msg( 'tux-sst-case-sensitive' )->text(),
			'tux-case-sensitive'
		);
		$checkLabel = Html::rawElement(
			'div',
			[ 'class' => 'tux-search-operators mw-ui-checkbox' ],
			$checkLabel
		);

		$lang = $this->getRequest()->getVal( 'language' );
		$language = $lang === null ? '' : Html::hidden( 'language', $lang );

		return Html::rawElement(
			'form',
			[ 'action' => wfScript(), 'name' => 'searchform' ],
			$title . $input . $submit . $typeHint . $checkLabel . $language
		);
	}

	protected function getGroupName(): string {
		return 'translation';
	}
}
