<?php
/**
 * Contains logic for special page Special:Translate.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2006-2012 Niklas Laxström, Siebrand Mazeland
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
		global $wgTranslateBlacklist, $wgContLang;

		$out = $this->getOutput();
		$out->addModules( 'ext.translate.special.translate' );

		$this->setHeaders();

		// @todo Move to api or so
		if ( $parameters === 'editpage' ) {
			$editpage = TranslationEditPage::newFromRequest( $this->getRequest() );

			if ( $editpage ) {
				$editpage->execute();
				return;
			}
		}

		$this->setup( $parameters );

		if ( $this->options['group'] === '' ) {
			TranslateUtils::addSpecialHelpLink( $out, 'Help:Extension:Translate/Translation_example' );
			$this->groupInformation();
			return;
		}

		TranslateUtils::addSpecialHelpLink( $out, 'Help:Extension:Translate/Translation_example' );
		// Show errors nicely.
		$errors = $this->getFormErrors();
		$out->addHTML( $this->settingsForm( $errors ) );

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
					$out->addWikiMsg( 'translate-page-disabled', $reason );
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
			$out->disable();
			header( 'Content-type: text/plain; charset=UTF-8' );
			echo $output;
		} else {
			$description = $this->getGroupDescription( $this->group );

			$taskid = $this->options['task'];
			if ( in_array( $taskid, array( 'untranslated', 'reviewall' ), true ) ) {
				$hasOptional = count( $this->group->getTags( 'optional' ) );
				if ( $hasOptional ) {
					$linktext = $this->msg( 'translate-page-description-hasoptional-open' )->escaped();
					$params = array( 'task' => 'optional' ) + $this->nondefaults;
					$link = Linker::link( $this->getTitle(), $linktext, array(), $params );
					$note = $this->msg( 'translate-page-description-hasoptional' )->rawParams( $link )->parseAsBlock();

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

			$groupId = $this->group->getId();
			// PHP is such an awesome language
			$priorityLangs = TranslateMetadata::get( $groupId, 'prioritylangs' );
			$priorityLangs = array_flip( array_filter( explode( ',', $priorityLangs ) ) );
			$priorityLangsCount = count( $priorityLangs );
			if ( $priorityLangsCount && !isset( $priorityLangs[$this->options['language']] ) ) {
				$priorityForce = TranslateMetadata::get( $groupId, 'priorityforce' );
				if ( $priorityForce === 'on' ) {
					// Hide table
					$this->paging['count'] = 0;
					$priorityMessageClass = 'errorbox';
					$priorityMessageKey = 'tpt-discouraged-language-force';
				} else {
					$priorityMessageClass = 'warningbox';
					$priorityMessageKey = 'tpt-discouraged-language';
				}

				$priorityLanguageNames = array();
				$languageNames = TranslateUtils::getLanguageNames( $this->getLanguage()->getCode() );
				foreach ( array_keys( $priorityLangs ) as $langCode ) {
					$priorityLanguageNames[] = $languageNames[$langCode];
				}

				$priorityReason = TranslateMetadata::get( $groupId, 'priorityreason' );
				if ( $priorityReason !== '' ) {
					// @todo Output format is implicit.
					$priorityReason = "\n\n" . $this->msg(
						'tpt-discouraged-language-reason',
						Xml::element( 'span',
							// The reason is probably written in the content language
							array(
								'lang' => $wgContLang->getCode(),
								'dir' => $wgContLang->getDir(),
							),
							$priorityReason
						)
					);
				}

				$description .= Html::RawElement( 'div',
					array( 'class' => $priorityMessageClass ),
					$this->msg(
						$priorityMessageKey,
						'', // param formerly used for reason, now empty
						$languageNames[$this->options['language']],
						$this->getLanguage()->listToText( $priorityLanguageNames )
					)->parseAsBlock() . $priorityReason
				);
			}
			if ( $description ) {
				$description = Xml::fieldset( $this->msg( 'translate-page-description-legend' )->text(), $description );
			}

			$links = $this->doStupidLinks();

			if ( $this->paging['count'] === 0 ) {
				$out->addHTML( $description . $links );
			} elseif ( $this->paging['count'] === $this->paging['total'] ) {
				$out->addHTML( $description . $output . $links );
			} else {
				$out->addHTML( $description . $links . $output . $links );
			}
		}
	}

	/**
	 * Returns array of errors in the form parameters.
	 */
	protected function getFormErrors() {
		$errors = array();

		$codes = TranslateUtils::getLanguageNames( 'en' );
		if ( !$this->options['language'] || !isset( $codes[$this->options['language']] ) ) {
			$errors['language'] = $this->msg( 'translate-page-no-such-language' )->text();
			$this->options['language'] = $this->defaults['language'];
		}

		if ( !$this->group instanceof MessageGroup ) {
			$errors['group'] = $this->msg( 'translate-page-no-such-group' )->text();
			$this->options['group'] = $this->defaults['group'];
		} else {
			$languages = $this->group->getTranslatableLanguages();
			if ( $languages !== null && !isset( $languages[$this->options['language']] ) ) {
				$errors['language'] = $this->msg( 'translate-language-disabled' )->text();
			}
		}
		return $errors;
	}

	protected function setup( $parameters ) {
		$defaults = array(
		/* str  */ 'taction'  => 'translate',
		/* str  */ 'task'     => 'untranslated',
		/* str  */ 'sort'     => 'normal',
		/* str  */ 'language' => $this->getLanguage()->getCode(),
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

		$request = $this->getRequest();
		foreach ( $defaults as $v => $t ) {
			if ( is_bool( $t ) ) {
				$r = isset( $pars[$v] ) ? (bool) $pars[$v] : $defaults[$v];
				$r = $request->getBool( $v, $r );
			} elseif ( is_int( $t ) ) {
				$r = isset( $pars[$v] ) ? (int) $pars[$v] : $defaults[$v];
				$r = $request->getInt( $v, $r );
			} elseif ( is_string( $t ) ) {
				$r = isset( $pars[$v] ) ? (string) $pars[$v] : $defaults[$v];
				$r = $request->getText( $v, $r );
			}

			wfAppendToArrayIfNotDefault( $v, $r, $defaults, $nondefaults );
		}

		// Fix defaults based on what we got
		if ( isset( $nondefaults['taction'] ) ) {
			if ( $nondefaults['taction'] === 'proofread' ) {
				if ( $this->getUser()->isAllowed( 'translate-messagereview' ) ) {
					$defaults['task'] = 'acceptqueue';
				} else {
					$defaults['task'] = 'reviewall';
				}
			} elseif ( $nondefaults['taction'] === 'export' ) {
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
		global $wgScript;
		$user = $this->getUser();

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
				$this->msg( 'translate-page-' . $g )->escaped(),
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

		$button = Xml::submitButton( $this->msg( 'translate-submit' )->text() );

		$form =
			Html::openElement( 'fieldset', array( 'class' => 'mw-sp-translate-settings' ) ) .
				Html::element( 'legend', null, $this->msg( 'translate-page-settings-legend' )->text() ) .
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
		$user = $this->getUser();

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
			return $sep . $this->msg( 'translate-taction-disabled' )->escaped();
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
				$this->msg( "translate-taskui-$id" )->escaped()
			);
		} else {
			$output = '';
			foreach ( $tasks as $index => $id ) {
				$output .= Html::rawElement( 'label', null,
				Xml::radio( 'task', $id, $this->options['task'] === $id ) . ' ' .
				$this->msg( "translate-taskui-$id" )->escaped()
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

			if ( !$group->exists() || ( $hide && $id !== $selected ) ) {
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
		return TranslateUtils::languageSelector(
			$this->getLanguage()->getCode(),
			$this->options['language']
		);
	}

	protected function limitSelector() {
		$items = array( 100, 1000, 5000 );
		$selector = new XmlSelect( 'limit', 'limit' );
		$selector->setDefault( $this->options['limit'] );

		foreach ( $items as $count ) {
			$selector->addOption( $this->msg( 'translate-page-limit-option' )->numParams( $count )->text(), $count );
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
		if ( $this->paging === null ) {
			return '';
		}

		$start = $this->paging['start'] + 1 ;
		$stop  = $start + $this->paging['count'] - 1;
		$total = $this->paging['total'];

		$allInThisPage = $start === 1 && $total <= $this->options['limit'];

		if ( $this->paging['count'] === 0 ) {
			$navigation = $this->msg( 'translate-page-showing-none' )->parse();
		} elseif ( $allInThisPage ) {
			$navigation = $this->msg( 'translate-page-showing-all' )->numParams( $total )->parse();
		} else {
			$previous = $this->msg( 'translate-prev' )->escaped();
			if ( $this->options['offset'] > 0 ) {
				$offset = max( 0, $this->options['offset'] - $this->options['limit'] );
				$previous = $this->makeOffsetLink( $previous, $offset );
			}

			$nextious = $this->msg( 'translate-next' )->escaped();

			if ( $this->paging['total'] != $this->paging['start'] + $this->paging['count'] ) {
				$offset = $this->options['offset'] + $this->options['limit'];
				$nextious = $this->makeOffsetLink( $nextious, $offset );
			}

			$start = $this->paging['start'] + 1 ;
			$stop  = $start + $this->paging['count'] - 1;
			$total = $this->paging['total'];

			$navigation  = $this->msg( 'translate-page-showing' )->numParams( $start, $stop, $total )->parse();
			$navigation .= ' ';
			$navigation .= $this->msg( 'translate-page-paging-links' )->rawParams( $previous, $nextious )->escaped();
		}

		return
			Html::openElement( 'fieldset' ) .
				Html::element( 'legend', null, $this->msg( 'translate-page-navigation-legend' )->text() ) .
				$navigation .
			Html::closeElement( 'fieldset' );
	}

	private function makeOffsetLink( $label, $offset ) {
		$query = array_merge(
			$this->nondefaults,
			array( 'offset' => $offset )
		);

		$link = Linker::link(
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
			return $this->getOutput()->parse( $description, false );
		}

		return '';
	}

	/**
	 * This funtion renders the default list of groups when no parameters
	 * are passed.
	 */
	public function groupInformation() {
		$structure = MessageGroups::getGroupStructure();
		$output = $this->getOutput();
		if ( !$structure ) {
			$output->addWikiMsg( 'translate-grouplisting-empty' );
			return;
		}

		$output->addWikiMsg( 'translate-grouplisting' );

		$out = '';
		foreach ( $structure as $blocks ) {
			$out .= $this->formatGroupInformation( $blocks );
		}

		$output->addHtml( Html::rawElement( 'table', array( 'class' => 'mw-sp-translate-grouplist wikitable' ), $out ) );
	}

	public function formatGroupInformation( $blocks, $level = 2 ) {
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

		$label = Linker::link(
			$title,
			htmlspecialchars( $block->getLabel() ),
			array(),
			$queryParams
		);

		$desc = $this->getGroupDescription( $block );
		$hasSubblocks = is_array( $blocks ) && count( $blocks );

		$subid = Sanitizer::escapeId( "mw-subgroup-$id" );

		if ( $hasSubblocks ) {
			$msg = $this->msg( 'translate-showsub' )->numParams( count( $blocks ) )->text();
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
		$stateConfig = $this->group->getWorkflowConfiguration();
		if ( !$stateConfig ) {
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

		$options = array();
		$stateConfig = array_merge(
			array( '' => array( 'right' => 'impossible-right' ) ),
			$stateConfig
		);

		$user = $this->getUser();
		if ( $user->isAllowed( 'translate-groupreview' ) ) {
			// Add an option for every state
			foreach ( $stateConfig as $state => $config ) {
				$stateMessage = $this->msg( "translate-workflow-state-$state" );
				$stateText = $stateMessage->isBlank() ? $state : $stateMessage->text();

				$attributes = array(
					'value' => $state,
				);

				if ( $state === strval( $current ) ) {
					$attributes['selected'] = 'selected';
				}

				if ( is_array( $config ) && isset( $config['right'] )
					&& !$user->isAllowed( $config['right'] ) )
				{
					// Grey out the forbidden option
					$attributes['disabled'] = 'disabled';
				}

				$options[] = Html::element( 'option', $attributes, $stateText );
			}
			$stateIndicator = Html::rawElement( 'select',
				array(
					'class' => 'mw-translate-workflowselector',
					'name' => 'workflow',
				),
				implode( "\n", $options )
			);

			$setButtonAttributes = array(
				'type' => 'button',
				'id' => 'mw-translate-workflowset',
				'data-token' => ApiGroupReview::getToken( 0, '' ),
				'data-group' => $this->options['group'],
				'data-language' => $this->options['language'],
				'style' => 'visibility: hidden;',
				'value' => 'Set',
			);
			$stateIndicator .= Html::element( 'input', $setButtonAttributes );
		} elseif ( strval( $current ) !== '' ) {
			$stateIndicator = $current;
		} else {
			$stateIndicator = $this->msg( 'translate-workflow-state-' )->escaped();
		}

		$message = $this->msg( 'translate-workflowstatus' )->rawParams( $stateIndicator );
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
			} elseif ( $alias === 'LanguageStats' ) {
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
