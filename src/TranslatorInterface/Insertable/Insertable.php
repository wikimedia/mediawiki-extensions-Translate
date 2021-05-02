<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Insertable;

/**
 * Insertable is a string that usually does not need translation and is
 * difficult to type manually.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2020.12
 */
class Insertable {
	/** @var string What to show to the user */
	protected $display;
	/** @var string What to insert before selection */
	protected $pre;
	/** @var string What to insert after selection */
	protected $post;

	/**
	 * @param string $display What to show to the user
	 * @param string $pre What to insert before selection
	 * @param string $post What to insert after selection
	 */
	public function __construct( string $display, string $pre = '', string $post = '' ) {
		$this->display = $display;
		$this->pre = $pre;
		$this->post = $post;
	}

	public function getPreText(): string {
		return $this->pre;
	}

	public function getPostText(): string {
		return $this->post;
	}

	public function getDisplayText(): string {
		return $this->display;
	}
}
