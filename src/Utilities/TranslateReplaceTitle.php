<?php
/**
 * Contains a helper class to help replace titles.
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\Utilities;

use MessageHandle;
use Title;

/**
 * Helper class that cotains utility methods to help with identifying and replace titles.
 * @since 2019.10
 */
class TranslateReplaceTitle {

	/**
	 * Returns two lists: a set of message handles that would be moved/renamed by
	 * the current text replacement, and the set of message handles that would ordinarily
	 * be moved but are not moveable, due to permissions or any other reason.
	 * @param MessageHandle $sourceMessageHandle
	 * @param string $replacement
	 * @return array
	 */
	public static function getTitlesForMove(
		MessageHandle $sourceMessageHandle, $replacement
	) {
		$titlesForMove = [];
		$namespace = $sourceMessageHandle->getTitle()->getNamespace();

		$titles = self::getMatchingTitles( $sourceMessageHandle );

		foreach ( $titles as $title ) {
			$handle = new MessageHandle( $title );
			// This takes care of situations where we have two different titles
			// foo and foo/bar, both will be matched and fetched but the slash
			// does not represent a language seperator
			if ( $handle->getKey() !== $sourceMessageHandle->getKey() ) {
				continue;
			}
			$targetTitle = Title::makeTitle(
				$namespace,
				\TranslateUtils::title( $replacement, $handle->getCode(), $namespace )
			);
			$titlesForMove[] = [ $title, $targetTitle ];
		}

		return $titlesForMove;
	}

	/**
	 * @param MessageHandle $handle
	 * @return \TitleArrayFromResult
	 */
	private static function getMatchingTitles( MessageHandle $handle ) {
		$dbr = wfGetDB( DB_MASTER );

		$tables = [ 'page' ];
		$vars = [ 'page_title', 'page_namespace', 'page_id' ];

		$comparisonCond = 'page_title ' . $dbr->buildLike(
			$handle->getTitleForBase()->getDBkey(), '/', $dbr->anyString()
		);

		$conds = [
			$comparisonCond,
			'page_namespace' => $handle->getTitle()->getNamespace(),
		];

		$result = $dbr->select( $tables, $vars, $conds, __METHOD__ );

		return \TitleArray::newFromResult( $result );
	}
}
