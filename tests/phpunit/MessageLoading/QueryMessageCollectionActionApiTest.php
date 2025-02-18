<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Tests\Api\ApiTestCase;
use MessageGroupTestTrait;
use WikiMessageGroup;

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
		$exampleMessageGroup = new WikiMessageGroup( 'theid', 'thesource' );
		$exampleMessageGroup->setLabel( 'thelabel' ); // Example
		$exampleMessageGroup->setNamespace( 5 ); // Example
		$list['theid'] = $exampleMessageGroup;

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
}
