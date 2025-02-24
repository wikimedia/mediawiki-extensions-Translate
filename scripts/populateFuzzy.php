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

use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;

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
		$this->addDescription( 'A script to populate fuzzy tags to revtag table.' );
		$this->addOption(
			'namespace',
			'(optional) Namepace name or id',
			/*required*/false,
			/*has arg*/true
		);
		$this->setBatchSize( 500 );
		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		global $wgTranslateMessageNamespaces;

		$namespace = $this->getOption( 'namespace', $wgTranslateMessageNamespaces );
		$nsInfo = MediaWikiServices::getInstance()->getNamespaceInfo();
		if ( is_string( $namespace ) && ( !is_numeric( $namespace ) || !$nsInfo->exists( (int)$namespace ) ) ) {
			$namespace = $nsInfo->getCanonicalIndex( $namespace );
			if ( $namespace === null ) {
				$this->fatalError( 'Bad namespace' );
			}
		}

		$dbw = MediaWikiServices::getInstance()->getDBLoadBalancer()
			->getMaintenanceConnectionRef( DB_PRIMARY );
		$revStore = MediaWikiServices::getInstance()->getRevisionStore();

		$limit = $this->getBatchSize();
		$offset = 0;
		while ( true ) {
			$this->output( '.', '0' );
			$res = $revStore->newSelectQueryBuilder( $dbw )
				->joinPage()
				->joinComment()
				->where( [
					'page_latest = rev_id',
					'page_namespace' => $namespace,
				] )
				->limit( $limit )
				->offset( $offset )
				->caller( __METHOD__ )
				->fetchResultSet();

			if ( !$res->numRows() ) {
				break;
			}

			$inserts = [];
			$slots = $revStore->getContentBlobsForBatch( $res, [ SlotRecord::MAIN ] )->getValue();
			foreach ( $res as $r ) {
				if ( isset( $slots[$r->rev_id] ) ) {
					$text = $slots[$r->rev_id][SlotRecord::MAIN]->blob_data;
				} else {
					$content = $revStore->newRevisionFromRow( $r )
						->getContent( SlotRecord::MAIN );
					$text = Utilities::getTextFromTextContent( $content );
				}
				if ( str_contains( $text, TRANSLATE_FUZZY ) ) {
					$inserts[] = [
						'rt_page' => $r->page_id,
						'rt_revision' => $r->rev_id,
						'rt_type' => RevTagStore::FUZZY_TAG,
					];
				}
			}

			$offset += $limit;

			if ( $inserts ) {
				$dbw->newInsertQueryBuilder()
					->insertInto( 'revtag' )
					->rows( $inserts )
					->caller( __METHOD__ )
					->execute();
			}
		}
	}
}

$maintClass = PopulateFuzzy::class;
require_once RUN_MAINTENANCE_IF_MAIN;
