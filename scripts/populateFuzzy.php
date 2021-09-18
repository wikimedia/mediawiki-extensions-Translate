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
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;

if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = __DIR__;
	$IP = "$dir/../../..";
}
require_once "$IP/maintenance/Maintenance.php";

/// A script to populate fuzzy tags to revtag table.
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
		$this->setBatchSize( 5000 );
		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		global $wgTranslateMessageNamespaces;

		$namespace = $this->getOption( 'namespace', $wgTranslateMessageNamespaces );
		$nsInfo = MediaWikiServices::getInstance()->getNamespaceInfo();
		if ( is_string( $namespace ) && !$nsInfo->exists( $namespace ) ) {
			$namespace = $nsInfo->getCanonicalIndex( $namespace );
			if ( $namespace === null ) {
				$this->fatalError( 'Bad namespace' );
			}
		}

		$dbw = MediaWikiServices::getInstance()->getDBLoadBalancer()
			->getMaintenanceConnectionRef( DB_PRIMARY );
		$revStore = MediaWikiServices::getInstance()->getRevisionStore();
		$queryInfo = $revStore->getQueryInfo( [ 'page' ] );

		$limit = $this->getBatchSize();
		$offset = 0;
		while ( true ) {
			$inserts = [];
			$this->output( '.', 0 );
			$options = [ 'LIMIT' => $limit, 'OFFSET' => $offset ];
			$res = $dbw->select(
				$queryInfo['tables'],
				$queryInfo['fields'],
				[
					'page_latest = rev_id',
					'page_namespace' => $namespace,
				],
				__METHOD__,
				$options,
				$queryInfo['joins']
			);

			if ( !$res->numRows() ) {
				break;
			}

			$slots = $revStore->getContentBlobsForBatch( $res, [ SlotRecord::MAIN ] )->getValue();
			foreach ( $res as $r ) {
				if ( isset( $slots[$r->rev_id] ) ) {
					$text = $slots[$r->rev_id][SlotRecord::MAIN]->blob_data;
				} else {
					$text = $revStore->newRevisionFromRow( $r )
						->getContent( SlotRecord::MAIN )
						->getNativeData();
				}
				if ( strpos( $text, TRANSLATE_FUZZY ) !== false ) {
					$inserts[] = [
						'rt_page' => $r->page_id,
						'rt_revision' => $r->rev_id,
						'rt_type' => RevTag::getType( 'fuzzy' ),
					];
				}
			}

			$offset += $limit;

			if ( $inserts ) {
				$dbw->replace( 'revtag', 'rt_type_page_revision', $inserts, __METHOD__ );
			}
		}
	}
}

$maintClass = PopulateFuzzy::class;
require_once RUN_MAINTENANCE_IF_MAIN;
