<?php
/**
 * TTMServer - The Translate extension translation memory interface
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @ingroup TTMServer
 */

/**
 * Class for handling remote TTMServers over MediaWiki API.
 * Currently querying is done in TranslationHelpers, and
 * this class only handles location retrieval.
 * @since 2012-06-27
 * @ingroup TTMServer
 */
class RemoteTTMServer extends TTMServer implements ReadableTTMServer {
	public function query( $sourceLanguage, $targetLanguage, $text ) {
		// TODO: implement some day perhaps?
		return array();
	}

	public function isLocalSuggestion( array $suggestion ) {
		return false;
	}

	public function expandLocation( array $suggestion ) {
		return $suggestion['location'];
	}
}
