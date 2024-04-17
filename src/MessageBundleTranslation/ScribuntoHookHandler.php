<?php

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use MediaWiki\Extension\Scribunto\Hooks\ScribuntoExternalLibrariesHook;

class ScribuntoHookHandler implements ScribuntoExternalLibrariesHook {
	public function onScribuntoExternalLibraries( string $engine, array &$extraLibraries ): void {
		if ( $engine !== 'lua' ) {
			return;
		}

		$extraLibraries['mw.ext.translate.messageBundle'] = [
			'class' => MessageBundleLuaLibrary::class,
			'deferLoad' => true
		];
	}
}
