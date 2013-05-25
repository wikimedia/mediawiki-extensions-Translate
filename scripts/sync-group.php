<?php
/**
 * Command line script to import/update source messages and translations into
 * the wiki database.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2007-2013, Niklas Laxström
 * @copyright Copyright © 2009-2013, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */

/// @cond

$options = array( 'git' );
$optionsWithArgs = array( 'group', 'lang', 'start', 'end' );
require __DIR__ . '/cli.inc';

# Override the memory limit for wfShellExec, 100 MB seems to be too little for svn
$wgMaxShellMemory = 1024 * 200;

function showUsage() {
	STDERR( <<<EOT
Options:
  --group       Comma separated list of group IDs (can use * as wildcard)
  --git         Use git to retrieve last modified date of i18n files. Will use
                subversion by default and fallback on filesystem timestamp
  --lang        Comma separated list of language codes or *
  --norc        Do not add entries to recent changes table
  --help        This help message
  --noask       Skip all conflicts
  --start       Start of the last export (changes in wiki after will conflict)
  --end         End of the last export (changes in source after will conflict)
  --nocolor     Without colors
EOT
	);
	exit( 1 );
}

if ( isset( $options['help'] ) ) {
	showUsage();
}

if ( !isset( $options['group'] ) ) {
	STDERR( "ESG1: Message group id must be supplied with group parameter." );
	exit( 1 );
}

$groupIds = explode( ',', trim( $options['group'] ) );
$groupIds = MessageGroups::expandWildcards( $groupIds );
$groups = MessageGroups::getGroupsById( $groupIds );

if ( !count( $groups ) ) {
	STDERR( "ESG2: No valid message groups identified." );
	exit( 1 );
}

if ( !isset( $options['lang'] ) || strval( $options['lang'] ) === '' ) {
	STDERR( "ESG3: List of language codes must be supplied with lang parameter." );
	exit( 1 );
}

$start = isset( $options['start'] ) ? strtotime( $options['start'] ) : false;
$end = isset( $options['end'] ) ? strtotime( $options['end'] ) : false;

STDOUT( "Conflict times: " . wfTimestamp( TS_ISO_8601, $start ) . " - " .
	wfTimestamp( TS_ISO_8601, $end ) );

$codes = array_filter( array_map( 'trim', explode( ',', $options['lang'] ) ) );

$supportedCodes = array_keys( TranslateUtils::getLanguageNames( 'en' ) );
ksort( $supportedCodes );

if ( $codes[0] === '*' ) {
	$codes = $supportedCodes;
}

/**
 * @var MessageGroup $group
 */
foreach ( $groups as &$group ) {
	// No sync possible for meta groups
	if ( $group->isMeta() ) {
		continue;
	}

	STDOUT( "{$group->getLabel()} ", $group );

	foreach ( $codes as $code ) {
		// No sync possible for unsupported language codes.
		if ( !in_array( $code, $supportedCodes ) ) {
			STDOUT( "Unsupported code " . $code . ": skipping." );
			continue;
		}

		if ( $group instanceof FileBasedMessageGroup ) {
			/**
			 * @var FileBasedMessageGroup $group
			 */
			$file = $group->getSourceFilePath( $code );
		} else {
			/**
			 * @var MessageGroupOld $group
			 */
			$file = $group->getMessageFileWithPath( $code );
		}

		if ( !$file ) {
			continue;
		}

		if ( !file_exists( $file ) ) {
			continue;
		}

		$cs = new ChangeSyncer( $group );
		if ( isset( $options['norc'] ) ) {
			$cs->norc = true;
		}

		if ( isset( $options['noask'] ) ) {
			$cs->interactive = false;
		}

		if ( isset( $options['nocolor'] ) ) {
			$cs->nocolor = true;
		}

		# Guess last modified date of the file from either git, svn or filesystem
		$ts = false;
		if ( isset( $options['git'] ) ) {
			$ts = $cs->getTimestampsFromGit( $file );
		} else {
			$ts = $cs->getTimestampsFromSvn( $file );
		}
		if ( !$ts ) {
			$ts = $cs->getTimestampsFromFs( $file );
		}

		STDOUT( "Modify time for $code: " . wfTimestamp( TS_ISO_8601, $ts ) );

		$cs->checkConflicts( $code, $start, $end, $ts );
	}

	unset( $group );
}

/// @endcond

/**
 * Simple external changes syncer and conflict resolution.
 */
class ChangeSyncer {
	public $group; ///< \type{MessageGroup}
	public $norc = false; ///< \bool Don't list changes in recent changes table.
	public $interactive = true; ///< \bool Whether the script can ask questions.
	public $nocolor = false; ///< \bool Disable color output.

	public function __construct( MessageGroup $group ) {
		$this->group = $group;
	}

	// svn component from pecl doesn't seem to have this in quick sight
	/**
	 * Fetch last changed timestamp for a versioned file for conflict resolution.
	 * @param $file \string Filename with full path.
	 * @return \string Timestamp or false.
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
	 * @param $file \string Filename with full path.
	 * @return \string Timestamp or false.
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
	 * @param $file \string Filename with full path.
	 * @return \string Timestamp or false.
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
				// STDOUT( "Unknown key $key" );
				continue;
			}

			// @todo Temporary exception. Should be fixed elsewhere more generically.
			if ( $translation == '{{PLURAL:GETTEXT|}}' ) {
				return;
			}

			$title = Title::makeTitleSafe( $this->group->getNamespace(), "$key/$code" );

			$page = $title->getPrefixedText();

			if ( $collection[$key]->translation() === null ) {
				STDOUT( "Importing $page as a new translation" );
				$this->import( $title, $translation, 'Importing a new translation' );
				continue;
			}

			$current = str_replace( TRANSLATE_FUZZY, '', $collection[$key]->translation() );
			$translation = str_replace( TRANSLATE_FUZZY, '', $translation );
			if ( $translation === $current ) {
				continue;
			}

			STDOUT( "Conflict in " . $this->color( 'bold', $page ) . "!", $page );

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
					STDOUT( " →Changed in wiki after export: IGNORE", $page );
					continue;
				} elseif ( !$wikiTs || ( $changeTs > $endTs && $wikiTs < $startTs ) ) {
					STDOUT( " →Changed in source after export: IMPORT", $page );
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

			STDOUT( " →Needs manual resolution", $page );
			STDOUT( "Source translation at $changeDate:" );
			STDOUT( $this->color( 'blue', $translation ) . "\n" );
			STDOUT( "Wiki translation at $wikiDate:" );
			STDOUT( $this->color( 'green', $current ) . "\n" );

			do {
				STDOUT( "Resolution: [S]kip [I]mport [C]onflict: ", 'foo' );
				$action = fgets( STDIN );
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
	 * @param $color \string Either blue, green or bold.
	 * @param $text \string
	 * @return \string
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
	 * @param $title Title
	 * @param $translation \string
	 * @param $comment \string Edit summary.
	 */
	public function import( $title, $translation, $comment ) {
		$context = RequestContext::getMain();
		$oldUser = $context->getUser();
		$context->setUser( FuzzyBot::getUser() );

		$flags = EDIT_FORCE_BOT;
		if ( $this->norc ) {
			$flags |= EDIT_SUPPRESS_RC;
		}

		$article = new Article( $title, 0 );
		STDOUT( "Importing {$title->getPrefixedText()}: ", $title );
		$status = $article->doEdit( $translation, $comment, $flags );
		$success = $status === true || ( is_object( $status ) && $status->isOK() );
		STDOUT( $success ? 'OK' : 'FAILED', $title );

		$context->setUser( $oldUser );
	}
}

// Print timestamp if the user wants to store it
STDOUT( wfTimestamp( TS_RFC2822 ) );
