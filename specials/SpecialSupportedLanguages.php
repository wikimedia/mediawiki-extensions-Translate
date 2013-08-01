<?php
/**
 * Contains logic for special page Special:SupportedLanguages
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2012-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0+
 */

/**
 * Implements special page Special:SupportedLanguages. The wiki administrator
 * must define NS_PORTAL, otherwise this page does not work. This page displays
 * a list of language portals for all portals corresponding with a language
 * code defined for MediaWiki and a subpage called "translators". The subpage
 * "translators" must contain the template [[:{{ns:template}}:User|User]],
 * taking a user name as parameter.
 *
 * @ingroup SpecialPage TranslateSpecialPage Stats
 */
class SpecialSupportedLanguages extends SpecialPage {
	/// Whether to skip and regenerate caches
	protected $purge = false;

	/// Cutoff time for inactivity in days
	protected $period = 180;

	public function __construct() {
		parent::__construct( 'SupportedLanguages' );
	}

	public function execute( $par ) {
		$out = $this->getOutput();
		$lang = $this->getLanguage();

		$this->purge = $this->getRequest()->getVal( 'action' ) === 'purge';

		$this->setHeaders();
		$out->addModules( 'ext.translate.special.supportedlanguages' );

		// Do not add html content to OutputPage before this block of code!
		$cache = wfGetCache( CACHE_ANYTHING );
		$cachekey = wfMemcKey( 'translate-supportedlanguages', $lang->getCode() );
		$data = $cache->get( $cachekey );
		if ( !$this->purge && is_string( $data ) ) {
			TranslateUtils::addSpecialHelpLink(
				$out,
				'Help:Extension:Translate/Statistics_and_reporting#List_of_languages_and_translators'
			);
			$out->addHtml( $data );

			return;
		}

		TranslateUtils::addSpecialHelpLink(
			$out,
			'Help:Extension:Translate/Statistics_and_reporting#List_of_languages_and_translators'
		);

		$this->outputHeader();
		$dbr = wfGetDB( DB_SLAVE );
		if ( $dbr->getType() === 'sqlite' ) {
			$out->addWikiText( '<div class=errorbox>SQLite is not supported.</div>' );

			return;
		}

		$out->addWikiMsg( 'supportedlanguages-colorlegend', $this->getColorLegend() );
		$out->addWikiMsg( 'supportedlanguages-localsummary' );

		// Check if CLDR extension has been installed.
		$cldrInstalled = class_exists( 'LanguageNames' );

		$locals = array();
		if ( $cldrInstalled ) {
			$locals = LanguageNames::getNames( $lang->getCode(),
				LanguageNames::FALLBACK_NORMAL,
				LanguageNames::LIST_MW_AND_CLDR
			);
		}

		$natives = Language::getLanguageNames( false );
		ksort( $natives );

		$this->outputLanguageCloud( $natives );

		// Requires NS_PORTAL. If not present, display error text.
		if ( !defined( 'NS_PORTAL' ) ) {
			$users = $this->fetchTranslatorsAuto();
		} else {
			$users = $this->fetchTranslatorsPortal( $natives );
		}

		$this->preQueryUsers( $users );

		list( $editcounts, $lastedits ) = $this->getUserStats();

		// Information to be used inside the foreach loop.
		$linkInfo['rc']['title'] = SpecialPage::getTitleFor( 'Recentchanges' );
		$linkInfo['rc']['msg'] = $this->msg( 'supportedlanguages-recenttranslations' )->escaped();
		$linkInfo['stats']['title'] = SpecialPage::getTitleFor( 'LanguageStats' );
		$linkInfo['stats']['msg'] = $this->msg( 'languagestats' )->escaped();

		foreach ( array_keys( $natives ) as $code ) {
			if ( !isset( $users[$code] ) ) {
				continue;
			}

			// If CLDR is installed, add localised header and link title.
			if ( $cldrInstalled ) {
				$headerText = $this->msg( 'supportedlanguages-portallink' )
					->params( $code, $locals[$code], $natives[$code] )->escaped();
			} else {
				// No CLDR, so a less localised header and link title.
				$headerText = $this->msg( 'supportedlanguages-portallink-nocldr' )
					->params( $code, $natives[$code] )->escaped();
			}

			$headerText = htmlspecialchars( $headerText );

			$out->addHtml( Html::openElement( 'h2', array( 'id' => $code ) ) );
			if ( defined( 'NS_PORTAL' ) ) {
				$portalTitle = Title::makeTitleSafe( NS_PORTAL, $code );
				$out->addHtml( Linker::linkKnown( $portalTitle, $headerText ) );
			} else {
				$out->addHtml( $headerText );
			}

			$out->addHTML( "</h2>" );

			// Add useful links for language stats and recent changes for the language.
			$links = array();
			$links[] = Linker::link(
				$linkInfo['stats']['title'],
				$linkInfo['stats']['msg'],
				array(),
				array(
					'code' => $code,
					'suppresscomplete' => '1'
				),
				array( 'known', 'noclasses' )
			);
			$links[] = Linker::link(
				$linkInfo['rc']['title'],
				$linkInfo['rc']['msg'],
				array(),
				array(
					'translations' => 'only',
					'trailer' => "/" . $code
				),
				array( 'known', 'noclasses' )
			);
			$linkList = $lang->listToText( $links );

			$out->addHTML( "<p>" . $linkList . "</p>\n" );
			$this->makeUserList( $users[$code], $editcounts, $lastedits );
		}

		$out->addHtml( Html::element( 'hr' ) );
		$out->addWikiMsg( 'supportedlanguages-count', $lang->formatNum( count( $users ) ) );

		$cache->set( $cachekey, $out->getHTML(), 3600 );
	}

	protected function languageCloud() {
		global $wgTranslateMessageNamespaces;

		$cache = wfGetCache( CACHE_ANYTHING );
		$cachekey = wfMemcKey( 'translate-supportedlanguages-language-cloud' );
		$data = $cache->get( $cachekey );
		if ( !$this->purge && is_array( $data ) ) {
			return $data;
		}

		$dbr = wfGetDB( DB_SLAVE );
		$tables = array( 'recentchanges' );
		$fields = array( 'substring_index(rc_title, \'/\', -1) as lang', 'count(*) as count' );
		$timestamp = $dbr->timestamp( TS_DB, wfTimeStamp( TS_UNIX ) - 60 * 60 * 24 * $this->period );
		$conds = array(
			'rc_title' . $dbr->buildLike( $dbr->anyString(), '/', $dbr->anyString() ),
			'rc_namespace' => $wgTranslateMessageNamespaces,
			'rc_timestamp > ' . $timestamp,
		);
		$options = array( 'GROUP BY' => 'lang', 'HAVING' => 'count > 20' );

		$res = $dbr->select( $tables, $fields, $conds, __METHOD__, $options );

		$data = array();
		foreach ( $res as $row ) {
			$data[$row->lang] = $row->count;
		}

		$cache->set( $cachekey, $data, 3600 );

		return $data;
	}

	protected function fetchTranslatorsAuto() {
		global $wgTranslateMessageNamespaces;

		$cache = wfGetCache( CACHE_ANYTHING );
		$cachekey = wfMemcKey( 'translate-supportedlanguages-translator-list' );
		$data = $cache->get( $cachekey );
		if ( !$this->purge && is_array( $data ) ) {
			return $data;
		}

		$dbr = wfGetDB( DB_SLAVE );
		$tables = array( 'page', 'revision' );
		$fields = array(
			'rev_user_text',
			'substring_index(page_title, \'/\', -1) as lang',
			'count(page_id) as count'
		);
		$conds = array(
			'page_title' . $dbr->buildLike( $dbr->anyString(), '/', $dbr->anyString() ),
			'page_namespace' => $wgTranslateMessageNamespaces,
			'page_id=rev_page',
		);
		$options = array( 'GROUP BY' => 'rev_user_text, lang' );

		$res = $dbr->select( $tables, $fields, $conds, __METHOD__, $options );

		$data = array();
		foreach ( $res as $row ) {
			$data[$row->lang][$row->rev_user_text] = $row->count;
		}

		$cache->set( $cachekey, $data, 3600 );

		return $data;
	}

	public function fetchTranslatorsPortal( $natives ) {
		$titles = array();
		foreach ( $natives as $code => $_ ) {
			$titles[] = Title::capitalize( $code, NS_PORTAL ) . '/translators';
		}

		$dbr = wfGetDB( DB_SLAVE );
		$tables = array( 'page', 'revision', 'text' );
		$vars = array_merge(
			Revision::selectTextFields(),
			array( 'page_title', 'page_namespace' ),
			Revision::selectFields()
		);
		$conds = array(
			'page_latest = rev_id',
			'rev_text_id = old_id',
			'page_namespace' => NS_PORTAL,
			'page_title' => $titles,
		);

		$res = $dbr->select( $tables, $vars, $conds, __METHOD__ );

		$users = array();
		$lb = new LinkBatch;

		foreach ( $res as $row ) {
			$rev = new Revision( $row );
			$text = $rev->getText();
			$code = strtolower( preg_replace( '!/translators$!', '', $row->page_title ) );

			preg_match_all( '!{{[Uu]ser\|([^}|]+)!', $text, $matches, PREG_SET_ORDER );
			foreach ( $matches as $match ) {
				$user = Title::capitalize( $match[1], NS_USER );
				$lb->add( NS_USER, $user );
				$lb->add( NS_USER_TALK, $user );
				if ( !isset( $users[$code] ) ) {
					$users[$code] = array();
				}
				$users[$code][strtr( $user, '_', ' ' )] = -1;
			}
		}

		$lb->execute();

		return $users;
	}

	protected function outputLanguageCloud( $names ) {
		$out = $this->getOutput();

		$langs = $this->languageCloud();
		$out->addHtml( '<div class="tagcloud">' );
		$langs = $this->shuffle_assoc( $langs );
		foreach ( $langs as $k => $v ) {
			$name = isset( $names[$k] ) ? $names[$k] : $k;
			$size = round( log( $v ) * 20 ) + 10;

			$params = array(
				'href' => "#$k",
				'class' => 'tag',
				'style' => "font-size:$size%",
				'lang' => $k,
			);

			$tag = Html::element( 'a', $params, $name );
			$out->addHtml( $tag . "\n" );
		}
		$out->addHtml( '</div>' );
	}

	protected function makeUserList( $users, $editcounts, $lastedits ) {
		$day = 60 * 60 * 24;

		// Scale of the activity colors, anything
		// longer than this is just inactive
		$period = $this->period;

		$links = array();
		$statsTable = new StatsTable();

		foreach ( $users as $username => $count ) {
			$title = Title::makeTitleSafe( NS_USER, $username );
			$enc = htmlspecialchars( $username );

			$attribs = array();
			$styles = array();
			if ( isset( $editcounts[$username] ) ) {
				if ( $count === -1 ) {
					$count = $editcounts[$username];
				}

				$styles['font-size'] = round( log( $count, 10 ) * 30 ) + 70 . '%';

				$last = wfTimestamp( TS_UNIX ) - $lastedits[$username];
				$last = round( $last / $day );
				$attribs['title'] = $this->msg( 'supportedlanguages-activity', $username )
					->numParams( $count, $last )->text();
				$last = max( 1, min( $period, $last ) );
				$styles['border-bottom'] = '3px solid #' .
					$statsTable->getBackgroundColor( $period - $last, $period );
			} else {
				$enc = "<del>$enc</del>";
			}

			$stylestr = $this->formatStyle( $styles );
			if ( $stylestr ) {
				$attribs['style'] = $stylestr;
			}

			$links[] = Linker::link( $title, $enc, $attribs );
		}

		$linkList = $this->getLanguage()->listToText( $links );
		$html = "<p class='mw-translate-spsl-translators'>";
		$html .= $this->msg(
			'supportedlanguages-translators',
			$linkList,
			count( $links )
		)->text();
		$html .= "</p>\n";
		$this->getOutput()->addHTML( $html );
	}

	protected function getUserStats() {
		$cache = wfGetCache( CACHE_ANYTHING );
		$cachekey = wfMemcKey( 'translate-supportedlanguages-userstats' );
		$data = $cache->get( $cachekey );
		if ( !$this->purge && is_array( $data ) ) {
			return $data;
		}

		$dbr = wfGetDB( DB_SLAVE );
		$editcounts = $lastedits = array();
		$tables = array( 'user', 'revision' );
		$fields = array( 'user_name', 'user_editcount', 'MAX(rev_timestamp) as lastedit' );
		$conds = array( 'user_id = rev_user' );
		$options = array( 'GROUP BY' => 'user_name' );

		$res = $dbr->select( $tables, $fields, $conds, __METHOD__, $options );
		foreach ( $res as $row ) {
			$editcounts[$row->user_name] = $row->user_editcount;
			$lastedits[$row->user_name] = wfTimestamp( TS_UNIX, $row->lastedit );
		}

		$data = array( $editcounts, $lastedits );
		$cache->set( $cachekey, $data, 3600 );

		return $data;
	}

	protected function formatStyle( $styles ) {
		$stylestr = '';
		foreach ( $styles as $key => $value ) {
			$stylestr .= "$key:$value;";
		}

		return $stylestr;
	}

	function shuffle_assoc( $list ) {
		if ( !is_array( $list ) ) {
			return $list;
		}

		$keys = array_keys( $list );
		shuffle( $keys );
		$random = array();
		foreach ( $keys as $key )
			$random[$key] = $list[$key];

		return $random;
	}

	protected function preQueryUsers( $users ) {
		$lb = new LinkBatch;
		foreach ( $users as $translators ) {
			foreach ( $translators as $user => $count ) {
				$user = Title::capitalize( $user, NS_USER );
				$lb->add( NS_USER, $user );
				$lb->add( NS_USER_TALK, $user );
			}
		}
		$lb->execute();
	}

	protected function getColorLegend() {
		$legend = '';
		$period = $this->period;
		$statsTable = new StatsTable();

		for ( $i = 0; $i <= $period; $i += 30 ) {
			$iFormatted = htmlspecialchars( $this->getLanguage()->formatNum( $i ) );
			$legend .= '<span style="background-color:#' .
				$statsTable->getBackgroundColor( $period - $i, $period ) .
				"\"> $iFormatted</span>";
		}

		return $legend;
	}
}
