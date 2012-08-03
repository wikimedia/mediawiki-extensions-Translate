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
 * @since 2011-11-28
 * @ingroup MessageGroup
 */
class RecentMessageGroup extends WikiMessageGroup {
	// Ugly
	protected $namespace = false;

	protected $language;

	public function __construct() {}

	public function setLanguage( $code ) {
		$this->language = $code;
	}

	public function getId() {
		return '!recent';
	}

	public function getLabel() {
		return wfMessage( 'translate-dynagroup-recent-label' )->text();
	}

	public function getDescription() {
		return wfMessage( 'translate-dynagroup-recent-desc' )->text();
	}

	protected function getRCCutoff() {
		$db = wfGetDB( DB_SLAVE );
		$tables = 'recentchanges';
		$max = $db->selectField( $tables, 'MAX(rc_id)', array(), __METHOD__ );
		return max( 0, $max - 50000 );
	}

	public function getDefinitions() {
		if ( !$this->language ) {
			throw new MWException( "Language not set" );
		}

		global $wgTranslateMessageNamespaces;
		$db = wfGetDB( DB_SLAVE );
		$tables = 'recentchanges';
		$fields = array( 'rc_namespace', 'rc_title' );
		$conds = array(
			'rc_title ' . $db->buildLike( $db->anyString(), '/' . $this->language ),
			'rc_namespace' => $wgTranslateMessageNamespaces,
			'rc_type != ' . RC_LOG,
			'rc_id > ' . $this->getRCCutoff(),
		);
		$options = array(
			'ORDER BY' => 'rc_id DESC',
			'LIMIT' => 1000
		);
		$res = $db->select( $tables, $fields, $conds, __METHOD__, $options );

		$defs = array();
		foreach ( $res as $row ) {
			$title = Title::makeTitle( $row->rc_namespace, $row->rc_title );
			$handle = new MessageHandle( $title );
			if ( !$handle->isValid() ) {
				continue;
			}

			$mkey = $row->rc_namespace . ':' . $handle->getKey();
			if ( !isset( $defs[$mkey] ) ) {
				$group = $handle->getGroup();
				$defs[$mkey] = $group->getMessage( $handle->getKey(), $group->getSourceLanguage() );
			}
		}
		return $defs;
	}

	public function getChecker() {
		return null;
	}

	/**
	 * Subpage language of any in the title is not used.
	 */
	public function getMessageContent( MessageHandle $handle ) {
		$groupId = MessageIndex::getPrimaryGroupId( $handle );
		$group = MessageGroups::getGroup( $groupId );
		if ( $group ) {
			return $group->getMessage( $handle->getKey(), $group->getSourceLanguage() );
		}
	}
}
