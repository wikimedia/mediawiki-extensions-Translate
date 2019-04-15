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
	public function validate( $messages, $code, array &$notices ) {
		foreach ( $messages as $message ) {
			// returning a dummy validation error
			$key = $message->key();
			$notices[$key][] = [
				[ 'plural', 'submissing', $key, $code ],
				'translate-checks-plural',
			];
		}
	}
}

class AnotherMockTranslateValidator implements Validator {
	public function validate( $messages, $code, array &$notices ) {
		foreach ( $messages as $message ) {
			// returning a dummy validation error
			$key = $message->key();
			$notices[$key][] = [
				[ 'plural', 'dupe', $key, $code ],
				'translate-checks-plural-dupe'
			];
		}
	}
}
