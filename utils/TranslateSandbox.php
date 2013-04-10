<?php
/**
 * Utilities for the sandbox feature of Translate.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL2+
 */

/**
 * Utility class for the sandbox feature of Translate.
 */
class TranslateSandbox {
	/**
	 * Adds a new user without doing much validation.
	 * @param string $name User name.
	 * @param string $email Email address.
	 * @param string $password User provided password.
	 * @return User
	 * @throws MWException
	 */
	public static function addUser( $name, $email, $password ) {
		$user = User::newFromName( $name, 'creatable' );
		if ( !$user instanceof User ) {
			throw new MWException( "Invalid user name" );
		}

		$user->setEmail( $email );
		$user->setPassword( $password );
		$status = $user->addToDatabase();

		if ( !$status->isOK() ) {
			throw new MWException( $status->getWikiText() );
		}

		// Need to have an id first
		$user->addGroup( 'translate-sandboxed' );
		$user->clearInstanceCache( 'name' );

		return $user;
	}

	/**
	 * Deletes a sandboxed user without doing much validation.
	 * @param User $user
	 * @throws MWException
	 */
	public static function deleteUser( User $user ) {
		$uid = $user->getId();

		if ( !in_array( 'translate-sandboxed', $user->getGroups(), true ) ) {
			throw new MWException( "Not a sandboxed user" );
		}

		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete( 'user', array( 'user_id' => $uid ), __METHOD__ );
		$dbw->delete( 'user_groups', array( 'ug_user' => $uid ), __METHOD__ );

		$user->clearInstanceCache( 'defaults' );
		// @todo why the bunny is this private?!
		// $user->clearSharedCache();
		global $wgMemc;
		$wgMemc->delete( wfMemcKey( 'user', 'id', $uid ) );
	}

	/**
	 * Get all sandboxed users.
	 * @return UserArray List of users.
	 */
	public static function getUsers() {
		$dbw = wfGetDB( DB_MASTER );
		$tables = array( 'user', 'user_groups' );
		$fields = User::selectFields();
		$conds = array(
			'ug_group' => 'translate-sandboxed',
			'ug_user = user_id',
		);

		$res = $dbw->select( $tables, $fields, $conds, __METHOD__ );
		return UserArray::newFromResult( $res );
	}

	/**
	 * Removes the user from the sandbox.
	 * @param User $user
	 * @throws MWException
	 */
	public static function promoteUser( User $user ) {
		global $wgTranslateSandboxPromotedGroup;

		if ( !in_array( 'translate-sandboxed', $user->getGroups(), true ) ) {
			throw new MWException( "Not a sandboxed user" );
		}

		$user->removeGroup( 'translate-sandboxed' );
		if ( $wgTranslateSandboxPromotedGroup ) {
			$user->addGroup( $wgTranslateSandboxPromotedGroup );
		}
	}

	/// Hook: UserGetRights
	public static function enforcePermissions( User $user, array &$rights ) {
		global $wgTranslateUseSandbox;

		if ( !$wgTranslateUseSandbox ) {
			return true;
		}

		if ( !in_array( 'translate-sandboxed', $user->getGroups(), true ) ) {
			return true;
		}

		$rights = array( 'read', 'translate-sandboxaction' );

		// Do not let other hooks add more actions
		return false;
	}
}
