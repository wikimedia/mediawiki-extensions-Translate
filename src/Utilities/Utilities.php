<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities;

use MediaWiki\Config\ConfigException;
use MediaWiki\Content\Content;
use MediaWiki\Content\TextContent;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\PageTranslation\Hooks as PageTranslationHooks;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePage;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Language\LanguageCode;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use MediaWiki\Xml\Xml;
use MessageGroup;
use UnexpectedValueException;
use Wikimedia\Rdbms\IDatabase;

/**
 * Essentially random collection of helper functions, similar to GlobalFunctions.php.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class Utilities {
	/**
	 * Does quick normalisation of message name so that in can be looked from the
	 * database.
	 * @param string $message Name of the message
	 * @param string $code Language code in lower case and with dash as delimiter
	 * @param int $ns Namespace constant
	 * @return string The normalised title as a string.
	 */
	public static function title( string $message, string $code, int $ns = NS_MEDIAWIKI ): string {
		// Cache some amount of titles for speed.
		static $cache = [];
		$key = $ns . ':' . $message;

		if ( !isset( $cache[$key] ) ) {
			$cache[$key] = Title::capitalize( $message, $ns );
		}

		if ( $code ) {
			return $cache[$key] . '/' . $code;
		} else {
			return $cache[$key];
		}
	}

	/**
	 * Splits page name into message key and language code.
	 * @param string $text
	 * @return array ( string, string ) Key and language code.
	 * @todo Handle names without slash.
	 */
	public static function figureMessage( string $text ): array {
		$pos = strrpos( $text, '/' );
		$code = substr( $text, $pos + 1 );
		$key = substr( $text, 0, $pos );

		return [ $key, $code ];
	}

	/**
	 * Loads page content *without* side effects.
	 * @param string $key Message key.
	 * @param string $language Language code.
	 * @param int $namespace Namespace number.
	 * @return string|null The contents or null.
	 */
	public static function getMessageContent( string $key, string $language, int $namespace = NS_MEDIAWIKI ): ?string {
		$title = self::title( $key, $language, $namespace );
		$data = self::getContents( [ $title ], $namespace );

		return $data[$title][0] ?? null;
	}

	/**
	 * Fetches contents for pagenames in given namespace without side effects.
	 *
	 * @param string|string[] $titles Database page names.
	 * @param int $namespace The number of the namespace.
	 * @return array ( string => array ( string, string ) ) Tuples of page
	 * text and last author indexed by page name.
	 */
	public static function getContents( $titles, int $namespace ): array {
		$mwServices = MediaWikiServices::getInstance();
		$dbr = $mwServices->getConnectionProvider()->getReplicaDatabase();
		$revStore = $mwServices->getRevisionStore();
		$titleContents = [];

		$rows = $revStore->newSelectQueryBuilder( $dbr )
			->joinPage()
			->joinComment()
			->where( [ 'page_namespace' => $namespace, 'page_title' => $titles, 'page_latest=rev_id' ] )
			->caller( __METHOD__ )
			->fetchResultSet();

		$revisions = $revStore->newRevisionsFromBatch( $rows, [
			'slots' => true,
			'content' => true
		] )->getValue();

		foreach ( $rows as $row ) {
			/** @var RevisionRecord|null $rev */
			$rev = $revisions[$row->rev_id];
			if ( $rev ) {
				/** @var TextContent $content */
				$content = $rev->getContent( SlotRecord::MAIN );
				if ( $content ) {
					$titleContents[$row->page_title] = [
						$content->getText(),
						$row->rev_user_text
					];
				}
			}
		}

		$rows->free();

		return $titleContents;
	}

	/**
	 * Returns the content for a given title and adds the fuzzy tag if requested.
	 * @param Title $title
	 * @param bool $addFuzzy Add the fuzzy tag if appropriate.
	 * @return string|null
	 */
	public static function getContentForTitle( Title $title, bool $addFuzzy = false ): ?string {
		$store = MediaWikiServices::getInstance()->getRevisionStore();
		$revision = $store->getRevisionByTitle( $title );

		if ( $revision === null ) {
			return null;
		}

		$content = $revision->getContent( SlotRecord::MAIN );
		$wiki = ( $content instanceof TextContent ) ? $content->getText() : null;

		// Either unexpected content type, or the revision content is hidden
		if ( $wiki === null ) {
			return null;
		}

		if ( $addFuzzy ) {
			$handle = new MessageHandle( $title );
			if ( $handle->isFuzzy() ) {
				$wiki = TRANSLATE_FUZZY . str_replace( TRANSLATE_FUZZY, '', $wiki );
			}
		}

		return $wiki;
	}

	/* Some other helpers for output */

	/**
	 * Returns a localised language name.
	 * @param string $code Language code.
	 * @param null|string $language Language code of the language that the name should be in.
	 * @return string Best-effort localisation of wanted language name.
	 */
	public static function getLanguageName( string $code, ?string $language = 'en' ): string {
		$languages = self::getLanguageNames( $language );
		return $languages[$code] ?? $code;
	}

	/**
	 * Get translated language names for the languages generally supported for
	 * translation in the current wiki. Message groups can have further
	 * exclusions.
	 * @param null|string $code
	 * @return array ( language code => language name )
	 */
	public static function getLanguageNames( ?string $code ): array {
		$mwServices = MediaWikiServices::getInstance();
		$languageNames = $mwServices->getLanguageNameUtils()->getLanguageNames( $code );

		$deprecatedCodes = LanguageCode::getDeprecatedCodeMapping();
		foreach ( array_keys( $deprecatedCodes ) as $deprecatedCode ) {
			unset( $languageNames[ $deprecatedCode ] );
		}
		Services::getInstance()->getHookRunner()->onTranslateSupportedLanguages( $languageNames, $code );

		return $languageNames;
	}

	/**
	 * Constructs a fieldset with contents.
	 * @param string $legend Raw html.
	 * @param string $contents Raw html.
	 * @param array $attributes Html attributes for the fieldset.
	 * @return string Html.
	 */
	public static function fieldset( string $legend, string $contents, array $attributes = [] ): string {
		return Xml::openElement( 'fieldset', $attributes ) .
			Xml::tags( 'legend', null, $legend ) . $contents .
			Xml::closeElement( 'fieldset' );
	}

	/**
	 * Escapes the message, and does some mangling to whitespace, so that it is
	 * preserved when outputted as-is to html page. Line feeds are converted to
	 * \<br /> and occurrences of leading and trailing and multiple consecutive
	 * spaces to non-breaking spaces.
	 *
	 * This is also implemented in JavaScript in ext.translate.quickedit.
	 *
	 * @param string $message Plain text string.
	 * @return string Text string that is ready for outputting.
	 */
	public static function convertWhiteSpaceToHTML( string $message ): string {
		$msg = htmlspecialchars( $message );
		$msg = preg_replace( '/^ /m', '&#160;', $msg );
		$msg = preg_replace( '/ $/m', '&#160;', $msg );
		$msg = preg_replace( '/  /', '&#160; ', $msg );
		$msg = str_replace( "\n", '<br />', $msg );

		return $msg;
	}

	/**
	 * Gets the path for cache files. The cache directory must be configured to use this method.
	 * @param string $filename
	 * @return string Full path.
	 */
	public static function cacheFile( string $filename ): string {
		global $wgTranslateCacheDirectory, $wgCacheDirectory;

		if ( $wgTranslateCacheDirectory !== false ) {
			$dir = $wgTranslateCacheDirectory;
		} elseif ( $wgCacheDirectory !== false ) {
			$dir = $wgCacheDirectory;
		} else {
			throw new ConfigException( "\$wgCacheDirectory must be configured" );
		}

		return "$dir/$filename";
	}

	/** Returns a random string that can be used as placeholder in strings. */
	public static function getPlaceholder(): string {
		static $i = 0;

		return "\x7fUNIQ" . dechex( mt_rand( 0, 0x7fffffff ) ) .
			dechex( mt_rand( 0, 0x7fffffff ) ) . '-' . $i++;
	}

	/**
	 * Get URLs for icons if available.
	 * @param MessageGroup $g
	 * @param int $size Length of the edge of a bounding box to fit the icon.
	 * @return null|array
	 */
	public static function getIcon( MessageGroup $g, int $size ): ?array {
		$icon = $g->getIcon();
		if ( !$icon || substr( $icon, 0, 7 ) !== 'wiki://' ) {
			return null;
		}

		$formats = [];

		$filename = substr( $icon, 7 );
		$file = MediaWikiServices::getInstance()->getRepoGroup()->findFile( $filename );
		if ( !$file ) {
			wfWarn( "Unknown message group icon file $icon" );

			return null;
		}

		if ( $file->isVectorized() ) {
			$formats['vector'] = $file->getFullUrl();
		}

		$formats['raster'] = $file->createThumb( $size, $size );

		return $formats;
	}

	/**
	 * Get a DB handle suitable for read and read-for-write cases
	 *
	 * @return IDatabase Primary for HTTP POST, CLI, DB already changed;
	 * replica otherwise
	 */
	public static function getSafeReadDB(): IDatabase {
		$lb = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$index = self::shouldReadFromPrimary() ? DB_PRIMARY : DB_REPLICA;

		return $lb->getConnection( $index );
	}

	/** Check whether primary should be used for reads to avoid reading stale data. */
	public static function shouldReadFromPrimary(): bool {
		$lb = MediaWikiServices::getInstance()->getDBLoadBalancer();
		// Parsing APIs need POST for payloads but are read-only, so avoid spamming
		// the primary then. No good way to check this at the moment...
		if ( PageTranslationHooks::$renderingContext ) {
			return false;
		}

		return PHP_SAPI === 'cli' ||
			RequestContext::getMain()->getRequest()->wasPosted() ||
			$lb->hasOrMadeRecentPrimaryChanges();
	}

	/**
	 * Get a URL that points to an editor for this message handle.
	 * @param MessageHandle $handle
	 * @param string $action_source If non-empty, defines where
	 * the link originates from, for metrics (event logging)
	 * @return string Domain relative URL
	 */
	public static function getEditorUrl( MessageHandle $handle, string $action_source = '' ): string {
		if ( !$handle->isValid() ) {
			return $handle->getTitle()->getLocalURL( [ 'action' => 'edit' ] );
		}

		$title = MediaWikiServices::getInstance()
			->getSpecialPageFactory()->getPage( 'Translate' )->getPageTitle();
		$urlParameters = [
			'showMessage' => $handle->getInternalKey(),
			'group' => $handle->getGroup()->getId(),
			'language' => $handle->getCode(),
		];

		if ( $action_source !== '' ) {
			$urlParameters[ 'action_source' ] = $action_source;
		}

		return $title->getFullURL( $urlParameters );
	}

	/**
	 * Serialize the given value
	 * @param mixed $value
	 */
	public static function serialize( $value ): string {
		return serialize( $value );
	}

	/**
	 * Deserialize the given string
	 * @return mixed
	 */
	public static function deserialize( string $str, array $opts = [ 'allowed_classes' => false ] ) {
		return unserialize( $str, $opts );
	}

	public static function getVersion(): string {
		// Avoid parsing JSON multiple time per request
		static $version = null;
		$version ??= json_decode( file_get_contents( __DIR__ . '../../../extension.json' ) )->version;
		return $version;
	}

	/**
	 * Checks if the namespace that the title belongs to allows subpages
	 *
	 * @internal - For internal use only
	 * @param Title $title
	 * @return bool
	 */
	public static function allowsSubpages( Title $title ): bool {
		$mwInstance = MediaWikiServices::getInstance();
		$namespaceInfo = $mwInstance->getNamespaceInfo();
		return $namespaceInfo->hasSubpages( $title->getNamespace() );
	}

	/**
	 * Checks whether a language code is supported for translation at the wiki level.
	 * Note that it is possible that message groups define other language codes which
	 * are not supported by the wiki, in which case this function would return false
	 * for those.
	 */
	public static function isSupportedLanguageCode( string $code ): bool {
		$all = self::getLanguageNames( LanguageNameUtils::AUTONYMS );
		return isset( $all[ $code ] );
	}

	public static function getTextFromTextContent( ?Content $content ): string {
		if ( !$content ) {
			throw new UnexpectedValueException( 'Expected $content to be TextContent, got null instead.' );
		}

		if ( $content instanceof TextContent ) {
			return $content->getText();
		}

		throw new UnexpectedValueException( 'Expected $content to be TextContent, but got ' . get_class( $content ) );
	}

	/**
	 * Returns all translations of a given message.
	 * @param MessageHandle $handle Language code is ignored.
	 * @return array ( string => array ( string, string ) ) Tuples of page
	 * text and last author indexed by page name.
	 */
	public static function getTranslations( MessageHandle $handle ): array {
		$namespace = $handle->getTitle()->getNamespace();
		$base = $handle->getKey();

		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();

		$titles = $dbr->newSelectQueryBuilder()
			->select( 'page_title' )
			->from( 'page' )
			->where( [
				'page_namespace' => $namespace,
				'page_title ' . $dbr->buildLike( "$base/", $dbr->anyString() ),
			] )
			->caller( __METHOD__ )
			->orderBy( 'page_title' )
			->fetchFieldValues();

		if ( $titles === [] ) {
			return [];
		}

		return self::getContents( $titles, $namespace );
	}

	public static function isTranslationPage( MessageHandle $handle ): bool {
		// FIXME: A lot of this code is similar to TranslatablePage::isTranslationPage.
		// See if they can be merged
		// The major difference is that this method does not run a database query to check if
		// the page is marked.
		$key = $handle->getKey();
		$languageCode = $handle->getCode();
		if ( $key === '' || $languageCode === '' ) {
			return false;
		}

		$baseTitle = Title::makeTitle( $handle->getTitle()->getNamespace(), $key );
		if ( !TranslatablePage::isSourcePage( $baseTitle ) ) {
			return false;
		}

		static $codes = null;
		$codes ??= self::getLanguageNames( LanguageNameUtils::AUTONYMS );

		return !$handle->isDoc() && isset( $codes[ $languageCode ] );
	}
}

class_alias( Utilities::class, 'TranslateUtils' );
