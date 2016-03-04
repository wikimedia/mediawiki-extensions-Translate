<?php
/**
 * API module for marking translations as reviewed
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * API module for marking translations as reviewed
 *
 * @ingroup API TranslateAPI
 */
class ApiTranslationReview extends ApiBase {
	protected static $right = 'translate-messagereview';

	public function execute() {
		if ( !$this->getUser()->isAllowed( self::$right ) ) {
			$this->dieUsage( 'Permission denied', 'permissiondenied' );
		}

		$params = $this->extractRequestParams();

		$revision = Revision::newFromId( $params['revision'] );
		if ( !$revision ) {
			$this->dieUsage( 'Invalid revision', 'invalidrevision' );
		}

		$error = self::getReviewBlockers( $this->getUser(), $revision );
		switch ( $error ) {
			case '':
				// Everything is okay
				break;
			case 'permissiondenied':
				$this->dieUsage( 'Permission denied', $error );
				break; // Unreachable, but throws off code analyzer.
			case 'blocked':
				$this->dieUsage( 'You have been blocked', $error );
				break; // Unreachable, but throws off code analyzer.
			case 'unknownmessage':
				$this->dieUsage( 'Unknown message', $error );
				break; // Unreachable, but throws off code analyzer.
			case 'owntranslation':
				$this->dieUsage( 'Cannot review own translations', $error );
				break; // Unreachable, but throws off code analyzer.
			case 'fuzzymessage':
				$this->dieUsage( 'Cannot review fuzzy translations', $error );
				break; // Unreachable, but throws off code analyzer.
			default:
				$this->dieUsage( 'Unknown error', $error );
		}

		$ok = self::doReview( $this->getUser(), $revision );
		if ( !$ok ) {
			$this->setWarning( 'Already marked as reviewed by you' );
		}

		$output = array( 'review' => array(
			'title' => $revision->getTitle()->getPrefixedText(),
			'pageid' => $revision->getPage(),
			'revision' => $revision->getId()
		) );

		$this->getResult()->addValue( null, $this->getModuleName(), $output );
	}

	/**
	 * Executes the real stuff. No checks done!
	 * @param User $user
	 * @param Revision $revision
	 * @param null|string $comment
	 * @return Bool, whether the action was recorded.
	 */
	public static function doReview( User $user, Revision $revision, $comment = null ) {
		$dbw = wfGetDB( DB_MASTER );
		$table = 'translate_reviews';
		$row = array(
			'trr_user' => $user->getId(),
			'trr_page' => $revision->getPage(),
			'trr_revision' => $revision->getId(),
		);
		$options = array( 'IGNORE' );
		$dbw->insert( $table, $row, __METHOD__, $options );

		if ( !$dbw->affectedRows() ) {
			return false;
		}

		$title = $revision->getTitle();

		$entry = new ManualLogEntry( 'translationreview', 'message' );
		$entry->setPerformer( $user );
		$entry->setTarget( $title );
		$entry->setComment( $comment );
		$entry->setParameters( array(
			'4::revision' => $revision->getId(),
		) );

		$logid = $entry->insert();
		$entry->publish( $logid );

		$handle = new MessageHandle( $title );
		Hooks::run( 'TranslateEventTranslationReview', array( $handle ) );

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
		return array(
			'revision' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
			),
			'token' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	protected function getExamplesMessages() {
		return array(
			'action=translationreview&revision=1&token=foo'
				=> 'apihelp-translationreview-example-1',
		);
	}
}
