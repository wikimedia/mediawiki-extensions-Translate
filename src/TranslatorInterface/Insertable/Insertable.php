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

	/**
	 * @param string $display What to show to the user
	 * @param string $pre What to insert before selection, or replace
	 * selection if $post remains empty
	 * @param string $post What to insert after selection. If it is not
	 * given, $pre will replace selection.
	 */
	public function __construct(
		private readonly string $display,
		private readonly string $pre = '',
		private readonly string $post = '',
	) {
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
