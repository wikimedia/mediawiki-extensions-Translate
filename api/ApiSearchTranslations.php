<?php
/**
 * API module for search translations
 * @since 2015.07
 */
class ApiSearchTranslations extends ApiBase {

	public function execute() {
		global $wgTranslateTranslationServices;
		$params = $this->extractRequestParams();

		$config = $wgTranslateTranslationServices[$params['service']];
		$server = TTMServer::factory( $config );

		$result = $this->getResult();

		if ( $params['filter'] !== '' ) {
			$translationSearch = new CrossLanguageTranslationSearchQuery( $params, $server );
			$documents = $translationSearch->getDocuments();
			$total = $translationSearch->getTotalHits();
		} else {
			$searchResults = $server->search(
				$params['query'],
				$params,
				array( '', '' )
			);
			$documents = $server->getDocuments( $searchResults );
			$total = $server->getTotalHits( $searchResults );
		}
		$result->addValue( array( 'search', 'metadata' ), 'total', $total );
		$result->addValue( 'search', 'translations', $documents );
	}

	protected function getAvailableTranslationServices() {
		global $wgTranslateTranslationServices;

		$good = array();
		foreach ( $wgTranslateTranslationServices as $id => $config ) {
			if ( isset( $config['public'] ) && $config['public'] === true ) {
				$good[] = $id;
			}
		}

		return $good;
	}

	protected function getAllowedFilters() {
		return array(
			'',
			'translated',
			'fuzzy',
			'untranslated'
		);
	}

	public function getAllowedParams() {
		global $wgLanguageCode;
		$available = $this->getAvailableTranslationServices();
		$filters = $this->getAllowedFilters();

		return array(
			'service' => array(
				ApiBase::PARAM_TYPE => $available,
				ApiBase::PARAM_DFLT => 'TTMServer',
			),
			'query' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
			'sourcelanguage' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => $wgLanguageCode,
			),
			'language' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
			),
			'group' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
			),
			'filter' => array(
				ApiBase::PARAM_TYPE => $filters,
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
			'api.php?action=searchtranslations&language=fr&query=aide',
			'api.php?action=searchtranslations&language=fr&query=edit&filter=untranslated'
		);
	}

	// Get examples messages
	protected function getExamplesMessages() {
		return array(
			'action=searchtranslations&language=fr&query=aide'
				=> 'apihelp-searchtranslations-example-1',
			'action=searchtranslations&language=fr&query=edit&filter=untranslated'
				=> 'apihelp-searchtranslations-example-2',
		);
	}
}
