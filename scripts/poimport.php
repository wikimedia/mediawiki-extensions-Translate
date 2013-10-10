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
		$this->mDescription = 'Po file importer';
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
			'Does not do anything unless this is specified',
			false, /*required*/
			false /*has arg*/
		);
	}

	public function execute() {
		/**
		 * Parse the po file.
		 */
		$p = new PoImporter( $this->getOption( 'file' ), $this );
		list( $changes, $group ) = $p->parse();

		if ( !count( $changes ) ) {
			$this->output( 'No changes to import' );
			exit( 0 );
		}

		/**
		 * Import changes to wiki.
		 */
		$w = new WikiWriter(
			$changes,
			$group,
			$this->getOption( 'user' ),
			!$this->hasOption( 'really' ),
			$this
		);
		$w->execute();
	}

	/**
	 * Public alternative for protected Maintenance::output() as we need to get
	 * messages from the ChangeSyncer class to the commandline.
	 * @param string $out The text to show to the user
	 * @param string $channel Unique identifier for the channel.
	 */
	public function myOutput( $out, $channel = null ) {
		$this->output( $out, $channel );
	}
}

/**
 * Parses a po file that has been exported from Mediawiki. Other files are not
 * supported.
 */
class PoImporter {
	/** @var PoImport Calling object */
	private $caller;

	/**
	 * Path to file to parse.
	 * @var bool|string
	 */
	private $file = false;

	/**
	 * @param $file File to import
	 * @param PoImport $caller Calling object
	 */
	public function __construct( $file, $caller = null ) {
		$this->poImport = $caller;
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
			$this->caller->myOutput( "Detected language as $code", 'code' );
		} else {
			$this->caller->myOutput( 'Unable to determine language code', 'code' );

			return false;
		}

		if ( preg_match( '/X-Message-Group:\s+(.*)\\\n/', $data, $matches ) ) {
			$groupId = $matches[1];
			$this->caller->myOutput( "Detected message group as $groupId", 'group' );
		} else {
			$this->caller->myOutput( 'Unable to determine message group', 'group' );

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
					$this->caller->myOutput( "Skipping empty translation in the po file for $key!", 'empty' );
				} else {
					if ( $oldtranslation === '' ) {
						$this->caller->myOutput( "New translation for $key", 'new' );
					} else {
						$this->caller->myOutput( "Translation of $key differs:\n$translation", 'differs' );
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
	/** @var PoImport Calling object */
	public $caller;

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
	 * @param PoImport $caller Calling object.
	 */
	public function __construct( $changes, $groupId, $user, $dryrun = true, $caller = null ) {
		$this->changes = $changes;
		$this->dryrun = $dryrun;
		$this->caller = $caller;
		$this->group = MessageGroups::getGroup( $groupId );
		if ( !$this->group ) {
			$this->caller->myOutput( "Group $groupId does not exist.", 'groupId' );

			return;
		}

		$this->user = User::newFromName( $user );
		if ( !$this->user->idForName() ) {
			$this->caller->myOutput( "User $user does not exist.", 'user' );

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
		$this->caller->myOutput( "Going to update $count pages.", 'pagecount' );

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
			$this->caller->myOutput( 'INVALID TITLE!', $page );

			return;
		}
		$this->caller->myOutput( "Updating {$title->getPrefixedText()}... ", $title );

		if ( $this->dryrun ) {
			$this->caller->myOutput( 'DRY RUN!', $title );

			return;
		}

		$article = new Article( $title, 0 );

		$status = $article->doEdit(
			$text,
			'Updating translation from gettext import',
			0,
			false,
			$this->user
		);

		if ( $status === true || ( is_object( $status ) && $status->isOK() ) ) {
			$this->caller->myOutput( 'OK!', $title );
		} else {
			$this->caller->myOutput( 'Failed!', $title );
		}
	}
}

$maintClass = 'PoImport';
require_once RUN_MAINTENANCE_IF_MAIN;
