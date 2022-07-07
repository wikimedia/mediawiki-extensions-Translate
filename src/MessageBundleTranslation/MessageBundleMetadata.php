<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

/**
 * Represents metadata for a message bundle
 * @author Abijeet Patro
 * @since 2022.05
 * @license GPL-2.0-or-later
 */
class MessageBundleMetadata {
	/** @var string|null */
	private $sourceLanguageCode;
	/** @var array|null */
	private $priorityLanguageCodes;
	/** @var bool */
	private $allowOnlyPriorityLanguages;
	/** @var string|null */
	private $description;

	public function __construct(
		?string $sourceLanguageCode,
		?array $priorityLanguageCodes,
		bool $allowOnlyPriorityLanguages,
		?string $description
	) {
		$this->sourceLanguageCode = $sourceLanguageCode;
		$this->priorityLanguageCodes = $priorityLanguageCodes;
		$this->allowOnlyPriorityLanguages = $allowOnlyPriorityLanguages;
		$this->description = $description;
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

	public function getDescription(): ?string {
		return $this->description;
	}
}
