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
	 * Filters out messages that should not be displayed here
	 * as they are not displayed in other places.
	 *
	 * @see https://bugzilla.wikimedia.org/43030
	 * @param MessageHandle $msg
	 * @return boolean
	 */
	protected function matchingMessage( MessageHandle $msg ) {
		$group = $msg->getGroup();
		$groupId = $group->getId();

		if ( !array_key_exists( $groupId, $this->groupInfoCache ) ) {
			$translatableLanguages = $group->getTranslatableLanguages();
			$languageTranslatable = true;

			global $wgRequest;
			if ( is_array( $translatableLanguages ) &&
				!array_key_exists( $wgRequest->getVal( 'mclanguage' ), $translatableLanguages )
			) {
				$languageTranslatable = false;
			}

			$this->groupInfoCache[$groupId] = array(
				'tags' => $group->getTags(),
				'relevant' => ( $languageTranslatable && ( MessageGroups::getPriority( $group ) !== 'discouraged' ) )
					? true
					: false,
			);
		}

		foreach ( array( 'ignored', 'optional' ) as $tag ) {
			if ( in_array( strtolower( $msg->getKey() ), $this->groupInfoCache[$groupId]['tags'][$tag] ) ) {
				return false;
			}
		}

		return $this->groupInfoCache[$groupId]['relevant'];
	}
}
