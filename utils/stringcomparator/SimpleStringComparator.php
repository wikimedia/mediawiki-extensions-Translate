<?php
/**
 * Contains a simple string compare class.
 * @license GPL-2.0-or-later
 */

/**
 * A simple string comparator, that compares two strings and determines if they are an exact match.
 * @since 2019.06
 */
class SimpleStringComparator implements StringComparator {
	/**
	 * @inheritDoc
	 */
	public function getSimilarity( $addedMessage, $deletedMessage ) {
		if ( $addedMessage === $deletedMessage ) {
			return 100;
		}

		if ( trim( mb_strtolower( $addedMessage ) ) === trim( mb_strtolower( $deletedMessage ) ) ) {
			return 95;
		}

		return 0;
	}
}
