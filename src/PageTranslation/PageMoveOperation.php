<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use Title;

/**
 * Represents a single page being moved.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2021.09
 */
class PageMoveOperation {
	/** @var Title */
	private $old;
	/** @var Title|null */
	private $new;

	public function __construct( Title $old, ?Title $new ) {
		$this->old = $old;
		$this->new = $new;
	}

	public function getOldTitle(): Title {
		return $this->old;
	}

	public function getNewTitle(): ?Title {
		return $this->new;
	}
}
