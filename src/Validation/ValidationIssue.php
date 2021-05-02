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
	/** @var string */
	private $type;
	/** @var string */
	private $subType;
	/** @var string */
	private $messageKey;
	/** @var array */
	private $messageParams;

	/** @stable for calling */
	public function __construct(
		string $type,
		string $subType,
		string $messageKey,
		array $messageParams = []
	) {
		$this->type = $type;
		$this->subType = $subType;
		$this->messageKey = $messageKey;
		$this->messageParams = $messageParams;
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
