<?php
/**
 * Contains logic for special page Special:Translate.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0+
 */

/**
 * Implements the core of Translate extension - a special page which shows
 * a list of messages in a format defined by Tasks.
 *
 * @ingroup SpecialPage TranslateSpecialPage
 */
class SpecialTranslate extends SpecialPage {
	/** @var TranslateTask */
	protected $task;

	/** @var MessageGroup */
	protected $group;

	protected $defaults;
	protected $nondefaults = array();
	protected $options;

	public function __construct() {
		parent::__construct( 'Translate' );
	}

	public function doesWrites() {
		return true;
	}

	protected function getGroupName() {
		return 'wiki';
	}

	/**
	 * Access point for this special page.
	 *
	 * @param null|string $parameters
	 * @throws ErrorPageError
	 */
	public function execute( $parameters ) {
		global $wgTranslateBlacklist, $wgContLang;

		$out = $this->getOutput();
		$out->addModuleStyles( array(
			'ext.translate.special.translate.styles',
			'jquery.uls.grid',
			'mediawiki.ui.button'
		) );

		$this->setHeaders();

		$request = $this->getRequest();
		// @todo Move to api or so
		if ( $parameters === 'editpage' ) {
			$editpage = TranslationEditPage::newFromRequest( $request );

			if ( $editpage ) {
				$editpage->execute();

				return;
			}
		}

		if ( !defined( 'ULS_VERSION' ) ) {
			throw new ErrorPageError(
				'translate-ulsdep-title',
				'translate-ulsdep-body'
			);
		}

		$this->setup( $parameters );
		$isBeta = self::isBeta( $request );

		if ( $this->options['group'] === '' || ( $isBeta && !$this->group ) ) {
			$this->groupInformation();

			return;
		}

		$errors = $this->getFormErrors();

		if ( $isBeta ) {
			$out->addModules( 'ext.translate.special.translate' );

			$out->addHTML( Html::openElement( 'div', array(
				'class' => 'grid ext-translate-container',
			) ) );

			$out->addHTML( $this->tuxSettingsForm( $errors ) );
			$out->addHTML( $this->messageSelector() );
		} else {
			$out->addModules( 'ext.translate.special.translate.legacy' );
			$out->addModuleStyles( 'ext.translate.legacy' );
			$out->addHelpLink( 'Help:Extension:Translate/Translation_example' );
			// Show errors nicely.
			$out->addHTML( $this->settingsForm( $errors ) );
		}

		if ( count( $errors ) ) {
			return;
		} else {
			$langCode = $this->options['language'];

			if ( $this->group->getSourceLanguage() === $langCode ) {
					$langName = TranslateUtils::getLanguageName(
						$langCode,
						$this->getLanguage()->getCode()
					);
					$reason = $this->msg( 'translate-page-disabled-source', $langName )->plain();
					$out->addWikiMsg( 'translate-page-disabled', $reason );
					if ( $isBeta ) {
						// Close div.ext-translate-container
						$out->addHTML( Html::closeElement( 'div' ) );
					}
					return;
			}

			$checks = array(
				$this->options['group'],
				strtok( $this->options['group'], '-' ),
				'*'
			);

			foreach ( $checks as $check ) {
				if ( isset( $wgTranslateBlacklist[$check][$langCode] ) ) {
					$reason = $wgTranslateBlacklist[$check][$langCode];
					$out->addWikiMsg( 'translate-page-disabled', $reason );
					if ( $isBeta ) {
						// Close div.ext-translate-container
						$out->addHTML( Html::closeElement( 'div' ) );
					}
					return;
				}
			}
		}

		$params = array( $this->getContext(), $this->task, $this->group, $this->options );
		if ( !Hooks::run( 'SpecialTranslate::executeTask', $params ) ) {
			return;
		}

		// Initialise and get output.
		if ( !$this->task ) {
			return;
		}

		$this->task->init( $this->group, $this->options, $this->nondefaults, $this->getContext() );
		$output = $this->task->execute();

		$description = $this->getGroupDescription( $this->group );

		$taskid = $this->options['task'];
		if ( in_array( $taskid, array( 'untranslated', 'reviewall' ), true ) ) {
			$hasOptional = count( $this->group->getTags( 'optional' ) );
			if ( $hasOptional ) {
				$linktext = $this->msg( 'translate-page-description-hasoptional-open' )->escaped();
				$params = array( 'task' => 'optional' ) + $this->nondefaults;
				$link = Linker::linkKnown( $this->getPageTitle(), $linktext, array(), $params );
				$note = $this->msg( 'translate-page-description-hasoptional' )
					->rawParams( $link )->parseAsBlock();

				if ( $description ) {
					$description .= '<br />' . $note;
				} else {
					$description = $note;
				}
			}
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
				$priorityReason = "\n\n" . $this->msg(
					'tpt-discouraged-language-reason',
					Xml::element( 'span',
						// The reason is probably written in the content language
						array(
							'lang' => $wgContLang->getHtmlCode(),
							'dir' => $wgContLang->getDir(),
						),
						$priorityReason
					)
				)->parse();
			}

			$description .= Html::rawElement( 'div',
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
			$description = Xml::fieldset(
				$this->msg( 'translate-page-description-legend' )->text(),
				$description,
				array( 'class' => 'mw-sp-translate-description' )
			);
		}

		if ( $isBeta ) {
			$out->addHTML( $output );
		} else {
			$out->addHTML( $description . $output );
		}

		if ( $isBeta ) {
			$out->addHTML( Html::closeElement( 'div' ) );
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
		$request = $this->getRequest();
		$isBeta = self::isBeta( $request );

		$defaults = array(
			/* str  */'taction'  => 'translate',
			/* str  */'task'     => $isBeta ? 'custom' : 'untranslated',
			/* str  */'language' => $this->getLanguage()->getCode(),
			/* str  */'group'    => $isBeta ? '!additions' : '',
			/* str  */'offset'   => '', // Used to be int, now str
			/* int  */'limit'    => $isBeta ? 0 : 100,
			/* int  */'optional' => '0',
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
				$r = isset( $pars[$v] ) ? (bool)$pars[$v] : $defaults[$v];
				$r = $request->getBool( $v, $r );
			} elseif ( is_int( $t ) ) {
				$r = isset( $pars[$v] ) ? (int)$pars[$v] : $defaults[$v];
				$r = $request->getInt( $v, $r );
			} elseif ( is_string( $t ) ) {
				$r = isset( $pars[$v] ) ? (string)$pars[$v] : $defaults[$v];
				$r = $request->getText( $v, $r );
			}

			if ( !isset( $r ) ) {
				throw new MWException( '$r was not set' );
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
				// Redirect old export URLs to Special:ExportTranslations
				$params = array();
				if ( isset( $nondefaults['group'] ) ) {
					$params['group'] = $nondefaults['group'];
				}
				if ( isset( $nondefaults['language'] ) ) {
					$params['language'] = $nondefaults['language'];
				}
				if ( isset( $nondefaults['task'] ) ) {
					$params['format'] = $nondefaults['task'];
				}

				$export = SpecialPage::getTitleFor( 'ExportTranslations' )->getLocalURL( $params );
				$this->getOutput()->redirect( $export );
			}
		}

		if ( $isBeta ) {
			/* @todo fix all the places in Translate to create correct links.
			 * The least effort way is to change them once we totally drop the
			 * old UI. The penalty is only http redirect in some cases. More
			 * effort would be to create utilities like makeTranslationLink
			 * and makeProofreadLink.
			 */
			$this->rewriteLegacyUrls( $nondefaults );
		}

		$this->defaults = $defaults;
		$this->nondefaults = $nondefaults;
		Hooks::run( 'TranslateGetSpecialTranslateOptions', array( &$defaults, &$nondefaults ) );

		$this->options = $nondefaults + $defaults;
		$this->group = MessageGroups::getGroup( $this->options['group'] );
		if ( $this->group ) {
			$this->options['group'] = $this->group->getId();
		}
		$this->task = TranslateTasks::getTask( $this->options['task'] );

		if ( $this->group && MessageGroups::isDynamic( $this->group ) ) {
			$this->group->setLanguage( $this->options['language'] );
		}
	}

	protected function rewriteLegacyUrls( $params ) {
		if (
			!isset( $params['task'] ) &&
			isset( $params['taction'] ) && $params['taction'] === 'proofread'
		) {
			$params['task'] = 'acceptqueue';
		}

		if ( !isset( $params['task'] ) || $params['task'] === 'custom' ) {
			return;
		}

		// Not used in TUX
		unset( $params['taction'], $params['limit'], $params['offset'] );

		$out = $this->getOutput();

		switch ( $params['task'] ) {
			case 'reviewall':
			case 'acceptqueue':
				// @todo handle these two separately
				unset( $params['task'] );
				$params['action'] = 'proofread';
				$out->redirect( $this->getPageTitle()->getLocalURL( $params ) );
				break;

			case 'view':
				unset( $params['task'] );
				$params['filter'] = '';
				$out->redirect( $this->getPageTitle()->getLocalURL( $params ) );
				break;

			// Optional does not directly map to the new UI.
			// Handle it as untranslated with optional filter.
			/** @noinspection PhpMissingBreakStatementInspection */
			case 'optional':
				$params['optional'] = 1;
			case 'untranslated':
				unset( $params['task'] );
				$params['filter'] = '!translated';
				$out->redirect( $this->getPageTitle()->getLocalURL( $params ) );
				break;
		}
	}

	protected function settingsForm( $errors ) {
		global $wgScript;

		$taction = $this->options['taction'];

		$selectors = array(
			'group' => $this->groupSelector(),
			'language' => $this->languageSelector(),
			'limit' => $this->limitSelector(),
		);

		$options = array();
		foreach ( $selectors as $g => $selector ) {
			// Give grep a chance to find the usages:
			// translate-page-group, translate-page-language, translate-page-limit
			$options[] = self::optionRow(
				$this->msg( 'translate-page-' . $g )->escaped(),
				$selector,
				array_key_exists( $g, $errors ) ? $errors[$g] : null
			);
		}

		if ( $taction === 'proofread' ) {
			$extra = $this->taskLinks( array( 'acceptqueue', 'reviewall' ) );
		} elseif ( $taction === 'translate' ) {
			$extra = $this->taskLinks( array( 'view', 'untranslated', 'optional' ) );
		} else {
			$extra = '';
		}

		$nonEssential = Html::rawElement(
			'span',
			array( 'class' => 'mw-sp-translate-nonessential' ),
			implode( '', $options )
		);

		$button = Xml::submitButton( $this->msg( 'translate-submit' )->text() );

		$formAttributes = array( 'class' => 'mw-sp-translate-settings' );
		if ( $this->group ) {
			$formAttributes['data-grouptype'] = get_class( $this->group );
		}
		$form =
			Html::openElement( 'fieldset', $formAttributes ) .
				Html::element( 'legend', array(), $this->msg( 'translate-page-settings-legend' )->text() ) .
				Html::openElement( 'form', array( 'action' => $wgScript, 'method' => 'get' ) ) .
				Html::hidden( 'title', $this->getPageTitle()->getPrefixedText() ) .
				Html::hidden( 'taction', $this->options['taction'] ) .
				"$nonEssential\n$extra\n$button\n" .
				Html::closeElement( 'form' ) .
				Html::closeElement( 'fieldset' );

		return $form;
	}

	protected function tuxSettingsForm() {
		$nojs = Html::element(
			'noscript',
			array( 'class' => 'tux-nojs errorbox' ),
			$this->msg( 'tux-nojs' )->plain()
		);

		$attrs = array( 'class' => 'row tux-editor-header' );
		$selectors = $this->tuxGroupSelector() .
			$this->tuxLanguageSelector() .
			$this->tuxGroupDescription() .
			$this->tuxWorkflowSelector() .
			$this->tuxGroupWarning();

		return Html::rawElement( 'div', $attrs, $selectors ) . $nojs;
	}

	protected function messageSelector() {
		$output = Html::openElement( 'div', array( 'class' => 'row tux-messagetable-header' ) );
		$output .= Html::openElement( 'div', array( 'class' => 'nine columns' ) );
		$output .= Html::openElement( 'ul', array( 'class' => 'row tux-message-selector' ) );
		$userId = $this->getUser()->getId();
		$tabs = array(
			'all' => '',
			'untranslated' => '!translated',
			'outdated' => 'fuzzy',
			'translated' => 'translated',
			'unproofread' => "translated|!reviewer:$userId|!last-translator:$userId",
		);

		$params = $this->nondefaults;
		$params['task'] = 'custom';

		foreach ( $tabs as $tab => $filter ) {
			// Possible classes and messages, for grepping:
			// tux-tab-all
			// tux-tab-untranslated
			// tux-tab-outdated
			// tux-tab-translated
			// tux-tab-unproofread
			$tabClass = "tux-tab-$tab";
			$taskParams = array( 'filter' => $filter ) + $params;
			ksort( $taskParams );
			$href = $this->getPageTitle()->getLocalURL( $taskParams );
			$link = Html::element( 'a', array( 'href' => $href ), $this->msg( $tabClass )->text() );
			$output .= Html::rawElement( 'li', array(
				'class' => 'column ' . $tabClass,
				'data-filter' => $filter,
				'data-title' => $tab,
			), $link );
		}

		// Check boxes for the "more" tab.
		// The array keys are used as the name attribute of the checkbox.
		// in the id attribute as tux-option-KEY,
		// and and also for the data-filter attribute.
		// The message is shown as the check box's label.
		$options = array(
			'optional' => $this->msg( 'tux-message-filter-optional-messages-label' )->escaped(),
		);

		$container = Html::openElement( 'ul', array( 'class' => 'column tux-message-selector' ) );
		foreach ( $options as $optFilter => $optLabel ) {
			$container .= Html::rawElement( 'li',
				array( 'class' => 'column' ),
				Xml::checkLabel(
					$optLabel,
					$optFilter,
					"tux-option-$optFilter",
					isset( $this->nondefaults[$optFilter] ),
					array( 'data-filter' => $optFilter )
				)
			);
		}

		$container .= Html::closeElement( 'ul' );

		// @todo FIXME: Hard coded "ellipsis".
		$output .= Html::openElement( 'li', array( 'class' => 'column more' ) ) .
			'...' .
			$container .
			Html::closeElement( 'li' );

		$output .= Html::closeElement( 'ul' );
		$output .= Html::closeElement( 'div' ); // close nine columns
		$output .= Html::openElement( 'div', array( 'class' => 'three columns' ) );
		$output .= Html::openElement( 'div', array( 'class' => 'tux-message-filter-wrapper' ) );
		$output .= Html::element( 'input', array(
			'class' => 'tux-message-filter-box',
			'type' => 'search',
		) );
		$output .= Html::closeElement( 'div' ); // close tux-message-filter-wrapper

		$output .= Html::closeElement( 'div' ); // close three columns

		$output .= Html::closeElement( 'div' ); // close the row

		return $output;
	}

	protected function tuxGroupSelector() {
		$group = MessageGroups::getGroup( $this->options['group'] );

		$groupClass = array( 'grouptitle', 'grouplink' );
		if ( $group instanceof AggregateMessageGroup ) {
			$groupClass[] = 'tux-breadcrumb__item--aggregate';
		}

		// @todo FIXME The selector should have expanded parent-child lists
		$output = Html::openElement( 'div', array(
			'class' => 'eight columns tux-breadcrumb',
			'data-language' => $this->options['language'],
		) ) .
			Html::element( 'span',
				array( 'class' => 'grouptitle' ),
				$this->msg( 'translate-msggroupselector-projects' )->text()
			) .
			Html::element( 'span',
				array( 'class' => 'grouptitle grouplink tux-breadcrumb__item--aggregate' ),
				$this->msg( 'translate-msggroupselector-search-all' )->text()
			) .
			Html::element( 'span',
				array(
					'class' => $groupClass,
					'data-msggroupid' => $this->options['group'],
				),
				$group->getLabel()
			) .
			Html::closeElement( 'div' );

		return $output;
	}

	protected function tuxLanguageSelector() {
		// Changes here must also be reflected when the language
		// changes on the client side
		global $wgTranslateDocumentationLanguageCode;

		if ( $this->options['language'] === $wgTranslateDocumentationLanguageCode ) {
			// The name will be displayed in the UI language,
			// so use for lang and dir
			$targetLang = $this->getLanguage();
			$targetLangName = $this->msg( 'translate-documentation-language' )->text();
		} else {
			$targetLang = Language::factory( $this->options['language'] );
			$targetLangName = Language::fetchLanguageName( $this->options['language'] );
		}

		// No-break space is added for spacing after the label
		// and to ensure separation of words (in Arabic, for example)
		return Html::rawElement( 'div',
			array( 'class' => 'four columns ext-translate-language-selector' ),
			Html::element( 'span',
				array( 'class' => 'ext-translate-language-selector-label' ),
				$this->msg( 'tux-languageselector' )->text()
			) .
				'&#160;' . // nbsp
				Html::element( 'span',
					array(
						'class' => 'uls',
						'lang' => $targetLang->getHtmlCode(),
						'dir' => $targetLang->getDir(),
					),
					$targetLangName
				)
		);
	}

	protected function tuxGroupDescription() {
		return Html::rawElement(
			'div',
			array( 'class' => 'twelve columns description' ),
			$this->getGroupDescription( $this->group )
		);
	}

	protected function tuxGroupWarning() {
		// Initialize an empty warning box to be filled client-side.
		return Html::element(
			'div',
			array( 'class' => 'twelve columns group-warning' ),
			''
		);
	}

	/**
	 * @param $label string
	 * @param $option string
	 * @param $error string Html
	 * @return string
	 */
	private static function optionRow( $label, $option, $error = null ) {
		return "<label>$label&nbsp;$option</label>" .
			( $error ?
				Html::rawElement( 'span', array( 'class' => 'mw-sp-translate-error' ), $error ) :
				''
			) . ' ';
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

			// Give grep a chance to find the usages:
			// translate-taskui-view, translate-taskui-untranslated, translate-taskui-optional,
			// translate-taskui-acceptqueue, translate-taskui-reviewall,
			return $sep . Html::rawElement( 'label', array(),
				Xml::radio( 'task', $id, true ) . ' ' .
					$this->msg( "translate-taskui-$id" )->escaped()
			);
		} else {
			$output = '';

			foreach ( $tasks as $id ) {
				// Give grep a chance to find the usages:
				// translate-taskui-view, translate-taskui-untranslated, translate-taskui-optional,
				// translate-taskui-acceptqueue, translate-taskui-reviewall,
				$output .= Html::rawElement( 'label', array(),
					Xml::radio( 'task', $id, $this->options['task'] === $id ) . ' ' .
						$this->msg( "translate-taskui-$id" )->escaped()
				) . ' ';
			}

			return $sep . $output;
		}
	}

	protected function groupSelector() {
		$groups = MessageGroups::getAllGroups();
		uasort( $groups, array( 'MessageGroups', 'groupLabelSort' ) );
		$dynamic = MessageGroups::getDynamicGroups();
		$groups = array_keys( array_merge( $dynamic, $groups ) );

		$selected = $this->options['group'];

		$selector = new XmlSelect( 'group', 'group' );
		$selector->setDefault( $selected );

		foreach ( $groups as $id ) {
			$group = MessageGroups::getGroup( $id );
			$hide = MessageGroups::getPriority( $group ) === 'discouraged';

			if ( !$group->exists() || ( $hide && $id !== $selected ) ) {
				continue;
			}

			$selector->addOption( $group->getLabel(), $id );
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
			$selector->addOption(
				$this->msg( 'translate-page-limit-option' )->numParams( $count )->text(),
				$count
			);
		}

		return $selector->getHTML();
	}

	protected function getGroupDescription( MessageGroup $group ) {
		$description = $group->getDescription( $this->getContext() );
		if ( $description !== null ) {
			return $this->getOutput()->parse( $description, false );
		}

		return '';
	}

	/**
	 * This function renders the default list of groups when no parameters
	 * are passed.
	 */
	public function groupInformation() {
		$output = $this->getOutput();

		// If we get here in the TUX mode, it means that invalid group
		// was requested. There is default group for no params case.
		if ( self::isBeta( $this->getRequest() ) ) {
			$output->addHTML( Html::rawElement(
				'div',
				array( 'class' => 'twelve columns group-warning' ),
				$this->msg( 'tux-translate-page-no-such-group' )->parse()
			) );
		}

		$output->addHTML(
			Html::openElement( 'div', array(
				'class' => 'eight columns tux-breadcrumb',
				'data-language' => $this->options['language'],
			) ) .
				'<span class="grouptitle">' .
				$this->msg( 'translate-msggroupselector-projects' )->escaped() .
				'</span>
			<span class="grouptitle grouplink tail">' .
				$this->msg( 'translate-msggroupselector-search-all' )->escaped() .
				'</span>
			</div>'
		);
	}

	protected function tuxWorkflowSelector() {
		return Html::element( 'div', array( 'class' => 'tux-workflow twelve columns' ) );
	}

	/**
	 * Adds the task-based tabs on Special:Translate and few other special pages.
	 * Hook: SkinTemplateNavigation::SpecialPage
	 * @since 2012-02-10
	 */
	public static function tabify( Skin $skin, array &$tabs ) {
		$title = $skin->getTitle();
		list( $alias, $sub ) = SpecialPageFactory::resolveAlias( $title->getText() );

		$pagesInGroup = array( 'Translate', 'LanguageStats', 'MessageGroupStats' );
		if ( !in_array( $alias, $pagesInGroup, true ) ) {
			return true;
		}

		$skin->getOutput()->addModuleStyles( 'ext.translate.tabgroup' );

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

		$request = $skin->getRequest();
		// However, query string params take precedence
		$params['language'] = $request->getVal( 'language' );
		$params['group'] = $request->getVal( 'group' );

		$taction = $request->getVal( 'taction', 'translate' );

		$translate = SpecialPage::getTitleFor( 'Translate' );
		$languagestats = SpecialPage::getTitleFor( 'LanguageStats' );
		$messagegroupstats = SpecialPage::getTitleFor( 'MessageGroupStats' );

		// Clear the special page tab that might be there already
		$tabs['namespaces'] = array();

		$tabs['namespaces']['translate'] = array(
			'text' => wfMessage( 'translate-taction-translate' )->text(),
			'href' => $translate->getLocalURL( $params ),
			'class' => 'tux-tab',
		);

		if ( $alias === 'Translate' && $taction === 'translate' ) {
			$tabs['namespaces']['translate']['class'] .= ' selected';
		}

		if ( !self::isBeta( $request ) ) {
			$tabs['namespaces']['proofread'] = array(
				'text' => wfMessage( 'translate-taction-proofread' )->text(),
				'href' => $translate->getLocalURL( array( 'taction' => 'proofread' ) + $params ),
				'class' => 'tux-tab',
			);

			if ( $alias === 'Translate' && $taction === 'proofread' ) {
				$tabs['namespaces']['proofread']['class'] .= ' selected';
			}
		}

		$tabs['views']['lstats'] = array(
			'text' => wfMessage( 'translate-taction-lstats' )->text(),
			'href' => $languagestats->getLocalURL( $params ),
			'class' => 'tux-tab',
		);
		if ( $alias === 'LanguageStats' ) {
			$tabs['views']['lstats']['class'] .= ' selected';
		}

		$tabs['views']['mstats'] = array(
			'text' => wfMessage( 'translate-taction-mstats' )->text(),
			'href' => $messagegroupstats->getLocalURL( $params ),
			'class' => 'tux-tab',
		);

		if ( $alias === 'MessageGroupStats' ) {
			$tabs['views']['mstats']['class'] .= ' selected';
		}

		$tabs['views']['export'] = array(
			'text' => wfMessage( 'translate-taction-export' )->text(),
			'href' => SpecialPage::getTitleFor( 'ExportTranslations' )->getLocalURL( $params ),
			'class' => 'tux-tab',
		);

		return true;
	}

	public static function isBeta( WebRequest $request ) {
		$tux = $request->getVal( 'tux', null );

		if ( $tux === null ) {
			$tux = $request->getCookie( 'tux', null, true );
		} elseif ( $tux ) {
			$request->response()->setCookie( 'tux', 1 );
		} else {
			$request->response()->setCookie( 'tux', 0 );
		}

		return $tux;
	}
}
