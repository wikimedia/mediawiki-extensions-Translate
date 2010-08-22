<?php
/**
 * @todo Needs documentation.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2009, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * @todo Needs documentation.
 */
class ArrayMemoryCache {
	protected $table;
	protected $key;
	protected $memc;
	protected $cache;

	public function __construct( $table ) {
		$this->table = $table;
		$this->key = wfMemcKey( $this->table );

		global $wgMemc;

		$this->memc = $wgMemc;
	}

	public function __destruct() {
		$this->save();
	}

	public static function factory( $table ) {
		// __CLASS__ doesn't work, but this is PHP
		return new ArrayMemoryCache( $table );
	}

	public function get( $group, $code ) {
		$this->load();

		if ( !isset( $this->cache[$group][$code] ) ) {
			return false;
		}

		return explode( ',', $this->cache[$group][$code] );
	}

	public function set( $group, $code, $value ) {
		$this->load();

		if ( !isset( $this->cache[$group] ) ) {
			$this->cache[$group] = array();
		}

		$this->cache[$group][$code] = implode( ',', $value );
	}

	public function clear( $group, $code ) {
		$this->load();

		if ( isset( $this->cache[$group][$code] ) ) {
			unset( $this->cache[$group][$code] );
		}

		if ( isset( $this->cache[$group] ) && !count( $this->cache[$group] ) ) {
			unset( $this->cache[$group] );
		}
	}

	public function clearGroup( $group ) {
		$this->load();
		unset( $this->cache[$group] );
	}

	public function clearAll() {
		$this->load();
		$this->cache = array();
	}

	public function commit() {
		$this->save();
	}


	protected function load() {
		if ( $this->cache === null ) {
			$this->cache = $this->memc->get( $this->key );

			if ( !is_array( $this->cache ) ) {
				$this->cache = array();
			}
		}
	}

	protected function save() {
		if ( $this->cache !== null ) {
			$this->memc->set( $this->key, $this->cache );
		}
	}
}
