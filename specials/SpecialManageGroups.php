<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\MessageSync\MessageSourceChange;
use MediaWiki\Extension\Translate\Synchronization\DisplayGroupSynchronizationInfo;
use MediaWiki\Extension\Translate\Synchronization\GroupSynchronizationCache;
use MediaWiki\Extension\Translate\Synchronization\MessageUpdateParameter;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\SlotRecord;
use OOUI\ButtonInputWidget;

/**
 * Class for special page Special:ManageMessageGroups. On this special page
 * file based message groups can be managed (FileBasedMessageGroup). This page
 * allows updating of the file cache, import and fuzzy for source language
 * messages, as well as import/update of messages in other languages.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @ingroup SpecialPage TranslateSpecialPage
 * @license GPL-2.0-or-later
 */
class SpecialManageGroups extends SpecialPage {
	private const GROUP_SYNC_INFO_WRAPPER_CLASS = 'smg-group-sync-cache-info';
	private const RIGHT = 'translate-manage';
	/** @var DifferenceEngine */
	protected $diff;
	/** @var string Path to the change cdb file. */
	protected $cdb;
	/** @var bool Has the necessary right specified by the RIGHT constant */
	protected $hasRight = false;
	/** @var Language */
	private $contLang;
	/** @var NamespaceInfo */
	private $nsInfo;
	/** @var RevisionLookup */
	private $revLookup;
	/** @var GroupSynchronizationCache */
	private $synchronizationCache;
	/** @var DisplayGroupSynchronizationInfo */
	private $displayGroupSyncInfo;

	public function __construct(
		Language $contLang,
		NamespaceInfo $nsInfo,
		RevisionLookup $revLookup,
		GroupSynchronizationCache $synchronizationCache
	) {
		// Anyone is allowed to see, but actions are restricted
		parent::__construct( 'ManageMessageGroups' );
		$this->contLang = $contLang;
		$this->nsInfo = $nsInfo;
		$this->revLookup = $revLookup;
		$this->synchronizationCache = $synchronizationCache;
		$this->displayGroupSyncInfo = new DisplayGroupSynchronizationInfo( $this, $this->getLinkRenderer() );
	}

	public function doesWrites() {
		return true;
	}

	protected function getGroupName() {
		return 'translation';
	}

	public function getDescription() {
		return $this->msg( 'managemessagegroups' )->text();
	}

	public function execute( $par ) {
		$this->setHeaders();

		$out = $this->getOutput();
		$out->addModuleStyles( 'ext.translate.specialpages.styles' );
		$out->addModules( 'ext.translate.special.managegroups' );
		$out->addHelpLink( 'Help:Extension:Translate/Group_management' );

		$name = $par ?: MessageChangeStorage::DEFAULT_NAME;

		$this->cdb = MessageChangeStorage::getCdbPath( $name );
		if ( !MessageChangeStorage::isValidCdbName( $name ) || !file_exists( $this->cdb ) ) {
			if ( $this->getConfig()->get( 'TranslateGroupSynchronizationCache' ) ) {
				$out->addHTML(
					$this->displayGroupSyncInfo->getGroupsInSyncHtml(
						$this->synchronizationCache->getGroupsInSync(),
						self::GROUP_SYNC_INFO_WRAPPER_CLASS
					)
				);

				$out->addHTML(
					$this->displayGroupSyncInfo->getHtmlForGroupsWithError(
						$this->synchronizationCache,
						self::GROUP_SYNC_INFO_WRAPPER_CLASS,
						$this->getLanguage()
					)
				);
			}

			// @todo Tell them when changes was last checked/process
			// or how to initiate recheck.
			$out->addWikiMsg( 'translate-smg-nochanges' );

			return;
		}

		$user = $this->getUser();
		$this->hasRight = $user->isAllowed( self::RIGHT );

		$req = $this->getRequest();
		if ( !$req->wasPosted() ) {
			$this->showChanges( $this->getLimit() );

			return;
		}

		$token = $req->getVal( 'token' );
		if ( !$this->hasRight || !$user->matchEditToken( $token ) ) {
			throw new PermissionsError( self::RIGHT );
		}

		$this->processSubmit();
	}

	/**
	 * How many changes can be shown per page.
	 * @return int
	 */
	protected function getLimit(): int {
		$limits = [
			1000, // Default max
			ini_get( 'max_input_vars' ),
			ini_get( 'suhosin.post.max_vars' ),
			ini_get( 'suhosin.request.max_vars' )
		];
		// Ignore things not set
		$limits = array_filter( $limits );
		return (int)min( $limits );
	}

	protected function getLegend(): string {
		$text = $this->diff->addHeader(
			'',
			$this->msg( 'translate-smg-left' )->escaped(),
			$this->msg( 'translate-smg-right' )->escaped()
		);

		return Html::rawElement( 'div', [ 'class' => 'mw-translate-smg-header' ], $text );
	}

	protected function showChanges( int $limit ): void {
		$diff = new DifferenceEngine( $this->getContext() );
		$diff->showDiffStyle();
		$diff->setReducedLineNumbers();
		$this->diff = $diff;

		$out = $this->getOutput();
		$out->addHTML(
			'' .
			Html::openElement( 'form', [ 'method' => 'post' ] ) .
			Html::hidden( 'title', $this->getPageTitle()->getPrefixedText(), [
				'id' => 'smgPageTitle'
			] ) .
			Html::hidden( 'token', $this->getUser()->getEditToken() ) .
			Html::hidden( 'changesetModifiedTime',
				MessageChangeStorage::getLastModifiedTime( $this->cdb ) ) .
			$this->getLegend()
		);

		// The above count as three
		$limit -= 3;

		$groupSyncCacheEnabled = $this->getConfig()->get( 'TranslateGroupSynchronizationCache' );
		if ( $groupSyncCacheEnabled ) {
			$out->addHTML(
				$this->displayGroupSyncInfo->getGroupsInSyncHtml(
					$this->synchronizationCache->getGroupsInSync(),
					self::GROUP_SYNC_INFO_WRAPPER_CLASS
				)
			);

			$out->addHTML(
				$this->displayGroupSyncInfo->getHtmlForGroupsWithError(
					$this->synchronizationCache,
					self::GROUP_SYNC_INFO_WRAPPER_CLASS,
					$this->getLanguage()
				)
			);
		}

		$reader = \Cdb\Reader::open( $this->cdb );
		$groups = $this->getGroupsFromCdb( $reader );
		foreach ( $groups as $id => $group ) {
			$sourceChanges = MessageSourceChange::loadModifications(
				TranslateUtils::deserialize( $reader->get( $id ) )
			);
			$out->addHTML( Html::element( 'h2', [], $group->getLabel() ) );

			if ( $groupSyncCacheEnabled && $this->synchronizationCache->groupHasErrors( $id ) ) {
				$out->addHTML(
					Html::warningBox( $this->msg( 'translate-smg-group-sync-error-warn' )->escaped(), 'center' )
				);
			}

			// Reduce page existance queries to one per group
			$lb = new LinkBatch();
			$ns = $group->getNamespace();
			$isCap = $this->nsInfo->isCapitalized( $ns );
			$languages = $sourceChanges->getLanguages();

			foreach ( $languages as $language ) {
				$languageChanges = $sourceChanges->getModificationsForLanguage( $language );
				foreach ( $languageChanges as $type => $changes ) {
					foreach ( $changes as $params ) {
						// Constructing title objects is way slower
						$key = $params['key'];
						if ( $isCap ) {
							$key = $this->contLang->ucfirst( $key );
						}
						$lb->add( $ns, "$key/$language" );
					}
				}
			}
			$lb->execute();

			foreach ( $languages as $language ) {
				// Handle and generate UI for additions, deletions, change
				$changes = [];
				$changes[ MessageSourceChange::ADDITION ] = $sourceChanges->getAdditions( $language );
				$changes[ MessageSourceChange::DELETION ] = $sourceChanges->getDeletions( $language );
				$changes[ MessageSourceChange::CHANGE ] = $sourceChanges->getChanges( $language );

				foreach ( $changes as $type => $messages ) {
					foreach ( $messages as $params ) {
						$change = $this->formatChange( $group, $sourceChanges, $language, $type, $params, $limit );
						$out->addHTML( $change );

						if ( $limit <= 0 ) {
							// We need to restrict the changes per page per form submission
							// limitations as well as performance.
							$out->wrapWikiMsg( "<div class=warning>\n$1\n</div>", 'translate-smg-more' );
							break 4;
						}
					}
				}

				// Handle and generate UI for renames
				$this->showRenames( $group, $sourceChanges, $out, $language, $limit );
			}
		}

		$out->enableOOUI();
		$button = new ButtonInputWidget( [
			'type' => 'submit',
			'label' => $this->msg( 'translate-smg-submit' )->plain(),
			'disabled' => !$this->hasRight ? 'disabled' : null,
			'classes' => [ 'mw-translate-smg-submit' ],
			'title' => !$this->hasRight ? $this->msg( 'translate-smg-notallowed' )->plain() : null,
			'flags' => [ 'primary', 'progressive' ],
		] );
		$out->addHTML( $button );
		$out->addHTML( Html::closeElement( 'form' ) );
	}

	protected function formatChange(
		MessageGroup $group,
		MessageSourceChange $changes,
		string $language,
		string $type,
		array $params,
		int &$limit
	): string {
		$key = $params['key'];
		$title = Title::makeTitleSafe( $group->getNamespace(), "$key/$language" );
		$id = self::changeId( $group->getId(), $language, $type, $key );
		$noticeHtml = '';
		$isReusedKey = false;

		if ( $title && $type === 'addition' && $title->exists() ) {
			// The message has for some reason dropped out from cache
			// or perhaps it is being reused. In any case treat it
			// as a change for display, so the admin can see if
			// action is needed and let the message be processed.
			// Otherwise it will end up in the postponed category
			// forever and will prevent rebuilding the cache, which
			// leads to many other annoying problems.
			$type = 'change';
			$noticeHtml .= Html::warningBox( $this->msg( 'translate-manage-key-reused' )->text() );
			$isReusedKey = true;
		} elseif ( $title && ( $type === 'deletion' || $type === 'change' ) && !$title->exists() ) {
			// This happens if a message key has been renamed
			// The change can be ignored.
			return '';
		}

		$text = '';
		$titleLink = $this->getLinkRenderer()->makeLink( $title );

		if ( $type === 'deletion' ) {
			$content = $this->revLookup
				->getRevisionByTitle( $title )
				->getContent( SlotRecord::MAIN );
			$wiki = ( $content instanceof TextContent ) ? $content->getText() : '';

			if ( $wiki === '' ) {
				$noticeHtml .= Html::warningBox(
					$this->msg( 'translate-manage-empty-content' )->text()
				);
			}

			$oldContent = ContentHandler::makeContent( $wiki, $title );
			$newContent = ContentHandler::makeContent( '', $title );
			$this->diff->setContent( $oldContent, $newContent );
			$text = $this->diff->getDiff( $titleLink, '', $noticeHtml );
		} elseif ( $type === 'addition' ) {
			$menu = '';
			$sourceLanguage = $group->getSourceLanguage();
			if ( $sourceLanguage === $language ) {
				if ( $this->hasRight ) {
					$menu = Html::rawElement(
						'button',
						[
							'class' => 'smg-rename-actions',
							'type' => 'button',
							'data-group-id' => $group->getId(),
							'data-lang' => $language,
							'data-msgkey' => $key,
							'data-msgtitle' => $title->getFullText()
						],
						''
					);
				}
			} elseif ( !self::isMessageDefinitionPresent( $group, $changes, $key ) ) {
				$noticeHtml .= Html::warningBox(
					$this->msg( 'translate-manage-source-message-not-found' )->text(),
					'mw-translate-smg-notice-important'
				);

				// Automatically ignore messages that don't have a definitions
				$menu = Html::hidden( "msg/$id", 'ignore', [ 'id' => "i/$id" ] );
				$limit--;
			}

			if ( $params['content'] === '' ) {
				$noticeHtml .= Html::warningBox(
					$this->msg( 'translate-manage-empty-content' )->text()
				);
			}

			$oldContent = ContentHandler::makeContent( '', $title );
			$newContent = ContentHandler::makeContent( $params['content'], $title );
			$this->diff->setContent( $oldContent, $newContent );
			$text = $this->diff->getDiff( '', $titleLink . $menu, $noticeHtml );
		} elseif ( $type === 'change' ) {
			$wiki = TranslateUtils::getContentForTitle( $title, true );

			$actions = '';
			$importSelected = true;
			$sourceLanguage = $group->getSourceLanguage();

			if ( $sourceLanguage === $language ) {
				$importSelected = false;
				$label = $this->msg( 'translate-manage-action-fuzzy' )->text();
				$actions .= Xml::radioLabel( $label, "msg/$id", "fuzzy", "f/$id", true );
			}

			if (
				$sourceLanguage !== $language &&
				$isReusedKey &&
				!self::isMessageDefinitionPresent( $group, $changes, $key )
			) {
				$noticeHtml .= Html::warningBox(
					$this->msg( 'translate-manage-source-message-not-found' )->text(),
					'mw-translate-smg-notice-important'
				);

				// Automatically ignore messages that don't have a definitions
				$actions .= Html::hidden( "msg/$id", 'ignore', [ 'id' => "i/$id" ] );
				$limit--;
			} else {
				$label = $this->msg( 'translate-manage-action-import' )->text();
				$actions .= Xml::radioLabel( $label, "msg/$id", "import", "imp/$id", $importSelected );

				$label = $this->msg( 'translate-manage-action-ignore' )->text();
				$actions .= Xml::radioLabel( $label, "msg/$id", "ignore", "i/$id" );
				$limit--;
			}

			$oldContent = ContentHandler::makeContent( $wiki, $title );
			$newContent = ContentHandler::makeContent( $params['content'], $title );

			$this->diff->setContent( $oldContent, $newContent );
			$text .= $this->diff->getDiff( $titleLink, $actions, $noticeHtml );
		}

		$hidden = Html::hidden( $id, 1 );
		$limit--;
		$text .= $hidden;
		$classes = "mw-translate-smg-change smg-change-$type";

		if ( $limit < 0 ) {
			// Don't add if one of the fields might get dropped of at submission
			return '';
		}

		return Html::rawElement( 'div', [ 'class' => $classes ], $text );
	}

	protected function processSubmit(): void {
		$req = $this->getRequest();
		$out = $this->getOutput();
		$errorGroups = [];

		$modificationJobs = $renameJobData = [];
		$lastModifiedTime = intval( $req->getVal( 'changesetModifiedTime' ) );

		if ( !MessageChangeStorage::isModifiedSince( $this->cdb, $lastModifiedTime ) ) {
			$out->addWikiMsg( 'translate-smg-changeset-modified' );
			return;
		}

		$reader = \Cdb\Reader::open( $this->cdb );
		$groups = $this->getGroupsFromCdb( $reader );
		$groupSyncCacheEnabled = $this->getConfig()->get( 'TranslateGroupSynchronizationCache' );
		$postponed = [];

		foreach ( $groups as $groupId => $group ) {
			try {
				$changes = TranslateUtils::deserialize( $reader->get( $groupId ) );
				if ( $groupSyncCacheEnabled && $this->synchronizationCache->groupHasErrors( $groupId ) ) {
					$postponed[$groupId] = $changes;
					continue;
				}

				$sourceChanges = MessageSourceChange::loadModifications( $changes );
				$groupModificationJobs = [];
				$groupRenameJobData = [];
				$languages = $sourceChanges->getLanguages();
				foreach ( $languages as $language ) {
					// Handle changes, additions, deletions
					$this->handleModificationsSubmit(
						$group,
						$sourceChanges,
						$req,
						$language,
						$postponed,
						$groupModificationJobs
					);

					// Handle renames, this might also add modification jobs based on user selection.
					$this->handleRenameSubmit(
						$group,
						$sourceChanges,
						$req,
						$language,
						$postponed,
						$groupRenameJobData,
						$groupModificationJobs
					);

					if ( !isset( $postponed[$groupId][$language] ) ) {
						$group->getMessageGroupCache( $language )->create();
					}
				}

				if ( $groupSyncCacheEnabled && !isset( $postponed[ $groupId ] ) ) {
					$this->synchronizationCache->markGroupAsReviewed( $groupId );
				}

				$modificationJobs[$groupId] = $groupModificationJobs;
				$renameJobData[$groupId] = $groupRenameJobData;
			} catch ( Exception $e ) {
				error_log(
					"SpecialManageGroups: Error in processSubmit. Group: $groupId\n" .
					"Exception: $e"
				);

				$errorGroups[] = $group->getLabel();
			}
		}

		$renameJobs = $this->createRenameJobs( $renameJobData );
		$this->startSync( $modificationJobs, $renameJobs );

		$reader->close();
		rename( $this->cdb, $this->cdb . '-' . wfTimestamp() );

		if ( $errorGroups ) {
			$errorMsg = $this->getProcessingErrorMessage( $errorGroups, count( $groups ) );
			$out->addElement(
				'p',
				[ 'class' => 'warningbox mw-translate-smg-submitted' ],
				$errorMsg
			);
		}

		if ( count( $postponed ) ) {
			$postponedSourceChanges = [];
			foreach ( $postponed as $groupId => $changes ) {
				$postponedSourceChanges[$groupId] = MessageSourceChange::loadModifications( $changes );
			}
			MessageChangeStorage::writeChanges( $postponedSourceChanges, $this->cdb );

			$this->showChanges( $this->getLimit() );
		} elseif ( $errorGroups === [] ) {
			$out->addWikiMsg( 'translate-smg-submitted' );
		}
	}

	protected static function changeId(
		string $groupId,
		string $language,
		string $type,
		string $key
	): string {
		return 'smg/' . substr( sha1( "$groupId/$language/$type/$key" ), 0, 7 );
	}

	/**
	 * Adds the task-based tabs on Special:Translate and few other special pages.
	 * Hook: SkinTemplateNavigation::SpecialPage
	 * @since 2012-05-14
	 * @param Skin $skin
	 * @param array &$tabs
	 */
	public static function tabify( Skin $skin, array &$tabs ): void {
		$title = $skin->getTitle();
		$specialPageFactory = MediaWikiServices::getInstance()->getSpecialPageFactory();
		[ $alias, ] = $specialPageFactory->resolveAlias( $title->getText() );

		$pagesInGroup = [
			'ManageMessageGroups' => 'namespaces',
			'AggregateGroups' => 'namespaces',
			'SupportedLanguages' => 'views',
			'TranslationStats' => 'views',
		];
		if ( !isset( $pagesInGroup[$alias] ) ) {
			return;
		}

		$tabs['namespaces'] = [];
		foreach ( $pagesInGroup as $spName => $section ) {
			$spClass = $specialPageFactory->getPage( $spName );

			if ( $spClass === null || $spClass instanceof DisabledSpecialPage ) {
				continue; // Page explicitly disabled
			}
			$spTitle = $spClass->getPageTitle();

			$tabs[$section][strtolower( $spName )] = [
				'text' => $spClass->getDescription(),
				'href' => $spTitle->getLocalURL(),
				'class' => $alias === $spName ? 'selected' : '',
			];
		}
	}

	/**
	 * Check if the message definition is present as an incoming addition
	 * OR exists already on the wiki
	 *
	 * @internal - For internal use only
	 * @param MessageGroup $group
	 * @param MessageSourceChange $changes
	 * @param string $msgKey
	 * @return bool
	 */
	private static function isMessageDefinitionPresent(
		MessageGroup $group,
		MessageSourceChange $changes,
		string $msgKey
	): bool {
		$sourceLanguage = $group->getSourceLanguage();
		if ( $changes->findMessage( $sourceLanguage, $msgKey, [ MessageSourceChange::ADDITION ] ) ) {
			return true;
		}

		$namespace = $group->getNamespace();
		$sourceHandle = new MessageHandle( Title::makeTitle( $namespace, $msgKey ) );
		return $sourceHandle->isValid();
	}

	private function showRenames(
		MessageGroup $group,
		MessageSourceChange $sourceChanges,
		OutputPage $out,
		string $language,
		int &$limit
	): void {
		$changes = $sourceChanges->getRenames( $language );
		foreach ( $changes as $key => $params ) {
			// Since we're removing items from the array within the loop add
			// a check here to ensure that the current key is still set.
			if ( !isset( $changes[ $key ] ) ) {
				continue;
			}

			if ( $group->getSourceLanguage() !== $language &&
				$sourceChanges->isEqual( $language, $key ) ) {
					// This is a translation rename, that does not have any changes.
					// We can group this along with the source rename.
					continue;
			}

			// Determine added key, and corresponding removed key.
			$firstMsg = $params;
			$secondKey = $sourceChanges->getMatchedKey( $language, $key );
			$secondMsg = $sourceChanges->getMatchedMessage( $language, $key );

			if (
				$sourceChanges->isPreviousState(
					$language,
					$key,
					[ MessageSourceChange::ADDITION, MessageSourceChange::CHANGE ]
				)
			) {
				$addedMsg = $firstMsg;
				$deletedMsg = $secondMsg;
			} else {
				$addedMsg = $secondMsg;
				$deletedMsg = $firstMsg;
			}

			$change = $this->formatRename(
				$group,
				$addedMsg,
				$deletedMsg,
				$language,
				$sourceChanges->isEqual( $language, $key ),
				$limit
			);
			$out->addHTML( $change );

			// no need to process the second key again.
			unset( $changes[$secondKey] );

			if ( $limit <= 0 ) {
				// We need to restrict the changes per page per form submission
				// limitations as well as performance.
				$out->wrapWikiMsg( "<div class=warning>\n$1\n</div>", 'translate-smg-more' );
				break;
			}
		}
	}

	private function formatRename(
		MessageGroup $group,
		array $addedMsg,
		array $deletedMsg,
		string $language,
		bool $isEqual,
		int &$limit
	): string {
		$addedKey = $addedMsg['key'];
		$deletedKey = $deletedMsg['key'];
		$actions = '';

		$addedTitle = Title::makeTitleSafe( $group->getNamespace(), "$addedKey/$language" );
		$deletedTitle = Title::makeTitleSafe( $group->getNamespace(), "$deletedKey/$language" );
		$id = self::changeId( $group->getId(), $language, MessageSourceChange::RENAME, $addedKey );

		$addedTitleLink = $this->getLinkRenderer()->makeLink( $addedTitle );
		$deletedTitleLink = $this->getLinkRenderer()->makeLink( $deletedTitle );

		$renameSelected = true;
		if ( $group->getSourceLanguage() === $language ) {
			if ( !$isEqual ) {
				$renameSelected = false;
				$label = $this->msg( 'translate-manage-action-rename-fuzzy' )->text();
				$actions .= Xml::radioLabel( $label, "msg/$id", "renamefuzzy", "rf/$id", true );
			}

			$label = $this->msg( 'translate-manage-action-rename' )->text();
			$actions .= Xml::radioLabel( $label, "msg/$id", "rename", "imp/$id", $renameSelected );
		} else {
			$label = $this->msg( 'translate-manage-action-import' )->text();
			$actions .= Xml::radioLabel( $label, "msg/$id", "import", "imp/$id", true );
		}

		if ( $group->getSourceLanguage() !== $language ) {
			// Allow user to ignore changes to non-source languages.
			$label = $this->msg( 'translate-manage-action-ignore-change' )->text();
			$actions .= Xml::radioLabel( $label, "msg/$id", "ignore", "i/$id" );
		}
		$limit--;

		$addedContent = ContentHandler::makeContent( $addedMsg['content'], $addedTitle );
		$deletedContent = ContentHandler::makeContent( $deletedMsg['content'], $deletedTitle );
		$this->diff->setContent( $deletedContent, $addedContent );

		$menu = '';
		if ( $group->getSourceLanguage() === $language && $this->hasRight ) {
			// Only show rename and add as new option for source language.
			$menu = Html::rawElement(
				'button',
				[
					'class' => 'smg-rename-actions',
					'type' => 'button',
					'data-group-id' => $group->getId(),
					'data-msgkey' => $addedKey,
					'data-msgtitle' => $addedTitle->getFullText()
				], ''
			);
		}

		$actions = Html::rawElement( 'div', [ 'class' => 'smg-change-import-options' ], $actions );

		$text = $this->diff->getDiff(
			$deletedTitleLink,
			$addedTitleLink . $menu . $actions,
			$isEqual ? htmlspecialchars( $addedMsg['content'] ) : ''
		);

		$hidden = Html::hidden( $id, 1 );
		$limit--;
		$text .= $hidden;

		return Html::rawElement(
			'div',
			[ 'class' => 'mw-translate-smg-change smg-change-rename' ],
			$text
		);
	}

	private function getRenameJobParams(
		array $currentMsg,
		MessageSourceChange $sourceChanges,
		string $languageCode,
		int $groupNamespace,
		string $selectedVal,
		bool $isSourceLang = true
	): ?array {
		if ( $selectedVal === 'ignore' ) {
			return null;
		}

		$params = [];
		$replacementContent = '';
		$currentMsgKey = $currentMsg['key'];
		$matchedMsg = $sourceChanges->getMatchedMessage( $languageCode, $currentMsgKey );
		$matchedMsgKey = $matchedMsg['key'];

		if (
			$sourceChanges->isPreviousState(
				$languageCode,
				$currentMsgKey,
				[ MessageSourceChange::ADDITION, MessageSourceChange::CHANGE ]
			)
		) {
			$params['target'] = $matchedMsgKey;
			$params['replacement'] = $currentMsgKey;
			$replacementContent = $currentMsg['content'];
		} else {
			$params['target'] = $currentMsgKey;
			$params['replacement'] = $matchedMsgKey;
			$replacementContent = $matchedMsg['content'];
		}

		$params['fuzzy'] = $selectedVal === 'renamefuzzy';

		$params['content'] = $replacementContent;

		if ( $isSourceLang ) {
			$params['targetTitle'] = Title::newFromText(
				TranslateUtils::title( $params['target'], $languageCode, $groupNamespace ),
				$groupNamespace
			);
			$params['others'] = [];
		}

		return $params;
	}

	private function handleRenameSubmit(
		MessageGroup $group,
		MessageSourceChange $sourceChanges,
		WebRequest $req,
		string $language,
		array &$postponed,
		array &$jobData,
		array &$modificationJobs
	): void {
		$groupId = $group->getId();
		$renames = $sourceChanges->getRenames( $language );
		$isSourceLang = $group->getSourceLanguage() === $language;
		$groupNamespace = $group->getNamespace();

		foreach ( $renames as $key => $params ) {
			// Since we're removing items from the array within the loop add
			// a check here to ensure that the current key is still set.
			if ( !isset( $renames[$key] ) ) {
				continue;
			}

			$id = self::changeId( $groupId, $language, MessageSourceChange::RENAME, $key );

			[ $renameMissing, $isCurrentKeyPresent ] = $this->isRenameMissing(
				$req,
				$sourceChanges,
				$id,
				$key,
				$language,
				$groupId,
				$isSourceLang
			);

			if ( $renameMissing ) {
				// we probably hit the limit with number of post parameters since neither
				// addition or deletion key is present.
				$postponed[$groupId][$language][MessageSourceChange::RENAME][$key] = $params;
				continue;
			}

			if ( !$isCurrentKeyPresent ) {
				// still don't process this key, and wait for the matched rename
				continue;
			}

			$selectedVal = $req->getVal( "msg/$id" );
			$jobParams = $this->getRenameJobParams(
				$params,
				$sourceChanges,
				$language,
				$groupNamespace,
				$selectedVal,
				$isSourceLang
			);

			if ( $jobParams === null ) {
				continue;
			}

			$targetStr = $jobParams[ 'target' ];
			if ( $isSourceLang ) {
				$jobData[ $targetStr ] = $jobParams;
			} elseif ( isset( $jobData[ $targetStr ] ) ) {
				// We are grouping the source rename, and content changes in other languages
				// for the message together into a single job in order to avoid race conditions
				// since jobs are not guaranteed to be run in order.
				$jobData[ $targetStr ][ 'others' ][ $language ] = $jobParams[ 'content' ];
			} else {
				// the source was probably ignored, we should add this as a modification instead,
				// since the source is not going to be renamed.
				$title = Title::newFromText(
					TranslateUtils::title( $targetStr, $language, $groupNamespace ),
					$groupNamespace
				);
				$modificationJobs[] = MessageUpdateJob::newJob( $title, $jobParams['content'] );
			}

			// remove the matched key in order to avoid double processing.
			$matchedKey = $sourceChanges->getMatchedKey( $language, $key );
			unset( $renames[$matchedKey] );
		}
	}

	private function handleModificationsSubmit(
		MessageGroup $group,
		MessageSourceChange $sourceChanges,
		WebRequest $req,
		string $language,
		array &$postponed,
		array &$messageUpdateJob
	): void {
		$groupId = $group->getId();
		$subchanges = $sourceChanges->getModificationsForLanguage( $language );

		// Ignore renames
		unset( $subchanges[ MessageSourceChange::RENAME ] );

		// Handle additions, deletions, and changes.
		foreach ( $subchanges as $type => $messages ) {
			foreach ( $messages as $index => $params ) {
				$key = $params['key'];
				$id = self::changeId( $groupId, $language, $type, $key );
				$title = Title::makeTitleSafe( $group->getNamespace(), "$key/$language" );

				if ( !$this->isTitlePresent( $title, $type ) ) {
					continue;
				}

				if ( !$req->getCheck( $id ) ) {
					// We probably hit the limit with number of post parameters.
					$postponed[$groupId][$language][$type][$index] = $params;
					continue;
				}

				$selectedVal = $req->getVal( "msg/$id" );
				if ( $type === MessageSourceChange::DELETION || $selectedVal === 'ignore' ) {
					continue;
				}

				$fuzzy = $selectedVal === 'fuzzy';
				$messageUpdateJob[] = MessageUpdateJob::newJob( $title, $params['content'], $fuzzy );
			}
		}
	}

	/**
	 * @param array $jobParams
	 * @return MessageUpdateJob[][]
	 */
	private function createRenameJobs( array $jobParams ): array {
		$jobs = [];
		foreach ( $jobParams as $groupId => $groupJobParams ) {
			$jobs[$groupId] = $jobs[$groupId] ?? [];
			foreach ( $groupJobParams as $params ) {
				$jobs[$groupId][] = MessageUpdateJob::newRenameJob(
					$params['targetTitle'],
					$params['target'],
					$params['replacement'],
					$params['fuzzy'],
					$params['content'],
					$params['others']
				);
			}
		}

		return $jobs;
	}

	/**
	 * Checks if a title still exists and can be processed.
	 * @param Title $title
	 * @param string $type
	 * @return bool
	 */
	private function isTitlePresent( Title $title, string $type ): bool {
		// phpcs:ignore SlevomatCodingStandard.ControlStructures.UselessIfConditionWithReturn
		if (
			( $type === MessageSourceChange::DELETION || $type === MessageSourceChange::CHANGE ) &&
			!$title->exists()
		) {
			// This means that this change was probably introduced due to a rename
			// which removed the key. No need to process.
			return false;
		}
		return true;
	}

	/**
	 * Checks if a renamed message key is missing from the user request submission.
	 * Checks the current key and the matched key. This is needed because as the
	 * keys in the wiki are not submitted along with the request, only the incoming
	 * modified keys are submitted.
	 * @param WebRequest $req
	 * @param MessageSourceChange $sourceChanges
	 * @param string $id
	 * @param string $key
	 * @param string $language
	 * @param string $groupId
	 * @param bool $isSourceLang
	 * @return bool[]
	 * $response = [
	 *   0 => (bool) True if rename is missing, false otherwise.
	 *   1 => (bool) Was the current $id found?
	 * ]
	 */
	private function isRenameMissing(
		WebRequest $req,
		MessageSourceChange $sourceChanges,
		string $id,
		string $key,
		string $language,
		string $groupId,
		bool $isSourceLang
	): array {
		if ( $req->getCheck( $id ) ) {
			return [ false, true ];
		}

		$isCurrentKeyPresent = false;

		// Checked the matched key is also missing to confirm if its truly missing
		$matchedKey = $sourceChanges->getMatchedKey( $language, $key );
		$matchedId = self::changeId( $groupId, $language, MessageSourceChange::RENAME, $matchedKey );
		if ( $req->getCheck( $matchedId ) ) {
			return [ false, $isCurrentKeyPresent ];
		}

		// For non source language, if strings are equal, they are not shown on the UI
		// and hence not submitted.
		return [
			$isSourceLang || !$sourceChanges->isEqual( $language, $matchedKey ),
			$isCurrentKeyPresent
		];
	}

	private function getProcessingErrorMessage( array $errorGroups, int $totalGroupCount ): string {
		// Number of error groups, are less than the total groups processed.
		if ( count( $errorGroups ) < $totalGroupCount ) {
			$errorMsg = $this->msg( 'translate-smg-submitted-with-failure' )
				->numParams( count( $errorGroups ) )
				->params(
					$this->getLanguage()->commaList( $errorGroups ),
					$this->msg( 'translate-smg-submitted-others-processing' )
				)->text();
		} else {
			$errorMsg = trim(
				$this->msg( 'translate-smg-submitted-with-failure' )
					->numParams( count( $errorGroups ) )
					->params( $this->getLanguage()->commaList( $errorGroups ), '' )
					->text()
			);
		}

		return $errorMsg;
	}

	/**
	 * @param \Cdb\Reader $reader
	 * @return FileBasedMessageGroup[]
	 */
	private function getGroupsFromCdb( \Cdb\Reader $reader ): array {
		$groups = [];
		$groupIds = TranslateUtils::deserialize( $reader->get( '#keys' ) );
		foreach ( $groupIds as $id ) {
			$groups[$id] = MessageGroups::getGroup( $id );
		}
		return array_filter( $groups );
	}

	/**
	 * Add jobs to the queue, updates the interim cache, and start sync process for the group.
	 * @param MessageUpdateJob[][] $modificationJobs
	 * @param MessageUpdateJob[][] $renameJobs
	 * @return void
	 */
	private function startSync( array $modificationJobs, array $renameJobs ): void {
		// We are adding an empty array for groups that have no jobs. This is mainly done to
		// avoid adding unnecessary checks. Remove those using array_filter
		$modificationGroupIds = array_keys( array_filter( $modificationJobs ) );
		$renameGroupIds = array_keys( array_filter( $renameJobs ) );
		$uniqueGroupIds = array_unique( array_merge( $modificationGroupIds, $renameGroupIds ) );
		$messageIndexInstance = MessageIndex::singleton();
		$jobQueueInstance = TranslateUtils::getJobQueueGroup();

		foreach ( $uniqueGroupIds as $groupId ) {
			$messages = [];
			$messageKeys = [];
			$groupJobs = [];

			$groupRenameJobs = $renameJobs[$groupId] ?? [];
			/** @var MessageUpdateJob $job */
			foreach ( $groupRenameJobs as $job ) {
				$groupJobs[] = $job;
				$messageUpdateParam = MessageUpdateParameter::createFromJob( $job );
				$messages[] = $messageUpdateParam;

				// Build the handle to add the message key in interim cache
				$replacement = $messageUpdateParam->getReplacementValue();
				$targetTitle = Title::makeTitle( $job->getTitle()->getNamespace(), $replacement );
				$messageKeys[] = ( new MessageHandle( $targetTitle ) )->getKey();
			}

			$groupModificationJobs = $modificationJobs[$groupId] ?? [];
			/** @var MessageUpdateJob $job */
			foreach ( $groupModificationJobs as $job ) {
				$groupJobs[] = $job;
				$messageUpdateParam = MessageUpdateParameter::createFromJob( $job );
				$messages[] = $messageUpdateParam;

				$messageKeys[] = ( new MessageHandle( $job->getTitle() ) )->getKey();
			}

			// Store all message keys in the interim cache - we're particularly interested in new
			// and renamed messages, but it's cleaner to just store everything.
			$group = MessageGroups::getGroup( $groupId );
			$messageIndexInstance->storeInterim( $group, $messageKeys );

			if ( $this->getConfig()->get( 'TranslateGroupSynchronizationCache' ) ) {
				$this->synchronizationCache->addMessages( $groupId, ...$messages );
				$this->synchronizationCache->markGroupForSync( $groupId );

				LoggerFactory::getInstance( 'Translate.GroupSynchronization' )->info(
					'[' . __CLASS__ . '] Synchronization started for {groupId} by {user}',
					[
						'groupId' => $groupId,
						'user' => $this->getUser()->getName()
					]
				);
			}

			// There is posibility for a race condition here: the translate_cache table / group sync
			// cache is not yet populated with the messages to be processed, but the jobs start
			// running and try to remove the message from the cache. This results in a "Key not found"
			// error. Avoid this condition by using a DeferredUpdate.
			DeferredUpdates::addCallableUpdate(
				static function () use ( $jobQueueInstance, $groupJobs ) {
					$jobQueueInstance->push( $groupJobs );
				}
			);

		}
	}
}
