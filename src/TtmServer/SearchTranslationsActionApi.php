<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TtmServer;

use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiMain;
use MediaWiki\Config\Config;
use MediaWiki\Config\ServiceOptions;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\ParamValidator\TypeDef\IntegerDef;

/**
 * API module for search translations
 * @license GPL-2.0-or-later
 */
class SearchTranslationsActionApi extends ApiBase {
	/** @var TtmServerFactory */
	private $ttmServerFactory;
	/** @var ServiceOptions */
	private $options;

	private const CONSTRUCTOR_OPTIONS = [
		'LanguageCode',
		'TranslateTranslationDefaultService',
		'TranslateTranslationServices',
	];

	public function __construct(
		ApiMain $main,
		string $moduleName,
		Config $config,
		TtmServerFactory $ttmServerFactory
	) {
		parent::__construct( $main, $moduleName );
		$this->options = new ServiceOptions( self::CONSTRUCTOR_OPTIONS, $config );
		$this->ttmServerFactory = $ttmServerFactory;
	}

	public function execute(): void {
		if ( !$this->getSearchableTtmServers() ) {
			$this->dieWithError( 'apierror-translate-notranslationservices' );
		}

		$params = $this->extractRequestParams();

		$server = $this->ttmServerFactory->create( $params[ 'service' ] );
		if ( !$server instanceof SearchableTtmServer ) {
			$this->dieWithError( 'apierror-translate-notranslationservices' );
		}

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

	/** @return string[] */
	private function getSearchableTtmServers(): array {
		$ttmServiceIds = $this->ttmServerFactory->getNames();

		$good = [];
		foreach ( $ttmServiceIds as $serviceId ) {
			$ttmServer = $this->ttmServerFactory->create( $serviceId );
			if ( $ttmServer instanceof SearchableTtmServer ) {
				$good[] = $serviceId;
			}
		}

		return $good;
	}

	protected function getAllowedFilters(): array {
		return [
			'',
			'translated',
			'fuzzy',
			'untranslated'
		];
	}

	protected function getAllowedParams(): array {
		$available = $this->getSearchableTtmServers();

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
				ParamValidator::PARAM_DEFAULT => $this->options->get( 'LanguageCode' ),
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
			$ret['service'][ParamValidator::PARAM_DEFAULT] =
				$this->options->get( 'TranslateTranslationDefaultService' );
		}

		return $ret;
	}

	protected function getExamplesMessages(): array {
		return [
			'action=searchtranslations&language=fr&query=aide'
				=> 'apihelp-searchtranslations-example-1',
			'action=searchtranslations&language=fr&query=edit&filter=untranslated'
				=> 'apihelp-searchtranslations-example-2',
		];
	}
}
