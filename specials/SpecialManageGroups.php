<?php
/**
 * Implements special page for group management, where file based message
 * groups are be managed.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;
use MediaWiki\Extensions\Translate\MessageSync\MessageSourceChange;

/**
 * Class for special page Special:ManageMessageGroups. On this special page
 * file based message groups can be managed (FileBasedMessageGroup). This page
 * allows updating of the file cache, import and fuzzy for source language
 * messages, as well as import/update of messages in other languages.
 *
 * @ingroup SpecialPage TranslateSpecialPage
 * Rewritten in 2012-04-23
 */
class SpecialManageGroups extends SpecialPage {
	const RIGHT = 'translate-manage';

	/**
	 * @var DifferenceEngine
	 */
	protected $diff;

	/**
	 * @var string Path to the change cdb file.
	 */
	protected $cdb;

	/**
	 * @var bool Has the necessary right specified by the RIGHT constant
	 */
	protected $hasRight = false;

	public function __construct() {
		// Anyone is allowed to see, but actions are restricted
		parent::__construct( 'ManageMessageGroups' );
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
		$out->addModuleStyles( 'ext.translate.special.managegroups.styles' );
		$out->addModules( 'ext.translate.special.managegroups' );
		$out->addHelpLink( 'Help:Extension:Translate/Group_management' );

		$name = $par ?: MessageChangeStorage::DEFAULT_NAME;

		$this->cdb = MessageChangeStorage::getCdbPath( $name );
		if ( !MessageChangeStorage::isValidCdbName( $name ) || !file_exists( $this->cdb ) ) {
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
	protected function getLimit() {
		$limits = [
			1000, // Default max
			ini_get( 'max_input_vars' ),
			ini_get( 'suhosin.post.max_vars' ),
			ini_get( 'suhosin.request.max_vars' )
		];
		// Ignore things not set
		$limits = array_filter( $limits );
		return min( $limits );
	}

	protected function getLegend() {
		$text = $this->diff->addHeader(
			'',
			$this->msg( 'translate-smg-left' )->escaped(),
			$this->msg( 'translate-smg-right' )->escaped()
		);

		return Html::rawElement( 'div', [ 'class' => 'mw-translate-smg-header' ], $text );
	}

	protected function showChanges( $limit ) {
		$contLang = MediaWikiServices::getInstance()->getContentLanguage();

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
		$limit = $limit - 3;

		$reader = \Cdb\Reader::open( $this->cdb );
		$groups = TranslateUtils::deserialize( $reader->get( '#keys' ) );
		foreach ( $groups as $id ) {
			$group = MessageGroups::getGroup( $id );
			if ( !$group ) {
				continue;
			}

			/**
			 * @var MessageSourceChange $sourceChanges
			 */
			$sourceChanges = MessageSourceChange::loadModifications(
				TranslateUtils::deserialize( $reader->get( $id ) )
			);
			$out->addHTML( Html::element( 'h2', [], $group->getLabel() ) );

			// Reduce page existance queries to one per group
			$lb = new LinkBatch();
			$ns = $group->getNamespace();
			$isCap = MWNamespace::isCapitalized( $ns );
			$languages = $sourceChanges->getLanguages();

			foreach ( $languages as $language ) {
				$languageChanges = $sourceChanges->getModificationsForLanguage( $language );
				foreach ( $languageChanges as $type => $changes ) {
					foreach ( $changes as $params ) {
						// Constructing title objects is way slower
						$key = $params['key'];
						if ( $isCap ) {
							$key = $contLang->ucfirst( $key );
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
						$change = $this->formatChange( $group, $language, $type, $params, $limit );
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

		$attribs = [ 'type' => 'submit', 'class' => 'mw-translate-smg-submit' ];
		if ( !$this->hasRight ) {
			$attribs['disabled'] = 'disabled';
			$attribs['title'] = $this->msg( 'translate-smg-notallowed' )->text();
		}
		$button = Html::element( 'button', $attribs, $this->msg( 'translate-smg-submit' )->text() );
		$out->addHTML( $button );
		$out->addHTML( Html::closeElement( 'form' ) );
	}

	/**
	 * @param MessageGroup $group
	 * @param string $language
	 * @param string $type
	 * @param array $params
	 * @param int &$limit
	 * @return string HTML
	 */
	protected function formatChange( MessageGroup $group, $language, $type, $params, &$limit ) {
		$key = $params['key'];
		$title = Title::makeTitleSafe( $group->getNamespace(), "$key/$language" );
		$id = self::changeId( $group->getId(), $language, $type, $key );

		if ( $title && $type === 'addition' && $title->exists() ) {
			// The message has for some reason dropped out from cache
			// or perhaps it is being reused. In any case treat it
			// as a change for display, so the admin can see if
			// action is needed and let the message be processed.
			// Otherwise it will end up in the postponed category
			// forever and will prevent rebuilding the cache, which
			// leads to many other annoying problems.
			$type = 'change';
		} elseif ( $title && ( $type === 'deletion' || $type === 'change' ) && !$title->exists() ) {
			// This happens if a message key has been renamed
			// The change can be ignored.
			return '';
		}

		$text = '';
		$titleLink = $this->getLinkRenderer()->makeLink( $title );

		if ( $type === 'deletion' ) {
			$wiki = ContentHandler::getContentText( Revision::newFromTitle( $title )->getContent() );
			$oldContent = ContentHandler::makeContent( $wiki, $title );
			$newContent = ContentHandler::makeContent( '', $title );

			$this->diff->setContent( $oldContent, $newContent );

			$text = $this->diff->getDiff( $titleLink, '' );
		} elseif ( $type === 'addition' ) {
			$oldContent = ContentHandler::makeContent( '', $title );
			$newContent = ContentHandler::makeContent( $params['content'], $title );

			$this->diff->setContent( $oldContent, $newContent );
			$menu = '';
			if ( $group->getSourceLanguage() === $language && $this->hasRight ) {
				$menu = Html::rawElement( 'button', [
					'class' => 'smg-rename-actions', 'type' => 'button',
					'data-group-id' => $group->getId(), 'data-lang' => $language, 'data-msgkey' => $key,
					'data-msgtitle' => $title->getFullText() ], '' );
			}
			$text = $this->diff->getDiff( '', $titleLink . $menu );
		} elseif ( $type === 'change' ) {
			$wiki = TranslateUtils::getContentForTitle( $title, true );

			$actions = '';
			$importSelected = true;
			if ( $group->getSourceLanguage() === $language ) {
				$importSelected = false;
				$label = $this->msg( 'translate-manage-action-fuzzy' )->text();
				$actions .= Xml::radioLabel( $label, "msg/$id", "fuzzy", "f/$id", true );
			}

			$label = $this->msg( 'translate-manage-action-import' )->text();
			$actions .= Xml::radioLabel( $label, "msg/$id", "import", "imp/$id", $importSelected );

			$label = $this->msg( 'translate-manage-action-ignore' )->text();
			$actions .= Xml::radioLabel( $label, "msg/$id", "ignore", "i/$id" );
			$limit--;

			$oldContent = ContentHandler::makeContent( $wiki, $title );
			$newContent = ContentHandler::makeContent( $params['content'], $title );

			$this->diff->setContent( $oldContent, $newContent );
			$text .= $this->diff->getDiff( $titleLink, $actions );
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

	protected function processSubmit() {
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
		$groups = TranslateUtils::deserialize( $reader->get( '#keys' ) );

		$postponed = [];

		foreach ( $groups as $groupId ) {
			$group = MessageGroups::getGroup( $groupId );
			try {
				$sourceChanges = MessageSourceChange::loadModifications(
					TranslateUtils::deserialize( $reader->get( $groupId ) )
				);
				$groupModificationJobs = [];
				$groupRenameJobData = [];
				$languages = $sourceChanges->getLanguages();
				foreach ( $languages as $language ) {
					// Handle changes, additions, deletions
					$this->handleModificationsSubmit( $group, $sourceChanges, $req,
						$language, $postponed, $groupModificationJobs );

					// Handle renames, this might also add modification jobs based on user selection.
					$this->handleRenameSubmit( $group, $sourceChanges, $req, $language,
						$postponed, $groupRenameJobData, $groupModificationJobs );

					if ( !isset( $postponed[$groupId][$language] ) ) {
						$cache = new MessageGroupCache( $groupId, $language );
						$cache->create();
					}
				}

				$modificationJobs = array_merge( $modificationJobs, $groupModificationJobs );
				$renameJobData = array_merge( $renameJobData, $groupRenameJobData );
			} catch ( Exception $e ) {
				error_log(
					"SpecialManageGroups: Error in processSubmit. Group: $groupId\n" .
					"Exception: $e"
				);

				$errorGroups[] = $group->getLabel();
			}
		}

		JobQueueGroup::singleton()->push( MessageIndexRebuildJob::newJob() );
		JobQueueGroup::singleton()->push( $modificationJobs );
		JobQueueGroup::singleton()->push( $this->createRenameJobs( $renameJobData ) );

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

	protected static function changeId( $groupId, $language, $type, $key ) {
		return 'smg/' . substr( sha1( "$groupId/$language/$type/$key" ), 0, 7 );
	}

	/**
	 * Adds the task-based tabs on Special:Translate and few other special pages.
	 * Hook: SkinTemplateNavigation::SpecialPage
	 * @since 2012-05-14
	 * @param Skin $skin
	 * @param array &$tabs
	 * @return true
	 */
	public static function tabify( Skin $skin, array &$tabs ) {
		$title = $skin->getTitle();
		list( $alias, ) = TranslateUtils::resolveSpecialPageAlias( $title->getText() );

		$pagesInGroup = [
			'ManageMessageGroups' => 'namespaces',
			'AggregateGroups' => 'namespaces',
			'SupportedLanguages' => 'views',
			'TranslationStats' => 'views',
		];
		if ( !isset( $pagesInGroup[$alias] ) ) {
			return true;
		}

		$skin->getOutput()->addModuleStyles( 'ext.translate.tabgroup' );

		$tabs['namespaces'] = [];
		foreach ( $pagesInGroup as $spName => $section ) {
			$spClass = TranslateUtils::getSpecialPage( $spName );

			// DisabledSpecialPage was added in MW 1.33
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

		return true;
	}

	/**
	 * Displays renames
	 * @param MessageGroup $group
	 * @param MessageSourceChange $sourceChanges
	 * @param OutputPage $out
	 * @param string $language
	 * @param int &$limit
	 */
	protected function showRenames(
		MessageGroup $group, MessageSourceChange $sourceChanges, OutputPage $out, $language, &$limit
	) {
		$changes = $sourceChanges->getRenames( $language );
		foreach ( $changes as $key => $params ) {
			if ( !isset( $changes[$key] ) ) {
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

			if ( $sourceChanges->isPreviousState(
				$language, $key, [ MessageSourceChange::ADDITION, MessageSourceChange::CHANGE ]
			) ) {
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

	/**
	 * @param MessageGroup $group
	 * @param array $addedMsg
	 * @param array $deletedMsg
	 * @param string $language
	 * @param bool $isEqual Are the renamed messages equal
	 * @param int &$limit
	 * @return string HTML
	 */
	protected function formatRename(
		MessageGroup $group, $addedMsg, $deletedMsg, $language, $isEqual, &$limit
	) {
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
			$actions .= Xml::radioLabel( $label, "msg/$id", "rename", "imp/$id",  $renameSelected );
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
			$menu = Html::rawElement( 'button', [
				'class' => 'smg-rename-actions', 'type' => 'button',
				'data-group-id' => $group->getId(), 'data-msgkey' => $addedKey,
				'data-msgtitle' => $addedTitle->getFullText()
			], '' );
		}

		$actions = Html::rawElement( 'div', [ 'class' => 'smg-change-import-options' ], $actions );

		$text = $this->diff->getDiff(
			$deletedTitleLink,
			$addedTitleLink . $menu . $actions,
			$isEqual ? $addedMsg['content'] : '' );

		$hidden = Html::hidden( $id, 1 );
		$limit--;
		$text .= $hidden;

		return Html::rawElement( 'div',
			[ 'class' => 'mw-translate-smg-change smg-change-rename' ], $text );
	}

	/**
	 * @param array $currentMsg
	 * @param MessageSourceChange $sourceChanges
	 * @param string $languageCode
	 * @param int $groupNamespace
	 * @param string $selectedVal
	 * @param bool $isSourceLang
	 * @return ?array
	 */
	protected function getRenameJobParams(
		$currentMsg, MessageSourceChange $sourceChanges, $languageCode,
		$groupNamespace, $selectedVal, $isSourceLang = true
	) {
		if ( $selectedVal === 'ignore' ) {
			return null;
		}

		$params = [];
		$replacementContent = '';
		$currentMsgKey = $currentMsg['key'];
		$matchedMsg = $sourceChanges->getMatchedMessage( $languageCode, $currentMsgKey );
		$matchedMsgKey = $matchedMsg['key'];

		if ( $sourceChanges->isPreviousState( $languageCode, $currentMsgKey, [
			MessageSourceChange::ADDITION, MessageSourceChange::CHANGE
		] ) ) {
			$params['target'] = $matchedMsgKey;
			$params['replacement'] = $currentMsgKey;
			$replacementContent = $currentMsg['content'];
		} else {
			$params['target'] = $currentMsgKey;
			$params['replacement'] = $matchedMsgKey;
			$replacementContent = $matchedMsg['content'];
		}

		if ( $selectedVal === 'renamefuzzy' ) {
			$params['fuzzy'] = 'fuzzy';
		} else {
			$params['fuzzy'] = false;
		}

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

	protected function handleRenameSubmit( MessageGroup $group, MessageSourceChange $sourceChanges,
		WebRequest $req, $language, &$postponed, &$jobData, &$modificationJobs
	) {
		$groupId = $group->getId();
		$renames = $sourceChanges->getRenames( $language );
		$isSourceLang = $group->getSourceLanguage() === $language;
		$groupNamespace = $group->getNamespace();

		foreach ( $renames as $key => $params ) {
			if ( !isset( $renames[ $key] ) ) {
				continue;
			}

			$id = self::changeId( $groupId, $language, MessageSourceChange::RENAME, $key );

			list( $renameMissing, $isCurrentKeyPresent ) = $this->isRenameMissing(
				$req, $sourceChanges, $id, $key, $language, $groupId, $isSourceLang
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
				$params, $sourceChanges, $language, $groupNamespace, $selectedVal, $isSourceLang
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

	protected function handleModificationsSubmit(
		MessageGroup $group, MessageSourceChange $sourceChanges, WebRequest $req,
		$language, &$postponed, &$messageUpdateJob
	) {
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

				$fuzzy = $selectedVal === 'fuzzy' ? 'fuzzy' : false;
				$messageUpdateJob[] = MessageUpdateJob::newJob( $title, $params['content'], $fuzzy );
			}
		}
	}

	protected function createRenameJobs( $jobParams ) {
		$jobs = [];
		foreach ( $jobParams as $params ) {
			$jobs[] = MessageUpdateJob::newRenameJob(
				$params['targetTitle'], $params['target'],
				$params['replacement'], $params['fuzzy'], $params['content'],
				$params['others']
			);
		}

		return $jobs;
	}

	/**
	 * Checks if a title still exists and can be processed.
	 *
	 * @param Title $title
	 * @param string $type
	 * @return bool
	 */
	protected function isTitlePresent( Title $title, $type ) {
		if ( ( $type === MessageSourceChange::DELETION || $type === MessageSourceChange::CHANGE ) &&
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
	 * @return bool[] $response
	 * $response = [
	 *   0 => (bool) True if rename is missing, false otherwise.
	 *   1 => (bool) Was the current $id found?
	 * ]
	 */
	protected function isRenameMissing(
		WebRequest $req, MessageSourceChange $sourceChanges, $id, $key,
		$language, $groupId, $isSourceLang
	) {
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

		if ( $isSourceLang === false && $sourceChanges->isEqual( $language, $matchedKey ) ) {
			// For non source language, if strings are equal, they are not shown on the UI
			// and hence not submitted.
			return [ false, $isCurrentKeyPresent ];
		}

		return [ true, $isCurrentKeyPresent ];
	}

	protected function getProcessingErrorMessage(
		array $errorGroups, int $totalGroupCount
	): string {
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
}
