<?php
/**
 * WebAPI for the sandbox feature of Translate.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * WebAPI for the sandbox feature of Translate.
 * @ingroup API TranslateAPI
 */
class ApiTranslateSandbox extends ApiBase {
	public function execute() {
		global $wgTranslateUseSandbox;
		if ( !$wgTranslateUseSandbox ) {
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
		}
	}

	protected function doCreate() {
		$params = $this->extractRequestParams();

		// Do validations
		foreach ( explode( '|', 'username|password|email' ) as $field ) {
			if ( !isset( $params[$field] ) ) {
				$this->dieWithError( [ 'apierror-missingparam', $field ], 'missingparam' );
			}
		}

		$username = $params['username'];
		if ( User::getCanonicalName( $username, 'creatable' ) === false ) {
			$this->dieWithError( 'noname', 'invalidusername' );
		}

		$user = User::newFromName( $username );
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

		$user->setOption( 'language', $this->getContext()->getLanguage()->getCode() );
		$user->saveSettings();

		$this->getResult()->addValue( null, $this->getModuleName(), $output );
	}

	protected function doDelete() {
		$this->checkUserRightsAny( 'translate-sandboxmanage' );

		$params = $this->extractRequestParams();

		foreach ( $params['userid'] as $user ) {
			$user = User::newFromId( $user );
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

	protected function doPromote() {
		$this->checkUserRightsAny( 'translate-sandboxmanage' );

		$params = $this->extractRequestParams();

		foreach ( $params['userid'] as $user ) {
			$user = User::newFromId( $user );

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

	protected function doRemind() {
		$params = $this->extractRequestParams();

		foreach ( $params['userid'] as $user ) {
			$user = User::newFromId( $user );

			try {
				TranslateSandbox::sendEmail( $this->getUser(), $user, 'reminder' );
			} catch ( MWException $e ) {
				$this->dieWithError(
					[ 'apierror-translate-sandbox-invalidparam', wfEscapeWikiText( $e->getMessage() ) ],
					'invalidparam'
				);
			}
		}
	}

	/**
	 * Create a user page for a user with a babel template based on the signup
	 * preferences.
	 *
	 * @param User $user
	 * @return Status|bool False when a user page already existed, or the Status
	 *   of the user page creation from WikiPage::doEditContent().
	 */
	protected function createUserPage( User $user ) {
		$userpage = $user->getUserPage();

		if ( $userpage->exists() ) {
			return false;
		}

		$languagePrefs = FormatJson::decode( $user->getOption( 'translate-sandbox' ) );
		$languages = implode( '|', $languagePrefs->languages );
		$babeltext = "{{#babel:$languages}}";
		$summary = $this->msg( 'tsb-create-user-page' )->inContentLanguage()->text();

		$page = WikiPage::factory( $userpage );
		$content = ContentHandler::makeContent( $babeltext, $userpage );

		$editResult = $page->doEditContent( $content, $summary, EDIT_NEW, false, $user );

		return $editResult;
	}

	public function isWriteMode() {
		return true;
	}

	public function needsToken() {
		return 'csrf';
	}

	public function getAllowedParams() {
		return [
			'do' => [
				ApiBase::PARAM_TYPE => [ 'create', 'delete', 'promote', 'remind' ],
				ApiBase::PARAM_REQUIRED => true,
			],
			'userid' => [
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => 0,
				ApiBase::PARAM_ISMULTI => true,
			],
			'token' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
			'username' => [ ApiBase::PARAM_TYPE => 'string' ],
			'password' => [ ApiBase::PARAM_TYPE => 'string' ],
			'email' => [ ApiBase::PARAM_TYPE => 'string' ],
		];
	}
}
