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
		$out->addModules( 'ext.translate.special.searchtranslations' );

		$this->opts = $opts = new FormOptions();
		$opts->add( 'query', '' );
		$opts->add( 'sourcelanguage', $wgLanguageCode );
		$opts->add( 'language', '' );
		$opts->add( 'group', '' );
		$opts->add( 'grouppath', '' );
		$opts->add( 'filter', '' );
		$opts->add( 'match', '' );
		$opts->add( 'limit', $this->limit );
		$opts->add( 'offset', 0 );

		$opts->fetchValuesFromRequest( $this->getRequest() );

		$queryString = $opts->getValue( 'query' );

		if ( $queryString === '' ) {
			$this->showEmptySearch();

			return;
		}

		$params = $opts->getAllValues();
		$filter = $opts->getValue( 'filter' );
		try {
			if ( $filter !== '' ) {
				$translationSearch = new CrossLanguageTranslationSearchQuery( $params, $server );
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

			if ( $handle->isValid() ) {
				$groupId = $handle->getGroup()->getId();
				$helpers = new TranslationHelpers( $title, $groupId );
				$resultAttribs['data-definition'] = $helpers->getDefinition();
				$resultAttribs['data-translation'] = $helpers->getTranslation();
				$resultAttribs['data-group'] = $groupId;

				$uri = $title->getLocalUrl( array( 'action' => 'edit' ) );
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

			$textAttribs = array(
				'class' => 'row tux-text',
				'lang' => wfBCP47( $document['language'] ),
				'dir' => Language::factory( $document['language'] )->getDir(),
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

		$this->showSearch( $search, $count, $facetHtml, $resultsHtml, $total );
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
		$this->getOutput()->addHtml( <<<HTML
<div class="grid tux-searchpage">
	<div class="row searchinput">
		<div class="nine columns offset-by-three">$search</div>
	</div>
	<div class="row count">
		<div class="nine columns offset-by-three">$count</div>
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
			$link = $this->getTitle()->getFullUrl( $params );
			$link = "<span class='plainlinks'>[$link $linkText]</span>";

			$this->getOutput()->wrapWikiMsg(
				'<div class="successbox">$1</div>',
				array( 'tux-sst-match-message', $link )
			);
		}

		$this->getOutput()->addHtml( <<<HTML
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
		$language = is_null( $lang ) ? '' : Html::hidden( 'language', $lang );

		$form = Html::rawElement( 'form', array( 'action' => wfScript() ),
			$title . $input . $submit . $language
		);

		return $form;
	}
}
