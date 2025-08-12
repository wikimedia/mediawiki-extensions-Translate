<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use JobQueueGroup;
use MediaWiki\Category\Category;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\Translate\Jobs\GenericTranslateJob;
use MediaWiki\Extension\Translate\MessageGroupProcessing\DeleteTranslatableBundleJob;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionStore;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use MediaWiki\User\UserRigorOptions;
use RecentChange;
use Wikimedia\ScopedCallback;

/**
 * Job for updating translation pages when translation or template changes.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup PageTranslation JobQueue
 */
class RenderTranslationPageJob extends GenericTranslateJob {
	public const ACTION_DELETE = 'delete';
	public const ACTION_CATEGORIZATION = 'categorization';

	public static function newJob(
		Title $target,
		?string $triggerAction = null,
		?string $unitTitleText = null
	): self {
		$job = new self( $target, [ 'triggerAction' => $triggerAction, 'unitTitle' => $unitTitleText ] );
		$job->setUser( FuzzyBot::getUser() );
		$job->setFlags( EDIT_FORCE_BOT );
		$job->setSummary( wfMessage( 'tpt-render-summary' )->inContentLanguage()->text() );

		return $job;
	}

	public static function newNonPrioritizedJob(
		Title $target,
		?string $triggerAction = null,
		?string $unitTitleText = null
	): self {
		$job = self::newJob( $target, $triggerAction, $unitTitleText );
		$job->command = 'NonPrioritizedRenderTranslationPageJob';
		return $job;
	}

	public function __construct( Title $title, array $params = [] ) {
		parent::__construct( 'RenderTranslationPageJob', $title, $params );
		$this->removeDuplicates = true;
	}

	public function run(): bool {
		$this->logJobStart();
		$mwServices = MediaWikiServices::getInstance();
		// We may be doing double wait here if this job was spawned by TranslationUpdateJob
		$lb = $mwServices->getDBLoadBalancerFactory();
		if ( !$lb->waitForReplication() ) {
			$this->logWarning( 'Continuing despite replication lag' );
		}

		// Initialization
		$translationPageTitle = $this->title;

		$tpPage = TranslatablePage::getTranslationPageFromTitle( $translationPageTitle );
		if ( !$tpPage ) {
			$this->logError( 'Cannot render translation page!' );
			return false;
		}

		// Other stuff
		$user = $this->getUser();
		$summary = $this->getSummary();
		$flags = $this->getFlags();

		if ( isset( $this->params['session'] ) ) {
			$scope = RequestContext::importScopedSession( $this->params['session'] );
			$this->addTeardownCallback( static function () use ( &$scope ) {
				ScopedCallback::consume( $scope );
			} );
		}

		// We should not re-create the translation page if a translation unit is being deleted
		// because it is possible that the translation page may also be queued for deletion.
		// Hence, set the flag to EDIT_UPDATE and remove EDIT_NEW if its added
		if ( $this->isDeleteTrigger() ) {
			$flags = ( $flags | EDIT_UPDATE ) & ~EDIT_NEW;
		}

		// @todo FuzzyBot hack
		Hooks::$allowTargetEdit = true;

		$commentStoreComment = CommentStoreComment::newUnsavedComment( $summary );
		// $percentageTranslated is modified by reference
		$content = $tpPage->getPageContent( $mwServices->getParser(), $percentageTranslated );
		$translationPageTitleExists = $translationPageTitle->exists();
		if ( $this->isCategoryTrigger() ) {
			$isNonEmptyCategory = true;
		} elseif ( $translationPageTitle->inNamespace( NS_CATEGORY ) ) {
			$cat = Category::newFromTitle( $translationPageTitle );
			$isNonEmptyCategory = $cat->getMemberCount() > 0;
		} else {
			$isNonEmptyCategory = false;
		}
		if ( $percentageTranslated === 0 && !$translationPageTitleExists && !$isNonEmptyCategory ) {
			Hooks::$allowTargetEdit = false;
			$this->logInfo( 'No translations found and translation page does not exist. Nothing to do.' );
			return true;
		}

		if (
			$percentageTranslated === 0 &&
			$translationPageTitleExists &&
			$this->hasOnlyFuzzyBotAsAuthor( $mwServices->getRevisionStore(), $translationPageTitle ) &&
			!$isNonEmptyCategory
		) {
			$this->logInfo( 'Deleting translation page having no translations and modified only by Fuzzybot' );
			// Page is not translated at all but the translation page exists and has been only edited by FuzzyBot
			$this->deleteTranslationPage( $mwServices->getJobQueueGroup(), $translationPageTitle, FuzzyBot::getUser() );
		} else {
			$pageUpdater = $mwServices->getWikiPageFactory()
				->newFromTitle( $translationPageTitle )
				->newPageUpdater( $user );
			$pageUpdater->setContent( SlotRecord::MAIN, $content );

			if ( $user->authorizeWrite( 'autopatrol', $translationPageTitle ) ) {
				$pageUpdater->setRcPatrolStatus( RecentChange::PRC_AUTOPATROLLED );
			}

			$pageUpdater->addTag( 'translate-translation-pages' );
			$pageUpdater->saveRevision( $commentStoreComment, $flags );
			$status = $pageUpdater->getStatus();

			if ( !$status->isOK() ) {
				if ( $this->isDeleteTrigger() && $status->hasMessage( 'edit-gone-missing' ) ) {
					$this->logInfo( 'Translation page missing with delete trigger' );
				} else {
					$this->logError(
						'Error while editing content in page.',
						[
							'content' => $content->getTextForSummary(),
							'errors' => $status->getErrors()
						]
					);
				}
			}
		}

		Hooks::$allowTargetEdit = false;

		$this->logInfo( 'Finished TranslateRenderJob' );
		return true;
	}

	public function setFlags( int $flags ): void {
		$this->params['flags'] = $flags;
	}

	private function getFlags(): int {
		return $this->params['flags'];
	}

	public function setSummary( string $summary ): void {
		$this->params['summary'] = $summary;
	}

	/** @inheritDoc */
	public function getDeduplicationInfo(): array {
		$info = parent::getDeduplicationInfo();
		// Unit title is only passed for logging and should not be used for de-duplication
		unset( $info['params']['unitTitle'] );
		return $info;
	}

	private function getSummary(): string {
		return $this->params['summary'];
	}

	/**
	 * @param UserIdentity|string $user
	 * @param ?array $session
	 */
	public function setUser( $user, ?array $session = null ): void {
		if ( $user instanceof UserIdentity ) {
			$this->params['user'] = $user->getName();
		} else {
			$this->params['user'] = $user;
		}

		if ( $session ) {
			$this->params['session'] = $session;
		}
	}

	/** Get a user object for doing edits. */
	private function getUser(): User {
		$userFactory = MediaWikiServices::getInstance()->getUserFactory();
		return $userFactory->newFromName( $this->params['user'], UserRigorOptions::RIGOR_NONE );
	}

	private function isDeleteTrigger(): bool {
		return ( $this->params['triggerAction'] ?? null ) === self::ACTION_DELETE;
	}

	private function isCategoryTrigger(): bool {
		return ( $this->params['triggerAction'] ?? null ) === self::ACTION_CATEGORIZATION;
	}

	private function logJobStart(): void {
		$unitTitleText = $this->params['unitTitle'] ?? null;
		$logMessage = 'Starting TranslateRenderJob ';
		if ( $unitTitleText ) {
			$logMessage .= "trigged by $unitTitleText ";
		}

		if ( $this->isDeleteTrigger() ) {
			$logMessage .= '- [deletion] ';
		}

		$this->logInfo( trim( $logMessage ) );
	}

	private function deleteTranslationPage(
		JobQueueGroup $jobQueueGroup,
		Title $translationPageTitle,
		UserIdentity $performer
	): void {
		$translatablePageTitle = ( new MessageHandle( $translationPageTitle ) )->getTitleForBase();
		$isTranslationPage = true;

		$job = DeleteTranslatableBundleJob::newJob(
			$translationPageTitle,
			$translatablePageTitle->getPrefixedText(),
			TranslatablePage::class,
			$isTranslationPage,
			$performer,
			wfMessage( 'pt-deletepage-lang-outdated-logreason' )->inContentLanguage()->text(),
			$this->params['session'] ?? null
		);

		$jobQueueGroup->push( $job );
	}

	private function hasOnlyFuzzyBotAsAuthor( RevisionStore $revisionStore, Title $title ): bool {
		$fuzzyBot = FuzzyBot::getUser();
		$pageAuthors = $revisionStore->getAuthorsBetween( $title->getId() );
		foreach ( $pageAuthors as $author ) {
			if ( !$author->equals( $fuzzyBot ) ) {
				return false;
			}
		}
		return true;
	}
}
