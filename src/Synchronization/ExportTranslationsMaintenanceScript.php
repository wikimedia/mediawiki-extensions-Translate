<?php

namespace MediaWiki\Extension\Translate\Synchronization;

use FileBasedMessageGroup;
use GettextFFS;
use Maintenance;
use MediaWiki\Logger\LoggerFactory;
use MessageGroup;
use MessageGroups;
use MessageGroupStats;
use MessageHandle;
use Title;
use TranslateUtils;

/**
 * Script to export translations of message groups to files.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0-or-later
 */
class ExportTranslationsMaintenanceScript extends Maintenance {
	/// The translation file should be deleted if it exists
	private const ACTION_DELETE = 'delete';
	/// The translation file should be created or updated
	private const ACTION_CREATE = 'create';
	/// The translation file should be updated if exists, but not created as a new
	private const ACTION_UPDATE = 'update';

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Export translations to files.' );
		$this->addOption(
			'group',
			'Comma separated list of message group IDs (supports * wildcard) to export',
			true, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'lang',
			'Comma separated list of language codes or *',
			true, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'target',
			'Target directory for exported files',
			true, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'skip',
			'(optional) Languages to skip, comma separated list',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'skipgroup',
			'(optional) Comma separated list of message group IDs (supports * wildcard) to not export',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'threshold',
			'(optional) Threshold for translation completion percentage that must be exceeded for initial export',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'removal-threshold',
			'(optional) Threshold for translation completion percentage that must be exceeded to keep the file',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'hours',
			'(optional) Only export languages with changes in the last given number of hours',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'no-fuzzy',
			'(optional) Do not include any messages marked as fuzzy/outdated',
			false, /*required*/
			false /*has arg*/
		);

		$this->addOption(
			'offline-gettext-format',
			'(optional) Export languages in offline Gettext format. Give a file pattern with '
			. '%GROUPID% and %CODE%. Empty pattern defaults to %GROUPID%/%CODE%.po.',
			false, /*required*/
			true /*has arg*/
		);
		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		$logger = LoggerFactory::getInstance( 'Translate.GroupSynchronization' );
		$groupPattern = $this->getOption( 'group' ) ?? '';
		$groupSkipPattern = $this->getOption( 'skipgroup' ) ?? '';

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

		$reqLangs = TranslateUtils::parseLanguageCodes( $this->getOption( 'lang' ) );
		if ( $this->hasOption( 'skip' ) ) {
			$skipLangs = array_map( 'trim', explode( ',', $this->getOption( 'skip' ) ) );
			$reqLangs = array_diff( $reqLangs, $skipLangs );
		}

		$forOffline = $this->hasOption( 'offline-gettext-format' );
		$offlineTargetPattern = $this->getOption( 'offline-gettext-format' ) ?: "%GROUPID%/%CODE%.po";

		$groups = $this->getMessageGroups( $groupPattern, $groupSkipPattern, $forOffline );
		if ( $groups === [] ) {
			$this->fatalError( 'EE1: No valid message groups identified.' );
		}

		$changeFilter = null;
		if ( $this->hasOption( 'hours' ) ) {
			$changeFilter =	$this->getRecentlyChangedItems(
				(int)$this->getOption( 'hours' ),
				$this->getNamespacesForGroups( $groups )
			);
		}

		foreach ( $groups as $groupId => $group ) {
			// No changes to this group at all
			if ( is_array( $changeFilter ) && !isset( $changeFilter[$groupId] ) ) {
				$this->output( "No recent changes to $groupId.\n" );
				continue;
			}

			if ( $exportThreshold || $removalThreshold ) {
				$logger->info( 'Calculating stats for group {groupId}', [ 'groupId' => $groupId ] );
				$tStartTime = microtime( true );

				$languageExportActions = $this->getLanguageExportActions(
					$groupId,
					$reqLangs,
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
				// Convert list to an associate array
				$languageExportActions = array_fill_keys( $reqLangs, self::ACTION_CREATE );
			}

			if ( $languageExportActions === [] ) {
				continue;
			}

			$this->output( "Exporting group $groupId\n" );
			$logger->info( 'Exporting group {groupId}', [ 'groupId' => $groupId ] );

			/** @var FileBasedMessageGroup $fileBasedGroup */
			if ( $forOffline ) {
				$fileBasedGroup = FileBasedMessageGroup::newFromMessageGroup( $group, $offlineTargetPattern );
				$ffs = new GettextFFS( $fileBasedGroup );
				$ffs->setOfflineMode( true );
			} else {
				$fileBasedGroup = $group;
				$ffs = $group->getFFS();
			}

			$ffs->setWritePath( $target );
			$sourceLanguage = $group->getSourceLanguage();
			$collection = $group->initCollection( $sourceLanguage );

			$whitelist = $group->getTranslatableLanguages();

			$langExportTimes = [
				'collection' => 0,
				'ffs' => 0,
			];

			$languagesExportedCount = 0;

			$langStartTime = microtime( true );
			foreach ( $languageExportActions as $lang => $action ) {
				// Do not export languages that are blacklisted (or not whitelisted).
				// Also check that whitelist is not null, which means that all
				// languages are allowed for translation and export.
				if ( is_array( $whitelist ) && !isset( $whitelist[$lang] ) ) {
					continue;
				}

				// Skip languages not present in recent changes
				if ( is_array( $changeFilter ) && !isset( $changeFilter[$groupId][$lang] ) ) {
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
					$collection->filter( 'ignored' );
				}

				if ( $noFuzzy ) {
					$collection->filter( 'fuzzy' );
				}

				$languagesExportedCount++;

				$endTime = microtime( true );
				$langExportTimes['collection'] += ( $endTime - $startTime );

				$startTime = microtime( true );
				$ffs->write( $collection );
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

		foreach ( $groups as $groupId => $group ) {
			if ( $group->isMeta() ) {
				$this->output( "Skipping meta message group $groupId.\n" );
				unset( $groups[$groupId] );
				continue;
			}

			if ( !$forOffline && !$group instanceof FileBasedMessageGroup ) {
				$this->output( "EE2: Unexportable message group $groupId.\n" );
				unset( $groups[$groupId] );
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

	/**
	 * @param int $hours
	 * @param int[] $namespaces
	 * @return array[]
	 */
	private function getRecentlyChangedItems( int $hours, array $namespaces ): array {
		$bots = true;
		$changeFilter = [];
		$rows = TranslateUtils::translationChanges( $hours, $bots, $namespaces );
		foreach ( $rows as $row ) {
			$title = Title::makeTitle( $row->rc_namespace, $row->rc_title );
			$handle = new MessageHandle( $title );
			$code = $handle->getCode();
			if ( !$code ) {
				continue;
			}
			$groupIds = $handle->getGroupIds();
			foreach ( $groupIds as $groupId ) {
				$changeFilter[$groupId][$code] = true;
			}
		}

		return $changeFilter;
	}

	/**
	 * @param MessageGroup[] $groups
	 * @return int[]
	 */
	private function getNamespacesForGroups( array $groups ): array {
		$namespaces = [];
		foreach ( $groups as $group ) {
			$namespaces[$group->getNamespace()] = true;
		}

		return array_keys( $namespaces );
	}

	private function getLanguageExportActions(
		string $groupId,
		array $requestedLanguages,
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
			$percentage = $total === 0 ? 0 : $translated / $total * 100;

			if ( $percentage === 0 || $percentage < $removalThreshold ) {
				$languages[$code] = self::ACTION_DELETE;
			} elseif ( $percentage > $exportThreshold ) {
				$languages[$code] = self::ACTION_CREATE;
			} else {
				$languages[$code] = self::ACTION_UPDATE;
			}
		}

		return $languages;
	}
}
