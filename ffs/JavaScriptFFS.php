<?php

/**
 * Generic file format support for JavaScript formatted files.
 * @ingroup FFS
 */
abstract class JavaScriptFFS extends SimpleFFS {
	public function getFileExtensions() {
		return array( '.js' );
	}

	/**
	 * Message keys format.
	 *
	 * @param $key string
	 *
	 * @return string
	 */
	abstract protected function transformKey( $key );

	/**
	 * Header of message file.
	 *
	 * @param $code string
	 * @param $authors array
	 */
	abstract protected function header( $code, $authors );

	/**
	 * Footer of message file.
	 */
	abstract protected function footer();

	/**
	 * @param $data array
	 * @return array Parsed data.
	 */
	public function readFromVariable( $data ) {
		/* Parse authors list */
		$authors = preg_replace( "#/\* Translators\:\n(.*?)\n \*/(.*)#s", '$1', $data );
		if ( $authors === $data ) {
			$authors = array();
		} else {
			$authors = explode( "\n", $authors );
			$count = count( $authors );
			for ( $i = 0; $i < $count; $i++ ) {
				// Each line should look like " *  - Translatorname"
				$authors[$i] = substr( $authors[$i], 6 );
			}
		}

		/* Pre-processing of messages */

		/**
		 * Find the start and end of the data section (enclosed in curly braces).
		 */
		$dataStart = strpos( $data, '{' );
		$dataEnd = strrpos( $data, '}' );

		/**
		 * Strip everything outside of the data section.
		 */
		$data = substr( $data, $dataStart + 1, $dataEnd - $dataStart - 1 );

		/**
		 * Strip comments.
		 */
		$data = preg_replace( '#^(\s*?)//(.*?)$#m', '', $data );

		/**
		 * Replace message endings with double quotes.
		 */
		$data = preg_replace( "#\'\,\n#", "\",\n", $data );

		/**
		 * Strip excess whitespace.
		 */
		$data = trim( $data );

		/**
		 * Per-key message processing.
		 */

		/**
		 * Break in to segments.
		 */
		$data = explode( "\",\n", $data );

		$messages = array();
		foreach ( $data as $segment ) {
			/**
			 * Add back trailing quote, removed by explosion.
			 */
			$segment .= '"';

			/**
			 * Concatenate separated strings.
			 */
			$segment = str_replace( '"+', '" +', $segment );
			$segment = explode( '" +', $segment );
			$count = count( $segment );
			for ( $i = 0; $i < $count; $i++ ) {
				$segment[$i] = ltrim( ltrim( $segment[$i] ), '"' );
			}
			$segment = implode( $segment );

			/**
			 * Remove line breaks between message keys and messages.
			 */
			$segment = preg_replace( "#\:(\s+)[\\\"\']#", ': "', $segment );

			/**
			 * Break in to key and message.
			 */
			$segments = explode( ': "', $segment );

			/**
			 * Strip excess whitespace from key and value, then quotation marks.
			 */
			$key = trim( trim( $segments[0] ), "'\"" );
			$value = trim( trim( $segments[1] ), "'\"" );

			/**
			 * Unescape any JavaScript string syntax and append to message array.
			 */
			$messages[$key] = self::unescapeJsString( $value );
		}

		$messages = $this->group->getMangler()->mangle( $messages );

		return array(
			'AUTHORS' => $authors,
			'MESSAGES' => $messages
		);
	}

	/**
	 * @param $collection MessageCollection
	 * @return string
	 */
	public function writeReal( MessageCollection $collection ) {
		$header = $this->header( $collection->code, $collection->getAuthors() );

		$mangler = $this->group->getMangler();

		/**
		 * Get and write messages.
		 */
		$body = '';
		/**
		 * @var TMessage $message
		 */
		foreach ( $collection as $message ) {
			if ( strlen( $message->translation() ) === 0 ) {
				continue;
			}

			$key = $mangler->unmangle( $message->key() );
			$key = $this->transformKey( self::escapeJsString( $key ) );

			$translation = self::escapeJsString( $message->translation() );

			$body .= "\t{$key}: \"{$translation}\",\n";
		}

		if ( strlen( $body ) === 0 ) {
			return false;
		}

		/**
		 * Strip last comma, re-add trailing newlines.
		 */
		$body = substr( $body, 0, -2 );
		$body .= "\n";

		return $header . $body . $this->footer();
	}

	/**
	 * @param $authors array
	 * @return string
	 */
	protected function authorsList( $authors ) {
		if ( count( $authors ) === 0 ) {
			return '';
		}

		$authorsList = '';
		foreach ( $authors as $author ) {
			$authorsList .= " *  - $author\n";
		}

		// Remove trailing newline, and return.
		return substr( " * Translators:\n$authorsList", 0, -1 );
	}

	// See ECMA 262 section 7.8.4 for string literal format
	private static $pairs = array(
		"\\" => "\\\\",
		"\"" => "\\\"",
		"'" => "\\'",
		"\n" => "\\n",
		"\r" => "\\r",

		// To avoid closing the element or CDATA section.
		'<' => "\\x3c",
		'>' => "\\x3e",

		// To avoid any complaints about bad entity refs.
		'&' => "\\x26",

		/*
		 * Work around https://bugzilla.mozilla.org/show_bug.cgi?id=274152
		 * Encode certain Unicode formatting chars so affected
		 * versions of Gecko do not misinterpret our strings;
		 * this is a common problem with Farsi text.
		 */
		"\xe2\x80\x8c" => "\\u200c", // ZERO WIDTH NON-JOINER
		"\xe2\x80\x8d" => "\\u200d", // ZERO WIDTH JOINER
	);

	/**
	 * @param $string string
	 * @return string
	 */
	protected static function escapeJsString( $string ) {
		return strtr( $string, self::$pairs );
	}

	/**
	 * @param $string string
	 * @return string
	 */
	protected static function unescapeJsString( $string ) {
		return strtr( $string, array_flip( self::$pairs ) );
	}
}

/**
 * File format support for Shapado, which uses JavaScript based format.
 * @ingroup FFS
 */
class ShapadoJsFFS extends JavaScriptFFS {

	/**
	 * @param $key string
	 *
	 * @return string
	 */
	protected function transformKey( $key ) {
		return $key;
	}

	/**
	 * @param $code string
	 * @param $authors array
	 * @return string
	 */
	protected function header( $code, $authors ) {
		global $wgSitename;

		$name = TranslateUtils::getLanguageName( $code );
		$native = TranslateUtils::getLanguageName( $code, $code );
		$authorsList = $this->authorsList( $authors );

		/** @cond doxygen_bug */
		return <<<EOT
/** Messages for $name ($native)
 *  Exported from $wgSitename
 *
{$authorsList}
 */

var I18n = {

EOT;
		/** @endcond */
	}

	/**
	 * @return string
	 */
	protected function footer() {
		return "};\n\n";
	}
}
