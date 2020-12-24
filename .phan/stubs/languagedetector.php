<?php

/**
 * stub for crodas/LanguageDetector
 * @phpcs:disable MediaWiki.Files.ClassMatchesFilename
 */

namespace LanguageDetector;

class Learn {

	public function __construct( Config $config ) {
	}

	/**
	 * @param callable $callback
	 * return self
	 */
	public function addStepCallback( $callback ) {
	}

	/**
	 * @param string $label
	 * @param string $text
	 */
	public function addSample( $label, $text ) {
	}

	public function clear() {
	}

	public function save( AbstractFormat $output ) {
	}

}

class Config {

	/** @param bool $use */
	public function useMb( $use ) {
	}

}

abstract class AbstractFormat {

	/**
	 * @param string $path
	 * @return self
	 */
	public static function initFormatByPath( $path ) {
	}

}
