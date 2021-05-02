<?php

namespace MediaWiki\Extension\Translate\Utilities\StringComparators;

/**
 * An interface to be implemented by comparators that will compare percentage
 * of similarity between strings.
 */
interface StringComparator {
	/**
	 * Compares the two messages and returns a similarity percentage
	 *
	 * @param string $a
	 * @param string $b
	 * @return float 0-1 with 1 being an exact match
	 */
	public function getSimilarity( $a, $b );
}
