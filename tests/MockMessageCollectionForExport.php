<?php
/**
 * Mock class for unit tests
 * @author Niklas Laxström
 * @file
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * This class can be used to test exporting of message groups.
 */
class MockMessageCollectionForExport extends MessageCollection {
	public function __construct() {
		$msg = new FatMessage( 'translatedmsg', 'definition' );
		$msg->setTranslation( 'translation' );
		$this->messages['translatedmsg'] = $msg;

		$msg = new FatMessage( 'fuzzymsg', 'definition' );
		$msg->addTag( 'fuzzy' );
		$msg->setTranslation( '!!FUZZY!!translation' );
		$this->messages['fuzzymsg'] = $msg;

		$msg = new FatMessage( 'untranslatedmsg', 'definition' );
		$this->messages['untranslatedmsg'] = $msg;

		$this->keys = array_flip( array_keys( $this->messages ) );
	}

	public function getAuthors() {
		return array( 'Nike the bunny' );
	}

	public function getLanguage() {
		return 'fi';
	}
}
