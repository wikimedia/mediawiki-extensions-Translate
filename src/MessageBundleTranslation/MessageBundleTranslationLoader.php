<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Languages\LanguageFallback;
use RuntimeException;

class MessageBundleTranslationLoader {
	private LanguageFallback $languageFallback;

	public function __construct( LanguageFallback $languageFallback ) {
		$this->languageFallback = $languageFallback;
	}

	/**
	 * Given a language code, returns translation for that language or its fallbacks in an array format.
	 * @param MessageBundle $messageBundle
	 * @param string $languageCode
	 * @param bool $skipFallbacks Skip loading the fallback languages
	 * @return array<string, string> Key is the key in the message bundle, value is the translation.
	 */
	public function get(
		MessageBundle $messageBundle,
		string $languageCode,
		bool $skipFallbacks
	): array {
		$translations = $this->getTranslationsWithFallback( $messageBundle, $languageCode, $skipFallbacks );
		$normalizedTranslations = [];
		foreach ( $translations as $key => $translation ) {
			$normalizedTranslations[
				str_replace( "{$messageBundle->getTitle()->getDBkey()}/", '', $key )
			] = $translation;
		}

		return $normalizedTranslations;
	}

	private function getTranslationsWithFallback(
		MessageBundle $messageBundle,
		string $languageCode,
		bool $skipFallbacks
	): array {
		$messageBundleGroup = MessageGroups::getGroup( $messageBundle->getMessageGroupId() );
		if ( !$messageBundleGroup ) {
			throw new RuntimeException(
				"Did not find message group for message bundle: {$messageBundle->getTitle()->getPrefixedText()}"
			);
		}

		if ( $skipFallbacks ) {
			$fallbackChain = [ $languageCode ];
		} else {
			$fallbackChain = [
				$languageCode,
				...$this->languageFallback->getAll( $languageCode ),
				$messageBundleGroup->getSourceLanguage()
			];
		}

		$collection = $messageBundleGroup->initCollection( $fallbackChain[0] );
		$translations = [];

		foreach ( $fallbackChain as $fallbackLanguageCode ) {
			$collection->resetForNewLanguage( $fallbackLanguageCode );
			// TODO use custom tag after fixing MessageCollection::filter
			$collection->setTags( 'ignored', array_keys( $translations ) );
			$collection->filter( 'ignored' );
			if ( count( $collection ) === 0 ) {
				break;
			}

			$collection->loadTranslations();
			$collection->filter( 'hastranslation', false );
			foreach ( $collection as $key => $message ) {
				if ( $message->translation() !== null ) {
					$translations[ $key ] = $message->translation();
				}
			}
		}

		return $translations;
	}
}
