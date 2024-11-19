<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use MediaWiki\Extension\Translate\MessageLoading\Message;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Extension\Translate\Utilities\Utilities;

/**
 * File format support for DTD.
 * @author Guillaume Duhamel
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2009-2010, Guillaume Duhamel, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @ingroup FileFormatSupport
 */
class DtdFormat extends SimpleFormat {
	public function getFileExtensions(): array {
		return [ '.dtd' ];
	}

	public function readFromVariable( string $data ): array {
		preg_match_all( ',# Author: ([^\n]+)\n,', $data, $matches );
		$authors = $matches[1];

		preg_match_all( ',<!ENTITY[ ]+([^ ]+)\s+"([^"]+)"[^>]*>,', $data, $matches );
		[ , $keys, $messages ] = $matches;
		$messages = array_combine(
			$keys,
			array_map(
				static fn ( $message ) => html_entity_decode( $message, ENT_QUOTES ),
				$messages
			)
		);

		$messages = $this->group->getMangler()->mangleArray( $messages );

		return [
			'AUTHORS' => $authors,
			'MESSAGES' => $messages,
		];
	}

	protected function writeReal( MessageCollection $collection ): string {
		$collection->loadTranslations();

		$header = "<!--\n";
		$header .= $this->doHeader( $collection );
		$header .= $this->doAuthors( $collection );
		$header .= "-->\n";

		$output = '';
		$mangler = $this->group->getMangler();

		/** @var Message $message */
		foreach ( $collection as $key => $message ) {
			$key = $mangler->unmangle( $key );
			$trans = $message->translation() ?? '';
			if ( $trans === '' ) {
				continue;
			}
			$trans = str_replace( TRANSLATE_FUZZY, '', $trans );

			$trans = str_replace( '"', '&quot;', $trans );
			$output .= "<!ENTITY $key \"$trans\">\n";
		}

		if ( $output ) {
			return $header . $output;
		}

		return '';
	}

	private function doHeader( MessageCollection $collection ): string {
		global $wgSitename;

		$code = $collection->code;
		$name = Utilities::getLanguageName( $code );
		$native = Utilities::getLanguageName( $code, $code );

		$output = "# Messages for $name ($native)\n";
		$output .= "# Exported from $wgSitename\n\n";

		return $output;
	}

	private function doAuthors( MessageCollection $collection ): string {
		$output = '';
		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $collection->code );

		foreach ( $authors as $author ) {
			$output .= "# Author: $author\n";
		}

		return $output;
	}
}

class_alias( DtdFormat::class, 'DtdFFS' );
