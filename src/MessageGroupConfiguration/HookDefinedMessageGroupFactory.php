<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupConfiguration;

use CacheDependency;
use MediaWiki\Extension\Translate\HookRunner;
use MediaWiki\Extension\Translate\MessageGroupProcessing\CachedMessageGroupFactory;
use Wikimedia\Rdbms\IReadableDatabase;

/**
 * Creates message groups from the TranslatePostInitGroupsHook.
 * @since 2024.06
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
final class HookDefinedMessageGroupFactory implements CachedMessageGroupFactory {
	private HookRunner $hookRunner;
	/** @var CacheDependency[] */
	private array $deps;

	public function __construct( HookRunner $hookRunner ) {
		$this->hookRunner = $hookRunner;
	}

	public function getCacheKey(): string {
		return 'hook-defined-groups';
	}

	public function getCacheVersion(): int {
		return 1;
	}

	public function getDependencies(): array {
		return $this->deps;
	}

	public function getData( IReadableDatabase $db ): array {
		$groups = $deps = $autoload = [];
		// When possible, a cache dependency is created to automatically recreate
		// the cache when configuration changes. Currently used by other extensions
		// such as Banner Messages and test cases to load message groups.
		$this->hookRunner->onTranslatePostInitGroups( $groups, $deps, $autoload );

		$value = [
			'groups' => $groups,
			'autoload' => $autoload
		];

		// Not ideal, but getDependencies is currently called after getData()
		$this->deps = $deps;

		return $value;
	}

	/** @inheritDoc */
	public function createGroups( $data ): array {
		global $wgAutoloadClasses;
		self::appendAutoloader( $data['autoload'], $wgAutoloadClasses );

		return $data['groups'];
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
