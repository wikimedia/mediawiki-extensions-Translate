<?php
/**
 * Support for XML translation format used by Apache Cocoon.
 *
 * @file
 * @author Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Support for Apache Cocoon translation format.
 * @since 2013-06-26
 * @ingroup FFS
 */
class ApacheCocoonXmlFFS extends AndroidXmlFFS {
	public function readFromVariable( $data ) {
		$reader = new SimpleXMLElement( $data );

		$messages = array();
		$mangler = $this->group->getMangler();

		foreach ( $reader->string as $string ) {
			$key = (string)$string['key'];
			$value = stripcslashes( (string)$string );

			if ( isset( $string['fuzzy'] ) && (string)$string['fuzzy'] === 'true' ) {
				$value = TRANSLATE_FUZZY . $value;
			}

			$messages[$key] = $value;
		}

		return array(
			'AUTHORS' => array(), // @todo
			'MESSAGES' => $mangler->mangle( $messages ),
		);
	}

	protected function writeReal( MessageCollection $collection ) {
		$langCode = $collection->getLanguage();
		$template = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$template .= "<catalogue xml:lang=\"\" xmlns:18n=\"http://apache.org/cocoon/i18n/2.1\"></catalogue>\n";

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

			// Kudos to the brilliant person who invented this braindead file format
			$string = $writer->addChild( 'string', addcslashes( $value, '"\'' ) );
			$string->addAttribute( 'name', $key );

			// This is non-standard
			if ( $m->hasTag( 'fuzzy' ) ) {
				$string->addAttribute( 'fuzzy', 'true' );
			}
		}

		// Make the output pretty with DOMDocument
		$dom = new DOMDocument( '1.0' );
		$dom->formatOutput = true;
		$dom->loadXML( $writer->asXML() );

		return $dom->saveXML();
	}
}
