<?php
/**
 * Api module for querying message group stats.
 *
 * @file
 * @author Tim Gerundt
 * @copyright Copyright © 2012-2013, Tim Gerundt
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Api module for querying message group stats.
 *
 * @ingroup API TranslateAPI
 */
class ApiQueryMessageGroupStats extends ApiStatsQuery {
	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'mgs' );
	}

	protected function getData() {
		$params = $this->extractRequestParams();
		$group = MessageGroups::getGroup( $params['group'] );
		if ( !$group ) {
			$this->dieUsageMsg( array( 'missingparam', 'mcgroup' ) );
		}

		return MessageGroupStats::forGroup( $group->getId() );
	}

	protected function makeItem( $item, $stats ) {
		$data = parent::makeItem( $item, $stats );
		$data['code'] = $item; // For BC
		$data['language'] = $item;

		return $data;
	}

	public function getAllowedParams() {
		$params = parent::getAllowedParams();
		$params['group'] = array(
			ApiBase::PARAM_TYPE => 'string',
			ApiBase::PARAM_REQUIRED => true,
		);

		return $params;
	}

	public function getParamDescription() {
		$desc = parent::getParamDescription();
		$desc['group'] = 'Message group id';

		return $desc;
	}

	public function getDescription() {
		return 'Query message group stats';
	}

	protected function getExamples() {
		$groups = MessageGroups::getAllGroups();
		$group = key( $groups );

		return array(
			"api.php?action=query&meta=messagegroupstats&mgsgroup=$group List of translation completion statistics for group $group",
		);
	}
}
