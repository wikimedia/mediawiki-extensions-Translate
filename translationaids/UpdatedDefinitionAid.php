<?php
/**
 * Translation aid provider.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Translation aid which gives the message definition.
 * This usually matches the content of the page ns:key/source_language.
 *
 * @ingroup TranslationAids
 * @since 2013-01-01
 */
class UpdatedDefinitionAid extends TranslationAid {
	public function getData() {
		$db = TranslateUtils::getSafeReadDB();
		$conds = array(
			'rt_page' => $this->handle->getTitle()->getArticleID(),
			'rt_type' => RevTag::getType( 'tp:transver' ),
		);
		$options = array(
			'ORDER BY' => 'rt_revision DESC',
		);

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

		// Using newFromId instead of newFromTitle, because the page might have been renamed
		$oldrev = Revision::newFromId( $translationRevision );
		if ( !$oldrev ) {
			throw new TranslationHelperException( 'Old definition version does not exist anymore' );
		}

		$oldContent = $oldrev->getContent();
		$newContent = $this->getDefinitionContent();

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
		if ( method_exists( 'DifferenceEngine', 'setTextLanguage' ) ) {
			$diff->setTextLanguage( $this->group->getSourceLanguage() );
		}
		$diff->setContent( $oldContent, $newContent );
		$diff->setReducedLineNumbers();
		$diff->showDiffStyle();

		$html = $diff->getDiff(
			$this->context->msg( 'tpt-diff-old' )->escaped(),
			$this->context->msg( 'tpt-diff-new' )->escaped()
		);

		return array(
			'value_old' => $oldContent->getNativeData(),
			'value_new' => $newContent->getNativeData(),
			'revisionid_old' => $oldrev->getId(),
			'revisionid_new' => $definitionTitle->getLatestRevID(),
			'language' => $this->group->getSourceLanguage(),
			'html' => $html,
		);
	}
}
