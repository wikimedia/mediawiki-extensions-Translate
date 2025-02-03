<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiMain;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\ParamValidator\TypeDef\IntegerDef;

/**
 * Api module for querying translation statistics
 * @ingroup API TranslateAPI
 * @since 2020.09
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */
class QueryTranslationStatsActionApi extends ApiBase {
	private TranslationStatsDataProvider $dataProvider;

	public function __construct(
		ApiMain $mainModule,
		string $moduleName,
		TranslationStatsDataProvider $dataProvider
	) {
		parent::__construct( $mainModule, $moduleName );
		$this->dataProvider = $dataProvider;
	}

	public function execute() {
		$params = $this->extractRequestParams();
		$graphOpts = new TranslationStatsGraphOptions();
		$graphOpts->bindArray( $params );

		$language = $this->getLanguage();

		[ $labels, $data ] = $this->dataProvider->getGraphData( $graphOpts, $language );
		$output = [
			'labels' => $labels,
			'data' => $data
		];

		$this->getResult()->addValue( null, $this->getModuleName(), $output );
	}

	protected function getAllowedParams(): array {
		return [
			'count' => [
				ParamValidator::PARAM_TYPE => $this->dataProvider->getGraphTypes(),
				ParamValidator::PARAM_REQUIRED => true,
			],
			'days' => [
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_DEFAULT => 30,
				IntegerDef::PARAM_MIN => 1,
				IntegerDef::PARAM_MAX => 10000,
				IntegerDef::PARAM_IGNORE_RANGE => false
			],
			'group' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_ISMULTI => true
			],
			'language' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_ISMULTI => true
			],
			'scale' => [
				ParamValidator::PARAM_TYPE => TranslationStatsGraphOptions::VALID_SCALES,
				ParamValidator::PARAM_DEFAULT => 'days'
			],
			'start' => [
				ParamValidator::PARAM_TYPE => 'timestamp'
			]
		];
	}

	protected function getExamplesMessages(): array {
		return [
			'action=translationstats&count=edits&days=30'
				=> 'apihelp-translationstats-example-1',
			'action=translationstats&count=edits&days=30&language=en|fr'
				=> 'apihelp-translationstats-example-2'
		];
	}
}
