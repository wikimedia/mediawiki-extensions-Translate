<?php
/**
 * Contains a class for querying external translation service.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2010-2013 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Implements support for ttmserver via MediaWiki API.
 * @see https://www.mediawiki.org/wiki/Help:Extension:Translate/Translation_memories
 * @ingroup TranslationWebService
 * @since 2013-01-01
 */
class RemoteTTMServerWebService extends TranslationWebService {
	public function getSuggestions( $translations, $from, $to ) {
		if ( $this->checkTranslationServiceFailure() ) {
			return array();
		}

		try {
			$text = $translations[$from];

			return $this->doRequest( $text, $from, $to );
		} catch ( Exception $e ) {
			$this->reportTranslationServiceFailure( $e );

			return array();
		}
	}

	protected function mapCode( $code ) {
		return $code; // Unused
	}

	protected function doPairs() {
		return null; // Unused
	}

	protected function doRequest( $text, $from, $to ) {
		$params = array(
			'format' => 'json',
			'action' => 'ttmserver',
			'sourcelanguage' => $from,
			'targetlanguage' => $to,
			'text' => $text,
			'*', // Because we hate IE
		);

		$url = $this->config['url'] . '?';
		$url .= wfArrayToCgi( $params );
		$req = MWHttpRequest::factory( $url );
		wfProfileIn( 'TranslateWebServiceRequest-' . $this->service );
		$status = $req->execute();
		wfProfileOut( 'TranslateWebServiceRequest-' . $this->service );
		$response = $req->getContent();

		if ( !$status->isOK() ) {
			// Most likely a timeout or other general error
			throw new TranslationWebServiceException(
				"Http::get failed:\n" .
					"* " . serialize( $response ) . "\n" .
					"* " . serialize( $status )
			);
		}

		$parsed = FormatJson::decode( $response, true );
		if ( !is_array( $parsed ) ) {
			throw new TranslationWebServiceException( serialize( $response ) );
		}

		if ( !isset( $parsed['ttmserver'] ) ) {
			throw new TranslationWebServiceException( 'Unexpected reply from remote server' );
		}

		return $parsed['ttmserver'];
	}
}
