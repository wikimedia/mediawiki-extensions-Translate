<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

/**
 * Section is one pair of <translate>...</translate> tags.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2020.08
 */
class Section {
	/** @var string */
	private $open;
	/** @var string */
	private $contents;
	/** @var string */
	private $close;

	public function __construct( string $open, string $contents, string $close ) {
		$this->open = $open;
		$this->contents = $contents;
		$this->close = $close;
	}

	public function contents(): string {
		// If <translate> tags are on their own line, avoid build-up of newlines
		return preg_replace( '/\A\n|\n\z/', '', $this->contents );
	}

	public function wrappedContents(): string {
		return $this->open . $this->contents . $this->close;
	}
}
