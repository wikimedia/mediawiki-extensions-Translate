<?php
/**
 * Contains class with filter to Special:RecentChanges to enable additional
 * filtering.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2010, Niklas Laxström
 * @license GPL-2.0-or-later
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
	 * @param array &$tables
	 * @param array &$fields
	 * @param array &$conds
	 * @param array &$query_options
	 * @param array &$join_conds
	 * @param FormOptions $opts
	 * @return bool true
	 */
	public static function translationFilter( $pageName, &$tables, &$fields, &$conds,
		&$query_options, &$join_conds, FormOptions $opts
	) {
		global $wgTranslateRcFilterDefault;

		if ( $pageName !== 'Recentchanges' || self::isStructuredFilterUiEnabled() ) {
			return true;
		}

		$request = RequestContext::getMain()->getRequest();
		$translations = $request->getVal( 'translations', $wgTranslateRcFilterDefault );
		$opts->add( 'translations', $wgTranslateRcFilterDefault );
		$opts->setValue( 'translations', $translations );

		$dbr = wfGetDB( DB_REPLICA );

		$namespaces = self::getTranslateNamespaces();

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

	private static function getTranslateNamespaces() {
		global $wgTranslateMessageNamespaces;
		$namespaces = [];

		foreach ( $wgTranslateMessageNamespaces as $index ) {
			$namespaces[] = $index;
			$namespaces[] = $index + 1; // Include Talk namespaces
		}

		return $namespaces;
	}

	/**
	 * Hooks SpecialRecentChangesPanel. See the hook documentation for
	 * documentation of the function parameters.
	 *
	 * Adds a HTMl selector into $items
	 * @param array &$items
	 * @param FormOptions $opts
	 * @return bool true
	 */
	public static function translationFilterForm( &$items, $opts ) {
		if ( self::isStructuredFilterUiEnabled() ) {
			return true;
		}

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

		$items['translations'] = [ $label, $select->getHTML() ];

		return true;
	}

	private static function isStructuredFilterUiEnabled() {
		$context = RequestContext::getMain();

		// This assumes usage only on RC page
		$page = new SpecialRecentChanges();
		$page->setContext( $context );

		// isStructuredFilterUiEnabled used to be a protected method in older versions :(
		return is_callable( [ $page, 'isStructuredFilterUiEnabled' ] ) &&
			$page->isStructuredFilterUiEnabled();
	}

	/**
	 * Hooks ChangesListSpecialPageStructuredFilters. See the hook documentation for
	 * documentation of the function parameters.
	 *
	 * Adds translations filters to structured UI
	 * @param ChangesListSpecialPage $special
	 * @return bool true
	 */
	public static function onChangesListSpecialPageStructuredFilters(
		ChangesListSpecialPage $special
	) {
		global $wgTranslateRcFilterDefault;
		$defaultFilter = $wgTranslateRcFilterDefault !== 'noaction' ?
			$wgTranslateRcFilterDefault :
			ChangesListStringOptionsFilterGroup::NONE;

		$translationsGroup = new ChangesListStringOptionsFilterGroup(
			[
				'name' => 'translations',
				'title' => 'translate-rcfilters-translations',
				'priority' => -7,
				'default' => $defaultFilter,
				'isFullCoverage' => true,
				'filters' => [
					[
						'name' => 'only',
						'label' => 'translate-rcfilters-translations-only-label',
						'description' => 'translate-rcfilters-translations-only-desc',
						'cssClassSuffix' => 'only',
						'isRowApplicableCallable' => function ( $ctx, $rc ) {
							$namespaces = self::getTranslateNamespaces();

							return in_array( $rc->getAttribute( 'rc_namespace' ), $namespaces ) &&
								strpos( $rc->getAttribute( 'rc_title' ), '/' ) !== false;
						}
					],
					[
						'name' => 'site',
						'label' => 'translate-rcfilters-translations-site-label',
						'description' => 'translate-rcfilters-translations-site-desc',
						'cssClassSuffix' => 'site',
						'isRowApplicableCallable' => function ( $ctx, $rc ) {
							$namespaces = self::getTranslateNamespaces();

							return in_array( $rc->getAttribute( 'rc_namespace' ), $namespaces ) &&
								strpos( $rc->getAttribute( 'rc_title' ), '/' ) === false;
						}
					],
					[
						'name' => 'filter',
						'label' => 'translate-rcfilters-translations-filter-label',
						'description' => 'translate-rcfilters-translations-filter-desc',
						'cssClassSuffix' => 'filter',
						'isRowApplicableCallable' => function ( $ctx, $rc ) {
							$namespaces = self::getTranslateNamespaces();

							return !in_array( $rc->getAttribute( 'rc_namespace' ), $namespaces );
						}
					],
				],
				'queryCallable' => function ( $specialClassName, $ctx, $dbr, &$tables,
					&$fields, &$conds, &$query_options, &$join_conds, $selectedValues
				) {
					$fields[] = 'rc_title';
					$fields[] = 'rc_namespace';

					$namespaces = self::getTranslateNamespaces();
					$inNamespaceCond = 'rc_namespace IN (' .
						$dbr->makeList( $namespaces ) . ')';
					$notInNamespaceCond = 'rc_namespace NOT IN (' .
						$dbr->makeList( $namespaces ) . ')';

					$onlyCond = $dbr->makeList( [
						$inNamespaceCond,
						'rc_title ' .
							$dbr->buildLike( $dbr->anyString(), '/', $dbr->anyString() )
					], LIST_AND );
					$siteCond = $dbr->makeList( [
						$inNamespaceCond,
						'rc_title NOT' .
							$dbr->buildLike( $dbr->anyString(), '/', $dbr->anyString() )
					], LIST_AND );

					if ( count( $selectedValues ) === 3 ) {
						// no filters
						return;
					}

					if ( $selectedValues === [ 'filter', 'only' ] ) {
						$conds[] = $dbr->makeList( [
							$notInNamespaceCond,
							$onlyCond
						], LIST_OR );
						return;
					}

					if ( $selectedValues === [ 'filter', 'site' ] ) {
						$conds[] = $dbr->makeList( [
							$notInNamespaceCond,
							$siteCond
						], LIST_OR );
						return;
					}

					if ( $selectedValues === [ 'only', 'site' ] ) {
						$conds[] = $inNamespaceCond;
						return;
					}

					if ( $selectedValues === [ 'filter' ] ) {
						$conds[] = $notInNamespaceCond;
						return;
					}

					if ( $selectedValues === [ 'only' ] ) {
						$conds[] = $onlyCond;
						return;
					}

					if ( $selectedValues === [ 'site' ] ) {
						$conds[] = $siteCond;
					}
				}
			]
		);

		$special->registerFilterGroup( $translationsGroup );
		return true;
	}
}
