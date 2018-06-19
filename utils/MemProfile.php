<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	die();
}
/**
 * Very crude tools to track memory usage
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2008, Niklas Laxström
 * @license GPL-2.0-or-later
 */

/// Memory usage at checkpoints
$wgMemUse = [];
/// Tracks the deepness of the stack
$wgMemStack = 0;

/**
 * Call to start memory counting for a block.
 * @param string $a Block name.
 */
function wfMemIn( $a ) {
	global $wgLang, $wgMemUse, $wgMemStack;

	$mem = memory_get_usage();
	$memR = memory_get_usage();

	$wgMemUse[$a][] = [ $mem, $memR ];

	$memF = $wgLang->formatNum( $mem );
	$memRF = $wgLang->formatNum( $memR );

	$pad = str_repeat( '.', $wgMemStack );
	wfDebug( "$pad$a-IN: \t$memF\t\t$memRF\n" );
	$wgMemStack++;
}

/**
 * Call to start stop counting for a block. Difference from start is shown.
 * @param string $a Block name.
 */
function wfMemOut( $a ) {
	global $wgLang, $wgMemUse, $wgMemStack;

	$mem = memory_get_usage();
	$memR = memory_get_usage();

	list( $memO, $memOR ) = array_pop( $wgMemUse[$a] );

	$memF = $wgLang->formatNum( $mem );
	$memRF = $wgLang->formatNum( $memR );

	$memD = $mem - $memO;
	$memRD = $memR - $memOR;

	$memDF = $wgLang->formatNum( $memD );
	$memRDF = $wgLang->formatNum( $memRD );

	$pad = str_repeat( '.', $wgMemStack - 1 );
	wfDebug( "$pad$a-OUT:\t$memF ($memDF)\t$memRF ($memRDF)\n" );
	$wgMemStack--;
}
