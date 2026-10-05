<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Context\IContextSource;
use MediaWiki\HTMLForm\Field\HTMLCheckField;
use MediaWiki\SpecialPage\Hook\SpecialPageBeforeFormDisplayHook;
use MediaWiki\Specials\Hook\LonelyPagesQueryHook;
use MediaWiki\Specials\Hook\SpecialPrefixIndexGetFormFiltersHook;
use MediaWiki\Specials\Hook\SpecialPrefixIndexQueryHook;
use MediaWiki\Specials\Hook\SpecialWhatLinksHereBacklinkQueryHook;
use Wikimedia\Rdbms\SelectQueryBuilder;

class PageTranslationHookHandler implements
	SpecialPrefixIndexGetFormFiltersHook,
	SpecialPrefixIndexQueryHook,
	LonelyPagesQueryHook,
	SpecialPageBeforeFormDisplayHook
{
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
		if ( $name === 'Whatlinkshere' && interface_exists( SpecialWhatLinksHereBacklinkQueryHook::class ) ) {
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
}
