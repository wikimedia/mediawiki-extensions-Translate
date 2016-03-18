<?php
/**
 * Contains logic for special page Special:SupportedLanguages
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
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

	protected function getGroupName() {
		return 'wiki';
	}

	function getDescription() {
		return $this->msg( 'supportedlanguages' )->text();
	}

	public function execute( $par ) {
		$out = $this->getOutput();
		$lang = $this->getLanguage();

		// Only for manual debugging nowdays
		$this->purge = false;

		$this->setHeaders();
		$out->addModules( 'ext.translate.special.supportedlanguages' );

		$out->addHelpLink(
			'Help:Extension:Translate/Statistics_and_reporting#List_of_languages_and_translators'
		);

		$this->outputHeader( 'supportedlanguages-summary' );
		$dbr = wfGetDB( DB_SLAVE );
		if ( $dbr->getType() === 'sqlite' ) {
			$out->addWikiText( '<div class=errorbox>SQLite is not supported.</div>' );

			return;
		}

		$out->addWikiMsg( 'supportedlanguages-localsummary' );

		$names = Language::fetchLanguageNames( null, 'all' );
		$languages = $this->languageCloud();
		// There might be all sorts of subpages which are not languages
		$languages = array_intersect_key( $languages, $names );

		$this->outputLanguageCloud( $languages, $names );
		$out->addWikiMsg( 'supportedlanguages-count', $lang->formatNum( count( $languages ) ) );

		if ( $par && Language::isKnownLanguageTag( $par ) ) {
			$code = $par;

			$out->addWikiMsg( 'supportedlanguages-colorlegend', $this->getColorLegend() );

			$users = $this->fetchTranslators( $code );
			if ( $users === false ) {
				// generic-pool-error is from MW core
				$out->wrapWikiMsg( '<div class="warningbox">$1</div>', 'generic-pool-error' );
				return;
			}

			global $wgTranslateAuthorBlacklist;
			$users = $this->filterUsers( $users, $code, $wgTranslateAuthorBlacklist );
			$this->preQueryUsers( $users );
			$this->showLanguage( $code, $users );
		}
	}

	protected function showLanguage( $code, $users ) {
		$out = $this->getOutput();
		$lang = $this->getLanguage();

		$usernames = array_keys( $users );
		$userStats = $this->getUserStats( $usernames );

		// Information to be used inside the foreach loop.
		$linkInfo = array();
		$linkInfo['rc']['title'] = SpecialPage::getTitleFor( 'Recentchanges' );
		$linkInfo['rc']['msg'] = $this->msg( 'supportedlanguages-recenttranslations' )->escaped();
		$linkInfo['stats']['title'] = SpecialPage::getTitleFor( 'LanguageStats' );
		$linkInfo['stats']['msg'] = $this->msg( 'languagestats' )->escaped();

		$local = Language::fetchLanguageName( $code, $lang->getCode(), 'all' );
		$native = Language::fetchLanguageName( $code, null, 'all' );

		if ( $local !== $native ) {
			$headerText = $this->msg( 'supportedlanguages-portallink' )
				->params( $code, $local, $native )->escaped();
		} else {
			// No CLDR, so a less localised header and link title.
			$headerText = $this->msg( 'supportedlanguages-portallink-nocldr' )
				->params( $code, $native )->escaped();
		}

		$out->addHTML( Html::rawElement( 'h2', array( 'id' => $code ), $headerText ) );

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
				'trailer' => '/' . $code
			),
			array( 'known', 'noclasses' )
		);
		$linkList = $lang->listToText( $links );

		$out->addHTML( '<p>' . $linkList . "</p>\n" );
		$this->makeUserList( $users, $userStats );
	}

	protected function languageCloud() {
		global $wgTranslateMessageNamespaces;

		$cache = wfGetCache( CACHE_ANYTHING );
		$cachekey = wfMemcKey( 'translate-supportedlanguages-language-cloud' );
		if ( $this->purge ) {
			$cache->delete( $cachekey );
		} else {
			$data = $cache->get( $cachekey );
			if ( is_array( $data ) ) {
				return $data;
			}
		}

		$dbr = wfGetDB( DB_SLAVE );
		$tables = array( 'recentchanges' );
		$fields = array( 'substring_index(rc_title, \'/\', -1) as lang', 'count(*) as count' );
		$timestamp = $dbr->timestamp( wfTimestamp( TS_UNIX ) - 60 * 60 * 24 * $this->period );
		$conds = array(
			# Without the quotes the rc_timestamp index isn't used and this query is much slower
			"rc_timestamp > '$timestamp'",
			'rc_namespace' => $wgTranslateMessageNamespaces,
			'rc_title' . $dbr->buildLike( $dbr->anyString(), '/', $dbr->anyString() ),
		);
		$options = array( 'GROUP BY' => 'lang', 'HAVING' => 'count > 20', 'ORDER BY' => 'NULL' );

		$res = $dbr->select( $tables, $fields, $conds, __METHOD__, $options );

		$data = array();
		foreach ( $res as $row ) {
			$data[$row->lang] = $row->count;
		}

		$cache->set( $cachekey, $data, 3600 );

		return $data;
	}

	/**
	 * Fetch the translators for a language with caching
	 *
	 * @param string $code
	 * @return array|bool Map of (user name => page count) or false on failure
	 */
	public function fetchTranslators( $code ) {
		$cache = wfGetCache( CACHE_ANYTHING );
		$cachekey = wfMemcKey( 'translate-supportedlanguages-translator-list-v1', $code );

		if ( $this->purge ) {
			$cache->delete( $cachekey );
			$data = false;
		} else {
			$staleCutoffUnix = time() - 3600;
			$data = $cache->get( $cachekey );
			if ( is_array( $data ) && $data['asOfTime'] > $staleCutoffUnix ) {
				return $data['users'];
			}
		}

		$that = $this;
		$work = new PoolCounterWorkViaCallback(
			'TranslateFetchTranslators',
			"TranslateFetchTranslators-$code",
			array(
				'doWork' => function () use ( $that, $code, $cache, $cachekey ) {
					$users = $that->loadTranslators( $code );
					$newData = array( 'users' => $users, 'asOfTime' => time() );
					$cache->set( $cachekey, $newData, 86400 );
					return $users;
				},
				'doCachedWork' => function () use ( $cache, $cachekey ) {
					$newData = $cache->get( $cachekey );
					// Use new cache value from other thread
					return is_array( $newData ) ? $newData['users'] : false;
				},
				'fallback' => function () use ( $data ) {
					// Use stale cache if possible
					return is_array( $data ) ? $data['users'] : false;
				}
			)
		);

		return $work->execute();
	}

	/**
	 * Fetch the translators for a language
	 *
	 * @param string $code
	 * @return array Map of (user name => page count)
	 */
	public function loadTranslators( $code ) {
		global $wgTranslateMessageNamespaces;

		$dbr = wfGetDB( DB_SLAVE, 'vslow' );
		$tables = array( 'page', 'revision' );
		$fields = array(
			'rev_user_text',
			'count(page_id) as count'
		);
		$conds = array(
			'page_title' . $dbr->buildLike( $dbr->anyString(), '/', $code ),
			'page_namespace' => $wgTranslateMessageNamespaces,
			'page_id=rev_page',
		);
		$options = array( 'GROUP BY' => 'rev_user_text', 'ORDER BY' => 'NULL' );

		$res = $dbr->select( $tables, $fields, $conds, __METHOD__, $options );

		$data = array();
		foreach ( $res as $row ) {
			$data[$row->rev_user_text] = $row->count;
		}

		return $data;
	}

	protected function filterUsers( array $users, $code, $blacklist ) {
		foreach ( array_keys( $users ) as $username ) {
			# We do not know the group
			$hash = "#;$code;$username";

			$blacklisted = false;
			foreach ( $blacklist as $rule ) {
				list( $type, $regex ) = $rule;

				if ( preg_match( $regex, $hash ) ) {
					if ( $type === 'white' ) {
						$blacklisted = false;
						break;
					} else {
						$blacklisted = true;
					}
				}
			}

			if ( $blacklisted ) {
				unset( $users[$username] );
			}
		}

		return $users;
	}

	protected function outputLanguageCloud( array $languages, array $names ) {
		$out = $this->getOutput();

		$out->addHTML( '<div class="tagcloud autonym">' );

		foreach ( $languages as $k => $v ) {
			$name = $names[$k];
			$size = round( log( $v ) * 20 ) + 10;

			$params = array(
				'href' => $this->getPageTitle( $k )->getLocalURL(),
				'class' => 'tag',
				'style' => "font-size:$size%",
				'lang' => $k,
			);

			$tag = Html::element( 'a', $params, $name );
			$out->addHTML( $tag . "\n" );
		}
		$out->addHTML( '</div>' );
	}

	protected function makeUserList( $users, $stats ) {
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
			if ( isset( $stats[$username][0] ) ) {
				if ( $count === -1 ) {
					$count = $stats[$username][0];
				}

				$styles['font-size'] = round( log( $count, 10 ) * 30 ) + 70 . '%';

				$last = wfTimestamp( TS_UNIX ) - wfTimestamp( TS_UNIX, $stats[$username][1] );
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

		// for GENDER support
		$username = '';
		if ( count( $users ) === 1 ) {
			$keys = array_keys( $users );
			$username = $keys[0];
		}

		$linkList = $this->getLanguage()->listToText( $links );
		$html = "<p class='mw-translate-spsl-translators'>";
		$html .= $this->msg( 'supportedlanguages-translators' )
			->rawParams( $linkList )
			->numParams( count( $links ) )
			->params( $username )
			->escaped();
		$html .= "</p>\n";
		$this->getOutput()->addHTML( $html );
	}

	protected function getUserStats( $users ) {
		$cache = wfGetCache( CACHE_ANYTHING );
		$dbr = wfGetDB( DB_SLAVE );
		$keys = array();

		foreach ( $users as $username ) {
			$keys[] = wfMemcKey( 'translate', 'sl-usertats', $username );
		}

		$cached = $cache->getMulti( $keys );
		$data = array();

		foreach ( $users as $index => $username ) {
			$cachekey = $keys[$index];

			if ( !$this->purge && isset( $cached[$cachekey] ) ) {
				$data[$username] = $cached[$cachekey];
				continue;
			}

			$tables = array( 'user', 'revision' );
			$fields = array( 'user_name', 'user_editcount', 'MAX(rev_timestamp) as lastedit' );
			$conds = array(
				'user_name' => $username,
				'user_id = rev_user',
			);

			$res = $dbr->selectRow( $tables, $fields, $conds, __METHOD__ );
			$data[$username] = array( $res->user_editcount, $res->lastedit );

			$cache->set( $cachekey, $data[$username], 3600 );
		}

		return $data;
	}

	protected function formatStyle( $styles ) {
		$stylestr = '';
		foreach ( $styles as $key => $value ) {
			$stylestr .= "$key:$value;";
		}

		return $stylestr;
	}

	protected function preQueryUsers( $users ) {
		$lb = new LinkBatch;
		foreach ( $users as $user => $count ) {
			$user = Title::capitalize( $user, NS_USER );
			$lb->add( NS_USER, $user );
			$lb->add( NS_USER_TALK, $user );
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
