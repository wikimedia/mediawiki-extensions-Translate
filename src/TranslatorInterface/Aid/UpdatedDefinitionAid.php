<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Aid;

use DifferenceEngine;
use MediaWiki\Extension\Translate\TranslatorInterface\TranslationHelperException;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use RevTag;
use Title;
use TranslateUtils;
use WikitextContent;

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
		$db = TranslateUtils::getSafeReadDB();
		$conds = [
			'rt_page' => $this->handle->getTitle()->getArticleID(),
			'rt_type' => RevTag::getType( 'tp:transver' ),
		];
		$options = [
			'ORDER BY' => 'rt_revision DESC',
		];

		$translationRevision = $db->selectField( 'revtag', 'rt_value', $conds, __METHOD__, $options );
		if ( $translationRevision === false ) {
			throw new TranslationHelperException( 'No definition revision recorded' );
		}

		$definitionTitle = Title::makeTitleSafe(
			$this->handle->getTitle()->getNamespace(),
			$this->handle->getKey() . '/' . $this->group->getSourceLanguage()
		);

		if ( !$definitionTitle || !$definitionTitle->exists() ) {
			throw new TranslationHelperException( 'Definition page does not exist' );
		}

		// Using getRevisionById instead of byTitle, because the page might have been renamed
		$oldRevRecord = MediaWikiServices::getInstance()
			->getRevisionLookup()
			->getRevisionById( $translationRevision );
		if ( !$oldRevRecord ) {
			throw new TranslationHelperException( 'Old definition version does not exist anymore' );
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

		$diff = new DifferenceEngine( $this->context );
		$diff->setTextLanguage( wfGetLangObj( $this->group->getSourceLanguage() ) );
		$diff->setContent( $oldContent, $newContent );
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
