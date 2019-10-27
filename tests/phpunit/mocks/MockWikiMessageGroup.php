<?php
/**
 * This file contains multiple unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0-or-later
 */

class MockWikiMessageGroup extends WikiMessageGroup {
	public function __construct( $id, array $messages ) {
		parent::__construct( $id, 'unused' );
		$this->id = $id;
		$this->messages = $messages;
	}

	public function getDefinitions() {
		return $this->messages;
	}
}

/**
 * Has validators that always return a valiation error and warning.
 */
class MockWikiValidationMessageGroup extends MockWikiMessageGroup {
	public function getValidator() {
		$validator = new MessageValidator( $this->getId() );
		$validator->setValidators( [
			[ 'class' => AnotherMockTranslateValidator::class ],
			[
				'class' => MockTranslateValidator::class,
				'enforce' => true,
				'keymatch' => [
					'translated',
					'untranslated',
					[
						'type' => 'regex',
						'pattern' => '/regex-key/'
					],
					[
						'type' => 'wildcard',
						'pattern' => '*translated*'
					]
				]
			],
		] );

		return $validator;
	}
}
