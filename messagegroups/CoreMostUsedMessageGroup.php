<?php
/**
 * This file contains an unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0+
 */

/**
 * This class implements the "Most used messages" group for %MediaWiki.
 * @todo Move to the new interface.
 * @ingroup MessageGroup
 */
class CoreMostUsedMessageGroup extends CoreMessageGroup {
	protected $label = 'MediaWiki (most used)';
	protected $id = 'core-0-mostused';
	protected $meta = true;
	protected $list;

	protected $description = '{{int:translate-group-desc-mediawikimostused}}';

	public function export( MessageCollection $messages ) {
		return 'Not supported';
	}

	public function exportToFile( MessageCollection $messages, $authors ) {
		return 'Not supported';
	}

	public function setListFile( $file ) {
		$this->list = $file;
	}

	function getDefinitions() {
		$data = file_get_contents( $this->list );
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

