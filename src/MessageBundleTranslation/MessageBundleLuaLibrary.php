<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use MediaWiki\Extension\Scribunto\Engines\LuaCommon\LibraryBase;
use MediaWiki\Extension\Scribunto\Engines\LuaCommon\LuaError;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\MediaWikiServices;

/**
 * Registers the interface for the Message bundle Scribunto Lua library
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @experimental
 */
class MessageBundleLuaLibrary extends LibraryBase {
	/** @inheritDoc */
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
		// Record template transclusion to the message bundle. We do it before isSourcePage check because
		// if the title is not a source page when the page containing the Lua page is parsed, but later becomes one,
		// the parser cache should be invalidated so that the error goes away.
		$this->getParser()->getOutput()->addTemplate( $mbTitle, $mbTitle->getId(), $mbTitle->getLatestRevID() );
		if ( !MessageBundle::isSourcePage( $mbTitle ) ) {
			throw new LuaError( "$messageBundleTitle is not a valid message bundle" );
		}
	}

	public function getMessageBundleTranslations(
		string $messageBundleTitle,
		string $languageCode,
		bool $skipFallbacks
	): array {
		$titleFactory = MediaWikiServices::getInstance()->getTitleFactory();
		$messageBundle = new MessageBundle( $titleFactory->newFromText( $messageBundleTitle ) );
		$messageBundleTranslationLoader = Services::getInstance()->getMessageBundleTranslationLoader();
		if ( !MessageBundle::isSourcePage( $messageBundle->getTitle() ) ) {
			throw new LuaError( "Message bundle with title $messageBundleTitle not found" );
		}

		if ( !Utilities::isSupportedLanguageCode( $languageCode ) ) {
			throw new LuaError( "Unsupported language code '$languageCode'" );
		}

		return [ $messageBundleTranslationLoader->get( $messageBundle, $languageCode, $skipFallbacks ) ];
	}
}
