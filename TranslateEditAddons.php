<?php
/**
 * Tools for edit page view to aid translators. This implements the so called
 * old style editing, which extends the normal edit page.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2007-2010 Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Various editing enhancements to the edit page interface.
 * Partly succeeded by the new ajax-enhanced editor but kept for compatibility.
 * Also has code that is still relevant, like the hooks on save.
 */
class TranslateEditAddons {

	/**
	 * Add some ugly navigation links below translations.
	 */
	static function addNavigation( &$outputpage, &$text ) {
		global $wgUser, $wgTitle;

		if ( !self::isMessageNamespace( $wgTitle ) ) {
			return true;
		}

		list( $key, $code, $group ) = self::getKeyCodeGroup( $wgTitle );
		if ( !$group || !$code ) {
			return true;
		}

		if ( $group instanceof MessageGroupBase ) {
			$cache = new MessageGroupCache( $group );

			if ( !$cache->exists() ) {
				return true;
			}

			$keys = $cache->getKeys();
			$defs = array();

			foreach ( $keys as $_ ) {
				$defs[$_] = $cache->get( $_ );
			}

			$skip = array_merge( $group->getTags( 'ignored' ), $group->getTags( 'optional' ) );
		} else {
			$defs = $group->getDefinitions();
			$skip = array_merge( $group->getIgnored(), $group->getOptional() );
		}

		$key = strtolower( strtr( $key, ' ', '_' ) );

		$next = $prev = $def = null;
		foreach ( array_keys( $defs ) as $tkey ) {
			if ( in_array( $tkey, $skip ) ) {
				continue;
			}

			/*
			 * Keys can have mixed case, but they have to be unique in a case
			 * insensitive manner. It is therefore safe and a must to use case
			 * insensitive comparison method.
			 */
			if ( $key === strtolower( strtr( $tkey, ' ', '_' ) ) ) {
				$next = true;
				$def = $defs[$tkey];
				continue;
			} elseif ( $next === true ) {
				$next = $tkey;
				break;
			}

			$prev = $tkey;
		}

		$skin = $wgUser->getSkin();
		$id = $group->getId();

		$ns = $wgTitle->getNamespace();
		$title = Title::makeTitleSafe( $ns, "$prev/$code" );
		$prevLink = wfMsgHtml( 'translate-edit-goto-no-prev' );

		$params = array();

		if ( $prev !== null ) {
			$params['loadgroup'] = $id;
			if ( !$title->exists() ) {
				$params['action'] = 'edit';
			}

			$prevLink = $skin->link( $title,
				wfMsgHtml( 'translate-edit-goto-prev' ), array(), $params );
		}

		$title = Title::makeTitleSafe( $ns, "$next/$code" );
		$nextLink = wfMsgHtml( 'translate-edit-goto-no-next' );

		if ( $next !== null && $next !== true ) {
			$params['loadgroup'] = $id;

			if ( !$title->exists() ) {
				$params['action'] = 'edit';
			}

			$nextLink = $skin->link( $title,
				wfMsgHtml( 'translate-edit-goto-next' ), array(), $params );
		}

		$title = SpecialPage::getTitleFor( 'Translate' );
		$title->mFragment = "msg_$next";
		$list = $skin->link(
			$title,
			wfMsgHtml( 'translate-edit-goto-list' ),
			array(),
			array(
				'group' => $id,
				'language' => $code
			)
		);

		$def = TranslateUtils::convertWhiteSpaceToHTML( $def );

		$text .= <<<EOEO
<hr />
<ul class="mw-translate-nav-prev-next-list">
<li>$prevLink</li>
<li>$nextLink</li>
<li>$list</li>
</ul><hr />
<div class="mw-translate-definition-preview">$def</div>
EOEO;

		return true;
	}

	/**
	 * Keep the usual diiba daaba hidden from translators.
	 */
	static function intro( $object ) {
		$object->suppressIntro = true;

		return true;
	}

	/**
	 * Adds the translation aids and navigation to the normal edit page.
	 */
	static function addTools( $object ) {
		if ( !self::isMessageNamespace( $object->mTitle ) ) {
			return true;
		}

		TranslateEditAddons::addNavigation( $ignored, $object->editFormTextTop );
		$object->editFormTextTop .= self::editBoxes( $object );

		return true;
	}

	/**
	 * Replace the normal save button with one that says if you are editing
	 * message documentation to try to avoid accidents.
	 */
	static function buttonHack( $editpage, &$buttons, $tabindex ) {
		global $wgTranslateDocumentationLanguageCode;

		if ( !self::isMessageNamespace( $editpage->mTitle ) ) {
			return true;
		}

		global $wgLang;

		list( , $code ) = self::figureMessage( $editpage->mTitle );

		if ( $code !== $wgTranslateDocumentationLanguageCode ) {
			return true;
		}

		$name = TranslateUtils::getLanguageName( $code, false, $wgLang->getCode() );
		$temp = array(
			'id'        => 'wpSave',
			'name'      => 'wpSave',
			'type'      => 'submit',
			'tabindex'  => ++$tabindex,
			'value'     => wfMsg( 'translate-save', $name ),
			'accesskey' => wfMsg( 'accesskey-save' ),
			'title'     => wfMsg( 'tooltip-save' ) . ' [' . wfMsg( 'accesskey-save' ) . ']',
		);
		$buttons['save'] = Xml::element( 'input', $temp, '' );

		return true;
	}

	/**
	* @return Array of the message and the language
	*/
	public static function figureMessage( Title $title ) {
		$text = $title->getDBkey();
		$pos = strrpos( $text, '/' );

		if ( $pos === false ) {
			$code = '';
			$key = $text;
		} else {
			$code = substr( $text, $pos + 1 );
			$key = substr( $text, 0, $pos );
		}

		return array( $key, $code );
	}

	public static function getKeyCodeGroup( Title $title ) {
		list( $key, $code ) = self::figureMessage( $title );
		$group = self::getMessageGroup( $title->getNamespace(), $key );

		return array( $key, $code, $group );
	}

	/**
	 * Tries to determine to which group this message belongs. It tries to get
	 * group id from loadgroup GET-paramater, but fallbacks to messageIndex file
	 * if no valid group was provided, or the group provided is a meta group.
	 *
	 * @param $namespace \int The namespace number for the key we are interested in.
	 * @param $key \string The message key we are interested in.
	 * @return MessageGroup which the key belongs to, or null.
	 */
	private static function getMessageGroup( $namespace, $key ) {
		global $wgRequest;

		$group = $wgRequest->getText( 'loadgroup', '' );
		$mg = MessageGroups::getGroup( $group );

		if ( $mg === null ) {
			$group = TranslateUtils::messageKeyToGroup( $namespace, $key );
			if ( $group ) {
				$mg = MessageGroups::getGroup( $group );
			}
		}

		return $mg;
	}

	private static function editBoxes( $object ) {
		global $wgOut, $wgRequest;

		$th = new TranslationHelpers( $object->mTitle );
		if ( $object->firsttime && !$wgRequest->getCheck( 'oldid' ) && !$wgRequest->getCheck( 'undo' ) ) {
			$object->textbox1 = $th->getTranslation();
		} else {
			$th->setTranslation( $object->textbox1 );
		}

		TranslateUtils::injectCSS();
		$wgOut->includeJQuery();

		return $th->getBoxes();
	}

	/**
	 * Check if a string contains the fuzzy string.
	 *
	 * @param $text \string Arbitrary text
	 * @return \bool If string contains fuzzy string.
	 */
	public static function hasFuzzyString( $text ) {
		return strpos( $text, TRANSLATE_FUZZY ) !== false;
	}

	/** Check if a title is marked as fuzzy.
	 *
	 * @param $title Title
	 * @return \bool If title is marked fuzzy.
	 */
	public static function isFuzzy( Title $title ) {
		$dbr = wfGetDB( DB_SLAVE );
		$id = $dbr->selectField( 'revtag_type', 'rtt_id', array( 'rtt_name' => 'fuzzy' ), __METHOD__ );

		$tables = array( 'page', 'revtag' );
		$fields = array( 'rt_type' );
		$conds  = array(
			'page_namespace' => $title->getNamespace(),
			'page_title' => $title->getDBkey(),
			'rt_type' => $id,
			'page_id=rt_page',
			'page_latest=rt_revision'
		);

		$res = $dbr->selectField( $tables, $fields, $conds, __METHOD__ );

		return $res === $id;
	}


	/** Check if a title is in a message namespace.
	 *
	 * @param $title Title
	 * @return \bool If title is in a message namespace.
	 */
	public static function isMessageNamespace( Title $title ) {
		global $wgTranslateMessageNamespaces;

		$namespace = $title->getNamespace();

		return in_array( $namespace, $wgTranslateMessageNamespaces, true );
	}

	public static function tabs( $skin, &$tabs ) {
		if ( !self::isMessageNamespace( $skin->mTitle ) ) {
			return true;
		}

		unset( $tabs['protect'] );

		return true;
	}

	public static function keepFields( $edit, $out ) {
		global $wgRequest;

		$out->addHTML( "\n" .
			Xml::hidden( 'loadgroup', $wgRequest->getText( 'loadgroup' ) ) .
			Xml::hidden( 'loadtask', $wgRequest->getText( 'loadtask' ) ) .
			"\n"
		);

		return true;
	}

	public static function onSave( $article, $user, $text, $summary,
			$minor, $_, $_, $flags, $revision
	) {
		$title = $article->getTitle();

		if ( !self::isMessageNamespace( $title ) ) {
			return true;
		}

		list( $key, $code, $group ) = self::getKeyCodeGroup( $title );

		/**
		 * Unknown message, do not handle.
		 */
		if ( !$group || !$code ) {
			return true;
		}

		$groups = TranslateUtils::messageKeyToGroups( $title->getNamespace(), $key );
		$cache = new ArrayMemoryCache( 'groupstats' );

		foreach ( $groups as $g ) {
			$cache->clear( $g, $code );
		}

		/**
		 * Check for explicit tag.
		 */
		$fuzzy = self::hasFuzzyString( $text );

		/**
		 * Check for problems, but only if not fuzzy already.
		 */
		global $wgTranslateDocumentationLanguageCode;
		if ( $code !== $wgTranslateDocumentationLanguageCode ) {
			$checker = $group->getChecker();

			if ( $checker ) {
				$en = $group->getMessage( $key, 'en' );
				$message = new FatMessage( $key, $en );
				/**
				 * Take the contents from edit field as a translation.
				 */
				$message->setTranslation( $text );

				$checks = $checker->checkMessage( $message, $code );
				if ( count( $checks ) ) {
					$fuzzy = true;
				}
			}
		}

		// Update it.
		if ( $revision === null ) {
			$rev = $article->getTitle()->getLatestRevId();
		} else {
			$rev = $revision->getID();
		}

		// begin fuzzy tag.
		$dbw = wfGetDB( DB_MASTER );

		$id = $dbw->selectField( 'revtag_type', 'rtt_id', array( 'rtt_name' => 'fuzzy' ), __METHOD__ );

		$conds = array(
			'rt_page' => $article->getTitle()->getArticleId(),
			'rt_type' => $id,
			'rt_revision' => $rev
		);
		// Remove any existing fuzzy tags for this revision
		$dbw->delete( 'revtag', $conds, __METHOD__ );

		// Add the fuzzy tag if needed.
		if ( $fuzzy !== false ) {
			$dbw->insert( 'revtag', $conds, __METHOD__ );
		}


		// Diffs for changed messages.
		if ( $fuzzy !== false ) {
			return true;
		}

		if ( $group instanceof WikiPageMessageGroup ) {
			return true;
		}

		$definitionTitle = Title::makeTitleSafe( $title->getNamespace(), "$key/en" );
		if ( $definitionTitle && $definitionTitle->exists() ) {
			$definitionRevision = $definitionTitle->getLatestRevID();

			$id = $dbw->selectField( 'revtag_type', 'rtt_id',
				array( 'rtt_name' => 'tp:transver' ), __METHOD__ );

			$conds = array(
				'rt_page' => $title->getArticleId(),
				'rt_type' => $id,
				'rt_revision' => $rev,
			);
			$dbw->delete( 'revtag', $conds, __METHOD__ );
			$conds['rt_value'] = $definitionRevision;
			$dbw->insert( 'revtag', $conds, __METHOD__ );
		}

		return true;
	}

	public static function customDisplay( $article, &$content ) {
		if ( self::isMessageNamespace( $article->getTitle() ) ) {
			$content = "<pre><nowiki>$content</nowiki></pre>";
		}
		return true;
	}
}
