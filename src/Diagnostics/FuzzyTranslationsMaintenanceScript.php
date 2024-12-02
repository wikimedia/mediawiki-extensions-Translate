<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Diagnostics;

use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Content\ContentHandler;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\Extension\Translate\Utilities\BaseMaintenanceScript;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\MediaWikiServices;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Revision\RevisionStore;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use MediaWiki\User\ActorNormalization;
use Wikimedia\Rdbms\IDBAccessObject;
use Wikimedia\Rdbms\ILoadBalancer;
use Wikimedia\Rdbms\IResultWrapper;

/**
 * @since 2022.01
 * @license GPL-2.0-or-later
 * @author Niklas Laxström
 */
class FuzzyTranslationsMaintenanceScript extends BaseMaintenanceScript {
	private ActorNormalization $actorNormalization;
	private RevisionStore $revisionStore;
	private ILoadBalancer $DBLoadBalancer;
	private WikiPageFactory $wikiPageFactory;

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
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->addOption(
			'comment',
			'(optional) Comment for updating',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->addOption(
			'user',
			'(optional) Fuzzy the translations made by user given as an argument.',
			self::OPTIONAL,
			self::NO_ARG
		);
		$this->requireExtension( 'Translate' );
	}

	private function initServices(): void {
		$mwServices = MediaWikiServices::getInstance();
		$this->actorNormalization = $mwServices->getActorNormalization();
		$this->revisionStore = $mwServices->getRevisionStore();
		$this->DBLoadBalancer = $mwServices->getDBLoadBalancer();
		$this->wikiPageFactory = $mwServices->getWikiPageFactory();
	}

	public function execute(): void {
		$this->initServices();

		$skipLanguages = [];
		if ( $this->hasOption( 'skiplanguages' ) ) {
			$skipLanguages = array_map(
				'trim',
				explode( ',', $this->getOption( 'skiplanguages' ) )
			);
		}

		if ( $this->hasOption( 'user' ) ) {
			$pages = $this->getPagesForUser( $this->getArg( 0 ), $skipLanguages );
		} else {
			$pages = $this->getPagesForPattern( $this->getArg( 0 ), $skipLanguages );
		}

		$dryrun = !$this->hasOption( 'really' );
		$comment = $this->getOption( 'comment' );
		$this->fuzzyTranslations( $pages, $dryrun, $comment );
	}

	private function fuzzyTranslations( array $pages, bool $dryrun, ?string $comment ): void {
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
	private function getMessageContentsFromRows( IResultWrapper $rows ): array {
		$messagesContents = [];
		$slots = $this->revisionStore->getContentBlobsForBatch( $rows, [ SlotRecord::MAIN ] )->getValue();
		foreach ( $rows as $row ) {
			$title = Title::makeTitle( $row->page_namespace, $row->page_title );
			if ( isset( $slots[$row->rev_id] ) ) {
				$text = $slots[$row->rev_id][SlotRecord::MAIN]->blob_data;
			} else {
				$content = $this->revisionStore
					->newRevisionFromRow( $row, IDBAccessObject::READ_NORMAL, $title )
					->getContent( SlotRecord::MAIN );
				$text = Utilities::getTextFromTextContent( $content );
			}
			$messagesContents[] = [ $title, $text ];
		}
		return $messagesContents;
	}

	/** Searches pages that match given patterns */
	private function getPagesForPattern( string $pattern, array $skipLanguages = [] ): array {
		$dbr = $this->DBLoadBalancer->getMaintenanceConnectionRef( DB_REPLICA );

		$conds = [
			'page_latest=rev_id',
		];

		$title = Title::newFromText( $pattern );
		if ( $title->inNamespace( NS_MAIN ) ) {
			$namespace = $this->getConfig()->get( 'TranslateMessageNamespaces' );
		} else {
			$namespace = $title->getNamespace();
		}

		$conds['page_namespace'] = $namespace;
		$conds[] = 'page_title' . $dbr->buildLike( $title->getDBkey(), $dbr->anyString() );

		if ( count( $skipLanguages ) ) {
			$skiplist = $dbr->makeList( $skipLanguages );
			$conds[] = "substring_index(page_title, '/', -1) NOT IN ($skiplist)";
		}

		$rows = $this->revisionStore->newSelectQueryBuilder( $dbr )
			->joinPage()
			->where( $conds )
			->caller( __METHOD__ )
			->fetchResultSet();
		return $this->getMessageContentsFromRows( $rows );
	}

	private function getPagesForUser( string $userName, array $skipLanguages = [] ): array {
		$dbr = $this->DBLoadBalancer->getMaintenanceConnectionRef( DB_REPLICA );
		$actorId = $this->actorNormalization->findActorIdByName( $userName, $dbr );
		if ( $actorId === null ) {
			return [];
		}

		$conds = [
			'page_latest=rev_id',
			'rev_actor' => $actorId,
			'page_namespace' => $this->getConfig()->get( 'TranslateMessageNamespaces' ),
			'page_title' . $dbr->buildLike( $dbr->anyString(), '/', $dbr->anyString() ),
		];
		if ( count( $skipLanguages ) ) {
			$skiplist = $dbr->makeList( $skipLanguages );
			$conds[] = "substring_index(page_title, '/', -1) NOT IN ($skiplist)";
		}

		$rows = $this->revisionStore->newSelectQueryBuilder( $dbr )
			->joinPage()
			->joinUser()
			->where( $conds )
			->caller( __METHOD__ )
			->fetchResultSet();

		return $this->getMessageContentsFromRows( $rows );
	}

	/**
	 * Does the actual edit if possible.
	 * @param Title $title
	 * @param string $text
	 * @param bool $dryrun Whether to really do it or just show what would be done.
	 * @param string|null $comment Edit summary.
	 */
	private function updateMessage( Title $title, string $text, bool $dryrun, ?string $comment = null ) {
		$this->output( "Updating {$title->getPrefixedText()}... ", $title );

		$documentationLanguageCode = $this->getConfig()->get( 'TranslateDocumentationLanguageCode' );
		$items = explode( '/', $title->getText(), 2 );
		if ( isset( $items[1] ) && $items[1] === $documentationLanguageCode ) {
			$this->output( 'IGNORED!', $title );

			return;
		}

		if ( $dryrun ) {
			$this->output( 'DRY RUN!', $title );

			return;
		}

		$wikiPage = $this->wikiPageFactory->newFromTitle( $title );
		$summary = CommentStoreComment::newUnsavedComment( $comment ?? 'Marking as fuzzy' );
		$content = ContentHandler::makeContent( $text, $title );
		$updater = $wikiPage->newPageUpdater( FuzzyBot::getUser() );
		$updater
			->setContent( SlotRecord::MAIN, $content )
			->saveRevision( $summary, EDIT_FORCE_BOT | EDIT_UPDATE );
		$status = $updater->getStatus();

		$this->output( $status->isOK() ? 'OK' : 'FAILED', $title );
	}
}
