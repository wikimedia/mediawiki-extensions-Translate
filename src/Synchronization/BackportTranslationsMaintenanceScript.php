<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use FileBasedMessageGroup;
use JsonFFS;
use MediaWiki\Extension\Translate\Utilities\BaseMaintenanceScript;
use MediaWiki\Logger\LoggerFactory;
use MessageGroups;
use RuntimeException;
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

		foreach ( $groups as $group ) {
			$groupId = $group->getId();
			if ( !$group instanceof FileBasedMessageGroup ) {
				$this->error( "Skipping $groupId: Not instance of FileBasedMessageGroup" );
				continue;
			}

			if ( !$group->getFFS() instanceof JsonFFS ) {
				$this->error( "Skipping $groupId: Only JSON format is supported" );
				continue;
			}

			/** @var FileBasedMessageGroup $group */
			$sourceLanguage = $group->getSourceLanguage();
			try {
				$sourceDefinitions = $this->loadDefinitions( $group, $sourcePath, $sourceLanguage );
				$targetDefinitions = $this->loadDefinitions( $group, $targetPath, $sourceLanguage );
			} catch ( RuntimeException $e ) {
				$this->output( "Skipping $groupId: Error while loading definitions: {$e->getMessage()}\n" );
				continue;
			}

			$compatibleKeys = $this->getCompatibleKeys(
				$sourceDefinitions['MESSAGES'],
				$targetDefinitions['MESSAGES']
			);

			if ( $compatibleKeys === [] ) {
				$this->output( "Skipping $groupId: No compatible keys found\n" );
				continue;
			}

			$languages = $group->getTranslatableLanguages() ??
				array_keys( TranslateUtils::getLanguageNames( 'en' ) );
			foreach ( $languages as $language ) {
				if ( $language === $sourceLanguage ) {
					continue;
				}

				$this->backport( $group, $sourcePath, $targetPath, $compatibleKeys, $language );
			}
		}
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

	/** @return string[] */
	private function getCompatibleKeys( array $source, array $target ): array {
		$keys = [];
		foreach ( $source as $key => $value ) {
			if ( ( $target[ $key ] ?? null ) === $value ) {
				$keys[] = $key;
			}
		}
		return $keys;
	}

	private function backport(
		FileBasedMessageGroup $group,
		string $source,
		string $targetPath,
		array $compatibleKeys,
		string $language
	): void {
		try {
			$sourceTranslations = $this->loadDefinitions( $group, $source, $language );
		} catch ( RuntimeException $e ) {
			return;
		}

		try {
			$targetTranslations = $this->loadDefinitions( $group, $targetPath, $language );
		} catch ( RuntimeException $e ) {
			$targetTranslations = [
				'MESSAGES' => [],
				'AUTHORS' => [],
			];
		}

		// Amend target with compatible things from source
		$hasUpdates = false;

		$ffs = $group->getFFS();

		// This has been checked before, but checking again to keep Phan and IDEs happy.
		// Remove once support for other FFS are added.
		if ( !$ffs instanceof JsonFFS ) {
			throw new RuntimeException(
				"Expected FFS type: " . JsonFFS::class . '; got: ' . get_class( $ffs )
			);
		}

		foreach ( $compatibleKeys as $key ) {
			$sourceValue = $sourceTranslations[ 'MESSAGES' ][ $key ] ?? null;
			$targetValue = $targetTranslations[ 'MESSAGES' ][ $key ] ?? null;
			if ( $sourceValue === null || $ffs->isContentEqual( $sourceValue, $targetValue ) ) {
				continue;
			}

			$hasUpdates = true;
			$targetTranslations[ 'MESSAGES' ][ $key ] = $sourceValue;
		}

		if ( !$hasUpdates ) {
			return;
		}

		// Copy over all authors (we do not know per-message level)
		$combinedAuthors = array_merge(
			$targetTranslations[ 'AUTHORS' ] ?? [],
			$sourceTranslations[ 'AUTHORS' ] ?? []
		);
		$combinedAuthors = array_unique( $combinedAuthors );
		$combinedAuthors = $ffs->filterAuthors( $combinedAuthors, $language );

		$backportedContent = $ffs->generateFile(
			$targetTranslations,
			$combinedAuthors,
			$targetTranslations[ 'MESSAGES' ]
		);

		$targetFilename = $targetPath . '/' . $group->getTargetFilename( $language );
		if ( file_exists( $targetFilename ) ) {
			$currentContent = file_get_contents( $targetFilename );

			if ( $ffs->shouldOverwrite( $currentContent, $backportedContent ) ) {
				file_put_contents( $targetFilename, $backportedContent );
			}
		} else {
			$this->output( "New language: $language\n" );
			file_put_contents( $targetFilename, $backportedContent );
		}
	}
}
