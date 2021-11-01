<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\TranslatorInterface\Insertable\Insertable;
use MediaWiki\Extension\Translate\TranslatorInterface\Insertable\InsertablesSuggester;
use MediaWiki\Extension\Translate\Validation\MessageValidator;
use MediaWiki\Extension\Translate\Validation\ValidationIssues;
use MediaWiki\Extension\Translate\Validation\ValidationRunner;

/**
 * @license GPL-2.0-or-later
 * @covers MessageGroupBase
 */
class MessageGroupBaseTest extends MediaWikiIntegrationTestCase {
	/** @var MessageGroup */
	protected $group;
	protected $groupConfiguration = [
		'BASIC' => [
			'class' => FileBasedMessageGroup::class,
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		],
	];

	protected function setUp(): void {
		parent::setUp();
		$this->group = MessageGroupBase::factory( $this->groupConfiguration );
	}

	protected function tearDown(): void {
		unset( $this->group );
		parent::tearDown();
	}

	public function testGetConfiguration() {
		$this->assertEquals(
			$this->groupConfiguration,
			$this->group->getConfiguration(),
			'configuration should not change.'
		);
	}

	public function testGetId() {
		$this->assertEquals(
			$this->groupConfiguration['BASIC']['id'],
			$this->group->getId(),
			'id comes from config.'
		);
	}

	public function testGetSourceLanguage() {
		$this->assertEquals(
			'en',
			$this->group->getSourceLanguage(),
			'source language defaults to en.'
		);
	}

	public function testGetNamespaceConstant() {
		$this->assertEquals(
			NS_MEDIAWIKI,
			$this->group->getNamespace(),
			'should parse string namespace contant.'
		);
	}

	public function testGetNamespaceNumber() {
		$conf = $this->groupConfiguration;
		$conf['BASIC']['namespace'] = NS_FILE;
		$this->group = MessageGroupBase::factory( $conf );

		$this->assertEquals(
			NS_FILE,
			$this->group->getNamespace(),
			'should parse integer namespace number.'
		);
	}

	public function testGetNamespaceString() {
		$conf = $this->groupConfiguration;
		$conf['BASIC']['namespace'] = 'image';
		$this->group = MessageGroupBase::factory( $conf );

		$this->assertEquals(
			NS_FILE,
			$this->group->getNamespace(),
			'should parse string namespace name.'
		);
	}

	public function testGetNamespaceInvalid() {
		$conf = $this->groupConfiguration;
		$conf['BASIC']['namespace'] = 'ergweofijwef';
		$this->expectException( MWException::class );
		$this->expectExceptionMessage( 'No valid namespace defined' );
		MessageGroupBase::factory( $conf );
	}

	public function testModifyMessageGroupStates() {
		// Create a basic workflow.
		$this->setMwGlobals( [
			'wgTranslateWorkflowStates' => [
				'progress' => [ 'color' => 'd33' ],
				'proofreading' => [ 'color' => 'fc3' ],
			],
		] );
		// Install a special permission when the group ID is matched.
		$this->setTemporaryHook(
			'Translate:modifyMessageGroupStates',
			static function ( $groupId, &$conf ) {
				if ( $groupId === 'test-id' ) {
					// No users have this.
					$conf['proofreading']['right'] = 'inobtanium';
				}
			}
		);

		$expectedStates = [
			'progress' => [ 'color' => 'd33' ],
			'proofreading' => [ 'color' => 'fc3', 'right' => 'inobtanium' ],
		];
		$states = $this->group->getMessageGroupStates()->getStates();
		$this->assertEquals( $expectedStates, $states );
	}

	public function testInsertableValidatorConfiguration() {
		$conf = $this->groupConfiguration;

		$conf['INSERTABLES'] = [
			[ 'class' => AnotherFakeInsertablesSuggester::class ]
		];
		$conf['VALIDATORS'] = [];
		$conf['VALIDATORS'][] = [
			'class' => FakeInsertableValidator::class,
			'insertable' => true,
			'params' => 'TEST'
		];

		$conf['VALIDATORS'][] = [
			'class' => AnotherFakeInsertableValidator::class,
			'insertable' => false,
			'params' => 'TEST2'
		];

		$this->group = MessageGroupBase::factory( $conf );
		$messageValidators = $this->group->getValidator();
		$insertables = $this->group->getInsertablesSuggester()->getInsertables( '' );

		$this->assertInstanceOf( ValidationRunner::class, $messageValidators,
			"should correctly fetch a 'ValidationRunner' using the 'VALIDATOR' configuration."
		);

		// Returns insertables from,
		// 1. INSERTABLES > AnotherFakeInsertablesSuggester
		// 2. VALIDATORS > FakeInsertableValidator ( insertable => true )
		// Does not return VALIDATORS > AnotherFakeInsertableValidator ( insertable => false )
		$this->assertCount( 2, $insertables,
			"should not add non-insertable validator when 'insertable' is false."
		);

		$this->assertEquals(
			new Insertable( 'Fake', 'Insertable', 'Validator' ),
			$insertables[1],
			"should correctly fetch an 'InsertableValidator' when 'insertable' is true."
		);
	}

	public function testInsertableArrayConfiguration() {
		$conf = $this->groupConfiguration;

		$conf['INSERTABLES'] = [
			[
				'class' => FakeInsertableValidator::class,
				'params' => 'Regex'
			],
			[
				'class' => AnotherFakeInsertableValidator::class,
				'params' => 'Regex'
			]
		];

		$this->group = MessageGroupBase::factory( $conf );
		$insertables = $this->group->getInsertablesSuggester()->getInsertables( '' );

		$this->assertCount( 2, $insertables,
			"should fetch the correct count of 'Insertables' when 'InsertablesSuggesters' " .
			"are configured using the array configuration."
		);

		$this->assertEquals(
			new Insertable( 'Another', 'Fake Insertable', 'Validator' ),
			$insertables[1],
			"should fetch the correct 'Insertables' when 'InsertablesSuggesters' " .
			"are configured using the array configuration."
		);
	}

	public function testGetManglers() {
		$conf = $this->groupConfiguration;
		$conf['MANGLER'] = [
			'class' => 'StringMatcher',
			'prefix' => 'msg-prefix-',
			'patterns' => [ '*' ]
		];
		$this->group = MessageGroupBase::factory( $conf );

		$manglers = $this->group->getMangler();
		$this->assertNotNull( $manglers );

		$key = $manglers->mangle( 'key' );
		$this->assertEquals( 'msg-prefix-key', $key, 'message should be mangled as per configuration' );
	}
}

class FakeInsertablesSuggester implements InsertablesSuggester {
	public function getInsertables( string $text ): array {
		return [ new Insertable( 'Fake', 'Insertables', 'Suggester' ) ];
	}
}

class AnotherFakeInsertablesSuggester implements InsertablesSuggester {
	public function getInsertables( string $text ): array {
		return [ new Insertable( 'AnotherFake', 'Insertables', 'Suggester' ) ];
	}
}

class FakeInsertableValidator implements MessageValidator, InsertablesSuggester {
	public function getIssues( TMessage $message, string $targetLanguage ): ValidationIssues {
		return new ValidationIssues();
	}

	public function getInsertables( string $text ): array {
		return [ new Insertable( 'Fake', 'Insertable', 'Validator' ) ];
	}
}

class AnotherFakeInsertableValidator implements MessageValidator, InsertablesSuggester {
	public function getIssues( TMessage $message, string $targetLanguage ): ValidationIssues {
		return new ValidationIssues();
	}

	public function getInsertables( string $text ): array {
		return [ new Insertable( 'Another', 'Fake Insertable', 'Validator' ) ];
	}
}
