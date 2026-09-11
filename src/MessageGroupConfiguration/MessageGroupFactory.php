<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupConfiguration;

use InvalidArgumentException;
use MessageGroup;
use MessageGroupBase;

/**
 * Canonical factory for constructing YAML-configured message groups.
 *
 * Centralises selector validation, type resolution, implementation
 * validation, construction, and configuration initialisation that was
 * previously spread across MessageGroupBase::factory() and its callers.
 *
 * MessageGroupBase::factory() remains as a backwards-compatible static
 * entry point and delegates here.
 *
 * @since 2026.09
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 */
class MessageGroupFactory {
	public function __construct(
		private readonly MessageGroupTypeRegistry $typeRegistry
	) {
	}

	/**
	 * Construct and initialise a MessageGroupBase instance from a parsed
	 * group configuration array.
	 *
	 * Exactly one of BASIC.type or BASIC.class must be present. Providing
	 * both, or neither, throws InvalidArgumentException.
	 *
	 * @param array $conf Parsed group configuration.
	 * @return MessageGroup
	 * @throws InvalidArgumentException on invalid selector or implementation.
	 */
	public function createGroup( array $conf ): MessageGroup {
		$class = $this->resolveClass( $conf );
		return $this->instantiate( $class, $conf );
	}

	/**
	 * Resolve the implementation class from the configuration selector.
	 *
	 * @param array $conf
	 * @return string Fully-qualified class name.
	 * @throws InvalidArgumentException
	 */
	private function resolveClass( array $conf ): string {
		$basic = $conf['BASIC'] ?? [];
		$hasType = array_key_exists( 'type', $basic );
		$hasClass = array_key_exists( 'class', $basic );

		if ( $hasType && $hasClass ) {
			throw new InvalidArgumentException(
				"Message group configuration must not specify both 'type' and 'class' " .
				"(group id: '{$basic['id']}')"
			);
		}

		if ( !$hasType && !$hasClass ) {
			throw new InvalidArgumentException(
				"Message group configuration must specify either 'type' or 'class' " .
				"(group id: '{$basic['id']}')"
			);
		}

		if ( $hasType ) {
			return $this->typeRegistry->getClass( $basic['type'] );
		}

		return $basic['class'];
	}

	/**
	 * Instantiate and initialise a MessageGroupBase from a resolved class name.
	 *
	 * @param string $class
	 * @param array $conf
	 * @return MessageGroup
	 * @throws InvalidArgumentException if the class is not a MessageGroupBase subclass.
	 */
	private function instantiate( string $class, array $conf ): MessageGroup {
		if ( !class_exists( $class ) ) {
			throw new InvalidArgumentException(
				"Message group implementation class '$class' does not exist or cannot be autoloaded"
			);
		}

		if ( !is_subclass_of( $class, MessageGroupBase::class ) ) {
			throw new InvalidArgumentException(
				"Message group implementation class '$class' must extend MessageGroupBase"
			);
		}

		return MessageGroupBase::newFromConf( $class, $conf );
	}
}
