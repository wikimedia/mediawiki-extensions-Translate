<?php
/**
 * API module.
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * API module for collecting and accessing translation related
 * information about the user.
 *
 * @ingroup API TranslateAPI
 * @since 2012-11-30
 */
class ApiTranslateUser extends ApiBase {
	public function execute() {
		$output = array();

		$user = $this->getUser();
		$groups = $user->getOption( 'translate-recent-groups', '' );
		$output['recentgroups'] = array();
		if ( strval( $groups ) !== '' ) {
			$output['recentgroups'] = explode( '|', $groups );
			$this->getResult()->setIndexedTagName( $output['recentgroups'], 'group' );
		}
		$this->getResult()->addValue( null, $this->getModuleName(), $output );
	}

	public function getDescription() {
		return 'Translation related information about the user. Experimental.';
	}

	public function getExamples() {
		return array(
			'api.php?action=translateuser',
		);
	}

	public function getVersion() {
		return '2012-11-30';
	}

	/**
	 * Keeps track of recently used message groups per user.
	 */
	public static function trackGroup( MessageGroup $group, User $user ) {
		if ( $user->isAnon() ) {
			return true;
		}

		$groups = $user->getOption( 'translate-recent-groups', '' );

		if ( $groups === '' ) {
			$groups = array();
		} else {
			$groups = explode( '|', $groups );
		}

		if ( isset( $groups[0] ) && $groups[0] === $group->getId() ) {
			return true;
		}

		array_unshift( $groups, $group->getId() );
		$groups = array_unique( $groups );
		$groups = array_slice( $groups, 0, 5 );

		$user->setOption( 'translate-recent-groups', implode( '|', $groups ) );
		$user->saveSettings();

		return true;
	}
}
