<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorSandbox;

use InvalidArgumentException;
use JobQueueGroup;
use MailAddress;
use MediaWiki\Auth\AuthenticationRequest;
use MediaWiki\Auth\AuthenticationResponse;
use MediaWiki\Auth\AuthManager;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Deferred\SiteStatsUpdate;
use MediaWiki\Extension\Translate\HookRunner;
use MediaWiki\Extension\Translate\SystemUsers\TranslateUserManager;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\User\ActorStore;
use MediaWiki\User\Options\UserOptionsManager;
use MediaWiki\User\User;
use MediaWiki\User\UserArray;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserGroupManager;
use RuntimeException;
use UnexpectedValueException;
use Wikimedia\Rdbms\IConnectionProvider;
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
	];

	private UserFactory $userFactory;
	private IConnectionProvider $dbProvider;
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
		IConnectionProvider $dbProvider,
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
		$this->dbProvider = $dbProvider;
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
		$dbw = $this->dbProvider->getPrimaryDatabase();
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'user' )
			->where( [ 'user_id' => $uid ] )
			->caller( __METHOD__ )
			->execute();
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'user_groups' )
			->where( [ 'ug_user' => $uid ] )
			->caller( __METHOD__ )
			->execute();
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'user_properties' )
			->where( [ 'up_user' => $uid ] )
			->caller( __METHOD__ )
			->execute();

		$this->actorStore->deleteActor( $user, $dbw );

		// Assume no joins are needed for logging or recentchanges
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'logging' )
			->where( [ 'log_actor' => $actorId ] )
			->caller( __METHOD__ )
			->execute();
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'recentchanges' )
			->where( [ 'rc_actor' => $actorId ] )
			->caller( __METHOD__ )
			->execute();

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
		$dbr = Utilities::getSafeReadDB();
		$query = User::newQueryBuilder( $dbr );

		$res = $query->join( 'user_groups', null, 'ug_user = user_id' )
			->where( [ 'ug_group' => 'translate-sandboxed' ] )
			->caller( __METHOD__ )
			->fetchResultSet();

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

		$this->userOptionsManager->setOption( $user, 'translate-sandbox-reminders', null );
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
}
