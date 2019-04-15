<?php

/**
 * A wrapper response class for validation responses.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\MessageValidator;

/**
 * A wrapper class built to make it easier to interact with the
 * response from MessageValidator
 * @since 2019.04
 */
class ValidationResult {
	/**
	 * Contains validation errors
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Contains validation warnings
	 * @var array
	 */
	protected $warnings = [];

	public function __construct( array $errors, array $warnings ) {
		$this->setErrors( $errors );
		$this->setWarnings( $warnings );
	}

	public function hasWarnings() {
		return count( $this->warnings ) > 0;
	}

	public function hasErrors() {
		return count( $this->errors ) > 0;
	}

	public function setWarnings( array $warnings ) {
		$this->warnings = $warnings;
	}

	public function addWarnings( $warning ) {
		$this->warnings[] = $warning;
	}

	public function getWarnings() {
		return $this->warnings;
	}

	public function getDescriptiveWarnings( \IContextSource $context ) {
		return self::expandMessages( $context, $this->warnings );
	}

	public function setErrors( array $errors ) {
		$this->errors = $errors;
	}

	public function addError( $error ) {
		$this->errors[] = $error;
	}

	public function getErrors() {
		return $this->errors;
	}

	public function getDescriptiveErrors( \IContextSource $context ) {
		return self::expandMessages( $context, $this->errors );
	}

	public static function expandMessages( \IContextSource $context, array $notices ) {
		$expandedNotices = [];

		foreach ( $notices as $item ) {
			$key = array_shift( $item );
			$msg = $context->msg( $key, $item )->parse();
			$expandedNotices[] = $msg;
		}

		return $expandedNotices;
	}
}
