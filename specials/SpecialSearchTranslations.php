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
		$this->setHeaders();
		$this->checkPermissions();

		$server = TTMServer::primary();
		if ( !$server instanceof SearchableTTMServer ) {
			throw new ErrorPageError( 'tux-sst-nosolr-title', 'tux-sst-nosolr-body' );
		}

		$out = $this->getOutput();
		$out->addModules( 'ext.translate.special.searchtranslations' );
		$out->addModuleStyles( 'ext.translate.special.translate' );

		$this->opts = $opts = new FormOptions();
		$opts->add( 'query', '' );
		$opts->add( 'language', '' );
		$opts->add( 'group', '' );
		$opts->add( 'grouppath', '' );
		$opts->add( 'filter', '' );
		$opts->add( 'limit', $this->limit );
		$opts->add( 'offset', 0 );

		$opts->fetchValuesFromRequest( $this->getRequest() );

		$queryString = $opts->getValue( 'query' );

		if ( $queryString === '' ) {
			$this->showEmptySearch();

			return;
		}

		try {
			$resultquery = $server->search( $queryString, $opts, $this->hl );
		} catch ( TTMServerException $e ) {
			error_log( 'Translation search server unavailable:' . $e->getMessage() );
			throw new ErrorPageError( 'tux-sst-solr-offline-title', 'tux-sst-solr-offline-body' );
		}

		$result = $this->applyFilter( $resultquery );

		// Part 1: facets
		$facets = $result['facets'];
		$lang = $opts->getValue( 'language' );
		if ( $lang !== '' && $lang !== null && array_key_exists( $lang, $facets['language'] ) ) {
			$total = $facets['language'][$lang];
		} else {
			$total = 0;
		}
		$facetHtml = $this->viewFacets( $facets );

		// Part 2: results
		$resultsHtml = $this->getResultsHtml( $result['documents'] );

		$resultsHtml .= Html::rawElement( 'hr', array( 'class' => 'tux-pagination-line' ) );

		$prev = $next = '';
		$offset = $this->opts->getValue( 'offset' );
		$params = $this->opts->getChangedValues();

		if ( $total - $offset > $this->limit ) {
			$newParams = array( 'offset' => $offset + $this->limit ) + $params;
			$attribs = array(
				'class' => 'mw-ui-button pager-next',
				'href' => $this->getPageTitle()->getLocalUrl( $newParams ),
			);
			$next = Html::element( 'a', $attribs, $this->msg( 'tux-sst-next' )->text() );
		}
		if ( $offset ) {
			$newParams = array( 'offset' => max( 0, $offset - $this->limit ) ) + $params;
			$attribs = array(
				'class' => 'mw-ui-button pager-prev',
				'href' => $this->getPageTitle()->getLocalUrl( $newParams ),
			);
			$prev = Html::element( 'a', $attribs, $this->msg( 'tux-sst-prev' )->text() );
		}

		$resultsHtml .= Html::rawElement( 'div', array( 'class' => 'tux-pagination-links' ),
			"$prev $next"
		);

		$search = $this->getSearchInput( $queryString );
		$count = $this->msg( 'tux-sst-count' )->numParams( $total );

		$language = $opts->getValue( 'language' );
		if ( $language === '') {
			$resultsHtml = Html::element( 'span',
				array(),
				$this->msg( 'tux-sst-nolang-selected' )->text()
			);
		}

		$this->showSearch( $search, $count, $facetHtml, $resultsHtml );
	}

	protected function getResultsHtml( $documents ) {
		$resultsHtml = '';
		foreach ( $documents as $document ) {
			$text = $document['content'];
			$text = TranslateUtils::convertWhiteSpaceToHTML( $text );

			list( $pre, $post ) = $this->hl;
			$text = str_replace( $pre, '<strong class="tux-highlight">', $text );
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

			$edit = '';
			if ( $handle->isValid() ) {
				$groupId = $handle->getGroup()->getId();
				$helpers = new TranslationHelpers( $title, $groupId );
				$resultAttribs['data-definition'] = $helpers->getDefinition();
				$resultAttribs['data-translation'] = $helpers->getTranslation();
				$resultAttribs['data-group'] = $groupId;

				$uri = wfAppendQuery( $document['uri'], array( 'action' => 'edit' ) );
				$link = Html::element( 'a', array(
					'href' => $uri,
				), $this->msg( 'tux-sst-edit' )->text() );
				$edit = Html::rawElement(
					'div',
					array( 'class' => 'row tux-edit tux-message-item' ),
					$link
				);
			}

			$titleText = $title->getPrefixedText();
			$titleAttribs = array(
				'class' => 'row tux-title',
				'dir' => 'ltr',
			);

			$textAttribs = array(
				'class' => 'row tux-text',
				'lang' => wfBCP47( $document['language'] ),
				'dir' => Language::factory( $document['language'] )->getDir(),
			);

			$resultsHtml = $resultsHtml
				. Html::openElement( 'div', $resultAttribs )
				. Html::rawElement( 'div', $textAttribs, $text )
				. Html::element( 'div', $titleAttribs, $titleText )
				. $edit
				. Html::closeElement( 'div' );
		}
		return $resultsHtml;
	}

	protected function viewFacets( $facets ) {
		$facetHtml = Html::element( 'div',
			array( 'class' => 'row facet languages',
				'data-facets' => FormatJson::encode( $this->getLanguages( $facets['language'] ) ),
				'data-language' => $this->opts->getValue( 'language' ),
			),
			$this->msg( 'tux-sst-facet-language' )
		);

		$facetHtml .= Html::element( 'div',
			array( 'class' => 'row facet groups',
				'data-facets' => FormatJson::encode( $this->getGroups( $facets['group'] ) ),
				'data-group' => $this->opts->getValue( 'group' ) ),
			$this->msg( 'tux-sst-facet-group' )
		);
		return $facetHtml;
	}

	protected function getLanguages( array $facet ) {
		$output = array();

		$nondefaults = $this->opts->getChangedValues();
		$selected = $this->opts->getValue( 'language' );

		foreach ( $facet as $key => $value ) {
			if ( $key === $selected ) {
				unset( $nondefaults['language'] );
			} else {
				$nondefaults['language'] = $key;
			}

			$url = $this->getPageTitle()->getLocalUrl( $nondefaults );
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
			$count = $this->getLanguage()->formatNum( $value );

			$output[$id] = array(
				'id' => $id,
				'count' => $count,
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

	protected function showSearch( $search, $count, $facets, $results ) {
		$messageSelector = $this->messageSelector();
		$this->getOutput()->addHtml( <<<HTML
<div class="grid tux-searchpage">
	<div class="row searchinput">
		<div class="nine columns offset-by-three">$search</div>
	</div>
	<div class="row count">
		<div class="nine columns offset-by-three">$count</div>
	</div>
	$messageSelector
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
		$this->getOutput()->addHtml( <<<HTML
<div class="grid tux-searchpage">
	<div class="row searchinput">
		<div class="nine columns offset-by-three">$search</div>
	</div>
</div>
HTML
		);
	}

	protected function messageSelector() {
		$nondefaults = $this->opts->getChangedValues();
		$output = Html::openElement( 'div', array( 'class' => 'row tux-messagetable-header' ) );
		$output .= Html::openElement( 'div', array( 'class' => 'seven columns' ) );
		$output .= Html::openElement( 'ul', array( 'class' => 'row tux-message-selector' ) );
		$tabs = array(
			'untranslated' => 'untranslated',
			'translated' => 'translated',
			'outdated' => 'fuzzy'
		);

		$selected = $this->opts->getValue( 'filter' );
		foreach ( $tabs as $tab => $filter ) {
			$tabClass = "tux-tab-$tab";
			$taskParams = array( 'filter' => $filter ) + $nondefaults;
			ksort( $taskParams );
			$href = $this->getTitle()->getLocalUrl( $taskParams );
			$link = Html::element( 'a', array( 'href' => $href ), $this->msg( $tabClass )->text() );
			if ( $selected === $filter ) {
				$tabClass = $tabClass . ' selected';
			}
			$output .= Html::rawElement( 'li', array(
				'class' => 'column ' . $tabClass,
				'data-filter' => $filter,
				'data-title' => $tab,
			), $link );
		}

		$output .= Html::closeElement( 'ul' );
		$output .= Html::closeElement( 'div' );
		$output .= Html::closeElement( 'div' );

		return $output;
	}

	/* Messages indexed include fuzzy messages
	 * Fuzzy messages are indexed with 'fuzzy' field
	 * Messages which are not indexed are untranslated messages
	 */
	protected function applyFilter( $resultquery ) {
		$server = TTMServer::primary();
		$filter = $this->opts->getValue( 'filter' );
		if ( !in_array( $filter, $server->getAvailableFilters(), true ) ) {
			throw new MWException( "Unknown filter $filter" );
		}

		// Get list of ids and scores to find second query
		$output = $server->getLocalId( $resultquery );
		// Get the list of messages for which translations exist
		$resultset = $server->filterTranslation( $output, $this->opts );

		if ( $filter === 'untranslated' ) {
			// Update Facet for untranslated messages
			$facets = $server->getFacets( $resultset );
			$facets['language'] = $server->getFacetsForUntranslated( $facets['language'] );

			$translated = $server->getLocalId( $resultset );
			$fullData = $server->getFullData( $resultquery );
			$documents = $server->filterUntranslated( $fullData, $translated, $this->opts );
		} else {
			$facets = $server->getFacets( $resultset );
			$documents = $server->getDocuments( $resultset );
		}
		return array( 'documents' => $documents, 'facets' => $facets );
	}

	protected function getSearchInput( $query ) {
		$attribs = array(
			'placeholder' => $this->msg( 'tux-sst-search-ph' ),
			'class' => 'searchinputbox',
			'dir' => $this->getLanguage()->getDir(),
		);

		$title = Html::hidden( 'title', $this->getPageTitle()->getPrefixedText() );
		$input = Xml::input( 'query', false, $query, $attribs );
		$submit = Xml::submitButton( $this->msg( 'tux-sst-search' ), array( 'class' => 'button' ) );
		$lang = $this->getRequest()->getVal( 'language' );
		$code = $this->getLanguage()->getCode();
		$language = is_null( $lang ) ?
			Html::hidden( 'language', $code ) : Html::hidden( 'language', $lang );

		$filter = Html::hidden( 'filter', 'translated' );
		$form = Html::rawElement( 'form', array( 'action' => wfScript() ),
			$title . $input . $submit . $language . $filter
		);

		return $form;
	}
}
