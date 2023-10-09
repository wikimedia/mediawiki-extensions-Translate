<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use ContentHandler;
use ManualLogEntry;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatablePageStore;
use MediaWiki\Page\WikiPageFactory;
use Message;
use User;

/**
 * Service to unmark pages from translation
 * @since 2023.10
 */
class TranslatablePageMarker {
	private TranslatablePageStore $translatablePageStore;
	private WikiPageFactory $wikiPageFactory;

	public function __construct(
		TranslatablePageStore $translatablePageStore,
		WikiPageFactory $wikiPageFactory
	) {
		$this->translatablePageStore = $translatablePageStore;
		$this->wikiPageFactory = $wikiPageFactory;
	}

	/**
	 * Remove a page from translation.
	 * @param TranslatablePage $page The page to remove from translation
	 * @param User $user The user performing the action
	 * @param bool $removeMarkup Whether to remove markup from the translation page
	 * @throws TranslatablePageMarkException If removing the markup from the translation page fails
	 */
	public function unmarkPage( TranslatablePage $page, User $user, bool $removeMarkup ): void {
		if ( $removeMarkup ) {
			$content = ContentHandler::makeContent(
				$page->getStrippedSourcePageText(),
				$page->getTitle()
			);

			$status = $this->wikiPageFactory->newFromTitle( $page->getPageIdentity() )->doUserEditContent(
				$content,
				$user,
				Message::newFromKey( 'tpt-unlink-summary' )->inContentLanguage()->text(),
				EDIT_FORCE_BOT | EDIT_UPDATE
			);

			if ( !$status->isOK() ) {
				throw new TranslatablePageMarkException( [ 'tpt-edit-failed', $status->getWikiText() ] );
			}
		}

		$this->translatablePageStore->unmark( $page->getPageIdentity() );
		$page->getTitle()->invalidateCache();

		$entry = new ManualLogEntry( 'pagetranslation', 'unmark' );
		$entry->setPerformer( $user );
		$entry->setTarget( $page->getPageIdentity() );
		$logId = $entry->insert();
		$entry->publish( $logId );
	}
}
