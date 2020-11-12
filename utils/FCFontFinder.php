<?php
/**
 * Contains class with wrapper around font-config.
 *
 * @author Niklas Laxström
 * @author Harry Burt
 * @license Unlicense
 * @file
 */

/**
 * Wrapper around font-config to get useful ttf font given a language code.
 * Uses wfShellExec, wfEscapeShellArg and wfDebugLog, and wfGetCache from %MediaWiki.
 *
 * @ingroup Stats
 */
class FCFontFinder {
	/**
	 * Searches for suitable font in the system.
	 * @param string $code Language code.
	 * @return bool|string Full path to the font file, false on failure
	 */
	public static function findFile( $code ) {
		$data = self::callFontConfig( $code );
		if ( is_array( $data ) ) {
			return $data['file'];
		}

		return false;
	}

	/**
	 * Searches for suitable font family in the system.
	 * @param string $code Language code.
	 * @return bool|string Name of font family, false on failure
	 */
	public static function findFamily( $code ) {
		$data = self::callFontConfig( $code );
		if ( is_array( $data ) ) {
			return $data['family'];
		}

		return false;
	}

	protected static function callFontConfig( $code ) {
		if ( ini_get( 'open_basedir' ) ) {
			wfDebugLog( 'fcfont', 'Disabled because of open_basedir is active' );

			// Most likely we can't access any fonts we might find
			return false;
		}

		$cache = self::getCache();
		$cachekey = $cache->makeKey( 'fcfont', $code );
		$timeout = 60 * 60 * 12;

		$cached = $cache->get( $cachekey );
		if ( is_array( $cached ) ) {
			return $cached;
		} elseif ( $cached === 'NEGATIVE' ) {
			return false;
		}

		$code = wfEscapeShellArg( ":lang=$code" );
		$ok = 0;
		$cmd = "fc-match $code";
		$suggestion = wfShellExec( $cmd, $ok );

		wfDebugLog( 'fcfont', "$cmd returned $ok" );

		if ( $ok !== 0 ) {
			wfDebugLog( 'fcfont', "fc-match error output: $suggestion" );
			$cache->set( $cachekey, 'NEGATIVE', $timeout );

			return false;
		}

		$pattern = '/^(.*?): "(.*)" "(.*)"$/';
		$matches = [];

		if ( !preg_match( $pattern, $suggestion, $matches ) ) {
			wfDebugLog( 'fcfont', "fc-match: return format not understood: $suggestion" );
			$cache->set( $cachekey, 'NEGATIVE', $timeout );

			return false;
		}

		list( , $file, $family, $type ) = $matches;
		wfDebugLog( 'fcfont', "fc-match: got $file: $family $type" );

		$file = wfEscapeShellArg( $file );
		$family = wfEscapeShellArg( $family );
		$type = wfEscapeShellArg( $type );
		$cmd = "fc-list $family $type $code file | grep $file";

		$candidates = trim( wfShellExec( $cmd, $ok ) );

		wfDebugLog( 'fcfont', "$cmd returned $ok" );

		if ( $ok !== 0 ) {
			wfDebugLog( 'fcfont', "fc-list error output: $candidates" );
			$cache->set( $cachekey, 'NEGATIVE', $timeout );

			return false;
		}

		# trim spaces
		$files = array_map( 'trim', explode( "\n", $candidates ) );
		$count = count( $files );
		if ( !$count ) {
			wfDebugLog( 'fcfont', "fc-list got zero canditates: $candidates" );
		}

		# remove the trailing ":"
		$chosen = substr( $files[0], 0, -1 );

		wfDebugLog( 'fcfont', "fc-list got $count candidates; using $chosen" );

		$data = [
			'family' => $family,
			'type' => $type,
			'file' => $chosen,
		];

		$cache->set( $cachekey, $data, $timeout );

		return $data;
	}

	/**
	 * @return BagOStuff
	 */
	protected static function getCache() {
		return wfGetCache( CACHE_ANYTHING );
	}
}
