<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Extension\Translate\Cache\PersistentCache;
use MediaWiki\Extension\Translate\Cache\PersistentCacheEntry;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleState;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Page\PageRecord;
use MediaWiki\Page\PageStore;

/**
 * Manage translation state for translatable pages
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2024.07
 */
class TranslatablePageStateStore {
	private PersistentCache $persistentCache;
	private PageStore $pageStore;

	public function __construct(
		PersistentCache $persistentCache,
		PageStore $pageStore
	) {
		$this->persistentCache = $persistentCache;
		$this->pageStore = $pageStore;
	}

	public function remove( PageIdentity $pageIdentity ): void {
		$this->persistentCache->delete( $this->getCacheKey( $pageIdentity ) );
	}

	public function set( PageIdentity $pageIdentity, TranslatableBundleState $selectedState ): void {
		$entry = new PersistentCacheEntry(
			$this->getCacheKey( $pageIdentity ),
			json_encode( $selectedState ),
			null,
			$this->getCacheTag( $selectedState )
		);

		$this->persistentCache->set( $entry );
	}

	public function get( PageIdentity $pageIdentity ): ?TranslatableBundleState {
		$entry = $this->persistentCache->get( $this->getCacheKey( $pageIdentity ) );
		if ( !$entry ) {
			return null;
		}

		return TranslatableBundleState::fromJson( $entry[0]->value() );
	}

	/** @return PageRecord[] */
	public function getRequested(): array {
		$proposedState = new TranslatableBundleState( TranslatableBundleState::PROPOSE );
		$entries = $this->persistentCache->getByTag( $this->getCacheTag( $proposedState ) );

		$pageIds = [];
		foreach ( $entries as $entry ) {
			$pageIds[] = $this->getPageIdFromCacheKey( $entry->key() );
		}

		return $this->pageStore->newSelectQueryBuilder()
			->wherePageIds( $pageIds )
			->fetchPageRecordArray();
	}

	private function getCacheKey( PageIdentity $pageIdentity ): string {
		return 'page-translation-state-' . $pageIdentity->getId();
	}

	private function getCacheTag( TranslatableBundleState $state ): string {
		return "tps_%state_{$state->getStateText()}%";
	}

	private function getPageIdFromCacheKey( string $key ): int {
		$parts = explode( '-', $key );
		return (int)end( $parts );
	}
}
