<?php
/**
 * @license GPL-2.0-or-later
 * @file
 */

/**
 * A utility trait containing reusable methods for use in tests
 * @since 2020.04
 */
trait TranslatablePageTestTrait {
	/**
	 * Creates a translatable page but does not mark it for translation.
	 *
	 * @param string $title
	 * @param string $content
	 * @param User $creator
	 * @return TranslatablePage
	 */
	public function createUnmarkedTranslatablePage(
		string $title, string $content, User $creator
	): TranslatablePage {
		return $this->createTranslatablePage( $title, $content, $creator, false );
	}

	/**
	 * Creates a translatable page and marks it for translation.
	 *
	 * @param string $title
	 * @param string $content
	 * @param User $creator
	 * @return TranslatablePage
	 */
	public function createMarkedTranslatablePage(
		string $title, string $content, User $creator
	): TranslatablePage {
		return $this->createTranslatablePage( $title, $content, $creator, true );
	}

	private function createTranslatablePage(
		string $title, string $content, User $creator, bool $markForTranslation
	): TranslatablePage {
		// Create new page
		$translatablePageTitle = Title::newFromText( $title );
		$page = WikiPage::factory( $translatablePageTitle );
		$text = "<translate>$content</translate>";
		$content = ContentHandler::makeContent( $text, $translatablePageTitle );
		$translatablePage = TranslatablePage::newFromTitle( $translatablePageTitle );

		// Create the page
		$editStatus = $page->doUserEditContent( $content, $creator, __METHOD__ );

		if ( $markForTranslation ) {
			// Mark the page for translation
			$latestRevisionId = $editStatus->value['revision-record']->getId();
			$translatablePage->addMarkedTag( $latestRevisionId );
		}

		return $translatablePage;
	}
}
