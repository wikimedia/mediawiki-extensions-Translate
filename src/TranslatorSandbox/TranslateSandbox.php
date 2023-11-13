<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorSandbox;

use ApiBase;
use ApiMessage;
use InvalidArgumentException;
use JobQueueGroup;
use MailAddress;
use MediaWiki\Auth\AuthenticationRequest;
use MediaWiki\Auth\AuthenticationResponse;
use MediaWiki\Auth\AuthManager;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\Translate\HookRunner;
use MediaWiki\Extension\Translate\SystemUsers\TranslateUserManager;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\User\ActorStore;
use MediaWiki\User\User;
use MediaWiki\User\UserArray;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserGroupManager;
use MediaWiki\User\UserOptionsManager;
use RuntimeException;
use SiteStatsUpdate;
use UnexpectedValueException;
use Wikimedia\Rdbms\ILoadBalancer;
use Wikimedia\ScopedCallback;

/**
 * Utility class for the sandbox feature of Translate. Do not try this yourself. This code makes a
 * lot of assumptions about what happens to the user account.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class TranslateSandbox {
	public const CONSTRUCTOR_OPTIONS = [
		'EmergencyContact',
		'TranslateSandboxPromotedGroup',
		'TranslateUseSandbox',
	];

	private UserFactory $userFactory;
	private ILoadBalancer $loadBalancer;
	private PermissionManager $permissionManager;
	private AuthManager $authManager;
	private UserGroupManager $userGroupManager;
	private ActorStore $actorStore;
	private UserOptionsManager $userOptionsManager;
	private JobQueueGroup $jobQueueGroup;
	private HookRunner $hookRunner;
	private ServiceOptions $options;

	public function __construct(
		UserFactory $userFactory,
		ILoadBalancer $loadBalancer,
		PermissionManager $permissionManager,
		AuthManager $authManager,
		UserGroupManager $userGroupManager,
		ActorStore $actorStore,
		UserOptionsManager $userOptionsManager,
		JobQueueGroup $jobQueueGroup,
		HookRunner $hookRunner,
		ServiceOptions $options
	) {
		$this->userFactory = $userFactory;
		$this->loadBalancer = $loadBalancer;
		$this->permissionManager = $permissionManager;
		$this->authManager = $authManager;
		$this->userGroupManager = $userGroupManager;
		$this->actorStore = $actorStore;
		$this->userOptionsManager = $userOptionsManager;
		$this->jobQueueGroup = $jobQueueGroup;
		$this->hookRunner = $hookRunner;
		$options->assertRequiredOptions( self::CONSTRUCTOR_OPTIONS );
		$this->options = $options;
	}

	/**
	 * Custom exception code used when user creation fails in order to differentiate between
	 * other exceptions that might occur.
	 */
	public const USER_CREATION_FAILURE = 56739;

	/** Adds a new user without doing much validation. */
	public function addUser( string $name, string $email, string $password ): User {
		$user = $this->userFactory->newFromName( $name, UserFactory::RIGOR_CREATABLE );

		if ( !$user ) {
			throw new InvalidArgumentException( 'Invalid user name' );
		}

		$data = [
			'username' => $user->getName(),
			'password' => $password,
			'retype' => $password,
			'email' => $email,
			'realname' => '',
		];

		$creator = TranslateUserManager::getUser();
		$guard = $this->permissionManager->addTemporaryUserRights( $creator, 'createaccount' );

		$reqs = $this->authManager->getAuthenticationRequests( AuthManager::ACTION_CREATE );
		$reqs = AuthenticationRequest::loadRequestsFromSubmission( $reqs, $data );
		$res = $this->authManager->beginAccountCreation( $creator, $reqs, 'null:' );

		ScopedCallback::consume( $guard );

		switch ( $res->status ) {
			case AuthenticationResponse::PASS:
				break;
			case AuthenticationResponse::FAIL:
				// Unless things are misconfigured, this will handle errors such as username taken,
				// invalid user name or too short password. The WebAPI is prechecking these to
				// provide nicer error messages.
				$reason = $res->message->inLanguage( 'en' )->useDatabase( false )->text();
				throw new RuntimeException(
					"Account creation failed: $reason",
					self::USER_CREATION_FAILURE
				);
			default:
				// A provider requested further user input. Abort but clean up first if it was a
				// secondary provider (in which case the user was created).
				if ( $user->getId() ) {
					$this->deleteUser( $user, 'force' );
				}

				throw new RuntimeException(
					'AuthManager does not support such simplified account creation'
				);
		}

		// group-translate-sandboxed group-translate-sandboxed-member
		$this->userGroupManager->addUserToGroup( $user, 'translate-sandboxed' );

		return $user;
	}

	/**
	 * Deletes a sandboxed user without doing much validation.
	 *
	 * @param User $user
	 * @param string $force If set to 'force' will skip the little validation we have.
	 * @throws UserNotSandboxedException
	 */
	public function deleteUser( User $user, string $force = '' ): void {
		$uid = $user->getId();
		$actorId = $user->getActorId();

		if ( $force !== 'force' && !self::isSandboxed( $user ) ) {
			throw new UserNotSandboxedException();
		}

		// Delete from database
		$dbw = $this->loadBalancer->getConnection( DB_PRIMARY );
		$dbw->delete( 'user', [ 'user_id' => $uid ], __METHOD__ );
		$dbw->delete( 'user_groups', [ 'ug_user' => $uid ], __METHOD__ );
		$dbw->delete( 'user_properties', [ 'up_user' => $uid ], __METHOD__ );

		$this->actorStore->deleteActor( $user, $dbw );

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

	/** Get all sandboxed users. */
	public function getUsers(): UserArray {
		$dbw = Utilities::getSafeReadDB();
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
	 * @throws UserNotSandboxedException
	 */
	public function promoteUser( User $user ): void {
		$translateSandboxPromotedGroup = $this->options->get( 'TranslateSandboxPromotedGroup' );

		if ( !self::isSandboxed( $user ) ) {
			throw new UserNotSandboxedException();
		}

		$this->userGroupManager->removeUserFromGroup( $user, 'translate-sandboxed' );
		if ( $translateSandboxPromotedGroup ) {
			$this->userGroupManager->addUserToGroup( $user, $translateSandboxPromotedGroup );
		}

		$this->userOptionsManager->setOption( $user, 'translate-sandbox-reminders', '' );
		$this->userOptionsManager->saveOptions( $user );

		$this->hookRunner->onTranslate_TranslatorSandbox_UserPromoted( $user );
	}

	/**
	 * Sends a reminder to the user.
	 * @param User $sender
	 * @param User $target
	 * @param string $type 'reminder' or 'promotion'
	 * @throws UserNotSandboxedException
	 */
	public function sendEmail( User $sender, User $target, string $type ): void {
		$emergencyContact = $this->options->get( 'EmergencyContact' );

		$targetLang = $this->userOptionsManager->getOption( $target, 'language' );

		switch ( $type ) {
			case 'reminder':
				if ( !self::isSandboxed( $target ) ) {
					throw new UserNotSandboxedException();
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
				throw new UnexpectedValueException( "'$type' is an invalid type of translate sandbox email" );
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
			'from' => new MailAddress( $emergencyContact ),
			'replyto' => new MailAddress( $emergencyContact ),
			'subj' => $subject,
			'body' => $body,
			'emailType' => $type,
		];

		$reminders = $this->userOptionsManager->getOption( $target, 'translate-sandbox-reminders' );
		$reminders = $reminders ? explode( '|', $reminders ) : [];
		$reminders[] = wfTimestamp();

		$this->userOptionsManager->setOption( $target, 'translate-sandbox-reminders', implode( '|', $reminders ) );
		$this->userOptionsManager->saveOptions( $target );

		$this->jobQueueGroup->push( TranslateSandboxEmailJob::newJob( $params ) );
	}

	/** Shortcut for checking if given user is in the sandbox. */
	public static function isSandboxed( User $user ): bool {
		$userGroupManager = MediaWikiServices::getInstance()->getUserGroupManager();
		return in_array( 'translate-sandboxed', $userGroupManager->getUserGroups( $user ), true );
	}

	/**
	 * Hook: UserGetRights
	 * @param User $user
	 * @param array &$rights
	 * @return bool
	 */
	public static function enforcePermissions( User $user, array &$rights ): bool {
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

	/** Hook: onGetPreferences */
	public static function onGetPreferences( User $user, array &$preferences ): bool {
		$preferences['translate-sandbox'] = $preferences['translate-sandbox-reminders'] =
			[ 'type' => 'api' ];

		return true;
	}

	/**
	 * Inclusion listing for certain API modules. See also enforcePermissions.
	 * Hook: ApiCheckCanExecute
	 */
	public static function onApiCheckCanExecute( ApiBase $module, User $user, string &$message ): bool {
		$inclusionList = [
			// Obviously this is needed to get out of the sandbox
			TranslationStashActionApi::class,
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
