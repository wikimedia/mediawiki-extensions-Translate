<?php
/**
 * Helper for TPParse.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extension\Translate\PageTranslation;

use Html;
use Language;
use TMessage;

/**
 * This class represents one individual section in translatable page.
 *
 * @ingroup PageTranslation
 */
class TPSection {
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

	public function setIsInline( $value ) {
		$this->inline = (bool)$value;
	}

	public function isInline() {
		return $this->inline;
	}

	/**
	 * @param bool $value
	 * @since 2020.07
	 */
	public function setCanWrap( bool $value ): void {
		$this->canWrap = $value;
	}

	/**
	 * @return bool
	 * @since 2020.07
	 */
	public function canWrap(): bool {
		return $this->canWrap;
	}

	/**
	 * Returns section text unmodified.
	 *
	 * @return string Wikitext.
	 */
	public function getText() {
		return $this->text;
	}

	/**
	 * Returns the text with tvars replaces with placeholders.
	 *
	 * @return string Wikitext.
	 * @since 2014.07
	 */
	public function getTextWithVariables() {
		$re = '~<tvar\|([^>]+)>(.*?)</>~us';

		return preg_replace( $re, '$\1', $this->text );
	}

	/**
	 * Returns section text with variables replaced.
	 *
	 * @return string Wikitext.
	 */
	public function getTextForTrans() {
		$re = '~<tvar\|([^>]+)>(.*?)</>~us';

		return preg_replace( $re, '\2', $this->text );
	}

	/**
	 * Returns the section text with updated or added section marker.
	 *
	 * @return string Wikitext.
	 */
	public function getMarkedText() {
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

	/**
	 * Returns oldtext, or current text if not available.
	 *
	 * @return string Wikitext.
	 */
	public function getOldText() {
		return $this->oldText ?? $this->text;
	}

	/**
	 * Returns array of variables defined on this section.
	 *
	 * @return array ( string => string ) Values indexed with keys which are
	 * prefixed with a dollar sign.
	 */
	public function getVariables() {
		$re = '~<tvar\|([^>]+)>(.*?)</>~us';
		$matches = [];
		preg_match_all( $re, $this->text, $matches, PREG_SET_ORDER );
		$vars = [];

		foreach ( $matches as $m ) {
			$vars['$' . $m[1]] = $m[2];
		}

		return $vars;
	}

	/**
	 * Serialize this object to a PHP array.
	 *
	 * @return array
	 * @since 2018.07
	 */
	public function serializeToArray() {
		$data = [];
		foreach ( self::$properties as $index => $property ) {
			// Because this is used for the JobQueue, use a list
			// instead of an array to save space.
			$data[$index] = $this->$property;
		}

		return $data;
	}

	/**
	 * Construct an object from previously serialized array.
	 *
	 * @param array $data
	 * @return self
	 * @since 2018.07
	 */
	public static function unserializeFromArray( $data ) {
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
