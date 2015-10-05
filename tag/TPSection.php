<?php
/**
 * Helper for TPParse.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2009-2013 Niklas Laxström
 * @license GPL-2.0+
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
		$re = '~<tvar\|([^>]+)>(.*?)</>~u';

		return preg_replace( $re, '$\1', $this->text );
	}

	/**
	 * Returns section text with variables replaced.
	 * @return string Wikitext.
	 */
	public function getTextForTrans() {
		$re = '~<tvar\|([^>]+)>(.*?)</>~u';

		return preg_replace( $re, '\2', $this->text );
	}

	/**
	 * Returns the section text with updated or added section marker.
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
			$text = $header . "\n" . $this->text;
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
		$re = '~<tvar\|([^>]+)>(.*?)</>~u';
		$matches = array();
		preg_match_all( $re, $this->text, $matches, PREG_SET_ORDER );
		$vars = array();

		foreach ( $matches as $m ) {
			$vars['$' . $m[1]] = $m[2];
		}

		return $vars;
	}
}
