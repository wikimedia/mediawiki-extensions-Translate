<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

/**
 * Interface for message objects used by MessageCollection.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2010, Niklas Laxström
 * @license GPL-2.0-or-later
 */
abstract class Message {
	/** Message display key. */
	protected string $key;
	/** Message definition. */
	protected ?string $definition;
	/** Committed in-file translation. */
	protected ?string $infile = null;
	/** @var string[] Message tags. */
	protected array $tags = [];
	/** Message properties. */
	protected array $props = [];
	/** @var string[] Message reviewers. */
	protected array $reviewers = [];

	/**
	 * Creates new message object.
	 *
	 * @param string $key Unique key identifying this message.
	 * @param string|null $definition The authoritave definition of this message.
	 */
	public function __construct( string $key, ?string $definition ) {
		$this->key = $key;
		$this->definition = $definition;
	}

	/** Get the message key. */
	public function key(): string {
		return $this->key;
	}

	/**
	 * Get the message definition.
	 * Message definition should not be empty, but sometimes is.
	 * See: https://phabricator.wikimedia.org/T285830
	 */
	public function definition(): string {
		return $this->definition ?? '';
	}

	public function rawDefinition(): ?string {
		return $this->definition;
	}

	/** Get the message translation. */
	abstract public function translation(): ?string;

	/** Set the committed translation. */
	public function setInfile( string $text ): void {
		$this->infile = $text;
	}

	/** Returns the committed translation. */
	public function infile(): ?string {
		return $this->infile;
	}

	/** Add a tag for this message. */
	public function addTag( string $tag ): void {
		$this->tags[] = $tag;
	}

	/** Check if this message has a given tag. */
	public function hasTag( string $tag ): bool {
		return in_array( $tag, $this->tags, true );
	}

	/**
	 * Return all tags for this message.
	 * @return string[]
	 */
	public function getTags(): array {
		return $this->tags;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function setProperty( string $key, $value ): void {
		$this->props[$key] = $value;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function appendProperty( string $key, $value ): void {
		if ( !isset( $this->props[$key] ) ) {
			$this->props[$key] = [];
		}
		$this->props[$key][] = $value;
	}

	/** @return mixed */
	public function getProperty( string $key ) {
		return $this->props[$key] ?? null;
	}

	/** Get all the available property names. */
	public function getPropertyNames(): array {
		return array_keys( $this->props );
	}
}
