<?php
/**
 * Imports gettext files exported from Special:Translate back.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2007-2008 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */

$optionsWithArgs = array( 'file', 'user' );
require( dirname( __FILE__ ) . '/cli.inc' );

function showUsage() {
	STDERR( <<<EOT
Po file importer

Usage: php poimport.php [options...]

Options:
  --file      Po file to import
  --user      User who makes edits to wiki
  --really    Does not do anything unless this is specified.

EOT
	);
	exit( 1 );
}

if ( isset( $options['help'] ) ) {
	showUsage();
}

if ( !isset( $options['file'] ) ) {
	STDERR( "You need to specify input file" );
	exit( 1 );
}

/**
 * Parse the po file.
 */
$p = new PoImporter( $options['file'] );
list( $changes, $group ) = $p->parse();

if ( !isset( $options['user'] ) ) {
	STDERR( "You need to specify user name for wiki import" );
	exit( 1 );
}

if ( !count( $changes ) ) {
	STDOUT( "No changes to import" );
	exit( 0 );
}

/**
 * Import changes to wiki.
 */
$w = new WikiWriter( $changes, $group, $options['user'], !isset( $options['really'] ) );
$w->execute();

/**
 * Parses a po file that has been exported from Mediawiki. Other files are not
 * supported.
 */
class PoImporter {
	/**
	 * Path to file to parse.
	 * @var bool|string
	 */
	private $file = false;

	public function __construct( $file ) {
		$this->file = $file;
	}

	/**
	 * Loads translations for comparison.
	 *
	 * @param string $id Id of MessageGroup.
	 * @param string $code Language code.
	 * @return MessageCollection
	 */
	protected function initMessages( $id, $code ) {
		$group = MessageGroups::getGroup( $id );

		$messages = $group->initCollection( $code );
		$messages->setInfile( $group->load( $code ) );
		$messages->loadTranslations();

		return $messages;
	}

	/**
	 * Parses relevant stuff from the po file.
	 * @return array|bool
	 */
	public function parse() {
		$data = file_get_contents( $this->file );
		$data = str_replace( "\r\n", "\n", $data );

		$matches = array();
		if ( preg_match( '/X-Language-Code:\s+(.*)\\\n/', $data, $matches ) ) {
			$code = $matches[1];
			STDOUT( "Detected language as $code" );
		} else {
			STDERR( "Unable to determine language code" );
			return false;
		}

		if ( preg_match( '/X-Message-Group:\s+(.*)\\\n/', $data, $matches ) ) {
			$groupId = $matches[1];
			STDOUT( "Detected message group as $groupId" );
		} else {
			STDERR( "Unable to determine message group" );
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
				if ( !isset( $contents[$key] ) ) {
					continue;
				}
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

			$oldtranslation = (string)$contents[$key]->translation();

			if ( $translation !== $oldtranslation ) {
				if ( $translation === '' ) {
					STDOUT( "Skipping empty translation in the po file for $key!" );
				} else {
					if ( $oldtranslation === '' ) {
						STDOUT( "New translation for $key" );
					} else {
						STDOUT( "Translation of $key differs:\n$translation\n" );
					}
					$changes["$key/$code"] = $translation;
				}
			}

		}

		return array( $changes, $groupId );
	}
}

/**
 * Import changes to wiki as given user
 */
class WikiWriter {
	private $changes = array();
	private $dryrun = true;
	private $allclear = false;
	private $group = null;
	protected $user;

	/**
	 * @param array $changes Array of key/langcode => translation.
	 * @param string $groupId Group ID.
	 * @param string $user User who makes the edits in wiki.
	 * @param bool $dryrun Do not do anything that affects the database.
	 */
	public function __construct( $changes, $groupId, $user, $dryrun = true ) {
		$this->changes = $changes;
		$this->dryrun = $dryrun;
		$this->group = MessageGroups::getGroup( $groupId );
		if ( !$this->group ) {
			STDERR( "Group $groupId does not exist." );
			return;
		}

		$this->user = User::newFromName( $user );
		if ( !$this->user->idForName() ) {
			STDERR( "User $user does not exist." );
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

		$count = count( $this->changes );
		STDOUT( "Going to update $count pages." );

		$ns = $this->group->getNamespace();

		foreach ( $this->changes as $title => $text ) {
			$this->updateMessage( $ns, $title, $text );
		}
	}

	/**
	 * Actually adds the new translation.
	 * @param int $namespace
	 * @param string $page
	 * @param string $text
	 */
	private function updateMessage( $namespace, $page, $text ) {
		$title = Title::makeTitleSafe( $namespace, $page );

		if ( !$title instanceof Title ) {
			STDOUT( "INVALID TITLE!", $page );
			return;
		}
		STDOUT( "Updating {$title->getPrefixedText()}... ", $title );

		if ( $this->dryrun ) {
			STDOUT( "DRY RUN!", $title );
			return;
		}

		$article = new Article( $title, 0 );

		$status = $article->doEdit( $text, 'Updating translation from gettext import', 0, false, $this->user );

		if ( $status === true || ( is_object( $status ) && $status->isOK() ) ) {
			STDOUT( "OK!", $title );
		} else {
			STDOUT( "Failed!", $title );
		}
	}
}
