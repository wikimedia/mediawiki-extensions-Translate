<?php
/**
 * Value object for insertables.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Insertable is a string that usually does not need translation and is
 * difficult to type manually.
 * @since 2013.09
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
	public function __construct( $display, $pre = '', $post = '' ) {
		$this->display = $display;
		$this->pre = $pre;
		$this->post = $post;
	}

	public function getPreText() {
		return $this->pre;
	}

	public function getPostText() {
		return $this->post;
	}

	public function getDisplayText() {
		return $this->display;
	}
}
