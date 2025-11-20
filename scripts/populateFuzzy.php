<?php
/**
 * A script to populate fuzzy tags to revtag table.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2009-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 * @file
 */

// Standard boilerplate to define $IP

use MediaWiki\Extension\Translate\ConfigNames;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;

if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = __DIR__;
	$IP = "$dir/../../..";
}
require_once "$IP/maintenance/Maintenance.php";

/**
 * A script to populate fuzzy tags to revtag table.
 */
class PopulateFuzzy extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription(
			sprintf(
				'Populate missing fuzzy tags to revtag table for pages containing %s.',
				TRANSLATE_FUZZY
			)
		);
		$this->addOption(
			'dry-run',
			"Don't write anything to the database; just report what would be done",
		);
		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		$services = MediaWikiServices::getInstance();
		$connectionProvider = $services->getConnectionProvider();
		$revStore = $services->getRevisionStore();
		$revTagStore = Services::getInstance()->getRevTagStore();
		$config = $services->getMainConfig();

		$namespaces = $config->get( ConfigNames::MessageNamespaces );
		$limit = $this->getBatchSize();
		$dryRun = $this->hasOption( 'dry-run' );
		$totalWouldInsert = 0;
		$dbr = $connectionProvider->getReplicaDatabase();
		$dbw = $connectionProvider->getPrimaryDatabase();

		$this->output( "\nPages to update:", 'loop' );
		foreach ( (array)$namespaces as $ns ) {
			$offset = [ 'page_namespace' => 0, 'page_title' => '' ];
			while ( true ) {
				$qb = $revStore->newSelectQueryBuilder( $dbr )
					->joinPage()
					->joinComment()
					->where( [
						'page_latest = rev_id',
						'page_namespace' => (int)$ns,
						$dbr->buildComparison( '>', $offset ),
					] )
					->orderBy( [ 'page_namespace', 'page_title' ], 'ASC' )
					->limit( $limit )
					->caller( __METHOD__ );
				$res = $qb->fetchResultSet();
				if ( !$res->numRows() ) {
					break;
				}

				$inserts = [];
				$slots = $revStore->getContentBlobsForBatch( $res, [ SlotRecord::MAIN ] )->getValue();
				foreach ( $res as $r ) {
					if ( isset( $slots[$r->rev_id] ) ) {
						$text = $slots[$r->rev_id][SlotRecord::MAIN]->blob_data;
					} else {
						$content = $revStore->newRevisionFromRow( $r )->getContent( SlotRecord::MAIN );
						$text = Utilities::getTextFromTextContent( $content );
					}
					if ( str_contains( $text, TRANSLATE_FUZZY ) ) {
						if ( !$revTagStore->isRevIdFuzzy( $r->rev_page, $r->rev_id ) ) {
							$inserts[] = [
								'rt_page' => $r->page_id,
								'rt_revision' => $r->rev_id,
								'rt_type' => RevTagStore::FUZZY_TAG,
							];

							$title = Title::makeTitle( $r->page_namespace, $r->page_title );
							$this->output( "\n - {$title->getPrefixedText()}", 'loop' );
						}
					}
					// Update cursor to current row
					$offset = [ 'page_namespace' => $r->page_namespace, 'page_title' => $r->page_title ];
				}

				if ( $inserts ) {
					if ( $dryRun ) {
						$totalWouldInsert += count( $inserts );
					} else {
						$dbw->newInsertQueryBuilder()
							->insertInto( 'revtag' )
							->rows( $inserts )
							->caller( __METHOD__ )
							->execute();
					}
				}
			}
		}

		// Ensure a new line before the end of a page list (if any)
		$this->output( '' );

		if ( $dryRun ) {
			$this->output( "\n[dry-run] Total fuzzy tags that would be inserted: $totalWouldInsert\n" );
		}
	}
}

$maintClass = PopulateFuzzy::class;
require_once RUN_MAINTENANCE_IF_MAIN;
