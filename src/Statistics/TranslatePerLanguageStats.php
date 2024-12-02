<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageLoading\MessageIndex;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Language\Language;
use MediaWiki\MediaWikiServices;
use stdClass;
use Wikimedia\Rdbms\IReadableDatabase;
use Wikimedia\Rdbms\SelectQueryBuilder;

/**
 * Graph which provides statistics on active users and number of translations.
 * @ingroup Stats
 * @license GPL-2.0-or-later
 * @since 2010.07
 */
class TranslatePerLanguageStats extends TranslationStatsBase {
	/** @var array For client side group by time period */
	protected array $seenUsers = [];
	protected array $groups = [];
	private Language $dateFormatter;
	private array $formatCache = [];
	protected MessageIndex $messageIndex;

	public function __construct( TranslationStatsGraphOptions $opts ) {
		parent::__construct( $opts );
		// This query is slow. Set a lower limit, but allow seeing one year at once.
		$opts->boundValue( 'days', 1, 400 );
		// TODO: inject
		$this->dateFormatter = MediaWikiServices::getInstance()->getContentLanguage();
		$this->messageIndex = Services::getInstance()->getMessageIndex();
	}

	public function createQueryBuilder( IReadableDatabase $database, string $caller ): SelectQueryBuilder {
		global $wgTranslateMessageNamespaces;

		$fields = [ 'rc_timestamp' ];

		$conds = [
			'rc_namespace' => $wgTranslateMessageNamespaces,
			'rc_bot' => 0,
			'rc_type != ' . RC_LOG,
		];

		$this->groups = array_map( [ MessageGroups::class, 'normalizeId' ], $this->opts->getGroups() );

		$namespaces = self::namespacesFromGroups( $this->groups );
		if ( count( $namespaces ) ) {
			$conds['rc_namespace'] = $namespaces;
		}

		$languages = [];
		foreach ( $this->opts->getLanguages() as $code ) {
			$languages[] = 'rc_title ' . $database->buildLike( $database->anyString(), "/$code" );
		}
		if ( count( $languages ) ) {
			$conds[] = $database->makeList( $languages, LIST_OR );
		}

		$fields[] = 'rc_title';

		if ( $this->groups ) {
			$fields[] = 'rc_namespace';
		}

		if ( $this->opts->getValue( 'count' ) === 'users' ) {
			$fields[] = 'rc_actor';
		}

		return $database->newSelectQueryBuilder()
			->table( 'recentchanges' )
			->fields( $fields )
			->conds( $conds )
			->options( [ 'ORDER BY' => 'rc_timestamp' ] )
			->caller( $caller . '-perlang' );
	}

	public function indexOf( stdClass $row ): ?array {
		if ( $this->opts->getValue( 'count' ) === 'users' ) {
			$date = $this->formatTimestamp( $row->rc_timestamp );

			if ( isset( $this->seenUsers[$date][$row->rc_actor] ) ) {
				return null;
			}

			$this->seenUsers[$date][$row->rc_actor] = true;
		}

		// Do not consider language-less pages.
		if ( !str_contains( $row->rc_title, '/' ) ) {
			return null;
		}

		// No filters, just one key to track.
		if ( !$this->groups && !$this->opts->getLanguages() ) {
			return [ 'all' ];
		}

		// The key-building needs to be in sync with ::labels().
		[ $key, $code ] = Utilities::figureMessage( $row->rc_title );

		$groups = [];
		$codes = [];

		if ( $this->groups ) {
			// Get list of keys that the message belongs to, and filter
			// out those which are not requested.
			$groups = $this->messageIndex->getGroupIdsForDatabaseTitle( (int)$row->rc_namespace, $key );
			$groups = array_intersect( $this->groups, $groups );
		}

		if ( $this->opts->getLanguages() ) {
			$codes = [ $code ];
		}

		return $this->combineTwoArrays( $groups, $codes );
	}

	public function labels(): array {
		return $this->combineTwoArrays( $this->groups, $this->opts->getLanguages() );
	}

	public function getTimestampColumn(): string {
		return 'rc_timestamp';
	}

	/**
	 * Makes a label for variable. If group or language code filters, or both
	 * are used, combine those in a pretty way.
	 * @param string $group Group name.
	 * @param string $code Language code.
	 * @return string Label.
	 */
	protected function makeLabel( $group, $code ) {
		if ( $group || $code ) {
			return "$group@$code";
		} else {
			return 'all';
		}
	}

	/**
	 * Cross-product of two lists with string results, where either
	 * list can be empty.
	 * @param string[] $groups Group names.
	 * @param string[] $codes Language codes.
	 * @return string[] Labels.
	 */
	protected function combineTwoArrays( $groups, $codes ) {
		if ( !count( $groups ) ) {
			$groups[] = false;
		}

		if ( !count( $codes ) ) {
			$codes[] = false;
		}

		$items = [];
		foreach ( $groups as $group ) {
			foreach ( $codes as $code ) {
				$items[] = $this->makeLabel( $group, $code );
			}
		}

		return $items;
	}

	/**
	 * Returns unique index for given item in the scale being used.
	 * Called a lot, so performance intensive.
	 * @param string $timestamp Timestamp in mediawiki format.
	 * @return string
	 */
	protected function formatTimestamp( $timestamp ) {
		switch ( $this->opts->getValue( 'scale' ) ) {
			case 'hours':
				$cut = 4;
				break;
			case 'days':
				$cut = 6;
				break;
			case 'months':
				$cut = 8;
				break;
			case 'years':
				$cut = 10;
				break;
			default:
				// Get the prefix that uniquely identifies a day in the MW timestamp format
				$index = substr( $timestamp, 0, -6 );
				// Date formatting is really slow, so do it at most once per day. This is not
				// adjusted for user timestamp, so it's safe to assume day boundaries follow UTC.
				$this->formatCache[$index] ??= $this->dateFormatter->sprintfDate( $this->getDateFormat(), $timestamp );
				return $this->formatCache[$index];
		}

		return substr( $timestamp, 0, -$cut );
	}
}
