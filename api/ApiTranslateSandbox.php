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
			$this->dieUsage( "User name is not acceptable", 'invalidusername' );
		}

		$user = User::newFromName( $username );
		if ( $user->getID() !== 0 ) {
			$this->dieUsage( "User name is in use", 'nonfreeusername' );
		}

		$password = $params['password'];
		if ( !$user->isValidPassword( $password ) ) {
			$this->dieUsage( "Password is not acceptable", 'invalidpassword' );
		}

		$email = $params['email'];
		if ( !Sanitizer::validateEmail( $email ) ) {
			$this->dieUsage( "Email is not acceptable", 'invalidemail' );
		}

		$user = TranslateSandbox::addUser( $username, $email, $password );
		$output = array( 'user' => array(
			'name' => $user->getName(),
			'id' => $user->getId(),
		) );

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

			$user->addNewUserLogEntry( 'create', $this->msg( 'tsb-promoted-from-sandbox' )->text() );
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

	public function mustBePosted() {
		return true;
	}

	public function isWriteMode() {
		return true;
	}

	public function needsToken() {
		return true;
	}

	public function getTokenSalt() {
		return 'sandbox';
	}

	public static function getToken() {
		// Who designed this?!?!?!
		$user = RequestContext::getMain()->getUser();
		return $user->getEditToken( 'sandbox' );
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

	public function getParamDescription() {
		$action = TranslateUtils::getTokenAction( 'translatesandbox' );

		return array(
			'do' => 'What to do',
			'userid' => 'User ids of the users being managed. Use 0 for creations.',
			'token' => "A token previously acquired with $action",
			'username' => 'Username when creating user',
			'password' => 'Password when creating user',
			'email' => 'Email when creating user',
		);
	}

	public function getDescription() {
		return 'Signup and manage sandboxed users';
	}

	public static function injectTokenFunction( &$list ) {
		$list['translatesandbox'] = array( __CLASS__, 'getToken' );

		return true;
	}

	// BC for MW 1.20
	public function getVersion() {
		return __CLASS__ . ': ' . TRANSLATE_VERSION;
	}
}
