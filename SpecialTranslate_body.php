<?php

class SpecialTranslate extends SpecialPage {
	const OUTPUT_DEFAULT = 1;
	const OUTPUT_TEXTAREA = 2;
	private $defaults    = array();
	private $nondefaults = array();
	private $options     = array();
	private $output      = false;
	private $outputType  = self::OUTPUT_DEFAULT;
	private $messages    = array();
	private $messageClass= null;
	private $classes     = array();
	private $language    = '';

	private static $existence = null;

	function __construct() {
		SpecialPage::SpecialPage( 'Translate' );
		$this->includable( true );

		wfRunHooks( 'SpecialTranslateAddMessageClass',
			array( &$this->classes ) );

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

		$this->outputType = self::OUTPUT_TEXTAREA;

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

		wfAppendToArrayIfNotDefault( 'changed',
			$wgRequest->getBool( 'changed', $defaults['changed'] ),
			$defaults, $nondefaults);
		wfAppendToArrayIfNotDefault( 'database',
			$wgRequest->getBool( 'database', $defaults['database'] ),
			$defaults, $nondefaults);
		wfAppendToArrayIfNotDefault( 'missing',
			$wgRequest->getBool( 'missing', $defaults['missing'] ),
			$defaults, $nondefaults);
		wfAppendToArrayIfNotDefault( 'extension',
			$wgRequest->getBool( 'extension', $defaults['extension'] ),
			$defaults, $nondefaults);
		wfAppendToArrayIfNotDefault( 'optional',
			$wgRequest->getBool( 'optional', $defaults['optional'] ),
			$defaults, $nondefaults);
		wfAppendToArrayIfNotDefault( 'ignored',
			$wgRequest->getBool( 'ignored', $defaults['ignored'] ),
			$defaults, $nondefaults);
		wfAppendToArrayIfNotDefault( 'sort',
			$wgRequest->getText( 'sort', $defaults['sort'] ),
			$defaults, $nondefaults);
		wfAppendToArrayIfNotDefault( 'endiff',
			$wgRequest->getBool( 'endiff', $defaults['endiff'] ),
			$defaults, $nondefaults);
		wfAppendToArrayIfNotDefault( 'uselang',
			$wgRequest->getText( 'uselang', $defaults['uselang'] ),
			$defaults, $nondefaults);
		wfAppendToArrayIfNotDefault( 'msgclass',
			$wgRequest->getText( 'msgclass', $defaults['msgclass'] ),
			$defaults, $nondefaults);


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

	function initializeMessages() {
		global $wgMessageCache, $wgLang;

		# Make sure all extension messages are available
		MessageCache::loadAllMessages();

		$filtered = $this->getFilteredMessages();
		$infile = $wgLang->getUnmergedMessagesFor($wgLang->getCode());
		$infbfile = null;
		if ( $wgLang->getFallbackLanguage() ) {
			$infbfile = $wgLang->getUnmergedMessagesFor($wgLang->getFallbackLanguage());
		}

		$array = array_merge( Language::getMessagesFor( 'en' ), $wgMessageCache->getExtensionMessagesFor( 'en' ) );
		if ( $this->options['sort'] === 'alpha' ) {
			ksort( $array );
		}

		$wgMessageCache->disableTransform();

		foreach ( $array as $key => $value ) {
			$this->messages[$key]['enmsg'] = $value;
			$this->messages[$key]['filtered'] = isset($filtered[$key]) ? false : true;
			$this->messages[$key]['statmsg'] = $this->options['endiff'] ? $value : wfMsgNoDb( $key );
			$this->messages[$key]['msg'] = wfMsg ( $key );
			$this->messages[$key]['infile'] = @$infile[$key];
			$this->messages[$key]['infbfile'] = @$infbfile[$key];
			$this->messages[$key]['optional'] = false;
			$this->messages[$key]['ignored'] = false;
		}

		$wgMessageCache->enableTransform();

		$this->messageClass->filter($this->messages);
		
		// Prefill some usefull variables
		foreach ( $this->messages as $key => $value ) {
			$this->messages[$key]['changed'] = ( $value['msg'] !== $value['statmsg'] );
			$this->messages[$key]['defined'] = ( $value['changed'] || $value['infile'] !== null );
		}

	}

	function output() {
		global $wgOut;

		$navText = wfMsg( 'allmessagestext' );

		if ( $this->output ) {
			$input = htmlspecialchars($this->messageClass->export($this->messages));
			if ( $this->outputType == self::OUTPUT_DEFAULT) {
				$wgOut->addHTML( '<pre id="wpTextbox1">' . $input . '</pre>');
			} else {
				$wgOut->addHTML( '<textarea id="wpTextbox1" rows="40">' . $input . '</textarea>');
			}
		} else {
			$wgOut->addHTML( $this->settingsForm() );
			$wgOut->addWikiText( $navText );
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

	function getFilteredMessages() {
		global $wgLang, $wgIgnoredMessages;
		$arr = $wgLang->getAllMessages();
		foreach ($arr as $key => $string) {
			if (in_array($key, $wgIgnoredMessages, true)) {
				unset($arr[$key]);
			}
		}
		return $arr;
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
		$sql = "SELECT page_namespace,page_title FROM $page WHERE page_namespace IN (" . NS_MEDIAWIKI . ", " . NS_MEDIAWIKI_TALK . ")";
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
			if ( !$options['extension'] && ( $m['filtered'] && !$m['optional'] && !$m['ignored'] ) ) { continue; }
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
			if ($m['filtered'] && !$m['ignored']) $opt .= ' dco';
	
			if($changed) {
				$info = wfOpenElement( 'tr', array( 'class' => "orig$opt") );
				$info .= wfOpenElement( 'td', array( 'rowspan' => '2') );
				$info .= "$anchor$pageLink<br />$talkLink";
				$info .= wfCloseElement( 'td' );
				$info .= wfElement( 'td', null, $original );
				$info .= wfCloseElement( 'tr' );
	
				$info .= wfOpenElement( 'tr', array( 'class' => 'new') );
				$info .= wfElement( 'td', null, $message );
				$info .= wfCloseElement( 'tr' );
	
				$output .= $info;
			} else {
				$info = wfOpenElement( 'tr', array( 'class' => "def$opt") );
				$info .= wfOpenElement( 'td' );
				$info .= "$anchor$pageLink<br />$talkLink";
				$info .= wfCloseElement( 'td' );
				$info .= wfElement( 'td', null, $message );
				$info .= wfCloseElement( 'tr' );
	
				$output .= $info;
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
	abstract function filter(&$array);

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

	function filter(&$array) {
		$msgs = Language::getMessagesFor('en');
		foreach( $array as $key => $msg) {
			if ( !isset( $msgs[$key] ) ) {
				unset( $array[$key] );
			}
		}

		global $wgOptionalMessages;
		foreach ($wgOptionalMessages as $optMsg) {
			$array[$optMsg]['optional'] = true;
		}

		global $wgIgnoredMessages;
		foreach ($wgIgnoredMessages as $optMsg) {
			$array[$optMsg]['ignored'] = true;
		}

	}
}

class RenameUserMessageClass extends MessageClass {

	protected $label = 'Extension: Rename user';
	protected $id    = 'ext-renameuser';
		
	function export(&$array) {
		global $wgLang;
		$code = $wgLang->getCode();
		$txt = "\$wgRenameuserMessages['$code'] = array(\n";

		$g1 = array( 'renameuser', 'renameuserold', 'renameusernew', 'renameusersubmit' );
		$g2 = array( 'renameusererrordoesnotexist', 'renameusererrorexists', 'renameusererrorinvalid', 'renameusererrortoomany', 'renameusersuccess' );
		$g3 = array( 'renameuserlogpage', 'renameuserlogpagetext', 'renameuserlog' );

		foreach ($g1 as $msg) {
			$txt .= "\t" . $this->exportLine($msg, $array[$msg], 19);
		}
		$txt .= "\n";
		foreach ($g2 as $msg) {
			$txt .= "\t" . $this->exportLine($msg, $array[$msg], 30);
		}
		$txt .= "\n";
		foreach ($g3 as $msg) {
			$txt .= "\t" . $this->exportLine($msg, $array[$msg], 24);
		}

		$txt .= ");";
		return $txt;
	}

	function filter(&$array) {
		global $wgRenameuserMessages;
		$msgs = $wgRenameuserMessages['en'];
		foreach( $array as $key => $msg) {
			if ( !isset( $msgs[$key] ) ) {
				unset( $array[$key] );
			}
		}

		$array['renameuserlogentry']['ignored'] = true;
	}
}

class TranslateMessageClass extends MessageClass {

	protected $label = 'Extension: Translate';
	protected $id    = 'ext-translate';
		
	function export(&$array) {
		global $wgLang;
		global $wgTranslateMessages;
		$code = $wgLang->getCode();
		$txt = "\$wgTranslateMessages['$code'] = array(\n";

		foreach ($wgTranslateMessages['en'] as $key => $msg) {
			$txt .= "\t" . $this->exportLine($key, $array[$key]);
		}
		$txt .= ");";
		return $txt;
	}

	function filter(&$array) {
		global $wgTranslateMessages;
		$msgs = $wgTranslateMessages['en'];
		foreach( $array as $key => $msg) {
			if ( !isset( $msgs[$key] ) ) {
				unset( $array[$key] );
			}
		}
	}
}

global $wgHooks;
$wgHooks['SpecialTranslateAddMessageClass'][] = 'wfSpecialTranslateAddMessageClasses';
function wfSpecialTranslateAddMessageClasses($class) {
	$class[] = new CoreMessageClass();
	$class[] = new RenameUserMessageClass();
	$class[] = new TranslateMessageClass();
}


?>
