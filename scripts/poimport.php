<?php
/**
 * Imports gettext files exported from Special:Translate back.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2007-2013 Niklas Laxström, Siebrand Mazeland
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

class PoImport extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Po file importer (does not make changes unless specified).';
		$this->addOption(
			'file',
			'Gettext file to import (Translate specific formatting)',
			true, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'user',
			'User who makes edits to wiki',
			true, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'really',
			'(optional) Actually make changes',
			false, /*required*/
			false /*has arg*/
		);
	}

	public function execute() {
		// Parse the po file.
		$p = new PoImporter( $this->getOption( 'file' ) );
		$p->setProgressCallback( array( $this, 'myOutput' ) );
		list( $changes, $group ) = $p->parse();

		if ( !count( $changes ) ) {
			$this->output( "No changes to import\n" );
			exit( 0 );
		}

		// Import changes to wiki.
		$w = new WikiWriter(
			$changes,
			$group,
			$this->getOption( 'user' ),
			!$this->hasOption( 'really' )
		);

		$w->setProgressCallback( array( $this, 'myOutput' ) );
		$w->execute();
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
 * Parses a po file that has been exported from Mediawiki. Other files are not
 * supported.
 */
class PoImporter {
	/** @var callable Function to report progress updates */
	protected $progressCallback;

	/**
	 * Path to file to parse.
	 * @var bool|string
	 */
	private $file = false;

	/**
	 * @param string $file File to import
	 */
	public function __construct( $file ) {
		$this->file = $file;
	}

	public function setProgressCallback( $callback ) {
		$this->progressCallback = $callback;
	}

	/// @see Maintenance::output for param docs
	protected function reportProgress( $text, $channel = null, $severity = 'status' ) {
		if ( is_callable( $this->progressCallback ) ) {
			$useErrorOutput = $severity === 'error';
			call_user_func( $this->progressCallback, $text, $channel, $useErrorOutput );
		}
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
			$this->reportProgress( "Detected language as $code", 'code' );
		} else {
			$this->reportProgress( 'Unable to determine language code', 'code', 'error' );

			return false;
		}

		if ( preg_match( '/X-Message-Group:\s+(.*)\\\n/', $data, $matches ) ) {
			$groupId = $matches[1];
			$this->reportProgress( "Detected message group as $groupId", 'group' );
		} else {
			$this->reportProgress( 'Unable to determine message group', 'group', 'error' );

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
					$this->reportProgress( "Skipping empty translation in the po file for $key!\n" );
				} else {
					if ( $oldtranslation === '' ) {
						$this->reportProgress( "New translation for $key\n" );
					} else {
						$this->reportProgress( "Translation of $key differs:\n$translation\n" );
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
	/** @var callable Function to report progress updates */
	protected $progressCallback;

	protected $user;

	private $changes = array();
	private $dryrun = true;
	private $group = null;

	/**
	 * @param array $changes Array of key/langcode => translation.
	 * @param string $groupId Group ID.
	 * @param string $user User who makes the edits in wiki.
	 * @param bool $dryrun Do not do anything that affects the database.
	 */
	public function __construct( $changes, $groupId, $user, $dryrun = true ) {
		$this->changes = $changes;
		$this->group = MessageGroups::getGroup( $groupId );
		$this->user = User::newFromName( $user );
		$this->dryrun = $dryrun;
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

	/**
	 * Updates pages on by one.
	 */
	public function execute() {
		if ( !$this->group ) {
			$this->reportProgress( 'Given group does not exist.', 'groupId', 'error' );

			return;
		}

		if ( !$this->user->idForName() ) {
			$this->reportProgress( 'Given user does not exist.', 'user', 'error' );

			return;
		}

		$count = count( $this->changes );
		$this->reportProgress( "Going to update $count pages.", 'pagecount' );

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
			$this->reportProgress( 'INVALID TITLE!', $page, 'error' );

			return;
		}
		$this->reportProgress( "Updating {$title->getPrefixedText()}... ", $title );

		if ( $this->dryrun ) {
			$this->reportProgress( 'DRY RUN!', $title );

			return;
		}

		$page = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( $text, $title );
		$status = $page->doEditContent(
			$content,
			'Updating translation from gettext import',
			0,
			false,
			$this->user
		);

		if ( $status === true || ( is_object( $status ) && $status->isOK() ) ) {
			$this->reportProgress( 'OK!', $title );
		} else {
			$this->reportProgress( 'Failed!', $title );
		}
	}
}

$maintClass = 'PoImport';
require_once RUN_MAINTENANCE_IF_MAIN;
