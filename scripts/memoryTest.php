<?php

require dirname( dirname( dirname( __DIR__ ) ) ) . '/core/maintenance/Maintenance.php';

/**
 * Maintenance class for the fast export of special page aliases and magic words.
 */
class MemoryTest extends Maintenance {
	public function execute() {
		$usage = array(
			0 => 999999999,
			1 => 999999999
		);

		$data = FormatJson::decode( file_get_contents( __DIR__ . '/../i18n/core/qqq.json' ), true );

		$usage[0] = memory_get_usage();

		if ( false ) {
			foreach ( $data as $key => $unused ) {
				if ( $key === '' || $key[0] === '@' ) {
					unset( $data[$key] );
				}
			}
		} else {
			foreach ( array_keys( $data ) as $key ) {
				if ( $key === '' || $key[0] === '@' ) {
					unset( $data[$key] );
				}
			}
		}

		$usage[1] = memory_get_usage();
		$usage = (int)$usage[1] - (int)$usage[0];
		echo "Usage: $usage";
	}
}

$maintClass = "MemoryTest";
require_once DO_MAINTENANCE;
