<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Aid;

use DifferenceEngine;
use MediaWiki\Content\WikitextContent;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\TranslatorInterface\TranslationHelperException;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\MutableRevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;

/**
 * Translation aid that provides the message definition.
 * This usually matches the content of the page ns:key/source_language.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013-01-01
 * @ingroup TranslationAids
 */
class UpdatedDefinitionAid extends TranslationAid {
	public function getData(): array {
		$db = Utilities::getSafeReadDB();

		$revTagStore = Services::getInstance()->getRevTagStore();

		$translationRevision = $revTagStore->getTransver( $this->handle->getTitle() );
		if ( $translationRevision === null ) {
			throw new TranslationHelperException( 'No definition revision recorded' );
		}

		$sourceLanguage = $this->group->getSourceLanguage();
		$definitionTitle = Title::makeTitleSafe(
			$this->handle->getTitle()->getNamespace(),
			$this->handle->getKey() . '/' . $sourceLanguage
		);

		if ( !$definitionTitle || !$definitionTitle->exists() ) {
			throw new TranslationHelperException( 'Definition page does not exist' );
		}

		// Using getRevisionById instead of byTitle, because the page might have been renamed
		$mwInstance = MediaWikiServices::getInstance();
		$revisionLookup = $mwInstance->getRevisionLookup();
		$oldRevRecord = $revisionLookup->getRevisionById( $translationRevision );
		if ( !$oldRevRecord ) {
			throw new TranslationHelperException( 'Old definition version does not exist anymore' );
		}

		// Escaping legacy issue (T330453)
		if ( $oldRevRecord->getPageId() !== $definitionTitle->getArticleID() ) {
			throw new TranslationHelperException(
				'Translation unit definition id does not match old revision definition id'
			);
		}

		$oldContent = $oldRevRecord->getContent( SlotRecord::MAIN );
		$newContent = $this->dataProvider->getDefinitionContent();

		if ( !$oldContent ) {
			throw new TranslationHelperException( 'Old definition version does not exist anymore' );
		}

		if ( !$oldContent instanceof WikitextContent || !$newContent instanceof WikitextContent ) {
			throw new TranslationHelperException( 'Can only work on Wikitext content' );
		}

		if ( $oldContent->equals( $newContent ) ) {
			throw new TranslationHelperException( 'No changes' );
		}

		$newRevRecord = new MutableRevisionRecord( $definitionTitle );
		$newRevRecord->setContent( SlotRecord::MAIN, $newContent );

		$diff = new DifferenceEngine( $this->context );
		$diff->setTextLanguage( $mwInstance->getLanguageFactory()->getLanguage( $sourceLanguage ) );
		$diff->setRevisions( $oldRevRecord, $newRevRecord );
		$diff->setReducedLineNumbers();
		$diff->showDiffStyle();

		$html = $diff->getDiff(
			$this->context->msg( 'tpt-diff-old' )->escaped(),
			$this->context->msg( 'tpt-diff-new' )->escaped()
		);

		return [
			'value_old' => $oldContent->getText(),
			'value_new' => $newContent->getText(),
			'revisionid_old' => $oldRevRecord->getId(),
			'revisionid_new' => $definitionTitle->getLatestRevID(),
			'language' => $this->group->getSourceLanguage(),
			'html' => $html,
		];
	}
}
