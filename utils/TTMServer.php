<?php
/**
 * TTMServer - The dead simple translation memory
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/// @since 2012-01-28
interface iTTMServer {

	/**
	 * Adds a new source message in the database. Note that update does this
	 * for you automatically.
	 *
	 * @param $context Title: title of the source page
	 * @param $sourceLanguage String: language code for the provide text
	 * @param $text String: the source text to add.
	 * @return Integer: sid (source id)
	 */
	public function insertSource( Title $context, $sourceLanguage, $text );

	/**
	 * Shovels the new translation into translation memory.
	 *
	 * @param $handle MessageHandle
	 * @param $targetText String
	 * @return Bool: Success or failture
	 */
	public function update( MessageHandle $handle, $targetText );

	/**
	 * Fetches all relevant suggestions for given text.
	 *
	 * @param $sourceLanguage String: language code for the provide text
	 * @param $targetLanguage String: language code for the suggestions
	 * @param $text String: the text for which to search suggestions
	 * @return List: unordered suggestions, which each has fields:
	 *   - source: String: the original text of the suggestion
	 *   - target: String: the suggestion
	 *   - context: String: title of the page where the suggestion comes from
	 *   - quality: Float: the quality of suggestion, 1 is perfect match
	 */
	public function query( $sourceLanguage, $targetLanguage, $text );
}

/**
 * TTMServer is the simple translation memory that is just good enough for us
 * @since 2012-01-28
 */
class TTMServer implements iTTMServer  {
	protected $config;

	public function __construct( $config ) {
		$this->config = $config;
	}

	/**
	 * Returns a server instance, useful for chaining.
	 * @return iTTMServer
	 */
	public static function primary() {
		global $wgTranslateTranslationServices;
		if ( isset( $wgTranslateTranslationServices['TTMServer'] ) ) {
			return new TTMServer( $wgTranslateTranslationServices['TTMServer'] );
		} else {
			return new FakeTTMServer();
		}
	}

	public function getDB( $mode = DB_SLAVE ) {
		return wfGetDB( $mode, 'ttmserver', $this->config['database'] );
	}

	public function update( MessageHandle $handle, $targetText ) {
		global $wgContLang;

		if ( !$handle->isValid() || $handle->getCode() === '' ) {
			return false;
		}

		$mkey  = $handle->getKey();
		$group = $handle->getGroup();
		$targetLanguage = $handle->getCode();
		$sourceLanguage = $group->getSourceLanguage();
		$title = $handle->getTitle();

		// Skip definitions to not slow down mass imports etc.
		// These will be added when the first translation is made
		if ( $targetLanguage === $sourceLanguage ) {
			return false;
		}

		$definition = $group->getMessage( $mkey, $sourceLanguage );
		if ( !is_string( $definition ) || !strlen( trim( $definition ) ) ) {
			return false;
		}

		$dbw = $this->getDB( DB_MASTER );
		/* Check that the definition exists and fetch the sid. If not, add
		 * the definition and retrieve the sid. If the definition changes,
		 * we will create a new entry - otherwise we could at some point
		 * get suggestions which do not match the original definition any
		 * longer. The old translations are still kept until purged by
		 * rerunning the bootstrap script. */
		$conds = array(
			'tms_context' => $title->getPrefixedText(),
			'tms_text' => $definition,
		);
		$sid = $dbw->selectField( 'translate_tms', 'tms_sid', $conds, __METHOD__ );
		if ( $sid === false ) {
			$sid = $this->insertSource( $title, $sourceLanguage, $definition );
		}

		// Delete old translations for this message if any. Could also use replace
		$deleteConds = array(
			'tmt_sid' => $sid,
			'tmt_lang' => $targetLanguage,
		);
		$dbw->delete( 'translate_tmt', $deleteConds, __METHOD__ );

		// Insert the new translation
		$row = $deleteConds + array(
			'tmt_text' => $targetText,
		);

		$dbw->insert( 'translate_tmt', $row, __METHOD__ );

		return true;
	}

	public function insertSource( Title $context, $sourceLanguage, $text ) {
		$row = array(
			'tms_lang' => $sourceLanguage,
			'tms_len' => mb_strlen( $text ),
			'tms_text' => $text,
			'tms_context' => $context->getPrefixedText(),
		);

		$dbw = $this->getDB( DB_MASTER );
		$dbw->insert( 'translate_tms', $row, __METHOD__ );
		$sid = $dbw->insertId();

		// Fulltext
		$fulltext = $this->filterForFulltext( $sourceLanguage, $text );
		if ( count( $fulltext ) ) {
			$row = array(
				'tmf_sid' => $sid,
				'tmf_text' => implode( ' ', $fulltext ),
			);
			$dbw->insert( 'translate_tmf', $row, __METHOD__ );
		}

		return $sid;
	}

	public function query( $sourceLanguage, $targetLanguage, $text ) {
		// Calculate the bounds of the string length which are able
		// to satisfy the cutoff percentage in edit distance.
		$len = mb_strlen( $text );
		$min = ceil( max( $len * $this->config['cutoff'], 2 ) );
		$max = floor( $len / $this->config['cutoff'] );

		// We could use fulltext index to narrow the results further
		$dbr = $this->getDB( DB_SLAVE );
		$tables = array( 'translate_tmt', 'translate_tms' );
		$fields = array( 'tms_context', 'tms_text', 'tmt_lang', 'tmt_text' );
		$conds = array(
			'tms_lang' => $sourceLanguage,
			'tmt_lang' => $targetLanguage,
			"tms_len BETWEEN $min AND $max",
			'tms_sid = tmt_sid',
		);

		$fulltext = $this->filterForFulltext( $sourceLanguage, $text );
		if ( $fulltext ) {
			$tables[] = 'translate_tmf';
			$list = implode( ' ',  $fulltext );
			$conds[] = 'tmf_sid = tmt_sid';
			$conds[] = "MATCH(tmf_text) AGAINST( '$list' )";
		}

		$res = $dbr->select( $tables, $fields, $conds, __METHOD__ );
		return $this->processQueryResults( $res, $text );
	}

	protected function processQueryResults( $res, $text ) {
		$results = array();
		foreach ( $res as $row ) {
			$a = $text;
			$b = $row->tms_text;
			$len = min( mb_strlen( $a ), mb_strlen( $b ) );
			if ( strlen( $a ) > 255 || strlen( $b ) > 255 ) {
				$dist = self::levenshtein_php( $a, $b );
			} else {
				$dist = levenshtein( $a, $b );
			}
			$quality = 1 - ( $dist / $len );

			if ( $quality >= $this->config['cutoff'] ) {
				$results[] = array(
					'source' => $row->tms_text,
					'target' => $row->tmt_text,
					'context' => $row->tms_context,
					'quality' => $quality,
				);
			}
		}
		usort( $results, array( $this, 'qualitySort' ) );
		return $results;
	}

	protected function qualitySort( $a, $b ) {
		list( $c, $d ) = array( $a['quality'], $b['quality'] );
		if ( $c === $d ) {
			return 0;
		}
		// Descending sort
		return ( $c > $d ) ? -1 : 1;
	}

	/**
	 * Tokenizes the text for fulltext search.
	 * Tries to find the most useful tokens.
	 */
	protected function filterForFulltext( $language, $input ) {
		$lang = Language::factory( $language );

		$text = preg_replace( '/[^[:alnum:]]/u', ' ', $input );
		$text = $lang->segmentByWord( $text );
		$text = $lang->lc( $text );
		$segments = preg_split( '/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY );
		if ( count( $segments ) < 4 ) {
			return array();
		}

		foreach ( $segments as $i => $segment ) {
			// Yes strlen
			$len = strlen( $segment );
			if ( $len < 4 || $len > 15 ) {
				unset( $segments[$i] );
			}
		}

		$segments = array_unique( $segments );
		$segments = array_slice( $segments, 0, 10 );
		return $segments;
	}

	/**
	 * Stolen from PHP manual comments.
	 * The native levenshtein is limited to 255 bytes.
	 */
	public static function levenshtein_php( $str1, $str2 ) {
		$len1 = mb_strlen( $str1 );
		$len2 = mb_strlen( $str2 );

		// strip common prefix
		$i = 0;
		do {
			if ( mb_substr( $str1, $i, 1 ) != mb_substr( $str2, $i, 1 ) )
				break;
			$i++;
			$len1--;
			$len2--;
		} while ( $len1 > 0 && $len2 > 0 );
		if ( $i > 0 ) {
			$str1 = mb_substr( $str1, $i );
			$str2 = mb_substr( $str2, $i );
		}

		// strip common suffix
		$i = 0;
		do {
			if ( mb_substr( $str1, $len1 -1, 1 ) != mb_substr( $str2, $len2 -1, 1 ) )
				break;
			$i++;
			$len1--;
			$len2--;
		} while ( $len1 > 0 && $len2 > 0 );
		if ( $i > 0 ) {
			$str1 = mb_substr( $str1, 0, $len1 );
			$str2 = mb_substr( $str2, 0, $len2 );
		}

		if ( $len1 == 0 )
			return $len2;
		if ( $len2 == 0 )
			return $len1;

		$v0 = range( 0, $len1 );
		$v1 = array();

		for ( $i = 1; $i <= $len2; $i++ ) {
			$v1[0] = $i;
			$str2j = mb_substr( $str2, $i - 1, 1 );

			for ( $j = 1; $j <= $len1; $j++ ) {
				$cost = ( mb_substr( $str1, $j - 1, 1 ) == $str2j ) ? 0 : 1;

				$m_min = $v0[$j] + 1;
				$b = $v1[$j - 1] + 1;
				$c = $v0[$j - 1] + $cost;

				if ( $b < $m_min )
					$m_min = $b;
				if ( $c < $m_min )
					$m_min = $c;

				$v1[$j] = $m_min;
			}

			$vTmp = $v0;
			$v0 = $v1;
			$v1 = $vTmp;
		}

		return $v0[$len1];
	}

}

/**
 * NO-OP version of TTMServer when it is disabled.
 * Keeps other code simpler when they can just do
 * TTMServer::primary()->update( ... );
 * @since 2012-01-28
 */
class FakeTTMServer implements iTTMServer {
	public function insertSource( Title $context, $sourceLanguage, $text ) {
		return false;
	}

	public function update( MessageHandle $handle, $targetText ) {
		return false;
	}

	public function query( $sourceLanguage, $targetLanguage, $text ) {
		return array();
	}
}
