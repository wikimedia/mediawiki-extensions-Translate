<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use LogicException;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundle;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Linker\LinkTarget;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MessageGroups;
use MessageGroupStats;
use MessageHandle;
use MWException;
use RuntimeException;
use SpecialPage;
use TextContent;
use Title;
use TranslateMetadata;
use TranslateUtils;
use Wikimedia\Rdbms\Database;
use Wikimedia\Rdbms\IResultWrapper;
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

	/** @var Title */
	protected $title;
	/** @var RevTagStore */
	protected $revTagStore;
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

	/** @param Title $title Title object for the page */
	protected function __construct( Title $title ) {
		$this->title = $title;
		$this->revTagStore = new RevTagStore();
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
	 * @throws MWException
	 */
	public static function newFromRevision( Title $title, int $revision ): self {
		$rev = MediaWikiServices::getInstance()
			->getRevisionLookup()
			->getRevisionByTitle( $title, $revision );
		if ( $rev === null ) {
			throw new MWException( 'Revision is null' );
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
	public static function newFromTitle( Title $title ): self {
		$obj = new self( $title );
		$obj->source = 'title';

		return $obj;
	}

	/** @inheritDoc */
	public function getTitle(): Title {
		return $this->title;
	}

	/** Returns the text for this translatable page. */
	public function getText(): string {
		if ( $this->text !== null ) {
			return $this->text;
		}

		$page = $this->getTitle()->getPrefixedDBkey();

		if ( $this->source === 'title' ) {
			$revision = $this->getMarkedTag();
			if ( !is_int( $revision ) ) {
				throw new LogicException(
					"Trying to load a text for $page which is not marked for translation"
				);
			}
			$this->revision = $revision;
		}

		$flags = TranslateUtils::shouldReadFromPrimary()
			? RevisionLookup::READ_LATEST
			: RevisionLookup::READ_NORMAL;
		$rev = MediaWikiServices::getInstance()
			->getRevisionLookup()
			->getRevisionByTitle( $this->getTitle(), $this->revision, $flags );
		$content = $rev->getContent( SlotRecord::MAIN );
		$text = ( $content instanceof TextContent ) ? $content->getText() : null;

		if ( !is_string( $text ) ) {
			throw new RuntimeException( "Failed to load text for $page" );
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
		return self::getMessageGroupIdFromTitle( $this->getTitle() );
	}

	/** Constructs MessageGroup id for any title. */
	public static function getMessageGroupIdFromTitle( Title $title ): string {
		return 'page-' . $title->getPrefixedText();
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
		$store = $factory->getReader( $this->getTitle() );
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
		$page = $this->getTitle()->getPrefixedDBkey();

		try {
			$group = $this->getMessageGroup();
		} catch ( RuntimeException $e ) {
			return null;
		}

		// Sanity check, seems to happen during moves
		if ( !$group ) {
			return null;
		}

		return $group->getMessage( "$page/$section", $languageCode, $group::READ_NORMAL );
	}

	public function getStrippedSourcePageText(): string {
		$parser = Services::getInstance()->getTranslatablePageParser();
		$text = $parser->cleanupTags( $this->getText() );
		$text = preg_replace( '~<languages\s*/>\n?~s', '', $text );

		return $text;
	}

	public static function getTranslationPageFromTitle( Title $title ): ?TranslationPage {
		$self = self::isTranslationPage( $title );
		if ( !$self ) {
			return null;
		}

		return $self->getTranslationPage( $self->targetLanguage );
	}

	public function getTranslationPage( string $targetLanguage ): TranslationPage {
		$mwServices = MediaWikiServices::getInstance();
		$config = $mwServices->getMainConfig();
		$parser = Services::getInstance()->getTranslatablePageParser();
		$parserOutput = $parser->parse( $this->getText() );
		$pageVersion = (int)TranslateMetadata::get( $this->getMessageGroupId(), 'version' );
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

	protected static $tagCache = [];

	/** Adds a tag which indicates that this page is suitable for translation. */
	public function addMarkedTag( int $revision, array $value = null ) {
		$this->revTagStore->replaceTag( $this->getTitle(), RevTagStore::TP_MARK_TAG, $revision, $value );
		self::clearSourcePageCache();
	}

	/** Adds a tag which indicates that this page source is ready for marking for translation. */
	public function addReadyTag( int $revision ): void {
		$this->revTagStore->replaceTag( $this->getTitle(), RevTagStore::TP_READY_TAG, $revision );
		if ( !self::isSourcePage( $this->getTitle() ) ) {
			self::clearSourcePageCache();
		}
	}

	/** Returns the latest revision which has marked tag, if any. */
	public function getMarkedTag(): ?int {
		return $this->revTagStore->getLatestRevisionWithTag( $this->getTitle(), RevTagStore::TP_MARK_TAG );
	}

	/** Returns the latest revision which has ready tag, if any. */
	public function getReadyTag(): ?int {
		return $this->revTagStore->getLatestRevisionWithTag( $this->getTitle(), RevTagStore::TP_READY_TAG );
	}

	/**
	 * Removes all page translation feature data from the database.
	 * Does not remove translated sections or translation pages.
	 */
	public function unmarkTranslatablePage(): void {
		$aid = $this->getTitle()->getArticleID();
		$dbw = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_PRIMARY );
		$this->revTagStore->removeTags( $this->getTitle(), RevTagStore::TP_MARK_TAG, RevTagStore::TP_READY_TAG );
		$dbw->delete( 'translate_sections', [ 'trs_page' => $aid ], __METHOD__ );

		self::clearSourcePageCache();
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

	public function getMarkedRevs(): IResultWrapper {
		$db = TranslateUtils::getSafeReadDB();

		$fields = [ 'rt_revision', 'rt_value' ];
		$conds = [
			'rt_page' => $this->getTitle()->getArticleID(),
			'rt_type' => RevTagStore::TP_MARK_TAG,
		];
		$options = [ 'ORDER BY' => 'rt_revision DESC' ];

		return $db->select( 'revtag', $fields, $conds, __METHOD__, $options );
	}

	/** @inheritDoc */
	public function getTranslationPages(): array {
		$mwServices = MediaWikiServices::getInstance();
		$knownLanguageCodes = $this->getMessageGroup()->getTranslatableLanguages()
			?? TranslateUtils::getLanguageNames( null );

		$prefixedDbTitleKey = $this->getTitle()->getDBkey() . '/';
		$baseNamespace = $this->getTitle()->getNamespace();

		// Build a link batch query for all translation pages
		$linkBatch = $mwServices->getLinkBatchFactory()->newLinkBatch();
		foreach ( array_keys( $knownLanguageCodes ) as $code ) {
			$linkBatch->add( $baseNamespace, $prefixedDbTitleKey . $code );
		}

		$translationPages = [];
		foreach ( $linkBatch->getPageIdentities() as $pageIdentity ) {
			if ( $pageIdentity->exists() ) {
				$translationPages[] = Title::castFromPageIdentity( $pageIdentity );
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
		$temp = MessageGroupStats::forGroup( $this->getMessageGroupId() );
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

	public function getTransRev( string $suffix ) {
		$title = Title::makeTitle( NS_TRANSLATIONS, $suffix );

		$db = TranslateUtils::getSafeReadDB();
		$fields = 'rt_value';
		$conds = [
			'rt_page' => $title->getArticleID(),
			'rt_type' => RevTagStore::TRANSVER_PROP,
		];
		$options = [ 'ORDER BY' => 'rt_revision DESC' ];

		return $db->selectField( 'revtag', $fields, $conds, __METHOD__, $options );
	}

	public function supportsTransclusion(): ?bool {
		$transclusion = TranslateMetadata::get( $this->getMessageGroupId(), 'transclusion' );
		if ( $transclusion === false ) {
			return null;
		}

		return $transclusion === '1';
	}

	public function setTransclusion( bool $supportsTransclusion ): void {
		TranslateMetadata::set(
			$this->getMessageGroupId(),
			'transclusion',
			$supportsTransclusion ? '1' : '0'
		);
	}

	public function getRevisionRecordWithFallback(): ?RevisionRecord {
		$title = $this->getTitle();
		$store = MediaWikiServices::getInstance()->getRevisionStore();
		$revRecord = $store->getRevisionByTitle( $title->getSubpage( $this->targetLanguage ) );
		if ( $revRecord ) {
			return $revRecord;
		}

		// Fetch the source fallback
		$sourceLanguage = $this->getMessageGroup()->getSourceLanguage();
		return $store->getRevisionByTitle( $title->getSubpage( $sourceLanguage ) );
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
		$key = $handle->getKey();
		$code = $handle->getCode();

		if ( $key === '' || $code === '' ) {
			return false;
		}

		$codes = MediaWikiServices::getInstance()->getLanguageNameUtils()->getLanguageNames();
		global $wgTranslateDocumentationLanguageCode;
		unset( $codes[$wgTranslateDocumentationLanguageCode] );

		if ( !isset( $codes[$code] ) ) {
			return false;
		}

		$newtitle = self::changeTitleText( $title, $key );

		if ( !$newtitle ) {
			return false;
		}

		$page = self::newFromTitle( $newtitle );

		if ( $page->getMarkedTag() === null ) {
			return false;
		}

		$page->targetLanguage = $code;

		return $page;
	}

	private static function changeTitleText( Title $title, string $text ): ?Title {
		return Title::makeTitleSafe( $title->getNamespace(), $text );
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

	public static function isSourcePage( Title $title ): bool {
		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
		$cacheKey = $cache->makeKey( 'pagetranslation', 'sourcepages' );

		$translatablePageIds = $cache->getWithSetCallback(
			$cacheKey,
			$cache::TTL_HOUR * 2,
			static function ( $oldValue, &$ttl, array &$setOpts ) {
				$dbr = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_REPLICA );
				$setOpts += Database::getCacheSetOptions( $dbr );

				return RevTagStore::getTranslatableBundleIds(
					RevTagStore::TP_MARK_TAG, RevTagStore::TP_READY_TAG
				);
			},
			[
				'checkKeys' => [ $cacheKey ],
				'pcTTL' => $cache::TTL_PROC_SHORT,
				'pcGroup' => __CLASS__ . ':1'
			]
		);

		return in_array( $title->getArticleID(), $translatablePageIds );
	}

	/** Clears the source page cache */
	public static function clearSourcePageCache(): void {
		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
		$cache->touchCheckKey( $cache->makeKey( 'pagetranslation', 'sourcepages' ) );
	}
}

class_alias( TranslatablePage::class, 'TranslatablePage' );
