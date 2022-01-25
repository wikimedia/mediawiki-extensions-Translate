<?php
/**
 * Utilities for the sandbox feature of Translate.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Auth\AuthenticationRequest;
use MediaWiki\Auth\AuthenticationResponse;
use MediaWiki\Auth\AuthManager;
use MediaWiki\Extension\Translate\SystemUsers\TranslateUserManager;
use MediaWiki\MediaWikiServices;
use Wikimedia\ScopedCallback;

/**
 * Utility class for the sandbox feature of Translate. Do not try this yourself. This code makes a
 * lot of assumptions about what happens to the user account.
 */
class TranslateSandbox {
	/**
	 * Adds a new user without doing much validation.
	 *
	 * @param string $name User name.
	 * @param string $email Email address.
	 * @param string $password User provided password.
	 * @return User
	 * @throws MWException
	 */
	public static function addUser( $name, $email, $password ) {
		$user = User::newFromName( $name, 'creatable' );

		if ( !$user ) {
			throw new MWException( 'Invalid user name' );
		}

		$data = [
			'username' => $user->getName(),
			'password' => $password,
			'retype' => $password,
			'email' => $email,
			'realname' => '',
		];

		$services = MediaWikiServices::getInstance();

		$permissionManager = $services->getPermissionManager();
		$creator = TranslateUserManager::getUser();
		$guard = $permissionManager->addTemporaryUserRights( $creator, 'createaccount' );

		$authManager = $services->getAuthManager();
		$reqs = $authManager->getAuthenticationRequests( AuthManager::ACTION_CREATE );
		$reqs = AuthenticationRequest::loadRequestsFromSubmission( $reqs, $data );
		$res = $authManager->beginAccountCreation( $creator, $reqs, 'null:' );

		ScopedCallback::consume( $guard );

		switch ( $res->status ) {
			case AuthenticationResponse::PASS:
				break;
			case AuthenticationResponse::FAIL:
				// Unless things are misconfigured, this will handle errors such as username taken,
				// invalid user name or too short password. The WebAPI is prechecking these to
				// provide nicer error messages.
				$reason = $res->message->inLanguage( 'en' )->useDatabase( false )->text();
				throw new MWException( "Account creation failed: $reason" );
			default:
				// A provider requested further user input. Abort but clean up first if it was a
				// secondary provider (in which case the user was created).
				if ( $user->getId() ) {
					self::deleteUser( $user, 'force' );
				}

				throw new MWException(
					'AuthManager does not support such simplified account creation'
				);
		}

		// group-translate-sandboxed group-translate-sandboxed-member
		$services->getUserGroupManager()->addUserToGroup( $user, 'translate-sandboxed' );

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
		$actorId = $user->getActorId();

		if ( $force !== 'force' && !self::isSandboxed( $user ) ) {
			throw new MWException( 'Not a sandboxed user' );
		}

		// Delete from database
		$dbw = wfGetDB( DB_PRIMARY );
		$dbw->delete( 'user', [ 'user_id' => $uid ], __METHOD__ );
		$dbw->delete( 'user_groups', [ 'ug_user' => $uid ], __METHOD__ );
		$dbw->delete( 'user_properties', [ 'up_user' => $uid ], __METHOD__ );

		if ( version_compare( MW_VERSION, '1.37', '>=' ) ) {
			MediaWikiServices::getInstance()->getActorStore()->deleteActor( $user, $dbw );
		} else {
			// MW < 1.37
			$dbw->delete( 'actor', [ 'actor_user' => $uid ], __METHOD__ );
			// In case we create an user with same name as was deleted during the same
			// request, we must also reset this cache or the User class will try to load
			// stuff for the old id, which is no longer present since we just deleted
			// the cache above. But it would have the side effect or overwriting all
			// member variables with null data. This used to manifest as a bug where
			// inserting a new user fails because the mName properpty is set to null,
			// which is then converted as the ip of the current user, and trying to
			// add that twice results in a name conflict. It was fun to debug.
			// @phan-suppress-next-line PhanUndeclaredStaticMethod
			User::resetIdByNameCache();
		}
		// Assume no joins are needed for logging or recentchanges
		$dbw->delete( 'logging', [ 'log_actor' => $actorId ], __METHOD__ );
		$dbw->delete( 'recentchanges', [ 'rc_actor' => $actorId ], __METHOD__ );

		// Update the site stats
		$statsUpdate = SiteStatsUpdate::factory( [ 'users' => -1 ] );
		$statsUpdate->doUpdate();

		// If someone tries to access still object still, they will get anon user
		// data.
		$user->clearInstanceCache( 'defaults' );

		// Nobody should access the user by id anymore, but in case they do, purge
		// the cache so they wont get stale data
		$user->invalidateCache();
	}

	/**
	 * Get all sandboxed users.
	 * @return UserArray List of users.
	 */
	public static function getUsers() {
		$dbw = TranslateUtils::getSafeReadDB();
		$userQuery = User::getQueryInfo();
		$tables = array_merge( $userQuery['tables'], [ 'user_groups' ] );
		$fields = $userQuery['fields'];
		$conds = [
			'ug_group' => 'translate-sandboxed',
		];
		$joins = [
			'user_groups' => [ 'JOIN', 'ug_user = user_id' ],
		] + $userQuery['joins'];

		$res = $dbw->select( $tables, $fields, $conds, __METHOD__, [], $joins );

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
			throw new MWException( 'Not a sandboxed user' );
		}

		$services = MediaWikiServices::getInstance();

		$userGroupManager = $services->getUserGroupManager();
		$userGroupManager->removeUserFromGroup( $user, 'translate-sandboxed' );

		if ( $wgTranslateSandboxPromotedGroup ) {
			$userGroupManager->addUserToGroup( $user, $wgTranslateSandboxPromotedGroup );
		}

		$userOptionsManager = $services->getUserOptionsManager();
		$userOptionsManager->setOption( $user, 'translate-sandbox-reminders', '' );
		$userOptionsManager->saveOptions( $user );
	}

	/**
	 * Sends a reminder to the user.
	 * @param User $sender
	 * @param User $target
	 * @param string $type 'reminder' or 'promotion'
	 * @throws MWException
	 * @since 2013.12
	 */
	public static function sendEmail( User $sender, User $target, $type ) {
		global $wgNoReplyAddress;

		$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();
		$targetLang = $userOptionsLookup->getOption( $target, 'language' );

		switch ( $type ) {
			case 'reminder':
				if ( !self::isSandboxed( $target ) ) {
					throw new MWException( 'Not a sandboxed user' );
				}

				$subjectMsg = 'tsb-reminder-title-generic';
				$bodyMsg = 'tsb-reminder-content-generic';
				$targetSpecialPage = 'TranslationStash';

				break;
			case 'promotion':
				$subjectMsg = 'tsb-email-promoted-subject';
				$bodyMsg = 'tsb-email-promoted-body';
				$targetSpecialPage = 'Translate';

				break;
			case 'rejection':
				$subjectMsg = 'tsb-email-rejected-subject';
				$bodyMsg = 'tsb-email-rejected-body';
				$targetSpecialPage = 'TwnMainPage';

				break;
			default:
				throw new MWException( "'$type' is an invalid type of translate sandbox email" );
		}

		$subject = wfMessage( $subjectMsg )->inLanguage( $targetLang )->text();
		$body = wfMessage(
			$bodyMsg,
			$target->getName(),
			SpecialPage::getTitleFor( $targetSpecialPage )->getCanonicalURL(),
			$sender->getName()
		)->inLanguage( $targetLang )->text();

		$params = [
			'user' => $target->getId(),
			'to' => MailAddress::newFromUser( $target ),
			'from' => MailAddress::newFromUser( $sender ),
			'replyto' => new MailAddress( $wgNoReplyAddress ),
			'subj' => $subject,
			'body' => $body,
			'emailType' => $type,
		];

		TranslateUtils::getJobQueueGroup()->push( TranslateSandboxEmailJob::newJob( $params ) );
	}

	/**
	 * Shortcut for checking if given user is in the sandbox.
	 * @param User $user
	 * @return bool
	 * @since 2013.06
	 */
	public static function isSandboxed( User $user ) {
		$userGroupManager = MediaWikiServices::getInstance()->getUserGroupManager();
		return in_array( 'translate-sandboxed', $userGroupManager->getUserGroups( $user ), true );
	}

	/**
	 * Hook: UserGetRights
	 * @param User $user
	 * @param array &$rights
	 * @return true
	 */
	public static function enforcePermissions( User $user, array &$rights ) {
		global $wgTranslateUseSandbox;

		if ( !$wgTranslateUseSandbox ) {
			return true;
		}

		if ( !self::isSandboxed( $user ) ) {
			return true;
		}

		// right-translate-sandboxaction action-translate-sandboxaction
		$rights = [
			'editmyoptions',
			'editmyprivateinfo',
			'read',
			'readapi',
			'translate-sandboxaction',
			'viewmyprivateinfo',
			'writeapi',
		];

		// Do not let other hooks add more actions
		return false;
	}

	/// Hook: onGetPreferences
	public static function onGetPreferences( $user, &$preferences ) {
		$preferences['translate-sandbox'] = $preferences['translate-sandbox-reminders'] =
			[ 'type' => 'api' ];

		return true;
	}

	/**
	 * Inclusion listing for certain API modules. See also enforcePermissions.
	 * Hook: ApiCheckCanExecute
	 * @param ApiBase $module
	 * @param User $user
	 * @param string &$message
	 * @return bool
	 */
	public static function onApiCheckCanExecute( ApiBase $module, User $user, &$message ) {
		$inclusionList = [
			// Obviously this is needed to get out of the sandbox
			'ApiTranslationStash',
			// Used by UniversalLanguageSelector for example
			'ApiOptions'
		];

		if ( self::isSandboxed( $user ) ) {
			$class = get_class( $module );
			if ( $module->isWriteMode() && !in_array( $class, $inclusionList, true ) ) {
				$message = ApiMessage::create( 'apierror-writeapidenied' );
				return false;
			}
		}

		return true;
	}
}
