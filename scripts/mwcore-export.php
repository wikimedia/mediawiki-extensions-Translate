<?php
/**
 * Script to export special core features of %MediaWiki.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2009-2013, Niklas Laxström, Siebrand Mazeland
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

class MwCoreExport extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Core special features exporter.';
		$this->addOption(
			'target',
			'Target directory for exported files',
			true, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'lang',
			'(optional) Comma separated list of language codes. Default: *',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'type',
			'Export type: "namespace", "special" or "magic"',
			true, /*required*/
			true /*has arg*/
		);
	}

	public function execute() {
		if ( !is_writable( $this->getOption( 'target' ) ) ) {
			$this->error( 'Target directory is not writable.', 1 );
		}

		$langs = TranslateUtils::parseLanguageCodes( $this->getOption( 'lang', '*' ) );
		$group = MessageGroups::getGroup( 'core' );
		$type = $this->getOption( 'type' );

		foreach ( $langs as $l ) {
			$o = null;

			switch ( $type ) {
				case 'special':
					$o = new SpecialPageAliasesCM( $l );
					break;
				case 'magic':
					$o = new MagicWordsCM( $l );
					break;
				case 'namespace':
					$o = new NamespaceCM( $l );
					break;
				default:
					$this->error( 'Invalid type: Must be one of special, magic, namespace.', 1 );
			}

			$export = $o->export( 'core' );
			if ( $export === '' ) {
				continue;
			}

			$matches = array();
			preg_match( '~^(\$[a-zA-Z]+)\s*=~m', $export, $matches );

			if ( !isset( $matches[1] ) ) {
				continue;
			}

			# remove useles comment
			$export = preg_replace( "~^# .*$\n~m", '', $export );

			if ( strpos( $export, '#!!' ) !== false ) {
				$this->error( "There are warnings with $l." );
			}

			$variable = preg_quote( $matches[1], '~' );

			/** @var FileBasedMessageGroup $group */
			$file = $group->getSourceFilePath( $l );
			// bandage
			$real = Language::getFileName( '/messages/Messages', $l );
			$file = preg_replace( '~/i18n/(.+)\.json$~', $real, $file );

			if ( !file_exists( $file ) ) {
				$this->error( "File $file does not exist!" );
				continue;
			}

			$data = file_get_contents( $file );

			$export = trim( $export ) . "\n";
			$escExport = addcslashes( $export, '\\$' ); # Darn backreferences

			$outFile = $this->getOption( 'target' ) . '/' . $group->getTargetFilename( $l );
			$outFile = preg_replace( '~/i18n/(.+)\.json$~', $real, $outFile );

			$count = 0;
			$data = preg_replace( "~$variable\s*=.*?\n\);\n~s", $escExport, $data, 1, $count );
			if ( $count ) {
				file_put_contents( $outFile, $data );
			} else {
				$this->error( "Adding new entry to $outFile, please double check location." );
				$pos = strpos( $data, '*/' );
				if ( $pos === false ) {
					$this->error( '. FAILED! Totally new file? No header?' );
				} else {
					$pos += 3;
				}

				$data = substr( $data, 0, $pos ) . "\n" . $export . substr( $data, $pos );

				file_put_contents( $outFile, $data );
			}
		}
	}
}

$maintClass = 'MwCoreExport';
require_once RUN_MAINTENANCE_IF_MAIN;
