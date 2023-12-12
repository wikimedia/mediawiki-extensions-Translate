<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\WebService;

/**
 * Implements support for MinT translation service via the Cxserver API
 * @ingroup TranslationWebService
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2023.06
 */
class MintCxserverWebService extends CxserverWebService {
	private const WIKITEXT_REGEX = [
		'/\b(PLURAL|GRAMMAR|GENDER)\b/',
		'/==\s*([^=]+)\s*==/', // heading
		"/''.+?''/", // italics & bold
		'/\[\[([^\[\]]+)\]\]/', // links
		'/\{\{([^{}]+)\}\}/', // templates
	];
	private const EXCLUDED_TARGET_LANGUAGES = [ 'zh' ];

	protected function handlePairsForService( array $response ): array {
		$pairs = [];
		foreach ( $response[$this->getServiceName()] as $source => $targets ) {
			$filteredTargets = array_diff( $targets, self::EXCLUDED_TARGET_LANGUAGES );
			foreach ( $filteredTargets as $target ) {
				$pairs[$source][$target] = true;
			}
		}

		return $pairs;
	}

	protected function getServiceName(): string {
		return 'MinT';
	}

	protected function handleServiceResponse( array $responseBody ): string {
		return trim( $this->unwrapUntranslatable( $responseBody[ 'contents' ] ) );
	}

	protected function wrapUntranslatable( string $text ): string {
		// Check if at least one instance of the patterns exists in the source string
		foreach ( self::WIKITEXT_REGEX as $pattern ) {
			if ( preg_match( $pattern, $text ) ) {
				throw new TranslationWebServiceInvalidInputException(
					'Wikitext instance(s) in source string. See T349375'
				);
			}
		}

		return $text;
	}
}
