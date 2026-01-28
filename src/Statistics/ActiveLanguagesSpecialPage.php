<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use InvalidArgumentException;
use MediaWiki\Config\Config;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\Translate\LogNames;
use MediaWiki\Extension\Translate\Utilities\ConfigHelper;
use MediaWiki\Html\Html;
use MediaWiki\Language\Language;
use MediaWiki\Language\LanguageCode;
use MediaWiki\Languages\LanguageFactory;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\Page\LinkBatchFactory;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;
use ObjectCacheFactory;
use Wikimedia\HtmlArmor\HtmlArmor;
use Wikimedia\ObjectCache\BagOStuff;
use Wikimedia\Rdbms\IConnectionProvider;

/**
 * This special page shows active languages and active translators per language.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @ingroup SpecialPage TranslateSpecialPage Stats
 */
class ActiveLanguagesSpecialPage extends SpecialPage {
	private readonly ServiceOptions $options;
	private readonly BagOStuff $cache;
	private StatsTable $progressStatsTable;
	/** @var int Cutoff time for inactivity in days */
	private const PERIOD = 180;

	public const CONSTRUCTOR_OPTIONS = [
		'TranslateMessageNamespaces',
	];

	public function __construct(
		private readonly TranslatorActivity $translatorActivity,
		private readonly LanguageNameUtils $langNameUtils,
		private readonly IConnectionProvider $dbProvider,
		private readonly ConfigHelper $configHelper,
		private readonly Language $contentLanguage,
		private readonly ProgressStatsTableFactory $progressStatsTableFactory,
		private readonly LinkBatchFactory $linkBatchFactory,
		private readonly LanguageFactory $languageFactory,
		Config $config,
		ObjectCacheFactory $objectCacheFactory,
	) {
		parent::__construct( 'SupportedLanguages' );
		$this->options = new ServiceOptions( self::CONSTRUCTOR_OPTIONS, $config );
		$this->cache = $objectCacheFactory->getInstance( CACHE_ANYTHING );
	}

	/** @inheritDoc */
	protected function getGroupName(): string {
		return 'translation';
	}

	/** @inheritDoc */
	public function getDescription() {
		return $this->msg( 'supportedlanguages' );
	}

	/** @inheritDoc */
	public function execute( $par ): void {
		$out = $this->getOutput();
		$lang = $this->getLanguage();
		$this->progressStatsTable = $this->progressStatsTableFactory->newFromContext( $this->getContext() );

		$this->setHeaders();
		$out->addModuleStyles( [
			'ext.translate.specialpages.styles',
			'mediawiki.codex.messagebox.styles',
		] );

		$out->addHelpLink(
			'Help:Extension:Translate/Statistics_and_reporting#List_of_languages_and_translators'
		);

		$this->outputHeader( 'supportedlanguages-summary' );
		$dbr = $this->dbProvider->getReplicaDatabase();
		$dbType = $dbr->getType();
		if ( $dbType === 'sqlite' || $dbType === 'postgres' ) {
			$out->addHTML(
				Html::errorBox(
					// Messages used: supportedlanguages-sqlite-error, supportedlanguages-postgres-error
					$out->msg( 'supportedlanguages-' . $dbType . '-error' )->parse()
				)
			);
			return;
		}

		$out->addWikiMsg( 'supportedlanguages-localsummary' );

		$names = $this->langNameUtils->getLanguageNames( LanguageNameUtils::AUTONYMS, LanguageNameUtils::ALL );
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
		} catch ( StatisticsUnavailable ) {
			// generic-pool-error is from MW core
			$out->addHTML( Html::errorBox( $this->msg( 'generic-pool-error' )->parse() ) );
			return;
		} catch ( InvalidArgumentException ) {
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

		$local = $this->langNameUtils->getLanguageName( $code, $lang->getCode() );
		$native = $this->langNameUtils->getLanguageName( $code );
		$statLanguage = $this->languageFactory->getLanguage( $code );
		$bcp47Code = $statLanguage->getHtmlCode();

		$span = Html::rawElement( 'span', [ 'lang' => $bcp47Code, 'dir' => $statLanguage->getDir() ], $native );

		$headerText = $local !== $native
			? $this->msg( 'supportedlanguages-portallink', $bcp47Code, $local, $span )->parse()
			// No CLDR, so a less localised header and link title
			: $this->msg( 'supportedlanguages-portallink-nocldr', $bcp47Code, $span )->parse();

		$out->addHTML( Html::rawElement( 'h2', [ 'id' => $code ], $headerText ) );

		// Add useful links for language stats and recent changes for the language.
		$links = [];
		$links[] = $this->getLinkRenderer()->makeKnownLink(
			SpecialPage::getTitleValueFor( 'LanguageStats' ),
			$this->msg( 'languagestats' )->text(),
			[],
			[
				'code' => $code,
				'suppresscomplete' => '1'
			]
		);
		$links[] = $this->getLinkRenderer()->makeKnownLink(
			SpecialPage::getTitleValueFor( 'Recentchanges' ),
			$this->msg( 'supportedlanguages-recenttranslations' )->text(),
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
		$cacheKey = $this->cache->makeKey( 'translate-supportedlanguages-language-cloud', 'v2' );

		$data = $this->cache->get( $cacheKey );
		if ( is_array( $data ) ) {
			return $data;
		}

		$dbr = $this->dbProvider->getReplicaDatabase();
		$timestamp = $dbr->timestamp( (int)wfTimestamp() - 60 * 60 * 24 * self::PERIOD );

		$res = $dbr->newSelectQueryBuilder()
			->select( [ 'lang' => 'substring_index(rc_title, \'/\', -1)', 'count' => 'COUNT(*)' ] )
			->from( 'recentchanges' )
			->where( [
				'rc_timestamp > ' . $dbr->addQuotes( $timestamp ),
				'rc_namespace' => $this->options->get( 'TranslateMessageNamespaces' ),
				'rc_title' . $dbr->buildLike( $dbr->anyString(), '/', $dbr->anyString() ),
			] )
			->groupBy( 'lang' )
			->having( 'count > 20' )
			->orderBy( 'NULL' )
			->caller( __METHOD__ )
			->fetchResultSet();

		$data = [];
		foreach ( $res as $row ) {
			$data[$row->lang] = (int)$row->count;
		}

		$this->cache->set( $cacheKey, $data, 3600 );

		return $data;
	}

	private function filterUsers( array $users, string $code ): array {
		foreach ( $users as $index => $user ) {
			$username = $user[TranslatorActivityQuery::USER_NAME];
			// We do not know the group
			if ( $this->configHelper->isAuthorExcluded( '#', $code, $username ) ) {
				unset( $users[$index] );
			}
		}

		return $users;
	}

	private function outputLanguageCloud( array $languages, array $names ): void {
		$out = $this->getOutput();

		$out->addHTML( '<div class="tagcloud autonym">' );

		$translateDocumentationLanguageCode = $this->getConfig()->get( 'TranslateDocumentationLanguageCode' );
		foreach ( $languages as $k => $v ) {
			$name = $names[$k];
			$langAttribute = $k;
			$size = round( log( $v ) * 20 ) + 10;

			if ( $langAttribute === $translateDocumentationLanguageCode ) {
				$langAttribute = $this->contentLanguage->getHtmlCode();
			} else {
				$langAttribute = LanguageCode::bcp47( $langAttribute );
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
		$period = self::PERIOD;

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
				LoggerFactory::getInstance( LogNames::MAIN )->warning(
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

			$last = (int)wfTimestamp() - (int)wfTimestamp( TS_UNIX, $lastTranslationTimestamp );
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

	private function formatStyle( array $styles ): string {
		$stylestr = '';
		foreach ( $styles as $key => $value ) {
			$stylestr .= "$key:$value;";
		}

		return $stylestr;
	}

	private function preQueryUsers( array $users ): void {
		$lb = $this->linkBatchFactory->newLinkBatch();
		foreach ( $users as $data ) {
			$username = $data[TranslatorActivityQuery::USER_NAME];
			$user = Title::capitalize( $username, NS_USER );
			$lb->add( NS_USER, $user );
			$lb->add( NS_USER_TALK, $user );
		}
		$lb->execute();
	}

	private function getColorLegend(): string {
		$legend = '';
		$period = self::PERIOD;

		for ( $i = 0; $i <= $period; $i += 30 ) {
			$iFormatted = htmlspecialchars( $this->getLanguage()->formatNum( $i ) );
			$legend .= '<span style="background-color:#' .
				$this->progressStatsTable->getBackgroundColor( ( $period - $i ) / $period ) .
				"\"> $iFormatted</span>";
		}

		return $legend;
	}
}
