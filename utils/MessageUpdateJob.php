<?php
/**
 * Job for updating translation pages.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2010, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Job for updating translation pages when translation or template changes.
 *
 * @ingroup JobQueue
 */
class MessageUpdateJob extends Job {

	public static function newJob( Title $target, $content, $fuzzy = false ) {
		$params = array(
			'content' => $content,
			'fuzzy' => $fuzzy,
		);
		$job = new self( $target, $params );
		return $job;
	}

	function __construct( $title, $params = array(), $id = 0 ) {
		parent::__construct( __CLASS__, $title, $params, $id );
		$this->params = $params;
	}

	function run() {
		$title = $this->title;
		$params = $this->params;
		$user = FuzzyBot::getUser();
		$flags = EDIT_DEFER_UPDATES | EDIT_FORCE_BOT;

		$article = new Article( $title, 0 );
		$summary = wfMessage( 'translate-manage-import-summary' )->plain();
		$article->doEdit( $params['content'], $summary, $flags, false, $user );

		if ( $params['fuzzy'] ) {
			$handle = new MessageHandle( $title );
			$key = $handle->getKey();
			$languages = array_keys( Language::getLanguageNames( false ) );

			$dbw = wfGetDB( DB_MASTER );
			$fields = array( 'page_id', 'page_latest' );
			$conds = array( 'page_namespace' => $title->getNamespace() );

			$pages = array();
			foreach ( $languages as $code ) {
				$otherTitle = Title::makeTitleSafe( $title->getNamespace(), "$key/$code" );
				$pages[$otherTitle->getDBKey()] = true;
			}
			unset( $pages[$title->getDBKey()] );
			if ( count( $pages ) === 0 ) {
				return true;
			}

			$conds['page_title'] = array_keys( $pages );

			$res = $dbw->select( 'page', $fields, $conds, __METHOD__ );
			$inserts = array();
			foreach ( $res as $row ) {
				$inserts[] = array(
					'rt_type' => Revtag::getType( 'fuzzy' ),
					'rt_page' => $row->page_id,
					'rt_revision' => $row->page_latest,
				);
			}

			$dbw->replace( 'revtag', array( array( 'rt_type', 'rt_page', 'rt_revision' ) ), $inserts, __METHOD__ );
		}

		return true;
	}

}
