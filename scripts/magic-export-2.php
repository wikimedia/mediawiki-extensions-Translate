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
unset( $langs[array_search( 'en', $langs )] );
$langs = array_merge( array( 'en' ), $langs );
$groups = MessageGroups::singleton()->getGroups();

$type = $options['type'] ;

// Open file handles.
STDOUT( "Opening file handles..." );
$handles = array();
$messagesOld = array();
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
	$messagesOld[$group->getId()] = $aliases;
	unset( $aliases );

	$handles[$group->getId()] = fopen( $options['target'] . '/' . $filename, 'w' );

	STDOUT( "\t{$group->getId()}" );
}

// Write header.
foreach( $handles as $handle ) {
	if( $type === 'special' ) {
			fwrite( $handle, <<<EOT
<?php

/**
 * Aliases for special pages
 *
 * @file
 * @ingroup Extensions
 */

\$aliases = array();
EOT
);
	} else {
	}
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
			exit( 1 );
	}

	if( !$title || !$title->exists() ) {
		STDOUT( "Skiping $l..." );

		$messagesNew = array();
	} else {
		STDOUT( "Processing $l..." );

		$article = new Article( $title );
		$data = $article->getContent();

		// Parse message file.
		$segments = explode( "\n", $data );
		array_shift( $segments );
		array_shift( $segments );
		unset( $segments[count($segments)-1] );
		unset( $segments[count($segments)-1] );
		$messagesNew = array();
		foreach( $segments as $segment ) {
			$parts = explode( '=', $segment );
			$key = trim( array_shift( $parts ) );
			$translations = explode( ', ', implode( $parts ) );
			$messagesNew[$key] = $translations;
		}
	}

	foreach( $handles as $group => $handle ) {
		STDOUT( "\t{$group}... " );
		$namesEn = LanguageNames::getNames( 'en' );
		$namesNative = Language::getLanguageNames();
		$messagesOut = array();
		foreach( $messagesOld[$group]['en'] as $key => $message ) {
			if( array_key_exists( $key, $messagesNew ) ) {
				$messagesOut[$key] = $messagesNew[$key];
			} elseif( isset( $messagesOld[$group][$l][$key] ) ) {
				$messagesOut[$key] = $messagesOld[$group][$l][$key];
			}
		}
		if( count( $messagesOut ) > 0 ) {
			$out = "\n\n/** {$namesEn[$l]} ({$namesNative[$l]}) */\n\$aliases['{$l}'] = array(\n";
			foreach( $messagesOut as $key => $translations ) {
				foreach( $translations as $id => $translation ) {
					$translations[$id] = addslashes( $translation );
				}
				$translations = implode( "', '", $translations );
				$out .= "\t'$key' => array( '$translations' ),\n";
			}
			$out .= ");";
			fwrite( $handle, $out );
		}
	}
}

// Close handles.
STDOUT( "Closing file handles..." );
foreach( $handles as $group => $handle ) {
	fclose( $handle );
}
