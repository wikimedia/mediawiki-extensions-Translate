<?php

use MediaWiki\Extension\Translate\Validation\ValidationRunner;

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

	public function getMessage( $key, $code ) {
		if ( $code === $this->getSourceLanguage() ) {
			return $this->messages[strtolower( $key )] ?? null;
		}
		parent::getMessage( $key, $code );
	}
}

/**
 * Has validators that always return a validation error and warning.
 */
class MockWikiValidationMessageGroup extends MockWikiMessageGroup {
	public function getValidator() {
		$validator = new ValidationRunner( $this->getId() );
		$validator->setValidators( [
			[ 'class' => AnotherMockTranslateValidator::class ],
			[
				'class' => MockTranslateValidator::class,
				'enforce' => true,
				'include' => [
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
				],
				'exclude' => [
					'key-excluded',
					[
						'type' => 'regex',
						'pattern' => '/regex-exclude/'
					],
					[
						'type' => 'wildcard',
						'pattern' => '*wildcard-exclude*'
					]
				]

			],
		] );

		return $validator;
	}
}
