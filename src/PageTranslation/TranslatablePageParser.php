<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Extension\Translate\Utilities\ParsingPlaceholderFactory;

/**
 * Generates ParserOutput from text or removes all tags from a text.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2020.08
 */
class TranslatablePageParser {
	private $placeholderFactory;

	public function __construct( ParsingPlaceholderFactory $placeholderFactory ) {
		$this->placeholderFactory = $placeholderFactory;
	}

	public function containsMarkup( string $text ): bool {
		$nowiki = [];
		$text = $this->armourNowiki( $nowiki, $text );
		return preg_match( '~</?translate[ >]~', $text ) !== 0;
	}

	/**
	 * Remove all opening and closing translate tags following the same whitespace rules as the
	 * regular parsing. This doesn't try to parse the page, so it can handle unbalanced tags.
	 */
	public function cleanupTags( string $text ): string {
		$nowiki = [];
		$text = $this->armourNowiki( $nowiki, $text );
		$text = preg_replace( '~<translate( nowrap)?>\n?~s', '', $text );
		$text = preg_replace( '~\n?</translate>~s', '', $text );
		// Markers: headers and the rest
		$ic = preg_quote( TranslationUnit::UNIT_MARKER_INVALID_CHARS, '~' );
		$text = preg_replace( "~(^=.*=) <!--T:[^$ic]+-->$~um", '\1', $text );
		$text = preg_replace( "~<!--T:[^$ic]+-->[\n ]?~um", '', $text );
		// Remove variables
		$unit = new TranslationUnit( $text );
		$text = $unit->getTextForTrans();

		$text = $this->unarmourNowiki( $nowiki, $text );
		return $text;
	}

	/** @throws ParsingFailure */
	public function parse( string $text ): ParserOutput {
		$nowiki = [];
		$text = $this->armourNowiki( $nowiki, $text );

		$sections = [];
		$tagPlaceHolders = [];

		while ( true ) {
			$re = '~(<translate(?: nowrap)?>)(.*?)</translate>~s';
			$matches = [];
			$ok = preg_match( $re, $text, $matches, PREG_OFFSET_CAPTURE );

			if ( $ok === 0 || $ok === false ) {
				break; // No match or failure
			}

			$contentWithTags = $matches[0][0];
			$contentWithoutTags = $matches[2][0];
			// These are offsets to the content inside the tags in $text
			$offsetStart = $matches[0][1];
			$offsetEnd = $offsetStart + strlen( $contentWithTags );

			// Replace the whole match with a placeholder
			$ph = $this->placeholderFactory->make();
			$text = substr( $text, 0, $offsetStart ) . $ph . substr( $text, $offsetEnd );

			if ( preg_match( '~<translate( nowrap)?>~', $contentWithoutTags ) !== 0 ) {
				throw new ParsingFailure(
					'Nested tags',
					[ 'pt-parse-nested', $contentWithoutTags ]
				);
			}

			$openTag = $matches[1][0];
			$canWrap = $openTag !== '<translate nowrap>';

			// Parse the content inside the tags
			$contentWithoutTags = $this->unarmourNowiki( $nowiki, $contentWithoutTags );
			$parse = $this->parseSection( $contentWithoutTags, $canWrap );

			// Update list of sections and the template with the results
			$sections += $parse['sections'];
			$tagPlaceHolders[$ph] = new Section( $openTag, $parse['template'], '</translate>' );
		}

		$prettyTemplate = $text;
		foreach ( $tagPlaceHolders as $ph => $value ) {
			$prettyTemplate = str_replace( $ph, '[...]', $prettyTemplate );
		}

		if ( preg_match( '~<translate( nowrap)?>~', $text ) !== 0 ) {
			throw new ParsingFailure(
				'Unmatched opening tag',
				[ 'pt-parse-open', $prettyTemplate ]
			);
		} elseif ( strpos( $text, '</translate>' ) !== false ) {
			throw new ParsingFailure(
				"Unmatched closing tag",
				[ 'pt-parse-close', $prettyTemplate ]
			);
		}

		$text = $this->unarmourNowiki( $nowiki, $text );

		return new ParserOutput( $text, $tagPlaceHolders, $sections );
	}

	/**
	 * Splits the content marked with \<translate> tags into translation units, which are
	 * separated with two or more newlines. Extra whitespace is captured in the template and
	 * is not included in the translation units.
	 * @internal
	 */
	public function parseSection( string $text, bool $canWrap ): array {
		$flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
		$parts = preg_split( '~(^\s*|\s*\n\n\s*|\s*$)~', $text, -1, $flags );

		$inline = preg_match( '~\n~', $text ) === 0;

		$template = '';
		$sections = [];

		foreach ( $parts as $_ ) {
			if ( trim( $_ ) === '' ) {
				$template .= $_;
			} else {
				$ph = $this->placeholderFactory->make();
				$tpsection = $this->parseUnit( $_ );
				$tpsection->setIsInline( $inline );
				$tpsection->setCanWrap( $canWrap );
				$sections[$ph] = $tpsection;
				$template .= $ph;
			}
		}

		return [
			'template' => $template,
			'sections' => $sections,
		];
	}

	/**
	 * Checks if this unit already contains a section marker. If there
	 * is not, a new one will be created. Marker will have the value of
	 * -1, which will later be replaced with a real value.
	 * @internal
	 */
	public function parseUnit( string $content ): TranslationUnit {
		$re = '~<!--T:(.*?)-->~';
		$matches = [];
		$count = preg_match_all( $re, $content, $matches, PREG_SET_ORDER );

		if ( $count > 1 ) {
			throw new ParsingFailure(
				'Multiple translation unit markers',
				[ 'pt-shake-multiple', $content ]
			);
		}

		// If no id given in the source, default to a new section id
		$id = TranslationUnit::NEW_UNIT_ID;
		if ( $count === 1 ) {
			foreach ( $matches as $match ) {
				[ /*full*/, $id ] = $match;

				// Currently handle only these two standard places.
				// Is this too strict?
				$rer1 = '~^<!--T:(.*?)-->( |\n)~'; // Normal sections
				$rer2 = '~\s*<!--T:(.*?)-->$~m'; // Sections with title
				$content = preg_replace( $rer1, '', $content );
				$content = preg_replace( $rer2, '', $content );

				if ( preg_match( $re, $content ) === 1 ) {
					throw new ParsingFailure(
						'Translation unit marker is in unsupported position',
						[ 'pt-shake-position', $content ]
					);
				} elseif ( trim( $content ) === '' ) {
					throw new ParsingFailure(
						'Translation unit has no content besides marker',
						[ 'pt-shake-empty', $id ]
					);
				}
			}
		}

		return new TranslationUnit( $content, $id );
	}

	/** @internal */
	public function armourNowiki( array &$holders, string $text ): string {
		$re = '~(<nowiki>)(.*?)(</nowiki>)~s';

		while ( preg_match( $re, $text, $matches ) ) {
			$ph = $this->placeholderFactory->make();
			$text = str_replace( $matches[0], $ph, $text );
			$holders[$ph] = $matches[0];
		}

		return $text;
	}

	/** @internal */
	public function unarmourNowiki( array $holders, string $text ): string {
		return strtr( $text, $holders );
	}
}
