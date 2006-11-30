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

	function __construct() {
		SpecialPage::SpecialPage( 'Translate' );
		$this->includable( true );
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
		$this->output();

	}

	function setup() {
		global $wgUser, $wgRequest;

		if ( $wgRequest->getText( 'ot' ) == 'msg' ) {
			$this->output = true;
		}

		if ( $wgRequest->getBool( 'box' ) ) {
			$this->outputType = self::OUTPUT_TEXTAREA;
		}

		$defaults = array(
		/* bool */ 'changed'      => false,
		/* bool */ 'database'     => false,
		/* bool */ 'missing'      => false,
		/* bool */ 'filter'       => true,
		/* bool */ 'sort'         => false,
		/* bool */ 'endiff'       => false,
		/* str  */ 'uselang'      => $wgUser->getOption( 'language' ),
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
		wfAppendToArrayIfNotDefault( 'filter',
			$wgRequest->getBool( 'filter', $defaults['filter'] ),
			$defaults, $nondefaults);
		wfAppendToArrayIfNotDefault( 'sort',
			$wgRequest->getBool( 'sort', $defaults['sort'] ),
			$defaults, $nondefaults);
		wfAppendToArrayIfNotDefault( 'endiff',
			$wgRequest->getBool( 'endiff', $defaults['endiff'] ),
			$defaults, $nondefaults);
		wfAppendToArrayIfNotDefault( 'uselang',
			$wgRequest->getText( 'uselang', $defaults['uselang'] ),
			$defaults, $nondefaults);

		$this->defaults    = $defaults;
		$this->nondefaults = $nondefaults;
		$this->options     = $nondefaults + $defaults;

	}

	function initializeMessages() {
		global $wgMessageCache, $wgLang;

		# Make sure all extension messages are available
		MessageCache::loadAllMessages();

		$filtered = $this->getFilteredMessages();
		$infile = $wgLang->getUnmergedMessagesFor($wgLang->getCode());
		$infbfile = null;
		if ( $wgLang->getFallbackLanguage() ) {
			$langFB = $wgLang->newFromCode($wgLang->getFallbackLanguage());
			$infbfile = $langFB->getUnmergedMessagesFor($wgLang->getFallbackLanguage());
		}

		$sortedArray = array_merge( Language::getMessagesFor( 'en' ), $wgMessageCache->getExtensionMessagesFor( 'en' ) );
		if ( $this->options['sort'] ) {
			ksort( $sortedArray );
		}

		$wgMessageCache->disableTransform();

		foreach ( $sortedArray as $key => $value ) {
			$this->messages[$key]['enmsg'] = $value;
			$this->messages[$key]['filtered'] = isset($filtered[$key]) ? false : true;
			$this->messages[$key]['statmsg'] = $this->options['endiff'] ? $value : wfMsgNoDb( $key );
			$this->messages[$key]['msg'] = wfMsg ( $key );
			$this->messages[$key]['infile'] = @$infile[$key];
			$this->messages[$key]['infbfile'] = @$infbfile[$key];
		}

		$wgMessageCache->enableTransform();

	}

	function output() {
		global $wgOut;

		$navText = wfMsg( 'allmessagestext' );

		if ( $this->output ) {
			$input = htmlspecialchars($this->makeMsg( $this->messages ));
			if ( $this->outputType == self::OUTPUT_DEFAULT) {
				$wgOut->addHTML( '<pre id="wpTextbox1">' . $input . '</pre>');
			} else {
				$wgOut->addHTML( '<textarea id="wpTextbox1" rows="40">' . $input . '</textarea>');
			}
		} else {
			$wgOut->addHTML( $this->makeNavigation() );
			$wgOut->addWikiText( $navText );
			$wgOut->addHTML( $this->makeHTMLText( $this->messages, $this->options ) );
		}

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

	function makeNavigation() {

		global $wgTitle;
		$showhide = array( 'Show', 'Hide' );
		$sorthide = array( 'don\'t sort', 'alphabetical' );
		$changedLink = $this->makeOptionsLink( $showhide[1-$this->options['changed']],
			array( 'changed' => 1-$this->options['changed'] ), $this->nondefaults);
		$databaseLink = $this->makeOptionsLink( $showhide[1-$this->options['database']],
			array( 'database' => 1-$this->options['database'] ), $this->nondefaults);
		$missingLink = $this->makeOptionsLink( $showhide[1-$this->options['missing']],
			array( 'missing' => 1-$this->options['missing'] ), $this->nondefaults);
		$filterLink = $this->makeOptionsLink( $showhide[1-$this->options['filter']],
			array( 'filter' => 1-$this->options['filter'] ), $this->nondefaults);
		$sortLink = $this->makeOptionsLink( $sorthide[1-$this->options['sort']],
			array( 'sort' => 1-$this->options['sort'] ), $this->nondefaults);

		$changed  = wfMsgHtml('translate-changed', $changedLink);
		$database  = wfMsgHtml('translate-database', $databaseLink);
		$missing  = wfMsgHtml('translate-translated', $missingLink);
		$filter = wfMsgHtml('translate-core', $filterLink);
		$sort = wfMsgHtml('translate-sort', $sortLink);

		$export1 = '<a href="'.$wgTitle->escapeLocalUrl('ot=msg&uselang='.$this->options['uselang']).'">Form 1</a>';
		$export2 = '<a href="'.$wgTitle->escapeLocalUrl('ot=msg&box=1&uselang='.$this->options['uselang']).'">Form 2</a>';
		$a = wfOpenElement('ul') .
			wfOpenElement('li') . $changed . wfCloseElement('li') .
			wfOpenElement('li') . $database . wfCloseElement('li') .
			wfOpenElement('li') . $missing . wfCloseElement('li') .
			wfOpenElement('li') . $filter . wfCloseElement('li') .
			wfOpenElement('li') . $sort . wfCloseElement('li') .
			wfCloseElement('ul') .
			wfMsgHtml( 'translate-export' ) .
			wfOpenElement('ul') .
			wfOpenElement('li') . $export1 . wfCloseElement('li') .
			wfOpenElement('li') . $export2 . wfCloseElement('li') .
			wfCloseElement('ul');
		return $a;
	}

	/**
	* Makes change an option link which carries all the other options
	* @param $title @see Title
	* @param $override
	* @param $options
	*/
	function makeOptionsLink( $title, $override, $options ) {
		global $wgUser, $wgContLang;
		$sk = $wgUser->getSkin();
		return $sk->makeKnownLink( $wgContLang->specialPage( 'Translate' ),
			$title, wfArrayToCGI( $override, $options ) );
	}

	/**
	*
	*/
	function makeMsg($messages) {
		global $wgLang, $wgContLang;
		$txt = "\$messages = array(\n";
		foreach( $messages as $key => $m ) {
			if ( $m['filtered'] ) { continue; }

			$title = $wgLang->ucfirst( $key );
			if( $wgLang->getCode() != $wgContLang->getCode() ) {
				$title .= '/' . $wgLang->getCode();
			}

			$titleObj =& Title::makeTitle( NS_MEDIAWIKI, $title );

			#if(strtolower($wgLanguageCode) == 'en' &&)
			if( ( ($m['msg'] === $m['enmsg']) || ($m['msg'] === $m['infbfile']) ) && !$titleObj->exists() ) {
				continue;
			} elseif ($m['msg'] == '&lt;'.$key.'&gt;'){
				$m['msg'] = 'Ohayo';
				$comment = ' #empty';
			} else {
				$comment = '';
			}
			$key = "'$key'";
			while ( strlen($key) < 24 ) { $key .= ' '; }
			$txt .= "$key=> '" . preg_replace( "/(?<!\\\\)'/", "\'", $m['msg']) . "',$comment\n";
		}
		$txt .= ");\n";
		return $txt;
	}

	static function doExistenceCheck() {
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

	/**
	 * Create a list of messages, formatted in HTML as a list of messages and values and showing differences between the default language file message and the message in MediaWiki: namespace.
	 * @param $messages Messages array.
	 * @return The HTML list of messages.
	 */
	static function makeHTMLText( $messages, $options ) {
		global $wgLang, $wgContLang, $wgUser;
		wfProfileIn( __METHOD__ );
	
		$sk =& $wgUser->getSkin();
		$talk = $wgLang->getNsText( NS_TALK );
		$mwnspace = $wgLang->getNsText( NS_MEDIAWIKI );
		$mwtalk = $wgLang->getNsText( NS_MEDIAWIKI_TALK );
		
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
	
		$tablefooter = wfCloseElement( 'table' );
	
		$output =  '';
	
		$pageExists = self::doExistenceCheck();
	
		if($wgLang->getCode() != $wgContLang->getCode()) {
			$lang = '/' . $wgLang->getCode();
		} else {
			$lang = '';
		}
	
		wfProfileIn( __METHOD__ . '-output' );
	
		$i = 0;
		$open = false;
	
		foreach( $messages as $key => $m ) {
	
			$title = $wgLang->ucfirst( $key ) . $lang;
	
			$titleObj =& Title::makeTitle( NS_MEDIAWIKI, $title );
			$talkPage =& Title::makeTitle( NS_MEDIAWIKI_TALK, $title );
	
			$changed = ($m['statmsg'] != $m['msg']);
			$defined = ($m['infile'] !== NULL || $changed);
	
			if( $defined && $options['missing'] ) { continue; }
			if( !$changed && $options['changed'] ) { continue; }
			if( $m['filtered'] && $options['filter'] ) { continue; }
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
	
			if($changed) {
				$info = wfOpenElement( 'tr', array( 'class' => 'orig') );
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
				$info = wfOpenElement( 'tr', array( 'class' => 'def') );
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

?>
