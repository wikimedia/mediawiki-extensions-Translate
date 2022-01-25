<?php
/**
 * Contains logic for special page Special:LanguageStats.
 *
 * @file
 * @author Siebrand Mazeland
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Cache\LinkBatchFactory;
use MediaWiki\Extension\Translate\Statistics\ProgressStatsTableFactory;

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
	/** @var StatsTable */
	private $table;
	/** @var array */
	private $targetValueName = [ 'code', 'language' ];
	/**
	 * Most of the displayed numbers added together at the bottom of the table.
	 */
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
	/**
	 * The target of stats, language code or group id.
	 */
	private $target;
	/**
	 * Whether to regenerate stats. Activated by action=purge in query params.
	 * @var bool
	 */
	private $purge;
	/**
	 * Helper variable to avoid overcounting message groups that appear
	 * multiple times in the list with different parents. Aggregate message
	 * group stats are always excluded from totals.
	 *
	 * @var array
	 */
	private $statsCounted = [];
	/** @var array */
	private $states;
	/** @var LinkBatchFactory */
	private $linkBatchFactory;
	/** @var ProgressStatsTableFactory */
	private $progressStatsTableFactory;

	public function __construct(
		LinkBatchFactory $linkBatchFactory,
		ProgressStatsTableFactory $progressStatsTableFactory
	) {
		parent::__construct( 'LanguageStats' );
		$this->totals = MessageGroupStats::getEmptyStats();
		$this->linkBatchFactory = $linkBatchFactory;
		$this->progressStatsTableFactory = $progressStatsTableFactory;
	}

	public function isIncludable() {
		return true;
	}

	protected function getGroupName() {
		return 'translation';
	}

	public function execute( $par ) {
		$this->target = $this->getLanguage()->getCode();
		$request = $this->getRequest();

		$this->purge = $request->getVal( 'action' ) === 'purge';
		if ( $this->purge && !$request->wasPosted() ) {
			self::showPurgeForm( $this->getContext() );
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

	/**
	 * Get stats.
	 * @param string $target For which target to get stats
	 * @param int $flags See MessageGroupStats for possible flags
	 * @return array[]
	 */
	private function loadStatistics( $target, $flags = 0 ) {
		return MessageGroupStats::forLanguage( $target, $flags );
	}

	private function getCacheRebuildJobParameters( $target ) {
		return [ 'languagecode' => $target ];
	}

	/**
	 * Return true if language exist in the list of allowed languages or false otherwise.
	 * @param string $value
	 * @return bool
	 */
	private function isValidValue( $value ) {
		$langs = Language::fetchLanguageNames();

		return isset( $langs[$value] );
	}

	/**
	 * Called when the target is unknown.
	 */
	private function invalidTarget() {
		$this->getOutput()->wrapWikiMsg(
			"<div class='error'>$1</div>",
			'translate-page-no-such-language'
		);
	}

	public static function showPurgeForm( IContextSource $context ): void {
		$formDescriptor = [
			'intro' => [
				'type' => 'info',
				'vertical-label' => true,
				'raw' => true,
				'default' => $context->msg( 'confirm-purge-top' )->parse()
			],
		];

		$derivativeContext = new DerivativeContext( $context );
		$requestValues = $derivativeContext->getRequest()->getQueryValues();

		HTMLForm::factory( 'ooui', $formDescriptor, $derivativeContext )
			->setWrapperLegendMsg( 'confirm-purge-title' )
			->setSubmitTextMsg( 'confirm_purge_button' )
			->addHiddenFields( $requestValues )
			->show();
	}

	/**
	 * HTMLForm for the top form rendering.
	 */
	private function addForm() {
		$formDescriptor = [
			'language' => [
				'type' => 'text',
				'name' => 'language',
				'id' => 'language',
				'label' => $this->msg( 'translate-language-code-field-name' )->text(),
				'size' => 10,
				'default' => $this->target,
			],
			'suppresscomplete' => [
				'type' => 'check',
				'label' => $this->msg( 'translate-suppress-complete' )->text(),
				'name' => 'suppresscomplete',
				'id' => 'suppresscomplete',
				'default' => $this->noComplete,
			],
			'suppressempty' => [
				'type' => 'check',
				'label' => $this->msg( 'translate-ls-noempty' )->text(),
				'name' => 'suppressempty',
				'id' => 'suppressempty',
				'default' => $this->noEmpty,
			],
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
	private function outputIntroduction() {
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
	private function addWorkflowStatesColumn() {
		global $wgTranslateWorkflowStates;

		if ( $wgTranslateWorkflowStates ) {
			$this->states = $this->getWorkflowStates();

			// An array where keys are state names and values are numbers
			$this->table->addExtraColumn( $this->msg( 'translate-stats-workflow' ) );
		}
	}

	private function getWorkflowStateCell( string $messageGroupId ): string {
		// This will be set by addWorkflowStatesColumn if needed
		if ( !isset( $this->states ) ) {
			return '';
		}

		return $this->table->makeWorkflowStateCell(
			$this->states[$messageGroupId] ?? null,
			MessageGroups::getGroup( $messageGroupId ),
			$this->target
		);
	}

	/**
	 * Returns the table itself.
	 * @param array $stats
	 * @return string HTML
	 */
	private function getTable( $stats ) {
		$table = $this->table;

		$this->addWorkflowStatesColumn();
		$out = '';

		// This avoids a database query per translatable page, which would be caused by
		// $group->getSourceLanguage() in $this->getWorkflowStateCell without preloading
		$lb = $this->linkBatchFactory->newLinkBatch();
		foreach ( MessageGroups::getAllGroups() as $group ) {
			if ( $group instanceof WikiPageMessageGroup ) {
				$lb->addObj( $group->getTitle() );
			}
		}
		$lb->setCaller( __METHOD__ )->execute();

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
	private function makeGroupGroup( $item, array $cache, MessageGroup $parent = null ) {
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
	 * is in the exclusion list or hidden by filters.
	 * @param MessageGroup $group
	 * @param array $cache
	 * @param MessageGroup|null $parent
	 * @return string
	 */
	private function makeGroupRow( MessageGroup $group, array $cache,
		MessageGroup $parent = null
	) {
		$groupId = $group->getId();

		if ( $this->table->isExcluded( $groupId, $this->target ) ) {
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

		// Place any state checks like $this->incomplete above this
		$params = $stats;
		$params[] = $this->states[$groupId] ?? '';
		$params[] = md5( $groupId );
		$params[] = $this->getLanguage()->getCode();
		$params[] = md5( $this->target );
		$params[] = $parent ? $parent->getId() : '!';

		$cache = ObjectCache::getInstance( CACHE_ANYTHING );

		return $cache->getWithSetCallback(
			$cache->makeKey( __METHOD__ . '-v3', implode( '-', $params ) ),
			$cache::TTL_DAY,
			function () use ( $translated, $total, $groupId, $group, $parent, $stats ) {
				// Any data variable read below should be part of the cache key above
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

				return "\t" .
					Html::openElement( 'tr', $rowParams ) .
					"\n\t\t" .
					Html::rawElement(
						'td',
						 [],
						$this->table->makeGroupLink( $group, $this->target, $extra )
					) . $this->table->makeNumberColumns( $stats ) .
					$this->getWorkflowStateCell( $groupId ) .
					"\n\t" .
					Html::closeElement( 'tr' ) .
					"\n";
			}
		);
	}

	private function getWorkflowStates() {
		$db = wfGetDB( DB_REPLICA );
		$res = $db->select(
			'translate_groupreviews',
			[ 'tgr_state', 'tgr_group' ],
			[ 'tgr_lang' => $this->target ],
			__METHOD__
		);

		$states = [];
		foreach ( $res as $row ) {
			$states[$row->tgr_group] = $row->tgr_state;
		}

		return $states;
	}
}
