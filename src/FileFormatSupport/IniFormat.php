<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use MediaWiki\Extension\Translate\MessageLoading\Message;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;

/**
 * IniFormat currently parses and generates flat ini files with language
 * code as header key.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup FileFormatSupport
 */
class IniFormat extends SimpleFormat {
	public function supportsFuzzy(): string {
		return 'write';
	}

	public function getFileExtensions(): array {
		return [ '.ini' ];
	}

	public function readFromVariable( string $data ): array {
		$authors = [];
		preg_match_all( '/^; Author: (.*)$/m', $data, $matches, PREG_SET_ORDER );
		foreach ( $matches as $match ) {
			$authors[] = $match[1];
		}

		// Remove comments
		$data = preg_replace( '/^\s*;.*$/m', '', $data );
		// Make sure values are quoted, PHP barks on stuff like ?{}|&~![()^
		$data = preg_replace( '/(^.+?=\s*)([^\'"].+)$/m', '\1"\2"', $data );

		$messages = parse_ini_string( $data );
		if ( is_array( $messages ) ) {
			$messages = $this->group->getMangler()->mangleArray( $messages );
		} else {
			$messages = null;
		}

		return [
			'MESSAGES' => $messages,
			'AUTHORS' => $authors,
		];
	}

	protected function writeReal( MessageCollection $collection ): string {
		$output = '';
		$mangler = $this->group->getMangler();

		/** @var Message $m */
		foreach ( $collection as $key => $m ) {
			$value = $m->translation();
			if ( $value === null ) {
				continue;
			}

			$comment = '';

			if ( $m->hasTag( 'fuzzy' ) ) {
				$value = str_replace( TRANSLATE_FUZZY, '', $value );
				$comment = "; Fuzzy\n";
			}

			$key = $mangler->unmangle( $key );
			$output .= "$comment$key = $value\n";
		}

		// Do not create empty files
		if ( $output === '' ) {
			return '';
		}

		global $wgSitename;
		// Accumulator
		$header = "; Exported from $wgSitename\n";

		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $collection->getLanguage() );
		foreach ( $authors as $author ) {
			$header .= "; Author: $author\n";
		}

		$header .= '[' . $collection->getLanguage() . "]\n";

		return $header . $output;
	}
}

class_alias( IniFormat::class, 'IniFFS' );
