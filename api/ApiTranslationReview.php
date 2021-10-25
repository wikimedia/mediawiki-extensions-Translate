<?php
/**
 * API module for marking translations as reviewed
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;

/**
 * API module for marking translations as reviewed
 *
 * @ingroup API TranslateAPI
 */
class ApiTranslationReview extends ApiBase {
	protected static $right = 'translate-messagereview';

	public function execute() {
		$this->checkUserRightsAny( self::$right );

		$params = $this->extractRequestParams();

		$revRecord = MediaWikiServices::getInstance()
			->getRevisionLookup()
			->getRevisionById( $params['revision'] );
		if ( !$revRecord ) {
			$this->dieWithError( [ 'apierror-nosuchrevid', $params['revision'] ], 'invalidrevision' );
		}

		$error = self::getReviewBlockers( $this->getUser(), $revRecord );
		switch ( $error ) {
			case '':
				// Everything is okay
				break;
			case 'permissiondenied':
				$this->dieWithError( 'apierror-permissiondenied-generic', 'permissiondenied' );
				// dieWithError prevents continuation
			case 'blocked':
				$this->dieBlocked( $this->getUser()->getBlock() );
				// dieBlocked prevents continuation
			case 'unknownmessage':
				$this->dieWithError( 'apierror-translate-unknownmessage', $error );
				// dieWithError prevents continuation
			case 'owntranslation':
				$this->dieWithError( 'apierror-translate-owntranslation', $error );
				// dieWithError prevents continuation
			case 'fuzzymessage':
				$this->dieWithError( 'apierror-translate-fuzzymessage', $error );
				// dieWithError prevents continuation
			default:
				$this->dieWithError( [ 'apierror-unknownerror', $error ], $error );
		}

		$ok = self::doReview( $this->getUser(), $revRecord );
		if ( !$ok ) {
			$this->addWarning( 'apiwarn-translate-alreadyreviewedbyyou' );
		}

		$prefixedText = MediaWikiServices::getInstance()
			->getTitleFormatter()
			->getPrefixedText( $revRecord->getPageAsLinkTarget() );
		$output = [ 'review' => [
			'title' => $prefixedText,
			'pageid' => $revRecord->getPageId(),
			'revision' => $revRecord->getId()
		] ];

		$this->getResult()->addValue( null, $this->getModuleName(), $output );
	}

	/**
	 * Executes the real stuff. No checks done!
	 * @param User $user
	 * @param RevisionRecord $revRecord
	 * @param null|string $comment
	 * @return bool whether the action was recorded.
	 */
	public static function doReview( User $user, RevisionRecord $revRecord, $comment = null ) {
		$dbw = wfGetDB( DB_PRIMARY );
		$table = 'translate_reviews';
		$row = [
			'trr_user' => $user->getId(),
			'trr_page' => $revRecord->getPageId(),
			'trr_revision' => $revRecord->getId(),
		];
		$options = [ 'IGNORE' ];
		$dbw->insert( $table, $row, __METHOD__, $options );

		if ( !$dbw->affectedRows() ) {
			return false;
		}

		$title = $revRecord->getPageAsLinkTarget();

		$entry = new ManualLogEntry( 'translationreview', 'message' );
		$entry->setPerformer( $user );
		$entry->setTarget( $title );
		$entry->setComment( $comment );
		$entry->setParameters( [
			'4::revision' => $revRecord->getId(),
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
	 * @param RevisionRecord $revRecord
	 * @return string Error key or empty string if review is allowed.
	 * @since 2012-09-24
	 */
	public static function getReviewBlockers( User $user, RevisionRecord $revRecord ) {
		if ( !$user->isAllowed( self::$right ) ) {
			return 'permissiondenied';
		}

		if ( $user->getBlock() ) {
			return 'blocked';
		}

		$title = $revRecord->getPageAsLinkTarget();
		$handle = new MessageHandle( $title );
		if ( !$handle->isValid() ) {
			return 'unknownmessage';
		}

		if ( $user->equals( $revRecord->getUser() ) ) {
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

	protected function getAllowedParams() {
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
