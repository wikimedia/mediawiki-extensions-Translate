<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\WebService;

/**
 * Implements support for Apertium translation service via the Cxserver API
 * @ingroup TranslationWebService
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2015.02; renamed in 2023.06
 */
class ApertiumCxserverWebService extends CxserverWebService {
	// Exclusions per https://phabricator.wikimedia.org/T177434
	private const EXCLUDED_APERTIUM_TARGET_LANGUAGES = [ 'fr', 'es', 'nl' ];

	protected function handlePairsForService( array $response ): array {
		$pairs = [];
		foreach ( $response[$this->getServiceName()] as $source => $targets ) {
			$filteredTargets = array_diff( $targets, self::EXCLUDED_APERTIUM_TARGET_LANGUAGES );
			foreach ( $filteredTargets as $target ) {
				$pairs[$source][$target] = true;
			}
		}

		return $pairs;
	}

	protected function handleServiceResponse( array $responseBody ): string {
		$text = $responseBody[ 'contents' ];
		if ( preg_match( '~^<div>(.*)</div>$~', $text ) ) {
			$text = preg_replace( '~^<div>(.*)</div>$~', '\1', $text );
		}

		return trim( $this->unwrapUntranslatable( $text ) );
	}

	protected function getServiceName(): string {
		return 'Apertium';
	}
}
