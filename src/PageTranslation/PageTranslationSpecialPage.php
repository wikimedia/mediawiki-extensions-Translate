<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use DifferenceEngine;
use ErrorPageError;
use InvalidArgumentException;
use JobQueueGroup;
use ManualLogEntry;
use MediaWiki\Cache\LinkBatchFactory;
use MediaWiki\Content\ContentHandler;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleState;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;
use MediaWiki\Extension\Translate\Statistics\RebuildMessageGroupStatsJob;
use MediaWiki\Extension\Translate\Synchronization\MessageWebImporter;
use MediaWiki\Extension\Translate\Utilities\LanguagesMultiselectWidget;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Extension\TranslationNotifications\SpecialNotifyTranslators;
use MediaWiki\Html\Html;
use MediaWiki\Language\FormatterFactory;
use MediaWiki\Languages\LanguageFactory;
use MediaWiki\Page\PageRecord;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Request\WebRequest;
use MediaWiki\Revision\MutableRevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Status\StatusFormatter;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\Widget\ToggleSwitchWidget;
use MediaWiki\Xml\Xml;
use OOUI\ButtonInputWidget;
use OOUI\CheckboxInputWidget;
use OOUI\DropdownInputWidget;
use OOUI\FieldLayout;
use OOUI\FieldsetLayout;
use OOUI\HtmlSnippet;
use OOUI\RadioInputWidget;
use OOUI\TextInputWidget;
use PermissionsError;
use UnexpectedValueException;
use UserBlockedError;
use Wikimedia\Rdbms\IDBAccessObject;
use Wikimedia\Rdbms\IResultWrapper;
use function count;
use function wfEscapeWikiText;

/**
 * A special page for marking revisions of pages for translation.
 *
 * This page is the main tool for translation administrators in the wiki.
 * It will list all pages in their various states and provides actions
 * that are suitable for given translatable page.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 */
class PageTranslationSpecialPage extends SpecialPage {
	private const DISPLAY_STATUS_MAPPING = [
		TranslatablePageStatus::PROPOSED => 'proposed',
		TranslatablePageStatus::ACTIVE => 'active',
		TranslatablePageStatus::OUTDATED => 'outdated',
		TranslatablePageStatus::BROKEN => 'broken'
	];
	private LanguageFactory $languageFactory;
	private LinkBatchFactory $linkBatchFactory;
	private JobQueueGroup $jobQueueGroup;
	private PermissionManager $permissionManager;
	private TranslatablePageMarker $translatablePageMarker;
	private TranslatablePageParser $translatablePageParser;
	private MessageGroupMetadata $messageGroupMetadata;
	private TranslatablePageView $translatablePageView;
	private TranslatablePageStateStore $translatablePageStateStore;
	private StatusFormatter $statusFormatter;

	public function __construct(
		LanguageFactory $languageFactory,
		LinkBatchFactory $linkBatchFactory,
		JobQueueGroup $jobQueueGroup,
		PermissionManager $permissionManager,
		TranslatablePageMarker $translatablePageMarker,
		TranslatablePageParser $translatablePageParser,
		MessageGroupMetadata $messageGroupMetadata,
		TranslatablePageView $translatablePageView,
		TranslatablePageStateStore $translatablePageStateStore,
		FormatterFactory $formatterFactory
	) {
		parent::__construct( 'PageTranslation' );
		$this->languageFactory = $languageFactory;
		$this->linkBatchFactory = $linkBatchFactory;
		$this->jobQueueGroup = $jobQueueGroup;
		$this->permissionManager = $permissionManager;
		$this->translatablePageMarker = $translatablePageMarker;
		$this->translatablePageParser = $translatablePageParser;
		$this->messageGroupMetadata = $messageGroupMetadata;
		$this->translatablePageView = $translatablePageView;
		$this->translatablePageStateStore = $translatablePageStateStore;
		$this->statusFormatter = $formatterFactory->getStatusFormatter( $this );
	}

	/** @inheritDoc */
	public function doesWrites(): bool {
		return true;
	}

	protected function getGroupName(): string {
		return 'translation';
	}

	/** @inheritDoc */
	public function execute( $parameters ) {
		$this->setHeaders();

		$user = $this->getUser();
		$request = $this->getRequest();

		$target = $request->getText( 'target', $parameters ?? '' );
		$revision = $request->getIntOrNull( 'revision' );
		$action = $request->getVal( 'do' );
		$out = $this->getOutput();
		$out->addModules( 'ext.translate.special.pagetranslation' );
		$out->addModuleStyles( [
			'ext.translate.specialpages.styles',
			'mediawiki.codex.messagebox.styles',
		] );
		$out->addHelpLink( 'Help:Extension:Translate/Page_translation_example' );
		$out->enableOOUI();

		if ( $target === '' ) {
			$this->listPages();

			return;
		}

		$title = Title::newFromText( $target );
		if ( !$title ) {
			$out->wrapWikiMsg( Html::errorBox( '$1' ), [ 'tpt-badtitle', $target ] );
			$out->addWikiMsg( 'tpt-list-pages-in-translations' );

			return;
		}

		$this->getSkin()->setRelevantTitle( $title );

		if ( !$title->exists() ) {
			$out->wrapWikiMsg(
				Html::errorBox( '$1' ),
				[ 'tpt-nosuchpage', $title->getPrefixedText() ]
			);
			$out->addWikiMsg( 'tpt-list-pages-in-translations' );

			return;
		}

		if ( $action === 'settings' && !$this->translatablePageView->isTranslationBannerNamespaceConfigured() ) {
			$this->showTranslationStateRestricted();
			return;
		}

		$block = $this->getBlock( $request, $user, $title );
		if ( $action === 'settings' && !$request->wasPosted() ) {
			$this->showTranslationSettings( $title, $block );
			return;
		}

		if ( $block ) {
			throw $block;
		}

		// Check token for all POST actions here
		$csrfTokenSet = $this->getContext()->getCsrfTokenSet();
		if ( $request->wasPosted() && !$csrfTokenSet->matchTokenField( 'token' ) ) {
			throw new PermissionsError( 'pagetranslation' );
		}

		if ( $action === 'settings' && $request->wasPosted() ) {
			$this->handleTranslationState( $title, $request->getRawVal( 'translatable-page-state' ) ?? '' );
			return;
		}

		// Anything other than listing the pages or manipulating settings needs permissions
		if ( !$user->isAllowed( 'pagetranslation' ) ) {
			throw new PermissionsError( 'pagetranslation' );
		}

		if ( $action === 'mark' ) {
			// Has separate form
			$this->onActionMark( $title, $revision );

			return;
		}

		// On GET requests, show form which has token
		if ( !$request->wasPosted() ) {
			if ( $action === 'unlink' ) {
				$this->showUnlinkConfirmation( $title );
			} else {
				$params = [
					'do' => $action,
					'target' => $title->getPrefixedText(),
					'revision' => $revision,
				];
				$this->showGenericConfirmation( $params );
			}

			return;
		}

		if ( $action === 'discourage' || $action === 'encourage' ) {
			$id = TranslatablePage::getMessageGroupIdFromTitle( $title );
			$current = MessageGroups::getPriority( $id );

			if ( $action === 'encourage' ) {
				$new = '';
			} else {
				$new = 'discouraged';
			}

			if ( $new !== $current ) {
				MessageGroups::setPriority( $id, $new );
				$entry = new ManualLogEntry( 'pagetranslation', $action );
				$entry->setPerformer( $user );
				$entry->setTarget( $title );
				$logId = $entry->insert();
				$entry->publish( $logId );
			}

			// Defer stats purging of parent aggregate groups. Shared groups can contain other
			// groups as well, which we do not need to update. We could filter non-aggregate
			// groups out, or use MessageGroups::getParentGroups, though it has an inconvenient
			// return value format for this use case.
			$group = MessageGroups::getGroup( $id );
			if ( $group ) {
				$sharedGroupIds = MessageGroups::getSharedGroups( $group );
				if ( $sharedGroupIds !== [] ) {
					$job = RebuildMessageGroupStatsJob::newRefreshGroupsJob( $sharedGroupIds );
					$this->jobQueueGroup->push( $job );
				}
			}

			// Show updated page with a notice
			$this->listPages();

			return;
		}

		if ( $action === 'unlink' || $action === 'unmark' ) {
			try {
				$this->translatablePageMarker->unmarkPage(
					TranslatablePage::newFromTitle( $title ),
					$user,
					$this,
					$action === 'unlink'
				);

				$out->wrapWikiMsg(
					Html::successBox( '$1' ),
					[ 'tpt-unmarked', $title->getPrefixedText() ]
				);
			} catch ( TranslatablePageMarkException $e ) {
				$out->wrapWikiMsg(
					Html::errorBox( '$1' ),
					$e->getMessageObject()
				);
			}

			$out->addWikiMsg( 'tpt-list-pages-in-translations' );
		}
	}

	protected function onActionMark( Title $title, ?int $revision ): void {
		$request = $this->getRequest();
		$out = $this->getOutput();
		$translateTitle = $request->getCheck( 'translatetitle' );

		try {
			$operation = $this->translatablePageMarker->getMarkOperation(
				$title->toPageRecord(
					$request->wasPosted() ? IDBAccessObject::READ_LATEST : IDBAccessObject::READ_NORMAL
				),
				$revision,
				// If the request was not posted, validate all the units so that initially we display all the errors
				// and then the user can choose whether they want to translate the title
				!$request->wasPosted() || $translateTitle
			);
		} catch ( TranslatablePageMarkException $e ) {
			$out->addHTML( Html::errorBox( $this->msg( $e->getMessageObject() )->parse() ) );
			$out->addWikiMsg( 'tpt-list-pages-in-translations' );
			return;
		}

		$unitNameValidationResult = $operation->getUnitValidationStatus();
		// Non-fatal error which prevents saving
		if ( $unitNameValidationResult->isOK() && $request->wasPosted() ) {
			// Fetch priority language related information
			[ $priorityLanguages, $forcePriorityLanguage, $priorityLanguageReason ] =
				$this->getPriorityLanguage( $this->getRequest() );

			$unitFuzzySelector = $request->getRawVal( 'unit-fuzzy-selector' );
			if ( $unitFuzzySelector === 'all' ) {
				$noFuzzyUnits = [];
			} else {
				// Get IDs of all changed units
				$allChangedUnits = array_map(
					static fn ( $unit ) => $unit->id,
					array_filter(
						$operation->getUnits(),
						static fn ( $unit ) => $unit->type === 'changed'
					)
				);

				if ( $unitFuzzySelector === 'none' ) {
					$noFuzzyUnits = $allChangedUnits;
				} else { // custom
					$fuzzyUnits = $request->getArray( 'tpt-sect-fuzzy' ) ?? [];
					// Filter the units that should not be fuzzied
					$noFuzzyUnits = array_filter(
						$allChangedUnits,
						static fn ( $value ) => !in_array( $value, $fuzzyUnits )
					);
				}
			}

			$translatablePageSettings = new TranslatablePageSettings(
				$priorityLanguages,
				$forcePriorityLanguage,
				$priorityLanguageReason,
				$noFuzzyUnits,
				$translateTitle,
				$request->getCheck( 'use-latest-syntax' ),
				$request->getCheck( 'transclusion' )
			);

			try {
				$unitCount = $this->translatablePageMarker->markForTranslation(
					$operation,
					$translatablePageSettings,
					$this,
					$this->getUser()
				);
				$this->showSuccess( $operation->getPage(), $operation->isFirstMark(), $unitCount );
			} catch ( TranslatablePageMarkException $e ) {
				$out->wrapWikiMsg(
					Html::errorBox( '$1' ),
					$e->getMessageObject()
				);
			}
		} else {
			if ( !$unitNameValidationResult->isOK() ) {
				$out->addHTML(
					Html::errorBox( $this->statusFormatter->getHTML( $unitNameValidationResult ) )
				);
			}

			$this->showPage( $operation );
		}
	}

	/**
	 * Displays success message and other instructions after a page has been marked for translation.
	 * @param TranslatablePage $page
	 * @param bool $firstMark true if it is the first time the page is being marked for translation.
	 * @param int $unitCount
	 * @return void
	 */
	private function showSuccess( TranslatablePage $page, bool $firstMark, int $unitCount ): void {
		$titleText = $page->getTitle()->getPrefixedText();
		$num = $this->getLanguage()->formatNum( $unitCount );
		$link = SpecialPage::getTitleFor( 'Translate' )->getFullURL( [
			'group' => $page->getMessageGroupId(),
			'action' => 'page',
			'filter' => '',
		] );

		$this->getOutput()->wrapWikiMsg(
			Html::successBox( '$1' ),
			[ 'tpt-saveok', $titleText, $num, $link ]
		);

		// If the page is being marked for translation for the first time
		// add a link to Special:PageMigration.
		if ( $firstMark ) {
			$this->getOutput()->addWikiMsg( 'tpt-saveok-first' );
		}

		// If TranslationNotifications is installed, and the user can notify
		// translators, add a convenience link.
		if ( method_exists( SpecialNotifyTranslators::class, 'execute' ) &&
			$this->getUser()->isAllowed( SpecialNotifyTranslators::$right )
		) {
			$link = SpecialPage::getTitleFor( 'NotifyTranslators' )->getFullURL(
				[ 'tpage' => $page->getTitle()->getArticleID() ]
			);
			$this->getOutput()->addWikiMsg( 'tpt-offer-notify', $link );
		}

		$this->getOutput()->addWikiMsg( 'tpt-list-pages-in-translations' );
	}

	private function showGenericConfirmation( array $params ): void {
		$formParams = [
			'method' => 'post',
			'action' => $this->getPageTitle()->getLocalURL(),
		];

		$params['title'] = $this->getPageTitle()->getPrefixedText();
		$params['token'] = $this->getContext()->getCsrfTokenSet()->getToken();

		$hidden = '';
		foreach ( $params as $key => $value ) {
			$hidden .= Html::hidden( $key, $value );
		}

		$this->getOutput()->addHTML(
			Html::openElement( 'form', $formParams ) .
			$hidden .
			$this->msg( 'tpt-generic-confirm' )->parseAsBlock() .
			Html::submitButton(
				$this->msg( 'tpt-generic-button' )->text(),
				[ 'class' => 'mw-ui-button mw-ui-progressive' ]
			) .
			Html::closeElement( 'form' )
		);
	}

	private function showUnlinkConfirmation( Title $target ): void {
		$formParams = [
			'method' => 'post',
			'action' => $this->getPageTitle()->getLocalURL(),
		];

		$this->getOutput()->addHTML(
			Html::openElement( 'form', $formParams ) .
			Html::hidden( 'do', 'unlink' ) .
			Html::hidden( 'title', $this->getPageTitle()->getPrefixedText() ) .
			Html::hidden( 'target', $target->getPrefixedText() ) .
			Html::hidden( 'token', $this->getContext()->getCsrfTokenSet()->getToken() ) .
			$this->msg( 'tpt-unlink-confirm', $target->getPrefixedText() )->parseAsBlock() .
			Html::submitButton(
				$this->msg( 'tpt-unlink-button' )->text(),
				[ 'class' => 'mw-ui-button mw-ui-destructive' ]
			) .
			Html::closeElement( 'form' )
		);
	}

	/**
	 * TODO: Move this function to SyncTranslatableBundleStatusMaintenanceScript once we
	 * start using the translatable_bundles table for fetching the translatabale pages
	 */
	public static function loadPagesFromDB(): IResultWrapper {
		$dbr = Utilities::getSafeReadDB();
		return $dbr->newSelectQueryBuilder()
			->select( [
				'page_id',
				'page_namespace',
				'page_title',
				'page_latest',
				'rt_revision' => 'MAX(rt_revision)',
				'rt_type'
			] )
			->from( 'page' )
			->join( 'revtag', null, 'page_id=rt_page' )
			->where( [
				'rt_type' => [ RevTagStore::TP_MARK_TAG, RevTagStore::TP_READY_TAG ],
			] )
			->orderBy( [ 'page_namespace', 'page_title' ] )
			->groupBy( [ 'page_id', 'page_namespace', 'page_title', 'page_latest', 'rt_type' ] )
			->caller( __METHOD__ )
			->fetchResultSet();
	}

	/**
	 * TODO: Move this function to SyncTranslatableBundleStatusMaintenanceScript once we
	 * start using the translatable_bundles table for fetching the translatabale pages
	 */
	public static function buildPageArray( IResultWrapper $res ): array {
		$pages = [];
		foreach ( $res as $r ) {
			// We have multiple rows for same page, because of different tags
			if ( !isset( $pages[$r->page_id] ) ) {
				$pages[$r->page_id] = [];
				$title = Title::newFromRow( $r );
				$pages[$r->page_id]['title'] = $title;
				$pages[$r->page_id]['latest'] = (int)$title->getLatestRevID();
			}

			$tag = $r->rt_type;
			$pages[$r->page_id][$tag] = (int)$r->rt_revision;
		}

		return $pages;
	}

	/**
	 * Classify a list of pages and amend them with additional metadata.
	 * @param array[] $pages
	 * @return array[]
	 * @phan-return array{proposed:array[],active:array[],broken:array[],outdated:array[]}
	 */
	private function classifyPages( array $pages ): array {
		$out = [
			// The ideal state for pages: marked and up to date
			'active' => [],
			'proposed' => [],
			'outdated' => [],
			'broken' => [],
		];

		if ( $pages === [] ) {
			return $out;
		}

		// Preload stuff for performance
		$messageGroupIdsForPreload = [];
		foreach ( $pages as $i => $page ) {
			$id = TranslatablePage::getMessageGroupIdFromTitle( $page['title'] );
			$messageGroupIdsForPreload[] = $id;
			$pages[$i]['groupid'] = $id;
		}
		// Performance optimization: load only data we need to classify the pages
		$metadata = $this->messageGroupMetadata->loadBasicMetadataForTranslatablePages(
			$messageGroupIdsForPreload,
			[ 'transclusion', 'version' ]
		);

		foreach ( $pages as $page ) {
			$groupId = $page['groupid'];
			$group = MessageGroups::getGroup( $groupId );

			$page['discouraged'] = false;
			if ( $group ) {
				$page['discouraged'] = MessageGroups::getPriority( $group ) === 'discouraged';
			}
			$page['version'] = $metadata[$groupId]['version'] ?? TranslatablePageMarker::DEFAULT_SYNTAX_VERSION;
			$page['transclusion'] = $metadata[$groupId]['transclusion'] ?? false;

			// TODO: Eventually we should query the status directly from the TranslatableBundleStore
			$tpStatus = TranslatablePage::determineStatus(
				$page[RevTagStore::TP_READY_TAG] ?? null,
				$page[RevTagStore::TP_MARK_TAG] ?? null,
				$page['latest']
			);

			if ( !$tpStatus ) {
				// Ignore pages for which status could not be determined.
				continue;
			}

			$out[self::DISPLAY_STATUS_MAPPING[$tpStatus->getId()]][] = $page;
		}

		return $out;
	}

	public function listPages(): void {
		$out = $this->getOutput();

		$res = self::loadPagesFromDB();
		$allPages = self::buildPageArray( $res );

		$pagesWithProposedState = [];
		if ( $this->translatablePageView->isTranslationBannerNamespaceConfigured() ) {
			$pagesWithProposedState = $this->translatablePageStateStore->getRequested();
		}

		if ( !count( $allPages ) && !count( $pagesWithProposedState ) ) {
			$out->addWikiMsg( 'tpt-list-nopages' );

			return;
		}

		$lb = $this->linkBatchFactory->newLinkBatch();
		$lb->setCaller( __METHOD__ );
		foreach ( $allPages as $page ) {
			$lb->addObj( $page['title'] );
		}

		foreach ( $pagesWithProposedState as $title ) {
			$lb->addObj( $title );
		}
		$lb->execute();

		$types = $this->classifyPages( $allPages );

		$pages = $types['proposed'];
		if ( $pages || $pagesWithProposedState ) {
			$out->wrapWikiMsg( '== $1 ==', 'tpt-new-pages-title' );
			if ( $pages ) {
				$out->addWikiMsg( 'tpt-new-pages', count( $pages ) );
				$out->addHTML( $this->getPageList( $pages, 'proposed' ) );
			}

			if ( $pagesWithProposedState ) {
				$out->addWikiMsg( 'tpt-proposed-state-pages', count( $pagesWithProposedState ) );
				$out->addHTML( $this->displayPagesWithProposedState( $pagesWithProposedState ) );
			}
		}

		$pages = $types['broken'];
		if ( $pages ) {
			$out->wrapWikiMsg( '== $1 ==', 'tpt-other-pages-title' );
			$out->addWikiMsg( 'tpt-other-pages', count( $pages ) );
			$out->addHTML( $this->getPageList( $pages, 'broken' ) );
		}

		$pages = $types['outdated'];
		if ( $pages ) {
			$out->wrapWikiMsg( '== $1 ==', 'tpt-outdated-pages-title' );
			$out->addWikiMsg( 'tpt-outdated-pages', count( $pages ) );
			$out->addHTML( $this->getPageList( $pages, 'outdated' ) );
		}

		$pages = $types['active'];
		if ( $pages ) {
			$out->wrapWikiMsg( '== $1 ==', 'tpt-old-pages-title' );
			$out->addWikiMsg( 'tpt-old-pages', count( $pages ) );
			$out->addHTML( $this->getPageList( $pages, 'active' ) );
		}
	}

	private function actionLinks( array $page, string $type ): string {
		// Performance optimization to avoid calling $this->msg in a loop
		static $messageCache = null;
		if ( $messageCache === null ) {
			$messageCache = [
				'mark' => $this->msg( 'tpt-rev-mark' )->text(),
				'mark-tooltip' => $this->msg( 'tpt-rev-mark-tooltip' )->text(),
				'encourage' => $this->msg( 'tpt-rev-encourage' )->text(),
				'encourage-tooltip' => $this->msg( 'tpt-rev-encourage-tooltip' )->text(),
				'discourage' => $this->msg( 'tpt-rev-discourage' )->text(),
				'discourage-tooltip' => $this->msg( 'tpt-rev-discourage-tooltip' )->text(),
				'unmark' => $this->msg( 'tpt-rev-unmark' )->text(),
				'unmark-tooltip' => $this->msg( 'tpt-rev-unmark-tooltip' )->text(),
				'pipe-separator' => $this->msg( 'pipe-separator' )->escaped(),
			];
		}

		$actions = [];
		/** @var Title $title */
		$title = $page['title'];
		$user = $this->getUser();

		// Class to allow one-click POSTs
		$js = [ 'class' => 'mw-translate-jspost' ];

		if ( $user->isAllowed( 'pagetranslation' ) ) {
			// Enable re-marking of all pages to allow changing of priority languages
			// or migration to the new syntax version
			if ( $type !== 'broken' ) {
				$actions[] = $this->getLinkRenderer()->makeKnownLink(
					$this->getPageTitle(),
					$messageCache['mark'],
					[ 'title' => $messageCache['mark-tooltip'] ],
					[
						'do' => 'mark',
						'target' => $title->getPrefixedText(),
						'revision' => $title->getLatestRevID(),
					]
				);
			}

			if ( $type !== 'proposed' ) {
				if ( $page['discouraged'] ) {
					$actions[] = $this->getLinkRenderer()->makeKnownLink(
						$this->getPageTitle(),
						$messageCache['encourage'],
						[ 'title' => $messageCache['encourage-tooltip'] ] + $js,
						[
							'do' => 'encourage',
							'target' => $title->getPrefixedText(),
							'revision' => -1,
						]
					);
				} else {
					$actions[] = $this->getLinkRenderer()->makeKnownLink(
						$this->getPageTitle(),
						$messageCache['discourage'],
						[ 'title' => $messageCache['discourage-tooltip'] ] + $js,
						[
							'do' => 'discourage',
							'target' => $title->getPrefixedText(),
							'revision' => -1,
						]
					);
				}

				$actions[] = $this->getLinkRenderer()->makeKnownLink(
					$this->getPageTitle(),
					$messageCache['unmark'],
					[ 'title' => $messageCache['unmark-tooltip'] ],
					[
						'do' => $type === 'broken' ? 'unmark' : 'unlink',
						'target' => $title->getPrefixedText(),
						'revision' => -1,
					]
				);
			}
		}

		if ( !$actions ) {
			return '';
		}

		return '<div>' . implode( $messageCache['pipe-separator'], $actions ) . '</div>';
	}

	private function showPage( TranslatablePageMarkOperation $operation ): void {
		$page = $operation->getPage();
		$out = $this->getOutput();
		$out->addWikiMsg( 'tpt-showpage-intro' );

		$this->addPageForm(
			$page->getTitle(),
			'mw-tpt-sp-markform',
			'mark',
			$page->getRevision()
		);

		$out->wrapWikiMsg( '==$1==', 'tpt-sections-oldnew' );

		$diffOld = $this->msg( 'tpt-diff-old' )->escaped();
		$diffNew = $this->msg( 'tpt-diff-new' )->escaped();
		$hasChanges = false;

		// Check whether page title was previously marked for translation.
		// If the page is marked for translation the first time, default to checked,
		// unless the page is a template. T305240
		$defaultChecked = (
			$operation->isFirstMark() &&
			!$page->getTitle()->inNamespace( NS_TEMPLATE )
		) || $page->hasPageDisplayTitle();

		$sourceLanguage = $this->languageFactory->getLanguage( $page->getSourceLanguageCode() );

		$hideUnchangedUnitToggle = '';
		// Toggle for unchanged translation units
		if ( array_filter(
			$operation->getUnits(),
			static fn ( $unit ) => $unit->type === 'old' && $unit->id !== TranslatablePage::DISPLAY_TITLE_UNIT_ID
		) ) {
			$hideUnchangedUnitToggle = ( new FieldLayout(
				new ToggleSwitchWidget( [
					'name' => 'unchanged-translation-units',
					'selected' => false
				] ),
				[
					'label' => $this->msg( 'tpt-translate-hide-unchanged-units' )->text(),
					'align' => 'left',
				]
			) )->toString();
		}

		// Check if there are changed units
		$requireUpdatesDropdown = '';
		if ( array_filter(
			$operation->getUnits(),
			static fn ( $unit ) => $unit->type === 'changed'
		) ) {
			$requireUpdatesDropdown = ( new FieldLayout(
				new DropdownInputWidget( [
					'name' => 'unit-fuzzy-selector',
					'options' => [
						[
							'data' => 'all',
							'label' => $this->msg( 'tpt-fuzzy-select-all' )->text()
						],
						[
							'data' => 'none',
							'label' => $this->msg( 'tpt-fuzzy-select-none' )->text()
						],
						[
							'data' => 'custom',
							'label' => $this->msg( 'tpt-fuzzy-select-custom' )->text()
						]
					],
					'value' => 'custom'
				] ),
				[
					'label' => $this->msg( 'tpt-fuzzy-select-label' )->text(),
					'align' => 'left',
				]
			) )->toString();
		}

		// General area
		if ( $hideUnchangedUnitToggle !== '' || $requireUpdatesDropdown !== '' ) {
			$out->addHTML( MessageWebImporter::makeSectionElement(
				$this->msg( 'tpt-general-area-header' )->text(),
				'general',
				$hideUnchangedUnitToggle . $requireUpdatesDropdown
			) );

			$out->addHTML( '<hr>' );
		}

		foreach ( $operation->getUnits() as $s ) {
			if ( $s->id === TranslatablePage::DISPLAY_TITLE_UNIT_ID ) {
				// Set section type as new if title previously unchecked
				$s->type = $defaultChecked ? $s->type : 'new';

				// Checkbox for page title optional translation
				$checkBox = new FieldLayout(
					new CheckboxInputWidget( [
						'name' => 'translatetitle',
						'selected' => $defaultChecked,
					] ),
					[
						'label' => $this->msg( 'tpt-translate-title' )->text(),
						'align' => 'inline',
						'classes' => [ 'mw-tpt-m-vertical' ]
					]
				);
				$out->addHTML( $checkBox->toString() );
			}

			if ( $s->type === 'new' ) {
				$hasChanges = true;
				$name = $this->msg( 'tpt-section-new', $s->id )->escaped();
			} else {
				$name = $this->msg( 'tpt-section', $s->id )->escaped();
			}

			if ( $s->type === 'changed' ) {
				$hasChanges = true;
				$diff = new DifferenceEngine();
				$diff->setTextLanguage( $sourceLanguage );
				$diff->setReducedLineNumbers();

				$tpTitle = $page->getTitle();
				$oldContent = ContentHandler::makeContent( $s->getOldText(), $tpTitle );
				$oldRevision = new MutableRevisionRecord( $tpTitle );
				$oldRevision->setContent( SlotRecord::MAIN, $oldContent );

				$newContent = ContentHandler::makeContent( $s->getText(), $tpTitle );
				$newRevision = new MutableRevisionRecord( $tpTitle );
				$newRevision->setContent( SlotRecord::MAIN, $newContent );

				$diff->setRevisions( $oldRevision, $newRevision );

				$text = $diff->getDiff( $diffOld, $diffNew );
				$diffOld = $diffNew = null;
				$diff->showDiffStyle();

				$checkLabel = new FieldLayout(
					new CheckboxInputWidget( [
						'name' => 'tpt-sect-fuzzy[]',
						'value' => $s->id,
						'selected' => !$s->onlyTvarsChanged()
					] ),
					[
						'label' => $this->msg( 'tpt-action-fuzzy' )->text(),
						'align' => 'inline',
						'classes' => [ 'mw-tpt-m-vertical', 'mw-tpt-action-field' ],
					]
				);
				$text = $checkLabel->toString() . $text;
			} else {
				$text = Utilities::convertWhiteSpaceToHTML( $s->getText() );
			}

			# For changed text, the language is set by $diff->setTextLanguage()
			$lang = $s->type === 'changed' ? null : $sourceLanguage;
			$out->addHTML( MessageWebImporter::makeSectionElement(
				$name,
				$s->type,
				$text,
				$lang,
				$s->id === TranslatablePage::DISPLAY_TITLE_UNIT_ID ?
					[ 'mw-tpt-sp-section-type-title' ] :
					[]
			) );

			foreach ( $s->getIssues() as $issue ) {
				$severity = $issue->getSeverity();
				if ( $severity === TranslationUnitIssue::WARNING ) {
					$box = Html::warningBox( $this->msg( $issue )->escaped() );
				} elseif ( $severity === TranslationUnitIssue::ERROR ) {
					$box = Html::errorBox( $this->msg( $issue )->escaped() );
				} else {
					throw new UnexpectedValueException(
						"Unknown severity: $severity for key: {$issue->getKey()}"
					);
				}

				$out->addHTML( $box );
			}
		}

		if ( $operation->getDeletedUnits() ) {
			$hasChanges = true;
			$out->wrapWikiMsg( '==$1==', 'tpt-sections-deleted' );

			foreach ( $operation->getDeletedUnits() as $s ) {
				$name = $this->msg( 'tpt-section-deleted', $s->id )->escaped();
				$text = Utilities::convertWhiteSpaceToHTML( $s->getText() );
				$out->addHTML( MessageWebImporter::makeSectionElement(
					$name,
					'deleted',
					$text,
					$sourceLanguage
				) );
			}
		}

		// Display template changes if applicable
		$markedTag = $page->getMarkedTag();
		if ( $markedTag !== null ) {
			$hasChanges = true;
			$newTemplate = $operation->getParserOutput()->sourcePageTemplateForDiffs();
			$tpTitle = $page->getTitle();
			$oldPage = TranslatablePage::newFromRevision( $tpTitle, $markedTag );
			$oldTemplate = $this->translatablePageParser
				->parse( $oldPage->getText() )
				->sourcePageTemplateForDiffs();

			if ( $oldTemplate !== $newTemplate ) {
				$out->wrapWikiMsg( '==$1==', 'tpt-sections-template' );

				$diff = new DifferenceEngine();
				$diff->setTextLanguage( $sourceLanguage );

				$oldContent = ContentHandler::makeContent( $oldTemplate, $tpTitle );
				$oldRevision = new MutableRevisionRecord( $tpTitle );
				$oldRevision->setContent( SlotRecord::MAIN, $oldContent );

				$newContent = ContentHandler::makeContent( $newTemplate, $tpTitle );
				$newRevision = new MutableRevisionRecord( $tpTitle );
				$newRevision->setContent( SlotRecord::MAIN, $newContent );

				$diff->setRevisions( $oldRevision, $newRevision );

				$text = $diff->getDiff(
					$this->msg( 'tpt-diff-old' )->escaped(),
					$this->msg( 'tpt-diff-new' )->escaped()
				);
				$diff->showDiffStyle();
				$diff->setReducedLineNumbers();

				$out->addHTML( Xml::tags( 'div', [], $text ) );
			}
		}

		if ( !$hasChanges ) {
			$out->wrapWikiMsg( Html::successBox( '$1' ), 'tpt-mark-nochanges' );
		}

		$this->priorityLanguagesForm( $page );

		// If an existing page does not have the supportsTransclusion flag, keep the checkbox unchecked,
		// If the page is being marked for translation for the first time, the checkbox can be checked
		$this->templateTransclusionForm( $page, $page->supportsTransclusion() ?? $operation->isFirstMark() );

		$version = $this->messageGroupMetadata->getWithDefaultValue(
			$page->getMessageGroupId(), 'version', TranslatablePageMarker::DEFAULT_SYNTAX_VERSION
		);
		$this->syntaxVersionForm( $version, $operation->isFirstMark() );

		$submitButton = new FieldLayout(
			new ButtonInputWidget( [
				'label' => $this->msg( 'tpt-submit' )->text(),
				'type' => 'submit',
				'flags' => [ 'primary', 'progressive' ],
			] ),
			[
				'label' => null,
				'align' => 'top',
			]
		);

		$out->addHTML( $submitButton->toString() );
		$out->addHTML( '</form>' );
	}

	private function priorityLanguagesForm( TranslatablePage $page ): void {
		$groupId = $page->getMessageGroupId();
		$interfaceLanguage = $this->getLanguage()->getCode();
		$storedLanguages = (string)$this->messageGroupMetadata->get( $groupId, 'prioritylangs' );
		$default = $storedLanguages !== '' ? explode( ',', $storedLanguages ) : [];

		$priorityReason = $this->messageGroupMetadata->get( $groupId, 'priorityreason' );
		$priorityReason = $priorityReason !== false ? $priorityReason : '';

		$form = new FieldsetLayout( [
			'items' => [
				new FieldLayout(
					new LanguagesMultiselectWidget( [
						'infusable' => true,
						'name' => 'prioritylangs',
						'id' => 'mw-translate-SpecialPageTranslation-prioritylangs',
						'languages' => Utilities::getLanguageNames( $interfaceLanguage ),
						'default' => $default,
					] ),
					[
						'label' => $this->msg( 'tpt-select-prioritylangs' )->text(),
						'align' => 'top',
					]
				),
				new FieldLayout(
					new CheckboxInputWidget( [
						'name' => 'forcelimit',
						'selected' => $this->messageGroupMetadata->get( $groupId, 'priorityforce' ) === 'on',
					] ),
					[
						'label' => $this->msg( 'tpt-select-prioritylangs-force' )->text(),
						'align' => 'inline',
						'help' => new HtmlSnippet( $this->msg( 'tpt-select-no-prioritylangs-force' )->parse() ),
					]
				),
				new FieldLayout(
					new TextInputWidget( [
						'name' => 'priorityreason',
						'value' => $priorityReason
					] ),
					[
						'label' => $this->msg( 'tpt-select-prioritylangs-reason' )->text(),
						'align' => 'top',
					]
				),

			],
		] );

		$this->getOutput()->wrapWikiMsg( '==$1==', 'tpt-sections-prioritylangs' );
		$this->getOutput()->addHTML( $form->toString() );
	}

	private function syntaxVersionForm( string $version, bool $firstMark ): void {
		$out = $this->getOutput();

		if ( $version === TranslatablePageMarker::LATEST_SYNTAX_VERSION || $firstMark ) {
			return;
		}

		$out->wrapWikiMsg( '==$1==', 'tpt-sections-syntaxversion' );
		$out->addWikiMsg(
			'tpt-syntaxversion-text',
			'<code>' . wfEscapeWikiText( '<span lang="en" dir="ltr">...</span>' ) . '</code>',
			'<code>' . wfEscapeWikiText( '<translate nowrap>...</translate>' ) . '</code>'
		);

		$checkBox = new FieldLayout(
			new CheckboxInputWidget( [
				'name' => 'use-latest-syntax'
			] ),
			[
				'label' => $out->msg( 'tpt-syntaxversion-label' )->text(),
				'align' => 'inline',
			]
		);

		$out->addHTML( $checkBox->toString() );
	}

	private function templateTransclusionForm( TranslatablePage $page, bool $supportsTransclusion ): void {
		$out = $this->getOutput();
		$out->wrapWikiMsg( '==$1==', 'tpt-transclusion' );

		$checkBox = new FieldLayout(
			new CheckboxInputWidget( [
				'name' => 'transclusion',
				'selected' => $supportsTransclusion
			] ),
			[
				'label' => $out->msg( 'tpt-transclusion-label' )->text(),
				'align' => 'inline',
				'help' => $out->msg( 'tpt-transclusion-help' )
					->params( $page->getTitle()->getSubpage( 'de' )->getPrefixedText() )
					->text(),
				'helpInline' => true,
			]
		);

		$out->addHTML( $checkBox->toString() );
	}

	private function getPriorityLanguage( WebRequest $request ): array {
		// Get the priority languages from the request
		// We've to do some extra work here because if JS is disabled, we will be getting
		// the values split by newline.
		$priorityLanguages = rtrim( trim( $request->getVal( 'prioritylangs', '' ) ), ',' );
		$priorityLanguages = str_replace( "\n", ',', $priorityLanguages );
		$priorityLanguages = array_map( 'trim', explode( ',', $priorityLanguages ) );
		$priorityLanguages = array_unique( array_filter( $priorityLanguages ) );

		$forcePriorityLanguage = $request->getCheck( 'forcelimit' );
		$priorityLanguageReason = trim( $request->getText( 'priorityreason' ) );

		return [ $priorityLanguages, $forcePriorityLanguage, $priorityLanguageReason ];
	}

	private function getPageList( array $pages, string $type ): string {
		$items = [];
		$tagsTextCache = [];

		$tagDiscouraged = $this->msg( 'tpt-tag-discouraged' )->escaped();
		$tagOldSyntax = $this->msg( 'tpt-tag-oldsyntax' )->escaped();
		$tagNoTransclusionSupport = $this->msg( 'tpt-tag-no-transclusion-support' )->escaped();

		foreach ( $pages as $page ) {
			$link = $this->getLinkRenderer()->makeKnownLink( $page['title'] );
			$acts = $this->actionLinks( $page, $type );
			$tags = [];
			if ( $page['discouraged'] ) {
				$tags[] = $tagDiscouraged;
			}
			if ( $type !== 'proposed' ) {
				if ( $page['version'] !== TranslatablePageMarker::LATEST_SYNTAX_VERSION ) {
					$tags[] = $tagOldSyntax;
				}

				if ( $page['transclusion'] !== '1' ) {
					$tags[] = $tagNoTransclusionSupport;
				}
			}

			$tagList = '';
			if ( $tags ) {
				// Performance optimization to avoid calling $this->msg in a loop
				$tagsKey = implode( '', $tags );
				$tagsTextCache[$tagsKey] ??= $this->msg( 'parentheses' )
					->rawParams( $this->getLanguage()->pipeList( $tags ) )
					->escaped();

				$tagList = Html::rawElement(
					'span',
					[ 'class' => 'mw-tpt-actions' ],
					$tagsTextCache[$tagsKey]
				);
			}

			$items[] = "<li class='mw-tpt-pagelist-item'>$link $tagList $acts</li>";
		}

		return '<ol>' . implode( '', $items ) . '</ol>';
	}

	/** @param PageRecord[] $pagesWithProposedState */
	private function displayPagesWithProposedState( array $pagesWithProposedState ): string {
		$items = [];
		$preparePageAction = $this->msg( 'tpt-prepare-page' )->text();
		$preparePageTooltip = $this->msg( 'tpt-prepare-page-tooltip' )->text();
		$linkRenderer = $this->getLinkRenderer();
		foreach ( $pagesWithProposedState as $pageRecord ) {
			$link = $linkRenderer->makeKnownLink( $pageRecord );
			$action = $linkRenderer->makeKnownLink(
				SpecialPage::getTitleFor( 'PagePreparation' ),
				$preparePageAction,
				[ 'title' => $preparePageTooltip ],
				[ 'page' => ( Title::newFromPageReference( $pageRecord ) )->getPrefixedText() ]
			);
			$items[] = "<li class='mw-tpt-pagelist-item'>$link <div>$action</div></li>";
		}
		return '<ol>' . implode( '', $items ) . '</ol>';
	}

	private function showTranslationSettings( Title $target, ?ErrorPageError $block ): void {
		$out = $this->getOutput();
		$out->setPageTitle( $this->msg( 'tpt-translation-settings-page-title' )->text() );

		$currentState = $this->translatablePageStateStore->get( $target );

		if ( !$this->translatablePageView->canManageTranslationSettings( $target, $this->getUser() ) ) {
			$out->wrapWikiMsg( Html::errorBox( '$1' ), 'tpt-translation-settings-restricted' );
			$out->addWikiMsg( 'tpt-list-pages-in-translations' );
			return;
		}

		if ( $block ) {
			$out->wrapWikiMsg( Html::errorBox( '$1' ), $block->getMessageObject() );
		}

		if ( $currentState ) {
			$this->displayStateInfoMessage( $target, $currentState );
		}

		$this->addPageForm( $target, 'mw-tpt-sp-settings', 'settings', null );
		$out->addHTML(
			Html::rawElement(
				'p',
				[ 'class' => 'mw-tpt-vm' ],
				Html::element( 'strong', [], $this->msg( 'tpt-translation-settings-subtitle' ) )
			)
		);

		$currentStateId = $currentState ? $currentState->getStateId() : null;
		$options = new FieldsetLayout( [
			'items' => [
				new FieldLayout(
					new RadioInputWidget( [
						'name' => 'translatable-page-state',
						'value' => 'ignored',
						'selected' => $currentStateId === TranslatableBundleState::IGNORE
					] ),
					[
						'label' => $this->msg( 'tpt-translation-settings-ignore' )->text(),
						'align' => 'inline',
						'help' => $this->msg( 'tpt-translation-settings-ignore-hint' )->text(),
						'helpInline' => true,
					]
				),
				new FieldLayout(
					new RadioInputWidget( [
						'name' => 'translatable-page-state',
						'value' => 'unstable',
						'selected' => $currentStateId === null
					] ),
					[
						'label' => $this->msg( 'tpt-translation-settings-unstable' )->text(),
						'align' => 'inline',
						'help' => $this->msg( 'tpt-translation-settings-unstable-hint' )->text(),
						'helpInline' => true,
					]
				),
				new FieldLayout(
					new RadioInputWidget( [
						'name' => 'translatable-page-state',
						'value' => 'proposed',
						'selected' => $currentStateId === TranslatableBundleState::PROPOSE
					] ),
					[
						'label' => $this->msg( 'tpt-translation-settings-propose' )->text(),
						'align' => 'inline',
						'help' => $this->msg( 'tpt-translation-settings-propose-hint' )->text(),
						'helpInline' => true,
					]
				),
			],
		] );

		$out->addHTML( $options->toString() );

		$submitButton = new FieldLayout(
			new ButtonInputWidget( [
				'label' => $this->msg( 'tpt-translation-settings-save' )->text(),
				'type' => 'submit',
				'flags' => [ 'primary', 'progressive' ],
				'disabled' => $block !== null,
			] )
		);

		$out->addHTML( $submitButton->toString() );
		$out->addHTML( Html::closeElement( 'form' ) );
	}

	private function handleTranslationState( Title $title, string $selectedState ): void {
		$validStateValues = [ 'ignored', 'unstable', 'proposed' ];
		$out = $this->getOutput();
		if ( !in_array( $selectedState, $validStateValues ) ) {
			throw new InvalidArgumentException( "Invalid translation state selected: $selectedState" );
		}

		$user = $this->getUser();
		if ( !$this->translatablePageView->canManageTranslationSettings( $title, $user ) ) {
			$this->showTranslationStateRestricted();
			return;
		}

		$bundleState = TranslatableBundleState::newFromText( $selectedState );
		if ( $selectedState === 'unstable' ) {
			$this->translatablePageStateStore->remove( $title );
		} else {
			$this->translatablePageStateStore->set( $title, $bundleState );
		}

		$this->displayStateInfoMessage( $title, $bundleState );
		$out->setPageTitle( $this->msg( 'tpt-translation-settings-page-title' )->text() );
		$out->addWikiMsg( 'tpt-list-pages-in-translations' );
	}

	private function addPageForm(
		Title $target,
		string $formClass,
		string $action,
		?int $revision
	): void {
		$formParams = [
			'method' => 'post',
			'action' => $this->getPageTitle()->getLocalURL(),
			'class' => $formClass
		];

		$this->getOutput()->addHTML(
			Xml::openElement( 'form', $formParams ) .
			Html::hidden( 'do', $action ) .
			Html::hidden( 'title', $this->getPageTitle()->getPrefixedText() ) .
			( $revision ? Html::hidden( 'revision', $revision ) : '' ) .
			Html::hidden( 'target', $target->getPrefixedText() ) .
			Html::hidden( 'token', $this->getContext()->getCsrfTokenSet()->getToken() )
		);
	}

	private function displayStateInfoMessage( Title $title, TranslatableBundleState $bundleState ): void {
		$stateId = $bundleState->getStateId();
		if ( $stateId === TranslatableBundleState::UNSTABLE ) {
			$infoMessage = $this->msg( 'tpt-translation-settings-unstable-notice' );
		} elseif ( $stateId === TranslatableBundleState::PROPOSE ) {
			$userHasPageTranslationRight = $this->getUser()->isAllowed( 'pagetranslation' );
			if ( $userHasPageTranslationRight ) {
				$infoMessage = $this->msg( 'tpt-translation-settings-proposed-pagetranslation-notice' )->params(
					'https://www.mediawiki.org/wiki/Special:MyLanguage/' .
					'Help:Extension:Translate/Page_translation_administration',
					$title->getFullURL( 'action=edit' ),
					SpecialPage::getTitleFor( 'PagePreparation' )
						->getFullURL( [ 'page' => $title->getPrefixedText() ] )
				);
			} else {
				$infoMessage = $this->msg( 'tpt-translation-settings-proposed-editor-notice' );
			}
		} else {
			$infoMessage = $this->msg( 'tpt-translation-settings-ignored-notice' );
		}

		$this->getOutput()->wrapWikiMsg( Html::noticeBox( '$1', '' ), $infoMessage );
	}

	private function getBlock( WebRequest $request, User $user, Title $title ): ?ErrorPageError {
		if ( $this->permissionManager->isBlockedFrom( $user, $title, !$request->wasPosted() ) ) {
			$block = $user->getBlock();
			if ( $block ) {
				return new UserBlockedError(
					$block,
					$user,
					$this->getLanguage(),
					$request->getIP()
				);
			}

			return new PermissionsError( 'pagetranslation', [ 'badaccess-group0' ] );
		}

		return null;
	}

	private function showTranslationStateRestricted(): void {
		$out = $this->getOutput();
		$out->wrapWikiMsg( Html::errorBox( "$1" ), 'tpt-translation-settings-restricted' );
		$out->addWikiMsg( 'tpt-list-pages-in-translations' );
	}
}
