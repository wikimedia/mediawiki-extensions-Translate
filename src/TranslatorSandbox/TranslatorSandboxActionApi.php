<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorSandbox;

use ManualLogEntry;
use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiMain;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Content\ContentHandler;
use MediaWiki\Json\FormatJson;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Parser\Sanitizer;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\User\Options\UserOptionsLookup;
use MediaWiki\User\Options\UserOptionsManager;
use MediaWiki\User\User;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserNameUtils;
use RuntimeException;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * WebAPI for the sandbox feature of Translate.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup API TranslateAPI
 */
class TranslatorSandboxActionApi extends ApiBase {
	private UserFactory $userFactory;
	private UserNameUtils $userNameUtils;
	private UserOptionsManager $userOptionsManager;
	private WikiPageFactory $wikiPageFactory;
	private UserOptionsLookup $userOptionsLookup;
	private TranslateSandbox $translateSandbox;
	private bool $isSandboxEnabled;
	public const CONSTRUCTOR_OPTIONS = [
		'TranslateUseSandbox',
	];

	public function __construct(
		ApiMain $mainModule,
		string $moduleName,
		UserFactory $userFactory,
		UserNameUtils $userNameUtils,
		UserOptionsManager $userOptionsManager,
		WikiPageFactory $wikiPageFactory,
		UserOptionsLookup $userOptionsLookup,
		TranslateSandbox $translateSandbox,
		ServiceOptions $options
	) {
		parent::__construct( $mainModule, $moduleName );
		$this->userFactory = $userFactory;
		$this->userNameUtils = $userNameUtils;
		$this->userOptionsManager = $userOptionsManager;
		$this->wikiPageFactory = $wikiPageFactory;
		$this->userOptionsLookup = $userOptionsLookup;
		$this->translateSandbox = $translateSandbox;
		$options->assertRequiredOptions( self::CONSTRUCTOR_OPTIONS );
		$this->isSandboxEnabled = $options->get( 'TranslateUseSandbox' );
	}

	public function execute(): void {
		if ( !$this->isSandboxEnabled ) {
			$this->dieWithError( 'apierror-translate-sandboxdisabled', 'sandboxdisabled' );
		}

		$params = $this->extractRequestParams();
		switch ( $params['do'] ) {
			case 'create':
				$this->doCreate();
				break;
			case 'delete':
				$this->doDelete();
				break;
			case 'promote':
				$this->doPromote();
				break;
			case 'remind':
				$this->doRemind();
				break;
			default:
				$this->dieWithError( [ 'apierror-badparameter', 'do' ] );
		}
	}

	private function doCreate(): void {
		$params = $this->extractRequestParams();

		// Do validations
		foreach ( explode( '|', 'username|password|email' ) as $field ) {
			if ( !isset( $params[$field] ) ) {
				$this->dieWithError( [ 'apierror-missingparam', $field ], 'missingparam' );
			}
		}

		$username = $params['username'];

		$canonicalName = $this->userNameUtils->getCanonical( $username, UserNameUtils::RIGOR_CREATABLE );

		if ( $canonicalName === false ) {
			$this->dieWithError( 'noname', 'invalidusername' );
		}

		$user = $this->userFactory->newFromName( $username );
		if ( $user->getId() !== 0 ) {
			$this->dieWithError( 'userexists', 'nonfreeusername' );
		}

		$password = $params['password'];
		$passwordValidityStatus = $user->checkPasswordValidity( $password );
		if ( !$passwordValidityStatus->isGood() ) {
			$this->dieStatus( $passwordValidityStatus );
		}

		$email = $params['email'];
		if ( !Sanitizer::validateEmail( $email ) ) {
			$this->dieWithError( 'invalidemailaddress', 'invalidemail' );
		}

		try {
			$user = $this->translateSandbox->addUser( $username, $email, $password );
		} catch ( RuntimeException $e ) {
			// Do not log this error as it might leak private information
			if ( $e->getCode() === TranslateSandbox::USER_CREATION_FAILURE ) {
				$this->dieWithError( 'apierror-translate-sandbox-user-add' );
			}

			throw $e;
		}

		$output = [ 'user' => [
			'name' => $user->getName(),
			'id' => $user->getId(),
		] ];

		$this->userOptionsManager->setOption( $user, 'language', $this->getContext()->getLanguage()->getCode() );
		$this->userOptionsManager->saveOptions( $user );

		$this->getResult()->addValue( null, $this->getModuleName(), $output );
	}

	private function doDelete(): void {
		$this->checkUserRightsAny( 'translate-sandboxmanage' );

		$params = $this->extractRequestParams();

		foreach ( $params['userid'] as $userId ) {
			$user = $this->userFactory->newFromId( $userId );
			$userPage = $user->getUserPage();

			$this->translateSandbox->sendEmail( $this->getUser(), $user, 'rejection' );

			try {
				$this->translateSandbox->deleteUser( $user );
			} catch ( UserNotSandboxedException $e ) {
				$this->dieWithError(
					[ 'apierror-translate-sandbox-invalidparam', wfEscapeWikiText( $e->getMessage() ) ],
					'invalidparam'
				);
			}

			$logEntry = new ManualLogEntry( 'translatorsandbox', 'rejected' );
			$logEntry->setPerformer( $this->getUser() );
			$logEntry->setTarget( $userPage );
			$logId = $logEntry->insert();
			$logEntry->publish( $logId );
		}
	}

	private function doPromote(): void {
		$this->checkUserRightsAny( 'translate-sandboxmanage' );

		$params = $this->extractRequestParams();

		foreach ( $params['userid'] as $userId ) {
			$user = $this->userFactory->newFromId( $userId );

			try {
				$this->translateSandbox->promoteUser( $user );
			} catch ( UserNotSandboxedException $e ) {
				$this->dieWithError(
					[ 'apierror-translate-sandbox-invalidparam', wfEscapeWikiText( $e->getMessage() ) ],
					'invalidparam'
				);
			}

			$this->translateSandbox->sendEmail( $this->getUser(), $user, 'promotion' );

			$logEntry = new ManualLogEntry( 'translatorsandbox', 'promoted' );
			$logEntry->setPerformer( $this->getUser() );
			$logEntry->setTarget( $user->getUserPage() );
			$logEntry->setParameters( [
				'4::userid' => $user->getId(),
			] );
			$logId = $logEntry->insert();
			$logEntry->publish( $logId );

			$this->createUserPage( $user );
		}
	}

	private function doRemind(): void {
		$params = $this->extractRequestParams();

		foreach ( $params['userid'] as $userId ) {
			$target = $this->userFactory->newFromId( $userId );

			try {
				$this->translateSandbox->sendEmail( $this->getUser(), $target, 'reminder' );
			} catch ( UserNotSandboxedException $e ) {
				$this->dieWithError(
					[ 'apierror-translate-sandbox-invalidparam', wfEscapeWikiText( $e->getMessage() ) ],
					'invalidparam'
				);
			}
		}
	}

	/** Create a user page for a user with a babel template based on the signup preferences. */
	private function createUserPage( User $user ): void {
		$userPage = $user->getUserPage();

		if ( $userPage->exists() ) {
			return;
		}

		$languagePreferences = FormatJson::decode(
			$this->userOptionsLookup->getOption( $user, 'translate-sandbox' ),
			true
		);
		$languages = implode( '|', $languagePreferences[ 'languages' ] ?? [] );
		$babelText = "{{#babel:$languages}}";
		$summary = $this->msg( 'tsb-create-user-page' )->inContentLanguage()->text();

		$page = $this->wikiPageFactory->newFromTitle( $userPage );
		$content = ContentHandler::makeContent( $babelText, $userPage );

		$page->newPageUpdater( $user )
			->setContent( SlotRecord::MAIN, $content )
			->saveRevision( CommentStoreComment::newUnsavedComment( trim( $summary ) ), EDIT_NEW );
	}

	public function isWriteMode(): bool {
		return true;
	}

	public function needsToken(): string {
		return 'csrf';
	}

	protected function getAllowedParams(): array {
		return [
			'do' => [
				ParamValidator::PARAM_TYPE => [ 'create', 'delete', 'promote', 'remind' ],
				ParamValidator::PARAM_REQUIRED => true,
			],
			'userid' => [
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_DEFAULT => 0,
				ParamValidator::PARAM_ISMULTI => true,
			],
			'username' => [ ParamValidator::PARAM_TYPE => 'string' ],
			'password' => [ ParamValidator::PARAM_TYPE => 'string' ],
			'email' => [ ParamValidator::PARAM_TYPE => 'string' ],
		];
	}
}
