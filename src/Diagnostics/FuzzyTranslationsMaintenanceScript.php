<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Diagnostics;

use ActorMigration;
use ContentHandler;
use IDBAccessObject;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\Extension\Translate\Utilities\BaseMaintenanceScript;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use Title;
use TranslateUtils;
use User;
use Wikimedia\Rdbms\IResultWrapper;
use WikiPage;

/**
 * @since 2022.01
 * @license GPL-2.0-or-later
 * @author Niklas Laxström
 */
class FuzzyTranslationsMaintenanceScript extends BaseMaintenanceScript {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Fuzzy bot command line script.' );
		$this->addArg(
			'arg',
			'Title pattern or username if user option is provided.'
		);
		$this->addOption(
			'really',
			'(optional) Really fuzzy, no dry-run'
		);
		$this->addOption(
			'skiplanguages',
			'(optional) Skip some languages (comma separated)',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'comment',
			'(optional) Comment for updating',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'user',
			'(optional) Fuzzy the translations made by user given as an argument.',
			false, /*required*/
			false /*has arg*/
		);
		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		$skipLanguages = [];
		if ( $this->hasOption( 'skiplanguages' ) ) {
			$skipLanguages = array_map(
				'trim',
				explode( ',', $this->getOption( 'skiplanguages' ) )
			);
		}

		if ( $this->hasOption( 'user' ) ) {
			$user = User::newFromName( $this->getArg( 0 ) );
			$pages = $this->getPagesForUser( $user, $skipLanguages );
		} else {
			$pages = $this->getPagesForPattern( $this->getArg( 0 ), $skipLanguages );
		}

		$dryrun = !$this->hasOption( 'really' );
		$comment = $this->getOption( 'comment' );
		$this->fuzzyTranslations( $pages, $dryrun, $comment );
	}

	private function fuzzyTranslations( array $pages, bool $dryrun, $comment ) {
		$count = count( $pages );
		$this->output( "Found $count pages to update.", 'pagecount' );

		foreach ( $pages as [ $title, $text ] ) {
			$this->updateMessage( $title, TRANSLATE_FUZZY . $text, $dryrun, $comment );
		}
	}

	/**
	 * Gets the message contents from database rows.
	 * @param IResultWrapper $rows
	 * @return array containing page titles and the text content of the page
	 */
	private function getMessageContentsFromRows( $rows ) {
		$revStore = MediaWikiServices::getInstance()->getRevisionStore();
		$messagesContents = [];
		$slots = $revStore->getContentBlobsForBatch( $rows, [ SlotRecord::MAIN ] )->getValue();
		foreach ( $rows as $row ) {
			$title = Title::makeTitle( $row->page_namespace, $row->page_title );
			if ( isset( $slots[$row->rev_id] ) ) {
				$text = $slots[$row->rev_id][SlotRecord::MAIN]->blob_data;
			} else {
				$text = $revStore->newRevisionFromRow( $row, IDBAccessObject::READ_NORMAL, $title )
					->getContent( SlotRecord::MAIN )
					->getNativeData();
			}
			$messagesContents[] = [ $title, $text ];
		}
		return $messagesContents;
	}

	/// Searches pages that match given patterns
	private function getPagesForPattern( $pattern, $skipLanguages = [] ) {
		global $wgTranslateMessageNamespaces;
		$dbr = wfGetDB( DB_REPLICA );

		$search = [];
		foreach ( (array)$pattern as $title ) {
			$title = Title::newFromText( $title );
			$ns = $title->getNamespace();
			if ( !isset( $search[$ns] ) ) {
				$search[$ns] = [];
			}
			$search[$ns][] = 'page_title' . $dbr->buildLike( $title->getDBkey(), $dbr->anyString() );
		}

		$title_conds = [];
		foreach ( $search as $ns => $names ) {
			if ( $ns === NS_MAIN ) {
				$ns = $wgTranslateMessageNamespaces;
			}
			$titles = $dbr->makeList( $names, LIST_OR );
			$title_conds[] = $dbr->makeList( [ 'page_namespace' => $ns, $titles ], LIST_AND );
		}

		$conds = [
			'page_latest=rev_id',
			$dbr->makeList( $title_conds, LIST_OR ),
		];

		if ( count( $skipLanguages ) ) {
			$skiplist = $dbr->makeList( $skipLanguages );
			$conds[] = "substring_index(page_title, '/', -1) NOT IN ($skiplist)";
		}

		$revStore = MediaWikiServices::getInstance()->getRevisionStore();
		$queryInfo = $revStore->getQueryInfo( [ 'page' ] );
		$rows = $dbr->select(
			$queryInfo['tables'],
			$queryInfo['fields'],
			$conds,
			__METHOD__,
			[],
			$queryInfo['joins']
		);
		return $this->getMessageContentsFromRows( $rows );
	}

	private function getPagesForUser( User $user, $skipLanguages = [] ) {
		global $wgTranslateMessageNamespaces;
		$dbr = wfGetDB( DB_REPLICA );

		$revWhere = ActorMigration::newMigration()->getWhere( $dbr, 'rev_user', $user );
		$conds = [
			'page_latest=rev_id',
			$revWhere['conds'],
			'page_namespace' => $wgTranslateMessageNamespaces,
			'page_title' . $dbr->buildLike( $dbr->anyString(), '/', $dbr->anyString() ),
		];
		if ( count( $skipLanguages ) ) {
			$skiplist = $dbr->makeList( $skipLanguages );
			$conds[] = "substring_index(page_title, '/', -1) NOT IN ($skiplist)";
		}

		$revStore = MediaWikiServices::getInstance()->getRevisionStore();
		$queryInfo = $revStore->getQueryInfo( [ 'page', 'user' ] );
		$rows = $dbr->select(
			$queryInfo['tables'],
			$queryInfo['fields'],
			$conds,
			__METHOD__,
			[],
			$queryInfo['joins'] + $revWhere['joins']
		);

		return $this->getMessageContentsFromRows( $rows );
	}

	/**
	 * Does the actual edit if possible.
	 * @param Title $title
	 * @param string $text
	 * @param bool $dryrun Whether to really do it or just show what would be done.
	 * @param string|null $comment Edit summary.
	 */
	private function updateMessage( $title, $text, $dryrun, $comment = null ) {
		global $wgTranslateDocumentationLanguageCode;

		$this->output( "Updating {$title->getPrefixedText()}... ", $title );
		if ( !$title instanceof Title ) {
			$this->output( 'INVALID TITLE!', $title );

			return;
		}

		$items = explode( '/', $title->getText(), 2 );
		if ( isset( $items[1] ) && $items[1] === $wgTranslateDocumentationLanguageCode ) {
			$this->output( 'IGNORED!', $title );

			return;
		}

		if ( $dryrun ) {
			$this->output( 'DRY RUN!', $title );

			return;
		}

		$wikipage = new WikiPage( $title );
		$content = ContentHandler::makeContent( $text, $title );
		$status = TranslateUtils::doPageEdit(
			$wikipage,
			$content,
			FuzzyBot::getUser(),
			$comment ?: 'Marking as fuzzy',
			EDIT_FORCE_BOT | EDIT_UPDATE
		);

		$success = $status && $status->isOK();
		$this->output( $success ? 'OK' : 'FAILED', $title );
	}
}
