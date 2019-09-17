<?php
/**
 * Classes for message objects TMessage, ThinMessage and RevisionMessage.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2010, Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;
use MediaWiki\Storage\RevisionRecord;
use MediaWiki\Storage\SlotRecord;

/**
 * Interface for message objects used by MessageCollection.
 */
abstract class TMessage {
	/** @var string Message display key. */
	protected $key;
	/** @var string Message definition. */
	protected $definition;
	/** @var string Committed in-file translation. */
	protected $infile;
	/** @var string[] Message tags. */
	protected $tags = [];
	/** @var array Message properties. */
	protected $props = [];
	/** @var string[] Message reviewers. */
	protected $reviewers = [];

	/**
	 * Creates new message object.
	 *
	 * @param string $key Unique key identifying this message.
	 * @param string $definition The authoritave definition of this message.
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
	 * @param string $text
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
	 * @param string $tag
	 */
	public function addTag( $tag ) {
		$this->tags[] = $tag;
	}

	/**
	 * Check if this message has a given tag.
	 * @param string $tag
	 * @return bool
	 */
	public function hasTag( $tag ) {
		return in_array( $tag, $this->tags, true );
	}

	/**
	 * Return all tags for this message;
	 * @return string[]
	 */
	public function getTags() {
		return $this->tags;
	}

	public function setProperty( $key, $value ) {
		$this->props[$key] = $value;
	}

	public function appendProperty( $key, $value ) {
		if ( !isset( $this->props[$key] ) ) {
			$this->props[$key] = [];
		}
		$this->props[$key][] = $value;
	}

	public function getProperty( $key ) {
		return $this->props[$key] ?? null;
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
 * @deprecated 2019.10 Use RevisionMessage instead.
 * With MCR using plain DB rows for accessing revision text in batches is inefficient,
 * so the revisions with pre-fetched content should be constructed using
 * RevisionStore::newRevisionsFromBatch that's available in MW Core 1.34+
 */
class ThinMessage extends TMessage {
	// This maps properties to fields in the database result row
	protected static $propertyMap = [
		'last-translator-text' => 'rev_user_text',
		'last-translator-id' => 'rev_user',
	];

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

		global $wgMultiContentRevisionSchemaMigrationStage;
		if ( $wgMultiContentRevisionSchemaMigrationStage & SCHEMA_COMPAT_WRITE_OLD ) {
			return Revision::getRevisionText( $this->row );
		} else {
			// Technically this should never happen since this class is deprecated and only
			// used when RevisionStore::newRevisionsFromBatch is not yet available in MW core,
			// (pre 1.34) and SCHEMA_COMPAT_WRITE_OLD was not removed yet in that release.
			// Just in case it does we provide a sensible fallback, but with MCR-enabled schema
			// this will be extremely slow.
			return MediaWikiServices::getInstance()->getRevisionStore()
				->newRevisionFromRow( $this->row )
				->getContent( SlotRecord::MAIN )
				->getNativeData();
		}
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
 * %Message object which is based on a RevisionRecord.
 * Message user name and id as well as message text properties
 * are loaded from the revision.
 */
class RevisionMessage extends TMessage {
	/** @var RevisionRecord */
	private $rev;

	/**
	 * Sets a revision the message is based on
	 * @param RevisionRecord $rev
	 */
	public function setRevision( RevisionRecord $rev ) {
		$this->rev = $rev;
	}

	/**
	 * Get the message translation.
	 * @return string|null
	 */
	public function translation() {
		if ( !$this->rev ) {
			return $this->infile();
		}
		return ContentHandler::getContentText( $this->rev->getContent( SlotRecord::MAIN ) );
	}

	// Re-implemented
	public function getProperty( $key ) {
		switch ( $key ) {
			case 'last-translator-text':
				return $this->rev ? $this->rev->getUser()->getName() : null;
			case 'last-translator-id':
				return $this->rev ? $this->rev->getUser()->getId() : null;
			default:
				return parent::getProperty( $key );
		}
	}

	// Re-implemented
	public function getPropertyNames() {
		return array_merge(
			parent::getPropertyNames(),
			[ 'last-translator-text', 'last-translator-id' ]
		);
	}
}

/**
 * %Message object where you can directly set the translation.
 * Hence the name fat. Authors are not supported.
 */
class FatMessage extends TMessage {
	/** @var string Stored translation. */
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
