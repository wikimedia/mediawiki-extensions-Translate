<?php
/**
 * Contains logic for special page ...
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2013 Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * ...
 *
 * @ingroup SpecialPage TranslateSpecialPage
 */
class SpecialSearchTranslations extends TranslateSpecialPage {
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
	protected $limit;

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
		$request = $this->getRequest();
		list( $this->limit, $this->offset ) = $request->getLimitOffset( 20, '' );

		$this->opts = $opts = new FormOptions();
		$opts->add( 'query', '' );
		$opts->add( 'language', '' );
		$opts->add( 'group', '' );
		$opts->add( 'grouppath', '' );
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

		// Part 1: facets
		$facets = $server->getFacets( $resultset );

		$facetHtml = Html::element( 'div',
			array( 'class' => 'row facet languages',
				'data-facets' => FormatJson::encode( $this->getLanguages( $facets['language'] ) ),
				'data-language' => $opts->getValue( 'language' ),
			),
			$this->msg( 'tux-sst-facet-language' )
		);

		$facetHtml .= Html::element( 'div',
			array( 'class' => 'row facet groups',
				'data-facets' => FormatJson::encode( $this->getGroups( $facets['group'] ) ),
				'data-group' => $opts->getValue( 'group' ) ),
			$this->msg( 'tux-sst-facet-group' )
		);

		// Part 2: results
		$resultsHtml = '';
		$documents = $server->getDocuments( $resultset );

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

			$resultsHtml = $resultsHtml
				. Html::openElement( 'div', $resultAttribs )
				. Html::rawElement( 'div', array( 'class' => 'row tux-text' ), $text )
				. Html::element( 'div', array( 'class' => 'row tux-title' ), $titleText )
				. $edit
				. Html::closeElement( 'div' );
		}

		$total = $server->getTotalHits( $resultset );
		$offset = $this->opts->getValue( 'offset' );
		$params = $this->opts->getChangedValues();
		$limit = $this->opts->getValue( 'limit' );
		$prevnext = null;

		if ( $total > $limit || $offset ) {
					$prevnext = $this->getLanguage()->viewPrevNext(
					$this->getTitle(),
					$offset,
					$limit,
					$params,
					$limit + $offset >= $total
				);
		}

		$resultsHtml .= Html::rawElement( 'div', array( 'class' => 'row tux-text' ), $prevnext );

		$search = $this->getSearchInput( $queryString );

		$result = $total - $offset;
		if ( $limit < $result ) {
			$result = $limit;
		}
		if ( $total > 0 && $offset < $total ) {
			$count = $this->msg( 'tux-sst-count' )
				->numParams( $offset + 1, $offset + $result, $total )
				->numParams( $result )
				->parse();
		}

		$this->showSearch( $search, $count, $facetHtml, $resultsHtml );
	}

	protected function viewPrevNext( Title $title, $offset, $limit,
				array $newParams = array(), $atend = false ) {
		# Make 'previous' link
		$prev = $this->msg( 'tux-sst-prev' )->inLanguage( $this )->title( $title )
			->numParams( $limit )->text();

		if ( $offset > 0 ) {
			$plink = $this->numLink( $title, max( 0, $offset - $limit ), $limit,
				$newParams, $prev, 'prev-tooltiptext', 'pager-prev' );
		} else {
			$plink = htmlspecialchars( $prev );
		}

		# Make 'next' link
		$next = $this->msg( 'tux-sst-next' )->inLanguage( $this )->title( $title )
			->numParams( $limit )->text();

		if ( $atend ) {
			$nlink = htmlspecialchars( $next );
		} else {
			$nlink = $this->numLink( $title, $offset + $limit, $limit,
				$newParams, $next, 'next-tooltiptext', 'pager-next' );
		}

		# Make links to set number of items per page
		$numLinks = array();
		foreach ( array( 20, 50, 100, 250, 500 ) as $num ) {
			$numLinks[] = $this->numLink( $title, $offset, $num,
				$newParams, $num, 'num-tooltiptext', 'pager-num' );
		}

		return $this->msg( 'tux-sst-view' )->inLanguage( $this )->title( $title
			)->rawParams( $plink, $nlink, $this->pipeList( $numLinks ) )->escaped();
	}

	protected function numLink( Title $title, $offset, $limit, array $newParams, $link,
				$tooltipMsg, $class ) {
		$newParams = array( 'limit' => $limit, 'offset' => $offset ) + $newParams;
		$tooltip = $this->msg( $tooltipMsg )->inLanguage( $this )->title( $title )
			->numParams( $limit )->text();

		return Html::element( 'a', array( 'href' => $title->getLocalURL( $newParams ),
			'title' => $tooltip, 'class' => $class ), $link );
	}

	protected function pipeList( array $list ) {
		return implode( $this->msg( 'pipe-separator' )->inLanguage( $this )->escaped(), $list );
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

			$url = $this->getTitle()->getLocalUrl( $nondefaults );
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
		$attribs = array(
			'placeholder' => $this->msg( 'tux-sst-search-ph' ),
			'class' => 'searchinputbox',
			'dir' => $this->getLanguage()->getDir(),
		);

		$title = Html::hidden( 'title', $this->getTitle()->getPrefixedText() );
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
