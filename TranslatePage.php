<?php

class SpecialTranslate extends SpecialPage {

	protected $task = null;
	protected $group = null;

	function __construct() {
		SpecialPage::SpecialPage( 'Translate' );
	}

	/** Access point for this special page */
	public function execute() {
		wfLoadExtensionMessages( 'Translate' );

		global $wgHooks;
		$wgHooks['SkinTemplateSetupPageCss'][] = array( $this , 'pagecss' );

		$this->setup();
		$this->setHeaders();

		global $wgOut;
		$wgOut->addHTML( $this->settingsForm() );
		$table = $this->task->execute();
		$links = $this->doStupidLinks();
		$wgOut->addHTML( $links . $table . $links );
	}

	function setup() {
		global $wgUser, $wgRequest;

		$defaults = array(
		/* str  */ 'task'     => '',
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


		$groups = MessageGroups::singleton()->getGroups();
		$this->group = $groups[0];
		foreach( $groups as $group ) {
			if ( $group->getId() === $this->options['group'] ) {
				$this->group = $group;
				break;
			}
		}

		$tasks = TranslateTasks::tasks();
		$this->task = $tasks[0];
		foreach( $tasks as $task ) {
			if ( $task->getId() === $this->options['task'] ) {
				$this->task = $task;
				break;
			}
		}

		$taskOptions = new TaskOptions(
			$this->options['language'],
			$this->options['limit'],
			$this->options['offset'],
			array( $this, 'cbAddPagingNumbers' )
		);
		$this->task->init( $this->group, $taskOptions );

	}

	protected function settingsForm() {
		global $wgTitle;

		$tasks = $this->taskSelector();
		$groups = $this->groupSelector();
		$languages = $this->languageSelector();
		$limit = $this->limitSelector();
		$button = Xml::submitButton( wfMsg( TranslateUtils::MSG . 'submit' ) );

		$line = wfMsgHtml( TranslateUtils::MSG . 'settings', $tasks, $groups, $languages, $limit, $button );
		$form = Xml::tags( 'form',
			array(
				'action' => $wgTitle->getLocalURL(),
				'method' => 'get'
			),
			$line
		);
		return $form;
	}

	protected function selector( $name, $options ) {
		return Xml::tags( 'select', array( 'name' => $name ), $options );
	}

	protected function simpleSelector( $name, $items, $selected ) {
		$options = array();
		foreach ( $items as $key => $item ) {
			$options[] = Xml::option( $item, $item, $item === $selected );
		}
		return $this->selector( $name, implode( "\n", $options ) );
	}


	private function taskSelector() {
		$items = array();
		foreach ( TranslateTasks::tasks() as $task ) {
			$items[$task->getId()] = $task->getLabel();
		}

		$options = '';
		foreach ( $items as $key => $label ) {
			$options .=  Xml::option( $label , $key, $this->task->getId() === $key );
		}

		return $this->selector( 'task', $options );
	}

	protected function languageSelector() {
		$languages = Language::getLanguageNames( false );
		ksort( $languages );

		$options = '';
		foreach( $languages as $code => $name ) {
			$selected = ($code == $this->options['language']);
			$options .= Xml::option( "$code - $name", $code, $selected ) . "\n";
		}

		return $this->selector( 'language', $options );
	}

	protected function limitSelector() {
		$items = array( 100, 250, 500, 1000, 2000 );
		$selected = $this->options['limit'];
		return $this->simpleSelector( 'limit' , $items, $selected );
	}

	protected function groupSelector() {
		$groups = MessageGroups::singleton()->getGroups();
		$options = '';
		foreach( $groups as $class) {
			$options .= Xml::option( $class->getLabel(), $class->getId(),
				$this->options['group'] === $class->getId()) . "\n";
		}
		return $this->selector( 'group', $options );
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