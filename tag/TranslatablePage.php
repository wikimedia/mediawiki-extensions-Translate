<?php

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
class TranslatablePage {
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

	/**
	 * Returns the title for this translatable page.
	 * @return Title
	 */
	public function getTitle() {
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

	/**
	 * Returns MessageGroup id (to be) used for translating this page.
	 * @return string
	 */
	public function getMessageGroupId() {
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
	public function addMarkedTag( $revision, $value = null ) {
		$this->addTag( 'tp:mark', $revision, $value );
		self::clearSourcePageCache();
	}

	/**
	 * Adds a tag which indicates that this page source is
	 * ready for marking for translation.
	 * @param int $revision
	 */
	public function addReadyTag( $revision ) {
		$this->addTag( 'tp:tag', $revision );
	}

	/**
	 * @param string $tag Tag name
	 * @param int $revision Revision ID to add tag for
	 * @param mixed|null $value Optional. Value to be stored as serialized with | as separator
	 * @throws MWException
	 */
	protected function addTag( $tag, $revision, $value = null ) {
		$dbw = wfGetDB( DB_PRIMARY );

		$aid = $this->getTitle()->getArticleID();

		if ( is_object( $revision ) ) {
			throw new MWException( 'Got object, expected id' );
		}

		$conds = [
			'rt_page' => $aid,
			'rt_type' => RevTag::getType( $tag ),
			'rt_revision' => $revision
		];
		$dbw->delete( 'revtag', $conds, __METHOD__ );

		if ( $value !== null ) {
			$conds['rt_value'] = serialize( implode( '|', $value ) );
		}

		$dbw->insert( 'revtag', $conds, __METHOD__ );

		self::$tagCache[$aid][$tag] = $revision;
	}

	/**
	 * Returns the latest revision which has marked tag, if any.
	 * @return int|bool false
	 */
	public function getMarkedTag() {
		return $this->getTag( 'tp:mark' );
	}

	/**
	 * Returns the latest revision which has ready tag, if any.
	 * @return int|bool false
	 */
	public function getReadyTag() {
		return $this->getTag( 'tp:tag' );
	}

	/**
	 * Removes all page translation feature data from the database.
	 * Does not remove translated sections or translation pages.
	 */
	public function unmarkTranslatablePage() {
		$aid = $this->getTitle()->getArticleID();

		$dbw = wfGetDB( DB_PRIMARY );
		$conds = [
			'rt_page' => $aid,
			'rt_type' => [
				RevTag::getType( 'tp:mark' ),
				RevTag::getType( 'tp:tag' ),
			],
		];

		$dbw->delete( 'revtag', $conds, __METHOD__ );
		$dbw->delete( 'translate_sections', [ 'trs_page' => $aid ], __METHOD__ );
		unset( self::$tagCache[$aid] );
		self::clearSourcePageCache();
	}

	/**
	 * @param string $tag
	 * @param int $dbt
	 * @return int|bool False if tag is not found, else revision id
	 */
	protected function getTag( $tag, $dbt = DB_REPLICA ) {
		if ( !$this->getTitle()->exists() ) {
			return false;
		}

		$aid = $this->getTitle()->getArticleID();

		// ATTENTION: Cache should only be updated on POST requests.
		if ( isset( self::$tagCache[$aid][$tag] ) ) {
			return self::$tagCache[$aid][$tag];
		}

		$db = wfGetDB( $dbt );

		$conds = [
			'rt_page' => $aid,
			'rt_type' => RevTag::getType( $tag ),
		];

		$options = [ 'ORDER BY' => 'rt_revision DESC' ];

		$value = $db->selectField( 'revtag', 'rt_revision', $conds, __METHOD__, $options );
		return $value === false ? $value : (int)$value;
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

	/**
	 * Fetch the available translation pages from database
	 * @return Title[]
	 */
	public function getTranslationPages() {
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
		$codes = Language::fetchLanguageNames();
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

	/**
	 * Returns a list of translation unit pages.
	 * @param string $set Can be either 'all', or 'active'
	 * @param string|bool $code Only list unit pages in given language.
	 * @return Title[]
	 * @since 2012-08-06
	 */
	public function getTranslationUnitPages( $set = 'active', $code = false ) {
		$dbw = wfGetDB( DB_PRIMARY );

		$base = $this->getTitle()->getPrefixedDBkey();
		// Including the / used as separator
		$baseLength = strlen( $base ) + 1;

		if ( $code !== false ) {
			$like = $dbw->buildLike( "$base/", $dbw->anyString(), "/$code" );
		} else {
			$like = $dbw->buildLike( "$base/", $dbw->anyString() );
		}

		$fields = [ 'page_namespace', 'page_title' ];
		$conds = [
			'page_namespace' => NS_TRANSLATIONS,
			'page_title ' . $like
		];
		$res = $dbw->select( 'page', $fields, $conds, __METHOD__ );

		// Only include pages which belong to this translatable page.
		// Problematic cases are when pages Foo and Foo/bar are both
		// translatable. Then when querying for Foo, we also get units
		// belonging to Foo/bar.
		$factory = Services::getInstance()->getTranslationUnitStoreFactory();
		// This method (getTranslationUnitPages) is only called when deleting or moving a
		// translatable page. This should be low traffic, and since this method is already using
		// the primary database for the other query, it seems safer to use the write here until
		// this is refactored.
		$store = $factory->getWriter( $this->getTitle() );
		$sections = array_flip( $store->getNames() );
		$units = [];
		foreach ( $res as $row ) {
			$title = Title::newFromRow( $row );

			// Strip the language code and the name of the
			// translatable to get plain section key
			$handle = new MessageHandle( $title );
			$key = substr( $handle->getKey(), $baseLength );
			if ( strpos( $key, '/' ) !== false ) {
				// Probably belongs to translatable subpage
				continue;
			}

			// Check against list of sections if requested
			if ( $set === 'active' && !isset( $sections[$key] ) ) {
				continue;
			}

			// We have a match :)
			$units[] = $title;
		}

		return $units;
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

		$codes = Language::fetchLanguageNames();
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

		if ( $page->getMarkedTag() === false ) {
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
			function ( $oldValue, &$ttl, array &$setOpts ) {
				$dbr = wfGetDB( DB_REPLICA );
				$setOpts += Database::getCacheSetOptions( $dbr );

				return self::getTranslatablePages();
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

	/**
	 * Get a list of page ids where the latest revision is either tagged or marked
	 * @return array
	 */
	public static function getTranslatablePages() {
		$dbr = TranslateUtils::getSafeReadDB();

		$tables = [ 'revtag', 'page' ];
		$fields = 'rt_page';
		$conds = [
			'rt_page = page_id',
			'rt_revision = page_latest',
			'rt_type' => [ RevTag::getType( 'tp:mark' ), RevTag::getType( 'tp:tag' ) ],
		];
		$options = [ 'GROUP BY' => 'rt_page' ];

		$res = $dbr->select( $tables, $fields, $conds, __METHOD__, $options );
		$results = [];
		foreach ( $res as $row ) {
			$results[] = $row->rt_page;
		}

		return $results;
	}
}
