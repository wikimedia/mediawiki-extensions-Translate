<?php
/**
 * Support for XML translation format used by Android.
 *
 * @file
 * @author Niklas Laxström
 * @author Ciaran Gultnieks
 * @license GPL-2.0+
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

		// Read normal strings...
		foreach ( $reader->string as $string ) {
			$key = (string)$string['name'];
			$value = stripcslashes( (string)$string );

			if ( isset( $string['fuzzy'] ) && (string)$string['fuzzy'] === 'true' ) {
				$value = TRANSLATE_FUZZY . $value;
			}

			$messages[$key] = $value;
		}

		// Read string arrays. Each item is given a message ID prefixed
		// with ASA_ and postfixed with _n where n is the index of the
		// item. This is to allow the array to be reconstructed at when
		// writing. (ASA = Android String Array)
		foreach ( $reader->{"string-array"} as $stringarray ) {
			$arrayname = (string)$stringarray['name'];
			$index = 0;
			foreach( $stringarray->item as $item ) {
				$key = "ASA_${arrayname}_$index";
				$messages[$key] = (string)$item;
				$index++;
			}
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

			if ( strpos($key, 'ASA_') === false ) {

				// Kudos to the brilliant person who invented this braindead file format
				$string = $writer->addChild( 'string', addcslashes( $value, '"\'' ) );
				$string->addAttribute( 'name', $key );

				// This is non-standard
				if ( $m->hasTag( 'fuzzy' ) ) {
					$string->addAttribute( 'fuzzy', 'true' );
                                }

			} else {

				// I'm assuming the strings are coming back out in
				// same order they went in. If that turns out not
				// to be the case, we'll have to do something more
				// here to ensure they go in the XML in the right
				// order. (we have the order, tagged on to the end
				// of the message IDs!)
				$arrayname = substr($key, 4);
				$arrayname = substr($arrayname, 0, strrpos($arrayname, '_'));
				$arrayel = $writer->xpath("//string-array[@name='$arrayname']");
                                if (count($arrayel) === 1 ) {
                                        $arrayel = $arrayel[0];
                                } else {
					$arrayel = $writer->addChild( 'string-array');
				        $arrayel->addAttribute( 'name', $arrayname);
				}

				$arrayel->addChild( 'item', $value);

			}
		}

		// Make the output pretty with DOMDocument
		$dom = new DOMDocument( '1.0' );
		$dom->formatOutput = true;
		$dom->loadXML( $writer->asXML() );

		return $dom->saveXML();
	}
}
