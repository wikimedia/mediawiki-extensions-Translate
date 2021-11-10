<?php

/**
 * Finds external changes for file based message groups.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013.12
 */

use MediaWiki\Extension\Translate\MessageSync\MessageSourceChange;
use MediaWiki\Extension\Translate\Utilities\StringComparators\StringComparator;

class ExternalMessageSourceStateComparator {
	/** Process all languages supported by the message group */
	public const ALL_LANGUAGES = 'all languages';

	/** @var StringComparator */
	protected $stringComparator;

	/** @param StringComparator $stringComparator */
	public function __construct( StringComparator $stringComparator ) {
		$this->stringComparator = $stringComparator;
	}

	/**
	 * Finds modifications in external sources compared to wiki state.
	 *
	 * The MessageSourceChange object returned stores the following about each modification,
	 * - First level of classification is the language code
	 * - Second level of classification is the type of modification,
	 *   - addition (new message in the file)
	 *   - deletion (message in wiki not present in the file)
	 *   - change (difference in content)
	 *   - rename (message key is modified)
	 * - Third level is a list of modifications
	 * - For each modification, the following is saved,
	 *   - key (the message key)
	 *   - content (the message content in external source, null for deletions)
	 *   - matched_to (present in case of renames, key of the matched message)
	 *   - similarity (present in case of renames, similarity % with the matched message)
	 *   - previous_state (present in case of renames, state of the message before rename)
	 *
	 * @param FileBasedMessageGroup $group
	 * @param array|string $languages
	 * @throws InvalidArgumentException
	 * @return MessageSourceChange
	 */
	public function processGroup( FileBasedMessageGroup $group, $languages ) {
		$changes = new MessageSourceChange();
		$processAll = false;

		if ( $languages === self::ALL_LANGUAGES ) {
			$processAll = true;
			$languages = $group->getTranslatableLanguages();

			// This means all languages
			if ( $languages === null ) {
				$languages = TranslateUtils::getLanguageNames( 'en' );
			}

			$languages = array_keys( $languages );
		} elseif ( !is_array( $languages ) ) {
			throw new InvalidArgumentException( 'Invalid input given for $languages' );
		}

		// Process the source language before others. Source language might not
		// be included in $group->getTranslatableLanguages(). The expected
		// behavior is that source language is always processed when given
		// self::ALL_LANGUAGES.
		$sourceLanguage = $group->getSourceLanguage();
		$index = array_search( $sourceLanguage, $languages );
		if ( $processAll || $index !== false ) {
			unset( $languages[$index] );
			$this->processLanguage( $group, $sourceLanguage, $changes );
		}

		foreach ( $languages as $language ) {
			$this->processLanguage( $group, $language, $changes );
		}

		return $changes;
	}

	protected function processLanguage(
		FileBasedMessageGroup $group, $language, MessageSourceChange $changes
	) {
		$cache = $group->getMessageGroupCache( $language );
		$reason = 0;
		if ( !$cache->isValid( $reason ) ) {
			$this->addMessageUpdateChanges( $group, $language, $changes, $reason, $cache );

			if ( $changes->getModificationsForLanguage( $language ) === [] ) {
				/* Update the cache immediately if file and wiki state match.
				 * Otherwise the cache will get outdated compared to file state
				 * and will give false positive conflicts later. */
				$cache->create();
			}
		}
	}

	/**
	 * This is the detective novel. We have three sources of information:
	 * - current message state in the file
	 * - current message state in the wiki
	 * - cached message state since cache was last build
	 *   (usually after export from wiki)
	 *
	 * Now we must try to guess what in earth has driven the file state and
	 * wiki state out of sync. Then we must compile list of events that would
	 * bring those to sync. Types of events are addition, deletion, (content)
	 * change and key renames. After that the list of events are stored for
	 * later processing of a translation administrator, who can decide what
	 * actions to take on those events to bring the state more or less in sync.
	 *
	 * @param FileBasedMessageGroup $group
	 * @param string $language
	 * @param MessageSourceChange $changes
	 * @param int $reason
	 * @param MessageGroupCache $cache
	 * @throws RuntimeException
	 */
	protected function addMessageUpdateChanges(
		FileBasedMessageGroup $group, $language, MessageSourceChange $changes, $reason, $cache
	) {
		// initCollection returns empty list before first import
		$wiki = $group->initCollection( $language );
		$wiki->filter( 'hastranslation', false );
		$wiki->loadTranslations();
		$wikiKeys = $wiki->getMessageKeys();

		$sourceLanguage = $group->getSourceLanguage();
		// By-pass cached message definitions
		$ffs = $group->getFFS();
		if ( $language === $sourceLanguage && !$ffs->exists( $language ) ) {
			$path = $group->getSourceFilePath( $language );
			throw new RuntimeException( "Source message file for {$group->getId()} does not exist: $path" );
		}

		$file = $ffs->read( $language );

		// Does not exist
		if ( $file === false ) {
			return;
		}

		// Something went wrong
		if ( !isset( $file['MESSAGES'] ) ) {
			$id = $group->getId();
			$ffsClass = get_class( $ffs );

			error_log( "$id has an FFS ($ffsClass) - it didn't return cake for $language" );

			return;
		}

		$fileKeys = array_keys( $file['MESSAGES'] );

		$common = array_intersect( $fileKeys, $wikiKeys );

		$supportsFuzzy = $ffs->supportsFuzzy();
		$changesToRemove = [];

		foreach ( $common as $key ) {
			$sourceContent = $file['MESSAGES'][$key];
			/** @var TMessage $wikiMessage */
			$wikiMessage = $wiki[$key];
			$wikiContent = $wikiMessage->translation();

			// @todo: Fuzzy checking can also be moved to $ffs->isContentEqual();
			// If FFS doesn't support it, ignore fuzziness as difference
			$wikiContent = str_replace( TRANSLATE_FUZZY, '', $wikiContent );

			// But if it does, ensure we have exactly one fuzzy marker prefixed
			if ( $supportsFuzzy === 'yes' && $wikiMessage->hasTag( 'fuzzy' ) ) {
				$wikiContent = TRANSLATE_FUZZY . $wikiContent;
			}

			if ( $ffs->isContentEqual( $sourceContent, $wikiContent ) ) {
				// File and wiki stage agree, nothing to do
				continue;
			}

			// Check against interim cache to see whether we have changes
			// in the wiki, in the file or both.

			if ( $reason !== MessageGroupCache::NO_CACHE ) {
				$cacheContent = $cache->get( $key );

				/* We want to ignore the common situation that the string
				 * in the wiki has been changed since the last export.
				 * Hence we check that source === cache && cache !== wiki
				 * and if so we skip this string. */
				if (
					!$ffs->isContentEqual( $wikiContent, $cacheContent ) &&
					$ffs->isContentEqual( $sourceContent, $cacheContent )
				) {
					continue;
				}
			}

			if ( $language !== $sourceLanguage ) {
				// Assuming that this is the old key, lets check if it has a corresponding
				// rename in the source language. The key of the matching message will be
				// the new renamed key.
				$renameMsg = $changes->getMatchedMessage( $sourceLanguage, $key );
				if ( $renameMsg !== null ) {
					// Rename present in source language but this message has a content change
					// with the OLD key in a non-source language. We will not process this
					// here but add it as a rename instead. This way, the key will be renamed
					// and then the content updated.
					$this->addNonSourceRenames(
						$changes, $key, $renameMsg['key'], $sourceContent, $wikiContent, $language
					);
					$changesToRemove[] = $key;
					continue;
				}
			}
			$changes->addChange( $language, $key, $sourceContent );
		}

		$changes->removeChanges( $language, $changesToRemove );

		$added = array_diff( $fileKeys, $wikiKeys );
		foreach ( $added as $key ) {
			$sourceContent = $file['MESSAGES'][$key];
			$changes->addAddition( $language, $key, $sourceContent );
		}

		/* Should the cache not exist, don't consider the messages
		 * missing from the file as deleted - they probably aren't
		 * yet exported. For example new language translations are
		 * exported the first time. */
		if ( $reason !== MessageGroupCache::NO_CACHE ) {
			$deleted = array_diff( $wikiKeys, $fileKeys );
			foreach ( $deleted as $key ) {
				if ( $cache->get( $key ) === false ) {
					/* This message has never existed in the cache, so it
					 * must be a newly made in the wiki. */
					continue;
				}
				$changes->addDeletion( $language, $key, $wiki[$key]->translation() );
			}
		}

		if ( $language === $sourceLanguage ) {
			$this->findAndMarkSourceRenames( $changes, $language );
		} else {
			// Non source language
			$this->checkNonSourceAdditionsForRename(
				$changes, $sourceLanguage, $language, $wiki, $wikiKeys
			);
		}
	}

	/**
	 * For non source languages, we look at additions and see if they have been
	 * added as renames in the source language.
	 * @param MessageSourceChange $changes
	 * @param string $sourceLanguage
	 * @param string $targetLanguage
	 * @param MessageCollection $wiki
	 * @param string[] $wikiKeys
	 */
	private function checkNonSourceAdditionsForRename(
		MessageSourceChange $changes, $sourceLanguage, $targetLanguage, MessageCollection $wiki, $wikiKeys
	) {
		$additions = $changes->getAdditions( $targetLanguage );
		if ( $additions === [] ) {
			return;
		}

		$additionsToRemove = [];
		$deletionsToRemove = [];
		foreach ( $additions as $addedMsg ) {
			$addedMsgKey = $addedMsg['key'];

			// Check if this key is renamed in source.
			$renamedSourceMsg = $changes->findMessage(
				$sourceLanguage, $addedMsgKey, [ MessageSourceChange::RENAME ]
			);

			if ( $renamedSourceMsg === null ) {
				continue;
			}

			// Since this key is new, and is present in the renames for the source language,
			// we will add it as a rename.
			$deletedSource = $changes->getMatchedMessage( $sourceLanguage, $renamedSourceMsg['key'] );
			$deletedMsgKey = $deletedSource['key'];
			$deletedMsg = $changes->findMessage(
				$targetLanguage, $deletedMsgKey, [ MessageSourceChange::DELETION ]
			);

			// Sometimes when the cache does not have the translations, the deleted message
			// is not added in the translations. It is also possible that for this non-source
			// language the key has not been removed.
			if ( $deletedMsg === null ) {
				$content = '';
				if ( array_search( $deletedMsgKey, $wikiKeys ) !== false ) {
					$content = $wiki[ $deletedMsgKey ]->translation();
				}
				$deletedMsg = [
					'key' => $deletedMsgKey,
					'content' => $content
				];
			}

			$similarityPercent = $this->stringComparator->getSimilarity(
				$addedMsg['content'], $deletedMsg['content']
			);

			$changes->addRename( $targetLanguage, [
				'key' => $addedMsgKey,
				'content' => $addedMsg['content']
			], [
				'key' => $deletedMsgKey,
				'content' => $deletedMsg['content']
			], $similarityPercent );

			$deletionsToRemove[] = $deletedMsgKey;
			$additionsToRemove[] = $addedMsgKey;
		}

		$changes->removeAdditions( $targetLanguage, $additionsToRemove );
		$changes->removeDeletions( $targetLanguage, $deletionsToRemove );
	}

	/**
	 * Check for renames and add them to the changes. To identify renames we need to
	 * compare the contents of the added messages with the deleted ones and identify
	 * messages that match.
	 * @param MessageSourcechange $changes
	 * @param string $sourceLanguage
	 */
	private function findAndMarkSourceRenames( MessageSourceChange $changes, $sourceLanguage ) {
		// Now check for renames. To identify renames we need to compare
		// the contents of the added messages with the deleted ones and
		// identify messages that match.
		$deletions = $changes->getDeletions( $sourceLanguage );
		$additions = $changes->getAdditions( $sourceLanguage );
		if ( $deletions === [] || $additions === [] ) {
			return;
		}

		// This array contains a dictionary with matching renames in the following structure -
		// [ A1|D1 => 1.0,  A1|D2 => 0.95, A2|D1 => 0.95 ]
		$potentialRenames = [];
		foreach ( $additions as $addedMsg ) {
			$addedMsgKey = $addedMsg['key'];

			foreach ( $deletions as $deletedMsg ) {
				$similarityPercent = $this->stringComparator->getSimilarity(
					$addedMsg['content'], $deletedMsg['content']
				);

				if ( $changes->areStringsSimilar( $similarityPercent ) ) {
					$potentialRenames[ $addedMsgKey . '|' . $deletedMsg['key'] ] = $similarityPercent;
				}
			}
		}

		$this->matchRenames( $changes, $potentialRenames, $sourceLanguage );
	}

	/**
	 * Adds non source language renames to the list of changes
	 * @param MessageSourceChange $changes
	 * @param string $key
	 * @param string $renameKey
	 * @param string $sourceContent
	 * @param string $wikiContent
	 * @param string $language
	 */
	private function addNonSourceRenames(
		MessageSourceChange $changes, $key, $renameKey, $sourceContent, $wikiContent, $language
	) {
		$addedMsg = [
			'key' => $renameKey,
			'content' => $sourceContent
		];

		$removedMsg = [
			'key' => $key,
			'content' => $wikiContent
		];

		$similarityPercent = $this->stringComparator->getSimilarity(
			$sourceContent, $wikiContent
		);
		$changes->addRename( $language, $addedMsg, $removedMsg, $similarityPercent );
	}

	/**
	 * Identifies which added message to be associated with the deleted message based on
	 * similarity percentage.
	 *
	 * We sort the $trackRename array on the similarity percentage and then start adding the
	 * messages as renames.
	 * @param MessageSourceChange $changes
	 * @param array $trackRename
	 * @param string $language
	 */
	private function matchRenames( MessageSourceChange $changes, array $trackRename, $language ) {
		arsort( $trackRename, SORT_NUMERIC );

		$alreadyRenamed = $additionsToRemove = $deletionsToRemove = [];
		foreach ( $trackRename as $key => $similarityPercent ) {
			list( $addKey, $deleteKey ) = explode( '|', $key, 2 );
			if ( isset( $alreadyRenamed[ $addKey ] ) || isset( $alreadyRenamed[ $deleteKey ] ) ) {
				// Already mapped with another name.
				continue;
			}

			// Using key should be faster than saving values and searching for them in the array.
			$alreadyRenamed[ $addKey ] = 1;
			$alreadyRenamed[ $deleteKey ] = 1;

			$addMsg = $changes->findMessage( $language, $addKey, [ MessageSourceChange::ADDITION ] );
			$deleteMsg = $changes->findMessage( $language, $deleteKey, [ MessageSourceChange::DELETION ] );

			$changes->addRename( $language, $addMsg, $deleteMsg, $similarityPercent );

			// @phan-suppress-next-line PhanTypeArraySuspiciousNullable
			$additionsToRemove[] = $addMsg['key'];
			// @phan-suppress-next-line PhanTypeArraySuspiciousNullable
			$deletionsToRemove[] = $deleteMsg['key'];
		}

		$changes->removeAdditions( $language, $additionsToRemove );
		$changes->removeDeletions( $language, $deletionsToRemove );
	}
}
