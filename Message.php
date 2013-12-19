<?php
/**
 * Classes for message objects TMessage and ThinMessage.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2010, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Interface for message objects used by MessageCollection.
 */
abstract class TMessage {
	/// \string Message display key.
	protected $key;
	/// \string Message definition.
	protected $definition;
	/// \string Committed in-file translation.
	protected $infile;
	/// \list{String} Message tags.
	protected $tags = array();
	/// \array Message properties.
	protected $props = array();
	/// \list{String} Message reviewers.
	protected $reviewers = array();

	/**
	 * Creates new message object.
	 *
	 * @param $key string Unique key identifying this message.
	 * @param $definition string The authoritave definition of this message.
	 */
	public function __construct( $key, $definition ) {
		$this->key = $key;
		$this->definition = $definition;
	}

	/**
	 * Get the message key.
	 * @return string
	 */
	public function key() {
		return $this->key;
	}

	/**
	 * Get the message definition.
	 * @return string
	 */
	public function definition() {
		return $this->definition;
	}

	/**
	 * Get the message translation.
	 * @return string|null
	 */
	abstract public function translation();

	/**
	 * Set the committed translation.
	 * @param $text \string
	 */
	public function setInfile( $text ) {
		$this->infile = $text;
	}

	/**
	 * Returns the committed translation.
	 * @return string|null
	 */
	public function infile() {
		return $this->infile;
	}

	/**
	 * Add a tag for this message.
	 * @param $tag \string
	 */
	public function addTag( $tag ) {
		$this->tags[] = $tag;
	}

	/**
	 * Check if this message has a given tag.
	 * @param $tag \string
	 * @return \bool
	 */
	public function hasTag( $tag ) {
		return in_array( $tag, $this->tags, true );
	}

	/**
	 * Return all tags for this message;
	 * @return array of strings
	 */
	public function getTags() {
		return $this->tags;
	}

	public function setProperty( $key, $value ) {
		$this->props[$key] = $value;
	}

	public function appendProperty( $key, $value ) {
		if ( !isset( $this->props[$key] ) ) {
			$this->props[$key] = array();
		}
		$this->props[$key][] = $value;
	}

	public function getProperty( $key ) {
		return isset( $this->props[$key] ) ? $this->props[$key] : null;
	}

	/**
	 * Get all the available property names.
	 * @return array
	 * @since 2013-01-17
	 */
	public function getPropertyNames() {
		return array_keys( $this->props );
	}
}

/**
 * %Message object which is based on database result row. Hence the name thin.
 * Needs fields rev_user_text and those that are needed for loading revision
 * text.
 */
class ThinMessage extends TMessage {
	// This maps properties to fields in the database result row
	protected static $propertyMap = array(
		'last-translator-text' => 'rev_user_text',
		'last-translator-id' => 'rev_user',
	);

	/**
	 * @var stdClass Database Result Row
	 */
	protected $row;

	/**
	 * Set the database row this message is based on.
	 * @param array $row Database Result Row
	 */
	public function setRow( $row ) {
		$this->row = $row;
	}

	public function translation() {
		if ( !isset( $this->row ) ) {
			return $this->infile();
		}

		return Revision::getRevisionText( $this->row );
	}

	// Re-implemented
	public function getProperty( $key ) {
		if ( !isset( self::$propertyMap[$key] ) ) {
			return parent::getProperty( $key );
		}

		$field = self::$propertyMap[$key];
		if ( !isset( $this->row->$field ) ) {
			return null;
		}

		return $this->row->$field;
	}

	// Re-implemented
	public function getPropertyNames() {
		return array_merge( parent::getPropertyNames(), array_keys( self::$propertyMap ) );
	}
}

/**
 * %Message object where you can directly set the translation.
 * Hence the name fat. Authors are not supported.
 */
class FatMessage extends TMessage {
	/// \string Stored translation.
	protected $translation;

	/**
	 * Set the current translation of this message.
	 * @param string $text
	 */
	public function setTranslation( $text ) {
		$this->translation = $text;
	}

	public function translation() {
		if ( $this->translation === null ) {
			return $this->infile;
		}

		return $this->translation;
	}
}
