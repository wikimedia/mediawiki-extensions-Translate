<?php
/**
 * Tools for edit page view to aid translators. This implements the so called
 * old style editing, which extends the normal edit page.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2007-2013 Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0+
 */

/**
 * Various editing enhancements to the edit page interface.
 * Partly succeeded by the new ajax-enhanced editor but kept for compatibility.
 * Also has code that is still relevant, like the hooks on save.
 */
class TranslateEditAddons {
	/**
	 * Add some tabs for navigation for users who do not use Ajax interface.
	 * Hook: SkinTemplateNavigation
	 */
	static function addNavigationTabs( Skin $skin, array &$tabs ) {
		$title = $skin->getTitle();
		$handle = new MessageHandle( $title );

		if ( !$handle->isValid() ) {
			return true;
		}

		$group = $handle->getGroup();
		// Happens when translation page move is in progress
		if ( !$group ) {
			return true;
		}

		$index = $next = $prev = $key = null;
		if ( $skin->getUser()->isAllowed( 'translate' ) ) {
			self::figureNextPrevMessages( $handle, $key, $index, $next, $prev );
		}

		$id = $group->getId();
		$code = $handle->getCode();

		$translate = SpecialPage::getTitleFor( 'Translate' );
		$fragment = htmlspecialchars( "#msg_$key" );

		$nav_params = array();
		$nav_params['loadgroup'] = $id;
		$nav_params['action'] = $skin->getRequest()->getText( 'action', 'edit' );

		$tabindex = 2;

		/**
		 * @var $prev Title
		 */
		if ( $prev !== null ) {
			$data = array(
				'text' => wfMessage( 'translate-edit-tab-prev' )->text(),
				'href' => $prev->getLocalUrl( $nav_params ),
			);
			self::addTab( $skin, $tabs, 'prev', $data, $tabindex );
		}

		$params = array(
			'group' => $id,
			'language' => $code,
			'task' => 'view',
			'offset' => max( 0, $index - 250 ),
			'limit' => 500,
		);
		$data = array(
			'text' => wfMessage( 'translate-edit-tab-list' )->text(),
			'href' => $translate->getLocalUrl( $params ) . $fragment,
		);
		self::addTab( $skin, $tabs, 'list', $data, $tabindex );

		/**
		 * @var $next Title
		 */
		if ( $next !== null ) {
			$data = array(
				'text' => wfMessage( 'translate-edit-tab-next' )->text(),
				'href' => $next->getLocalUrl( $nav_params ),
			);
			self::addTab( $skin, $tabs, 'next', $data, $tabindex );
		}

		return true;
	}

	protected static function addTab( $skin, &$tabs, $name, $data, &$index ) {
		// SkinChihuahua is an exception for userbase.kde.org.
		if ( $skin instanceof SkinVector || $skin instanceof SkinChihuahua ) {
			$data['class'] = false; // These skins need it for some reason
			$tabs['namespaces'][$name] = $data;
		} else {
			array_splice( $tabs, $index++, 0, array( $name => $data ) );
		}
	}

	/**
	 * Takes a MessageHandle, loads the keys of the primary group it belongs to
	 * and tries to find messages coming before and after.
	 * @param MessageHandle $handle
	 * @param string &$key will be filled with message in correct case etc.
	 * @param int|null &$index approximate index of the message, for setting offset
	 *                    and limit on Special:Translate
	 * @param Title|null &$next Title of the next message or null
	 * @param Title|null &$prev Title of the previous message or null
	 * @since 2012-08-21
	 */
	protected static function figureNextPrevMessages( MessageHandle $handle, &$key,
		&$index, &$next, &$prev
	) {
		wfProfileIn( __METHOD__ );
		$group = $handle->getGroup();

		if ( $group instanceof MessageGroupBase ) {
			$keys = $group->getKeys();
		} else {
			$keys = array_keys( $group->getDefinitions() );
		}

		$key = $handle->getKey();
		$key = strtolower( strtr( $key, ' ', '_' ) );
		$match = -100; // Stupid PHP thinks null is 0
		foreach ( $keys as $index => $tkey ) {
			// The cases may differ in first letter
			if ( $key === strtolower( strtr( $tkey, ' ', '_' ) ) ) {
				// Fill in the correct case
				$key = $tkey;
				$match = $index;
				break;
			}
		}

		$ns = $handle->getTitle()->getNamespace();
		$code = $handle->getCode();

		if ( isset( $keys[$match - 1] ) ) {
			$mkey = $keys[$match - 1];
			$prev = Title::makeTitleSafe( $ns, "$mkey/$code" );
		}
		if ( isset( $keys[$match + 1] ) ) {
			$mkey = $keys[$match + 1];
			$next = Title::makeTitleSafe( $ns, "$mkey/$code" );
		}
		wfProfileOut( __METHOD__ );
	}

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
			if ( $handle->getCode() && $languages !== null && !isset( $languages[$handle->getCode()] ) ) {
				$editpage->getArticle()->getContext()->getOutput()->wrapWikiMsg(
					"<div class='error'>$1</div>", 'translate-language-disabled'
				);

				return false;
			}

			return true;
		}
		$msg = wfMessage( 'translate-edit-tag-warning' )->inContentLanguage()->plain();

		// @todo Are the 1st 2 checks the same as !$msg->isDisabled()?
		if ( $msg !== '' &&
			$msg !== '-' &&
			TranslatablePage::isSourcePage( $editpage->getTitle() )
		) {
			$editpage->editFormTextTop .= $editpage->getArticle()->getContext()
				->getOutput()->parse( $msg );
		}

		return true;
	}

	/**
	 * Adds the translation aids and navigation to the normal edit page.
	 * Hook: EditPage::showEditForm:initial
	 */
	static function addTools( EditPage $object ) {
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
	static function buttonHack( EditPage $editpage, &$buttons, $tabindex ) {
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

		global $wgTranslateSupportUrl;
		if ( !$wgTranslateSupportUrl ) {
			return true;
		}

		$supportTitle = Title::newFromText( $wgTranslateSupportUrl['page'] );
		if ( !$supportTitle ) {
			return true;
		}

		$supportParams = $wgTranslateSupportUrl['params'];
		foreach ( $supportParams as &$value ) {
			$value = str_replace( '%MESSAGE%', $handle->getTitle()->getPrefixedText(), $value );
		}

		$temp = array(
			'id' => 'wpSupport',
			'name' => 'wpSupport',
			'type' => 'button',
			'tabindex' => ++$tabindex,
			'value' => $context->msg( 'translate-js-support' )->text(),
			'title' => $context->msg( 'translate-js-support-title' )->text(),
			'data-load-url' => $supportTitle->getLocalUrl( $supportParams ),
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
	 * Check if a string contains the fuzzy string.
	 *
	 * @param string $text Arbitrary text
	 * @return bool If string contains fuzzy string.
	 */
	public static function hasFuzzyString( $text ) {
		# wfDeprecated( __METHOD__, '1.19' );
		return MessageHandle::hasFuzzyString( $text );
	}

	/**
	 * Check if a title is marked as fuzzy.
	 * @param Title $title
	 * @return bool If title is marked fuzzy.
	 */
	public static function isFuzzy( Title $title ) {
		# wfDeprecated( __METHOD__, '1.19' );
		$handle = new MessageHandle( $title );

		return $handle->isFuzzy();
	}

	/**
	 * Hook: EditPage::showEditForm:fields
	 */
	public static function keepFields( EditPage $editpage, OutputPage $out ) {
		$request = $editpage->getArticle()->getContext()->getRequest();

		$out->addHTML(
			"\n" .
				Html::hidden( 'loadgroup', $request->getText( 'loadgroup' ) ) .
				Html::hidden( 'loadtask', $request->getText( 'loadtask' ) ) .
				"\n"
		);

		return true;
	}

	/**
	 * Runs message checks, adds tp:transver tags and updates statistics.
	 * Hook: ArticleSaveComplete, PageContentSaveComplete
	 *
	 * Switch to this when BC goes no further than 1.21:
	 *     public static function onSave( WikiPage $wikiPage, $user, $content, $summary,
	 */
	public static function onSave( $wikiPage, $user, $content, $summary,
		$minor, $_, $_, $flags, $revision
	) {

		if ( $content instanceof TextContent ) {
			$text = $content->getNativeData();
		} elseif ( is_string( $content ) ) {
			// BC 1.20
			$text = $content;
		} else {
			// Screw it, not interested
			return true;
		}

		$title = $wikiPage->getTitle();
		$handle = new MessageHandle( $title );

		if ( !$handle->isValid() ) {
			return true;
		}

		// Update it.
		if ( $revision === null ) {
			$rev = $wikiPage->getTitle()->getLatestRevId();
		} else {
			$rev = $revision->getID();
		}

		$fuzzy = self::checkNeedsFuzzy( $handle, $text );
		self::updateFuzzyTag( $title, $rev, $fuzzy );
		wfRunHooks( 'TranslateEventTranslationEdit', array( $handle ) );

		if ( $fuzzy === false ) {
			wfRunHooks( 'Translate:newTranslation', array( $handle, $rev, $text, $user ) );
		}

		TTMServer::onChange( $handle, $text, $fuzzy );

		return true;
	}

	/**
	 * @param MessageHandle $handle
	 * @param string $text
	 * @return bool
	 */
	protected static function checkNeedsFuzzy( MessageHandle $handle, $text ) {
		// Check for explicit tag.
		$fuzzy = self::hasFuzzyString( $text );

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
	 * @param bool $fuzzy
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

		$de->loadNewText();
		$out->setRevisionId( $de->mNewRev->getId() );

		$th = new TranslationHelpers( $title, /*group*/false );
		$th->setEditMode( false );

		if ( isset( $de->mNewContent ) && $de->mNewContent instanceof TextContent ) {
			$th->setTranslation( $de->mNewContent->getNativeData() );
		} elseif ( isset( $de->mNewtext ) ) {
			// BC 1.20
			$th->setTranslation( $de->mNewtext );
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
		$out->addHtml( $output );

		return true;
	}
}
