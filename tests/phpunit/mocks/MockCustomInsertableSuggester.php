<?php
declare( strict_types = 1 );

use MediaWiki\Extensions\Translate\TranslatorInterface\Insertable\InsertablesSuggester;

class MockCustomInsertableSuggester implements InsertablesSuggester {
	public function getInsertables( string $text ): array {
		return [ new Insertable( 'test' ) ];
	}
}
