<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use ApiBase;
use ApiMain;
use ManualLogEntry;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\RevisionRecord;
use MessageHandle;
use Status;
use TitleFormatter;
use User;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * API module for marking translations as reviewed
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup API TranslateAPI
 */
class ReviewTranslationActionApi extends ApiBase {
	protected static $right = 'translate-messagereview';
	/** @var RevisionLookup */
	private $revisionLookup;
	/** @var TitleFormatter */
	private $titleFormatter;
	/** @var ILoadBalancer */
	private $loadBalancer;

	public function __construct(
		ApiMain $main,
		string $moduleName,
		RevisionLookup $revisionLookup,
		TitleFormatter $titleFormatter,
		ILoadBalancer $loadBalancer
	) {
		parent::__construct( $main, $moduleName );
		$this->revisionLookup = $revisionLookup;
		$this->titleFormatter = $titleFormatter;
		$this->loadBalancer = $loadBalancer;
	}

	public function execute() {
		$this->checkUserRightsAny( self::$right );

		$params = $this->extractRequestParams();

		$revRecord = $this->revisionLookup->getRevisionById( $params['revision'] );
		if ( !$revRecord ) {
			$this->dieWithError( [ 'apierror-nosuchrevid', $params['revision'] ], 'invalidrevision' );
		}

		$status = $this->getReviewBlockers( $this->getUser(), $revRecord );
		if ( !$status->isGood() ) {
			if ( $status->hasMessage( 'blocked' ) ) {
				$this->dieBlocked( $this->getUser()->getBlock() );
			} else {
				$this->dieStatus( $status );
			}
		}

		$ok = $this->doReview( $this->getUser(), $revRecord );
		if ( !$ok ) {
			$this->addWarning( 'apiwarn-translate-alreadyreviewedbyyou' );
		}

		$prefixedText = $this->titleFormatter->getPrefixedText( $revRecord->getPageAsLinkTarget() );
		$output = [ 'review' => [
			'title' => $prefixedText,
			'pageid' => $revRecord->getPageId(),
			'revision' => $revRecord->getId()
		] ];

		$this->getResult()->addValue( null, $this->getModuleName(), $output );
	}

	/**
	 * Executes the real stuff. No checks done!
	 * @return bool whether the action was recorded.
	 */
	private function doReview( User $user, RevisionRecord $revRecord ): bool {
		$dbw = $this->loadBalancer->getConnection( DB_PRIMARY );
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
		$entry->setParameters( [
			'4::revision' => $revRecord->getId(),
		] );

		$logid = $entry->insert();
		$entry->publish( $logid );

		$handle = new MessageHandle( $title );
		$this->getHookContainer()->run( 'TranslateEventTranslationReview', [ $handle ] );

		return true;
	}

	/**
	 * Validates review action by checking permissions and other things.
	 * @return Status Contains error key that describes the review blocker.
	 */
	private function getReviewBlockers( User $user, RevisionRecord $revRecord ): Status {
		if ( !$user->isAllowed( self::$right ) ) {
			return Status::newFatal( 'apierror-permissiondenied-generic' );
		}

		if ( $user->getBlock() ) {
			return Status::newFatal( 'blocked' );
		}

		$title = $revRecord->getPageAsLinkTarget();
		$handle = new MessageHandle( $title );
		if ( !$handle->isValid() ) {
			return Status::newFatal( 'apierror-translate-unknownmessage' );
		}

		if ( $user->equals( $revRecord->getUser() ) ) {
			return Status::newFatal( 'apierror-translate-owntranslation' );
		}

		if ( $handle->isFuzzy() ) {
			return Status::newFatal( 'apierror-translate-fuzzymessage' );
		}

		return Status::newGood();
	}

	public function isWriteMode(): bool {
		return true;
	}

	public function needsToken(): string {
		return 'csrf';
	}

	protected function getAllowedParams(): array {
		return [
			'revision' => [
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'token' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
		];
	}

	protected function getExamplesMessages(): array {
		return [
			'action=translationreview&revision=1&token=foo'
				=> 'apihelp-translationreview-example-1',
		];
	}
}
