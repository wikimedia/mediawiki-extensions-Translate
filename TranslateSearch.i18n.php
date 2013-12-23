<?php
$messages = array();
$GLOBALS['wgHooks']['LocalisationCacheRecache'][] = function ( $cache, $code, &$cachedData ) {
	$codeSequence = array_merge( array( $code ), $cachedData['fallbackSequence'] );
	foreach ( $codeSequence as $csCode ) {
		$fileName = __DIR__ . "/i18n/search/$csCode.json";
		if ( is_readable( $fileName ) ) {
			$data = FormatJson::decode( file_get_contents( $fileName ), true );
			foreach ( $data as $key => $unused ) {
				if ( $key === '' || $key[0] === '@' ) {
					unset( $data[$key] );
				}
			}
			$cachedData['messages'] = array_merge( $data, $cachedData['messages'] );
		}

		$cachedData['deps'][] = new FileDependency( $fileName );
	}
	return true;
};
