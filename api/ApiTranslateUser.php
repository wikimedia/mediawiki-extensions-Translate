<?php
/**
 * API module.
 * @file
 * @author Niklas Laxström
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

	protected function getExamplesMessages() {
		return array(
			'action=translateuser'
				=> 'apihelp-translateuser-example-1',
		);
	}

	/**
	 * Keeps track of recently used message groups per user.
	 *
	 * @param MessageGroup $group
	 * @param User $user
	 */
	public static function trackGroup( MessageGroup $group, User $user ) {
		if ( $user->isAnon() ) {
			return;
		}

		$groups = $user->getOption( 'translate-recent-groups', '' );

		if ( $groups === '' ) {
			$groups = array();
		} else {
			$groups = explode( '|', $groups );
		}

		if ( isset( $groups[0] ) && $groups[0] === $group->getId() ) {
			return;
		}

		array_unshift( $groups, $group->getId() );
		$groups = array_unique( $groups );
		$groups = array_slice( $groups, 0, 5 );

		$user->setOption( 'translate-recent-groups', implode( '|', $groups ) );
		// Promise to persist the data post-send
		DeferredUpdates::addCallableUpdate( function() use ( $user ) {
			$user->saveSettings();
		} );
	}
}
