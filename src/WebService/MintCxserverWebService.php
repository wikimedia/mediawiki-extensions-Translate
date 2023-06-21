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
	protected function handlePairsForService( array $response ): array {
		$pairs = [];
		foreach ( $response[$this->getServiceName()] as $source => $targets ) {
			foreach ( $targets as $target ) {
				$pairs[$source][$target] = true;
			}
		}

		return $pairs;
	}

	protected function getServiceName(): string {
		return 'MinT';
	}

	protected function handleServiceResponse( array $responseBody ): string {
		$text = $responseBody[ 'contents' ];
		if ( preg_match( '~^<div>(.*)</div>$~', $text ) ) {
			$text = preg_replace( '~^<div>(.*)</div>$~', '\1', $text );
		}

		return trim( $this->unwrapUntranslatable( $text ) );
	}
}
