<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use EmptyIterator;
use JobQueueGroup;
use LogicException;
use ManualLogEntry;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Content\ContentHandler;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupSubscription;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatablePageStore;
use MediaWiki\Extension\Translate\MessageLoading\MessageIndex;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;
use MediaWiki\Language\FormatterFactory;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Message\Message;
use MediaWiki\Page\PageRecord;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Status\Status;
use MediaWiki\Storage\PageUpdater;
use MediaWiki\Title\MalformedTitleException;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFormatter;
use MediaWiki\Title\TitleParser;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use MessageLocalizer;
use RecentChange;
use Wikimedia\Rdbms\IConnectionProvider;
use WikiPageMessageGroup;

/**
 * Service to mark/unmark pages from translation and perform related validations
 * @since 2023.10
 */
class TranslatablePageMarker {
	public const LATEST_SYNTAX_VERSION = '2';
	public const DEFAULT_SYNTAX_VERSION = '1';

	private IConnectionProvider $dbProvider;
	private JobQueueGroup $jobQueueGroup;
	private LinkRenderer $linkRenderer;
	private MessageGroups $messageGroups;
	private MessageIndex $messageIndex;
	private TitleFormatter $titleFormatter;
	private TitleParser $titleParser;
	private TranslatablePageParser $translatablePageParser;
	private TranslatablePageStore $translatablePageStore;
	private TranslatablePageStateStore $translatablePageStateStore;
	private TranslationUnitStoreFactory $translationUnitStoreFactory;
	private MessageGroupMetadata $messageGroupMetadata;
	private WikiPageFactory $wikiPageFactory;
	private TranslatablePageView $translatablePageView;
	private MessageGroupSubscription $messageGroupSubscription;
	private FormatterFactory $formatterFactory;

	public function __construct(
		IConnectionProvider $dbProvider,
		JobQueueGroup $jobQueueGroup,
		LinkRenderer $linkRenderer,
		MessageGroups $messageGroups,
		MessageIndex $messageIndex,
		TitleFormatter $titleFormatter,
		TitleParser $titleParser,
		TranslatablePageParser $translatablePageParser,
		TranslatablePageStore $translatablePageStore,
		TranslatablePageStateStore $translatablePageStateStore,
		TranslationUnitStoreFactory $translationUnitStoreFactory,
		MessageGroupMetadata $messageGroupMetadata,
		WikiPageFactory $wikiPageFactory,
		TranslatablePageView $translatablePageView,
		MessageGroupSubscription $messageGroupSubscription,
		FormatterFactory $formatterFactory
	) {
		$this->dbProvider = $dbProvider;
		$this->jobQueueGroup = $jobQueueGroup;
		$this->linkRenderer = $linkRenderer;
		$this->messageIndex = $messageIndex;
		$this->titleFormatter = $titleFormatter;
		$this->titleParser = $titleParser;
		$this->translatablePageParser = $translatablePageParser;
		$this->translatablePageStore = $translatablePageStore;
		$this->translatablePageStateStore = $translatablePageStateStore;
		$this->translationUnitStoreFactory = $translationUnitStoreFactory;
		$this->wikiPageFactory = $wikiPageFactory;
		$this->messageGroups = $messageGroups;
		$this->messageGroupMetadata = $messageGroupMetadata;
		$this->translatablePageView = $translatablePageView;
		$this->messageGroupSubscription = $messageGroupSubscription;
		$this->formatterFactory = $formatterFactory;
	}

	/**
	 * Remove a page from translation.
	 * @param TranslatablePage $page The page to remove from translation
	 * @param User $user The user performing the action
	 * @param MessageLocalizer $localizer
	 * @param bool $removeMarkup Whether to remove markup from the translation page
	 * @throws TranslatablePageMarkException If removing the markup from the translation page fails
	 */
	public function unmarkPage(
		TranslatablePage $page,
		User $user,
		MessageLocalizer $localizer,
		bool $removeMarkup
	): void {
		if ( $removeMarkup ) {
			$pageTitle = $page->getTitle();
			$content = ContentHandler::makeContent( $page->getStrippedSourcePageText(), $pageTitle );

			$wikiPage = $this->wikiPageFactory->newFromTitle( $pageTitle );
			$updater = $wikiPage->newPageUpdater( $user )
				->setContent( SlotRecord::MAIN, $content );
			$summary = CommentStoreComment::newUnsavedComment(
				Message::newFromKey( 'tpt-unlink-summary' )->inContentLanguage()->text()
			);

			if ( $user->authorizeWrite( 'autopatrol', $pageTitle ) ) {
				$updater->setRcPatrolStatus( RecentChange::PRC_AUTOPATROLLED );
			}
			$updater->saveRevision( $summary, EDIT_FORCE_BOT | EDIT_UPDATE );
			$this->throwIfEditFailed( $updater, $localizer );
		}

		$this->translatablePageStore->unmark( $page->getPageIdentity() );

		$entry = new ManualLogEntry( 'pagetranslation', 'unmark' );
		$entry->setPerformer( $user );
		$entry->setTarget( $page->getPageIdentity() );
		$logId = $entry->insert();
		$entry->publish( $logId );
	}

	/**
	 * Parse the given page and create a new MarkPageOperation with the page and the given revision
	 * if the revision is latest and that latest revision is ready to be marked.
	 * @param PageRecord $page
	 * @param ?int $revision Revision to use, or null to use the latest
	 *  revision of the given page (i.e. not do the latest revision check)
	 * @throws TranslatablePageMarkException If the revision was provided and was
	 *  non-latest, or if the latest revision of the page is not ready to be marked
	 * @throws ParsingFailure If the parse fails
	 */
	public function getMarkOperation(
		PageRecord $page,
		?int $revision,
		bool $validateUnitTitle
	): TranslatablePageMarkOperation {
		$latestRevID = $page->getLatest();
		if ( $revision === null ) {
			// Get the latest revision
			$revision = $latestRevID;
		}

		// This also catches the case where revision does not belong to the title
		if ( $revision !== $latestRevID ) {
			// We do want to notify the reviewer if the underlying page changes during review
			$link = $this->linkRenderer->makeKnownLink(
				$page,
				(string)$revision,
				[],
				[ 'oldid' => (string)$revision ]
			);
			throw new TranslatablePageMarkException( [
				'tpt-oldrevision',
				$this->titleFormatter->getPrefixedText( $page ),
				Message::rawParam( $link )
			] );
		}

		// newFromRevision never fails, but getReadyTag might fail if revision does not belong
		// to the page (checked above)
		$translatablePage = TranslatablePage::newFromRevision( $page, $revision );
		if ( $translatablePage->getReadyTag() !== $latestRevID ) {
			throw new TranslatablePageMarkException( [
				'tpt-notsuitable',
				$this->titleFormatter->getPrefixedText( $page ),
				Message::plaintextParam( '<translate>' )
			] );
		}

		$parserOutput = $this->translatablePageParser->parse( $translatablePage->getText() );
		[ $units, $deletedUnits ] = $this->prepareTranslationUnits( $translatablePage, $parserOutput );

		$unitValidationStatus = $this->validateUnitNames(
			$translatablePage,
			$units,
			$validateUnitTitle
		);

		return new TranslatablePageMarkOperation(
			$translatablePage,
			$parserOutput,
			$units,
			$deletedUnits,
			$translatablePage->getMarkedTag() === null,
			$unitValidationStatus
		);
	}

	/**
	 * Validate translation unit names.
	 * @param TranslatablePage $page
	 * @param TranslationUnit[] $units
	 * @param bool $includePageDisplayTitle Whether to validate the page display title as
	 * well (notably, it could fail the length validation). Duplicate ID check will be performed
	 * on the page display title even if this is false, as reusing the page display title unit name
	 * for a normal unit is an error for that unit.
	 * @return Status If OK, returns the validated units as a value in the Status object
	 */
	private function validateUnitNames(
		TranslatablePage $page,
		array $units,
		bool $includePageDisplayTitle
	): Status {
		$usedNames = [];
		$status = Status::newGood();
		$ic = preg_quote( TranslationUnit::UNIT_MARKER_INVALID_CHARS, '~' );
		foreach ( $units as $key => $s ) {
			$unitStatus = Status::newGood();
			if ( $includePageDisplayTitle || $key !== TranslatablePage::DISPLAY_TITLE_UNIT_ID ) {
				// xx-yyyyyyyyyy represents a long language code. 2 more characters than nl-informal which
				// is the longest non-redirect language code in language-data
				$pageTitle = $this->titleFormatter->getPrefixedText( $page->getPageIdentity() );
				$longestUnitTitle = "Translations:$pageTitle/{$s->id}/xx-yyyyyyyyyy";
				try {
					$this->titleParser->parseTitle( $longestUnitTitle );
				} catch ( MalformedTitleException $e ) {
					if ( $e->getErrorMessage() === 'title-invalid-too-long' ) {
						$unitStatus->fatal(
							'tpt-unit-title-too-long',
							$s->id,
							Message::numParam( strlen( $longestUnitTitle ) ),
							$e->getErrorMessageParameters()[0],
							$pageTitle
						);
					} else {
						$unitStatus->fatal( 'tpt-unit-title-invalid', $s->id, $e->getMessageObject() );
					}
				}

				// Only perform custom validation if the TitleParser validation passed
				if ( $unitStatus->isGood() && preg_match( "~[$ic]~", $s->id ) ) {
					$unitStatus->fatal( 'tpt-invalid', $s->id );
				}
			}

			// We need to do checks for both new and existing units. Someone might have tampered with the
			// page source adding duplicate or invalid markers.
			if ( isset( $usedNames[$s->id] ) ) {
				// If the same ID is used three or more times, the same
				// error will be added more than once, but that's okay,
				// Status::fatal will deduplicate
				$unitStatus->fatal( 'tpt-duplicate', $s->id );
			}
			$usedNames[$s->id] = true;

			$status->merge( $unitStatus );
		}

		return $status;
	}

	/**
	 * This function does the heavy duty of marking a page.
	 * - Updates the source page with section markers.
	 * - Updates translate_sections table
	 * - Updates revtags table
	 * - Sets up renderjobs to update the translation pages
	 * - Invalidates caches
	 * - Adds interim cache for MessageIndex
	 *
	 * @param TranslatablePageMarkOperation $operation
	 * @param TranslatablePageSettings $pageSettings Contains information about priority languages, units that should
	 * not be fuzzed, whether title should be translated and other translatable page settings
	 * @param MessageLocalizer $localizer
	 * @param User $user User performing the action. Checking user
	 * permissions is the caller’s responsibility
	 * @return int The number of translation units actually used
	 */
	public function markForTranslation(
		TranslatablePageMarkOperation $operation,
		TranslatablePageSettings $pageSettings,
		MessageLocalizer $localizer,
		User $user
	): int {
		if ( !$operation->isValid() ) {
			throw new LogicException( 'Trying to mark a page for translation that is not valid' );
		}

		$page = $operation->getPage();
		$title = $page->getTitle();
		$newRevisionId = $this->updateSectionMarkers( $page, $user, $localizer, $operation );
		// Probably a no-change edit, so no new revision was assigned. Get the latest revision manually
		// Could also occur on the off chance $newRevisionRecord->getId() returns null
		$newRevisionId ??= $title->getLatestRevID();

		$inserts = [];
		$changed = [];
		$groupId = $page->getMessageGroupId();
		$maxId = (int)$this->messageGroupMetadata->get( $groupId, 'maxid' );

		$pageId = $title->getArticleID();
		$sections = $pageSettings->shouldTranslateTitle()
			? $operation->getUnits()
			: array_filter(
				$operation->getUnits(),
				static fn ( TranslationUnit $s ) => $s->id !== TranslatablePage::DISPLAY_TITLE_UNIT_ID
			);

		foreach ( array_values( $sections ) as $index => $s ) {
			$maxId = max( $maxId, (int)$s->id );
			$changed[] = $s->id;

			if ( in_array( $s->id, $pageSettings->getNoFuzzyUnits(), true ) ) {
				// UpdateTranslatablePageJob will only fuzzy when type is changed
				$s->type = 'old';
			}

			$inserts[] = [
				'trs_page' => $pageId,
				'trs_key' => $s->id,
				'trs_text' => $s->getText(),
				'trs_order' => $index
			];
		}

		$dbw = $this->dbProvider->getPrimaryDatabase();
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'translate_sections' )
			->where( [ 'trs_page' => $title->getArticleID() ] )
			->caller( __METHOD__ )
			->execute();

		if ( $inserts ) {
			$dbw->newInsertQueryBuilder()
				->insertInto( 'translate_sections' )
				->rows( $inserts )
				->caller( __METHOD__ )
				->execute();
		}

		$this->saveMetadata( $operation, $pageSettings, $maxId, $user );

		$page->addMarkedTag( $newRevisionId );

		if ( $this->translatablePageView->isTranslationBannerNamespaceConfigured() ) {
			$this->translatablePageStateStore->remove( $page->getPageIdentity() );
		}

		// TODO: Ideally we would only invalidate translatable page message group cache
		$this->messageGroups->recache();

		$group = new WikiPageMessageGroup( $groupId, $title );
		$newKeys = $group->makeGroupKeys( $changed );
		// Interim cache is temporary cache to make new message groups keys known
		// until MessageIndex is rebuilt (which can take a long time)
		$this->messageIndex->storeInterim( $group, $newKeys );

		$job = UpdateTranslatablePageJob::newFromPage( $page, $sections );
		$this->jobQueueGroup->push( $job );

		// Logging
		$entry = new ManualLogEntry( 'pagetranslation', 'mark' );
		$entry->setPerformer( $user );
		$entry->setTarget( $title );
		$entry->setParameters( [
			'revision' => $newRevisionId,
			'changed' => count( $changed ),
		] );
		$logId = $entry->insert();
		$entry->publish( $logId );

		// Clear more caches
		$title->invalidateCache();

		$this->sendNotifications( $sections, $group, $groupId, $operation->isFirstMark() );

		return count( $sections );
	}

	private function saveMetadata(
		TranslatablePageMarkOperation $operation,
		TranslatablePageSettings $pageSettings,
		int $maxId,
		UserIdentity $user
	): void {
		$page = $operation->getPage();
		$groupId = $page->getMessageGroupId();

		$this->messageGroupMetadata->set( $groupId, 'maxid', (string)$maxId );
		if ( $pageSettings->shouldForceLatestSyntaxVersion() || $operation->isFirstMark() ) {
			$this->messageGroupMetadata->set( $groupId, 'version', self::LATEST_SYNTAX_VERSION );
		}

		$this->messageGroupMetadata->set(
			$groupId,
			'transclusion',
			$pageSettings->shouldEnableTransclusion() ? '1' : '0'
		);

		$this->handlePriorityLanguages( $operation->getPage(), $pageSettings, $user );
	}

	private function handlePriorityLanguages(
		TranslatablePage $page,
		TranslatablePageSettings $pageSettings,
		UserIdentity $user
	): void {
		$languages = $pageSettings->getPriorityLanguages() ?
			implode( ',', $pageSettings->getPriorityLanguages() ) :
			false;
		$force = $pageSettings->shouldForcePriorityLanguage() ? 'on' : false;
		$hasPriorityConfig = $languages || $force;

		// We use the reason if priority force and / or priority languages are set
		// Otherwise just a reason doesn't make sense
		if ( $hasPriorityConfig && $pageSettings->getPriorityLanguageComment() !== '' ) {
			$reason = $pageSettings->getPriorityLanguageComment();
		} else {
			$reason = false;
		}

		$groupId = $page->getMessageGroupId();
		// old metadata
		$opLanguages = $this->messageGroupMetadata->get( $groupId, 'prioritylangs' );
		$opForce = $this->messageGroupMetadata->get( $groupId, 'priorityforce' );
		$opReason = $this->messageGroupMetadata->get( $groupId, 'priorityreason' );

		$this->messageGroupMetadata->set( $groupId, 'prioritylangs', $languages );
		$this->messageGroupMetadata->set( $groupId, 'priorityforce', $force );
		$this->messageGroupMetadata->set( $groupId, 'priorityreason', $reason );

		if (
			$opLanguages !== $languages ||
			// Since 2024.04, we started storing false instead of 'off' to avoid additional storage
			// Remove after 2024.07 MLEB release
			( $opForce !== $force && !( $force === false && $opForce === 'off' ) ) ||
			// Since 2024.04, empty reason values are no longer stored.
			// Remove casting to string after 2024.07 MLEB release
			( (string)$opReason !== (string)$reason )
		) {
			$logComment = $reason === false ? '' : $reason;
			$params = [
				'languages' => $languages,
				'force' => $force,
				'reason' => $reason,
			];

			$entry = new ManualLogEntry( 'pagetranslation', 'prioritylanguages' );
			$entry->setPerformer( $user );
			$entry->setTarget( $page->getTitle() );
			$entry->setParameters( $params );
			$entry->setComment( $logComment );
			$logId = $entry->insert();
			$entry->publish( $logId );
		}
	}

	private function prepareTranslationUnits( TranslatablePage $page, ParserOutput $parserOutput ): array {
		$highest = (int)$this->messageGroupMetadata->get( $page->getMessageGroupId(), 'maxid' );

		$store = $this->translationUnitStoreFactory->getReader( $page->getPageIdentity() );
		$storedUnits = $store->getUnits();

		// Prepend the display title unit, which is not part of the page contents
		$displayTitle = new TranslationUnit(
			$this->titleFormatter->getPrefixedText( $page->getPageIdentity() ),
			TranslatablePage::DISPLAY_TITLE_UNIT_ID
		);

		$units = [ TranslatablePage::DISPLAY_TITLE_UNIT_ID => $displayTitle ] + $parserOutput->units();

		// Figure out the largest used translation unit id
		foreach ( array_keys( $storedUnits ) as $key ) {
			$highest = max( $highest, (int)$key );
		}
		foreach ( $units as $_ ) {
			$highest = max( $highest, (int)$_->id );
		}

		foreach ( $units as $s ) {
			$s->type = 'old';

			if ( $s->id === TranslationUnit::NEW_UNIT_ID ) {
				$s->type = 'new';
				$s->id = (string)( ++$highest );
			} else {
				if ( isset( $storedUnits[$s->id] ) ) {
					$storedText = $storedUnits[$s->id]->text;
					if ( $s->text !== $storedText ) {
						$s->type = 'changed';
						$s->oldText = $storedText;
					}
				}
			}
		}

		// Figure out which units were deleted by removing the still existing units
		$deletedUnits = $storedUnits;
		foreach ( $units as $s ) {
			unset( $deletedUnits[$s->id] );
		}

		return [ $units, $deletedUnits ];
	}

	private function updateSectionMarkers(
		TranslatablePage $page,
		Authority $authority,
		MessageLocalizer $localizer,
		TranslatablePageMarkOperation $operation
	): ?int {
		$pageUpdater = $this->wikiPageFactory->newFromTitle( $page->getTitle() )->newPageUpdater( $authority );
		$content = ContentHandler::makeContent(
			$operation->getParserOutput()->sourcePageTextForSaving(),
			$page->getTitle()
		);
		$comment = CommentStoreComment::newUnsavedComment(
			Message::newFromKey( 'tpt-mark-summary' )->inContentLanguage()->text()
		);

		$pageUpdater->setContent( SlotRecord::MAIN, $content );
		if ( $authority->authorizeWrite( 'autopatrol', $page->getTitle() ) ) {
			$pageUpdater->setRcPatrolStatus( RecentChange::PRC_AUTOPATROLLED );
		}
		$newRevisionRecord = $pageUpdater->saveRevision( $comment, EDIT_FORCE_BOT | EDIT_UPDATE );

		$this->throwIfEditFailed( $pageUpdater, $localizer );

		return $newRevisionRecord !== null ? $newRevisionRecord->getId() : null;
	}

	private function throwIfEditFailed( PageUpdater $pageUpdater, MessageLocalizer $messageLocalizer ): void {
		$status = $pageUpdater->getStatus();
		if ( !$status->isOK() ) {
			throw new TranslatablePageMarkException(
				[
					'tpt-edit-failed',
					$this->formatterFactory->getStatusFormatter( $messageLocalizer )->getMessage( $status ),
				]
			);
		}
	}

	/**
	 * @param TranslationUnit[] $sections
	 * @param WikiPageMessageGroup $group
	 * @param string $groupId
	 * @param bool $isFirstMark
	 */
	public function sendNotifications(
		array $sections,
		WikiPageMessageGroup $group,
		string $groupId,
		bool $isFirstMark
	): void {
		$changedMessages = array_filter( $sections, static function ( $section ) {
			return $section->type !== 'old';
		} );

		if ( !$changedMessages ) {
			return;
		}

		if ( $isFirstMark ) {
			$subscribers = $this->messageGroupSubscription->getGroupSubscribers( $groupId );
			if ( $subscribers instanceof EmptyIterator ) {
				// A non-translatable page may have subscribers if it was marked for translation before, or
				// if it was subscribed to from Special:ManageMessageGroupSubscriptions/raw.
				// This page is being marked for the first time, and has no subscribers, so no need to
				// send notifications
				return;
			}
		}

		$code = $group->getSourceLanguage();
		$pageTitle = $group->getTitle();

		foreach ( $changedMessages as $message ) {
			$messageTitle = Title::makeTitle( NS_TRANSLATIONS, "$pageTitle/$message->id/$code" );
			$this->messageGroupSubscription->queueMessage(
				$messageTitle,
				$message->type === 'new'
				 ? MessageGroupSubscription::STATE_ADDED
				 : MessageGroupSubscription::STATE_UPDATED,
				$groupId
			);
		}
		$this->messageGroupSubscription->queueNotificationJob();
	}
}
