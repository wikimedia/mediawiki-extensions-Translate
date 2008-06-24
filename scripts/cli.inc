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

$dir = dirname( __FILE__ ); $IP = "$dir/../../..";
@include("$dir/../../CorePath.php"); // Allow override
require_once( "$IP/maintenance/commandLine.inc" );

function STDOUT( $str, $singeLineItem = null ) {
	global $options;
	if ( isset($options['quiet']) ) return;

	static $atStart = true;
	static $lastSingle = false;

	if ( !$atStart && $singeLineItem === null || !$lastSingle && !$atStart && $singeLineItem ) {
		fwrite( STDOUT, "\n" );
	}
	fwrite( STDOUT, $str );

	if ( $singeLineItem !== true ) {
		fwrite( STDOUT, "\n" );
		$atStart = true;
		$lastSingle = false;
	} else {
		$lastSingle = true;
		$atStart = false;
	}
}

function STDERR( $message ) {
	fwrite( STDERR, $message . "\n" );
}