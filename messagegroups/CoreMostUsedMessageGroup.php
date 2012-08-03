<?php
/**
 * This file contains a unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2012, Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * This class implements the "Most used messages" group for %MediaWiki.
 * @todo Move to the new interface.
 * @ingroup MessageGroup
 */
class CoreMostUsedMessageGroup extends CoreMessageGroup {
	protected $label = 'MediaWiki (most used)';
	protected $id    = 'core-0-mostused';
	protected $meta  = true;

	protected $description = '{{int:translate-group-desc-mediawikimostused}}';

	public function export( MessageCollection $messages ) { return 'Not supported'; }
	public function exportToFile( MessageCollection $messages, $authors ) { return 'Not supported'; }

	function getDefinitions() {
		$data = file_get_contents( dirname( __FILE__ ) . '/wikimedia-mostused-2011.txt' );
		$data = str_replace( "\r", '', $data );
		$messages = explode( "\n", $data );
		$contents = parent::getDefinitions();
		$definitions = array();

		foreach ( $messages as $key ) {
			if ( isset( $contents[$key] ) ) {
				$definitions[$key] = $contents[$key];
			}
		}

		return $definitions;
	}
}

