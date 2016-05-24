<?php
/**
 * Contains logic for special page ...
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * ...
 *
 * @ingroup SpecialPage TranslateSpecialPage
 */
class SpecialSearchTranslations extends SpecialPage {
	/** @var FormOptions */
	protected $opts;

	/**
	 * Placeholders used for highlighting. Solr can mark the beginning and
	 * end but we need to run htmlspecialchars on the result first and then
	 * replace the placeholders with the html. It is assumed placeholders
	 * don't contain any chars that are escaped in html.
	 * @var array
	 */
	protected $hl = array();

	/**
	 * How many search results to display per page
	 * @var int
	 */
	protected $limit = 25;

	public function __construct() {
		parent::__construct( 'SearchTranslations' );
		$this->hl = array(
			TranslateUtils::getPlaceholder(),
			TranslateUtils::getPlaceholder(),
		);
	}

	public function setHeaders() {
		// Overwritten the parent because it sucks!
		// We want to set <title> but not <h1>
		$out = $this->getOutput();
		$out->setArticleRelated( false );
		$out->setRobotPolicy( 'noindex,nofollow' );
		$name = $this->msg( 'searchtranslations' );
		$name = Sanitizer::stripAllTags( $name );
		$out->setHTMLTitle( $this->msg( 'pagetitle' )->rawParams( $name ) );
	}

	public function execute( $par ) {
		global $wgLanguageCode;
		$this->setHeaders();
		$this->checkPermissions();

		$server = TTMServer::primary();
		if ( !$server instanceof SearchableTTMServer ) {
			throw new ErrorPageError( 'tux-sst-nosolr-title', 'tux-sst-nosolr-body' );
		}

		$out = $this->getOutput();
		$out->addModuleStyles( 'jquery.uls.grid' );
		$out->addModuleStyles( 'ext.translate.special.searchtranslations.styles' );
		$out->addModuleStyles( 'ext.translate.special.translate.styles' );
		$out->addModuleStyles( [ 'mediawiki.ui.button', 'mediawiki.ui.input', 'mediawiki.ui.checkbox' ] );
		$out->addModules( 'ext.translate.special.searchtranslations' );
		$out->addModules( 'ext.translate.special.searchtranslations.operatorsuggest' );
		$out->addHelpLink( 'Help:Extension:Translate#searching' );

		$this->opts = $opts = new FormOptions();
		$opts->add( 'query', '' );
		$opts->add( 'sourcelanguage', $wgLanguageCode );
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

		$options = $params = $opts->getAllValues();
		$filter = $opts->getValue( 'filter' );
		try {
			if ( $opts->getValue( 'language' ) === '' ) {
				$options['language'] = $this->getLanguage()->getCode();
			}
			$translationSearch = new CrossLanguageTranslationSearchQuery( $options, $server );
			if ( in_array( $filter, $translationSearch->getAvailableFilters() ) ) {
				$opts->setValue( 'language', $options['language'] );
				$documents = $translationSearch->getDocuments();
				$total = $translationSearch->getTotalHits();
				$resultset = $translationSearch->getResultSet();
			} else {
				$resultset = $server->search( $queryString, $params, $this->hl );
				$documents = $server->getDocuments( $resultset );
				$total = $server->getTotalHits( $resultset );
			}
		} catch ( TTMServerException $e ) {
			error_log( 'Translation search server unavailable:' . $e->getMessage() );
			throw new ErrorPageError( 'tux-sst-solr-offline-title', 'tux-sst-solr-offline-body' );
		}

		// Part 1: facets
		$facets = $server->getFacets( $resultset );
		$facetHtml = '';

		if ( count( $facets['language'] ) > 0 ) {
			if ( $filter !== '' ) {
				$facets['language'] = array_merge(
					$facets['language'],
					array( $opts->getValue( 'language' ) => $total )
				);
			}
			$facetHtml = Html::element( 'div',
				array( 'class' => 'row facet languages',
					'data-facets' => FormatJson::encode( $this->getLanguages( $facets['language'] ) ),
					'data-language' => $opts->getValue( 'language' ),
				),
				$this->msg( 'tux-sst-facet-language' )
			);
		}

		if ( count( $facets['group'] ) > 0 ) {
			$facetHtml .= Html::element( 'div',
				array( 'class' => 'row facet groups',
					'data-facets' => FormatJson::encode( $this->getGroups( $facets['group'] ) ),
					'data-group' => $opts->getValue( 'group' ) ),
				$this->msg( 'tux-sst-facet-group' )
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
				$groupId = $handle->getGroup()->getId();
				$helpers = new TranslationHelpers( $title, $groupId );
				$document['wiki'] = wfWikiID();
				$document['localid'] = $handle->getTitleForBase()->getPrefixedText();
				$document['content'] = $helpers->getTranslation();
				$document['language'] = $handle->getCode();
				array_unshift( $documents, $document );
				$total++;
			}
		}

		foreach ( $documents as $document ) {
			$text = $document['content'];
			$text = TranslateUtils::convertWhiteSpaceToHTML( $text );

			list( $pre, $post ) = $this->hl;
			$text = str_replace( $pre, '<strong class="tux-search-highlight">', $text );
			$text = str_replace( $post, '</strong>', $text );

			$title = Title::newFromText( $document['localid'] . '/' . $document['language'] );
			if ( !$title ) {
				// Should not ever happen but who knows...
				continue;
			}

			$resultAttribs = array(
				'class' => 'row tux-message',
				'data-title' => $title->getPrefixedText(),
				'data-language' => $document['language'],
			);

			$handle = new MessageHandle( $title );

			if ( $handle->isValid() ) {
				$groupId = $handle->getGroup()->getId();
				$helpers = new TranslationHelpers( $title, $groupId );
				$resultAttribs['data-definition'] = $helpers->getDefinition();
				$resultAttribs['data-translation'] = $helpers->getTranslation();
				$resultAttribs['data-group'] = $groupId;

				$uri = $title->getLocalURL( array( 'action' => 'edit' ) );
				$link = Html::element(
					'a',
					array( 'href' => $uri ),
					$this->msg( 'tux-sst-edit' )->text()
				);
			} else {
				$url = wfParseUrl( $document['uri'] );
				$domain = $url['host'];
				$link = Html::element(
					'a',
					array( 'href' => $document['uri'] ),
					$this->msg( 'tux-sst-view-foreign', $domain )->text()
				);
			}

			$access = Html::rawElement(
				'div',
				array( 'class' => 'row tux-edit tux-message-item' ),
				$link
			);

			$titleText = $title->getPrefixedText();
			$titleAttribs = array(
				'class' => 'row tux-title',
				'dir' => 'ltr',
			);

			$language = Language::factory( $document['language'] );
			$textAttribs = array(
				'class' => 'row tux-text',
				'lang' => $language->getHtmlCode(),
				'dir' => $language->getDir(),
			);

			$resultsHtml = $resultsHtml
				. Html::openElement( 'div', $resultAttribs )
				. Html::rawElement( 'div', $textAttribs, $text )
				. Html::element( 'div', $titleAttribs, $titleText )
				. $access
				. Html::closeElement( 'div' );
		}

		$resultsHtml .= Html::rawElement( 'hr', array( 'class' => 'tux-pagination-line' ) );

		$prev = $next = '';
		$offset = $this->opts->getValue( 'offset' );
		$params = $this->opts->getChangedValues();

		if ( $total - $offset > $this->limit ) {
			$newParams = array( 'offset' => $offset + $this->limit ) + $params;
			$attribs = array(
				'class' => 'mw-ui-button pager-next',
				'href' => $this->getPageTitle()->getLocalURL( $newParams ),
			);
			$next = Html::element( 'a', $attribs, $this->msg( 'tux-sst-next' )->text() );
		}
		if ( $offset ) {
			$newParams = array( 'offset' => max( 0, $offset - $this->limit ) ) + $params;
			$attribs = array(
				'class' => 'mw-ui-button pager-prev',
				'href' => $this->getPageTitle()->getLocalURL( $newParams ),
			);
			$prev = Html::element( 'a', $attribs, $this->msg( 'tux-sst-prev' )->text() );
		}

		$resultsHtml .= Html::rawElement( 'div', array( 'class' => 'tux-pagination-links' ),
			"$prev $next"
		);

		$search = $this->getSearchInput( $queryString );
		$count = $this->msg( 'tux-sst-count' )->numParams( $total );

		$this->showSearch( $search, $count, $facetHtml, $resultsHtml, $total );
	}

	protected function getLanguages( array $facet ) {
		$output = array();

		$nondefaults = $this->opts->getChangedValues();
		$selected = $this->opts->getValue( 'language' );
		$filter = $this->opts->getValue( 'filter' );

		foreach ( $facet as $key => $value ) {
			if ( $filter !== '' && $key === $selected ) {
				unset( $nondefaults['language'] );
				unset( $nondefaults['filter'] );
			} elseif ( $filter !== '' ) {
				$nondefaults['language'] = $key;
				$nondefaults['filter'] = $filter;
			} elseif ( $key === $selected ) {
				unset( $nondefaults['language'] );
			} else {
				$nondefaults['language'] = $key;
			}

			$url = $this->getPageTitle()->getLocalURL( $nondefaults );
			$value = $this->getLanguage()->formatNum( $value );

			$output[$key] = array(
				'count' => $value,
				'url' => $url
			);
		}

		return $output;
	}

	protected function getGroups( array $facet ) {
		$structure = MessageGroups::getGroupStructure();
		return $this->makeGroupFacetRows( $structure, $facet );
	}

	protected function makeGroupFacetRows( array $groups, $counts, $level = 0, $pathString = '' ) {
		$output = array();

		$nondefaults = $this->opts->getChangedValues();
		$selected = $this->opts->getValue( 'group' );
		$path = explode( '|', $this->opts->getValue( 'grouppath' ) );

		foreach ( $groups as $mixed ) {
			$subgroups = $group = $mixed;

			if ( is_array( $mixed ) ) {
				$group = array_shift( $subgroups );
			} else {
				$subgroups = array();
			}

			$id = $group->getId();

			if ( $id !== $selected && !isset( $counts[$id] ) ) {
				continue;
			}

			if ( $id === $selected ) {
				unset( $nondefaults['group'] );
				$nondefaults['grouppath'] = $pathString;
			} else {
				$nondefaults['group'] = $id;
				$nondefaults['grouppath'] = $pathString . $id;
			}

			$value = isset( $counts[$id] ) ? $counts[$id] : 0;

			$output[$id] = array(
				'id' => $id,
				'count' => $value,
				'label' => $group->getLabel(),
			);

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

	protected function showSearch( $search, $count, $facets, $results, $total ) {
		$messageSelector = $this->messageSelector();
		$this->getOutput()->addHTML( <<<HTML
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
			$params = array( 'match' => 'all' ) + $params;
			$linkText = $this->msg( 'tux-sst-link-all-match' )->text();
			$link = $this->getPageTitle()->getFullURL( $params );
			$link = "<span class='plainlinks'>[$link $linkText]</span>";

			$this->getOutput()->wrapWikiMsg(
				'<div class="successbox">$1</div>',
				array( 'tux-sst-match-message', $link )
			);
		}

		$this->getOutput()->addHTML( <<<HTML
	<div class="row searchcontent">
		<div class="three columns facets">$facets</div>
		<div class="nine columns results">$results</div>
	</div>
</div>
HTML
		);
	}

	protected function showEmptySearch() {
		$search = $this->getSearchInput( '' );
		$this->getOutput()->addHTML( <<<HTML
<div class="grid tux-searchpage">
	<div class="row searchinput">
		<div class="nine columns offset-by-three">$search</div>
	</div>
</div>
HTML
		);
	}

	// Build ellipsis to select options
	protected function ellipsisSelector( $key, $value ) {
		$nondefaults = $this->opts->getChangedValues();
		$taskParams = array( 'filter' => $value ) + $nondefaults;
		ksort( $taskParams );
		$href = $this->getPageTitle()->getLocalURL( $taskParams );
		$link = Html::element( 'a',
			array( 'href' => $href ),
			// Messages for grepping:
			// tux-sst-ellipsis-untranslated
			// tux-sst-ellipsis-outdated
			$this->msg( 'tux-sst-ellipsis-' . $key )->text()
		);

		$container = Html::rawElement( 'li', array(
			'class' => 'column',
			'data-filter' => $value,
			'data-title' => $key,
		), $link );

		return $container;
	}

	/*
	 * Design the tabs
	 */
	protected function messageSelector() {
		$nondefaults = $this->opts->getChangedValues();
		$output = Html::openElement( 'div', array( 'class' => 'row tux-messagetable-header' ) );
		$output .= Html::openElement( 'div', array( 'class' => 'nine columns' ) );
		$output .= Html::openElement( 'ul', array( 'class' => 'row tux-message-selector' ) );
		$tabs = array(
			'default' => '',
			'translated' => 'translated',
			'untranslated' => 'untranslated'
		);

		$ellipsisOptions = array(
			'outdated' => 'fuzzy'
		);

		$selected = $this->opts->getValue( 'filter' );
		$keys = array_keys( $tabs );
		if ( in_array( $selected, array_values( $ellipsisOptions ) ) ) {
			$key = $keys[count( $keys ) - 1];
			$ellipsisOptions = array( $key => $tabs[$key] );

			// Remove the last tab
			unset( $tabs[$key] );
			$tabs = array_merge( $tabs, array( 'outdated' => $selected ) );
		} elseif ( !in_array( $selected, array_values( $tabs ) ) ) {
			$selected = '';
		}

		$container = Html::openElement( 'ul', array( 'class' => 'column tux-message-selector' ) );
		foreach ( $ellipsisOptions as $optKey => $optValue ) {
			$container .= $this->ellipsisSelector( $optKey, $optValue );
		}

		$sourcelanguage = $this->opts->getValue( 'sourcelanguage' );
		$sourcelanguage = TranslateUtils::getLanguageName( $sourcelanguage );
		foreach ( $tabs as $tab => $filter ) {
			// Messages for grepping:
			// tux-sst-default
			// tux-sst-translated
			// tux-sst-untranslated
			// tux-sst-outdated
			$tabClass = "tux-sst-$tab";
			$taskParams = array( 'filter' => $filter ) + $nondefaults;
			ksort( $taskParams );
			$href = $this->getPageTitle()->getLocalURL( $taskParams );
			if ( $tab === 'default' ) {
				$link = Html::element(
					'a',
					array( 'href' => $href ),
					$this->msg( $tabClass )->text()
				);
			} else {
				$link = Html::element(
					'a',
					array( 'href' => $href ),
					$this->msg( $tabClass, $sourcelanguage )->text()
				);
			}

			if ( $selected === $filter ) {
				$tabClass = $tabClass . ' selected';
			}
			$output .= Html::rawElement( 'li', array(
				'class' => array( 'column', $tabClass ),
				'data-filter' => $filter,
				'data-title' => $tab,
			), $link );
		}

		// More column
		$output .= Html::openElement( 'li', array( 'class' => 'column more' ) ) .
			'...' .
			$container .
			Html::closeElement( 'li' );

		$output .= Html::closeElement( 'ul' );
		$output .= Html::closeElement( 'div' );
		$output .= Html::closeElement( 'div' );

		return $output;
	}

	protected function getSearchInput( $query ) {
		$attribs = array(
			'placeholder' => $this->msg( 'tux-sst-search-ph' ),
			'class' => 'searchinputbox mw-ui-input',
			'dir' => $this->getLanguage()->getDir(),
		);

		$title = Html::hidden( 'title', $this->getPageTitle()->getPrefixedText() );
		$input = Xml::input( 'query', false, $query, $attribs );
		$submit = Xml::submitButton(
			$this->msg( 'tux-sst-search' ),
			array( 'class' => 'mw-ui-button' )
		);

		$nondefaults = $this->opts->getChangedValues();
		$checkLabel = Xml::checkLabel(
			$this->msg( 'tux-sst-case-sensitive' )->text(),
			'case',
			'tux-case-sensitive',
			isset( $nondefaults['case'] )
		);
		$checkLabel = Html::openElement(
			'div',
			array( 'class' => 'tux-search-operators mw-ui-checkbox' )
		) .
			$checkLabel .
			Html::closeElement( 'div' );

		$lang = $this->getRequest()->getVal( 'language' );
		$language = is_null( $lang ) ? '' : Html::hidden( 'language', $lang );

		$form = Html::rawElement( 'form', array( 'action' => wfScript(), 'name' => 'searchform' ),
			$title . $input . $submit . $checkLabel . $language
		);

		return $form;
	}
}
