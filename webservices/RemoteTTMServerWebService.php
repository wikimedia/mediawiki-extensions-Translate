<?php
/**
 * Contains a class for querying external translation service.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2010-2013 Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Implements support for ttmserver via MediaWiki API.
 * @see https://www.mediawiki.org/wiki/Help:Extension:Translate/Translation_memories
 * @ingroup TranslationWebService
 * @since 2013-01-01
 */
class RemoteTTMServerWebService extends TranslationWebService {
	public function getType() {
		return 'ttmserver';
	}

	protected function mapCode( $code ) {
		return $code; // Unused
	}

	protected function doPairs() {
		return null; // Unused
	}

	protected function getQuery( $text, $from, $to ) {
		$params = array(
			'format' => 'json',
			'action' => 'ttmserver',
			'sourcelanguage' => $from,
			'targetlanguage' => $to,
			'text' => $text
		);

		if ( isset( $this->config['service'] ) ) {
			$params['service'] = $this->config['service'];
		}

		return TranslationQuery::factory( $this->config['url'] )
			->timeout( $this->config['timeout'] )
			->queryParamaters( $params );
	}

	protected function parseResponse( TranslationQueryResponse $reply ) {
		$body = $reply->getBody();
		$parsed = FormatJson::decode( $body, true );
		if ( !is_array( $parsed ) ) {
			throw new TranslationWebServiceException( 'Invalid json: ' . serialize( $body ) );
		}

		if ( !isset( $parsed['ttmserver'] ) ) {
			throw new TranslationWebServiceException( 'Unexpected reply from remote server' );
		}

		return $parsed['ttmserver'];
	}
}
