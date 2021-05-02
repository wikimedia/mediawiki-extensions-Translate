<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation;

use IContextSource;
use InvalidArgumentException;

/**
 * Container for validation issues returned by MessageValidator.
 *
 * @author Abijeet Patro
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2020.06 (originally 2019.06)
 */
class ValidationResult {
	/** @var ValidationIssues */
	protected $errors;
	/** @var ValidationIssues */
	protected $warnings;

	public function __construct( ValidationIssues $errors, ValidationIssues $warnings ) {
		$this->errors = $errors;
		$this->warnings = $warnings;
	}

	public function hasIssues(): bool {
		return $this->hasWarnings() || $this->hasErrors();
	}

	public function getIssues(): ValidationIssues {
		$issues = new ValidationIssues();
		$issues->merge( $this->errors );
		$issues->merge( $this->warnings );
		return $issues;
	}

	public function hasWarnings(): bool {
		return $this->warnings->hasIssues();
	}

	public function hasErrors(): bool {
		return $this->errors->hasIssues();
	}

	public function getWarnings(): ValidationIssues {
		return $this->warnings;
	}

	public function getErrors(): ValidationIssues {
		return $this->errors;
	}

	public function getDescriptiveWarnings( IContextSource $context ): array {
		return $this->expandMessages( $context, $this->warnings );
	}

	public function getDescriptiveErrors( IContextSource $context ): array {
		return $this->expandMessages( $context, $this->errors );
	}

	private function expandMessages( IContextSource $context, ValidationIssues $issues ): array {
		$expandMessage = function ( ValidationIssue $issue ) use ( $context ): string {
			$params = $this->fixMessageParams( $context, $issue->messageParams() );
			return $context->msg( $issue->messageKey() )->params( $params )->parse();
		};

		return array_map( $expandMessage, iterator_to_array( $issues ) );
	}

	private function fixMessageParams( IContextSource $context, array $params ): array {
		$out = [];
		$lang = $context->getLanguage();

		foreach ( $params as $param ) {
			if ( !is_array( $param ) ) {
				$out[] = $param;
			} else {
				[ $type, $value ] = $param;
				if ( $type === 'COUNT' ) {
					$out[] = $lang->formatNum( $value );
				} elseif ( $type === 'PARAMS' ) {
					$out[] = $lang->commaList( $value );
				} elseif ( $type === 'PLAIN-PARAMS' ) {
					$value = array_map( 'wfEscapeWikiText', $value );
					$out[] = $lang->commaList( $value );
				} elseif ( $type === 'PLAIN' ) {
					$out[] = wfEscapeWikiText( $value );
				} elseif ( $type === 'MESSAGE' ) {
					$messageKey = array_shift( $value );
					$messageParams = $this->fixMessageParams( $context, $value );
					$out[] = $context->msg( $messageKey )->params( $messageParams );
				} else {
					throw new InvalidArgumentException( "Unknown type $type" );
				}
			}
		}

		return $out;
	}
}
