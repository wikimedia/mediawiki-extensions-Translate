<?php

/**
 * Finds external changes for file based message groups.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2016.02
 */

use MediaWiki\Extension\Translate\MessageSync\MessageSourceChange;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Synchronization\MessageUpdateParameter;
use MediaWiki\MediaWikiServices;

class ExternalMessageSourceStateImporter {

	/**
	 * @param MessageSourceChange[] $changeData
	 * @return array
	 */
	public function importSafe( array $changeData ) {
		$processed = [];
		$skipped = [];
		$jobs = [];

		/** @var MessageSourceChange $changesForGroup */
		foreach ( $changeData as $groupId => $changesForGroup ) {
			/** @var FileBasedMessageGroup */
			$group = MessageGroups::getGroup( $groupId );
			if ( !$group ) {
				unset( $changeData[$groupId] );
				continue;
			}
			'@phan-var FileBasedMessageGroup $group';

			$processed[$groupId] = [];
			$languages = $changesForGroup->getLanguages();
			$groupJobs = [];

			$groupSafeLanguages = self::identifySafeLanguages( $group, $changesForGroup );

			foreach ( $languages as $language ) {
				if ( !$groupSafeLanguages[ $language ] ) {
					$skipped[$groupId] = true;
					continue;
				}

				$additions = $changesForGroup->getAdditions( $language );
				if ( $additions === [] ) {
					continue;
				}

				[ $groupLanguageJobs, $groupProcessed ] = $this->createMessageUpdateJobs(
					$group, $additions, $language
				);

				$groupJobs = array_merge( $groupJobs, $groupLanguageJobs );
				$processed[$groupId][$language] = $groupProcessed;

				$changesForGroup->removeChangesForLanguage( $language );
				$group->getMessageGroupCache( $language )->create();
			}

			if ( $groupJobs !== [] ) {
				$this->updateGroupSyncInfo( $groupId, $groupJobs );
				$jobs = array_merge( $jobs, $groupJobs );
			}
		}

		// Remove groups where everything was imported
		$changeData = array_filter( $changeData, static function ( MessageSourceChange $change ) {
			return $change->getAllModifications() !== [];
		} );

		// Remove groups with no imports
		$processed = array_filter( $processed );

		$name = 'unattended';
		$file = MessageChangeStorage::getCdbPath( $name );
		MessageChangeStorage::writeChanges( $changeData, $file );
		JobQueueGroup::singleton()->push( $jobs );

		return [
			'processed' => $processed,
			'skipped' => $skipped,
			'name' => $name,
		];
	}

	/**
	 * Creates MessagUpdateJobs additions for a language under a group
	 *
	 * @param MessageGroup $group
	 * @param string[][] $additions
	 * @param string $language
	 * @return array
	 */
	private function createMessageUpdateJobs(
		MessageGroup $group, array $additions, string $language
	) {
		$groupId = $group->getId();
		$jobs = [];
		$processed = 0;
		foreach ( $additions as $addition ) {
			$namespace = $group->getNamespace();
			$name = "{$addition['key']}/$language";

			$title = Title::makeTitleSafe( $namespace, $name );
			if ( !$title ) {
				wfWarn( "Invalid title for group $groupId key {$addition['key']}" );
				continue;
			}

			$jobs[] = MessageUpdateJob::newJob( $title, $addition['content'] );
			$processed++;
		}

		return [ $jobs, $processed ];
	}

	/**
	 * @param string $groupId
	 * @param MessageUpdateJob[] $groupJobs
	 */
	private function updateGroupSyncInfo( string $groupId, array $groupJobs ): void {
		$config = MediaWikiServices::getInstance()->getMainConfig();

		if ( !$config->get( 'TranslateGroupSynchronizationCache' ) ) {
			return;
		}

		$messageParams = [];
		$groupMessageKeys = [];
		foreach ( $groupJobs as $job ) {
			$messageParams[] = MessageUpdateParameter::createFromJob( $job );
			// Ensure there are no duplicates as the same key may be present in
			// multiple languages
			$groupMessageKeys[( new MessageHandle( $job->getTitle() ) )->getKey()] = true;
		}

		$group = MessageGroups::getGroup( $groupId );
		if ( $group === null ) {
			// How did we get here? This should never happen.
			throw new RuntimeException( "Did not find group $groupId" );
		}

		MessageIndex::singleton()->storeInterim( $group, array_keys( $groupMessageKeys ) );

		$groupSyncCache = Services::getInstance()->getGroupSynchronizationCache();
		$groupSyncCache->addMessages( $groupId, ...$messageParams );
		$groupSyncCache->markGroupForSync( $groupId );
	}

	/**
	 * Identifies languages in a message group that are safe to import
	 * @param MessageGroup $group
	 * @param MessageSourceChange $changesForGroup
	 * @return bool[]
	 */
	private static function identifySafeLanguages(
		MessageGroup $group,
		MessageSourceChange $changesForGroup
	): array {
		$sourceLanguage = $group->getSourceLanguage();
		$safeLanguagesMap = [];
		$modifiedLanguages = $changesForGroup->getLanguages();

		// Set all languages to not safe to start with.
		$safeLanguagesMap[ $sourceLanguage ] = false;
		foreach ( $modifiedLanguages as $language ) {
			$safeLanguagesMap[ $language ] = false;
		}

		if ( !$changesForGroup->hasOnly( $sourceLanguage, MessageSourceChange::ADDITION ) ) {
			return $safeLanguagesMap;
		}

		$sourceLanguageKeyCache = [];
		foreach ( $changesForGroup->getAdditions( $sourceLanguage ) as $change ) {
			if ( $change['content'] === '' ) {
				return $safeLanguagesMap;
			}

			$sourceLanguageKeyCache[ $change['key'] ] = true;
		}

		$safeLanguagesMap[ $sourceLanguage ] = true;

		$groupNamespace = $group->getNamespace();

		// Remove source language from the modifiedLanguage list if present since it's already processed.
		// The $sourceLanguageKeyCache will only have values if sourceLanguage has safe changes.
		if ( $sourceLanguageKeyCache ) {
			array_splice( $modifiedLanguages, array_search( $sourceLanguage, $modifiedLanguages ), 1 );
		}

		foreach ( $modifiedLanguages as $language ) {
			if ( !$changesForGroup->hasOnly( $language, MessageSourceChange::ADDITION ) ) {
				continue;
			}

			foreach ( $changesForGroup->getAdditions( $language ) as $change ) {
				if ( $change['content'] === '' ) {
					continue 2;
				}

				$msgKey = $change['key'];

				if ( !isset( $sourceLanguageKeyCache[ $msgKey ] ) ) {
					// This is either a new external translation which is not added in the same sync
					// as the source language key, or this translation does not have a correspoding
					// definition. We will check the message index to determine which of the two.
					$sourceHandle = new MessageHandle( Title::makeTitle( $groupNamespace, $msgKey ) );
					$sourceLanguageKeyCache[ $msgKey ] = $sourceHandle->isValid();
				}

				if ( !$sourceLanguageKeyCache[ $msgKey ] ) {
					continue 2;
				}
			}

			$safeLanguagesMap[ $language ] = true;
		}

		return $safeLanguagesMap;
	}
}
