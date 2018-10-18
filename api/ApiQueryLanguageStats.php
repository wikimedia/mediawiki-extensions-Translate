<?php
/**
 * Api module for language group stats.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Api module for querying language stats.
 *
 * @ingroup API TranslateAPI
 * @since 2012-11-30
 */
class ApiQueryLanguageStats extends ApiStatsQuery {
	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'ls' );
	}

	/// Overwritten from ApiStatsQuery
	protected function validateTargetParamater( array $params ) {
		$all = TranslateUtils::getLanguageNames( null );
		$requested = $params[ 'language' ];

		if ( !isset( $all[ $requested ] ) ) {
			$this->dieWithError( [ 'apierror-translate-invalidlanguage' ] );
		}

		return $requested;
	}

	/// Overwritten from ApiStatsQuery
	protected function loadStatistics( $target, $flags = 0 ) {
		return MessageGroupStats::forLanguage( $target, $flags );
	}

	protected function makeItem( $item, $stats ) {
		$data = parent::makeItem( $item, $stats );
		$data['group'] = $item;

		return $data;
	}

	public function getAllowedParams() {
		$params = parent::getAllowedParams();
		$params['language'] = [
			ApiBase::PARAM_TYPE => 'string',
			ApiBase::PARAM_REQUIRED => true,
		];

		return $params;
	}

	protected function getExamplesMessages() {
		return [
			'action=query&meta=languagestats&lslanguage=fi'
				=> 'apihelp-query+languagestats-example-1',
		];
	}
}
