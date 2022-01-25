<?php
/**
 * Contains logic for special page Special:MessageGroupStats.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\Statistics\ProgressStatsTableFactory;

/**
 * Implements includable special page Special:MessageGroupStats which provides
 * translation statistics for all languages for a group.
 *
 * @ingroup SpecialPage TranslateSpecialPage Stats
 */
class SpecialMessageGroupStats extends SpecialPage {
	/** @var StatsTable */
	private $table;
	/** @var array */
	private $targetValueName = [ 'group' ];
	/** Most of the displayed numbers added together at the bottom of the table. */
	private $totals;
	/**
	 * Flag to set if nothing to show.
	 * @var bool
	 */
	private $nothing = false;
	/**
	 * Flag to set if not all numbers are available.
	 * @var bool
	 */
	private $incomplete = false;
	/**
	 * Whether to hide rows which are fully translated.
	 * @var bool
	 */
	private $noComplete = true;
	/**
	 * Whether to hide rows which are fully untranslated.
	 * @var bool
	 */
	private $noEmpty = false;
	/** The target of stats: group id. */
	private $target;
	/**
	 * Whether to regenerate stats. Activated by action=purge in query params.
	 * @var bool
	 */
	private $purge;
	/** @var array */
	private $states;
	/** @var ProgressStatsTableFactory */
	private $progressStatsTableFactory;
	private $names;
	private $translate;
	/** @var int */
	private $numberOfShownLanguages;

	// region SpecialPage overrides

	public function __construct(
		ProgressStatsTableFactory $progressStatsTableFactory
	) {
		parent::__construct( 'MessageGroupStats' );
		$this->progressStatsTableFactory = $progressStatsTableFactory;
		$this->totals = MessageGroupStats::getEmptyStats();
	}

	public function getDescription() {
		return $this->msg( 'translate-mgs-pagename' )->text();
	}

	public function isIncludable() {
		return true;
	}

	protected function getGroupName() {
		return 'translation';
	}

	public function execute( $par ) {
		$request = $this->getRequest();

		$this->purge = $request->getVal( 'action' ) === 'purge';
		if ( $this->purge && !$request->wasPosted() ) {
			SpecialLanguageStats::showPurgeForm( $this->getContext() );
			return;
		}

		$this->table = $this->progressStatsTableFactory->newFromContext( $this->getContext() );

		$this->setHeaders();
		$this->outputHeader();

		$out = $this->getOutput();

		$out->addModules( 'ext.translate.special.languagestats' );
		$out->addModuleStyles( 'ext.translate.statstable' );

		$params = explode( '/', $par );

		if ( isset( $params[0] ) && trim( $params[0] ) ) {
			$this->target = $params[0];
		}

		if ( isset( $params[1] ) ) {
			$this->noComplete = (bool)$params[1];
		}

		if ( isset( $params[2] ) ) {
			$this->noEmpty = (bool)$params[2];
		}

		// Whether the form has been submitted, only relevant if not including
		$submitted = !$this->including() && $request->getVal( 'x' ) === 'D';

		// Default booleans to false if the form was submitted
		foreach ( $this->targetValueName as $key ) {
			$this->target = $request->getVal( $key, $this->target );
		}
		$this->noComplete = $request->getBool(
			'suppresscomplete',
			$this->noComplete && !$submitted
		);
		$this->noEmpty = $request->getBool( 'suppressempty', $this->noEmpty && !$submitted );

		if ( !$this->including() ) {
			$out->addHelpLink( 'Help:Extension:Translate/Statistics_and_reporting' );
			$this->addForm();
		}

		if ( $this->isValidValue( $this->target ) ) {
			$this->outputIntroduction();

			$stats = $this->loadStatistics( $this->target, MessageGroupStats::FLAG_CACHE_ONLY );
			$output = $this->getTable( $stats );
			if ( $this->incomplete ) {
				$out->wrapWikiMsg(
					"<div class='error'>$1</div>",
					'translate-langstats-incomplete'
				);
			}

			if ( $this->incomplete || $this->purge ) {
				DeferredUpdates::addCallableUpdate( function () {
					// Attempt to recache on the fly the missing stats, unless a
					// purge was requested, because that is likely to time out.
					// Even though this is executed inside a deferred update, it
					// counts towards the maximum execution time limit. If that is
					// reached, or any other failure happens, no updates at all
					// will be written into the database, as it does only single
					// update at the end. Hence we always add a job too, so that
					// even the slower updates will get done at some point. In
					// regular case (no purge), the job sees that the stats are
					// already updated, so it is not much of an overhead.
					$jobParams = $this->getCacheRebuildJobParameters( $this->target );
					$jobParams[ 'purge' ] = $this->purge;
					$job = MessageGroupStatsRebuildJob::newJob( $jobParams );
					TranslateUtils::getJobQueueGroup()->push( $job );

					// $this->purge is only true if request was posted
					if ( !$this->purge ) {
						$this->loadStatistics( $this->target );
					}
				} );
			}
			if ( $this->nothing ) {
				$out->wrapWikiMsg( "<div class='error'>$1</div>", 'translate-mgs-nothing' );
			}
			$out->addHTML( $output );
		} elseif ( $submitted ) {
			$this->invalidTarget();
		}
	}

	// endregion

	private function loadStatistics( $target, $flags = 0 ) {
		return MessageGroupStats::forGroup( $target, $flags );
	}

	private function getCacheRebuildJobParameters( $target ) {
		return [ 'groupid' => $target ];
	}

	private function isValidValue( $value ) {
		$group = MessageGroups::getGroup( $value );
		if ( $group ) {
			if ( MessageGroups::isDynamic( $group ) ) {
				/* Dynamic groups are not listed, but it is possible to end up
				 * on this page with a dynamic group by navigating from
				 * translation or proofreading activity or by giving group id
				 * of dynamic group explicitly. Ignore dynamic group to avoid
				 * throwing exceptions later. */
				$group = false;
			} else {
				$this->target = $group->getId();
			}
		}

		return (bool)$group;
	}

	private function invalidTarget() {
		$this->getOutput()->wrapWikiMsg(
			"<div class='error'>$1</div>",
			[ 'translate-mgs-invalid-group', $this->target ]
		);
	}

	private function outputIntroduction() {
		$priorityLangs = TranslateMetadata::get( $this->target, 'prioritylangs' );
		if ( $priorityLangs ) {
			$hasPriorityForce = TranslateMetadata::get( $this->target, 'priorityforce' ) === 'on';
			if ( $hasPriorityForce ) {
				$this->getOutput()->addWikiMsg( 'tpt-priority-languages-force', $priorityLangs );
			} else {
				$this->getOutput()->addWikiMsg( 'tpt-priority-languages', $priorityLangs );
			}
		}
	}

	/**
	 * If workflow states are configured, adds a workflow states column
	 */
	private function addWorkflowStatesColumn() {
		global $wgTranslateWorkflowStates;

		if ( $wgTranslateWorkflowStates ) {
			$this->states = $this->getWorkflowStates();

			// An array where keys are state names and values are numbers
			$this->table->addExtraColumn( $this->msg( 'translate-stats-workflow' ) );
		}
	}

	/**
	 * If workflow states are configured, adds a cell with the workflow state to the row,
	 * @param string $language Language tag
	 * @return string Html
	 */
	private function getWorkflowStateCell( string $language ) {
		// This will be set by addWorkflowStatesColumn if needed
		if ( !isset( $this->states ) ) {
			return '';
		}

		return $this->table->makeWorkflowStateCell(
			$this->states[$language] ?? null,
			MessageGroups::getGroup( $this->target ),
			$language
		);
	}

	private function addForm() {
		$formDescriptor = [
			'select' => [
				'type' => 'select',
				'name' => 'group',
				'id' => 'group',
				'label' => $this->msg( 'translate-mgs-group' )->text(),
				'options' => $this->getGroupOptions(),
				'default' => $this->target
			],
			'nocomplete-check' => [
				'type' => 'check',
				'name' => 'suppresscomplete',
				'id' => 'suppresscomplete',
				'label' => $this->msg( 'translate-mgs-nocomplete' )->text(),
				'default' => $this->noComplete,
			],
			'noempty-check' => [
				'type' => 'check',
				'name' => 'suppressempty',
				'id' => 'suppressempty',
				'label' => $this->msg( 'translate-mgs-noempty' )->text(),
				'default' => $this->noEmpty,
			]
		];

		$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() );

		/* Since these pages are in the tabgroup with Special:Translate,
		 * it makes sense to retain the selected group/language parameter
		 * on post requests even when not relevant to the current page. */
		$val = $this->getRequest()->getVal( 'language' );
		if ( $val !== null ) {
			$htmlForm->addHiddenField( 'language', $val );
		}

		$htmlForm
			->addHiddenField( 'x', 'D' ) // To detect submission
			->setMethod( 'get' )
			->setSubmitTextMsg( 'translate-mgs-submit' )
			->setWrapperLegendMsg( 'translate-mgs-fieldset' )
			->prepareForm()
			->displayForm( false );
	}

	private function getTable( $stats ) {
		$table = $this->table;

		$this->addWorkflowStatesColumn();
		$out = '';

		$this->numberOfShownLanguages = 0;
		$languages = array_keys(
			TranslateUtils::getLanguageNames( $this->getLanguage()->getCode() )
		);
		sort( $languages );
		$this->filterPriorityLangs( $languages, $this->target, $stats );
		foreach ( $languages as $code ) {
			if ( $table->isExcluded( $this->target, $code ) ) {
				continue;
			}
			$out .= $this->makeRow( $code, $stats );
		}

		if ( $out ) {
			$table->setMainColumnHeader( $this->msg( 'translate-mgs-column-language' ) );
			$out = $table->createHeader() . "\n" . $out;
			$out .= Html::closeElement( 'tbody' );

			$out .= Html::openElement( 'tfoot' );
			$out .= $table->makeTotalRow(
				$this->msg( 'translate-mgs-totals' )
					->numParams( $this->numberOfShownLanguages ),
				$this->totals
			);
			$out .= Html::closeElement( 'tfoot' );

			$out .= Html::closeElement( 'table' );

			return $out;
		} else {
			$this->nothing = true;

			return '';
		}
	}

	/**
	 * Filter an array of languages based on whether a priority set of
	 * languages present for the passed group. If priority languages are
	 * present, to that list add languages with more than 0% translation.
	 * @param array &$languages Array of Languages to be filtered
	 * @param string $group
	 * @param array $cache
	 */
	private function filterPriorityLangs( &$languages, $group, $cache ) {
		$filterLangs = TranslateMetadata::get( $group, 'prioritylangs' );
		if ( strlen( $filterLangs ) === 0 ) {
			// No restrictions, keep everything
			return;
		}
		$filter = array_flip( explode( ',', $filterLangs ) );
		foreach ( $languages as $id => $code ) {
			if ( isset( $filter[$code] ) ) {
				continue;
			}
			$translated = $cache[$code][1];
			if ( $translated === 0 ) {
				unset( $languages[$id] );
			}
		}
	}

	/**
	 * @param string $code
	 * @param array $cache
	 * @return string
	 */
	private function makeRow( $code, $cache ) {
		$stats = $cache[$code];
		$total = $stats[MessageGroupStats::TOTAL];
		$translated = $stats[MessageGroupStats::TRANSLATED];
		$fuzzy = $stats[MessageGroupStats::FUZZY];

		if ( $total === null ) {
			$this->incomplete = true;
			$extra = [];
		} else {
			if ( $this->noComplete && $fuzzy === 0 && $translated === $total ) {
				return '';
			}

			if ( $this->noEmpty && $translated === 0 && $fuzzy === 0 ) {
				return '';
			}

			// Skip below 2% if "don't show without translations" is checked.
			if ( $this->noEmpty && ( $translated / $total ) < 0.02 ) {
				return '';
			}

			if ( $translated === $total ) {
				$extra = [ 'action' => 'proofread' ];
			} else {
				$extra = [];
			}
		}
		$this->numberOfShownLanguages += 1;
		$this->totals = MessageGroupStats::multiAdd( $this->totals, $stats );

		$rowParams = [];
		if ( $this->numberOfShownLanguages % 2 === 0 ) {
			$rowParams[ 'class' ] = 'tux-statstable-even';
		}

		$out = "\t" . Html::openElement( 'tr', $rowParams );
		$out .= "\n\t\t" . $this->getMainColumnCell( $code, $extra );
		$out .= $this->table->makeNumberColumns( $stats );
		$out .= $this->getWorkflowStateCell( $code );

		$out .= "\n\t" . Html::closeElement( 'tr' ) . "\n";

		return $out;
	}

	/**
	 * @param string $code
	 * @param array $params
	 * @return string
	 */
	private function getMainColumnCell( $code, $params ) {
		if ( !isset( $this->names ) ) {
			$this->names = TranslateUtils::getLanguageNames( $this->getLanguage()->getCode() );
			$this->translate = SpecialPage::getTitleFor( 'Translate' );
		}

		$queryParameters = $params + [
			'group' => $this->target,
			'language' => $code
		];

		if ( isset( $this->names[$code] ) ) {
			$text = "$code: {$this->names[$code]}";
		} else {
			$text = $code;
		}
		$link = $this->getLinkRenderer()->makeKnownLink(
			$this->translate,
			$text,
			[],
			$queryParameters
		);

		return Html::rawElement( 'td', [], $link );
	}

	/** @return array */
	private function getWorkflowStates() {
		$db = wfGetDB( DB_REPLICA );
		$res = $db->select(
			'translate_groupreviews',
			[ 'tgr_state', 'tgr_lang' ],
			[ 'tgr_group' => $this->target ],
			__METHOD__
		);

		$states = [];
		foreach ( $res as $row ) {
			$states[$row->tgr_lang] = $row->tgr_state;
		}

		return $states;
	}

	/**
	 * Creates a simple message group options.
	 *
	 * @return array
	 */
	private function getGroupOptions() {
		$options = [];
		$groups = MessageGroups::getAllGroups();

		foreach ( $groups as $id => $class ) {
			if ( MessageGroups::getGroup( $id )->exists() ) {
				$options[$class->getLabel()] = $id;
			}
		}

		return $options;
	}
}
