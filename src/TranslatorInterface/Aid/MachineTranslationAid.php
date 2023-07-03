<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Aid;

use MediaWiki\Extension\Translate\TranslatorInterface\TranslationHelperException;
use MediaWiki\Extension\Translate\WebService\TranslationWebService;
use MediaWiki\Extension\Translate\WebService\TranslationWebServiceConfigurationException;

/**
 * Translation aid that provides suggestion from machine translation services.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013-01-01 | 2015.02 extends QueryAggregatorAwareTranslationAid
 * @ingroup TranslationAids
 */
class MachineTranslationAid extends QueryAggregatorAwareTranslationAid {
	public function populateQueries(): void {
		$definition = $this->dataProvider->getDefinition();
		$translations = $this->dataProvider->getGoodTranslations();
		$sourceLanguage = $this->group->getSourceLanguage();
		$targetLanguage = $this->handle->getCode();

		if ( trim( $definition ) === '' ) {
			return;
		}

		foreach ( $this->getWebServices() as $service ) {
			if ( $service->checkTranslationServiceFailure() ) {
				continue;
			}

			try {
				if ( $service->isSupportedLanguagePair( $sourceLanguage, $targetLanguage ) ) {
					$this->storeQuery( $service, $sourceLanguage, $targetLanguage, $definition );
					continue;
				}

				// Search for translations which we can use as a source for MT
				// @todo: Support setting priority of languages like Yandex used to have
				foreach ( $translations as $alternateSourceLanguage => $text ) {
					if ( !$service->isSupportedLanguagePair( $alternateSourceLanguage, $targetLanguage ) ) {
						continue;
					}

					$this->storeQuery( $service, $alternateSourceLanguage, $targetLanguage, $text );
					break;
				}
			} catch ( TranslationWebServiceConfigurationException $e ) {
				throw new TranslationHelperException( $service->getName() . ': ' . $e->getMessage() );
			}
		}
	}

	public function getData(): array {
		$suggestions = [ '**' => 'suggestion' ];

		foreach ( $this->getQueryData() as $queryData ) {
			$suggestions[] = $this->formatSuggestion( $queryData );
		}

		return array_filter( $suggestions );
	}

	protected function formatSuggestion( array $queryData ): ?array {
		$service = $queryData['service'];
		$response = $queryData['response'];
		$sourceLanguage = $queryData['language'];
		$sourceText = $queryData['text'];

		$result = $service->getResultData( $response );
		if ( $result === null ) {
			return null;
		}

		return [
			'target' => $result,
			'service' => $service->getName(),
			'source_language' => $sourceLanguage,
			'source' => $sourceText,
		];
	}

	/** @return TranslationWebService[] */
	private function getWebServices(): array {
		global $wgTranslateTranslationServices;

		$services = [];
		foreach ( $wgTranslateTranslationServices as $name => $config ) {
			$service = TranslationWebService::factory( $name, $config );
			if ( !$service || $service->getType() !== 'mt' ) {
				continue;
			}

			$services[$name] = $service;
		}

		return $services;
	}
}
