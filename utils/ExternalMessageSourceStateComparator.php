<?php

/**
 * Finds external changes for file based message groups.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013.12
 */
class ExternalMessageSourceStateComparator {
	/** Process all languages supported by the message group */
	const ALL_LANGUAGES = 'all languages';

	/**
	 * @var int
	 */
	const MIN_THRESHOLD = 100;

	/**
	 * @var MessageSourceChange
	 */
	protected $changes;

	/**
	 * @var StringComparator
	 */
	protected $stringComparator;

	/**
	 * Set the string comparator to be used for the comparison
	 * @param StringComparator $stringComparator
	 * @return void
	 */
	public function setStringComparator( StringComparator $stringComparator ) {
		$this->stringComparator = $stringComparator;
	}

	/**
	 * Finds changes in external sources compared to wiki state.
	 *
	 * The returned array is as following:
	 * - First level is indexed by language code
	 * - Second level is indexed by change type:
	 * - - addition (new message in the file)
	 * - - deletion (message in wiki not present in the file)
	 * - - change (difference in content)
	 * - Third level is a list of changes
	 * - Fourth level is change properties
	 * - - key (the message key)
	 * - - content (the message content in external source, null for deletions)
	 * - - matched_to (present in case of renames, key of the matched message)
	 * - - similarity (present in case of renames, similarity % with the matched message)
	 * - - previous_state ( present in case of renames, state of the message before rename )
	 *
	 * @param FileBasedMessageGroup $group
	 * @param array|string $languages
	 * @throws InvalidArgumentException
	 * @return array array[language code][change type] = change.
	 */
	public function processGroup( FileBasedMessageGroup $group, $languages ) {
		$this->changes = new MessageSourceChange();
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
			$this->processLanguage( $group, $sourceLanguage );
		}

		foreach ( $languages as $code ) {
			$this->processLanguage( $group, $code );
		}

		return $this->changes;
	}

	protected function processLanguage( FileBasedMessageGroup $group, $code ) {
		$cache = new MessageGroupCache( $group, $code );
		$reason = 0;
		if ( !$cache->isValid( $reason ) ) {
			$this->addMessageUpdateChanges( $group, $code, $reason, $cache );

			if ( $this->changes->getModifications( $code ) === [] ) {
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
	 * @param string $code Language code.
	 * @param int $reason
	 * @param MessageGroupCache $cache
	 * @throws MWException
	 */
	protected function addMessageUpdateChanges( FileBasedMessageGroup $group, $code,
		$reason, $cache
	) {
		/* This throws a warning if message definitions are not yet
		 * cached and will read the file for definitions. */
		Wikimedia\suppressWarnings();
		$wiki = $group->initCollection( $code );
		Wikimedia\restoreWarnings();
		$wiki->filter( 'hastranslation', false );
		$wiki->loadTranslations();
		$wikiKeys = $wiki->getMessageKeys();
		$sourceLang = $group->getSourceLanguage();

		// By-pass cached message definitions
		/** @var FFS $ffs */
		$ffs = $group->getFFS();
		if ( $code === $sourceLang && !$ffs->exists( $code ) ) {
			$path = $group->getSourceFilePath( $code );
			throw new MWException( "Source message file for {$group->getId()} does not exist: $path" );
		}

		$file = $ffs->read( $code );

		// Does not exist
		if ( $file === false ) {
			return;
		}

		// Something went wrong
		if ( !isset( $file['MESSAGES'] ) ) {
			$id = $group->getId();
			$ffsClass = get_class( $ffs );

			error_log( "$id has an FFS ($ffsClass) - it didn't return cake for $code" );

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

			if ( $code !== $sourceLang ) {
				// Assume that this is the old key, we're checking if it has a corresponding
				// renamed message, which is the new key.
				$renameMsg = $this->changes->getMatchedMsg( $sourceLang, $key );
				if ( $renameMsg !== null ) {
					// Rename present in source language but this message has a content change
					// with the OLD key. We will not process this here but add it as a
					// rename instead. This way, the key will be renamed and then the content
					// updated.
					$addedMsg = [
						'key' => $renameMsg['key'],
						'content' => $sourceContent
					];

					$removedMsg = [
						'key' => $key,
						'content' => $wikiContent
					];

					$similarityPercent = $this->stringComparator->getSimilarity( $sourceContent,
						$wikiContent );
					$this->changes->addRename( $code, $addedMsg, $removedMsg, $similarityPercent );
					$changesToRemove[] = $removedMsg['key'];
					continue;
				}
			}
			$this->changes->addChange( $code, $key, $sourceContent );
		}

		$this->changes->removeChanges( $code, $changesToRemove );

		$added = array_diff( $fileKeys, $wikiKeys );
		foreach ( $added as $key ) {
			$sourceContent = $file['MESSAGES'][$key];
			if ( trim( $sourceContent ) === '' ) {
				continue;
			}

			$this->changes->addAddition( $code, $key, $sourceContent );
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
				$this->changes->addDeletion( $code, $key, $wiki[$key]->translation() );
			}
		}

		if ( $code !== $sourceLang ) {
			// For non source languages, we look at additions and see if
			// they have been added as renames in the source language.
			$additions = $this->changes->getAdditions( $code );
			if ( $additions === [] ) {
				return;
			}

			$additionsToRemove = [];
			$deletionsToRemove = [];
			foreach ( $additions as $addedMsg ) {
				$addedMsgKey = $addedMsg['key'];

				$addedSource = $this->changes->findMessage( $sourceLang, $addedMsgKey,
					[ MessageSourceChange::M_RENAME ] );
				if ( $addedSource === null ) {
					continue;
				}

				// Since this key is new, and is present in the renames for the source language,
				// we will add it as a rename.
				$deletedSource = $this->changes->getMatchedMsg( $sourceLang, $addedSource['key'] );
				$deletedMsgKey = $deletedSource['key'];
				$deletedMsg = $this->changes->findMessage( $code, $deletedMsgKey,
					[ MessageSourceChange::M_DELETION ] );

				// Sometimes when the cache does not have the translations, the deleted message
				// is not added in the translations.
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

				$similarityPercent = 0;
				if ( $this->stringComparator !== null ) {
					$similarityPercent = $this->stringComparator->getSimilarity(
						$addedMsg['content'], $deletedMsg['content']
					);
				}

				$this->changes->addRename( $code, [
					'key' => $addedMsgKey,
					'content' => $addedMsg['content']
				], [
					'key' => $deletedMsgKey,
					'content' => $deletedMsg['content']
				], $similarityPercent );

				$deletionsToRemove[] = $deletedMsgKey;
				$additionsToRemove[] = $addedMsgKey;
			}

			$this->changes->removeAdditions( $code, $additionsToRemove );
			$this->changes->removeDeletions( $code, $deletionsToRemove );
			return;
		}

		if ( $this->stringComparator === null ) {
			return;
		}

		// Now check for renames. To identify renames we need to compare
		// the contents of the added messages with the deleted ones and
		// identify messages that match.
		$deletions = $this->changes->getDeletions( $code );
		$additions = $this->changes->getAdditions( $code );
		if ( $deletions === [] || $additions === [] ) {
			return;
		}

		$additionsToRemove = [];
		$deletionsToRemove = [];
		foreach ( $additions as $addedMsg ) {
			foreach ( $deletions as $deletedMsg ) {
				$similarityPercent = $this->stringComparator->getSimilarity(
					$addedMsg['content'],
					$deletedMsg['content']
				);

				if ( $similarityPercent >= self::MIN_THRESHOLD ) {
					$this->changes->addRename( $code, $addedMsg, $deletedMsg, $similarityPercent );

					// keep track of messages to be removed from addition and deletion arrays
					$additionsToRemove[] = $addedMsg['key'];
					$deletionsToRemove[] = $deletedMsg['key'];
				}
			}
		}

		$this->changes->removeAdditions( $code, $additionsToRemove );
		$this->changes->removeDeletions( $code, $deletionsToRemove );
	}
}
