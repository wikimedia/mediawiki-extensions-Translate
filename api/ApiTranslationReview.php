<?php
/**
 * API module for marking translations as reviewed
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2011-2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * API module for marking translations as reviewed
 *
 * @ingroup API TranslateAPI
 */
class ApiTranslationReview extends ApiBase {
	protected static $right = 'translate-messagereview';
	protected static $salt = 'translate-messagereview';

	public function execute() {
		$params = $this->extractRequestParams();
		$revision = Revision::newFromId( $params['revision'] );

		if ( !$revision ) {
			$this->dieUsage( 'Invalid revision', 'invalidrevision' );
		}

		$error = self::getReviewBlockers( $this->getUser(), $handle, $revision );
		switch ( $error ) {
		case '':
			// Everything is okay
			break;
		case 'permissiondenied':
			$this-dieUsage( 'Permission denied', $error );
		case 'unknownmessage':
			$this->dieUsage( 'Unknown message', $error );
		case 'owntranslation':
			$this->dieUsage( 'Cannot review own translations', $error );
		case 'fuzzymessage':
			$this->dieUsage( 'Cannot review fuzzy translations', $error );
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
	 * Records a translation review action in the database. Does not
	 * do any checks.
	 * @return bool True if review was recorder, false if not.
	 * @since 2012-09-24
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
		} else {
			$title = $revision->getTitle();

			$logger = new LogPage( 'translationreview' );
			$params = array( $revision->getId() );
			$logger->addEntry( 'message', $title, $comment, $params, $user );

			$handle = new MessageHandle( $title );
			MessageGroupStats::clear( $handle );
			return true;
		}
	}

	/**
	 * Validates review action by checking permissions and other things.
	 * @return string Error key or empty string if review is allowed.
	 * @since 2012-09-24
	 */
	public static function getReviewBlockers( User $user, Revision $revision ) {
		if ( !$user->isallowed( self::$right ) ) {
			return 'permissiondenied';
		}

		$title = $revision->getTitle();
		$handle = new MessageHandle( $title );
		if ( !$handle->isValid() ) {
			return 'unknownmessage';
		}

		if ( $revision->getUser() == $user->getId() ) {
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
		return true;
	}

	public function getTokenSalt() {
		return self::$salt;
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

	public function getParamDescription() {
		$action = TranslateUtils::getTokenAction( 'translationreview' );
		return array(
			'revision' => 'The revision number to review',
			'token' => "A token previously acquired with $action",
		);
	}

	public function getDescription() {
		return 'Mark translations reviewed';
	}

	public function getPossibleErrors() {
		$right = self::$right;
		return array_merge( parent::getPossibleErrors(), array(
			array( 'code' => 'permissiondenied', 'info' => "You must have $right right" ),
			array( 'code' => 'unknownmessage', 'info' => 'Title $1 does not belong to a message group' ),
			array( 'code' => 'fuzzymessage', 'info' => 'Cannot review fuzzy translations' ),
			array( 'code' => 'owntranslation', 'info' => 'Cannot review own translations' ),
			array( 'code' => 'invalidrevision', 'info' => 'Revision $1 is invalid' ),
		) );
	}

	public function getExamples() {
		return array(
			'api.php?action=translationreview&revision=1&token=foo',
		);
	}

	public function getVersion() {
		return __CLASS__ . ': ' . TRANSLATE_VERSION;
	}

	public static function getToken() {
		global $wgUser;
		if ( !$wgUser->isAllowed( self::$right ) ) {
			return false;
		}

		return $wgUser->editToken( self::$salt );
	}

	public static function injectTokenFunction( &$list ) {
		$list['translationreview'] = array( __CLASS__, 'getToken' );
		return true; // Hooks must return bool
	}

	public static function getRight() {
		return self::$right;
	}

}
