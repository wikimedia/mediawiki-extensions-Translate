<?php
/**
 * This file contains classes with static helper functions for other classes.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2007-2013 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Essentially random collection of helper functions, similar to GlobalFunctions.php.
 */
class TranslateUtils {
	/**
	 * Does quick normalisation of message name so that in can be looked from the
	 * database.
	 * @param string $message Name of the message
	 * @param string $code Language code in lower case and with dash as delimieter
	 * @return string The normalised title as a string.
	 */
	public static function title( $message, $code ) {
		global $wgContLang;

		// Cache some amount of titles for speed.
		static $cache = array();

		if ( !isset( $cache[$message] ) ) {
			$cache[$message] = $wgContLang->ucfirst( $message );
		}

		if ( $code ) {
			return $cache[$message] . '/' . $code;
		} else {
			return $cache[$message];
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

		return array( $key, $code );
	}

	/**
	 * Loads page content *without* side effects.
	 * @param string $key Message key.
	 * @param string $language Language code.
	 * @param int $namespace Namespace number.
	 * @return string|null The contents or null.
	 */
	public static function getMessageContent( $key, $language, $namespace = NS_MEDIAWIKI ) {
		$title = self::title( $key, $language );
		$data = self::getContents( array( $title ), $namespace );

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
		$dbr = wfGetDB( DB_SLAVE );
		$rows = $dbr->select( array( 'page', 'revision', 'text' ),
			array( 'page_title', 'old_text', 'old_flags', 'rev_user_text' ),
			array(
				'page_namespace' => $namespace,
				'page_latest=rev_id',
				'rev_text_id=old_id',
				'page_title' => $titles
			),
			__METHOD__
		);

		$titles = array();
		foreach ( $rows as $row ) {
			$titles[$row->page_title] = array(
				Revision::getRevisionText( $row ),
				$row->rev_user_text
			);
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
	 * @return array List of recent changes.
	 */
	public static function translationChanges( $hours = 24, $bots = false, $ns = null ) {
		global $wgTranslateMessageNamespaces;

		$dbr = wfGetDB( DB_SLAVE );
		$recentchanges = $dbr->tableName( 'recentchanges' );
		$hours = intval( $hours );
		$cutoff_unixtime = time() - ( $hours * 3600 );
		$cutoff = $dbr->timestamp( $cutoff_unixtime );

		$namespaces = $dbr->makeList( $wgTranslateMessageNamespaces );
		if ( $ns ) {
			$namespaces = $dbr->makeList( $ns );
		}

		$fields = 'rc_title, rc_timestamp, rc_user_text, rc_namespace';

		// @todo Raw SQL
		$sql = "SELECT $fields, substring_index(rc_title, '/', -1) as lang FROM $recentchanges " .
			"WHERE rc_timestamp >= '{$cutoff}' " .
			( $bots ? '' : 'AND rc_bot = 0 ' ) .
			"AND rc_namespace in ($namespaces) " .
			"ORDER BY lang ASC, rc_timestamp DESC";

		$res = $dbr->query( $sql, __METHOD__ );
		$rows = iterator_to_array( $res );
		return $rows;
	}

	/* Some other helpers for output */

	/**
	 * Returns a localised language name.
	 * @param string $code Language code.
	 * @param string $language Language code of language the the name should be in.
	 * @return string Best-effort localisation of wanted language name.
	 */
	public static function getLanguageName( $code, $language = 'en' ) {
		$languages = TranslateUtils::getLanguageNames( $language );

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
		return $selector->getHtml();
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
	 * @param string $code
	 * @return array ( language code => language name )
	 */
	public static function getLanguageNames( /*string */$code ) {
		if ( is_callable( array( 'Language', 'fetchLanguageNames' ) ) ) {
			$languageNames = Language::fetchLanguageNames( $code, 'mw' ); // since 1.20
		} elseif ( is_callable( array( 'LanguageNames', 'getNames' ) ) ) {
			$languageNames = LanguageNames::getNames( $code,
				LanguageNames::FALLBACK_NORMAL,
				LanguageNames::LIST_MW
			);
		} else {
			$languageNames = Language::getLanguageNames( false );
		}

		// Remove languages with deprecated codes (bug 35475)
		global $wgDummyLanguageCodes;

		foreach ( array_keys( $wgDummyLanguageCodes ) as $dummyLanguageCode ) {
			unset( $languageNames[$dummyLanguageCode] );
		}

		wfRunHooks( 'TranslateSupportedLanguages', array( &$languageNames, $code ) );

		return $languageNames;
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
			return array();
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
		return strtr( "$namespace:$key", " ", "_" );
	}

	/**
	 * Constructs a fieldset with contents.
	 * @param string $legend Raw html.
	 * @param string $contents Raw html.
	 * @param array $attributes Html attributes for the fieldset.
	 * @return string Html.
	 */
	public static function fieldset( $legend, $contents, $attributes = array() ) {
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

	public static function groupSelector( $default = false ) {
		$groups = MessageGroups::getAllGroups();
		$selector = new XmlSelect( 'group', 'group', $default );

		foreach ( $groups as $id => $class ) {
			if ( MessageGroups::getGroup( $id )->exists() ) {
				$selector->addOption( $class->getLabel(), $id );
			}
		}

		return $selector;
	}

	/**
	 * Adds help link with an icon to upper right corner.
	 * @param OutputPage $out
	 * @param string $to
	 * @param bool $overrideBaseUrl
	 * @since 2012-01-12
	 */
	public static function addSpecialHelpLink( OutputPage $out, $to, $overrideBaseUrl = false ) {
		$out->addModules( 'ext.translate.helplink' );
		$text = wfMessage( 'translate-gethelp' )->escaped();

		if ( $overrideBaseUrl ) {
			$helpUrl = $to;
		} else {
			$helpUrl = "//www.mediawiki.org/wiki/Special:MyLanguage/$to";
		}

		$link = Html::rawElement(
			'a',
			array(
				'href' => $helpUrl,
				'target' => '_blank'
			),
			"$text" );
		$wrapper = Html::rawElement( 'div', array( 'class' => 'mw-translate-helplink' ), $link );
		$out->addHtml( $wrapper );
	}

	/**
	 * Convenience function that handles BC with changed way of
	 * acquiring tokens via API.
	 * @param string $token
	 * @return string
	 * @since 2012-05-03
	 */
	public static function getTokenAction( $token ) {
		global $wgVersion;
		$method = "action=tokens&type=$token";
		if ( version_compare( $wgVersion, '1.20', '<' ) ) {
			$method = "action=query&prop=info&intoken=$token&titles=Token";
		}
		return $method;
	}

	/**
	 * Returns a random string that can be used as placeholder in strings.
	 * @return string
	 * @since 2012-07-31
	 */
	public static function getPlaceholder() {
		static $i = 0;
		return "\x7fUNIQ" . dechex( mt_rand( 0, 0x7fffffff ) ) . dechex( mt_rand( 0, 0x7fffffff ) ) . '-' . $i++;
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

		$formats = array();

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
}
