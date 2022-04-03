<?php

use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\ParamValidator\TypeDef\IntegerDef;

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
		'@phan-var SearchableTTMServer $server';

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

	protected function getAllowedParams() {
		global $wgLanguageCode,
			$wgTranslateTranslationDefaultService;
		$available = $this->getAvailableTranslationServices();

		$filters = $this->getAllowedFilters();

		$ret = [
			'service' => [
				ParamValidator::PARAM_TYPE => $available,
			],
			'query' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'sourcelanguage' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_DEFAULT => $wgLanguageCode,
			],
			'language' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_DEFAULT => '',
			],
			'group' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_DEFAULT => '',
			],
			'filter' => [
				ParamValidator::PARAM_TYPE => $filters,
				ParamValidator::PARAM_DEFAULT => '',
			],
			'match' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_DEFAULT => '',
			],
			'case' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_DEFAULT => '0',
			],
			'offset' => [
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_DEFAULT => 0,
			],
			'limit' => [
				ParamValidator::PARAM_DEFAULT => 25,
				ParamValidator::PARAM_TYPE => 'limit',
				IntegerDef::PARAM_MIN => 1,
				IntegerDef::PARAM_MAX => ApiBase::LIMIT_SML1,
				IntegerDef::PARAM_MAX2 => ApiBase::LIMIT_SML2
			],
		];

		if ( $available ) {
			// Don't add this if no services are available, it makes
			// ApiStructureTest unhappy
			$ret['service'][ParamValidator::PARAM_DEFAULT] = $wgTranslateTranslationDefaultService;
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
