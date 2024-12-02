<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use ManualLogEntry;
use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiMain;
use MediaWiki\Extension\Translate\HookRunner;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Status\Status;
use MediaWiki\Title\TitleFormatter;
use MediaWiki\User\User;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * API module for marking translations as reviewed
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup API TranslateAPI
 */
class ReviewTranslationActionApi extends ApiBase {
	/** @var string */
	protected static $right = 'translate-messagereview';
	private RevisionLookup $revisionLookup;
	private TitleFormatter $titleFormatter;
	private ILoadBalancer $loadBalancer;
	private HookRunner $hookRunner;

	public function __construct(
		ApiMain $main,
		string $moduleName,
		RevisionLookup $revisionLookup,
		TitleFormatter $titleFormatter,
		ILoadBalancer $loadBalancer,
		HookRunner $hookRunner
	) {
		parent::__construct( $main, $moduleName );
		$this->revisionLookup = $revisionLookup;
		$this->titleFormatter = $titleFormatter;
		$this->loadBalancer = $loadBalancer;
		$this->hookRunner = $hookRunner;
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
		$dbw->newInsertQueryBuilder()
			->insertInto( 'translate_reviews' )
			->ignore()
			->row( [
				'trr_user' => $user->getId(),
				'trr_page' => $revRecord->getPageId(),
				'trr_revision' => $revRecord->getId(),
			] )
			->caller( __METHOD__ )
			->execute();

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
		$this->hookRunner->onTranslateEventTranslationReview( $handle );

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
		];
	}

	protected function getExamplesMessages(): array {
		return [
			'action=translationreview&revision=1&token=foo'
				=> 'apihelp-translationreview-example-1',
		];
	}
}
