<?php
/**
 * Support for XML translation format used by Android.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Support for XML translation format used by Android.
 * @since 2012-08-19
 * @ingroup FFS
 */
class AndroidXmlFFS extends SimpleFFS {
	protected static $pluralWords = array(
		'zero' => 1,
		'one' => 1,
		'two' => 1,
		'few' => 1,
		'many' => 1,
		'other' => 1,
	);

	public function supportsFuzzy() {
		return 'yes';
	}

	public function getFileExtensions() {
		return array( '.xml' );
	}

	/**
	 * @param string $data
	 * @return array Parsed data.
	 */
	public function readFromVariable( $data ) {
		$reader = new SimpleXMLElement( $data );

		$messages = array();
		$mangler = $this->group->getMangler();

		/** @var SimpleXMLElement $element */
		foreach ( $reader as $element ) {
			$key = (string)$element['name'];

			if ( $element->getName() === 'string' ) {
				$value = $this->readElementContents( $element );
			} elseif ( $element->getName() === 'plurals' ) {
				$forms = array();
				foreach ( $element as $item ) {
					$forms[(string)$item['quantity']] = $this->readElementContents( $item );
				}
				$value = $this->flattenPlural( $forms );
			} else {
				wfDebug( __METHOD__ . ': Unknown XML element name.' );
				continue;
			}

			if ( isset( $element['fuzzy'] ) && (string)$element['fuzzy'] === 'true' ) {
				$value = TRANSLATE_FUZZY . $value;
			}

			$messages[$key] = $value;
		}

		return array(
			'AUTHORS' => array(), // @todo
			'MESSAGES' => $mangler->mangle( $messages ),
		);
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
		return $escaped;
	}

	protected function writeReal( MessageCollection $collection ) {
		$template = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<resources></resources>
XML;

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

			// Handle plurals
			if ( strpos( $value, '{{PLURAL' ) === false ) {
				$element = $writer->addChild( 'string', $this->formatElementContents( $value ) );
			} else {
				$element = $writer->addChild( 'plurals' );
				$forms = $this->unflattenPlural( $value );
				foreach ( $forms as $quantity => $content ) {
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

	/**
	 * Flattens array of plurals into string.
	 *
	 * @param array $forms array
	 * @return string
	 */
	protected function flattenPlural( array $forms ) {
		$pls = '{{PLURAL';
		foreach ( $forms as $key => $value ) {
			$pls .= "|$key=$value";
		}

		$pls .= '}}';
		return $pls;
	}

	/**
	 * Converts the flattened plural into messages
	 *
	 * @param string $message
	 * @return array
	 */
	protected function unflattenPlural( $message ) {
		$regex = '~\{\{PLURAL\|(.*?)}}~s';
		$matches = array();
		$match = array();

		while ( preg_match( $regex, $message, $match ) ) {
			$uniqkey = TranslateUtils::getPlaceholder();
			$matches[$uniqkey] = $match;
			$message = preg_replace( $regex, $uniqkey, $message, 1 );
		}

		// No plurals, should not happen.
		if ( !count( $matches ) ) {
			return array();
		}

		// The final array of alternative plurals forms.
		$alts = array();

		/*
		 * Then loop trough each plural block and replacing the placeholders
		 * to construct the alternatives. Produces invalid output if there is
		 * multiple plural bocks which don't have the same set of keys.
		 */
		$pluralChoice = implode( '|', array_keys( self::$pluralWords ) );
		$regex = "~($pluralChoice)\s*=\s*(.+)~s";
		foreach ( $matches as $ph => $plu ) {
			$forms = explode( '|', $plu[1] );

			foreach ( $forms as $form ) {
				if ( $form === '' ) {
					continue;
				}

				$match = array();
				if ( !preg_match( $regex, $form, $match ) ) {
					// No quantity key was provided
					continue;
				}

				$formWord = $match[1];
				$value = $match[2];
				if ( !isset( $alts[$formWord] ) ) {
					$alts[$formWord] = $message;
				}

				$string = $alts[$formWord];

				$alts[$formWord] = str_replace( $ph, $value, $string );
			}
		}

		return $alts;
	}
}
