<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate;

use ChangesListStringOptionsFilterGroup;
use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\Hook\SpecialRecentChangesPanelHook;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\SpecialPage\Hook\ChangesListSpecialPageQueryHook;
use MediaWiki\SpecialPage\Hook\ChangesListSpecialPageStructuredFiltersHook;
use MediaWiki\Specials\SpecialRecentChanges;
use MediaWiki\Storage\NameTableAccessException;
use MediaWiki\Xml\XmlSelect;
use RecentChange;
use Wikimedia\Rdbms\ILoadBalancer;
use Wikimedia\Rdbms\IReadableDatabase;

/**
 * Class to add a new filter to Special:RecentChanges which makes it possible to filter
 * translations away or show them only.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2010, Niklas Laxström
 * @license GPL-2.0-or-later
 */
class RecentChangesTranslationFilterHookHandler implements
	SpecialRecentChangesPanelHook,
	ChangesListSpecialPageStructuredFiltersHook,
	ChangesListSpecialPageQueryHook
{
	private ILoadBalancer $loadBalancer;
	private Config $config;

	public function __construct( ILoadBalancer $loadBalancer, Config $config ) {
		$this->loadBalancer = $loadBalancer;
		$this->config = $config;
	}

	/** @inheritDoc */
	public function onChangesListSpecialPageQuery(
		$pageName,
		&$tables,
		&$fields,
		&$conds,
		&$query_options,
		&$join_conds,
		$opts
	): void {
		$translateRcFilterDefault = $this->config->get( 'TranslateRcFilterDefault' );

		if ( $pageName !== 'Recentchanges' || $this->isStructuredFilterUiEnabled() ) {
			return;
		}

		$request = RequestContext::getMain()->getRequest();
		$translations = $request->getVal( 'translations', $translateRcFilterDefault );
		$opts->add( 'translations', $translateRcFilterDefault );
		$opts->setValue( 'translations', $translations );

		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );

		$namespaces = $this->getTranslateNamespaces();

		if ( $translations === 'only' ) {
			$conds[] = 'rc_namespace IN (' . $dbr->makeList( $namespaces ) . ')';
			$conds[] = 'rc_title like \'%%/%%\'';
		} elseif ( $translations === 'filter' ) {
			$conds[] = 'rc_namespace NOT IN (' . $dbr->makeList( $namespaces ) . ')';
		} elseif ( $translations === 'site' ) {
			$conds[] = 'rc_namespace IN (' . $dbr->makeList( $namespaces ) . ')';
			$conds[] = 'rc_title not like \'%%/%%\'';
		}
	}

	private function getTranslateNamespaces(): array {
		$translateMessageNamespaces = $this->config->get( 'TranslateMessageNamespaces' );
		$namespaces = [];

		foreach ( $translateMessageNamespaces as $index ) {
			$namespaces[] = $index;
			$namespaces[] = $index + 1; // Include Talk namespaces
		}

		return $namespaces;
	}

	/**
	 * Adds a HTMl selector into $items
	 * @inheritDoc
	 */
	public function onSpecialRecentChangesPanel( &$extraOpts, $opts ): void {
		if ( $this->isStructuredFilterUiEnabled() ) {
			return;
		}

		$opts->consumeValue( 'translations' );
		$default = $opts->getValue( 'translations' );

		$label = Html::label(
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

		$extraOpts['translations'] = [ $label, $select->getHTML() ];
	}

	private function isStructuredFilterUiEnabled(): bool {
		$context = RequestContext::getMain();

		// This assumes usage only on RC page
		$page = new SpecialRecentChanges();
		$page->setContext( $context );

		return $page->isStructuredFilterUiEnabled();
	}

	/**
	 * Adds translations filters to structured UI
	 * @inheritDoc
	 */
	public function onChangesListSpecialPageStructuredFilters( $special ): void {
		$translateRcFilterDefault = $this->config->get( 'TranslateRcFilterDefault' );
		$defaultFilter = $translateRcFilterDefault !== 'noaction' ?
			$translateRcFilterDefault :
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
						'isRowApplicableCallable' => function ( IContextSource $ctx, RecentChange $rc ) {
							$namespaces = $this->getTranslateNamespaces();

							return in_array( $rc->getAttribute( 'rc_namespace' ), $namespaces ) &&
								!str_contains( $rc->getAttribute( 'rc_title' ), '/' );
						}
					],
					[
						'name' => 'site',
						'label' => 'translate-rcfilters-translations-site-label',
						'description' => 'translate-rcfilters-translations-site-desc',
						'cssClassSuffix' => 'site',
						'isRowApplicableCallable' => function ( IContextSource $ctx, RecentChange $rc ) {
							$namespaces = $this->getTranslateNamespaces();

							return in_array( $rc->getAttribute( 'rc_namespace' ), $namespaces ) &&
								!str_contains( $rc->getAttribute( 'rc_title' ), '/' );
						}
					],
					[
						'name' => 'filter',
						'label' => 'translate-rcfilters-translations-filter-label',
						'description' => 'translate-rcfilters-translations-filter-desc',
						'cssClassSuffix' => 'filter',
						'isRowApplicableCallable' => function ( IContextSource $ctx, RecentChange $rc ) {
							$namespaces = $this->getTranslateNamespaces();

							return !in_array( $rc->getAttribute( 'rc_namespace' ), $namespaces );
						}
					],
					[
						'name' => 'filter-translation-pages',
						'label' => 'translate-rcfilters-translations-filter-translation-pages-label',
						'description' => 'translate-rcfilters-translations-filter-translation-pages-desc',
						'cssClassSuffix' => 'filter-translation-pages',
						'isRowApplicableCallable' => static function ( IContextSource $ctx, RecentChange $rc ) {
							$tags = explode( ', ', $rc->getAttribute( 'ts_tags' ) ?? '' );
							return !in_array( 'translate-filter-translation-pages', $tags );
						}
					],

				],
				'queryCallable' => function (
					string $specialClassName,
					IContextSource $ctx,
					IReadableDatabase $dbr,
					array &$tables,
					array &$fields,
					array &$conds,
					array &$query_options,
					array &$join_conds,
					array $selectedValues
				) {
					$fields = array_merge( $fields, [ 'rc_title', 'rc_namespace' ] );

					// Handle changes to translation pages separately
					$filterRenderedIndex = array_search( 'filter-translation-pages', $selectedValues );
					if ( $filterRenderedIndex !== false ) {
						unset( $selectedValues[$filterRenderedIndex] );
						$selectedValues = array_values( $selectedValues );

						$changeTagDefStore = MediaWikiServices::getInstance()->getChangeTagDefStore();
						try {
							$renderedPage = $changeTagDefStore->getId( 'translate-translation-pages' );
							// Hard-coded string, as ChangeTags::CHANGE_TAG is a private const.
							$tables['translatetags'] = 'change_tag';
							$join_conds['translatetags'] = [
								'LEFT JOIN',
								[ 'translatetags.ct_rc_id=rc_id', 'translatetags.ct_tag_id' => $renderedPage ]
							];
							$conds['translatetags.ct_tag_id'] = null;
						} catch ( NameTableAccessException $exception ) {
							// Tag name does not yet exist in DB.
						}
					}

					$namespaces = $this->getTranslateNamespaces();
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
	}
}
