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
	private int $wikitextCount;
	private const WIKITEXT_REGEX = '/{?{(PLURAL|GRAMMAR|GENDER):/';
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
		// Keep track of the number of wikitext instances in the source string.
		$this->wikitextCount = preg_match_all( self::WIKITEXT_REGEX, $text );
		return $text;
	}

	protected function unwrapUntranslatable( string $text ): string {
		if ( $this->wikitextCount !== 0 ) {
			// Verify that the wikitext instances are the same as before translation
			$postWikitextCount = preg_match_all( self::WIKITEXT_REGEX, $text );
			if ( $postWikitextCount !== $this->wikitextCount ) {
				throw new TranslationWebServiceException( 'Missing wikitext in response from MinT' );
			}
		}
		return $text;
	}
}
