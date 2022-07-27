<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorSandbox;

use ApiBase;
use ApiMain;
use CommentStoreComment;
use ContentHandler;
use FormatJson;
use ManualLogEntry;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserNameUtils;
use MediaWiki\User\UserOptionsLookup;
use MediaWiki\User\UserOptionsManager;
use MWException;
use Sanitizer;
use TranslateSandbox;
use User;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * WebAPI for the sandbox feature of Translate.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup API TranslateAPI
 */
class TranslatorSandboxActionApi extends ApiBase {
	/** @var UserFactory */
	private $userFactory;
	/** @var UserNameUtils */
	private $userNameUtils;
	/** @var UserOptionsManager */
	private $userOptionsManager;
	/** @var WikiPageFactory */
	private $wikiPageFactory;
	/** @var UserOptionsLookup */
	private $userOptionsLookup;
	/** @var ServiceOptions */
	private $options;

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
		ServiceOptions $options
	) {
		parent::__construct( $mainModule, $moduleName );
		$this->userFactory = $userFactory;
		$this->userNameUtils = $userNameUtils;
		$this->userOptionsManager = $userOptionsManager;
		$this->wikiPageFactory = $wikiPageFactory;
		$this->userOptionsLookup = $userOptionsLookup;
		$this->options = $options;
	}

	public function execute(): void {
		if ( !$this->options->get( 'TranslateUseSandbox' ) ) {
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
		if ( !$user->isValidPassword( $password ) ) {
			$this->dieWithError( 'apierror-translate-sandbox-invalidpassword', 'invalidpassword' );
		}

		$email = $params['email'];
		if ( !Sanitizer::validateEmail( $email ) ) {
			$this->dieWithError( 'invalidemailaddress', 'invalidemail' );
		}

		$user = TranslateSandbox::addUser( $username, $email, $password );
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
			$userpage = $user->getUserPage();

			TranslateSandbox::sendEmail( $this->getUser(), $user, 'rejection' );

			try {
				TranslateSandbox::deleteUser( $user );
			} catch ( MWException $e ) {
				$this->dieWithError(
					[ 'apierror-translate-sandbox-invalidparam', wfEscapeWikiText( $e->getMessage() ) ],
					'invalidparam'
				);
			}

			$logEntry = new ManualLogEntry( 'translatorsandbox', 'rejected' );
			$logEntry->setPerformer( $this->getUser() );
			$logEntry->setTarget( $userpage );
			$logid = $logEntry->insert();
			$logEntry->publish( $logid );
		}
	}

	private function doPromote(): void {
		$this->checkUserRightsAny( 'translate-sandboxmanage' );

		$params = $this->extractRequestParams();

		foreach ( $params['userid'] as $userId ) {
			$user = $this->userFactory->newFromId( $userId );

			try {
				TranslateSandbox::promoteUser( $user );
			} catch ( MWException $e ) {
				$this->dieWithError(
					[ 'apierror-translate-sandbox-invalidparam', wfEscapeWikiText( $e->getMessage() ) ],
					'invalidparam'
				);
			}

			TranslateSandbox::sendEmail( $this->getUser(), $user, 'promotion' );

			$logEntry = new ManualLogEntry( 'translatorsandbox', 'promoted' );
			$logEntry->setPerformer( $this->getUser() );
			$logEntry->setTarget( $user->getUserPage() );
			$logEntry->setParameters( [
				'4::userid' => $user->getId(),
			] );
			$logid = $logEntry->insert();
			$logEntry->publish( $logid );

			$this->createUserPage( $user );
		}
	}

	private function doRemind(): void {
		$params = $this->extractRequestParams();

		foreach ( $params['userid'] as $userId ) {
			$target = $this->userFactory->newFromId( $userId );

			try {
				TranslateSandbox::sendEmail( $this->getUser(), $target, 'reminder' );
			} catch ( MWException $e ) {
				$this->dieWithError(
					[ 'apierror-translate-sandbox-invalidparam', wfEscapeWikiText( $e->getMessage() ) ],
					'invalidparam'
				);
			}
		}
	}

	/** Create a user page for a user with a babel template based on the signup preferences. */
	private function createUserPage( User $user ): void {
		$userpage = $user->getUserPage();

		if ( $userpage->exists() ) {
			return;
		}

		$languagePrefs = FormatJson::decode(
			$this->userOptionsLookup->getOption( $user, 'translate-sandbox' ),
			true
		);
		$languages = implode( '|', $languagePrefs[ 'languages' ] ?? [] );
		$babeltext = "{{#babel:$languages}}";
		$summary = $this->msg( 'tsb-create-user-page' )->inContentLanguage()->text();

		$page = $this->wikiPageFactory->newFromTitle( $userpage );
		$content = ContentHandler::makeContent( $babeltext, $userpage );

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
			'token' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'username' => [ ParamValidator::PARAM_TYPE => 'string' ],
			'password' => [ ParamValidator::PARAM_TYPE => 'string' ],
			'email' => [ ParamValidator::PARAM_TYPE => 'string' ],
		];
	}
}
