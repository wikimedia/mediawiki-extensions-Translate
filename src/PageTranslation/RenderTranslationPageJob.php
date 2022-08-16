<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use CommentStoreComment;
use MediaWiki\Extension\Translate\Jobs\GenericTranslateJob;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\User\UserIdentity;
use MediaWiki\User\UserRigorOptions;
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
		$title = $this->title;

		$tpPage = TranslatablePage::getTranslationPageFromTitle( $title );
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
		// Hence set the flag to EDIT_UPDATE and remove EDIT_NEW if its added
		if ( $this->isDeleteTrigger() ) {
			$flags = ( $flags | EDIT_UPDATE ) & ~EDIT_NEW;
		}

		// @todo FuzzyBot hack
		Hooks::$allowTargetEdit = true;
		$commentStoreComment = CommentStoreComment::newUnsavedComment( $summary );
		$content = $tpPage->getPageContent();

		$pageUpdater = $mwServices->getWikiPageFactory()
			->newFromTitle( $title )
			->newPageUpdater( $user );
		$pageUpdater->setContent( SlotRecord::MAIN, $content );

		if ( $user->authorizeWrite( 'autopatrol', $title ) ) {
			$pageUpdater->setRcPatrolStatus( RecentChange::PRC_AUTOPATROLLED );
		}

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
}
