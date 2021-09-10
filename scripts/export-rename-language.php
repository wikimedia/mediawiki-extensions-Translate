<?php
/**
 * Script to automate renaming of language codes in supported repos.
 *
 * @license GPL-2.0-or-later
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

class ExportRenameLanguage extends Maintenance {
	private const MARKER = '%CODE%';

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Renames language codes in repos.' );
		$this->addOption(
			'group',
			'Comma separated list of group IDs (can use * as wildcard)',
			true, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'source-language',
			'Language code',
			true, /*required*/
			true /*has arg*/
		);

		$this->addOption(
			'target-language',
			'Language code',
			true, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'target',
			'Target directory for exported files',
			true, /*required*/
			true /*has arg*/
		);
		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		$target = rtrim( $this->getOption( 'target' ), '/' );
		$sourceLanguage = $this->getOption( 'source-language' );
		$targetLanguage = $this->getOption( 'target-language' );

		if ( !is_writable( $target ) ) {
			$this->fatalError( "Target directory is not writable ($target)." );
		}

		$groupIds = explode( ',', trim( $this->getOption( 'group' ) ) );
		$groupIds = MessageGroups::expandWildcards( $groupIds );
		$groups = MessageGroups::getGroupsById( $groupIds );
		$groups = $this->filterGroups( $groups );

		if ( $groups === [] ) {
			$this->fatalError( 'EE1: No valid message groups identified.' );
		}

		foreach ( $groups as $group ) {
			// Source path can be wrong if source language is the source language of the
			// message group. This is because getTargetFilename doesn't check definitionFile
			// property first.
			$sourcePath = $group->getTargetFilename( $sourceLanguage );
			$targetPath = $group->getTargetFilename( $targetLanguage );

			if ( !file_exists( "$target/$sourcePath" ) ) {
				continue;
			}

			$this->output( "Renaming $sourcePath to $targetPath\n" );
			$this->renameFile( "$target/$sourcePath", "$target/$targetPath" );

			$pathPattern = "$target/" . $group->getTargetFilename( self::MARKER );
			$pathToRemove = '';
			$needsCleanup = $this->needsCleanup( $pathPattern, $sourceLanguage, $pathToRemove );
			if ( $needsCleanup === 'yes' ) {
				$this->output( "Removing empty directory $pathToRemove\n" );
				rmdir( $pathToRemove );
			} elseif ( $needsCleanup === 'maybe' ) {
				$this->output( "Not removing (yet?) non-empty directory $pathToRemove\n" );
			}
		}

		$this->output( "Done\n" );
	}

	/**
	 * @param MessageGroup[] $groups
	 * @return FileBasedMessageGroup[]
	 */
	private function filterGroups( array $groups ) {
		$return = [];
		foreach ( $groups as $groupId => $group ) {
			if ( !$group instanceof FileBasedMessageGroup ) {
				$this->output( "Skipping non-file based message group $groupId.\n" );
				continue;
			}
			$return[$groupId] = $group;
		}
		return $return;
	}

	private function renameFile( $source, $target ) {
		// In case %CODE% is in the path
		if ( !is_dir( dirname( $target ) ) ) {
			mkdir( dirname( $target ), 0777, true );
		}

		rename( $source, $target );
	}

	private function isDirectoryEmpty( $dir ) {
		return array_diff( scandir( $dir ), [ '..', '.' ] ) === [];
	}

	private function needsCleanup( $pathPattern, $sourceLanguage, &$pathToRemove ) {
		do {
			$currentComponent = basename( $pathPattern );
			if ( strpos( $currentComponent, self::MARKER ) === false ) {
				$pathPattern = dirname( $pathPattern );
				continue;
			}

			$pathToRemove = str_replace( self::MARKER, $sourceLanguage, $pathPattern );
			if ( !is_dir( $pathToRemove ) ) {
				// %CODE% is in the filename
				return 'no';
			}

			return $this->isDirectoryEmpty( $pathToRemove ) ? 'yes' : 'maybe';
		} while ( $currentComponent !== '' );

		// This should never be reached.
		return 'no';
	}
}

$maintClass = ExportRenameLanguage::class;
require_once RUN_MAINTENANCE_IF_MAIN;
