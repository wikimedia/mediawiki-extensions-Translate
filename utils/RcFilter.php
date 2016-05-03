<?php
/**
 * Contains class with filter to Special:RecentChanges to enable additional
 * filtering.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2010, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Adds a new filter to Special:RecentChanges which makes it possible to filter
 * translations away or show them only.
 */
class TranslateRcFilter {
	/**
	 * Hooks ChangesListSpecialPageQuery. See the hook documentation for
	 * documentation of the function parameters.
	 *
	 * Appends SQL filter conditions into $conds.
	 * @param string $pageName
	 * @param array $tables
	 * @param array $fields
	 * @param array $conds
	 * @param array $query_options
	 * @param array $join_conds
	 * @param FormOptions $opts
	 * @return bool true
	 */
	public static function translationFilter( $pageName, &$tables, &$fields, &$conds, &$query_options,
		&$join_conds, FormOptions $opts
	) {
		global $wgTranslateMessageNamespaces, $wgTranslateRcFilterDefault;

		if ( $pageName !== 'Recentchanges' ) {
			return true;
		}

		$request = RequestContext::getMain()->getRequest();
		$translations = $request->getVal( 'translations', $wgTranslateRcFilterDefault );
		$opts->add( 'translations', $wgTranslateRcFilterDefault );
		$opts->setValue( 'translations', $translations );

		$dbr = wfGetDB( DB_SLAVE );

		$namespaces = array();

		foreach ( $wgTranslateMessageNamespaces as $index ) {
			$namespaces[] = $index;
			$namespaces[] = $index + 1; // Talk too
		}

		if ( $translations === 'only' ) {
			$conds[] = 'rc_namespace IN (' . $dbr->makeList( $namespaces ) . ')';
			$conds[] = 'rc_title like \'%%/%%\'';
		} elseif ( $translations === 'filter' ) {
			$conds[] = 'rc_namespace NOT IN (' . $dbr->makeList( $namespaces ) . ')';
		} elseif ( $translations === 'site' ) {
			$conds[] = 'rc_namespace IN (' . $dbr->makeList( $namespaces ) . ')';
			$conds[] = 'rc_title not like \'%%/%%\'';
		}

		return true;
	}

	/**
	 * Hooks SpecialRecentChangesPanel. See the hook documentation for
	 * documentation of the function parameters.
	 *
	 * Adds a HTMl selector into $items
	 * @param $items
	 * @param FormOptions $opts
	 * @return bool true
	 */
	public static function translationFilterForm( &$items, $opts ) {
		$opts->consumeValue( 'translations' );
		$default = $opts->getValue( 'translations' );

		$label = Xml::label(
			wfMessage( 'translate-rc-translation-filter' )->text(),
			'mw-translation-filter'
		);
		$select = new XmlSelect( 'translations', 'mw-translation-filter', $default );
		$select->addOption(
			wfMessage( 'translate-rc-translation-filter-no' )->text(),
			'noaction'
		);
		$select->addOption( wfMessage( 'translate-rc-translation-filter-only' )->text(), 'only' );
		$select->addOption(
			wfMessage( 'translate-rc-translation-filter-filter' )->text(),
			'filter'
		);
		$select->addOption( wfMessage( 'translate-rc-translation-filter-site' )->text(), 'site' );

		$items['translations'] = array( $label, $select->getHTML() );

		return true;
	}
}
