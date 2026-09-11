<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupConfiguration;

use AggregateMessageGroup;
use FileBasedMessageGroup;
use InvalidArgumentException;
use MediaWikiExtensionMessageGroup;
use MessagePrefixMessageGroup;

/**
 * Registry of built-in symbolic message group type IDs.
 *
 * Maps stable type identifiers to ObjectFactory-compatible construction
 * specifications. Does not create message groups.
 *
 * @since 2026.09
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 */
class MessageGroupTypeRegistry {
	private const TYPES = [
		'file' => [
			'class' => FileBasedMessageGroup::class,
		],
		'aggregate' => [
			'class' => AggregateMessageGroup::class,
		],
		'mediawiki-extension' => [
			'class' => MediaWikiExtensionMessageGroup::class,
		],
		'message-prefix' => [
			'class' => MessagePrefixMessageGroup::class,
		],
	];

	/**
	 * Return the ObjectFactory-compatible construction specification for a type ID.
	 *
	 * @param string $type Registered type identifier.
	 * @return array Construction specification.
	 * @throws InvalidArgumentException if the type ID is not registered.
	 */
	public function getSpec( string $type ): array {
		if ( !isset( self::TYPES[$type] ) ) {
			throw new InvalidArgumentException( "Unknown message group type: '$type'" );
		}
		return self::TYPES[$type];
	}

	/**
	 * Return the implementation class name for a type ID.
	 *
	 * @param string $type Registered type identifier.
	 * @return string Fully-qualified class name.
	 * @throws InvalidArgumentException if the type ID is not registered.
	 */
	public function getClass( string $type ): string {
		return $this->getSpec( $type )['class'];
	}
}
