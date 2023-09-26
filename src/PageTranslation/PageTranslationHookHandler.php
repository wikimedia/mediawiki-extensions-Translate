<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use HTMLCheckField;
use IContextSource;
use MediaWiki\Hook\SpecialPrefixIndexGetFormFiltersHook;
use MediaWiki\Hook\SpecialPrefixIndexQueryHook;
use Wikimedia\Rdbms\SelectQueryBuilder;

class PageTranslationHookHandler implements
	SpecialPrefixIndexGetFormFiltersHook,
	SpecialPrefixIndexQueryHook
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
}
