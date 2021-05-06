<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use Language;
use MediaWiki\Config\ServiceOptions;
use MessageGroups;
use TranslateUtils;
use Wikimedia\ObjectFactory;
use const TS_MW;

/**
 * Provides translation stats data
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2020.09
 */
class TranslationStatsDataProvider {
	public const CONSTRUCTOR_OPTIONS = [
		'TranslateStatsProviders'
	];

	/** @var ObjectFactory */
	private $objectFactory;
	/** @var ServiceOptions */
	private $options;

	public function __construct( ServiceOptions $options, ObjectFactory $objectFactory ) {
		$this->options = $options;
		$this->objectFactory = $objectFactory;
	}

	private function getGraphSpecifications(): array {
		return array_filter( $this->options->get( 'TranslateStatsProviders' ) );
	}

	public function getGraphTypes(): array {
		return array_keys( $this->getGraphSpecifications() );
	}

	/**
	 * Fetches and preprocesses graph data that can be fed to graph drawer.
	 * @param TranslationStatsGraphOptions $opts
	 * @param Language $language
	 * @return array ( string => array ) Data indexed by their date labels.
	 */
	public function getGraphData( TranslationStatsGraphOptions $opts, Language $language ): array {
		$dbr = wfGetDB( DB_REPLICA );

		$so = $this->getStatsProvider( $opts->getValue( 'count' ), $opts );

		$fixedStart = $opts->getValue( 'start' ) !== '';

		$now = time();
		$period = 3600 * 24 * $opts->getValue( 'days' );

		if ( $fixedStart ) {
			$cutoff = (int)wfTimestamp( TS_UNIX, $opts->getValue( 'start' ) );
		} else {
			$cutoff = $now - $period;
		}
		$cutoff = self::roundTimestampToCutoff( $opts->getValue( 'scale' ), $cutoff, 'earlier' );

		$start = $cutoff;

		if ( $fixedStart ) {
			$end = self::roundTimestampToCutoff( $opts->getValue( 'scale' ), $start + $period, 'later' ) - 1;
		} else {
			$end = null;
		}

		$tables = [];
		$fields = [];
		$conds = [];
		$type = __METHOD__;
		$options = [];
		$joins = [];

		$so->preQuery( $tables, $fields, $conds, $type, $options, $joins, $start, $end );
		$res = $dbr->select( $tables, $fields, $conds, $type, $options, $joins );
		wfDebug( __METHOD__ . "-queryend\n" );

		// Start processing the data
		$dateFormat = $so->getDateFormat();
		$increment = self::getIncrement( $opts->getValue( 'scale' ) );

		$labels = $so->labels();
		$keys = array_keys( $labels );
		$values = array_pad( [], count( $labels ), 0 );
		$defaults = array_combine( $keys, $values );

		$data = [];
		// Allow 10 seconds in the future for processing time
		$lastValue = $end ?? $now + 10;
		while ( $cutoff <= $lastValue ) {
			$date = $language->sprintfDate( $dateFormat, wfTimestamp( TS_MW, $cutoff ) );
			$cutoff += $increment;
			$data[$date] = $defaults;
		}
		// Ensure $lastValue is within range, in case the loop above jumped over it
		$data[$language->sprintfDate( $dateFormat, wfTimestamp( TS_MW, $lastValue ) )] = $defaults;

		// Processing
		$labelToIndex = array_flip( $labels );

		foreach ( $res as $row ) {
			$indexLabels = $so->indexOf( $row );
			if ( $indexLabels === false ) {
				continue;
			}

			foreach ( $indexLabels as $i ) {
				if ( !isset( $labelToIndex[$i] ) ) {
					continue;
				}
				$date = $language->sprintfDate( $dateFormat, $so->getTimestamp( $row ) );
				// Ignore values outside range
				if ( !isset( $data[$date] ) ) {
					continue;
				}

				$data[$date][$labelToIndex[$i]]++;
			}
		}

		// Don't display dummy label
		if ( count( $labels ) === 1 && $labels[0] === 'all' ) {
			$labels = [];
		}

		foreach ( $labels as &$label ) {
			if ( strpos( $label, '@' ) === false ) {
				continue;
			}
			[ $groupId, $code ] = explode( '@', $label, 2 );
			if ( $code && $groupId ) {
				$code = TranslateUtils::getLanguageName( $code, $language->getCode() ) . " ($code)";
				$group = MessageGroups::getGroup( $groupId );
				$group = $group ? $group->getLabel() : $groupId;
				$label = "$group @ $code";
			} elseif ( $code ) {
				$label = TranslateUtils::getLanguageName( $code, $language->getCode() ) . " ($code)";
			} elseif ( $groupId ) {
				$group = MessageGroups::getGroup( $groupId );
				$label = $group ? $group->getLabel() : $groupId;
			}
		}

		// Indicator that the last value is not full
		if ( $end === null ) {
			// Warning: do not user array_splice, which does not preserve numerical keys
			$last = end( $data );
			$key = key( $data );
			unset( $data[$key] );
			$data[ "$key*" ] = $last;
		}

		return [ $labels, $data ];
	}

	/** @noinspection PhpIncompatibleReturnTypeInspection */
	private function getStatsProvider( string $type, TranslationStatsGraphOptions $opts ): TranslationStatsInterface {
		$specs = $this->getGraphSpecifications();
		return $this->objectFactory->createObject(
			$specs[$type],
			[
				'allowClassName' => true,
				'extraArgs' => [ $opts ],
			]
		);
	}

	/**
	 * Gets the closest earliest timestamp that corresponds to start of a
	 * period in given scale, like, midnight, monday or first day of the month.
	 */
	private static function roundTimestampToCutoff(
		string $scale, int $cutoff, string $direction = 'earlier'
	): int {
		$dir = $direction === 'earlier' ? -1 : 1;

		/* Ensure that the first item in the graph has full data even
		* if it doesn't align with the given 'days' boundary */
		if ( $scale === 'hours' ) {
			$cutoff += self::roundingAddition( $cutoff, 3600, $dir );
		} elseif ( $scale === 'days' ) {
			$cutoff += self::roundingAddition( $cutoff, 86400, $dir );
		} elseif ( $scale === 'weeks' ) {
			/* Here we assume that week starts on monday, which does not
			* always hold true. Go Xwards day by day until we are on monday */
			while ( date( 'D', $cutoff ) !== 'Mon' ) {
				$cutoff += $dir * 86400;
			}
			// Round to nearest day
			$cutoff -= ( $cutoff % 86400 );
		} elseif ( $scale === 'months' ) {
			// Go Xwards/ day by day until we are on the first day of the month
			while ( date( 'j', $cutoff ) !== '1' ) {
				$cutoff += $dir * 86400;
			}
			// Round to nearest day
			$cutoff -= ( $cutoff % 86400 );
		} elseif ( $scale === 'years' ) {
			// Go Xwards/ day by day until we are on the first day of the year
			while ( date( 'z', $cutoff ) !== '0' ) {
				$cutoff += $dir * 86400;
			}
			// Round to nearest day
			$cutoff -= ( $cutoff % 86400 );
		}

		return $cutoff;
	}

	private static function roundingAddition( int $ts, int $amount, int $dir ): int {
		if ( $dir === -1 ) {
			return -1 * ( $ts % $amount );
		} else {
			return $amount - ( $ts % $amount );
		}
	}

	/**
	 * Returns an increment in seconds for a given scale.
	 * The increment must be small enough that we will hit every item in the
	 * scale when using different multiples of the increment. It should be
	 * large enough to avoid hitting the same item multiple times.
	 */
	private static function getIncrement( string $scale ): int {
		$increment = 3600 * 24;
		if ( $scale === 'years' ) {
			$increment = 3600 * 24 * 350;
		} elseif ( $scale === 'months' ) {
			/* We use increment to fill up the values. Use number small enough
			 * to ensure we hit each month */
			$increment = 3600 * 24 * 15;
		} elseif ( $scale === 'weeks' ) {
			$increment = 3600 * 24 * 7;
		} elseif ( $scale === 'hours' ) {
			$increment = 3600;
		}

		return $increment;
	}
}
