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
		$opts->add( 'sourcelanguage', '' );
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
			$resultset = $server->search( $queryString, $opts, $this->hl );
		} catch ( TTMServerException $e ) {
			error_log( 'Translation search server unavailable:' . $e->getMessage() );
			throw new ErrorPageError( 'tux-sst-solr-offline-title', 'tux-sst-solr-offline-body' );
		}

		$terms = array();
		if ( $opts->getValue( 'filter' ) === 'untranslated' ) {
			if ( $opts->getValue( 'language' ) === '' ) {
				$opts->add( 'language', $this->getLanguage()->getCode() );
			}
			$collection = $this->applyFilter( $resultset );
			$docs = $collection['documents'];
			$terms = $collection['terms'];
			$documents = $this->getMessages( $docs );
			$total = $collection['total'];
		} else {
			$documents = $server->getDocuments( $resultset );
			$total = $server->getTotalHits( $resultset );
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

			$edit = '';
			if ( $handle->isValid() ) {
				$groupId = $handle->getGroup()->getId();
				$helpers = new TranslationHelpers( $title, $groupId );
				$resultAttribs['data-definition'] = $helpers->getDefinition();
				$resultAttribs['data-translation'] = $helpers->getTranslation();
				$resultAttribs['data-group'] = $groupId;

				$uri = wfAppendQuery( $handle->getTitle()->getCanonicalUrl(), array( 'action' => 'edit' ) );
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

		$this->showSearch( $search, $count, $facetHtml, $resultsHtml );
	}

	protected function getMessages( $collect ) {
		$ret = $documents = array();
		foreach ( $collect as $mkey => $value ) {
			$ret = array();
			if ( $this->opts->getValue( 'filter' ) === 'untranslated' ) {
				$ret['content'] = $collect[$mkey]['definition'];
			}
			$localid = explode( '/', $collect[$mkey]['title']->getPrefixedText() );
			$ret['localid'] = $localid[0];
			$ret['language'] = $localid[1];
			$documents[] = $ret;
		}
		return $documents;
	}

	protected function applyFilter( $resultset ) {
		$messages = $documents = $terms = array();
		$language = $this->opts->getValue( 'language' );
		foreach ( $resultset->getResults() as $document ) {
			$data = $document->getData();
			$localid = explode( ':', $data['localid'] );
			$namespace = strtoupper( "NS_" . $localid[0] );
			$key = implode( ':', array( constant( $namespace ), $localid[1] ) );
			$messages[$key] = $data['content'];
			$terms[] = $data['localid'];
		}

		$definitions = new MessageDefinitions( $messages );
		$collection = MessageCollection::newFromDefinitions( $definitions, $language );

		if ( $this->opts->getValue( 'filter' ) === 'untranslated' ) {
			$collection->filter( 'hastranslation', true );
		}
		$total = count( $collection );
		$offset = $collection->slice( $this->opts->getValue('offset'), $this->limit );
		$collection->loadTranslations();

		foreach ( $collection->keys() as $mkey => $title ) {
			$documents[$mkey]['title'] = $title;
			$documents[$mkey]['definition'] = $messages[$mkey];
			$documents[$mkey]['translation'] = $collection[$mkey]->translation();
		}
		return array( 'documents' => $documents, 'terms'=> $terms, 'total' => $total );
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
		$this->getOutput()->addHtml( <<<HTML
<div class="grid tux-searchpage">
	<div class="row searchinput">
		<div class="nine columns offset-by-three">$search</div>
	</div>
	<div class="row count">
		<div class="nine columns offset-by-three">$count</div>
	</div>
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
		global $wgLanguageCode;
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

		$filter = $this->getRequest()->getVal( 'filter' );
		$filter = is_null( $filter ) ? Html::hidden( 'filter', 'all' ) : Html::hidden( 'filter', $filter );

		$sourcelanguage = Html::hidden( 'sourcelanguage', $wgLanguageCode );
		$form = Html::rawElement( 'form', array( 'action' => wfScript() ),
			$title . $input . $submit . $language . $sourcelanguage
		);

		return $form;
	}
}
