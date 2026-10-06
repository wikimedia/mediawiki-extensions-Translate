<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use MediaWiki\Content\TextContent;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;

/**
 * One plain-text file represents one message, independent of its filename.
 * @license GPL-2.0-or-later
 * @ingroup FileFormatSupport
 */
class PlainTextFormat extends SimpleFormat {
	private const MESSAGE_KEY = 'message';

	public function getFileExtensions(): array {
		return [ '.txt' ];
	}

	public function readFromVariable( string $data ): array {
		// Match MediaWiki's normalization, including trimming trailing whitespace.
		$data = TextContent::normalizeLineEndings( $data );

		return [
			'AUTHORS' => [],
			'MESSAGES' => $this->group->getMangler()->mangleArray( [ self::MESSAGE_KEY => $data ] ),
		];
	}

	protected function writeReal( MessageCollection $collection ): string {
		$key = $this->group->getMangler()->mangle( self::MESSAGE_KEY );
		$translation = ( $collection[$key] ?? null )?->translation();
		if ( $translation === null ) {
			return '';
		}

		$translation = str_replace( TRANSLATE_FUZZY, '', $translation );
		return TextContent::normalizeLineEndings( $translation ) . "\n";
	}
}
