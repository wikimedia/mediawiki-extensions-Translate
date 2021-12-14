<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use Config;
use Html;
use HtmlArmor;
use InvalidArgumentException;
use Language;
use LanguageCode;
use LinkBatch;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\Translate\Utilities\ConfigHelper;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Logger\LoggerFactory;
use ObjectCache;
use SpecialPage;
use StatsTable;
use Title;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * This special page shows active languages and active translators per language.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @ingroup SpecialPage TranslateSpecialPage Stats
 */
class ActiveLanguagesSpecialPage extends SpecialPage {
	/** @var ServiceOptions */
	private $options;
	/** @var TranslatorActivity */
	private $translatorActivity;
	/** @var LanguageNameUtils */
	private $langNameUtils;
	/** @var ILoadBalancer */
	private $loadBalancer;
	/** @var ConfigHelper */
	private $configHelper;
	/** @var Language */
	private $contentLanguage;
	/** @var ProgressStatsTableFactory */
	private $progressStatsTableFactory;
	/** @var StatsTable */
	private $progressStatsTable;
	/** @var int Cutoff time for inactivity in days */
	private $period = 180;

	public const CONSTRUCTOR_OPTIONS = [
		'TranslateMessageNamespaces',
	];

	public function __construct(
		Config $config,
		TranslatorActivity $translatorActivity,
		LanguageNameUtils $langNameUtils,
		ILoadBalancer $loadBalancer,
		ConfigHelper $configHelper,
		Language $contentLanguage,
		ProgressStatsTableFactory $progressStatsTableFactory
	) {
		parent::__construct( 'SupportedLanguages' );
		$this->options = new ServiceOptions( self::CONSTRUCTOR_OPTIONS, $config );
		$this->translatorActivity = $translatorActivity;
		$this->langNameUtils = $langNameUtils;
		$this->loadBalancer = $loadBalancer;
		$this->configHelper = $configHelper;
		$this->contentLanguage = $contentLanguage;
		$this->progressStatsTableFactory = $progressStatsTableFactory;
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
		$this->progressStatsTable = $this->progressStatsTableFactory->newFromContext( $this->getContext() );

		$this->setHeaders();
		$out->addModuleStyles( 'ext.translate.specialpages.styles' );

		$out->addHelpLink(
			'Help:Extension:Translate/Statistics_and_reporting#List_of_languages_and_translators'
		);

		$this->outputHeader( 'supportedlanguages-summary' );
		$dbr = $this->loadBalancer->getConnectionRef( DB_REPLICA );
		if ( $dbr->getType() === 'sqlite' ) {
			$out->wrapWikiMsg(
				'<div class="errorbox">$1</div>',
				'supportedlanguages-sqlite-error'
			);
			return;
		}

		$out->addWikiMsg( 'supportedlanguages-localsummary' );

		$names = $this->langNameUtils->getLanguageNames( null, 'all' );
		$languages = $this->languageCloud();
		// There might be all sorts of subpages which are not languages
		$languages = array_intersect_key( $languages, $names );

		$this->outputLanguageCloud( $languages, $names );
		$out->addWikiMsg( 'supportedlanguages-count', $lang->formatNum( count( $languages ) ) );

		if ( !$par ) {
			return;
		}

		// Convert formatted language tag like zh-Hant to internal format like zh-hant
		$language = strtolower( $par );
		try {
			$data = $this->translatorActivity->inLanguage( $language );
		} catch ( StatisticsUnavailable $e ) {
			// generic-pool-error is from MW core
			$out->addHTML( Html::errorBox( $this->msg( 'generic-pool-error' )->parse() ) );
			return;
		} catch ( InvalidArgumentException $e ) {
			$errorMessageHtml = $this->msg( 'translate-activelanguages-invalid-code' )
				->params( LanguageCode::bcp47( $language ) )
				->parse();
			$out->addHTML( Html::errorBox( $errorMessageHtml ) );
			return;
		}

		$users = $data['users'];
		$users = $this->filterUsers( $users, $language );
		$this->preQueryUsers( $users );
		$this->showLanguage( $language, $users, (int)$data['asOfTime'] );
	}

	private function showLanguage( string $code, array $users, int $cachedAt ): void {
		$out = $this->getOutput();
		$lang = $this->getLanguage();
		$bcp47Code = LanguageCode::bcp47( $code );

		// Information to be used inside the foreach loop.
		$linkInfo = [];
		$linkInfo['rc']['title'] = SpecialPage::getTitleFor( 'Recentchanges' );
		$linkInfo['rc']['msg'] = $this->msg( 'supportedlanguages-recenttranslations' )->text();
		$linkInfo['stats']['title'] = SpecialPage::getTitleFor( 'LanguageStats' );
		$linkInfo['stats']['msg'] = $this->msg( 'languagestats' )->text();

		$local = $this->langNameUtils->getLanguageName( $code, $lang->getCode(), 'all' );
		$native = $this->langNameUtils->getLanguageName( $code, null, 'all' );

		if ( $local !== $native ) {

			$headerText = $this->msg( 'supportedlanguages-portallink' )
				->params( $bcp47Code, $local, $native )->escaped();
		} else {
			// No CLDR, so a less localised header and link title.
			$headerText = $this->msg( 'supportedlanguages-portallink-nocldr' )
				->params( $bcp47Code, $native )->escaped();
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

		$ageString = $this->getLanguage()->formatTimePeriod(
			time() - $cachedAt,
			[ 'noabbrevs' => true, 'avoid' => 'avoidseconds' ]
		);
		$out->addWikiMsg( 'supportedlanguages-colorlegend', $this->getColorLegend() );
		$out->addWikiMsg( 'translate-supportedlanguages-cached', $ageString );
	}

	private function languageCloud(): array {
		// TODO: Inject a factory when such a thing is available in MediaWiki core
		$cache = ObjectCache::getInstance( CACHE_ANYTHING );
		$cachekey = $cache->makeKey( 'translate-supportedlanguages-language-cloud', 'v2' );

		$data = $cache->get( $cachekey );
		if ( is_array( $data ) ) {
			return $data;
		}

		$dbr = $this->loadBalancer->getConnectionRef( DB_REPLICA );
		$tables = [ 'recentchanges' ];
		$fields = [ 'substring_index(rc_title, \'/\', -1) as lang', 'count(*) as count' ];
		$timestamp = $dbr->timestamp( wfTimestamp( TS_UNIX ) - 60 * 60 * 24 * $this->period );
		$conds = [
			# Without the quotes the rc_timestamp index isn't used and this query is much slower
			"rc_timestamp > '$timestamp'",
			'rc_namespace' => $this->options->get( 'TranslateMessageNamespaces' ),
			'rc_title' . $dbr->buildLike( $dbr->anyString(), '/', $dbr->anyString() ),
		];
		$options = [ 'GROUP BY' => 'lang', 'HAVING' => 'count > 20', 'ORDER BY' => 'NULL' ];

		$res = $dbr->select( $tables, $fields, $conds, __METHOD__, $options );

		$data = [];
		foreach ( $res as $row ) {
			$data[$row->lang] = (int)$row->count;
		}

		$cache->set( $cachekey, $data, 3600 );

		return $data;
	}

	protected function filterUsers( array $users, string $code ): array {
		foreach ( $users as $index => $user ) {
			$username = $user[TranslatorActivityQuery::USER_NAME];
			// We do not know the group
			if ( $this->configHelper->isAuthorExcluded( '#', $code, $username ) ) {
				unset( $users[$index] );
			}
		}

		return $users;
	}

	protected function outputLanguageCloud( array $languages, array $names ) {
		global $wgTranslateDocumentationLanguageCode;

		$out = $this->getOutput();

		$out->addHTML( '<div class="tagcloud autonym">' );

		foreach ( $languages as $k => $v ) {
			$name = $names[$k];
			$langAttribute = $k;
			$size = round( log( $v ) * 20 ) + 10;

			if ( $langAttribute === $wgTranslateDocumentationLanguageCode ) {
				$langAttribute = $this->contentLanguage->getHtmlCode();
			}

			$params = [
				'href' => $this->getPageTitle( $k )->getLocalURL(),
				'class' => 'tag',
				'style' => "font-size:$size%",
				'lang' => $langAttribute,
			];

			$tag = Html::element( 'a', $params, $name );
			$out->addHTML( $tag . "\n" );
		}
		$out->addHTML( '</div>' );
	}

	private function makeUserList( array $userStats ): void {
		$day = 60 * 60 * 24;

		// Scale of the activity colors, anything
		// longer than this is just inactive
		$period = $this->period;

		$links = [];
		// List users in descending order by number of translations in this language
		usort( $userStats, static function ( $a, $b ) {
			return -(
				$a[TranslatorActivityQuery::USER_TRANSLATIONS]
				<=>
				$b[TranslatorActivityQuery::USER_TRANSLATIONS]
			);
		} );

		foreach ( $userStats as $stats ) {
			$username = $stats[TranslatorActivityQuery::USER_NAME];
			$title = Title::makeTitleSafe( NS_USER, $username );
			if ( !$title ) {
				LoggerFactory::getInstance( 'Translate' )->warning(
					"T248125: Got Title-invalid username '{username}'",
					[ 'username' => $username ]
				);
				continue;
			}

			$count = $stats[TranslatorActivityQuery::USER_TRANSLATIONS];
			$lastTranslationTimestamp = $stats[TranslatorActivityQuery::USER_LAST_ACTIVITY];

			$enc = htmlspecialchars( $username );

			$attribs = [];
			$styles = [];
			$styles['font-size'] = round( log( $count, 10 ) * 30 ) + 70 . '%';

			$last = wfTimestamp( TS_UNIX ) - wfTimestamp( TS_UNIX, $lastTranslationTimestamp );
			$last = round( $last / $day );
			$attribs['title'] =
				$this->msg( 'supportedlanguages-activity', $username )
					->numParams( $count, $last )
					->text();
			$last = max( 1, min( $period, $last ) );
			$styles['border-bottom'] =
				'3px solid #' . $this->progressStatsTable->getBackgroundColor( ( $period - $last ) / $period );

			$stylestr = $this->formatStyle( $styles );
			if ( $stylestr ) {
				$attribs['style'] = $stylestr;
			}

			$links[] =
				$this->getLinkRenderer()->makeLink( $title, new HtmlArmor( $enc ), $attribs );
		}

		// for GENDER support
		$usernameForGender = '';
		if ( count( $userStats ) === 1 ) {
			$usernameForGender = $userStats[0][TranslatorActivityQuery::USER_NAME];
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
		$lb = new LinkBatch();
		foreach ( $users as $data ) {
			$username = $data[TranslatorActivityQuery::USER_NAME];
			$user = Title::capitalize( $username, NS_USER );
			$lb->add( NS_USER, $user );
			$lb->add( NS_USER_TALK, $user );
		}
		$lb->execute();
	}

	protected function getColorLegend() {
		$legend = '';
		$period = $this->period;

		for ( $i = 0; $i <= $period; $i += 30 ) {
			$iFormatted = htmlspecialchars( $this->getLanguage()->formatNum( $i ) );
			$legend .= '<span style="background-color:#' .
				$this->progressStatsTable->getBackgroundColor( ( $period - $i ) / $period ) .
				"\"> $iFormatted</span>";
		}

		return $legend;
	}
}
