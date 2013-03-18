<?php
/**
 * This file contains a unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * @since 2012-11-01
 * @ingroup MessageGroup
 */
class RecentAdditionsMessageGroup extends RecentMessageGroup {
	public function getId() {
		return '!additions';
	}

	public function getLabel( IContextSource $context = null ) {
		$msg = wfMessage( 'translate-dynagroup-additions-label' );
		$msg = self::addContext( $msg, $context );
		return $msg->plain();
	}

	public function getDescription( IContextSource $context = null ) {
		$msg = wfMessage( 'translate-dynagroup-additions-desc' );
		$msg = self::addContext( $msg, $context );
		return $msg->plain();
	}

	protected function getQueryConditions() {
		global $wgTranslateMessageNamespaces;
		$db = wfGetDB( DB_SLAVE );
		$conds = array(
			'rc_title ' . $db->buildLike( $db->anyString(), '/en' ),
			'rc_namespace' => $wgTranslateMessageNamespaces,
			'rc_type != ' . RC_LOG,
			'rc_id > ' . $this->getRCCutoff(),
			'rc_user' => FuzzyBot::getUser()->getId(),
		);
		return $conds;
	}

	/**
	 * Filters out messages that aren't useful.
	 * See bug 43030.
	 *
	 * @param $group MessageGroup
	 * @param $msg string message id
	 * @return boolean
	 */
	protected function groupMatches( $group, $msg ) {
		$groupId = $group->getId();

		if ( !array_key_exists( $groupId, $this->groupInfoCache ) ) {
			$this->groupInfoCache[$groupId] = array(
				'tags' => $group->getTags(),
				'translatableLanguages' => $group->getTranslatableLanguages(),
				'discouraged' => ( MessageGroups::getPriority( $group ) === 'discouraged' ),
			);
		}

		foreach ( array( 'ignored', 'optional' ) as $tag ) {
			if ( in_array( $msg, $this->groupInfoCache[$groupId]['tags'][$tag] ) ) {
				return false;
			}
		}

		if ( $this->groupInfoCache[$groupId]['discouraged']
			|| is_array( $this->groupInfoCache[$groupId]['translatableLanguages'] ) &&
				in_array( $msg, $this->groupInfoCache[$groupId]['translatableLanguages'] )
		) {
			return false;
		}

		return true;
	}
}
