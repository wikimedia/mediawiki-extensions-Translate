<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Tests\Api\ApiTestCase;
use MessageGroupTestTrait;
use MockWikiMessageGroup;

/**
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @group medium
 * @group Database
 * @covers \MediaWiki\Extension\Translate\MessageLoading\QueryMessageCollectionActionApi
 */
class QueryMessageCollectionActionApiTest extends ApiTestCase {
	use MessageGroupTestTrait;

	protected function setUp(): void {
		parent::setUp();
		$this->setupGroupTestEnvironmentWithGroups( $this, $this->getTestGroups() );
	}

	public function getTestGroups(): array {
		$messages = [
			'msg_1' => 'definition one',
			'msg_2' => 'definition two',
			'msg_3' => 'definition three',
			'msg_4' => 'definition four',
			'msg_5' => 'definition five',
			'msg_6' => 'definition six',
		];
		$list['theid'] = new MockWikiMessageGroup( 'theid', $messages );

		return $list;
	}

	public function testSameAsSourceLanguage(): void {
		$group = MessageGroups::getGroup( 'theid' );
		[ $response ] = $this->doApiRequest(
			[
				'mcgroup' => $group->getId(),
				'action' => 'query',
				'list' => 'messagecollection',
				'mcprop' => 'definition|translation|tags|properties',
				// @see https://gerrit.wikimedia.org/r/#/c/160222/
				'continue' => '',
				'errorformat' => 'html',
				'mclanguage' => $group->getSourceLanguage()
			]
		);

		$this->assertArrayHasKey( 'warnings', $response,
			'warning triggered when target language same as source language.' );
		$this->assertArrayNotHasKey( 'errors', $response,
			'no error triggered when target language same as source language.' );
	}

	public function testFilterWithPagination(): void {
		$user = $this->getTestSysop()->getUser();

		// Translate 4 of 6 messages
		foreach ( [ 'Msg_1', 'Msg_2', 'Msg_3', 'Msg_4' ] as $key ) {
			$this->editPage( "MediaWiki:$key/fi", "translation_$key", '', NS_MAIN, $user );
		}

		// First page: filter=translated, limit=2
		[ $response ] = $this->doApiRequest(
			[
				'action' => 'query',
				'list' => 'messagecollection',
				'mcgroup' => 'theid',
				'mclanguage' => 'fi',
				'mcfilter' => 'translated',
				'mclimit' => '2',
				'mcprop' => 'translation',
				'continue' => '',
			]
		);

		$messages = $response['query']['messagecollection'];
		$this->assertCount( 2, $messages );
		foreach ( $messages as $msg ) {
			$this->assertArrayHasKey( 'translation', $msg );
		}

		$metadata = $response['query']['metadata'];
		$this->assertSame( 4, $metadata['resultsize'] );
		$this->assertSame( 2, $metadata['remaining'] );
		$this->assertArrayHasKey( 'continue', $response );

		$firstBatchKeys = array_column( $messages, 'title' );

		// Second page using continuation
		[ $response2 ] = $this->doApiRequest(
			[
				'action' => 'query',
				'list' => 'messagecollection',
				'mcgroup' => 'theid',
				'mclanguage' => 'fi',
				'mcfilter' => 'translated',
				'mclimit' => '2',
				'mcprop' => 'translation',
				'continue' => '',
				'mcoffset' => $response['continue']['mcoffset'],
			]
		);

		$messages2 = $response2['query']['messagecollection'];
		$this->assertCount( 2, $messages2 );

		$secondBatchKeys = array_column( $messages2, 'title' );
		$this->assertSame(
			[],
			array_intersect( $firstBatchKeys, $secondBatchKeys ),
			'No overlap between first and second page'
		);
	}
}
