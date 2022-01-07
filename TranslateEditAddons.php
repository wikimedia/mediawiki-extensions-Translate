<?php
/**
 * Tools for edit page view to aid translators. This implements the so called
 * old style editing, which extends the normal edit page.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\Services;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Storage\EditResult;
use MediaWiki\User\UserIdentity;

/**
 * Various editing enhancements to the edit page interface.
 * Partly succeeded by the new ajax-enhanced editor but kept for compatibility.
 * Also has code that is still relevant, like the hooks on save.
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
	 * @return bool
	 */
	public static function disallowLangTranslations( Title $title, User $user,
		$action, &$result
	) {
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
	 *
	 * Hook: PageSaveComplete
	 * @param WikiPage $wikiPage
	 * @param UserIdentity $userIdentity
	 * @param string $summary
	 * @param int $flags
	 * @param RevisionRecord $revisionRecord
	 * @param EditResult $editResult
	 * @return true
	 */
	public static function onSaveComplete(
		WikiPage $wikiPage,
		UserIdentity $userIdentity,
		string $summary,
		int $flags,
		RevisionRecord $revisionRecord,
		EditResult $editResult
	) {
		global $wgEnablePageTranslation;

		$content = $wikiPage->getContent();

		if ( !$content instanceof TextContent ) {
			// Screw it, not interested
			return true;
		}

		$text = $content->getText();
		$title = $wikiPage->getTitle();
		$handle = new MessageHandle( $title );

		if ( !$handle->isValid() ) {
			return true;
		}

		// Update it.
		$revId = $revisionRecord->getId();

		$fuzzy = self::checkNeedsFuzzy( $handle, $text );
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

		$user = User::newFromIdentity( $userIdentity );

		if ( $fuzzy === false ) {
			Hooks::run( 'Translate:newTranslation', [ $handle, $revId, $text, $user ] );
		}

		TTMServer::onChange( $handle );

		if ( $wgEnablePageTranslation && $handle->isPageTranslation() ) {
			// Updates for translatable pages only
			$minor = $flags & EDIT_MINOR;
			PageTranslationHooks::onSectionSave( $wikiPage, $user, $content,
				$summary, $minor, $flags, $handle );
		}

		return true;
	}

	/**
	 * Returns true if message is fuzzy, OR fails checks OR fails validations (error OR warning).
	 * @param MessageHandle $handle
	 * @param string $text
	 * @return bool
	 */
	protected static function checkNeedsFuzzy( MessageHandle $handle, $text ) {
		// Docs are exempt for checks
		if ( $handle->isDoc() ) {
			return false;
		}

		// Check for explicit tag.
		if ( MessageHandle::hasFuzzyString( $text ) ) {
			return true;
		}

		// Not all groups have validators
		$group = $handle->getGroup();
		$validator = $group->getValidator();

		// no validator set
		if ( !$validator ) {
			return false;
		}

		$code = $handle->getCode();
		$key = $handle->getKey();
		$en = $group->getMessage( $key, $group->getSourceLanguage() );
		$message = new FatMessage( $key, $en );
		// Take the contents from edit field as a translation.
		$message->setTranslation( $text );
		if ( $message->definition() === null ) {
			// This should NOT happen, but add a check since it seems to be happening
			// See: https://phabricator.wikimedia.org/T255669
			LoggerFactory::getInstance( 'Translate' )->warning(
				'Message definition is empty! Title: {title}, group: {group}, key: {key}',
				[
					'title' => $handle->getTitle()->getPrefixedText(),
					'group' => $group->getId(),
					'key' => $key
				]
			);
			return false;
		}

		$validationResult = $validator->quickValidate( $message, $code );
		return $validationResult->hasIssues();
	}

	/**
	 * @param Title $title
	 * @param int $revision
	 * @param bool $fuzzy Whether to fuzzy or not
	 * @return bool Whether status changed
	 */
	protected static function updateFuzzyTag( Title $title, $revision, $fuzzy ) {
		$dbw = wfGetDB( DB_PRIMARY );

		$conds = [
			'rt_page' => $title->getArticleID(),
			'rt_type' => RevTag::getType( 'fuzzy' ),
			'rt_revision' => $revision
		];

		// Replace the existing fuzzy tag, if any
		if ( $fuzzy !== false ) {
			$index = array_keys( $conds );
			$dbw->replace( 'revtag', [ $index ], $conds, __METHOD__ );
		} else {
			$dbw->delete( 'revtag', $conds, __METHOD__ );
		}

		return (bool)$dbw->affectedRows();
	}

	/**
	 * Adds tag which identifies the revision of source message at that time.
	 * This is used to show diff against current version of source message
	 * when updating a translation.
	 * Hook: Translate:newTranslation
	 * @param MessageHandle $handle
	 * @param int $revision
	 * @param string $text
	 * @param User $user
	 * @return bool
	 */
	public static function updateTransverTag( MessageHandle $handle, $revision,
		$text, User $user
	) {
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

		$dbw = wfGetDB( DB_PRIMARY );

		$conds = [
			'rt_page' => $title->getArticleID(),
			'rt_type' => RevTag::getType( 'tp:transver' ),
			'rt_revision' => $revision,
			'rt_value' => $definitionRevision,
		];
		$index = [ 'rt_type', 'rt_page', 'rt_revision' ];
		$dbw->replace( 'revtag', [ $index ], $conds, __METHOD__ );

		return true;
	}

	/**
	 * Hook: ArticlePrepareTextForEdit
	 * @param WikiPage $wikiPage
	 * @param ParserOptions $popts
	 * @return bool
	 */
	public static function disablePreSaveTransform( WikiPage $wikiPage, ParserOptions $popts ) {
		global $wgTranslateUsePreSaveTransform;

		if ( !$wgTranslateUsePreSaveTransform ) {
			$handle = new MessageHandle( $wikiPage->getTitle() );
			if ( $handle->isMessageNamespace() && !$handle->isDoc() ) {
				$popts->setPreSaveTransform( false );
			}
		}

		return true;
	}
}
