<?php
/**
 * Command line script to import/update source messages and translations into
 * the wiki database.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2007-2013, Niklas Laxström
 * @copyright Copyright © 2009-2013, Siebrand Mazeland
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

# Override the memory limit for wfShellExec, 100 MB seems to be too little for svn
$wgMaxShellMemory = 1024 * 200;

class SyncGroup extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Import or update source messages and translations into ' .
			'the wiki database.';
		$this->addOption(
			'git',
			'(optional) Use git to retrieve last modified date of i18n files. Will use subversion ' .
			'by default and fallback on filesystem timestamp',
			false, /*required*/
			false /*has arg*/
		);
		$this->addOption(
			'group',
			'Comma separated list of group IDs (can use * as wildcard).',
			true, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'lang',
			'(optional) Comma separated list of language codes or *',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'norc',
			'(optional) Do not add entries to recent changes table',
			false, /*required*/
			false /*has arg*/
		);
		$this->addOption(
			'noask',
			'(optional) Skip all conflicts',
			false, /*required*/
			false /*has arg*/
		);
		$this->addOption(
			'start',
			'(optional) Start of the last export (changes in wiki after will conflict)',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'end',
			'(optional) End of the last export (changes in source after will conflict)',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'nocolor',
			'(optional) Without colors',
			false, /*required*/
			false /*has arg*/
		);
		$this->addOption(
			'core-meta',
			'(optional) Allow export of specific MediaWiki core meta groups ' .
			'(translatewiki.net specific)',
			false, /*required*/
			false /*has arg*/
		);
	}

	public function execute() {
		$groupIds = explode( ',', trim( $this->getOption( 'group' ) ) );
		$groupIds = MessageGroups::expandWildcards( $groupIds );
		$groups = MessageGroups::getGroupsById( $groupIds );

		if ( !count( $groups ) ) {
			$this->output( "ESG2: No valid message groups identified.\n" );
			exit( 1 );
		}

		$start = $this->getOption( 'start', false ) ?
			strtotime( $this->getOption( 'start' ) ) :
			false;
		$end = $this->getOption( 'end', false ) ?
			strtotime( $this->getOption( 'end' ) ) :
			false;

		$this->output( "Conflict times: " . wfTimestamp( TS_ISO_8601, $start ) . " - " .
		wfTimestamp( TS_ISO_8601, $end ) . "\n" );

		$codes = array_filter( array_map( 'trim', explode( ',', $this->getOption( 'lang' ) ) ) );

		$supportedCodes = array_keys( TranslateUtils::getLanguageNames( 'en' ) );
		ksort( $supportedCodes );

		if ( $codes[0] === '*' ) {
			$codes = $supportedCodes;
		}

		$coreMeta = $this->hasOption( 'core-meta' ) ? true : false;

		/** @var MessageGroup $group */
		foreach ( $groups as $groupId => &$group ) {
			if ( $group->isMeta() ) {
				if ( !$coreMeta ) {
					$this->output( "Skipping meta message group $groupId.\n" );
					continue;
				}

				// Special case for MediaWiki core branches with pattern "core-1*"
				if ( strstr( $group->getId(), 'core-1', true ) !== '' ) {
					$this->output( "Skipping meta message group $groupId.\n" );
					continue;
				}
			}

			$this->output( "{$group->getLabel()} ", $group );

			foreach ( $codes as $code ) {
				// No sync possible for unsupported language codes.
				if ( !in_array( $code, $supportedCodes ) ) {
					$this->output( "Unsupported code " . $code . ": skipping.\n" );
					continue;
				}

				if ( $group instanceof FileBasedMessageGroup ) {
					/** @var FileBasedMessageGroup $group */
					$file = $group->getSourceFilePath( $code );
				} else {
					/** @var MessageGroupOld $group */
					$file = $group->getMessageFileWithPath( $code );
				}

				if ( !$file ) {
					continue;
				}

				if ( !file_exists( $file ) ) {
					continue;
				}

				$cs = new ChangeSyncer( $group, $this );
				$cs->interactive = $this->hasOption( 'noask' ) ? false : true;
				$cs->nocolor = $this->hasOption( 'nocolor' ) ? true : false;
				$cs->norc = $this->hasOption( 'norc' ) ? true : false;

				# Guess last modified date of the file from either git, svn or filesystem
				if ( $this->hasOption( 'git' ) ) {
					$ts = $cs->getTimestampsFromGit( $file );
				} else {
					$ts = $cs->getTimestampsFromSvn( $file );
				}
				if ( !$ts ) {
					$ts = $cs->getTimestampsFromFs( $file );
				}

				$this->output( "Modify time for $code: " . wfTimestamp( TS_ISO_8601, $ts ) . "\n" );

				$cs->checkConflicts( $code, $start, $end, $ts );
			}

			unset( $group );
		}
		// Print timestamp if the user wants to store it
		$this->output( wfTimestamp( TS_RFC2822 ) . "\n" );
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
 * Simple external changes syncer and conflict resolution.
 */
class ChangeSyncer {
	/** @var bool  Don't list changes in recent changes table. */
	public $norc = false;

	/** @var bool Whether the script can ask questions. */
	public $interactive = true;

	/** @var bool Disable color output. */
	public $nocolor = false;

	/** @var MessageGroup */
	protected $group;

	/** @var SyncGroup */
	protected $caller;

	/**
	 * @param MessageGroup $group Message group to synchronise.
	 * @param SyncGroup $caller Calling SyncGroup object so that messages
	 * can be relayed back.
	 */
	public function __construct( MessageGroup $group, SyncGroup $caller ) {
		$this->group = $group;
		$this->caller = $caller;
	}

	// svn component from pecl doesn't seem to have this in quick sight
	/**
	 * Fetch last changed timestamp for a versioned file for conflict resolution.
	 * @param string $file Filename with full path.
	 * @return string Timestamp or false.
	 */
	public function getTimestampsFromSvn( $file ) {
		$file = escapeshellarg( $file );
		$retval = 0;
		$output = wfShellExec( "svn info $file 2>/dev/null", $retval );

		if ( $retval ) {
			return false;
		}

		$matches = array();
		// PHP doesn't allow foo || return false;
		// Thank
		// you
		// PHP (for being an ass)!
		$regex = '^Last Changed Date: (.*) \(';
		$ok = preg_match( "~$regex~m", $output, $matches );
		if ( $ok ) {
			return strtotime( $matches[1] );
		}

		return false;
	}

	/**
	 * Fetch last changed timestamp for a versioned file for conflict resolution.
	 * @param string $file Filename with full path.
	 * @return string|bool Timestamp or false.
	 */
	public function getTimestampsFromGit( $file ) {
		$file = escapeshellarg( $file );
		$retval = 0;
		$output = wfShellExec( "git log -n 1 --format=%cd $file", $retval );

		if ( $retval ) {
			return false;
		}

		return strtotime( $output );
	}

	/**
	 * Fetch last changed timestamp for any file for conflict resolution.
	 * @param string $file Filename with full path.
	 * @return string Timestamp or false.
	 */
	public function getTimestampsFromFs( $file ) {
		if ( !file_exists( $file ) ) {
			return false;
		}

		$stat = stat( $file );

		return $stat['mtime'];
	}

	/**
	 * Do some conflict resolution for translations.
	 * @param string $code Language code.
	 * @param bool|int $startTs Time of the last export (changes in wiki after
	 * this will conflict)
	 * @param bool|int $endTs Time of the last export (changes in source before
	 * this won't conflict)
	 * @param bool|int $changeTs When change happened in the source.
	 */
	public function checkConflicts( $code, $startTs = false, $endTs = false, $changeTs = false ) {
		$messages = $this->group->load( $code );

		if ( !count( $messages ) ) {
			return;
		}

		$collection = $this->group->initCollection( $code );
		$collection->filter( 'ignored' );
		$collection->loadTranslations();

		foreach ( $messages as $key => $translation ) {
			if ( !isset( $collection[$key] ) ) {
				continue;
			}

			// @todo Temporary exception. Should be fixed elsewhere more generically.
			if ( $translation == '{{PLURAL:GETTEXT|}}' ) {
				return;
			}

			$title = Title::makeTitleSafe( $this->group->getNamespace(), "$key/$code" );

			$page = $title->getPrefixedText();

			if ( $collection[$key]->translation() === null ) {
				$this->caller->myOutput( "Importing $page as a new translation\n" );
				$this->import( $title, $translation, 'Importing a new translation' );
				continue;
			}

			$current = str_replace( TRANSLATE_FUZZY, '', $collection[$key]->translation() );
			$translation = str_replace( TRANSLATE_FUZZY, '', $translation );
			if ( $translation === $current ) {
				continue;
			}

			$this->caller->myOutput( "Conflict in " . $this->color( 'bold', $page ) . "!", $page );

			$iso = 'xnY-xnm-xnd"T"xnH:xni:xns';
			$lang = RequestContext::getMain()->getLanguage();

			// Finally all is ok, now lets start comparing timestamps
			// Make sure we are comparing timestamps in same format
			$wikiTs = $this->getLastGoodChange( $title, $startTs );
			if ( $wikiTs ) {
				$wikiTs = wfTimestamp( TS_UNIX, $wikiTs );
				$wikiDate = $lang->sprintfDate( $iso, wfTimestamp( TS_MW, $wikiTs ) );
			} else {
				$wikiDate = 'Unknown';
			}

			if ( $startTs ) {
				$startTs = wfTimestamp( TS_UNIX, $startTs );
			}

			if ( $endTs ) {
				$endTs = wfTimestamp( TS_UNIX, $endTs );
			}
			if ( $changeTs ) {
				$changeTs = wfTimestamp( TS_UNIX, $changeTs );
				$changeDate = $lang->sprintfDate( $iso, wfTimestamp( TS_MW, $changeTs ) );
			} else {
				$changeDate = 'Unknown';
			}

			if ( $changeTs ) {
				if ( $wikiTs > $startTs && $changeTs <= $endTs ) {
					$this->caller->myOutput( " →Changed in wiki after export: IGNORE", $page );
					continue;
				} elseif ( !$wikiTs || ( $changeTs > $endTs && $wikiTs < $startTs ) ) {
					$this->caller->myOutput( " →Changed in source after export: IMPORT", $page );
					$this->import(
						$title,
						$translation,
						'Updating translation from external source'
					);
					continue;
				}
			}

			if ( !$this->interactive ) {
				continue;
			}

			$this->caller->myOutput( " →Needs manual resolution", $page );
			$this->caller->myOutput( "Source translation at $changeDate:" );
			$this->caller->myOutput( $this->color( 'blue', $translation ) . "\n" );
			$this->caller->myOutput( "Wiki translation at $wikiDate:" );
			$this->caller->myOutput( $this->color( 'green', $current ) . "\n" );

			do {
				$this->caller->myOutput( "Resolution: [S]kip [I]mport [C]onflict: ", 'foo' );
				// @todo Will this work? (probably not; how to fix?)
				$action = fgets( STDIN );
				$action = strtoupper( trim( $action ) );
				$action = strtoupper( trim( $action ) );

				if ( $action === 'S' ) {
					break;
				}

				if ( $action === 'I' ) {
					$this->import(
						$title,
						$translation,
						'Updating translation from external source'
					);
					break;
				}

				if ( $action === 'C' ) {
					$this->import(
						$title,
						TRANSLATE_FUZZY . $translation,
						'Edit conflict between wiki and source'
					);
					break;
				}
			} while ( true );
		}
	}

	/**
	 * Colors text for shell output
	 * @param string $color Either blue, green or bold.
	 * @param string $text
	 * @return string
	 */
	public function color( $color, $text ) {
		switch ( $color ) {
			case 'blue':
				return "\033[1;34m$text\033[0m";
			case 'green':
				return "\033[1;32m$text\033[0m";
			case 'bold':
				return "\033[1m$text\033[0m";
			default:
				return $text;
		}
	}

	/**
	 * Try to identify when the translation was last changed in the wiki.
	 * @param Title $title Title of the page which contains translation.
	 * @param int|bool $startTs Timestamp how far back to go before giving up.
	 * @return int|bool Timestamp or false.
	 */
	public function getLastGoodChange( $title, $startTs = false ) {
		global $wgTranslateFuzzyBotName;

		$wikiTs = false;
		$revision = Revision::newFromTitle( $title );
		while ( $revision ) {
			// No need to go back further
			if ( $startTs && $wikiTs && ( $wikiTs < $startTs ) ) {
				break;
			}

			if ( $revision->getRawUserText() === $wgTranslateFuzzyBotName ) {
				$revision = $revision->getPrevious();
				continue;
			}

			$wikiTs = wfTimestamp( TS_UNIX, $revision->getTimestamp() );
			break;
		}

		return $wikiTs;
	}

	/**
	 * Does the actual edit.
	 * @param Title $title
	 * @param string $translation
	 * @param string $comment Edit summary.
	 */
	public function import( $title, $translation, $comment ) {
		$flags = EDIT_FORCE_BOT;
		if ( $this->norc ) {
			$flags |= EDIT_SUPPRESS_RC;
		}

		$wikipage = new WikiPage( $title );
		$this->caller->myOutput( "Importing {$title->getPrefixedText()}: ", $title );
		$status = $wikipage->doEdit(
			$translation, $comment, $flags, false, FuzzyBot::getUser()
		);
		$success = $status === true || ( is_object( $status ) && $status->isOK() );
		$this->caller->myOutput( $success ? 'OK' : 'FAILED', $title );
	}
}

$maintClass = 'SyncGroup';
require_once RUN_MAINTENANCE_IF_MAIN;
