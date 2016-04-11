<?php
/**
 * TTMServer - The Translate extension translation memory interface
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 * @defgroup TTMServer The Translate extension translation memory interface
 */

/**
 * Some general static methods for instantiating TTMServer and helpers.
 * @since 2012-01-28
 * Rewritten in 2012-06-27.
 * @ingroup TTMServer
 */
class TTMServer {
	protected $config;

	protected function __construct( $config ) {
		$this->config = $config;
	}

	public static function factory( $config ) {
		if ( isset( $config['class'] ) ) {
			$class = $config['class'];

			return new $class( $config );
		} elseif ( isset( $config['type'] ) ) {
			$type = $config['type'];
			switch ( $type ) {
				case 'ttmserver':
					return new DatabaseTTMServer( $config );
				case 'remote-ttmserver':
					return new RemoteTTMServer( $config );
				default:
					return null;
			}
		}

		throw new MWEXception( 'TTMServer with no type' );
	}

	/**
	 * Returns the primary server instance, useful for chaining.
	 * Primary one is defined as config with key TTMServer
	 * in $wgTranslateTranslationServices.
	 * @return WritableTTMServer
	 */
	public static function primary() {
		global $wgTranslateTranslationServices;
		if ( isset( $wgTranslateTranslationServices['TTMServer'] ) ) {
			$obj = self::factory( $wgTranslateTranslationServices['TTMServer'] );
			if ( $obj instanceof WritableTTMServer ) {
				return $obj;
			}
		}

		return new FakeTTMServer();
	}

	public static function sortSuggestions( array $suggestions ) {
		usort( $suggestions, array( __CLASS__, 'qualitySort' ) );

		return $suggestions;
	}

	protected static function qualitySort( $a, $b ) {
		list( $c, $d ) = array( $a['quality'], $b['quality'] );
		if ( $c === $d ) {
			return 0;
		}

		// Descending sort
		return ( $c > $d ) ? -1 : 1;
	}

	/**
	 * PHP implementation of Levenshtein edit distance algorithm.
	 * Uses the native PHP implementation when possible for speed.
	 * The native levenshtein is limited to 255 bytes.
	 *
	 * @param string $str1
	 * @param string $str2
	 * @param int $length1
	 * @param int $length2
	 * @return int
	 */
	public static function levenshtein( $str1, $str2, $length1, $length2 ) {
		if ( $length1 === 0 ) {
			return $length2;
		}
		if ( $length2 === 0 ) {
			return $length1;
		}
		if ( $str1 === $str2 ) {
			return 0;
		}

		$bytelength1 = strlen( $str1 );
		$bytelength2 = strlen( $str2 );
		if ( $bytelength1 === $length1 && $bytelength1 <= 255
			&& $bytelength2 === $length2 && $bytelength2 <= 255
		) {
			return levenshtein( $str1, $str2 );
		}

		$prevRow = range( 0, $length2 );
		for ( $i = 0; $i < $length1; $i++ ) {
			$currentRow = array();
			$currentRow[0] = $i + 1;
			$c1 = mb_substr( $str1, $i, 1 );
			for ( $j = 0; $j < $length2; $j++ ) {
				$c2 = mb_substr( $str2, $j, 1 );
				$insertions = $prevRow[$j + 1] + 1;
				$deletions = $currentRow[$j] + 1;
				$substitutions = $prevRow[$j] + ( ( $c1 !== $c2 ) ? 1 : 0 );
				$currentRow[] = min( $insertions, $deletions, $substitutions );
			}
			$prevRow = $currentRow;
		}

		return $prevRow[$length2];
	}

	/// Hook: ArticleDeleteComplete
	public static function onDelete( WikiPage $wikipage ) {
		$handle = new MessageHandle( $wikipage->getTitle() );
		$job = TTMServerMessageUpdateJob::newJob( $handle, 'delete' );
		JobQueueGroup::singleton()->push( $job );
	}

	/// Called from TranslateEditAddons::onSave
	public static function onChange( MessageHandle $handle, $text, $fuzzy ) {
		$job = TTMServerMessageUpdateJob::newJob( $handle, 'refresh' );
		JobQueueGroup::singleton()->push( $job );
	}

	public static function onGroupChange( MessageHandle $handle, $old, $new ) {
		if ( $old === array() ) {
			// Don't bother for newly added messages
			return;
		}

		$job = TTMServerMessageUpdateJob::newJob( $handle, 'rebuild' );
		JobQueueGroup::singleton()->push( $job );
	}
}
