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

	public function __construct(
		private readonly SplObjectStorage $blockers,
	) {
		parent::__construct();
	}

	public function getBlockers(): SplObjectStorage {
		return $this->blockers;
	}
}
