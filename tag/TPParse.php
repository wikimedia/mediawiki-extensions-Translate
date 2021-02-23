<?php
/**
 * Helper code for TranslatablePage.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2009-2013 Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\PageTranslation\TranslationUnit;

/**
 * This class represents the results of parsed source page, that is, the
 * extracted sections and a template.
 *
 * @ingroup PageTranslation
 */
class TPParse {
	/** @var Title Title of the page. */
	protected $title;
	/**
	 * @todo Encapsulate
	 * @var TranslationUnit[] Parsed sections indexed with placeholder.
	 */
	public $sections = [];
	/**
	 * @todo Encapsulate
	 * @var string Page source with content replaced with placeholders.
	 */
	public $template = null;
	/** @var null|array Sections saved in the database. array( string => TranslationUnit, ... ) */
	protected $dbSections = null;

	/// Constructor
	public function __construct( Title $title ) {
		$this->title = $title;
	}

	/**
	 * Returns the number of sections in this page.
	 * @return int
	 */
	public function countSections() {
		return count( $this->sections );
	}

	/**
	 * Returns the page template where translatable content is replaced with
	 * placeholders.
	 * @return string
	 */
	public function getTemplate() {
		return $this->template;
	}

	/**
	 * Returns the page template where the ugly placeholders are replaced with
	 * section markers. Sections which previously had no number will get one
	 * assigned now.
	 * @return string
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
	 *
	 * @param int $highest The largest used integer id (Since 2012-08-02)
	 * @return TranslationUnit[] array( string => TranslationUnit, ... )
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
	 *
	 * @return TranslationUnit[] List of sections indexed by id. array( string => TranslationUnit, ... )
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

		$this->dbSections = [];

		$db = TranslateUtils::getSafeReadDB();
		$tables = 'translate_sections';
		$vars = [ 'trs_key', 'trs_text' ];
		$conds = [ 'trs_page' => $this->title->getArticleID() ];

		$res = $db->select( $tables, $vars, $conds, __METHOD__ );
		foreach ( $res as $r ) {
			$section = new TranslationUnit;
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
	 * Chops of trailing or preceding whitespace intelligently to avoid
	 * build up of unintended whitespace.
	 * @param string[] $matches
	 * @return string
	 */
	protected static function replaceTagCb( $matches ) {
		return preg_replace( '~^\n|\n\z~', '', $matches[1] );
	}
}
