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
		$db = wfGetDB( DB_MASTER );
		$conds = array(
			'rt_page' => $this->handle->getTitle()->getArticleID(),
			'rt_type' => RevTag::getType( 'tp:transver' ),
		);
		$options = array(
			'ORDER BY' => 'rt_revision DESC',
		);

		$translationRevision = $db->selectField( 'revtag', 'rt_value', $conds, __METHOD__, $options );
		if ( $translationRevision === false ) {
			throw new TranslationHelperException( "No definition revision recorded" );
		}

		$definitionTitle = Title::makeTitleSafe(
			$this->handle->getTitle()->getNamespace(),
			$this->handle->getKey() . '/' . $this->group->getSourceLanguage()
		);

		if ( !$definitionTitle || !$definitionTitle->exists() ) {
			throw new TranslationHelperException( "Definition page doesn't exist" );
		}

		// Using newFromId instead of newFromTitle, because the page might have been renamed
		$oldrev = Revision::newFromId( $translationRevision );
		if ( !$oldrev ) {
			throw new TranslationHelperException( "Old definition version doesn't exist anymore" );
		}

		$oldtext = $oldrev->getText();
		$newtext = $this->getDefinition();

		if ( $oldtext === $newtext ) {
			throw new TranslationHelperException( "No changes" );
		}

		$diff = new DifferenceEngine;
		if ( method_exists( 'DifferenceEngine', 'setTextLanguage' ) ) {
			$diff->setTextLanguage( $this->group->getSourceLanguage() );
		}
		$diff->setText( $oldtext, $newtext );
		$diff->setReducedLineNumbers();
		$diff->showDiffStyle();

		$html = $diff->getDiff(
			$this->context->msg( 'tpt-diff-old' )->escaped(),
			$this->context->msg( 'tpt-diff-new' )->escaped()
		);

		return array(
			'value_old' => $oldtext,
			'value_new' => $newtext,
			'revisionid_old' => $oldrev->getId(),
			'revisionid_new' => $definitionTitle->getLatestRevId(),
			'language' => $this->group->getSourceLanguage(),
			'html' => $html,
		);
	}
}
