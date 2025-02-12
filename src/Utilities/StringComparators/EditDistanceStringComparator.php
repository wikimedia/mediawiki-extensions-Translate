<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities\StringComparators;

/**
 * Smart string comparator that uses simple string comparison, and then
 * the levenshtein algorithm to compare two strings.
 * @author Abijeet Patro
 * @since 2023.11
 * @license GPL-2.0-or-later
 */
class EditDistanceStringComparator implements StringComparator {
	private SimpleStringComparator $simpleStringComparator;

	public function __construct() {
		$this->simpleStringComparator = new SimpleStringComparator();
	}

	/** @inheritDoc */
	public function getSimilarity( $a, $b ) {
		$similarity = $this->simpleStringComparator->getSimilarity( $a, $b );
		if ( $similarity > 0.9 ) {
			return $similarity;
		}

		$similarity = $this->levenshtein( $a, $b, mb_strlen( $a ), mb_strlen( $b ) );
		if ( $similarity === -1 ) {
			return 0;
		}

		// See: https://stackoverflow.com/a/59585447
		$maxLength = max( strlen( $a ), strlen( $b ) );
		return ( $maxLength - $similarity ) / $maxLength;
	}

	/**
	 * PHP implementation of Levenshtein edit distance algorithm. Uses the native PHP implementation
	 * when possible for speed. The native levenshtein is limited to 255 bytes.
	 */
	public function levenshtein( string $str1, string $str2, int $length1, int $length2 ): int {
		if ( $length1 === 0 ) {
			return $length2;
		}

		if ( $length2 === 0 ) {
			return $length1;
		}

		if ( $str1 === $str2 ) {
			return 0;
		}

		$byteLength1 = strlen( $str1 );
		$byteLength2 = strlen( $str2 );
		if ( $byteLength1 === $length1 && $byteLength1 <= 255
			&& $byteLength2 === $length2 && $byteLength2 <= 255
		) {
			return levenshtein( $str1, $str2 );
		}

		$prevRow = range( 0, $length2 );
		for ( $i = 0; $i < $length1; $i++ ) {
			$currentRow = [];
			$currentRow[0] = $i + 1;
			$c1 = mb_substr( $str1, $i, 1 );
			for ( $j = 0; $j < $length2; $j++ ) {
				$c2 = mb_substr( $str2, $j, 1 );
				$insertions = $prevRow[$j + 1] + 1;
				$deletions = $currentRow[$j] + 1;
				$substitutions = $prevRow[$j] + ( ( $c1 !== $c2 ) ? 1 : 0 );
				$currentRow[] = min( $insertions, $deletions, $substitutions );
			}
			$prevRow = $currentRow;
		}

		return $prevRow[$length2];
	}
}
