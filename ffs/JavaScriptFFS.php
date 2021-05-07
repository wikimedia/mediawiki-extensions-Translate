<?php

/**
 * Generic file format support for JavaScript formatted files.
 * @ingroup FFS
 */
abstract class JavaScriptFFS extends SimpleFFS {
	public function getFileExtensions() {
		return [ '.js' ];
	}

	/**
	 * Message keys format.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	abstract protected function transformKey( $key );

	/**
	 * Header of message file.
	 *
	 * @param string $code
	 * @param string[] $authors
	 */
	abstract protected function header( $code, array $authors );

	/**
	 * Footer of message file.
	 */
	abstract protected function footer();

	/**
	 * @param string $data
	 * @return array Parsed data.
	 */
	public function readFromVariable( $data ) {
		/* Parse authors list */
		$authors = preg_replace( "#/\* Translators\:\n(.*?)\n \*/(.*)#s", '$1', $data );
		if ( $authors === $data ) {
			$authors = [];
		} else {
			$authors = array_map(
				static function ( $author ) {
					// Each line should look like " *  - Translatorname"
					return substr( $author, 6 );
				},
				explode( "\n", $authors )
			);
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

		$messages = [];
		foreach ( $data as $segment ) {
			/**
			 * Add back trailing quote, removed by explosion.
			 */
			$segment .= '"';

			/**
			 * Concatenate separated strings.
			 */
			$segment = preg_replace( '/"\s*\+\s*"/', '', $segment );

			list( $key, $value ) = preg_split( '/:\s*[\'"]/', $segment, 2 );

			/**
			 * Strip excess whitespace from key and value, then quotation marks.
			 */
			$key = trim( trim( $key ), "'\"" );
			$value = trim( trim( $value ), "'\"" );

			/**
			 * Unescape any JavaScript string syntax and append to message array.
			 */
			$messages[$key] = self::unescapeJsString( $value );
		}

		$messages = $this->group->getMangler()->mangleArray( $messages );

		return [
			'AUTHORS' => $authors,
			'MESSAGES' => $messages
		];
	}

	/**
	 * @param MessageCollection $collection
	 * @return string
	 */
	public function writeReal( MessageCollection $collection ) {
		$header = $this->header( $collection->code, $collection->getAuthors() );

		$mangler = $this->group->getMangler();

		/**
		 * Get and write messages.
		 */
		$body = '';
		/** @var TMessage $message */
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
	 * @param string[] $authors
	 * @return string
	 */
	protected function authorsList( array $authors ) {
		if ( $authors === [] ) {
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
	private static $pairs = [
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
	];

	/**
	 * @param string $string
	 * @return string
	 */
	protected static function escapeJsString( $string ) {
		return strtr( $string, self::$pairs );
	}

	/**
	 * @param string $string
	 * @return string
	 */
	protected static function unescapeJsString( $string ) {
		return strtr( $string, array_flip( self::$pairs ) );
	}
}
