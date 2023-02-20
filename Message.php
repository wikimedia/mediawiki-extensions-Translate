<?php
/**
 * Classes for message objects ThinMessage
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2010, Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\MessageLoading\Message;

/**
 * %Message object which is based on database result row. Hence the name thin.
 * Needs fields rev_user_text and those that are needed for loading revision
 * text.
 */
class ThinMessage extends Message {
	// This maps properties to fields in the database result row
	protected static $propertyMap = [
		'last-translator-text' => 'rev_user_text',
		'last-translator-id' => 'rev_user',
	];
	/** @var stdClass Database Result Row */
	protected $row;
	/** @var string Stored translation. */
	protected $translation;

	/**
	 * Set the database row this message is based on.
	 * @param stdClass $row Database Result Row
	 */
	public function setRow( $row ) {
		$this->row = $row;
	}

	/**
	 * Set the current translation of this message.
	 * @param string $text
	 */
	public function setTranslation( $text ) {
		$this->translation = $text;
	}

	/** @inheritDoc */
	public function translation(): ?string {
		if ( !isset( $this->row ) ) {
			return $this->infile();
		}

		return $this->translation;
	}

	// Re-implemented
	public function getProperty( string $key ) {
		if ( !isset( self::$propertyMap[$key] ) ) {
			return parent::getProperty( $key );
		}

		$field = self::$propertyMap[$key];

		return $this->row->$field ?? null;
	}

	// Re-implemented
	public function getPropertyNames(): array {
		return array_merge( parent::getPropertyNames(), array_keys( self::$propertyMap ) );
	}
}
