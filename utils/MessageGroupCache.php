<?php
/**
 * @todo Needs documentation.
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2009 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * @todo Needs documentation.
 */
class MessageGroupCache {
	/// \string 
	protected $group;
	/// CdbReader
	protected $cache;
	/// \string
	protected $code;

	/**
	 * Contructs a new cache object for given group and language code.
	 * @param $group \types{String,FileBasedMessageGroup} Group object or id.
	 * @param $code \string Language code. Default value 'en'.
	 */
	public function __construct( $group, $code = 'en' ) {
		if ( is_object( $group ) ) {
			$this->group = $group->getId();
		} else {
			$this->group = $group;
		}
		$this->code = $code;
	}

	public function exists() {
		return file_exists( $this->getCacheFileName() );
	}

	public function getKeys() {
		return unserialize( $this->open()->get( $this->specialKey( 'keys' ) ) );
	}

	public function getTimestamp() {
		return $this->open()->get( $this->specialKey( 'timestamp' ) );
	}

	public function getHash() {
		return $this->open()->get( $this->specialKey( 'hash' ) );
	}

	public function get( $key ) {
		return $this->open()->get( $key );
	}

	public function create() {
		$this->close(); // Close the reader instance just to be sure

		$group = MessageGroups::getGroup( $this->group );
		$messages = $group->load( $this->code );
		if ( !count( $messages ) ) {
			return; // Don't create empty caches
		}
		$hash = md5( file_get_contents( $group->getSourceFilePath( $this->code ) ) );

		$cache = CdbWriter::open( $this->getCacheFileName() );
		$keys = array_keys( $messages );
		$cache->set( $this->specialKey( 'keys' ), serialize( $keys ) );

		foreach ( $messages as $key => $value ) {
			$cache->set( $key, $value );
		}

		$cache->set( $this->specialKey( 'timestamp' ), wfTimestamp() );
		$cache->set( $this->specialKey( 'hash' ), $hash );
		$cache->close();
	}

	protected function open() {
		if ( $this->cache === null ) {
			$this->cache = CdbReader::open( $this->getCacheFileName() );
		}
		return $this->cache;
	}

	protected function close() {
		if ( $this->cache !== null ) {
			$this->cache->close();
			$this->cache = null;
		}
	}

	protected function getCacheFileName() {
		return TranslateUtils::cacheFile( "translate_groupcache-{$this->group}-{$this->code}.cdb" );
	}

	protected function specialKey( $key ) {
		return "<|$key#>";
	}
}
