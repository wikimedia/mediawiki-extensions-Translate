<?php

require dirname( dirname( dirname( __DIR__ ) ) ) . '/core/maintenance/Maintenance.php';

/**
 * Maintenance class for the fast export of special page aliases and magic words.
 */
class MemoryTest extends Maintenance {
	public function execute() {
		$usage = array(
			'before' => 999999999,
			'during' => 999999999,
			'after' => 999999999
		);

		$data = FormatJson::decode( file_get_contents( __DIR__ . '/../i18n/core/qqq.json' ), true );
		$usage['before'] = memory_get_usage();
		$type = 'unused';

		if ( $type == 'unused' ) {
			foreach ( $data as $key => $unused ) {
				$usage['during'] = memory_get_usage();
				if ( $key === '' || $key[0] === '@' ) {
					unset( $data[$key] );
				}
			}
		} else {
			foreach ( array_keys( $data ) as $key ) {
				$usage['during'] = memory_get_usage();
				if ( $key === '' || $key[0] === '@' ) {
					unset( $data[$key] );
				}
			}
		}

		$usage['after'] = memory_get_usage();
		$usage = (int)max( $usage ) - (int)$usage['before'];
		echo "Usage: $usage";
	}
}

$maintClass = "MemoryTest";
require_once DO_MAINTENANCE;
