<?php
/**
 * Script to export special page aliases and magic words of extensions.
 *
 * @author Robert Leverington <robert@rhl.me.uk>
 *
 * @copyright Copyright © 2010 Robert Leverington
 * @license GPL-2.0-or-later
 * @file
 */

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$IP = __DIR__ . '/../../../';
}
require_once "$IP/maintenance/Maintenance.php";

/**
 * Maintenance class for the fast export of special page aliases and magic words.
 */
class MagicExport extends Maintenance {
	protected $type;
	protected $target;

	protected $handles = [];
	protected $messagesOld = [];
	protected $extraInformation = [];

	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Export of aliases and magic words for MediaWiki extensions.';
		$this->addOption(
			'target',
			'Target directory for exported files',
			true, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'type',
			'magic or special',
			true, /*required*/
			true /*has arg*/
		);
	}

	public function execute() {
		$this->target = $this->getOption( 'target' );
		$this->type = $this->getOption( 'type' );

		switch ( $this->type ) {
			case 'special':
			case 'magic':
				break;
			default:
				$this->error( 'Invalid type.', 1 );
		}

		$this->openHandles();
		$this->writeHeaders();
		$this->writeFiles();
		$this->writeFooters();
		$this->closeHandles();
	}

	/**
	 * Iterate through all groups, loading current data from the existing
	 * extension and opening message files for message output.
	 *  - If the group does not define a special page alias file or magic
	 *    words file, or that file does not exist, it is ignored silently.
	 *  - If the file does contain a data array (e.g. $aliases) then the
	 *    program exits.
	 */
	protected function openHandles() {
		$this->output( "Opening file handles and loading current data...\n" );

		$groups = MessageGroups::singleton()->getGroups();
		foreach ( $groups as $group ) {
			if ( !$group instanceof MediaWikiExtensionMessageGroup ) {
				continue;
			}

			$conf = $group->getConfiguration();

			$inFile = $outFile = null;

			if ( $this->type === 'special' && isset( $conf['FILES']['aliasFile'] ) ) {
				$inFile  = $conf['FILES']['aliasFileSource'];
				$outFile = $conf['FILES']['aliasFile'];
			}

			if ( $this->type === 'magic' && isset( $conf['FILES']['magicFile'] ) ) {
				$inFile  = $conf['FILES']['magicFileSource'];
				$outFile = $conf['FILES']['magicFile'];
			}

			if ( $inFile === null ) {
				continue;
			}

			$inFile = $group->replaceVariables( $inFile, 'en' );
			$outFile = $this->target . '/' . $outFile;
			$varName = null;

			if ( !is_readable( $inFile ) ) {
				$this->error( "File '$inFile' not readable." );
				continue;
			}

			include $inFile;
			switch ( $this->type ) {
				case 'special':
					if ( isset( $aliases ) ) {
						$this->messagesOld[$group->getId()] = $aliases;
						unset( $aliases );
						$varName = '$aliases';
					} elseif ( isset( $specialPageAliases ) ) {
						$this->messagesOld[$group->getId()] = $specialPageAliases;
						unset( $specialPageAliases );
						$varName = '$specialPageAliases';
					} else {
						$this->error( "File '$inFile' does not contain an aliases array." );
						continue 2;
					}
					break;
				case 'magic':
					if ( !isset( $magicWords ) ) {
						$this->error( "File '$inFile' does not contain a magic words array." );
						continue 2;
					}
					$this->messagesOld[$group->getId()] = $magicWords;
					unset( $magicWords );
					$varName = '$magicWords';
					break;
			}

			wfMkdirParents( dirname( $outFile ), null, __METHOD__ );
			$this->handles[$group->getId()] = fopen( $outFile, 'w' );
			$headerInformation = $this->readHeader( $inFile, $varName );
			fwrite( $this->handles[$group->getId()], $headerInformation['fileBegin'] );
			$this->extraInformation[$group->getId()] = $headerInformation;

			$this->output( "\t{$group->getId()}\n" );
		}
	}

	protected function readHeader( $file, $varName ) {
		$data = file_get_contents( $file );

		// Seek first '*/'.
		$end = strpos( $data, '*/' );

		// But not when it is the english comment
		$varPos = strpos( $data, $varName );
		if ( $varPos && $end && $varPos <= $end ) {
			$end = false;
		}

		if ( $end === false ) {
			$fileBegin = "<?php\n";
		} else {
			// Grab header.
			$fileBegin = substr( $data, 0, $end + 2 );
		}

		// preserve the phpcs codingStandardsIgnoreFile, if exists
		$preserveIgnoreTag = strpos( $data, '@codingStandardsIgnoreFile' ) !== false;

		// preserve the long array syntax, if varName is written with it
		$preserveLongArraySyntax = preg_match(
			'/' . preg_quote( $varName, '/' ) . '\s*=\s*array\s*\(\s*\)\s*;/',
			$data
		);

		// avoid difference by the last character
		$preserveNewlineAtEnd = substr( $data, -1 ) === "\n";

		return [
			'fileBegin' => $fileBegin,
			'preserveIgnoreTag' => $preserveIgnoreTag,
			'preserveLongArraySyntax' => $preserveLongArraySyntax,
			'preserveNewlineAtEnd' => $preserveNewlineAtEnd,
		];
	}

	/**
	 * Write the opening of the files for each output file handle.
	 */
	protected function writeHeaders() {
		foreach ( $this->handles as $group => $handle ) {
			$arraySyntax = $this->extraInformation[$group]['preserveLongArraySyntax']
				? 'array()'
				: '[]';
			switch ( $this->type ) {
				case 'special':
					$ignoreTag = $this->extraInformation[$group]['preserveIgnoreTag']
						? "\n// @codingStandardsIgnoreFile"
						: '';
					fwrite( $handle, <<<PHP
$ignoreTag

\$specialPageAliases = $arraySyntax;
PHP
					);
					break;
				case 'magic':
					fwrite( $handle, <<<PHP

\$magicWords = $arraySyntax;
PHP
					);
					break;
			}
		}
	}

	/**
	 * Itterate through available languages, loading and parsing the data
	 * message from the MediaWiki namespace and writing the data to its output
	 * file handle.
	 */
	protected function writeFiles() {
		$langs = TranslateUtils::parseLanguageCodes( '*' );
		unset( $langs[array_search( 'en', $langs )] );
		$langs = array_merge( [ 'en' ], $langs );
		foreach ( $langs as $l ) {
			// Load message page.
			switch ( $this->type ) {
				case 'special':
					$title = Title::makeTitleSafe( NS_MEDIAWIKI, 'Sp-translate-data-SpecialPageAliases/' . $l );
					break;
				case 'magic':
					$title = Title::makeTitleSafe( NS_MEDIAWIKI, 'Sp-translate-data-MagicWords/' . $l );
					break;
				default:
					exit( 1 );
			}

			// Parse message page.
			if ( !$title || !$title->exists() ) {
				$this->output( "Skiping $l...\n" );

				$messagesNew = [];
			} else {
				$this->output( "Processing $l...\n" );

				$page = WikiPage::factory( $title );
				$content = $page->getContent();
				$data = $content->getNativeData();

				// Parse message file.
				$segments = explode( "\n", $data );
				array_shift( $segments );
				array_shift( $segments );
				unset( $segments[count( $segments ) - 1] );
				unset( $segments[count( $segments ) - 1] );
				$messagesNew = [];
				foreach ( $segments as $segment ) {
					$parts = explode( ' = ', $segment );
					$key = array_shift( $parts );
					$translations = explode( ', ', implode( $parts ) );
					$messagesNew[$key] = $translations;
				}
			}

			// Write data to handles.
			$namesEn = LanguageNames::getNames( 'en' );
			$namesNative = Language::fetchLanguageNames();

			foreach ( $this->handles as $group => $handle ) {
				// Find messages to write to this handle.
				$messagesOut = [];
				if ( !isset( $this->messagesOld[$group] ) ) {
					continue;
				}

				foreach ( $this->messagesOld[$group]['en'] as $key => $message ) {
					if ( array_key_exists( $key, $messagesNew ) ) {
						$messagesOut[$key] = $messagesNew[$key];
					} elseif ( isset( $this->messagesOld[$group][$l][$key] ) ) {
						$messagesOut[$key] = $this->messagesOld[$group][$l][$key];
					}
				}
				if ( $this->extraInformation[$group]['preserveLongArraySyntax'] ) {
					$arrayStart = 'array(';
					$arrayEnd = ')';
				} else {
					$arrayStart = '[';
					$arrayEnd = ']';
				}

				// If there are messages to write, write them.
				if ( $messagesOut !== [] ) {
					$out = '';
					switch ( $this->type ) {
						case 'special':
							$out .= "\n\n/** {$namesEn[$l]} ({$namesNative[$l]}) " .
								"*/\n\$specialPageAliases['{$l}'] = {$arrayStart}\n";
							break;
						case 'magic':
							$out .= "\n\n/** {$namesEn[$l]} ({$namesNative[$l]}) *" .
								"/\n\$magicWords['{$l}'] = {$arrayStart}\n";
							break;
					}
					foreach ( $messagesOut as $key => $translations ) {
						if ( !is_array( $translations ) ) {
							$this->error( "$l in $group has not an array..." );
							continue;
						}
						foreach ( $translations as $id => $translation ) {
							$translations[$id] = addslashes( $translation );
							if ( $this->type === 'magic' && $translation === 0 ) {
								unset( $translations[$id] );
							}
						}
						$translations = implode( "', '", $translations );
						switch ( $this->type ) {
							case 'special':
								$out .= "\t'$key' => $arrayStart '$translations' $arrayEnd,\n";
								break;
							case 'magic':
								if ( $this->messagesOld[$group]['en'][$key][0] === 0 ) {
									$out .= "\t'$key' => $arrayStart 0, '$translations' $arrayEnd,\n";
								} else {
									$out .= "\t'$key' => $arrayStart '$translations' $arrayEnd,\n";
								}
								break;
						}
					}
					$out .= "$arrayEnd;";
					fwrite( $handle, $out );
				}
			}
		}
	}

	/**
	 * Do whatever needs doing after writing the primary content.
	 */
	protected function writeFooters() {
		$this->output( "Writing file footers...\n" );
		foreach ( $this->handles as $group => $handle ) {
			if ( $this->extraInformation[$group]['preserveNewlineAtEnd'] ) {
				// php files should end with a newline
				fwrite( $handle, "\n" );
			}
		}
	}

	/**
	 * Close all output file handles.
	 */
	protected function closeHandles() {
		$this->output( "Closing file handles...\n" );
		foreach ( $this->handles as $handle ) {
			fclose( $handle );
		}
	}
}

$maintClass = MagicExport::class;
require_once RUN_MAINTENANCE_IF_MAIN;
