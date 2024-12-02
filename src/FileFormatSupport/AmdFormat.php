<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use MediaWiki\Extension\Translate\MessageLoading\Message;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Json\FormatJson;

/**
 * Support for the AMD i18n message file format (used by require.js and Dojo). See:
 * http://requirejs.org/docs/api.html#i18n
 *
 * A limitation is that it only accepts json compatible structures inside the define
 * wrapper function. For example the following example is not ok since there are no
 * quotation marks around the keys:
 * define({
 *   key1: "somevalue",
 *   key2: "anothervalue"
 * });
 *
 * Instead it should look like:
 * define({
 *   "key1": "somevalue",
 *   "key2": "anothervalue"
 * });
 *
 * It also supports the top-level bundle with a root construction and language indicators.
 * The following example will give the same messages as above:
 * define({
 *   "root": {
 *      "key1": "somevalue",
 *      "key2": "anothervalue"
 *   },
 *   "sv": true
 * });
 *
 * Note that it does not support exporting with the root construction, there is only support
 * for reading it. However, this is not a serious limitation as Translatewiki doesn't export
 * the base language.
 *
 * AmdFormat implements a message format where messages are encoded
 * as key-value pairs in JSON objects wrapped in a define call.
 *
 * @author Matthias Palmér
 * @copyright Copyright © 2011-2015, MetaSolutions AB
 * @license GPL-2.0-or-later
 * @ingroup FileFormatSupport
 */
class AmdFormat extends SimpleFormat {

	public function getFileExtensions(): array {
		return [ '.js' ];
	}

	/** @inheritDoc */
	public function readFromVariable( string $data ): array {
		$authors = $this->extractAuthors( $data );
		$data = $this->extractMessagePart( $data );
		$messages = (array)FormatJson::decode( $data, /*as array*/true );
		$metadata = [];

		// Take care of regular language bundles, as well as the root bundle.
		if ( isset( $messages['root'] ) ) {
			$messages = $this->group->getMangler()->mangleArray( $messages['root'] );
		} else {
			$messages = $this->group->getMangler()->mangleArray( $messages );
		}

		return [
			'MESSAGES' => $messages,
			'AUTHORS' => $authors,
			'METADATA' => $metadata,
		];
	}

	protected function writeReal( MessageCollection $collection ): string {
		$messages = [];
		$mangler = $this->group->getMangler();

		/** @var Message $m */
		foreach ( $collection as $key => $m ) {
			$value = $m->translation();
			if ( $value === null ) {
				continue;
			}

			if ( $m->hasTag( 'fuzzy' ) ) {
				$value = str_replace( TRANSLATE_FUZZY, '', $value );
			}

			$key = $mangler->unmangle( $key );
			$messages[$key] = $value;
		}

		// Do not create empty files
		if ( !count( $messages ) ) {
			return '';
		}
		$header = $this->header( $collection->code, $collection->getAuthors() );
		return $header . FormatJson::encode( $messages, "\t", FormatJson::UTF8_OK ) . ");\n";
	}

	private function extractMessagePart( string $data ): string {
		// Find the start and end of the data section (enclosed in the define function call).
		$dataStart = strpos( $data, 'define(' ) + 6;
		$dataEnd = strrpos( $data, ')' );

		// Strip everything outside the data section.
		return substr( $data, $dataStart + 1, $dataEnd - $dataStart - 1 );
	}

	private function extractAuthors( string $data ): array {
		preg_match_all( '~\n \*  - (.+)~', $data, $result );
		return $result[1];
	}

	private function header( string $code, array $authors ): string {
		global $wgSitename;

		$name = Utilities::getLanguageName( $code );
		$authorsList = $this->authorsList( $authors );

		return <<<EOT
			/**
			 * Messages for $name
			 * Exported from $wgSitename
			 *
			{$authorsList}
			 */
			define(
			EOT;
	}

	/** @param string[] $authors */
	private function authorsList( array $authors ): string {
		if ( $authors === [] ) {
			return '';
		}

		$prefix = ' *  - ';
		$authorList = implode( "\n$prefix", $authors );
		return " * Translators:\n$prefix$authorList";
	}
}

class_alias( AmdFormat::class, 'AmdFFS' );
