<?php
/**
 * Support for XML translation format used by Android.
 *
 * @file
 * @author Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Support for XML translation format used by Android.
 * @since 2012-08-19
 * @ingroup FFS
 */
class AndroidXmlFFS extends SimpleFFS {
	public function supportsFuzzy() {
		return 'yes';
	}

	public function getFileExtensions() {
		return array( '.xml' );
	}

	public function readFromVariable( $data ) {
		$reader = new SimpleXMLElement( $data );

		$messages = array();
		$mangler = $this->group->getMangler();

		foreach ( $reader as $element ) {
			$key = (string)$element['name'];

			if ( $element->getName() === 'string' ) {
				$value = stripcslashes( (string)$element );
				if ( isset( $element['fuzzy'] ) && (string)$element['fuzzy'] === 'true' ) {
					$value = TRANSLATE_FUZZY . $value;
				}
			} else {
				// TODO: process the subelements
				$value = $this->flattenPlural( array() );
			}

			$messages[$key] = $value;
		}

		return array(
			'AUTHORS' => array(), // @todo
			'MESSAGES' => $mangler->mangle( $messages ),
		);
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
			if ( strpos( $message, '{{PLURAL' ) !== false ) {
				// TODO Create plurals element with sub elements
			} else {
				// Kudos to the brilliant person who invented this braindead file format
				$string = $writer->addChild( 'string', addcslashes( $value, '"\'' ) );
				$string->addAttribute( 'name', $key );

				// This is non-standard
				if ( $m->hasTag( 'fuzzy' ) ) {
					$string->addAttribute( 'fuzzy', 'true' );
				}
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
		foreach ( $messages as $key => $value ) {
			$pls .= "|$key=$value";
		}

		$pls .= "}}";
		return $pls;
	}

	/**
	 * Converts the flattened plural into messages
	 *
	 * @param string $key
	 * @param string $message
	 * @return bool|array
	 */
	protected function unflattenPlural( $key, $message ) {
		// See RubyYamlFFS implementation
	}
}
