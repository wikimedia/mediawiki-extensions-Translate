<?php
/**
 * Helper for TPParse.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * This class represents one individual section in translatable page.
 *
 * @ingroup PageTranslation
 */
class TPSection {
	/**
	 * @var string Section name
	 */
	public $id;

	/**
	 * @var string New name of the section, that will be saved to database.
	 */
	public $name;

	/**
	 * @var string Section text.
	 */
	public $text;

	/**
	 * @var string Is this new, existing, changed or deleted section.
	 */
	public $type;

	/**
	 * @var string Text of previous version of this section.
	 */
	public $oldText;

	/**
	 * @var bool Whether this section is inline section.
	 * E.g. "Something <translate>foo</translate> bar".
	 */
	protected $inline = false;

	/**
	 * @var int Version number for the serialization.
	 */
	private $version = 1;

	/**
	 * @var string[] List of properties to serialize.
	 */
	private static $properties = [ 'version', 'id', 'name', 'text', 'type', 'oldText', 'inline' ];

	public function setIsInline( $value ) {
		$this->inline = (bool)$value;
	}

	public function isInline() {
		return $this->inline;
	}

	/**
	 * Returns section text unmodified.
	 * @return string Wikitext.
	 */
	public function getText() {
		return $this->text;
	}

	/**
	 * Returns the text with tvars replaces with placeholders.
	 * @return string Wikitext.
	 * @since 2014.07
	 */
	public function getTextWithVariables() {
		$re = '~<tvar\|([^>]+)>(.*?)</>~us';

		return preg_replace( $re, '$\1', $this->text );
	}

	/**
	 * Returns section text with variables replaced.
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
		$id = isset( $this->name ) ? $this->name : $this->id;
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
	 * @return string Wikitext.
	 */
	public function getOldText() {
		return isset( $this->oldText ) ? $this->oldText : $this->text;
	}

	/**
	 * Returns array of variables defined on this section.
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
	 * @return array
	 * @since 2018.07
	 */
	public function serializeToArray() {
		$data = [];
		foreach ( self::$properties as $index => $property ) {
			// Because this is used for the JobQueue, use a list
			// instead of an array to save space.
			$data[ $index ] = $this->$property;
		}

		return $data;
	}

	/**
	 * Construct an object from previously serialized array.
	 * @param array $data
	 * @return TPSection
	 * @since 2018.07
	 */
	public static function unserializeFromArray( $data ) {
		$section = new self;
		foreach ( self::$properties as $index => $property ) {
			$section->$property = $data[ $index ];
		}

		return $section;
	}
}
