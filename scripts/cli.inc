<?php
/**
 * File to perform initialisation to setup command line scripts properly for
 * translate extension.
 *
 * @author Niklas Laxstrom
 *
 * @copyright Copyright © 2008, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */


$dir = dirname( __FILE__ ); $IP = "$dir/../..";
@include("$dir/../CorePath.php"); // Allow override
require_once( "$IP/maintenance/commandLine.inc" );

function STDOUT( $str, $forceNewLine = false ) {
	static $atStart = true;
	if ( !$atStart && $forceNewLine ) echo "\n";

	$len = strlen($str);
	if ( $len ) $atStart = ($str[$len-1] === "\n");

	echo $str;
}

function STDERR( $message ) {
	fwrite( STDERR, $message . "\n" );
}