<?php
/**
 * File to perform initialisation to setup command line scripts properly for
 * translate extension.
 *
 * @author Niklas Laxstrom
 * @copyright Copyright © 2008-2010, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = dirname( __FILE__ ); $IP = "$dir/../../..";
}
define( 'TRANSLATE_CLI', 1 );
require_once( "$IP/maintenance/commandLine.inc" );

function STDOUT( $str, $channel = null, $force = false ) {
	global $options;

	if ( isset($options['quiet']) &&!$force ) {
		return;
	}

	static $lastChannel = null;
	static $lineStart = true;

	if ( $channel !== null && ($lineStart || $channel === $lastChannel) ) {
		fwrite( STDOUT, $str );
	} elseif ( $str === false ) {
		// Cleanup
		if ( !$lineStart ) {
			fwrite( STDOUT, "\n" );
		}

		return;
	} else {
		if ( !$lineStart ) {
			fwrite( STDOUT, "\n" );
		}

		fwrite( STDOUT, $str );
	}

	$lineStart = false;
	if ( $channel === null ) {
		fwrite( STDOUT, "\n" );
		$lineStart = true;
	}

	$lastChannel = $channel;
}

function STDERR( $message, $channel = null ) {
	STDOUT( $message, $channel, true );
}

register_shutdown_function('STDOUT', false);

/**
 * @todo Needs documentation.
 */
class Cli {
	public static function parseLanguageCodes( /* string */ $codes ) {
		$langs = array_map( 'trim', explode( ',', $codes ) );
		if ( $langs[0] === '*' ) {
			$languages = Language::getLanguageNames();
			ksort($languages);
			$langs = array_keys($languages);
		}

		return $langs;
	}
}
