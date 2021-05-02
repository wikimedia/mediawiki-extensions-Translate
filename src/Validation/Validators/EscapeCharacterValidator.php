<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

use InvalidArgumentException;
use MediaWiki\Extension\Translate\Validation\MessageValidator;
use MediaWiki\Extension\Translate\Validation\ValidationIssue;
use MediaWiki\Extension\Translate\Validation\ValidationIssues;
use TMessage;

/**
 * Ensures that only the specified escape characters are present.
 * @license GPL-2.0-or-later
 * @since 2020.01
 */
class EscapeCharacterValidator implements MessageValidator {
	/** @var string[] */
	protected $allowedCharacters;
	/** @var string */
	protected $regex;

	/** List of valid escape characters recognized. */
	private const VALID_CHARS = [ '\t', '\n', '\\\'', '\"', '\f', '\r', '\a', '\b', '\\\\' ];

	public function __construct( array $params ) {
		$this->allowedCharacters = $params['values'] ?? [];

		if ( $this->allowedCharacters === [] || !is_array( $this->allowedCharacters ) ) {
			throw new InvalidArgumentException(
				'No values provided for EscapeCharacter validator.'
			);
		}

		$this->regex = $this->buildRegex( $this->allowedCharacters );
	}

	public function getIssues( TMessage $message, string $targetLanguage ): ValidationIssues {
		$issues = new ValidationIssues();
		$translation = $message->translation();
		preg_match_all( "/$this->regex/U", $translation, $transVars );

		// Check for missing variables in the translation
		$params = $transVars[0];
		if ( count( $params ) ) {
			$messageParams = [
				[ 'PARAMS', $params ],
				[ 'COUNT', count( $params ) ],
				[ 'PARAMS', $this->allowedCharacters ],
				[ 'COUNT', count( $this->allowedCharacters ) ]
			];

			$issue =
				new ValidationIssue(
					'escape', 'invalid', 'translate-checks-escape', $messageParams
				);
			$issues->add( $issue );
		}

		return $issues;
	}

	private function buildRegex( array $allowedCharacters ): string {
		$regex = '\\\\[^';
		$prefix = '';
		foreach ( $allowedCharacters as $character ) {
			if ( !in_array( $character, self::VALID_CHARS ) ) {
				throw new InvalidArgumentException(
					"Invalid escape character encountered: $character during configuration." .
					'Valid escape characters include: ' . implode( ', ', self::VALID_CHARS )
				);
			}

			if ( $character !== '\\' ) {
				$character = stripslashes( $character );
				// negative look ahead, to avoid "\\ " being treated as an accidental escape
				$prefix = '(?<!\\\\)';
			}

			// This is done because in the regex we need slashes for some characters such as
			// \", \', but not for others such as \n, \t etc
			$normalizedChar = addslashes( $character );
			$regex .= $normalizedChar;
		}
		$regex .= ']';

		return $prefix . $regex;
	}
}
