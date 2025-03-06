<?php

namespace MediaWiki\Extension\Translate\Synchronization;

use FileBasedMessageGroup;
use MediaWiki\Extension\Translate\FileFormatSupport\GettextFormat;
use MediaWiki\Extension\Translate\LogNames;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Statistics\MessageGroupStats;
use MediaWiki\Extension\Translate\Utilities\BaseMaintenanceScript;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MessageGroup;

/**
 * Script to export translations of message groups to files.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0-or-later
 */
class ExportTranslationsMaintenanceScript extends BaseMaintenanceScript {
	/** The translation file should be deleted if it exists */
	private const ACTION_DELETE = 'delete';
	/** The translation file should be created or updated */
	private const ACTION_CREATE = 'create';
	/** The translation file should be updated if it exists, but should not be created as a new file */
	private const ACTION_UPDATE = 'update';

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Export translations to files.' );

		$this->addOption(
			'group',
			'Comma separated list of message group IDs (supports * wildcard) to export',
			self::REQUIRED,
			self::HAS_ARG
		);
		$this->addOption(
			'lang',
			'Comma separated list of language codes to export or * for all languages',
			self::REQUIRED,
			self::HAS_ARG
		);
		$this->addOption(
			'always-export-languages',
			'(optional) Comma separated list of languages to export ignoring export threshold',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->addOption(
			'never-export-languages',
			'(optional) Comma separated list of languages to never export (overrides everything else)',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->addOption(
			'skip-source-language',
			'(optional) Do not export the source language of each message group',
			self::OPTIONAL,
			self::NO_ARG
		);
		$this->addOption(
			'target',
			'Target directory for exported files',
			self::REQUIRED,
			self::HAS_ARG
		);
		$this->addOption(
			'skipgroup',
			'(optional) Comma separated list of message group IDs (supports * wildcard) to not export',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->addOption(
			'threshold',
			'(optional) Threshold for translation completion percentage that must be exceeded for initial export',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->addOption(
			'removal-threshold',
			'(optional) Threshold for translation completion percentage that must be exceeded to keep the file',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->addOption(
			'no-fuzzy',
			'(optional) Do not include any messages marked as fuzzy/outdated'
		);
		$this->addOption(
			'offline-gettext-format',
			'(optional) Export languages in offline Gettext format. Give a file pattern with '
			. '%GROUPID% and %CODE%. Empty pattern defaults to %GROUPID%/%CODE%.po.',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->addOption(
			'skip-group-sync-check',
			'(optional) Skip exporting group if synchronization is still in progress or if there ' .
				'was an error during synchronization. See: ' .
				'https://www.mediawiki.org/wiki/Help:Extension:Translate/Group_management#Strong_synchronization'
		);

		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		$logger = LoggerFactory::getInstance( LogNames::GROUP_SYNCHRONIZATION );
		$groupPattern = $this->getOption( 'group' ) ?? '';
		$groupSkipPattern = $this->getOption( 'skipgroup' ) ?? '';
		$skipGroupSyncCheck = $this->hasOption( 'skip-group-sync-check' );

		$logger->info(
			'Starting exports for groups {groups}',
			[ 'groups' => $groupPattern ]
		);
		$exportStartTime = microtime( true );

		$target = $this->getOption( 'target' );
		if ( !is_writable( $target ) ) {
			$this->fatalError( "Target directory is not writable ($target)." );
		}

		$exportThreshold = $this->getOption( 'threshold' );
		$removalThreshold = $this->getOption( 'removal-threshold' );
		$noFuzzy = $this->hasOption( 'no-fuzzy' );
		$requestedLanguages = $this->parseLanguageCodes( $this->getOption( 'lang' ) );
		$alwaysExportLanguages = $this->csv2array(
			$this->getOption( 'always-export-languages' ) ?? ''
		);
		$neverExportLanguages = $this->csv2array(
			$this->getOption( 'never-export-languages' ) ?? ''
		);
		$skipSourceLanguage = $this->hasOption( 'skip-source-language' );

		$forOffline = $this->hasOption( 'offline-gettext-format' );
		$offlineTargetPattern = $this->getOption( 'offline-gettext-format' ) ?: "%GROUPID%/%CODE%.po";

		$groups = $this->getMessageGroups( $groupPattern, $groupSkipPattern, $forOffline );
		if ( $groups === [] ) {
			$this->fatalError( 'EE1: No valid message groups identified.' );
		}

		$groupSyncCacheEnabled = MediaWikiServices::getInstance()->getMainConfig()
			->get( 'TranslateGroupSynchronizationCache' );
		$groupSyncCache = Services::getInstance()->getGroupSynchronizationCache();

		foreach ( $groups as $groupId => $group ) {
			if ( $groupSyncCacheEnabled && !$skipGroupSyncCheck ) {
				if ( !$this->canGroupBeExported( $groupSyncCache, $groupId ) ) {
					continue;
				}
			}

			if ( $exportThreshold !== null || $removalThreshold !== null ) {
				$logger->info( 'Calculating stats for group {groupId}', [ 'groupId' => $groupId ] );
				$tStartTime = microtime( true );

				$languageExportActions = $this->getLanguageExportActions(
					$groupId,
					$requestedLanguages,
					$alwaysExportLanguages,
					(int)$exportThreshold,
					(int)$removalThreshold
				);

				$tEndTime = microtime( true );
				$logger->info(
					'Finished calculating stats for group {groupId}. Time: {duration} secs',
					[
						'groupId' => $groupId,
						'duration' => round( $tEndTime - $tStartTime, 3 ),
					]
				);
			} else {
				// Convert list to an associative array
				$languageExportActions = array_fill_keys( $requestedLanguages, self::ACTION_CREATE );

				foreach ( $alwaysExportLanguages as $code ) {
					$languageExportActions[ $code ] = self::ACTION_CREATE;
				}
			}

			foreach ( $neverExportLanguages as $code ) {
				unset( $languageExportActions[ $code ] );
			}

			if ( $skipSourceLanguage ) {
				unset( $languageExportActions[ $group->getSourceLanguage() ] );
			}

			if ( $languageExportActions === [] ) {
				continue;
			}

			$this->output( "Exporting group $groupId\n" );
			$logger->info( 'Exporting group {groupId}', [ 'groupId' => $groupId ] );

			if ( $forOffline ) {
				$fileBasedGroup = FileBasedMessageGroup::newFromMessageGroup( $group, $offlineTargetPattern );
				$fileFormat = new GettextFormat( $fileBasedGroup );
				$fileFormat->setOfflineMode( true );
			} else {
				$fileBasedGroup = $group;
				// At this point $group should be an instance of FileBasedMessageGroup
				// This is primarily to keep linting tools / IDE happy.
				if ( !$fileBasedGroup instanceof FileBasedMessageGroup ) {
					$this->fatalError( "EE2: Unexportable message group $groupId" );
				}
				$fileFormat = $fileBasedGroup->getFFS();
			}

			$fileFormat->setWritePath( $target );
			$sourceLanguage = $group->getSourceLanguage();
			$collection = $group->initCollection( $sourceLanguage );

			$inclusionList = $group->getTranslatableLanguages();

			$langExportTimes = [
				'collection' => 0,
				'ffs' => 0,
			];

			$languagesExportedCount = 0;

			$langStartTime = microtime( true );
			foreach ( $languageExportActions as $lang => $action ) {
				// Check for customized list of translatable languages
				if ( $inclusionList !== MessageGroup::DEFAULT_LANGUAGES && !isset( $inclusionList[$lang] ) ) {
					continue;
				}

				$targetFilePath = $target . '/' . $fileBasedGroup->getTargetFilename( $lang );
				if ( $action === self::ACTION_DELETE ) {
					// phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
					@$ok = unlink( $targetFilePath );
					if ( $ok ) {
						$logger->info( "Removed $targetFilePath due to removal threshold" );
					}
					continue;
				} elseif ( $action === self::ACTION_UPDATE && !file_exists( $targetFilePath ) ) {
					// Language is under export threshold, do not export yet
					$logger->info( "Not creating $targetFilePath due to export threshold" );
					continue;
				}

				$startTime = microtime( true );
				$collection->resetForNewLanguage( $lang );
				$collection->loadTranslations();
				// Don't export ignored, unless it is the source language
				// or message documentation
				global $wgTranslateDocumentationLanguageCode;
				if ( $lang !== $wgTranslateDocumentationLanguageCode
					&& $lang !== $sourceLanguage
				) {
					$collection->filter( MessageCollection::FILTER_IGNORED, MessageCollection::EXCLUDE_MATCHING );
				}

				if ( $noFuzzy ) {
					$collection->filter( MessageCollection::FILTER_FUZZY, MessageCollection::EXCLUDE_MATCHING );
				}

				$languagesExportedCount++;

				$endTime = microtime( true );
				$langExportTimes['collection'] += ( $endTime - $startTime );

				$startTime = microtime( true );
				$fileFormat->write( $collection );
				$endTime = microtime( true );
				$langExportTimes['ffs'] += ( $endTime - $startTime );
			}
			$langEndTime = microtime( true );

			$logger->info(
				'Done exporting {count} languages for group {groupId}. Time taken {duration} secs.',
				[
					'count' => $languagesExportedCount,
					'groupId' => $groupId,
					'duration' => round( $langEndTime - $langStartTime, 3 ),
				]
			);

			foreach ( $langExportTimes as $type => $time ) {
				$logger->info(
					'Time taken by "{type}" for group {groupId} – {duration} secs.',
					[
						'groupId' => $groupId,
						'type' => $type,
						'duration' => round( $time, 3 ),
					]
				);
			}
		}

		$exportEndTime = microtime( true );
		$logger->info(
			'Finished export process for groups {groups}. Time: {duration} secs.',
			[
				'groups' => $groupPattern,
				'duration' => round( $exportEndTime - $exportStartTime, 3 ),
			]
		);
	}

	/** @return MessageGroup[] */
	private function getMessageGroups(
		string $groupPattern,
		string $excludePattern,
		bool $forOffline
	): array {
		$groupIds = MessageGroups::expandWildcards( explode( ',', trim( $groupPattern ) ) );
		$groups = MessageGroups::getGroupsById( $groupIds );
		if ( !$forOffline ) {
			foreach ( $groups as $groupId => $group ) {
				if ( $group->isMeta() ) {
					$this->output( "Skipping meta message group $groupId.\n" );
					unset( $groups[$groupId] );
					continue;
				}

				if ( !$group instanceof FileBasedMessageGroup ) {
					$this->output( "EE2: Unexportable message group $groupId.\n" );
					unset( $groups[$groupId] );
				}
			}
		}

		$skipIds = MessageGroups::expandWildcards( explode( ',', trim( $excludePattern ) ) );
		foreach ( $skipIds as $groupId ) {
			if ( isset( $groups[$groupId] ) ) {
				unset( $groups[$groupId] );
				$this->output( "Group $groupId is in skipgroup.\n" );
			}
		}

		return $groups;
	}

	/** @return string[] */
	private function getLanguageExportActions(
		string $groupId,
		array $requestedLanguages,
		array $alwaysExportLanguages,
		int $exportThreshold = 0,
		int $removalThreshold = 0
	): array {
		$stats = MessageGroupStats::forGroup( $groupId );

		$languages = [];

		foreach ( $requestedLanguages as $code ) {
			// Statistics unavailable. This should only happen if unknown language code requested.
			if ( !isset( $stats[$code] ) ) {
				continue;
			}

			$total = $stats[$code][MessageGroupStats::TOTAL];
			$translated = $stats[$code][MessageGroupStats::TRANSLATED];
			$percentage = $total ? $translated / $total * 100 : 0;

			if ( $percentage === 0 || $percentage < $removalThreshold ) {
				$languages[$code] = self::ACTION_DELETE;
			} elseif ( $percentage > $exportThreshold ) {
				$languages[$code] = self::ACTION_CREATE;
			} else {
				$languages[$code] = self::ACTION_UPDATE;
			}
		}

		foreach ( $alwaysExportLanguages as $code ) {
			$languages[$code] = self::ACTION_CREATE;
			// DWIM: Do not export languages with zero translations, even if requested
			if ( ( $stats[$code][MessageGroupStats::TRANSLATED] ?? null ) === 0 ) {
				$languages[$code] = self::ACTION_DELETE;
			}
		}

		return $languages;
	}

	private function canGroupBeExported( GroupSynchronizationCache $groupSyncCache, string $groupId ): bool {
		if ( $groupSyncCache->isGroupBeingProcessed( $groupId ) ) {
			$this->error( "Group $groupId is currently being synchronized; skipping exports\n" );
			return false;
		}

		if ( $groupSyncCache->groupHasErrors( $groupId ) ) {
			$this->error( "Skipping $groupId due to synchronization error\n" );
			return false;
		}

		if ( $groupSyncCache->isGroupInReview( $groupId ) ) {
			$this->error( "Group $groupId is currently in review. Review changes on Special:ManageMessageGroups\n" );
			return false;
		}
		return true;
	}

	/** @return string[] */
	private function csv2array( string $input ): array {
		return array_filter(
			array_map( 'trim', explode( ',', $input ) ),
			static function ( $v ) {
				return $v !== '';
			}
		);
	}

	/** @return string[] */
	private function parseLanguageCodes( string $input ): array {
		if ( $input === '*' ) {
			$languageNameUtils = MediaWikiServices::getInstance()->getLanguageNameUtils();
			$languages = $languageNameUtils->getLanguageNames();
			ksort( $languages );
			return array_keys( $languages );
		}

		return $this->csv2array( $input );
	}
}
