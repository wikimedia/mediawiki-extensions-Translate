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
 * Mysql based backend shared with multiple wikies.
 * @ingroup TTMServer
 * @since 2012-06-27
 */
class SharedDatabaseTTMServer extends DatabaseTTMServer {
	protected function getExtraConditions() {
		return array( 'tms_wiki' => wfWikiId() );
	}

	public function beginBootstrap() {
		$dbw = $this->getDB( DB_MASTER );
		$wiki = $this->getExtraConditions();

		$dbw->deleteJoin(
			'translate_tmf', 'translate_tms',
			'tmf_sid', 'tms_sid',
			$wiki, __METHOD__
		);

		$dbw->deleteJoin(
			'translate_tmt', 'translate_tms',
				'tmt_sid', 'tms_sid',
				$wiki, __METHOD__
		);
		$dbw->delete( 'translate_tms', $wiki, __METHOD__ );
	}

	// Overwrite parent behaviour
	public function endBootstrap() {}

	/* Reading interface */

	public function isLocalSuggestion( array $suggestion ) {
		return $suggestion['wiki'] === wfWikiId();
	}

	public function expandLocation( array $suggestion ) {
		$wiki = WikiMap::getWiki( $suggestion['wiki'] );
		return $wiki->getCanonicalUrl( $suggestion['location'] );
	}
}
