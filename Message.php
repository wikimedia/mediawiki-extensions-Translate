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


/**
 * MessageCollection is a collection of TMessages. It supports array accces of
 * TMessage object by message key. One collection can only have items for one
 * translation target language.
 */
class MessageCollection implements Iterator, ArrayAccess, Countable {

	/**
	 * Messages are stored in an array.
	 */
	private $messages = array();

	/**
	 * Information of what type of MessageCollection this is.
	 */
	public $code = null;

	/**
	 * Creates new empty messages collection.
	 *
	 * @param $code Language code
	 */
	public function __construct( $code ) {
		$this->code = $code;
	}

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

	/* Countable methods */
	/**
	 * Counts the number of items in this collection.
	 *
	 * @return Integer count of items.
	 */
	public function count() {
		return count( $this->messages );
	}


	/**
	 *  Adds new TMessage object to collection.
	 */
	public function add( TMessage $message ) {
		$this->messages[$message->key] = $message;
	}

	/**
	 * Adds array of TMessages to this collection.
	 *
	 * @param $messages Array of TMessage objects.
	 * @throws MWException
	 */
	public function addMany( Array $messages ) {
		foreach ( $messages as $message ) {
			if ( !$message instanceof TMessage ) {
				throw new MWException( __METHOD__ . ": Array contains something else than TMessage" );
			}
			$this->messages[$message->key] = $message;
		}
	}

	/**
	 * Provides an array of keys for safe iteration.
	 *
	 * @return Array of string keys.
	 */
	public function keys() {
		return array_keys( $this->messages );
	}

	/**
	 * Does array_slice to the messages.
	 *
	 * @param $offset Starting offset.
	 * @param $count Numer of items to slice.
	 */
	public function slice( $offset, $count ) {
		$this->messages = array_slice( $this->messages, $offset, $count );
	}

	/**
	 * PHP function array_intersect_key doesn't seem to like object-as-arrays, so
	 * have to do provide some way to do it. Does not change object state.
	 *
	 * @param $array List of keys for messages that should be returned.
	 * @return New MessageCollection.
	 */
	public function intersect_key( Array $array ) {
		$collection = new MessageCollection( $this->code );
		$collection->addMany( array_intersect_key( $this->messages, $array ) );
		return $collection;
	}

	/* Fail fast */
	public function __get( $name ) {
		throw new MWException( __METHOD__ . ": Trying to access unknown property $name" );
	}

	/* Fail fast */
	public function __set( $name, $value ) {
		throw new MWException( __METHOD__ . ": Trying to modify unknown property $name" );
	}

	public function getAuthors() {
		global $wgTranslateFuzzyBotName;

		$authors = array();
		foreach ( $this->keys() as $key ) {
			// Check if there is authors
			$_authors = $this->messages[$key]->authors;
			if ( !count($_authors) ) continue;

			foreach ( $_authors as $author ) {
				if ( !isset($authors[$author]) ) {
					$authors[$author] = 1;
				} else {
					$authors[$author]++;
				}
			}
		}

		arsort( $authors, SORT_NUMERIC );
		foreach ( $authors as $author => $edits ) {
			if ( $author !== $wgTranslateFuzzyBotName ) {
				$filteredAuthors[] = $author;
			}
		}
		return isset($filteredAuthors) ? $filteredAuthors : array();
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

	private $infile   = null;
	private $database = null;

	private $optional   = false;
	private $ignored    = false;
	private $pageExists = false;
	private $talkExists = false;

	/**
	 * Creates new message object.
	 *
	 * @param $key Uniquer key identifying this message.
	 * @param $definition The authoritave definition of this message.
	 */
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

	/**
	 * Determines if this message has uncommitted changes.
	 *
	 * @return true or false
	 */
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

	/**
	 * Returns the current translation of message. Translation in database are
	 * preferred over those in source files.
	 *
	 * @return Translated string or null if there isn't translation.
	 */
	public function translation() {
		return $this->database ? $this->database : $this->infile;
	}

	/**
	 * Determines if the current translation in database (if any) is marked as
	 * fuzzy.
	 *
	 * @return true or false
	 */
	public function fuzzy() {
		if ( $this->database !== null ) {
			return strpos($this->database, TRANSLATE_FUZZY) !== false;
		} else {
			return false;
		}
	}

	private static $callable = array( 'authors', 'changed', 'translated', 'translation', 'fuzzy' );
	private static $writable = array( 'infile', 'database', 'pageExists', 'talkExists', 'optional', 'ignored' );

	public function __get( $name ) {
		if ( property_exists( $this, $name ) ) {
			return $this->$name;
		} else {
			if ( in_array( $name, self::$callable ) ) {
				return $this->$name();
			}
		}
		throw new MWException( __METHOD__ . ": Trying to access unknown property $name" );
	}

	public function __set( $name, $value ) {
		if ( in_array( $name, self::$writable ) ) {
			if ( gettype($this->$name) === gettype($value) || $this->$name === null && is_string($value) ) {
				$this->$name = $value;
			} else {
				$type = gettype($value);
				throw new MWException( __METHOD__ . ": Trying to set the value of property $name to illegal data type $type" );
			}
		} else {
			throw new MWException( __METHOD__ . ": Trying to set unknown property $name with value $value" );
		}
	}

	public function __isset( $name ) {
		if ( property_exists( $this, $name ) ) {
			return $this->$name !== null;
		} else {
			return false;
		}
	}

}
