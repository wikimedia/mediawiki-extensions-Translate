<?php
/**
 * A script to populate fuzzy tags to revtag table.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2009-2013, Niklas Laxström
 * @license GPL-2.0+
 * @file
 */

// Standard boilerplate to define $IP
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
		$this->mDescription = 'A script to populate fuzzy tags to revtag table.';
		$this->addOption(
			'namespace',
			'(optional) Namepace name or id',
			/*required*/false,
			/*has arg*/true
		);
	}

	public function execute() {
		global $wgTranslateMessageNamespaces;

		$namespace = $this->getOption( 'namespace', $wgTranslateMessageNamespaces );
		if ( is_string( $namespace ) &&
			!MWNamespace::exists( $namespace )
		) {
			$namespace = MWNamespace::getCanonicalIndex( $namespace );

			if ( $namespace === null ) {
				$this->error( 'Bad namespace', true );
			}
		}

		$dbw = wfGetDB( DB_MASTER );
		$tables = array( 'page', 'text', 'revision' );
		$fields = array( 'page_id', 'page_title', 'page_namespace', 'rev_id', 'old_text', 'old_flags' );
		$conds = array(
			'page_latest = rev_id',
			'old_id = rev_text_id',
			'page_namespace' => $namespace,
		);

		$limit = 100;
		$offset = 0;
		while ( true ) {
			$inserts = array();
			$this->output( '.', 0 );
			$options = array( 'LIMIT' => $limit, 'OFFSET' => $offset );
			$res = $dbw->select( $tables, $fields, $conds, __METHOD__, $options );

			if ( !$res->numRows() ) {
				break;
			}

			foreach ( $res as $r ) {
				$text = Revision::getRevisionText( $r );
				if ( strpos( $text, TRANSLATE_FUZZY ) !== false ) {
					$inserts[] = array(
						'rt_page' => $r->page_id,
						'rt_revision' => $r->rev_id,
						'rt_type' => RevTag::getType( 'fuzzy' ),
					);
				}
			}

			$offset += $limit;

			$dbw->replace( 'revtag', 'rt_type_page_revision', $inserts, __METHOD__ );
		}
	}
}

$maintClass = 'PopulateFuzzy';
require_once RUN_MAINTENANCE_IF_MAIN;
