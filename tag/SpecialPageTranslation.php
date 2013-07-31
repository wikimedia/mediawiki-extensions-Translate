<?php
/**
 * Contains logic for special page Special:ImportTranslations.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2013 Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0+
 */

/**
 * A special page for marking revisions of pages for translation.
 *
 * This page is the main tool for translation administrators in the wiki.
 * It will list all pages in their various states and provides actions
 * that are suitable for given translatable page.
 *
 * @ingroup SpecialPage PageTranslation
 */
class SpecialPageTranslation extends SpecialPage {
	function __construct() {
		parent::__construct( 'PageTranslation' );
	}

	public function execute( $parameters ) {
		$this->setHeaders();

		$user = $this->getUser();
		$request = $this->getRequest();

		$target = $request->getText( 'target', $parameters );
		$revision = $request->getInt( 'revision', 0 );
		$action = $request->getVal( 'do' );
		$out = $this->getOutput();

		TranslateUtils::addSpecialHelpLink(
			$out,
			'Help:Extension:Translate/Page_translation_example'
		);

		// No specific page or invalid input
		$title = Title::newFromText( $target );
		if ( !$title ) {
			if ( $target !== '' ) {
				$out->addWikiMsg( 'tpt-badtitle' );
			} else {
				$this->listPages();
			}

			return;
		}

		// Check permissions
		if ( !$user->isAllowed( 'pagetranslation' ) ) {
			throw new PermissionsError( 'pagetranslation' );
		}

		// Check permissions
		if ( $request->wasPosted() && !$user->matchEditToken( $request->getText( 'token' ) ) ) {
			throw new PermissionsError( 'pagetranslation' );
		}

		// We are processing some specific page
		if ( !$title->exists() ) {
			$out->addWikiMsg( 'tpt-nosuchpage', $title->getPrefixedText() );

			return;
		}

		if ( $action === 'discourage' || $action === 'encourage' ) {
			$id = TranslatablePage::getMessageGroupIdFromTitle( $title );
			$current = MessageGroups::getPriority( $id );

			if ( $action === 'encourage' ) {
				$new = '';
			} else {
				$new = 'discouraged';
			}

			if ( $new !== $current ) {
				MessageGroups::setPriority( $id, $new );
				$entry = new ManualLogEntry( 'pagetranslation', $action );
				$entry->setPerformer( $user );
				$entry->setTarget( $title );
				$logid = $entry->insert();
				$entry->publish( $logid );
			}

			$this->listPages();

			$group = MessageGroups::getGroup( $id );
			$parents = MessageGroups::getSharedGroups( $group );
			MessageGroupStats::clearGroup( $parents );

			return;
		}

		if ( $action === 'unmark' ) {
			$page = TranslatablePage::newFromTitle( $title );
			$page->unmarkTranslatablePage();
			$page->getTitle()->invalidateCache();

			$entry = new ManualLogEntry( 'pagetranslation', 'unmark' );
			$entry->setPerformer( $user );
			$entry->setTarget( $page->getTitle() );
			$logid = $entry->insert();
			$entry->publish( $logid );

			$out->addWikiMsg( 'tpt-unmarked', $title->getPrefixedText() );

			return;
		}

		if ( $revision === 0 ) {
			// Get the latest revision
			$revision = intval( $title->getLatestRevID() );
		}

		$page = TranslatablePage::newFromRevision( $title, $revision );
		if ( !$page instanceof TranslatablePage ) {
			$out->addWikiMsg( 'tpt-notsuitable', $title->getPrefixedText(), $revision );

			return;
		}

		if ( $revision !== intval( $title->getLatestRevID() ) ) {
			// We do want to notify the reviewer if the underlying page changes during review
			$target = $title->getFullUrl( array( 'oldid' => $revision ) );
			$link = "<span class='plainlinks'>[$target $revision]</span>";
			$out->addWikiMsg( 'tpt-oldrevision', $title->getPrefixedText(), $link );

			return;
		}

		$lastrev = $page->getMarkedTag();
		if ( $lastrev !== false && $lastrev === $revision ) {
			$out->addWikiMsg( 'tpt-already-marked' );
			$this->listPages();

			return;
		}

		// This will modify the sections to include name property
		$error = false;
		$sections = $this->checkInput( $page, $error );

		// Non-fatal error which prevents saving
		if ( $error === false && $request->wasPosted() ) {
			$err = $this->markForTranslation( $page, $sections );

			if ( $err ) {
				call_user_func_array( array( $out, 'addWikiMsg' ), $err );
			} else {
				$this->showSuccess( $page );
				$this->listPages();
			}

			return;
		}

		$this->showPage( $page, $sections );
	}

	/**
	 * @param TranslatablePage $page
	 */
	public function showSuccess( TranslatablePage $page ) {
		$titleText = $page->getTitle()->getPrefixedText();
		$num = $this->getLanguage()->formatNum( $page->getParse()->countSections() );
		$link = SpecialPage::getTitleFor( 'Translate' )->getFullUrl(
			array( 'group' => $page->getMessageGroupId() ) );

		$this->getOutput()->addWikiMsg( 'tpt-saveok', $titleText, $num, $link );
	}

	public function loadPagesFromDB() {
		$dbr = wfGetDB( DB_MASTER );
		$tables = array( 'page', 'revtag' );
		$vars = array(
			'page_id',
			'page_title',
			'page_namespace',
			'page_latest',
			'MAX(rt_revision) AS rt_revision',
			'rt_type'
		);
		$conds = array(
			'page_id=rt_page',
			'rt_type' => array( RevTag::getType( 'tp:mark' ), RevTag::getType( 'tp:tag' ) ),
		);
		$options = array(
			'ORDER BY' => 'page_namespace, page_title',
			'GROUP BY' => 'page_id, rt_type',
		);
		$res = $dbr->select( $tables, $vars, $conds, __METHOD__, $options );

		return $res;
	}

	protected function buildPageArray( /*db result*/$res ) {
		$pages = array();
		foreach ( $res as $r ) {
			// We have multiple rows for same page, because of different tags
			if ( !isset( $pages[$r->page_id] ) ) {
				$pages[$r->page_id] = array();
				$title = Title::newFromRow( $r );
				$pages[$r->page_id]['title'] = $title;
				$pages[$r->page_id]['latest'] = intval( $title->getLatestRevID() );
			}

			$tag = RevTag::typeToTag( $r->rt_type );
			$pages[$r->page_id][$tag] = intval( $r->rt_revision );
		}

		return $pages;
	}

	/**
	 * @param array $in
	 * @return array
	 */
	protected function classifyPages( array $in ) {
		$out = array(
			'proposed' => array(),
			'active' => array(),
			'broken' => array(),
			'discouraged' => array(),
		);

		foreach ( $in as $index => $page ) {
			if ( !isset( $page['tp:mark'] ) ) {
				// Never marked, check that the latest version is ready
				if ( $page['tp:tag'] === $page['latest'] ) {
					$out['proposed'][$index] = $page;
				} // Otherwise ignore such pages
			} elseif ( $page['tp:tag'] === $page['latest'] ) {
				// Marked and latest version if fine
				$out['active'][$index] = $page;
			} else {
				// Marked but latest version if not fine
				$out['broken'][$index] = $page;
			}
		}

		// broken and proposed take preference over discouraged status
		foreach ( $out['active'] as $index => $page ) {
			$id = TranslatablePage::getMessageGroupIdFromTitle( $page['title'] );
			$group = MessageGroups::getGroup( $id );
			if ( MessageGroups::getPriority( $group ) === 'discouraged' ) {
				$out['discouraged'][$index] = $page;
				unset( $out['active'][$index] );
			}
		}

		return $out;
	}

	public function listPages() {
		$out = $this->getOutput();

		$res = $this->loadPagesFromDB();
		$allpages = $this->buildPageArray( $res );
		if ( !count( $allpages ) ) {
			$out->addWikiMsg( 'tpt-list-nopages' );

			return;
		}
		$types = $this->classifyPages( $allpages );

		$pages = $types['proposed'];
		if ( count( $pages ) ) {
			$out->wrapWikiMsg( '== $1 ==', 'tpt-new-pages-title' );
			$out->addWikiMsg( 'tpt-new-pages', count( $pages ) );
			$out->addHtml( '<ol>' );
			foreach ( $pages as $page ) {
				$link = Linker::link( $page['title'] );
				$acts = $this->actionLinks( $page, 'proposed' );
				$out->addHtml( "<li>$link $acts</li>" );
			}
			$out->addHtml( '</ol>' );
		}

		$pages = $types['active'];
		if ( count( $pages ) ) {
			$out->wrapWikiMsg( '== $1 ==', 'tpt-old-pages-title' );
			$out->addWikiMsg( 'tpt-old-pages', count( $pages ) );
			$out->addHtml( '<ol>' );
			foreach ( $pages as $page ) {
				$link = Linker::link( $page['title'] );
				if ( $page['tp:mark'] !== $page['tp:tag'] ) {
					$link = "<strong>$link</strong>";
				}

				$acts = $this->actionLinks( $page, 'active' );
				$out->addHtml( "<li>$link $acts</li>" );
			}
			$out->addHtml( '</ol>' );
		}

		$pages = $types['broken'];
		if ( count( $pages ) ) {
			$out->wrapWikiMsg( '== $1 ==', 'tpt-other-pages-title' );
			$out->addWikiMsg( 'tpt-other-pages', count( $pages ) );
			$out->addHtml( '<ol>' );
			foreach ( $pages as $page ) {
				$link = Linker::link( $page['title'] );
				$acts = $this->actionLinks( $page, 'broken' );
				$out->addHtml( "<li>$link $acts</li>" );
			}
			$out->addHtml( '</ol>' );
		}

		$pages = $types['discouraged'];
		if ( count( $pages ) ) {
			$out->wrapWikiMsg( '== $1 ==', 'tpt-discouraged-pages-title' );
			$out->addWikiMsg( 'tpt-discouraged-pages', count( $pages ) );
			$out->addHtml( '<ol>' );
			foreach ( $pages as $page ) {
				$link = Linker::link( $page['title'] );
				if ( $page['tp:mark'] !== $page['tp:tag'] ) {
					$link = "<strong>$link</strong>";
				}

				$acts = $this->actionLinks( $page, 'discouraged' );
				$out->addHtml( "<li>$link $acts</li>" );
			}
			$out->addHtml( '</ol>' );
		}
	}

	/**
	 * @param array $page
	 * @param string $type
	 * @return string
	 */
	protected function actionLinks( array $page, $type ) {
		$actions = array();
		/**
		 * @var Title $title
		 */
		$title = $page['title'];
		$user = $this->getUser();

		if ( $user->isAllowed( 'pagetranslation' ) ) {
			$token = $user->getEditToken();

			$pending = $type === 'active' && $page['latest'] !== $page['tp:mark'];
			if ( $type === 'proposed' || $pending ) {
				$actions[] = Linker::link(
					$this->getTitle(),
					$this->msg( 'tpt-rev-mark' )->escaped(),
					array( 'title' => $this->msg( 'tpt-rev-mark-tooltip' )->text() ),
					array(
						'do' => 'mark',
						'target' => $title->getPrefixedText(),
						'revision' => $title->getLatestRevId(),
						'token' => $token,
					)
				);
			} elseif ( $type === 'broken' ) {
				$actions[] = Linker::link(
					$this->getTitle(),
					$this->msg( 'tpt-rev-unmark' )->escaped(),
					array( 'title' => $this->msg( 'tpt-rev-unmark-tooltip' )->text() ),
					array(
						'do' => 'unmark',
						'target' => $title->getPrefixedText(),
						'revision' => -1,
						'token' => $token,
					)
				);
			}

			if ( $type === 'active' ) {
				$actions[] = Linker::link(
					$this->getTitle(),
					$this->msg( 'tpt-rev-discourage' )->escaped(),
					array( 'title' => $this->msg( 'tpt-rev-discourage-tooltip' )->text() ),
					array(
						'do' => 'discourage',
						'target' => $title->getPrefixedText(),
						'revision' => -1,
						'token' => $token,
					)
				);
			} elseif ( $type === 'discouraged' ) {
				$actions[] = Linker::link(
					$this->getTitle(),
					$this->msg( 'tpt-rev-encourage' )->escaped(),
					array( 'title' => $this->msg( 'tpt-rev-encourage-tooltip' )->text() ),
					array(
						'do' => 'encourage',
						'target' => $title->getPrefixedText(),
						'revision' => -1,
						'token' => $token,
					)
				);
			}
		}

		if ( !count( $actions ) ) {
			return '';
		}

		$flattened = $this->getLanguage()->semicolonList( $actions );

		return Html::rawElement(
			'span',
			array( 'class' => 'mw-tpt-actions' ),
			$this->msg( 'parentheses' )->rawParams( $flattened )->escaped()
		);
	}

	/**
	 * @param TranslatablePage $page
	 * @param bool $error
	 * @return array
	 */
	public function checkInput( TranslatablePage $page, &$error = false ) {
		$usedNames = array();
		$highest = intval( TranslateMetadata::get( $page->getMessageGroupId(), 'maxid' ) );
		$parse = $page->getParse();
		$sections = $parse->getSectionsForSave( $highest );

		foreach ( $sections as $s ) {
			// We need to do checks for both new and existing sections.
			// Someone might have tampered with the page source adding
			// duplicate or invalid markers.
			if ( isset( $usedNames[$s->id] ) ) {
				$this->getOutput()->addWikiMsg( 'tpt-duplicate', $s->id );
				$error = true;
			}
			$usedNames[$s->id] = true;
			$s->name = $s->id;
		}

		return $sections;
	}

	/**
	 * Displays the sections and changes for the user to review
	 * @param TranslatablePage $page
	 * @param array $sections
	 */
	public function showPage( TranslatablePage $page, array $sections ) {
		global $wgContLang;

		$out = $this->getOutput();

		$out->setSubtitle( Linker::link( $page->getTitle() ) );
		$out->addModules( 'ext.translate.special.pagetranslation' );

		$out->addWikiMsg( 'tpt-showpage-intro' );

		$formParams = array(
			'method' => 'post',
			'action' => $this->getTitle()->getFullURL(),
			'class' => 'mw-tpt-sp-markform',
		);

		$out->addHTML(
			Xml::openElement( 'form', $formParams ) .
				Html::hidden( 'title', $this->getTitle()->getPrefixedText() ) .
				Html::hidden( 'revision', $page->getRevision() ) .
				Html::hidden( 'target', $page->getTitle()->getPrefixedtext() ) .
				Html::hidden( 'token', $this->getUser()->getEditToken() )
		);

		$out->wrapWikiMsg( '==$1==', 'tpt-sections-oldnew' );

		$diffOld = $this->msg( 'tpt-diff-old' )->escaped();
		$diffNew = $this->msg( 'tpt-diff-new' )->escaped();

		/**
		 * @var TPSection $s
		 */
		foreach ( $sections as $s ) {
			if ( $s->type === 'new' ) {
				$name = $this->msg( 'tpt-section-new', $s->name )->escaped();
			} else {
				$name = $this->msg( 'tpt-section', $s->name )->escaped();
			}

			if ( $s->type === 'changed' ) {
				$diff = new DifferenceEngine;
				if ( method_exists( 'DifferenceEngine', 'setTextLanguage' ) ) {
					$diff->setTextLanguage( $wgContLang );
				}
				$diff->setReducedLineNumbers();
				$diff->setText( $s->getOldText(), $s->getText() );
				$text = $diff->getDiff( $diffOld, $diffNew );
				$diffOld = $diffNew = null;
				$diff->showDiffStyle();

				$id = "tpt-sect-{$s->id}-action-nofuzzy";
				$checkLabel = Xml::checkLabel(
					$this->msg( 'tpt-action-nofuzzy' )->text(),
					$id,
					$id,
					false
				);
				$text = $checkLabel . $text;
			} else {
				$text = TranslateUtils::convertWhiteSpaceToHTML( $s->getText() );
			}

			# For changed text, the language is set by $diff->setTextLanguage()
			$lang = $s->type === 'changed' ? null : $wgContLang;
			$out->addHTML( MessageWebImporter::makeSectionElement(
				$name,
				$s->type,
				$text, $lang
			) );
		}

		$deletedSections = $page->getParse()->getDeletedSections();
		if ( count( $deletedSections ) ) {
			$out->wrapWikiMsg( '==$1==', 'tpt-sections-deleted' );

			/**
			 * @var TPSection $s
			 */
			foreach ( $deletedSections as $s ) {
				$name = $this->msg( 'tpt-section-deleted', $s->id )->escaped();
				$text = TranslateUtils::convertWhiteSpaceToHTML( $s->getText() );
				$out->addHTML( MessageWebImporter::makeSectionElement(
					$name,
					$s->type,
					$text,
					$wgContLang
				) );
			}
		}

		// Display template changes if applicable
		if ( $page->getMarkedTag() !== false ) {
			$newTemplate = $page->getParse()->getTemplatePretty();
			$oldPage = TranslatablePage::newFromRevision(
				$page->getTitle(),
				$page->getMarkedTag()
			);
			$oldTemplate = $oldPage->getParse()->getTemplatePretty();

			if ( $oldTemplate !== $newTemplate ) {
				$out->wrapWikiMsg( '==$1==', 'tpt-sections-template' );

				$diff = new DifferenceEngine;
				if ( method_exists( 'DifferenceEngine', 'setTextLanguage' ) ) {
					$diff->setTextLanguage( $wgContLang );
				}
				$diff->setText( $oldTemplate, $newTemplate );
				$text = $diff->getDiff(
					$this->msg( 'tpt-diff-old' )->escaped(),
					$this->msg( 'tpt-diff-new' )->escaped()
				);
				$diff->showDiffStyle();
				$diff->setReducedLineNumbers();

				$contentParams = array( 'class' => 'mw-tpt-sp-content' );
				$out->addHTML( Xml::tags( 'div', $contentParams, $text ) );
			}
		}

		$this->priorityLanguagesForm( $page );

		$out->addHTML(
			Xml::submitButton( $this->msg( 'tpt-submit' )->text() ) .
				Xml::closeElement( 'form' )
		);
	}

	/**
	 * @param TranslatablePage $page
	 */
	protected function priorityLanguagesForm( TranslatablePage $page ) {
		global $wgContLang;

		$groupId = $page->getMessageGroupId();
		$this->getOutput()->wrapWikiMsg( '==$1==', 'tpt-sections-prioritylangs' );

		$langSelector = Xml::languageSelector(
			$wgContLang->getCode(),
			false,
			$this->getLanguage()->getCode()
		);

		$hLangs = Xml::inputLabelSep(
			$this->msg( 'tpt-select-prioritylangs' )->text(),
			'prioritylangs', // name
			'tpt-prioritylangs', // id
			50,
			TranslateMetadata::get( $groupId, 'prioritylangs' )
		);

		$hForce = Xml::checkLabel(
			$this->msg( 'tpt-select-prioritylangs-force' )->text(),
			'forcelimit', // name
			'tpt-priority-forcelimit', // id
			TranslateMetadata::get( $groupId, 'priorityforce' ) === 'on'
		);

		$hReason = Xml::inputLabelSep(
			$this->msg( 'tpt-select-prioritylangs-reason' )->text(),
			'priorityreason', // name
			'tpt-priority-reason', // id
			50, // size
			TranslateMetadata::get( $groupId, 'priorityreason' )
		);

		$this->getOutput()->addHTML(
			"<table>" .
				"<tr>" .
				"<td class='mw-label'>$hLangs[0]</td>" .
				"<td class='mw-input'>$hLangs[1]$langSelector[1]</td>" .
				"</tr>" .
				"<tr><td></td><td class='mw-inout'>$hForce</td></tr>" .
				"<tr>" .
				"<td class='mw-label'>$hReason[0]</td>" .
				"<td class='mw-input'>$hReason[1]</td>" .
				"</tr>" .
				"</table>"
		);
	}

	/**
	 * This function does the heavy duty of marking a page.
	 * - Updates the source page with section markers.
	 * - Updates translate_sections table
	 * - Updates revtags table
	 * - Setups renderjobs to update the translation pages
	 * - Invalidates caches
	 * @param TranslatablePage $page
	 * @param array $sections
	 * @return array|bool
	 */
	public function markForTranslation( TranslatablePage $page, array $sections ) {
		// Add the section markers to the source page
		$wikiPage = WikiPage::factory( $page->getTitle() );
		$status = $wikiPage->doEdit(
			$page->getParse()->getSourcePageText(), // Content
			$this->msg( 'tpt-mark-summary' )->inContentLanguage()->text(), // Summary
			EDIT_FORCE_BOT | EDIT_UPDATE // Flags
		);

		if ( !$status->isOK() ) {
			return array( 'tpt-edit-failed', $status->getWikiText() );
		}

		$newrevision = $status->value['revision'];

		// In theory it is either null or Revision object,
		// never revision object with null id, but who knows
		if ( $newrevision instanceof Revision ) {
			$newrevision = $newrevision->getId();
		}

		if ( $newrevision === null ) {
			// Probably a no-change edit, so no new revision was assigned.
			// Get the latest revision manually
			$newrevision = $page->getTitle()->getLatestRevId();
		}

		$inserts = array();
		$changed = array();
		$maxid = intval( TranslateMetadata::get( $page->getMessageGroupId(), 'maxid' ) );

		$pageId = $page->getTitle()->getArticleID();
		/**
		 * @var TPSection $s
		 */
		foreach ( array_values( $sections ) as $index => $s ) {
			$maxid = max( $maxid, intval( $s->name ) );
			$changed[] = $s->name;

			if ( $this->getRequest()->getCheck( "tpt-sect-{$s->id}-action-nofuzzy" ) ) {
				// This will be checked by getTranslationUnitJobs
				$s->type = 'old';
			}

			$inserts[] = array(
				'trs_page' => $pageId,
				'trs_key' => $s->name,
				'trs_text' => $s->getText(),
				'trs_order' => $index
			);
		}

		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete(
			'translate_sections',
			array( 'trs_page' => $page->getTitle()->getArticleID() ),
			__METHOD__
		);
		$dbw->insert( 'translate_sections', $inserts, __METHOD__ );
		TranslateMetadata::set( $page->getMessageGroupId(), 'maxid', $maxid );

		$page->addMarkedTag( $newrevision );
		MessageGroups::clearCache();

		$jobs = self::getRenderJobs( $page );
		Job::batchInsert( $jobs );

		$jobs = self::getTranslationUnitJobs( $page, $sections );
		Job::batchInsert( $jobs );

		// Logging
		$this->handlePriorityLanguages( $this->getRequest(), $page, $this->getUser() );

		$entry = new ManualLogEntry( 'pagetranslation', 'mark' );
		$entry->setPerformer( $this->getUser() );
		$entry->setTarget( $page->getTitle() );
		$entry->setParameters( array(
			'revision' => $newrevision,
			'changed' => count( $changed ),
		) );
		$logid = $entry->insert();
		$entry->publish( $logid );

		// Clear more caches
		$page->getTitle()->invalidateCache();
		MessageIndexRebuildJob::newJob()->run();

		return false;
	}

	/**
	 * @param WebRequest $request
	 * @param TranslatablePage $page
	 * @param User $user
	 */
	protected function handlePriorityLanguages( WebRequest $request, TranslatablePage $page,
		User $user
	) {
		// new priority languages
		$npLangs = rtrim( trim( $request->getVal( 'prioritylangs' ) ), ',' );
		$npForce = $request->getCheck( 'forcelimit' ) ? 'on' : 'off';
		$npReason = trim( $request->getText( 'priorityreason' ) );

		// Normalize
		$npLangs = array_map( 'trim', explode( ',', $npLangs ) );
		$npLangs = array_unique( $npLangs );
		// Remove invalid language codes.
		$languages = Language::getLanguageNames();
		foreach ( $npLangs as $index => $language ) {
			if ( !array_key_exists( $language, $languages ) ) {
				unset( $npLangs[$index] );
			}
		}
		$npLangs = implode( ',', $npLangs );
		if ( $npLangs === '' ) {
			$npLangs = false;
			$npForce = false;
			$npReason = false;
		}

		$groupId = $page->getMessageGroupId();
		// old priority languages
		$opLangs = TranslateMetadata::get( $groupId, 'prioritylangs' );
		$opForce = TranslateMetadata::get( $groupId, 'priorityforce' );
		$opReason = TranslateMetadata::get( $groupId, 'priorityreason' );

		TranslateMetadata::set( $groupId, 'prioritylangs', $npLangs );
		TranslateMetadata::set( $groupId, 'priorityforce', $npForce );
		TranslateMetadata::set( $groupId, 'priorityreason', $npReason );

		if ( $opLangs !== $npLangs || $opForce !== $npForce || $opReason !== $npReason ) {
			$params = array(
				'languages' => $npLangs,
				'force' => $npForce,
				'reason' => $npReason,
			);

			$entry = new ManualLogEntry( 'pagetranslation', 'prioritylanguages' );
			$entry->setPerformer( $this->getUser() );
			$entry->setTarget( $page->getTitle() );
			$entry->setParameters( $params );
			$entry->setComment( $npReason );
			$logid = $entry->insert();
			$entry->publish( $logid );
		}
	}

	/**
	 * Creates jobs needed to create or update all translation pages.
	 * @param TranslatablePage $page
	 * @return Job[]
	 * @since 2013-01-28
	 */
	public static function getRenderJobs( TranslatablePage $page ) {
		$jobs = array();

		$titles = $page->getTranslationPages();
		foreach ( $titles as $t ) {
			$jobs[] = TranslateRenderJob::newJob( $t );
		}

		return $jobs;
	}

	/**
	 * Creates jobs needed to create or update all translation page definitions.
	 * @param TranslatablePage $page
	 * @param array $sections
	 * @return Job[]
	 * @since 2013-01-28
	 */
	public static function getTranslationUnitJobs( TranslatablePage $page, array $sections ) {
		$jobs = array();

		$code = $page->getSourceLanguageCode();
		$prefix = $page->getTitle()->getPrefixedText();

		foreach ( $sections as $s ) {
			$unit = $s->name;
			$title = Title::makeTitle( NS_TRANSLATIONS, "$prefix/$unit/$code" );

			$fuzzy = $s->type === 'changed';
			$jobs[] = MessageUpdateJob::newJob( $title, $s->text, $fuzzy );
		}

		return $jobs;
	}
}
