<?php

/**
 * Finds external changes for file based message groups.
 *
 * @author Niklas Laxström
 * @license GPL-2.0+
 * @since 2013.12
 */
class ExternalMessageSourceStateComparator {
	/** Process all languages supported by the message group */
	const ALL_LANGUAGES = 'all languages';

	protected $changes = array();

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
	 *
	 * @param FileBasedMessageGroup $group
	 * @param array|string $languages
	 * @throws MWException
	 * @return array array[language code][change type] = change.
	 */
	public function processGroup( FileBasedMessageGroup $group, $languages ) {
		$this->changes = array();

		if ( $languages === self::ALL_LANGUAGES ) {
			$languages = $group->getTranslatableLanguages();

			// This means all languages
			if ( $languages === null ) {
				$languages = TranslateUtils::getLanguageNames( 'en' );
			}

			$languages = array_keys( $languages );
		} elseif ( !is_array( $languages ) ) {
			throw new MWException( 'Invalid input given for $languages' );
		}

		// Process the source language before others
		$sourceLanguage = $group->getSourceLanguage();
		$index = array_search( $sourceLanguage, $languages );
		if ( $index !== false ) {
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

			if ( !isset( $this->changes[$code] ) ) {
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
	 * change and possible rename in the future. After that the list of events
	 * are stored for later processing of a translation administrator, who can
	 * decide what actions to take on those events to bring the state more or
	 * less in sync.
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
		wfSuppressWarnings();
		$wiki = $group->initCollection( $code );
		wfRestoreWarnings();
		$wiki->filter( 'hastranslation', false );
		$wiki->loadTranslations();
		$wikiKeys = $wiki->getMessageKeys();

		// By-pass cached message definitions
		/** @var FFS $ffs */
		$ffs = $group->getFFS();
		if ( $code === $group->getSourceLanguage() && !$ffs->exists( $code ) ) {
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

		foreach ( $common as $key ) {
			$sourceContent = $file['MESSAGES'][$key];
			/** @var TMessage $wikiMessage */
			$wikiMessage = $wiki[$key];
			$wikiContent = $wikiMessage->translation();

			// If FFS doesn't support it, ignore fuzziness as difference
			$wikiContent = str_replace( TRANSLATE_FUZZY, '', $wikiContent );

			// But if it does, ensure we have exactly one fuzzy marker prefixed
			if ( $supportsFuzzy === 'yes' && $wikiMessage->hasTag( 'fuzzy' ) ) {
				$wikiContent = TRANSLATE_FUZZY . $wikiContent;
			}

			if ( self::compareContent( $sourceContent, $wikiContent ) ) {
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
					!self::compareContent( $wikiContent, $cacheContent ) &&
					self::compareContent( $sourceContent, $cacheContent )
				) {
					continue;
				}
			}

			$this->addChange( 'change', $code, $key, $sourceContent );
		}

		$added = array_diff( $fileKeys, $wikiKeys );
		foreach ( $added as $key ) {
			$sourceContent = $file['MESSAGES'][$key];
			if ( trim( $sourceContent ) === '' ) {
				continue;
			}
			$this->addChange( 'addition', $code, $key, $sourceContent );
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
				$this->addChange( 'deletion', $code, $key, null );
			}
		}

	}

	protected function addChange( $type, $language, $key, $content ) {
		$this->changes[$language][$type][] = array(
			'key' => $key,
			'content' => $content,
		);
	}

	/**
	 * Compares two strings.
	 * @todo Ignore changes in different way inlined plurals.
	 * @todo Handle fuzzy state changes if FFS supports it.
	 *
	 * @param string $a
	 * @param string $b
	 * @return bool Whether two strings are equal
	 */
	protected static function compareContent( $a, $b ) {
		return $a === $b;
	}
}
