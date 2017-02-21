<?php
/**
 * Contains a class for querying external translation service.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Implements support Caighdean translator api.
 * @see https://github.com/kscanne/caighdean/blob/master/API.md
 * @ingroup TranslationWebService
 * @since 2017.04
 */
class CaighdeanWebService extends TranslationWebService {
	public function getType() {
		return 'mt';
	}

	public function mapCode( $code ) {
		return $code;
	}

	protected function doPairs() {
		$pairs = [
			'gd' => [ 'ga' => true ],
			'gv' => [ 'ga' => true ],
		];

		return $pairs;
	}

	protected function getQuery( $text, $from, $to ) {
		if ( !isset( $this->config['url'] ) ) {
			throw new TranslationWebServiceConfigurationException( '`url` not set in configuration' );
		}

		$text = trim( $text );
		if ( $text === '' ) {
			throw new TranslationWebServiceInvalidInputException( 'Input is empty' );
		}

		$text = $this->wrapUntranslatable( $text );

		// Maximum payload is 16 KiB. Based ont testing 16000 bytes is safe by leaving 224
		// bytes for other things.
		if ( strlen( $text ) > 16000 ) {
			throw new TranslationWebServiceInvalidInputException( 'Input is over 16000 bytes long' );
		}

		$params = [
			'x-application' => 'MediaWiki Translate extension ' . TRANSLATE_VERSION,
		];

		$data = [
			'teacs' => $text,
			'foinse' => $from,
		];

		return TranslationQuery::factory( $this->config['url'] )
			->timeout( $this->config['timeout'] )
			->queryParamaters( $params )
			->postWithData( $data );
	}

	protected function parseResponse( TranslationQueryResponse $reply ) {
		$body = $reply->getBody();
		$response = FormatJson::decode( $body );
		if ( !is_array( $response ) ) {
			throw new TranslationWebServiceException( 'Invalid json: ' . serialize( $body ) );
		}

		$off = false;
		$text = implode( array_map( function ( $a ) use ( $off ) {
			$index = $off ? 0 : 1;
			if ( $a[1] === '<xs:off>' ) {
				$off = true;
				return null;
			} elseif ( $a[1] === '</xs:off>' ) {
				$off = false;
				return null;
			}

			return $a[$index];
		}, $response ) );

		$text = $this->unwrapUntranslatable( $text );
		return trim( $text );
	}

	protected function wrapUntranslatable( $text ) {
		// The response loses spaces, but retains tags.
		$text = str_replace( " ", '<xs:sp>', $text );
		$pattern = '~%[^% ]+%|\$\d|{VAR:[^}]+}|{?{(PLURAL|GRAMMAR|GENDER):[^|]+\||%(\d\$)?[sd]~';
		// This should work ok assuming no-reording.
		$wrap = '<xs:off>\0</xs:off>';
		return preg_replace( $pattern, $wrap, $text );
	}

	protected function unwrapUntranslatable( $text ) {
		$text = str_replace( '<xs:sp>', " ", $text );
		return $text;
	}
}
