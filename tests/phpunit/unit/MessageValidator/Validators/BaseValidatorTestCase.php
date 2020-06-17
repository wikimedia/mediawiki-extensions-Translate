<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

declare( strict_types = 1 );

use MediaWiki\Extensions\Translate\Validation\MessageValidator;
use MediaWiki\Extensions\Translate\Validation\ValidationIssue;
use MediaWiki\Extensions\Translate\Validation\ValidationIssues;

class BaseValidatorTestCase extends MediaWikiUnitTestCase {
	public function runValidatorTests(
		MessageValidator $validator,
		string $type,
		string $definition,
		string $translation,
		array $subTypes,
		string $msg
	) {
		$message = new FatMessage( 'key', $definition );
		$message->setTranslation( $translation );

		$actual = $validator->getIssues( $message, 'en-gb' );
		foreach ( $actual as $issue ) {
			/** @var ValidationIssue $issue */
			$this->assertSame( $type, $issue->type(), $msg );
		}
		$this->assertArrayEquals( $subTypes, self::getSubTypes( $actual ) );
	}

	/** @return string[] */
	private static function getSubTypes( ValidationIssues $issues ): array {
		return array_map( function ( ValidationIssue $x ) {
			return $x->subType();
		}, iterator_to_array( $issues ) );
	}
}
