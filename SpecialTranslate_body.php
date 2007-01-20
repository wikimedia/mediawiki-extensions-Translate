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
	private $language    = '';

	private static $existence = null;

	function __construct() {
		SpecialPage::SpecialPage( 'Translate' );
		$this->includable( true );

		global $wgLang, $wgContLang;
		if( $wgLang->getCode() != $wgContLang->getCode() ) {
			$this->language = '/' . $wgLang->getCode();
		}

	}

	function execute() {
		global $wgOut, $wgUseDatabaseMessages;

		# The page isn't much use if the MediaWiki namespace is not being used
		if( !$wgUseDatabaseMessages ) {
			$wgOut->addWikiText( wfMsg( 'allmessagesnotsupportedDB' ) );
			return;
		}

		wfRunHooks( 'SpecialTranslateAddMessageClass',
			array( &$this->classes ) );

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
		/* bool */ 'changed'      => false,
		/* bool */ 'database'     => false,
		/* bool */ 'missing'      => false,
		/* bool */ 'extension'    => true,
		/* bool */ 'optional'     => false,
    /* bool */ 'ignored'      => false,
		/* str  */ 'sort'         => 'normal',
		/* bool */ 'endiff'       => false,
		/* str  */ 'uselang'      => $wgUser->getOption( 'language' ),
		/* str  */ 'msgclass'     => 'core',
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
			if ( $class->getId() === $this->options['msgclass'] ) {
				$this->messageClass = $class;
			}
		}

	}

	// TODO: fix endiff
	function initializeMessages() {
		global $wgMessageCache, $wgLang;

		# Make sure all extension messages are available
		MessageCache::loadAllMessages();

		$array = $this->messageClass->getArray();

		if ( $this->options['sort'] === 'alpha' ) {
			ksort( $array );
		}

		$wgMessageCache->disableTransform();

		foreach ( $array as $key => $value ) {
			$msg = wfMsg( $key );
			if ( wfEmptyMsg( $key, $msg ) ) {
				$msg = wfMsgNoDb( $key );
			}
			$this->messages[$key]['enmsg'] = $value; // the very original message
			$this->messages[$key]['statmsg'] = $this->options['endiff'] ? $value : wfMsgNoDb( $key ); // committed translation or fallback
			$this->messages[$key]['msg'] = $msg; // current translation
			$this->messages[$key]['extension'] = true; // overwritten by 'core'
			$this->messages[$key]['infile'] = null; // filled by message class
			$this->messages[$key]['infbfile'] = null; // filled by message class
			$this->messages[$key]['optional'] = false; // filled by message class
			$this->messages[$key]['ignored'] = false; // filled by message class
			$this->messages[$key]['changed'] = false; // filled later
			$this->messages[$key]['defined'] = false; // filled later
		}

		$wgMessageCache->enableTransform();

		$this->messageClass->fill($this->messages);
		
		// Calculate some usefull variables
		foreach ( $this->messages as $key => $value ) {
			$this->messages[$key]['changed'] = ( $value['msg'] !== $value['statmsg'] );
			$this->messages[$key]['defined'] = ( $value['changed'] || $value['infile'] !== null );
		}

	}

	function output() {
		global $wgOut;

		if ( $this->output ) {
			$input = htmlspecialchars($this->messageClass->export($this->messages));
			$wgOut->addHTML( '<textarea id="wpTextbox1" rows="40">' . $input . '</textarea>');
		} else {
			$wgOut->addHTML( $this->settingsForm() );
			$wgOut->addHTML( $this->makeHTMLText( $this->messages, $this->options, $this->language ) );
		}

	}

	function settingsForm() {
		$form = "\n\n" . Xml::openElement('form');
		$form .= $this->prioritySelector() . wfElement('br');
		$form .= $this->messageClassSelector() . " ";
		$form .= $this->sortSelector() . " ";
		if ( isset( $this->nondefaults['uselang'] ) ) {
			$form .= Xml::hidden( 'uselang', $this->nondefaults['uselang'] );
		}
		$form .= Xml::submitButton( wfMsg( 'translate-fetch-button') );
		$form .= Xml::submitButton( wfMsg( 'translate-export-button' ), array( 'name' => 'ot'));
		$form .= Xml::closeElement('form'). "\n\n";
		return $form;
	}

	function prioritySelector() {
		$str = wfMsgHtml( 'translate-show-label' ) . ' ' . '<table>' .
		'<tr><td>' .
 			Xml::checkLabel('Extension', 'extension',
			'msgp-extension', $this->options['extension'], array( 'disabled' => 'disabled') ) .
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

	function sortSelector() {
		$str = wfMsgHtml('translate-sort-label') . " " .
			Xml::openElement('select', array( 'name' => 'sort' )) .
			Xml::option( wfMsg( 'translate-sort-normal' ), 'normal', $this->options['sort'] === 'normal') .
			Xml::option( wfMsg( 'translate-sort-alpha' ), 'alpha', $this->options['sort'] === 'alpha') .
			"</select>";
		return $str;
	}

	function messageClassSelector() {
		$str = wfMsgHtml( 'translate-messageclass' ) . ' ' .
			Xml::openElement('select', array( 'name' => 'msgclass' ));
		foreach( $this->classes as $class) {
			$str.= Xml::option( $class->getLabel(), $class->getId(),
				$this->options['msgclass'] === $class->getId());
		}
		$str .= "</select>";
		return $str;
	}

	static function getExistence() {
		if (self::$existence === null) {
			self::$existence = self::doExistenceCheck();
		}
		return self::$existence;
	}

	static function pageExists($page, $talk = false) {
		global $wgContLang;
		if (self::$existence === null) {
			self::$existence = self::doExistenceCheck();
		}
		$title = $wgContLang->ucfirst( $key ) . $this->language;
		return isset( self::$existence[!$talk ? NS_MEDIAWIKI : NS_MEDIAWIKI_TALK][$page] );
	}


	static private function doExistenceCheck() {
		wfProfileIn( __METHOD__ );
		# This is a nasty hack to avoid doing independent existence checks
		# without sending the links and table through the slow wiki parser.
		$pageExists = array(
			NS_MEDIAWIKI => array(),
			NS_MEDIAWIKI_TALK => array()
		);
		$dbr =& wfGetDB( DB_SLAVE );
		$page = $dbr->tableName( 'page' );
		$sql = "SELECT page_namespace,page_title FROM $page WHERE page_namespace IN (" .
			NS_MEDIAWIKI . ", " . NS_MEDIAWIKI_TALK . ")";
		$res = $dbr->query( $sql );
		while( $s = $dbr->fetchObject( $res ) ) {
			$pageExists[$s->page_namespace][$s->page_title] = true;
		}
		$dbr->freeResult( $res );
		wfProfileOut( __METHOD__ );
		return $pageExists;
	}


	static private function tableHeader() {
		$tableheader = wfElement( 'table', array(
			'class'   => 'mw-special-translate-table',
			'border'  => '1',
			'cellspacing' => '0'),
			null
		);
	
		$tableheader .= wfOpenElement('tr');
		$tableheader .= wfElement('th',
			array( 'rowspan' => '2'),
			wfMsgHtml('allmessagesname')
		);
		$tableheader .= wfElement('th', null, wfMsgHtml('allmessagesdefault') );
		$tableheader .= wfCloseElement('tr');
	
		$tableheader .= wfOpenElement('tr');
		$tableheader .= wfElement('th', null, wfMsgHtml('allmessagescurrent') );
		$tableheader .= wfCloseElement('tr');

		return $tableheader;
	}

	/**
	 * Create a list of messages, formatted in HTML as a list of messages and values and showing differences between the default language file message and the message in MediaWiki: namespace.
	 * @param $messages Messages array.
	 * @return The HTML list of messages.
	 */
	static function makeHTMLText( $messages, $options, $language ) {
		global $wgLang, $wgContLang, $wgUser;
		wfProfileIn( __METHOD__ );
	
		$sk = $wgUser->getSkin();
		$talk = $wgLang->getNsText( NS_TALK );

		$tableheader = self::tableHeader();
		$tablefooter = wfCloseElement( 'table' );	
	
		$pageExists = SpecialTranslate::getExistence();
		
		wfProfileIn( __METHOD__ . '-output' );
	
		$i = 0;
		$open = false;
		$output =  '';

		foreach( $messages as $key => $m ) {
	
			$title = $wgContLang->ucfirst( $key ) . $language;
	
			$titleObj = Title::makeTitle( NS_MEDIAWIKI, $title );
			$talkPage = Title::makeTitle( NS_MEDIAWIKI_TALK, $title );
	
			$changed = ($m['statmsg'] != $m['msg']);
			$defined = ($m['infile'] !== NULL || $changed);
	
			if( $defined && $options['missing'] ) { continue; }
			if( !$changed && $options['changed'] ) { continue; }
			if( $m['optional'] && !$options['optional'] ) { continue; }
			if( $m['ignored'] && !$options['ignored'] ) { continue; }
			$original = $m['statmsg'];
			$message = $m['msg'];
	
			if( isset( $pageExists[NS_MEDIAWIKI][$title] ) ) {
				$pageLink = $sk->makeKnownLinkObj( $titleObj, htmlspecialchars( $key ) );
			} else {
				if ( $options['database'] ) { continue; }
				$pageLink = $sk->makeBrokenLinkObj( $titleObj, htmlspecialchars( $key ) );
			}
			if( isset( $pageExists[NS_MEDIAWIKI_TALK][$title] ) ) {
				$talkLink = $sk->makeKnownLinkObj( $talkPage, htmlspecialchars( $talk ) );
			} else {
				$talkLink = $sk->makeBrokenLinkObj( $talkPage, htmlspecialchars( $talk ) );
			}

			$editLink = $sk->makeKnownLinkObj( $titleObj, wfMsgHtml('edit'), 'action=edit' );
			$historyLink = $sk->makeKnownLinkObj( $titleObj, wfMsgHtml('history'), 'action=history' );
			
			$anchor = 'msg_' . htmlspecialchars( strtolower( $title ) );
			$anchor = wfElement( 'a', array( 'name' => $anchor ) );
	
			if( $i % 3000 === 0 ) {
				if ( $open ) {
					$output .= $tablefooter;
					$open = true;
				}
				$output .= $tableheader;
			}

			$opt = '';
			if ($m['optional']) $opt .= ' opt';
			if ($m['ignored']) $opt .= ' ign';
			if ($m['extension'] && !$m['ignored'] && !$m['optional']) $opt .= ' dco';
	
			if($changed) {
				$info = wfOpenElement( 'tr', array( 'class' => "orig$opt") );
				$info .= wfOpenElement( 'td', array( 'rowspan' => '2') );
				$info .= "$anchor$pageLink<br />$talkLink | $editLink | $historyLink";
				$info .= wfCloseElement( 'td' );
				$info .= wfElement( 'td', null, $original );
				$info .= wfCloseElement( 'tr' );
	
				$info .= wfOpenElement( 'tr', array( 'class' => 'new') );
				$info .= wfElement( 'td', null, $message );
				$info .= wfCloseElement( 'tr' );
	
				$output .= $info . "\n";
			} else {
				$info = wfOpenElement( 'tr', array( 'class' => "def$opt") );
				$info .= wfOpenElement( 'td' );
				$info .= "$anchor$pageLink<br />$talkLink | $editLink | $historyLink";
				$info .= wfCloseElement( 'td' );
				$info .= wfElement( 'td', null, $message );
				$info .= wfCloseElement( 'tr' );
	
				$output .= $info . "\n";
			}
	
			$i++;
	
		}
	
		$output .= $tablefooter;
	
		wfProfileOut( __METHOD__ . '-output' );
		wfProfileOut( __METHOD__ );
		return $output;
	}

}

abstract class MessageClass {

	protected $label = 'none';
	protected $id    = 'none';
	function getLabel() { return $this->label; }
	function getId() { return $this->id; }
	abstract function export(&$array);
	abstract function getArray();
	function fill(&$array) {}

	function exportLine($key, $m, $pad = false) {
		if ( $m['ignored'] ) { return ''; }
		$comment = '';
		$fallback = ( $m['infbfile'] === null ) ? $m['enmsg'] : $m['infbfile'];

		if ( $m['optional'] ) {
			if ( $m['msg'] !== $fallback ) {
				$comment = "#optional";
			} else {
				return '';
			}
		}

		if ( $m['msg'] === $fallback ) {
			return "\n";
		}

		$key = "'$key'";
		if ($pad) while ( strlen($key) < $pad ) { $key .= ' '; }
		$txt .= "$key=> '" . preg_replace( "/(?<!\\\\)'/", "\'", $m['msg']) . "',$comment\n";
		return $txt;
	}

}

class CoreMessageClass extends MessageClass {
	protected $label = 'Core system messages';
	protected $id    = 'core';
	function export(&$array) {
		$txt = "\$messages = array(\n";
		foreach( $array as $key => $m ) {
			$txt .= $this->exportLine($key, $m, 24);
		}
		$txt .= ");";
		return $txt;
	}

	function getArray() {
		return Language::getMessagesFor('en');
	}

	function fill(&$array) {
		global $wgLang;
		$l = new languages();

		foreach ($l->getOptionalMessages() as $optMsg) {
			$array[$optMsg]['optional'] = true;
		}

		foreach ($l->getIgnoredMessages() as $optMsg) {
			$array[$optMsg]['ignored'] = true;
		}

		$lp = new LangProxy();

		$infile = $lp->getMessagesInFile( $wgLang->getCode() );
		$infbfile = null;
		if ( $wgLang->getFallbackLanguageCode() ) {
			$infbfile = $lp->getMessagesInFile( $wgLang->getFallbackLanguageCode() );
		}

		foreach ( $array as $key => $value ) {
			$array[$key]['extension'] = false;
			$array[$key]['infile'] = isset( $infile[$key] ) ? $infile[$key] : null;
			$array[$key]['infbfile'] = isset( $infbfile[$key] ) ? $infbfile[$key] : null;
		}


	}
}



global $wgHooks;
$wgHooks['SpecialTranslateAddMessageClass'][] = 'wfSpecialTranslateAddMessageClasses';
function wfSpecialTranslateAddMessageClasses($class) {
	$class[] = new CoreMessageClass();
	return true;
}

require_once( 'SpecialTranslate_exts.php' );


?>
