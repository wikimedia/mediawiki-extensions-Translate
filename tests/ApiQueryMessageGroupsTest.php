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

	function setUp() {
		parent::setUp();
	}

	function tearDown() {
		global $wgTranslateCC;
		unset( $wgTranslateCC['theid'] );
		unset( $wgTranslateCC['anotherid'] );

		MessageGroups::clearCache();
		MessageIndexRebuildJob::newJob()->insert();
	}

	/** @dataProvider provideMessageGroups */
	function testAPIAccuracy( $mgs = array() ) {
		// Okay, very simple, we're going to add the message group(s) to $wgTranslateCC,
		// get it into the MessageIndex,
		// and then make sure it displays correctly in the API
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
				// Check structure
				$this->assertEquals( $id, 'id' );
				$this->assertCount( 1, $data );
				$this->assertArrayHasKey( 'query', $data );
				$this->assertCount( 1, $data['query'] );
				$this->assertArrayHasKey( 'messagegroups', $data['query'] );
				$this->assertCount( 0, $data['query']['messagegroups'] ); // Shouldn't find anything
				continue;
			}

			// Check structure
			$this->assertCount( 1, $data );
			$this->assertArrayHasKey( 'query', $data );
			$this->assertCount( 1, $data['query'] );
			$this->assertArrayHasKey( 'messagegroups', $data['query'] );
			$this->assertCount( 1, $data['query']['messagegroups'] ); // Filter is unique given these names

			// Check content
			$item = $data['query']['groups'][0];
			$this->assertCount( 5, $item );

			$this->assertSame( $item['id'], $id );
			$this->assertSame( $item['label'], 'thelabel' );
			$this->assertSame( $item['exists'], true );
			$this->assertStringEndsWith( 'id', $item['id'] ); // theid, anotherid
			$this->assertSame( $item['namespace'], 5 );
			$this->assertSame( $item['class'], 'WikiMessageGroup' );
		}
	}

	function testBadProperty() {
		// The following request has no tigroups parameter, so
		// it should raise a warning
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
		$this->assertArrayHasKey( '*', $data['warnings']['query'] );
	}

	function provideMessageGroups (){
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
