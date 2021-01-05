<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use ActorMigration;
use MediaWiki\MediaWikiServices;
use TranslateUtils;

/**
 * Graph which provides statistics on active users and number of translations.
 * @ingroup Stats
 * @license GPL-2.0-or-later
 * @since 2010.07
 */
class TranslatePerLanguageStats extends TranslationStatsBase {
	/** @var int[][] array( string => int ) Cache used to count active users only once per day. */
	protected $usercache;
	protected $groups;

	public function __construct( TranslationStatsGraphOptions $opts ) {
		parent::__construct( $opts );
		// This query is slow... ensure a lower limit.
		$opts->boundValue( 'days', 1, 200 );
	}

	public function preQuery( &$tables, &$fields, &$conds, &$type, &$options, &$joins, $start, $end ) {
		global $wgTranslateMessageNamespaces;

		$db = wfGetDB( DB_REPLICA );

		$tables = [ 'recentchanges' ];
		$fields = [ 'rc_timestamp' ];
		$joins = [];

		$conds = [
			'rc_namespace' => $wgTranslateMessageNamespaces,
			'rc_bot' => 0,
			'rc_type != ' . RC_LOG,
		];

		$timeConds = self::makeTimeCondition( 'rc_timestamp', $start, $end );
		$conds = array_merge( $conds, $timeConds );

		$options = [ 'ORDER BY' => 'rc_timestamp' ];

		$this->groups = array_map( 'MessageGroups::normalizeId', $this->opts->getGroups() );

		$namespaces = self::namespacesFromGroups( $this->groups );
		if ( count( $namespaces ) ) {
			$conds['rc_namespace'] = $namespaces;
		}

		$languages = [];
		foreach ( $this->opts->getLanguages() as $code ) {
			$languages[] = 'rc_title ' . $db->buildLike( $db->anyString(), "/$code" );
		}
		if ( count( $languages ) ) {
			$conds[] = $db->makeList( $languages, LIST_OR );
		}

		$fields[] = 'rc_title';

		if ( $this->groups ) {
			$fields[] = 'rc_namespace';
		}

		if ( $this->opts->getValue( 'count' ) === 'users' ) {
			if ( class_exists( ActorMigration::class ) ) {
				$actorQuery = ActorMigration::newMigration()->getJoin( 'rc_user' );
				$tables += $actorQuery['tables'];
				$fields['rc_user_text'] = $actorQuery['fields']['rc_user_text'];
				$joins += $actorQuery['joins'];
			} else {
				$fields[] = 'rc_user_text';
			}
		}

		$type .= '-perlang';
	}

	public function indexOf( $row ) {
		// We need to check that there is only one user per day.
		if ( $this->opts->getValue( 'count' ) === 'users' ) {
			$date = $this->formatTimestamp( $row->rc_timestamp );

			if ( isset( $this->usercache[$date][$row->rc_user_text] ) ) {
				return false;
			} else {
				$this->usercache[$date][$row->rc_user_text] = 1;
			}
		}

		// Do not consider language-less pages.
		if ( strpos( $row->rc_title, '/' ) === false ) {
			return false;
		}

		// No filters, just one key to track.
		if ( !$this->groups && !$this->opts->getLanguages() ) {
			return [ 'all' ];
		}

		// The key-building needs to be in sync with ::labels().
		list( $key, $code ) = TranslateUtils::figureMessage( $row->rc_title );

		$groups = [];
		$codes = [];

		if ( $this->groups ) {
			/*
			 * Get list of keys that the message belongs to, and filter
			 * out those which are not requested.
			 */
			$groups = TranslateUtils::messageKeyToGroups( $row->rc_namespace, $key );
			$groups = array_intersect( $this->groups, $groups );
		}

		if ( $this->opts->getLanguages() ) {
			$codes = [ $code ];
		}

		return $this->combineTwoArrays( $groups, $codes );
	}

	public function labels() {
		return $this->combineTwoArrays( $this->groups, $this->opts->getLanguages() );
	}

	public function getTimestamp( $row ) {
		return $row->rc_timestamp;
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
			default:
				return MediaWikiServices::getInstance()->getContentLanguage()
					->sprintfDate( $this->getDateFormat(), $timestamp );
		}

		return substr( $timestamp, 0, -$cut );
	}
}

class_alias( TranslatePerLanguageStats::class, '\MediaWiki\Extensions\Translate\TranslatePerLanguageStats' );
