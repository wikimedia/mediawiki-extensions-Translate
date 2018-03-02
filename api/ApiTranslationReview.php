<?php
/**
 * API module for marking translations as reviewed
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * API module for marking translations as reviewed
 *
 * @ingroup API TranslateAPI
 */
class ApiTranslationReview extends ApiBase {
	protected static $right = 'translate-messagereview';

	public function execute() {
		if ( method_exists( $this, 'checkUserRightsAny' ) ) {
			$this->checkUserRightsAny( self::$right );
		} else {
			if ( !$this->getUser()->isAllowed( self::$right ) ) {
				$this->dieUsage( 'Permission denied', 'permissiondenied' );
			}
		}

		$params = $this->extractRequestParams();

		$revision = Revision::newFromId( $params['revision'] );
		if ( !$revision ) {
			if ( method_exists( $this, 'dieWithError' ) ) {
				$this->dieWithError( [ 'apierror-nosuchrevid', $params['revision'] ], 'invalidrevision' );
			} else {
				$this->dieUsage( 'Invalid revision', 'invalidrevision' );
			}
		}

		$error = self::getReviewBlockers( $this->getUser(), $revision );
		switch ( $error ) {
			case '':
				// Everything is okay
				break;
			case 'permissiondenied':
				if ( method_exists( $this, 'dieWithError' ) ) {
					$this->dieWithError( 'apierror-permissiondenied-generic', 'permissiondenied' );
				} else {
					$this->dieUsage( 'Permission denied', $error );
				}
				break; // Unreachable, but throws off code analyzer.
			case 'blocked':
				if ( method_exists( $this, 'dieBlocked' ) ) {
					$this->dieBlocked( $this->getUser()->getBlock() );
				} else {
					$this->dieUsage( 'You have been blocked', $error );
				}
				break; // Unreachable, but throws off code analyzer.
			case 'unknownmessage':
				if ( method_exists( $this, 'dieWithError' ) ) {
					$this->dieWithError( 'apierror-translate-unknownmessage', $error );
				} else {
					$this->dieUsage( 'Unknown message', $error );
				}
				break; // Unreachable, but throws off code analyzer.
			case 'owntranslation':
				if ( method_exists( $this, 'dieWithError' ) ) {
					$this->dieWithError( 'apierror-translate-owntranslation', $error );
				} else {
					$this->dieUsage( 'Cannot review own translations', $error );
				}
				break; // Unreachable, but throws off code analyzer.
			case 'fuzzymessage':
				if ( method_exists( $this, 'dieWithError' ) ) {
					$this->dieWithError( 'apierror-translate-fuzzymessage', $error );
				} else {
					$this->dieUsage( 'Cannot review fuzzy translations', $error );
				}
				break; // Unreachable, but throws off code analyzer.
			default:
				if ( method_exists( $this, 'dieWithError' ) ) {
					$this->dieWithError( [ 'apierror-unknownerror', $error ], $error );
				} else {
					$this->dieUsage( 'Unknown error', $error );
				}
		}

		$ok = self::doReview( $this->getUser(), $revision );
		if ( !$ok ) {
			if ( method_exists( $this, 'addWarning' ) ) {
				$this->addWarning( 'apiwarn-translate-alreadyreviewedbyyou' );
			} else {
				$this->setWarning( 'Already marked as reviewed by you' );
			}
		}

		$output = [ 'review' => [
			'title' => $revision->getTitle()->getPrefixedText(),
			'pageid' => $revision->getPage(),
			'revision' => $revision->getId()
		] ];

		$this->getResult()->addValue( null, $this->getModuleName(), $output );
	}

	/**
	 * Executes the real stuff. No checks done!
	 * @param User $user
	 * @param Revision $revision
	 * @param null|string $comment
	 * @return bool whether the action was recorded.
	 */
	public static function doReview( User $user, Revision $revision, $comment = null ) {
		$dbw = wfGetDB( DB_MASTER );
		$table = 'translate_reviews';
		$row = [
			'trr_user' => $user->getId(),
			'trr_page' => $revision->getPage(),
			'trr_revision' => $revision->getId(),
		];
		$options = [ 'IGNORE' ];
		$dbw->insert( $table, $row, __METHOD__, $options );

		if ( !$dbw->affectedRows() ) {
			return false;
		}

		$title = $revision->getTitle();

		$entry = new ManualLogEntry( 'translationreview', 'message' );
		$entry->setPerformer( $user );
		$entry->setTarget( $title );
		$entry->setComment( $comment );
		$entry->setParameters( [
			'4::revision' => $revision->getId(),
		] );

		$logid = $entry->insert();
		$entry->publish( $logid );

		$handle = new MessageHandle( $title );
		Hooks::run( 'TranslateEventTranslationReview', [ $handle ] );

		return true;
	}

	/**
	 * Validates review action by checking permissions and other things.
	 * @param User $user
	 * @param Revision $revision
	 * @return string Error key or empty string if review is allowed.
	 * @since 2012-09-24
	 */
	public static function getReviewBlockers( User $user, Revision $revision ) {
		if ( !$user->isAllowed( self::$right ) ) {
			return 'permissiondenied';
		}

		if ( $user->isBlocked() ) {
			return 'blocked';
		}

		$title = $revision->getTitle();
		$handle = new MessageHandle( $title );
		if ( !$handle->isValid() ) {
			return 'unknownmessage';
		}

		if ( $revision->getUser() === $user->getId() ) {
			return 'owntranslation';
		}

		if ( $handle->isFuzzy() ) {
			return 'fuzzymessage';
		}

		return '';
	}

	public function isWriteMode() {
		return true;
	}

	public function needsToken() {
		return 'csrf';
	}

	public function getAllowedParams() {
		return [
			'revision' => [
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
			],
			'token' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
		];
	}

	protected function getExamplesMessages() {
		return [
			'action=translationreview&revision=1&token=foo'
				=> 'apihelp-translationreview-example-1',
		];
	}
}
