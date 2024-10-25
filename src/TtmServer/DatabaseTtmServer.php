<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TtmServer;

use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\Utilities\StringComparators\EditDistanceStringComparator;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\WikiMap\WikiMap;
use Wikimedia\Rdbms\DBQueryError;
use Wikimedia\Rdbms\IDatabase;
use Wikimedia\Rdbms\IResultWrapper;

/**
 * MySQL/MariaDB-based based backend for translation memory.
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup TTMServer
 */
class DatabaseTtmServer extends TtmServer implements WritableTtmServer, ReadableTtmServer {
	private array $sids;

	private function getDB( int $mode = DB_REPLICA ): IDatabase {
		return MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection(
			$mode, 'ttmserver', $this->config['database']
		);
	}

	public function update( MessageHandle $handle, ?string $targetText ): bool {
		if ( !$handle->isValid() || $handle->getCode() === '' ) {
			return false;
		}

		$mKey = $handle->getKey();
		$group = $handle->getGroup();
		$targetLanguage = $handle->getCode();
		$sourceLanguage = $group->getSourceLanguage();

		// Skip definitions to not slow down mass imports etc.
		// These will be added when the first translation is made
		if ( $targetLanguage === $sourceLanguage ) {
			return false;
		}

		$definition = $group->getMessage( $mKey, $sourceLanguage );
		if ( !is_string( $definition ) || !strlen( trim( $definition ) ) ) {
			return false;
		}

		$context = Title::makeTitle( $handle->getTitle()->getNamespace(), $mKey );
		$dbw = $this->getDB( DB_PRIMARY );
		/* Check that the definition exists and fetch the sid. If not, add
		 * the definition and retrieve the sid. If the definition changes,
		 * we will create a new entry - otherwise we could at some point
		 * get suggestions which do not match the original definition any
		 * longer. The old translations are still kept until purged by
		 * rerunning the bootstrap script. */
		$sid = $dbw->newSelectQueryBuilder()
			->select( 'tms_sid' )
			->from( 'translate_tms' )
			->where( [
				'tms_context' => $context->getPrefixedText(),
				'tms_text' => $definition,
			] )
			->caller( __METHOD__ )
			->fetchField();
		if ( $sid === false ) {
			$sid = $this->insertSource( $context, $sourceLanguage, $definition );
		}

		// Delete old translations for this message if any. Could also use replace
		$deleteConditions = [
			'tmt_sid' => $sid,
			'tmt_lang' => $targetLanguage,
		];
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'translate_tmt' )
			->where( $deleteConditions )
			->caller( __METHOD__ )
			->execute();

		// Insert the new translation
		if ( $targetText !== null ) {
			$row = $deleteConditions + [
				'tmt_text' => $targetText,
			];

			$dbw->newInsertQueryBuilder()
				->insertInto( 'translate_tmt' )
				->row( $row )
				->caller( __METHOD__ )
				->execute();
		}

		return true;
	}

	private function insertSource( Title $context, string $sourceLanguage, string $text ): int {
		$dbw = $this->getDB( DB_PRIMARY );
		$dbw->newInsertQueryBuilder()
			->insertInto( 'translate_tms' )
			->row( [
				'tms_lang' => $sourceLanguage,
				'tms_len' => mb_strlen( $text ),
				'tms_text' => $text,
				'tms_context' => $context->getPrefixedText(),
			] )
			->caller( __METHOD__ )
			->execute();
		$sid = $dbw->insertId();

		$fulltext = $this->filterForFulltext( $sourceLanguage, $text );
		if ( count( $fulltext ) ) {
			$dbw->newInsertQueryBuilder()
				->insertInto( 'translate_tmf' )
				->row( [
					'tmf_sid' => $sid,
					'tmf_text' => implode( ' ', $fulltext ),
				] )
				->caller( __METHOD__ )
				->execute();
		}

		return $sid;
	}

	/** Tokenizes the text for fulltext search. Tries to find the most useful tokens. */
	protected function filterForFulltext( string $languageCode, string $input ): array {
		$lang = MediaWikiServices::getInstance()->getLanguageFactory()->getLanguage( $languageCode );

		$text = preg_replace( '/[^[:alnum:]]/u', ' ', $input );
		$text = $lang->segmentByWord( $text );
		$text = $lang->lc( $text );
		$segments = preg_split( '/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY );
		if ( count( $segments ) < 4 ) {
			return [];
		}

		foreach ( $segments as $i => $segment ) {
			// Yes strlen
			$len = strlen( $segment );
			if ( $len < 4 || $len > 15 ) {
				unset( $segments[$i] );
			}
		}

		return array_slice( array_unique( $segments ), 0, 10 );
	}

	public function beginBootstrap(): void {
		$dbw = $this->getDB( DB_PRIMARY );
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'translate_tms' )
			->where( '*' )
			->caller( __METHOD__ )
			->execute();
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'translate_tmt' )
			->where( '*' )
			->caller( __METHOD__ )
			->execute();
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'translate_tmf' )
			->where( '*' )
			->caller( __METHOD__ )
			->execute();
		$table = $dbw->tableName( 'translate_tmf' );
		try {
			$dbw->query( "DROP INDEX tmf_text ON $table", __METHOD__ );
		} catch ( DBQueryError $e ) {
			// Perhaps the script was aborted before it got
			// chance to add the index back.
		}
	}

	public function beginBatch(): void {
		$this->sids = [];
	}

	public function batchInsertDefinitions( array $batch ): void {
		$mwInstance = MediaWikiServices::getInstance();
		$titleFactory = $mwInstance->getTitleFactory();
		foreach ( $batch as $key => $item ) {
			[ $handle, $language, $text ] = $item;
			$context = $titleFactory->makeTitle( $handle->getTitle()->getNamespace(), $handle->getKey() );
			$this->sids[$key] = $this->insertSource( $context, $language, $text );
		}

		$mwInstance->getDBLoadBalancerFactory()->waitForReplication( [ 'ifWritesSince' => 10 ] );
	}

	public function batchInsertTranslations( array $batch ): void {
		if ( $batch === [] ) {
			return;
		}
		$rows = [];
		foreach ( $batch as $key => $data ) {
			[ , $language, $text ] = $data;
			$rows[] = [
				'tmt_sid' => $this->sids[$key],
				'tmt_lang' => $language,
				'tmt_text' => $text,
			];
		}

		$dbw = $this->getDB( DB_PRIMARY );
		$dbw->newInsertQueryBuilder()
			->insertInto( 'translate_tmt' )
			->rows( $rows )
			->caller( __METHOD__ )
			->execute();

		MediaWikiServices::getInstance()
			->getDBLoadBalancerFactory()
			->waitForReplication( [ 'ifWritesSince' => 10 ] );
	}

	public function endBatch(): void {
	}

	public function endBootstrap(): void {
		$dbw = $this->getDB( DB_PRIMARY );
		$table = $dbw->tableName( 'translate_tmf' );
		$dbw->query( "CREATE FULLTEXT INDEX tmf_text ON $table (tmf_text)", __METHOD__ );
	}

	/* Reading interface */

	public function isLocalSuggestion( array $suggestion ): bool {
		return true;
	}

	public function expandLocation( array $suggestion ): string {
		return Title::newFromText( $suggestion['location'] )->getCanonicalURL();
	}

	public function query( string $sourceLanguage, string $targetLanguage, string $text ): array {
		// Calculate the bounds of the string length which are able
		// to satisfy the cutoff percentage in edit distance.
		$len = mb_strlen( $text );
		$min = ceil( max( $len * $this->config['cutoff'], 2 ) );
		$max = floor( $len / $this->config['cutoff'] );

		// We could use fulltext index to narrow the results further
		$dbr = $this->getDB();
		$tables = [ 'translate_tmt', 'translate_tms' ];
		$fields = [ 'tms_context', 'tms_text', 'tmt_lang', 'tmt_text' ];

		$conditions = [
			'tms_lang' => $sourceLanguage,
			'tmt_lang' => $targetLanguage,
			"tms_len BETWEEN $min AND $max",
			'tms_sid = tmt_sid',
		];

		$fulltext = $this->filterForFulltext( $sourceLanguage, $text );
		if ( $fulltext ) {
			$tables[] = 'translate_tmf';
			$list = implode( ' ', $fulltext );
			$conditions[] = 'tmf_sid = tmt_sid';
			$conditions[] = "MATCH(tmf_text) AGAINST( '$list' )";
		}

		$res = $dbr->newSelectQueryBuilder()
			->tables( $tables )
			->select( $fields )
			->where( $conditions )
			->caller( __METHOD__ )
			->fetchResultSet();

		return $this->processQueryResults( $res, $text, $targetLanguage );
	}

	private function processQueryResults( IResultWrapper $res, string $text, string $targetLanguage ): array {
		$timeLimit = microtime( true ) + 5;

		$lenA = mb_strlen( $text );
		$results = [];
		$stringComparator = new EditDistanceStringComparator();
		foreach ( $res as $row ) {
			if ( microtime( true ) > $timeLimit ) {
				// Having no suggestions is better than preventing translation
				// altogether by timing out the request :(
				break;
			}

			$a = $text;
			$b = $row->tms_text;
			$lenB = mb_strlen( $b );
			$len = min( $lenA, $lenB );
			if ( $len > 600 ) {
				// two strings of length 1500 ~ 10s
				// two strings of length 2250 ~ 30s
				$dist = $len;
			} else {
				$dist = $stringComparator->levenshtein( $a, $b, $lenA, $lenB );
			}
			$quality = 1 - ( $dist * 0.9 / $len );

			if ( $quality >= $this->config['cutoff'] ) {
				$results[] = [
					'source' => $row->tms_text,
					'target' => $row->tmt_text,
					'context' => $row->tms_context,
					'location' => $row->tms_context . '/' . $targetLanguage,
					'quality' => $quality,
					'wiki' => $row->tms_wiki ?? WikiMap::getCurrentWikiId(),
				];
			}
		}

		return TTMServer::sortSuggestions( $results );
	}

	public function setDoReIndex(): void {
	}
}
