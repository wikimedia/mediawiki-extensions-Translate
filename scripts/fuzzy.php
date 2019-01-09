<?php
/**
 * Command line script to mark translations fuzzy (similar to gettext fuzzy).
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2007-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0-or-later
 */

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = __DIR__;
	$IP = "$dir/../../..";
}
require_once "$IP/maintenance/Maintenance.php";

# Override the memory limit for wfShellExec, 100 MB appears to be too little
$wgMaxShellMemory = 1024 * 200;

class Fuzzy extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Fuzzy bot command line script.';
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
			$pages = FuzzyScript::getPagesForUser( $user, $skipLanguages );
		} else {
			$pages = FuzzyScript::getPagesForPattern( $this->getArg( 0 ), $skipLanguages );
		}

		$bot = new FuzzyScript( $pages );
		$bot->comment = $this->getOption( 'comment' );
		$bot->dryrun = !$this->hasOption( 'really' );
		$bot->setProgressCallback( [ $this, 'myOutput' ] );
		$bot->execute();
	}

	/**
	 * Public alternative for protected Maintenance::output() as we need to get
	 * messages from the ChangeSyncer class to the commandline.
	 * @param string $text The text to show to the user
	 * @param string|null $channel Unique identifier for the channel.
	 * @param bool $error Whether this is an error message
	 */
	public function myOutput( $text, $channel = null, $error = false ) {
		if ( $error ) {
			$this->error( $text, $channel );
		} else {
			$this->output( $text, $channel );
		}
	}
}

/**
 * Class for marking translation fuzzy.
 */
class FuzzyScript {
	/**
	 * @var bool Check for configuration problems.
	 */
	private $allclear = false;

	/** @var callable Function to report progress updates */
	protected $progressCallback;

	/**
	 * @var bool Dont do anything unless confirmation is given
	 */
	public $dryrun = true;

	/**
	 * @var string Edit summary.
	 */
	public $comment;

	/**
	 * @param array $pages
	 */
	public function __construct( $pages ) {
		$this->pages = $pages;
		$this->allclear = true;
	}

	public function setProgressCallback( $callback ) {
		$this->progressCallback = $callback;
	}

	/// @see Maintenance::output for param docs
	protected function reportProgress( $text, $channel, $severity = 'status' ) {
		if ( is_callable( $this->progressCallback ) ) {
			$useErrorOutput = $severity === 'error';
			call_user_func( $this->progressCallback, $text, $channel, $useErrorOutput );
		}
	}

	public function execute() {
		if ( !$this->allclear ) {
			return;
		}

		$msgs = $this->pages;
		$count = count( $msgs );
		$this->reportProgress( "Found $count pages to update.", 'pagecount' );

		foreach ( $msgs as $phpIsStupid ) {
			list( $title, $text ) = $phpIsStupid;
			$this->updateMessage( $title, TRANSLATE_FUZZY . $text, $this->dryrun, $this->comment );
			unset( $phpIsStupid );
		}
	}

	/// Searches pages that match given patterns
	public static function getPagesForPattern( $pattern, $skipLanguages = [] ) {
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
			'rev_text_id=old_id',
			$dbr->makeList( $title_conds, LIST_OR ),
		];

		if ( count( $skipLanguages ) ) {
			$skiplist = $dbr->makeList( $skipLanguages );
			$conds[] = "substring_index(page_title, '/', -1) NOT IN ($skiplist)";
		}

		$rows = $dbr->select(
			[ 'page', 'revision', 'text' ],
			[ 'page_title', 'page_namespace', 'old_text', 'old_flags' ],
			$conds,
			__METHOD__
		);

		$messagesContents = [];
		foreach ( $rows as $row ) {
			$title = Title::makeTitle( $row->page_namespace, $row->page_title );
			$messagesContents[] = [ $title, Revision::getRevisionText( $row ) ];
		}

		$rows->free();

		return $messagesContents;
	}

	public static function getPagesForUser( User $user, $skipLanguages = [] ) {
		global $wgTranslateMessageNamespaces;
		$dbr = wfGetDB( DB_REPLICA );

		if ( class_exists( ActorMigration::class ) ) {
			$revWhere = ActorMigration::newMigration()->getWhere( $dbr, 'rev_user', $user );
		} else {
			$revWhere = [
				'tables' => [],
				'conds' => 'rev_user = ' . (int)$user->getId(),
				'joins' => [],
			];
		}

		$conds = [
			$revWhere['conds'],
			'page_namespace' => $wgTranslateMessageNamespaces,
			'page_title' . $dbr->buildLike( $dbr->anyString(), '/', $dbr->anyString() ),
		];

		if ( count( $skipLanguages ) ) {
			$skiplist = $dbr->makeList( $skipLanguages );
			$conds[] = "substring_index(page_title, '/', -1) NOT IN ($skiplist)";
		}

		$rows = $dbr->select(
			[ 'page', 'revision', 'text' ] + $revWhere['tables'],
			[ 'page_title', 'page_namespace', 'old_text', 'old_flags' ],
			$conds,
			__METHOD__,
			[],
			[
				'revision' => [ 'JOIN', 'page_latest=rev_id' ],
				'text' => [ 'JOIN', 'rev_text_id=old_id' ],
			] + $revWhere['joins']
		);

		$messagesContents = [];
		foreach ( $rows as $row ) {
			$title = Title::makeTitle( $row->page_namespace, $row->page_title );
			$messagesContents[] = [ $title, Revision::getRevisionText( $row ) ];
		}

		$rows->free();

		return $messagesContents;
	}

	/**
	 * Does the actual edit if possible.
	 * @param Title $title
	 * @param string $text
	 * @param bool $dryrun Whether to really do it or just show what would be done.
	 * @param string $comment Edit summary.
	 */
	private function updateMessage( $title, $text, $dryrun, $comment = null ) {
		global $wgTranslateDocumentationLanguageCode;

		$this->reportProgress( "Updating {$title->getPrefixedText()}... ", $title );
		if ( !$title instanceof Title ) {
			$this->reportProgress( 'INVALID TITLE!', $title );

			return;
		}

		$items = explode( '/', $title->getText(), 2 );
		if ( isset( $items[1] ) && $items[1] === $wgTranslateDocumentationLanguageCode ) {
			$this->reportProgress( 'IGNORED!', $title );

			return;
		}

		if ( $dryrun ) {
			$this->reportProgress( 'DRY RUN!', $title );

			return;
		}

		$wikipage = new WikiPage( $title );
		$content = ContentHandler::makeContent( $text, $title );
		$status = $wikipage->doEditContent(
			$content,
			$comment ?: 'Marking as fuzzy',
			EDIT_FORCE_BOT | EDIT_UPDATE,
			false, /*base revision id*/
			FuzzyBot::getUser()
		);

		$success = $status === true || ( is_object( $status ) && $status->isOK() );
		$this->reportProgress( $success ? 'OK' : 'FAILED', $title );
	}
}

$maintClass = Fuzzy::class;
require_once RUN_MAINTENANCE_IF_MAIN;
