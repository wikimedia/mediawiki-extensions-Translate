<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

use BadMethodCallException;
use MediaWiki\Extension\Translate\LogNames;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Language\Language;
use MediaWiki\Linker\LinkTarget;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MessageGroup;
use RuntimeException;

/**
 * Class for pointing to messages, like Title class is for titles.
 * Also enhances Title with stuff related to message groups
 * @author Niklas Laxström
 * @copyright Copyright © 2011-2013 Niklas Laxström
 * @license GPL-2.0-or-later
 */
class MessageHandle {
	private LinkTarget $title;
	private ?string $key = null;
	private ?string $languageCode = null;
	/** @var string[]|null */
	private ?array $groupIds = null;
	private MessageIndex $messageIndex;

	public function __construct( LinkTarget $title ) {
		$this->title = $title;
		$this->messageIndex = Services::getInstance()->getMessageIndex();
	}

	/** Check if this handle is in a message namespace. */
	public function isMessageNamespace(): bool {
		global $wgTranslateMessageNamespaces;
		$namespace = $this->title->getNamespace();

		return in_array( $namespace, $wgTranslateMessageNamespaces );
	}

	/**
	 * Recommended to use getCode and getKey instead.
	 * @return string[] Array of the message key and the language code
	 */
	public function figureMessage(): array {
		if ( $this->key === null ) {
			// Check if this is a valid message first
			$this->key = $this->title->getDBkey();
			$known = $this->messageIndex->getGroupIds( $this ) !== [];

			$pos = strrpos( $this->key, '/' );
			if ( $known || $pos === false ) {
				$this->languageCode = '';
			} else {
				// For keys like Foo/, substr returns false instead of ''
				$this->languageCode = (string)( substr( $this->key, $pos + 1 ) );
				$this->key = substr( $this->key, 0, $pos );
			}
		}

		return [ $this->key, $this->languageCode ];
	}

	/** Returns the identified or guessed message key. */
	public function getKey(): string {
		$this->figureMessage();

		return $this->key;
	}

	/**
	 * Returns the language code.
	 * For language codeless source messages will return empty string.
	 */
	public function getCode(): string {
		$this->figureMessage();

		return $this->languageCode;
	}

	/**
	 * Return the Language object for the assumed language of the content, which might
	 * be different from the subpage code (qqq, no subpage).
	 */
	public function getEffectiveLanguage(): Language {
		$code = $this->getCode();
		$mwServices = MediaWikiServices::getInstance();
		if ( !$mwServices->getLanguageNameUtils()->isKnownLanguageTag( $code ) ||
			$this->isDoc()
		) {
			return $mwServices->getContentLanguage();
		}

		return $mwServices->getLanguageFactory()->getLanguage( $code );
	}

	/** Determine whether the current handle is for message documentation. */
	public function isDoc(): bool {
		global $wgTranslateDocumentationLanguageCode;

		return $this->getCode() === $wgTranslateDocumentationLanguageCode;
	}

	/**
	 * Determine whether the current handle is for page translation feature.
	 * This does not consider whether the handle corresponds to any message.
	 */
	public function isPageTranslation(): bool {
		return $this->title->inNamespace( NS_TRANSLATIONS );
	}

	/**
	 * Returns all message group ids this message belongs to.
	 * The primary message group id is always the first one.
	 * If the handle does not correspond to any message, the returned array
	 * is empty.
	 * @return string[]
	 */
	public function getGroupIds() {
		if ( $this->groupIds === null ) {
			$this->groupIds = $this->messageIndex->getGroupIds( $this );
		}

		return $this->groupIds;
	}

	/**
	 * Get the primary MessageGroup this message belongs to.
	 * You should check first that the handle is valid.
	 */
	public function getGroup(): ?MessageGroup {
		$ids = $this->getGroupIds();
		if ( !isset( $ids[0] ) ) {
			throw new BadMethodCallException( 'called before isValid' );
		}
		return MessageGroups::getGroup( $ids[0] );
	}

	/** Checks if the handle corresponds to a known message. */
	public function isValid(): bool {
		static $jobHasBeenScheduled = false;

		if ( !$this->isMessageNamespace() ) {
			return false;
		}

		$groups = $this->getGroupIds();
		if ( !$groups ) {
			return false;
		}

		// Do another check that the group actually exists
		$group = $this->getGroup();
		if ( !$group ) {
			$logger = LoggerFactory::getInstance( LogNames::MAIN );
			$logger->warning(
				'[MessageHandle] MessageIndex is out of date. Page {pagename} refers to ' .
				'unknown group {messagegroup}',
				[
					'pagename' => $this->getTitle()->getPrefixedText(),
					'messagegroup' => $groups[0],
					'exception' => new RuntimeException(),
					'hasJobBeenScheduled' => $jobHasBeenScheduled
				]
			);

			if ( !$jobHasBeenScheduled ) {
				// Schedule a job in the job queue (with deduplication)
				$job = RebuildMessageIndexJob::newJob( __METHOD__ );
				MediaWikiServices::getInstance()->getJobQueueGroup()->lazyPush( $job );
				$jobHasBeenScheduled = true;
			}

			return false;
		}

		return true;
	}

	/** Get the original title. */
	public function getTitle(): Title {
		return Title::newFromLinkTarget( $this->title );
	}

	/** Get the original title with the passed language code. */
	public function getTitleForLanguage( string $languageCode ): Title {
		return Title::makeTitle(
			$this->title->getNamespace(),
			$this->getKey() . "/$languageCode"
		);
	}

	/** Get the title for the page base. */
	public function getTitleForBase(): Title {
		return Title::makeTitle(
			$this->title->getNamespace(),
			$this->getKey()
		);
	}

	/**
	 * Check if a string contains the fuzzy string.
	 * @param string $text Arbitrary text
	 * @return bool If string contains fuzzy string.
	 */
	public static function hasFuzzyString( string $text ): bool {
		return str_contains( $text, TRANSLATE_FUZZY );
	}

	/** Check if a string has fuzzy string and if not, add it */
	public static function makeFuzzyString( string $text ): string {
		return self::hasFuzzyString( $text ) ? $text : TRANSLATE_FUZZY . $text;
	}

	/** Check if a title is marked as fuzzy. */
	public function isFuzzy(): bool {
		$dbr = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_REPLICA );

		$res = $dbr->newSelectQueryBuilder()
			->select( 'rt_type' )
			->from( 'page' )
			->join( 'revtag', null, [
				'page_id=rt_page',
				'page_latest=rt_revision',
				'rt_type' => RevTagStore::FUZZY_TAG,
			] )
			->where( [
				'page_namespace' => $this->title->getNamespace(),
				'page_title' => $this->title->getDBkey(),
			] )
			->caller( __METHOD__ )
			->fetchField();

		return $res !== false;
	}

	/**
	 * This returns the key that can be used for showMessage parameter for Special:Translate
	 * for regular message groups. It is not possible to automatically determine this key
	 * from the title alone.
	 */
	public function getInternalKey(): string {
		$mwServices = MediaWikiServices::getInstance();
		$nsInfo = $mwServices->getNamespaceInfo();
		$contentLanguage = $mwServices->getContentLanguage();

		$key = $this->getKey();
		$group = $this->getGroup();
		$groupKeys = $group->getKeys();

		if ( in_array( $key, $groupKeys, true ) ) {
			return $key;
		}

		$namespace = $this->title->getNamespace();
		if ( $nsInfo->isCapitalized( $namespace ) ) {
			$lowercaseKey = $contentLanguage->lcfirst( $key );
			if ( in_array( $lowercaseKey, $groupKeys, true ) ) {
				return $lowercaseKey;
			}
		}

		// Brute force all the keys to find the one. This one should always find a match
		// if there is one.
		foreach ( $groupKeys as $haystackKey ) {
			$normalizedHaystackKey = Title::makeTitleSafe( $namespace, $haystackKey )->getDBkey();
			if ( $normalizedHaystackKey === $key ) {
				return $haystackKey;
			}
		}

		return "BUG:$key";
	}

	/** Returns true if message is fuzzy, OR fails checks OR fails validations (error OR warning). */
	public function needsFuzzy( string $text ): bool {
		// Docs are exempt for checks
		if ( $this->isDoc() ) {
			return false;
		}

		// Check for explicit tag.
		if ( self::hasFuzzyString( $text ) ) {
			return true;
		}

		// Not all groups have validators
		$group = $this->getGroup();
		$validator = $group->getValidator();

		// no validator set
		if ( !$validator ) {
			return false;
		}

		$code = $this->getCode();
		$key = $this->getKey();
		$en = $group->getMessage( $key, $group->getSourceLanguage() );
		$message = new FatMessage( $key, $en );
		// Take the contents from edit field as a translation.
		$message->setTranslation( $text );
		if ( $message->rawDefinition() === null ) {
			// This should NOT happen, but add a check since it seems to be happening
			// See: https://phabricator.wikimedia.org/T255669
			LoggerFactory::getInstance( LogNames::MAIN )->warning(
				'Message definition is empty! Title: {title}, group: {group}, key: {key}',
				[
					'title' => $this->getTitle()->getPrefixedText(),
					'group' => $group->getId(),
					'key' => $key
				]
			);
			return false;
		}

		$validationResult = $validator->quickValidate( $message, $code );
		return $validationResult->hasIssues();
	}
}
