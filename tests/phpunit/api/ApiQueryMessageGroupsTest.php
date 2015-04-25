<?php
/**
 * Unit tests for api module.
 *
 * @file
 * @author Harry Burt
 * @copyright Copyright © 2012-2013, Harry Burt
 * @license GPL-2.0+
 */

/**
 * @group medium
 */
class ApiQueryMessageGroupsTest extends ApiTestCase {

	protected function setUp() {
		parent::setUp();

		global $wgHooks;
		$this->setMwGlobals( array(
			'wgHooks' => $wgHooks,
			'wgTranslateTranslationServices' => array(),
		) );
		$wgHooks['TranslatePostInitGroups'] = array( array( $this, 'getTestGroups' ) );

		$mg = MessageGroups::singleton();
		$mg->setCache( wfGetCache( 'hash' ) );
		$mg->recache();
	}

	public function getTestGroups( &$list ) {
		$exampleMessageGroup = new WikiMessageGroup( 'theid', 'thesource' );
		$exampleMessageGroup->setLabel( 'thelabel' ); // Example
		$exampleMessageGroup->setNamespace( 5 ); // Example
		$list['theid'] = $exampleMessageGroup;

		$anotherExampleMessageGroup = new WikiMessageGroup( 'anotherid', 'thesource' );
		$anotherExampleMessageGroup->setLabel( 'thelabel' ); // Example
		$anotherExampleMessageGroup->setNamespace( 5 ); // Example
		$list['anotherid'] = $anotherExampleMessageGroup;

		return false;
	}

	public function testAPIAccuracy() {
		list( $data ) = $this->doApiRequest(
			array(
				'action' => 'query',
				'meta' => 'messagegroups',
				'mgprop' => 'id|label|class|namespace|exists',
				// @see https://gerrit.wikimedia.org/r/#/c/160222/
				'continue' => ''
			)
		);

		// Check structure
		$this->assertArrayNotHasKey( 'warnings', $data );
		$this->assertArrayHasKey( 'query', $data );
		$this->assertCount( 1, $data['query'] );
		$this->assertArrayHasKey( 'messagegroups', $data['query'] );

		// Basic content checks
		$items = $data['query']['messagegroups'];

		// Ignore dynamic groups
		foreach ( $items as $index => $group ) {
			if ( $group['id'][0] === '!' ) {
				unset( $items[$index] );
			}
		}

		// Renumber keys
		$items = array_values( $items );

		$this->assertCount( 2, $items, 'Only the two groups specified are in the api' );
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

	public function testAPIFilterAccuracy() {
		$ids = array( 'MadeUpGroup' );
		$ids += array_keys( MessageGroups::getAllGroups() );

		foreach ( $ids as $id ) {
			list( $data ) = $this->doApiRequest(
				array(
					'action' => 'query',
					'meta' => 'messagegroups',
					'mgprop' => 'id|label|class|namespace|exists',
					'mgfilter' => $id,
					// @see https://gerrit.wikimedia.org/r/#/c/160222/
					'continue' => ''
				)
			);

			if ( $id === 'MadeUpGroup' ) {
				// Check structure (shouldn't find anything)
				$this->assertArrayNotHasKey( 'warnings', $data );
				$this->assertArrayHasKey( 'query', $data );
				$this->assertCount( 1, $data['query'] );
				$this->assertArrayHasKey( 'messagegroups', $data['query'] );
				$this->assertCount( 0, $data['query']['messagegroups'] );
				continue;
			}

			// Check structure (filter is unique given these names)
			$this->assertArrayNotHasKey( 'warnings', $data );
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
				'mgprop' => 'madeupproperty',
				// @see https://gerrit.wikimedia.org/r/#/c/160222/
				'continue' => ''
			)
		);

		$this->assertArrayHasKey( 'query', $data );
		$this->assertCount( 1, $data['query'] );
		$this->assertArrayHasKey( 'messagegroups', $data['query'] );
		// This doesn't work. invalid properties are only warnings,
		// so we ged empty groups listed
		// $this->assertCount( 0, $data['query']['messagegroups'] );

		$this->assertArrayHasKey( 'warnings', $data );
		$this->assertCount( 1, $data['warnings'] );
		$this->assertArrayHasKey( 'messagegroups', $data['warnings'] );
		$this->assertCount( 1, $data['warnings']['messagegroups'] );
		$this->assertArrayHasKey( 'warnings', $data['warnings']['messagegroups'] );
	}
}
