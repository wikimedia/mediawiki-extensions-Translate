<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use MediaWiki\Extension\Scribunto\Engines\LuaCommon\LibraryBase;

class MessageBundleLuaLibrary extends LibraryBase {
	public function register() {
		$extensionLuaPath = __DIR__ . '/lua/MessageBundleLibrary.lua';
		$lib = [
			'getMessageBundleTranslations' => [ $this, 'getMessageBundleTranslations' ]
		];
		$opts = [];

		return $this->getEngine()->registerInterface( $extensionLuaPath, $lib, $opts );
	}

	public function getMessageBundleTranslations( string $messageBundleTitle, string $languageCode ): array {
		// TODO: Actually load the translation
		return [ [
			'title' => $messageBundleTitle,
			'code' => $languageCode,
			'test' => 'pewpew'
		] ];
	}
}
