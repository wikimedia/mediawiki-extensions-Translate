<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Status\Status;
use MediaWiki\Title\Title;

/**
 * Collection of pages potentially affected by a page move operation.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2021.09
 */
class PageMoveCollection {
	private PageMoveOperation $translatablePage;
	/** @var PageMoveOperation[] */
	private array $translationPagePairs;
	/** @var PageMoveOperation[] */
	private array $unitPagesPairs;
	/** @var PageMoveOperation[] */
	private array $subpagesPairs;
	/** @var PageMoveOperation[] */
	private array $talkpagesPairs;
	/** @var Title[] */
	private array $translatableSubpages;
	/** @var array<string,Status> */
	private array $nonMovableSubpages;

	/**
	 * @param PageMoveOperation $translatablePage
	 * @param PageMoveOperation[] $translationPagePairs Translation pages
	 * @param PageMoveOperation[] $unitPagesPairs Translation unit pages
	 * @param PageMoveOperation[] $subpagesPairs Non-translatable subpages
	 * @param array<string,Status> $nonMovableSubpages Subpages that are not movable
	 * @param Title[] $translatableSubpages
	 */
	public function __construct(
		PageMoveOperation $translatablePage,
		array $translationPagePairs,
		array $unitPagesPairs,
		array $subpagesPairs,
		array $nonMovableSubpages,
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

		$this->nonMovableSubpages = $nonMovableSubpages;
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

	/** @return array<string,bool> */
	public function getListOfPagesToRedirect(): array {
		$redirects = [
			$this->translatablePage->getOldTitle()->getPrefixedText() =>
					$this->translatablePage->shouldLeaveRedirect()
		];

		$redirects = array_merge( $redirects, $this->getLeaveRedirectPairFromList( $this->translationPagePairs ) );
		$redirects = array_merge( $redirects, $this->getLeaveRedirectPairFromList( $this->unitPagesPairs ) );
		$redirects = array_merge( $redirects, $this->getLeaveRedirectPairFromList( $this->subpagesPairs ) );
		$redirects = array_merge( $redirects, $this->getLeaveRedirectPairFromList( $this->talkpagesPairs ) );

		return $redirects;
	}

	/**
	 * Get list of subpages which cannot be moved for various reasons
	 * (e.g. the target page already exists). Those do not include translatable
	 * subpages which cannot be moved because of current limitation.
	 */
	public function getNonMovableSubpages(): array {
		return $this->nonMovableSubpages;
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

	/** @return array<string,bool> */
	private function getLeaveRedirectPairFromList( array $pagePairs ): array {
		$pairs = [];
		foreach ( $pagePairs as $pair ) {
			if ( $pair->shouldLeaveRedirect() ) {
				$pairs[ $pair->getOldTitle()->getPrefixedText() ] = true;
				$talkpage = $pair->getOldTalkPage();
				if ( $talkpage ) {
					$pairs[ $talkpage->getPrefixedText() ] = true;
				}
			}
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
