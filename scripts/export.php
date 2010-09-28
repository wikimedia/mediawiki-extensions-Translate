<?php
/**
 * Script to export translations of one message group to file(s).
 *
 * @author Niklas Laxstrom
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2010, Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */

$optionsWithArgs = array( 'lang', 'skip', 'target', 'group', 'threshold', 'ppgettext' );
require( dirname( __FILE__ ) . '/cli.inc' );

function showUsage() {
	STDERR( <<<EOT
Message exporter.

Usage: php export.php [options...]

Options:
  --target      Target directory for exported files
  --lang        Comma separated list of language codes or *
  --skip        Languages to skip, comma separated list
  --group       Group ID
  --threshold   Do not export under this percentage translated
  --ppgettext   Group root path for checkout of product. "msgmerge" will post
                process on the export result based on the current definitionFile
                in that location
EOT
);
	exit( 1 );
}

if ( isset( $options['help'] ) || $args === 1 ) {
	showUsage();
}

if ( !isset( $options['target'] ) ) {
	STDERR( "You need to specify target directory" );
	exit( 1 );
}

if ( !isset( $options['lang'] ) ) {
	STDERR( "You need to specify languages to export" );
	exit( 1 );
}

if ( isset( $options['skip'] ) ) {
	$skip = array_map( 'trim', explode( ',', $options['skip'] ) );
} else {
	$skip = array();
}

if ( !isset( $options['group'] ) ) {
	STDERR( "You need to specify group" );
	exit( 1 );
}

if ( !is_writable( $options['target'] ) ) {
	STDERR( "Target directory is not writable (" . $options['target'] . ")" );
	exit( 1 );
}
if ( isset( $options['threshold'] ) && intval( $options['threshold'] ) ) {
	$threshold = $options['threshold'];
} else {
	$threshold = false;
}

$langs = Cli::parseLanguageCodes( $options['lang'] );

$group = MessageGroups::getGroup( $options['group'] );

if ( !$group instanceof MessageGroup ) {
	STDERR( "Invalid group: " . $options['group'] );
	exit( 1 );
}

if ( $threshold ) {
	$langs = TranslationStats::getPercentageTranslated(
		$options['group'],
		$langs,
		$threshold,
		true
	);
}

if ( $group instanceof FileBasedMessageGroup ) {
	$ffs = $group->getFFS();
	$ffs->setWritePath( $options['target'] );
	$collection = $group->initCollection( 'en' );

	$definitionFile = false;

	if ( isset( $options['ppgettext'] ) && $ffs instanceof GettextFFS ) {
		global $wgMaxShellMemory;

		// Need more shell memory for msgmerge.
		$wgMaxShellMemory = 302400;

		$conf = $group->getConfiguration();
		$definitionFile = str_replace( '%GROUPROOT%', $options['ppgettext'], $conf['FILES']['definitionFile'] );
	}

	foreach ( $langs as $lang ) {
		// Do not export if language code is to be skipped.
		if( in_array( $lang, $skip ) && !$group->isValidLanguage( $lang ) ) {
			continue;
		}

		$collection->resetForNewLanguage( $lang );
		$ffs->write( $collection );

		// Do post processing if requested.
		if ( $definitionFile ) {
			if ( is_file( $definitionFile ) ) {
				$targetFileName = $ffs->getWritePath() . $group->getTargetFilename( $collection->code );
				$cmd = "msgmerge --quiet --update --backup=off " . $targetFileName . ' ' . $definitionFile;
				wfShellExec( $cmd, $ret );

				// Report on errors.
				if ( $ret ) {
					STDERR( 'ERROR: ' . $ret );
				}
			} else {
				STDERR( $definitionFile . ' does not exist.' );
				exit( 1 );
			}
		}
	}
} else {
	$writer = $group->getWriter();
	$writer->fileExport( $langs, $options['target'] );
}
