<?php
/**
 * Translatable page parse exception.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2009-2013 Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Class to signal syntax errors in translatable pages.
 *
 * @ingroup PageTranslation
 */
class TPException extends MWException {
	private $msg;

	/** @param array $msg Message key with parameters */
	public function __construct( array $msg ) {
		$this->msg = $msg;
		// Using ->plain() instead of ->text() due to bug T58226
		$wikitext = wfMessage( ...$msg )->plain();
		parent::__construct( $wikitext );
	}

	public function getMsg(): array {
		return $this->msg;
	}
}
