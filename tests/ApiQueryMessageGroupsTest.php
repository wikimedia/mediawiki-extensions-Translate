<?php
/**
 * Unit tests.
 *
 * @file
 * @author Harry Burt
 * @copyright Copyright © 2012, Harry Burt
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class ApiQueryMessageGroupsTest extends ApiTestCase {

	public function setUp() {
		parent::setUp();
		$this->setMwGlobals( array(
			'wgTranslateCC' => array(),
			'wgTranslateEC' => array(),
		) );
	}

	public function tearDown() {
		MessageGroups::clearCache();
		MessageIndexRebuildJob::newJob()->insert();
	}

	/** @dataProvider provideMessageGroups */
	public function testAPIAccuracy( $mgs = array() ) {
		// Okay, very simple, we're going to add the message group(s) to $wgTranslateCC,
		// get it into the MessageIndex,
		// and then make sure it displays correctly in the API
		global $wgTranslateCC;

		foreach( $mgs as $mg ) {
			$id = $mg->getId();
			$wgTranslateCC[$id] = $mg;
		}

		MessageGroups::clearCache();
		MessageIndexRebuildJob::newJob()->insert();

		list( $data ) = $this->doApiRequest(
			array(
				'action' => 'query',
				'meta' => 'messagegroups',
				'mgprop' => 'id|label|class|namespace|exists',
			)
		);

		// Check structure
		$this->assertCount( 1, $data );
		$this->assertArrayHasKey( 'query', $data );
		$this->assertCount( 1, $data['query'] );
		$this->assertArrayHasKey( 'messagegroups', $data['query'] );
		$this->assertCount( 2, $data['query']['messagegroups'] );

		// Basic content checks
		$items = $data['query']['messagegroups'];
		$this->assertStringEndsWith( 'id', $items[0]['id'] );
		$this->assertStringEndsWith( 'id', $items[1]['id'] );
		$this->assertSame( $items[0]['label'], 'thelabel' );
		$this->assertSame( $items[1]['label'], 'thelabel' );
		$this->assertSame( $items[0]['exists'], true );
		$this->assertSame( $items[1]['exists'], true );
		$this->assertSame( $items[0]['namespace'], 5 );
		$this->assertSame( $items[1]['namespace'], 5 );
		$this->assertSame( $items[0]['class'], 'WikiMessageGroup' );
		$this->assertSame( $items[1]['class'], 'WikiMessageGroup' );
	}

	/** @dataProvider provideMessageGroups */
	public function testAPIFilterAccuracy( $mgs = array() ) {
		// Slightly more complicated, this time we attempt a filter
		global $wgTranslateCC;

		$ids = array( 'MadeUpGroup' );

		foreach( $mgs as $mg ) {
			$id = $mg->getId();
			$ids[] = $id;

			$wgTranslateCC[$id] = $mg;
		}

		MessageGroups::clearCache();
		MessageIndexRebuildJob::newJob()->insert();

		foreach( $ids as $id ){
			list( $data ) = $this->doApiRequest(
				array(
					'action' => 'query',
					'meta' => 'messagegroups',
					'mgprop' => 'id|label|class|namespace|exists',
					'mgfilter' => $id
				)
			);

			if( $id === 'MadeUpGroup' ){			
				// Check structure (shouldn't find anything)
				$this->assertCount( 1, $data );
				$this->assertArrayHasKey( 'query', $data );
				$this->assertCount( 1, $data['query'] );
				$this->assertArrayHasKey( 'messagegroups', $data['query'] );
				$this->assertCount( 0, $data['query']['messagegroups'] );
				continue;
			}

			// Check structure (filter is unique given these names)
			$this->assertCount( 1, $data );
			$this->assertArrayHasKey( 'query', $data );
			$this->assertCount( 1, $data['query'] );
			$this->assertArrayHasKey( 'messagegroups', $data['query'] );
			$this->assertCount( 1, $data['query']['messagegroups'] );

			// Check content
			$item = $data['query']['messagegroups'][0];
			$this->assertCount( 5, $item );

			$this->assertSame( $item['id'], $id );
			$this->assertSame( $item['label'], 'thelabel' );
			$this->assertSame( $item['exists'], true );
			$this->assertStringEndsWith( 'id', $item['id'] ); // theid, anotherid
			$this->assertSame( $item['namespace'], 5 );
			$this->assertSame( $item['class'], 'WikiMessageGroup' );
		}
	}

	public function testBadProperty() {
		list( $data ) = $this->doApiRequest(
			array(
				'action' => 'query',
				'meta' => 'messagegroups',
				'mgprop' => 'madeupproperty'
			)
		);

		$this->assertCount( 2, $data );

		$this->assertArrayHasKey( 'query', $data );
		$this->assertCount( 1, $data['query'] );
		$this->assertArrayHasKey( 'messagegroups', $data['query'] );
		$this->assertCount( 0, $data['query']['messagegroups'] );

		$this->assertArrayHasKey( 'warnings', $data );
		$this->assertCount( 1, $data['warnings'] );
		$this->assertArrayHasKey( 'messagegroups', $data['warnings'] );
		$this->assertCount( 1, $data['warnings']['messagegroups'] );
		$this->assertArrayHasKey( '*', $data['warnings']['messagegroups'] );
	}

	public static function provideMessageGroups (){
		$exampleMessageGroup = new WikiMessageGroup( 'theid', 'thesource' );
		$exampleMessageGroup->setLabel( 'thelabel' ); // Example
		$exampleMessageGroup->setNamespace( 5 ); // Example

		$anotherExampleMessageGroup = new WikiMessageGroup( 'anotherid', 'thesource' );
		$anotherExampleMessageGroup->setLabel( 'thelabel' ); // Example
		$anotherExampleMessageGroup->setNamespace( 5 ); // Example

		return array(
			array( array( $exampleMessageGroup, $anotherExampleMessageGroup ) )
		);
	}
}
