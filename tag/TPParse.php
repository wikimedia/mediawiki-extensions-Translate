<?php
/**
 * Helper code for TranslatablePage.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2009-2013 Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * This class represents the results of parsed source page, that is, the
 * extracted sections and a template.
 *
 * @ingroup PageTranslation
 */
class TPParse {
	/// \type{Title} Title of the page.
	protected $title;

	/** \arrayof{String,TPSection} Parsed sections indexed with placeholder.
	 * @todo Encapsulate
	 */
	public $sections = array();
	/** \string Page source with content replaced with placeholders.
	 * @todo Encapsulate
	 */
	public $template = null;
	/**
	 * @var null|array Sections saved in the database. array( string => TPSection, ... )
	 */
	protected $dbSections = null;

	/// Constructor
	public function __construct( Title $title ) {
		$this->title = $title;
	}

	/**
	 * Returns the number of sections in this page.
	 * @return \int
	 */
	public function countSections() {
		return count( $this->sections );
	}

	/**
	 * Returns the page template where translatable content is replaced with
	 * placeholders.
	 * @return \string
	 */
	public function getTemplate() {
		return $this->template;
	}

	/**
	 * Returns the page template where the ugly placeholders are replaced with
	 * section markers. Sections which previously had no number will get one
	 * assigned now.
	 * @return \string
	 */
	public function getTemplatePretty() {
		$text = $this->template;
		$sections = $this->getSectionsForSave();
		foreach ( $sections as $ph => $s ) {
			$text = str_replace( $ph, "<!--T:{$s->id}-->", $text );
		}

		return $text;
	}

	/**
	 * Gets the sections and assigns section id for new sections
	 * @param int $highest The largest used integer id (Since 2012-08-02)
	 * @return array array( string => TPSection, ... )
	 */
	public function getSectionsForSave( $highest = 0 ) {
		$this->loadFromDatabase();

		$sections = $this->sections;
		foreach ( array_keys( $this->dbSections ) as $key ) {
			$highest = max( $highest, (int)$key );
		}

		foreach ( $sections as $_ ) {
			$highest = max( $highest, (int)$_->id );
		}

		foreach ( $sections as $s ) {
			$s->type = 'old';

			if ( $s->id === -1 ) {
				$s->type = 'new';
				$s->id = ++$highest;
			} else {
				if ( isset( $this->dbSections[$s->id] ) ) {
					$storedText = $this->dbSections[$s->id]->text;
					if ( $s->text !== $storedText ) {
						$s->type = 'changed';
						$s->oldText = $storedText;
					}
				}
			}
		}

		return $sections;
	}

	/**
	 * Returns list of deleted sections.
	 * @return array List of sections indexed by id. array( string => TPsection, ... )
	 */
	public function getDeletedSections() {
		$sections = $this->getSectionsForSave();
		$deleted = $this->dbSections;

		foreach ( $sections as $s ) {
			if ( isset( $deleted[$s->id] ) ) {
				unset( $deleted[$s->id] );
			}
		}

		return $deleted;
	}

	/**
	 * Load section saved in the database. Populates dbSections.
	 */
	protected function loadFromDatabase() {
		if ( $this->dbSections !== null ) {
			return;
		}

		$this->dbSections = array();

		$db = TranslateUtils::getSafeReadDB();
		$tables = 'translate_sections';
		$vars = array( 'trs_key', 'trs_text' );
		$conds = array( 'trs_page' => $this->title->getArticleID() );

		$res = $db->select( $tables, $vars, $conds, __METHOD__ );
		foreach ( $res as $r ) {
			$section = new TPSection;
			$section->id = $r->trs_key;
			$section->text = $r->trs_text;
			$section->type = 'db';
			$this->dbSections[$r->trs_key] = $section;
		}
	}

	/**
	 * Returns the source page with translation section mark-up added.
	 *
	 * @return string Wikitext.
	 */
	public function getSourcePageText() {
		$text = $this->template;

		foreach ( $this->sections as $ph => $s ) {
			$text = str_replace( $ph, $s->getMarkedText(), $text );
		}

		return $text;
	}

	/**
	 * Returns translation page with all possible translations replaced in
	 * and ugly translation tags removed.
	 *
	 * @param MessageCollection $collection Collection that holds translated messages.
	 * @return string Whole page as wikitext.
	 */
	public function getTranslationPageText( $collection ) {
		$text = $this->template; // The source

		// For finding the messages
		$prefix = $this->title->getPrefixedDBkey() . '/';

		if ( $collection instanceof MessageCollection ) {
			$collection->loadTranslations();
			$collection->filter( 'translated', false );
		}

		foreach ( $this->sections as $ph => $s ) {
			$sectiontext = null;

			if ( isset( $collection[$prefix . $s->id] ) ) {
				/**
				 * @var TMessage $msg
				 */
				$msg = $collection[$prefix . $s->id];
				$sectiontext = $msg->translation();
			}

			// Use the original text if no translation is available.

			// For the source language, this will actually be the source, which
			// contains variable declarations (tvar) instead of variables ($1).
			// The getTextWithVariables will convert declarations to normal variables
			// for us so that the variable substitutions below will also work
			// for the source language.
			if ( $sectiontext === null || $sectiontext === $s->getText() ) {
				$sectiontext = $s->getTextWithVariables();
			}

			// Substitute variables into section text and substitute text into document
			$sectiontext = strtr( $sectiontext, $s->getVariables() );
			$text = str_replace( $ph, $sectiontext, $text );
		}

		$nph = array();
		$text = TranslatablePage::armourNowiki( $nph, $text );

		// Remove translation markup from the template to produce final text
		$cb = array( __CLASS__, 'replaceTagCb' );
		$text = preg_replace_callback( '~(<translate>)(.*)(</translate>)~sU', $cb, $text );
		$text = TranslatablePage::unArmourNowiki( $nph, $text );

		return $text;
	}

	/**
	 * Chops of trailing or preceeding whitespace intelligently to avoid
	 * build up of unintented whitespace.
	 * @param array $matches
	 * @return string
	 */
	protected static function replaceTagCb( $matches ) {
		return preg_replace( '~^\n|\n\z~', '', $matches[2] );
	}
}
