<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use InvalidArgumentException;
use MediaWiki\Extension\Translate\LogNames;
use MediaWiki\Extension\Translate\MessageGroupConfiguration\MetaYamlSchemaExtender;
use MediaWiki\Extension\Translate\MessageLoading\Message;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Utilities\GettextPlural;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Language\LanguageCode;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Specials\SpecialVersion;
use MediaWiki\Title\Title;
use RuntimeException;

/**
 * FileFormat class that implements support for gettext file format.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2010, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @ingroup FileFormatSupport
 */
class GettextFormat extends SimpleFormat implements MetaYamlSchemaExtender {
	private bool $allowPotMode = false;
	private bool $offlineMode = false;

	public function supportsFuzzy(): string {
		return 'yes';
	}

	public function getFileExtensions(): array {
		return [ '.pot', '.po' ];
	}

	public function setOfflineMode( bool $value ): void {
		$this->offlineMode = $value;
	}

	/** @inheritDoc */
	public function read( $languageCode ) {
		// This is somewhat hacky, but pot mode should only ever be used for the source language.
		// See https://phabricator.wikimedia.org/T230361
		$this->allowPotMode = $this->getGroup()->getSourceLanguage() === $languageCode;

		try {
			return parent::read( $languageCode );
		} finally {
			$this->allowPotMode = false;
		}
	}

	public function readFromVariable( string $data ): array {
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

	private function parseGettext( string $data ): array {
		$mangler = $this->group->getMangler();
		$useCtxtAsKey = $this->extra['CtxtAsKey'] ?? false;
		$keyAlgorithm = 'simple';
		if ( isset( $this->extra['keyAlgorithm'] ) ) {
			$keyAlgorithm = $this->extra['keyAlgorithm'];
		}

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
		$match = $this->expectKeyword( 'msgstr', $headerSection );
		if ( $match !== null ) {
			$headerBlock = $this->formatForWiki( $match, 'trim' );
			$headers = $this->parseHeaderTags( $headerBlock );

			// Check for pot-mode by checking if the header is fuzzy
			$flags = $this->parseFlags( $headerSection );
			if ( in_array( 'fuzzy', $flags, true ) ) {
				$potmode = $this->allowPotMode;
			}
		} else {
			$message = "Gettext file header was not found:\n\n$data";
			throw new GettextParseException( $message );
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
		$pluralCount = null;
		if ( $potmode ) {
			$pluralCount = 2;
		} elseif ( isset( $headers['Plural-Forms'] ) ) {
			$pluralCount = $metadata['plural'] = GettextPlural::getPluralCount( $headers['Plural-Forms'] );
		}

		$metadata['plural'] = $pluralCount;

		// Then parse the messages
		foreach ( $sections as $section ) {
			$item = $this->parseGettextSection( $section, $pluralCount );
			if ( $item === null ) {
				continue;
			}

			if ( $useCtxtAsKey ) {
				if ( !isset( $item['ctxt'] ) ) {
					error_log( "ctxt missing for: $section" );
					continue;
				}
				$key = $item['ctxt'];
			} else {
				$key = $this->generateKeyFromItem( $item, $keyAlgorithm );
			}

			$key = $mangler->mangle( $key );
			$messages[$key] = $potmode ? $item['id'] : $item['str'];
			$template[$key] = $item;
		}

		return [
			'MESSAGES' => $messages,
			'EXTRA' => [
				'TEMPLATE' => $template,
				'METADATA' => $metadata,
				'HEADERS' => $headers,
			],
		];
	}

	private function parseGettextSection( string $section, ?int $pluralCount ): ?array {
		if ( trim( $section ) === '' ) {
			return null;
		}

		/* These inactive sections are of no interest to us. Multiline mode
		 * is needed because there may be flags or other annoying stuff
		 * before the commented out sections.
		 */
		if ( preg_match( '/^#~/m', $section ) ) {
			return null;
		}

		$item = [
			'ctxt' => false,
			'id' => '',
			'str' => '',
			'flags' => [],
			'comments' => [],
		];

		$match = $this->expectKeyword( 'msgid', $section );
		if ( $match !== null ) {
			$item['id'] = $this->formatForWiki( $match );
		} else {
			throw new RuntimeException( "Unable to parse msgid:\n\n$section" );
		}

		$match = $this->expectKeyword( 'msgctxt', $section );
		if ( $match !== null ) {
			$item['ctxt'] = $this->formatForWiki( $match );
		}

		$pluralMessage = false;
		$match = $this->expectKeyword( 'msgid_plural', $section );
		if ( $match !== null ) {
			$pluralMessage = true;
			$plural = $this->formatForWiki( $match );
			$item['id'] = GettextPlural::flatten( [ $item['id'], $plural ] );
		}

		if ( $pluralMessage ) {
			$pluralMessageText = $this->processGettextPluralMessage( $pluralCount, $section );

			// Keep the translation empty if no form has translation
			if ( $pluralMessageText !== '' ) {
				$item['str'] = $pluralMessageText;
			}
		} else {
			$match = $this->expectKeyword( 'msgstr', $section );
			if ( $match !== null ) {
				$item['str'] = $this->formatForWiki( $match );
			} else {
				throw new RuntimeException( "Unable to parse msgstr:\n\n$section" );
			}
		}

		// Parse flags
		$flags = $this->parseFlags( $section );
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
				if ( $match[1] !== ',' && !str_starts_with( $match[1], '[Wiki]' ) ) {
					$item['comments'][$match[1]][] = $match[2];
				}
			}
		}

		return $item;
	}

	private function processGettextPluralMessage( ?int $pluralCount, string $section ): string {
		$actualForms = [];

		for ( $i = 0; $i < $pluralCount; $i++ ) {
			$match = $this->expectKeyword( "msgstr\\[$i\\]", $section );

			if ( $match !== null ) {
				$actualForms[] = $this->formatForWiki( $match );
			} else {
				$actualForms[] = '';
				error_log( "Plural $i not found, expecting total of $pluralCount for $section" );
			}
		}

		if ( array_sum( array_map( 'strlen', $actualForms ) ) > 0 ) {
			return GettextPlural::flatten( $actualForms );
		} else {
			return '';
		}
	}

	private function parseFlags( string $section ): array {
		$matches = [];
		if ( preg_match( '/^#,(.*)$/mu', $section, $matches ) ) {
			return array_map( 'trim', explode( ',', $matches[1] ) );
		} else {
			return [];
		}
	}

	private function expectKeyword( string $name, string $section ): ?string {
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
	 */
	public function generateKeyFromItem( array $item, string $algorithm = 'simple' ): string {
		$lang = MediaWikiServices::getInstance()->getLanguageFactory()->getLanguage( 'en' );

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
			$snippet = $lang->truncateForDatabase( $item['id'], 30, '' );
			$snippet = str_replace( ' ', '_', trim( $snippet ) );
		} else { // legacy
			$legalChars = Title::legalChars();
			$snippet = $item['id'];
			$snippet = preg_replace( "/[^$legalChars]/", ' ', $snippet );
			$snippet = preg_replace( '/[:&%\/_]/', ' ', $snippet );
			$snippet = preg_replace( '/ {2,}/', ' ', $snippet );
			$snippet = $lang->truncateForDatabase( $snippet, 30, '' );
			$snippet = str_replace( ' ', '_', trim( $snippet ) );
		}

		return "$hash-$snippet";
	}

	/**
	 * This method processes the gettext text block format.
	 */
	private function processData( string $data ): string {
		$quotePattern = '/(^"|"$\n?)/m';
		$data = preg_replace( $quotePattern, '', $data );
		return stripcslashes( $data );
	}

	/**
	 * This method handles the whitespace at the end of the data.
	 * @throws InvalidArgumentException
	 */
	private function handleWhitespace( string $data, string $whitespace ): string {
		if ( preg_match( '/\s$/', $data ) ) {
			if ( $whitespace === 'mark' ) {
				$data .= '\\';
			} elseif ( $whitespace === 'trim' ) {
				$data = rtrim( $data );
			} else {
				// This condition will never happen as long as $whitespace is 'mark' or 'trim'
				throw new InvalidArgumentException( "Unknown action for whitespace: $whitespace" );
			}
		}

		return $data;
	}

	/**
	 * This parses the Gettext text block format. Since trailing whitespace is
	 * not allowed in MediaWiki pages, the default action is to append
	 * \-character at the end of the message. You can also choose to ignore it
	 * and use the trim action instead.
	 */
	private function formatForWiki( string $data, string $whitespace = 'mark' ): string {
		$data = $this->processData( $data );
		return $this->handleWhitespace( $data, $whitespace );
	}

	private function parseHeaderTags( string $headers ): array {
		$tags = [];
		foreach ( explode( "\n", $headers ) as $line ) {
			if ( !str_contains( $line, ':' ) ) {
				error_log( __METHOD__ . ": $line" );
			}
			[ $key, $value ] = explode( ':', $line, 2 );
			$tags[trim( $key )] = trim( $value );
		}

		return $tags;
	}

	protected function writeReal( MessageCollection $collection ): string {
		// FIXME: this should be the source language
		$pot = $this->read( 'en' ) ?? [];
		$code = $collection->code;
		$template = $this->read( $code ) ?? [];
		$output = $this->doGettextHeader( $collection, $template['EXTRA'] ?? [] );

		$pluralRule = GettextPlural::getPluralRule( $code );
		if ( !$pluralRule ) {
			$pluralRule = GettextPlural::getPluralRule( 'en' );
			LoggerFactory::getInstance( LogNames::MAIN )->warning(
				"T235180: Missing Gettext plural rule for '{languagecode}'",
				[ 'languagecode' => $code ]
			);
		}
		$pluralCount = GettextPlural::getPluralCount( $pluralRule );

		$documentationLanguageCode = MediaWikiServices::getInstance()
			->getMainConfig()
			->get( 'TranslateDocumentationLanguageCode' );
		$documentationCollection = null;
		if ( is_string( $documentationLanguageCode ) ) {
			$documentationCollection = clone $collection;
			$documentationCollection->resetForNewLanguage( $documentationLanguageCode );
			$documentationCollection->loadTranslations();
		}

		/** @var Message $m */
		foreach ( $collection as $key => $m ) {
			$transTemplate = $template['EXTRA']['TEMPLATE'][$key] ?? [];
			$potTemplate = $pot['EXTRA']['TEMPLATE'][$key] ?? [];
			$documentation = isset( $documentationCollection[$key] ) ?
				$documentationCollection[$key]->translation() : null;

			$output .= $this->formatMessageBlock(
				$key,
				$m,
				$transTemplate,
				$potTemplate,
				$pluralCount,
				$documentation
			);
		}

		return $output;
	}

	private function doGettextHeader( MessageCollection $collection, array $template ): string {
		global $wgSitename;

		$code = $collection->code;
		$name = Utilities::getLanguageName( $code );
		$native = Utilities::getLanguageName( $code, $code );
		$authors = $this->doAuthors( $collection );
		if ( isset( $this->extra['header'] ) ) {
			$extra = "# --\n" . $this->extra['header'];
		} else {
			$extra = '';
		}

		$group = $this->getGroup();
		$output =
			<<<EOT
			# Translation of {$group->getLabel()} to $name ($native)
			# Exported from $wgSitename
			#
			$authors$extra
			EOT;

		// Make sure there is no empty line before msgid
		$output = trim( $output ) . "\n";

		$specs = $template['HEADERS'] ?? [];

		$timestamp = wfTimestampNow();
		$specs['PO-Revision-Date'] = $this->formatTime( $timestamp );
		if ( $this->offlineMode ) {
			$specs['POT-Creation-Date'] = $this->formatTime( $timestamp );
		} else {
			$specs['X-POT-Import-Date'] = $this->formatTime( wfTimestamp( TS_MW, $this->getPotTime() ) );
		}
		$specs['Content-Type'] = 'text/plain; charset=UTF-8';
		$specs['Content-Transfer-Encoding'] = '8bit';

		$specs['Language'] = LanguageCode::bcp47( $this->group->mapCode( $code ) );

		Services::getInstance()->getHookRunner()->onTranslate_GettextFormat_headerFields(
			$specs,
			$this->group,
			$code
		);

		$specs['X-Generator'] = 'MediaWiki '
			. SpecialVersion::getVersion()
			. '; Translate '
			. Utilities::getVersion();

		if ( $this->offlineMode ) {
			$specs['X-Language-Code'] = $code;
			$specs['X-Message-Group'] = $group->getId();
		}

		$specs['Plural-Forms'] = GettextPlural::getPluralRule( $code )
			?: GettextPlural::getPluralRule( 'en' );

		$output .= 'msgid ""' . "\n";
		$output .= 'msgstr ""' . "\n";
		$output .= '""' . "\n";

		foreach ( $specs as $k => $v ) {
			$output .= $this->escape( "$k: $v\n" ) . "\n";
		}

		$output .= "\n";

		return $output;
	}

	private function doAuthors( MessageCollection $collection ): string {
		$output = '';
		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $collection->code );

		foreach ( $authors as $author ) {
			$output .= "# Author: $author\n";
		}

		return $output;
	}

	private function formatMessageBlock(
		string $key,
		Message $message,
		array $trans,
		array $pot,
		int $pluralCount,
		?string $documentation
	): string {
		$header = $this->formatDocumentation( $documentation );
		$content = '';

		$comments = $pot['comments'] ?? $trans['comments'] ?? [];
		foreach ( $comments as $type => $typecomments ) {
			foreach ( $typecomments as $comment ) {
				$header .= "#$type $comment\n";
			}
		}

		$flags = $pot['flags'] ?? $trans['flags'] ?? [];
		$flags = array_merge( $message->getTags(), $flags );

		if ( $this->offlineMode ) {
			$content .= 'msgctxt ' . $this->escape( $key ) . "\n";
		} else {
			$ctxt = $pot['ctxt'] ?? $trans['ctxt'] ?? false;
			if ( $ctxt !== false ) {
				$content .= 'msgctxt ' . $this->escape( $ctxt ) . "\n";
			}
		}

		$msgid = $message->definition();
		$msgstr = $message->translation() ?? '';
		if ( strpos( $msgstr, TRANSLATE_FUZZY ) !== false ) {
			$msgstr = str_replace( TRANSLATE_FUZZY, '', $msgstr );
			// Might be fuzzy infile
			$flags[] = 'fuzzy';
		}

		if ( GettextPlural::hasPlural( $msgid ) ) {
			$forms = GettextPlural::unflatten( $msgid, 2 );
			$content .= 'msgid ' . $this->escape( $forms[0] ) . "\n";
			$content .= 'msgid_plural ' . $this->escape( $forms[1] ) . "\n";

			try {
				$forms = GettextPlural::unflatten( $msgstr, $pluralCount );
				foreach ( $forms as $index => $form ) {
					$content .= "msgstr[$index] " . $this->escape( $form ) . "\n";
				}
			} catch ( GettextPluralException $e ) {
				$flags[] = 'invalid-plural';
				for ( $i = 0; $i < $pluralCount; $i++ ) {
					$content .= "msgstr[$i] \"\"\n";
				}
			}
		} else {
			$content .= 'msgid ' . $this->escape( $msgid ) . "\n";
			$content .= 'msgstr ' . $this->escape( $msgstr ) . "\n";
		}

		if ( $flags ) {
			sort( $flags );
			$header .= '#, ' . implode( ', ', array_unique( $flags ) ) . "\n";
		}

		$output = $header ?: "#\n";
		$output .= $content . "\n";

		return $output;
	}

	private function formatTime( string $time ): string {
		$lang = MediaWikiServices::getInstance()->getLanguageFactory()->getLanguage( 'en' );

		return $lang->sprintfDate( 'xnY-xnm-xnd xnH:xni:xns+0000', $time );
	}

	private function getPotTime(): string {
		$cache = $this->group->getMessageGroupCache( $this->group->getSourceLanguage() );

		return $cache->exists() ? $cache->getTimestamp() : wfTimestampNow();
	}

	private function formatDocumentation( ?string $documentation ): string {
		if ( !is_string( $documentation ) ) {
			return '';
		}

		if ( !$this->offlineMode ) {
			return '';
		}

		$lines = explode( "\n", $documentation );
		$out = '';
		foreach ( $lines as $line ) {
			$out .= "#. [Wiki] $line\n";
		}

		return $out;
	}

	private function escape( string $line ): string {
		// There may be \ as a last character, for keeping trailing whitespace
		$line = preg_replace( '/(\s)\\\\$/', '\1', $line );
		$line = addcslashes( $line, '\\"' );
		$line = str_replace( "\n", '\n', $line );
		return '"' . $line . '"';
	}

	public function shouldOverwrite( string $a, string $b ): bool {
		$regex = '/^"(.+)-Date: \d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d\+\d\d\d\d\\\\n"$/m';

		$a = preg_replace( $regex, '', $a );
		$b = preg_replace( $regex, '', $b );

		return $a !== $b;
	}

	public static function getExtraSchema(): array {
		return [
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
	}

	public function isContentEqual( ?string $a, ?string $b ): bool {
		if ( $a === $b ) {
			return true;
		}

		if ( $a === null || $b === null ) {
			return false;
		}

		try {
			$parsedA = GettextPlural::parsePluralForms( $a );
			$parsedB = GettextPlural::parsePluralForms( $b );

			// if they have the different number of plural forms, just fail
			if ( count( $parsedA[1] ) !== count( $parsedB[1] ) ) {
				return false;
			}

		} catch ( GettextPluralException $e ) {
			// Something failed, invalid syntax?
			return false;
		}

		$expectedPluralCount = count( $parsedA[1] );

		// GettextPlural::unflatten() will return an empty array when $expectedPluralCount is 0
		// So if they do not have translations and are different strings, they are not equal
		if ( $expectedPluralCount === 0 ) {
			return false;
		}

		return GettextPlural::unflatten( $a, $expectedPluralCount )
			=== GettextPlural::unflatten( $b, $expectedPluralCount );
	}
}

class_alias( GettextFormat::class, 'GettextFFS' );
