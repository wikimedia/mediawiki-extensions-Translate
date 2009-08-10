<?php
if ( !defined( 'MEDIAWIKI' ) ) die();

class GettextFormatReader extends SimpleFormatReader {
	protected $pot = false;
	public function setPotMode( $value ) {
		$this->pot = $value;
	}

	protected $prefix = '';
	public function setPrefix( $value ) {
		$this->prefix = $value;
	}
	

	public function parseAuthors() {
		return array(); // Not implemented
	}

	public function parseStaticHeader() {
		if ( $this->filename === false ) {
			return '';
		}
		$data = file_get_contents( $this->filename );
		$start = (int) strpos( $data, '# --');
		if ( $start ) $start += 5;
		$end = (int) strpos( $data, "msgid" );
		return substr( $data, $start, $end-$start );
	}

	public function parseFile() {
		$data = file_get_contents( $this->filename );
		$data = str_replace( "\r\n", "\n", $data );

		$pluralForms = false;

		$matches = array();
		if ( preg_match( '/X-Language-Code:\s+([a-zA-Z-_]+)/', $data, $matches ) ) {
			$code = $matches[1];
		}

		if ( preg_match( '/X-Message-Group:\s+([a-zA-Z0-9-_]+)/', $data, $matches ) ) {
			$groupId = $matches[1];
		}

		if ( preg_match( '/Plural-Forms:\s+nplurals=([0-9]+).*;/', $data, $matches ) ) {
			$pluralForms = $matches;
		}

		$useCtxtAsKey = false;

		$poformat = '".*"\n?(^".*"$\n?)*';
		$quotePattern = '/(^"|"$\n?)/m';

		$sections = preg_split( '/\n{2,}/', $data );
		array_shift( $sections ); // First isn't an actual message
		$changes = array();

		foreach ( $sections as $section ) {
			if ( trim( $section ) === '' ) continue;

			$item = array(
				'ctxt'  => '',
				'id'    => '',
				'str'   => '',
				'flags' => array(),
				'comments' => array(),
			);

			$matches = array();
			if ( preg_match( "/^msgctxt\s($poformat)/mx", $section, $matches ) ) {
				// Remove quoting
				$item['ctxt'] = GettextFFS::formatForWiki( $matches[1] );
			} elseif ( $useCtxtAsKey ) {
				// Invalid message
				continue;
			}

			$matches = array();
			if ( preg_match( "/^msgid\s($poformat)/mx", $section, $matches ) ) {
				$item['id'] = GettextFFS::formatForWiki( $matches[1] );
			} else {
				# echo "Definition not found!\n$section";
				continue;
			}

			$pluralMessage = false;
			$matches = array();
			if ( preg_match( "/^msgid_plural\s($poformat)/mx", $section, $matches ) ) {
				$pluralMessage = true;
				$plural = GettextFFS::formatForWiki( $matches[1] );
				$item['id'] = "{{PLURAL:GETTEXT|{$item['id']}|$plural}}";
			}

			if ( $pluralMessage ) {

				$actualForms = array();
				for ( $i = 0; $i < $pluralForms[1]; $i++ ) {
					$matches = array();
					if ( preg_match( "/^msgstr\[$i\]\s($poformat)/mx", $section, $matches ) ) {
						$actualForms[] = GettextFFS::formatForWiki( $matches[1] );
					} else {
						throw new MWException( "Plural not found, expecting $i" );
					}
				}

				$item['str'] = '{{PLURAL:GETTEXT|' . implode( '|', $actualForms ) . '}}';
			} else {

				$matches = array();
				if ( preg_match( "/^msgstr\s($poformat)/mx", $section, $matches ) ) {
					$item['str'] = GettextFFS::formatForWiki( $matches[1] );
				} else {
					# echo "Translation not found!\n";
					continue;
				}
			}

			// Parse flags
			$matches = array();
			if ( preg_match( '/^#,(.*)$/mu', $section, $matches ) ) {
				$flags = array_map( 'trim', explode( ',', $matches[1] ) );
				foreach ( $flags as $key => $flag ) {
					if ( $flag === 'fuzzy' ) {
						$item['str'] = TRANSLATE_FUZZY . $item['str'];
						unset( $flags[$key] );
					}
				}
				$item['flags'] = $flags;
			}

			$matches = array();
			if ( preg_match_all( '/^#(.?) (.*)$/m', $section, $matches, PREG_SET_ORDER ) ) {
				foreach ( $matches as $match ) {
					if ( $match[1] !== ',' ) {
						$item['comments'][$match[1]][] = $match[2];
					}
				}
			}

			$lang = Language::factory( 'en' );
			if ( $useCtxtAsKey ) {
				$key = $item['ctxt'];
			} else {
				$key = GettextFFS::generateKeyFromItem( $item );
			}

			$changes[$key] = $item;

		}
		$changes['PLURAL'] = $pluralForms;
		return $changes;
	}


	public function parseMessages( StringMangler $mangler ) {
		$defs = $this->parseFile();
		unset($defs['PLURAL']);
		$messages = array();
		foreach ( $defs as $key => $def ) {
			if ( $this->pot ) {
				$messages[$key] = $def['id'];
			} else {
				if ( $def['str'] !== '' ) {
					$messages[$key] = $def['str'];
				}
			}
		}
		return $messages;
	}

}

class GettextFormatWriter extends SimpleFormatWriter {
	protected $data = array();
	protected $plural = array(false, 0);

	public function load( $code ) {
		$reader = $this->group->getReader( $code );
		$readerEn = $this->group->getReader( 'en' );
		if ( $reader instanceof GettextFormatReader ) {
			$this->addAuthors( $reader->parseAuthors(), $code );
			$this->staticHeader = $reader->parseStaticHeader();
			$data = $reader->parseFile();
			$this->plural = $data['PLURAL'];
		}
		if ( $readerEn instanceof GettextFormatReader ) {
			$this->data = $readerEn->parseFile();
		}
	}


	public function exportLanguage( $handle, MessageCollection $messages ) {
		global $wgSitename, $wgServer, $wgTranslateDocumentationLanguageCode;

		$code = $messages->code;
		$this->load( $code );
		$lang = Language::factory( 'en' );

		$out = '';
		$now = wfTimestampNow();
		$label = $this->group->getLabel();
		$languageName = TranslateUtils::getLanguageName( $code );

		$headers = array();
		$headers['Project-Id-Version'] = $label;
		// TODO: make this customisable or something
		// $headers['Report-Msgid-Bugs-To'] = $wgServer;
		// TODO: sprintfDate doesn't support any time zone flags
		// $headers['POT-Creation-Date']
		$headers['PO-Revision-Date'] = $lang->sprintfDate( 'xnY-xnm-xnd xnH:xni:xns+0000', $now );
		$headers['Language-Team'] = $languageName;
		$headers['Content-Type'] = 'text/plain; charset=UTF-8';
		$headers['Content-Transfer-Encoding'] = '8bit';

		$headers['X-Generator'] = 'MediaWiki ' . SpecialVersion::getVersion() .
			"; Translate extension (" . TRANSLATE_VERSION . ")";

		$headers['X-Translation-Project'] = "$wgSitename at $wgServer";
		$headers['X-Language-Code'] = $code;
		$headers['X-Message-Group'] = $this->group->getId();
		if( $this->plural[0] ) {
			list( $header, $rest ) = explode( ':', $this->plural[0] );
			$headers[$header] = trim($rest);
		}

		$headerlines = array( '' );
		foreach ( $headers as $key => $value ) {
			$headerlines[] = "$key: $value\n";
		}

		fwrite( $handle, "# Translation of $label to $languageName\n#\n" );
		fwrite( $handle, $this->formatAuthors( "# Author@$wgSitename: ", $code ) );
		fwrite( $handle, "# --\n" );

		$header = preg_replace( '/^# translation of (.*) to (.*)$\n/im', '', $this->staticHeader );

		fwrite( $handle, $header );
		fwrite( $handle, $this->formatmsg( '', $headerlines  ) );

		foreach ( $messages as $key => $m ) {
			$flags = array();

			$translation = $m->translation();
			# CASE2: no translation
			if ( $translation === null ) $translation = '';

			# CASE3: optional messages; accept only if different
			if ( $m->hasTag( 'optional') ) $flags[] = 'x-optional';

			# Remove fuzzy markings before export
			$flags = array();
			$comments = array();
			if ( isset( $this->data[$key]['flags'] ) ) {
				$flags = $this->data[$key]['flags'];
			}
			if ( strpos( $translation, TRANSLATE_FUZZY ) !== false ) {
				$translation = str_replace( TRANSLATE_FUZZY, '', $translation );
				$flags[] = 'fuzzy';
			}

			$documentation = '';
			if ( $wgTranslateDocumentationLanguageCode ) {
				$documentation = TranslateUtils::getMessageContent( $key, $wgTranslateDocumentationLanguageCode );
			}

			$comments = array();
			if ( isset( $this->data[$key]['comments'] ) ) {
				$comments = $this->data[$key]['comments'];
			}

			fwrite( $handle, self::formatcomments( $comments, $documentation, $flags ) );

			$ckey = '';
			if ( isset( $this->data[$key]['ctxt'] ) ) {
				$ckey = $this->data[$key]['ctxt'];
			}
			fwrite( $handle, $this->formatmsg( $m->definition(), $translation, $ckey ) );

		}

		return $out;
	}

	protected function escape( $line ) {
		// There may be \ as a last character, for keeping trailing whitespace
		$line = preg_replace( '/\\\\$/', '', $line );
		$line = addcslashes( $line, '\\"' );
		$line = str_replace( "\n", '\n', $line );
		$line = '"' . $line . '"';
		return $line;
	}

	public static function formatcomments( $comments, $documentation = false, $flags = false ) {
		if ( $documentation ) {
			foreach ( explode( "\n", $documentation ) as $line ) {
				$comments['.'][] = $line;
			}
		}

		if ( $flags ) {
			$comments[','][] = implode( ', ', $flags );
		}

		// Ensure there is always something
		if ( !count( $comments ) ) $comments[':'][] = '';

		$order = array( '', '.', ':', ',', '|' );
		$output = array();
		foreach ( $order as $type ) {
			if ( !isset( $comments[$type] ) ) continue;
			foreach ( $comments[$type] as $value ) {
				$output[] = "#$type $value";
			}
		}

		return implode( "\n", $output ) . "\n";
	}

	protected function formatmsg( $msgid, $msgstr, $msgctxt = false ) {
		$output = array();
		
		if ( $msgctxt ) {
			$output[] = 'msgctxt ' . $this->escape( $msgctxt );
		}

		if ( preg_match( '/{{PLURAL:GETTEXT/i', $msgid ) ) {
			$forms = $this->splitPlural( $msgid, 2 );
			$output[] = 'msgid ' . $this->escape( $forms[0] );
			$output[] = 'msgid_plural ' . $this->escape( $forms[1] );

			$forms = $this->splitPlural( $msgstr, $this->plural[1] );
			foreach( $forms as $index => $form ) {
				$output[] = "msgstr[$index] " . $this->escape( $form );
			}
		} else {
			$output[] = 'msgid ' . $this->escape( $msgid );

			// Special case for the header
			if ( is_array( $msgstr ) ) {
				$output[] = 'msgstr ""';
				foreach ( $msgstr as $line )
					$output[] = $this->escape( $line );
			} else {
				$output[] = 'msgstr ' . $this->escape( $msgstr );
			}
		}

		$out = implode( "\n", $output ) . "\n\n";
		return $out;

	}

	protected function splitPlural( $text, $forms ) {
		if ( $forms === 1 ) {
			return $text;
		} elseif( !$forms ) {
			$forms = (int) $forms;
			throw new MWException( "Don't know how to split $text into $forms forms" );
		}

		$splitPlurals = array();
		for ( $i = 0; $i < $forms; $i++ ) {
			$plurals = array();
			$match = preg_match_all( '/{{PLURAL:GETTEXT\|(.*)}}/iU', $text, $plurals );
			if ( !$match ) throw new MWException( "Failed to parse plural for: $text" );
			$pluralForm = $text;
			foreach ( $plurals[0] as $index => $definition ) {
				$parsedFormsArray = explode( '|', $plurals[1][$index] );
				if ( !isset($parsedFormsArray[$i]) ) throw new MWException( "Too few plural forms in: $text" );
				$pluralForm = str_replace( $pluralForm, $definition, $parsedFormsArray[$i] );
			}
			$splitPlurals[$i] = $pluralForm;
		}

		return $splitPlurals;
	}
}

class GettextFFS extends SimpleFFS {

	//
	// READ
	//

	public function readFromVariable( $data ) {
		$authors = $messages = array();

		# Authors first
		$matches = array();
		preg_match_all( '/^#\s*Author:\s*(.*)$/m', $data, $matches );
		$authors = $matches[1];

		# Then messages and everything else
		$parsedData = $this->parseGettext( $data );
		$parsedData['MESSAGES'] = $this->group->getMangler()->mangle( $parsedData['MESSAGES'] );
		$parsedData['AUTHORS'] = $authors;

		return $parsedData;
	}

	public function parseGettext( $data ) {
		$data = str_replace( "\r\n", "\n", $data );
		$messages = $template = $metadata = array();

		// Defined only once. Be sure to *not* use it without match, or you might get old data
		$matches = array();

		if ( preg_match( '/X-Language-Code:\s+([a-zA-Z-_]+)/', $data, $matches ) ) {
			$metadata['code'] = $matches[1];
		} 

		if ( preg_match( '/X-Message-Group:\s+([a-zA-Z0-9-_]+)/', $data, $matches ) ) {
			$metadata['group'] = $matches[1];
		}

		$pluralForms = false;
		if ( preg_match( '/Plural-Forms:\s+nplurals=([0-9]+).*;/', $data, $matches ) ) {
			$metadata['plurals'] = $matches;
			$pluralForms = $matches;
		}

		$useCtxtAsKey = isset($this->extra['CtxtAsKey']) && $this->extra['CtxtAsKey'];

		$poformat = '".*"\n?(^".*"$\n?)*';
		$quotePattern = '/(^"|"$\n?)/m';

		$sections = preg_split( '/\n{2,}/', $data );
		array_shift( $sections ); // First isn't an actual message

		foreach ( $sections as $section ) {
			if ( trim( $section ) === '' ) continue;

			$item = array(
				'ctxt'  => '',
				'id'    => '',
				'str'   => '',
				'flags' => array(),
				'comments' => array(),
			);

			$matches = array();
			if ( preg_match( "/^msgid\s($poformat)/mx", $section, $matches ) ) {
				$item['id'] = self::formatForWiki( $matches[1] );
			} else {
				throw new MWException( "Unable to parse msgid:\n\n$section" );
			}

			if ( preg_match( "/^msgctxt\s($poformat)/mx", $section, $matches ) ) {
				$item['ctxt'] = self::formatForWiki( $matches[1] );
			} elseif ( $useCtxtAsKey ) { // Invalid message
				$metadata['warnings'][] = "Ctxt missing for {$item['id']}";
			}


			$pluralMessage = false;
			if ( preg_match( "/^msgid_plural\s($poformat)/mx", $section, $matches ) ) {
				$pluralMessage = true;
				$plural = self::formatForWiki( $matches[1] );
				$item['id'] = "{{PLURAL:GETTEXT|{$item['id']}|$plural}}";
			}

			if ( $pluralMessage ) {

				$actualForms = array();
				for ( $i = 0; $i < $pluralForms[1]; $i++ ) {
					if ( preg_match( "/^msgstr\[$i\]\s($poformat)/mx", $section, $matches ) ) {
						$actualForms[] = self::formatForWiki( $matches[1] );
					} else {
						throw new MWException( "Plural not found, expecting $i" );
					}
				}

				$item['str'] = '{{PLURAL:GETTEXT|' . implode( '|', $actualForms ) . '}}';
			} else {

				$matches = array();
				if ( preg_match( "/^msgstr\s($poformat)/mx", $section, $matches ) ) {
					$item['str'] = self::formatForWiki( $matches[1] );
				} else {
					throw new MWException( "Unable to parse msgstr:\n\n$section" );
				}
			}

			// Parse flags
			$matches = array();
			if ( preg_match( '/^#,(.*)$/mu', $section, $matches ) ) {
				$flags = array_map( 'trim', explode( ',', $matches[1] ) );
				foreach ( $flags as $key => $flag ) {
					if ( $flag === 'fuzzy' ) {
						$item['str'] = TRANSLATE_FUZZY . $item['str'];
						unset( $flags[$key] );
					}
				}
				$item['flags'] = $flags;
			}

			// Rest of the comments
			$matches = array();
			if ( preg_match_all( '/^#(.?) (.*)$/m', $section, $matches, PREG_SET_ORDER ) ) {
				foreach ( $matches as $match ) {
					if ( $match[1] !== ',' ) {
						$item['comments'][$match[1]][] = $match[2];
					}
				}
			}

			if ( $useCtxtAsKey ) {
				$key = $item['ctxt'];
			} else {
				$key = self::generateKeyFromItem( $item );
			}

			$messages[$key] = $item['str'];
			$template[$key] = $item;

		}

		return array(
			'MESSAGES' => $messages,
			'TEMPLATE' => $template,
			'METADATA' => $metadata,
		);
	}

	public static function generateKeyFromItem( $item ) {
		$lang = Language::factory( 'en' );
		global $wgLegalTitleChars;
		$hash = sha1( $item['ctxt'] . $item['id'] );
		$snippet = $item['id'];
		$snippet = preg_replace( "/[^$wgLegalTitleChars]/", ' ', $snippet );
		$snippet = preg_replace( "/[:&%\/_]/", ' ', $snippet );
		$snippet = preg_replace( "/ {2,}/", ' ', $snippet );
		$snippet = $lang->truncate( $snippet, 30, '' );
		$snippet = str_replace( ' ', '_', trim( $snippet ) );
		return "$hash-$snippet";
	}

	public static function formatForWiki( $data ) {
		$quotePattern = '/(^"|"$\n?)/m';
		$data = preg_replace( $quotePattern, '', $data );
		$data = stripcslashes( $data );
		if ( preg_match( '/\s$/', $data ) ) {
			$data .= '\\';
		}
		return $data;
	}

	//
	// WRITE
	//

	protected function writeReal( MessageCollection $collection ) {
		throw new MWException( 'Not implemented' );
		$output  = $this->doHeader( $collection );
		$output .= $this->doAuthors( $collection );

		$mangler = $this->group->getMangler();

		$messages = array();
		foreach ( $collection as $key => $m ) {
			$key = $mangler->unmangle( $key );
			$value = $m->translation();
			$value = str_replace( TRANSLATE_FUZZY, '', $value );
			if ( $value === '' ) continue;

			$messages[$key] = $value;
		}
		$output .= TranslateSpyc::dump( $messages );
		return $output;
	}

	protected function doHeader( MessageCollection $collection ) {
		global $wgSitename;
		$code = $collection->code;
		$name = TranslateUtils::getLanguageName( $code );
		$native = TranslateUtils::getLanguageName( $code, true );
		$output  = "# Messages for $name ($native)\n";
		$output .= "# Exported from $wgSitename\n";
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

}