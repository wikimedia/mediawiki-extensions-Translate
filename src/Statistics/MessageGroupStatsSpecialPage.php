<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use DeferredUpdates;
use HTMLForm;
use JobQueueGroup;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MessageGroupStats;
use MessageGroupStatsRebuildJob;
use SpecialPage;
use TranslateMetadata;

/**
 * Implements includable special page Special:MessageGroupStats which provides
 * translation statistics for all languages for a group.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @ingroup SpecialPage TranslateSpecialPage Stats
 */
class MessageGroupStatsSpecialPage extends SpecialPage {
	/** @var array */
	private $targetValueName = [ 'group' ];
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
	/** @var JobQueueGroup */
	private $jobQueueGroup;
	/** @var MessageGroupStatsTableFactory */
	private $messageGroupStatsTableFactory;

	public function __construct(
		JobQueueGroup $jobQueueGroup,
		MessageGroupStatsTableFactory $messageGroupStatsTableFactory
	) {
		parent::__construct( 'MessageGroupStats' );
		$this->jobQueueGroup = $jobQueueGroup;
		$this->messageGroupStatsTableFactory = $messageGroupStatsTableFactory;
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

		$purge = $request->getVal( 'action' ) === 'purge';
		if ( $purge && !$request->wasPosted() ) {
			LanguageStatsSpecialPage::showPurgeForm( $this->getContext() );
			return;
		}

		$this->setHeaders();
		$this->outputHeader();

		$out = $this->getOutput();

		$out->addModules( 'ext.translate.special.languagestats' );
		$out->addModuleStyles( 'ext.translate.statstable' );

		$params = $par ? explode( '/', $par ) : [];

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

			$messageGroupStatsTable = $this->messageGroupStatsTableFactory->newFromContext( $this->getContext() );
			$output = $messageGroupStatsTable->get(
				$stats,
				MessageGroups::getGroup( $this->target ),
				$this->noComplete,
				$this->noEmpty
			);

			$incomplete = $messageGroupStatsTable->areStatsIncomplete();
			if ( $incomplete ) {
				$out->wrapWikiMsg(
					"<div class='error'>$1</div>",
					'translate-langstats-incomplete'
				);
			}

			if ( $incomplete || $purge ) {
				DeferredUpdates::addCallableUpdate( function () use ( $purge ) {
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
					$jobParams[ 'purge' ] = $purge;
					$job = MessageGroupStatsRebuildJob::newJob( $jobParams );
					$this->jobQueueGroup->push( $job );

					// $purge is only true if request was posted
					if ( !$purge ) {
						$this->loadStatistics( $this->target );
					}
				} );
			}
			if ( !$output ) {
				$out->wrapWikiMsg( "<div class='error'>$1</div>", 'translate-mgs-nothing' );
			}
			$out->addHTML( $output );
		} elseif ( $submitted ) {
			$this->invalidTarget();
		}
	}

	private function loadStatistics( string $target, int $flags = 0 ): array {
		return MessageGroupStats::forGroup( $target, $flags );
	}

	private function getCacheRebuildJobParameters( string $target ): array {
		return [ 'groupid' => $target ];
	}

	private function isValidValue( ?string $value ): bool {
		if ( $value === null ) {
			return false;
		}

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

	private function invalidTarget(): void {
		$this->getOutput()->wrapWikiMsg(
			"<div class='error'>$1</div>",
			[ 'translate-mgs-invalid-group', $this->target ]
		);
	}

	private function outputIntroduction(): void {
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

	private function addForm(): void {
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

	/** Creates a simple message group options. */
	private function getGroupOptions(): array {
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
