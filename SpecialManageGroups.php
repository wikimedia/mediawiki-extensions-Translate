<?php
/**
 * Implements classes for Special:Translate/manage from where file based message
 * groups are be managed.
 *
 * @ingroup SpecialPage
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2009-2010, Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Class for special page Special:Translate/manage. On this special page file
 * based message groups can be managed (FileBasedMessageGroup). This page
 * allows updating of the file cache, import and fuzzy for source language
 * messages, as well as import/update of messages in other languages.
 *
 * @todo Needs documentation.
 */
class SpecialManageGroups {
	protected $skin, $user, $out;
	/**
	 * Maximum allowed processing time in seconds.
	 */
	protected $processingTime = 30;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wgOut, $wgUser;
		$this->out = $wgOut;
		$this->user = $wgUser;
		$this->skin = $wgUser->getSkin();
	}

	public function execute() {
		global $wgRequest;

		$this->out->setPageTitle( htmlspecialchars( wfMsg( 'translate-managegroups' ) ) );

		$group = $wgRequest->getText( 'group' );
		$group = MessageGroups::getGroup( $group );

		/**
		 * Only supported for FileBasedMessageGroups.
		 */
		if ( !$group instanceof FileBasedMessageGroup ) {
			$group = null;
		}

		if ( $group ) {
			if (
				$wgRequest->getBool( 'rebuildall', false ) &&
				$wgRequest->wasPosted() &&
				$this->user->isAllowed( 'translate-manage' ) &&
				$this->user->matchEditToken( $wgRequest->getVal( 'token' ) )
			) {
				$languages = explode( ',', $wgRequest->getText( 'codes' ) );
				foreach ( $languages as $code ) {
					$cache = new MessageGroupCache( $group, $code );
					$cache->create();
				}
			}

			$code = $wgRequest->getText( 'language', 'en' );
			// Go to English for undefined codes.
			$codes = array_keys( Language::getLanguageNames( false ) );
			if ( !in_array( $code, $codes ) ) {
				$code = 'en';
			}

			$this->importForm( $group, $code );
		} else {
			global $wgLang, $wgOut;

			$groups = MessageGroups::singleton()->getGroups();

			$wgOut->wrapWikiMsg( '==$1==', 'translate-manage-listgroups' );
			$separator = wfMsg( 'word-separator' );

			foreach ( $groups as $group ) {
				if ( !$group instanceof FileBasedMessageGroup ) {
					continue;
				}

				$link = $this->skin->link( $this->getTitle(), $group->getLabel(), array(), array( 'group' => $group->getId() ) );
				$out = $link . $separator;

				$cache = new MessageGroupCache( $group );
				if ( $cache->exists() ) {
					$timestamp = wfTimestamp( TS_MW, $cache->getTimestamp() );
					$out .= wfMsg( 'translate-manage-cacheat',
						$wgLang->date( $timestamp ),
						$wgLang->time( $timestamp )
					);

					if ( $this->changedSinceCached( $group ) ) {
						$out = '<span style="color:red">!!</span> ' . $out;
					}

				} else {
					$out .= wfMsg( 'translate-manage-newgroup' );
				}

				$wgOut->addHtml( $out );
				$wgOut->addHtml( '<hr>' );
			}

			$wgOut->wrapWikiMsg( '==$1==', 'translate-manage-listgroups-old' );
			$wgOut->addHTML( '<ul>' );

			foreach ( $groups as $group ) {
				if ( $group instanceof FileBasedMessageGroup ) {
					continue;
				}

				$wgOut->addHtml( Xml::element( 'li', null, $group->getLabel() ) );
			}

			$wgOut->addHTML( '</ul>' );
		}
	}

	/**
	 * Special:Translate/manage.
	 */
	public function getTitle() {
		return SpecialPage::getTitleFor( 'Translate', 'manage' );
	}

	/**
	 * @todo Very long code block; split up.
	 */
	public function importForm( $group, $code ) {
		$this->setSubtitle( $group, $code );

		$formParams = array(
			'method' => 'post',
			'action' => $this->getTitle()->getFullURL( array( 'group' => $group->getId() ) ),
			'class'  => 'mw-translate-manage'
		);

		global $wgRequest, $wgLang;
		if (
			$wgRequest->wasPosted() &&
			$wgRequest->getBool( 'process', false ) &&
			$this->user->isAllowed( 'translate-manage' ) &&
			$this->user->matchEditToken( $wgRequest->getVal( 'token' ) )
		) {
			$process = true;
		} else {
			$process = false;
		}

		$this->out->addHTML(
			Xml::openElement( 'form', $formParams ) .
			Xml::hidden( 'title', $this->getTitle()->getPrefixedText() ) .
			Xml::hidden( 'token', $this->user->editToken() ) .
			Xml::hidden( 'group', $group->getId() ) .
			Xml::hidden( 'process', 1 )
		);

		// BEGIN
		$cache = new MessageGroupCache( $group, $code );
		if ( !$cache->exists() && $code === 'en' ) {
			$cache->create();
		}

		$collection = $group->initCollection( $code );
		$collection->loadTranslations();

		$diff = new DifferenceEngine;
		$diff->showDiffStyle();
		$diff->setReducedLineNumbers();

		$ignoredMessages = $collection->getTags( 'ignored' );
		if ( !is_array( $ignoredMessages ) ) {
			$ignoredMessages = array();
		}

		$messages = $group->load( $code );
		$changed = array();
		foreach ( $messages as $key => $value ) {
			// ignored? ignore!
			if ( in_array( $key, $ignoredMessages ) ) {
				continue;
			}

			$fuzzy = $old = false;

			if ( isset( $collection[$key] ) ) {
				$old = $collection[$key]->translation();
			}

			// No changes at all, ignore.
			if ( str_replace( TRANSLATE_FUZZY, '', $old ) === $value ) {
				continue;
			}

			if ( $old === false ) {
				$name = wfMsgHtml( 'translate-manage-import-new',
					'<code style="font-weight:normal;">' . htmlspecialchars( $key ) . '</code>'
				);

				$text = TranslateUtils::convertWhiteSpaceToHTML( $value );

				$changed[] = MessageWebImporter::makeSectionElement( $name, 'new', $text );
			} else {
				if ( TranslateEditAddons::hasFuzzyString( $old ) ) {
					// NO-OP
				} else {
					$transTitle = MessageWebImporter::makeTranslationTitle( $group, $key, $code );
					if ( TranslateEditAddons::isFuzzy( $transTitle ) ) {
						$old = TRANSLATE_FUZZY . $old;
					}
				}

				$diff->setText( $old, $value );
				$text = $diff->getDiff( '', '' );
				$type = 'changed';

				if ( $process ) {
					if ( !count( $changed ) ) {
						$changed[] = '<ul>';
					}

					$action = $wgRequest->getVal( MessageWebImporter::escapeNameForPHP( "action-$type-$key" ) );

					if ( $action === null ) {
						$message = wfMsgExt( 'translate-manage-inconsistent', 'parseinline', wfEscapeWikiText( "action-$type-$key" ) );
						$changed[] = "<li>$message</li></ul>";
						$process = false;
					} else {
						// Initialise processing time counter.
						if ( !isset( $this->time ) ) {
							$this->time = wfTimestamp();
						}

						$fuzzybot = MessageWebImporter::getFuzzyBot();
						$message = MessageWebImporter::doAction(
							$action,
							$group,
							$key,
							$code,
							$value,
							'', /* default edit summary */
							$fuzzybot,
							EDIT_FORCE_BOT
						);

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

				if ( $code !== 'en' ) {
					$actions = array( 'import', 'conflict', 'ignore' );
				} else {
					$actions = array( 'import', 'fuzzy', 'ignore' );
				}

				$act = array();
				$defaction = $fuzzy ? 'conflict' : 'import';

				foreach ( $actions as $action ) {
					$label = wfMsg( "translate-manage-action-$action" );
					$name = self::escapeNameForPHP( "action-$type-$key" );
					$id = Sanitizer::escapeId( "action-$key-$action" );
					$act[] = Xml::radioLabel( $label, $name, $action, $id, $action === $defaction );
				}

				$name = wfMsg( 'translate-manage-import-diff',
					'<code style="font-weight:normal;">' . htmlspecialchars( $key ) . '</code>',
					implode( ' ', $act )
				);

				$changed[] = MessageWebImporter::makeSectionElement( $name, $type, $text );
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

				$changed[] = MessageWebImporter::makeSectionElement( $name, 'deleted', $text );
			}
		}

		if ( $process || ( !count( $changed ) && $code !== 'en' ) ) {
			if ( !count( $changed ) ) {
				$this->out->addWikiMsg( 'translate-manage-nochanges-other' );
			}

			if ( !count( $changed ) || strpos( $changed[count( $changed ) - 1], '<li>' ) !== 0 ) {
				$changed[] = '<ul>';
			}

			$cache->create();
			$message = wfMsgExt( 'translate-manage-import-rebuild', 'parseinline' );
			$changed[] = "<li>$message</li>";
			$message = wfMsgExt( 'translate-manage-import-done', 'parseinline' );
			$changed[] = "<li>$message</li></ul>";
			$this->out->addHTML( implode( "\n", $changed ) );
		} else {
			// END
			if ( count( $changed ) ) {
				if ( $code === 'en' ) {
					$this->out->addWikiMsg( 'translate-manage-intro-en' );
				} else {
					$lang = TranslateUtils::getLanguageName( $code, false, $wgLang->getCode() );
					$this->out->addWikiMsg( 'translate-manage-intro-other', $lang );
				}
				$this->out->addHTML( Xml::hidden( 'language', $code ) );
				$this->out->addHTML( implode( "\n", $changed ) );
				$this->out->addHTML( Xml::submitButton( wfMsg( 'translate-manage-submit' ) ) );
			} else {
				$cache->create(); // Update timestamp
				$this->out->addWikiMsg( 'translate-manage-nochanges' );
			}
		}

		$this->out->addHTML( '</form>' );

		if ( $code === 'en' ) {
			$this->doModLangs( $group );
		} else {
			$this->out->addHTML( '<p>' . $this->skin->link(
				$this->getTitle(),
				wfMsgHtml( 'translate-manage-return-to-group' ),
				array(),
				array( 'group' => $group->getId() )
			) . '</p>' );
		}
	}

	public function doModLangs( $group ) {
		global $wgLang;

		$languages = array_keys( Language::getLanguageNames( false ) );
		$modified = $codes = array();

		foreach ( $languages as $code ) {
			if ( $code === 'en' ) {
				continue;
			}

			if ( !$this->changedSinceCached( $group, $code ) ) {
				continue;
			}

			$link = $this->skin->link(
				$this->getTitle(),
				htmlspecialchars( TranslateUtils::getLanguageName( $code, false, $wgLang->getCode() ) . " ($code)" ),
				array(),
				array( 'group' => $group->getId(), 'language' => $code )
			);

			if ( !$cache->exists() ) {
				$modified[] = wfMsgHtml( 'translate-manage-modlang-new', $link  );
			} else {
				$modified[] = $link;
			}

			$codes[] = $code;
		}

		if ( count( $modified ) ) {
			$this->out->addWikiMsg( 'translate-manage-modlangs',
				$wgLang->formatNum( count( $modified ) )
			);

			$formParams = array(
				'method' => 'post',
				'action' => $this->getTitle()->getFullURL( array( 'group' => $group->getId() ) ),
			);

			$this->out->addHTML(
				Xml::openElement( 'form', $formParams ) .
				Xml::hidden( 'title', $this->getTitle()->getPrefixedText() ) .
				Xml::hidden( 'token', $this->user->editToken() ) .
				Xml::hidden( 'group', $group->getId() ) .
				Xml::hidden( 'codes', implode( ',', $codes ) ) .
				Xml::hidden( 'rebuildall', 1 ) .
				Xml::submitButton( wfMsg( 'translate-manage-import-rebuild-all' ) ) .
				Xml::closeElement( 'form' )
			);

			$this->out->addHTML(
				'<ul><li>' . implode( "</li>\n<li>", $modified ) . '</li></ul>'
			);
		}
	}

	/**
	 * Reports if processing time for current page has exceeded the set
	 * maximum ($processingTime).
	 */
	protected function checkProcessTime() {
		return wfTimestamp() - $this->time >= $this->processingTime;
	}

	/**
	 * Set a subtitle like "Manage > FreeCol (open source game) > German"
	 * based on group and language code. The language part is not shown if
	 * it is 'en', and all three possible parts of the subtitle are linked.
	 *
	 * @param $group Object MessageGroup.
	 * @param $code \string Language code.
	 */
	protected function setSubtitle( $group, $code ) {
		global $wgLang;

		$links[] = $this->skin->link(
			$this->getTitle(),
			wfMsgHtml( 'translate-manage-subtitle' )
		);

		$links[] = $this->skin->link(
			$this->getTitle(),
			$group->getLabel(),
			array(),
			array( 'group' => $group->getId() )
		);

		// Do not show language part for English.
		if ( $code !== 'en' ) {
			$links[] = $this->skin->link(
				$this->getTitle(),
				TranslateUtils::getLanguageName( $code, false, $wgLang->getCode() ),
				array(),
				array( 'group' => $group->getId(), 'language' => $code )
			);
		}

		$this->out->setSubtitle( implode( ' > ', $links ) );
	}

	/**
	 * Checks if the source file has changed since last check.
	 * Uses modification timestamps and file hashes to check.
	 */
	protected function changedSinceCached( $group, $code = 'en' ) {
		$cache = new MessageGroupCache( $group, $code );
		$filename = $group->getSourceFilePath( $code );

		$mtime = file_exists( $filename ) ? filemtime( $filename ) : false;
		$cachetime = $cache->exists() ? $cache->getTimestamp() : false;

		// No such language at all, or cache is up to date
		if ( $mtime <= $cachetime ) {
			return false;
		}

		// Timestamps differ (or either cache or the file does not exists)
		$oldhash = $cache->exists() ? $cache->getHash() : false;
		$newhash = file_exists( $filename ) ? md5( file_get_contents( $filename ) ) : false;
		wfDebugLog( 'translate-manage', "$mtime === $cachetime | $code | $oldhash !== $newhash\n" );
		if ( $newhash === $oldhash ) {
			// Update cache so that we don't need to compare hashes next time
			$cache->create();
			return false;
		}
	
		return true;
	}
}
