<?php
/**
 * Unit tests.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Unit tests for api token retrieval.
 */
class ApiTokensTest extends MediaWikiTestCase {

	/** @dataProvider getTokenClasses */
	public function testTokenRetrieval( $id, $class ) {
		$expected = $class::getToken();

		$actionString = TranslateUtils::getTokenAction( $id );
		$params = wfCgiToArray( $actionString );

		$req = new FauxRequest( $params );
		$api = new ApiMain( $req );
		$api->execute();

		$data = $api->getResultData();
		if ( isset( $data['query'] ) ) {
			foreach ( $data['query']['pages'] as $page ) {
				$this->assertSame( $expected, $page[$id . 'token'] );
			}
		} else {
			$this->assertArrayHasKey( 'tokens', $data, 'Result has tokens' );
			$this->assertSame( $expected, $data['tokens'][$id . 'token'] );
		}
	}

	public function getTokenClasses() {
		return array(
			array( 'groupreview', 'ApiGroupReview' ),
			array( 'translationreview', 'ApiTranslationReview' ),
			array( 'aggregategroups', 'ApiAggregateGroups' ),
		);
	}
}
