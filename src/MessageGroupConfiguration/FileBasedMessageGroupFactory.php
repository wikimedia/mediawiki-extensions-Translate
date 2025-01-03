<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupConfiguration;

use FileDependency;
use MainConfigDependency;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\Translate\MessageGroupProcessing\CachedMessageGroupFactory;
use MessageGroupBase;
use Wikimedia\Rdbms\IReadableDatabase;

/**
 * Creates FileBasedMessageGroup message groups.
 * @since 2024.06
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
final class FileBasedMessageGroupFactory implements CachedMessageGroupFactory {
	public const SERVICE_OPTIONS = [ 'TranslateGroupFiles' ];

	private MessageGroupConfigurationParser $messageGroupConfigurationParser;
	/** @var string[] */
	private array $groupFiles;
	private string $contentLanguageCode;

	public function __construct(
		MessageGroupConfigurationParser $messageGroupConfigurationParser,
		string $contentLanguageCode,
		ServiceOptions $serviceOptions
	) {
		$this->messageGroupConfigurationParser = $messageGroupConfigurationParser;
		$this->contentLanguageCode = $contentLanguageCode;
		$serviceOptions->assertRequiredOptions( self::SERVICE_OPTIONS );
		$this->groupFiles = $serviceOptions->get( 'TranslateGroupFiles' );
	}

	public function getCacheKey(): string {
		return 'file-based-groups';
	}

	public function getCacheVersion(): int {
		return 1;
	}

	public function getDependencies(): array {
		$deps = [ new MainConfigDependency( 'TranslateGroupFiles' ) ];

		foreach ( $this->groupFiles as $configFile ) {
			$deps[] = new FileDependency( realpath( $configFile ) );
		}

		return $deps;
	}

	public function getData( IReadableDatabase $db ): array {
		$autoload = $groups = $value = [];
		$parser = $this->messageGroupConfigurationParser;
		foreach ( $this->groupFiles as $configFile ) {
			$yaml = file_get_contents( $configFile );
			$parsedData = $parser->getHopefullyValidConfigurations(
				$yaml,
				static function ( $index, $config, $error ) use ( $configFile ) {
					trigger_error( "Document $index in $configFile is invalid: $error", E_USER_WARNING );
				}
			);

			foreach ( $parsedData as $id => $conf ) {
				if ( !empty( $conf['AUTOLOAD'] ) && is_array( $conf['AUTOLOAD'] ) ) {
					$dir = dirname( $configFile );
					$additions = array_map( static function ( $file ) use ( $dir ) {
						return "$dir/$file";
					}, $conf['AUTOLOAD'] );
					self::appendAutoloader( $additions, $autoload );
				}

				$groups[$id] = $conf;
			}
		}
		$value['groups'] = $groups;
		$value['autoload'] = $autoload;

		return $value;
	}

	/** @inheritDoc */
	public function createGroups( $data ): array {
		global $wgAutoloadClasses;
		self::appendAutoloader( $data['autoload'], $wgAutoloadClasses );

		$groups = [];
		foreach ( $data['groups'] as $id => $conf ) {
			$conf['BASIC']['sourcelanguage'] ??= $this->contentLanguageCode;
			$groups[$id] = MessageGroupBase::factory( $conf );
		}

		return $groups;
	}

	/**
	 * Safely merges first array to second array, throwing warning on duplicates and removing
	 * duplicates from the first array.
	 * @param array $additions Things to append
	 * @param array &$to Where to append
	 */
	private static function appendAutoloader( array $additions, array &$to ) {
		foreach ( $additions as $class => $file ) {
			if ( isset( $to[$class] ) && $to[$class] !== $file ) {
				$msg = "Autoload conflict for $class: $to[$class] !== $file";
				trigger_error( $msg, E_USER_WARNING );
				continue;
			}

			$to[$class] = $file;
		}
	}
}
