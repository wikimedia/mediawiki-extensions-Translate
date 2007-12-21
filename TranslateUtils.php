<?php


class TranslateUtils {

	const MSG = 'translate-';

	/**
	 * Does quick normalisation of message name so that in can be looked from the
	 * database.
	 * @param $message Name of the message
	 * @param $code Language code in lower case and with dash as delimieter
	 * @return The normalised title as a string.
	 */
	public static function title( $message, $code ) {
		global $wgContLang;
		return $wgContLang->ucfirst( $message . '/' . strtolower( $code ) );
	}

	/**
	 * Initializes messages array.
	 */
	public static function initializeMessages( Array $definitions, $sortCallback = null ) {
		$messages = array();

		if ( is_callable( $sortCallback ) ) {
			call_user_func_array( $sortCallback, array( &$definitions ) );
		}

		foreach ( $definitions as $key => $value ) {
			$messages[$key]['definition'] = $value; // the very original message
			$messages[$key]['database']   = null; // current translation in db
			$messages[$key]['author']     = null; // Author of the latest revision
			$messages[$key]['infile']     = null; // current translation in file
			$messages[$key]['fallback']   = null; // current fallback
			$messages[$key]['optional'] = false;
			$messages[$key]['ignored']  = false;
			$messages[$key]['changed']  = false;
			$messages[$key]['pageexists'] = false;
			$messages[$key]['talkexists'] = false;
		}

		return $messages;
	}

	public static function fillExistence( MessageCollection $messages, $language ) {
		self::doExistenceQuery();
		foreach ( $messages->keys() as $key ) {
			$messages[$key]->pageExists = isset( self::$pageExists[self::title( $key, $language )] );
			$messages[$key]->talkExists = isset( self::$talkExists[self::title( $key, $language )] );
		}
	}

	public static function fillContents( MessageCollection $messages, $language ) {
		$titles = array();
		foreach ( $messages->keys() as $key ) {
			$titles[self::title( $key, $language )] = null;
		}
		// Determine for which messages are not fetched already
		$missing = array_diff( $titles, self::$contents );


		// Don't fetch pages that do not exists
		self::doExistenceQuery();
		foreach ( array_keys( $missing ) as $message ) {
			if ( !isset(self::$pageExists[$message] ) ) {
				unset( $missing[$message] );
			}
		}

		// Fetch contents for the rest
		if ( count( $missing ) ) {
			self::getContents( array_keys( $missing ) );
		}

		foreach ( $messages->keys() as $key ) {
			$title = self::title( $key, $language );
			if ( isset( self::$contents[$title] ) ) {
				$messages[$key]->database = self::$contents[$title];
				$messages[$key]->addAuthor( self::$editors[$title] );
			}
		}

		self::$contents = array();
		self::$editors = array();
	}

	public static function getMessageContent( $key, $language ) {
		wfProfileIn( __METHOD__ );
		$title = self::title( $key, $language );
		if ( !isset(self::$contents[$title]) ) {
			self::getContents( array( $title ) );
		}
		wfProfileOut( __METHOD__ );
		return isset(self::$contents[$title]) ? self::$contents[$title] : null;
	}


	private static $contents = array();
	private static $editors = array();
	private static function getContents( Array $titles ) {
		wfProfileIn( __METHOD__ );
		$dbr = wfGetDB( DB_SLAVE );
		$rows = $dbr->select( array( 'page', 'revision', 'text' ),
			array( 'page_title', 'old_text', 'old_flags', 'rev_user_text' ),
			array(
				'page_is_redirect'  => 0,
				'page_namespace'    => NS_MEDIAWIKI,
				'page_latest=rev_id',
				'rev_text_id=old_id',
				'page_title'        => $titles
			),
			__METHOD__
		);


		foreach ( $rows as $row ) {
			self::$contents[$row->page_title] = Revision::getRevisionText( $row );
			self::$editors[$row->page_title] = $row->rev_user_text;
		}

		$rows->free();
		wfProfileOut( __METHOD__ );
	}


	private static $pageExists = null;
	private static $talkExists = null;
	private static function doExistenceQuery() {
		wfProfileIn( __METHOD__ );
		if ( self::$pageExists !== null && self::$talkExists !== null ) {
			wfProfileOut( __METHOD__ );
			return;
		}

		$dbr = wfGetDB( DB_SLAVE );
		$rows = $dbr->select(
			'page',
			array( 'page_namespace', 'page_title' ),
			array( 'page_namespace' => array( NS_MEDIAWIKI, NS_MEDIAWIKI_TALK ) ),
			__METHOD__ 
		);

		foreach ( $rows as $row ) {
			if ( $row->page_namespace == NS_MEDIAWIKI ) {
				self::$pageExists[$row->page_title] = true;
			} else {
				self::$talkExists[$row->page_title] = true;
			}
		}
		$rows->free();
		wfProfileOut( __METHOD__ );
	}

	/* Table output helpers */

	public static function tableHeader( $title = '' ) {
		$tableheader = Xml::openElement( 'table', array(
			'class'   => 'mw-sp-translate-table',
			'border'  => '1',
			'cellspacing' => '0')
		);

		$tableheader .= Xml::openElement('tr');
		$tableheader .= Xml::element('th',
			array( 'rowspan' => '2'),
			$title ? $title : wfMsgHtml('allmessagesname')
		);
		$tableheader .= Xml::element('th', null, wfMsgHtml('allmessagesdefault') );
		$tableheader .= Xml::closeElement('tr');

		$tableheader .= Xml::openElement('tr');
		$tableheader .= Xml::element('th', null, wfMsgHtml('allmessagescurrent') );
		$tableheader .= Xml::closeElement('tr');

		return $tableheader;
	}

	public static function makeListing( MessageCollection $messages, $language, $group, $review = false ) {
		global $wgUser;
		$sk = $wgUser->getSkin();
		wfLoadExtensionMessages( 'Translate' );

		$uimsg = array();
		foreach ( array( 'talk', 'edit', 'history', 'optional', 'ignored', 'delete' ) as $msg ) {
			$uimsg[$msg] = wfMsgHtml( self::MSG . $msg );
		}

		$output =  '';

		foreach( $messages as $key => $m ) {

			$title = self::title( $key, $language );

			$page['object'] = Title::makeTitle( NS_MEDIAWIKI, $title );
			$talk['object'] = Title::makeTitle( NS_MEDIAWIKI_TALK, $title );

			$original = $m->definition;
			$message = $m->translation ? $m->translation : $original;

			if( $m->pageExists ) {
				$page['link'] = $sk->makeKnownLinkObj( $page['object'], htmlspecialchars( $key ) );
			} else {
				$page['link'] = $sk->makeBrokenLinkObj( $page['object'], htmlspecialchars( $key ) );
			}
			if( $m->talkExists ) {
				$talk['link'] = $sk->makeKnownLinkObj( $talk['object'], $uimsg['talk'] );
			} else {
				$talk['link'] = $sk->makeBrokenLinkObj( $talk['object'], $uimsg['talk'] );
			}

			$page['edit'] = $uimsg['edit'];
			if ( $wgUser->isAllowed( 'translate' ) ) {
				$page['edit'] = $sk->makeKnownLinkObj( $page['object'], $uimsg['edit'], "action=edit&loadgroup=$group" );
			}
			$page['history'] = $sk->makeKnownLinkObj( $page['object'], $uimsg['history'], 'action=history' );
			$page['delete'] = $sk->makeKnownLinkObj( $page['object'], $uimsg['delete'], 'action=delete' );

			$anchor = 'msg_' . $key;
			$anchor = Xml::element( 'a', array( 'name' => $anchor, 'href' => "#$anchor" ), "↓" );

			$extra = '';
			if ( $m->optional ) $extra = $uimsg['optional'];
			if ( $m->ignored )  $extra = $uimsg['ignored'];

			$leftColumn = $anchor . ' ' . $page['link'] . ' ' . $extra . '<br />' .
				implode( ' | ', array( $talk['link'] , $page['edit'], $page['history'], $page['delete'] ) );

			if ( $review ) {
				$output .= Xml::tags( 'tr', array( 'class' => 'orig' ),
					Xml::tags( 'td', array( 'rowspan' => '2'), $leftColumn ) .
					Xml::element( 'td', null, $original )
				);

				$output .= Xml::tags( 'tr', array( 'class' => 'new' ),
					Xml::element( 'td', null, $message ) .
					Xml::closeElement( 'tr' )
				);
			} else {
				$output .= Xml::tags( 'tr', array( 'class' => 'def' ),
					Xml::tags( 'td', null, $leftColumn ) .
					Xml::element( 'td', null, $message )
				);
			}

		}

		return $output;
	}

	/* Some other helpers for ouput*/

	public static function selector( $name, $options ) {
		return Xml::tags( 'select', array( 'name' => $name ), $options );
	}

	public static function simpleSelector( $name, $items, $selected ) {
		$options = array();
		foreach ( $items as $item ) {
			$item = strval( $item );
			$options[] = Xml::option( $item, $item, $item == $selected );
		}
		return self::selector( $name, implode( "\n", $options ) );
	}

	public static function getLanguageName( $code, $native = false, $language = 'en' ) {
		if ( !$native && is_callable(array( 'LanguageNames', 'getNames' )) ) {
			$languages = LanguageNames::getNames( $language ,
				LanguageNames::FALLBACK_NORMAL,
				LanguageNames::LIST_MW_AND_CLDR
			);
		} else {
			$languages = Language::getLanguageNames( false );
		}

		$parts = explode( '-', $code );
		$suffix = '';
		switch ( @$parts[1] ) {
			case 'latn':
				$suffix = ' (Latin)'; # TODO: i18n
				unset( $parts[1] );
				break;
			case 'cyrl':
				$suffix = ' (Cyrillic)'; # TODO: i18n
				unset( $parts[1] );
				break;
		}
		$code = implode( '-', $parts );
		return isset($languages[$code]) ? $languages[$code] . $suffix : false;
	}

	public static function languageSelector( $selectedId ) {
		global $wgLang;
		if ( is_callable(array( 'LanguageNames', 'getNames' )) ) {
			$languages = LanguageNames::getNames( $wgLang->getCode(),
				LanguageNames::FALLBACK_NORMAL,
				LanguageNames::LIST_MW_AND_CLDR
			);
		} else {
			$languages = Language::getLanguageNames( false );
		}
		
		ksort( $languages );

		$options = '';
		foreach( $languages as $code => $name ) {
			$selected = ($code === $selectedId);
			$options .= Xml::option( "$code - $name", $code, $selected ) . "\n";
		}

		return self::selector( 'language', $options );
	}

	public static function messageKeyToGroup( $key ) {
		$key = strtolower( $key );
		$index = self::messageIndex();
		return @$index[$key];
	}

	public static function messageIndex() {
		$keyToGroup = array();
		if ( file_exists(TRANSLATE_INDEXFILE) ) {
			$keyToGroup = unserialize( file_get_contents(TRANSLATE_INDEXFILE) );
		} else {
			wfDebug( __METHOD__ . ": Message index missing." );
		}

		return $keyToGroup;
	}
}