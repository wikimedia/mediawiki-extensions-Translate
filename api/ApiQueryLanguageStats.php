<?php
/**
 * Api module for language group stats.
 *
 * @file
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
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

	protected function getData() {
		$params = $this->extractRequestParams();

		return MessageGroupStats::forLanguage( $params['language'] );
	}

	protected function makeItem( $item, $stats ) {
		$data = parent::makeItem( $item, $stats );
		$data['group'] = $item;

		return $data;
	}

	public function getAllowedParams() {
		$params = parent::getAllowedParams();
		$params['language'] = array(
			ApiBase::PARAM_TYPE => 'string',
			ApiBase::PARAM_REQUIRED => true,
		);

		return $params;
	}

	public function getParamDescription() {
		$desc = parent::getParamDescription();
		$desc['language'] = 'Language code';

		return $desc;
	}

	public function getDescription() {
		return 'Query language stats';
	}

	protected function getExamples() {
		return array(
			"api.php?action=query&meta=messagegroupstats&code=fi List of translation completion statistics for language fi",
		);
	}
}
