<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Context\IContextSource;
use MediaWiki\Hook\LonelyPagesQueryHook;
use MediaWiki\Hook\SpecialPrefixIndexGetFormFiltersHook;
use MediaWiki\Hook\SpecialPrefixIndexQueryHook;
use MediaWiki\Hook\SpecialWhatLinksHereQueryHook;
use MediaWiki\HTMLForm\Field\HTMLCheckField;
use MediaWiki\SpecialPage\Hook\SpecialPageBeforeFormDisplayHook;
use Wikimedia\Rdbms\SelectQueryBuilder;

class PageTranslationHookHandler implements
	SpecialPrefixIndexGetFormFiltersHook,
	SpecialPrefixIndexQueryHook,
	LonelyPagesQueryHook,
	SpecialPageBeforeFormDisplayHook,
	SpecialWhatLinksHereQueryHook
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
		// Temporarily disabled: https://phabricator.wikimedia.org/T385139#10559646
		if ( $name === 'Whatlinkshere' ) {
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

	/** @inheritDoc */
	public function onSpecialWhatLinksHereQuery( $table, $data, $queryBuilder ) {
		// Temporarily disabled: https://phabricator.wikimedia.org/T385139#10559646
		$isSupportedTable = in_array( $table, [ 'pagelinks', 'templatelinks', 'imagelinks' ] );

		if ( $data[ 'translate-hidetranslations' ] && $isSupportedTable ) {
			$queryBuilder->leftJoin(
				'page_props',
				'translate_pp',
				[
					'translate_pp.pp_page=page_id',
					'translate_pp.pp_propname' => 'translate-is-translation',
				]
			)
			->andWhere( [ 'translate_pp.pp_value' => null ] );
		}
	}
}
