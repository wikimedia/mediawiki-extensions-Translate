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

	/// Hook: OutputPageBeforeHTML
	public static function injectCss( OutputPage $out, /*string*/$text ) {
		global $wgTranslatePageTranslationULS;

		$title = $out->getTitle();
		$isSource = TranslatablePage::isSourcePage( $title );
		$isTranslation = TranslatablePage::isTranslationPage( $title );

		if ( $isSource || $isTranslation ) {
			$out->addModuleStyles( 'ext.translate.min' );
			$out->addModules( 'ext.translate' );
			if ( $wgTranslatePageTranslationULS ) {
				$out->addModules( 'ext.translate.pagetranslation.uls' );
			}

			// Per bug T63331
			$type =  $isSource ? 'source' : 'translation';
			$out->addJsConfigVars( 'wgTranslatePageTranslation', $type );
		}

		return true;
	}

	/**
	 * Hook: PageContentSaveComplete
	 */
	public static function onSectionSave( WikiPage $wikiPage, User $user, $content, $summary,
		$minor, $_1, $_2, $flags, $revision
	) {
		$title = $wikiPage->getTitle();

		if ( $content instanceof TextContent ) {
			$text = $content->getNativeData();
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
			$wikiPage = WikiPage::factory( $title );
			$wikiPage->doPurge();
		}

		// And the source page itself too
		$wikiPage = WikiPage::factory( $page->getTitle() );
		$wikiPage->doPurge();
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
			$isSourcePage = true;
		} else {
			$isSourcePage = true;
		}

		if ( $page === false || $page->getMarkedTag() === false ) {
			return '';
		}

		$marked = $page->getMarkedTag();
		$ready = $page->getReadyTag();
		$latest = $currentTitle->getLatestRevId();
		$canmark = ( $ready === $latest ) && ( $marked !== $latest );

		$status = $page->getTranslationPercentages();
		$currentLanguage = $currentTitle->getPageLanguage()->getCode();

		$percentTranslation = $status[$currentLanguage];

		$outputArr = LangPopulate::langPopulateOrder( $parser, $page );
		$languages = $outputArr[1];
		$total = $outputArr[0];

		// Fix title
		$pageTitle = $page->getTitle();

		// This way the parser knows to fragment the parser cache by language code
		$userLang = $parser->getOptions()->getUserLangObj();
		$userLangCode = $userLang->getCode();

		$translateStyle = $isSourcePage ? "width:100%" : "width:85%;border-right:0";
		$percentInfo = '';
		$markButton = '';
		$downButton = '';
		$secondaryToolbar = '';

		if ( $isSourcePage ) {
			if ( $canmark && $parser->getUser()->isAllowed( 'pagetranslation' ) ) {
				$par = array( 'target' => $currentTitle->getPrefixedText() );
				$translate = SpecialPage::getTitleFor( 'PageTranslation' );

				$translateStyle = "width:44%";
				$markStyle = '';

				$markButton = Html::rawElement('li', array(
					'class' => 'mw-translate-language mw-translate-translate mw-translate-mark',
					'style' => $markStyle,
					'title' => wfMessage( 'tpt-langbar-mark-tooltip' )
				),
					wfMessage( 'tpt-langbar-mark' )
				);

				$markButton = Linker::link( $translate, $markButton, array(), $par );
				// This page has previous unmarked changes
				if ( $marked ) {
					$downButton = Html::openElement(
						'li',
						array( 'class' => 'mw-translate-action' )
					);
					$downButton .= Html::element( 'img', array(
							'src' => TranslateUtils::assetPath( "resources/images/down.png" ),
							'alt' => 'More',
							'title' => 'More',
							'width' => '15',
							'height' => '15',
						)
					);
					$downButton .= Html::openElement('ul', array(
							'class' => 'mw-translate-options'
						)
					);
					$downButton .= '<span class="caret-before"></span>
						<span class="caret-after"></span>';

					$changesButton = Html::rawElement( 'li', array(
							'class' => 'mw-translate-language mw-translate-option'
						),
						wfMessage( 'tpt-langbar-viewchanges' )
					);
					$changesButton = Linker::link(
						$currentTitle,
						$changesButton,
						array( 'title' => wfMessage( 'tpt-langbar-viewchanges-tooltip' ) ),
						array( 'oldid' => $marked, 'diff' => $latest )
					);
					$downButton .= $changesButton . '</ul></li>';
				}
			}
		} else {
			$percentInfo .= Html::element( 'li', array(
					'class' => 'mw-translate-language mw-translate-translate mw-translate-percentage'
				),
				$percentTranslation*100 . '%'
			);
		}

		if ( $marked && $parser->getUser()->isAllowed( 'translate' ) ) {
			$par = array(
				'group' => $page->getMessageGroupId(),
				'language' => $currentLanguage,
				'action' => 'page',
				'filter' => ''
			);
			$translate = SpecialPage::getTitleFor( 'Translate' );
			$translateButton = Html::rawElement( 'li', array(
					'class' => 'mw-translate-language mw-translate-translate',
					'style' => $translateStyle,
					'title' => wfMessage(
						'tpt-langbar-translate-tooltip',
						TranslateUtils::getLanguageName( $currentLanguage, $userLangCode )
					)
				), wfMessage( 'tpt-tab-translate' )
			);
			$translateButton = Linker::link(
				$translate,
				$translateButton,
				array(),
				$par
			);

			$secondaryToolbar .= HTML::openElement( 'ul', array(
					'class' => 'mw-translate-langbar-row'
				)
			);
			$secondaryToolbar .= $percentInfo . $translateButton . $markButton . $downButton;
			$secondaryToolbar .= HTML::closeElement( 'ul' );
		}

		$langBar = HTML::rawElement( 'div',
			array( 'class' => 'mw-translate-langbar-min' ),
			'<b>' . wfMessage( 'tpt-langbar-min-text', array( $total ) ) . '</b>'
		);
		$langBar .= HTML::openElement( 'div', array( 'class' => 'mw-translate-langbar' ) );
		$langBar .= HTML::openElement( 'div',
			array( 'class' => 'mw-translate-langbar-container' )
		);
		$langBar .= HTML::element( 'span', array( 'class' => 'caret-before' ) );
		$langBar .= HTML::element( 'span', array( 'class' => 'caret-after' ) );

		$langBar .= HTML::openElement( 'ul', array( 'class' => 'mw-translate-langbar-row' ) );

		$langNames = array_keys( $languages );
		$source = reset( $langNames );
		$last = end( $langNames );

		foreach ( $languages as $code => $percent ) {
			$name = TranslateUtils::getLanguageName( $code, $userLangCode );
			$name = htmlspecialchars( $name ); // Unlikely, but better safe

			// Add links to other languages
			$suffix = ( $code === $source ) ? '' : "/$code";
			$targetTitleString = $pageTitle->getDBkey() . $suffix;
			$subpage = Title::makeTitle( $pageTitle->getNamespace(), $targetTitleString );

			$classes = array( 'mw-translate-language' );
			if ( $currentTitle->equals( $subpage ) ) {
				$classes[] = 'mw-translate-selected';
			}
			if ( $code === $source ) {
				$classes[] = 'mw-translate-source';
			}
			if ( $code === $last ) {
				$classes[] = 'mw-translate-language-last';
			}

			$langCell = Html::rawElement( 'li',
				array( 'class' => implode( " ", $classes ) ),
				$name
			);
			if ( $subpage->isKnown() ) {
				$langCell = Linker::linkKnown( $subpage, $langCell );
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
				$langCell = Linker::link( $specialTranslateTitle, $langCell, $attribs, $params );
			}

			$langBar .= $langCell;
		}

		$langBar .= '<li class="mw-translate-language mw-translate-viewmore">...'
			. $outputArr[2]
			. '</li>';

		$langBar .= HTML::closeElement( 'ul' );
		$langBar .= $secondaryToolbar;
		$langBar .= HTML::closeElement( 'div' );
		$langBar .= HTML::closeElement( 'div' );
		return $langBar;
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
	public static function preventUnknownTranslations( Title $title, User $user,
		$action, &$result
	) {
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
	 * Prevent editing of restricted languages.
	 * Hook: getUserPermissionsErrorsExpensive
	 * @since 2012-03-01
	 */
	public static function preventRestrictedTranslations( Title $title, User $user,
		$action, &$result
	) {
		global $wgTranslateDocumentationLanguageCode;
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

		// Allow adding message documentation even when translation is restricted
		if ( $handle->getCode() === $wgTranslateDocumentationLanguageCode ) {
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
					':' . $page->getTitle()->getPrefixedText(),
					// This url shouldn't get cached
					wfExpandUrl( $page->getTranslationUrl( $code ) )
				);

				return false;
			}
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
			$out->redirect( $new->getFullUrl() );
		}

		return true;
	}

	/// Hook: SpecialPage_initList
	public static function replaceMovePage( &$list ) {
		$list['Movepage'] = 'SpecialPageTranslationMovePage';

		return true;
	}

	/// Hook: getUserPermissionsErrorsExpensive
	public static function lockedPagesCheck( Title $title, User $user, $action, &$result ) {
		if ( $action == 'read' ) {
			return true;
		}

		$cache = wfGetCache( CACHE_ANYTHING );
		$key = wfMemcKey( 'pt-lock', sha1( $title->getPrefixedText() ) );
		// At least memcached mangles true to "1"
		if ( $cache->get( $key ) == true ) {
			$result = array( 'pt-locked-page' );

			return false;
		}

		return true;
	}

	/// Hook: SkinSubPageSubtitle
	public static function replaceSubtitle( &$subpages, $skin = null, OutputPage $out ) {
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

	/**
	 * Converts the edit tab (if exists) for translation pages to translate tab.
	 * Hook: SkinTemplateNavigation
	 * @since 2013.06
	 */
	static function translateTab( Skin $skin, array &$tabs ) {
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
			if ( strval( $code ) === '' ) {
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
}
