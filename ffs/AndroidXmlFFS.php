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
	public function readFromVariable( $data ) {
		$reader = new SimpleXMLElement( $data );

		$messages = array();
		foreach ( $reader->string as $string ) {
			$messages[(string) $string['name']] = (string) $string;
		}

		return array(
			'AUTHORS' => array(), // TODO
			'MESSAGES' => $messages,
		);
	}

	protected function writeReal( MessageCollection $collection ) {
		$template = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<resources></resources>
XML;

		$writer = new SimpleXMLElement( $template );

		foreach ( $collection as $key => $m ) {
			$value = $m->translation();
			if ( $value === null ) {
				continue;
			}

			$string = $writer->addChild( 'string', $value );
			$string->addAttribute( 'name', $key );

			// This is non-standard
			if ( $m->hasTag( 'fuzzy' ) ) {
				$string->addAttribute( 'fuzzy', 'true' );
			}

		}

		return $writer->asXml();
	}
}
