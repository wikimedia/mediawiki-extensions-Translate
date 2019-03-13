<?php
/**
 * Gettext file format handler for both old and new style message groups.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2010, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @file
 */

/**
 * Identifies Gettext plural exceptions.
 */
class GettextPluralException extends MWException {
}

/**
 * New-style FFS class that implements support for gettext file format.
 * @ingroup FFS
 */
class GettextFFS extends SimpleFFS implements MetaYamlSchemaExtender {
	public function supportsFuzzy() {
		return 'yes';
	}

	public function getFileExtensions() {
		return [ '.pot', '.po' ];
	}

	protected $offlineMode = false;

	/**
	 * @param bool $value
	 */
	public function setOfflineMode( $value ) {
		$this->offlineMode = $value;
	}

	/**
	 * @param string $data
	 * @return array
	 */
	public function readFromVariable( $data ) {
		# Authors first
		$matches = [];
		preg_match_all( '/^#\s*Author:\s*(.*)$/m', $data, $matches );
		$authors = $matches[1];

		# Then messages and everything else
		$parsedData = $this->parseGettext( $data );
		$parsedData['AUTHORS'] = $authors;

		foreach ( $parsedData['MESSAGES'] as $key => $value ) {
			if ( $value === '' ) {
				unset( $parsedData['MESSAGES'][$key] );
			}
		}

		return $parsedData;
	}

	public function parseGettext( $data ) {
		$mangler = $this->group->getMangler();
		$useCtxtAsKey = isset( $this->extra['CtxtAsKey'] ) && $this->extra['CtxtAsKey'];
		$keyAlgorithm = 'simple';
		if ( isset( $this->extra['keyAlgorithm'] ) ) {
			$keyAlgorithm = $this->extra['keyAlgorithm'];
		}

		return self::parseGettextData( $data, $useCtxtAsKey, $mangler, $keyAlgorithm );
	}

	/**
	 * Parses gettext file as string into internal representation.
	 * @param string $data
	 * @param bool $useCtxtAsKey Whether to create message keys from the context
	 * or use msgctxt (non-standard po-files)
	 * @param StringMangler $mangler
	 * @param string $keyAlgorithm Key generation algorithm, see generateKeyFromItem
	 * @throws MWException
	 * @return array
	 */
	public static function parseGettextData( $data, $useCtxtAsKey, $mangler, $keyAlgorithm ) {
		$potmode = false;

		// Normalise newlines, to make processing easier
		$data = str_replace( "\r\n", "\n", $data );

		/* Delimit the file into sections, which are separated by two newlines.
		 * We are permissive and accept more than two. This parsing method isn't
		 * efficient wrt memory, but was easy to implement */
		$sections = preg_split( '/\n{2,}/', $data );

		/* First one isn't an actual message. We'll handle it specially below */
		$headerSection = array_shift( $sections );
		/* Since this is the header section, we are only interested in the tags
		 * and msgid is empty. Somewhere we should extract the header comments
		 * too */
		$match = self::expectKeyword( 'msgstr', $headerSection );
		if ( $match !== null ) {
			$headerBlock = self::formatForWiki( $match, 'trim' );
			$headers = self::parseHeaderTags( $headerBlock );

			// Check for pot-mode by checking if the header is fuzzy
			$flags = self::parseFlags( $headerSection );
			if ( in_array( 'fuzzy', $flags, true ) ) {
				$potmode = true;
			}
		} else {
			throw new MWException( "Gettext file header was not found:\n\n$data" );
		}

		$template = [];
		$messages = [];

		// Extract some metadata from headers for easier use
		$metadata = [];
		if ( isset( $headers['X-Language-Code'] ) ) {
			$metadata['code'] = $headers['X-Language-Code'];
		}

		if ( isset( $headers['X-Message-Group'] ) ) {
			$metadata['group'] = $headers['X-Message-Group'];
		}

		/* At this stage we are only interested how many plurals forms we should
		 * be expecting when parsing the rest of this file. */
		$pluralCount = false;
		if ( isset( $headers['Plural-Forms'] ) &&
			preg_match( '/nplurals=([0-9]+).*;/', $headers['Plural-Forms'], $matches )
		) {
			$pluralCount = $metadata['plural'] = $matches[1];
		}

		// Then parse the messages
		foreach ( $sections as $section ) {
			$item = self::parseGettextSection( $section, $pluralCount, $metadata );
			if ( $item === false ) {
				continue;
			}

			if ( $useCtxtAsKey ) {
				if ( !isset( $item['ctxt'] ) ) {
					error_log( "ctxt missing for: $section" );
					continue;
				}
				$key = $item['ctxt'];
			} else {
				$key = self::generateKeyFromItem( $item, $keyAlgorithm );
			}

			$key = $mangler->mangle( $key );
			$messages[$key] = $potmode ? $item['id'] : $item['str'];
			$template[$key] = $item;
		}

		return [
			'MESSAGES' => $messages,
			'TEMPLATE' => $template,
			'METADATA' => $metadata,
			'HEADERS' => $headers
		];
	}

	public static function parseGettextSection( $section, $pluralCount, &$metadata ) {
		if ( trim( $section ) === '' ) {
			return false;
		}

		/* These inactive sections are of no interest to us. Multiline mode
		 * is needed because there may be flags or other annoying stuff
		 * before the commented out sections.
		 */
		if ( preg_match( '/^#~/m', $section ) ) {
			return false;
		}

		$item = [
			'ctxt' => false,
			'id' => '',
			'str' => '',
			'flags' => [],
			'comments' => [],
		];

		$match = self::expectKeyword( 'msgid', $section );
		if ( $match !== null ) {
			$item['id'] = self::formatForWiki( $match );
		} else {
			throw new MWException( "Unable to parse msgid:\n\n$section" );
		}

		$match = self::expectKeyword( 'msgctxt', $section );
		if ( $match !== null ) {
			$item['ctxt'] = self::formatForWiki( $match );
		}

		$pluralMessage = false;
		$match = self::expectKeyword( 'msgid_plural', $section );
		if ( $match !== null ) {
			$pluralMessage = true;
			$plural = self::formatForWiki( $match );
			$item['id'] = "{{PLURAL:GETTEXT|{$item['id']}|$plural}}";
		}

		if ( $pluralMessage ) {
			$pluralMessageText = self::processGettextPluralMessage( $pluralCount, $section );

			// Keep the translation empty if no form has translation
			if ( $pluralMessageText !== '' ) {
				$item['str'] = $pluralMessageText;
			}
		} else {
			$match = self::expectKeyword( 'msgstr', $section );
			if ( $match !== null ) {
				$item['str'] = self::formatForWiki( $match );
			} else {
				throw new MWException( "Unable to parse msgstr:\n\n$section" );
			}
		}

		// Parse flags
		$flags = self::parseFlags( $section );
		foreach ( $flags as $key => $flag ) {
			if ( $flag === 'fuzzy' ) {
				$item['str'] = TRANSLATE_FUZZY . $item['str'];
				unset( $flags[$key] );
			}
		}
		$item['flags'] = $flags;

		// Rest of the comments
		$matches = [];
		if ( preg_match_all( '/^#(.?) (.*)$/m', $section, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				if ( $match[1] !== ',' && strpos( $match[1], '[Wiki]' ) !== 0 ) {
					$item['comments'][$match[1]][] = $match[2];
				}
			}
		}

		return $item;
	}

	public static function processGettextPluralMessage( $pluralCount, $section ) {
		$actualForms = [];

		for ( $i = 0; $i < $pluralCount; $i++ ) {
			$match = self::expectKeyword( "msgstr\\[$i\\]", $section );

			if ( $match !== null ) {
				$actualForms[] = self::formatForWiki( $match );
			} else {
				$actualForms[] = '';
				error_log( "Plural $i not found, expecting total of $pluralCount for $section" );
			}
		}

		if ( array_sum( array_map( 'strlen', $actualForms ) ) > 0 ) {
			return '{{PLURAL:GETTEXT|' . implode( '|', $actualForms ) . '}}';
		} else {
			return '';
		}
	}

	public static function parseFlags( $section ) {
		$matches = [];
		if ( preg_match( '/^#,(.*)$/mu', $section, $matches ) ) {
			return array_map( 'trim', explode( ',', $matches[1] ) );
		} else {
			return [];
		}
	}

	public static function expectKeyword( $name, $section ) {
		/* Catches the multiline textblock that comes after keywords msgid,
		 * msgstr, msgid_plural, msgctxt.
		 */
		$poformat = '".*"\n?(^".*"$\n?)*';

		$matches = [];
		if ( preg_match( "/^$name\s($poformat)/mx", $section, $matches ) ) {
			return $matches[1];
		} else {
			return null;
		}
	}

	/**
	 * Generates unique key for each message. Changing this WILL BREAK ALL
	 * existing pages!
	 * @param array $item As returned by parseGettextSection
	 * @param string $algorithm Algorithm used to generate message keys: simple or legacy
	 * @return string
	 */
	public static function generateKeyFromItem( array $item, $algorithm = 'simple' ) {
		$lang = Language::factory( 'en' );

		if ( $item['ctxt'] === '' ) {
			/* Messages with msgctxt as empty string should be different
			 * from messages without any msgctxt. To avoid BC break make
			 * the empty ctxt a special case */
			$hash = sha1( $item['id'] . 'MSGEMPTYCTXT' );
		} else {
			$hash = sha1( $item['ctxt'] . $item['id'] );
		}

		if ( $algorithm === 'simple' ) {
			$hash = substr( $hash, 0, 6 );
			if ( !is_callable( [ $lang, 'truncateForDatabase' ] ) ) {
				// Backwards compatibility code; remove once MW 1.30 is
				// no longer supported (aka once MW 1.33 is released)
				$snippet = $lang->truncate( $item['id'], 30, '' );
			} else {
				$snippet = $lang->truncateForDatabase( $item['id'], 30, '' );
			}
			$snippet = str_replace( ' ', '_', trim( $snippet ) );
		} else { // legacy
			global $wgLegalTitleChars;
			$snippet = $item['id'];
			$snippet = preg_replace( "/[^$wgLegalTitleChars]/", ' ', $snippet );
			$snippet = preg_replace( "/[:&%\/_]/", ' ', $snippet );
			$snippet = preg_replace( '/ {2,}/', ' ', $snippet );
			if ( !is_callable( [ $lang, 'truncateForDatabase' ] ) ) {
				// Backwards compatibility code; remove once MW 1.30 is
				// no longer supported (aka once MW 1.33 is released)
				$snippet = $lang->truncate( $snippet, 30, '' );
			} else {
				$snippet = $lang->truncateForDatabase( $snippet, 30, '' );
			}
			$snippet = str_replace( ' ', '_', trim( $snippet ) );
		}

		return "$hash-$snippet";
	}

	/**
	 * This parses the Gettext text block format. Since trailing whitespace is
	 * not allowed in MediaWiki pages, the default action is to append
	 * \-character at the end of the message. You can also choose to ignore it
	 * and use the trim action instead.
	 * @param string $data
	 * @param string $whitespace
	 * @throws MWException
	 * @return string
	 */
	public static function formatForWiki( $data, $whitespace = 'mark' ) {
		$quotePattern = '/(^"|"$\n?)/m';
		$data = preg_replace( $quotePattern, '', $data );
		$data = stripcslashes( $data );

		if ( preg_match( '/\s$/', $data ) ) {
			if ( $whitespace === 'mark' ) {
				$data .= '\\';
			} elseif ( $whitespace === 'trim' ) {
				$data = rtrim( $data );
			} else {
				// @todo Only triggered if there is trailing whitespace
				throw new MWException( 'Unknown action for whitespace' );
			}
		}

		return $data;
	}

	public static function parseHeaderTags( $headers ) {
		$tags = [];
		foreach ( explode( "\n", $headers ) as $line ) {
			if ( strpos( $line, ':' ) === false ) {
				error_log( __METHOD__ . ": $line" );
			}
			list( $key, $value ) = explode( ':', $line, 2 );
			$tags[trim( $key )] = trim( $value );
		}

		return $tags;
	}

	protected function writeReal( MessageCollection $collection ) {
		$pot = $this->read( 'en' );
		$template = $this->read( $collection->code );
		$pluralCount = false;
		$output = $this->doGettextHeader( $collection, $template, $pluralCount );

		/** @var TMessage $m */
		foreach ( $collection as $key => $m ) {
			$transTemplate = $template['TEMPLATE'][$key] ?? [];
			$potTemplate = $pot['TEMPLATE'][$key] ?? [];

			$output .= $this->formatMessageBlock( $key, $m, $transTemplate, $potTemplate, $pluralCount );
		}

		return $output;
	}

	protected function doGettextHeader( MessageCollection $collection, $template, &$pluralCount ) {
		global $wgSitename;

		$code = $collection->code;
		$name = TranslateUtils::getLanguageName( $code );
		$native = TranslateUtils::getLanguageName( $code, $code );
		$authors = $this->doAuthors( $collection );
		if ( isset( $this->extra['header'] ) ) {
			$extra = "# --\n" . $this->extra['header'];
		} else {
			$extra = '';
		}

		$output = <<<PHP
# Translation of {$this->group->getLabel()} to $name ($native)
# Exported from $wgSitename
#
$authors$extra
PHP;

		// Make sure there is no empty line before msgid
		$output = trim( $output ) . "\n";

		$specs = $template['HEADERS'] ?? [];

		$timestamp = wfTimestampNow();
		$specs['PO-Revision-Date'] = self::formatTime( $timestamp );
		if ( $this->offlineMode ) {
			$specs['POT-Creation-Date'] = self::formatTime( $timestamp );
		} elseif ( $this->group instanceof MessageGroupBase ) {
			$specs['X-POT-Import-Date'] = self::formatTime( wfTimestamp( TS_MW, $this->getPotTime() ) );
		}
		$specs['Content-Type'] = 'text/plain; charset=UTF-8';
		$specs['Content-Transfer-Encoding'] = '8bit';
		$specs['Language'] = LanguageCode::bcp47( $this->group->mapCode( $code ) );
		Hooks::run( 'Translate:GettextFFS:headerFields', [ &$specs, $this->group, $code ] );
		$specs['X-Generator'] = $this->getGenerator();

		if ( $this->offlineMode ) {
			$specs['X-Language-Code'] = $code;
			$specs['X-Message-Group'] = $this->group->getId();
		}

		$plural = self::getPluralRule( $code );
		if ( $plural ) {
			$specs['Plural-Forms'] = $plural;
		} elseif ( !isset( $specs['Plural-Forms'] ) ) {
			$specs['Plural-Forms'] = 'nplurals=2; plural=(n != 1);';
		}

		$match = [];
		preg_match( '/nplurals=(\d+);/', $specs['Plural-Forms'], $match );
		$pluralCount = $match[1];

		$output .= 'msgid ""' . "\n";
		$output .= 'msgstr ""' . "\n";
		$output .= '""' . "\n";

		foreach ( $specs as $k => $v ) {
			$output .= self::escape( "$k: $v\n" ) . "\n";
		}

		$output .= "\n";

		return $output;
	}

	protected function doAuthors( MessageCollection $collection ) {
		$output = '';
		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $collection->code );

		foreach ( $authors as $author ) {
			$output .= "# Author: $author\n";
		}

		return $output;
	}

	/**
	 * @param string $key
	 * @param TMessage $m
	 * @param array $trans
	 * @param array $pot
	 * @param int $pluralCount
	 * @return string
	 */
	protected function formatMessageBlock( $key, $m, $trans, $pot, $pluralCount ) {
		$header = $this->formatDocumentation( $key );
		$content = '';

		$comments = self::chainGetter( 'comments', $pot, $trans, [] );
		foreach ( $comments as $type => $typecomments ) {
			foreach ( $typecomments as $comment ) {
				$header .= "#$type $comment\n";
			}
		}

		$flags = self::chainGetter( 'flags', $pot, $trans, [] );
		$flags = array_merge( $m->getTags(), $flags );

		if ( $this->offlineMode ) {
			$content .= 'msgctxt ' . self::escape( $key ) . "\n";
		} else {
			$ctxt = self::chainGetter( 'ctxt', $pot, $trans, false );
			if ( $ctxt !== false ) {
				$content .= 'msgctxt ' . self::escape( $ctxt ) . "\n";
			}
		}

		$msgid = $m->definition();
		$msgstr = $m->translation();
		if ( strpos( $msgstr, TRANSLATE_FUZZY ) !== false ) {
			$msgstr = str_replace( TRANSLATE_FUZZY, '', $msgstr );
			// Might by fuzzy infile
			$flags[] = 'fuzzy';
		}

		if ( preg_match( '/{{PLURAL:GETTEXT/i', $msgid ) ) {
			$forms = $this->splitPlural( $msgid, 2 );
			$content .= 'msgid ' . self::escape( $forms[0] ) . "\n";
			$content .= 'msgid_plural ' . self::escape( $forms[1] ) . "\n";

			try {
				$forms = $this->splitPlural( $msgstr, $pluralCount );
				foreach ( $forms as $index => $form ) {
					$content .= "msgstr[$index] " . self::escape( $form ) . "\n";
				}
			} catch ( GettextPluralException $e ) {
				$flags[] = 'invalid-plural';
				for ( $i = 0; $i < $pluralCount; $i++ ) {
					$content .= "msgstr[$i] \"\"\n";
				}
			}
		} else {
			$content .= 'msgid ' . self::escape( $msgid ) . "\n";
			$content .= 'msgstr ' . self::escape( $msgstr ) . "\n";
		}

		if ( $flags ) {
			sort( $flags );
			$header .= '#, ' . implode( ', ', array_unique( $flags ) ) . "\n";
		}

		$output = $header ?: "#\n";
		$output .= $content . "\n";

		return $output;
	}

	/**
	 * @param string $key
	 * @param array $a
	 * @param array $b
	 * @param mixed $default
	 * @return mixed
	 */
	protected static function chainGetter( $key, $a, $b, $default ) {
		if ( isset( $a[$key] ) ) {
			return $a[$key];
		} elseif ( isset( $b[$key] ) ) {
			return $b[$key];
		} else {
			return $default;
		}
	}

	protected static function formatTime( $time ) {
		$lang = Language::factory( 'en' );

		return $lang->sprintfDate( 'xnY-xnm-xnd xnH:xni:xns+0000', $time );
	}

	protected function getPotTime() {
		$defs = new MessageGroupCache( $this->group );

		return $defs->exists() ? $defs->getTimestamp() : wfTimestampNow();
	}

	protected function getGenerator() {
		return 'MediaWiki ' . SpecialVersion::getVersion() .
			'; Translate ' . TRANSLATE_VERSION;
	}

	protected function formatDocumentation( $key ) {
		global $wgTranslateDocumentationLanguageCode;

		if ( !$this->offlineMode ) {
			return '';
		}

		$code = $wgTranslateDocumentationLanguageCode;
		if ( !$code ) {
			return '';
		}

		$documentation = TranslateUtils::getMessageContent( $key, $code, $this->group->getNamespace() );
		if ( !is_string( $documentation ) ) {
			return '';
		}

		$lines = explode( "\n", $documentation );
		$out = '';
		foreach ( $lines as $line ) {
			$out .= "#. [Wiki] $line\n";
		}

		return $out;
	}

	protected static function escape( $line ) {
		// There may be \ as a last character, for keeping trailing whitespace
		$line = preg_replace( '/(\s)\\\\$/', '\1', $line );
		$line = addcslashes( $line, '\\"' );
		$line = str_replace( "\n", '\n', $line );
		$line = '"' . $line . '"';

		return $line;
	}

	/**
	 * Returns plural rule for Gettext.
	 * @param string $code Language code.
	 * @return string
	 */
	public static function getPluralRule( $code ) {
		$rulefile = __DIR__ . '/../data/plural-gettext.txt';
		$rules = file_get_contents( $rulefile );
		foreach ( explode( "\n", $rules ) as $line ) {
			if ( trim( $line ) === '' ) {
				continue;
			}
			list( $rulecode, $rule ) = explode( "\t", $line );
			if ( $rulecode === $code ) {
				return $rule;
			}
		}

		return '';
	}

	protected function splitPlural( $text, $forms ) {
		if ( $forms === 1 ) {
			return $text;
		}

		$placeholder = TranslateUtils::getPlaceholder();
		# |/| is commonly used in KDE to support inflections
		$text = str_replace( '|/|', $placeholder, $text );

		$plurals = [];
		$match = preg_match_all( '/{{PLURAL:GETTEXT\|(.*)}}/iUs', $text, $plurals );
		if ( !$match ) {
			throw new GettextPluralException( "Failed to find plural in: $text" );
		}

		$splitPlurals = [];
		for ( $i = 0; $i < $forms; $i++ ) {
			# Start with the hole string
			$pluralForm = $text;
			# Loop over *each* {{PLURAL}} instance and replace
			# it with the plural form belonging to this index
			foreach ( $plurals[0] as $index => $definition ) {
				$parsedFormsArray = explode( '|', $plurals[1][$index] );
				if ( !isset( $parsedFormsArray[$i] ) ) {
					error_log( "Too few plural forms in: $text" );
					$pluralForm = '';
				} else {
					$pluralForm = str_replace( $pluralForm, $definition, $parsedFormsArray[$i] );
				}
			}

			$pluralForm = str_replace( $placeholder, '|/|', $pluralForm );
			$splitPlurals[$i] = $pluralForm;
		}

		return $splitPlurals;
	}

	public function shouldOverwrite( $a, $b ) {
		$regex = '/^"(.+)-Date: \d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d\+\d\d\d\d\\\\n"$/m';

		$a = preg_replace( $regex, '', $a );
		$b = preg_replace( $regex, '', $b );

		return $a !== $b;
	}

	public static function getExtraSchema() {
		$schema = [
			'root' => [
				'_type' => 'array',
				'_children' => [
					'FILES' => [
						'_type' => 'array',
						'_children' => [
							'header' => [
								'_type' => 'text',
							],
							'keyAlgorithm' => [
								'_type' => 'enum',
								'_values' => [ 'simple', 'legacy' ],
							],
							'CtxtAsKey' => [
								'_type' => 'boolean',
							],
						]
					]
				]
			]
		];

		return $schema;
	}
}
