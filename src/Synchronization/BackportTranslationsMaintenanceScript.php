<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use FileBasedMessageGroup;
use JsonFFS;
use MediaWiki\Extension\Translate\Utilities\BaseMaintenanceScript;
use MediaWiki\Logger\LoggerFactory;
use MessageGroups;
use RuntimeException;
use SimpleFFS;
use TranslateUtils;

/**
 * Script to backport translations from one branch to another.
 *
 * @since 2021.05
 * @license GPL-2.0-or-later
 * @author Niklas Laxström
 */
class BackportTranslationsMaintenanceScript extends BaseMaintenanceScript {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Backport translations from one branch to another.' );

		$this->addOption(
			'group',
			'Comma separated list of message group IDs (supports * wildcard) to backport',
			self::REQUIRED,
			self::HAS_ARG
		);
		$this->addOption(
			'source-path',
			'Source path for reading updated translations. Defaults to $wgTranslateGroupRoot.',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->addOption(
			'target-path',
			'Target path for writing backported translations',
			self::REQUIRED,
			self::HAS_ARG
		);
		$this->addOption(
			'filter-path',
			'Only export a group if its export path matches this prefix (relative to target-path)',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->addOption(
			'never-export-languages',
			'Languages to not export',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->requireExtension( 'Translate' );
	}

	/** @inheritDoc */
	public function execute() {
		$config = $this->getConfig();
		$logger = LoggerFactory::getInstance( 'Translate.GroupSynchronization' );
		$groupPattern = $this->getOption( 'group' ) ?? '';
		$logger->info(
			'Starting backports for groups {groups}',
			[ 'groups' => $groupPattern ]
		);

		$sourcePath = $this->getOption( 'source-path' ) ?: $config->get( 'TranslateGroupRoot' );
		if ( !is_readable( $sourcePath ) ) {
			$this->fatalError( "Source directory is not readable ($sourcePath)." );
		}

		$targetPath = $this->getOption( 'target-path' );
		if ( !is_writable( $targetPath ) ) {
			$this->fatalError( "Target directory is not writable ($targetPath)." );
		}

		$groupIds = MessageGroups::expandWildcards( explode( ',', trim( $groupPattern ) ) );
		$groups = MessageGroups::getGroupsById( $groupIds );
		if ( $groups === [] ) {
			$this->fatalError( "Pattern $groupPattern did not match any message groups." );
		}

		$neverExportLanguages = $this->csv2array(
			$this->getOption( 'never-export-languages' ) ?? ''
		);
		$supportedLanguages = TranslateUtils::getLanguageNames( 'en' );

		foreach ( $groups as $group ) {
			$groupId = $group->getId();
			if ( !$group instanceof FileBasedMessageGroup ) {
				if ( !$this->hasOption( 'filter-path' ) ) {
					$this->error( "Skipping $groupId: Not instance of FileBasedMessageGroup" );
				}
				continue;
			}

			if ( !$group->getFFS() instanceof JsonFFS ) {
				$this->error( "Skipping $groupId: Only JSON format is supported" );
				continue;
			}

			if ( $this->hasOption( 'filter-path' ) ) {
				$filter = $this->getOption( 'filter-path' );
				$exportPath = $group->getTargetFilename( '*' );
				if ( !$this->matchPath( $filter, $exportPath ) ) {
					continue;
				}
			}

			/** @var FileBasedMessageGroup $group */
			$sourceLanguage = $group->getSourceLanguage();
			try {
				$sourceDefinitions = $this->loadDefinitions( $group, $sourcePath, $sourceLanguage );
				$targetDefinitions = $this->loadDefinitions( $group, $targetPath, $sourceLanguage );
			} catch ( RuntimeException $e ) {
				$this->output(
					"Skipping $groupId: Error while loading definitions: {$e->getMessage()}\n"
				);
				continue;
			}

			$keyCompatibilityMap = $this->getKeyCompatibilityMap(
				$sourceDefinitions['MESSAGES'],
				$targetDefinitions['MESSAGES'],
				$group->getFFS()
			);

			if ( array_filter( $keyCompatibilityMap ) === [] ) {
				$this->output( "Skipping $groupId: No compatible keys found\n" );
				continue;
			}

			$summary = [];
			$languages = array_keys( $group->getTranslatableLanguages() ?? $supportedLanguages );
			$languagesToSkip = $neverExportLanguages;
			$languagesToSkip[] = $sourceLanguage;
			$languages = array_diff( $languages, $languagesToSkip );

			foreach ( $languages as $language ) {
				$status = $this->backport(
					$group,
					$sourcePath,
					$targetPath,
					$keyCompatibilityMap,
					$language
				);

				$summary[$status][] = $language;
			}

			$numUpdated = count( $summary[ 'updated' ] ?? [] );
			$numAdded = count( $summary[ 'new' ] ?? [] );
			if ( ( $numUpdated + $numAdded ) > 0 ) {
				$this->output(
					sprintf(
						"%s: Compatible keys: %d. Updated %d languages, %d new (%s)\n",
						$group->getId(),
						count( $keyCompatibilityMap ),
						$numUpdated,
						$numAdded,
						implode( ', ', $summary[ 'new' ] ?? [] )
					)
				);
			}
		}
	}

	private function csv2array( string $input ): array {
		return array_filter(
			array_map( 'trim', explode( ',', $input ) ),
			static function ( $v ) {
				return $v !== '';
			}
		);
	}

	private function matchPath( string $prefix, string $full ): bool {
		$prefix = "./$prefix";
		$length = strlen( $prefix );
		return substr( $full, 0, $length ) === $prefix;
	}

	private function loadDefinitions(
		FileBasedMessageGroup $group,
		string $path,
		string $language
	): array {
		$file = $path . '/' . $group->getTargetFilename( $language );

		if ( !file_exists( $file ) ) {
			throw new RuntimeException( "File $file does not exist" );
		}

		$contents = file_get_contents( $file );
		return $group->getFFS()->readFromVariable( $contents );
	}

	/**
	 * Compares two arrays and returns a new array with keys from the target array with associated values
	 * being a boolean indicating whether the source array value is compatible with the target array value.
	 *
	 * Target array key order was chosen because in backporting we want to use the order of keys in the
	 * backport target (stable branch). Comparison is done with SimpleFFS::isContentEqual.
	 *
	 * @return array<string,bool> Keys in target order
	 */
	private function getKeyCompatibilityMap( array $source, array $target, SimpleFFS $ffs ): array {
		$keys = [];
		foreach ( $target as $key => $value ) {
			$keys[$key] = isset( $source[ $key ] ) && $ffs->isContentEqual( $source[ $key ], $value );
		}
		return $keys;
	}

	private function backport(
		FileBasedMessageGroup $group,
		string $source,
		string $targetPath,
		array $keyCompatibilityMap,
		string $language
	): string {
		try {
			$sourceTemplate = $this->loadDefinitions( $group, $source, $language );
		} catch ( RuntimeException $e ) {
			return 'no definitions';
		}

		try {
			$targetTemplate = $this->loadDefinitions( $group, $targetPath, $language );
		} catch ( RuntimeException $e ) {
			$targetTemplate = [
				'MESSAGES' => [],
				'AUTHORS' => [],
			];
		}

		// Amend the target with compatible things from the source
		$hasUpdates = false;

		$ffs = $group->getFFS();

		// This has been checked before, but checking again to keep Phan and IDEs happy.
		// Remove once support for other FFS are added.
		if ( !$ffs instanceof JsonFFS ) {
			throw new RuntimeException(
				"Expected FFS type: " . JsonFFS::class . '; got: ' . get_class( $ffs )
			);
		}

		$combinedMessages = [];
		// $keyCompatibilityMap has the target (stable branch) source language key order
		foreach ( $keyCompatibilityMap as $key => $isCompatible ) {
			$sourceValue = $sourceTemplate['MESSAGES'][$key] ?? null;
			$targetValue = $targetTemplate['MESSAGES'][$key] ?? null;

			// Use existing translation value from the target (stable branch) as the default
			if ( $targetValue !== null ) {
				$combinedMessages[$key] = $targetValue;
			}

			// If the source (development branch) has a different translation for a compatible key
			// replace the target (stable branch) translation with it.
			if ( !$isCompatible ) {
				continue;
			}
			if ( $sourceValue !== null && !$ffs->isContentEqual( $sourceValue, $targetValue ) ) {
				// Keep track if we actually overwrote any values, so we can report back stats
				$hasUpdates = true;
				$combinedMessages[$key] = $sourceValue;
			}
		}

		if ( !$hasUpdates ) {
			return 'no updates';
		}

		// Copy over all authors (we do not know per-message level)
		$combinedAuthors = array_merge(
			$targetTemplate[ 'AUTHORS' ] ?? [],
			$sourceTemplate[ 'AUTHORS' ] ?? []
		);
		$combinedAuthors = array_unique( $combinedAuthors );
		$combinedAuthors = $ffs->filterAuthors( $combinedAuthors, $language );

		$targetTemplate['AUTHORS'] = $combinedAuthors;
		$targetTemplate['MESSAGES'] = $combinedMessages;

		$backportedContent = $ffs->generateFile( $targetTemplate );

		$targetFilename = $targetPath . '/' . $group->getTargetFilename( $language );
		if ( file_exists( $targetFilename ) ) {
			$currentContent = file_get_contents( $targetFilename );

			if ( $ffs->shouldOverwrite( $currentContent, $backportedContent ) ) {
				file_put_contents( $targetFilename, $backportedContent );
			}
			return 'updated';
		} else {
			file_put_contents( $targetFilename, $backportedContent );
			return 'new';
		}
	}
}
