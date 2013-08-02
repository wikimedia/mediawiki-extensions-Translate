<?php
/**
 * A script to forward error log messages with some rate limiting.
 *
 * @author Niklas Laxstrom
 *
 * @copyright Copyright © 2010, Niklas Laxström
 * @license GPL-2.0+
 * @file
 */

if( isset( $_SERVER['argv'][1] ) ) {
	$file = $_SERVER['argv'][1];
} else {
	exit( "OMG\n" );
}

if ( !is_readable( $file ) ) {
	exit( "OMG\n" );
}

$handle = fopen( $file, "rt" );
fseek( $handle, 0, SEEK_END );
while ( true ) {
	$count = 0;
	$line = false;
	while ( !feof( $handle ) ) {
		$count++;
		$input = fgets( $handle );
		if ( $input !== false ) {
			$line = $input;
		}
	}

	// I don't know why this is needed
	fseek( $handle, 0, SEEK_END );

	if ( $line !== false ) {
		$prefix = '';
		if ( $count > 2 ) {
			$count -= 2;
			$prefix = "($count lines skipped) ";
		}
		if ( mb_strlen( $line ) > 400 ) {
			$line = mb_substr( $line, 0, 400 ) . '...';
		}
		echo trim( $prefix . $line ) . "\n";
	}

	sleep( 30 );
}
