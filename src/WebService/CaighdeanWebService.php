<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\WebService;

use MediaWiki\Json\FormatJson;

/**
 * Implements support Caighdean translator api.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup TranslationWebService
 * @since 2017.04
 * @see https://github.com/kscanne/caighdean/blob/master/API.md
 */
class CaighdeanWebService extends TranslationWebService {
	/** @inheritDoc */
	public function getType(): string {
		return 'mt';
	}

	/** @inheritDoc */
	protected function mapCode( string $code ): string {
		return $code;
	}

	/** @inheritDoc */
	protected function doPairs(): array {
		$pairs = [
			'gd' => [ 'ga' => true ],
			'gv' => [ 'ga' => true ],
		];

		return $pairs;
	}

	/** @inheritDoc */
	protected function getQuery( string $text, string $sourceLanguage, string $targetLanguage ): TranslationQuery {
		if ( !isset( $this->config['url'] ) ) {
			throw new TranslationWebServiceConfigurationException( '`url` not set in configuration' );
		}

		$text = trim( $text );
		if ( $text === '' ) {
			throw new TranslationWebServiceInvalidInputException( 'Input is empty' );
		}

		$data = wfArrayToCgi( [
			'foinse' => $sourceLanguage,
			'teacs' => $text,
		] );

		// Maximum payload is 16 KiB. Based ont testing 16000 bytes is safe by leaving 224
		// bytes for other things.
		if ( strlen( $data ) > 16000 ) {
			throw new TranslationWebServiceInvalidInputException( 'Input is over 16000 bytes long' );
		}

		return TranslationQuery::factory( $this->config['url'] )
			->timeout( intval( $this->config['timeout'] ) )
			->postWithData( $data )
			->attachProcessingInstructions( $text );
	}

	/** @inheritDoc */
	protected function parseResponse( TranslationQueryResponse $reply ): string {
		$body = $reply->getBody();
		$response = FormatJson::decode( $body );
		if ( !is_array( $response ) ) {
			throw new TranslationWebServiceException( 'Invalid json: ' . serialize( $body ) );
		}

		$text = '';
		$originalText = $reply->getQuery()->getProcessingInstructions();
		foreach ( $response as [ $sourceToken, $targetToken ] ) {
			$separator = ' ';
			$pos = strpos( $originalText, $sourceToken );
			// Try to keep the effects local. If we fail to match at token, we could accidentally
			// scan very far ahead in the text, find a false match and not find matches for all
			// of the tokens in the between.
			if ( $pos !== false && $pos < 50 ) {
				// Remove the portion of text we have processed. $pos should be zero, unless
				// we failed to match something earlier.
				$originalText = substr( $originalText, $pos + strlen( $sourceToken ) );
				if ( preg_match( '/^\s+/', $originalText, $match ) ) {
					$separator = $match[ 0 ];
					$originalText = substr( $originalText, strlen( $separator ) );
				} else {
					$separator = '';
				}
			}

			$text .= $targetToken . $separator;
		}

		return $text;
	}
}
