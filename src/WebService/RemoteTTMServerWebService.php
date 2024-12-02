<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\WebService;

use MediaWiki\Json\FormatJson;

/**
 * Class for querying external translation service. Implements support for ttmserver via MediaWiki API.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013-01-01
 * @ingroup TranslationWebService
 * @see https://www.mediawiki.org/wiki/Help:Extension:Translate/Translation_memories
 */
class RemoteTTMServerWebService extends TranslationWebService {
	/** @inheritDoc */
	public function getType(): string {
		return 'ttmserver';
	}

	/** @inheritDoc */
	protected function mapCode( string $code ): string {
		return $code; // Unused
	}

	/** @inheritDoc */
	protected function doPairs(): array {
		return []; // Unused
	}

	/** @inheritDoc */
	protected function getQuery( string $text, string $sourceLanguage, string $targetLanguage ): TranslationQuery {
		$params = [
			'format' => 'json',
			'action' => 'ttmserver',
			'sourcelanguage' => $sourceLanguage,
			'targetlanguage' => $targetLanguage,
			'text' => $text
		];

		if ( isset( $this->config['service'] ) ) {
			$params['service'] = $this->config['service'];
		}

		return TranslationQuery::factory( $this->config['url'] )
			->timeout( intval( $this->config['timeout'] ) )
			->queryParameters( $params );
	}

	/** @inheritDoc */
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
