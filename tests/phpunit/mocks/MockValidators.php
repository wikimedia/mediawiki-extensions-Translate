<?php
/**
 * Contains mock validators used for testing purpose.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */
use MediaWiki\Extensions\Translate\MessageValidator\Validator;

class MockTranslateValidator implements Validator {
	public function validate( TMessage $message, $code, array &$notices ) {
		// returning a dummy validation error
		$key = $message->key();
		$notices[$key][] = [
			[ 'plural', 'submissing', $key, $code ],
			'translate-checks-plural',
		];

		$notices[$key][] = [
			[ 'pagename', 'namespace', $key, $code ],
			'translate-checks-pagename',
		];
	}
}

class AnotherMockTranslateValidator implements Validator {
	public function validate( TMessage $message, $code, array &$notices ) {
		// returning a dummy validation error
		$key = $message->key();
		$notices[$key][] = [
			[ 'plural', 'dupe', $key, $code ],
			'translate-checks-plural-dupe'
		];
	}
}
