<?php
/**
 * Script to gather translator stats.
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

class TSP extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Script to calculate monthly stats about tsv data produced ' .
			'by translator-stats.php.';
		$this->addArg(
			'file',
			'tsv file to process'
		);
	}

	protected function median( $a ) {
		sort( $a );
		$len = count( $a );
		if ( $len === 0 ) {
			return 0;
		} elseif( $len === 1 ) {
			return $a[0];
		} elseif ( $len % 2 === 0 ) {
			return $a[$len/2];
		} else {
			return ( $a[floor( $len/2 )] + $a[ceil( $len/2 )] ) / 2;
		}
	}

	public function execute() {
		$handle = fopen( $this->getArg( 0 ), 'r' );
		// remove heading
		fgets( $handle );

		$data = array();
		while ( true ) {
			$l = fgets( $handle );
			if ( $l === false ) {
				break;
			}

			$fields = explode( "\t", trim( $l, "\n" ) );
			list( $name, $reg, $edits, $translator, $promoted, $method ) = $fields;
			$month = substr( $reg, 0, 4 ) . '-' . substr( $reg, 4, 2 ) . '-01';
			$data[$month][] = $fields;
		}

		fclose( $handle );

		ksort( $data );

		echo "period\tnew\tpromoted\tgood\tmedian promotion time\t" .
		"avg promotion time\tsandbox approval rate\n";

		foreach ( $data as $key => $period ) {
			$total = 0;
			$promoted = 0;
			$good = 0;
			$delay = array();
			$avg = 'N/A';
			$sbar = array();

			foreach ( $period as $p ) {
				list( $name, $reg, $edits, $translator, $promtime, $method ) = $p;
				$total++;
				if ( $translator === 'translator' ) {
					$promoted++;
				}

				if ( $edits > 100 ) {
					$good++;
				}

				if ( $promtime ) {
					$delay[] = wfTimestamp( TS_UNIX, $promtime ) - wfTimestamp( TS_UNIX, $reg );
				}

				if ( $method === 'sandbox' ) {
					if ( $promtime ) {
						$sbar[] = true;
					} else {
						$sbar[] = false;
					}
				}

			}

			$median = round( $this->median( $delay ) / 3600 );
			if ( count( $delay ) ) {
				$avg = round( array_sum( $delay ) / count( $delay ) / 3600 );
			}

			if ( $sbar === array() ) {
				$sbar = 'N/A';
			} else {
				$sbar = count( array_filter( $sbar ) ) / count( $sbar );
			}

			echo "$key\t$total\t$promoted\t$good\t$median\t$avg\t$sbar\n";
		}
	}
}

$maintClass = 'TSP';
require_once RUN_MAINTENANCE_IF_MAIN;
