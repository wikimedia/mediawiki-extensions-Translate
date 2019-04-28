<?php
/**
 * This file contains a managed custom cached message group implementation.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

class MockCustomCacheMessageGroup extends WikiMessageGroup implements \CustomCacheMessageGroup {
	public static function getCacheData() {
		$list = [];
		$exampleMessageGroup = new MockCustomCacheMessageGroup( 'mock_theid', 'thesource' );
		$exampleMessageGroup->setLabel( 'thelabel' ); // Example
		$exampleMessageGroup->setNamespace( 5 ); // Example
		$list['mock_theid'] = $exampleMessageGroup;

		$anotherExampleMessageGroup = new MockCustomCacheMessageGroup( 'mock_anotherid', 'thesource' );
		$anotherExampleMessageGroup->setLabel( 'thelabel' ); // Example
		$anotherExampleMessageGroup->setNamespace( 5 ); // Example
		$list['mock_anotherid'] = $anotherExampleMessageGroup;

		return [
			'groups' => $list
		];
	}

	public static function getCacheKey() {
		return 'mocked';
	}

	public static function getCacheVersion() {
		return 1;
	}

	public static function registerToCache( array &$customGroupCache ) {
		$customGroupCache[] = self::class;
	}

	/**
	 * Hook: TranslateCustomCacheGroups
	 *
	 * @param array &$customGroupCache
	 * @return void
	 */
	public static function registerToCacheDupe( array &$customGroupCache ) {
		$customGroupCache[] = self::class;
	}
}
