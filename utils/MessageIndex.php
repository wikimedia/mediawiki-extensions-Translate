<?php
/**
 * Only used for page translation currently.
 *
 * @author Niklas Laxstrom
 *
 * @copyright Copyright © 2008-2009, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */

class MessageIndex {
	static $cache = array();

	// Nice shortcut
	public static function titleToGroup( Title $title ) {
		$namespace = $title->getNamespace();
		$text = $title->getDBkey();
		list( $key, ) = TranslateUtils::figureMessage( $text );
		return self::messageToGroup( $namespace, $key );
	}

	public static function messageToGroup( $namespace, $key ) {
		$namepace = MWNamespace::getCanonicalName( $namespace );
		if ( $namespace === false ) return null;

		$key = self::normaliseKey( $key );
		$index = self::index( $namespace );
		return @$index[$key];
	}

	public static function cache( $namespace = null ) {
		if ( $namespace !== null ) {
			$namepace = MWNamespace::getCanonicalName( $namespace );
			if ( $namespace === false ) return null;
		}

		$groups = MessageGroups::singleton()->getGroups();

		$hugearray = array();
		$postponed = array();

		STDOUT( "Working with ", 'main' );

		foreach ( $groups as $g ) {
			# the cache is split per namespace for efficieny
			if ( $namespace !== null && $g->namespaces[0] !== $namespace )
				continue;

			# Skip meta thingies
			if ( $g->isMeta() ) {
				$postponed[] = $g;
				continue;
			}

			self::checkAndAdd( $hugearray, $g );
		}

		foreach ( $postponed as $g ) {
			self::checkAndAdd( $hugearray, $g, true );
		}

		global $wgMemc;
		foreach ( $hugearray as $ns => $array ) {
			$memcKey = wfMemcKey( 'messageindex', $ns );
			$wgMemc->set( $memcKey, serialize( $array ), 60*60*12 );
			$cache = $wgMemc->get( $memcKey );
		}
	}

	protected static function checkAndAdd( &$hugearray, $g, $ignore = false ) {
		$messages = $g->getDefinitions();
		$id = $g->getId();

		if ( !is_array( $messages ) ) continue;

		STDOUT( "$id ", 'main' );

		$namespace = $g->namespaces[0];

		foreach ( $messages as $key => $data ) {
			# Force all keys to lower case, because the case doesn't matter and it is
			# easier to do comparing when the case of first letter is unknown, because
			# mediawiki forces it to upper case
			$key = self::normaliseKey( $key );
			if ( isset( $hugearray[$namespace][$key] ) ) {
				if ( !$ignore )
					STDERR( "Key $key already belongs to $hugearray[$namespace][$key], conflict with $id" );
			} else {
				$hugearray[$namespace][$key] = &$id;
			}
		}
		unset( $id ); // Disconnect the previous references to this $id

	}

	protected static function normaliseKey( $key ) {
		global $wgContLang;
		return $wgContLang->ucfirst( str_replace( " ", "_", $key ) );
	}

	protected static function index( $namespace ) {
		global $wgMemc;
		$memcKey = wfMemcKey( 'messageindex', $namespace );
		$cache = unserialize( $wgMemc->get($memcKey) );

		// Missing? Update it
		if ( !is_array($cache) ) self::cache( $namespace );
		$cache = unserialize( $wgMemc->get($memcKey) );
		if ( !is_array($cache) ) throw new MWException( "Caching failed" );

		return $cache;

	}
}