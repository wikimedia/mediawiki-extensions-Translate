<?php
/**
 * Contains logic for special page Special:LanguageStats.
 *
 * @file
 * @author Siebrand Mazeland
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Implements includable special page Special:LanguageStats which provides
 * translation statistics for all defined message groups.
 *
 * Loosely based on the statistics code in phase3/maintenance/language
 *
 * Use {{Special:LanguageStats/nl/1}} to show for 'nl' and suppress completely
 * translated groups.
 *
 * @ingroup SpecialPage TranslateSpecialPage Stats
 */
class SpecialLanguageStats extends SpecialPage {
	/**
	 * @var StatsTable
	 */
	protected $table;

	/**
	 * @var Array
	 */
	protected $targetValueName = [ 'code', 'language' ];

	/**
	 * Most of the displayed numbers added together at the bottom of the table.
	 */
	protected $totals;

	/**
	 * Flag to set if nothing to show.
	 * @var bool
	 */
	protected $nothing = false;

	/**
	 * Flag to set if not all numbers are available.
	 * @var bool
	 */
	protected $incomplete = false;

	/**
	 * Whether to hide rows which are fully translated.
	 * @var bool
	 */
	protected $noComplete = true;

	/**
	 * Whether to hide rows which are fully untranslated.
	 * @var bool
	 */
	protected $noEmpty = false;

	/**
	 * The target of stats, language code or group id.
	 */
	protected $target;

	/**
	 * Whether to regenerate stats. Activated by action=purge in query params.
	 * @var bool
	 */
	protected $purge;

	/**
	 * Helper variable to avoid overcounting message groups that appear
	 * multiple times in the list with different parents. Aggregate message
	 * group stats are always excluded from totals.
	 *
	 * @var array
	 */
	protected $statsCounted = [];

	/**
	 * @var array
	 */
	protected $states;

	public function __construct() {
		parent::__construct( 'LanguageStats' );

		$this->target = $this->getLanguage()->getCode();
		$this->totals = MessageGroupStats::getEmptyStats();
	}

	public function isIncludable() {
		return true;
	}

	protected function getGroupName() {
		return 'wiki';
	}

	public function execute( $par ) {
		$request = $this->getRequest();

		$this->purge = $request->getVal( 'action' ) === 'purge';
		if ( $this->purge && !$request->wasPosted() ) {
			$this->showPurgeForm();
			return;
		}

		$this->table = new StatsTable();

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
					JobQueueGroup::singleton()->push( $job );

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

	/**
	 * Get stats.
	 * @param string $target For which target to get stats
	 * @param int $flags See MessageGroupStats for possible flags
	 * @return array[]
	 */
	protected function loadStatistics( $target, $flags = 0 ) {
		return MessageGroupStats::forLanguage( $target, $flags );
	}

	protected function getCacheRebuildJobParameters( $target ) {
		return [ 'languagecode' => $target ];
	}

	/**
	 * Return true if language exist in the list of allowed languages or false otherwise.
	 * @param string $value
	 * @return bool
	 */
	protected function isValidValue( $value ) {
		$langs = Language::fetchLanguageNames();

		return isset( $langs[$value] );
	}

	/**
	 * Called when the target is unknown.
	 */
	protected function invalidTarget() {
		$this->getOutput()->wrapWikiMsg(
			"<div class='error'>$1</div>",
			'translate-page-no-such-language'
		);
	}

	protected function showPurgeForm() {
		$formDescriptor[ 'intro' ] = [
			'type' => 'info',
			'vertical-label' => true,
			'raw' => true,
			'default' => $this->msg( 'confirm-purge-top' )->parse()
		];

		$context = new DerivativeContext( $this->getContext() );
		$requestValues = $this->getRequest()->getQueryValues();

		HTMLForm::factory( 'ooui', $formDescriptor, $context )
			->setWrapperLegendMsg( 'confirm-purge-title' )
			->setSubmitTextMsg( 'confirm_purge_button' )
			->addHiddenFields( $requestValues )
			->show();
	}

	/**
	 * HTMLForm for the top form rendering.
	 */
	protected function addForm() {
		$formDescriptor[ 'language' ] = [
			'type' => 'text',
			'name' => 'language',
			'id' => 'language',
			'label' => $this->msg( 'translate-language-code-field-name' )->text(),
			'size' => 10,
			'default' => $this->target,
		];
		$formDescriptor[ 'suppresscomplete' ] = [
			'type' => 'check',
			'label' => $this->msg( 'translate-suppress-complete' )->text(),
			'name' => 'suppresscomplete',
			'id' => 'suppresscomplete',
			'default' => $this->noComplete,
		];
		$formDescriptor[ 'suppressempty' ] = [
			'type' => 'check',
			'label' => $this->msg( 'translate-ls-noempty' )->text(),
			'name' => 'suppressempty',
			'id' => 'suppressempty',
			'default' => $this->noEmpty,
		];

		$context = new DerivativeContext( $this->getContext() );
		$context->setTitle( $this->getPageTitle() ); // Remove subpage

		$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $context );

		/* Since these pages are in the tabgroup with Special:Translate,
		* it makes sense to retain the selected group/language parameter
		* on post requests even when not relevant to the current page. */
		$val = $this->getRequest()->getVal( 'group' );
		if ( $val !== null ) {
			$htmlForm->addHiddenField( 'group', $val );
		}

		$htmlForm
			->addHiddenField( 'x', 'D' ) // To detect submission
			->setMethod( 'get' )
			->setSubmitTextMsg( 'translate-ls-submit' )
			->setWrapperLegendMsg( 'translate-mgs-fieldset' )
			->prepareForm()
			->displayForm( false );
	}

	/**
	 * Output something helpful to guide the confused user.
	 */
	protected function outputIntroduction() {
		$languageName = TranslateUtils::getLanguageName(
			$this->target,
			$this->getLanguage()->getCode()
		);

		$rcInLangLink = $this->getLinkRenderer()->makeKnownLink(
			SpecialPage::getTitleFor( 'Translate', '!recent' ),
			$this->msg( 'languagestats-recenttranslations' )->text(),
			[],
			[
				'action' => 'proofread',
				'language' => $this->target
			]
		);

		$out = $this->msg( 'languagestats-stats-for', $languageName )->rawParams( $rcInLangLink )
			->parseAsBlock();
		$this->getOutput()->addHTML( $out );
	}

	/**
	 * If workflow states are configured, adds a workflow states column
	 */
	protected function addWorkflowStatesColumn() {
		global $wgTranslateWorkflowStates;

		if ( $wgTranslateWorkflowStates ) {
			$this->states = $this->getWorkflowStates();

			// An array where keys are state names and values are numbers
			$this->table->addExtraColumn( $this->msg( 'translate-stats-workflow' ) );
		}
	}

	/**
	 * Returns the value of the workflow state for the given target.
	 * @param string $target Whose workflow state we want, either the language code or group id
	 * @return string Workflow state value
	 */
	protected function getWorkflowStateValue( $target ) {
		return $this->states[$target] ?? '';
	}

	/**
	 * If workflow states are configured, adds a cell with the workflow state to the row,
	 * @param string $target Whose workflow state do we want, such as language code or group id.
	 * @param string $state The workflow state id
	 * @return string Html
	 */
	protected function getWorkflowStateCell( $target, $state ) {
		// This will be set by addWorkflowStatesColumn if needed
		if ( !isset( $this->states ) ) {
			return '';
		}

		if ( $state === '' ) {
			return "\n\t\t" . $this->table->element( '', '', -1 );
		}

		if ( $this instanceof SpecialMessageGroupStats ) {
			// Same for every language
			$group = MessageGroups::getGroup( $this->target );
			$stateConfig = $group->getMessageGroupStates()->getStates();
			$languageCode = $target;
		} else {
			// The message group for this row
			$group = MessageGroups::getGroup( $target );
			$stateConfig = $group->getMessageGroupStates()->getStates();
			$languageCode = $this->target;
		}

		if ( $group->getSourceLanguage() === $languageCode ) {
			return "\n\t\t" . $this->table->element( '', '', -1 );
		}

		$sortValue = -1;
		$stateColor = '';
		if ( isset( $stateConfig[$state] ) ) {
			$sortIndex = array_flip( array_keys( $stateConfig ) );
			$sortValue = $sortIndex[$state] + 1;

			if ( is_string( $stateConfig[$state] ) ) {
				// BC for old configuration format
				$stateColor = $stateConfig[$state];
			} elseif ( isset( $stateConfig[$state]['color'] ) ) {
				$stateColor = $stateConfig[$state]['color'];
			}
		}

		$stateMessage = $this->msg( "translate-workflow-state-$state" );
		$stateText = $stateMessage->isBlank() ? $state : $stateMessage->text();

		return "\n\t\t" . $this->table->element(
			$stateText,
			$stateColor,
			$sortValue
		);
	}

	/**
	 * Returns the table itself.
	 * @param array $stats
	 * @return string HTML
	 */
	protected function getTable( $stats ) {
		$table = $this->table;

		$this->addWorkflowStatesColumn();
		$out = '';

		TranslateMetadata::preloadGroups( array_keys( MessageGroups::getAllGroups() ) );
		$structure = MessageGroups::getGroupStructure();
		foreach ( $structure as $item ) {
			$out .= $this->makeGroupGroup( $item, $stats );
		}

		if ( $out ) {
			$table->setMainColumnHeader( $this->msg( 'translate-ls-column-group' ) );
			$out = $table->createHeader() . "\n" . $out;
			$out .= Html::closeElement( 'tbody' );

			$out .= Html::openElement( 'tfoot' );
			$out .= $table->makeTotalRow(
				$this->msg( 'translate-languagestats-overall' ),
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
	 * Creates a html table row for given (top-level) message group.
	 * If $item is an array, meaning that the first group is an
	 * AggregateMessageGroup and the latter are its children, it will recurse
	 * and create rows for them too.
	 * @param MessageGroup|MessageGroup[] $item
	 * @param array $cache Cache as returned by MessageGroupStats::forLanguage
	 * @param MessageGroup|null $parent MessageGroup (do not use, used internally only)
	 * @return string
	 */
	protected function makeGroupGroup( $item, array $cache, MessageGroup $parent = null ) {
		if ( !is_array( $item ) ) {
			return $this->makeGroupRow( $item, $cache, $parent );
		}

		// The first group in the array is the parent AggregateMessageGroup
		$out = '';
		$top = array_shift( $item );
		$out .= $this->makeGroupRow( $top, $cache, $parent );

		// Rest are children
		foreach ( $item as $subgroup ) {
			$out .= $this->makeGroupGroup( $subgroup, $cache, $top );
		}

		return $out;
	}

	/**
	 * Actually creates the table for single message group, unless it
	 * is blacklisted or hidden by filters.
	 * @param MessageGroup $group
	 * @param array $cache
	 * @param MessageGroup|null $parent
	 * @return string
	 */
	protected function makeGroupRow( MessageGroup $group, array $cache,
		MessageGroup $parent = null
	) {
		$groupId = $group->getId();

		if ( $this->table->isBlacklisted( $groupId, $this->target ) !== null ) {
			return '';
		}

		$stats = $cache[$groupId];
		$total = $stats[MessageGroupStats::TOTAL];
		$translated = $stats[MessageGroupStats::TRANSLATED];
		$fuzzy = $stats[MessageGroupStats::FUZZY];

		// Quick checks to see whether filters apply
		if ( $this->noComplete && $fuzzy === 0 && $translated === $total ) {
			return '';
		}
		if ( $this->noEmpty && $translated === 0 && $fuzzy === 0 ) {
			return '';
		}

		if ( $total === null ) {
			$this->incomplete = true;
		}

		// Calculation of summary row values
		if ( !$group instanceof AggregateMessageGroup &&
			!isset( $this->statsCounted[$groupId] )
		) {
			$this->totals = MessageGroupStats::multiAdd( $this->totals, $stats );
			$this->statsCounted[$groupId] = true;
		}

		$state = $this->getWorkflowStateValue( $groupId );

		// Place any state checks like $this->incomplete above this
		$params = $stats;
		$params[] = $state;
		$params[] = md5( $groupId );
		$params[] = $this->getLanguage()->getCode();
		$params[] = md5( $this->target );
		$cachekey = wfMemcKey( __METHOD__ . '-v3', implode( '-', $params ) );
		$cacheval = wfGetCache( CACHE_ANYTHING )->get( $cachekey );
		if ( is_string( $cacheval ) ) {
			return $cacheval;
		}

		$extra = [];
		if ( $translated === $total ) {
			$extra = [ 'action' => 'proofread' ];
		}

		$rowParams = [];
		$rowParams['data-groupid'] = $groupId;
		$rowParams['class'] = get_class( $group );
		if ( $parent ) {
			$rowParams['data-parentgroup'] = $parent->getId();
		}

		$out = "\t" . Html::openElement( 'tr', $rowParams );
		$out .= "\n\t\t" . Html::rawElement( 'td', [],
			$this->table->makeGroupLink( $group, $this->target, $extra ) );
		$out .= $this->table->makeNumberColumns( $stats );
		$out .= $this->getWorkflowStateCell( $groupId, $state );
		$out .= "\n\t" . Html::closeElement( 'tr' ) . "\n";

		wfGetCache( CACHE_ANYTHING )->set( $cachekey, $out, 3600 * 24 );

		return $out;
	}

	protected function getWorkflowStates( $field = 'tgr_group', $filter = 'tgr_lang' ) {
		$db = wfGetDB( DB_REPLICA );
		$res = $db->select(
			'translate_groupreviews',
			[ 'tgr_state', $field ],
			[ $filter => $this->target ],
			__METHOD__
		);

		$states = [];
		foreach ( $res as $row ) {
			$states[$row->$field] = $row->tgr_state;
		}

		return $states;
	}
}
