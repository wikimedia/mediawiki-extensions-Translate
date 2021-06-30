<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use Exception;
use SplObjectStorage;

/**
 * Exception thrown when a translatable page move is not possible
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2021.09
 */
class ImpossiblePageMove extends Exception {
	/** @var SplObjectStorage */
	private $blockers;

	public function __construct( SplObjectStorage $blockers ) {
		parent::__construct();
		$this->blockers = $blockers;
	}

	public function getBlockers(): SplObjectStorage {
		return $this->blockers;
	}
}
