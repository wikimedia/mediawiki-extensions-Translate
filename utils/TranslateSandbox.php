<?php
/**
 * Utilities for the sandbox feature of Translate.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
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
	 *
	 * @param User $user
	 * @param string $force If set to 'force' will skip the little validation we have.
	 * @throws MWException
	 */
	public static function deleteUser( User $user, $force = '' ) {
		$uid = $user->getId();

		if ( $force !== 'force' && !self::isSandboxed( $user ) ) {
			throw new MWException( "Not a sandboxed user" );
		}

		// Delete from database
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete( 'user', array( 'user_id' => $uid ), __METHOD__ );
		$dbw->delete( 'user_groups', array( 'ug_user' => $uid ), __METHOD__ );

		// If someone tries to access still object still, they will get anon user
		// data.
		$user->clearInstanceCache( 'defaults' );

		// Nobody should access the user by id anymore, but in case they do, purge
		// the cache so they wont get stale data
		// @todo why the bunny is this private?!
		// $user->clearSharedCache();
		global $wgMemc;
		$wgMemc->delete( wfMemcKey( 'user', 'id', $uid ) );

		// In case we create an user with same name as was deleted during the same
		// request, we must also reset this cache or the User class will try to load
		// stuff for the old id, which is no longer present since we just deleted
		// the cache above. But it would have the side effect or overwriting all
		// member variables with null data. This used to manifest as a bug where
		// inserting a new user fails because the mName properpty is set to null,
		// which is then converted as the ip of the current user, and trying to
		// add that twice results in a name conflict. It was fun to debug.
		User::resetIdByNameCache();
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

		if ( !self::isSandboxed( $user ) ) {
			throw new MWException( "Not a sandboxed user" );
		}

		$user->removeGroup( 'translate-sandboxed' );
		if ( $wgTranslateSandboxPromotedGroup ) {
			$user->addGroup( $wgTranslateSandboxPromotedGroup );
		}
	}

	/**
	 * Sends a reminder to the user.
	 * @param User $sender
	 * @param User $target
	 * @param string $subject Subject of the email.
	 * @param string $body Body of the email.
	 * @throws MWException
	 */
	public static function sendReminder( User $sender, User $target, $subject, $body ) {
		global $wgNoReplyAddress;

		if ( !self::isSandboxed( $user ) ) {
			throw new MWException( "Not a sandboxed user" );
		}

		$params = array(
			'user' => $target->getId(),
			'to' => $target->getEmail(),
			'from' => $sender->getEmail(),
			'replyto' => $wgNoReplyAddress,
			'subj' => $subject,
			'body' => $body,
		);

		TranslateSandboxReminderJob::newJob( $params )->insert();
	}

	/**
	 * Shortcut for checking if given user is in the sandbox.
	 * @param User $user
	 * @return bool
	 * @since 2013.06
	 */
	public static function isSandboxed( User $user ) {
		if ( in_array( 'translate-sandboxed', $user->getGroups(), true ) ) {
			return true;
		}

		return false;
	}

	/// Hook: UserGetRights
	public static function enforcePermissions( User $user, array &$rights ) {
		global $wgTranslateUseSandbox;

		if ( !$wgTranslateUseSandbox ) {
			return true;
		}

		if ( !self::isSandboxed( $user ) ) {
			return true;
		}

		$rights = array( 'read', 'translate-sandboxaction', 'readapi', 'writeapi', 'editmyoptions' );

		// Do not let other hooks add more actions
		return false;
	}

	/// Hook: onGetPreferences
	public static function onGetPreferences( $user, &$preferences ) {
		$preferences['translate-sandbox'] = $preferences['translate-sandbox-reminders'] =
			array( 'type' => 'api' );

		return true;
	}

	/**
	 * Whitelisting for certain API modules. See also enforcePermissions.
	 * Hook: ApiCheckCanExecute
	 */
	public static function onApiCheckCanExecute( ApiBase $module, User $user, &$message ) {
		$whitelist = array(
			// Obviously this is needed to get out of the sandbox
			'ApiTranslationStash',
			// Used by UniversalLanguageSelector for example
			'ApiOptions'
		);

		if ( TranslateSandbox::isSandboxed( $user ) ) {
			$class = get_class( $module );
			if ( $module->isWriteMode() && !in_array( $class, $whitelist, true ) ) {
				$message = 'writerequired';
				return false;
			}
		}

		return true;
	}
}
