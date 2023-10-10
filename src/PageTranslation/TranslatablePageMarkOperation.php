<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

/**
 * This class encapsulates the information / state needed to mark a page for translation
 * @since 2023.10
 */
class TranslatablePageMarkOperation {
	/** @var TranslationUnit[]|null */
	private ?array $units = null;
	/** @var TranslationUnit[]|null */
	private ?array $deletedUnits = null;
	private ParserOutput $parserOutput;
	private TranslatablePage $page;
	private bool $firstMark;

	public function __construct(
		TranslatablePage $page,
		ParserOutput $parserOutput,
		array $units,
		array $deletedUnits,
		bool $isFirstMark
	) {
		$this->page = $page;
		$this->parserOutput = $parserOutput;
		$this->units = $units;
		$this->deletedUnits = $deletedUnits;
		$this->firstMark = $isFirstMark;
	}

	public function getPage(): TranslatablePage {
		return $this->page;
	}

	/** Get the result of the parse */
	public function getParserOutput(): ParserOutput {
		return $this->parserOutput;
	}

	/**
	 * Get translation units present in the parsed text
	 * @return TranslationUnit[]
	 */
	public function getUnits(): array {
		return $this->units;
	}

	/**
	 * Get translation units present in the previously marked text, but
	 * not in the parsed one
	 * @return TranslationUnit[]
	 */
	public function getDeletedUnits(): array {
		return $this->deletedUnits;
	}

	/** Whether the page has not been marked for translation before */
	public function isFirstMark(): bool {
		return $this->firstMark;
	}
}
