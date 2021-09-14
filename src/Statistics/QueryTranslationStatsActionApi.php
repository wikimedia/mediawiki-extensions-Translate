<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use ApiBase;
use ApiMain;
use MediaWiki\Extension\Translate\Services;

/**
 * Api module for querying translation statistics
 * @ingroup API TranslateAPI
 * @since 2020.09
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */
class QueryTranslationStatsActionApi extends ApiBase {
	/** @var TranslationStatsDataProvider */
	private $dataProvider;

	public function __construct( ApiMain $mainModule, $moduleName ) {
		parent::__construct( $mainModule, $moduleName );
		$this->dataProvider = Services::getInstance()->getTranslationStatsDataProvider();
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

	protected function getAllowedParams() {
		return [
			'count' => [
				ApiBase::PARAM_TYPE => $this->dataProvider->getGraphTypes(),
				ApiBase::PARAM_REQUIRED => true,
			],
			'days' => [
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_DFLT => 30,
				ApiBase::PARAM_MIN => 1,
				ApiBase::PARAM_MAX => 10000,
				ApiBase::PARAM_RANGE_ENFORCE => true
			],
			'group' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_ISMULTI => true
			],
			'language' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_ISMULTI => true
			],
			'scale' => [
				ApiBase::PARAM_TYPE => TranslationStatsGraphOptions::VALID_SCALES,
				ApiBase::PARAM_DFLT => 'days'
			],
			'start' => [
				ApiBase::PARAM_TYPE => 'timestamp'
			]
		];
	}

	protected function getExamplesMessages() {
		return [
			'action=translationstats&count=edits&days=30'
				=> 'apihelp-translationstats-example-1',
			'action=translationstats&count=edits&days=30&language=en|fr'
				=> 'apihelp-translationstats-example-2'
		];
	}
}
