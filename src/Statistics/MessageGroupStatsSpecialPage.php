<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use JobQueueGroup;
use MediaWiki\Config\Config;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Deferred\DeferredUpdates;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;
use MediaWiki\Extension\Translate\TranslatorInterface\EntitySearch;
use MediaWiki\Html\Html;
use MediaWiki\HTMLForm\HTMLForm;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\SpecialPage\SpecialPage;
use MessagePrefixMessageGroup;

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
	/** Whether to hide rows which are fully translated. */
	private bool $noComplete = true;
	/** Whether to hide rows which are fully untranslated. */
	private bool $noEmpty = false;
	/** The target of stats: group id or message prefix. */
	private string $target;
	/** The target type of stats requested: */
	private ?string $targetType = null;
	private ServiceOptions $options;
	private JobQueueGroup $jobQueueGroup;
	private MessageGroupStatsTableFactory $messageGroupStatsTableFactory;
	private EntitySearch $entitySearch;
	private MessagePrefixStats $messagePrefixStats;
	private LanguageNameUtils $languageNameUtils;
	private MessageGroupMetadata $messageGroupMetadata;

	private const GROUPS = 'group';
	private const MESSAGES = 'messages';

	private const CONSTRUCTOR_OPTIONS = [
		'TranslateMessagePrefixStatsLimit',
	];

	public function __construct(
		Config $config,
		JobQueueGroup $jobQueueGroup,
		MessageGroupStatsTableFactory $messageGroupStatsTableFactory,
		EntitySearch $entitySearch,
		MessagePrefixStats $messagePrefixStats,
		LanguageNameUtils $languageNameUtils,
		MessageGroupMetadata $messageGroupMetadata
	) {
		parent::__construct( 'MessageGroupStats' );
		$this->options = new ServiceOptions( self::CONSTRUCTOR_OPTIONS, $config );
		$this->jobQueueGroup = $jobQueueGroup;
		$this->messageGroupStatsTableFactory = $messageGroupStatsTableFactory;
		$this->entitySearch = $entitySearch;
		$this->messagePrefixStats = $messagePrefixStats;
		$this->languageNameUtils = $languageNameUtils;
		$this->messageGroupMetadata = $messageGroupMetadata;
	}

	/** @inheritDoc */
	public function getDescription() {
		return $this->msg( 'translate-mgs-pagename' );
	}

	/** @inheritDoc */
	public function isIncludable() {
		return true;
	}

	protected function getGroupName(): string {
		return 'translation';
	}

	/** @inheritDoc */
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
		$out->addModuleStyles( 'ext.translate.special.groupstats' );
		$out->addModuleStyles( 'mediawiki.codex.messagebox.styles' );

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

		$this->target = $request->getVal( self::GROUPS, $this->target ?? '' );
		if ( $this->target !== '' ) {
			$this->targetType = self::GROUPS;
		} else {
			$this->target = $request->getVal( self::MESSAGES, '' );
			if ( $this->target !== '' ) {
				$this->targetType = self::MESSAGES;
			}
		}

		// Default booleans to false if the form was submitted
		$this->noComplete = $request->getBool(
			'suppresscomplete',
			$this->noComplete && !$submitted
		);
		$this->noEmpty = $request->getBool( 'suppressempty', $this->noEmpty && !$submitted );

		if ( !$this->including() ) {
			$out->addHelpLink( 'Help:Extension:Translate/Statistics_and_reporting' );
			$this->addForm();
		}

		$stats = $output = null;
		if ( $this->targetType === self::GROUPS && $this->isValidGroup( $this->target ) ) {
			$this->outputIntroduction();

			$stats = $this->loadStatistics( $this->target, MessageGroupStats::FLAG_CACHE_ONLY );

			$messageGroupStatsTable = $this->messageGroupStatsTableFactory->newFromContext( $this->getContext() );
			$group = MessageGroups::getGroup( $this->target );
			$description = $group ? $group->getDescription( $this->getContext() ) : '';
			$output = $group ? $messageGroupStatsTable->get(
				$stats,
				$group,
				$this->noComplete,
				$this->noEmpty
			) : '';
			// If description is present parse it to HTML and show it above the stats
			if ( $description ) {
				$this->getOutput()->addWikiTextAsContent( $description );
			}

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
					$job = RebuildMessageGroupStatsJob::newJob( $jobParams );
					$this->jobQueueGroup->push( $job );

					// $purge is only true if request was posted
					if ( !$purge ) {
						$this->loadStatistics( $this->target );
					}
				} );
			}
		} elseif ( $this->targetType === self::MESSAGES ) {
			$messagesWithPrefix = $this->entitySearch->matchMessages( $this->target );
			if ( $messagesWithPrefix ) {
				$messageWithPrefixLimit = $this->options->get( 'TranslateMessagePrefixStatsLimit' );
				if ( count( $messagesWithPrefix ) > $messageWithPrefixLimit ) {
					$out->addHTML(
						Html::errorBox(
							$this->msg( 'translate-mgs-message-prefix-limit' )
								->params( $messageWithPrefixLimit )
								->parse()
						)
					);
					return;
				}

				$stats = $this->messagePrefixStats->forAll( ...$messagesWithPrefix );
				$messageGroupStatsTable = $this->messageGroupStatsTableFactory
					->newFromContext( $this->getContext() );
				$output = $messageGroupStatsTable->get(
					$stats,
					new MessagePrefixMessageGroup(),
					$this->noComplete,
					$this->noEmpty
				);
			}
		}

		if ( $output ) {
			// If output is present, put it on the page
			$out->addHTML( $output );
		} elseif ( $stats !== null ) {
			// Output not present, but stats are present. Probably an issue?
			$out->addHTML( Html::warningBox( $this->msg( 'translate-mgs-nothing' )->parse() ) );
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

	private function isValidGroup( ?string $value ): bool {
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
		$priorityLangs = $this->messageGroupMetadata->get( $this->target, 'prioritylangs' );
		if ( $priorityLangs ) {
			$languagesFormatted = $this->formatLanguageList( explode( ',', $priorityLangs ) );
			$hasPriorityForce = $this->messageGroupMetadata->get( $this->target, 'priorityforce' ) === 'on';
			if ( $hasPriorityForce ) {
				$this->getOutput()->addWikiMsg( 'tpt-priority-languages-force', $languagesFormatted );
			} else {
				$this->getOutput()->addWikiMsg( 'tpt-priority-languages', $languagesFormatted );
			}
		}
	}

	private function formatLanguageList( array $codes ): string {
		foreach ( $codes as &$value ) {
			$value = $this->languageNameUtils->getLanguageName( $value, $this->getLanguage()->getCode() )
				. $this->msg( 'word-separator' )->plain()
				. $this->msg( 'parentheses', $value )->plain();
		}

		return $this->getLanguage()->listToText( $codes );
	}

	private function addForm(): void {
		$formDescriptor = [
			'select' => [
				'type' => 'select',
				'name' => self::GROUPS,
				'id' => self::GROUPS,
				'label' => $this->msg( 'translate-mgs-group' )->text(),
				'options' => $this->getGroupOptions(),
				'default' => $this->targetType === self::GROUPS ? $this->target : null,
				'cssclass' => 'message-group-selector'
			],
			'input' => [
				'type' => 'text',
				'name' => self::MESSAGES,
				'id' => self::MESSAGES,
				'label' => $this->msg( 'translate-mgs-prefix' )->text(),
				'default' => $this->targetType === self::MESSAGES ? $this->target : null,
				'cssclass' => 'message-prefix-selector'
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
			->setId( 'mw-message-group-stats-form' )
			->setSubmitTextMsg( 'translate-mgs-submit' )
			->setWrapperLegendMsg( 'translate-mgs-fieldset' )
			->prepareForm()
			->displayForm( false );
	}

	/** Creates a simple message group options. */
	private function getGroupOptions(): array {
		$options = [ '' => null ];
		$groups = MessageGroups::getAllGroups();

		foreach ( $groups as $id => $class ) {
			if ( MessageGroups::getGroup( $id )->exists() ) {
				$options[$class->getLabel()] = $id;
			}
		}

		return $options;
	}
}
