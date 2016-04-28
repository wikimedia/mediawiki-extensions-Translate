<?php
/**
 * Translatable page model.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Class to parse translatable wiki pages.
 *
 * @ingroup PageTranslation
 */
class TranslatablePage {
	/**
	 * Title of the page.
	 */
	protected $title;

	/**
	 * Text contents of the page.
	 */
	protected $text;

	/**
	 * Revision of the page, if applicaple.
	 *
	 * @var int
	 */
	protected $revision;

	/**
	 * From which source this object was constructed.
	 * Can be: text, revision, title
	 */
	protected $source;

	/**
	 * Whether the page contents is already loaded.
	 */
	protected $init = false;

	/**
	 * Name of the section which contains the translated page title.
	 */
	protected $displayTitle = 'Page display title';

	/**
	 * Whether the title should be translated
	 * @var bool
	 */
	protected $pageDisplayTitle;

	protected $cachedParse;

	/**
	 * @param Title $title Title object for the page
	 */
	protected function __construct( Title $title ) {
		$this->title = $title;
	}

	// Public constructors //

	/**
	 * Constructs a translatable page from given text.
	 * Some functions will fail unless you set revision
	 * parameter manually.
	 *
	 * @param Title $title
	 * @param string $text
	 *
	 * @return TranslatablePage
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
	 * @param integer $revision Revision number
	 * @throws MWException
	 * @return TranslatablePage
	 */
	public static function newFromRevision( Title $title, $revision ) {
		$rev = Revision::newFromTitle( $title, $revision );
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
	 * The text of last marked revision is loaded when neded.
	 *
	 * @param Title $title
	 * @return TranslatablePage
	 */
	public static function newFromTitle( Title $title ) {
		$obj = new self( $title );
		$obj->source = 'title';

		return $obj;
	}

	// Getters //

	/**
	 * Returns the title for this translatable page.
	 * @return Title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Returns the text for this translatable page.
	 * @throws MWException
	 * @return string
	 */
	public function getText() {
		if ( $this->init === false ) {
			switch ( $this->source ) {
				case 'text':
					break;
				/** @noinspection PhpMissingBreakStatementInspection */
				case 'title':
					$this->revision = $this->getMarkedTag();
				case 'revision':
					$rev = Revision::newFromTitle( $this->getTitle(), $this->revision );
					$this->text = ContentHandler::getContentText( $rev->getContent() );
					break;
			}
		}

		if ( !is_string( $this->text ) ) {
			throw new MWException( 'We have no text' );
		}

		$this->init = true;

		return $this->text;
	}

	/**
	 * Revision is null if object was constructed using newFromText.
	 * @return null or integer
	 */
	public function getRevision() {
		return $this->revision;
	}

	/**
	 * Manually set a revision number to use loading page text.
	 * @param integer $revision
	 */
	public function setRevision( $revision ) {
		$this->revision = $revision;
		$this->source = 'revision';
		$this->init = false;
	}

	// Public functions //

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
	 * @return 2014.06
	 */
	public function hasPageDisplayTitle() {
		// Cached value
		if ( $this->pageDisplayTitle !== null ) {
			return $this->pageDisplayTitle;
		}

		$this->pageDisplayTitle = true;

		// Check if title section exists in list of sections
		$previous = $this->getSections();
		if ( $previous && !in_array( $this->displayTitle, $previous ) ) {
			$this->pageDisplayTitle = false;
		}

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
		$section = str_replace( ' ', '_', $this->displayTitle );
		$page = $this->getTitle()->getPrefixedDBkey();

		$group = $this->getMessageGroup();
		// Sanity check, seems to happen during moves
		if ( !$group instanceof WikiPageMessageGroup ) {
			return null;
		}

		return $group->getMessage( "$page/$section", $code );
	}

	/**
	 * Returns a TPParse object which represents the parsed page.
	 *
	 * @throws TPException
	 * @return TPParse
	 */
	public function getParse() {
		if ( isset( $this->cachedParse ) ) {
			return $this->cachedParse;
		}

		$text = $this->getText();

		$nowiki = array();
		$text = self::armourNowiki( $nowiki, $text );

		$sections = array();

		// Add section to allow translating the page name
		$displaytitle = new TPSection;
		$displaytitle->id = $this->displayTitle;
		$displaytitle->text = $this->getTitle()->getPrefixedText();
		$sections[TranslateUtils::getPlaceholder()] = $displaytitle;

		$tagPlaceHolders = array();

		while ( true ) {
			$re = '~(<translate>)\s*(.*?)(</translate>)~s';
			$matches = array();
			$ok = preg_match_all( $re, $text, $matches, PREG_OFFSET_CAPTURE );

			if ( $ok === 0 ) {
				break; // No matches
			}

			// Do-placehold for the whole stuff
			$ph = TranslateUtils::getPlaceholder();
			$start = $matches[0][0][1];
			$len = strlen( $matches[0][0][0] );
			$end = $start + $len;
			$text = self::index_replace( $text, $ph, $start, $end );

			// Sectionise the contents
			// Strip the surrounding tags
			$contents = $matches[0][0][0]; // full match
			$start = $matches[2][0][1] - $matches[0][0][1]; // bytes before actual content
			$len = strlen( $matches[2][0][0] ); // len of the content
			$end = $start + $len;

			$sectiontext = substr( $contents, $start, $len );

			if ( strpos( $sectiontext, '<translate>' ) !== false ) {
				throw new TPException( array( 'pt-parse-nested', $sectiontext ) );
			}

			$sectiontext = self::unArmourNowiki( $nowiki, $sectiontext );

			$ret = $this->sectionise( $sections, $sectiontext );

			$tagPlaceHolders[$ph] =
				self::index_replace( $contents, $ret, $start, $end );
		}

		$prettyTemplate = $text;
		foreach ( $tagPlaceHolders as $ph => $value ) {
			$prettyTemplate = str_replace( $ph, '[...]', $prettyTemplate );
		}

		if ( strpos( $text, '<translate>' ) !== false ) {
			throw new TPException( array( 'pt-parse-open', $prettyTemplate ) );
		} elseif ( strpos( $text, '</translate>' ) !== false ) {
			throw new TPException( array( 'pt-parse-close', $prettyTemplate ) );
		}

		foreach ( $tagPlaceHolders as $ph => $value ) {
			$text = str_replace( $ph, $value, $text );
		}

		if ( count( $sections ) === 1 ) {
			// Don't return display title for pages which have no sections
			$sections = array();
		}

		$text = self::unArmourNowiki( $nowiki, $text );

		$parse = new TPParse( $this->getTitle() );
		$parse->template = $text;
		$parse->sections = $sections;

		// Cache it
		$this->cachedParse = $parse;

		return $parse;
	}

	// Inner functionality //

	/**
	 * @param array $holders
	 * @param string $text
	 * @return string
	 */
	public static function armourNowiki( &$holders, $text ) {
		$re = '~(<nowiki>)(.*?)(</nowiki>)~s';

		while ( preg_match( $re, $text, $matches ) ) {
			$ph = TranslateUtils::getPlaceholder();
			$text = str_replace( $matches[0], $ph, $text );
			$holders[$ph] = $matches[0];
		}

		return $text;
	}

	/**
	 * @param $holders
	 * @param string $text
	 * @return mixed
	 */
	public static function unArmourNowiki( $holders, $text ) {
		foreach ( $holders as $ph => $value ) {
			$text = str_replace( $ph, $value, $text );
		}

		return $text;
	}

	/**
	 * @param string $string
	 * @param string $rep
	 * @param int $start
	 * @param int $end
	 * @return string
	 */
	protected static function index_replace( $string, $rep, $start, $end ) {
		return substr( $string, 0, $start ) . $rep . substr( $string, $end );
	}

	/**
	 * Splits the content marked with \<translate> tags into sections, which
	 * are separated with with two or more newlines. Extra whitespace is captured
	 * in the template and not included in the sections.
	 * @param array $sections Array of placeholder => TPSection.
	 * @param string $text Contents of one pair of \<translate> tags.
	 * @return string Template with placeholders for sections, which itself are added to $sections.
	 */
	protected function sectionise( &$sections, $text ) {
		$flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
		$parts = preg_split( '~(\s*\n\n\s*|\s*$)~', $text, -1, $flags );

		$template = '';
		foreach ( $parts as $_ ) {
			if ( trim( $_ ) === '' ) {
				$template .= $_;
			} else {
				$ph = TranslateUtils::getPlaceholder();
				$sections[$ph] = $this->shakeSection( $_ );
				$template .= $ph;
			}
		}

		return $template;
	}

	/**
	 * Checks if this section already contains a section marker. If there
	 * is not, a new one will be created. Marker will have the value of
	 * -1, which will later be replaced with a real value.
	 *
	 * May throw a TPException if there is error with existing section
	 * markers.
	 *
	 * @param string $content Content of one section
	 * @throws TPException
	 * @return TPSection
	 */
	protected function shakeSection( $content ) {
		$re = '~<!--T:(.*?)-->~';
		$matches = array();
		$count = preg_match_all( $re, $content, $matches, PREG_SET_ORDER );

		if ( $count > 1 ) {
			throw new TPException( array( 'pt-shake-multiple', $content ) );
		}

		$section = new TPSection;
		if ( $count === 1 ) {
			foreach ( $matches as $match ) {
				list( /*full*/, $id ) = $match;
				$section->id = $id;

				// Currently handle only these two standard places.
				// Is this too strict?
				$rer1 = '~^<!--T:(.*?)-->\n~'; // Normal sections
				$rer2 = '~\s*<!--T:(.*?)-->$~m'; // Sections with title
				$content = preg_replace( $rer1, '', $content );
				$content = preg_replace( $rer2, '', $content );

				if ( preg_match( $re, $content ) === 1 ) {
					throw new TPException( array( 'pt-shake-position', $content ) );
				} elseif ( trim( $content ) === '' ) {
					throw new TPException( array( 'pt-shake-empty', $id ) );
				}
			}
		} else {
			// New section
			$section->id = -1;
		}

		$section->text = $content;

		return $section;
	}

	// Tag methods //

	protected static $tagCache = array();

	/**
	 * Adds a tag which indicates that this page is
	 * suitable for translation.
	 * @param integer $revision
	 * @param null|string $value
	 */
	public function addMarkedTag( $revision, $value = null ) {
		$this->addTag( 'tp:mark', $revision, $value );
	}

	/**
	 * Adds a tag which indicates that this page source is
	 * ready for marking for translation.
	 * @param integer $revision
	 */
	public function addReadyTag( $revision ) {
		$this->addTag( 'tp:tag', $revision );
	}

	/**
	 * @param string $tag Tag name
	 * @param int $revision Revision ID to add tag for
	 * @param mixed $value Optional. Value to be stored as serialized with | as separator
	 * @throws MWException
	 */
	protected function addTag( $tag, $revision, $value = null ) {
		$dbw = wfGetDB( DB_MASTER );

		$aid = $this->getTitle()->getArticleID();

		if ( is_object( $revision ) ) {
			throw new MWException( 'Got object, expected id' );
		}

		$conds = array(
			'rt_page' => $aid,
			'rt_type' => RevTag::getType( $tag ),
			'rt_revision' => $revision
		);
		$dbw->delete( 'revtag', $conds, __METHOD__ );

		if ( $value !== null ) {
			$conds['rt_value'] = serialize( implode( '|', $value ) );
		}

		$dbw->insert( 'revtag', $conds, __METHOD__ );

		self::$tagCache[$aid][$tag] = $revision;
	}

	/**
	 * Returns the latest revision which has marked tag, if any.
	 * @return integer|bool false
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

		$dbw = wfGetDB( DB_MASTER );
		$conds = array(
			'rt_page' => $aid,
			'rt_type' => array(
				RevTag::getType( 'tp:mark' ),
				RevTag::getType( 'tp:tag' ),
			),
		);

		$dbw->delete( 'revtag', $conds, __METHOD__ );
		$dbw->delete( 'translate_sections', array( 'trs_page' => $aid ), __METHOD__ );
		unset( self::$tagCache[$aid] );
	}

	/**
	 * @param $tag
	 * @param int $dbt
	 * @return int|bool False if tag is not found, else revision id
	 */
	protected function getTag( $tag, $dbt = DB_SLAVE ) {
		if ( !$this->getTitle()->exists() ) {
			return false;
		}

		$aid = $this->getTitle()->getArticleID();

		// ATTENTION: Cache should only be updated on POST requests.
		if ( isset( self::$tagCache[$aid][$tag] ) ) {
			return self::$tagCache[$aid][$tag];
		}

		$db = wfGetDB( $dbt );

		$conds = array(
			'rt_page' => $aid,
			'rt_type' => RevTag::getType( $tag ),
		);

		$options = array( 'ORDER BY' => 'rt_revision DESC' );

		$value = $db->selectField( 'revtag', 'rt_revision', $conds, __METHOD__, $options );
		return $value === false ? $value : (int)$value;
	}

	/**
	 * Produces a link to translation view of a translation page.
	 * @param string|bool $code MediaWiki language code. Default: false.
	 * @return string Relative url
	 */
	public function getTranslationUrl( $code = false ) {
		$params = array(
			'group' => $this->getMessageGroupId(),
			'action' => 'page',
			'filter' => '',
			'language' => $code,
		);

		$translate = SpecialPage::getTitleFor( 'Translate' );

		return $translate->getLocalURL( $params );
	}

	public function getMarkedRevs() {
		$db = TranslateUtils::getSafeReadDB();

		$fields = array( 'rt_revision', 'rt_value' );
		$conds = array(
			'rt_page' => $this->getTitle()->getArticleID(),
			'rt_type' => RevTag::getType( 'tp:mark' ),
		);
		$options = array( 'ORDER BY' => 'rt_revision DESC' );

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
			array( 'page_namespace', 'page_title' ),
			array(
				'page_namespace' => $this->getTitle()->getNamespace(),
				"page_title $likePattern"
			),
			__METHOD__
		);

		$titles = TitleArray::newFromResult( $res );
		$filtered = array();

		// Make sure we only get translation subpages while ignoring others
		$codes = Language::fetchLanguageNames();
		$prefix = $this->getTitle()->getText();
		/** @var Title $title */
		foreach ( $titles as $title ) {
			list( $name, $code ) = TranslateUtils::figureMessage( $title->getText() );
			if ( !isset( $codes[$code] ) || $name !== $prefix ) {
				continue;
			}
			$filtered[] = $title;
		}

		return $filtered;
	}

	/**
	 * Returns a list section ids.
	 * @return string[] List of string
	 * @since 2012-08-06
	 */
	protected function getSections() {
		$dbr = TranslateUtils::getSafeReadDB();

		$conds = array( 'trs_page' => $this->getTitle()->getArticleID() );
		$res = $dbr->select( 'translate_sections', 'trs_key', $conds, __METHOD__ );

		$sections = array();
		foreach ( $res as $row ) {
			$sections[] = $row->trs_key;
		}

		return $sections;
	}

	/**
	 * Returns a list of translation unit pages.
	 * @param string $set Can be either 'all', or 'active'
	 * @param string|bool $code Only list unit pages in given language.
	 * @return Title[] List of Titles.
	 * @since 2012-08-06
	 */
	public function getTranslationUnitPages( $set = 'active', $code = false ) {
		$dbw = wfGetDB( DB_MASTER );

		$base = $this->getTitle()->getPrefixedDBkey();
		// Including the / used as separator
		$baseLength = strlen( $base ) + 1;

		if ( $code !== false ) {
			$like = $dbw->buildLike( "$base/", $dbw->anyString(), "/$code" );
		} else {
			$like = $dbw->buildLike( "$base/", $dbw->anyString() );
		}

		$fields = array( 'page_namespace', 'page_title' );
		$conds = array(
			'page_namespace' => NS_TRANSLATIONS,
			'page_title ' . $like
		);
		$res = $dbw->select( 'page', $fields, $conds, __METHOD__ );

		// Only include pages which belong to this translatable page.
		// Problematic cases are when pages Foo and Foo/bar are both
		// translatable. Then when querying for Foo, we also get units
		// belonging to Foo/bar.
		$sections = array_flip( $this->getSections() );
		$units = array();
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

	/**
	 *
	 * @return array
	 */
	public function getTranslationPercentages() {
		// Calculate percentages for the available translations
		$group = $this->getMessageGroup();
		if ( !$group instanceof WikiPageMessageGroup ) {
			return array();
		}

		$titles = $this->getTranslationPages();
		$temp = MessageGroupStats::forGroup( $this->getMessageGroupId() );
		$stats = array();

		foreach ( $titles as $t ) {
			$handle = new MessageHandle( $t );
			$code = $handle->getCode();

			// Sometimes we want to display 0.00 for pages for which translation
			// hasn't started yet.
			$stats[$code] = 0.00;
			if ( isset( $temp[$code] ) && $temp[$code][MessageGroupStats::TOTAL] > 0 ) {
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
		$conds = array(
			'rt_page' => $title->getArticleID(),
			'rt_type' => RevTag::getType( 'tp:transver' ),
		);
		$options = array( 'ORDER BY' => 'rt_revision DESC' );

		return $db->selectField( 'revtag', $fields, $conds, __METHOD__, $options );
	}

	/**
	 * @param Title $title
	 * @return bool|TranslatablePage
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

		$page = TranslatablePage::newFromTitle( $newtitle );

		if ( $page->getMarkedTag() === false ) {
			return false;
		}

		return $page;
	}

	protected static function changeTitleText( Title $title, $text ) {
		return Title::makeTitleSafe( $title->getNamespace(), $text );
	}

	/**
	 * @param Title $title
	 * @return bool
	 */
	public static function isSourcePage( Title $title ) {
		static $cache = null;

		$cacheObj = wfGetCache( CACHE_ANYTHING );
		$cacheKey = wfMemcKey( 'pagetranslation', 'sourcepages' );

		if ( $cache === null ) {
			$cache = $cacheObj->get( $cacheKey );
		}
		if ( !is_array( $cache ) ) {
			$cache = self::getTranslatablePages();
			$cacheObj->set( $cacheKey, $cache, 60 * 5 );
		}

		return in_array( $title->getArticleID(), $cache );
	}

	/**
	 * Get a list of page ids where the latest revision is either tagged or marked
	 */
	public static function getTranslatablePages() {
		$dbr = TranslateUtils::getSafeReadDB();

		$tables = array( 'revtag', 'page' );
		$fields = 'rt_page';
		$conds = array(
			'rt_page = page_id',
			'rt_revision = page_latest',
			'rt_type' => array( RevTag::getType( 'tp:mark' ), RevTag::getType( 'tp:tag' ) ),
		);
		$options = array( 'GROUP BY' => 'rt_page' );

		$res = $dbr->select( $tables, $fields, $conds, __METHOD__, $options );
		$results = array();
		foreach ( $res as $row ) {
			$results[] = $row->rt_page;
		}

		return $results;
	}
}
