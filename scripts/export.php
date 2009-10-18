<?php
/**
 * Script to export translations of one message group to file(s).
 *
 * @author Niklas Laxstrom
 *
 * @copyright Copyright © 2008, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */

$optionsWithArgs = array( 'lang', 'target', 'group', 'threshold' );
require( dirname( __FILE__ ) . '/cli.inc' );

// FIXME: this needs to be in a lib.
/**
 * Returns translated percentage for message group in languages
 *
 * @param $group String Unique key identifying the group
 * @param $languages Array of language codes
 * @param $threshold Int Minimum required percentage translated to return.
 *        Other given language codes will not be returned.
 * @param $simple Bool Return only codes or code/pecentage pairs
 * @return Array of key value pairs code/percentage or array of codes,
 *         depending on $simple
 */
function getPercentageTranslated( $group, $languages, $threshold = false, $simple = false ) {
	$stats = array();

	$g = MessageGroups::singleton()->getGroup( $group );

	$collection = $g->initCollection( 'en' );
	foreach ( $languages as $code ) {
		$collection->resetForNewLanguage( $code );
		// Initialise messages
		$collection->filter( 'ignored' );
		$collection->filter( 'optional' );
		// Store the count of real messages for later calculation.
		$total = count( $collection );
		$collection->filter( 'translated', false );
		$translated = count( $collection );

		$translatedPercentage = ( $translated * 100 ) / $total;
		if ( $translatedPercentage >= $threshold ) {
			if ( $simple ) {
				$stats[] = $code;
			} else {
				$stats[$code] = $translatedPercentage;
			}
		}
	}

	return $stats;
}

function showUsage() {
	STDERR( <<<EOT
Message exporter.

Usage: php export.php [options...]

Options:
  --target      Target directory for exported files
  --lang        Comma separated list of language codes or *
  --group       Group ID
  --threshold   Do not export under this percentage translated
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

if ( !isset( $options['group'] ) ) {
	STDERR( "You need to specify group" );
	exit( 1 );
}

if ( !is_writable( $options['target'] ) ) {
	STDERR( "Target directory is not writable" );
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
	STDERR( "Invalid group" );
	exit( 1 );
}

if ( $threshold ) {
	$langs = getPercentageTranslated( $options['group'], $langs, $threshold, true );
}

if ( $group instanceof FileBasedMessageGroup ) {
	$ffs = $group->getFFS();
	$ffs->setWritePath( $options['target'] );
	$collection = $group->initCollection( 'en' );

	foreach ( $langs as $lang ) {
		$collection->resetForNewLanguage( $lang );
		$ffs->write( $collection );
	}
} else {
	$writer = $group->getWriter();
	$writer->fileExport( $langs, $options['target'] );
}
