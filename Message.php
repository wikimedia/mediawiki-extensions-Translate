<?php
if (!defined('MEDIAWIKI')) die();
/**
 * An extension to ease the translation of Mediawiki
 *
 * @addtogroup Extensions
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2007, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */


class MessageCollection implements Iterator, ArrayAccess, Countable {

	private $messages = array();

	/* Iterator methods */

	public function rewind() {
		reset($this->messages);
	}

	public function current() {
		$messages = current($this->messages);
		return $messages;
	}

	public function key() {
		$messages = key($this->messages);
		return $messages;
	}

	public function next() {
		$messages = next($this->messages);
		return $messages;
	}

	public function valid() {
		$messages = $this->current() !== false;
		return $messages;
	}

	/* ArrayAccess methods */

	public function offsetExists( $offset ) {
		return isset($this->messages[$offset]);
	}

	public function offsetGet( $offset ) {
		return $this->messages[$offset];
	}

	public function offsetSet( $offset, $value ) {
		if ( !$value instanceof TMessage ) {
			throw new MWException( __METHOD__ . ": Trying to set member to invalid type" );
		}
		$this->messages[$offset] = $value;
	}

	public function offsetUnset( $offset ) {
		unset( $this->messages[$offset] );
	}

	public function add( TMessage $message ) {
		$this->messages[$message->key] = $message;
	}

	public function addMany( Array $messages ) {
		foreach ( $messages as $message ) {
			if ( !$message instanceof TMessage ) {
				throw new MWException( __METHOD__ . ": Array contains something else than TMessage" );
			}
			$this->messages[$message->key] = $message;
		}
	}


	public function keys() {
		return array_keys( $this->messages );
	}

	public function slice( $offset, $count ) {
		$this->messages = array_slice( $this->messages, $offset, $count );
	}

	public function intersect_key( Array $array ) {
		return array_intersect_key( $this->messages, $array );
	}

	public function count() {
		return count( $this->messages );
	}

	public function __get( $name ) {
		if (isset($this->messages[$name])) {
			return $this->messages[$name];
		}

		throw new MWException( __METHOD__ . ": Trying to access unknown property $name" );
	}

	public function __isset( $name ) {
		return isset($this->messages[$name]);
	}

	public function __unset( $name ) {
		unset($this->messages[$name]);
	}

}

class TMessage {
	/**
	 * String that uniquely identifies this message.
	 */
	private $key = null;

	/**
	 * The definition of this message - usually in English.
	 */
	private $definition = null;

	/**
	 * Authors who have taken part in translating this message.
	 */
	private $authors = array();

	public $infile   = null;
	public $fallback = null;
	public $database = null;

	public $optional   = false;
	public $ignored    = false;
	public $pageExists = false;
	public $talkExists = false;

	public function __construct( $key, $definition ) {
		$this->key = $key;
		$this->definition = $definition;
	}

	public function addAuthor( $author ) {
		$this->authors[] = $author;
	}

	public function authors() {
		return $this->authors;
	}

	public function changed() {
		return $this->pageExists && ( $this->infile !== $this->database );
	}

	public function translated() {
		if ( $this->pageExists ) {
			return true;
		} else {
			return $this->translation !== null && $this->translation !== $this->definition;
		}
	}

	public function translation() {
		return $this->database ? $this->database : $this->infile;
	}

	public function fuzzy() {
		return strpos($this->database, TRANSLATE_FUZZY) !== false;
	}

	private $fProperties = array( 'authors', 'changed', 'translated', 'translation', 'fuzzy' );

	public function __get( $name ) {
		if ( isset( $this->$name) ) {
			return $this->$name;
		} else {
			if ( in_array( $name, $this->fProperties ) ) {
				return $this->$name();
			}
		}

		throw new MWException( __METHOD__ . ": Trying to access unknown property $name" );
	}

	public function __set( $name, $value ) {
		if ( isset($this->$name) ) {
			if ( gettype($this->$name) === gettype($value) || $this->$name === null && is_string($value) ) {
				$this->$name = $value;
			} else {
				$type = gettype($value);
				throw new MWException( __METHOD__ . ": Trying to set the value of property $name to illegal data type $type" );
			}
		}
		throw new MWException( __METHOD__ . ": Trying to set unknown property $name with value $value" );
	}

	public function __isset( $name ) {
		if ( isset($this->$name) ) {
			return $this->$name !== null;
		} else {
			return false;
		}
	}

}