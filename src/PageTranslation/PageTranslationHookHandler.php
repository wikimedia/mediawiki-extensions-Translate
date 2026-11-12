<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Context\IContextSource;
use MediaWiki\Deferred\LinksUpdate\LinksTable;
use MediaWiki\HTMLForm\Field\HTMLCheckField;
use MediaWiki\SpecialPage\Hook\SpecialPageBeforeFormDisplayHook;
use MediaWiki\Specials\Hook\LonelyPagesQueryHook;
use MediaWiki\Specials\Hook\SpecialPrefixIndexGetFormFiltersHook;
use MediaWiki\Specials\Hook\SpecialPrefixIndexQueryHook;
use MediaWiki\Specials\Hook\SpecialWhatLinksHereBacklinkQueryHook;
use Wikimedia\Rdbms\ILBFactory;
use Wikimedia\Rdbms\SelectQueryBuilder;

class PageTranslationHookHandler implements
	SpecialPrefixIndexGetFormFiltersHook,
	SpecialPrefixIndexQueryHook,
	LonelyPagesQueryHook,
	SpecialPageBeforeFormDisplayHook
{
	public function __construct( private readonly ILBFactory $lbFactory ) {
	}

	/** @inheritDoc */
	public function onSpecialPrefixIndexGetFormFilters( IContextSource $contextSource, array &$filters ) {
		$filters[ 'translate-hidetranslations' ] = [
			'class' => HTMLCheckField::class,
			'name' => 'translate-hidetranslations',
			'label-message' => 'translate-hidetranslations',
		];
	}

	/** @inheritDoc */
	public function onSpecialPrefixIndexQuery( array $fieldData, SelectQueryBuilder $queryBuilder ) {
		if ( $fieldData[ 'translate-hidetranslations' ] === true ) {
			$queryBuilder->leftJoin(
				'page_props',
				'translate_pp',
				[
					'translate_pp.pp_page=page_id',
					'translate_pp.pp_propname' => 'translate-is-translation'
				]
			)->andWhere( [ 'translate_pp.pp_value' => null ] );
		}
	}

	/** @inheritDoc */
	public function onLonelyPagesQuery( &$tables, &$conds, &$joinConds ) {
		$tables[ 'translate_pp' ] = 'page_props';
		$joinConds['translate_pp'] = [
			'LEFT JOIN', [
				'translate_pp.pp_page=page_id',
				'translate_pp.pp_propname' => 'translate-is-translation'
			]
		];
		$conds['translate_pp.pp_value'] = null;
	}

	/** @inheritDoc */
	public function onSpecialPageBeforeFormDisplay( $name, $form ): void {
		// The filter needs the SpecialWhatLinksHereBacklinkQuery hook added in MW 1.48
		// The filter joins page_props to the link tables. This join fails when the link
		// tables are on a separate cluster (T444583), thus do not show the filter then.
		if (
			$name === 'Whatlinkshere' &&
			interface_exists( SpecialWhatLinksHereBacklinkQueryHook::class ) &&
			!$this->linkTablesAreOnSeparateCluster()
		) {
			$form->addFields( [
				'translate-hidetranslations' => [
					'type' => 'check',
					'name' => 'translate-hidetranslations',
					'label-message' => 'translate-hidetranslations',
					'section' => 'whatlinkshere-filter',
				]
			] );
		}
	}

	private function linkTablesAreOnSeparateCluster(): bool {
		return $this->lbFactory->getLoadBalancer( LinksTable::VIRTUAL_DOMAIN ) !== $this->lbFactory->getMainLB();
	}
}
