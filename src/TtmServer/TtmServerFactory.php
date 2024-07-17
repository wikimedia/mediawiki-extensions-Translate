<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TtmServer;

use InvalidArgumentException;

/**
 * @since 2021.01
 * @license GPL-2.0-or-later
 * @author Niklas Laxström
 */
class TtmServerFactory {
	private array $configs;
	private ?string $default;
	private const TTMSERVER_CLASSES = [
		ReadableTtmServer::class,
		WritableTtmServer::class,
		SearchableTtmServer::class
	];

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

			// Translation memory configuration may not define a type, in such
			// cases we determine whether the service is a TTM server using the
			// interfaces it implements.
			$serviceClass = $config['class'] ?? null;
			if ( $serviceClass !== null ) {
				foreach ( self::TTMSERVER_CLASSES as $ttmClass ) {
					if ( $serviceClass instanceof $ttmClass ) {
						$ttmServersIds[] = $serviceId;
						break;
					}
				}
			}
		}
		return $ttmServersIds;
	}

	public function has( string $name ): bool {
		$ttmServersIds = $this->getNames();
		return in_array( $name, $ttmServersIds );
	}

	public function create( string $name ): TtmServer {
		if ( !$this->has( $name ) ) {
			throw new ServiceCreationFailure( "No configuration for name '$name'" );
		}

		$config = $this->configs[$name];
		if ( !is_array( $config ) ) {
			throw new ServiceCreationFailure( "Invalid configuration for name '$name'" );
		}

		if ( isset( $config['class'] ) ) {
			$class = $config['class'];

			// TODO: Add a factory to create TTM server instances
			if ( in_array( $class, [ DatabaseTtmServer::class, 'DatabaseTTMServer', 'DatabaseTtmServer' ] ) ) {
				return new DatabaseTtmServer( $config );
			}

			return new $class( $config );
		} elseif ( isset( $config['type'] ) ) {
			$type = $config['type'];
			switch ( $type ) {
				case 'ttmserver':
					return new DatabaseTtmServer( $config );
				case 'remote-ttmserver':
					return new RemoteTTMServer( $config );
				default:
					throw new ServiceCreationFailure( "Unknown type for name '$name': $type" );
			}
		}

		throw new ServiceCreationFailure( "Invalid configuration for name '$name': type not specified" );
	}

	public function getDefaultForQuerying(): ReadableTtmServer {
		if ( $this->default === null ) {
			return new FakeTtmServer();
		}

		if ( $this->configs[ $this->default ][ 'writable' ] ?? false ) {
			throw new InvalidArgumentException(
				"Default TTM service {$this->default} cannot be write only"
			);
		}

		$service = $this->create( $this->default );

		if ( $service instanceof ReadableTtmServer ) {
			return $service;
		}

		throw new InvalidArgumentException(
			"Default TTM service {$this->default} must implement ReadableTtmServer."
		);
	}

	/**
	 * Returns writable servers if configured, else returns the default TtmServer else returns null.
	 * @return array [ serverId => WritableTtmServer ]
	 */
	public function getWritable(): array {
		$writableServers = $readOnlyServers = [];
		$ttmServerIds = $this->getNames();

		foreach ( $ttmServerIds as $serverId ) {
			$isWritable = $this->configs[ $serverId ][ 'writable' ] ?? null;

			if ( $isWritable ) {
				if ( $serverId === $this->default ) {
					throw new InvalidArgumentException(
						"Default TTM server {$this->default} cannot be write only"
					);
				}

				$server = $this->create( $serverId );
				if ( !$server instanceof WritableTtmServer ) {
					throw new InvalidArgumentException(
						"Server '$serverId' marked writable does not implement WritableTtmServer interface"
					);
				}
				$writableServers[ $serverId ] = $server;
			} elseif ( $isWritable === false ) {
				$readOnlyServers[] = $serverId;
			}
		}

		if ( $writableServers ) {
			return $writableServers;
		}

		// If there are no writable server, check and use the default server
		if ( $this->default ) {
			$defaultTtmServer = $this->create( $this->default );

			if ( $defaultTtmServer instanceof WritableTtmServer ) {
				if ( !in_array( $this->default, $readOnlyServers ) ) {
					$writableServers[ $this->default ] = $defaultTtmServer;
				}
			}

			if ( $writableServers ) {
				return $writableServers;
			}
		}

		// Did not find any writable servers.
		return [];
	}

	/** Get servers marked as writable */
	public function getWriteOnly(): array {
		$ttmServerIds = $this->getNames();
		$writableServers = [];
		foreach ( $ttmServerIds as $serverId ) {
			if ( $this->configs[ $serverId ][ 'writable' ] ?? false ) {
				$server = $this->create( $serverId );
				if ( !$server instanceof WritableTtmServer ) {
					throw new \InvalidArgumentException(
						"Server '$serverId' marked writable does not implement WritableTtmServer interface"
					);
				}
				$writableServers[ $serverId ] = $server;
			}
		}

		return $writableServers;
	}
}
