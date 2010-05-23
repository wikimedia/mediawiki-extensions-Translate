<?php
/**
 * Script to test page translation parser
 *
 * @author Niklas Laxstrom
 *
 * @copyright Copyright © 2010, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = dirname( __FILE__ ); $IP = "$dir/../../..";
}
require_once( "$IP/maintenance/Maintenance.php" );


class PageTranslationParserTester extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Script to test and debug page translation parser';
	}

	public function execute() {
		$dir = dirname( __FILE__ );
		$testDirectory = "$dir/../tests/pagetranslation";
		$testFiles = glob( "$testDirectory/*.ptfile" );

		foreach ( $testFiles as $file ) {
			$filename = basename( $file );
			list( $pagename, ) = explode( '.', $filename, 2 );
			$title = Title::newFromText( $pagename );
			$translatablePage = TranslatablePage::newFromText( $title, file_get_contents( $file ) );

			try {
				$parse = $translatablePage->getParse();
			} catch ( TPException $e ) {
				$this->output( "Testfile $filename failed to parse because: " . $e->getMessage() );
			}

			$pattern = "$testDirectory/$pagename";

			if ( file_exists( "$pattern.ptsource" ) ) {
				$source = $parse->getSourcePageText();
				if ( $source !== file_get_contents( "$pattern.ptsource" ) ) {
					$this->output( "Testfile $filename failed with source page output... writing $pattern.ptsource.fail" );
					file_put_contents( "$pattern.ptsource.fail", $source );
				}
			}

			if ( file_exists( "$pattern.pttarget" ) ) {
				$target = $parse->getTranslationPageText( new TestMessageCollection( "foo" ) );
				if ( $target !== file_get_contents( "$pattern.pttarget" ) ) {
					$this->output( "Testfile $filename failed with target page output... writing $pattern.pttarget.fail" );
					file_put_contents( "$pattern.pttarget.fail", $target );
				}
			}
	
			// Custom tests written in php
			if ( file_exists( "$pattern.pttest" ) ) {
				require( "$pattern.pttest" );
			}

		}
	}

}

$maintClass = 'PageTranslationParserTester';
require_once( DO_MAINTENANCE );