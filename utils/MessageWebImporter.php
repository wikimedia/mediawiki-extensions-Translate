<?php
/**
 * Class which encapsulates message importing. It scans for changes (new, changed, deleted),
 * displays them in pretty way with diffs and finally executes the actions the user choices.
 *
 * @addtogroup Extensions
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2009, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class MessageWebImporter {
	protected $title;
	protected $user;
	protected $group;
	protected $code;

	protected $processingTime = 60; // Seconds

	public function __construct( Title $title = null, $group = null, $code = 'en' ) {
		$this->setTitle( $title );
		$this->setGroup( $group );
		$this->setCode( $code );
	}

	// Wrapper for consistency with SpecialPage
	public function getTitle() { return $this->title; }
	public function setTitle( Title $title ) { $this->title = $title; }

	public function getUser() {
		global $wgUser;
		return $this->user ? $this->user : $wgUser;
	}

	public function setUser( User $user ) {
		$this->user = $user;
	}

	public function getGroup() {
		return $this->group;
	}

	/**
	 * Group is either MessageGroup object or group id.
	 */
	public function setGroup( $group ) {
		if ( $group instanceof MessageGroup ) {
			$this->group = $group;
		} else {
			$this->group = MessageGroups::getGroup( $group );
		}
	}

	public function getCode() {
		return $this->code;
	}

	public function setCode( $code = 'en' ) {
		$this->code = $code;
	}

	protected function getAction() {
		return $this->getTitle()->getFullURL();
	}

	protected function doHeader() {
		TranslateUtils::injectCSS();

		$formParams = array(
			'method' => 'post',
			'action' => $this->getAction(),
			'class'  => 'mw-translate-manage'
		);

		return
			Xml::openElement( 'form', $formParams ) .
			Xml::hidden( 'title', $this->getTitle()->getPrefixedText() ) .
			Xml::hidden( 'token', $this->getUser()->editToken() ) .
			Xml::hidden( 'process', 1 );
	}

	protected function doFooter() {
		return '</form>';
	}

	protected function allowProcess() {
		global $wgRequest;

		if ( $wgRequest->wasPosted() &&
			$wgRequest->getBool( 'process', false ) &&
			$this->getUser()->matchEditToken( $wgRequest->getVal( 'token' ) ) ) {

			return true;
		}
		return false;
	}

	protected function getActions() {
		if ( $this->code === 'en' ) {
			return array( 'import', 'fuzzy', 'ignore' );
		} else {
			return array( 'import', 'conflict', 'ignore' );
		}
	}

	protected function getDefaultAction( $fuzzy, $action ) {
		if ( $action ) {
			return $action;
		}

		return $fuzzy ? 'conflict' : 'import';
	}

	public function execute( $messages ) {
		global $wgOut;

		$this->out = $wgOut;

		// Set up diff engine
		$diff = new DifferenceEngine;
		$diff->showDiffStyle();
		$diff->setReducedLineNumbers();

		// Check whether we do processing
		$process = $this->allowProcess();

		// Initialise collection
		$group = $this->getGroup();
		$code = $this->getCode();
		$collection = $group->initCollection( $code );
		$collection->loadTranslations();

		$this->out->addHTML( $this->doHeader() );

		// Determine changes
		$alldone = $process;
		$changed = array();

		foreach ( $messages as $key => $value ) {
			$fuzzy = $old = false;

			if ( isset( $collection[$key] ) ) {
				$old = $collection[$key]->translation();
				/* Fuzzy state of the in-wiki message is of no
				 * concern. Default action should be 'import'.
				 * If the string is marked fuzzy in the gettext,
				 * fuzzy tag is added anyway on import.
				$fuzzy = TranslateEditAddons::hasFuzzyString( $old ) ||
					TranslateEditAddons::isFuzzy( self::makeTitle( $group, $key, $code ) );
				 */
			}

			// No changes at all, ignore
			if ( strval( $old ) === strval( $value ) ) continue;

			if ( $old === false ) {
				$name = wfMsgHtml( 'translate-manage-import-new',
					'<code style="font-weight:normal;">' . htmlspecialchars( $key ) . '</code>'
				);
				$text = TranslateUtils::convertWhiteSpaceToHTML( $value );
				$changed[] = $this->makeSectionElement( $name, 'new', $text );
			} else {
				$diff->setText( $old, $value );
				$text = $diff->getDiff( '', '' );
				$type = 'changed';

				global $wgRequest;
				# Spaces don't seem to survive round trip in addition to dots
				# which are silently handled in getVal
				$safekey = str_replace( ' ', '_', $key );
				$action = $wgRequest->getVal( "action-$type-$safekey" );

				if ( $process ) {
					if ( !count( $changed ) ) {
						$changed[] = '<ul>';
					}

					global $wgLang;
					if ( $action === null ) {
						$message = wfMsgExt( 'translate-manage-inconsistent', 'parseinline', wfEscapeWikiText( "action-$type-$key" ) );
						$changed[] = "<li>$message</li></ul>";
						$process = false;
					} else {
						// Check processing time
						if ( !isset( $this->time ) ) $this->time = wfTimestamp();

						$message = $this->doAction( $action, $group, $key, $code, $value );

						$key = array_shift( $message );
						$params = $message;
						$message = wfMsgExt( $key, 'parseinline', $params );
						$changed[] = "<li>$message</li>";

						if ( $this->checkProcessTime() ) {
							$process = false;
							$duration = $wgLang->formatNum( $this->processingTime );
							$message = wfMsgExt( 'translate-manage-toolong', 'parseinline', $duration );
							$changed[] = "<li>$message</li></ul>";
						}
						continue;
					}
				}

				$alldone = false;

				$actions = $this->getActions();
				$defaction = $this->getDefaultAction( $fuzzy, $action );

				$act = array();

				foreach ( $actions as $action ) {
					$label = wfMsg( "translate-manage-action-$action" );
					$act[] = Xml::radioLabel( $label, "action-$type-$key", $action, "action-$key-$action", $action === $defaction );
				}

				$name = wfMsg( 'translate-manage-import-diff',
					'<code style="font-weight:normal;">' . htmlspecialchars( $key ) . '</code>',
					implode( ' ', $act )
				);

				$changed[] = $this->makeSectionElement( $name, $type, $text );
			}
		}

		if ( !$process ) {
			$collection->filter( 'hastranslation', false );
			$keys = array_keys( $collection->keys() );

			$diff = array_diff( $keys, array_keys( $messages ) );

			foreach ( $diff as $s ) {
				$name = wfMsgHtml( 'translate-manage-import-deleted',
					'<code style="font-weight:normal;">' . htmlspecialchars( $s ) . '</code>'
				);
				$text = TranslateUtils::convertWhiteSpaceToHTML(  $collection[$s]->translation() );
				$changed[] = $this->makeSectionElement( $name, 'deleted', $text );
			}
		}

		if ( $process || ( !count( $changed ) && $code !== 'en' ) ) {
			if ( !count( $changed ) ) {
				$this->out->addWikiMsg( 'translate-manage-nochanges-other' );
			}

			if ( !count( $changed ) || strpos( $changed[count( $changed ) - 1], '<li>' ) !== 0 ) {
				$changed[] = '<ul>';
			}

			$message = wfMsgExt( 'translate-manage-import-done', 'parseinline' );
			$changed[] = "<li>$message</li></ul>";
			$this->out->addHTML( implode( "\n", $changed ) );
		} else {
			// END
			if ( count( $changed ) ) {
				if ( $code === 'en' ) {
					$this->out->addWikiMsg( 'translate-manage-intro-en' );
				} else {
					global $wgLang;
					$lang = TranslateUtils::getLanguageName( $code, false, $wgLang->getCode() );
					$this->out->addWikiMsg( 'translate-manage-intro-other', $lang );
				}
				$this->out->addHTML( Xml::hidden( 'language', $code ) );
				$this->out->addHTML( implode( "\n", $changed ) );
				$this->out->addHTML( Xml::submitButton( wfMsg( 'translate-manage-submit' ) ) );
			} else {
				$this->out->addWikiMsg( 'translate-manage-nochanges' );
			}
		}

		$this->out->addHTML( $this->doFooter() );
		return $alldone;
	}

	// FIXME: lot of duplication with SpecialManageGroups::doAction()
	protected function doAction( $action, $group, $key, $code, $message, $comment = '' ) {
		if ( $action === 'import' || $action === 'conflict' ) {
			if ( $action === 'import' ) {
				$comment = wfMsgForContentNoTrans( 'translate-manage-import-summary' );
			} else {
				$comment = wfMsgForContentNoTrans( 'translate-manage-conflict-summary' );
				$message = TRANSLATE_FUZZY . $message;
			}

			$title = self::makeTitle( $group, $key, $code );

			return $this->doImport( $title, $message, $comment );
		} elseif ( $action === 'ignore' ) {
			return array( 'translate-manage-import-ignore', $key );
		} elseif ( $action === 'fuzzy' && $code != 'en' ) {
			return $this->doFuzzy( $title, $message, $comment );
		} else {
			throw new MWException( "Unhandled action $action" );
		}
	}

	protected function checkProcessTime() {
		return wfTimestamp() - $this->time >= $this->processingTime;
	}

	// FIXME: lot of duplication with SpecialManageGroups::doImport()
	protected function doImport( $title, $message, $comment, $user = null ) {
		$article = new Article( $title );
		$status = $article->doEdit( $message, $comment );
		$success = $status->isOK();

		if ( $success ) {
			return array( 'translate-manage-import-ok',
				wfEscapeWikiText( $title->getPrefixedText() )
			);
		} else {
			throw new MWException( "Failed to import new version of page {$title->getPrefixedText()}\n{$status->getWikiText()}" );
		}
	}

	// FIXME: lot of duplication with SpecialManageGroups::doFuzzy()
	protected function doFuzzy( $title, $message, $comment ) {
		$dbw = wfGetDB( DB_MASTER );
		$titleText = $title->getDBKey();
		$condArray = array(
			'page_namespace' => $title->getNamespace(),
			'page_latest=rev_id',
			'rev_text_id=old_id',
			"page_title LIKE '{$dbw->escapeLike( $titleText )}/%%'"
		);

		$rows = $dbw->select(
			array( 'page', 'revision', 'text' ),
			array( 'page_title', 'page_namespace', 'old_text', 'old_flags' ),
			$conds,
			__METHOD__
		);

		$changed = array();
		$fuzzybot = self::getFuzzyBot();

		foreach ( $rows as $row ) {
			$ttitle = Title::makeTitle( $row->page_namespace, $row->page_title );

			$changed[] = $this->doImport(
				$ttitle,
				TRANSLATE_FUZZY . Revision::getRevisionText( $row ),
				$comment,
				$fuzzybot
			);

			if ( $this->checkProcessTime() ) {
				break;
			}
		}

		if ( count( $changed ) === count( $rows ) ) {
			$comment = wfMsgForContentNoTrans( 'translate-manage-import-summary' );
			$changed[] = $this->doImport( $title, $message, $comment );
		}

		$text = '';
		foreach ( $changed as $c ) {
			$key = array_shift( $c );
			$text = "* " . wfMsgExt( $key, array(), $c );
		}

		return array( 'translate-manage-import-fuzzy', "\n" . $text );
	}

	// FIXME: duplicate of SpecialManageGroups::getFuzzyBot()
	protected static function getFuzzyBot() {
		global $wgTranslateFuzzyBotName;

		$user = User::newFromName( $wgTranslateFuzzyBotName );

		if ( !$user->isLoggedIn() ) {
			$user->addToDatabase();
		}

		return $user;
	}

	// FIXME: duplicate of SpecialManageGroups::makeTitle()
	protected static function makeTitle( $group, $key, $code ) {
		$ns = $group->getNamespace();
		$titlekey = "$key/$code";

		return Title::makeTitleSafe( $ns, $titlekey );
	}

	protected function makeSectionElement( $legend, $type, $content ) {
		$containerParams = array( 'class' => "mw-tpt-sp-section mw-tpt-sp-section-type-{$type}" );
		$legendParams = array( 'class' => 'mw-translate-manage-legend' );
		$contentParams = array( 'class' => 'mw-tpt-sp-content' );

		return Xml::tags( 'div', $containerParams,
			Xml::tags( 'div', $legendParams, $legend ) .
			Xml::tags( 'div', $contentParams, $content )
		);
	}
}
