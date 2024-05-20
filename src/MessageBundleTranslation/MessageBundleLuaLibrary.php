<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use MediaWiki\Extension\Scribunto\Engines\LuaCommon\LibraryBase;
use MediaWiki\Extension\Scribunto\Engines\LuaCommon\LuaError;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\MediaWikiServices;

class MessageBundleLuaLibrary extends LibraryBase {
	public function register() {
		$extensionLuaPath = __DIR__ . '/lua/MessageBundleLibrary.lua';
		$lib = [
			'validate' => [ $this, 'validate' ],
			'getMessageBundleTranslations' => [ $this, 'getMessageBundleTranslations' ]
		];
		$opts = [
			'pageLanguageCode' => $this->getTitle()->getPageLanguage()->getCode()
		];

		return $this->getEngine()->registerInterface( $extensionLuaPath, $lib, $opts );
	}

	public function validate( string $messageBundleTitle ): void {
		$titleFactory = MediaWikiServices::getInstance()->getTitleFactory();
		$mbTitle = $titleFactory->newFromText( $messageBundleTitle );
		if ( !MessageBundle::isSourcePage( $mbTitle ) ) {
			throw new LuaError( "$messageBundleTitle is not a valid message bundle." );
		}
	}

	public function getMessageBundleTranslations( string $messageBundleTitle, string $languageCode ): array {
		$titleFactory = MediaWikiServices::getInstance()->getTitleFactory();
		$messageBundle = new MessageBundle( $titleFactory->newFromText( $messageBundleTitle ) );
		$messageBundleTranslationLoader = Services::getInstance()->getMessageBundleTranslationLoader();
		if ( !MessageBundle::isSourcePage( $messageBundle->getTitle() ) ) {
			throw new LuaError( "Message bundle with title $messageBundleTitle not found" );
		}

		return [ $messageBundleTranslationLoader->get( $messageBundle, $languageCode ) ];
	}
}
