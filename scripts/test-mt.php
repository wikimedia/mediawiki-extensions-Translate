<?php
/**
 * Script to test web services from the command line
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
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

class TestMT extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Test webservices.';

		$this->addOption(
			'service',
			'Which service to use',
			true, /*required*/
			true /*has arg*/
		);

		$this->addOption(
			'from',
			'Source language tag',
			true, /*required*/
			true /*has arg*/
		);

		$this->addOption(
			'to',
			'Target language tag',
			true, /*required*/
			true /*has arg*/
		);

		$this->addArg(
			'text',
			'Text to translate',
			true /*required*/
		);
	}

	public function execute() {
		global $wgTranslateTranslationServices;

		$name = $this->getOption( 'service' );

		if ( !isset( $wgTranslateTranslationServices[ $name ] ) ) {
			$this->fatalError( "Unknown service.\n" );
		}

		$service = TranslationWebService::factory( $name, $wgTranslateTranslationServices[ $name ] );
		$service->setLogger( new TranslateCliLogger( function ( $msg ) {
			$this->output( "$msg\n" );
		} ) );

		$from = $this->getOption( 'from' );
		$to = $this->getOption( 'to' );
		$text = $this->getArg( 0 );

		if ( !$service->isSupportedLanguagePair( $from, $to ) ) {
			$this->fatalError( "Unsupported language pair.\n" );
		}

		$query = $service->getQueries( $text, $from, $to );
		if ( $query === [] ) {
			$this->fatalError( "Service query error.\n" );
		}

		$agg = new QueryAggregator();
		$id = $agg->addQuery( $query[ 0 ] );
		$agg->run();
		$res = $agg->getResponse( $id );
		if ( $res === null ) {
			$this->fatalError( "Service response error.\n" );
		}

		$this->output( $service->getResultData( $res ), 1 );
	}
}

$maintClass = TestMT::class;
require_once RUN_MAINTENANCE_IF_MAIN;
