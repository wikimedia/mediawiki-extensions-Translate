<?php
if ( !defined( 'MEDIAWIKI' ) ) die();

/**
 * Tools for edit page view to aid translators.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2007-2009 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
class TranslateEditAddons {
	const MSG = 'translate-edit-';

	static function addNavigation( &$outputpage, &$text ) {
		global $wgUser, $wgTitle;
		static $done = false;
		if ($done) return true;
		$done = true;

		if ( !self::isMessageNamespace( $wgTitle ) ) return true;


		list( $key, $code, $group) = self::getKeyCodeGroup( $wgTitle );
		if ( $group === null ) return true;

		if ( $group instanceof MessageGroupBase ) {
			$cache = new MessageGroupCache($group);
			if ( !$cache->exists() ) return true;
			$keys = $cache->getKeys();
			$defs = array();
			foreach ( $keys as $_ ) $defs[$_] = $cache->get( $_ );
			$skip = array_merge( $group->getTags( 'ignored' ), $group->getTags( 'optional' ) );
		} else {
			$defs = $group->getDefinitions();
			$skip = array_merge( $group->getIgnored(), $group->getOptional() );
		}

		$key = strtolower( strtr( $key, ' ', '_' ) );

		$next = $prev = $def = null;
		foreach ( array_keys( $defs ) as $tkey ) {
			if ( in_array( $tkey, $skip ) ) continue;
			// Keys can have mixed case, but they have to be unique in a case
			// insensitive manner. It is therefore safe and a must to use case
			// insensitive comparison method
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
		wfLoadExtensionMessages( 'Translate' );

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

	static function intro( $object ) {
		$object->suppressIntro = true;
		return true;
	}


	static function addTools( $object ) {
		if ( !self::isMessageNamespace( $object->mTitle ) ) return true;
		
		TranslateEditAddons::addNavigation( &$ignored, $object->editFormTextTop );
		$object->editFormTextTop .= self::editBoxes( $object );

		return true;
	}

	static function buttonHack( $editpage, &$buttons, $tabindex ) {
		if ( !self::isMessageNamespace( $editpage->mTitle ) ) return true;

		global $wgLang;
		list( , $code ) = self::figureMessage( $editpage->mTitle );
		if ( $code !== 'qqq' ) return true;
		$name = TranslateUtils::getLanguageName( $code, false, $wgLang->getCode() );
		$temp = array(
			'id'        => 'wpSave',
			'name'      => 'wpSave',
			'type'      => 'submit',
			'tabindex'  => ++$tabindex,
			'value'     => wfMsg( 'translate-save', $name ),
			'accesskey' => wfMsg( 'accesskey-save' ),
			'title'     => wfMsg( 'tooltip-save' ).' ['.wfMsg( 'accesskey-save' ).']',
		);
		$buttons['save'] = Xml::element('input', $temp, '');
		return true;
	}

	private static function getFallbacks( $code ) {
		global $wgUser, $wgTranslateLanguageFallbacks;

		$preference = $wgUser->getOption( 'translate-editlangs' );
		if ( $preference !== 'default' ) {
			$fallbacks = array_map( 'trim', explode( ',', $preference ) );
			foreach( $fallbacks as $k => $v ) if ( $v === $code ) unset($fallbacks[$k]);
			return $fallbacks;
		}

		$fallbacks = array();
		if ( isset( $wgTranslateLanguageFallbacks[$code] ) ) {
				$temp = $wgTranslateLanguageFallbacks[$code];
			if ( !is_array( $temp ) ) {
				$fallbacks = array( $temp );
			} else {
				$fallbacks = $temp;
			}
		}

		$realFallback = $code ? Language::getFallbackFor( $code ) : 'en';
		if ( $realFallback && $realFallback !== 'en' ) {
			$fallbacks = array_merge( array( $realFallback ), $fallbacks );
		}

		return $fallbacks;
	}

	private static function doBox( $msg, $code, $title = false, $makelink = false, $group = false ) {
		global $wgUser, $wgLang;
		if ( $msg === null ) { return ''; }

		$name = TranslateUtils::getLanguageName( $code, false, $wgLang->getCode() );
		$code = strtolower( $code );

		$attributes = array();
		if ( !$title ) {
			$attributes['class'] = 'mw-sp-translate-in-other-big';
		} elseif ( $code === 'en' ) {
			$attributes['class'] = 'mw-sp-translate-edit-definition';
		} else {
			$attributes['class'] = 'mw-sp-translate-edit-committed';
		}
		if ( mb_strlen( $msg ) < 100 && !$title ) {
			$attributes['class'] = 'mw-sp-translate-in-other-small';
		}

		$msg = TranslateUtils::convertWhiteSpaceToHTML( $msg );

		if ( !$title ) $title = "$name ($code)";
		$title = htmlspecialchars( $title );

		if( $makelink ) {
			$skin = $wgUser->getSkin();
			$linkTitle = Title::newFromText( $makelink );
			$title = $skin->link(
				$linkTitle,
				$title,
				array(),
				array( 'action' => 'edit' )
			);
		}

		if( $group && $attributes['class'] == 'mw-sp-translate-edit-definition' ) {
			global $wgLang;

			$skin = $wgUser->getSkin();
			$userLang = $wgLang->getCode();
			$groupId = $group->getId();
			$linkTitle = SpecialPage::getTitleFor( 'Translate' );
			$title = $skin->link(
				$linkTitle,
				$title,
				array(),
				array(
					'group' => $groupId,
					'language' => $userLang
				)
			);
		}
		return TranslateUtils::fieldset( $title, Xml::tags( 'code', null, $msg ), $attributes );
	}

	/**
	* @return Array of the message and the language
	*/
	private static function figureMessage( Title $title ) {
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
	 * @param $key The message key we are interested in.
	 * @return MessageGroup which the key belongs to, or null.
	 */
	private static function getMessageGroup( $namespace, $key ) {
		global $wgRequest;
		$group = $wgRequest->getText( 'loadgroup', '' );
		$mg = MessageGroups::getGroup( $group );

		# If we were not given group, or the group given was meta...
		if ( is_null( $mg ) || $mg->isMeta() ) {
			# .. then try harder, because meta groups are *inefficient*
			$group = TranslateUtils::messageKeyToGroup( $namespace, $key );
			if ( $group ) {
				$mg = MessageGroups::getGroup( $group );
			}
		}

		return $mg;
	}

	private static function editBoxes( $object ) {
		global $wgTranslateDocumentationLanguageCode, $wgOut, $wgRequest;

		list( $key, $code, $group ) = self::getKeyCodeGroup( $object->mTitle );
		if ( $group === null ) return;

		$nsMain = $group->getNamespace();

		$en = $group->getMessage( $key, 'en' );
		$xx = $group->getMessage( $key, $code );

		// Set-up the content area contents properly and not randomly as in
		// MediaWiki core. $translation is also used for checks later on. Also
		// add the fuzzy string if necessary.
		$translation = TranslateUtils::getMessageContent( $key, $code, $nsMain );
		$isfuzzy = false;
		if ( $translation !== null ) {
			$isfuzzy = self::isFuzzy( $object->mTitle );
			if ( !self::hasFuzzyString( $translation) && $isfuzzy ) {
				$translation = TRANSLATE_FUZZY . $translation;
			}
		} else {
			$translation = $xx;
		}

		if ( $object->firsttime && !$wgRequest->getCheck( 'oldid' ) && !$wgRequest->getCheck( 'undo' ) ) {
			$object->textbox1 = $translation;
		} else {
			$translation = $object->textbox1;
		}

		$boxes = array();
		// In other languages (if any)
		$inOtherLanguages = array();
		$namespace = $object->mTitle->getNsText();
		foreach ( self::getFallbacks( $code ) as $fbcode ) {
			$fb = TranslateUtils::getMessageContent( $key, $fbcode, $nsMain );
			// Try harder TODO: fixme with the new localisation cache
			if ( $fb === null ) $fb = $group->getMessage( $key, $fbcode );
			if ( $fb !== null ) {
				/* add a link for editing the fallback messages */
				$inOtherLanguages[] = self::dobox( $fb, $fbcode, false, $namespace . ':' . $key . '/' . $fbcode );
			}
		}
		if ( count( $inOtherLanguages ) ) {
			$boxes[] = TranslateUtils::fieldset( wfMsgHtml( self::MSG . 'in-other-languages' , $key ),
				implode( "\n", $inOtherLanguages ), array( 'class' => 'mw-sp-translate-edit-inother' ) );
		}

		global $wgTranslateTM;
		if ( $wgTranslateTM !== false ) {
			$sugboxes = array();

			$server = $wgTranslateTM['server'];
			$port   = $wgTranslateTM['port'];
			$timeout= $wgTranslateTM['timeout'];

			$def = rawurlencode( $en );
			$url = "$server:$port/tmserver/en/$code/unit/$def";
			$suggestions = Http::get( $url, $timeout );
			if ( $suggestions !== false ) {
				$suggestions = json_decode( $suggestions, true );
				$suggestions = array_slice( $suggestions, 0, 3 );
				foreach ( $suggestions as $s ) {
					if ( $s['target'] === $translation ) continue;
					$sugboxes[] = TranslateUtils::fieldset( 
						wfMsgHtml( 'translate-edit-tmsug' , sprintf( '%.2f', $s['quality'] ) ),
						TranslateUtils::convertWhiteSpaceToHTML( $s['target'] ),
						array( 'class' => 'mw-sp-translate-edit-tmsug', 'title' => $s['source'] )
					);
				}
			}
			if ( count($sugboxes) > 1 ) {
				$boxes[] = TranslateUtils::fieldset( wfMsgHtml( 'translate-edit-tmsugs' ),
					implode( "\n", $sugboxes ), array( 'class' => 'mw-sp-translate-edit-tmsugs' ) );
			} elseif( count($sugboxes) ) {
				$boxes[] = $sugboxes[0];
			}
		}

		// Make the non-mandatory boxes a different group, for easy access
		$boxes = array(
			Xml::tags( 'div', array( 'class' => 'mw-sp-translate-edit-extra' ), implode( "\n\n", $boxes ) )
		);

		// User provided documentation
		if ( $wgTranslateDocumentationLanguageCode ) {
			global $wgUser;
			$title = Title::makeTitle( $nsMain, $key . '/' . $wgTranslateDocumentationLanguageCode );
			$edit = $wgUser->getSkin()->link(
				$title,
				wfMsgHtml( self::MSG . 'contribute' ),
				array(),
				array( 'action' => 'edit' )
			);
			$info = TranslateUtils::getMessageContent( $key, $wgTranslateDocumentationLanguageCode, $nsMain );
			if ( $info === null ) {
				$info = $group->getMessage( $key, $wgTranslateDocumentationLanguageCode );
			}
			$class = 'mw-sp-translate-edit-info';
			if ( $info === null ) {
				$info = wfMsg( self::MSG . 'no-information' );
				$class = 'mw-sp-translate-edit-noinfo';
			}

			if ( $group instanceof GettextMessageGroup ) {
				$reader = $group->getReader( 'en' );
				if ( $reader ) {
					$data = $reader->parseFile();
					$help = GettextFormatWriter::formatcomments( @$data[$key]['comments'], false, @$data[$key]['flags'] );
					$info .= "<hr /><pre>$help</pre>";
				}
			}

			$class .= ' mw-sp-translate-message-documentation';

			if ( $info ) {
				$contents = $wgOut->parse( $info );
				// Remove whatever block element wrapup the parser likes to add
				$contents = preg_replace( '~^<([a-z]+)>(.*)</\1>$~us', '\2', $contents );
				$boxes[] = TranslateUtils::fieldset(
					wfMsgHtml( self::MSG . 'information', $edit , $key ), $contents, array( 'class' => $class )
				);
			}
		}

		// Diff of current version of original, and its version before last edit of translation, if any
		global $wgEnablePageTranslation;
		$showdiff1 = ( $wgEnablePageTranslation && $group instanceof WikiPageMessageGroup );
		$showdiff2 = ( $isfuzzy );
		if ( $showdiff1 ) {
			// TODO: encapsulate somewhere
			$page = TranslatablePage::newFromTitle( $group->title );
			$rev = $page->getTransRev( "$key/$code" );
			$latest = $page->getMarkedTag();
			if ( $rev !== $latest ) {
				$oldpage = TranslatablePage::newFromRevision( $group->title, $rev );
				$oldtext = null;
				$newtext = null;
				foreach ( $oldpage->getParse()->getSectionsForSave() as $section ) {
					if ( $group->title->getPrefixedDBKey() .'/'. $section->id === $key ) {
						$oldtext = $section->getTextForTrans();
					}
				}
				foreach ( $page->getParse()->getSectionsForSave() as $section ) {
					if ( $group->title->getPrefixedDBKey() .'/'. $section->id === $key ) {
						$newtext = $section->getTextForTrans();
					}
				}
			}
			else
			{
			}
		} elseif ( $showdiff2 ) {
			$newtext = $en;	// FIXME: this should be sufficient, but this seems to come from the MessagesEnphp file instead of most recent the data base record, which is wrong.
			$en_title = Title::makeTitle( $nsMain, $key . '/en' );	// workaround
			$revision = Revision::newFromTitle($en_title);		// workaround
			$newtext = $revision->getText();			// workaround
			$oldtext = FALSE;
			// find timestamp of last revision of the current page before it was fuzzied.
			$rev = $object->getBaseRevision()->getId();
			if ( $rev )
			{
				$fname = __METHOD__ . ' (' . get_class( $this ) . ')';
				$dbr = wfGetDB( DB_SLAVE );
				$pid = $dbr->selectField( 'revision', 'rev_page', array( 'rev_id' => $rev ), $fname );
				if ( $pid )
				{
					$not_fuzzy = TranslateEditAddons::findLastRevisionByTag( $rev, 'fuzzy', FALSE );
					if ( $not_fuzzy )
					{
						$rev_en = $dbr->selectRow(
							array ( 'revision', 'page'),
							'rev_id',
							array(
								'page_namespace' => $nsMain,
								'page_title' => $key . '/en',
								'rev_page = page_id',
								'rev_timestamp < '.$not_fuzzy->rev_timestamp
							),
							$fname,
							array(
								'ORDER BY' => 'rev_timestamp DESC',
								'LIMIT' => 1
							)
						);
						if ( $rev_en )
						{
							$rev_en = $rev_en->rev_id;
						}
						if ( $rev_en )
						{
							$revision = Revision::newFromId($rev_en);
							$oldtext = $revision->getText();

							// FIXME: Own header messages for this case
							// FIXME: Links to the revision pages
							// FIXME: Previous and next links
							// FIXME: Allow switching to HTMLdiff
						}
					}
				}
			}
		}
		if ( ( $showdiff1 || $showdiff2 ) && ( $oldtext !== $newtext ) && ( $oldtext !== FALSE ) ) {
			wfLoadExtensionMessages( 'PageTranslation' );
			$diff = new DifferenceEngine;
			if ( $showdiff1 )
				$diff->setReducedLineNumbers();
			$diff->setText( $oldtext, $newtext );
			$diff->showDiffStyle();
			$boxes[] = $diff->getDiff( wfMsgHtml('tpt-diff-old'), wfMsgHtml('tpt-diff-new') );
							// FIXME: If there are versions in between say so.
		}

		// Definition
		if ( $en !== null ) {
			$label = " ({$group->getLabel()})";
			$boxes[] = self::doBox( $en, 'en', wfMsg( self::MSG . 'definition' ) . $label, false, $group );
		}

		// Some syntactic checks
		if ( $translation !== null && $code !== $wgTranslateDocumentationLanguageCode) {
			$message = new FatMessage( $key, $en );
			// Take the contents from edit field as a translation
			$message->setTranslation( $translation );
			$checker = $group->getChecker();
			if ( $checker ) {
				$checks = $checker->checkMessage( $message, $code );
				if ( count( $checks ) ) {
					$checkMessages = array();
					foreach ( $checks as $checkParams ) {
						array_splice( $checkParams, 1, 0, 'parseinline' );
						$checkMessages[] = call_user_func_array( 'wfMsgExt', $checkParams );
					}

					$boxes[] = TranslateUtils::fieldset(
						wfMsgHtml( self::MSG . 'warnings' ), implode( '<hr />', $checkMessages ),
						array( 'class' => 'mw-sp-translate-edit-warnings' ) );
				}
			}
		}

		TranslateUtils::injectCSS();
		return Xml::tags( 'div', array( 'class' => 'mw-sp-translate-edit-fields' ), implode( "\n\n", $boxes ) );
	}

	public static function hasFuzzyString( $text ) {
		return strpos( $text, TRANSLATE_FUZZY ) !== false;
	}

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

	/**
	 * Get last revision of a page (not) having a specific revision tag
	 * @param $pageId page Id
	 * @param $set	TRUE: find youngest revison having the tag
	 *		FALSE: find youngest revison *not* having the tag
	 * @param $rtt_name revision tag name to look for
	 * @return FALSE: no such revision found
	 *	otherwise,
	 *		if $not is TRUE:	object ( rev_id, rev_timestamp, NULL, NULL )
	 *		if $not is FALSE:	object ( rev_id, rev_timestamp, rt_value, rt_revision )
	 * TODO: move to a more appropriate place, maybe in core.
	 * @author Purodha
	 */
	public static function findLastRevisionByTag( $pageId, $rtt_name='fuzzy', $set=false ) {
		$fname = __METHOD__ . ' (' . get_class( $this ) . ')';
		$dbr = wfGetDB( DB_SLAVE );
		$id = $dbr->selectField( 'revtag_type', 'rtt_id', array( 'rtt_name' => $rtt_name ), $fname );
		$tables = array( 'revision' , 'revtag' );
		$fields = array( 'rev_id', 'rev_timestamp', 'rt_value', 'rt_revision' );
		$conds  = array(
			'rev_page' => $pageId,
		);
		if ( $set )
		{
			$conds[ 'rt_page' ] = $pageId;
			$conds[ 'rt_type' ] = $id;
		}
		else
		{
			$conds[] = 'rt_page IS NULL';
			$conds[] = 'rt_type IS NULL';
		}
		$join_conds [ 'revtag' ] = array('LEFT JOIN', 'rev_id = rt_revision');
		$options['ORDER BY'] = 'rev_timestamp DESC';
		$options['LIMIT'] = 1;
		$res = $dbr->selectRow( $tables, $fields, $conds, $fname, $options, $join_conds );
		return $res;
	}

	public static function isMessageNamespace( Title $title ) {
		global $wgTranslateMessageNamespaces;;
		$namespace = $title->getNamespace();
		return in_array( $namespace, $wgTranslateMessageNamespaces, true);
	}

	public static function tabs( $skin, &$tabs ) {
		if ( !self::isMessageNamespace( $skin->mTitle ) ) return true;

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
			$minor, $_, $_, $flags, $revision ) {

		$title = $article->getTitle();

		if ( !self::isMessageNamespace( $title ) ) return true;

		list( $key, $code, $group ) = self::getKeyCodeGroup( $title );

		// Unknown message, do not handle
		if ( !$group || !$code ) return true;

		$groups = TranslateUtils::messageKeyToGroups( $title->getNamespace(), $key );
		$cache = new ArrayMemoryCache( 'groupstats' );
		foreach ( $groups as $g ) $cache->clear( $g, $code );

		// Check for explicit tag
		$fuzzy = self::hasFuzzyString( $text );

		// Check for problems, but only if not fuzzy already
		global $wgTranslateDocumentationLanguageCode;
		if ( $code !== $wgTranslateDocumentationLanguageCode ) {
			$checker = $group->getChecker();
			if ( $checker ) {
				$en = $group->getMessage( $key, 'en' );
				$message = new FatMessage( $key, $en );
				// Take the contents from edit field as a translation
				$message->setTranslation( $text );

				$checks = $checker->checkMessage( $message, $code );
				if ( count( $checks ) ) $fuzzy = true;
			}
		}

		// Update it
		if ( $revision === null ) {
			$rev = $article->getTitle()->getLatestRevId();
		} else {
			$rev = $revision->getID();
		}

		// Add the ready tag
		$dbw = wfGetDB( DB_MASTER );

		$id = $dbw->selectField( 'revtag_type', 'rtt_id', array( 'rtt_name' => 'fuzzy' ), __METHOD__ );

		$conds = array(
			'rt_page' => $article->getTitle()->getArticleId(),
			'rt_type' => $id,
			'rt_revision' => $rev
		);
		// Remove any existing fuzzy tags for this revision
		$dbw->delete( 'revtag', $conds, __METHOD__ );

		// Add the fuzzy tag if needed
		if ( $fuzzy !== false ) {
			$dbw->insert( 'revtag', $conds, __METHOD__ );
		}

		return true;
	}

}

