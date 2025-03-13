<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Extension\Translate\MessageLoading\Message;
use MediaWiki\Html\Html;
use MediaWiki\Language\Language;
use MediaWiki\Parser\Parser;
use const PREG_SET_ORDER;

/**
 * This class represents one translation unit in a translatable page.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup PageTranslation
 */
class TranslationUnit {
	public const UNIT_MARKER_INVALID_CHARS = "_/\n<>";
	public const NEW_UNIT_ID = '-1';
	// Deprecated syntax. Example: <tvar|1>...</>
	public const TVAR_OLD_SYNTAX_REGEX = '~<tvar\|([^>]+)>(.*?)</>~us';
	// Current syntax. Example: <tvar name=1>...</tvar>
	public const TVAR_NEW_SYNTAX_REGEX =
		<<<'REGEXP'
		~
		<tvar \s+ name \s* = \s*
		( ( ' (?<key1> [^']* ) ' ) | ( " (?<key2> [^"]* ) " ) | (?<key3> [^"'\s>]* ) )
		\s* > (?<value>.*?) </tvar \s* >
		~xusi
		REGEXP;
	/**
	 * Regular expression matching the `{{TRANSLATIONLANGUAGE}}` “magic word”
	 * (which is not a real magic word, but rather replaced in the source text)
	 */
	public const TRANSLATIONLANGUAGE_REGEX = '/{{\s*TRANSLATIONLANGUAGE\s*}}/';

	/** @var string Unit name */
	public $id;
	/** @var string Unit text. */
	public $text;
	/** @var string Is this new, existing, changed or deleted unit. */
	public $type;
	/** @var string|null Text of previous version of this unit. */
	public $oldText = null;
	/**
	 * @var bool Whether this unit is inline unit.
	 * E.g. "Something <translate>foo</translate> bar".
	 */
	protected $inline = false;
	/** @var bool Whether wrapping the unit is allowed */
	private $canWrap = true;
	/** @var int Version number for the serialization. */
	private $version = 1;
	/** @var string[] List of properties to serialize. */
	private static $properties = [ 'version', 'id', 'text', 'type', 'oldText', 'inline' ];

	public function __construct(
		string $text,
		string $id = self::NEW_UNIT_ID,
		string $type = 'new',
		?string $oldText = null
	) {
		$this->text = $text;
		$this->id = $id;
		$this->type = $type;
		$this->oldText = $oldText;
	}

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

	/** Returns unit text unmodified */
	public function getText(): string {
		return $this->text;
	}

	/** Returns the text with tvars replaces with placeholders */
	public function getTextWithVariables(): string {
		return $this->replaceVariablesWithNames( $this->text );
	}

	private function replaceVariablesWithNames( string $text ): string {
		$variableReplacements = [];
		foreach ( $this->loadVariables( $text ) as $variable ) {
			$variableReplacements[$variable->getDefinition()] = $variable->getName();
		}

		return strtr( $text, $variableReplacements );
	}

	/** Returns unit text with variables replaced. */
	public function getTextForTrans(): string {
		$variableReplacements = [];
		foreach ( $this->getVariables() as $variable ) {
			$variableReplacements[$variable->getDefinition()] = $variable->getValue();
		}

		return strtr( $this->text, $variableReplacements );
	}

	/** Returns whether all changes to the unit were done inside tvars */
	public function onlyTvarsChanged(): bool {
		if ( $this->oldText === null ) {
			// This shouldn't ever be called if oldText is null, but just in case
			return false;
		}
		$newText = $this->getTextWithVariables();
		$oldText = $this->replaceVariablesWithNames( $this->oldText );
		return $oldText === $newText;
	}

	/** Returns the unit text with updated or added unit marker */
	public function getMarkedText(): string {
		$id = $this->id;
		$header = "<!--T:$id-->";

		if ( $this->getHeading( $this->text ) !== null ) {
			$text = $this->text . ' ' . $header;
		} else {
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

	/** @return TranslationVariable[] */
	public function getVariables(): array {
		return $this->loadVariables( $this->text );
	}

	/** @return TranslationVariable[] */
	private function loadVariables( string $text ): array {
		$vars = [];

		$matches = [];
		preg_match_all( self::TVAR_OLD_SYNTAX_REGEX, $text, $matches, PREG_SET_ORDER );
		foreach ( $matches as $m ) {
			$vars[] = new TranslationVariable( $m[0], '$' . $m[1], $m[2] );
		}

		$matches = [];
		preg_match_all( self::TVAR_NEW_SYNTAX_REGEX, $text, $matches, PREG_SET_ORDER );
		foreach ( $matches as $m ) {
			$vars[] = new TranslationVariable(
				$m[0],
				// Maximum of one of these is non-empty string
				'$' . ( $m['key1'] . $m['key2'] . $m['key3'] ),
				$m['value']
			);
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

	public static function unserializeFromArray( array $data ): self {
		// Give dummy default text, will be overridden
		$unit = new self( '' );
		foreach ( self::$properties as $index => $property ) {
			$unit->$property = $data[$index];
		}

		return $unit;
	}

	public function getTextForRendering(
		?Message $msg,
		Language $sourceLanguage,
		Language $targetLanguage,
		bool $wrapUntranslated,
		?Parser $parser = null
	): string {
		$attributes = [];
		$headingText = null;

		$content = $msg ? $msg->translation() : null;
		if ( $content !== null ) {
			$headingText = $this->getHeading( $msg->definition() );

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

		$variableReplacements = [];
		foreach ( $this->getVariables() as $variable ) {
			$variableReplacements[$variable->getName()] = $variable->getValue();
		}

		if (
			$parser &&
			$this->shouldAddAnchor(
				$sourceLanguage,
				$targetLanguage,
				$headingText,
				$msg,
				$this->isInline()
			)
		) {
			$sectionName = substr( $parser->guessSectionNameFromWikiText( $headingText ), 1 );
			$attributes = [ 'id' => $sectionName ];
			$content = Html::rawElement( 'span', $attributes, '' ) . "\n$content";
		}

		$content = strtr( $content, $variableReplacements );

		// Allow wrapping this inside variables
		$content = preg_replace(
			self::TRANSLATIONLANGUAGE_REGEX,
			$translationLanguage,
			$content
		);

		return $content;
	}

	/** @return TranslationUnitIssue[] */
	public function getIssues(): array {
		$issues = $usedNames = [];
		foreach ( $this->getVariables() as $variable ) {
			$name = $variable->getName();
			$pattern = '/^' . TranslatablePageInsertablesSuggester::NAME_PATTERN . '$/u';
			if ( !preg_match( $pattern, $name ) ) {
				// Key by name to avoid multiple issues of the same name
				$issues[$name] = new TranslationUnitIssue(
					TranslationUnitIssue::WARNING,
					'tpt-validation-not-insertable',
					[ wfEscapeWikiText( $name ) ]
				);
			}

			$usedNames[ $name ][] = $variable->getValue();
		}

		foreach ( $usedNames as $name => $contents ) {
			$uniqueValueCount = count( array_unique( $contents ) );
			if ( $uniqueValueCount > 1 ) {
				$issues[] = new TranslationUnitIssue(
					TranslationUnitIssue::ERROR,
					'tpt-validation-name-reuse',
					[ wfEscapeWikiText( $name ) ]
				);
			}
		}

		return array_values( $issues );
	}

	/** Mimic the behavior of how Parser handles headings including handling of unbalanced "=" signs */
	private function getHeading( string $text ): ?string {
		$match = [];
		preg_match( '/^(={1,6})[ \t]*(.+?)[ \t]*\1\s*$/', $text, $match );
		return $match[2] ?? null;
	}

	private function shouldAddAnchor(
		Language $sourceLanguage,
		Language $targetLanguage,
		?string $headingText,
		?Message $msg,
		bool $isInline
	): bool {
		// If it's not a heading, don't bother adding an anchor
		if ( $headingText === null ) {
			return false;
		}

		// We only add an anchor for a translation. See: https://phabricator.wikimedia.org/T62544
		if ( $sourceLanguage->getCode() === $targetLanguage->getCode() ) {
			return false;
		}

		// Translation and the source text are same, avoid adding an anchor that would create
		// an id attribute with duplicate value
		if ( $msg && $msg->translation() === $msg->definition() ) {
			return false;
		}

		// If nowrap attribute is set, do not add the anchor
		if ( !$this->canWrap() ) {
			return false;
		}

		// We don't add anchors for inline translate tags to avoid breaking input like this:
		// Text here <translate>== not a heading ==</translate>
		return !$isInline;
	}
}
