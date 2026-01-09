<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extension\Translate\Validation;

/**
 * Value object.
 *
 * @newable
 * @since 2020.06
 */
class ValidationIssue {

	/** @stable for calling */
	public function __construct(
		private readonly string $type,
		private readonly string $subType,
		private readonly string $messageKey,
		private readonly array $messageParams = [],
	) {
	}

	public function type(): string {
		return $this->type;
	}

	public function subType(): string {
		return $this->subType;
	}

	public function messageKey(): string {
		return $this->messageKey;
	}

	public function messageParams(): array {
		return $this->messageParams;
	}
}
