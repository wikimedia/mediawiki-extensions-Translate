<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use ContentHandler;
use DifferenceEngine;
use Html;
use JobQueueGroup;
use ManualLogEntry;
use MediaWiki\Cache\LinkBatchFactory;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageProcessing\TranslateMetadata;
use MediaWiki\Extension\Translate\Synchronization\MessageWebImporter;
use MediaWiki\Extension\Translate\Utilities\LanguagesMultiselectWidget;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Extension\TranslationNotifications\SpecialNotifyTranslators;
use MediaWiki\Languages\LanguageFactory;
use MediaWiki\MediaWikiServices;
use MessageGroupStatsRebuildJob;
use OOUI\ButtonInputWidget;
use OOUI\CheckboxInputWidget;
use OOUI\FieldLayout;
use OOUI\FieldsetLayout;
use OOUI\TextInputWidget;
use PermissionsError;
use SpecialPage;
use Title;
use UnexpectedValueException;
use UserBlockedError;
use WebRequest;
use Wikimedia\Rdbms\IResultWrapper;
use Xml;
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
	private TranslatablePageMarker $translatablePageMarker;
	private TranslatablePageParser $translatablePageParser;

	public function __construct(
		LanguageFactory $languageFactory,
		LinkBatchFactory $linkBatchFactory,
		JobQueueGroup $jobQueueGroup,
		TranslatablePageMarker $translatablePageMarker,
		TranslatablePageParser $translatablePageParser
	) {
		parent::__construct( 'PageTranslation' );
		$this->languageFactory = $languageFactory;
		$this->linkBatchFactory = $linkBatchFactory;
		$this->jobQueueGroup = $jobQueueGroup;
		$this->translatablePageMarker = $translatablePageMarker;
		$this->translatablePageParser = $translatablePageParser;
	}

	public function doesWrites(): bool {
		return true;
	}

	protected function getGroupName(): string {
		return 'translation';
	}

	public function execute( $parameters ) {
		$this->setHeaders();

		$user = $this->getUser();
		$request = $this->getRequest();

		$target = $request->getText( 'target', $parameters ?? '' );
		$revision = $request->getIntOrNull( 'revision' );
		$action = $request->getVal( 'do' );
		$out = $this->getOutput();
		$out->addModules( 'ext.translate.special.pagetranslation' );
		$out->addModuleStyles( 'ext.translate.specialpages.styles' );
		$out->addHelpLink( 'Help:Extension:Translate/Page_translation_example' );
		$out->enableOOUI();

		if ( $target === '' ) {
			$this->listPages();

			return;
		}

		// Anything else than listing the pages need permissions
		if ( !$user->isAllowed( 'pagetranslation' ) ) {
			throw new PermissionsError( 'pagetranslation' );
		}

		$title = Title::newFromText( $target );
		if ( !$title ) {
			$out->wrapWikiMsg( Html::errorBox( '$1' ), [ 'tpt-badtitle', $target ] );
			$out->addWikiMsg( 'tpt-list-pages-in-translations' );

			return;
		} elseif ( !$title->exists() ) {
			$out->wrapWikiMsg(
				Html::errorBox( '$1' ),
				[ 'tpt-nosuchpage', $title->getPrefixedText() ]
			);
			$out->addWikiMsg( 'tpt-list-pages-in-translations' );

			return;
		}

		// Check for blocks
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
		if ( $permissionManager->isBlockedFrom( $user, $title, !$request->wasPosted() ) ) {
			$block = $user->getBlock();
			if ( $block ) {
				throw new UserBlockedError(
					$block,
					$user,
					$this->getLanguage(),
					$request->getIP()
				);
			}

			throw new PermissionsError( 'pagetranslation', [ 'badaccess-group0' ] );

		}

		// Check token for all POST actions here
		$csrfTokenSet = $this->getContext()->getCsrfTokenSet();
		if ( $request->wasPosted() && !$csrfTokenSet->matchTokenField( 'token' ) ) {
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
				$logid = $entry->insert();
				$entry->publish( $logid );
			}

			// Defer stats purging of parent aggregate groups. Shared groups can contain other
			// groups as well, which we do not need to update. We could filter non-aggregate
			// groups out, or use MessageGroups::getParentGroups, though it has an inconvenient
			// return value format for this use case.
			$group = MessageGroups::getGroup( $id );
			$sharedGroupIds = MessageGroups::getSharedGroups( $group );
			if ( $sharedGroupIds !== [] ) {
				$job = MessageGroupStatsRebuildJob::newRefreshGroupsJob( $sharedGroupIds );
				$this->jobQueueGroup->push( $job );
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
				$title->toPageRecord( $request->wasPosted() ? Title::READ_LATEST : Title::READ_NORMAL ),
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

			$noFuzzyUnits = array_filter(
				preg_replace(
					'/^tpt-sect-(.*)-action-nofuzzy$|.*/',
					'$1',
					array_keys( $request->getValues() )
				),
				'strlen'
			);

			// https://www.php.net/manual/en/language.variables.external.php says:
			// "Dots and spaces in variable names are converted to underscores.
			// For example <input name="a b" /> becomes $_REQUEST["a_b"]."
			// Therefore, we need to convert underscores back to spaces where they were used in section
			// markers.
			$noFuzzyUnits = str_replace( '_', ' ', $noFuzzyUnits );

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
					Html::errorBox(
						$unitNameValidationResult->getHTML( false, false, $this->getLanguage() )
					)
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
	private function showSuccess(
		TranslatablePage $page, bool $firstMark, int $unitCount
	): void {
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

	protected function showGenericConfirmation( array $params ): void {
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
			Xml::submitButton(
				$this->msg( 'tpt-generic-button' )->text(),
				[ 'class' => 'mw-ui-button mw-ui-progressive' ]
			) .
			Html::closeElement( 'form' )
		);
	}

	protected function showUnlinkConfirmation( Title $target ): void {
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
			Xml::submitButton(
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
			->tables( [ 'page', 'revtag' ] )
			->where( [
					'page_id=rt_page',
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
	 *
	 * @param array[] $pages
	 * @return array[]
	 * @phan-return array{proposed:array[],active:array[],broken:array[],outdated:array[]}
	 */
	private function classifyPages( array $pages ): array {
		// Preload stuff for performance
		$messageGroupIdsForPreload = [];
		foreach ( $pages as $i => $page ) {
			$id = TranslatablePage::getMessageGroupIdFromTitle( $page['title'] );
			$messageGroupIdsForPreload[] = $id;
			$pages[$i]['groupid'] = $id;
		}
		// Performance optimization: load only data we need to classify the pages
		$metadata = TranslateMetadata::loadBasicMetadataForTranslatablePages(
			$messageGroupIdsForPreload,
			[ 'transclusion', 'version' ]
		);

		$out = [
			// The ideal state for pages: marked and up to date
			'active' => [],
			'proposed' => [],
			'outdated' => [],
			'broken' => [],
		];

		foreach ( $pages as $page ) {
			$groupId = $page['groupid'];
			$group = MessageGroups::getGroup( $groupId );
			$page['discouraged'] = MessageGroups::getPriority( $group ) === 'discouraged';
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
		if ( !count( $allPages ) ) {
			$out->addWikiMsg( 'tpt-list-nopages' );

			return;
		}

		$lb = $this->linkBatchFactory->newLinkBatch();
		$lb->setCaller( __METHOD__ );
		foreach ( $allPages as $page ) {
			$lb->addObj( $page['title'] );
		}
		$lb->execute();

		$types = $this->classifyPages( $allPages );

		$pages = $types['proposed'];
		if ( $pages ) {
			$out->wrapWikiMsg( '== $1 ==', 'tpt-new-pages-title' );
			$out->addWikiMsg( 'tpt-new-pages', count( $pages ) );
			$out->addHTML( $this->getPageList( $pages, 'proposed' ) );
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
		$out->addBacklinkSubtitle( $page->getTitle() );
		$out->addWikiMsg( 'tpt-showpage-intro' );

		$formParams = [
			'method' => 'post',
			'action' => $this->getPageTitle()->getLocalURL(),
			'class' => 'mw-tpt-sp-markform',
		];

		$out->addHTML(
			Xml::openElement( 'form', $formParams ) .
			Html::hidden( 'do', 'mark' ) .
			Html::hidden( 'title', $this->getPageTitle()->getPrefixedText() ) .
			Html::hidden( 'revision', $page->getRevision() ) .
			Html::hidden( 'target', $page->getTitle()->getPrefixedText() ) .
			Html::hidden( 'token', $this->getContext()->getCsrfTokenSet()->getToken() )
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

				$oldContent = ContentHandler::makeContent( $s->getOldText(), $diff->getTitle() );
				$newContent = ContentHandler::makeContent( $s->getText(), $diff->getTitle() );

				$diff->setContent( $oldContent, $newContent );

				$text = $diff->getDiff( $diffOld, $diffNew );
				$diffOld = $diffNew = null;
				$diff->showDiffStyle();

				$id = "tpt-sect-{$s->id}-action-nofuzzy";
				$checkLabel = new FieldLayout(
					new CheckboxInputWidget( [
						'name' => $id,
						'selected' => false,
					] ),
					[
						'label' => $this->msg( 'tpt-action-nofuzzy' )->text(),
						'align' => 'inline',
						'classes' => [ 'mw-tpt-m-vertical' ]
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
				$lang
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
			$oldPage = TranslatablePage::newFromRevision(
				$page->getTitle(),
				$markedTag
			);
			$oldTemplate = $this->translatablePageParser
				->parse( $oldPage->getText() )
				->sourcePageTemplateForDiffs();

			if ( $oldTemplate !== $newTemplate ) {
				$out->wrapWikiMsg( '==$1==', 'tpt-sections-template' );

				$diff = new DifferenceEngine();
				$diff->setTextLanguage( $sourceLanguage );

				$oldContent = ContentHandler::makeContent( $oldTemplate, $diff->getTitle() );
				$newContent = ContentHandler::makeContent( $newTemplate, $diff->getTitle() );

				$diff->setContent( $oldContent, $newContent );

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
		$this->templateTransclusionForm( $page->supportsTransclusion() ?? $operation->isFirstMark() );

		$version = TranslateMetadata::getWithDefaultValue(
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
		$storedLanguages = (string)TranslateMetadata::get( $groupId, 'prioritylangs' );
		$default = $storedLanguages !== '' ? explode( ',', $storedLanguages ) : [];

		$priorityReason = TranslateMetadata::get( $groupId, 'priorityreason' );
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
						'selected' => TranslateMetadata::get( $groupId, 'priorityforce' ) === 'on',
					] ),
					[
						'label' => $this->msg( 'tpt-select-prioritylangs-force' )->text(),
						'align' => 'inline',
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

	private function templateTransclusionForm( bool $supportsTransclusion ): void {
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
		$priorityLanguages = array_unique( $priorityLanguages );

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

		return '<ol>' . implode( "", $items ) . '</ol>';
	}
}
