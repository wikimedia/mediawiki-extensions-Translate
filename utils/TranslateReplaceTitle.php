<?php
/**
 * Contains a helper class to help replace titles.
 * @license GPL-2.0-or-later
 */

/**
 * Helper class that cotains utility methods to help with identifying and replace titles.
 * @since 2019.06
 */
class TranslateReplaceTitle {

	/**
	 * Returns two lists: the set of titles that would be moved/renamed by
	 * the current text replacement, and the set of titles that would
	 * ordinarily be moved but are not moveable, due to permissions or any
	 * other reason.
	 * @param string $target
	 * @param string $selectedNamespace
	 * @param string $replacement
	 * @return array
	 */
	public static function getTitlesForMove( $target, $selectedNamespace, $replacement ) {
		$titlesForMove = [];

		$res = self::getMatchingTitles( $target, $selectedNamespace );
		foreach ( $res as $row ) {
			$title = Title::newFromRow( $row );
			if ( $title == null ) {
				continue;
			}
			$newTitle = self::getReplacedTitle( $title, $target, $replacement );

			$titlesForMove[] = [ $title, $newTitle ];
		}

		return $titlesForMove;
	}

	/**
	 * Do a replacement on a title.
	 * @param Title $title
	 * @param string $search
	 * @param string $replacement
	 * @return Title|null
	 */
	private static function getReplacedTitle( Title $title, $search, $replacement ) {
		$oldTitleText = $title->getText();
		$newTitleText = str_replace( $search, $replacement, $oldTitleText );
		return Title::makeTitleSafe( $title->getNamespace(), $newTitleText );
	}

	/**
	 * @param string $str
	 * @param array $namespaces
	 * @return IResultWrapper Resulting rows
	 */
	private static function getMatchingTitles( $str, $namespaces ) {
		$dbr = wfGetDB( DB_REPLICA );

		$tables = [ 'page' ];
		$vars = [ 'page_title', 'page_namespace', 'page_id' ];

		$str = str_replace( ' ', '_', $str );

		$any = $dbr->anyString();
		$comparisonCond = 'page_title ' . $dbr->buildLike( $any, $str, $any );

		$conds = [
			$comparisonCond,
			'page_namespace' => $namespaces,
		];

		$sort = [ 'ORDER BY' => 'page_namespace, page_title' ];

		return $dbr->select( $tables, $vars, $conds, __METHOD__, $sort );
	}

}
