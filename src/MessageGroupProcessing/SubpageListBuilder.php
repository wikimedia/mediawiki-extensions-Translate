<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Cache\LinkBatchFactory;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Title\Title;

/**
 * Generates list of subpages for the translatable bundle that can be
 * moved or deleted
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2022.04
 */
class SubpageListBuilder {
	private TranslatableBundleFactory $bundleFactory;
	private LinkBatchFactory $linkBatchFactory;

	public function __construct(
		TranslatableBundleFactory $bundleFactory,
		LinkBatchFactory $linkBatchFactory
	) {
		$this->bundleFactory = $bundleFactory;
		$this->linkBatchFactory = $linkBatchFactory;
	}

	public function getSubpagesPerType( TranslatableBundle $bundle, bool $fetchTalkPages ): array {
		$classifiedSubPages = $this->getEmptyResultSet();

		$classifiedSubPages['translationPages'] = $bundle->getTranslationPages();
		$classifiedSubPages['translationUnitPages'] = $bundle->getTranslationUnitPages();

		// It's possible that subpages may not be allowed and getSubpages will return an
		// empty array but that's not a problem.
		$allSubpages = $bundle->getTitle()->getSubpages();

		// Index the subpages
		$allSubpagesIndexed = [];
		foreach ( $allSubpages as $page ) {
			$allSubpagesIndexed[ $page->getPrefixedDBkey() ] = $page;
		}

		// Remove translation pages from subpages
		foreach ( $classifiedSubPages[ 'translationPages' ] as $translationPage ) {
			if ( isset( $allSubpagesIndexed[ $translationPage->getPrefixedDBkey() ] ) ) {
				unset( $allSubpagesIndexed[ $translationPage->getPrefixedDBkey() ] );
			}
		}

		// Remove subpages that are translatable bundles
		foreach ( $allSubpagesIndexed as $index => $subpage ) {
			if ( $this->bundleFactory->getBundle( $subpage ) ) {
				$classifiedSubPages['translatableSubpages'][] = $subpage;
				unset( $allSubpagesIndexed[$index] );
			}
		}

		// Remove translation pages for translatable pages found
		$allSubpagesIndexed = $this->filterOtherTranslationPages(
			$allSubpagesIndexed, $classifiedSubPages['translatableSubpages']
		);

		$classifiedSubPages['normalSubpages'] = $allSubpagesIndexed;

		if ( $fetchTalkPages && !$bundle->getTitle()->isTalkPage() ) {
			// We don't fetch talk pages for translatable subpages
			$talkPages = $this->getTalkPages(
				array_merge(
					[ $bundle->getTitle() ],
					$classifiedSubPages['translationPages'],
					$classifiedSubPages['translationUnitPages'],
					$classifiedSubPages['normalSubpages']
				)
			);

			$translatableTalkPages = [];
			foreach ( $talkPages as $key => $talkPage ) {
				if ( $talkPage === null ) {
					continue;
				}

				if ( $this->bundleFactory->getBundle( $talkPage ) ) {
					$translatableTalkPages[] = $talkPage;
					unset( $talkPages[$key] );
				}
			}

			$classifiedSubPages['talkPages'] = $talkPages;
			$classifiedSubPages['translatableTalkPages'] = $translatableTalkPages;
		}

		return $classifiedSubPages;
	}

	public function getEmptyResultSet(): array {
		return [
			'translationPages' => [],
			'translatableSubpages' => [],
			'translationUnitPages' => [],
			'normalSubpages' => [],
			'talkPages' => [],
			'translatableTalkPages' => []
		];
	}

	/**
	 * Remove translation pages for translatable pages from the list of all pages
	 * @param Title[] $allPages
	 * @param Title[] $translatablePages
	 */
	private function filterOtherTranslationPages( array $allPages, array $translatablePages ): array {
		$mappedTranslatablePages = [];
		foreach ( $translatablePages as $index => $page ) {
			$mappedTranslatablePages[ $page->getText() ] = $index;
		}

		foreach ( $allPages as $prefixedDbKeyTitle => $subpage ) {
			[ $key, ] = Utilities::figureMessage( $subpage->getText() );
			if ( isset( $mappedTranslatablePages[ $key ] ) ) {
				unset( $allPages[ $prefixedDbKeyTitle ] );
			}
		}

		return $allPages;
	}

	/**
	 * To identify the talk pages, we first gather the possible talk pages into an array
	 * and then check that they exist. Title::exists perform a database check so we gather
	 * them into LinkBatch to reduce the performance impact.
	 * @param Title[] $pages
	 * @return Title[]
	 */
	private function getTalkPages( array $pages ): array {
		$lb = $this->linkBatchFactory->newLinkBatch();
		$talkPageList = [];

		foreach ( $pages as $page ) {
			$talkPage = $page->getTalkPageIfDefined();
			$talkPageList[ $page->getPrefixedDBkey() ] = $talkPage;
			if ( $talkPage ) {
				$lb->addObj( $talkPage );
			}
		}

		$lb->setCaller( __METHOD__ )->execute();
		foreach ( $talkPageList as $index => $talkPage ) {
			if ( !$talkPage || !$talkPage->exists() ) {
				$talkPageList[$index] = null;
			}
		}

		return $talkPageList;
	}
}
