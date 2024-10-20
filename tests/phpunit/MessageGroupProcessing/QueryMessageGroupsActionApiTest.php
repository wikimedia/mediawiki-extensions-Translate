<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Tests\Api\ApiTestCase;
use MessageGroupTestTrait;
use WikiMessageGroup;

/**
 * @author Harry Burt
 * @copyright Copyright © 2012-2013, Harry Burt
 * @license GPL-2.0-or-later
 * @group medium
 * @group Database
 * @covers \MediaWiki\Extension\Translate\MessageGroupProcessing\QueryMessageGroupsActionApi
 */
class QueryMessageGroupsActionApiTest extends ApiTestCase {
	use MessageGroupTestTrait;

	protected function setUp(): void {
		parent::setUp();
		$this->setupGroupTestEnvironmentWithGroups( $this, $this->getTestGroups() );
	}

	public function getTestGroups(): array {
		$exampleMessageGroup = new WikiMessageGroup( 'theid', 'thesource' );
		$exampleMessageGroup->setLabel( 'thelabel' ); // Example
		$exampleMessageGroup->setNamespace( 5 ); // Example
		$list['theid'] = $exampleMessageGroup;

		$anotherExampleMessageGroup = new WikiMessageGroup( 'anotherid', 'thesource' );
		$anotherExampleMessageGroup->setLabel( 'thelabel' ); // Example
		$anotherExampleMessageGroup->setNamespace( 5 ); // Example
		$list['anotherid'] = $anotherExampleMessageGroup;

		return $list;
	}

	public function testAPIAccuracy(): void {
		[ $data ] = $this->doApiRequest(
			[
				'action' => 'query',
				'meta' => 'messagegroups',
				'mgprop' => 'id|label|class|namespace|exists',
				// @see https://gerrit.wikimedia.org/r/#/c/160222/
				'continue' => ''
			]
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
		$this->assertSame( 'thelabel', $items[0]['label'] );
		$this->assertSame( 'thelabel', $items[1]['label'] );
		$this->assertTrue( $items[0]['exists'] );
		$this->assertTrue( $items[1]['exists'] );
		$this->assertSame( 5, $items[0]['namespace'] );
		$this->assertSame( 5, $items[1]['namespace'] );
		$this->assertSame( WikiMessageGroup::class, $items[0]['class'] );
		$this->assertSame( WikiMessageGroup::class, $items[1]['class'] );
	}

	public function testAPIFilterAccuracy(): void {
		$ids = [ 'MadeUpGroup' ];
		$ids += array_keys( MessageGroups::getAllGroups() );

		foreach ( $ids as $id ) {
			[ $data ] = $this->doApiRequest(
				[
					'action' => 'query',
					'meta' => 'messagegroups',
					'mgprop' => 'id|label|class|namespace|exists',
					'mgfilter' => $id,
					// @see https://gerrit.wikimedia.org/r/#/c/160222/
					'continue' => ''
				]
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

			$this->assertSame( $id, $item['id'] );
			$this->assertSame( 'thelabel', $item['label'] );
			$this->assertTrue( $item['exists'] );
			$this->assertStringEndsWith( 'id', $item['id'] ); // theid, anotherid
			$this->assertSame( 5, $item['namespace'] );
			$this->assertSame( WikiMessageGroup::class, $item['class'] );
		}
	}

	public function testBadProperty(): void {
		[ $data ] = $this->doApiRequest(
			[
				'action' => 'query',
				'meta' => 'messagegroups',
				'mgprop' => 'madeupproperty',
				// @see https://gerrit.wikimedia.org/r/#/c/160222/
				'continue' => ''
			]
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
