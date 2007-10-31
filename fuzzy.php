<?php

#$optionsWithArgs = array( 'u', 's' );

require_once( 'commandLine.inc' );

if ( count( $args ) == 0 || isset( $options['help'] ) ) {
	print <<<EOT
Fuzzy bot command line script

Usage: php fuzzy.php [options...] <messages>

Options:
  -really      Don't just run dry-run

EOT;
	exit( 1 );
}

class FuzzyBot {

	private $titles = array();
	private $dryrun = true;
	private $allclear = false;
	public function __construct( $titles, $dryrun = true ) {
		$this->titles = $titles;
		$this->dryrun = $dryrun;

		global $wgTranslateFuzzyBotName, $wgUser;

		if ( !isset( $wgTranslateFuzzyBotName ) ) {
			echo "\$wgTranslateFuzzyBotName is not set\n";
			return;
		}

		$wgUser = User::newFromName( $wgTranslateFuzzyBotName );

		if ( $wgUser->isAnon() ) {
			echo "Creating user $wgTranslateFuzzyBotName\n";
			$wgUser->addToDatabase();
		}

		$this->allclear = true;

	}

	public function execute() {
		if ( !$this->allclear ) {
			return;
		}

		$msgs = $this->getPages();
		$count = count($msgs);
		echo "Found $count pages to update.\n";

		foreach ( $msgs as $title => $text ) {
			$this->updateMessage( $title, $text );
		}

	}

	private function getPages() {
		$dbr = wfGetDB( DB_SLAVE );

		$search_titles = array();
		foreach ( $this->titles as $title ) {
			$title = TranslateUtils::title( $title, '' );
			$search_titles[] = "page_title LIKE '{$dbr->escapeLike( $title )}%%'";
		}

		$condArray = array(
			'page_is_redirect'  => 0,
			'page_namespace'    => NS_MEDIAWIKI,
			'page_latest=rev_id',
			'rev_text_id=old_id',
			$dbr->makeList( $search_titles, LIST_OR ),
		);

		$conds = $dbr->makeList( $condArray, LIST_AND);

		$rows = $dbr->select(
			array( 'page', 'revision', 'text' ),
			array( 'page_title', 'old_text', 'old_flags' ),
			$conds,
			__METHOD__
		);

		$messagesContents = array();
		foreach ( $rows as $row ) {
			$messagesContents[$row->page_title] = Revision::getRevisionText( $row );
		}

		$rows->free();

		return $messagesContents;
	}

	private function updateMessage( $title, $text ) {
		global $wgTitle;
		$wgTitle = Title::newFromText( "Mediawiki:$title" );

		echo "Updating {$wgTitle->getPrefixedText()}... ";
		if ( !$wgTitle instanceof Title ) {
			echo "INVALID TITLE!\n";
			return;
		}

		if ( $this->dryrun ) {
			echo "DRY RUN!\n";
			return;
		}

		$wgArticle = new Article( $wgTitle );

		$success = $wgArticle->doEdit( TRANSLATE_FUZZY . $text, 'Marking as fuzzy', EDIT_FORCE_BOT );

		if ( $success ) {
			echo "OK!\n";
		} else {
			echo "Failed!\n";
		}

	}

}

$bot = new FuzzyBot( $args, !isset( $options['really'] ) );

$bot->execute();
/*

$wgArticle = new Article( $wgTitle );

# Read the text
$text = file_get_contents( 'php://stdin' );

# Do the edit
print "Saving... ";
$success = $wgArticle->doEdit( $text, $summary, 
	( $minor ? EDIT_MINOR : 0 ) |
	( $bot ? EDIT_FORCE_BOT : 0 ) | 
	( $autoSummary ? EDIT_AUTOSUMMARY : 0 ) |
	( $noRC ? EDIT_SUPPRESS_RC : 0 ) );
if ( $success ) {
	print "done\n";
} else {
	print "failed\n";
	exit( 1 );
}

*/