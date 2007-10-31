<?php


class TranslateUtils {

	const MSG = "translate-";

	public static function databaseLanguageSuffix( $language ) {
		return '/' . $language;
	}

	public static function title( $key, $language ) {
		global $wgContLang;
		return $wgContLang->ucfirst( $key . self::databaseLanguageSuffix( $language ) );
	}

	static public function prettyCode( $code ) {
		return ucfirst(strtolower(str_replace('-', '_', $code)));
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

	public static function fillExistence( Array &$messages, $language ) {
		self::doExistenceQuery();
		foreach ( array_keys($messages) as $key ) {
			$messages[$key]['pageexists'] = isset( self::$pageExists[self::title( $key, $language )] );
			$messages[$key]['talkexists'] = isset( self::$talkExists[self::title( $key, $language )] );
		}
	}

	public static function fillContents( Array &$messages, $language ) {
		$titles = array();
		foreach ( array_keys($messages) as $key ) {
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

		foreach ( array_keys($messages) as $key ) {
			$title = self::title( $key, $language );
			if ( isset( self::$contents[$title] ) ) {
				$messages[$key]['database'] = self::$contents[$title];
				$messages[$key]['author'] = self::$editors[$title];
			}
		}
		
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

	public static  function tableHeader( $title = '' ) {
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

	public static function makeListing( $messages, $language, $group ) {
		global $wgUser;
		$sk = $wgUser->getSkin();
		wfLoadExtensionMessages( 'Translate' );

		static $uimsg = array();
		if ( !count( $uimsg ) ) {
			foreach ( array( 'talk', 'edit', 'history', 'optional', 'ignored' ) as $msg ) {
				$uimsg[$msg] = wfMsgHtml( self::MSG . $msg );
			}
		}

		$output =  '';

		foreach( $messages as $key => $m ) {

			$title = self::title( $key, $language );

			$page['object'] = Title::makeTitle( NS_MEDIAWIKI, $title );
			$talk['object'] = Title::makeTitle( NS_MEDIAWIKI_TALK, $title );

			$original = $m['definition'];
			$message = isset( $m['database'] ) ? $m['database'] : $m['infile'];
			if ( !$message ) { $message = $original; }

			if( $m['pageexists'] ) {
				$page['link'] = $sk->makeKnownLinkObj( $page['object'], htmlspecialchars( $key ) );
			} else {
				$page['link'] = $sk->makeBrokenLinkObj( $page['object'], htmlspecialchars( $key ) );
			}
			if( $m['talkexists'] ) {
				$talk['link'] = $sk->makeKnownLinkObj( $talk['object'], $uimsg['talk'] );
			} else {
				$talk['link'] = $sk->makeBrokenLinkObj( $talk['object'], $uimsg['talk'] );
			}

			$page['edit'] = $uimsg['edit'];
			if ( $wgUser->isAllowed( 'translate' ) ) {
				$page['edit'] = $sk->makeKnownLinkObj( $page['object'], $uimsg['edit'], "action=edit&loadgroup=$group" );
			}
			$page['history'] = $sk->makeKnownLinkObj( $page['object'], $uimsg['history'], 'action=history' );

			$anchor = 'msg_' . $key;
			$anchor = Xml::element( 'a', array( 'name' => $anchor, 'href' => "#$anchor" ), "↓" );

			$extra = '';
			if ( $m['optional'] ) $extra = $uimsg['optional'];
			if ( $m['ignored'] )  $extra = $uimsg['ignored'];

			$leftColumn = $anchor . ' ' . $page['link'] . ' ' . $extra . '<br />' .
				implode( ' | ', array( $talk['link'] , $page['edit'], $page['history'] ) );

			if ( $m['changed'] ) {
				$info = Xml::openElement( 'tr', array( 'class' => "orig") );
				$info .= Xml::openElement( 'td', array( 'rowspan' => '2') );
				$info .= $leftColumn;
				$info .= Xml::closeElement( 'td' );
				$info .= Xml::element( 'td', null, $original );
				$info .= Xml::closeElement( 'tr' );

				$info .= Xml::openElement( 'tr', array( 'class' => 'new') );
				$info .= Xml::element( 'td', null, $message );
				$info .= Xml::closeElement( 'tr' );

				$output .= $info . "\n";
			} else {
				$info = Xml::openElement( 'tr', array( 'class' => "def") );
				$info .= Xml::openElement( 'td' );
				$info .= $leftColumn;
				$info .= Xml::closeElement( 'td' );
				$info .= Xml::element( 'td', null, $message );
				$info .= Xml::closeElement( 'tr' );

				$output .= $info . "\n";
			}

		}

		return $output;
	}

	/* Some other helpers for ouput*/

	public static function selector( $name, $options ) {
		return Xml::tags( 'select', array( 'name' => $name ), $options );
	}

	public static function getLanguageName( $code, $native = false ) {
		if ( !$native && is_callable(array( 'LanguageNames', 'getNames' )) ) {
			$languages = LanguageNames::getNames( 'en',
				LanguageNames::FALLBACK_NORMAL,
				LanguageNames::LIST_MW_AND_CLDR
			);
		} else {
			$languages = Language::getLanguageNames( false );
		}
		return isset($languages[$code]) ? $languages[$code] : false;
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


}