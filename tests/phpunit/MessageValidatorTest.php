<?php
/**
 * Tests for Message Validator and ValidatorResult.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

class MessageValidatorTest extends MediaWikiTestCase {

	/**
	 * @var MessageGroup
	 */
	protected function setUp() {
		parent::setUp();

		$this->setTemporaryHook(
			'TranslatePostInitGroups',
			[ $this, 'getTestGroups' ]
		);

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => wfGetCache( 'hash' ) ] ) );
		$mg->recache();

		MessageIndex::setInstance( new HashMessageIndex() );
		MessageIndex::singleton()->rebuild();
	}

	public function getTestGroups( &$list ) {
		$messages = [
			'translated' => 'bunny',
			'untranslated' => 'fanny',
		];

		$list['test-group'] = new MockWikiMessageGroup( 'test-group', $messages );
	}

	public function testValidateMessage() {
		$group = MessageGroups::getGroup( 'test-group' );
		$collection = $group->initCollection( 'en-gb' );
		$collection->loadTranslations();

		$msgValidator = $group->getValidator();
		$validationResult = $msgValidator->validateMessage( $collection[ 'translated' ],
			'en-gb' );

		$requestContext = new RequestContext();
		$requestContext->setLanguage( 'en' );

		$this->assertTrue( $validationResult->hasErrors(),
			'errors are correctly identified.' );
		$this->assertTrue( $validationResult->hasWarnings(),
			'warnings are correctly identified.' );

		$this->assertCount( 1, $validationResult->getWarnings(),
			'there is 1 warning returned as per the validator.' );
		$this->assertCount( 1, $validationResult->getErrors(),
			'there is 1 error returned as per the validator.' );

		$validationResult = $msgValidator->validateMessage( $collection[ 'translated' ],
			'en-gb', true );
		$this->assertTrue( $validationResult->hasErrors(),
			'errors are correctly identified if ignore warnings  is set.' );
		$this->assertFalse( $validationResult->hasWarnings(),
			'warnings are ignored if ignore warnings is set.' );
	}

	public function testQuickValidate() {
		$group = MessageGroups::getGroup( 'test-group' );
		$collection = $group->initCollection( 'en-gb' );
		$collection->loadTranslations();

		$msgValidator = $group->getValidator();
		$validationResult = $msgValidator->quickValidate( $collection[ 'translated' ], 'en-gb' );

		$this->assertTrue( $validationResult->hasErrors() || $validationResult->hasWarnings(),
			'either errors or warnings are set.' );
		$this->assertFalse( $validationResult->hasWarnings() && $validationResult->hasErrors(),
			'either error or warnings are set.' );

		$this->assertCount( 1,
			$validationResult->getWarnings() + $validationResult->getErrors(),
			'there is a single warning or error returned as per the validator.' );

			$validationResult = $msgValidator->quickValidate( $collection[ 'translated' ],
				'en-gb', true );

		$this->assertTrue( $validationResult->hasErrors(),
			'errors are identified if ignore warnings is set.' );
		$this->assertFalse( $validationResult->hasWarnings(),
			'warnings are not identified if ignore warnings is set.' );
	}

	public function testDescriptiveMessage() {
		$group = MessageGroups::getGroup( 'test-group' );
		$collection = $group->initCollection( 'en-gb' );
		$collection->loadTranslations();

		$msgValidator = $group->getValidator();
		$validationResult = $msgValidator->validateMessage( $collection[ 'translated' ],
			'en-gb' );

		$requestContext = new RequestContext();
		$requestContext->setLanguage( 'en' );

		$this->assertCount( count( $validationResult->getErrors() ),
			$validationResult->getDescriptiveErrors( $requestContext ),
			'the number of descriptive errors messages matches the number of errors.'
		);
		$this->assertCount( count( $validationResult->getWarnings() ),
			$validationResult->getDescriptiveWarnings( $requestContext ),
			'the number of descriptive warnings messages matches the number of warnings.'
		);

		$this->assertInternalType( 'string',
			$validationResult->getDescriptiveWarnings( $requestContext )[0],
			'warning messages are of type string.'
		);
		$this->assertInternalType( 'string',
			$validationResult->getDescriptiveErrors( $requestContext )[0],
			'error messages are of type string'
		);
	}
}
