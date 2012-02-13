<?php
/**
 * Contains logic for special page Special:Translate.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2006-2011 Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Implements the core of Translate extension - a special page which shows
 * a list of messages in a format defined by Tasks.
 *
 * @ingroup SpecialPage TranslateSpecialPage
 */
class SpecialTranslate extends SpecialPage {

	/**
	 * @var Task
	 */
	protected $task = null;

	/**
	 * @var MessageGroup
	 */
	protected $group = null;

	protected $defaults    = null;
	protected $nondefaults = null;
	protected $options     = null;

	function __construct() {
		parent::__construct( 'Translate' );
	}

	/**
	 * Access point for this special page.
	 */
	public function execute( $parameters ) {
		global $wgOut, $wgTranslateBlacklist, $wgRequest;

		$wgOut->addModules( 'ext.translate.special.translate' );

		$this->setHeaders();

		// @todo Move to api or so
		if ( $parameters === 'editpage' ) {
			$editpage = TranslationEditPage::newFromRequest( $wgRequest );

			if ( $editpage ) {
				$editpage->execute();
				return;
			}
		}

		$this->setup( $parameters );

		if ( $this->options['group'] === '' ) {
			TranslateUtils::addSpecialHelpLink( $wgOut, 'Help:Extension:Translate/Translation_example' );
			$this->groupInformation();
			return;
		}

		$codes = Language::getLanguageNames( false );
		$errors = array();
		if ( !$this->options['language'] || !isset( $codes[$this->options['language']] ) ) {
			$errors['language'] = wfMessage( 'translate-page-no-such-language' )->text();
			$this->options['language'] = $this->defaults['language'];
		}

		if ( !$this->group instanceof MessageGroup ) {
			$errors['group'] = wfMessage( 'translate-page-no-such-group' )->text();
			$this->options['group'] = $this->defaults['group'];
		}

		TranslateUtils::addSpecialHelpLink( $wgOut, 'Help:Extension:Translate/Translation_example' );
		// Show errors nicely.
		$wgOut->addHTML( $this->settingsForm( $errors ) );

		if ( count( $errors ) ) {
			return;
		} else {
			$checks = array(
				$this->options['group'],
				strtok( $this->options['group'], '-' ),
				'*'
			);

			foreach ( $checks as $check ) {
				$reason = @$wgTranslateBlacklist[$check][$this->options['language']];
				if ( $reason !== null ) {
					$wgOut->addWikiMsg( 'translate-page-disabled', $reason );
					return;
				}
			}
		}

		// Proceed.
		$taskOptions = new TaskOptions(
			$this->options['language'],
			$this->options['limit'],
			$this->options['offset'],
			array( $this, 'cbAddPagingNumbers' )
		);

		// Initialise and get output.
		if ( !$this->task ) {
			return;
		}
		$this->task->init( $this->group, $taskOptions );
		$output = $this->task->execute();

		if ( $this->task->plainOutput() ) {
			$wgOut->disable();
			header( 'Content-type: text/plain; charset=UTF-8' );
			echo $output;
		} else {
			$description = $this->getGroupDescription( $this->group );

			$taskid = $this->options['task'];
			if ( in_array( $taskid, array( 'untranslated', 'reviewall' ), true ) ) {
				$hasOptional = count( $this->group->getTags( 'optional' ) );
				if ( $hasOptional ) {
					$linker = class_exists( 'DummyLinker' ) ? new DummyLinker : new Linker;
					$linktext = wfMessage( 'translate-page-description-hasoptional-open' )->escaped();
					$params = array( 'task' => 'optional' ) + $this->nondefaults;
					$link = $linker->link( $this->getTitle(), $linktext, array(), $params );
					$note = wfMessage( 'translate-page-description-hasoptional' )->rawParams( $link )->parseAsBlock();

					if ( $description ) {
						$description .= '<br>' . $note;
					} else {
						$description = $note;
					}
				}
			}

			$status = $this->getWorkflowStatus();
			if ( $status !== false ) {
				$description = $status . $description;
			}

			if ( $description ) {
				$description = Xml::fieldset( wfMsg( 'translate-page-description-legend' ), $description );
			}

			$links = $this->doStupidLinks();

			if ( $this->paging['count'] === 0 ) {
				$wgOut->addHTML( $description . $links );
			} elseif( $this->paging['count'] === $this->paging['total']  ) {
				$wgOut->addHTML( $description . $output . $links );
			} else {
				$wgOut->addHTML( $description . $links . $output . $links );
			}
		}
	}

	protected function setup( $parameters ) {
		global $wgUser, $wgRequest;

		$defaults = array(
		/* str  */ 'taction'  => 'translate',
		/* str  */ 'task'     => 'untranslated',
		/* str  */ 'sort'     => 'normal',
		/* str  */ 'language' => $wgUser->getOption( 'language' ),
		/* str  */ 'group'    => '',
		/* int  */ 'offset'   => 0,
		/* int  */ 'limit'    => 100,
		);

		// Dump everything here
		$nondefaults = array();

		$parameters = array_map( 'trim', explode( ';', $parameters ) );
		$pars = array();

		foreach ( $parameters as $_ ) {
			if ( $_ === '' ) {
				continue;
			}

			if ( strpos( $_, '=' ) !== false ) {
				list( $key, $value ) = array_map( 'trim', explode( '=', $_, 2 ) );
			} else {
				$key = 'group';
				$value = $_;
			}

			$pars[$key] = $value;
		}

		foreach ( $defaults as $v => $t ) {
			if ( is_bool( $t ) ) {
				$r = isset( $pars[$v] ) ? (bool) $pars[$v] : $defaults[$v];
				$r = $wgRequest->getBool( $v, $r );
			} elseif ( is_int( $t ) ) {
				$r = isset( $pars[$v] ) ? (int) $pars[$v] : $defaults[$v];
				$r = $wgRequest->getInt( $v, $r );
			} elseif ( is_string( $t ) ) {
				$r = isset( $pars[$v] ) ? (string) $pars[$v] : $defaults[$v];
				$r = $wgRequest->getText( $v, $r );
			}

			wfAppendToArrayIfNotDefault( $v, $r, $defaults, $nondefaults );
		}

		// Fix defaults based on what we got
		if ( isset( $nondefaults['taction'] ) ) {
			if ( $nondefaults['taction'] === 'proofread' ) {
				if ( $wgUser->isAllowed( 'translate-messagereview' ) ) {
					$defaults['task'] = 'acceptqueue';
				} else {
					$defaults['task'] = 'reviewall';
				}
			} elseif( $nondefaults['taction'] === 'export' ) {
				$defaults['task'] = '';
			}
		}

		$this->defaults    = $defaults;
		$this->nondefaults = $nondefaults;
		$this->options     = $nondefaults + $defaults;

		$this->group = MessageGroups::getGroup( $this->options['group'] );
		$this->task  = TranslateTasks::getTask( $this->options['task'] );

		if ( $this->group instanceof RecentMessageGroup ) {
			$this->group->setLanguage( $this->options['language'] );
		}
	}

	protected function settingsForm( $errors ) {
		global $wgScript, $wgUser;
		$user = $wgUser;

		$taction = $this->options['taction'];

		$selectors = array(
			'group' => $this->groupSelector(),
			'language' => $this->languageSelector(),
			'limit' => $this->limitSelector(),
		);

		if ( $taction === 'export' ) {
			unset( $selectors['limit'] );
		}

		$options = array();
		foreach ( $selectors as $g => $selector ) {
			$options[] = self::optionRow(
				wfMessage( 'translate-page-' . $g )->escaped(),
				$selector,
				array_key_exists( $g, $errors ) ? $errors[$g] : null
			);
		}

		if ( $taction === 'proofread' ) {
			$extra = $this->taskLinks( array( 'acceptqueue', 'reviewall' ) );
		} elseif ( $taction === 'translate' ) {
			$extra = $this->taskLinks( array( 'view', 'untranslated', 'optional', 'suggestions' ) );
		} elseif ( $taction === 'export' ) {
			$extra = $this->taskLinks( array( 'export-as-po', 'export-to-file' ) );
		} else {
			$extra = '';
		}

		$nonEssential = Html::rawElement( 'span', array( 'class' => 'mw-sp-translate-nonessential' ), implode( "", $options ) );

		$button = Xml::submitButton( wfMsg( 'translate-submit' ) );

		$form =
			Html::openElement( 'fieldset', array( 'class' => 'mw-sp-translate-settings' ) ) .
				Html::element( 'legend', null, wfMsg( 'translate-page-settings-legend' ) ) .
				Html::openElement( 'form', array( 'action' => $wgScript, 'method' => 'get' ) ) .
					Html::hidden( 'title', $this->getTitle()->getPrefixedText() ) .
					Html::hidden( 'taction', $this->options['taction'] ) .
						"$nonEssential\n$extra\n$button\n" .
				Html::closeElement( 'form' ) .
			Html::closeElement( 'fieldset' );
		return $form;
	}

	/**
	 * @param $label string
	 * @param $option string
	 * @param $error string Html
	 * @return string
	 */
	private static function optionRow( $label, $option, $error = null ) {
		return
				"<label>$label&nbsp;$option</label>" .
				( $error ? Html::rawElement( 'span', array( 'class' => 'mw-sp-translate-error' ), $error ) : '' ) . ' ';
	}

	protected function taskLinks( $tasks ) {
		global $wgUser;
		$user = $wgUser;

		foreach ( $tasks as $index => $id ) {
			$task = TranslateTasks::getTask( $id );
			if ( !$task ) {
				unset( $tasks[$index] );
				continue;
			}

			if ( !$task->isAllowedFor( $user ) ) {
				unset( $tasks[$index] );
				continue;
			}
		}

		$sep = Html::element( 'br' );
		$count = count( $tasks );
		if ( $count === 0 ) {
			return $sep . wfMessage( 'translate-taction-disabled' )->escaped();
		} elseif ( $count === 1 ) {
			$id = array_pop( $tasks );
			// If there is only one task, and it is the default task, hide it.
			// If someone disables the default task for action, we will show
			// a list of alternative task(s), but not showing anything
			// by default. */
			if ( $this->defaults['task'] === $id ) {
				return '';
			}
			return $sep . Html::rawElement( 'label', null,
				Xml::radio( 'task', $id, true ) . ' ' .
				wfMessage( "translate-taskui-$id" )->escaped()
			);
		} else {
			$output = '';
			foreach ( $tasks as $index => $id ) {
				$output .= Html::rawElement( 'label', null,
				Xml::radio( 'task', $id, $this->options['task'] === $id ) . ' ' .
				wfMessage( "translate-taskui-$id" )->escaped()
			) . ' ';
			}
			return $sep . $output;
		}
	}

	protected function groupSelector() {
		$activeId = false;
		if ( $this->group ) {
			$activeId = $this->group->getId();
		}

		$groups = MessageGroups::getAllGroups();
		$dynamic = MessageGroups::getDynamicGroups();
		$groups = array_keys( array_merge( $groups, $dynamic ) );

		$selected = $this->options['group'];

		$selector = new XmlSelect( 'group', 'group' );
		$selector->setDefault( $selected );

		foreach ( $groups as $id ) {
			if ( $id === $activeId ) {
				$activeId = false;
			}
			$group = MessageGroups::getGroup( $id );
			$hide = MessageGroups::getPriority( $group ) === 'discouraged';

			if ( !$group->exists() || $hide ) {
				continue;
			}

			$selector->addOption( $group->getLabel(), $id );
		}

		if ( $activeId ) {
			$selector->addOption( $this->group->getLabel(), $activeId );
		}

		return $selector->getHTML();
	}

	protected function languageSelector() {
		global $wgLang;

		return TranslateUtils::languageSelector(
			$wgLang->getCode(),
			$this->options['language']
		);
	}

	protected function limitSelector() {
		global $wgLang;

		$items = array( 100, 1000, 5000 );
		$selector = new XmlSelect( 'limit', 'limit' );
		$selector->setDefault( $this->options['limit'] );

		foreach ( $items as $count ) {
			$selector->addOption( wfMsgExt( 'translate-page-limit-option', 'parsemag', $wgLang->formatNum( $count ) ), $count );
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
		global $wgLang;

		if ( $this->paging === null ) {
			return '';
		}

		$start = $this->paging['start'] + 1 ;
		$stop  = $start + $this->paging['count'] - 1;
		$total = $this->paging['total'];

		$allInThisPage = $start === 1 && $total <= $this->options['limit'];

		if ( $this->paging['count'] === 0 ) {
			$navigation = wfMessage( 'translate-page-showing-none' )->parse();
		} elseif ( $allInThisPage ) {
			$navigation = wfMessage( 'translate-page-showing-all', $wgLang->formatNum( $total ) )->parse();
		} else {
			$previous = wfMsg( 'translate-prev' );
			if ( $this->options['offset'] > 0 ) {
				$offset = max( 0, $this->options['offset'] - $this->options['limit'] );
				$previous = $this->makeOffsetLink( $previous, $offset );
			}

			$nextious = wfMsg( 'translate-next' );

			if ( $this->paging['total'] != $this->paging['start'] + $this->paging['count'] ) {
				$offset = $this->options['offset'] + $this->options['limit'];
				$nextious = $this->makeOffsetLink( $nextious, $offset );
			}

			$start = $this->paging['start'] + 1 ;
			$stop  = $start + $this->paging['count'] - 1;
			$total = $this->paging['total'];

			$showing = wfMsgExt(
				'translate-page-showing',
				array( 'parseinline' ),
				$wgLang->formatNum( $start ),
				$wgLang->formatNum( $stop ),
				$wgLang->formatNum( $total )
			);

			$navigation = wfMsgExt(
				'translate-page-paging-links',
				array( 'escape', 'replaceafter' ),
				$previous,
				$nextious
			);

			$navigation = $showing . ' ' . $navigation;
		}

		return
			Html::openElement( 'fieldset' ) .
				Html::element( 'legend', null, wfMsg( 'translate-page-navigation-legend' ) ) .
				$navigation .
			Html::closeElement( 'fieldset' );
	}

	private function makeOffsetLink( $label, $offset ) {
		$linker = class_exists( 'DummyLinker' ) ? new DummyLinker : new Linker;

		$query = array_merge(
			$this->nondefaults,
			array( 'offset' => $offset )
		);

		$link = $linker->link(
			$this->getTitle(),
			$label,
			array(),
			$query
		);

		return $link;
	}

	protected function getGroupDescription( MessageGroup $group ) {
		$description = $group->getDescription();
		if ( $description !== null ) {
			global $wgOut;
			return $wgOut->parse( $description, false );
		}

		return '';
	}

	/**
	 * This funtion renders the default list of groups when no parameters
	 * are passed.
	 */
	public function groupInformation() {
		global $wgOut;
		$structure = MessageGroups::getGroupStructure();
		if ( !$structure ) {
			$wgOut->addWikiMsg( 'translate-grouplisting-empty' );
			return;
		}

		$wgOut->addWikiMsg( 'translate-grouplisting' );

		$out = '';
		foreach ( $structure as $blocks ) {
			$out .= $this->formatGroupInformation( $blocks );
		}

		$wgOut->addHtml( Html::rawElement( 'table', array( 'class' => 'mw-sp-translate-grouplist wikitable' ), $out ) );
	}

	public function formatGroupInformation( $blocks, $level = 2 ) {
		global $wgLang;

		if ( is_array( $blocks ) ) {
			foreach ( $blocks as $i => $block ) {
				if ( !is_array( $block ) && MessageGroups::getPriority( $block ) === 'discouraged' ) {
					unset( $blocks[$i] );
				}
			}
			$block = array_shift( $blocks );
		} else {
			$block = $blocks;
			if ( MessageGroups::getPriority( $block ) === 'discouraged' ) {
				return '';
			}
		}

		$id = $block->getId();

		$title = $this->getTitle();

		$queryParams = array(
			'group' => $id,
			'language' => $this->options['language'],
			'taction' => $this->options['taction'],
		);

		$linker = class_exists( 'DummyLinker' ) ? new DummyLinker : new Linker;

		$label = $linker->link(
			$title,
			htmlspecialchars( $block->getLabel() ),
			array(),
			$queryParams
		);

		$desc = $this->getGroupDescription( $block );
		$hasSubblocks = is_array( $blocks ) && count( $blocks );

		$subid = Sanitizer::escapeId( "mw-subgroup-$id" );

		if ( $hasSubblocks ) {
			$msg = wfMessage( 'translate-showsub', $wgLang->formatNum( count( $blocks ) ) )->text();
			$target = TranslationHelpers::jQueryPathId( $subid );
			$desc .= Html::element( 'a', array( 'onclick' => "jQuery($target).toggle()", 'class' => 'mw-sp-showmore' ), $msg );
		}

		$out = "\n<tr><td>$label</td>\n<td>$desc</td></tr>\n";
		if ( $hasSubblocks ) {
			$out .= "<tr><td></td><td>\n";
			$tableParams = array(
				'id' => $subid,
				'style' => 'display:none;',
				'class' => "mw-sp-translate-subgroup depth-$level",
			);
			$out .= Html::openElement( 'table', $tableParams );
			foreach ( $blocks as $subBlock ) {
				$out .= $this->formatGroupInformation( $subBlock, $level + 1 );
			}
			$out .= '</table></td></tr>';
		}

		return $out;
	}

	protected function getWorkflowStatus() {
		global $wgTranslateWorkflowStates, $wgUser;
		if ( !$wgTranslateWorkflowStates ) {
			return false;
		}

		if ( MessageGroups::isDynamic( $this->group ) ) {
			return false;
		}

		$dbr = wfGetDB( DB_SLAVE );
		$current = $dbr->selectField(
			'translate_groupreviews',
			'tgr_state',
			array( 'tgr_group' => $this->options['group'], 'tgr_lang' => $this->options['language'] ),
			__METHOD__
		);

		if ( $wgUser->isAllowed( 'translate-groupreview' ) ) {
			$selector = new XmlSelect( 'workflow' );

			$selector->setAttribute( 'class', 'mw-translate-workflowselector' );
			$selector->setDefault( $current );
			$selector->addOption( wfMessage( 'translate-workflow-state-' )->text(), '' );
			foreach ( array_keys( $wgTranslateWorkflowStates ) as $state ) {
				$stateMessage = wfMessage( "translate-workflow-state-$state" );
				$stateText = $stateMessage->isBlank() ? $state : $stateMessage->text();
				$selector->addOption( $stateText, $state );
			}
			$state = $selector->getHTML();

			$attributes = array(
				'type' => 'button',
				'id' => 'mw-translate-workflowset',
				'data-token' => ApiGroupReview::getToken( 0, '' ),
				'data-group' => $this->options['group'],
				'data-language' => $this->options['language'],
				'style' => 'visibility: hidden;',
				'value' => 'Set',
			);
			$state .= Html::element( 'input', $attributes );
		} elseif ( strval( $current ) !== '' ) {
			$state = $current;
		} else {
			$state = wfMessage( 'translate-workflow-state-' )->escaped();
		}

		$message = wfMessage( 'translate-workflowstatus' )->rawParams( $state );
		$box = Html::rawElement( 'div', array( 'id' => 'mw-sp-translate-workflow' ), $message->escaped() );
		return $box;
	}

	/**
	 * Adds the task-based tabs on Special:Translate and few other special pages.
	 * Hook: SkinTemplateNavigation::SpecialPage
	 * @since 2012-02-10
	 */
	static function tabify( Skin $skin, array &$tabs ) {
		global $wgRequest, $wgOut;

		$title = $skin->getTitle();
		list( $alias, $sub ) = SpecialPage::resolveAliasWithSubpage( $title->getText() );

		$pagesInGroup = array( 'Translate', 'LanguageStats', 'MessageGroupStats' );
		if ( !in_array( $alias, $pagesInGroup, true ) ) {
			return true;
		}

		$wgOut->addModules( 'ext.translate.tabgroup' );

		// Extract subpage syntax, otherwise the values are not passed forward
		$params = array();
		if ( trim( $sub ) !== '' ) {
			if ( $alias === 'Translate' || $alias === 'MessageGroupStats' ) {
				$params['group'] = $sub;
			} elseif( $alias === 'LanguageStats' ) {
				// Breaks if additional parameters besides language are code provided
				$params['language'] = $sub;
			}
		}
		// However, query string params take precedence
		$params = $wgRequest->getQueryValues() + $params;
		asort( $params );

		$taction = $wgRequest->getVal( 'taction', 'translate' );

		$translate = SpecialPage::getTitleFor( 'Translate' );
		$languagestats = SpecialPage::getTitleFor( 'LanguageStats' );
		$messagegroupstats = SpecialPage::getTitleFor( 'MessageGroupStats' );


		unset( $params['task'] ); // Depends on taction
		unset( $params['taction'] ); // We're supplying this ourself
		unset( $params['title'] ); // As above
		unset( $params['x'] ); // Was posted -marker on stats pages
		unset( $params['suppresscomplete'] ); // Stats things, should
		unset( $params['suppressempty'] ); // not be passed

		// Clear the special page tab that might be there already
		$tabs['namespaces'] = array();

		$tabs['namespaces']['translate'] = $data = array(
			'text' => wfMessage( 'translate-taction-translate' )->text(),
			'href' => $translate->getLocalUrl( array( 'taction' => 'translate' ) + $params ),
			'class' => $alias === 'Translate' && $taction === 'translate' ? 'selected' : '',
		);
		$tabs['namespaces']['proofread'] = $data = array(
			'text' => wfMessage( 'translate-taction-proofread' )->text(),
			'href' => $translate->getLocalUrl( array( 'taction' => 'proofread' ) + $params ),
			'class' => $alias === 'Translate' && $taction === 'proofread' ? 'selected' : '',
		);

		// Limit only applies to the above
		unset( $params['limit'] );

		$tabs['views']['lstats'] = array(
			'text' => wfMessage( 'translate-taction-lstats' )->text(),
			'href' => $languagestats->getLocalUrl( $params ),
			'class' => $alias === 'LanguageStats' ? 'selected' : '',
		);
		$tabs['views']['mstats'] = array(
			'text' => wfMessage( 'translate-taction-mstats' )->text(),
			'href' => $messagegroupstats->getLocalUrl( $params ),
			'class' => $alias === 'MessageGroupStats' ? 'selected' : '',
		);
		$tabs['views']['export'] = array(
			'text' => wfMessage( 'translate-taction-export' )->text(),
			'href' => $translate->getLocalUrl( array( 'taction' => 'export' ) + $params ),
			'class' => $alias === 'Translate' && $taction === 'export' ? 'selected' : '',
		);

		return true;
	}
}
