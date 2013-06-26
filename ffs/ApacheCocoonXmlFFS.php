<?php
/**
 * Support for XML translation format used by Apache Cocoon.
 *
 * @file
 * @author Siebrand Mazeland
 * @license GPL-2.0+
 */

/**
 * Support for Apache Cocoon translation format.
 * @since 2013.10
 * @ingroup FFS
 */
class ApacheCocoonXmlFFS extends SimpleFFS {
	public static function isValid( $data ) {
		$doc = new DomDocument( '1.0' );
		$doc->loadXML( $data );

		$errors = libxml_get_errors();
		if ( $errors ) {
			return false;
		}

		// HTTP 404 http://apache.org/cocoon/i18n/2.1
		/*$schema = ''
		if ( !$doc->schemaValidate( $schema ) ) {
			return false;
		}*/

		return true;
	}

	public function getFileExtensions() {
		return array( '.xml' );
	}

	public function supportsFuzzy() {
		return 'yes';
	}

	public function readFromVariable( $data ) {
		$reader = new SimpleXMLElement( $data );

		$messages = array();
		$mangler = $this->group->getMangler();

		foreach ( $reader->message as $string ) {
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
		$langCode = htmlspecialchars( $collection->getLanguage() );
		$template = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<catalogue xml:lang="$langCode">
</catalogue>
XML;

		$writer = new SimpleXMLElement( $template );
		$mangler = $this->group->getMangler();

		$collection->filter( 'hastranslation', false );
		if ( count( $collection ) === 0 ) {
			return '';
		}

		/// @var $m TMessage
		foreach ( $collection as $key => $m ) {
			$key = $mangler->unmangle( $key );

			$value = $m->translation();
			$value = str_replace( TRANSLATE_FUZZY, '', $value );

			$string = $writer->addChild( 'message', $value );
			$string->addAttribute( 'key', $key );

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
