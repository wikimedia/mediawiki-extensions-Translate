<?php
/**
 * Support for XML translation format used by Android.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Support for XML translation format used by Android.
 * @since 2012-08-19
 * @ingroup FFS
 */
class AndroidXmlFFS extends SimpleFFS {
	public function __construct( FileBasedMessageGroup $group ) {
		parent::__construct( $group );
		$this->flattener = $this->getFlattener();
	}

	public function supportsFuzzy() {
		return 'yes';
	}

	public function getFileExtensions() {
		return [ '.xml' ];
	}

	/**
	 * @param string $data
	 * @return array Parsed data.
	 */
	public function readFromVariable( $data ) {
		$reader = new SimpleXMLElement( $data );

		$messages = [];
		$mangler = $this->group->getMangler();

		/** @var SimpleXMLElement $element */
		foreach ( $reader as $element ) {
			$key = (string)$element['name'];

			if ( $element->getName() === 'string' ) {
				$value = $this->readElementContents( $element );
			} elseif ( $element->getName() === 'plurals' ) {
				$forms = [];
				foreach ( $element as $item ) {
					$forms[(string)$item['quantity']] = $this->readElementContents( $item );
				}
				$value = $this->flattener->flattenCLDRPlurals( $forms );
			} else {
				wfDebug( __METHOD__ . ': Unknown XML element name.' );
				continue;
			}

			if ( isset( $element['fuzzy'] ) && (string)$element['fuzzy'] === 'true' ) {
				$value = TRANSLATE_FUZZY . $value;
			}

			$messages[$key] = $value;
		}

		return [
			'AUTHORS' => $this->scrapeAuthors( $data ),
			'MESSAGES' => $mangler->mangle( $messages ),
		];
	}

	protected function scrapeAuthors( $string ) {
		$match = [];
		preg_match( '~<!-- Authors:\n((?:\* .*\n)*)-->~', $string, $match );
		if ( !$match ) {
			return [];
		}

		$authors = $matches = [];
		preg_match_all( '~\* (.*)~', $match[ 1 ], $matches );
		foreach ( $matches[1] as $author ) {
			// PHP7: \u{2011}
			$authors[] = str_replace( "\xE2\x80\x91\xE2\x80\x91", '--', $author );
		}
		return $authors;
	}

	protected function readElementContents( $element ) {
		return stripcslashes( (string)$element );
	}

	protected function formatElementContents( $contents ) {
		// Kudos to the brilliant person who invented this braindead file format
		$escaped = addcslashes( $contents, '"\'' );
		if ( substr( $escaped, 0, 1 ) === '@' ) {
			// '@' at beginning of string refers to another string by name.
			// Add backslash to escape it too.
			$escaped = '\\' . $escaped;
		}
		// All html entities seen would be inserted by translators themselves.
		// Treat them as plain text.
		$escaped = str_replace( '&', '&amp;', $escaped );

		// Newlines must be escaped
		$escaped = str_replace( "\n", '\n', $escaped );
		return $escaped;
	}

	protected function doAuthors( MessageCollection $collection ) {
		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $collection->code );

		if ( !$authors ) {
			return '';
		}

		$output = "\n<!-- Authors:\n";

		foreach ( $authors as $author ) {
			// Since -- is not allowed in XML comments, we rewrite them to
			// U+2011 (non-breaking hyphen). PHP7: \u{2011}
			$author = str_replace( '--', "\xE2\x80\x91\xE2\x80\x91", $author );
			$output .= "* $author\n";
		}

		$output .= "-->\n";

		return $output;
	}

	protected function writeReal( MessageCollection $collection ) {
		$template  = '<?xml version="1.0" encoding="utf-8"?>';
		$template .= $this->doAuthors( $collection );
		$template .= '<resources></resources>';

		$writer = new SimpleXMLElement( $template );
		$mangler = $this->group->getMangler();

		$collection->filter( 'hastranslation', false );
		if ( count( $collection ) === 0 ) {
			return '';
		}

		/**
		 * @var $m TMessage
		 */
		foreach ( $collection as $key => $m ) {
			$key = $mangler->unmangle( $key );

			$value = $m->translation();
			$value = str_replace( TRANSLATE_FUZZY, '', $value );

			$plurals = $this->flattener->unflattenCLDRPlurals( '', $value );

			if ( $plurals === false ) {
				$element = $writer->addChild( 'string', $this->formatElementContents( $value ) );
			} else {
				$element = $writer->addChild( 'plurals' );
				foreach ( $plurals as $quantity => $content ) {
					$item = $element->addChild( 'item', $this->formatElementContents( $content ) );
					$item->addAttribute( 'quantity', $quantity );
				}
			}

			$element->addAttribute( 'name', $key );
			// This is non-standard
			if ( $m->hasTag( 'fuzzy' ) ) {
				$element->addAttribute( 'fuzzy', 'true' );
			}
		}

		// Make the output pretty with DOMDocument
		$dom = new DOMDocument( '1.0' );
		$dom->formatOutput = true;
		$dom->loadXML( $writer->asXML() );

		return $dom->saveXML();
	}

	protected function getFlattener() {
		$flattener = new ArrayFlattener( '', true );
		return $flattener;
	}

	public function isContentEqual( $a, $b ) {
		return $this->flattener->compareContent( $a, $b );
	}
}
