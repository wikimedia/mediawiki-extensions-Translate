<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use Wikimedia\Parsoid\Ext\ExtensionModule;

class TranslateExt implements ExtensionModule {

	/** @inheritDoc */
	public function getConfig(): array {
		return [
			'name' => 'Translate',
			'annotations' => [ 'translate', 'tvar' ]
		];
	}
}
