<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Context\IContextSource;
use MediaWiki\Hook\LonelyPagesQueryHook;
use MediaWiki\Hook\SpecialPrefixIndexGetFormFiltersHook;
use MediaWiki\Hook\SpecialPrefixIndexQueryHook;
use MediaWiki\HTMLForm\Field\HTMLCheckField;
use Wikimedia\Rdbms\SelectQueryBuilder;

class PageTranslationHookHandler implements
	SpecialPrefixIndexGetFormFiltersHook,
	SpecialPrefixIndexQueryHook,
	LonelyPagesQueryHook
{

	public function onSpecialPrefixIndexGetFormFilters( IContextSource $contextSource, array &$filters ) {
		$filters[ 'translate-hidetranslations' ] = [
			'class' => HTMLCheckField::class,
			'name' => 'translate-hidetranslations',
			'label-message' => 'translate-hidetranslations',
		];
	}

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
}
