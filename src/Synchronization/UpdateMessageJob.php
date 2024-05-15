<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use ContentHandler;
use FileBasedMessageGroup;
use MediaWiki\Extension\Translate\Jobs\GenericTranslateJob;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\MessageProcessing\TranslateReplaceTitle;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

/**
 * Job for updating translation pages when translation or message definition changes.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup JobQueue
 */
class UpdateMessageJob extends GenericTranslateJob {
	/** Create a normal message update job without a rename process */
	public static function newJob(
		Title $target, string $content, bool $fuzzy = false
	): self {
		$params = [
			'content' => $content,
			'fuzzy' => $fuzzy,
		];

		return new self( $target, $params );
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

		return new self( $target, $params );
	}

	public function __construct( Title $title, array $params = [] ) {
		parent::__construct( 'UpdateMessageJob', $title, $params );
	}

	public function run(): bool {
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
		$wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );
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

	private function handleRename( string $target, string $replacement, User $user ): ?Title {
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

			[ , $targetCode ] = Utilities::figureMessage( $replacementTitle->getText() );
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
			return null;
		}
	}

	/**
	 * Handles fuzzying. Message documentation and the source language are excluded from
	 * fuzzying. The source language is the identified via the $title parameter
	 */
	private function handleFuzzy( Title $title ): void {
		global $wgTranslateDocumentationLanguageCode;
		$handle = new MessageHandle( $title );

		$languages = Utilities::getLanguageNames( 'en' );

		// Don't fuzzy the message documentation
		unset( $languages[$wgTranslateDocumentationLanguageCode] );
		$languages = array_keys( $languages );

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

		$dbw = MediaWikiServices::getInstance()
			->getDBLoadBalancer()
			->getMaintenanceConnectionRef( DB_PRIMARY );

		$res = $dbw->newSelectQueryBuilder()
			->select( [ 'page_id', 'page_latest' ] )
			->from( 'page' )
			->where( [
				'page_namespace' => $title->getNamespace(),
				'page_title' => array_keys( $pages ),
			] )
			->caller( __METHOD__ )
			->fetchResultSet();
		$inserts = [];
		foreach ( $res as $row ) {
			$inserts[] = [
				'rt_type' => RevTagStore::FUZZY_TAG,
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

	/** Updates the translation unit pages in non-source languages. */
	private function processTranslationChanges(
		array $langChanges,
		string $baseTitle,
		int $groupNamespace,
		string $summary,
		int $flags,
		User $user
	): void {
		$wikiPageFactory = MediaWikiServices::getInstance()->getWikiPageFactory();
		foreach ( $langChanges as $code => $contentStr ) {
			$titleStr = Utilities::title( $baseTitle, $code, $groupNamespace );
			$title = Title::newFromText( $titleStr, $groupNamespace );
			$wikiPage = $wikiPageFactory->newFromTitle( $title );
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
