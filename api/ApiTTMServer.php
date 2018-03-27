<?php
/**
 * API module for TTMServer
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * API module for TTMServer
 *
 * @ingroup API TranslateAPI TTMServer
 * @since 2012-01-26
 */
class ApiTTMServer extends ApiBase {

	public function execute() {
		global $wgTranslateTranslationServices;

		if ( !$this->getAvailableTranslationServices() ) {
			$this->dieWithError( 'apierror-translate-notranslationservices' );
		}

		$params = $this->extractRequestParams();

		$config = $wgTranslateTranslationServices[$params['service']];
		$server = TTMServer::factory( $config );

		$suggestions = $server->query(
			$params['sourcelanguage'],
			$params['targetlanguage'],
			$params['text']
		);

		$result = $this->getResult();
		foreach ( $suggestions as $sug ) {
			$sug['location'] = $server->expandLocation( $sug );
			unset( $sug['wiki'] );
			$result->addValue( $this->getModuleName(), null, $sug );
		}

		$result->addIndexedTagName( $this->getModuleName(), 'suggestion' );
	}

	protected function getAvailableTranslationServices() {
		global $wgTranslateTranslationServices;

		$good = [];
		foreach ( $wgTranslateTranslationServices as $id => $config ) {
			if ( isset( $config['public'] ) && $config['public'] === true ) {
				$good[] = $id;
			}
		}

		return $good;
	}

	public function getAllowedParams() {
		global $wgTranslateTranslationDefaultService;
		$available = $this->getAvailableTranslationServices();

		$ret = [
			'service' => [
				ApiBase::PARAM_TYPE => $available,
			],
			'sourcelanguage' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
			'targetlanguage' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
			'text' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
		];

		if ( $available ) {
			// Don't add this if no services are available, it makes
			// ApiStructureTest unhappy
			$ret['service'][ApiBase::PARAM_DFLT] = $wgTranslateTranslationDefaultService;
		}

		return $ret;
	}

	protected function getExamplesMessages() {
		return [
			'action=ttmserver&sourcelanguage=en&targetlanguage=fi&text=Help'
				=> 'apihelp-ttmserver-example-1',
		];
	}
}
