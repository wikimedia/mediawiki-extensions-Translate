<?php
declare( strict_types=1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

use stdClass;

/**
 * Message object which is based on database result row. Hence the name thin.
 * Needs fields rev_user_text and those that are needed for loading revision
 * text.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2010, Niklas Laxström
 * @license GPL-2.0-or-later
 */
class ThinMessage extends Message {
	/** This maps properties to fields in the database result row */
	protected static array $propertyMap = [
		'last-translator-text' => 'rev_user_text',
		'last-translator-id' => 'rev_user',
	];
	/** Database Result Row */
	protected ?stdClass $row = null;
	/** Stored translation. */
	protected ?string $translation = null;

	/**
	 * Set the database row this message is based on.
	 * @param stdClass $row Database Result Row
	 */
	public function setRow( stdClass $row ): void {
		$this->row = $row;
	}

	/** Set the current translation of this message. */
	public function setTranslation( string $text ): void {
		$this->translation = $text;
	}

	/** @inheritDoc */
	public function translation(): ?string {
		if ( $this->row === null ) {
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
