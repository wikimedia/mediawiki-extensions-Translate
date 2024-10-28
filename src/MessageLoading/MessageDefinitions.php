<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

use MediaWiki\Title\Title;

/**
 * Wrapper for message definitions, just to beauty the code.
 * @author Niklas Laxström
 * @copyright Copyright © 2007-2011, Niklas Laxström
 * @license GPL-2.0-or-later
 */
class MessageDefinitions {
	private ?int $namespace;
	/** @var string[] */
	private array $messages;
	/** @var Title[]|null */
	private ?array $pages = null;

	/**
	 * @param string[] $messages
	 * @param int|false $namespace
	 */
	public function __construct( array $messages, $namespace = false ) {
		$this->messages = $messages;
		$this->namespace = $namespace === false ? null : $namespace;
	}

	/** @return string[] */
	public function getDefinitions(): array {
		return $this->messages;
	}

	/** @return Title[] List of title indexed by message key. */
	public function getPages(): array {
		$namespace = $this->namespace;
		if ( $this->pages !== null ) {
			return $this->pages;
		}

		$pages = [];
		foreach ( array_keys( $this->messages ) as $key ) {
			if ( $namespace === null ) {
				// pages are in format ex. "8:jan"
				[ $tns, $tkey ] = explode( ':', $key, 2 );
				$title = Title::makeTitleSafe( (int)$tns, $tkey );
			} else {
				$title = Title::makeTitleSafe( $namespace, $key );
			}

			if ( !$title ) {
				wfWarn( "Invalid title ($namespace:)$key" );
				continue;
			}

			$pages[$key] = $title;
		}

		$this->pages = $pages;

		return $this->pages;
	}
}
