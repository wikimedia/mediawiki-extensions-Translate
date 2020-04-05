<?php
/**
 * Contains logic for special page Special:SupportedLanguages
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 */

use MediaWiki\Logger\LoggerFactory;

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
	private const USER_TRANSLATIONS = 0;
	private const USER_LAST_ACTIVITY = 1;

	/// Whether to skip and regenerate caches
	protected $purge = false;

	/// Cutoff time for inactivity in days
	protected $period = 180;

	public function __construct() {
		parent::__construct( 'SupportedLanguages' );
	}

	protected function getGroupName() {
		return 'translation';
	}

	public function getDescription() {
		return $this->msg( 'supportedlanguages' )->text();
	}

	public function execute( $par ) {
		$out = $this->getOutput();
		$lang = $this->getLanguage();

		// Only for manual debugging nowdays
		$this->purge = false;

		$this->setHeaders();
		$out->addModules( 'ext.translate.special.supportedlanguages' );
		$out->addModuleStyles( 'ext.translate.special.supportedlanguages' );

		$out->addHelpLink(
			'Help:Extension:Translate/Statistics_and_reporting#List_of_languages_and_translators'
		);

		$this->outputHeader( 'supportedlanguages-summary' );
		$dbr = wfGetDB( DB_REPLICA );
		if ( $dbr->getType() === 'sqlite' ) {
			$out->wrapWikiMsg(
				'<div class="errorbox">$1</div>',
				'supportedlanguages-sqlite-error'
			);
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
			if ( !$users ) {
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

	protected function showLanguage( string $code, array $users ): void {
		$out = $this->getOutput();
		$lang = $this->getLanguage();

		// Information to be used inside the foreach loop.
		$linkInfo = [];
		$linkInfo['rc']['title'] = SpecialPage::getTitleFor( 'Recentchanges' );
		$linkInfo['rc']['msg'] = $this->msg( 'supportedlanguages-recenttranslations' )->text();
		$linkInfo['stats']['title'] = SpecialPage::getTitleFor( 'LanguageStats' );
		$linkInfo['stats']['msg'] = $this->msg( 'languagestats' )->text();

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

		$out->addHTML( Html::rawElement( 'h2', [ 'id' => $code ], $headerText ) );

		// Add useful links for language stats and recent changes for the language.
		$links = [];
		$links[] = $this->getLinkRenderer()->makeKnownLink(
			$linkInfo['stats']['title'],
			$linkInfo['stats']['msg'],
			[],
			[
				'code' => $code,
				'suppresscomplete' => '1'
			]
		);
		$links[] = $this->getLinkRenderer()->makeKnownLink(
			$linkInfo['rc']['title'],
			$linkInfo['rc']['msg'],
			[],
			[
				'translations' => 'only',
				'trailer' => '/' . $code
			]
		);
		$linkList = $lang->listToText( $links );

		$out->addHTML( '<p>' . $linkList . "</p>\n" );
		$this->makeUserList( $users );
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

		$dbr = wfGetDB( DB_REPLICA );
		$tables = [ 'recentchanges' ];
		$fields = [ 'substring_index(rc_title, \'/\', -1) as lang', 'count(*) as count' ];
		$timestamp = $dbr->timestamp( wfTimestamp( TS_UNIX ) - 60 * 60 * 24 * $this->period );
		$conds = [
			# Without the quotes the rc_timestamp index isn't used and this query is much slower
			"rc_timestamp > '$timestamp'",
			'rc_namespace' => $wgTranslateMessageNamespaces,
			'rc_title' . $dbr->buildLike( $dbr->anyString(), '/', $dbr->anyString() ),
		];
		$options = [ 'GROUP BY' => 'lang', 'HAVING' => 'count > 20', 'ORDER BY' => 'NULL' ];

		$res = $dbr->select( $tables, $fields, $conds, __METHOD__, $options );

		$data = [];
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
	 * @return ?array Map of (user name => translation stats) or null on failure
	 */
	public function fetchTranslators( string $code ): ?array {
		$cache = wfGetCache( CACHE_ANYTHING );
		$cachekey = wfMemcKey( 'translate-supportedlanguages-translator-list-v2', $code );

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
			[
				'doWork' => function () use ( $that, $code, $cache, $cachekey ) {
					$users = $that->loadTranslators( $code );
					$newData = [ 'users' => $users, 'asOfTime' => time() ];
					$cache->set( $cachekey, $newData, 86400 );
					return $users;
				},
				'doCachedWork' => function () use ( $cache, $cachekey ) {
					$newData = $cache->get( $cachekey );
					// Use new cache value from other thread
					return is_array( $newData ) ? $newData['users'] : null;
				},
				'fallback' => function () use ( $data ) {
					// Use stale cache if possible
					return is_array( $data ) ? $data['users'] : null;
				}
			]
		);

		return $work->execute();
	}

	/**
	 * Fetch the translators for a language
	 *
	 * @param string $code
	 * @return array Map of user name to translation stats
	 */
	public function loadTranslators( string $code ): array {
		global $wgTranslateMessageNamespaces;

		$dbr = wfGetDB( DB_REPLICA, 'vslow' );

		$actorQuery = ActorMigration::newMigration()->getJoin( 'rev_user' );

		$tables = [ 'page', 'revision' ] + $actorQuery['tables'];
		$fields = [
			'rev_user_text' => $actorQuery['fields']['rev_user_text'],
			'MAX(rev_timestamp) as lastedit',
			'count(page_id) as count'
		];
		$conds = [
			'page_title' . $dbr->buildLike( $dbr->anyString(), '/', $code ),
			'page_namespace' => $wgTranslateMessageNamespaces,
		];
		$options = [ 'GROUP BY' => $actorQuery['fields']['rev_user_text'], 'ORDER BY' => 'NULL' ];
		$joins = [
			'revision' => [ 'JOIN', 'page_id=rev_page' ],
		] + $actorQuery['joins'];

		$res = $dbr->select( $tables, $fields, $conds, __METHOD__, $options, $joins );

		$data = [];
		foreach ( $res as $row ) {
			$data[$row->rev_user_text] = [
				self::USER_TRANSLATIONS => $row->count,
				self::USER_LAST_ACTIVITY => $row->lastedit,
			];
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

			$params = [
				'href' => $this->getPageTitle( $k )->getLocalURL(),
				'class' => 'tag',
				'style' => "font-size:$size%",
				'lang' => $k,
			];

			$tag = Html::element( 'a', $params, $name );
			$out->addHTML( $tag . "\n" );
		}
		$out->addHTML( '</div>' );
	}

	protected function makeUserList( array $userStats ): void {
		$day = 60 * 60 * 24;

		// Scale of the activity colors, anything
		// longer than this is just inactive
		$period = $this->period;

		$links = [];
		$statsTable = new StatsTable();

		// List users in descending order by number of translations in this language
		uasort( $userStats, function ( $a, $b ) {
			return -( $a[self::USER_TRANSLATIONS] <=> $b[self::USER_TRANSLATIONS] );
		} );

		foreach ( $userStats as $username => $stats ) {
			$title = Title::makeTitleSafe( NS_USER, $username );
			if ( !$title ) {
				LoggerFactory::getInstance( 'Translate' )->warning(
					"T248125: Got Title-invalid username '{username}'",
					[ 'username' => $username ]
				);
				continue;
			}

			$count = $stats[self::USER_TRANSLATIONS];
			$lastTranslationTimestamp = $stats[self::USER_LAST_ACTIVITY];

			$enc = htmlspecialchars( $username );

			$attribs = [];
			$styles = [];
			$styles['font-size'] = round( log( $count, 10 ) * 30 ) + 70 . '%';

			$last = wfTimestamp( TS_UNIX ) - wfTimestamp( TS_UNIX, $lastTranslationTimestamp );
			$last = round( $last / $day );
			$attribs['title'] = $this->msg( 'supportedlanguages-activity', $username )
				->numParams( $count, $last )->text();
			$last = max( 1, min( $period, $last ) );
			$styles['border-bottom'] = '3px solid #' .
				$statsTable->getBackgroundColor( ( $period - $last ) / $period );

			$stylestr = $this->formatStyle( $styles );
			if ( $stylestr ) {
				$attribs['style'] = $stylestr;
			}

			$links[] = $this->getLinkRenderer()->makeLink( $title, new HtmlArmor( $enc ), $attribs );
		}

		// for GENDER support
		$usernameForGender = '';
		if ( count( $userStats ) === 1 ) {
			$usernameForGender = array_key_first( $userStats );
		}

		$linkList = $this->getLanguage()->listToText( $links );
		$html = "<p class='mw-translate-spsl-translators'>";
		$html .= $this->msg( 'supportedlanguages-translators' )
			->rawParams( $linkList )
			->numParams( count( $links ) )
			->params( $usernameForGender )
			->escaped();
		$html .= "</p>\n";
		$this->getOutput()->addHTML( $html );
	}

	protected function formatStyle( $styles ) {
		$stylestr = '';
		foreach ( $styles as $key => $value ) {
			$stylestr .= "$key:$value;";
		}

		return $stylestr;
	}

	protected function preQueryUsers( array $users ): void {
		$lb = new LinkBatch;
		foreach ( $users as $user => $data ) {
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
				$statsTable->getBackgroundColor( ( $period - $i ) / $period ) .
				"\"> $iFormatted</span>";
		}

		return $legend;
	}
}
