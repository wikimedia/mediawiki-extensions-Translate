<?php
/**
 * Script to export special page aliases and magic words of extensions.
 *
 * @author Robert Leverington <robert@rhl.me.uk>
 *
 * @copyright Copyright © 2010 Robert Leverington
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */

$optionsWithArgs = array( 'target', 'type' );
require( dirname( __FILE__ ) . '/cli.inc' );

function showUsage() {
	STDERR( <<<EOT
Magic exporter.

Usage: php magic-export.php [options...]

Options:
  --target      Target directory for exported files
  --type        magic or special
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

if ( !is_writable( $options['target'] ) ) {
	STDERR( "Target directory is not writable" );
	exit( 1 );
}

if ( !isset( $options['type'] ) ) {
	STDERR( "Type must be one of the following: special magic" );
	exit( 1 );
}

$langs = Cli::parseLanguageCodes( '*' );
$groups = MessageGroups::singleton()->getGroups();

$type = $options['type'] ;

// Open file handles.
STDOUT( "Opening file handles..." );
$handles = array();
$keys = array();error_reporting( E_ALL | E_STRICT );
foreach ( $groups as $group ) {
	if ( !$group instanceof ExtensionMessageGroup ) continue;

	if ( $type === 'special' ) {
		$filename = $group->getAliasFile();
	} else {
		$filename = $group->getMagicFile();
	}

	if ( $filename === null ) continue;

	$file = "$wgTranslateExtensionDirectory/$filename";
	if ( !file_exists( $file ) ) continue;

	include( $file );
	if( !isset( $aliases ) ) continue;
	$keys[$group->getId()] = array_keys( $aliases['en'] );
	unset( $aliases );

	$handles[$group->getId()] = fopen( $options['target'] . '/' . $filename, 'w' );

	STDOUT( "\t{$group->getId()}" );
}

foreach ( $langs as $l ) {
	switch ( $options['type'] ) {
		case 'special':
			$title = Title::newFromText( 'MediaWiki:Sp-translate-data-SpecialPageAliases/' . $l );
			break;
		case 'magic':
			$title = Title::newFromText( 'MediaWiki:Sp-translate-data-MagicWords/' . $l );
			break;
		default:
			STDERR( "Invalid type: must be one of: special, magic" );
			exit( 1 );
	}

	if( !$title || !$title->exists() ) {
		STDOUT( "Skiping $l..." );
		continue;
	} else {
		STDOUT( "Processing $l..." );
	}
	$article = new Article( $title );
	$data = $article->getContent();

	// Parse message file.
	$segments = explode( "\n", $data );
	array_shift( $segments );
	array_shift( $segments );
	unset( $segments[count($segments)-1] );
	unset( $segments[count($segments)-1] );
	$messages = array();
	foreach( $segments as $segment ) {
		$parts = explode( '=', $segment );
		$key = trim( array_shift( $parts ) );
		$translations = explode( ', ', implode( $parts ) );
		$messages[$key] = $translations;
	}

	// Need to only provide the keys applicable to the file that is being written.
	foreach( $handles as $group => $handle ) {
		STDOUT( "\t{$group}... " );
		$thismessages = $messages; // TODO: Reduce.
		$out = "\$aliases['{$group}	'] = array(\n";
		foreach( $thismessages as $key => $translations ) {
			$translations = implode( "', '", $translations );
			$out .= "\t'$key' => array( '$translations' ),\n";
		}
		$out .= ");\n\n";
		fwrite( $handle, $out );
	}
}

// Close handles.
STDOUT( "Closing file handles..." );
foreach( $handles as $group => $handle ) {
	fclose( $handle );
}
