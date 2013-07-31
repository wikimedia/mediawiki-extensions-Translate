<?php
/**
 * This file contains an unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * @since 2012-11-01
 * @ingroup MessageGroup
 */
class RecentAdditionsMessageGroup extends RecentMessageGroup {
	protected $groupInfoCache = array();

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

			if ( is_array( $translatableLanguages ) &&
				!array_key_exists( $this->language, $translatableLanguages )
			) {
				$languageTranslatable = false;
			}

			$groupDiscouraged = MessageGroups::getPriority( $group ) !== 'discouraged';
			$this->groupInfoCache[$groupId] = array(
				'relevant' => ( $languageTranslatable && $groupDiscouraged ),
				'tags' => array(),
			);

			$groupTags = $group->getTags();
			foreach ( array( 'ignored', 'optional' ) as $tag ) {
				if ( isset( $groupTags[$tag] ) ) {
					foreach ( $groupTags[$tag] as $key ) {
						$this->groupInfoCache[$groupId]['tags'][ucfirst( $key )] = true;
					}
				}
			}
		}

		return !isset( $this->groupInfoCache[$groupId]['tags'][$msg->getKey()] ) &&
			$this->groupInfoCache[$groupId]['relevant'];
	}
}
