<?php
/**
 * TTMServer - The Translate extension translation memory interface
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @ingroup TTMServer
 */

/**
 * Mysql based backend.
 * @ingroup TTMServer
 * @since 2012-06-27
 */
class DatabaseTTMServer extends TTMServer implements WritableTTMServer, ReadableTTMServer {
	protected $sids;

	/**
	 * @param $mode int DB_SLAVE|DB_MASTER
	 * @return DatabaseBase
	 */
	protected function getDB( $mode = DB_SLAVE ) {
		return wfGetDB( $mode, 'ttmserver', $this->config['database'] );
	}

	public function update( MessageHandle $handle, $targetText ) {
		if ( !$handle->isValid() || $handle->getCode() === '' ) {
			return false;
		}

		$mkey  = $handle->getKey();
		$group = $handle->getGroup();
		$targetLanguage = $handle->getCode();
		$sourceLanguage = $group->getSourceLanguage();

		// Skip definitions to not slow down mass imports etc.
		// These will be added when the first translation is made
		if ( $targetLanguage === $sourceLanguage ) {
			return false;
		}

		$definition = $group->getMessage( $mkey, $sourceLanguage );
		if ( !is_string( $definition ) || !strlen( trim( $definition ) ) ) {
			return false;
		}

		$context = Title::makeTitle( $handle->getTitle()->getNamespace(), $mkey );
		$dbw = $this->getDB( DB_MASTER );
		/* Check that the definition exists and fetch the sid. If not, add
		 * the definition and retrieve the sid. If the definition changes,
		 * we will create a new entry - otherwise we could at some point
		 * get suggestions which do not match the original definition any
		 * longer. The old translations are still kept until purged by
		 * rerunning the bootstrap script. */
		$conds = array(
			'tms_context' => $context->getPrefixedText(),
			'tms_text' => $definition,
		);

		$extra = $this->getExtraConditions();
		$conds = array_merge( $conds, $extra );

		$sid = $dbw->selectField( 'translate_tms', 'tms_sid', $conds, __METHOD__ );
		if ( $sid === false ) {
			$sid = $this->insertSource( $context, $sourceLanguage, $definition );
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

	/// For subclasses
	protected function getExtraConditions() {
		return array();
	}

	protected function insertSource( Title $context, $sourceLanguage, $text ) {
		wfProfileIn( __METHOD__ );
		$row = array(
			'tms_lang' => $sourceLanguage,
			'tms_len' => mb_strlen( $text ),
			'tms_text' => $text,
			'tms_context' => $context->getPrefixedText(),
		);

		$extra = $this->getExtraConditions();
		$row = array_merge( $row, $extra );

		$dbw = $this->getDB( DB_MASTER );
		$dbw->insert( 'translate_tms', $row, __METHOD__ );
		$sid = $dbw->insertId();

		$fulltext = $this->filterForFulltext( $sourceLanguage, $text );
		if ( count( $fulltext ) ) {
			$row = array(
				'tmf_sid' => $sid,
				'tmf_text' => implode( ' ', $fulltext ),
			);
			$dbw->insert( 'translate_tmf', $row, __METHOD__ );
		}

		wfProfileOut( __METHOD__ );
		return $sid;
	}

	/**
	 * Tokenizes the text for fulltext search.
	 * Tries to find the most useful tokens.
	 */
	protected function filterForFulltext( $language, $input ) {
		wfProfileIn( __METHOD__ );
		$lang = Language::factory( $language );

		$text = preg_replace( '/[^[:alnum:]]/u', ' ', $input );
		$text = $lang->segmentByWord( $text );
		$text = $lang->lc( $text );
		$segments = preg_split( '/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY );
		if ( count( $segments ) < 4 ) {
			wfProfileOut( __METHOD__ );
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
		wfProfileOut( __METHOD__ );
		return $segments;
	}

	public function beginBootstrap() {
		$dbw = $this->getDB( DB_MASTER );
		$dbw->delete( 'translate_tms', '*', __METHOD__ );
		$dbw->delete( 'translate_tmt', '*', __METHOD__ );
		$dbw->delete( 'translate_tmf', '*', __METHOD__ );
		$table = $dbw->tableName( 'translate_tmf' );
		$dbw->ignoreErrors( true );
		$dbw->query( "DROP INDEX tmf_text ON $table" );
		$dbw->ignoreErrors( false );
	}

	public function beginBatch() {
		$this->sids = array();
	}

	public function batchInsertDefinitions( array $batch ) {
		foreach ( $batch as $key => $item ) {
			list( $title, $language, $text ) = $item;
			$handle = new MessageHandle( $title );
			$context = Title::makeTitle( $handle->getTitle()->getNamespace(), $handle->getKey() );
			$this->sids[$key] = $this->insertSource( $context, $language, $text );
		}
		wfWaitForSlaves( 10 );
	}

	public function batchInsertTranslations( array $batch ) {
		$rows = array();
		foreach ( $batch as $key => $data ) {
			list( $title, $language, $text ) = $data;
			$rows[] = array(
				'tmt_sid' => $this->sids[$key],
				'tmt_lang' => $language,
				'tmt_text' => $text,
			);
		}

		$dbw = $this->getDB( DB_MASTER );
		$dbw->insert( 'translate_tmt', $rows, __METHOD__ );
		wfWaitForSlaves( 10 );
	}

	public function endBatch() {}

	public function endBootstrap() {
		$dbw = $this->getDB( DB_MASTER );
		$table = $dbw->tableName( 'translate_tmf' );
		$dbw->query( "CREATE FULLTEXT INDEX tmf_text ON $table (tmf_text)" );
	}

	/* Reading interface */

	public function isLocalSuggestion( array $suggestion ) {
		return true;
	}

	public function expandLocation( array $suggestion ) {
		$title = Title::newFromText( $suggestion['location'] );
		return $title->getCanonicalUrl();
	}

	public function query( $sourceLanguage, $targetLanguage, $text ) {
		wfProfileIn( __METHOD__ );
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

		$extra = $this->getExtraConditions();
		$fields = array_merge( $fields, array_keys( $extra ) );
		$conds = array_merge( $conds, $extra );

		$fulltext = $this->filterForFulltext( $sourceLanguage, $text );
		if ( $fulltext ) {
			$tables[] = 'translate_tmf';
			$list = implode( ' ',  $fulltext );
			$conds[] = 'tmf_sid = tmt_sid';
			$conds[] = "MATCH(tmf_text) AGAINST( '$list' )";
		}

		$res = $dbr->select( $tables, $fields, $conds, __METHOD__ );
		wfProfileOut( __METHOD__ );
		return $this->processQueryResults( $res, $text, $sourceLanguage, $targetLanguage );
	}

	protected function processQueryResults( $res, $text, $sourceLanguage, $targetLanguage ) {
		wfProfileIn( __METHOD__ );
		$lenA = mb_strlen( $text );
		$results = array();
		foreach ( $res as $row ) {
			$a = $text;
			$b = $row->tms_text;
			$lenB = mb_strlen( $b );
			$len = min( $lenA, $lenB );
			if ( $len > 600 ) {
				// two strings of length 1500 ~ 10s
				// two strings of length 2250 ~ 30s
				$dist = $len;
			} else {
				$dist = self::levenshtein( $a, $b, $lenA, $lenB );
			}
			$quality = 1 - ( $dist * 0.9 / $len );

			if ( $quality >= $this->config['cutoff'] ) {
				$results[] = array(
					'source' => $row->tms_text,
					'target' => $row->tmt_text,
					'context' => $row->tms_context,
					'location' => $row->tms_context . '/' . $targetLanguage,
					'quality' => $quality,
					'wiki' => isset( $row->tms_wiki ) ? $row->tms_wiki : wfWikiId(),
				);
			}
		}
		$results = TTMServer::sortSuggestions( $results );
		wfProfileOut( __METHOD__ );
		return $results;
	}

}
