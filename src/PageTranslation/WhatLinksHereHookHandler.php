<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Specials\Hook\SpecialWhatLinksHereBacklinkQueryData;
use MediaWiki\Specials\Hook\SpecialWhatLinksHereBacklinkQueryHook;
use Wikimedia\Rdbms\RawSQLValue;
use Wikimedia\Rdbms\SelectQueryBuilder;

/**
 * Hides translation pages on Special:WhatLinksHere. Separate from PageTranslationHookHandler
 * as the hook interface needs MW >= 1.48.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2026.11
 */
class WhatLinksHereHookHandler implements SpecialWhatLinksHereBacklinkQueryHook {
	/** @inheritDoc */
	public function onSpecialWhatLinksHereBacklinkQuery(
		SpecialWhatLinksHereBacklinkQueryData $data,
		SelectQueryBuilder $queryBuilder
	): void {
		if ( $data->formData[ 'translate-hidetranslations' ] ) {
			$queryBuilder->leftJoin(
				'page_props',
				'translate_pp',
				[
					$data->db->expr(
						'translate_pp.pp_page',
						'=',
						new RawSQLValue( $data->sourcePageIdField )
					),
					'translate_pp.pp_propname' => 'translate-is-translation',
				]
			)
			->andWhere( [ 'translate_pp.pp_value' => null ] );
		}
	}
}
