<?php
/**
 * Job for updating translation pages.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\Jobs\GenericTranslateJob;
use MediaWiki\Extension\Translate\MessageProcessing\TranslateReplaceTitle;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\MediaWikiServices;

/**
 * Job for updating translation pages when translation or message definition changes.
 *
 * @ingroup JobQueue
 */
class MessageUpdateJob extends GenericTranslateJob {
	/**
	 * Create a normal message update job without a rename process
	 * @param Title $target
	 * @param string $content
	 * @param string|false $fuzzy
	 * @return self
	 */
	public static function newJob(
		Title $target, string $content, $fuzzy = false
	): self {
		$params = [
			'content' => $content,
			'fuzzy' => $fuzzy,
		];

		$job = new self( $target, $params );

		return $job;
	}

	/**
	 * Create a message update job containing a rename process
	 * @param Title $target
	 * @param string $targetStr
	 * @param string $replacement
	 * @param string|false $fuzzy
	 * @param string $content
	 * @param array $otherLangContents
	 * @return self
	 */
	public static function newRenameJob(
		Title $target,
		string $targetStr,
		string $replacement,
		$fuzzy,
		string $content,
		array $otherLangContents = []
	): self {
		$params = [
			'target' => $targetStr,
			'replacement' => $replacement,
			'fuzzy' => $fuzzy,
			'rename' => 'rename',
			'content' => $content,
			'otherLangs' => $otherLangContents
		];

		$job = new self( $target, $params );

		return $job;
	}

	/**
	 * @param Title $title
	 * @param array $params
	 */
	public function __construct( $title, $params = [] ) {
		parent::__construct( __CLASS__, $title, $params );
	}

	public function run() {
		$params = $this->params;
		$user = FuzzyBot::getUser();
		$flags = EDIT_FORCE_BOT;
		$isRename = $params['rename'] ?? false;
		$isFuzzy = $params['fuzzy'] ?? false;
		$otherLangs = $params['otherLangs'] ?? [];
		$originalTitle = Title::newFromLinkTarget( $this->title->getTitleValue(), Title::NEW_CLONE );

		if ( $isRename ) {
			$this->title = $this->handleRename( $params['target'], $params['replacement'], $user );
			if ( $this->title === null ) {
				// There was a failure, return true, but don't proceed further.
				$this->logWarning(
					'Rename process could not find the source title.',
					[
						'replacement' => $params['replacement'],
						'target' => $params['target']
					]
				);

				$this->removeFromCache( $originalTitle );
				return true;
			}
		}

		$title = $this->title;
		$wikiPage = WikiPage::factory( $title );
		$summary = wfMessage( 'translate-manage-import-summary' )
			->inContentLanguage()->plain();
		$content = ContentHandler::makeContent( $params['content'], $title );
		$editStatus = $wikiPage->doUserEditContent(
			$content,
			$user,
			$summary,
			$flags
		);
		if ( !$editStatus->isOK() ) {
			$this->logError(
				'Failed to update content for source message',
				[
					'content' => $content,
					'errors' => $editStatus->getErrors()
				]
			);
		}

		if ( $isRename ) {
			// Update other language content if present.
			$this->processTranslationChanges(
				$otherLangs, $params['replacement'], $params['namespace'], $summary, $flags, $user
			);
		}

		if ( $isFuzzy ) {
			$this->handleFuzzy( $title );
		}

		$this->removeFromCache( $originalTitle );
		return true;
	}

	/**
	 * Handles renames
	 * @param string $target
	 * @param string $replacement
	 * @param User $user
	 * @return Title|null
	 */
	private function handleRename( $target, $replacement, User $user ) {
		$newSourceTitle = null;

		$sourceMessageHandle = new MessageHandle( $this->title );
		$movableTitles = TranslateReplaceTitle::getTitlesForMove( $sourceMessageHandle, $replacement );

		if ( $movableTitles === [] ) {
			$this->logError(
				'No moveable titles found with target text.',
				[
					'title' => $this->title->getPrefixedText(),
					'replacement' => $replacement,
					'target' => $target
				]
			);
			return null;
		}

		$renameSummary = wfMessage( 'translate-manage-import-rename-summary' )
			->inContentLanguage()->plain();

		foreach ( $movableTitles as [ $sourceTitle, $replacementTitle ] ) {
			$mv = MediaWikiServices::getInstance()
				->getMovePageFactory()
				->newMovePage( $sourceTitle, $replacementTitle );

			$status = $mv->move( $user, $renameSummary, false );
			if ( !$status->isOK() ) {
				$this->logError(
					'Error moving message',
					[
						'target' => $sourceTitle->getPrefixedText(),
						'replacement' => $replacementTitle->getPrefixedText(),
						'errors' => $status->getErrors()
					]
				);
			}

			[ , $targetCode ] = TranslateUtils::figureMessage( $replacementTitle->getText() );
			if ( !$newSourceTitle && $sourceMessageHandle->getCode() === $targetCode ) {
				$newSourceTitle = $replacementTitle;
			}
		}

		if ( $newSourceTitle ) {
			return $newSourceTitle;
		} else {
			// This means that the old source Title was never moved
			// which is not possible but handle it.
			$this->logError(
				'Source title was not in the list of moveable titles.',
				[ 'title' => $this->title->getPrefixedText() ]
			);
		}
	}

	/**
	 * Handles fuzzying. Message documentation and the source language are excluded from
	 * fuzzying. The source language is the identified via the $title parameter
	 * @param Title $title
	 */
	private function handleFuzzy( Title $title ) {
		global $wgTranslateDocumentationLanguageCode;
		$handle = new MessageHandle( $title );

		$languages = TranslateUtils::getLanguageNames( 'en' );

		// Don't fuzzy the message documentation
		unset( $languages[$wgTranslateDocumentationLanguageCode] );
		$languages = array_keys( $languages );

		$dbw = wfGetDB( DB_PRIMARY );
		$fields = [ 'page_id', 'page_latest' ];
		$conds = [ 'page_namespace' => $title->getNamespace() ];

		$pages = [];
		foreach ( $languages as $code ) {
			$otherTitle = $handle->getTitleForLanguage( $code );
			$pages[$otherTitle->getDBkey()] = true;
		}

		// Unset to ensure that the source language is not fuzzied
		unset( $pages[$title->getDBkey()] );

		if ( $pages === [] ) {
			return;
		}

		$conds['page_title'] = array_keys( $pages );

		$res = $dbw->select( 'page', $fields, $conds, __METHOD__ );
		$inserts = [];
		foreach ( $res as $row ) {
			$inserts[] = [
				'rt_type' => RevTag::getType( 'fuzzy' ),
				'rt_page' => $row->page_id,
				'rt_revision' => $row->page_latest,
			];
		}

		if ( $inserts === [] ) {
			return;
		}

		$dbw->replace(
			'revtag',
			[ [ 'rt_type', 'rt_page', 'rt_revision' ] ],
			$inserts,
			__METHOD__
		);
	}

	/**
	 * Updates the translation unit pages in non-source languages.
	 * @param array $langChanges
	 * @param string $baseTitle
	 * @param int $groupNamespace
	 * @param string $summary
	 * @param int $flags
	 * @param User $user
	 */
	private function processTranslationChanges(
		array $langChanges, $baseTitle, $groupNamespace, $summary, $flags, User $user
	) {
		foreach ( $langChanges as $code => $contentStr ) {
			$titleStr = TranslateUtils::title( $baseTitle, $code, $groupNamespace );
			$title = Title::newFromText( $titleStr, $groupNamespace );
			$wikiPage = WikiPage::factory( $title );
			$content = ContentHandler::makeContent( $contentStr, $title );
			$status = $wikiPage->doUserEditContent(
				$content,
				$user,
				$summary,
				$flags
			);
			if ( !$status->isOK() ) {
				$this->logError(
					'Failed to update content for non-source message',
					[
						'title' => $title->getPrefixedText(),
						'errors' => $status->getErrors()
					]
				);
			}
		}
	}

	private function removeFromCache( Title $title ): void {
		$config = MediaWikiServices::getInstance()->getMainConfig();

		if ( !$config->get( 'TranslateGroupSynchronizationCache' ) ) {
			return;
		}

		$currentTitle = $title;
		// Check if the current title, is equal to the title passed. This condition will be
		// true incase of rename where the old title would have been renamed.
		if ( $this->title && $this->title->getPrefixedDBkey() !== $title->getPrefixedDBkey() ) {
			$currentTitle = $this->title;
		}

		$sourceMessageHandle = new MessageHandle( $currentTitle );
		$groupIds = $sourceMessageHandle->getGroupIds();
		if ( !$groupIds ) {
			$this->logWarning(
				"Could not find group Id for message title: {$currentTitle->getPrefixedDBkey()}",
				$this->getParams()
			);
			return;
		}

		$groupId = $groupIds[0];
		$group = MessageGroups::getGroup( $groupId );

		if ( !$group instanceof FileBasedMessageGroup ) {
			return;
		}

		$groupSyncCache = Services::getInstance()->getGroupSynchronizationCache();
		$messageKey = $title->getPrefixedDBkey();

		if ( $groupSyncCache->isMessageBeingProcessed( $groupId, $messageKey ) ) {
			$groupSyncCache->removeMessages( $groupId, $messageKey );
			$groupSyncCache->extendGroupExpiryTime( $groupId );
		} else {
			$this->logWarning(
				"Did not find key: $messageKey; in group: $groupId in group sync cache",
				$this->getParams()
			);
		}
	}
}
