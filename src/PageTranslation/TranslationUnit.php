<?php

namespace MediaWiki\Extension\Translate\PageTranslation;

use Html;
use Language;
use TMessage;

/**
 * This class represents one translation unit in a translatable page.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup PageTranslation
 */
class TranslationUnit {
	public const UNIT_MARKER_INVALID_CHARS = "_/\n<>";
	/** @var string Section name */
	public $id;
	/** @var string|null New name of the section, that will be saved to database. */
	public $name = null;
	/** @var string Section text. */
	public $text;
	/** @var string Is this new, existing, changed or deleted section. */
	public $type;
	/** @var string|null Text of previous version of this section. */
	public $oldText = null;
	/**
	 * @var bool Whether this section is inline section.
	 * E.g. "Something <translate>foo</translate> bar".
	 */
	protected $inline = false;
	/** @var bool Whether wrapping the section is allowed */
	private $canWrap = true;
	/** @var int Version number for the serialization. */
	private $version = 1;
	/** @var string[] List of properties to serialize. */
	private static $properties = [ 'version', 'id', 'name', 'text', 'type', 'oldText', 'inline' ];

	public function setIsInline( bool $value ): void {
		$this->inline = $value;
	}

	public function isInline(): bool {
		return $this->inline;
	}

	public function setCanWrap( bool $value ): void {
		$this->canWrap = $value;
	}

	public function canWrap(): bool {
		return $this->canWrap;
	}

	/** Returns section text unmodified */
	public function getText(): string {
		return $this->text;
	}

	/** Returns the text with tvars replaces with placeholders */
	public function getTextWithVariables(): string {
		$re = '~<tvar\|([^>]+)>(.*?)</>~us';

		return preg_replace( $re, '$\1', $this->text );
	}

	/** Returns section text with variables replaced. */
	public function getTextForTrans(): string {
		$re = '~<tvar\|([^>]+)>(.*?)</>~us';

		return preg_replace( $re, '\2', $this->text );
	}

	/** Returns the section text with updated or added section marker */
	public function getMarkedText(): string {
		$id = $this->name ?? $this->id;
		$header = "<!--T:{$id}-->";
		$re = '~^(=+.*?=+\s*?$)~m';
		$rep = "\\1 $header";
		$count = 0;

		$text = preg_replace( $re, $rep, $this->text, 1, $count );

		if ( $count === 0 ) {
			if ( $this->inline ) {
				$text = $header . ' ' . $this->text;
			} else {
				$text = $header . "\n" . $this->text;
			}
		}

		return $text;
	}

	/** Returns oldtext, or current text if not available */
	public function getOldText(): string {
		return $this->oldText ?? $this->text;
	}

	/** Returns array of variables and their values defined on this unit */
	public function getVariables(): array {
		$re = '~<tvar\|([^>]+)>(.*?)</>~us';
		$matches = [];
		preg_match_all( $re, $this->text, $matches, PREG_SET_ORDER );
		$vars = [];

		foreach ( $matches as $m ) {
			$vars['$' . $m[1]] = $m[2];
		}

		return $vars;
	}

	/** Serialize this object to a PHP array */
	public function serializeToArray(): array {
		$data = [];
		foreach ( self::$properties as $index => $property ) {
			// Because this is used for the JobQueue, use a list
			// instead of an array to save space.
			$data[$index] = $this->$property;
		}

		return $data;
	}

	/** Construct an object from previously serialized array */
	public static function unserializeFromArray( $data ): self {
		$section = new self();
		foreach ( self::$properties as $index => $property ) {
			$section->$property = $data[$index];
		}

		return $section;
	}

	public function getTextForRendering(
		?TMessage $msg,
		Language $sourceLanguage,
		Language $targetLanguage,
		bool $wrapUntranslated
	): string {
		$attributes = [];

		if ( $msg && $msg->translation() !== null ) {
			$content = $msg->translation();
			if ( $msg->hasTag( 'fuzzy' ) ) {
				// We do not ever want to show explicit fuzzy marks in the rendered pages
				$content = str_replace( TRANSLATE_FUZZY, '', $content );
				$attributes['class'] = 'mw-translate-fuzzy';
			}
			$translationLanguage = $targetLanguage->getCode();
		} else {
			$content = $this->getTextWithVariables();
			if ( $wrapUntranslated ) {
				$attributes['lang'] = $sourceLanguage->getHtmlCode();
				$attributes['dir'] = $sourceLanguage->getDir();
				$attributes['class'] = 'mw-content-' . $sourceLanguage->getDir();
			}
			$translationLanguage = $sourceLanguage->getCode();
		}

		if ( $this->canWrap() && $attributes ) {
			$tag = $this->isInline() ? 'span' : 'div';
			$content = $this->isInline() ? $content : "\n$content\n";
			$content = Html::rawElement( $tag, $attributes, $content );
		}

		$content = strtr( $content, $this->getVariables() );

		// Allow wrapping this inside variables
		$content = preg_replace(
			'/\{\{\s*TRANSLATIONLANGUAGE\s*\}\}/',
			$translationLanguage,
			$content
		);

		return $content;
	}
}
