<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use InvalidArgumentException;
use MediaWiki\Extension\Translate\Validation\ValidationIssue;
use MessageSpecifier;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.05
 * @see ValidationIssue (similar, but different use case)
 */
class TranslationUnitIssue implements MessageSpecifier {
	public const ERROR = 'error';
	public const WARNING = 'warning';
	/** @var string self::ERROR|self::WARNING */
	private $severity;
	/** @var string */
	private $messageKey;
	/** @var array */
	private $messageParams;

	public function __construct( string $severity, string $messageKey, array $messageParams = [] ) {
		if ( !in_array( $severity, [ self::ERROR, self::WARNING ] ) ) {
			throw new InvalidArgumentException( 'Invalid value for severity: ' . $severity );
		}
		$this->severity = $severity;
		$this->messageKey = $messageKey;
		$this->messageParams = $messageParams;
	}

	public function getSeverity(): string {
		return $this->severity;
	}

	public function getKey(): string {
		return $this->messageKey;
	}

	public function getParams(): array {
		return $this->messageParams;
	}
}
