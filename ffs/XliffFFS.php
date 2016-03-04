<?php
/**
 * Partial support for the Xliff translation format.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Partial support for the Xliff translation format.
 * @since 2013-04
 * @ingroup FFS
 */
class XliffFFS extends SimpleFFS {
	public static function isValid( $data ) {
		$doc = new DomDocument( '1.0' );
		$doc->loadXML( $data );

		$errors = libxml_get_errors();
		if ( $errors ) {
			return false;
		}

		if ( strpos( $data, 'version="1.2">' ) !== false ) {
			$schema = __DIR__ . '/../data/xliff-core-1.2-transitional.xsd';
			if ( !$doc->schemaValidate( $schema ) ) {
				return false;
			}
		}

		return true;
	}

	public function getFileExtensions() {
		return array( '.xlf', '.xliff', '.xml' );
	}

	/**
	 * @param string $data
	 * @param string $element
	 * @return array Parsed data.
	 */
	public function readFromVariable( $data, $element = 'target' ) {
		$messages = array();
		$mangler = $this->group->getMangler();

		$reader = new SimpleXMLElement( $data );
		$reader->registerXPathNamespace(
			'xliff',
			'urn:oasis:names:tc:xliff:document:1.2'
		);

		$items = array_merge(
			$reader->xpath( '//trans-unit' ),
			$reader->xpath( '//xliff:trans-unit' )
		);

		foreach ( $items as $item ) {
			/** @var SimpleXMLElement $source */
			$source = $item->$element;

			if ( !$source ) {
				continue;
			}

			$key = (string)$item['id'];

			/* In case there are tags inside the element, preserve
			 * them. */
			$dom = new DOMDocument( '1.0' );
			$dom->loadXML( $source->asXML() );
			$value = self::getInnerXml( $dom->documentElement );

			/* This might not be 100% according to the spec, but
			 * for now if there is explicit approved=no, mark it
			 * as fuzzy, but don't do that if the attribute is not
			 * set */
			if ( (string)$source['state'] === 'needs-l10n' ) {
				$value = TRANSLATE_FUZZY . $value;
			}

			// Strip CDATA if present
			$value = preg_replace( '/<!\[CDATA\[(.*?)\]\]>/s', '\1', $value );

			$messages[$key] = $value;
		}

		return array(
			'MESSAGES' => $mangler->mangle( $messages ),
		);
	}

	/**
	 * @param string $code Language code.
	 * @return array|bool
	 * @throws MWException
	 */
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
	 *
	 * @param DomElement $node
	 * @return string
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

		/** @var TMessage $m */
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
