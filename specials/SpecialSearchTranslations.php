<?php
/**
 * Contains logic for special page ...
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2013 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * ...
 *
 * @ingroup SpecialPage TranslateSpecialPage
 */
class SpecialSearchTranslations extends SpecialPage {
	/**
	 * Placeholders used for highlighting. Solr can mark the beginning and
	 * end but we need to run htmlspecialchars on the result first and then
	 * replace the placeholders with the html. It is assumed placeholders
	 * don't contain any chars that are escaped in html.
	 * @var array
	 */
	protected $hl = array();

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
		if ( !$server instanceof SolrTTMServer ) {
			throw new ErrorPageError( 'tux-sst-nosolr-title', 'tux-sst-nosolr-body' );
		}

		$out = $this->getOutput();
		$out->addModules( 'ext.translate.grid' );
		$out->addModules( 'ext.translate.special.searchtranslations' );

		$queryString = $this->getRequest()->getVal( 'query', null );

		if ( $queryString === null ) {
			$this->showSearchEmptySearch();
			return;
		}

		$resultset = $this->doSearch( $server->getSolarium(), $queryString );

		// Part 1: facets
		$facets = '';

		$facets .= Html::element( 'div',
			array( 'class' => 'row facet' ),
			$this->msg( 'tux-sst-facet-language' )
		);
		$facet = $resultset->getFacetSet()->getFacet( 'language' );
		$facets .= $this->renderFacet( $facet );

		$facets .= Html::element( 'div',
			array( 'class' => 'row facet' ),
			$this->msg( 'tux-sst-facet-group' )
		);
		$facet = $resultset->getFacetSet()->getFacet( 'group' );
		$facets .= $this->renderFacet( $facet );

		// Part 2: results
		$results = '';

		$highlighting = $resultset->getHighlighting();
		foreach ( $resultset as $document ) {

			$hdoc = $highlighting->getResult( $document->globalid );
			$text = $hdoc->getField( 'text' );
			if ( $text === array() ) {
				$text = $document->text;
				$text = htmlspecialchars( $text );
			} else {
				$text = $text[0];
				$text = htmlspecialchars( $text );

				list( $pre, $post ) = $this->hl;
				$text = str_replace( $pre, '<strong class="tux-highlight">', $text );
				$text = str_replace( $post, '</strong>', $text );
			}

			$results .= Html::rawElement( 'div', array( 'class' => 'row tux-text' ), $text );
			$results .= Html::element( 'div', array( 'class' => 'row tux-title' ), $document->messageid );
			$uri = wfAppendQuery( $document->uri, array( 'action' => 'edit' ) );
			$link = Html::element( 'a', array( 'href' => $uri ), $this->msg( 'tux-sst-edit' ) );
			$results .= Html::rawElement( 'div', array( 'class' => 'row tux-edit' ), $link );
		}

		$search = $this->getSearchInput( $queryString );
		$count = $this->msg( 'tux-sst-count' )->numParams( $resultset->getNumFound() );

		$this->showSearch( $search, $count, $facets, $results );
	}

	protected function doSearch( Solarium_Client $client, $queryString ) {
		$query = $client->createSelect();
		$dismax = $query->getDisMax();
		$dismax->setQueryParser( 'edismax' );
		$query->setQuery( $queryString );

		list( $pre, $post ) = $this->hl;
		$hl = $query->getHighlighting();
		$hl->setFields( 'text' );
		$hl->setSimplePrefix( $pre );
		$hl->setSimplePostfix( $post );
		$hl->setMaxAnalyzedChars( '5000' );
		$hl->setFragSize( '5000' );
		$hl->setSnippets( 1 );

		$facetSet = $query->getFacetSet();
		$language = $facetSet->createFacetField( 'language' );
		$language->setField( 'language' );
		$language->setMincount( 1 );

		$facetSet = $query->getFacetSet();
		$group = $facetSet->createFacetField( 'group' );
		$group->setField( 'group' );
		$group->setMincount( 1 );
		$group->setMissing( true );

		return $client->select( $query );
	}

	protected function renderFacet( Solarium_Result_Select_Facet_Field $facet ) {
		$output = '';

		foreach ( $facet as $key => $value ) {
			if ( $key === '' ) {
				$key = $this->msg( 'tux-sst-facet-orphan' )->text();
			}

			$name = Html::element( 'span', array( 'class' => 'facet-name' ), $key );
			$value = $this->getLanguage()->formatNum( $value );
			$count = Html::element( 'span', array( 'class' => 'facet-count' ), $value );

			$output .= Html::rawElement( 'div',
				array( 'class' => 'row facet-item' ),
				$name . $count
			);
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
		);

		$title = Html::hidden( 'title', $this->getTitle()->getPrefixedText() );
		$input = Xml::input( 'query', false, $query, $attribs );
		$submit = Xml::submitButton( $this->msg( 'tux-sst-search' ) );

		$form = Html::rawElement( 'form', array( 'action' => wfScript() ),
			$title . $input . $submit
		);

		return $form;
	}
}
