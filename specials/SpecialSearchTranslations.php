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
		if ( !$server instanceof SolrTTMServer ) {
			throw new ErrorPageError( 'tux-sst-nosolr-title', 'tux-sst-nosolr-body' );
		}

		$out = $this->getOutput();
		$out->addModules( 'ext.translate.special.searchtranslations' );

		$this->opts = $opts = new FormOptions();
		$opts->add( 'query', '' );
		$opts->add( 'language', '' );
		$opts->add( 'group', '' );
		$opts->add( 'grouppath', '' );

		$opts->fetchValuesFromRequest( $this->getRequest() );


		$queryString = $opts->getValue( 'query' );

		if ( $queryString === '' ) {
			$this->showEmptySearch();
			return;
		}

		try {
			$resultset = $this->doSearch( $server->getSolarium(), $queryString );
		} catch ( Solarium_Client_HttpException $e ) {
			error_log( 'Translate: Solr search server unavailable' );
			throw new ErrorPageError( 'tux-sst-solr-offline-title', 'tux-sst-solr-offline-body' );
		}

		// Part 1: facets
		$facets = '';

		$facet = $resultset->getFacetSet()->getFacet( 'language' );

		$facets .= Html::element( 'div',
			array( 'class' => 'row facet languages',
				'data-facets' => FormatJson::encode( $this->getLanguages( $facet ) ),
				'data-language' => $opts->getValue( 'language' ),
			),
			$this->msg( 'tux-sst-facet-language' )
		);

		$facet = $resultset->getFacetSet()->getFacet( 'group' );
		$facets .= Html::element( 'div',
			array( 'class' => 'row facet groups',
				'data-facets' => FormatJson::encode(  $this->getGroups( $facet) ),
				'data-group' => $opts->getValue( 'group' ), ),
			$this->msg( 'tux-sst-facet-group' )
		);

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

			$title = Title::newFromText( $document->messageid . '/' . $document->language );
			if ( !$title ) {
				// Should not ever happen but who knows...
				continue;
			}

			$resultAttribs = array(
				'class' => 'row tux-message',
				'data-title' => $title->getPrefixedText(),
				'data-language' => $document->language,
			);

			$handle = new MessageHandle( $title );
			if ( $handle->isValid() ) {
				$groupId = $handle->getGroup()->getId();
				$helpers = new TranslationHelpers( $title, $groupId );
				$resultAttribs['data-definition'] = $helpers->getDefinition();
				$resultAttribs['data-translation'] = $helpers->getTranslation();
				$resultAttribs['data-group'] = $groupId;
			}

			$result = Html::openElement( 'div', $resultAttribs );
			$result .= Html::rawElement( 'div', array( 'class' => 'row tux-text' ), $text );
			$result .= Html::element( 'div', array( 'class' => 'row tux-title' ), $document->messageid );
			if ( $handle->isValid() ) {
				$uri = wfAppendQuery( $document->uri, array( 'action' => 'edit' ) );
				$link = Html::element( 'a', array(
					'href' => $uri,
				), $this->msg( 'tux-sst-edit' )->text() );
				$result .= Html::rawElement( 'div', array( 'class' => 'row tux-edit tux-message-item' ), $link );
			}

			$result .= Html::closeElement( 'div' );
			$results .= $result;
		}

		$prev = $next = '';
		$total = $resultset->getNumFound();
		$offset = $this->getRequest()->getInt( 'offset' );
		$params = $this->getRequest()->getValues();

		if ( $total - $offset > $this->limit ) {
			$newParams = array( 'offset' => $offset + $this->limit ) + $params;
			$attribs = array(
				'class' => 'pager-next',
				'href' => $this->getTitle()->getLocalUrl( $newParams ),
			);
			$next = Html::element( 'a', $attribs, $this->msg( 'tux-sst-next' )->text() );
		}
		if ( $offset ) {
			$newParams = array( 'offset' => max( 0, $offset - $this->limit ) ) + $params;
			$attribs = array(
				'class' => 'pager-prev',
				'href' => $this->getTitle()->getLocalUrl( $newParams ),
			);
			$prev = Html::element( 'a', $attribs, $this->msg( 'tux-sst-prev' )->text() );
		}

		$results .= Html::rawElement( 'div', array(), "$prev $next" );

		$search = $this->getSearchInput( $queryString );
		$count = $this->msg( 'tux-sst-count' )->numParams( $resultset->getNumFound() );

		$this->showSearch( $search, $count, $facets, $results );
	}

	protected function doSearch( Solarium_Client $client, $queryString ) {
		$query = $client->createSelect();
		$dismax = $query->getDisMax();
		$dismax->setQueryParser( 'edismax' );
		$query->setQuery( $queryString );
		$query->setRows( $this->limit );
		$query->setStart( $this->getRequest()->getInt( 'offset' ) );

		list( $pre, $post ) = $this->hl;
		$hl = $query->getHighlighting();
		$hl->setFields( 'text' );
		$hl->setSimplePrefix( $pre );
		$hl->setSimplePostfix( $post );
		$hl->setMaxAnalyzedChars( '5000' );
		$hl->setFragSize( '5000' );
		$hl->setSnippets( 1 );

		$languageFilter = $this->opts->getValue( 'language' );
		if ( $languageFilter !== '' ) {
			$query->createFilterQuery( 'languageFilter' )
				->setQuery( 'language:%P1%', array( $languageFilter ) )
				->addTag( 'filter' );
		}

		$groupFilter = $this->opts->getValue( 'group' );
		if ( $groupFilter !== '' ) {
			$query->createFilterQuery( 'groupFilter' )
				->setQuery( 'group:%P1%', array( $groupFilter ) )
				->addTag( 'filter' );
		}

		$facetSet = $query->getFacetSet();

		$language = $facetSet->createFacetField( 'language' );
		$language->setField( 'language' );
		$language->setMincount( 1 );
		$language->addExclude( 'filter' );

		$group = $facetSet->createFacetField( 'group' );
		$group->setField( 'group' );
		$group->setMincount( 1 );
		$group->setMissing( true );
		$group->addExclude( 'filter' );

		return $client->select( $query );
	}


	protected function getLanguages( Solarium_Result_Select_Facet_Field $facet ) {
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


	protected function getGroups( Solarium_Result_Select_Facet_Field $facet ) {
		$structure = MessageGroups::getGroupStructure();
		$counts = iterator_to_array( $facet );
		return $this->makeGroupFacetRows( $structure, $counts );
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

			$url = $this->getTitle()->getLocalUrl( $nondefaults );

			$value = isset( $counts[$id] ) ? $counts[$id] : 0;
			$count = $this->getLanguage()->formatNum( $value );

			$output[$id] = array(
				'id' => $id,
				'count' => $count,
				'url' => $url,
				'label' => $group->getLabel(),
				'description' => $group->getDescription(),
				'icon' => TranslateUtils::getIcon( $group, 100 ),
			);

			if ( isset( $path[$level] ) && $path[$level] === $id ) {
				$output[$id]['groups'] = $this->makeGroupFacetRows( $subgroups, $counts, $level + 1, "$pathString$id|" );
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
		);

		$title = Html::hidden( 'title', $this->getTitle()->getPrefixedText() );
		$input = Xml::input( 'query', false, $query, $attribs );
		$submit = Xml::submitButton( $this->msg( 'tux-sst-search' ), array( 'class' => 'button' ) );

		$form = Html::rawElement( 'form', array( 'action' => wfScript() ),
			$title . $input . $submit
		);

		return $form;
	}
}
