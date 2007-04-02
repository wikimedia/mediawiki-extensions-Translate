<?php

class SpecialTranslate extends SpecialPage {
	const OUTPUT_DEFAULT = 1;
	const OUTPUT_TEXTAREA = 2;
	private $defaults    = array();
	private $nondefaults = array();
	private $options     = array();
	private $output      = false;
	private $messages    = array();
	private $messageClass= null;
	private $classes     = array();

	private static $maxRowCount = 3000;

	function __construct() {
		SpecialPage::SpecialPage( 'Translate' );
		$this->includable( true );
	}

	function execute() {
		require_once( 'SpecialTranslate_exts.php' );
		$this->classes = efInitializeExtensionClasses( );

		$this->setup();
		$this->initializeMessages();
		$this->setHeaders();
		$this->output();
	}

	function setup() {
		global $wgUser, $wgRequest;

		if ( $wgRequest->getText( 'ot' ) !== '' ) {
			$this->output = true;
		}

		$defaults = array(
		/* bool */ 'x'            => false,
		/* bool */ 'changed'      => false,
		/* bool */ 'database'     => false,
		/* bool */ 'missing'      => false,
		/* bool */ 'extension'    => false,
		/* bool */ 'optional'     => false,
    /* bool */ 'ignored'      => false,
		/* str  */ 'sort'         => 'normal',
		/* bool */ 'endiff'       => false,
		/* str  */ 'uselang'      => $wgUser->getOption( 'language' ),
		/* str  */ 'msgclass'     => 'core',
		/* str  */ 'filter-key'   => '',
		/* str  */ 'filter-msg'   => '',
		);

		// Dump everything here
		$nondefaults = array();

		foreach ( $defaults as $v => $t ) {
			if ( is_bool($t) ) {
				$r = $wgRequest->getBool( $v, $defaults[$v] );
			} elseif( is_string($t) ) {
				$r = $wgRequest->getText( $v, $defaults[$v] );
			}
			wfAppendToArrayIfNotDefault( $v, $r, $defaults, $nondefaults);
		}

		$this->defaults    = $defaults;
		$this->nondefaults = $nondefaults;
		$this->options     = $nondefaults + $defaults;

		$this->messageClass = $this->classes[0];
		foreach( $this->classes as $class ) {
			if ( $class->hasMessages() && $class->getId() === $this->options['msgclass']) {
				if ( $this->options['msgclass'] !== 'core' ) {
					$this->options['extension'] = true;
				}
				$this->messageClass = $class;
				break;
			}
		}

	}

	function initializeMessages() {
		global $wgMessageCache, $wgContLang;

		// Don't print huge page on first load
		if ( !$this->options['x'] ) { return; }

		# Make sure all extension messages are available
		MessageCache::loadAllMessages();

		$array = $this->messageClass->getArray();

		if ( $this->options['sort'] === 'alpha' ) {
			ksort( $array );
		}

		$LinkBatch = new LinkBatch();
		$wgMessageCache->disableTransform();

		foreach ( $array as $key => $value ) {
			$msg = wfMsg( $key );
			if ( wfEmptyMsg( $key, $msg ) ) {
				$msg = wfMsgNoDb( $key );
			}

			$this->messages[$key]['enmsg'] = $value; // the very original message
			$this->messages[$key]['statmsg'] = wfMsgNoDb( $key ); // committed translation or fallback
			$this->messages[$key]['msg'] = $msg; // current translation
			$this->messages[$key]['extension'] = true; // overwritten by 'core'
			$this->messages[$key]['infile'] = null; // filled by message class
			$this->messages[$key]['infbfile'] = null; // filled by message class
			$this->messages[$key]['optional'] = false; // filled by message class
			$this->messages[$key]['ignored'] = false; // filled by message class
			$this->messages[$key]['changed'] = false; // filled later
			$this->messages[$key]['pageexists'] = false; // filled later
			$this->messages[$key]['talkexists'] = false; // filled later
			$this->messages[$key]['defined'] = false; // filled later

			$LinkBatch->add( NS_MEDIAWIKI, self::titleFromKey( $key ) );
			$LinkBatch->add( NS_MEDIAWIKI_TALK, self::titleFromKey( $key ) );
		}

		$wgMessageCache->enableTransform();

		if ( count($this->messages) > 50 ) {
			$exists = self::doExistenceCheck();
		} else {
			$exists = $LinkBatch->execute();
		}

		$this->messageClass->fill($this->messages);

		$pagePrefix = $wgContLang->getNsText( NS_MEDIAWIKI ) . ':';
		$talkPrefix = $wgContLang->getNsText( NS_MEDIAWIKI_TALK ) . ':';
		// Calculate some usefull variables
		foreach ( array_keys( $this->messages ) as $key ) {
			$title = self::titleFromKey( $key );
			$pageExists = isset( $exists[$pagePrefix . $title] ) &&
				$exists[$pagePrefix . $title];

			$talkExists = isset( $exists[$talkPrefix . $title] ) &&
				$exists[$talkPrefix . $title];

			if ( $this->options['endiff'] ) {
				$this->messages[$key]['statmsg'] = $this->messages[$key]['enmsg'];
			}

			$this->messages[$key]['changed'] = ( $this->messages[$key]['msg'] !== $this->messages[$key]['statmsg'] );
			$this->messages[$key]['pageexists'] = $pageExists;
			$this->messages[$key]['talkexists'] = $talkExists;
			$this->messages[$key]['defined'] = ( $pageExists || $this->messages[$key]['infile'] !== null );

		}

	}


	function output() {
		global $wgOut;

		if ( $this->output ) {
			$wgOut->addHTML( Xml::element( 'textarea',
				array( 'id' => 'wpTextbox1', 'rows' => '40' ),
				$this->messageClass->export($this->messages) )
			);
		} else {
			if ( !$this->options['x'] ) {
				$wgOut->addHTML( wfMsg( 'translate-choose-settings' ) );
			}

			$wgOut->addHTML( $this->settingsForm() );
			$wgOut->addHTML( $this->makeHTMLText( $this->messages, $this->options ) );
		}

	}

	protected function settingsForm() {
		$form = "\n\n" . Xml::openElement('form');
		$form .= Xml::hidden( 'x', '1' );
		$form .= $this->prioritySelector() . Xml::element('br');
		$form .= $this->messageClassSelector() . " ";
		$form .= $this->sortSelector() . " ";
		$form .= $this->languageSelector() . " ";
		$form .= Xml::submitButton( wfMsg( 'translate-fetch-button') );
		$form .= Xml::submitButton( wfMsg( 'translate-export-button' ), array( 'name' => 'ot'));
		$form .= Xml::closeElement('form'). "\n\n";
		return $form;
	}

	protected function filterInputs() {
		return
			Xml::inputLabel( "Key filter:", 'filter-key', 'mw-sp-filter-key' ) . ' ' .
			Xml::inputLabel( "Messages filter:", 'filter-msg', 'mw-sp-filter-msg' );
	}

	protected function prioritySelector() {
		$str = wfMsgHtml( 'translate-show-label' ) . ' ' . '<table>' .
		'<tr><td>' .
 			Xml::checkLabel( wfMsg( 'translate-opt-review' ), 'endiff',
			'msgp-endiff', $this->options['endiff']) .
		'</td><td>' .
			Xml::checkLabel( wfMsg( 'translate-opt-trans' ), 'missing',
				'msgs-translated', $this->options['missing']) .
		'</td></tr><tr><td>' .
			Xml::checkLabel( wfMsg( 'translate-opt-optional' ), 'optional',
				'msgp-optional', $this->options['optional']) .
		'</td><td>' .
			Xml::checkLabel( wfMsg( 'translate-opt-changed' ), 'changed',
				'msgs-changed', $this->options['changed']) .
		'</td></tr><tr><td>' .
			Xml::checkLabel( wfMsg( 'translate-opt-ignored' ), 'ignored',
				'msgp-ignored', $this->options['ignored']) .
		'</td><td>' .
			Xml::checkLabel( wfMsg( 'translate-opt-database' ), 'database',
				'msgs-database', $this->options['database']) .
		'</td></tr></table>';
		return $str;
	}

	protected function sortSelector() {
		$str = wfMsgHtml('translate-sort-label') . " " .
			Xml::openElement('select', array( 'name' => 'sort' )) .
			Xml::option( wfMsg( 'translate-sort-normal' ), 'normal', $this->options['sort'] === 'normal') .
			Xml::option( wfMsg( 'translate-sort-alpha' ), 'alpha', $this->options['sort'] === 'alpha') .
			"</select>";
		return $str;
	}

	protected function languageSelector() {
		global $wgLang;
		$languages = Language::getLanguageNames( false );
		ksort( $languages );

		$options = '';
		$language = $wgLang->getCode();
		foreach( $languages as $code => $name ) {
			$selected = ($code == $language);
			$options .= Xml::option( "$code - $name", $code, $selected ) . "\n";
		}
		$str = wfMsg( 'translate-language' ) . ': <select name="uselang" class="mw-language-selector">' . $options . '</select>';
		return $str;
	}

	protected function messageClassSelector() {
		$str = wfMsgHtml( 'translate-messageclass' ) . ' ' .
			Xml::openElement('select', array( 'name' => 'msgclass' ));
		foreach( $this->classes as $class) {
			if ( !$class->hasMessages() ) { continue; }
			$str.= Xml::option( $class->getLabel(), $class->getId(),
				$this->options['msgclass'] === $class->getId());
		}
		$str .= "</select>";
		return $str;
	}

	static private function tableHeader() {
		$tableheader = Xml::element( 'table', array(
			'class'   => 'mw-special-translate-table',
			'border'  => '1',
			'cellspacing' => '0'),
			null
		);

		$tableheader .= Xml::openElement('tr');
		$tableheader .= Xml::element('th',
			array( 'rowspan' => '2'),
			wfMsgHtml('allmessagesname')
		);
		$tableheader .= Xml::element('th', null, wfMsgHtml('allmessagesdefault') );
		$tableheader .= Xml::closeElement('tr');

		$tableheader .= Xml::openElement('tr');
		$tableheader .= Xml::element('th', null, wfMsgHtml('allmessagescurrent') );
		$tableheader .= Xml::closeElement('tr');

		return $tableheader;
	}

	/**
	 * Create a list of messages, formatted in HTML as a list of messages and values and showing differences between the default language file message and the message in MediaWiki: namespace.
	 * @param $messages Messages array.
	 * @return The HTML list of messages.
	 */
	static function makeHTMLText( $messages, $options ) {
		global $wgLang, $wgContLang, $wgUser;
		wfProfileIn( __METHOD__ );

		$sk = $wgUser->getSkin();
		$talkLinkText = $wgLang->getNsText( NS_TALK ); // FIXME

		$language = STools::getLanguage();

		$tableheader = self::tableHeader();
		$tablefooter = Xml::closeElement( 'table' );

		$i = 0;
		$open = false;
		$output =  '';

		foreach( $messages as $key => $m ) {

			$title = self::titleFromKey( $key );
			$page['object'] = Title::makeTitle( NS_MEDIAWIKI, $title );
			$talk['object'] = Title::makeTitle( NS_MEDIAWIKI_TALK, $title );

			if ( $options['missing']  && $m['defined'] )     { continue; }
			if ( $options['changed']  && !$m['changed'] )    { continue; }
			if (!$options['optional'] && $m['optional'] )    { continue; }
			if (!$options['ignored']  && $m['ignored'] )     { continue; }
			if ( $options['database'] && !$m['pageexists'] ) { continue; }

			$original = $m['statmsg'];
			$message = $m['msg'];

			if( $m['pageexists'] ) {
				$page['link'] = $sk->makeKnownLinkObj( $page['object'], htmlspecialchars( $key ) );
			} else {
				$page['link'] = $sk->makeBrokenLinkObj( $page['object'], htmlspecialchars( $key ) );
			}
			if( $m['talkexists'] ) {
				$talk['link'] = $sk->makeKnownLinkObj( $talk['object'], $talkLinkText );
			} else {
				$talk['link'] = $sk->makeBrokenLinkObj( $talk['object'], $talkLinkText );
			}

			$page['edit'] = $sk->makeKnownLinkObj( $page['object'], wfMsgHtml('edit'), 'action=edit' );
			$page['history'] = $sk->makeKnownLinkObj( $page['object'], wfMsgHtml('history'), 'action=history' );

			$anchor = 'msg_' . $key;
			$anchor = Xml::element( 'a', array( 'name' => $anchor ) );

			if( $i % self::$maxRowCount === 0 ) {
				if ( $open ) {
					$output .= $tablefooter;
					$open = true;
				}
				$output .= $tableheader;
			}

			$opt = '';
			if ( $m['optional'] )  $opt .= ' opt';
			if ( $m['ignored'] )   $opt .= ' ign';

			$leftColumn = $anchor . $page['link'] . '<br />' .
				implode( ' | ', array( $talk['link'] , $page['edit'], $page['history'] ) );

			if ( $m['changed'] ) {
				$info = Xml::openElement( 'tr', array( 'class' => "orig$opt") );
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
				$info = Xml::openElement( 'tr', array( 'class' => "def$opt") );
				$info .= Xml::openElement( 'td' );
				$info .= $leftColumn;
				$info .= Xml::closeElement( 'td' );
				$info .= Xml::element( 'td', null, $message );
				$info .= Xml::closeElement( 'tr' );

				$output .= $info . "\n";
			}

			$i++;
		}

		$output .= $tablefooter;

		wfProfileOut( __METHOD__ );
		return $output;
	}

	static function titleFromKey( $key ) {
		global $wgContLang;
		$title = $wgContLang->ucfirst( $key ) . STools::getLanguage();
		return $title;
	}

	static function doExistenceCheck() {
		global $wgContLang;
		wfProfileIn( __METHOD__ );
		# This is a nasty hack to avoid doing independent existence checks
		# without sending the links and table through the slow wiki parser.
		$pageExists = array(
			NS_MEDIAWIKI => array(),
			NS_MEDIAWIKI_TALK => array()
		);
		$dbr = wfGetDB( DB_SLAVE );
		$page = $dbr->tableName( 'page' );
		$sql = "SELECT page_namespace,page_title FROM $page WHERE page_namespace IN (" . NS_MEDIAWIKI . ", " . NS_MEDIAWIKI_TALK . ")";
		$res = $dbr->query( $sql );

		$pagePrefix = $wgContLang->getNsText( NS_MEDIAWIKI ) . ':';
		$talkPrefix = $wgContLang->getNsText( NS_MEDIAWIKI_TALK ) . ':';

		while( $s = $dbr->fetchObject( $res ) ) {
			if ( $s->page_namespace == NS_MEDIAWIKI ) {
				$pageExists[$pagePrefix . $s->page_title] = true;
			} else {
				$pageExists[$talkPrefix . $s->page_title] = true;
			}
		}
		$dbr->freeResult( $res );

		wfProfileOut( __METHOD__ );
		return $pageExists;
	}

}

?>
