<?php
/**
 * Tools for edit page view to aid translators. This implements the so called
 * old style editing, which extends the normal edit page.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 */

/**
 * Various editing enhancements to the edit page interface.
 * Partly succeeded by the new ajax-enhanced editor but kept for compatibility.
 * Also has code that is still relevant, like the hooks on save.
 */
class TranslateEditAddons {
	/**
	 * Do not show the usual introductory messages on edit page for messages.
	 * Hook: AlternateEdit
	 * @param EditPage $editPage
	 */
	public static function suppressIntro( EditPage $editPage ) {
		$handle = new MessageHandle( $editPage->getTitle() );
		if ( $handle->isValid() ) {
			$editPage->suppressIntro = true;
		}
	}

	/**
	 * Prevent translations to non-translatable languages for the group
	 * Hook: getUserPermissionsErrorsExpensive
	 *
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @param mixed &$result
	 * @return bool
	 */
	public static function disallowLangTranslations( Title $title, User $user,
		$action, &$result
	) {
		global $wgTranslateBlacklist;

		if ( $action !== 'edit' ) {
			return true;
		}

		$handle = new MessageHandle( $title );
		if ( !$handle->isValid() ) {
			return true;
		}

		if ( $user->isAllowed( 'translate-manage' ) ) {
			return true;
		}

		$group = $handle->getGroup();
		$languages = $group->getTranslatableLanguages();
		$langCode = $handle->getCode();
		if ( $languages !== null && $langCode && !isset( $languages[$langCode] ) ) {
			$result = [ 'translate-language-disabled' ];
			return false;
		}

		$groupId = $group->getId();
		$checks = [
			$groupId,
			strtok( $groupId, '-' ),
			'*'
		];

		foreach ( $checks as $check ) {
			if ( isset( $wgTranslateBlacklist[$check][$langCode] ) ) {
				$reason = $wgTranslateBlacklist[$check][$langCode];
				$result = [ 'translate-page-disabled', $reason ];
				return false;
			}
		}

		return true;
	}

	/**
	 * Adds the translation aids and navigation to the normal edit page.
	 * Hook: EditPage::showEditForm:initial
	 * @param EditPage $object
	 * @return true
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
	 *
	 * @param EditPage $editpage
	 * @param array &$buttons
	 * @param int $tabindex
	 */
	public static function buttonHack( EditPage $editpage, &$buttons, $tabindex ) {
		$handle = new MessageHandle( $editpage->getTitle() );
		if ( !$handle->isValid() ) {
			return;
		}

		$context = $editpage->getArticle()->getContext();

		if ( $handle->isDoc() ) {
			$langCode = $context->getLanguage()->getCode();
			$name = TranslateUtils::getLanguageName( $handle->getCode(), $langCode );
			$attribs = [
				'id' => 'wpSave',
				'name' => 'wpSave',
				'tabindex' => ++$tabindex,
			] + Linker::tooltipAndAccesskeyAttribs( 'save' );

			$saveConfig = OOUI\Element::configFromHtmlAttributes( $attribs );
			$buttons['save'] = new OOUI\ButtonInputWidget( [
				// Support: IE 6 – Use <input>, otherwise it can't distinguish which button was clicked
				'useInputTag' => true,
				'flags' => [ 'progressive', 'primary' ],
				'label' => $context->msg( 'translate-save', $name )->text(),
				'type' => 'submit',
			] + $saveConfig );
		}

		try {
			$supportUrl = SupportAid::getSupportUrl( $handle->getTitle() );
		} catch ( TranslationHelperException $e ) {
			return;
		}

		$attribs = [
			'id' => 'wpSupport',
			'name' => 'wpSupport',
			'type' => 'button',
			'tabindex' => ++$tabindex,
			'title' => $context->msg( 'translate-js-support-title' )->text(),
		];

		$attribs += [
			'label' => $context->msg( 'translate-js-support' )->text(),
			'href' => $supportUrl,
			'target' => '_blank',
		];
		$saveConfig = OOUI\Element::configFromHtmlAttributes( $attribs );
		$buttons['ask'] = new OOUI\ButtonWidget( $saveConfig );
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
	 * @param WikiPage $wikiPage
	 * @param User $user
	 * @param Content $content
	 * @param string $summary
	 * @param bool $minor
	 * @param string $_1
	 * @param bool $_2
	 * @param int $flags
	 * @param Revision $revision
	 * @return true
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
			Hooks::run( 'Translate:newTranslation', [ $handle, $rev, $text, $user ] );
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

		$conds = [
			'rt_page' => $title->getArticleID(),
			'rt_type' => RevTag::getType( 'fuzzy' ),
			'rt_revision' => $revision
		];

		// Replace the existing fuzzy tag, if any
		if ( $fuzzy !== false ) {
			$index = array_keys( $conds );
			$dbw->replace( 'revtag', [ $index ], $conds, __METHOD__ );
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

		$conds = [
			'rt_page' => $title->getArticleID(),
			'rt_type' => RevTag::getType( 'tp:transver' ),
			'rt_revision' => $revision,
			'rt_value' => $definitionRevision,
		];
		$index = [ 'rt_type', 'rt_page', 'rt_revision' ];
		$dbw->replace( 'revtag', [ $index ], $conds, __METHOD__ );

		return true;
	}

	/**
	 * Hook: ArticlePrepareTextForEdit
	 * @param WikiPage $wikiPage
	 * @param ParserOptions $popts
	 * @return bool
	 */
	public static function disablePreSaveTransform( WikiPage $wikiPage, ParserOptions $popts ) {
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
	 * @param DifferenceEngine $de
	 * @param OutputPage $out
	 * @return true
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
		if ( method_exists( $de, 'getNewRevision' ) ) {
			$newRevision = $de->getNewRevision();
			$newContent = $newRevision ? $newRevision->getContent( 'main' ) : null;
		} else {
			$newContent = $de->mNewRev ? $de->mNewRev->getContent() : null;
		}
		if ( $newContent instanceof TextContent ) {
			$th->setTranslation( $newContent->getNativeData() );
		} else {
			// Screw you, not interested.
			return true;
		}
		TranslationHelpers::addModules( $out );

		$boxes = [];
		$boxes[] = $th->callBox( 'documentation', [ $th, 'getDocumentationBox' ] );
		$boxes[] = $th->callBox( 'definition', [ $th, 'getDefinitionBox' ] );
		$boxes[] = $th->callBox( 'translation', [ $th, 'getTranslationDisplayBox' ] );

		$output = implode( "\n", $boxes );
		$output = Html::rawElement(
			'div',
			[ 'class' => 'mw-sp-translate-edit-fields' ],
			$output
		);
		$out->addHTML( $output );

		return true;
	}
}
