<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use ContentHandler;
use DifferenceEngine;
use Html;
use ManualLogEntry;
use MediaWiki\Cache\LinkBatchFactory;
use MediaWiki\Extension\Translate\Utilities\LanguagesMultiselectWidget;
use MediaWiki\Languages\LanguageFactory;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\User\UserIdentity;
use MessageGroups;
use MessageGroupStatsRebuildJob;
use MessageIndex;
use MessageWebImporter;
use MWException;
use OOUI\ButtonInputWidget;
use OOUI\CheckboxInputWidget;
use OOUI\FieldLayout;
use OOUI\FieldsetLayout;
use OOUI\TextInputWidget;
use PermissionsError;
use RevTag;
use SpecialNotifyTranslators;
use SpecialPage;
use Title;
use TranslatablePage;
use TranslateMetadata;
use TranslateUtils;
use TranslationsUpdateJob;
use WebRequest;
use Wikimedia\Rdbms\IResultWrapper;
use WikiPage;
use Xml;
use function count;
use function wfEscapeWikiText;
use function wfGetDB;
use const EDIT_FORCE_BOT;
use const EDIT_UPDATE;

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
	private const LATEST_SYNTAX_VERSION = '2';
	private const DEFAULT_SYNTAX_VERSION = '1';
	/** @var LanguageNameUtils */
	private $languageNameUtils;
	/** @var LanguageFactory */
	private $languageFactory;
	/** @var TranslationUnitStoreFactory */
	private $translationUnitStoreFactory;
	/** @var TranslatablePageParser */
	private $translatablePageParser;
	/** @var LinkBatchFactory */
	private $linkBatchFactory;

	public function __construct(
		LanguageNameUtils $languageNameUtils,
		LanguageFactory $languageFactory,
		TranslationUnitStoreFactory $translationUnitStoreFactory,
		TranslatablePageParser $translatablePageParser,
		LinkBatchFactory $linkBatchFactory
	) {
		parent::__construct( 'PageTranslation' );
		$this->languageNameUtils = $languageNameUtils;
		$this->languageFactory = $languageFactory;
		$this->translationUnitStoreFactory = $translationUnitStoreFactory;
		$this->translatablePageParser = $translatablePageParser;
		$this->linkBatchFactory = $linkBatchFactory;
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

		$target = $request->getText( 'target', $parameters );
		$revision = $request->getInt( 'revision', 0 );
		$action = $request->getVal( 'do' );
		$out = $this->getOutput();
		$out->addModules( 'ext.translate.special.pagetranslation' );
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

		// Check token for all POST actions here
		if ( $request->wasPosted() && !$user->matchEditToken( $request->getText( 'token' ) ) ) {
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
				TranslateUtils::getJobQueueGroup()->push( $job );
			}

			// Show updated page with a notice
			$this->listPages();

			return;
		}

		if ( $action === 'unlink' ) {
			$page = TranslatablePage::newFromTitle( $title );

			$content = ContentHandler::makeContent(
				$page->getStrippedSourcePageText(),
				$title
			);

			$status = WikiPage::factory( $title )->doUserEditContent(
				$content,
				$this->getUser(),
				$this->msg( 'tpt-unlink-summary' )->inContentLanguage()->text(),
				EDIT_FORCE_BOT | EDIT_UPDATE
			);

			if ( !$status->isOK() ) {
				$out->wrapWikiMsg(
					Html::errorBox( '$1' ),
					[ 'tpt-edit-failed', $status->getWikiText() ]
				);
				$out->addWikiMsg( 'tpt-list-pages-in-translations' );

				return;
			}

			$page = TranslatablePage::newFromTitle( $title );
			$this->unmarkPage( $page, $user );
			$out->wrapWikiMsg(
				Html::successBox( '$1' ),
				[ 'tpt-unmarked', $title->getPrefixedText() ]
			);
			$out->addWikiMsg( 'tpt-list-pages-in-translations' );

			return;
		}

		if ( $action === 'unmark' ) {
			$page = TranslatablePage::newFromTitle( $title );
			$this->unmarkPage( $page, $user );
			$out->wrapWikiMsg(
				Html::successBox( '$1' ),
				[ 'tpt-unmarked', $title->getPrefixedText() ]
			);
			$out->addWikiMsg( 'tpt-list-pages-in-translations' );
		}
	}

	protected function onActionMark( Title $title, int $revision ): void {
		$request = $this->getRequest();
		$out = $this->getOutput();

		$out->addModuleStyles( 'ext.translate.specialpages.styles' );

		if ( $revision === 0 ) {
			// Get the latest revision
			$revision = (int)$title->getLatestRevID();
		}

		// This also catches the case where revision does not belong to the title
		if ( $revision !== (int)$title->getLatestRevID() ) {
			// We do want to notify the reviewer if the underlying page changes during review
			$target = $title->getFullURL( [ 'oldid' => $revision ] );
			$link = "<span class='plainlinks'>[$target $revision]</span>";
			$out->wrapWikiMsg(
				Html::warningBox( '$1' ),
				[ 'tpt-oldrevision', $title->getPrefixedText(), $link ]
			);
			$out->addWikiMsg( 'tpt-list-pages-in-translations' );

			return;
		}

		// newFromRevision never fails, but getReadyTag might fail if revision does not belong
		// to the page (checked above)
		$page = TranslatablePage::newFromRevision( $title, $revision );
		if ( $page->getReadyTag() !== $title->getLatestRevID() ) {
			$out->wrapWikiMsg(
				Html::errorBox( '$1' ),
				[ 'tpt-notsuitable', $title->getPrefixedText() ]
			);
			$out->addWikiMsg( 'tpt-list-pages-in-translations' );

			return;
		}

		$firstMark = $page->getMarkedTag() === false;

		$parse = $this->translatablePageParser->parse( $page->getText() );
		[ $units, $deletedUnits ] = $this->prepareTranslationUnits( $page, $parse );

		$error = $this->validateUnitIds( $units );

		// Non-fatal error which prevents saving
		if ( !$error && $request->wasPosted() ) {
			// Check if user wants to translate title
			// If not, remove it from the list of units
			if ( !$request->getCheck( 'translatetitle' ) ) {
				$units = array_filter( $units, static function ( $s ) {
					return $s->id !== TranslatablePage::DISPLAY_TITLE_UNIT_ID;
				} );
			}

			$setVersion = $firstMark || $request->getCheck( 'use-latest-syntax' );
			$transclusion = $request->getCheck( 'transclusion' );

			$err = $this->markForTranslation( $page, $parse, $units, $setVersion, $transclusion );

			if ( $err ) {
				call_user_func_array( [ $out, 'addWikiMsg' ], $err );
			} else {
				$this->showSuccess( $page, $firstMark, count( $units ) );
			}

			return;
		}

		$this->showPage( $page, $parse, $units, $deletedUnits, $firstMark );
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
			'action' => $this->getPageTitle()->getFullURL(),
		];

		$params['title'] = $this->getPageTitle()->getPrefixedText();
		$params['token'] = $this->getUser()->getEditToken();

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
			'action' => $this->getPageTitle()->getFullURL(),
		];

		$this->getOutput()->addHTML(
			Html::openElement( 'form', $formParams ) .
			Html::hidden( 'do', 'unlink' ) .
			Html::hidden( 'title', $this->getPageTitle()->getPrefixedText() ) .
			Html::hidden( 'target', $target->getPrefixedText() ) .
			Html::hidden( 'token', $this->getUser()->getEditToken() ) .
			$this->msg( 'tpt-unlink-confirm', $target->getPrefixedText() )->parseAsBlock() .
			Xml::submitButton(
				$this->msg( 'tpt-unlink-button' )->text(),
				[ 'class' => 'mw-ui-button mw-ui-destructive' ]
			) .
			Html::closeElement( 'form' )
		);
	}

	protected function unmarkPage( TranslatablePage $page, UserIdentity $user ): void {
		$page->unmarkTranslatablePage();
		$page->getTitle()->invalidateCache();

		$entry = new ManualLogEntry( 'pagetranslation', 'unmark' );
		$entry->setPerformer( $user );
		$entry->setTarget( $page->getTitle() );
		$logid = $entry->insert();
		$entry->publish( $logid );
	}

	public function loadPagesFromDB(): IResultWrapper {
		$dbr = TranslateUtils::getSafeReadDB();
		$tables = [ 'page', 'revtag' ];
		$vars = [
			'page_id',
			'page_namespace',
			'page_title',
			'page_latest',
			'MAX(rt_revision) AS rt_revision',
			'rt_type'
		];
		$conds = [
			'page_id=rt_page',
			'rt_type' => [ RevTag::getType( 'tp:mark' ), RevTag::getType( 'tp:tag' ) ],
		];
		$options = [
			'ORDER BY' => 'page_namespace, page_title',
			'GROUP BY' => 'page_id, page_namespace, page_title, page_latest, rt_type',
		];

		return $dbr->select( $tables, $vars, $conds, __METHOD__, $options );
	}

	protected function buildPageArray( IResultWrapper $res ): array {
		$pages = [];
		foreach ( $res as $r ) {
			// We have multiple rows for same page, because of different tags
			if ( !isset( $pages[$r->page_id] ) ) {
				$pages[$r->page_id] = [];
				$title = Title::newFromRow( $r );
				$pages[$r->page_id]['title'] = $title;
				$pages[$r->page_id]['latest'] = (int)$title->getLatestRevID();
			}

			$tag = RevTag::typeToTag( $r->rt_type );
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
			$page['version'] = $metadata[$groupId]['version'] ?? self::DEFAULT_SYNTAX_VERSION;
			$page['transclusion'] = $metadata[$groupId]['transclusion'] ?? false;

			if ( !isset( $page['tp:mark'] ) ) {
				// Never marked, check that the latest version is ready
				if ( $page['tp:tag'] === $page['latest'] ) {
					$out['proposed'][] = $page;
				} // Otherwise, ignore such pages
			} elseif ( $page['tp:tag'] === $page['latest'] ) {
				if ( $page['tp:mark'] === $page['tp:tag'] ) {
					// Marked and latest version is fine
					$out['active'][] = $page;
				} else {
					$out['outdated'][] = $page;
				}
			} else {
				// Marked but latest version is not fine
				$out['broken'][] = $page;
			}
		}

		return $out;
	}

	public function listPages(): void {
		$out = $this->getOutput();

		$res = $this->loadPagesFromDB();
		$allPages = $this->buildPageArray( $res );
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

	public function validateUnitIds( array $units ): bool {
		$usedNames = [];
		$error = false;

		$ic = preg_quote( TranslationUnit::UNIT_MARKER_INVALID_CHARS, '~' );
		foreach ( $units as $s ) {
			if ( preg_match( "~[$ic]~", $s->id ) ) {
				$this->getOutput()->addElement(
					'p',
					[ 'class' => 'errorbox' ],
					$this->msg( 'tpt-invalid' )->params( $s->id )->text()
				);
				$error = true;
			}

			// We need to do checks for both new and existing units.
			// Someone might have tampered with the page source adding
			// duplicate or invalid markers.
			$usedNames[$s->id] = ( $usedNames[$s->id] ?? 0 ) + 1;
		}
		foreach ( $usedNames as $name => $count ) {
			if ( $count > 1 ) {
				// Only show error once per duplicated translation unit
				$this->getOutput()->addElement(
					'p',
					[ 'class' => 'errorbox' ],
					$this->msg( 'tpt-duplicate' )->params( $name )->text()
				);
				$error = true;
			}
		}

		return $error;
	}

	/** @return TranslationUnit[][] */
	private function prepareTranslationUnits( TranslatablePage $page, ParserOutput $parse ): array {
		$highest = (int)TranslateMetadata::get( $page->getMessageGroupId(), 'maxid' );

		$store = $this->translationUnitStoreFactory->getReader( $page->getTitle() );
		$storedUnits = $store->getUnits();
		$parsedUnits = $parse->units();

		// Prepend the display title unit, which is not part of the page contents
		$displayTitle = new TranslationUnit(
			$page->getTitle()->getPrefixedText(),
			TranslatablePage::DISPLAY_TITLE_UNIT_ID
		);
		$parsedUnits = [ TranslatablePage::DISPLAY_TITLE_UNIT_ID => $displayTitle ] + $parsedUnits;

		// Figure out the largest used translation unit id
		foreach ( array_keys( $storedUnits ) as $key ) {
			$highest = max( $highest, (int)$key );
		}
		foreach ( $parsedUnits as $_ ) {
			$highest = max( $highest, (int)$_->id );
		}

		foreach ( $parsedUnits as $s ) {
			$s->type = 'old';

			if ( $s->id === TranslationUnit::NEW_UNIT_ID ) {
				$s->type = 'new';
				$s->id = (string)( ++$highest );
			} else {
				if ( isset( $storedUnits[$s->id] ) ) {
					$storedText = $storedUnits[$s->id]->text;
					if ( $s->text !== $storedText ) {
						$s->type = 'changed';
						$s->oldText = $storedText;
					}
				}
			}
		}

		// Figure out which units were deleted by removing the still existing units
		$deletedUnits = $storedUnits;
		foreach ( $parsedUnits as $s ) {
			unset( $deletedUnits[$s->id] );
		}

		return [ $parsedUnits, $deletedUnits ];
	}

	private function showPage(
		TranslatablePage $page,
		ParserOutput $parse,
		array $sections,
		array $deletedUnits,
		bool $firstMark
	): void {
		$out = $this->getOutput();
		$out->setSubtitle( $this->getLinkRenderer()->makeKnownLink( $page->getTitle() ) );
		$out->addWikiMsg( 'tpt-showpage-intro' );

		$formParams = [
			'method' => 'post',
			'action' => $this->getPageTitle()->getFullURL(),
			'class' => 'mw-tpt-sp-markform',
		];

		$out->addHTML(
			Xml::openElement( 'form', $formParams ) .
			Html::hidden( 'do', 'mark' ) .
			Html::hidden( 'title', $this->getPageTitle()->getPrefixedText() ) .
			Html::hidden( 'revision', $page->getRevision() ) .
			Html::hidden( 'target', $page->getTitle()->getPrefixedText() ) .
			Html::hidden( 'token', $this->getUser()->getEditToken() )
		);

		$out->wrapWikiMsg( '==$1==', 'tpt-sections-oldnew' );

		$diffOld = $this->msg( 'tpt-diff-old' )->escaped();
		$diffNew = $this->msg( 'tpt-diff-new' )->escaped();
		$hasChanges = false;

		// Check whether page title was previously marked for translation.
		// If the page is marked for translation the first time, default to checked.
		$defaultChecked = $firstMark || $page->hasPageDisplayTitle();

		$sourceLanguage = $this->languageFactory->getLanguage( $page->getSourceLanguageCode() );

		foreach ( $sections as $s ) {
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
				$text = TranslateUtils::convertWhiteSpaceToHTML( $s->getText() );
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
					throw new MWException(
						"Unknown severity: $severity for key: {$issue->getKey()}"
					);
				}

				$out->addHTML( $box );
			}
		}

		if ( $deletedUnits ) {
			$hasChanges = true;
			$out->wrapWikiMsg( '==$1==', 'tpt-sections-deleted' );

			foreach ( $deletedUnits as $s ) {
				$name = $this->msg( 'tpt-section-deleted', $s->id )->escaped();
				$text = TranslateUtils::convertWhiteSpaceToHTML( $s->getText() );
				$out->addHTML( MessageWebImporter::makeSectionElement(
					$name,
					'deleted',
					$text,
					$sourceLanguage
				) );
			}
		}

		// Display template changes if applicable
		if ( $page->getMarkedTag() !== false ) {
			$hasChanges = true;
			$newTemplate = $parse->sourcePageTemplateForDiffs();
			$oldPage = TranslatablePage::newFromRevision(
				$page->getTitle(),
				$page->getMarkedTag()
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
		$this->templateTransclusionForm( $page->supportsTransclusion() ?? $firstMark );

		$version = TranslateMetadata::getWithDefaultValue(
			$page->getMessageGroupId(), 'version', self::DEFAULT_SYNTAX_VERSION
		);
		$this->syntaxVersionForm( $version, $firstMark );

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

		$form = new FieldsetLayout( [
			'items' => [
				new FieldLayout(
					new LanguagesMultiselectWidget( [
						'infusable' => true,
						'name' => 'prioritylangs',
						'id' => 'mw-translate-SpecialPageTranslation-prioritylangs',
						'languages' => TranslateUtils::getLanguageNames( $interfaceLanguage ),
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

		if ( $version === self::LATEST_SYNTAX_VERSION || $firstMark ) {
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

	/**
	 * This function does the heavy duty of marking a page.
	 * - Updates the source page with section markers.
	 * - Updates translate_sections table
	 * - Updates revtags table
	 * - Sets up renderjobs to update the translation pages
	 * - Invalidates caches
	 * - Adds interim cache for MessageIndex
	 *
	 * @param TranslatablePage $page
	 * @param ParserOutput $parse
	 * @param TranslationUnit[] $sections
	 * @param bool $updateVersion
	 * @param bool $transclusion
	 * @return array|bool
	 */
	protected function markForTranslation(
		TranslatablePage $page,
		ParserOutput $parse,
		array $sections,
		bool $updateVersion,
		bool $transclusion
	) {
		// Add the section markers to the source page
		$wikiPage = WikiPage::factory( $page->getTitle() );
		$content = ContentHandler::makeContent(
			$parse->sourcePageTextForSaving(),
			$page->getTitle()
		);

		$status = $wikiPage->doUserEditContent(
			$content,
			$this->getUser(),
			$this->msg( 'tpt-mark-summary' )->inContentLanguage()->text(),
			EDIT_FORCE_BOT | EDIT_UPDATE
		);

		if ( !$status->isOK() ) {
			return [ 'tpt-edit-failed', $status->getWikiText() ];
		}

		// @phan-suppress-next-line PhanTypeArraySuspiciousNullable
		$newRevisionRecord = $status->value['revision-record'];
		// In theory it is either null or RevisionRecord object,
		// not a RevisionRecord object with null id, but who knows
		if ( $newRevisionRecord instanceof RevisionRecord ) {
			$newRevisionId = $newRevisionRecord->getId();
		} else {
			$newRevisionId = null;
		}

		// Probably a no-change edit, so no new revision was assigned.
		// Get the latest revision manually
		// Could also occur on the off chance $newRevisionRecord->getId() returns null
		if ( $newRevisionId === null ) {
			$newRevisionId = $page->getTitle()->getLatestRevID();
		}

		$inserts = [];
		$changed = [];
		$groupId = $page->getMessageGroupId();
		$maxid = (int)TranslateMetadata::get( $groupId, 'maxid' );

		$pageId = $page->getTitle()->getArticleID();
		/** @var TranslationUnit $s */
		foreach ( array_values( $sections ) as $index => $s ) {
			$maxid = max( $maxid, (int)$s->id );
			$changed[] = $s->id;

			if ( $this->getRequest()->getCheck( "tpt-sect-{$s->id}-action-nofuzzy" ) ) {
				// TranslationsUpdateJob will only fuzzy when type is changed
				$s->type = 'old';
			}

			$inserts[] = [
				'trs_page' => $pageId,
				'trs_key' => $s->id,
				'trs_text' => $s->getText(),
				'trs_order' => $index
			];
		}

		$dbw = wfGetDB( DB_PRIMARY );
		$dbw->delete(
			'translate_sections',
			[ 'trs_page' => $page->getTitle()->getArticleID() ],
			__METHOD__
		);
		$dbw->insert( 'translate_sections', $inserts, __METHOD__ );
		TranslateMetadata::set( $groupId, 'maxid', $maxid );
		if ( $updateVersion ) {
			TranslateMetadata::set( $groupId, 'version', self::LATEST_SYNTAX_VERSION );
		}

		$page->setTransclusion( $transclusion );

		$page->addMarkedTag( $newRevisionId );
		MessageGroups::singleton()->recache();

		// Store interim cache
		$group = $page->getMessageGroup();
		$newKeys = $group->makeGroupKeys( $changed );
		MessageIndex::singleton()->storeInterim( $group, $newKeys );

		$job = TranslationsUpdateJob::newFromPage( $page, $sections );
		TranslateUtils::getJobQueueGroup()->push( $job );

		$this->handlePriorityLanguages( $this->getRequest(), $page );

		// Logging
		$entry = new ManualLogEntry( 'pagetranslation', 'mark' );
		$entry->setPerformer( $this->getUser() );
		$entry->setTarget( $page->getTitle() );
		$entry->setParameters( [
			'revision' => $newRevisionId,
			'changed' => count( $changed ),
		] );
		$logid = $entry->insert();
		$entry->publish( $logid );

		// Clear more caches
		$page->getTitle()->invalidateCache();

		return false;
	}

	/**
	 * @param WebRequest $request
	 * @param TranslatablePage $page
	 * @return void
	 */
	protected function handlePriorityLanguages( WebRequest $request, TranslatablePage $page ): void {
		// Get the priority languages from the request
		// We've to do some extra work here because if JS is disabled, we will be getting
		// the values split by newline.
		$npLangs = rtrim( trim( $request->getVal( 'prioritylangs', '' ) ), ',' );
		$npLangs = implode( ',', explode( "\n", $npLangs ) );
		$npLangs = array_map( 'trim', explode( ',', $npLangs ) );
		$npLangs = array_unique( $npLangs );

		$npForce = $request->getCheck( 'forcelimit' ) ? 'on' : 'off';
		$npReason = trim( $request->getText( 'priorityreason' ) );

		// Remove invalid language codes.
		$languages = $this->languageNameUtils->getLanguageNames();
		foreach ( $npLangs as $index => $language ) {
			if ( !array_key_exists( $language, $languages ) ) {
				unset( $npLangs[$index] );
			}
		}
		$npLangs = implode( ',', $npLangs );
		if ( $npLangs === '' ) {
			$npLangs = false;
			$npForce = false;
			$npReason = false;
		}

		$groupId = $page->getMessageGroupId();
		// old priority languages
		$opLangs = TranslateMetadata::get( $groupId, 'prioritylangs' );
		$opForce = TranslateMetadata::get( $groupId, 'priorityforce' );
		$opReason = TranslateMetadata::get( $groupId, 'priorityreason' );

		TranslateMetadata::set( $groupId, 'prioritylangs', $npLangs );
		TranslateMetadata::set( $groupId, 'priorityforce', $npForce );
		TranslateMetadata::set( $groupId, 'priorityreason', $npReason );

		if ( $opLangs !== $npLangs || $opForce !== $npForce || $opReason !== $npReason ) {
			$params = [
				'languages' => $npLangs,
				'force' => $npForce,
				'reason' => $npReason,
			];

			$entry = new ManualLogEntry( 'pagetranslation', 'prioritylanguages' );
			$entry->setPerformer( $this->getUser() );
			$entry->setTarget( $page->getTitle() );
			$entry->setParameters( $params );
			$entry->setComment( $npReason );
			$logid = $entry->insert();
			$entry->publish( $logid );
		}
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
				if ( $page['version'] !== self::LATEST_SYNTAX_VERSION ) {
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
				$tagsTextCache[$tagsKey] = $tagsTextCache[$tagsKey] ??
					$this->msg( 'parentheses' )
						->rawParams( $this->getLanguage()->pipeList( $tags ) )
						->escaped();

				$tagList = Html::rawElement(
					'span',
					[ 'class' => 'mw-tpt-actions' ],
					$tagsTextCache[$tagsKey]
				);
			}

			$items[] = "<li>$link $tagList $acts</li>";
		}

		return '<ol>' . implode( "", $items ) . '</ol>';
	}
}
