<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use Title;

/**
 * Collection of pages potentially affected by a page move operation.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2021.09
 */
class PageMoveCollection {
	/** @var PageMoveOperation|null */
	private $translatablePage;
	/** @var PageMoveOperation[] */
	private $translationPagePairs;
	/** @var PageMoveOperation[] */
	private $unitPagesPairs;
	/** @var PageMoveOperation[] */
	private $subpagesPairs;
	/** @var PageMoveOperation[] */
	private $talkpagesPairs;
	/** @var Title[] */
	private $translatableSubpages;

	/**
	 * @param PageMoveOperation $translatablePage Translatable page
	 * @param PageMoveOperation[] $translationPagePairs Translation pages
	 * @param PageMoveOperation[] $unitPagesPairs Translation unit pages
	 * @param PageMoveOperation[] $subpagesPairs Non translatable sub pages
	 * @param array $translatableSubpages Translatable sub pages
	 */
	public function __construct(
		PageMoveOperation $translatablePage,
		array $translationPagePairs,
		array $unitPagesPairs,
		array $subpagesPairs,
		array $translatableSubpages
	) {
		$this->translatablePage = $translatablePage;
		$this->translationPagePairs = $translationPagePairs;
		$this->unitPagesPairs = $unitPagesPairs;
		$this->subpagesPairs = $subpagesPairs;
		$this->translatableSubpages = $translatableSubpages;

		// Populate the talk pages from the various inputs.
		$this->talkpagesPairs = $this->getTalkpages(
			$this->translatablePage, ...$translationPagePairs, ...$unitPagesPairs, ...$subpagesPairs
		);
	}

	public function getTranslatablePage(): PageMoveOperation {
		return $this->translatablePage;
	}

	/** @return PageMoveOperation[] */
	public function getTranslationPagesPair(): array {
		return $this->translationPagePairs;
	}

	/** @return PageMoveOperation[] */
	public function getUnitPagesPair(): array {
		return $this->unitPagesPairs;
	}

	/** @return PageMoveOperation[] */
	public function getSubpagesPair(): array {
		return $this->subpagesPairs;
	}

	/** @return Title[] */
	public function getTranslatableSubpages(): array {
		return $this->translatableSubpages;
	}

	/** @return Title[] */
	public function getTranslationPages(): array {
		return $this->getOldPagesFromList( $this->translationPagePairs );
	}

	/** @return Title[] */
	public function getUnitPages(): array {
		return $this->getOldPagesFromList( $this->unitPagesPairs );
	}

	/** @return Title[] */
	public function getSubpages(): array {
		return $this->getOldPagesFromList( $this->subpagesPairs );
	}

	/** @return string[] */
	public function getListOfPages(): array {
		$pageList = [
			$this->translatablePage->getOldTitle()->getPrefixedText() =>
				$this->translatablePage->getNewTitle() ?
					$this->translatablePage->getNewTitle()->getPrefixedText() : null
		];
		$pageList = array_merge( $pageList, $this->getPagePairFromList( $this->translationPagePairs ) );
		$pageList = array_merge( $pageList, $this->getPagePairFromList( $this->unitPagesPairs ) );
		$pageList = array_merge( $pageList, $this->getPagePairFromList( $this->subpagesPairs ) );
		$pageList = array_merge( $pageList, $this->getPagePairFromList( $this->talkpagesPairs ) );

		return $pageList;
	}

	/**
	 * @param PageMoveOperation[] $pagePairs
	 * @return Title[]
	 */
	private function getOldPagesFromList( array $pagePairs ): array {
		$oldTitles = [];
		foreach ( $pagePairs as $pair ) {
			$oldTitles[] = $pair->getOldTitle();
		}

		return $oldTitles;
	}

	/** @return string[] */
	private function getPagePairFromList( array $pagePairs ): array {
		$pairs = [];
		foreach ( $pagePairs as $pair ) {
			$pairs[ $pair->getOldTitle()->getPrefixedText() ] =
				$pair->getNewTitle() ? $pair->getNewTitle()->getPrefixedText() : null;
		}

		return $pairs;
	}

	/** @return PageMoveOperation[] */
	private function getTalkpages( PageMoveOperation ...$allMoveOperations ): array {
		$talkpagesPairs = [];
		foreach ( $allMoveOperations as $moveOperation ) {
			if ( $moveOperation->hasTalkpage() ) {
				$talkpagesPairs[] = new PageMoveOperation(
					$moveOperation->getOldTalkpage(), $moveOperation->getNewTalkpage()
				);
			}
		}
		return $talkpagesPairs;
	}
}
