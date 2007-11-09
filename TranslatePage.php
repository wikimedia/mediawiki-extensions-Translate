<?php

class SpecialTranslate extends SpecialPage {
	const MSG = 'translate-page-';

	protected $task = null;
	protected $group = null;

	protected $defaults    = null;
	protected $nondefaults = null;
	protected $options     = null;

	function __construct() {
		SpecialPage::SpecialPage( 'Translate' );
	}

	/**
	 * Access point for this special page.
	 * GLOBALS: $wgHooks, $wgOut.
	 */
	public function execute( $parameters ) {
		wfLoadExtensionMessages( 'Translate' );

		global $wgHooks;
		$wgHooks['SkinTemplateSetupPageCss'][] = array( $this , 'pagecss' );

		$this->setup();
		$this->setHeaders();

		global $wgOut;
		$wgOut->addHTML( $this->settingsForm() );

		if ( !$this->options['language'] ) {
			$wgOut->addHTML(
				wfMsgExt( self::MSG . 'no-such-language', array( 'parse' ) )
			);
			return;
		}

		# Everything ok, proceed
		$table = $this->task->execute();
		$links = $this->doStupidLinks();
		$wgOut->addHTML( $links . $table . $links );
	}

	function setup() {
		global $wgUser, $wgRequest;

		$defaults = array(
		/* str  */ 'task'     => 'view',
		/* str  */ 'sort'     => 'normal',
		/* str  */ 'language' => $wgUser->getOption( 'language' ),
		/* str  */ 'group'    => 'core',
		/* int  */ 'offset'   => 0,
		/* int  */ 'limit'    => 100,
		);

		// Dump everything here
		$nondefaults = array();

		foreach ( $defaults as $v => $t ) {
			if ( is_bool($t) ) {
				$r = $wgRequest->getBool( $v, $defaults[$v] );
			} elseif( is_int($t) ) {
				$r = $wgRequest->getInt( $v, $defaults[$v] );
			} elseif( is_string($t) ) {
				$r = $wgRequest->getText( $v, $defaults[$v] );
			}
			wfAppendToArrayIfNotDefault( $v, $r, $defaults, $nondefaults);
		}

		$this->defaults    = $defaults;
		$this->nondefaults = $nondefaults;
		$this->options     = $nondefaults + $defaults;

		$this->group = MessageGroups::getGroup( $this->options['group'] );
		$this->task  = TranslateTasks::getTask( $this->options['task'] );

		$taskOptions = new TaskOptions(
			$this->options['language'],
			$this->options['limit'],
			$this->options['offset'],
			array( $this, 'cbAddPagingNumbers' )
		);
		$this->task->init( $this->group, $taskOptions );

	}

	/**
	 * GLOBALS: $wgTitle, $wgScript
	 */
	protected function settingsForm() {
		global $wgTitle, $wgScript;

		$tasks = $this->taskSelector();
		$groups = $this->groupSelector();
		$languages = $this->languageSelector();
		$limit = $this->limitSelector();
		$button = Xml::submitButton( wfMsg( TranslateUtils::MSG . 'submit' ) );

		$line = wfMsgHtml( TranslateUtils::MSG . 'settings', $tasks, $groups, $languages, $limit, $button );
		$form = Xml::tags( 'form',
			array(
				'action' => $wgScript,
				'method' => 'get'
			),
			$line . Xml::hidden( 'title', $wgTitle->getPrefixedText() )
		);
		return $form;
	}

	private function taskSelector() {
		$options = '';
		foreach ( TranslateTasks::getTasks() as $id => $class ) {
			$label = call_user_func( array( $class, 'labelForTask' ), $id );
			$options .=  Xml::option( $label , $id, $this->task->getId() === $id );
		}

		return TranslateUtils::selector( 'task', $options );
	}

	protected function languageSelector() {
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
			$selected = ($code == $this->options['language']);
			$options .= Xml::option( "$code - $name", $code, $selected ) . "\n";
		}

		return TranslateUtils::selector( 'language', $options );
	}

	protected function limitSelector() {
		$items = array( 100, 250, 500, 1000, 2000 );
		$selected = $this->options['limit'];
		return TranslateUtils::simpleSelector( 'limit' , $items, $selected );
	}

	protected function groupSelector() {
		$groups = MessageGroups::singleton()->getGroups();
		$options = '';
		foreach( $groups as $class) {
			$options .= Xml::option( $class->getLabel(), $class->getId(),
				$this->options['group'] === $class->getId()) . "\n";
		}
		return TranslateUtils::selector( 'group', $options );
	}


	private $paging = null;
	public function cbAddPagingNumbers( $start, $count, $total ) {
		$this->paging = array(
			'start' => $start,
			'count' => $count,
			'total' => $total
		);
	}

	protected function doStupidLinks() {
		if ( $this->paging === null ) { return ''; }

		$previous = wfMsg( TranslateUtils::MSG . 'prev' );
		if ( $this->options['offset'] > 0 ) {
			$offset = max( 0, $this->options['offset']-$this->options['limit'] );
			$previous = $this->makeOffsetLink( $previous, $offset );
		}

		$nextious = wfMsg( TranslateUtils::MSG . 'next' );
		if ( $this->paging['total'] != $this->paging['start'] + $this->paging['count'] ) {
			$offset = $this->options['offset']+$this->options['limit'];
			$nextious = $this->makeOffsetLink( $nextious, $offset );
		}

		$start = $this->paging['start'] +1 ;
		$stop  = $start + $this->paging['count']-1;
		$total = $this->paging['total'];

		return wfMsgWikiHtml( TranslateUtils::MSG . 'paging', $start, $stop, $total, $previous, $nextious );
	}

	private function makeOffsetLink( $label, $offset ) {
		global $wgTitle, $wgUser;
		$skin = $wgUser->getSkin();
		$link = $skin->makeLinkObj( $wgTitle, $label,
			wfArrayToCGI(
				array( 'offset' => $offset),
				$this->nondefaults
			)
		);
		return $link;
	}

	public function pagecss( $css ) {
		$file = dirname( __FILE__ ) . '/Translate.css';
		$css .= "/*<![CDATA[*/\n" . htmlspecialchars( file_get_contents( $file ) ) . "\n/*]]>*/";
		return true;
	}


}