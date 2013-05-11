<?php
/**
 * Contains class with page translation feature hooks.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2013, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Hooks for page translation.
 *
 * @ingroup PageTranslation
 */
class PageTranslationHooks {
	// Uuugly hack
	public static $allowTargetEdit = false;

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
			} catch ( TPException $e ) {
				// Show ugly preview without processed <translate> tags
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

	/// Hook: OutputPageBeforeHTML
	public static function injectCss( OutputPage $out, /*string*/$text ) {
		global $wgTranslatePageTranslationULS;

		$title = $out->getTitle();
		if ( TranslatablePage::isSourcePage( $title ) || TranslatablePage::isTranslationPage( $title ) ) {
			$out->addModules( 'ext.translate' );
			if ( $wgTranslatePageTranslationULS ) {
				$out->addModules( 'ext.translate.pagetranslation.uls' );
			}
		}

		return true;
	}

	/**
	 * Hook: ArticleSaveComplete, PageContentSaveComplete
	 *
	 * Change to this line once BC is 1.21 and later:
	 * public static function onSectionSave( WikiPage $wikiPage, User $user, $content, $summary,
	 */
	public static function onSectionSave( $wikiPage, User $user, $content, $summary,
		$minor, $_, $_, $flags, $revision
	) {
		$title = $wikiPage->getTitle();

		if ( $content instanceof TextContent ) {
			$text = $content->getNativeData();
		} elseif ( is_string( $content ) ) {
			// BC 1.20
			$text = $content;
		} else {
			// Screw it, not interested
			return true;
		}

		// Some checks
		$handle = new MessageHandle( $title );

		// We are only interested in the translations namespace
		if ( !$handle->isPageTranslation() || !$handle->isValid() ) {
			return true;
		}

		// Do not trigger renders for fuzzy
		if ( strpos( $text, TRANSLATE_FUZZY ) !== false ) {
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
		$target = Title::makeTitle( $source->getNamespace(), $source->getDBkey() . "/$code" );

		// We don't know and don't care
		$flags &= ~EDIT_NEW & ~EDIT_UPDATE;

		// Update the target page
		$job = TranslateRenderJob::newJob( $target );
		$job->setUser( $user );
		$job->setSummary( $summary );
		$job->setFlags( $flags );
		$job->run();

		// Regenerate translation caches
		$page->getTranslationPercentages( 'force' );

		// Invalidate caches
		$pages = $page->getTranslationPages();
		foreach ( $pages as $title ) {
			$article = new Article( $title, 0 );
			$article->doPurge();
		}

		// And the source page itself too
		$article = new Article( $page->getTitle(), 0 );
		$article->doPurge();
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
		$userLangCode = $parser->getOptions()->getUserLang();
		$userLangDir = $parser->getOptions()->getUserLangObj()->getDir();
		// Should call $page->getMessageGroup()->getSourceLanguage(), but
		// group is sometimes null on WMF during page moves, reason unknown.
		// This should do the same thing for now.
		$sourceLanguage = $pageTitle->getPageLanguage()->getCode();

		$languages = array();
		foreach ( $status as $code => $percent ) {
			$name = TranslateUtils::getLanguageName( $code, $userLangCode );
			$name = htmlspecialchars( $name ); // Unlikely, but better safe

			/* Percentages are too accurate and take more
			 * space than simple images */
			$percent *= 100;
			if ( $percent < 20 ) {
				$image = 1;
			} elseif ( $percent < 40 ) {
				$image = 2;
			} elseif ( $percent < 60 ) {
				$image = 3;
			} elseif ( $percent < 80 ) {
				$image = 4;
			} else {
				$image = 5;
			}

			$percentImage = Xml::element( 'img', array(
				'src' => TranslateUtils::assetPath( "resources/images/prog-$image.png" ),
				'alt' => "$percent%", // @todo i18n missing.
				'title' => "$percent%", // @todo i18n missing.
				'width' => '9',
				'height' => '9',
			) );

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
			}

			if ( count( $classes ) ) {
				$attribs = array( 'class' => implode( ' ', $classes ) );
				$name = Html::rawElement( 'span', $attribs, $name );
			}

			if ( $currentTitle->equals( $subpage ) ) {
				// No further processing needed
			} elseif ( $subpage->isKnown() ) {
				$name = Linker::linkKnown( $subpage, $name );
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
				$attribs = array(
					'title' => wfMessage( 'tpt-languages-zero' )->text(),
					'class' => 'new', // For red link color
				);
				$name = Linker::link( $specialTranslateTitle, $name, $attribs, $params );
			}

			$languages[] = "$name&#160;$percentImage";
		}

		// dirmark (rlm/lrm) is added, because languages with RTL names can
		// mess the display
		$lang = Language::factory( $userLangCode );
		$sep = wfMessage( 'tpt-languages-separator' )->inLanguage( $lang )->plain();
		$sep .= $lang->getDirMark();
		$languages = implode( $sep, $languages );

		$out = Html::openElement( 'div', array(
			'class' => 'mw-pt-languages noprint',
			'lang' => $userLangCode,
			'dir' => $userLangDir
		) );
		$out .= Html::openElement( 'table' );
		$out .= Html::openElement( 'tbody' );
		$out .= Html::openElement( 'tr', array( 'valign' => 'top' ) );
		$out .= Html::rawElement( 'td',
			array( 'class' => 'mw-pt-languages-label' ),
			wfMessage( 'tpt-languages-legend' )->escaped()
		);
		$out .= Html::rawElement( 'td',
			array( 'class' => 'mw-pt-languages-list' ),
			$languages
		);
		$out .= Html::closeElement( 'tr' );
		$out .= Html::closeElement( 'tbody' );
		$out .= Html::closeElement( 'table' );
		$out .= Html::closeElement( 'div' );

		return $out;
	}

	/**
	 * Display nice error when editing content.
	 * Hook: EditFilterMergedContent (since MW 1.21)
	 */
	public static function tpSyntaxCheckForEditContent( $context, $content, $status, $summary ) {
		if ( !( $content instanceof TextContent ) ) {
			return true; // whatever.
		}

		$text = $content->getNativeData();
		$title = $context->getTitle();

		$e = self::tpSyntaxError( $title, $text );

		if ( $e ) {
			$msg = $e->getMsg();
			//$msg is an array containing a message key followed by any parameters.
			//todo: use Message object instead.

			call_user_func_array( array( $status, 'fatal' ), $msg );
		}

		return true;
	}

	/**
	 * Display nice error for editpage.
	 * Hook: EditFilterMerged (until MW 1.20)
	 */
	public static function tpSyntaxCheckForEditPage( $editpage, $text, &$error, $summary ) {
		$title = $editpage->getTitle();
		$e = self::tpSyntaxError( $title, $text );

		if ( $e ) {
			$error .= Html::rawElement( 'div', array( 'class' => 'error' ), $e->getMessage() );
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
	 * edit conflict if there wasn't tpSyntaxCheckForEditPage
	 * Hook: ArticleSave, PageContentSave
	 */
	public static function tpSyntaxCheck( $wikiPage, $user, $content, $summary,
		$minor, $_, $_, $flags, $status
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
	 * Hook: ArticleSaveComplete, PageContentSaveComplete
	 */
	public static function addTranstag( $wikiPage, $user, $content, $summary,
		$minor, $_, $_, $flags, $revision
	) {
		// We are not interested in null revisions
		if ( $revision === null ) {
			return true;
		}

		if ( $content instanceof TextContent ) {
			$text = $content->getNativeData();
		} elseif ( is_string( $content ) ) {
			// BC 1.20
			$text = $content;
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

		/* Title might be null when using replicated databases.
		 * Even in that case null revisions should have valid
		 * titles since e778bf8. See bug 32983. */
		if ( !$title ) {
			return true;
		}

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
		// FIXME: optimize away this query. Bug 36588.
		$oldTextId = $dbw->selectField( $table, $field, $conds, __METHOD__ );

		if ( strval( $newTextId ) !== strval( $oldTextId ) ) {
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
	 * Prevent editing of unknown pages in Translations namespace.
	 * Hook: getUserPermissionsErrorsExpensive
	 */
	public static function preventUnknownTranslations( Title $title, User $user, $action, &$result ) {
		$handle = new MessageHandle( $title );
		if ( $handle->isPageTranslation() && $action === 'edit' ) {
			if ( !$handle->isValid() ) {
				$result = array( 'tpt-unknown-page' );

				return false;
			}
		}

		return true;
	}

	/**
	 * Prevent editing of restricted languages
	 * Hook: getUserPermissionsErrorsExpensive
	 * @since 2012-03-01
	 */
	public static function preventRestrictedTranslations( Title $title, User $user,
		$action, &$result
	) {
		// Preventing editing (includes creation) should be enough
		if ( $action !== 'edit' ) {
			return true;
		}

		$handle = new MessageHandle( $title );
		if ( !$handle->isValid() ) {
			return true;
		}

		// Get the primary group id
		$ids = $handle->getGroupIds();
		$groupId = $ids[0];

		// Check if anything is prevented for the group in the first place
		$force = TranslateMetadata::get( $groupId, 'priorityforce' );
		if ( $force !== 'on' ) {
			return true;
		}

		// And finally check whether the language is not included in whitelist
		$languages = TranslateMetadata::get( $groupId, 'prioritylangs' );
		$filter = array_flip( explode( ',', $languages ) );
		if ( !isset( $filter[$handle->getCode()] ) ) {
			// @todo Default reason if none provided
			$reason = TranslateMetadata::get( $groupId, 'priorityreason' );
			$result = array( 'tpt-translation-restricted', $reason );

			return false;
		}

		return true;
	}

	/**
	 * Prevent editing of translation pages directly.
	 * Hook: getUserPermissionsErrorsExpensive
	 */
	public static function preventDirectEditing( Title $title, User $user, $action, &$result ) {
		$page = TranslatablePage::isTranslationPage( $title );
		$whitelist = array(
			'read' => true,
			'delete' => true,
			'review' => true, // FlaggedRevs
		);

		if ( $page !== false && !isset( $whitelist[$action] ) ) {
			if ( self::$allowTargetEdit ) {
				return true;
			}

			if ( $page->getMarkedTag() ) {
				list( , $code ) = TranslateUtils::figureMessage( $title->getText() );
				$result = array(
					'tpt-target-page',
					$page->getTitle()->getPrefixedText(),
					$page->getTranslationUrl( $code )
				);

				return false;
			}
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
		if ( TranslatablePage::isSourcePage( $title ) || TranslatablePage::isTranslationPage( $title ) ) {
			$new = SpecialPage::getTitleFor( 'PageTranslationDeletePage', $title->getPrefixedText() );
			$out->redirect( $new->getFullUrl() );
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

		$title = $article->getTitle();

		if ( TranslatablePage::isTranslationPage( $title ) ) {
			self::translationPageHeader( $title );
		} else {
			// Check for pages that are tagged or marked
			self::sourcePageHeader( $title );
		}

		return true;
	}

	protected static function sourcePageHeader( Title $title ) {
		global $wgUser, $wgLang;

		$page = TranslatablePage::newFromTitle( $title );

		$marked = $page->getMarkedTag();
		$ready = $page->getReadyTag();

		$title = $page->getTitle();

		$latest = $title->getLatestRevId();
		$canmark = $ready === $latest && $marked !== $latest;

		$actions = array();

		if ( $marked && $wgUser->isAllowed( 'translate' ) ) {
			$par = array(
				'group' => $page->getMessageGroupId(),
				'language' => $wgLang->getCode(),
				'task' => 'view'
			);

			$translate = SpecialPage::getTitleFor( 'Translate' );
			$linkDesc = wfMessage( 'translate-tag-translate-link-desc' )->escaped();
			$actions[] = Linker::link( $translate, $linkDesc, array(), $par );
		}

		if ( $canmark ) {
			$diffUrl = $title->getFullUrl( array( 'oldid' => $marked, 'diff' => $latest ) );
			$par = array( 'target' => $title->getPrefixedText() );
			$translate = SpecialPage::getTitleFor( 'PageTranslation' );

			if ( $wgUser->isAllowed( 'pagetranslation' ) ) {
				// This page has never been marked
				if ( $marked === false ) {
					$linkDesc = wfMessage( 'translate-tag-markthis' )->escaped();
					$actions[] = Linker::link( $translate, $linkDesc, array(), $par );
				} else {
					$markUrl = $translate->getFullUrl( $par );
					$actions[] = wfMessage( 'translate-tag-markthisagain', $diffUrl, $markUrl )->parse();
				}
			} else {
				$actions[] = wfMessage( 'translate-tag-hasnew', $diffUrl )->parse();
			}
		}

		if ( !count( $actions ) ) {
			return;
		}

		$legend = Html::rawElement(
			'div',
			array( 'class' => 'mw-pt-translate-header noprint nomobile' ),
			$wgLang->semicolonList( $actions )
		) . Html::element( 'hr' );

		global $wgOut;

		$wgOut->addHTML( $legend );
	}

	protected static function translationPageHeader( Title $title ) {
		global $wgOut;

		if ( !$title->exists() ) {
			return;
		}

		// Check if applicable
		$page = TranslatablePage::isTranslationPage( $title );
		if ( $page === false ) {
			return;
		}

		list( , $code ) = TranslateUtils::figureMessage( $title->getText() );

		// Get the translation percentage
		$pers = $page->getTranslationPercentages();
		$per = 0;
		if ( isset( $pers[$code] ) ) {
			$per = $pers[$code] * 100;
		}
		$titleText = $page->getTitle()->getPrefixedText();
		$url = $page->getTranslationUrl( $code );

		// Output
		$wrap = '<div class="mw-translate-page-info">$1</div>';
		$wgOut->wrapWikiMsg( $wrap, array( 'tpt-translation-intro', $url, $titleText, $per ) );

		$wgOut->addHTML( '<hr />' );
	}

	/// Hook: SpecialPage_initList
	public static function replaceMovePage( &$list ) {
		$old = is_array( $list['Movepage'] );
		$list['Movepage'] = array( 'SpecialPageTranslationMovePage', $old );

		return true;
	}

	/// Hook: getUserPermissionsErrorsExpensive
	public static function lockedPagesCheck( Title $title, User $user, $action, &$result ) {
		if ( $action == 'read' ) {
			return true;
		}

		$cache = wfGetCache( CACHE_ANYTHING );
		$key = wfMemcKey( 'pt-lock', $title->getPrefixedText() );
		// At least memcached mangles true to "1"
		if ( $cache->get( $key ) == true ) {
			$result = array( 'pt-locked-page' );

			return false;
		}

		return true;
	}

	/// Hook: SkinSubPageSubtitle
	public static function replaceSubtitle( &$subpages, $skin = null, $out = null ) {
		global $wgOut;
		// $out was only added in some MW version
		if ( $out === null ) {
			$out = $wgOut;
		}

		if ( !TranslatablePage::isTranslationPage( $out->getTitle() )
			&& !TranslatablePage::isSourcePage( $out->getTitle() )
		) {
			return true;
		}

		// Copied from Skin::subPageSubtitle()
		if ( $out->isArticle() && MWNamespace::hasSubpages( $out->getTitle()->getNamespace() ) ) {
			$ptext = $out->getTitle()->getPrefixedText();
			if ( preg_match( '/\//', $ptext ) ) {
				$links = explode( '/', $ptext );
				array_pop( $links );
				// Also pop of one extra for language code is needed
				if ( TranslatablePage::isTranslationPage( $out->getTitle() ) ) {
					array_pop( $links );
				}
				$c = 0;
				$growinglink = '';
				$display = '';

				foreach ( $links as $link ) {
					$growinglink .= $link;
					$display .= $link;
					$linkObj = Title::newFromText( $growinglink );

					if ( is_object( $linkObj ) && $linkObj->exists() ) {
						$getlink = Linker::linkKnown(
							SpecialPage::getTitleFor( 'MyLanguage', $growinglink ),
							htmlspecialchars( $display )
						);

						$c++;

						if ( $c > 1 ) {
							$subpages .= wfMessage( 'pipe-separator' )->plain();
						} else {
							// This one is stupid imho, doesn't work with chihuahua
							// $subpages .= '&lt; ';
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

	/// Hook: SpecialTranslate::executeTask
	public static function sourceExport( RequestContext $context,
		TranslateTask $task = null, MessageGroup $group, array $options
	) {
		if ( $task || $options['taction'] !== 'export'
			|| !$group instanceof WikiPageMessageGroup
		) {
			return true;
		}

		$page = TranslatablePage::newFromTitle( $group->getTitle() );
		$collection = $group->initCollection( $options['language'] );
		$collection->loadTranslations( DB_MASTER );
		$text = $page->getParse()->getTranslationPageText( $collection );
		$display = $page->getPageDisplayTitle( $options['language'] );
		if ( $display ) {
			$text = "{{DISPLAYTITLE:$display}}$text";
		}
		$output = Html::element( 'textarea', array( 'rows' => 25 ), $text );
		$context->getOutput()->addHtml( $output );

		return false;
	}
}
