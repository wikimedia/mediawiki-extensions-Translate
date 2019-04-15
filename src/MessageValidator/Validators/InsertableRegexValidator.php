<?php
/**
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\MessageValidator\Validators;

use MediaWiki\Extensions\Translate\MessageValidator\Validator;
use MediaWiki\Extensions\Translate\MessageValidator\ValidationHelper;
use \InvalidArgumentException;

/**
 * A generic regex validator and insertable that can be reused by other classes.
 * @since 2019.05
 */
class InsertableRegexValidator extends \RegexInsertablesSuggester implements Validator {
	use ValidationHelper;

	/**
	 * The regex to run on the message for validation purpose.
	 * @var string
	 */
	protected $validationRegex;

	public function __construct( $params ) {
		parent::__construct( $params );

		if ( is_string( $params ) ) {
			$this->validationRegex = $params;
		} elseif ( is_array( $params ) ) {
			$this->validationRegex = $params['regex'] ?? null;
		}

		if ( $this->validationRegex === null ) {
			throw new InvalidArgumentException( 'The configuration for InsertableRegexValidator does not ' .
				'specify a regular expression.' );
		}
	}

	public function validate( $messages, $code, array &$notice ) {
		self::parameterCheck( $messages, $code, $notice, $this->validationRegex );
	}
}
