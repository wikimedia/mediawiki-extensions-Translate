<?php
/**
 * WebAPI for the sandbox feature of Translate.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * WebAPI for the sandbox feature of Translate.
 * @ingroup API TranslateAPI
 */
class ApiTranslateSandbox extends ApiBase {
	public function execute() {
		global $wgTranslateUseSandbox;
		if ( !$wgTranslateUseSandbox ) {
			$this->dieUsage( 'Sandbox feature is not in use', 'sandboxdisabled' );
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
				$this->dieUsage( "Missing parameter $field", 'missingparam' );
			}
		}

		$username = $params['username'];
		if ( User::getCanonicalName( $username, 'creatable' ) === false ) {
			$this->dieUsage( 'User name is not acceptable', 'invalidusername' );
		}

		$user = User::newFromName( $username );
		if ( $user->getId() !== 0 ) {
			$this->dieUsage( 'User name is in use', 'nonfreeusername' );
		}

		$password = $params['password'];
		if ( !$user->isValidPassword( $password ) ) {
			$this->dieUsage( 'Password is not acceptable', 'invalidpassword' );
		}

		$email = $params['email'];
		if ( !Sanitizer::validateEmail( $email ) ) {
			$this->dieUsage( 'Email is not acceptable', 'invalidemail' );
		}

		$user = TranslateSandbox::addUser( $username, $email, $password );
		$output = array( 'user' => array(
			'name' => $user->getName(),
			'id' => $user->getId(),
		) );

		$user->setOption( 'language', $this->getContext()->getLanguage()->getCode() );
		$user->saveSettings();

		$this->getResult()->addValue( null, $this->getModuleName(), $output );
	}

	protected function doDelete() {
		if ( !$this->getUser()->isAllowed( 'translate-sandboxmanage' ) ) {
			$this->dieUsage( 'Access denied', 'missingperms' );
		}

		$params = $this->extractRequestParams();

		foreach ( $params['userid'] as $user ) {
			$user = User::newFromId( $user );
			$userpage = $user->getUserPage();

			TranslateSandbox::sendEmail( $this->getUser(), $user, 'rejection' );

			try {
				TranslateSandbox::deleteUser( $user );
			} catch ( MWException $e ) {
				$this->dieUsage( $e->getMessage(), 'invalidparam' );
			}

			$logEntry = new ManualLogEntry( 'translatorsandbox', 'rejected' );
			$logEntry->setPerformer( $this->getUser() );
			$logEntry->setTarget( $userpage );
			$logid = $logEntry->insert();
			$logEntry->publish( $logid );
		}
	}

	protected function doPromote() {
		if ( !$this->getUser()->isAllowed( 'translate-sandboxmanage' ) ) {
			$this->dieUsage( 'Access denied', 'missingperms' );
		}

		$params = $this->extractRequestParams();

		foreach ( $params['userid'] as $user ) {
			$user = User::newFromId( $user );

			try {
				TranslateSandbox::promoteUser( $user );
			} catch ( MWException $e ) {
				$this->dieUsage( $e->getMessage(), 'invalidparam' );
			}

			TranslateSandbox::sendEmail( $this->getUser(), $user, 'promotion' );

			$logEntry = new ManualLogEntry( 'translatorsandbox', 'promoted' );
			$logEntry->setPerformer( $this->getUser() );
			$logEntry->setTarget( $user->getUserPage() );
			$logEntry->setParameters( array(
				'4::userid' => $user->getId(),
			) );
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
				$this->dieUsage( $e->getMessage(), 'invalidparam' );
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
		return array(
			'do' => array(
				ApiBase::PARAM_TYPE => array( 'create', 'delete', 'promote', 'remind' ),
				ApiBase::PARAM_REQUIRED => true,
			),
			'userid' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => 0,
				ApiBase::PARAM_ISMULTI => true,
			),
			'token' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
			'username' => array( ApiBase::PARAM_TYPE => 'string' ),
			'password' => array( ApiBase::PARAM_TYPE => 'string' ),
			'email' => array( ApiBase::PARAM_TYPE => 'string' ),
		);
	}
}
