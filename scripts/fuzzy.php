<?php
/**
 * Command line script to mark translations fuzzy (similar to gettext fuzzy).
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2007-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0+
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
			'messages',
			'Message to fuzzy'
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
	}

	public function execute() {
		$bot = new FuzzyScript( $this->getArg( 0 ) );

		if ( $this->hasOption( 'skiplanguages' ) ) {
			$bot->skipLanguages = array_map(
				'trim',
				explode( ',', $this->getOption( 'skiplanguages' ) )
			);
		}

		$bot->comment = $this->getOption( 'comment' );
		$bot->dryrun = !$this->hasOption( 'really' );
		$bot->setProgressCallback( array( $this, 'myOutput' ) );
		$bot->execute();
	}

	/**
	 * Public alternative for protected Maintenance::output() as we need to get
	 * messages from the ChangeSyncer class to the commandline.
	 * @param string $text The text to show to the user
	 * @param string $channel Unique identifier for the channel.
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
	/* @var string[] List of patterns to mark.
	 */
	private $titles = array();

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
	 * string[] List of language codes to skip.
	 */
	public $skipLanguages = array();

	/**
	 * @param string[] $titles
	 */
	public function __construct( $titles ) {
		$this->titles = (array)$titles;
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

		$msgs = $this->getPages();
		$count = count( $msgs );
		$this->reportProgress( "Found $count pages to update.", 'pagecount' );

		foreach ( $msgs as $phpIsStupid ) {
			list( $title, $text ) = $phpIsStupid;
			$this->updateMessage( $title, TRANSLATE_FUZZY . $text, $this->dryrun, $this->comment );
			unset( $phpIsStupid );
		}
	}

	/// Searches pages that match given patterns
	private function getPages() {
		global $wgTranslateMessageNamespaces;
		$dbr = wfGetDB( DB_SLAVE );

		$search = array();
		foreach ( $this->titles as $title ) {
			$title = Title::newFromText( $title );
			$ns = $title->getNamespace();
			if ( !isset( $search[$ns] ) ) {
				$search[$ns] = array();
			}
			$search[$ns][] = 'page_title' . $dbr->buildLike( $title->getDBkey(), $dbr->anyString() );
		}

		$title_conds = array();
		foreach ( $search as $ns => $names ) {
			if ( $ns === NS_MAIN ) {
				$ns = $wgTranslateMessageNamespaces;
			}
			$titles = $dbr->makeList( $names, LIST_OR );
			$title_conds[] = $dbr->makeList( array( 'page_namespace' => $ns, $titles ), LIST_AND );
		}

		$conds = array(
			'page_latest=rev_id',
			'rev_text_id=old_id',
			$dbr->makeList( $title_conds, LIST_OR ),
		);

		if ( count( $this->skipLanguages ) ) {
			$skiplist = $dbr->makeList( $this->skipLanguages );
			$conds[] = "substring_index(page_title, '/', -1) NOT IN ($skiplist)";
		}

		$rows = $dbr->select(
			array( 'page', 'revision', 'text' ),
			array( 'page_title', 'page_namespace', 'old_text', 'old_flags' ),
			$conds,
			__METHOD__
		);

		$messagesContents = array();
		foreach ( $rows as $row ) {
			$title = Title::makeTitle( $row->page_namespace, $row->page_title );
			$messagesContents[] = array( $title, Revision::getRevisionText( $row ) );
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
			$comment ? $comment : 'Marking as fuzzy',
			EDIT_FORCE_BOT | EDIT_UPDATE,
			false, /*base revision id*/
			FuzzyBot::getUser()
		);

		$success = $status === true || ( is_object( $status ) && $status->isOK() );
		$this->reportProgress( $success ? 'OK' : 'FAILED', $title );
	}
}

$maintClass = 'Fuzzy';
require_once RUN_MAINTENANCE_IF_MAIN;
