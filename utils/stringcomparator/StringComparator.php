<?php
/**
 * An interface to be implemented by comparators that will compare percentage
 * of similarity between strings.
 */
interface StringComparator {
	/**
	 * Compares the two messages and returns a similarity percentage
	 *
	 * @param string $addedMessage
	 * @param string $deletedMessage
	 * @return int 0-100
	 */
	public function getSimilarity( $addedMessage, $deletedMessage );
}
