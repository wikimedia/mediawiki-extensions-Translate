<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Extension\Translate\Cache\PersistentCache;
use MediaWiki\Extension\Translate\Cache\PersistentCacheEntry;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleState;
use MediaWiki\Page\PageIdentity;

/**
 * Manage translation state for translatable pages
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2024.07
 */
class TranslatablePageStateStore {
	private PersistentCache $persistentCache;

	public function __construct( PersistentCache $persistentCache ) {
		$this->persistentCache = $persistentCache;
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

	private function getCacheKey( PageIdentity $pageIdentity ): string {
		return 'page-translation-state-' . $pageIdentity->getId();
	}

	private function getCacheTag( TranslatableBundleState $state ): string {
		return "tps_%state_{$state->getStateText()}%";
	}
}
