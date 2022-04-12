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

	public function __construct( ?string $sourceLanguageCode ) {
		$this->sourceLanguageCode = $sourceLanguageCode;
	}

	public function getSourceLanguageCode(): ?string {
		return $this->sourceLanguageCode;
	}
}
