<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use Wikimedia\Parsoid\Ext\ExtensionModule;

class TranslateExt implements ExtensionModule {

	/** @inheritDoc */
	public function getConfig(): array {
		if ( version_compare( MW_VERSION, '1.41', '<' ) ) {
			// Before MW 1.41 Wikimedia\Parsoid\Config\SiteConfig::processExtensionModule expected annotations
			// to be an array of strings. This was updated in I4e9a7a8bec3cb9532ef8a729fd2c6c4acca5d8a0
			return [
				'name' => 'Translate',
				'annotations' => [ 'translate', 'tvar' ]
			];
		} else {
			return [
				'name' => 'Translate',
				'annotations' => [
					'tagNames' => [ 'translate', 'tvar' ],
					'annotationStripper' =>
						[
							'class' => TranslateAnnotationStripper::class,
							'services' => [ 'Translate:TranslatablePageParser' ]
						],
				],
			];
		}
	}
}
