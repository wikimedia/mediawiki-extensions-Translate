<?php
/**
 * Contains logic for special page Special:Translations.
 *
 * @file
 * @author Siebrand Mazeland
 * @author Niklas Laxstörm
 * @copyright Copyright © 2008-2012 Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Implements a special page which shows all translations for a message.
 * Bits taken from SpecialPrefixindex.php and TranslateTasks.php
 *
 * @ingroup SpecialPage TranslateSpecialPage
 */
class SpecialTranslations extends SpecialAllpages {
	function __construct() {
		parent::__construct( 'Translations' );
	}

	/**
	 * Entry point : initialise variables and call subfunctions.
	 * @param $par \string Message key. Becomes "MediaWiki:Allmessages" when called like
	 *             Special:Translations/MediaWiki:Allmessages (default null)
	 */
	function execute( $par ) {
		$this->setHeaders();
		$this->outputHeader();
		$this->includeAssets();

		$out = $this->getOutput();

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
		TranslateUtils::addSpecialHelpLink( $out, 'Help:Extension:Translate/Statistics_and_reporting#Translations_in_all_languages' );
		if ( !$title ) {
			$title = Title::makeTitle( NS_MEDIAWIKI, '' );
			$out->addHTML( $this->namespaceMessageForm( $title ) );
		} else {
			$out->addHTML( $this->namespaceMessageForm( $title ) . '<br />' );
			$this->showTranslations( $title );
		}
	}

	/**
	 * Message input fieldset
	 *
	 * @param $title Title (default: null)
	 * @return \string HTML for fieldset.
	 */
	function namespaceMessageForm( Title $title = null ) {
		global $wgScript;

		$namespaces = new XmlSelect( 'namespace', 'namespace' );
		$namespaces->setDefault( $title->getNamespace() );

		foreach ( $this->getSortedNamespaces() as $text => $index ) {
			$namespaces->addOption( $text, $index );
		}

		$out  = Xml::openElement( 'div', array( 'class' => 'namespaceoptions' ) );
		$out .= Xml::openElement( 'form', array( 'method' => 'get', 'action' => $wgScript ) );
		$out .= Html::hidden( 'title', $this->getTitle()->getPrefixedText() );
		$out .= Xml::openElement( 'fieldset' );
		$out .= Xml::element( 'legend', null, $this->msg( 'translate-translations-fieldset-title' )->escaped() );
		$out .= Xml::openElement( 'table', array( 'id' => 'nsselect', 'class' => 'allpages' ) );
		$out .= "<tr>
				<td class='mw-label'>" .
				Xml::label( $this->msg( 'translate-translations-messagename' )->escaped(), 'message' ) .
				"</td>
				<td class='mw-input'>" .
					Xml::input( 'message', 30, $title->getText(), array( 'id' => 'message' ) ) .
				"</td>
			</tr>
			<tr>
				<td class='mw-label'>" .
					Xml::label( $this->msg( 'translate-translations-project' )->escaped(), 'namespace' ) .
				"</td>
				<td class='mw-input'>" .
					$namespaces->getHTML() . ' ' .
					Xml::submitButton( $this->msg( 'allpagessubmit' )->escaped() ) .
				"</td>
				</tr>";
		$out .= Xml::closeElement( 'table' );
		$out .= Xml::closeElement( 'fieldset' );
		$out .= Xml::closeElement( 'form' );
		$out .= Xml::closeElement( 'div' );

		return $out;
	}

	/**
	 * Returns sorted array of namespaces.
	 *
	 * @return \arrayof{String,Integer}
	 */
	public function getSortedNamespaces() {
		global $wgTranslateMessageNamespaces, $wgContLang;

		$nslist = array();
		foreach ( $wgTranslateMessageNamespaces as $ns ) {
			$nslist[$wgContLang->getFormattedNsText( $ns )] = $ns;
		}
		ksort( $nslist );

		return $nslist;
	}

	/**
	 * Builds a table with all translations of $title.
	 *
	 * @param $title Title (default: null)
	 * @return void
	 */
	function showTranslations( Title $title ) {
		$handle = new MessageHandle( $title );
		$namespace = $title->getNamespace();
		$message = $handle->getKey();

		if ( !$handle->isValid() ) {
			$wgOut->addWikiMsg( 'translate-translations-no-message', $title->getPrefixedText() );
			return;
		}

		$dbr = wfGetDB( DB_SLAVE );

		$res = $dbr->select( 'page',
			array( 'page_namespace', 'page_title' ),
			array(
				'page_namespace' => $namespace,
				'page_title ' . $dbr->buildLike( "$message/", $dbr->anyString() ),
			),
			__METHOD__,
			array(
				'ORDER BY'  => 'page_title',
				'USE INDEX' => 'name_title',
			)
		);

		if ( !$res->numRows() ) {
			$wgOut->addWikiMsg( 'translate-translations-no-message', $title->getPrefixedText() );
			return;
		} else {
			$wgOut->addWikiMsg( 'translate-translations-count', $wgLang->formatNum( $res->numRows() ) );
		}

		// Normal output.
		$titles = array();

		foreach ( $res as $s ) {
			$titles[] = $s->page_title;
		}

		$pageInfo = TranslateUtils::getContents( $titles, $namespace );

		$tableheader = Xml::openElement( 'table', array(
			'class'   => 'mw-sp-translate-table sortable'
		) );

		$tableheader .= Xml::openElement( 'tr' );
		$tableheader .= Xml::element( 'th', null, $this->msg( 'allmessagesname' )->escaped() );
		$tableheader .= Xml::element( 'th', null, $this->msg( 'allmessagescurrent' )->escaped() );
		$tableheader .= Xml::closeElement( 'tr' );

		// Adapted version of TranslateUtils:makeListing() by Nikerabbit.
		$out = $tableheader;

		$canTranslate = $wgUser->isAllowed( 'translate' );

		$ajaxPageList = array();
		$historyText = "&#160;<sup>" . $this->msg( 'translate-translations-history-short' )->escaped() . "</sup>&#160;";
		$separator = wfMessage( 'word-separator' )->plain();

		foreach ( $res as $s ) {
			$key = $s->page_title;
			$tTitle = Title::makeTitle( $s->page_namespace, $key );
			$ajaxPageList[] = $tTitle->getPrefixedDBkey();
			$tHandle = new MessageHandle( $tTitle );

			$code = $tHandle->getCode();

			$text = TranslateUtils::getLanguageName( $code, false, $wgLang->getCode() );
			$text .= $separator;
			$text .= wfMessage( 'parentheses' )->params( $code )->escaped();
			$text = htmlspecialchars( $text );

			if ( $canTranslate ) {
				$tools['edit'] = TranslationHelpers::ajaxEditLink(
					$tTitle,
					$text
				);
			} else {
				$tools['edit'] = Linker::link( $tTitle, $text );
			}

			$tools['history'] = Linker::link(
				$tTitle,
				$historyText,
				array(
					'action',
					'title' => $this->msg( 'history-title', $tTitle->getPrefixedDBkey() )->escaped()
				),
				array( 'action' => 'history' )
			);

			if ( MessageHandle::hasFuzzyString( $pageInfo[$key][0] ) || $tHandle->isFuzzy() ) {
				$class = 'orig';
			} else {
				$class = 'def';
			}

			$leftColumn = $tools['history'] . $tools['edit'];
			$out .= Xml::tags( 'tr', array( 'class' => $class ),
				Xml::tags( 'td', null, $leftColumn ) .
				Xml::tags( 'td', array( 'lang' => $code, 'dir' => Language::factory( $code )->getDir() ),
					TranslateUtils::convertWhiteSpaceToHTML( $pageInfo[$key][0] ) )
			);
		}

		$out .= Xml::closeElement( 'table' );
		$wgOut->addHTML( $out );

		$vars = array( 'trlKeys' => $ajaxPageList );
		$wgOut->addScript( Skin::makeVariablesScript( $vars ) );
	}

	/**
	 * Get code for a page name
	 *
	 * @param $name \string Page title (f.e. "MediaWiki:Main_page/nl").
	 * @return \string Language code
	 */
	private function getCode( $name ) {
		$from = strrpos( $name, '/' );
		return substr( $name, $from + 1 );
	}

	/**
	 * Add JavaScript assets
	 *
	 * @return void
	 */
	private function includeAssets() {
		$out = $this->getOutput();
		TranslationHelpers::addModules( $out );
		$out->addModules( 'ext.translate.messagetable' );
	}
}
