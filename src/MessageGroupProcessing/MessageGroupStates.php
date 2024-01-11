<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

/**
 * Wrapper class for using message group states.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2012-2013 Niklas Laxström
 * @license GPL-2.0-or-later
 */
class MessageGroupStates {
	private const CONDKEY = 'state conditions';

	private ?array $config;

	public function __construct( ?array $config = null ) {
		$this->config = $config;
	}

	public function getStates(): ?array {
		$conf = $this->config;
		unset( $conf[self::CONDKEY] );

		return $conf;
	}

	public function getConditions(): array {
		$conf = $this->config;
		return $conf[self::CONDKEY] ?? [];
	}
}

class_alias( MessageGroupStates::class, 'MessageGroupStates' );
