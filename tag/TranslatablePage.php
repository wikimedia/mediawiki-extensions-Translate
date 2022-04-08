<?php

use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundle;
use MediaWiki\Extension\Translate\PageTranslation\TranslationPage;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Linker\LinkTarget;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use Wikimedia\Rdbms\Database;

/**
 * Mixed bag of methods related to translatable pages.
 *
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
	 *
	 * @param Title $title
	 * @param string $text
	 *
	 * @return self
	 */
	public static function newFromText( Title $title, $text ) {
		$obj = new self( $title );
		$obj->text = $text;
		$obj->source = 'text';

		return $obj;
	}

	/**
	 * Constructs a translatable page from given revision.
	 * The revision must belong to the title given or unspecified
	 * behavior will happen.
	 *
	 * @param Title $title
	 * @param int $revision Revision number
	 * @throws MWException
	 * @return self
	 */
	public static function newFromRevision( Title $title, int $revision ) {
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
	 *
	 * @param Title $title
	 * @return self
	 */
	public static function newFromTitle( Title $title ) {
		$obj = new self( $title );
		$obj->source = 'title';

		return $obj;
	}

	/** @inheritDoc */
	public function getTitle(): Title {
		return $this->title;
	}

	/**
	 * Returns the text for this translatable page.
	 * @return string
	 */
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
	 * @return string
	 * @since 2013-01-28
	 */
	public function getSourceLanguageCode() {
		return $this->getTitle()->getPageLanguage()->getCode();
	}

	/** @inheritDoc */
	public function getMessageGroupId(): string {
		return self::getMessageGroupIdFromTitle( $this->getTitle() );
	}

	/**
	 * Constructs MessageGroup id for any title.
	 * @param Title $title
	 * @return string
	 */
	public static function getMessageGroupIdFromTitle( Title $title ) {
		return 'page-' . $title->getPrefixedText();
	}

	/**
	 * Returns MessageGroup used for translating this page. It may still be empty
	 * if the page has not been ever marked.
	 * @return WikiPageMessageGroup
	 */
	public function getMessageGroup() {
		return MessageGroups::getGroup( $this->getMessageGroupId() );
	}

	/**
	 * Check whether title is marked for translation
	 * @return bool
	 * @since 2014.06
	 */
	public function hasPageDisplayTitle() {
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

	/**
	 * Get translated page title.
	 * @param string $code Language code.
	 * @return string|null
	 */
	public function getPageDisplayTitle( $code ) {
		// Return null if title not marked for translation
		if ( !$this->hasPageDisplayTitle() ) {
			return null;
		}

		// Display title from DB
		$section = str_replace( ' ', '_', self::DISPLAY_TITLE_UNIT_ID );
		$page = $this->getTitle()->getPrefixedDBkey();

		$group = $this->getMessageGroup();
		// Sanity check, seems to happen during moves
		if ( !$group instanceof WikiPageMessageGroup ) {
			return null;
		}

		return $group->getMessage( "$page/$section", $code, $group::READ_NORMAL );
	}

	/**
	 * @return string
	 * @since 2020.07
	 */
	public function getStrippedSourcePageText(): string {
		$parser = Services::getInstance()->getTranslatablePageParser();
		$text = $parser->cleanupTags( $this->getText() );
		$text = preg_replace( '~<languages\s*/>\n?~s', '', $text );

		return $text;
	}

	/**
	 * @param Title $title
	 * @return ?TranslationPage
	 * @since 2020.07
	 */
	public static function getTranslationPageFromTitle( Title $title ): ?TranslationPage {
		$self = self::isTranslationPage( $title );
		if ( !$self ) {
			return null;
		}

		return $self->getTranslationPage( $self->targetLanguage );
	}

	/**
	 * @param string $targetLanguage
	 * @return TranslationPage
	 * @since 2020.07
	 */
	public function getTranslationPage( string $targetLanguage ): TranslationPage {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		$parser = Services::getInstance()->getTranslatablePageParser();
		$parserOutput = $parser->parse( $this->getText() );
		$pageVersion = (int)TranslateMetadata::get( $this->getMessageGroupId(), 'version' );
		$wrapUntranslated = $pageVersion >= 2;

		return new TranslationPage(
			$parserOutput,
			$this->getMessageGroup(),
			Language::factory( $targetLanguage ),
			Language::factory( $this->getSourceLanguageCode() ),
			$config->get( 'TranslateKeepOutdatedTranslations' ),
			$wrapUntranslated,
			$this->getTitle()
		);
	}

	protected static $tagCache = [];

	/**
	 * Adds a tag which indicates that this page is
	 * suitable for translation.
	 * @param int $revision
	 * @param null|string $value
	 */
	public function addMarkedTag( int $revision, $value = null ) {
		$this->revTagStore->addTag( $this->getTitle(), 'tp:mark', $revision, $value );
		self::clearSourcePageCache();
	}

	/**
	 * Adds a tag which indicates that this page source is
	 * ready for marking for translation.
	 * @param int $revision
	 */
	public function addReadyTag( int $revision ): void {
		$this->revTagStore->addTag( $this->getTitle(), 'tp:tag', $revision );
	}

	/**
	 * Returns the latest revision which has marked tag, if any.
	 * @return ?int
	 */
	public function getMarkedTag() {
		return $this->revTagStore->getLatestRevisionWithTag( $this->getTitle(), 'tp:mark' );
	}

	/**
	 * Returns the latest revision which has ready tag, if any.
	 * @return ?int
	 */
	public function getReadyTag(): ?int {
		return $this->revTagStore->getLatestRevisionWithTag( $this->getTitle(), 'tp:tag' );
	}

	/**
	 * Removes all page translation feature data from the database.
	 * Does not remove translated sections or translation pages.
	 */
	public function unmarkTranslatablePage() {
		$aid = $this->getTitle()->getArticleID();
		$dbw = wfGetDB( DB_PRIMARY );

		$this->revTagStore->removeTags( $this->getTitle(), 'tp:mark', 'tp:tag' );
		$dbw->delete( 'translate_sections', [ 'trs_page' => $aid ], __METHOD__ );

		self::clearSourcePageCache();
	}

	/**
	 * Produces a link to translation view of a translation page.
	 * @param string|bool $code MediaWiki language code. Default: false.
	 * @return string Relative url
	 */
	public function getTranslationUrl( $code = false ) {
		$params = [
			'group' => $this->getMessageGroupId(),
			'action' => 'page',
			'filter' => '',
			'language' => $code,
		];

		$translate = SpecialPage::getTitleFor( 'Translate' );

		return $translate->getLocalURL( $params );
	}

	public function getMarkedRevs() {
		$db = TranslateUtils::getSafeReadDB();

		$fields = [ 'rt_revision', 'rt_value' ];
		$conds = [
			'rt_page' => $this->getTitle()->getArticleID(),
			'rt_type' => RevTag::getType( 'tp:mark' ),
		];
		$options = [ 'ORDER BY' => 'rt_revision DESC' ];

		return $db->select( 'revtag', $fields, $conds, __METHOD__, $options );
	}

	/** @inheritDoc */
	public function getTranslationPages(): array {
		$dbr = TranslateUtils::getSafeReadDB();

		$prefix = $this->getTitle()->getDBkey() . '/';
		$likePattern = $dbr->buildLike( $prefix, $dbr->anyString() );
		$res = $dbr->select(
			'page',
			[ 'page_namespace', 'page_title' ],
			[
				'page_namespace' => $this->getTitle()->getNamespace(),
				"page_title $likePattern"
			],
			__METHOD__
		);

		$titles = TitleArray::newFromResult( $res );
		$filtered = [];

		// Make sure we only get translation subpages while ignoring others
		$codes = MediaWikiServices::getInstance()->getLanguageNameUtils()->getLanguageNames();
		$prefix = $this->getTitle()->getText();
		/** @var Title $title */
		foreach ( $titles as $title ) {
			[ $name, $code ] = TranslateUtils::figureMessage( $title->getText() );
			if ( !isset( $codes[$code] ) || $name !== $prefix ) {
				continue;
			}
			$filtered[] = $title;
		}

		return $filtered;
	}

	/** @inheritDoc */
	public function getTranslationUnitPages( ?string $code = null ): array {
		return $this->getTranslationUnitPagesByTitle( $this->title, $code );
	}

	/** @return array */
	public function getTranslationPercentages() {
		// Calculate percentages for the available translations
		$group = $this->getMessageGroup();
		if ( !$group instanceof WikiPageMessageGroup ) {
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

	public function getTransRev( $suffix ) {
		$title = Title::makeTitle( NS_TRANSLATIONS, $suffix );

		$db = TranslateUtils::getSafeReadDB();
		$fields = 'rt_value';
		$conds = [
			'rt_page' => $title->getArticleID(),
			'rt_type' => RevTag::getType( 'tp:transver' ),
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

	/**
	 * @param Title $title
	 * @return bool|self
	 */
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

	protected static function changeTitleText( Title $title, $text ) {
		return Title::makeTitleSafe( $title->getNamespace(), $text );
	}

	/**
	 * Helper to guess translation page from translation unit.
	 *
	 * @param LinkTarget $translationUnit
	 * @return array
	 * @since 2019.10
	 */
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

	/**
	 * @param Title $title
	 * @return bool
	 */
	public static function isSourcePage( Title $title ) {
		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
		$cacheKey = $cache->makeKey( 'pagetranslation', 'sourcepages' );

		$translatablePageIds = $cache->getWithSetCallback(
			$cacheKey,
			$cache::TTL_HOUR * 2,
			static function ( $oldValue, &$ttl, array &$setOpts ) {
				$dbr = wfGetDB( DB_REPLICA );
				$setOpts += Database::getCacheSetOptions( $dbr );

				return RevTagStore::getTranslatableBundleIds(
					RevTag::getType( 'tp:mark' ), RevTag::getType( 'tp:tag' )
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

	/**
	 * Clears the source page cache
	 */
	public static function clearSourcePageCache(): void {
		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
		$cache->touchCheckKey( $cache->makeKey( 'pagetranslation', 'sourcepages' ) );
	}
}
