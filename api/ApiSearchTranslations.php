<?php
/**
 * API module for search translations
 * @since 2015.07
 * @license GPL-2.0-or-later
 */
class ApiSearchTranslations extends ApiBase {
	public function execute() {
		global $wgTranslateTranslationServices;

		if ( !$this->getAvailableTranslationServices() ) {
			$this->dieWithError( 'apierror-translate-notranslationservices' );
		}

		$params = $this->extractRequestParams();

		$config = $wgTranslateTranslationServices[$params['service']];
		/** @var SearchableTTMServer $server */
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
				[ '', '' ]
			);
			$documents = $server->getDocuments( $searchResults );
			$total = $server->getTotalHits( $searchResults );
		}
		$result->addValue( [ 'search', 'metadata' ], 'total', $total );
		$result->addValue( 'search', 'translations', $documents );
	}

	protected function getAvailableTranslationServices() {
		global $wgTranslateTranslationServices;

		$good = [];
		foreach ( $wgTranslateTranslationServices as $id => $config ) {
			if ( TTMServer::factory( $config ) instanceof SearchableTTMServer ) {
				$good[] = $id;
			}
		}

		return $good;
	}

	protected function getAllowedFilters() {
		return [
			'',
			'translated',
			'fuzzy',
			'untranslated'
		];
	}

	public function getAllowedParams() {
		global $wgLanguageCode,
			$wgTranslateTranslationDefaultService;
		$available = $this->getAvailableTranslationServices();

		$filters = $this->getAllowedFilters();

		$ret = [
			'service' => [
				ApiBase::PARAM_TYPE => $available,
			],
			'query' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
			'sourcelanguage' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => $wgLanguageCode,
			],
			'language' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
			],
			'group' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
			],
			'filter' => [
				ApiBase::PARAM_TYPE => $filters,
				ApiBase::PARAM_DFLT => '',
			],
			'match' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
			],
			'case' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '0',
			],
			'offset' => [
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => 0,
			],
			'limit' => [
				ApiBase::PARAM_DFLT => 25,
				ApiBase::PARAM_TYPE => 'limit',
				ApiBase::PARAM_MIN => 1,
				ApiBase::PARAM_MAX => ApiBase::LIMIT_SML1,
				ApiBase::PARAM_MAX2 => ApiBase::LIMIT_SML2
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
			'action=searchtranslations&language=fr&query=aide'
				=> 'apihelp-searchtranslations-example-1',
			'action=searchtranslations&language=fr&query=edit&filter=untranslated'
				=> 'apihelp-searchtranslations-example-2',
		];
	}
}
