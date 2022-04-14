<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

/**
 * Represents metadata for a translatable bundle
 * @author Abijeet Patro
 * @since 2022.05
 * @license GPL-2.0-or-later
 */
class TranslatableBundleMetadata {
	/** @var string */
	private $sourceLanguageCode;
	/** @var array|null */
	private $priorityLanguageCodes;
	/** @var bool */
	private $allowOnlyPriorityLanguages;

	public function __construct(
		?string $sourceLanguageCode,
		?array $priorityLanguageCodes,
		bool $allowOnlyPriorityLanguages
	) {
		$this->sourceLanguageCode = $sourceLanguageCode;
		$this->priorityLanguageCodes = $priorityLanguageCodes;
		$this->allowOnlyPriorityLanguages = $allowOnlyPriorityLanguages;
	}

	public function getSourceLanguageCode(): ?string {
		return $this->sourceLanguageCode;
	}

	public function getPriorityLanguages(): ?array {
		return $this->priorityLanguageCodes;
	}

	public function areOnlyPriorityLanguagesAllowed(): bool {
		return $this->allowOnlyPriorityLanguages;
	}
}
