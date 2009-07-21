<?php
class TranslateSpyc {

	public static function load( $file ) {
		require_once( dirname(__FILE__).'/spyc.php' );
		$text = file_get_contents( $file );
		return spyc_load( $text );
	}

	public static function dump( $text ) {
		require_once( dirname(__FILE__).'/spyc.php' );
		return Spyc::YAMLDump( $text );
	}
}
