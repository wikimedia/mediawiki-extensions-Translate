<?php
/**
 * Partial support for the Xliff translation format.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL2+
 */

/**
 * Partial support for the Xliff translation format.
 * @since 2013-04
 * @ingroup FFS
 */
class XliffFFS extends SimpleFFS {
	public function readFromVariable( $data, $element = 'target' ) {

		$messages = array();
		$mangler = $this->group->getMangler();

		$reader = new SimpleXMLElement( $data );
		foreach ( $reader->xpath( '//trans-unit' ) as $item ) {
			$source = $item->$element;

			if ( !$source ) {
				continue;
			}

			$key = (string) $item['id'];

			/* In case there are tags inside the element, preserve
			 * them. */
			$dom = new DOMDocument( '1.0' );
			$dom->loadXML( $source->asXml() );
			$value = self::getInnerXml( $dom->documentElement );

			/* This might not be 100% according to the spec, but
			 * for now if there is explicit approved=no, mark it
			 * as fuzzy, but don't do that if the attribute is not
			 * set */
			if ( (string) $source['state'] === 'needs-l10n' ) {
				$value = TRANSLATE_FUZZY . $value;
			}

			$messages[$key] = $value;
		}

		return array(
			'MESSAGES' => $mangler->mangle( $messages ),
		);
	}

	public function read( $code ) {
		if ( !$this->exists( $code ) ) {
			return false;
		}

		$filename = $this->group->getSourceFilePath( $code );
		$input = file_get_contents( $filename );
		if ( $input === false ) {
			throw new MWException( "Unable to read file $filename." );
		}

		$element = $code === $this->group->getSourceLanguage() ? 'source' : 'target';

		return $this->readFromVariable( $input, $element );
	}

	/**
	 * Gets the html inside en element without the element itself.
	 */
	public static function getInnerXml( DomElement $node ) {
		$text = '';
		foreach ( $node->childNodes as $child ) {
			$text .= $child->ownerDocument->saveXML( $child );
		}
		return $text;
	}

	protected function writeReal( MessageCollection $collection ) {
		$mangler = $this->group->getMangler();

		$template = new DomDocument( '1.0' );
		$template->preserveWhiteSpace = false;
		$template->formatOutput = true;

		// Try to use the definition file as template
		$sourceLanguage = $this->group->getSourceLanguage();
		$sourceFile = $this->group->getSourceFilePath( $sourceLanguage );
		if ( file_exists( $sourceFile ) ) {
			$template->load( $sourceFile );
		} else {
			// Else use standard template
			$template->load( __DIR__ . '/../data/xliff-template.xml' );
		}

		$list = $template->getElementsByTagName( 'body' )->item( 0 );
		$list->nodeValue = null;

		foreach ( $collection as $key => $m ) {
			$key = $mangler->unmangle( $key );

			$value = $m->translation();
			$value = str_replace( TRANSLATE_FUZZY, '', $value );

			// @todo Support placeholder tags etc.
			$source = $template->createDocumentFragment();
			$source->appendXML( htmlspecialchars( $m->definition() ) );

			$target = $template->createDocumentFragment();
			$target->appendXML( htmlspecialchars( $value ) );

			$sourceElement = $template->createElement( 'source' );
			$sourceElement->appendChild( $source );

			$targetElement = $template->createElement( 'target' );
			$targetElement->appendChild( $target );
			if ( $m->getProperty( 'status' ) === 'fuzzy' ) {
				$targetElement->setAttribute( 'state', 'needs-l10n' );
			}
			if ( $m->getProperty( 'status' ) === 'proofread' ) {
				$targetElement->setAttribute( 'state', 'signed-off' );
			}

			$transUnit = $template->createElement( 'trans-unit' );
			$transUnit->setAttribute( 'id', $key );
			$transUnit->appendChild( $sourceElement );
			$transUnit->appendChild( $targetElement );

			$list->appendChild( $transUnit );
		}

		$template->encoding = 'UTF-8';
		return $template->saveXML();
	}

	public function supportsFuzzy() {
		return 'yes';
	}
}
