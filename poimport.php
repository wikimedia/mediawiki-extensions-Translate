<?php
/**
 * Imports po files exported from Special:Translate back.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2007-2008 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

$optionsWithArgs = array( 'file', 'user' );

$IP = "../../maintenance/";
require_once( $IP . 'commandLine.inc' );

function showUsage() {
	print <<<EOT
Po file importer

Usage: php poimport.php [options...]

Options:
  --file      Po file to import
  --user      User who makes edits to wiki
  --really    Doesn't do anything unless this is specified.

EOT;
	exit( 1 );
}

if ( isset( $options['help'] ) ) {
	showUsage();
}

if (!isset($options['file'])) {
	echo "You need to specify input file\n\n";
	showUsage();
}

/*
 * Parse the po file.
 */
$p = new PoImporter( $options['file'] );
$changes = $p->parse();

if (!isset($options['user'])) {
	echo "You need to specify user name for wiki import\n\n";
	showUsage();
}

if (!count($changes)) {
	echo "No changes to import\n";
}

/*
 * Import changes to wiki.
 */
$w = new WikiWriter( $changes, $options['user'], !isset($options['really']) );
$w->execute();

/**
 * Parses a po file that has been exported from Mediawiki. Other files are not
 * supported.
 */
class PoImporter {

	/**
	 * Path to file to parse.
	 */
	private $file = false;

	public function __construct( $file ) {
		$this->file = $file;
	}

	/**
	 * Loads translations for comparison.
	 *
	 * @param $id Id of MessageGroup.
	 * @param $code Language code.
	 * @return MessageCollection
	 */
	protected function initMessages( $id, $code ) {
		$messages = new MessageCollection( $code );
		$group = MessageGroups::getGroup( $id );

		$definitions = $group->getDefinitions();
		foreach ( $definitions as $key => $definition ) {
			$messages->add( new TMessage( $key, $definition ) );
		}

		$bools = $group->getBools();
		foreach ( $bools['optional'] as $key ) {
			if ( isset($messages[$key]) ) { $messages[$key]->optional = true; }
		}
		foreach ( $bools['ignored'] as $key ) {
			if ( isset($messages[$key]) ) { $messages[$key]->ignored = true; }
		}

		$messages->populatePageExistence();
		$messages->populateTranslationsFromDatabase();
		$group->fill( $messages );

		return $messages;
	}

	/**
	 * Parses relevant stuff from the po file.
	 */
	public function parse() {
		$data = file_get_contents( $this->file );
		$data = str_replace( "\r\n", "\n", $data );

		$matches = array();
		if ( preg_match( '/X-Language-Code:\s+([a-zA-Z-_]+)/', $data, $matches ) ) {
			$code = $matches[1];
			echo "Detected language as $code\n";
		} else {
			echo "Unable to determine language code\n";
			return false;
		}

		if ( preg_match( '/X-Message-Group:\s+([a-zA-Z0-9-_]+)/', $data, $matches ) ) {
			$groupId = $matches[1];
			echo "Detected message group as $groupId\n";
		} else {
			echo "Unable to determine message group\n";
			return false;
		}

		$contents = $this->initMessages( $groupId, $code );

		echo "----\n";

		$poformat = '".*"\n?(^".*"$\n?)*';
		$quotePattern = '/(^"|"$\n?)/m';

		$sections = preg_split( '/\n{2,}/', $data );
		$changes = array();
		foreach ( $sections as $section ) {
			$matches = array();
			if ( preg_match( "/^msgctxt\s($poformat)/mx", $section, $matches ) ) {
				// Remove quoting
				$key = preg_replace( $quotePattern, '', $matches[1] );
				// Ignore unknown keys
				if ( !isset($contents[$key]) ) continue;
			} else {
				continue;
			}
			$matches = array();
			if ( preg_match( "/^msgstr\s($poformat)/mx", $section, $matches ) ) {
				// Remove quoting
				$translation = preg_replace( $quotePattern, '', $matches[1] );
				// Restore new lines and remove quoting
				$translation = stripcslashes( $translation );
			} else {
				continue;
			}

			// Fuzzy messages
			if ( preg_match( '/^#, fuzzy$/m', $section ) ) {
				$translation = TRANSLATE_FUZZY . $translation;
			}

			if ( $translation !== (string) $contents[$key]->translation ) {
				echo "Translation of $key differs:\n$translation\n\n";
				$changes["$key/$code"] = $translation;
			}

		}

		return $changes;

	}

}

/**
 * Import changes to MediaWiki namespace as given user
 */
class WikiWriter {

	private $changes = array();
	private $dryrun = true;
	private $allclear = false;
	private $user = '';

	/**
	 * @param $changes Array of key/langcode => translation.
	 * @param $user User who makes the edits in wiki.
	 * @param $dryrun Don't do anything that affects the database.
	 */
	public function __construct( $changes, $user, $dryrun = true ) {
		$this->changes = $changes;
		$this->dryrun = $dryrun;

		global $wgUser;

		$wgUser = User::newFromName( $user );

		if ( !$wgUser->idForName() ) {
			echo "User $user does not exist.\n";
			return;
		}

		$this->allclear = true;

	}

	/**
	 * Updates pages on by one.
	 */
	public function execute() {
		if ( !$this->allclear ) {
			return;
		}

		$count = count($this->changes);
		echo "Going to update $count pages.\n";

		foreach ( $this->changes as $title => $text ) {
			$this->updateMessage( $title, $text );
		}

	}

	/**
	 * Actually adds the new translation.
	 */
	private function updateMessage( $title, $text ) {
		global $wgTitle, $wgArticle;
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

		$success = $wgArticle->doEdit( $text, 'Updating translation from gettext import' );

		if ( $success ) {
			echo "OK!\n";
		} else {
			echo "Failed!\n";
		}

	}

}
