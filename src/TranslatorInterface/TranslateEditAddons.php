<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use DeferredUpdates;
use ManualLogEntry;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupStatesUpdaterJob;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\PageTranslation\Hooks as PageTranslationHooks;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Statistics\MessageGroupStats;
use MediaWiki\Extension\Translate\TtmServer\TtmServer;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Storage\EditResult;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use ParserOptions;
use TextContent;
use WikiPage;

/**
 * Various editing enhancements to the edit page interface.
 * Partly succeeded by the new ajax-enhanced editor but kept for compatibility.
 * Also has code that is still relevant, like the hooks on save.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 */
class TranslateEditAddons {
	/**
	 * Prevent translations to non-translatable languages for the group
	 * Hook: getUserPermissionsErrorsExpensive
	 *
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @param mixed &$result
	 */
	public static function disallowLangTranslations(
		Title $title,
		User $user,
		string $action,
		&$result
	): bool {
		if ( $action !== 'edit' ) {
			return true;
		}

		$handle = new MessageHandle( $title );
		if ( !$handle->isValid() ) {
			return true;
		}

		if ( $user->isAllowed( 'translate-manage' ) ) {
			return true;
		}

		$group = $handle->getGroup();
		$languages = $group->getTranslatableLanguages();
		$langCode = $handle->getCode();
		if ( $languages !== null && $langCode && !isset( $languages[$langCode] ) ) {
			$result = [ 'translate-language-disabled' ];
			return false;
		}

		$groupId = $group->getId();
		$checks = [
			$groupId,
			strtok( $groupId, '-' ),
			'*'
		];

		$disabledLanguages = Services::getInstance()->getConfigHelper()->getDisabledTargetLanguages();
		foreach ( $checks as $check ) {
			if ( isset( $disabledLanguages[$check][$langCode] ) ) {
				$reason = $disabledLanguages[$check][$langCode];
				$result = [ 'translate-page-disabled', $reason ];
				return false;
			}
		}

		return true;
	}

	/**
	 * Runs message checks, adds tp:transver tags and updates statistics.
	 * Hook: PageSaveComplete
	 */
	public static function onSaveComplete(
		WikiPage $wikiPage,
		UserIdentity $userIdentity,
		string $summary,
		int $flags,
		RevisionRecord $revisionRecord,
		EditResult $editResult
	): void {
		global $wgEnablePageTranslation;

		$content = $wikiPage->getContent();

		if ( !$content instanceof TextContent ) {
			// Screw it, not interested
			return;
		}

		$text = $content->getText();
		$title = $wikiPage->getTitle();
		$handle = new MessageHandle( $title );

		if ( !$handle->isValid() ) {
			return;
		}

		// Update it.
		$revId = $revisionRecord->getId();
		$mwServices = MediaWikiServices::getInstance();

		$fuzzy = $handle->needsFuzzy( $text );
		$parentId = $revisionRecord->getParentId();
		if ( $editResult->isNullEdit() || $parentId == 0 ) {
			// In this case the page_latest hasn't changed so we can rely on its fuzzy status
			$wasFuzzy = $handle->isFuzzy();
		} else {
			// In this case the page_latest will (probably) have changed. The above might work by chance
			// since it reads from a replica database which might not have gotten the update yet, but
			// don't trust it and read the fuzzy status of the parent ID from the database instead
			$revTagStore = Services::getInstance()->getRevTagStore();
			$wasFuzzy = $revTagStore->isRevIdFuzzy( $title->getArticleID(), $parentId );
		}
		if ( !$fuzzy && $wasFuzzy ) {
			$title = $mwServices->getTitleFactory()->castFromPageIdentity( $wikiPage );
			$user = $mwServices->getUserFactory()->newFromUserIdentity( $userIdentity );

			if ( !$mwServices->getPermissionManager()->userCan( 'unfuzzy', $user, $title ) ) {
				// No permission to unfuzzy this unit so leave it fuzzy
				$fuzzy = true;
			} elseif ( $editResult->isNullEdit() ) {
				$entry = new ManualLogEntry( 'translationreview', 'unfuzzy' );
				// Generate a log entry and null revision for the otherwise
				// invisible unfuzzying
				$dbw = $mwServices->getDBLoadBalancer()->getConnection( DB_PRIMARY );
				$nullRevision = $mwServices->getRevisionStore()->newNullRevision(
					$dbw,
					$wikiPage,
					CommentStoreComment::newUnsavedComment(
						$summary !== '' ? $summary : wfMessage( "translate-unfuzzy-comment" )
					),
					false,
					$userIdentity
				);
				if ( $nullRevision ) {
					$nullRevision = $mwServices->getRevisionStore()->insertRevisionOn( $nullRevision, $dbw );
					// Overwrite $revId so the revision ID of the null revision rather than the previous parent
					// revision is used for any further edits
					$revId = $nullRevision->getId();
					$wikiPage->updateRevisionOn( $dbw, $nullRevision, $nullRevision->getParentId() );
					$entry->setAssociatedRevId( $revId );
				}

				$entry->setPerformer( $userIdentity );
				$entry->setTarget( $title );
				$logId = $entry->insert();
				$entry->publish( $logId );
			}
		}
		self::updateFuzzyTag( $title, $revId, $fuzzy );

		$group = $handle->getGroup();
		// Update translation stats - source language should always be up to date
		if ( $handle->getCode() !== $group->getSourceLanguage() ) {
			// This will update in-process cache immediately, but the value is saved
			// to the database in a deferred update. See MessageGroupStats::queueUpdates.
			// In case an error happens before that, the stats may be stale, but that
			// would be fixed by the next update or purge.
			MessageGroupStats::clear( $handle );
		}

		// This job asks for stats, however the updated stats are written in a deferred update.
		// To make it less likely that the job would be executed before the updated stats are
		// written, create the job inside a deferred update too.
		DeferredUpdates::addCallableUpdate(
			static function () use ( $handle ) {
				MessageGroupStatesUpdaterJob::onChange( $handle );
			}
		);
		$user = $mwServices->getUserFactory()
			->newFromId( $userIdentity->getId() );

		if ( !$fuzzy ) {
			Services::getInstance()->getHookRunner()
				->onTranslate_newTranslation( $handle, $revId, $text, $user );
		}

		TtmServer::onChange( $handle );

		if ( $wgEnablePageTranslation && $handle->isPageTranslation() ) {
			// Updates for translatable pages only
			$minor = (bool)( $flags & EDIT_MINOR );
			PageTranslationHooks::onSectionSave( $wikiPage, $user, $content,
				$summary, $minor, $flags, $handle );
		}
	}

	/**
	 * @param Title $title
	 * @param int $revision
	 * @param bool $fuzzy Whether to fuzzy or not
	 */
	private static function updateFuzzyTag( Title $title, int $revision, bool $fuzzy ): void {
		$dbw = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();

		$conds = [
			'rt_page' => $title->getArticleID(),
			'rt_type' => RevTagStore::FUZZY_TAG,
			'rt_revision' => $revision
		];

		// Replace the existing fuzzy tag, if any
		if ( $fuzzy ) {
			$index = array_keys( $conds );
			$dbw->newReplaceQueryBuilder()
				->replaceInto( 'revtag' )
				->uniqueIndexFields( $index )
				->row( $conds )
				->caller( __METHOD__ )
				->execute();
		} else {
			$dbw->delete( 'revtag', $conds, __METHOD__ );
		}
	}

	/**
	 * Adds tag which identifies the revision of source message at that time.
	 * This is used to show diff against current version of source message
	 * when updating a translation.
	 * Hook: Translate:newTranslation
	 */
	public static function updateTransverTag(
		MessageHandle $handle,
		int $revision,
		string $text,
		User $user
	): bool {
		if ( $user->isAllowed( 'bot' ) ) {
			return false;
		}

		$group = $handle->getGroup();

		$title = $handle->getTitle();
		$name = $handle->getKey() . '/' . $group->getSourceLanguage();
		$definitionTitle = Title::makeTitleSafe( $title->getNamespace(), $name );
		if ( !$definitionTitle || !$definitionTitle->exists() ) {
			return true;
		}

		$definitionRevision = $definitionTitle->getLatestRevID();
		$revTagStore = Services::getInstance()->getRevTagStore();
		$revTagStore->setTransver( $title, $revision, $definitionRevision );

		return true;
	}

	/** Hook: ArticlePrepareTextForEdit */
	public static function disablePreSaveTransform(
		WikiPage $wikiPage,
		ParserOptions $popts
	): void {
		global $wgTranslateUsePreSaveTransform;

		if ( !$wgTranslateUsePreSaveTransform ) {
			$handle = new MessageHandle( $wikiPage->getTitle() );
			if ( $handle->isMessageNamespace() && !$handle->isDoc() ) {
				$popts->setPreSaveTransform( false );
			}
		}
	}
}
