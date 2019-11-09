<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\MessageValidator\Validators;

use MediaWiki\Extensions\Translate\MessageValidator\Validator;
use TMessage;

/**
 * Ensures that the translation for a message matches a value from a list.
 * @since 2019.12
 */
class MatchSetValidator implements Validator {
	/**
	 * @var string[]
	 */
	protected $possibleValues;

	/**
	 * @var string[]
	 */
	protected $normalizedValues;

	/**
	 * @var bool
	 */
	protected $caseSensitive;

	public function __construct( array $params ) {
		$this->possibleValues = $params['values'] ?? [];
		$this->caseSensitive = (bool)( $params['caseSensitive'] ?? true );

		if ( $this->possibleValues === [] ) {
			throw new \InvalidArgumentException( 'No values provided for MatchSet validator.' );
		}

		if ( $this->caseSensitive ) {
			$this->normalizedValues = $this->possibleValues;
		} else {
			$this->normalizedValues = array_map( 'strtolower', $this->possibleValues );
		}
	}

	public function validate( TMessage $message, $code, array &$notices ) : void {
		$translation =
			$this->caseSensitive ? $message->translation() : strtolower( $message->translation() );
		$key = $message->key();

		if ( array_search( $translation, $this->normalizedValues, true ) === false ) {
			$notices[$key][] = [
				[ 'value-not-present', 'invalid', $key, $code ],
				'translate-checks-value-not-present',
				[ 'PLAIN-PARAMS', $this->possibleValues ],
				[ 'COUNT', count( $this->possibleValues ) ]
			];
		}
	}
}
