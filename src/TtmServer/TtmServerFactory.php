<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TtmServer;

use DatabaseTTMServer;
use FakeTTMServer;
use RemoteTTMServer;
use TTMServer;
use WritableTTMServer;

/**
 * @since 2021.01
 * @license GPL-2.0-or-later
 * @author Niklas Laxström
 */
class TtmServerFactory {
	/** @var array */
	private $configs;
	/** @var ?string */
	private $default;

	/** @see https://www.mediawiki.org/wiki/Help:Extension:Translate/Translation_memories#Configuration */
	public function __construct( array $configs, ?string $default = null ) {
		$this->configs = $configs;
		$this->default = $default;
	}

	/** @return string[] */
	public function getNames(): array {
		$ttmServersIds = [];
		foreach ( $this->configs as $serviceId => $config ) {
			$type = $config['type'] ?? '';
			if ( $type === 'ttmserver' || $type === 'remote-ttmserver' ) {
				$ttmServersIds[] = $serviceId;
			}
		}
		return $ttmServersIds;
	}

	public function has( string $name ): bool {
		$ttmServersIds = $this->getNames();
		return in_array( $name, $ttmServersIds );
	}

	public function create( string $name ): TTMServer {
		if ( !$this->has( $name ) ) {
			throw new ServiceCreationFailure( "No configuration for name '$name'" );
		}

		$config = $this->configs[$name];
		if ( !is_array( $config ) ) {
			throw new ServiceCreationFailure( "Invalid configuration for name '$name'" );
		}

		if ( isset( $config['class'] ) ) {
			$class = $config['class'];
			return new $class( $config );
		} elseif ( isset( $config['type'] ) ) {
			$type = $config['type'];
			switch ( $type ) {
				case 'ttmserver':
					return new DatabaseTTMServer( $config );
				case 'remote-ttmserver':
					return new RemoteTTMServer( $config );
				default:
					throw new ServiceCreationFailure( "Unknown type for name '$name': $type" );
			}
		}

		throw new ServiceCreationFailure( "Invalid configuration for name '$name': type not specified" );
	}

	/** Return the primary service or a no-op fallback if primary cannot be constructed. */
	public function getDefault(): WritableTTMServer {
		$service = null;

		try {
			if ( $this->default !== null ) {
				$service = $this->create( $this->default );
			}
		} catch ( ServiceCreationFailure $e ) {
		}

		if ( $service instanceof WritableTTMServer ) {
			return $service;
		}

		return new FakeTTMServer();
	}
}
