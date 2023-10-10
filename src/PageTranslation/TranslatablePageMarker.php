<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use ContentHandler;
use ManualLogEntry;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatablePageStore;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Page\PageRecord;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Title\TitleFormatter;
use Message;
use TranslateMetadata;
use User;

/**
 * Service to unmark pages from translation
 * @since 2023.10
 */
class TranslatablePageMarker {
	private LinkRenderer $linkRenderer;
	private TitleFormatter $titleFormatter;
	private TranslatablePageParser $translatablePageParser;
	private TranslatablePageStore $translatablePageStore;
	private TranslationUnitStoreFactory $translationUnitStoreFactory;
	private WikiPageFactory $wikiPageFactory;

	public function __construct(
		LinkRenderer $linkRenderer,
		TitleFormatter $titleFormatter,
		TranslatablePageParser $translatablePageParser,
		TranslatablePageStore $translatablePageStore,
		TranslationUnitStoreFactory $translationUnitStoreFactory,
		WikiPageFactory $wikiPageFactory
	) {
		$this->linkRenderer = $linkRenderer;
		$this->titleFormatter = $titleFormatter;
		$this->translatablePageParser = $translatablePageParser;
		$this->translatablePageStore = $translatablePageStore;
		$this->translationUnitStoreFactory = $translationUnitStoreFactory;
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
	public function getMarkOperation( PageRecord $page, ?int $revision ): TranslatablePageMarkOperation {
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
		return new TranslatablePageMarkOperation(
			$translatablePage,
			$parserOutput,
			$units,
			$deletedUnits,
			$translatablePage->getMarkedTag() === null
		);
	}

	private function prepareTranslationUnits( TranslatablePage $page, ParserOutput $parserOutput ): array {
		$highest = (int)TranslateMetadata::get( $page->getMessageGroupId(), 'maxid' );

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
}
