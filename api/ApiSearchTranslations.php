<?php
/**
 * API module for search translations
 * @since 2015.06
 */
class ApiSearchTranslations extends ApiBase {

	public function execute() {
		global $wgTranslateTranslationServices;
		$params = $this->extractRequestParams();

		$config = $wgTranslateTranslationServices[$params['service']];
		$server = TTMServer::factory( $config );

		$opts = new FormOptions();
		foreach ( $params as $param => $value ) {
			$opts->add( $param, $value );
		}

		$searchResults = $server->search(
			$params['query'],
			$opts,
			''
		);

		$result = $this->getResult();
		$documents = $server->getDocuments( $searchResults );

		$result->addValue( 'search', 'translations', $documents );
	}

	public function getAllowedParams() {

		return array(
			'service' => array(
				ApiBase::PARAM_DFLT => 'TTMServer',
			),
			'query' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
			'language' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
			),
			'group' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
			),
			'offset' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => 0,
			),
			'limit' => array(
				ApiBase::PARAM_DFLT => 25,
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_MIN => 1,
				ApiBase::PARAM_MAX => ApiBase::LIMIT_SML1,
				ApiBase::PARAM_MAX2 => ApiBase::LIMIT_SML2
			),
		);
	}

	// Get examples
	public function getExamples() {
		return array(
			'api.php?action=searchtranslations&language=fr&query=Help'
				=> 'apihelp-searchtranslations-example-1',
		);
	}
}
