<?php
/**
 * Implements a special page which shows all translations for a message.
 * Bits taken from SpecialPrefixindex.php and TranslateTasks.php
 *
 * @author Siebrand Mazeland
 * @author Niklas Laxstörm
 * @copyright Copyright © 2008-2010 Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class SpecialTranslations extends SpecialAllpages {
	function __construct() {
		parent::__construct( 'Translations' );
	}

	/**
	 * Entry point : initialise variables and call subfunctions.
	 * @param $par String: becomes "MediaWiki:Allmessages" when called like
	 *             Special:Translations/MediaWiki:Allmessages (default null)
	 */
	function execute( $par ) {
		global $wgRequest, $wgOut;

		$this->setHeaders();
		$this->outputHeader();

		self::includeAssets();

		$title = null;

		if ( $this->including() ) {
			$title = Title::newFromText( $par );
			if ( !$title ) {
				$wgOut->addWikiMsg( 'translate-translations-including-no-param' );
			} else {
				$this->showTranslations( $title );
			}

			return;
		}

		/**
		 * GET values.
		 */
		$message = $wgRequest->getText( 'message' );
		$namespace = $wgRequest->getInt( 'namespace', NS_MAIN );
		if ( $message !== '' ) {
			$title = Title::newFromText( $message, $namespace );
		} else {
			$title = Title::newFromText( $par, $namespace );
		}

		if ( !$title ) {
			$title = Title::makeTitle( NS_MEDIAWIKI, '' );
			$wgOut->addHTML( $this->namespaceMessageForm( $title ) );
		} else {
			$wgOut->addHTML( $this->namespaceMessageForm( $title ) . '<br />' );
			$this->showTranslations( $title );
		}
	}

	/**
	* Message input fieldset
	*/
	function namespaceMessageForm( Title $title = null ) {
		global $wgContLang, $wgScript, $wgTranslateMessageNamespaces;

		$t = $this->getTitle();

		$namespaces = new XmlSelect( 'namespace', 'namespace' );
		$namespaces->setDefault( $title->getNamespace() );

		foreach ( $wgTranslateMessageNamespaces as $ns ) {
			$namespaces->addOption( $wgContLang->getFormattedNsText( $ns ), $ns );
		}

		$out  = Xml::openElement( 'div', array( 'class' => 'namespaceoptions' ) );
		$out .= Xml::openElement( 'form', array( 'method' => 'get', 'action' => $wgScript ) );
		$out .= Xml::hidden( 'title', $t->getPrefixedText() );
		$out .= Xml::openElement( 'fieldset' );
		$out .= Xml::element( 'legend', null, wfMsg( 'translate-translations-fieldset-title' ) );
		$out .= Xml::openElement( 'table', array( 'id' => 'nsselect', 'class' => 'allpages' ) );
		$out .= "<tr>
				<td class='mw-label'>" .
				Xml::label( wfMsg( 'translate-translations-messagename' ), 'message' ) .
				"</td>
				<td class='mw-input'>" .
					Xml::input( 'message', 30, $title->getText(), array( 'id' => 'message' ) ) .
				"</td>
			</tr>
			<tr>
				<td class='mw-label'>" .
					Xml::label( wfMsg( 'translate-translations-project' ), 'namespace' ) .
				"</td>
				<td class='mw-input'>" .
					$namespaces->getHTML() . ' ' .
					Xml::submitButton( wfMsg( 'allpagessubmit' ) ) .
				"</td>
				</tr>";
		$out .= Xml::closeElement( 'table' );
		$out .= Xml::closeElement( 'fieldset' );
		$out .= Xml::closeElement( 'form' );
		$out .= Xml::closeElement( 'div' );

		return $out;
	}

	function showTranslations( Title $title ) {
		global $wgOut, $wgUser;

		$sk = $wgUser->getSkin();

		$namespace = $title->getNamespace();
		$message = $title->getDBkey();

		$inMessageGroup = TranslateUtils::messageKeyToGroup( $title->getNamespace(), $title->getText() );

		if ( !$inMessageGroup ) {
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
		}

		/**
		 * Normal output.
		 */
		$titles = array();

		foreach ( $res as $s ) {
			$titles[] = $s->page_title;
		}

		$pageInfo = TranslateUtils::getContents( $titles, $namespace );

		$tableheader = Xml::openElement( 'table', array(
			'class'   => 'mw-sp-translate-table'
		) );

		$tableheader .= Xml::openElement( 'tr' );
		$tableheader .= Xml::element( 'th', null, wfMsg( 'allmessagesname' ) );
		$tableheader .= Xml::element( 'th', null, wfMsg( 'allmessagescurrent' ) );
		$tableheader .= Xml::closeElement( 'tr' );

		/**
		 * Adapted version of TranslateUtils:makeListing() by Nikerabbit.
		 */
		$out = $tableheader;

		$canTranslate = $wgUser->isAllowed( 'translate' );

		$ajaxPageList = array();
		$historyText = "&#160;<sup>" . wfMsg( 'translate-translations-history-short' ) . "</sup>&#160;";

		foreach ( $res as $s ) {
			$key = $s->page_title;
			$tTitle = Title::makeTitle( $s->page_namespace, $key );
			$ajaxPageList[] = $tTitle->getDBkey();

			$text = htmlspecialchars( $this->getCode( $s->page_title ) );

			if ( $canTranslate ) {
				$tools['edit'] = TranslationHelpers::ajaxEditLink(
					$tTitle,
					$text
				);
			} else {
				$tools['edit'] = $sk->link( $tTitle, $text );
			}

			$tools['history'] = $sk->link(
				$tTitle,
				$historyText,
				array(
					'action',
					'title' => wfMsg( 'history-title', $tTitle->getPrefixedDbKey() )
				),
				array( 'action' => 'history' )
			);

			if ( TranslateEditAddons::isFuzzy( $tTitle ) ) {
				$class = 'orig';
			} else {
				$class = 'def';
			}

			$leftColumn = $tools['history'] . $tools['edit'];
			$out .= Xml::tags( 'tr', array( 'class' => $class ),
				Xml::tags( 'td', null, $leftColumn ) .
				Xml::tags( 'td', null, TranslateUtils::convertWhiteSpaceToHTML( $pageInfo[$key][0] ) )
			);
		}

		TranslateUtils::injectCSS();

		$out .= Xml::closeElement( 'table' );
		$wgOut->addHTML( $out );

		$vars = array(
			'trlKeys' => $ajaxPageList,
			'trlMsgNoNext' => wfMsg( 'translate-js-nonext' ),
			'trlMsgSaveFailed' => wfMsg( 'translate-js-save-failed' ),
		);

		$wgOut->addScript( Skin::makeVariablesScript( $vars ) );
	}

	private function getCode( $name ) {
		$from = strrpos( $name, '/' );

		return substr( $name, $from + 1 );
	}

	private static function includeAssets() {
		global $wgOut;

		TranslateUtils::injectCSS();
		$wgOut->addScriptFile( TranslateUtils::assetPath( 'js/quickedit.js' ) );
		$wgOut->includeJQuery();
		$wgOut->addScriptFile( TranslateUtils::assetPath( 'js/jquery-ui-1.7.2.custom.min.js' ) );
		$wgOut->addScriptFile( TranslateUtils::assetPath( 'js/jquery.form.js' ) );
		$wgOut->addExtensionStyle( TranslateUtils::assetPath( 'js/base/custom-theme/jquery-ui-1.7.2.custom.css' ) );

		/**
		 * Might be needed, but ajax does not load it.
		 */
		$diff = new DifferenceEngine;
		$diff->showDiffStyle();
	}
}
