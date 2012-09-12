<?php
/**
 * Unit tests.
 *
 * @file
 * @author Harry Burt
 * @copyright Copyright © 2012, Harry Burt
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
 
class ApiTranslateInfoTest extends ApiTestCase {

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

		$ids = 'MadeUpGroup';

		foreach( $mgs as $mg ) {
			$id = $mg->getId();
			$ids .= '|' . $id;

			$wgTranslateCC[$id] = $mg;
			MessageGroups::clearCache();
			MessageIndexRebuildJob::newJob()->insert();
			$this->assertNotNull( MessageGroups::getGroup( $id ) );
		}

		list( $data ) = $this->doApiRequest(
			array(
				'action' => 'query',
				'prop' => 'translateinfo',
				'tiprop' => 'label|class|namespace|exists',
				'tigroups' => $ids
			)
		);

		// Check structure
		$this->assertCount( 1, $data );
		$this->assertArrayHasKey( 'query', $data );
		$this->assertCount( 1, $data['query'] );
		$this->assertArrayHasKey( 'groups', $data['query'] );
		$this->assertCount( count( $mgs ), $data['query']['groups'] );

		// Check content
		$i = 0;
		foreach( $data['query']['groups'] as $item ){
			$this->assertCount( 5, $item );

			$this->assertSame( $item['label'], 'thelabel' );
			$this->assertSame( $item['exists'], true );
			$this->assertStringEndsWith( 'id', $item['id'] ); // theid, anotherid
			$this->assertSame( $item['namespace'], 5 );
			$this->assertSame( $item['class'], get_class( $mgs[$i] ) );
			$i++;
		}
	}

	/** @expectedException UsageException
	*/
	function testMissingGroupsParameter() {
		// The following request has no tigroups parameter, so
		// it should raise a UsageError.
		list( $data ) = $this->doApiRequest(
			array(
				'action' => 'query',
				'prop' => 'translateinfo',
				'tiprop' => 'label|class|namespace|exists'
			)
		);
	}

	function testBadProperty() {
		// The following request has no tigroups parameter, so
		// it should raise a warning
		list( $data ) = $this->doApiRequest(
			array(
				'action' => 'query',
				'prop' => 'translateinfo',
				'tiprop' => 'madeupproperty',
				'tigroups' => 'MadeUpGroup'
			)
		);

		$this->assertCount( 2, $data );

		$this->assertArrayHasKey( 'query', $data );
		$this->assertCount( 1, $data['query'] );
		$this->assertArrayHasKey( 'groups', $data['query'] );
		$this->assertCount( 0, $data['query']['groups'] );

		$this->assertArrayHasKey( 'warnings', $data );
		$this->assertCount( 1, $data['warnings'] );
		$this->assertArrayHasKey( 'translateinfo', $data['warnings'] );
		$this->assertCount( 1, $data['warnings']['translateinfo'] );
		$this->assertArrayHasKey( '*', $data['warnings']['translateinfo'] );
	}

	function provideMessageGroups (){
		$exampleMessageGroup = new WikiMessageGroup( 'theid', 'thesource' );
		$exampleMessageGroup->setLabel( 'thelabel' ); // Example
		$exampleMessageGroup->setNamespace( 5 ); // Example

		$anotherExampleMessageGroup = new WikiMessageGroup( 'anotherid', 'thesource' );
		$anotherExampleMessageGroup->setLabel( 'thelabel' ); // Example
		$anotherExampleMessageGroup->setNamespace( 5 ); // Example

		return array(
			array( array( $exampleMessageGroup ) ),
			array( array( $anotherExampleMessageGroup ) ),
			array( array( $exampleMessageGroup, $anotherExampleMessageGroup ) )
		);
	}
}
