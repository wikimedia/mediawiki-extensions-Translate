<?php

namespace MediaWiki\Extension\Translate\Statistics\Output;

/**
 * A general output object. Need to be overridden
 */
class StatsOutput {
	/**
	 * @param int|float $subset
	 * @param int|float $total
	 * @param bool $revert
	 * @param int|float $accuracy
	 * @return string
	 */
	public function formatPercent( $subset, $total, $revert = false, $accuracy = 2 ) {
		// phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
		$return = @sprintf( '%.' . $accuracy . 'f%%', 100 * $subset / $total );

		return $return;
	}

	public function heading() {
	}

	public function footer() {
	}

	public function blockstart() {
	}

	public function blockend() {
	}

	/**
	 * @param string|float|int $in
	 * @param bool $heading
	 */
	public function element( $in, $heading = false ) {
	}
}
