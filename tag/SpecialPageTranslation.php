<?php
/**
 * A special page for marking revisions of pages for translation.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2009 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
class SpecialPageTranslation extends SpecialPage {
	function __construct() {
		SpecialPage::SpecialPage( 'PageTranslation' );
	}

	/**
	 * Access point for this special page.
	 * GLOBALS: $wgHooks, $wgOut.
	 */
	public function execute( $parameters ) {
		wfLoadExtensionMessages( 'PageTranslation' );
		$this->setHeaders();

		global $wgRequest, $wgOut, $wgUser;
		$this->user = $wgUser;

		$target = $wgRequest->getText( 'target', $parameters );
		$revision = $wgRequest->getText( 'revision', 0 );
		
		// No specific page or invalid input
		$title = Title::newFromText( $target );
		if ( !$title ) {
			if ( $target !== '' ) {
				$wgOut->addWikiMsg( 'tpt-badtitle' );
			} else {
				$this->listPages();
			}
			return;
		}

		// Check permissions
		if ( !$this->user->isAllowed( 'pagetranslation' ) ) {
			$wgOut->permissionRequired( 'pagetranslation' );
			return;
		}

		// We are processing some specific page
		if ( $revision === '0' ) {
			$revision = $title->getLatestRevID();
		} elseif ( $revision !== $title->getLatestRevID() ) {
			$wgOut->addWikiMsg( 'tpt-oldrevision', $title->getPrefixedText(), $revision );
			return;
		}

		$page = TranslatablePage::newFromRevision( $title, $revision );

		if ( !$page instanceof TranslatablePage ) {
			$wgOut->addWikiMsg( 'tpt-notsuitable', $title->getPrefixedText(), $revision );
			$this->listPages();
			return;
		}

		$lastrev = $page->getMarkedTag();
		if ( $lastrev !== false && $lastrev === $revision ) {
			$wgOut->addWikiMsg( 'tpt-already-marked' );
			$this->listPages();
			return;
		}

		// This will modify the sections to include name property
		$error = false;
		$sections = $this->checkInput( $page, &$error );
		// Non-fatal error which prevents saving
		if ( $error === false && $wgRequest->wasPosted() ) {
			$err = $this->markForTranslation( $page, $sections );
			if ( $err ) {
				call_user_func_array( array($wgOut, 'addWikiMsg' ), $err );
			} else {
				$this->showSuccess( $page );
				$this->listPages();
			}
			return;
		}
		$this->showPage( $page, $sections );
	}

	public function showSuccess( TranslatablePage $page ) {
		global $wgOut, $wgLang;

		$titleText = $page->getTitle()->getPrefixedText();
		$num = $wgLang->formatNum( $page->getParse()->countSections() );
		$link = SpecialPage::getTitleFor( 'Translate' )->getFullUrl(
			array( 'group' => 'page|' . $page->getTitle()->getPrefixedText() ) );
		$wgOut->addWikiMsg( 'tpt-saveok', $titleText, $num, $link );
	}

	public function loadPagesFromDB() {
		$dbr = wfGetDB( DB_SLAVE );
		$tables = array( 'page', 'revtag_type', 'revtag' );
		$vars = array( 'page_id', 'page_title', 'page_namespace', 'page_latest', 'rt_revision', 'rtt_name' );
		$conds = array(
			'page_id=rt_page',
			'rt_type=rtt_id',
			'rtt_name' => array( 'tp:mark', 'tp:tag' ),
		);
		$res = $dbr->select( $tables, $vars, $conds, __METHOD__ );
		return $res;
	}

	public function listPages() {
		global $wgOut;

		$res = $this->loadPagesFromDB();
		if ( !$res->numRows() ) {
			$wgOut->addWikiMsg( 'tpt-list-nopages' );
			return;
		}

		$old = $new = array();
		foreach ( $res as $r ) {
			if ( $r->rtt_name === 'tp:mark' ) {
				$old[$r->page_id] = array(
					$r->rt_revision,
					Title::newFromRow( $r )
				);
			} elseif ( $r->rtt_name === 'tp:tag' ) {
				$new[$r->page_id] = array(
					$r->rt_revision,
					Title::newFromRow( $r )
				);
			}
		}

		// Pages may have both tags, ignore the transtag if both
		foreach( array_keys($old) as $k ) unset($new[$k]);

		if ( count($old) ) {
			$wgOut->addWikiMsg( 'tpt-old-pages' );
			$wgOut->addHTML( '<ol>' );
			foreach ( $old as $o ) {
				list( $rev, $title ) = $o;
				$link = $this->user->getSkin()->link( $title );
				$acts = $this->actionLinks( $title, $rev, 'old' );
				$wgOut->addHTML( "<li>$link ($acts) </li>" );
			}
			$wgOut->addHTML( '</ol>' );
		}

		if ( count($new) ) {
			$wgOut->addWikiMsg( 'tpt-new-pages' );
			$wgOut->addHTML( '<ol>' );
			foreach ( $new as $n ) {
				list( $rev, $title ) = $n;
				$link = $this->user->getSkin()->link( $title );
				$acts = $this->actionLinks( $title, $rev, 'new' );
				$wgOut->addHTML( "<li>$link ($acts) </li>" );
			}
			$wgOut->addHTML( '</ol>' );
		}

	}

	protected function actionLinks( $title, $rev, $old = 'old' ) {
		$actions = array();
		$latest = $title->getLatestRevId();

		if ( $latest !== $rev ) {
			$text = wfMsg( 'tpt-rev-old', $rev );
			$actions[] = $this->user->getSkin()->link(
				$title,
				htmlspecialchars( $text ),
				array(),
				array( 'oldid' => $rev, 'diff' => $title->getLatestRevId() )
			);
		} else {
			$actions[] = wfMsgHtml( 'tpt-rev-latest' );
		}

		if ( $this->user->isAllowed( 'pagetranslation') &&
			 (($old === 'new' && $latest === $rev) ||
		     ($old === 'old' && $latest !== $rev)) ) {
			$actions[] = $this->user->getSkin()->link(
				$this->getTitle(),
				wfMsgHtml( 'tpt-rev-mark-new' ),
				array(),
				array( 
					'target' => $title->getPrefixedText(),
					'revision' => $title->getLatestRevId()
				)
			);
		}

		if ( $old === 'old' && $this->user->isAllowed( 'translate' ) ) {
			$actions[] = $this->user->getSkin()->link(
				SpecialPage::getTitleFor( 'Translate' ),
				wfMsgHtml( 'tpt-translate-this' ),
				array(),
				array( 'group' => 'page|' . $title->getPrefixedText() )
			);
		}

		global $wgLang;
		return $wgLang->semicolonList( $actions );
	}

	public function checkInput( TranslatablePage $page, &$error = false ) {
		global $wgOut, $wgRequest;

		$parse = $page->getParse();
		$sections = $parse->getSectionsForSave();
		foreach ( $sections as $s ) {
			// We want to preserve $id, because it is the only thing we can use
			// to link the new names to current sections. Name will become
			// the new id only after it is saved into db and the page.
			// Do not allow changing names for old sections
			$s->name = $s->id;
			if ( $s->type !== 'new' ) continue;

			$name = $wgRequest->getText( 'tpt-sect-' . $s->id, $s->id );

			$sectionTitle = Title::makeTitleSafe(
				NS_TRANSLATIONS,
				$page->getTitle()->getPrefixedText() . '/' . $name . '/qqq'
			);
			if ( trim($name) === '' || !$sectionTitle ) {
				$wgOut->addWikiMsg( 'tpt-badsect', $name, $s->id );
				$error = true;
			} else {
				// Update the name
				$s->name = $name;
			}
		}

		return $sections;
	}

	public function showPage( TranslatablePage $page, $sections ) {
		global $wgOut, $wgScript;

	
		$wgOut->addWikiMsg( 'tpt-showpage-intro' );

		$wgOut->addHTML(
			Xml::openElement( 'form', array( 'method' => 'post', 'action' => $this->getTitle()->getFullURL() ) ) .
			Xml::hidden( 'title', $this->getTitle()->getPrefixedText() ) .
			Xml::hidden( 'revision', $page->getRevision() ) .
			Xml::hidden( 'target', $page->getTitle()->getPrefixedtext() )
		);

		foreach ( $sections as $s ) {
			if ( $s->type === 'new' ) {
				$name = wfMsgHtml('tpt-section-new') . ' ' . Xml::input( 'tpt-sect-' . $s->id, 10, $s->name );
			} else {
				$name = wfMsgHtml('tpt-section') . ' ' . htmlspecialchars( $s->name );
			}

			if ( $s->type === 'changed' ) {
				$diff = new DifferenceEngine;
				$diff->setText( $s->getOldText(), $s->getText() );
				$text = $diff->getDiff( wfMsgHtml('tpt-diff-old'), wfMsgHtml('tpt-diff-new') );
				$diff->showDiffStyle();
			} else {
				$text = TranslateUtils::convertWhiteSpaceToHTML( $s->getText() );
			}

			$wgOut->addHTML(
				Xml::openElement( 'fieldset' ) .
				Xml::tags( 'legend', null,  $name ) .
				$text .
				Xml::closeElement( 'fieldset' )
			);
		}

		$deletedSections = $page->getParse()->getDeletedSections();
		if ( count($deletedSections) ) {
			$wgOut->addWikiMsg( 'tpt-deletedsections' );
			foreach ( $deletedSections as $s ) {
				$name = htmlspecialchars( $s->id );
				$wgOut->addHTML(
					Xml::openElement( 'fieldset' ) .
					Xml::tags( 'legend', null, wfMsgHtml('tpt-section') . ' ' . $name ) .
					TranslateUtils::convertWhiteSpaceToHTML( $s->getText() ) .
					Xml::closeElement( 'fieldset' )
				);
			}
		}
		$wgOut->addHTML(
			Xml::submitButton( wfMsg( 'tpt-submit' ) ) .
			Xml::closeElement( 'form' )
		);
	}

	public function markForTranslation( TranslatablePage $page, $sections ) {
		$text = $page->getParse()->getSourcePageText();

		$article = new Article( $page->getTitle() );
		$status = $article->doEdit(
			$text,
			wfMsgForContent( 'tpt-mark-summary' ),
			EDIT_FORCE_BOT | EDIT_UPDATE,
			$page->getRevision()
		);

		if ( !$status->isOK() ) return array( 'tpt-edit-failed', $status->getWikiText() );

		$newrevision = $status->value['revision'];
		if ( $newrevision === null ) {
			// Probably a no-change edit, so no new revision was assigned
			$newrevision = $page->getTitle()->getLatestRevId();
		}
		
		$inserts = array();
		$changed = array();
		foreach ( $sections as $s ) {
			if ( $s->type === 'changed' ) $changed[] = $s->name;
			$inserts[] = array(
				'trs_page' => $page->getTitle()->getArticleId(),
				'trs_key' => $s->name,
				'trs_text' => $s->getText(),
			);
		}
		// Don't add empty rows
		if ( !count($changed) ) $changed = null;

		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete( 'translate_sections', array( 'trs_page' => $page->getTitle()->getArticleId() ), __METHOD__ );
		$ok = $dbw->insert( 'translate_sections', $inserts, __METHOD__ );
		if ( $ok === false ) return array( 'tpt-insert-failed' );

		$page->addMarkedTag( $newrevision, $changed );

		MessageIndex::cache( NS_TRANSLATIONS );
		return false;
	}

}