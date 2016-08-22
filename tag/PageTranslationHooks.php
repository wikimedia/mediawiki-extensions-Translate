<?php
/**
 * Contains class with page translation feature hooks.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Hooks for page translation.
 *
 * @ingroup PageTranslation
 */
class PageTranslationHooks {
	// Uuugly hacks
	public static $allowTargetEdit = false;

	// Check if job queue is running
	public static $jobQueueRunning = false;

	/**
	 * Hook: ParserBeforeStrip
	 * @param $parser Parser
	 * @param $text
	 * @param $state
	 * @return bool
	 */
	public static function renderTagPage( $parser, &$text, $state ) {
		$title = $parser->getTitle();

		if ( strpos( $text, '<translate>' ) !== false ) {
			try {
				$parse = TranslatablePage::newFromText( $parser->getTitle(), $text )->getParse();
				$text = $parse->getTranslationPageText( null );
				$parser->getOutput()->addModuleStyles( 'ext.translate' );
			} catch ( TPException $e ) {
				// Show ugly preview without processed <translate> tags
				wfDebug( 'TPException caught; expected' );
			}
		}

		// Set display title
		$page = TranslatablePage::isTranslationPage( $title );
		if ( !$page ) {
			return true;
		}

		list( , $code ) = TranslateUtils::figureMessage( $title->getText() );
		$name = $page->getPageDisplayTitle( $code );

		if ( $name ) {
			$name = $parser->recursivePreprocess( $name );
			$parser->getOutput()->setDisplayTitle( $name );
		}

		// Disable edit section links
		$parser->getOptions()->setEditSection( false );

		return true;
	}

	/**
	 * Set the right page content language for translated pages ("Page/xx").
	 * Hook: PageContentLanguage
	 */
	public static function onPageContentLanguage( Title $title, /*string*/&$pageLang ) {
		// For translation pages, parse plural, grammar etc with correct language,
		// and set the right direction
		if ( TranslatablePage::isTranslationPage( $title ) ) {
			list( , $code ) = TranslateUtils::figureMessage( $title->getText() );
			$pageLang = $code;
		}

		return true;
	}

	/**
	 * Display an edit notice for translatable source pages if it's enabled
	 * Hook: TitleGetEditNotices
	 *
	 * @param Title $title
	 * @param int $oldid
	 * @param array &$notices
	 */
	public static function onTitleGetEditNotices( Title $title, $oldid, array &$notices ) {
		$msg = wfMessage( 'translate-edit-tag-warning' )->inContentLanguage();

		if ( !$msg->isDisabled() && TranslatablePage::isSourcePage( $title ) ) {
			$notices['translate-tag'] = $msg->parseAsBlock();
		}
	}

	/// Hook: OutputPageBeforeHTML
	public static function injectCss( OutputPage $out, /*string*/$text ) {
		global $wgTranslatePageTranslationULS;

		$title = $out->getTitle();
		$isSource = TranslatablePage::isSourcePage( $title );
		$isTranslation = TranslatablePage::isTranslationPage( $title );

		if ( $isSource || $isTranslation ) {
			if ( $wgTranslatePageTranslationULS ) {
				$out->addModules( 'ext.translate.pagetranslation.uls' );
			}

			if ( $isTranslation ) {
				// Source pages get this module via <translate>, but for translation
				// pages we need to add it manually.
				$out->addModuleStyles( 'ext.translate' );
				$out->addJsConfigVars( 'wgTranslatePageTranslation', 'translation' );
			} else {
				$out->addJsConfigVars( 'wgTranslatePageTranslation', 'source' );
			}
		}

		return true;
	}

	/**
	 * This is triggered after saves to translation unit pages
	 */
	public static function onSectionSave( WikiPage $wikiPage, User $user, TextContent $content,
		$summary, $minor, $flags, $revision, MessageHandle $handle
	) {
		// FuzzyBot may do some duplicate work already worked on by other jobs
		if ( FuzzyBot::getName() === $user->getName() ) {
			return true;
		}

		$group = $handle->getGroup();
		if ( !$group instanceof WikiPageMessageGroup ) {
			return true;
		}

		// Finally we know the title and can construct a Translatable page
		$page = TranslatablePage::newFromTitle( $group->getTitle() );

		// Update the target translation page
		if ( !$handle->isDoc() ) {
			$code = $handle->getCode();
			self::updateTranslationPage( $page, $code, $user, $flags, $summary );
		}

		return true;
	}

	public static function updateTranslationPage( TranslatablePage $page,
		$code, $user, $flags, $summary
	) {
		$source = $page->getTitle();
		$target = $source->getSubpage( $code );

		// We don't know and don't care
		$flags &= ~EDIT_NEW & ~EDIT_UPDATE;

		// Update the target page
		$job = TranslateRenderJob::newJob( $target );
		$job->setUser( $user );
		$job->setSummary( $summary );
		$job->setFlags( $flags );
		$job->run();

		// Invalidate caches so that language bar is up-to-date
		$pages = $page->getTranslationPages();
		foreach ( $pages as $title ) {
			$wikiPage = WikiPage::factory( $title );
			$wikiPage->doPurge();
		}
		$sourceWikiPage = WikiPage::factory( $source );
		$sourceWikiPage->doPurge();
	}

	/**
	 * @param $data
	 * @param $params
	 * @param $parser Parser
	 * @return string
	 */
	public static function languages( $data, $params, $parser ) {
		$currentTitle = $parser->getTitle();

		// Check if this is a source page or a translation page
		$page = TranslatablePage::newFromTitle( $currentTitle );
		if ( $page->getMarkedTag() === false ) {
			$page = TranslatablePage::isTranslationPage( $currentTitle );
		}

		if ( $page === false || $page->getMarkedTag() === false ) {
			return '';
		}

		$status = $page->getTranslationPercentages();
		if ( !$status ) {
			return '';
		}

		// If priority languages have been set always show those languages
		$priorityLangs = TranslateMetadata::get( $page->getMessageGroupId(), 'prioritylangs' );
		$priorityForce = TranslateMetadata::get( $page->getMessageGroupId(), 'priorityforce' );
		$filter = null;
		if ( strlen( $priorityLangs ) > 0 ) {
			$filter = array_flip( explode( ',', $priorityLangs ) );
		}
		if ( $filter !== null ) {
			// If translation is restricted to some languages, only show them
			if ( $priorityForce === 'on' ) {
				// Do not filter the source language link
				$filter[$page->getMessageGroup()->getSourceLanguage()] = true;
				$status = array_intersect_key( $status, $filter );
			}
			foreach ( $filter as $langCode => $value ) {
				if ( !isset( $status[$langCode] ) ) {
					// We need to show all priority languages even if no translation started
					$status[$langCode] = 0;
				}
			}
		}

		// Fix title
		$pageTitle = $page->getTitle();

		// Sort by language code, which seems to be the only sane method
		ksort( $status );

		// This way the parser knows to fragment the parser cache by language code
		$userLang = $parser->getOptions()->getUserLangObj();
		$userLangCode = $userLang->getCode();
		// Should call $page->getMessageGroup()->getSourceLanguage(), but
		// group is sometimes null on WMF during page moves, reason unknown.
		// This should do the same thing for now.
		$sourceLanguage = $pageTitle->getPageLanguage()->getCode();

		$languages = array();
		foreach ( $status as $code => $percent ) {
			// Get autonyms (null)
			$name = TranslateUtils::getLanguageName( $code, null );
			$name = htmlspecialchars( $name ); // Unlikely, but better safe

			// Add links to other languages
			$suffix = ( $code === $sourceLanguage ) ? '' : "/$code";
			$targetTitleString = $pageTitle->getDBkey() . $suffix;
			$subpage = Title::makeTitle( $pageTitle->getNamespace(), $targetTitleString );

			$classes = array();
			if ( $code === $userLangCode ) {
				$classes[] = 'mw-pt-languages-ui';
			}

			if ( $currentTitle->equals( $subpage ) ) {
				$classes[] = 'mw-pt-languages-selected';
				$classes = array_merge( $classes, self::tpProgressIcon( $percent ) );
				$name = Html::rawElement( 'span', array( 'class' => $classes ), $name );
			} elseif ( $subpage->isKnown() ) {
				$pagename = $page->getPageDisplayTitle( $code );
				if ( !is_string( $pagename ) ) {
					$pagename = $subpage->getPrefixedText();
				}

				$classes = array_merge( $classes, self::tpProgressIcon( $percent ) );

				$title = wfMessage( 'tpt-languages-nonzero' )
					->inLanguage( $userLang )
					->params( $pagename )
					->numParams( 100 * $percent )
					->text();
				$attribs = array(
					'title' => $title,
					'class' => $classes,
				);

				$name = Linker::linkKnown( $subpage, $name, $attribs );
			} else {
				/* When language is included because it is a priority language,
				 * but translation does not yet exists, link directly to the
				 * translation view. */
				$specialTranslateTitle = SpecialPage::getTitleFor( 'Translate' );
				$params = array(
					'group' => $page->getMessageGroupId(),
					'language' => $code,
					'task' => 'view'
				);

				$classes[] = 'new';  // For red link color
				$attribs = array(
					'title' => wfMessage( 'tpt-languages-zero' )->inLanguage( $userLang )->text(),
					'class' => $classes,
				);
				$name = Linker::linkKnown( $specialTranslateTitle, $name, $attribs, $params );
			}

			$languages[] = $name;
		}

		// dirmark (rlm/lrm) is added, because languages with RTL names can
		// mess the display
		$sep = wfMessage( 'tpt-languages-separator' )->inLanguage( $userLang )->escaped();
		$sep .= $userLang->getDirMark();
		$languages = implode( $sep, $languages );

		$out = Html::openElement( 'div', array(
			'class' => 'mw-pt-languages noprint',
			'lang' => $userLang->getHtmlCode(),
			'dir' => $userLang->getDir()
		) );
		$out .= Html::rawElement( 'div', array( 'class' => 'mw-pt-languages-label' ),
			wfMessage( 'tpt-languages-legend' )->inLanguage( $userLang )->escaped()
		);
		$out .= Html::rawElement(
			'div',
			array( 'class' => 'mw-pt-languages-list autonym' ),
			$languages
		);
		$out .= Html::closeElement( 'div' );

		$parser->getOutput()->addModuleStyles( 'ext.translate.tag.languages' );

		return $out;
	}

	/**
	 * Return icon CSS class for given progress status: percentages
	 * are too accurate and take more space than simple images.
	 * @param $percent float
	 * @return string[]
	 */
	protected static function tpProgressIcon( $percent ) {
		$classes = array( 'mw-pt-progress' );
		$percent *= 100;
		if ( $percent < 20 ) {
			$classes[] = 'mw-pt-progress--stub';
		} elseif ( $percent < 40 ) {
			$classes[] = 'mw-pt-progress--low';
		} elseif ( $percent < 60 ) {
			$classes[] = 'mw-pt-progress--med';
		} elseif ( $percent < 80 ) {
			$classes[] = 'mw-pt-progress--high';
		} else {
			$classes[] = 'mw-pt-progress--complete';
		}
		return $classes;
	}

	/**
	 * Display nice error when editing content.
	 * Hook: EditFilterMergedContent
	 */
	public static function tpSyntaxCheckForEditContent( $context, $content, $status, $summary ) {
		if ( !$content instanceof TextContent ) {
			return true; // whatever.
		}

		$text = $content->getNativeData();
		$title = $context->getTitle();

		$e = self::tpSyntaxError( $title, $text );

		if ( $e ) {
			$msg = $e->getMsg();
			// $msg is an array containing a message key followed by any parameters.
			// @todo Use Message object instead.

			call_user_func_array( array( $status, 'fatal' ), $msg );
		}

		return true;
	}

	/**
	 * Returns any syntax error.
	 */
	protected static function tpSyntaxError( $title, $text ) {
		if ( strpos( $text, '<translate>' ) === false ) {
			return null;
		}

		$page = TranslatablePage::newFromText( $title, $text );
		try {
			$page->getParse();

			return null;
		} catch ( TPException $e ) {
			return $e;
		}
	}

	/**
	 * When attempting to save, last resort. Edit page would only display
	 * edit conflict if there wasn't tpSyntaxCheckForEditPage.
	 * Hook: PageContentSave
	 */
	public static function tpSyntaxCheck( $wikiPage, $user, $content, $summary,
		$minor, $_1, $_2, $flags, $status
	) {
		if ( $content instanceof TextContent ) {
			$text = $content->getNativeData();
		} else {
			// Screw it, not interested
			return true;
		}

		// Quick escape on normal pages
		if ( strpos( $text, '<translate>' ) === false ) {
			return true;
		}

		$page = TranslatablePage::newFromText( $wikiPage->getTitle(), $text );
		try {
			$page->getParse();
		} catch ( TPException $e ) {
			call_user_func_array( array( $status, 'fatal' ), $e->getMsg() );

			return false;
		}

		return true;
	}

	/**
	 * Hook: PageContentSaveComplete
	 */
	public static function addTranstag( $wikiPage, $user, $content, $summary,
		$minor, $_1, $_2, $flags, $revision
	) {
		// We are not interested in null revisions
		if ( $revision === null ) {
			return true;
		}

		if ( $content instanceof TextContent ) {
			$text = $content->getNativeData();
		} else {
			// Screw it, not interested
			return true;
		}

		// Quick escape on normal pages
		if ( strpos( $text, '</translate>' ) === false ) {
			return true;
		}

		// Add the ready tag
		$page = TranslatablePage::newFromTitle( $wikiPage->getTitle() );
		$page->addReadyTag( $revision->getId() );

		return true;
	}

	/**
	 * Page moving and page protection (and possibly other things) creates null
	 * revisions. These revisions re-use the previous text already stored in
	 * the database. Those however do not trigger re-parsing of the page and
	 * thus the ready tag is not updated. This watches for new revisions,
	 * checks if they reuse existing text, checks whether the parent version
	 * is the latest version and has a ready tag. If that is the case,
	 * also adds a ready tag for the new revision (which is safe, because
	 * the text hasn't changed). The interface will say that there has been
	 * a change, but shows no change in the content. This lets the user to
	 * update the translation pages in the case, the non-text changes affect
	 * the rendering of translation pages. I'm not aware of any such cases
	 * at the moment.
	 * Hook: RevisionInsertComplete
	 * @since 2012-05-08
	 */
	public static function updateTranstagOnNullRevisions( Revision $rev, $text, $flags ) {
		$title = $rev->getTitle();

		$newRevId = $rev->getId();
		$oldRevId = $rev->getParentId();
		$newTextId = $rev->getTextId();

		/* This hook doesn't provide any way to detech null revisions
		 * without extra query */
		$dbw = wfGetDB( DB_MASTER );
		$table = 'revision';
		$field = 'rev_text_id';
		$conds = array(
			'rev_page' => $rev->getPage(),
			'rev_id' => $oldRevId,
		);
		// FIXME: optimize away this query. Bug T38588.
		$oldTextId = $dbw->selectField( $table, $field, $conds, __METHOD__ );

		if ( (string)$newTextId !== (string)$oldTextId ) {
			// Not a null revision, bail out.
			return true;
		}

		$page = TranslatablePage::newFromTitle( $title );
		if ( $page->getReadyTag() === $oldRevId ) {
			$page->addReadyTag( $newRevId );
		}

		return true;
	}

	/**
	 * Prevent editing of certain pages in Translations namespace.
	 * Hook: getUserPermissionsErrorsExpensive
	 *
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @param mixed $result
	 * @return bool
	 */
	public static function onGetUserPermissionsErrorsExpensive( Title $title, User $user,
		$action, &$result
	) {
		$handle = new MessageHandle( $title );

		// Check only when someone tries to edit (or create) page translation messages
		if ( $action !== 'edit' || !$handle->isPageTranslation() ) {
			return true;
		}

		if ( !$handle->isValid() ) {
			// Don't allow editing invalid messages that do not belong to any translatable page
			$result = array( 'tpt-unknown-page' );
			return false;
		}

		$error = self::getTranslationRestrictions( $handle );
		if ( count( $error ) ) {
			$result = $error;
			return false;
		}

		return true;
	}

	/**
	 * Prevent editing of restricted languages when prioritized.
	 *
	 * @param MessageHandle $handle
	 * @return array array containing error message if restricted, empty otherwise
	 */
	private static function getTranslationRestrictions( MessageHandle $handle ) {
		global $wgTranslateDocumentationLanguageCode;

		// Allow adding message documentation even when translation is restricted
		if ( $handle->getCode() === $wgTranslateDocumentationLanguageCode ) {
			return array();
		}

		// Get the primary group id
		$ids = $handle->getGroupIds();
		$groupId = $ids[0];

		// Check if anything is prevented for the group in the first place
		$force = TranslateMetadata::get( $groupId, 'priorityforce' );
		if ( $force !== 'on' ) {
			return array();
		}

		// And finally check whether the language is not included in whitelist
		$languages = TranslateMetadata::get( $groupId, 'prioritylangs' );
		$filter = array_flip( explode( ',', $languages ) );
		if ( !isset( $filter[$handle->getCode()] ) ) {
			// @todo Default reason if none provided
			$reason = TranslateMetadata::get( $groupId, 'priorityreason' );
			return array( 'tpt-translation-restricted', $reason );
		}

		return array();
	}

	/**
	 * Prevent editing of translation pages directly.
	 * Hook: getUserPermissionsErrorsExpensive
	 */
	public static function preventDirectEditing( Title $title, User $user, $action, &$result ) {
		if ( self::$allowTargetEdit ) {
			return true;
		}

		$whitelist = array(
			'read', 'delete', 'undelete', 'deletedtext', 'deletedhistory',
			'review', // FlaggedRevs
		);
		if ( in_array( $action, $whitelist ) ) {
			return true;
		}

		$page = TranslatablePage::isTranslationPage( $title );
		if ( $page !== false && $page->getMarkedTag() ) {
			list( , $code ) = TranslateUtils::figureMessage( $title->getText() );
			$result = array(
				'tpt-target-page',
				':' . $page->getTitle()->getPrefixedText(),
				// This url shouldn't get cached
				wfExpandUrl( $page->getTranslationUrl( $code ) )
			);

			return false;
		}

		return true;
	}

	/**
	 * Prevent patrol links from appearing on translation pages.
	 * Hook: getUserPermissionsErrors
	 *
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @param mixed $result
	 *
	 * @return bool
	 */
	public static function preventPatrolling( Title $title, User $user, $action, &$result ) {
		if ( $action !== 'patrol' ) {
			return true;
		}

		$page = TranslatablePage::isTranslationPage( $title );

		if ( $page !== false ) {
			$result[] = 'tpt-patrolling-blocked';
			return false;
		}

		return true;
	}

	/**
	 * Redirects the delete action to our own for translatable pages.
	 * Hook: ArticleConfirmDelete
	 *
	 * @param $article Article
	 * @param $out OutputPage
	 * @param $reason
	 *
	 * @return bool
	 */
	public static function disableDelete( $article, $out, &$reason ) {
		$title = $article->getTitle();
		if ( TranslatablePage::isSourcePage( $title ) ||
			TranslatablePage::isTranslationPage( $title )
		) {
			$new = SpecialPage::getTitleFor(
				'PageTranslationDeletePage',
				$title->getPrefixedText()
			);
			$out->redirect( $new->getFullURL() );
		}

		return true;
	}

	/**
	 * Hook: ArticleViewHeader
	 *
	 * @param $article Article
	 * @param $outputDone
	 * @param $pcache
	 * @return bool
	 */
	public static function translatablePageHeader( &$article, &$outputDone, &$pcache ) {
		if ( $article->getOldID() ) {
			return true;
		}

		$transPage = TranslatablePage::isTranslationPage( $article->getTitle() );
		$context = $article->getContext();
		if ( $transPage ) {
			self::translationPageHeader( $context, $transPage );
		} else {
			// Check for pages that are tagged or marked
			self::sourcePageHeader( $context );
		}

		return true;
	}

	protected static function sourcePageHeader( IContextSource $context ) {
		$language = $context->getLanguage();
		$title = $context->getTitle();

		$page = TranslatablePage::newFromTitle( $title );

		$marked = $page->getMarkedTag();
		$ready = $page->getReadyTag();
		$latest = $title->getLatestRevID();

		$actions = array();
		if ( $marked && $context->getUser()->isAllowed( 'translate' ) ) {
			$actions[] =  self::getTranslateLink( $context, $page, $language->getCode() );
		}

		$hasChanges = $ready === $latest && $marked !== $latest;
		if ( $hasChanges ) {
			$diffUrl = $title->getFullURL( array( 'oldid' => $marked, 'diff' => $latest ) );

			if ( $context->getUser()->isAllowed( 'pagetranslation' ) ) {
				$pageTranslation = SpecialPage::getTitleFor( 'PageTranslation' );
				$params = array( 'target' => $title->getPrefixedText(), 'do' => 'mark' );

				if ( $marked === false ) {
					// This page has never been marked
					$linkDesc = $context->msg( 'translate-tag-markthis' )->escaped();
					$actions[] = Linker::linkKnown( $pageTranslation, $linkDesc, array(), $params );
				} else {
					$markUrl = $pageTranslation->getFullURL( $params );
					$actions[] = $context->msg( 'translate-tag-markthisagain', $diffUrl, $markUrl )
						->parse();
				}
			} else {
				$actions[] = $context->msg( 'translate-tag-hasnew', $diffUrl )->parse();
			}
		}

		if ( !count( $actions ) ) {
			return;
		}

		$header = Html::rawElement(
			'div',
			array(
				'class' => 'mw-pt-translate-header noprint nomobile',
				'dir' => $language->getDir(),
				'lang' => $language->getHtmlCode(),
			),
			$language->semicolonList( $actions )
		) . Html::element( 'hr' );

		$context->getOutput()->addHTML( $header );
	}

	private static function getTranslateLink(
		IContextSource $context, TranslatablePage $page, $langCode
	) {
		return Linker::linkKnown(
				SpecialPage::getTitleFor( 'Translate' ),
				$context->msg( 'translate-tag-translate-link-desc' )->escaped(),
				array(),
				array(
					'group' => $page->getMessageGroupId(),
					'language' => $langCode,
					'action' => 'page',
					'filter' => '',
				)
			);
	}

	protected static function translationPageHeader(
		IContextSource $context, TranslatablePage $page
	) {
		$title = $context->getTitle();
		if ( !$title->exists() ) {
			return;
		}

		list( , $code ) = TranslateUtils::figureMessage( $title->getText() );

		// Get the translation percentage
		$pers = $page->getTranslationPercentages();
		$per = 0;
		if ( isset( $pers[$code] ) ) {
			$per = $pers[$code] * 100;
		}

		$language = $context->getLanguage();

		if ( $page->getSourceLanguageCode() === $code ) {
			// If we are on the source language page, link to translate for user's language
			$msg = self::getTranslateLink( $context, $page, $language->getCode() );
		} else {
			$url = wfExpandUrl( $page->getTranslationUrl( $code ), PROTO_RELATIVE );
			$msg = $context->msg( 'tpt-translation-intro',
				$url,
				':' . $page->getTitle()->getPrefixedText(),
				$language->formatNum( $per )
			)->parse();
		}

		$header = Html::rawElement(
			'div',
			array(
				'class' => 'mw-pt-translate-header noprint',
				'dir' => $language->getDir(),
				'lang' => $language->getHtmlCode(),
			),
			$msg
		) . Html::element( 'hr' );

		$context->getOutput()->addHTML( $header );
	}

	/// Hook: SpecialPage_initList
	public static function replaceMovePage( &$list ) {
		$list['Movepage'] = 'SpecialPageTranslationMovePage';

		return true;
	}

	/// Hook: getUserPermissionsErrorsExpensive
	public static function lockedPagesCheck( Title $title, User $user, $action, &$result ) {
		if ( $action === 'read' ) {
			return true;
		}

		$cache = wfGetCache( CACHE_ANYTHING );
		$key = wfMemcKey( 'pt-lock', sha1( $title->getPrefixedText() ) );
		// At least memcached mangles true to "1"
		if ( $cache->get( $key ) !== false ) {
			$result = array( 'pt-locked-page' );

			return false;
		}

		return true;
	}

	/// Hook: SkinSubPageSubtitle
	public static function replaceSubtitle( &$subpages, $skin = null, OutputPage $out ) {
		$isTranslationPage = TranslatablePage::isTranslationPage( $out->getTitle() );
		if ( !$isTranslationPage
			&& !TranslatablePage::isSourcePage( $out->getTitle() )
		) {
			return true;
		}

		// Copied from Skin::subPageSubtitle()
		if ( $out->isArticle() && MWNamespace::hasSubpages( $out->getTitle()->getNamespace() ) ) {
			$ptext = $out->getTitle()->getPrefixedText();
			if ( strpos( $ptext, '/' ) !== false ) {
				$links = explode( '/', $ptext );
				array_pop( $links );
				if ( $isTranslationPage ) {
					// Also remove language code page
					array_pop( $links );
				}
				$c = 0;
				$growinglink = '';
				$display = '';
				$lang = $skin->getLanguage();

				foreach ( $links as $link ) {
					$growinglink .= $link;
					$display .= $link;
					$linkObj = Title::newFromText( $growinglink );

					if ( is_object( $linkObj ) && $linkObj->isKnown() ) {
						$getlink = Linker::linkKnown(
							SpecialPage::getTitleFor( 'MyLanguage', $growinglink ),
							htmlspecialchars( $display )
						);

						$c++;

						if ( $c > 1 ) {
							$subpages .= $lang->getDirMarkEntity() . $skin->msg( 'pipe-separator' )->escaped();
						} else {
							$subpages .= '&lt; ';
						}

						$subpages .= $getlink;
						$display = '';
					} else {
						$display .= '/';
					}

					$growinglink .= '/';
				}
			}

			return false;
		}

		return true;
	}

	/**
	 * Converts the edit tab (if exists) for translation pages to translate tab.
	 * Hook: SkinTemplateNavigation
	 * @since 2013.06
	 */
	public static function translateTab( Skin $skin, array &$tabs ) {
		$title = $skin->getTitle();
		$handle = new MessageHandle( $title );
		$code = $handle->getCode();
		$page = TranslatablePage::isTranslationPage( $title );
		if ( !$page ) {
			return true;
		}
		// The source language has a subpage too, but cannot be translated
		if ( $page->getSourceLanguageCode() === $code ) {
			return true;
		}

		if ( isset( $tabs['views']['edit'] ) ) {
			$tabs['views']['edit']['text'] = $skin->msg( 'tpt-tab-translate' )->text();
			$tabs['views']['edit']['href'] = $page->getTranslationUrl( $code );
		}

		return true;
	}

	/**
	 * Hook to update source and destination translation pages on moving translation units
	 * Hook: TitleMoveComplete
	 * @since 2014.08
	 */
	public static function onMoveTranslationUnits( Title $ot, Title $nt, User $user,
		$oldid, $newid, $reason
	) {
		// Do the update only once. In case running by job queue, the update is not done here
		if ( self::$jobQueueRunning ) {
			return;
		}

		$groupLast = null;
		foreach ( array( $ot, $nt ) as $title ) {
			$handle = new MessageHandle( $title );
			if ( !$handle->isValid() ) {
				continue;
			}

			// Documentation pages are never translation pages
			if ( $handle->isDoc() ) {
				continue;
			}

			$group = $handle->getGroup();
			if ( !$group instanceof WikiPageMessageGroup ) {
				continue;
			}

			$language = $handle->getCode();

			// Ignore pages such as Translations:Page/unit without language code
			if ( (string)$language === '' ) {
				continue;
			}

			// Update the page only once if source and destination units
			// belong to the same page
			if ( $group !== $groupLast ) {
				$groupLast = $group;
				$page = TranslatablePage::newFromTitle( $group->getTitle() );
				self::updateTranslationPage( $page, $language, $user, 0, $reason );
			}
		}
	}

	/**
	 * Hook to update translation page on deleting a translation unit
	 * Hook: ArticleDeleteComplete
	 * @since 2016.05
	 */
	public static function onDeleteTranslationUnit( WikiPage &$unit, User &$user, $reason,
		$id, $content, $logEntry
	) {
		// Do the update. In case job queue is doing the work, the update is not done here
		if ( self::$jobQueueRunning ) {
			return;
		}
		$title = $unit->getTitle();

		$handle = new MessageHandle( $title );
		if ( !$handle->isValid() ) {
			return;
		}

		$group = $handle->getGroup();
		if ( !$group instanceof WikiPageMessageGroup ) {
			return true;
		}

		// There could be interfaces which may allow mass deletion (eg. Nuke). Since they could
		// delete many units in one request, it may do several unnecessary edits and cause several
		// other unnecessary updates to be done slowing down the user. To avoid that, we push this
		// to a queue that is run after the current transaction is committed so that we can see the
		// version that is after all the deletions has been done. This allows us to do just one edit
		// per translation page after the current deletions has been done. This is sort of hackish
		// but this is better user experience and is also more efficent.
		static $queuedPages = array();
		$target = $group->getTitle();
		$langCode = $handle->getCode();
		$targetPage = $target->getSubpage( $langCode )->getPrefixedText();

		if ( !isset( $queuedPages[ $targetPage ] ) ) {
			$queuedPages[ $targetPage ] = true;

			$dbw = wfGetDB( DB_MASTER );
			$dbw->onTransactionIdle( function () use ( $dbw, $queuedPages, $targetPage,
				$target, $handle, $langCode, $user, $reason
			) {
				// For atomicity
				$dbw->setFlag( DBO_TRX );

				$page = TranslatablePage::newFromTitle( $target );

				MessageGroupStats::clear( $handle );
				MessageGroupStats::forItem( $page->getMessageGroupId(), $langCode );

				if ( !$handle->isDoc() ) {
					// Assume that $user and $reason for the first deletion is the same for all
					self::updateTranslationPage( $page, $langCode, $user, 0, $reason );
				}

				// If a unit was deleted after the edit here is done, this allows us
				// to add the page back to the queue again and so we can make another
				// edit here with the latest changes.
				unset( $queuedPages[ $targetPage ] );
			} );
		}
	}
}
