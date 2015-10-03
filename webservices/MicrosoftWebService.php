<?php
/**
 * Contains a class for querying external translation service.
 *
 * @file
 * @author Niklas Laxström, Ulrich Strauss
 * @license GPL-2.0+
 */

/**
 * Implements support for Microsoft translation api v2.
 * @see http://msdn.microsoft.com/en-us/library/ff512421.aspx
 * @ingroup TranslationWebService
 * @since 2013-01-01
 */
class MicrosoftWebService extends TranslationWebService {
	public function getType() {
		return 'mt';
	}

	protected function mapCode( $code ) {
		$map = array(
			'zh-hant' => 'zh-CHT',
			'zh-hans' => 'zh-CHS',
		);

		return isset( $map[$code] ) ? $map[$code] : $code;
	}

	protected function getMSTokens($clientID, $clientSecret)
	{

		$authUrl = "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/";

		$params = array(
			'grant_type' => "client_credentials",
			'scope' => "http://api.microsofttranslator.com",
			'client_id' => $clientID,
			'client_secret' => $clientSecret
		);

		$params = wfArrayToCgi($params);

		$options['method']   = 'POST';
		$options['timeout']  = $this->config['timeout'];
		$options['postData'] = $params;
		
		$req = MWHttpRequest::factory($authUrl, $options);

		$status = $req->execute();
		if (!$status->isOK()) {
			$error = $req->getContent();
			// Most likely a timeout or other general error
			throw new TranslationWebServiceException('Http::get failed: ' . $authUrl . serialize($error) . serialize($status));
		}
		$ret = $req->getContent();

		$objResponse = json_decode($ret);
		if ($objResponse->error) {
			throw new TranslationWebServiceException($objResponse->error_description);
		}
		return $objResponse->access_token;
		
	}

	protected function doPairs() {
		if ( !isset($this->config['clientId']) || !isset($this->config['clientSecret'])) {
			throw new TranslationWebServiceException('clientId or clientSecret is not set');
		}
		

		$clientID = $this->config['clientId'];
		$clientSecret = $this->config['clientSecret'];

		$accessToken = $this->getMSTokens($clientID, $clientSecret); // get access token from service

		$options = array();
		$options['method']  = 'GET';
		$options['timeout'] = $this->config['timeout'];

		$url = 'http://api.microsofttranslator.com/V2/Http.svc/GetLanguagesForTranslate?';

		$req = MWHttpRequest::factory($url, $options);

		$req->setHeader("Authorization", "Bearer " . $accessToken);

		wfProfileIn('TranslateWebServiceRequest-' . $this->service . '-pairs');
		$status = $req->execute();
		wfProfileOut('TranslateWebServiceRequest-' . $this->service . '-pairs');

		if (!$status->isOK()) {
			$error = $req->getContent();
			// Most likely a timeout or other general error
			throw new TranslationWebServiceException('Http::get failed:' . serialize($error) . serialize($status));
		}
		$xml = simplexml_load_string($req->getContent());

		$languages = array();
		foreach ($xml->string as $language) {
			$languages[] = strval($language);
		}

		// Let's make a cartesian product, assuming we can translate from any language to any language
		$pairs = array();
		foreach ($languages as $from) {
			foreach ($languages as $to) {
				$pairs[$from][$to] = true;
			}
		}

		return $pairs;
	}

	protected function getQuery( $text, $from, $to ) {
		if ( !isset($this->config['clientId']) || !isset($this->config['clientSecret'])) {
			throw new TranslationWebServiceException('clientId or clientSecret is not set');
		}

		$text = trim( $text );
		$text = $this->wrapUntranslatable( $text );

		$accessToken = $this->getMSTokens($this->config['clientId'], $this->config['clientSecret']); // get access token from service

		$params = array(
			'text' => $text,
			'from' => $from,
			'to' => $to,
		);
		$headers = array(
				'Authorization' => 'Bearer '.$accessToken,
			);

		return TranslationQuery::factory( $this->config['url'] )
			->timeout( $this->config['timeout'] )
			->queryParamaters( $params )->queryHeaders($headers);
	}

	protected function parseResponse( TranslationQueryResponse $reply ) {
		$body = $reply->getBody();

		$text = preg_replace( '~<string.*>(.*)</string>~', '\\1', $body );
		$text = Sanitizer::decodeCharReferences( $text );
		$text = str_replace('! N!', '!N!', $text); // Cleanup MS specific newline
		$text = $this->unwrapUntranslatable( $text );

		return $text;
	}
}
