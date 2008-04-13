<?php
if (!defined('MEDIAWIKI')) die();

/**
 * Implements the core of Translate extension - a special page which shows
 * a list of messages in a format defined by Tasks.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2006-2007 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
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
		TranslateUtils::injectCSS();

		$this->setup();
		$this->setHeaders();

		$errors = array();

		if ( !$this->options['language'] ) {
			$errors['language'] = wfMsgExt( self::MSG . 'no-such-language', array( 'parse' ) );
			$this->options['language'] = $this->defaults['language'];
		}
		if ( !$this->task instanceof TranslateTask ) {
			$errors['task'] = wfMsgExt( self::MSG . 'no-such-task', array( 'parse' ) );
			$this->options['task'] = $this->defaults['task'];
		}
		if ( !$this->group instanceof MessageGroup ) {
			$errors['group'] = wfMsgExt( self::MSG . 'no-such-group', array( 'parse' ) );
			$this->options['group'] = $this->defaults['group'];
		}

		// Show errors nicely
		$wgOut->addHTML( $this->settingsForm( $errors ) );

		if ( count($errors) ) return;

		# Proceed
		$taskOptions = new TaskOptions(
			$this->options['language'],
			$this->options['limit'],
			$this->options['offset'],
			array( $this, 'cbAddPagingNumbers' )
		);

		// Initialise and get output
		$this->task->init( $this->group, $taskOptions );
		$output = $this->task->execute();

		if ( $this->task->plainOutput() ) {
			$wgOut->disable();
			header( 'Content-type: text/plain; charset=UTF-8' );
			echo $output;
		} else {
			$description = $this->getGroupDescription();
			$links = $this->doStupidLinks();
			if ( $this->paging['count'] === 0 ) {
				$wgOut->addHTML( $description . $links );
			} else {
				$wgOut->addHTML( $description . $links . $output . $links );
			}
		}
	}

	protected function setup() {
		global $wgUser, $wgRequest;

		$defaults = array(
		/* str  */ 'task'     => 'untranslated',
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
			wfAppendToArrayIfNotDefault( $v, $r, $defaults, $nondefaults );
		}

		$this->defaults    = $defaults;
		$this->nondefaults = $nondefaults;
		$this->options     = $nondefaults + $defaults;

		$this->group = MessageGroups::getGroup( $this->options['group'] );
		$this->task  = TranslateTasks::getTask( $this->options['task'] );
	}

	/**
	 * GLOBALS: $wgTitle, $wgScript
	 */
	protected function settingsForm($errors) {
		global $wgTitle, $wgScript;

		$task = $this->taskSelector();
		$group = $this->groupSelector();
		$language = $this->languageSelector();
		$limit = $this->limitSelector();
		$button = Xml::submitButton( wfMsg( TranslateUtils::MSG . 'submit' ) );


		$options = array();
		foreach ( array( 'task', 'group', 'language', 'limit' ) as $g ) {
			$options[] = self::optionRow(
				Xml::label( wfMsg( self::MSG . $g ), $g),
				$$g,
				array_key_exists( $g, $errors ) ? $errors[$g] : null
			);
		}

		$form =
			Xml::openElement( 'fieldset', array( 'class' => 'mw-sp-translate-settings' ) ) .
				Xml::element( 'legend', null, wfMsg( self::MSG . 'settings-legend' ) ) .
				Xml::openElement( 'form', array( 'action' => $wgScript, 'method' => 'get' ) ) .
					Xml::hidden( 'title', $wgTitle->getPrefixedText() ) .
					Xml::openElement( 'table' ) .
						implode( "", $options ) .
						self::optionRow( $button, ' ' ) .
					Xml::closeElement( 'table' ) .
				Xml::closeElement( 'form' ) .
			Xml::closeElement( 'fieldset' );
		return $form;
	}

	private static function optionRow( $label, $option, $error = null ) {
		return
			Xml::openElement( 'tr' ) .
				Xml::tags( 'td', null, $label ) .
				Xml::tags( 'td', null, $option ) .
				( $error ? Xml::tags( 'td', array( 'class' => 'mw-sp-translate-error' ), $error ) : '' ) .
			Xml::closeElement( 'tr' );

	}

	/* Selectors ahead */

	protected function groupSelector() {
		$groups = MessageGroups::singleton()->getGroups();
		$selector = new HTMLSelector( 'group', 'group', $this->options['group'] );
		foreach( $groups as $id => $class ) {
			$selector->addOption( $class->getLabel(), $id );
		}
		return $selector->getHTML();
	}

	protected function taskSelector() {
		$selector = new HTMLSelector( 'task', 'task', $this->options['task'] );
		foreach ( TranslateTasks::getTasks() as $id ) {
			$label = call_user_func( array( 'TranslateTask', 'labelForTask' ), $id );
			$selector->addOption( $label, $id );
		}
		return $selector->getHTML();
	}

	protected function languageSelector() {
		global $wgLang;
		return TranslateUtils::languageSelector( $wgLang->getCode(), $this->options['language'] );
	}

	protected function limitSelector() {
		global $wgLang;
		$items = array( 100, 250, 500, 1000, 2500 );
		$selector = new HTMLSelector( 'limit', 'limit', $this->options['limit'] );
		foreach ( $items as $count ) {
			$selector->addOption( wfMsgExt( self::MSG . 'limit-option', 'parsemag', $wgLang->formatNum( $count ) ), $count );
		}
		return $selector->getHTML();
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
		if ( $this->paging === null ) return '';

		$start = $this->paging['start'] +1 ;
		$stop  = $start + $this->paging['count']-1;
		$total = $this->paging['total'];

		$allInThisPage = $start === 1 && $total <= $this->options['limit'];

		if ( $this->paging['count'] === 0 ) {
			$navigation = wfMsgExt( self::MSG . 'showing-none', array( 'parse' ) );
		} elseif ( $allInThisPage ) {
			$navigation = wfMsgExt( self::MSG . 'showing-all',
				array( 'parse' ), $total );
		} else {
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

			$showing = wfMsgExt( self::MSG . 'showing',
				array( 'parseinline' ), $start, $stop, $total );
			$navigation = wfMsgExt( self::MSG . 'paging-links',
				array( 'escape', 'replaceafter' ), $previous, $nextious );

			$navigation = Xml::tags( 'p', null, $showing . ' ' . $navigation );
		}

		return
			Xml::openElement( 'fieldset' ) .
				Xml::element( 'legend', null, wfMsg( self::MSG . 'navigation-legend' ) ) .
				$navigation .
			Xml::closeElement( 'fieldset' );
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

	protected function getGroupDescription() {
		global $wgOut;
		$description = $this->group->getDescription();
		if ( !$description ) return '';
		return
			Xml::openElement( 'fieldset' ) .
				Xml::element( 'legend', null, wfMsg( self::MSG . 'description-legend' ) ) .
				$wgOut->parse( $description ) .
			Xml::closeElement( 'fieldset' );
	}
}
