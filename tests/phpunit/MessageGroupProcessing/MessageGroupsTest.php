<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWikiIntegrationTestCase;
use MessageGroupTestConfig;
use MessageGroupTestTrait;

/**
 * @author Niklas Laxström
 * @group Database
 * ^ See AggregateMessageGroup::getGroups -> MessageGroups::getPriority
 * @covers \MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups
 * @license GPL-2.0-or-later
 */
class MessageGroupsTest extends MediaWikiIntegrationTestCase {
	use MessageGroupTestTrait;

	protected function setUp(): void {
		parent::setUp();

		$config = new MessageGroupTestConfig();
		$config->translateGroupFiles = [
			__DIR__ . '../../data/ParentGroups.yaml',
			__DIR__ . '../../data/ValidatorGroup.yaml',
		];
		$this->setupGroupTestEnvironmentWithConfig( $this, $config );

		MessageGroups::singleton()->recache();
	}

	/** @dataProvider provideGroups */
	public function testGetParentGroups( $expected, $target ) {
		$group = MessageGroups::getGroup( $target );
		$got = MessageGroups::getParentGroups( $group );
		$this->assertEquals( $expected, $got );
	}

	public static function provideGroups(): array {
		$cases = [];
		$cases[] = [
			[ [ 'root1' ], [ 'root2' ] ],
			'twoparents'
		];

		$cases[] = [
			[ [ 'root3', 'sub1' ], [ 'root3', 'sub2' ] ],
			'oneparent-twopaths'
		];

		$cases[] = [
			[
				[ 'root4' ],
				[ 'root4', 'nested1' ],
				[ 'root4', 'nested1', 'nested2' ],
				[ 'root4', 'nested2' ],
			],
			'multilevelnested'
		];

		return $cases;
	}

	public function testHaveSingleSourceLanguage(): void {
		$config = new MessageGroupTestConfig();
		$config->skipMessageIndexRebuild = true;
		$config->translateGroupFiles = [
			__DIR__ . '../../data/MixedSourceLanguageGroups.yaml',
		];
		$this->setupGroupTestEnvironmentWithConfig( $this, $config );

		MessageGroups::singleton()->clearProcessCache();
		MessageGroups::singleton()->recache();

		$enGroup1 = MessageGroups::getGroup( 'EnglishGroup1' );
		$enGroup2 = MessageGroups::getGroup( 'EnglishGroup2' );
		$teGroup1 = MessageGroups::getGroup( 'TeluguGroup1' );

		$this->assertEquals( 'en', MessageGroups::haveSingleSourceLanguage(
			[ $enGroup1, $enGroup2 ] )
		);
		$this->assertSame( '', MessageGroups::haveSingleSourceLanguage(
			[ $enGroup1, $enGroup2, $teGroup1 ] )
		);
	}

	public function testGroupYAMLParsing(): void {
		$group = MessageGroups::getGroup( 'test-validator-group' );
		$msgValidator = $group->getValidator();
		$suggester = $group->getInsertablesSuggester();

		$this->assertCount( 1, $msgValidator->getValidators() );
		$this->assertCount( 2, $suggester->getInsertables( "$1 \case" ) );
	}
}
