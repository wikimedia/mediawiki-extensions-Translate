<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use LogicException;
use MediaWiki\Content\TextContent;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundle;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Statistics\MessageGroupStats;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Linker\LinkTarget;
use MediaWiki\MediaWikiServices;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Page\PageReference;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;
use RuntimeException;
use Wikimedia\Rdbms\Database;
use Wikimedia\Rdbms\IDBAccessObject;
use WikiPageMessageGroup;

/**
 * Mixed bag of methods related to translatable pages.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup PageTranslation
 */
class TranslatablePage extends TranslatableBundle {
	/**
	 * List of keys in the metadata table that need to be handled for moves and deletions
	 * @phpcs-require-sorted-array
	 */
	public const METADATA_KEYS = [
		'maxid',
		'priorityforce',
		'prioritylangs',
		'priorityreason',
		'transclusion',
		'version'
	];
	/** @var string Name of the section which contains the translated page title. */
	public const DISPLAY_TITLE_UNIT_ID = 'Page display title';

	protected PageIdentity $title;
	protected RevTagStore $revTagStore;
	/** @var ?string Text contents of the page. */
	protected $text;
	/** @var ?int Revision of the page, if applicable. */
	protected $revision;
	/** @var string From which source this object was constructed: text, revision or title */
	protected $source;
	/** @var ?bool Whether the title should be translated */
	protected $pageDisplayTitle;
	/** @var ?string */
	private $targetLanguage;

	protected function __construct( PageIdentity $title ) {
		$this->title = $title;
		$this->revTagStore = Services::getInstance()->getRevTagStore();
	}

	/**
	 * Constructs a translatable page from given text.
	 * Some functions will fail unless you set revision
	 * parameter manually.
	 */
	public static function newFromText( Title $title, string $text ): self {
		$obj = new self( $title );
		$obj->text = $text;
		$obj->source = 'text';

		return $obj;
	}

	/**
	 * Constructs a translatable page from given revision.
	 * The revision must belong to the title given or unspecified
	 * behavior will happen.
	 */
	public static function newFromRevision( PageIdentity $title, int $revision ): self {
		$rev = MediaWikiServices::getInstance()
			->getRevisionLookup()
			->getRevisionByTitle( $title, $revision );
		if ( $rev === null ) {
			throw new RuntimeException( 'Revision is null' );
		}

		$obj = new self( $title );
		$obj->source = 'revision';
		$obj->revision = $revision;

		return $obj;
	}

	/**
	 * Constructs a translatable page from title.
	 * The text of last marked revision is loaded when needed.
	 */
	public static function newFromTitle( PageIdentity $title ): self {
		$obj = new self( $title );
		$obj->source = 'title';

		return $obj;
	}

	/** @inheritDoc */
	public function getTitle(): Title {
		return Title::newFromPageIdentity( $this->title );
	}

	public function getPageIdentity(): PageIdentity {
		return $this->title;
	}

	/** Returns the text for this translatable page. */
	public function getText(): string {
		if ( $this->text !== null ) {
			return $this->text;
		}

		if ( $this->source === 'title' ) {
			$revision = $this->getMarkedTag();
			if ( !is_int( $revision ) ) {
				throw new LogicException(
					"Trying to load a text for {$this->getPageIdentity()} which is not marked for translation"
				);
			}
			$this->revision = $revision;
		}

		$flags = Utilities::shouldReadFromPrimary()
			? IDBAccessObject::READ_LATEST
			: IDBAccessObject::READ_NORMAL;
		$rev = MediaWikiServices::getInstance()
			->getRevisionLookup()
			->getRevisionByTitle( $this->getPageIdentity(), $this->revision, $flags );
		$content = $rev->getContent( SlotRecord::MAIN );
		$text = ( $content instanceof TextContent ) ? $content->getText() : null;

		if ( !is_string( $text ) ) {
			throw new RuntimeException( "Failed to load text for {$this->getPageIdentity()}" );
		}

		$this->text = $text;

		return $this->text;
	}

	/**
	 * Revision is null if object was constructed using newFromText.
	 * @return null|int
	 */
	public function getRevision(): ?int {
		return $this->revision;
	}

	/**
	 * Returns the source language of this translatable page. In other words
	 * the language in which the page without language code is written.
	 * @since 2013-01-28
	 */
	public function getSourceLanguageCode(): string {
		return $this->getTitle()->getPageLanguage()->getCode();
	}

	/** @inheritDoc */
	public function getMessageGroupId(): string {
		return self::getMessageGroupIdFromTitle( $this->getPageIdentity() );
	}

	/** Constructs MessageGroup id for any title. */
	public static function getMessageGroupIdFromTitle( PageReference $page ): string {
		return 'page-' . MediaWikiServices::getInstance()->getTitleFormatter()->getPrefixedText( $page );
	}

	/**
	 * Returns MessageGroup used for translating this page. It may still be empty
	 * if the page has not been ever marked.
	 */
	public function getMessageGroup(): ?WikiPageMessageGroup {
		$groupId = $this->getMessageGroupId();
		$group = MessageGroups::getGroup( $groupId );
		if ( !$group || $group instanceof WikiPageMessageGroup ) {
			return $group;
		}

		throw new RuntimeException(
			"Expected $groupId to be of type WikiPageMessageGroup; got " .
			get_class( $group )
		);
	}

	/** Check whether title is marked for translation */
	public function hasPageDisplayTitle(): bool {
		// Cached value
		if ( $this->pageDisplayTitle !== null ) {
			return $this->pageDisplayTitle;
		}

		// Check if title section exists in list of sections
		$factory = Services::getInstance()->getTranslationUnitStoreFactory();
		$store = $factory->getReader( $this->getPageIdentity() );
		$this->pageDisplayTitle = in_array( self::DISPLAY_TITLE_UNIT_ID, $store->getNames() );

		return $this->pageDisplayTitle;
	}

	/** Get translated page title. */
	public function getPageDisplayTitle( string $languageCode ): ?string {
		// Return null if title not marked for translation
		if ( !$this->hasPageDisplayTitle() ) {
			return null;
		}

		// Display title from DB
		$section = str_replace( ' ', '_', self::DISPLAY_TITLE_UNIT_ID );
		$page = MediaWikiServices::getInstance()->getTitleFormatter()->getPrefixedDBkey( $this->getPageIdentity() );

		try {
			$group = $this->getMessageGroup();
		} catch ( RuntimeException $e ) {
			return null;
		}

		// Sanity check, seems to happen during moves
		if ( !$group ) {
			return null;
		}

		return $group->getMessage( "$page/$section", $languageCode, IDBAccessObject::READ_NORMAL );
	}

	public function getStrippedSourcePageText(): string {
		$parser = Services::getInstance()->getTranslatablePageParser();
		$text = $parser->cleanupTags( $this->getText() );
		$text = preg_replace( '~<languages\s*/>\n?~s', '', $text );

		return $text;
	}

	public static function getTranslationPageFromTitle( Title $title ): ?TranslationPage {
		$self = self::isTranslationPage( $title );
		return $self ? $self->getTranslationPage( $self->targetLanguage ) : null;
	}

	public function getTranslationPage( string $targetLanguage ): TranslationPage {
		$mwServices = MediaWikiServices::getInstance();
		$config = $mwServices->getMainConfig();
		$services = Services::getInstance();
		$parser = $services->getTranslatablePageParser();
		$parserOutput = $parser->parse( $this->getText() );
		$pageVersion = (int)$services->getMessageGroupMetadata()
			->get( $this->getMessageGroupId(), 'version' );
		$wrapUntranslated = $pageVersion >= 2;
		$languageFactory = $mwServices->getLanguageFactory();

		return new TranslationPage(
			$parserOutput,
			$this->getMessageGroup(),
			$languageFactory->getLanguage( $targetLanguage ),
			$languageFactory->getLanguage( $this->getSourceLanguageCode() ),
			$config->get( 'TranslateKeepOutdatedTranslations' ),
			$wrapUntranslated,
			$this->getTitle()
		);
	}

	/** Adds a tag which indicates that this page is suitable for translation. */
	public function addMarkedTag( int $revision, ?array $value = null ) {
		$this->revTagStore->replaceTag( $this->getPageIdentity(), RevTagStore::TP_MARK_TAG, $revision, $value );
		self::clearSourcePageCache();
	}

	/** Adds a tag which indicates that this page source is ready for marking for translation. */
	public function addReadyTag( int $revision ): void {
		$this->revTagStore->replaceTag( $this->getPageIdentity(), RevTagStore::TP_READY_TAG, $revision );
		if ( !self::isSourcePage( $this->getPageIdentity() ) ) {
			self::clearSourcePageCache();
		}
	}

	/** Returns the latest revision which has marked tag, if any. */
	public function getMarkedTag(): ?int {
		return $this->revTagStore->getLatestRevisionWithTag( $this->getPageIdentity(), RevTagStore::TP_MARK_TAG );
	}

	/** Returns the latest revision which has ready tag, if any. */
	public function getReadyTag(): ?int {
		return $this->revTagStore->getLatestRevisionWithTag( $this->getPageIdentity(), RevTagStore::TP_READY_TAG );
	}

	/**
	 * Produces a link to translation view of a translation page.
	 * @param string|bool $code MediaWiki language code. Default: false.
	 * @return string Relative url
	 */
	public function getTranslationUrl( $code = false ): string {
		$params = [
			'group' => $this->getMessageGroupId(),
			'action' => 'page',
			'filter' => '',
			'language' => $code,
		];

		$translate = SpecialPage::getTitleFor( 'Translate' );

		return $translate->getLocalURL( $params );
	}

	/** @inheritDoc */
	public function getTranslationPages(): array {
		$mwServices = MediaWikiServices::getInstance();

		$messageGroup = $this->getMessageGroup();
		$knownLanguageCodes = $messageGroup ? $messageGroup->getTranslatableLanguages() : null;
		$knownLanguageCodes ??= Utilities::getLanguageNames( LanguageNameUtils::AUTONYMS );

		$prefixedDbTitleKey = $this->getPageIdentity()->getDBkey() . '/';
		$baseNamespace = $this->getPageIdentity()->getNamespace();

		// Build a link batch query for all translation pages
		$linkBatch = $mwServices->getLinkBatchFactory()->newLinkBatch();
		foreach ( array_keys( $knownLanguageCodes ) as $code ) {
			$linkBatch->add( $baseNamespace, $prefixedDbTitleKey . $code );
		}

		$translationPages = [];
		foreach ( $linkBatch->getPageIdentities() as $pageIdentity ) {
			if ( $pageIdentity->exists() ) {
				$translationPages[] = Title::newFromPageIdentity( $pageIdentity );
			}
		}

		return $translationPages;
	}

	/** @inheritDoc */
	public function getTranslationUnitPages( ?string $code = null ): array {
		return $this->getTranslationUnitPagesByTitle( $this->title, $code );
	}

	public function getTranslationPercentages(): array {
		// Calculate percentages for the available translations
		try {
			$group = $this->getMessageGroup();
		} catch ( RuntimeException $e ) {
			return [];
		}

		if ( !$group ) {
			return [];
		}

		$titles = $this->getTranslationPages();
		$temp = MessageGroupStats::forGroup( $this->getMessageGroupId(), MessageGroupStats::FLAG_CACHE_ONLY );
		$stats = [];

		foreach ( $titles as $t ) {
			$handle = new MessageHandle( $t );
			$code = $handle->getCode();

			// Sometimes we want to display 0.00 for pages for which translation
			// hasn't started yet.
			$stats[$code] = 0.00;
			if ( ( $temp[$code][MessageGroupStats::TOTAL] ?? 0 ) > 0 ) {
				$total = $temp[$code][MessageGroupStats::TOTAL];
				$translated = $temp[$code][MessageGroupStats::TRANSLATED];
				$percentage = $translated / $total;
				$stats[$code] = sprintf( '%.2f', $percentage );
			}
		}

		// Content language is always up-to-date
		$stats[$this->getSourceLanguageCode()] = 1.00;

		return $stats;
	}

	public function supportsTransclusion(): ?bool {
		$transclusion = Services::getInstance()
			->getMessageGroupMetadata()
			->get( $this->getMessageGroupId(), 'transclusion' );
		if ( $transclusion === false ) {
			return null;
		}

		return $transclusion === '1';
	}

	public function getRevisionRecordWithFallback(): ?RevisionRecord {
		$title = $this->getTitle();
		$store = MediaWikiServices::getInstance()->getRevisionStore();
		$revRecord = $store->getRevisionByTitle( $title->getSubpage( $this->targetLanguage ) );
		if ( $revRecord ) {
			return $revRecord;
		}

		// Fetch the source fallback
		return $store->getRevisionByTitle( $title->getSubpage( $this->getSourceLanguageCode() ) );
	}

	/** @inheritDoc */
	public function isMoveable(): bool {
		return $this->getMarkedTag() !== null;
	}

	/** @inheritDoc */
	public function isDeletable(): bool {
		return $this->getMarkedTag() !== null;
	}

	/** @return bool|self */
	public static function isTranslationPage( Title $title ) {
		$handle = new MessageHandle( $title );
		if ( !Utilities::isTranslationPage( $handle ) ) {
			return false;
		}

		$languageCode = $handle->getCode();
		$newTitle = $handle->getTitleForBase();

		$page = self::newFromTitle( $newTitle );

		if ( $page->getMarkedTag() === null ) {
			return false;
		}

		$page->targetLanguage = $languageCode;

		return $page;
	}

	/** Helper to guess translation page from translation unit. */
	public static function parseTranslationUnit( LinkTarget $translationUnit ): array {
		// Format is Translations:SourcePageNamespace:SourcePageName/SectionName/LanguageCode.
		// We will drop the namespace immediately here.
		$parts = explode( '/', $translationUnit->getText() );

		// LanguageCode and SectionName are guaranteed to not have '/'.
		$language = array_pop( $parts );
		$section = array_pop( $parts );
		$sourcepage = implode( '/', $parts );

		return [
			'sourcepage' => $sourcepage,
			'section' => $section,
			'language' => $language
		];
	}

	public static function isSourcePage( PageIdentity $page ): bool {
		if ( !$page->exists() ) {
			// No point in loading all translatable pages if the page
			// doesn’t exist. This also avoids PreconditionExceptions
			// if $page is a Title pointing to a non-proper page like
			// a special page.
			return false;
		}

		$localCache = MediaWikiServices::getInstance()->getLocalServerObjectCache();
		$localKey = $localCache->makeKey( 'pagetranslation', 'sourcepages', 'local' );
		// Store the value in the local cache for a short duration to reduce the number of
		// times we hit the WAN cache. See: T366455
		$translatablePageIds = $localCache->getWithSetCallback(
			$localKey,
			$localCache::TTL_SECOND * 8,
			static function () {
				$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
				$cacheKey = $cache->makeKey( 'pagetranslation', 'sourcepages' );

				return $cache->getWithSetCallback(
					$cacheKey,
					$cache::TTL_HOUR * 2,
					[ TranslatablePage::class, 'getCacheValue' ],
					[
						'checkKeys' => [ $cacheKey ],
						'pcTTL' => $cache::TTL_PROC_SHORT,
						'pcGroup' => __CLASS__ . ':1',
						'version' => 3,
					]
				);
			},
			$localCache::READ_LATEST
		);

		return str_contains( $translatablePageIds, ( ',' . $page->getId() . ',' ) );
	}

	/** Clears the source page cache */
	public static function clearSourcePageCache(): void {
		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
		$cache->touchCheckKey( $cache->makeKey( 'pagetranslation', 'sourcepages' ) );
	}

	public static function determineStatus(
		?int $readyRevisionId,
		?int $markRevisionId,
		int $latestRevisionId
	): ?TranslatablePageStatus {
		$status = null;
		if ( $markRevisionId === null ) {
			// Never marked, check that the latest version is ready
			if ( $readyRevisionId === $latestRevisionId ) {
				$status = TranslatablePageStatus::PROPOSED;
			} else {
				// Otherwise, ignore such pages
				return null;
			}
		} elseif ( $readyRevisionId === $latestRevisionId ) {
			if ( $markRevisionId === $readyRevisionId ) {
				// Marked and latest version is fine
				$status = TranslatablePageStatus::ACTIVE;
			} else {
				$status = TranslatablePageStatus::OUTDATED;
			}
		} else {
			// Marked but latest version is not fine
			$status = TranslatablePageStatus::BROKEN;
		}

		return new TranslatablePageStatus( $status );
	}

	/**
	 * Get list of translatable page ids to be stored in the cache
	 * @internal
	 * @param mixed $oldValue
	 * @param int &$ttl
	 * @param array &$setOpts
	 * @return string
	 */
	public static function getCacheValue( $oldValue, &$ttl, array &$setOpts ): string {
		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();
		$setOpts += Database::getCacheSetOptions( $dbr );

		$ids = RevTagStore::getTranslatableBundleIds(
			RevTagStore::TP_MARK_TAG,
			RevTagStore::TP_READY_TAG
		);

		// Adding a comma at the end and beginning so that we can check for page ID
		// existence with the "," delimiters
		return ',' . implode( ',', $ids ) . ',';
	}
}

class_alias( TranslatablePage::class, 'TranslatablePage' );
