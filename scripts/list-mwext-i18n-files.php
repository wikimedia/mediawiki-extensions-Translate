<?php
/**
 * Script which lists required i18n files for mediawiki extensions.
 * Can be used to crate smaller and faster checkouts.
 *
 * @author Niklas Laxstrom
 *
 * @copyright Copyright © 2010-2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = dirname( __FILE__ ); $IP = "$dir/../../..";
}
require_once( "$IP/maintenance/Maintenance.php" );

/// Script which lists required i18n files for MediaWiki extensions.
class MWExtFileList extends Maintenance {

	/**
	 * @var array
	 */
	protected $files;

	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Script which lists required i18n files for mediawiki extension style groups';
		$this->addOption( 'group', 'Only groups that match the provided pattern', true, 'witharg' );
	}

	public function execute() {
		$groupIds = explode( ',', $this->getOption( 'group' ) );
		$groupIds = MessageGroups::expandWildcards( $groupIds );
		$groups = MessageGroups::getGroupsById( $groupIds );
		$this->files = array();

		foreach ( $groups as $group ) {
			if ( !$group instanceof ExtensionMessageGroup ) continue;
			$this->addPaths( $group->getMessageFile( 'en' ) );
			$this->addPaths( $group->getAliasFile( 'en' ) );
			$this->addPaths( $group->getMagicFile( 'en' ) );
		}

		$files = array_keys( $this->files );
		$this->output( trim( implode( "\n", $files ) . "\n" ) );
	}

	public function addPaths( $file ) {
		if ( $file === '' ) return;

		$paths = array();
		do {
			$paths[] = $file;
			$file = dirname( $file );
		} while ( $file !== '.' && $file !== '' );

		// Directories first
		$paths = array_reverse( $paths );
		foreach ( $paths as $path ) {
			$this->files[$path] = true;
		}
	}
}

$maintClass = 'MWExtFileList';
require_once( DO_MAINTENANCE );
