<?php
declare( strict_types = 1 );

/**
 * A script to populate fuzzy tags to revtag table.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2009-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 * @file
 */

use MediaWiki\Extension\Translate\ConfigNames;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;

// Standard boilerplate to define $IP
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
		$this->setBatchSize( 500 );
		$this->requireExtension( 'Translate' );
	}

	/** @inheritDoc */
	public function execute(): void {
		$services = MediaWikiServices::getInstance();
		$connectionProvider = $services->getConnectionProvider();
		$revStore = $services->getRevisionStore();
		$pageStore = $services->getPageStore();
		$revTagStore = Services::getInstance()->getRevTagStore();
		$titleFormatter = $services->getTitleFormatter();
		$config = $services->getMainConfig();

		$namespaces = $config->get( ConfigNames::MessageNamespaces );
		$limit = $this->getBatchSize();
		$dryRun = $this->hasOption( 'dry-run' );
		$totalWouldInsert = 0;
		$dbr = $connectionProvider->getReplicaDatabase();
		$dbw = $connectionProvider->getPrimaryDatabase();

		$this->output( "\nPages to update:", 'loop' );
		$offset = [ 'page_namespace' => 0, 'page_title' => '' ];
		while ( true ) {
			$pages = $pageStore->newSelectQueryBuilder()
				->select( [ 'page_latest', 'page_namespace', 'page_title' ] )
				->where( [
					'page_namespace' => $namespaces,
					$dbr->buildComparison( '>', $offset ),
				] )
				->orderByTitle()
				->limit( $limit )
				->caller( __METHOD__ )
				->fetchResultSet();
			$pages = iterator_to_array( $pages );
			if ( !$pages ) {
				break;
			}

			$revIds = array_column( $pages, 'page_latest' );
			// Update cursor to current row. TODO array_last in PHP 8.5
			$offset = (array)end( $pages );
			unset( $offset['page_latest'] );

			$res = $revStore->newSelectQueryBuilder( $dbr )
				->joinPage()
				->where( [ 'rev_id' => $revIds ] )
				->caller( __METHOD__ )
				->fetchResultSet();

			$inserts = [];
			$slots = $revStore->getContentBlobsForBatch( $res, [ SlotRecord::MAIN ] )->getValue();
			foreach ( $res as $r ) {
				if ( isset( $slots[$r->rev_id] ) ) {
					$text = $slots[$r->rev_id][SlotRecord::MAIN]->blob_data;
				} else {
					$content = $revStore->newRevisionFromRow( $r )->getContent( SlotRecord::MAIN );
					$text = Utilities::getTextFromTextContent( $content );
				}

				$needsUpdate = str_contains( $text, TRANSLATE_FUZZY ) &&
					!$revTagStore->isRevIdFuzzy( (int)$r->rev_page, (int)$r->rev_id );

				if ( $needsUpdate ) {
					$handle = new MessageHandle( new TitleValue( (int)$r->page_namespace, $r->page_title ) );
					// Skip orphaned messages to avoid unnecessary database pollution
					if ( $handle->getGroupIds() ) {
						$inserts[] = [
							'rt_page' => (int)$r->page_id,
							'rt_revision' => (int)$r->rev_id,
							'rt_type' => RevTagStore::FUZZY_TAG,
						];

						$prefixedText = $titleFormatter->formatTitle( (int)$r->page_namespace, $r->page_title );
						$this->output( "\n - $prefixedText", 'loop' );
					}
				}
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

		// Ensure a new line before the end of a page list (if any)
		$this->output( '' );

		if ( $dryRun ) {
			$this->output( "\n[dry-run] Total fuzzy tags that would be inserted: $totalWouldInsert\n" );
		}
	}
}

$maintClass = PopulateFuzzy::class;
require_once RUN_MAINTENANCE_IF_MAIN;
