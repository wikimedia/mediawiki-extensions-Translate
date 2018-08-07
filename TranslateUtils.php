<?php
/**
 * This file contains classes with static helper functions for other classes.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;

/**
 * Essentially random collection of helper functions, similar to GlobalFunctions.php.
 */
class TranslateUtils {
	/**
	 * Does quick normalisation of message name so that in can be looked from the
	 * database.
	 * @param string $message Name of the message
	 * @param string $code Language code in lower case and with dash as delimiter
	 * @param int $ns Namespace constant
	 * @return string The normalised title as a string.
	 */
	public static function title( $message, $code, $ns = NS_MEDIAWIKI ) {
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
	public static function figureMessage( $text ) {
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
	public static function getMessageContent( $key, $language, $namespace = NS_MEDIAWIKI ) {
		$title = self::title( $key, $language, $namespace );
		$data = self::getContents( [ $title ], $namespace );

		return isset( $data[$title][0] ) ? $data[$title][0] : null;
	}

	/**
	 * Fetches contents for pagenames in given namespace without side effects.
	 *
	 * @param string|string[] $titles Database page names.
	 * @param int $namespace The number of the namespace.
	 * @return array ( string => array ( string, string ) ) Tuples of page
	 * text and last author indexed by page name.
	 */
	public static function getContents( $titles, $namespace ) {
		$dbr = wfGetDB( DB_REPLICA );

		if ( class_exists( ActorMigration::class ) ) {
			$actorQuery = ActorMigration::newMigration()->getJoin( 'rev_user' );
		} else {
			$actorQuery = [
				'tables' => [],
				'fields' => [ 'rev_user_text' => 'rev_user_text' ],
				'joins' => [],
			];
		}

		$rows = $dbr->select( [ 'page', 'revision', 'text' ] + $actorQuery['tables'],
			[
				'page_title', 'old_text', 'old_flags',
				'rev_user_text' => $actorQuery['fields']['rev_user_text']
			],
			[
				'page_namespace' => $namespace,
				'page_title' => $titles
			],
			__METHOD__,
			[],
			[
				'revision' => [ 'JOIN', 'page_latest=rev_id' ],
				'text' => [ 'JOIN', 'rev_text_id=old_id' ],
			] + $actorQuery['joins']
		);

		$titles = [];
		foreach ( $rows as $row ) {
			$titles[$row->page_title] = [
				Revision::getRevisionText( $row ),
				$row->rev_user_text
			];
		}
		$rows->free();

		return $titles;
	}

	/**
	 * Fetches recent changes for titles in given namespaces
	 *
	 * @param int $hours Number of hours.
	 * @param bool $bots Should bot edits be included.
	 * @param null|int[] $ns List of namespace IDs.
	 * @param string[] $extraFields List of extra columns to fetch.
	 * @return array List of recent changes.
	 */
	public static function translationChanges(
		$hours = 24, $bots = false, $ns = null, array $extraFields = []
	) {
		global $wgTranslateMessageNamespaces;

		$dbr = wfGetDB( DB_REPLICA );

		if ( class_exists( ActorMigration::class ) ) {
			$actorQuery = ActorMigration::newMigration()->getJoin( 'rc_user' );
		} else {
			$actorQuery = [
				'tables' => [],
				'fields' => [ 'rc_user_text' => 'rc_user_text' ],
				'joins' => [],
			];
		}

		$hours = (int)$hours;
		$cutoff_unixtime = time() - ( $hours * 3600 );
		$cutoff = $dbr->timestamp( $cutoff_unixtime );

		$conds = [
			'rc_timestamp >= ' . $dbr->addQuotes( $cutoff ),
			'rc_namespace' => $ns ?: $wgTranslateMessageNamespaces,
		];
		if ( $bots ) {
			$conds['rc_bot'] = 0;
		}

		$res = $dbr->select(
			[ 'recentchanges' ] + $actorQuery['tables'],
			array_merge( [
				'rc_namespace', 'rc_title', 'rc_timestamp',
				'rc_user_text' => $actorQuery['fields']['rc_user_text'],
			], $extraFields ),
			$conds,
			__METHOD__,
			[],
			$actorQuery['joins']
		);
		$rows = iterator_to_array( $res );

		// Calculate 'lang', then sort by it and rc_timestamp
		foreach ( $rows as &$row ) {
			$pos = strrpos( $row->rc_title, '/' );
			$row->lang = $pos === false ? $row->rc_title : substr( $row->rc_title, $pos + 1 );
		}
		unset( $row );

		usort( $rows, function ( $a, $b ) {
			$x = strcmp( $a->lang, $b->lang );
			if ( !$x ) {
				// descending order
				$x = strcmp(
					wfTimestamp( TS_MW, $b->rc_timestamp ),
					wfTimestamp( TS_MW, $a->rc_timestamp )
				);
			}
			return $x;
		} );

		return $rows;
	}

	/* Some other helpers for output */

	/**
	 * Returns a localised language name.
	 * @param string $code Language code.
	 * @param null|string $language Language code of the language that the name should be in.
	 * @return string Best-effort localisation of wanted language name.
	 */
	public static function getLanguageName( $code, $language = 'en' ) {
		$languages = self::getLanguageNames( $language );

		if ( isset( $languages[$code] ) ) {
			return $languages[$code];
		} else {
			return $code;
		}
	}

	/**
	 * Returns a language selector.
	 * @param string $language Language code of the language the names should be localised to.
	 * @param string $selectedId The language code that is selected by default.
	 * @return string
	 */
	public static function languageSelector( $language, $selectedId ) {
		$selector = self::getLanguageSelector( $language );
		$selector->setDefault( $selectedId );
		$selector->setAttribute( 'id', 'language' );
		$selector->setAttribute( 'name', 'language' );

		return $selector->getHTML();
	}

	/**
	 * Standard language selector in Translate extension.
	 * @param string $language Language code of the language the names should be localised to.
	 * @param bool $labelOption
	 * @return XmlSelect
	 */
	public static function getLanguageSelector( $language, $labelOption = false ) {
		$languages = self::getLanguageNames( $language );
		ksort( $languages );

		$selector = new XmlSelect();
		if ( $labelOption !== false ) {
			$selector->addOption( $labelOption, '-' );
		}

		foreach ( $languages as $code => $name ) {
			$selector->addOption( "$code - $name", $code );
		}

		return $selector;
	}

	/**
	 * Get translated language names for the languages generally supported for
	 * translation in the current wiki. Message groups can have further
	 * exclusions.
	 * @param null|string $code
	 * @return array ( language code => language name )
	 */
	public static function getLanguageNames( $code ) {
		$languageNames = Language::fetchLanguageNames( $code );

		// Remove languages with deprecated codes (bug T37475)
		global $wgDummyLanguageCodes;

		foreach ( array_keys( $wgDummyLanguageCodes ) as $dummyLanguageCode ) {
			unset( $languageNames[$dummyLanguageCode] );
		}

		Hooks::run( 'TranslateSupportedLanguages', [ &$languageNames, $code ] );

		return $languageNames;
	}

	/**
	 * Get the normalised IETF language tag.
	 * @param string $code The language code.
	 * @deprecated This provides backward compatibility; replace with
	 *   \LanguageCode::bcp47() once MW 1.30 is no longer supported.
	 */
	public static function bcp47( $code ) {
		if ( !is_callable( [ 'LanguageCode', 'bcp47' ] ) ) {
			return wfBCP47( $code );
		}
		return LanguageCode::bcp47( $code );
	}

	/**
	 * Returns the primary group message belongs to.
	 * @param int $namespace
	 * @param string $key
	 * @return string|null Group id or null.
	 */
	public static function messageKeyToGroup( $namespace, $key ) {
		$groups = self::messageKeyToGroups( $namespace, $key );

		return count( $groups ) ? $groups[0] : null;
	}

	/**
	 * Returns the all the groups message belongs to.
	 * @param int $namespace
	 * @param string $key
	 * @return string[] Possibly empty list of group ids.
	 */
	public static function messageKeyToGroups( $namespace, $key ) {
		$mi = MessageIndex::singleton()->retrieve();
		$normkey = self::normaliseKey( $namespace, $key );

		if ( isset( $mi[$normkey] ) ) {
			return (array)$mi[$normkey];
		} else {
			return [];
		}
	}

	/**
	 * Converts page name and namespace to message index format.
	 * @param int $namespace
	 * @param string $key
	 * @return string
	 */
	public static function normaliseKey( $namespace, $key ) {
		$key = lcfirst( $key );

		return strtr( "$namespace:$key", ' ', '_' );
	}

	/**
	 * Constructs a fieldset with contents.
	 * @param string $legend Raw html.
	 * @param string $contents Raw html.
	 * @param array $attributes Html attributes for the fieldset.
	 * @return string Html.
	 */
	public static function fieldset( $legend, $contents, array $attributes = [] ) {
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
	 * @param string $msg Plain text string.
	 * @return string Text string that is ready for outputting.
	 */
	public static function convertWhiteSpaceToHTML( $msg ) {
		$msg = htmlspecialchars( $msg );
		$msg = preg_replace( '/^ /m', '&#160;', $msg );
		$msg = preg_replace( '/ $/m', '&#160;', $msg );
		$msg = preg_replace( '/  /', '&#160; ', $msg );
		$msg = str_replace( "\n", '<br />', $msg );

		return $msg;
	}

	/**
	 * Construct the web address to given asset.
	 * @param string $path Path to the resource relative to extensions root directory.
	 * @return string Full or partial web path.
	 */
	public static function assetPath( $path ) {
		global $wgExtensionAssetsPath;

		return "$wgExtensionAssetsPath/Translate/$path";
	}

	/**
	 * Gets the path for cache files
	 * @param string $filename
	 * @return string Full path.
	 * @throws MWException If cache directory is not configured.
	 */
	public static function cacheFile( $filename ) {
		global $wgTranslateCacheDirectory, $wgCacheDirectory;

		if ( $wgTranslateCacheDirectory !== false ) {
			$dir = $wgTranslateCacheDirectory;
		} elseif ( $wgCacheDirectory !== false ) {
			$dir = $wgCacheDirectory;
		} else {
			throw new MWException( "\$wgCacheDirectory must be configured" );
		}

		return "$dir/$filename";
	}

	/**
	 * Returns a random string that can be used as placeholder in strings.
	 * @return string
	 * @since 2012-07-31
	 */
	public static function getPlaceholder() {
		static $i = 0;

		return "\x7fUNIQ" . dechex( mt_rand( 0, 0x7fffffff ) ) .
			dechex( mt_rand( 0, 0x7fffffff ) ) . '-' . $i++;
	}

	/**
	 * Get URLs for icons if available.
	 * @param MessageGroup $g
	 * @param int $size Length of the edge of a bounding box to fit the icon.
	 * @return null|array
	 * @since 2013-04-01
	 */
	public static function getIcon( MessageGroup $g, $size ) {
		$icon = $g->getIcon();
		if ( substr( $icon, 0, 7 ) !== 'wiki://' ) {
			return null;
		}

		$formats = [];

		$filename = substr( $icon, 7 );
		$file = wfFindFile( $filename );
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
	 * Parses list of language codes to an array.
	 * @param string $codes Comma separated list of language codes. "*" for all.
	 * @return string[] Language codes.
	 */
	public static function parseLanguageCodes( $codes ) {
		$langs = array_map( 'trim', explode( ',', $codes ) );
		if ( $langs[0] === '*' ) {
			$languages = Language::fetchLanguageNames();
			ksort( $languages );
			$langs = array_keys( $languages );
		}

		return $langs;
	}

	/**
	 * Get a DB handle suitable for read and read-for-write cases
	 *
	 * @return \Wikimedia\Rdbms\IDatabase Master for HTTP POST, CLI, DB already changed;
	 *  slave otherwise
	 */
	public static function getSafeReadDB() {
		$lb = MediaWikiServices::getInstance()->getDBLoadBalancer();
		// Parsing APIs need POST for payloads but are read-only, so avoid spamming
		// the master then. No good way to check this at the moment...
		if ( PageTranslationHooks::$renderingContext ) {
			$index = DB_REPLICA;
		} else {
			$index = (
				PHP_SAPI === 'cli' ||
				RequestContext::getMain()->getRequest()->wasPosted() ||
				$lb->hasOrMadeRecentMasterChanges()
			) ? DB_MASTER : DB_REPLICA;
		}

		return $lb->getConnection( $index );
	}

	/**
	 * Get an URL that points to an editor for this message handle.
	 * @param MessageHandle $handle
	 * @return string Domain relative URL
	 * @since 2017.10
	 */
	public static function getEditorUrl( MessageHandle $handle ) {
		if ( !$handle->isValid() ) {
			return $handle->getTitle()->getLocalURL( [ 'action' => 'edit' ] );
		}

		$title = self::getSpecialPage( 'Translate' )->getPageTitle();
		return $title->getLocalURL( [
			'showMessage' => $handle->getInternalKey(),
			'group' => $handle->getGroup()->getId(),
			'language' => $handle->getCode(),
		] );
	}

	/**
	 * Compatibility for pre-1.32, when SpecialPageFactory methods were static.
	 *
	 * @see SpecialPageFactory::resolveAlias
	 * @param string $text
	 * @return array
	 */
	public static function resolveSpecialPageAlias( $text ) : array {
		if ( method_exists( MediaWikiServices::class, 'getSpecialPageFactory' ) ) {
			return MediaWikiServices::getInstance()->getSpecialPageFactory()->resolveAlias( $text );
		}
		return SpecialPageFactory::resolveAlias( $text );
	}

	/**
	 * Compatibility for pre-1.32, when SpecialPageFactory methods were static.
	 *
	 * @see SpecialPageFactory::getPage
	 * @param string $name
	 * @return SpecialPage|null
	 */
	public static function getSpecialPage( $name ) {
		if ( method_exists( MediaWikiServices::class, 'getSpecialPageFactory' ) ) {
			return MediaWikiServices::getInstance()->getSpecialPageFactory()->getPage( $name );
		}
		return SpecialPageFactory::getPage( $name );
	}
}
