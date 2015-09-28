<?php
/**
 * Script to generate some random data to help testing sandbox.
 *
 * @author Niklas Laxström
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

class TranslateGenerateRandomSandboxData extends Maintenance {

	public function execute() {
		$users = 10;

		// For number of translations, limited to [0,20]
		$mean = 15;
		$stddev = 20;

		$stash = new TranslationStashStorage( wfGetDB( DB_MASTER ) );

		$languages = array_keys( Language::fetchLanguageNames() );

		for ( $i = 0; $i < $users; $i++ ) {
			$username = 'Pupu' . wfRandomString( 6 );
			$password = wfRandomString( 12 );
			$email = "$username.$password@blackhole.io";
			$user = TranslateSandbox::addUser( $username, $password, $email );

			$language = $languages[rand( 0, count( $languages ) - 1 )];

			$count = wfGaussMs( $mean, $stddev );
			$count = min( 20, $count );
			$count = max( 0, $count );

			for ( $j = 0; $j < $count; $j++ ) {
				$title = Title::makeTitle( NS_MEDIAWIKI, wfRandomString( 24 ) . '/' . $language );

				$value = array( 'Pupu söi' );
				for ( $k = rand( 0, 20 ); $k > 0; $k-- ) {
					$value[] = wfRandomString( rand( 1, 28 ) );
				}

				$value = implode( "\n", $value );
				$translation = new StashedTranslation( $user, $title, $value );
				$stash->addTranslation( $translation );
			}
		}
	}
}

/*
 * Gauss functions are based on Mark Baker's code from
 * http://stackoverflow.com/questions/5188900/bell-curve-algorithm-with-php
 */

function wfGauss() {
	static $useExists = false;
	static $useValue;

	if ( $useExists ) {
		// Use value from a previous call to this function
		$useExists = false;
		return $useValue;
	} else {
		// Polar form of the Box-Muller transformation
		$w = 2.0;
		while ( ( $w >= 1.0 ) || ( $w == 0.0 ) ) {
				$x = wfRandomPn();
				$y = wfRandomPn();
				$w = ( $x * $x ) + ( $y * $y );
		}
		$w = sqrt( ( -2.0 * log( $w ) ) / $w );

		// Set value for next call to this function
		$useValue = $y * $w;
		$useExists = true;

		return $x * $w;
	}
}

function wfGaussMs( $mean, $stddev ) {
	// Adjust our gaussian random to fit the mean and standard deviation.
	// The division by 4 is an arbitrary value to help fit the distribution
	// within our required range, and gives a best fit for $stddev = 1.0.
	return wfGauss() * ( $stddev / 4 ) + $mean;
}

function wfRandom01() {
	// Returns random number using mt_rand() with a flat distribution from 0 to 1 inclusive
	return (float) mt_rand() / (float) mt_getrandmax();
}

function wfRandomPn() {
	// Returns random number using mt_rand() with a flat distribution from -1 to 1 inclusive
	return ( 2.0 * wfRandom01() ) - 1.0;
}

$maintClass = 'TranslateGenerateRandomSandboxData';
require_once RUN_MAINTENANCE_IF_MAIN;
