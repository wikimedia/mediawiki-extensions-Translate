<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use JobQueueGroup;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Extension\Translate\Jobs\GenericTranslateJob;
use MediaWiki\Extension\Translate\MessageGroupProcessing\DeleteTranslatableBundleJob;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionStore;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\User\UserIdentity;
use MediaWiki\User\UserRigorOptions;
use MessageHandle;
use RecentChange;
use Title;
use User;

/**
 * Job for updating translation pages when translation or template changes.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup PageTranslation JobQueue
 */
class RenderTranslationPageJob extends GenericTranslateJob {
	public const ACTION_DELETE = 'delete';

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

		// We should not re-create the translation page if a translation unit is being deleted
		// because it is possible that the translation page may also be queued for deletion.
		// Hence, set the flag to EDIT_UPDATE and remove EDIT_NEW if its added
		if ( $this->isDeleteTrigger() ) {
			$flags = ( $flags | EDIT_UPDATE ) & ~EDIT_NEW;
		}

		// @todo FuzzyBot hack
		Hooks::$allowTargetEdit = true;

		if ( class_exists( CommentStoreComment::class ) ) {
			$commentStoreComment = CommentStoreComment::newUnsavedComment( $summary );
		} else {
			// MW < 1.40
			$commentStoreComment = \CommentStoreComment::newUnsavedComment( $summary );
		}

		// $percentageTranslated is modified by reference
		$content = $tpPage->getPageContent( $mwServices->getParser(), $percentageTranslated );
		if ( $percentageTranslated === 0 ) {
			// Page is not translated at all. It is possible that when the RenderTranslationPageJob was created
			// translations existed, but have since been deleted.
			if ( $translationPageTitle->exists() ) {
				$this->deletePageIfFuzzyBotEdited(
					$mwServices->getRevisionStore(),
					$mwServices->getJobQueueGroup(),
					$translationPageTitle
				);
			} else {
				$this->logInfo( 'No translations found; nothing to render.' );
			}
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

			if ( !$status->isGood() ) {
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

			$this->logInfo( 'Finished page edit operation' );
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

	/** @param UserIdentity|string $user */
	public function setUser( $user ): void {
		if ( $user instanceof UserIdentity ) {
			$this->params['user'] = $user->getName();
		} else {
			$this->params['user'] = $user;
		}
	}

	/** Get a user object for doing edits. */
	private function getUser(): User {
		$userFactory = MediaWikiServices::getInstance()->getUserFactory();
		return $userFactory->newFromName( $this->params['user'], UserRigorOptions::RIGOR_NONE );
	}

	private function isDeleteTrigger(): bool {
		$triggerAction = $this->params['triggerAction'] ?? null;
		return $triggerAction === self::ACTION_DELETE;
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

	/**
	 * Used on translation page that exist but have no translations. Checks if the translation page was only
	 * created or modified only by FuzzyBot, if so deletes it.
	 */
	private function deletePageIfFuzzyBotEdited(
		RevisionStore $revisionStore,
		JobQueueGroup $jobQueueGroup,
		Title $translationPageTitle
	): void {
		$fuzzyBot = FuzzyBot::getUser();
		$hasOnlyFuzzyBotAuthor = $this->hasOnlyFuzzyBotAsAuthor( $revisionStore, $translationPageTitle, $fuzzyBot );

		if ( $hasOnlyFuzzyBotAuthor ) {
			$translatablePageTitle = ( new MessageHandle( $translationPageTitle ) )->getTitleForBase();
			$isTranslationPage = true;

			$job = DeleteTranslatableBundleJob::newJob(
				$translationPageTitle,
				$translatablePageTitle->getPrefixedText(),
				TranslatablePage::class,
				$isTranslationPage,
				$fuzzyBot,
				wfMessage( 'pt-deletepage-lang-outdated-logreason' )->inContentLanguage()->text()
			);
			$jobQueueGroup->push( $job );

			$this->logInfo( 'Deleting translation page that had no translations' );
		} else {
			$this->logInfo( 'No translations found but translation page exists' );
		}
	}

	private function hasOnlyFuzzyBotAsAuthor(
		RevisionStore $revisionStore,
		Title $title,
		UserIdentity $fuzzyBot
	): bool {
		$pageAuthors = $revisionStore->getAuthorsBetween( $title->getId() );
		foreach ( $pageAuthors as $author ) {
			if ( !$author->equals( $fuzzyBot ) ) {
				return false;
			}
		}
		return true;
	}
}
