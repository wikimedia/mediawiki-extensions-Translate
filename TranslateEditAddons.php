<?php
/**
 * Tools for edit page view to aid translators. This implements the so called
 * old style editing, which extends the normal edit page.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0+
 */

/**
 * Various editing enhancements to the edit page interface.
 * Partly succeeded by the new ajax-enhanced editor but kept for compatibility.
 * Also has code that is still relevant, like the hooks on save.
 */
class TranslateEditAddons {
	/**
	 * Keep the usual diiba daaba hidden from translators.
	 * Hook: AlternateEdit
	 */
	public static function intro( EditPage $editpage ) {
		$handle = new MessageHandle( $editpage->getTitle() );
		if ( $handle->isValid() ) {
			$editpage->suppressIntro = true;
			$group = $handle->getGroup();
			$languages = $group->getTranslatableLanguages();
			if ( $languages !== null && $handle->getCode() && !isset( $languages[$handle->getCode()] ) ) {
				$editpage->getArticle()->getContext()->getOutput()->wrapWikiMsg(
					"<div class='error'>$1</div>", 'translate-language-disabled'
				);

				return false;
			}
		}

		return true;
	}

	/**
	 * Adds the translation aids and navigation to the normal edit page.
	 * Hook: EditPage::showEditForm:initial
	 */
	public static function addTools( EditPage $object ) {
		$handle = new MessageHandle( $object->getTitle() );
		if ( !$handle->isValid() ) {
			return true;
		}

		$object->editFormTextTop .= self::editBoxes( $object );

		return true;
	}

	/**
	 * Replace the normal save button with one that says if you are editing
	 * message documentation to try to avoid accidents.
	 * Hook: EditPageBeforeEditButtons
	 */
	public static function buttonHack( EditPage $editpage, &$buttons, $tabindex ) {
		$handle = new MessageHandle( $editpage->getTitle() );
		if ( !$handle->isValid() ) {
			return true;
		}

		$context = $editpage->getArticle()->getContext();

		if ( $handle->isDoc() ) {
			$langCode = $context->getLanguage()->getCode();
			$name = TranslateUtils::getLanguageName( $handle->getCode(), $langCode );
			$accessKey = $context->msg( 'accesskey-save' )->plain();
			$temp = array(
				'id' => 'wpSave',
				'name' => 'wpSave',
				'type' => 'submit',
				'tabindex' => ++$tabindex,
				'value' => $context->msg( 'translate-save', $name )->text(),
				'accesskey' => $accessKey,
				'title' => $context->msg( 'tooltip-save' )->text() . ' [' . $accessKey . ']',
			);
			$buttons['save'] = Xml::element( 'input', $temp, '' );
		}

		try {
			$supportUrl = SupportAid::getSupportUrl( $handle->getTitle() );
		} catch ( TranslationHelperException $e ) {
			return true;
		}

		$temp = array(
			'id' => 'wpSupport',
			'name' => 'wpSupport',
			'type' => 'button',
			'tabindex' => ++$tabindex,
			'value' => $context->msg( 'translate-js-support' )->text(),
			'title' => $context->msg( 'translate-js-support-title' )->text(),
			'data-load-url' => $supportUrl,
			'onclick' => "window.open( jQuery(this).attr('data-load-url') );",
		);
		$buttons['ask'] = Html::element( 'input', $temp, '' );

		return true;
	}

	/**
	 * @param EditPage $editpage
	 * @return string
	 */
	private static function editBoxes( EditPage $editpage ) {
		$context = $editpage->getArticle()->getContext();
		$request = $context->getRequest();

		$groupId = $request->getText( 'loadgroup', '' );
		$th = new TranslationHelpers( $editpage->getTitle(), $groupId );

		if ( $editpage->firsttime &&
			!$request->getCheck( 'oldid' ) &&
			!$request->getCheck( 'undo' )
		) {
			$editpage->textbox1 = (string)$th->getTranslation();
		} else {
			$th->setTranslation( $editpage->textbox1 );
		}

		TranslationHelpers::addModules( $context->getOutput() );

		return $th->getBoxes();
	}

	/**
	 * Runs message checks, adds tp:transver tags and updates statistics.
	 * Hook: PageContentSaveComplete
	 */
	public static function onSave( WikiPage $wikiPage, $user, $content, $summary,
		$minor, $_1, $_2, $flags, $revision
	) {
		global $wgEnablePageTranslation;

		if ( !$content instanceof TextContent ) {
			// Screw it, not interested
			return true;
		}

		$text = $content->getNativeData();
		$title = $wikiPage->getTitle();
		$handle = new MessageHandle( $title );

		if ( !$handle->isValid() ) {
			return true;
		}

		// Update it.
		if ( $revision === null ) {
			$rev = $wikiPage->getTitle()->getLatestRevID();
		} else {
			$rev = $revision->getID();
		}

		$fuzzy = self::checkNeedsFuzzy( $handle, $text );
		self::updateFuzzyTag( $title, $rev, $fuzzy );

		$group = $handle->getGroup();
		// Update translation stats - source language should always be update
		if ( $handle->getCode() !== $group->getSourceLanguage() ) {
			MessageGroupStats::clear( $handle );
			MessageGroupStats::forItem( $group->getId(), $handle->getCode() );
		}

		MessageGroupStatesUpdaterJob::onChange( $handle );

		if ( $fuzzy === false ) {
			Hooks::run( 'Translate:newTranslation', array( $handle, $rev, $text, $user ) );
		}

		TTMServer::onChange( $handle, $text, $fuzzy );

		if ( $wgEnablePageTranslation && $handle->isPageTranslation() ) {
			// Updates for translatable pages only
			PageTranslationHooks::onSectionSave( $wikiPage, $user, $content,
				$summary, $minor, $flags, $revision, $handle );
		}

		return true;
	}

	/**
	 * @param MessageHandle $handle
	 * @param string $text
	 * @return bool
	 */
	protected static function checkNeedsFuzzy( MessageHandle $handle, $text ) {
		// Check for explicit tag.
		$fuzzy = MessageHandle::hasFuzzyString( $text );

		// Docs are exempt for checks
		if ( $handle->isDoc() ) {
			return $fuzzy;
		}

		// Not all groups have checkers
		$group = $handle->getGroup();
		$checker = $group->getChecker();
		if ( !$checker ) {
			return $fuzzy;
		}

		$code = $handle->getCode();
		$key = $handle->getKey();
		$en = $group->getMessage( $key, $group->getSourceLanguage() );
		$message = new FatMessage( $key, $en );
		// Take the contents from edit field as a translation.
		$message->setTranslation( $text );

		$checks = $checker->checkMessage( $message, $code );
		if ( count( $checks ) ) {
			$fuzzy = true;
		}

		return $fuzzy;
	}

	/**
	 * @param Title $title
	 * @param int $revision
	 * @param bool $fuzzy Whether to fuzzy or not
	 * @return bool Whether status changed
	 */
	protected static function updateFuzzyTag( Title $title, $revision, $fuzzy ) {
		$dbw = wfGetDB( DB_MASTER );

		$conds = array(
			'rt_page' => $title->getArticleID(),
			'rt_type' => RevTag::getType( 'fuzzy' ),
			'rt_revision' => $revision
		);

		// Replace the existing fuzzy tag, if any
		if ( $fuzzy !== false ) {
			$index = array_keys( $conds );
			$dbw->replace( 'revtag', array( $index ), $conds, __METHOD__ );
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

		$dbw = wfGetDB( DB_MASTER );

		$conds = array(
			'rt_page' => $title->getArticleID(),
			'rt_type' => RevTag::getType( 'tp:transver' ),
			'rt_revision' => $revision,
			'rt_value' => $definitionRevision,
		);
		$index = array( 'rt_type', 'rt_page', 'rt_revision' );
		$dbw->replace( 'revtag', array( $index ), $conds, __METHOD__ );

		return true;
	}

	/**
	 * Hook: ArticlePrepareTextForEdit
	 * @param WikiPage $wikiPage
	 * @param ParserOptions $popts
	 * @return bool
	 */
	public static function disablePreSaveTransform( $wikiPage, ParserOptions $popts ) {
		global $wgTranslateUsePreSaveTransform;

		if ( !$wgTranslateUsePreSaveTransform ) {
			$handle = new MessageHandle( $wikiPage->getTitle() );
			if ( $handle->isMessageNamespace() && !$handle->isDoc() ) {
				$popts->setPreSaveTransform( false );
			}
		}

		return true;
	}

	/**
	 * Hook: ArticleContentOnDiff
	 */
	public static function displayOnDiff( DifferenceEngine $de, OutputPage $out ) {
		$title = $de->getTitle();
		$handle = new MessageHandle( $title );

		if ( !$handle->isValid() ) {
			return true;
		}

		$th = new TranslationHelpers( $title, /*group*/false );
		$th->setEditMode( false );

		$de->loadNewText();
		if ( $de->mNewContent instanceof TextContent ) {
			$th->setTranslation( $de->mNewContent->getNativeData() );
		} else {
			// Screw you, not interested.
			return true;
		}
		TranslationHelpers::addModules( $out );

		$boxes = array();
		$boxes[] = $th->callBox( 'documentation', array( $th, 'getDocumentationBox' ) );
		$boxes[] = $th->callBox( 'definition', array( $th, 'getDefinitionBox' ) );
		$boxes[] = $th->callBox( 'translation', array( $th, 'getTranslationDisplayBox' ) );

		$output = implode( "\n", $boxes );
		$output = Html::rawElement(
			'div',
			array( 'class' => 'mw-sp-translate-edit-fields' ),
			$output
		);
		$out->addHTML( $output );

		return true;
	}
}
