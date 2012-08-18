<?php
/**
 * Contains logic for special page Special:ImportTranslations.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2012 Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
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

		TranslateUtils::addSpecialHelpLink( $out, 'Help:Extension:Translate/Page_translation_example' );

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
			self::superDebug( __METHOD__, "token failure", $user );
			throw new PermissionsError( 'pagetranslation' );
		}

		// We are processing some specific page
		if ( !$title->exists() ) {
			$out->addWikiMsg( 'tpt-nosuchpage', $title->getPrefixedText() );
			return;
		}

		if ( $action === 'discourage' || $action === 'encourage' ) {
			$id = TranslatablePage::getMessageGroupIdFromTitle( $title );
			$dbw = wfGetDB( DB_MASTER );
			$table = 'translate_groupreviews';
			$row = array(
				'tgr_group' => $id,
				'tgr_lang' => '*priority',
				'tgr_state' => 'discouraged',
			);

			$logger = new LogPage( 'pagetranslation' );
			$params = array( 'user' => $user->getName() );

			$priority = MessageGroups::getPriority( $id );

			// encouraged is default priority (''). Only do this if the priority is discouraged.
			if ( $action === 'encourage' && $priority === 'discouraged' ) {
				$dbw->delete( $table, $row, __METHOD__ );
				$logger->addEntry( 'encourage', $title, null, array( serialize( $params ) ), $this->getUser() );
			} else {
				$index = array( 'tgr_group', 'tgr_lang' );
				$dbw->replace( $table, array( $index ), $row, __METHOD__ );

				// Prevent duplicate log entries.
				if( $priority !== 'discouraged' ) {
					$logger->addEntry( 'discourage', $title, null, array( serialize( $params ) ), $this->getUser() );
				}
			}

			$this->listPages();

			$group = MessageGroups::getGroup( $id );
			$parents = MessageGroups::getParentGroups( $group );
			MessageGroupStats::clearGroup( $parents );

			return;
		}

		if ( $action === 'unmark' ) {
			$page = TranslatablePage::newFromTitle( $title );
			$page->removeTags();
			$page->getTitle()->invalidateCache();

			$logger = new LogPage( 'pagetranslation' );
			$params = array( 'user' => $user->getName() );
			$logger->addEntry( 'unmark', $page->getTitle(), null, array( serialize( $params ) ), $this->getUser() );
			$out->addWikiMsg( 'tpt-unmarked', $title->getPrefixedText() );
			self::superDebug( __METHOD__, "unmarked page", $user, $title );

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
			self::superDebug( __METHOD__, "revision mismatch while marking", $user, $title, $revision, intval( $title->getLatestRevID() ) );
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

		self::superDebug( __METHOD__, "marking page", $user, $title, $revision );
		$this->showPage( $page, $sections );
	}

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

	protected function buildPageArray( /*db result*/ $res ) {
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
	 * @param $page array
	 * @param $type string
	 * @return string
	 */
	protected function actionLinks( array $page, $type ) {
		$actions = array();
		$title = $page['title'];
		$user = $this->getUser();

		if ( $user->isAllowed( 'pagetranslation' ) ) {
			$token = $user->editToken();

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

	public function checkInput( TranslatablePage $page, &$error = false ) {
		$usedNames = array();

		$highest = intval( TranslateMetadata::get( $page->getMessageGroupId(), 'maxid' ) );

		$parse = $page->getParse();
		$sections = $parse->getSectionsForSave( $highest );
		foreach ( $sections as $s ) {
			// We want to preserve $id, because it is the only thing we can use
			// to link the new names to current sections. Name will become
			// the new id only after it is saved into db and the page.
			// Do not allow changing names for old sections
			if ( $s->type === 'new' ) {
				$name = $this->getRequest()->getText( 'tpt-sect-' . $s->id, $s->id );
			} else {
				$name = $s->id;
			}

			// We need to do checks for both new and existing sections.
			// Someone might have tampered with the page source adding
			// duplicate or invalid markers.
			if ( isset( $usedNames[$name] ) ) {
				$this->getOutput()->addWikiMsg( 'tpt-duplicate', $name );
				$error = true;
			}
			$usedNames[$name] = true;

			// Make sure valid title can be constructed
			$sectionTitle = Title::makeTitleSafe(
				NS_TRANSLATIONS,
				$page->getTitle()->getPrefixedText() . '/' . $name . '/foo'
			);

			if ( trim( $name ) === '' || !$sectionTitle ) {
				$this->getOutput()->addWikiMsg( 'tpt-badsect', $name, $s->id );
				$error = true;
			} else {
				// Update the name
				$s->name = $name;
			}
		}

		return $sections;
	}

	/**
	 * Displays the sections and changes for the user to review
	 * @param $page TranslatablePage
	 * @param $sections array
	 */
	public function showPage( TranslatablePage $page, Array $sections ) {
		global $wgContLang;

		$out = $this->getOutput();

		$out->setSubtitle( Linker::link( $page->getTitle() ) );
		$out->addModules( 'ext.translate.special.pagetranslation' );

		$out->addWikiMsg( 'tpt-showpage-intro' );

		$formParams = array(
			'method' => 'post',
			'action' => $this->getTitle()->getFullURL(),
			'class'  => 'mw-tpt-sp-markform',
		);

		$out->addHTML(
			Xml::openElement( 'form', $formParams ) .
			Html::hidden( 'title', $this->getTitle()->getPrefixedText() ) .
			Html::hidden( 'revision', $page->getRevision() ) .
			Html::hidden( 'target', $page->getTitle()->getPrefixedtext() ) .
			Html::hidden( 'token', $this->getUser()->editToken() )
		);

		$out->wrapWikiMsg( '==$1==', 'tpt-sections-oldnew' );

		$diffOld = $this->msg( 'tpt-diff-old' )->escaped();
		$diffNew = $this->msg( 'tpt-diff-new' )->escaped();

		foreach ( $sections as $s ) {
			if ( $s->type === 'new' ) {
				$input = Xml::input( 'tpt-sect-' . $s->id, 15, $s->name );
				$name = $this->msg( 'tpt-section-new' )->rawParams( $input )->escaped();
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
			$out->addHTML( MessageWebImporter::makeSectionElement( $name, $s->type, $text, $lang ) );
		}

		$deletedSections = $page->getParse()->getDeletedSections();
		if ( count( $deletedSections ) ) {
			$out->wrapWikiMsg( '==$1==', 'tpt-sections-deleted' );

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
			$oldPage = TranslatablePage::newFromRevision( $page->getTitle(), $page->getMarkedTag() );
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
			"<tr><td class='mw-label'>$hLangs[0]</td><td class='mw-input'>$hLangs[1]$langSelector[1]</td></tr>" .
			"<tr><td></td><td class='mw-inout'>$hForce</td></tr>" .
			"<tr><td class='mw-label'>$hReason[0]</td><td class='mw-input'>$hReason[1]</td></tr>" .
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
	 * @param $page TranslatablePage
	 * @param $sections array
	 * @return array|bool
	 */
	public function markForTranslation( TranslatablePage $page, Array $sections ) {
		// Add the section markers to the source page
		$article = new Article( $page->getTitle(), 0 );
		$status = $article->doEdit(
			$page->getParse()->getSourcePageText(), // Content
			$this->msg( 'tpt-mark-summary' )->inContentLanguage()->text(),  // Summary
			EDIT_FORCE_BOT | EDIT_UPDATE,           // Flags
			$page->getRevision()                    // Based-on revision
		);

		if ( !$status->isOK() ) {
			self::superDebug( __METHOD__, 'edit-fail', $this->getUser(), $page->getTitle(), $status );
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

		self::superDebug( __METHOD__, 'latestrev', $page->getTitle(), $newrevision );

		$inserts = array();
		$changed = array();
		$maxid = intval( TranslateMetadata::get( $page->getMessageGroupId(), 'maxid' ) );

		$pageId = $page->getTitle()->getArticleID();
		foreach ( array_values( $sections ) as $index => $s ) {
			$maxid = max( $maxid, intval( $s->name ) );

			if ( $s->type === 'changed' ) {
				// Allow silent changes to avoid fuzzying unnecessary.
				if ( !$this->getRequest()->getCheck( "tpt-sect-{$s->id}-action-nofuzzy" ) ) {
					$changed[] = $s->name;
				}
			}

			$inserts[] = array(
				'trs_page' => $pageId,
				'trs_key' => $s->name,
				'trs_text' => $s->getText(),
				'trs_order' => $index
			);
		}

		// Don't add stuff if no changes, use the plain null instead for prettiness
		if ( !count( $changed ) ) {
			$changed = null;
		}

		$dbw = wfGetDB( DB_MASTER );
		if ( !$dbw->fieldExists( 'translate_sections', 'trs_order', __METHOD__ ) ) {
			error_log( 'Field trs_order does not exist. Please run update.php.' );
			foreach ( array_keys( $inserts ) as $index ) {
				unset( $inserts[$index]['trs_order'] );
			}
		}
		$dbw->delete(
			'translate_sections',
			array( 'trs_page' => $page->getTitle()->getArticleID() ),
			__METHOD__
		);
		$dbw->insert( 'translate_sections', $inserts, __METHOD__ );
		TranslateMetadata::set( $page->getMessageGroupId(), 'maxid', $maxid );

		/* Stores the names of changed sections in the database.
		 * Used for calculating completion percentages for outdated messages */
		$page->addMarkedTag( $newrevision, $changed );
		$this->addFuzzyTags( $page, $changed );

		$logger = new LogPage( 'pagetranslation' );
		$params = array(
			'user' => $this->getUser()->getName(),
			'revision' => $newrevision,
			'changed' => count( $changed ),
		);
		$logger->addEntry( 'mark', $page->getTitle(), null, array( serialize( $params ) ), $this->getUser() );

		$this->handlePriorityLanguages( $this->getRequest(), $page, $this->getUser() );

		// @todo FIXME: When translatable page X is tagged for translation and when X has <languages/>,
		//              then there is no language bar on X after tagging and a job queue run. The below
		//              tried to resolve that, but didn't. No correct solution known at the moment.
		//              Manual fix is "action=purge" on X.
		// This updates the language bars properly as well as the translatable page.
		//$page->getTranslationPercentages( true );
		//WikiPage::factory( $page->getTitle() )->doPurge();

		$page->getTitle()->invalidateCache();
		$this->setupRenderJobs( $page );

		// Re-generate caches
		MessageGroups::clearCache();
		MessageIndexRebuildJob::newJob()->insert();
		return false;
	}

	protected function handlePriorityLanguages( WebRequest $request, TranslatablePage $page, User $user ) {
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
			if( !array_key_exists( $language, $languages ) ) {
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
				'user' => $user->getName(),
				'languages' => $npLangs,
				'force' => $npForce,
				'reason' => $npReason,
			);
			$logger = new LogPage( 'pagetranslation' );
			$logger->addEntry(
				'prioritylanguages',
				$page->getTitle(),
				null,
				array( serialize( $params ) ),
				$this->getUser()
			);
		}
	}

	/**
	 * @param $page Article
	 * @param $changed
	 */
	public function addFuzzyTags( $page, $changed ) {
		if ( !count( $changed ) ) {
			self::superDebug( __METHOD__, 'nochanged', $page->getTitle() );
			return;
		}

		$titles = array();
		$prefix = $page->getTitle()->getPrefixedText();
		$db = wfGetDB( DB_MASTER );

		foreach ( $changed as $c ) {
			$title = Title::makeTitleSafe( NS_TRANSLATIONS, "$prefix/$c" );
			if ( $title ) {
				$titles[] = 'page_title ' . $db->buildLike( $title->getDBkey() . '/', $db->anyString() );
			}
		}

		$titleCond = $db->makeList( $titles, LIST_OR );

		$fields = array( 'page_id', 'page_latest' );
		$conds = array( 'page_namespace' => NS_TRANSLATIONS, $titleCond );
		$res = $db->select( 'page', $fields, $conds, __METHOD__ );

		$inserts = array();

		// @todo Filter out qqq so it is not marked as fuzzy.
		foreach ( $res as $r ) {
			$inserts[] = array(
				'rt_page' => $r->page_id,
				'rt_type' => RevTag::getType( 'fuzzy' ),
				'rt_revision' => $r->page_latest,
			);
		}

		if ( count( $inserts ) ) {
			self::superDebug( __METHOD__, 'inserts', $inserts );
			$db->replace( 'revtag', array( 'rt_type_page_revision' ), $inserts, __METHOD__ );
		}
	}

	public function setupRenderJobs( TranslatablePage $page ) {
		$titles = $page->getTranslationPages();
		$this->addInitialRenderJob( $page, $titles );
		$jobs = array();

		foreach ( $titles as $t ) {
			self::superDebug( __METHOD__, 'renderjob', $t );
			$jobs[] = RenderJob::newJob( $t );
		}

		if ( count( $jobs ) < 10 ) {
			self::superDebug( __METHOD__, 'renderjob-immediate' );
			foreach ( $jobs as $j ) {
				$j->run();
			}
		} else {
			// Use the job queue
			self::superDebug( __METHOD__, 'renderjob-delayed' );
			Job::batchInsert( $jobs );
		}
	}

	/**
	 * If this page is marked for the first time, /en may not yet exists.
	 * If this is the case, add a RenderJob for it, but don't execute it
	 * immediately, since the message group doesn't exist during this request.
	 * @param $page Article
	 * @param $titles array
	 */
	protected function addInitialRenderJob( $page, $titles ) {
		global $wgContLang;

		$en = Title::newFromText( $page->getTitle()->getPrefixedText() . '/' . $wgContLang->getCode() );
		$hasen = false;

		foreach ( $titles as $t ) {
			if ( $t->equals( $en ) ) {
				$hasen = true;
				break;
			}
		}

		if ( !$hasen ) {
			RenderJob::newJob( $en )->insert();
		}
	}

	/**
	 * Enhanced version of wfDebug that allows more detailed debugging.
	 * You can pass anything as varags and it will be serialized. Article
	 * and User objects have special handling to only output name and id.
	 * @param $method \string Calling method.
	 * @param $msg \string Debug message.
	 * @todo Move to better place.
	 */
	public static function superDebug( $method, $msg /* varags */ ) {
		$args = func_get_args();
		$args = array_slice( $args, 2 );
		foreach ( $args as &$arg ) {
			if ( $arg instanceof User ) {
				$arg = array( 'user' => $arg->getName(), 'id' => $arg->getId() );
			} elseif ( $arg instanceof Title ) {
				$arg = array( 'title' => $arg->getPrefixedText(), 'aid' => $arg->getArticleID() );
			}
			$arg = serialize( $arg );
		}

		wfDebugLog( 'pagetranslation', "$method: $msg [" . implode( " ", $args ) . "]\n" );
	}
}
