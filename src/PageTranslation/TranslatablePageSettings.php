<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

/**
 * Value object containing user configurable settings when marking a page for translation
 * @since 2023.11
 */
class TranslatablePageSettings {
	/** @var string[] */
	private array $priorityLanguages;
	private bool $forcePriorityLanguages;
	private string $priorityReason;
	/** @var string[] */
	private array $noFuzzyUnits;
	private bool $translateTitle;
	private bool $forceLatestSyntaxVersion;
	private bool $enableTransclusion;

	public function __construct(
		array $priorityLanguages,
		bool $forcePriorityLanguages,
		string $priorityReason,
		array $noFuzzyUnits,
		bool $translateTitle,
		bool $forceLatestSyntaxVersion,
		bool $enableTransclusion
	) {
		$this->priorityLanguages = $priorityLanguages;
		$this->forcePriorityLanguages = $forcePriorityLanguages;
		$this->priorityReason = $priorityReason;
		$this->noFuzzyUnits = $noFuzzyUnits;
		$this->translateTitle = $translateTitle;
		$this->forceLatestSyntaxVersion = $forceLatestSyntaxVersion;
		$this->enableTransclusion = $enableTransclusion;
	}

	public function getPriorityLanguages(): array {
		return $this->priorityLanguages;
	}

	public function shouldForcePriorityLanguage(): bool {
		return $this->forcePriorityLanguages;
	}

	public function getPriorityLanguageComment(): string {
		return $this->priorityReason;
	}

	public function getNoFuzzyUnits(): array {
		return $this->noFuzzyUnits;
	}

	public function shouldTranslateTitle(): bool {
		return $this->translateTitle;
	}

	public function shouldForceLatestSyntaxVersion(): bool {
		return $this->forceLatestSyntaxVersion;
	}

	public function shouldEnableTransclusion(): bool {
		return $this->enableTransclusion;
	}
}
