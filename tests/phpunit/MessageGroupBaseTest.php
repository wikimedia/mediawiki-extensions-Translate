<?php

use MediaWiki\Extensions\Translate\MessageValidator\Validator;

class MessageGroupBaseTest extends MediaWikiTestCase {

	/**
	 * @var MessageGroup
	 */
	protected $group;

	protected $groupConfiguration = [
		'BASIC' => [
			'class' => 'FileBasedMessageGroup',
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		],
	];

	protected function setUp() {
		parent::setUp();
		$this->group = MessageGroupBase::factory( $this->groupConfiguration );
	}

	protected function tearDown() {
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

	public function testInsertablesSuggesterClass() {
		$conf = $this->groupConfiguration;
		$conf['INSERTABLES']['class'] = 'FakeInsertablesSuggester';
		$this->group = MessageGroupBase::factory( $conf );

		$this->assertArrayEquals(
			[ new Insertable( 'Fake', 'Insertables', 'Suggester' ) ],
			$this->group->getInsertablesSuggester()->getInsertables( '' )
		);
	}

	public function testInsertablesSuggesterClasses() {
		$conf = $this->groupConfiguration;
		$conf['INSERTABLES']['classes'] = [
			'FakeInsertablesSuggester',
			'AnotherFakeInsertablesSuggester',
		];
		$this->group = MessageGroupBase::factory( $conf );

		$this->assertArrayEquals(
			[
				new Insertable( 'Fake', 'Insertables', 'Suggester' ),
				new Insertable( 'AnotherFake', 'Insertables', 'Suggester' ),
			],
			$this->group->getInsertablesSuggester()->getInsertables( '' )
		);
	}

	public function testInsertablesSuggesterClassAndClasses() {
		$conf = $this->groupConfiguration;
		$conf['INSERTABLES']['class'] = 'FakeInsertablesSuggester';
		$conf['INSERTABLES']['classes'] = [ 'AnotherFakeInsertablesSuggester' ];
		$this->group = MessageGroupBase::factory( $conf );

		$this->assertArrayEquals(
			[
				new Insertable( 'Fake', 'Insertables', 'Suggester' ),
				new Insertable( 'AnotherFake', 'Insertables', 'Suggester' ),
			],
			$this->group->getInsertablesSuggester()->getInsertables( '' )
		);

		$conf['INSERTABLES']['classes'][] = 'FakeInsertablesSuggester';
		$conf['INSERTABLES']['classes'][] = 'AnotherFakeInsertablesSuggester';
		$this->group = MessageGroupBase::factory( $conf );

		$this->assertArrayEquals(
			[
				new Insertable( 'Fake', 'Insertables', 'Suggester' ),
				new Insertable( 'AnotherFake', 'Insertables', 'Suggester' ),
			],
			$this->group->getInsertablesSuggester()->getInsertables( '' ),
			false,
			false,
			'should correctly get InsertablesSuggesters using ' .
			'both \'class\' and \'classes\' options and removing duplicates.'
		);
	}

	public function testGetNamespaceInvalid() {
		$conf = $this->groupConfiguration;
		$conf['BASIC']['namespace'] = 'ergweofijwef';
		$this->setExpectedException( MWException::class, 'No valid namespace defined' );
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
			function ( $groupId, &$conf ) {
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

		unset( $conf['INSERTABLES']['class'] );
		$conf['INSERTABLES']['classes'] = [ 'AnotherFakeInsertablesSuggester' ];
		$conf['VALIDATORS'] = [];
		$conf['VALIDATORS'][] = [
			'class' => 'FakeInsertableValidator',
			'insertable' => true,
			'params' => 'TEST'
		];

		$conf['VALIDATORS'][] = [
			'class' => 'AnotherFakeInsertableValidator',
			'insertable' => false,
			'params' => 'TEST2'
		];

		$this->group = MessageGroupBase::factory( $conf );
		$messageValidators = $this->group->getValidator();
		$insertables = $this->group->getInsertablesSuggester()->getInsertables( '' );

		$this->assertInstanceOf( MessageValidator::class, $messageValidators,
			'should correctly fetch a \'MessageValidator\' using the \'VALIDATOR\' configuration.'
		);

		$this->assertCount( 2, $insertables,
			'should not add non-insertable validator when \'insertable\' is false.'
		);

		$this->assertEquals(
			new Insertable( 'Fake', 'Insertable', 'Validator' ),
			$insertables[1],
			'should correctly fetch an \'InsertableValidator\' when \'insertable\' is true.'
		);
	}

	public function testInsertableArrayConfiguration() {
		$conf = $this->groupConfiguration;
		unset( $conf['INSERTABLES']['class'] );
		unset( $conf['INSERTABLES']['classes'] );

		$conf['INSERTABLES'] = [
			[
				'class' => 'FakeInsertableValidator',
				'params' => 'Regex'
			],
			[
				'class' => 'AnotherFakeInsertableValidator',
				'params' => 'Regex'
			]
		];

		$this->group = MessageGroupBase::factory( $conf );
		$insertables = $this->group->getInsertablesSuggester()->getInsertables( '' );

		$this->assertCount( 2, $insertables,
			'should fetch the correct count of \'Insertables\' when \'InsertablesSuggesters\' ' .
			'are configured using the array configuration.'
		);

		$this->assertEquals(
			new Insertable( 'Another', 'Fake Insertable', 'Validator' ),
			$insertables[1],
			'should fetch the correct \'Insertables\' when \'InsertablesSuggesters\' ' .
			'are configured using the array configuration.'
		);
	}
}

class FakeInsertablesSuggester implements InsertablesSuggester {
	public function getInsertables( $text ) {
		return [ new Insertable( 'Fake', 'Insertables', 'Suggester' ) ];
	}
}

class AnotherFakeInsertablesSuggester implements InsertablesSuggester {
	public function getInsertables( $text ) {
		return [ new Insertable( 'AnotherFake', 'Insertables', 'Suggester' ) ];
	}
}

class FakeInsertableValidator implements Validator, InsertablesSuggester {
	public function validate( $messages, $code, array &$notices ) {
	}

	public function getInsertables( $text ) {
		return [ new Insertable( 'Fake', 'Insertable', 'Validator' ) ];
	}
}

class AnotherFakeInsertableValidator implements Validator, InsertablesSuggester {
	public function validate( $messages, $code, array &$notices ) {
	}

	public function getInsertables( $text ) {
		return [ new Insertable( 'Another', 'Fake Insertable', 'Validator' ) ];
	}
}
