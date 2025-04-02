<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use DifferenceEngine;
use InvalidArgumentException;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Content\ContentHandler;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Html\Html;
use MediaWiki\Language\Language;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\MutableRevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\Xml\Xml;
use MessageGroup;
use MessageLocalizer;
use RecentChange;
use RuntimeException;

/**
 * Class which encapsulates message importing. It scans for changes (new, changed, deleted),
 * displays them in pretty way with diffs and finally executes the actions the user choices.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2009-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0-or-later
 */
class MessageWebImporter {
	private Title $title;
	private User $user;
	private MessageGroup $group;
	private string $code;
	/** @var int|null */
	private $time;
	private MessageLocalizer $messageLocalizer;
	/** Maximum processing time in seconds. */
	private const MAX_PROCESSING_TIME = 43;

	/**
	 * @param Title $title
	 * @param User $user
	 * @param MessageLocalizer $messageLocalizer
	 * @param MessageGroup|string|null $group
	 * @param string $code
	 */
	public function __construct(
		Title $title,
		User $user,
		MessageLocalizer $messageLocalizer,
		$group = null,
		string $code = 'en'
	) {
		$this->setTitle( $title );
		$this->setUser( $user );
		$this->setGroup( $group );
		$this->setCode( $code );
		$this->messageLocalizer = $messageLocalizer;
	}

	/** Wrapper for consistency with SpecialPage */
	public function getTitle(): Title {
		return $this->title;
	}

	public function setTitle( Title $title ): void {
		$this->title = $title;
	}

	public function getUser(): User {
		return $this->user;
	}

	public function setUser( User $user ): void {
		$this->user = $user;
	}

	public function getGroup(): MessageGroup {
		return $this->group;
	}

	/** @param MessageGroup|string $group MessageGroup object or group ID */
	public function setGroup( $group ): void {
		if ( is_string( $group ) ) {
			$group = MessageGroups::getGroup( $group );
			if ( !$group ) {
				throw new InvalidArgumentException( __METHOD__ . ' called with invalid group ID ' . $group );
			}
		}
		$this->group = $group;
	}

	public function getCode(): string {
		return $this->code;
	}

	public function setCode( string $code = 'en' ): void {
		$this->code = $code;
	}

	protected function getAction(): string {
		return $this->getTitle()->getLocalURL();
	}

	protected function doHeader(): string {
		$formParams = [
			'method' => 'post',
			'action' => $this->getAction(),
			'class' => 'mw-translate-manage'
		];

		$csrfTokenSet = RequestContext::getMain()->getCsrfTokenSet();
		return Xml::openElement( 'form', $formParams ) .
			Html::hidden( 'title', $this->getTitle()->getPrefixedText() ) .
			Html::hidden( 'token', $csrfTokenSet->getToken() ) .
			Html::hidden( 'process', 1 );
	}

	protected function doFooter(): string {
		return '</form>';
	}

	protected function allowProcess(): bool {
		$context = RequestContext::getMain();
		$request = $context->getRequest();
		$csrfTokenSet = $context->getCsrfTokenSet();

		return $request->wasPosted()
			&& $request->getBool( 'process' )
			&& $csrfTokenSet->matchTokenField( 'token' );
	}

	protected function getActions(): array {
		return [
			'import',
			$this->code === 'en' ? 'fuzzy' : 'conflict',
			'ignore',
		];
	}

	public function execute( array $messages ): bool {
		$context = RequestContext::getMain();
		$output = $context->getOutput();

		// Set up diff engine
		$diff = new DifferenceEngine();
		$diff->showDiffStyle();
		$diff->setReducedLineNumbers();

		// Check whether we do processing
		$process = $this->allowProcess();

		// Initialise collection
		$group = $this->getGroup();
		$code = $this->getCode();
		$collection = $group->initCollection( $code );
		$collection->loadTranslations();

		$output->addHTML( $this->doHeader() );

		// Initialise variable to keep track whether all changes were imported
		// or not. If we're allowed to process, initially assume they were.
		$allDone = $process;

		// Determine changes for each message.
		$changed = [];

		foreach ( $messages as $key => $value ) {
			$old = null;
			$isExistingMessageFuzzy = false;

			if ( isset( $collection[$key] ) ) {
				// This returns null if no existing translation is found
				$old = $collection[$key]->translation();
				$isExistingMessageFuzzy = $collection[$key]->hasTag( 'fuzzy' );
			}

			if ( $old === null ) {
				// We found a new translation for this message of the
				// current group: import it.
				if ( $process ) {
					$action = 'import';
					$this->doAction(
						$action,
						$group,
						$key,
						$value
					);
				}
				// Show the user that we imported the new translation
				$para = '<code class="mw-tmi-new">' . htmlspecialchars( $key ) . '</code>';
				$name = $context->msg( 'translate-manage-import-new' )->rawParams( $para )
					->escaped();
				$text = Utilities::convertWhiteSpaceToHTML( $value );
				$changed[] = self::makeSectionElement( $name, 'new', $text );
			} else {
				// No changes at all, ignore
				if ( $old === (string)$value ) {
					continue;
				}

				// Check if the message is already fuzzy in the system, and then determine if there are changes
				$oldTextForDiff = $old;
				if ( $isExistingMessageFuzzy ) {
					if ( MessageHandle::makeFuzzyString( $old ) === (string)$value ) {
						continue;
					}

					// Normalize the display of FUZZY message diffs so that if an old message has
					// a fuzzy tag, then that is added to the text used in the diff.
					$oldTextForDiff = MessageHandle::makeFuzzyString( $old );
				}

				// MutableRevisionRecord expects a page that can exist, so use a dummy non-special page.
				$dummyMainPage = Title::makeTitle( NS_MAIN, 'Some title just for diff' );
				$oldContent = ContentHandler::makeContent( $oldTextForDiff, $dummyMainPage );
				$oldRevision = new MutableRevisionRecord( $dummyMainPage );
				$oldRevision->setContent( SlotRecord::MAIN, $oldContent );

				$newContent = ContentHandler::makeContent( $value, $dummyMainPage );
				$newRevision = new MutableRevisionRecord( $dummyMainPage );
				$newRevision->setContent( SlotRecord::MAIN, $newContent );

				$diff->setRevisions( $oldRevision, $newRevision );
				$text = $diff->getDiff( '', '' );

				// This is a changed translation. Note it for the next steps.
				$type = 'changed';

				// Get the user instructions for the current message,
				// submitted together with the form
				$action = $context->getRequest()
					->getVal( self::escapeNameForPHP( "action-$type-$key" ) );

				if ( $process ) {
					if ( $changed === [] ) {
						// Initialise the HTML list showing the changes performed
						$changed[] = '<ul>';
					}

					if ( $action === null ) {
						// We have been told to process the messages, but not
						// what to do with this one. Tell the user.
						$message = $context->msg(
							'translate-manage-inconsistent',
							wfEscapeWikiText( "action-$type-$key" )
						)->parse();
						$changed[] = "<li>$message</li></ul>";

						// Also stop any further processing for the other messages.
						$process = false;
					} else {
						// Check processing time
						if ( $this->time === null ) {
							$this->time = (int)wfTimestamp();
						}

						// We have all the necessary information on this changed
						// translation: actually process the message
						$messageKeyAndParams = $this->doAction(
							$action,
							$group,
							$key,
							$value
						);

						// Show what we just did, adding to the list of changes
						$msgKey = array_shift( $messageKeyAndParams );
						$params = $messageKeyAndParams;
						$message = $context->msg( $msgKey, $params )->parse();
						$changed[] = "<li>$message</li>";

						// Stop processing further messages if too much time
						// has been spent.
						if ( $this->checkProcessTime() ) {
							$process = false;
							$message = $context->msg( 'translate-manage-toolong' )
								->numParams( self::MAX_PROCESSING_TIME )->parse();
							$changed[] = "<li>$message</li></ul>";
						}

						continue;
					}
				}

				// We are not processing messages, or no longer, or this was an
				// un-actionable translation. We will eventually return false
				$allDone = false;

				// Prepare to ask the user what to do with this message
				$actions = $this->getActions();
				$defaultAction = $action ?: 'import';

				$act = [];

				// Give grep a chance to find the usages:
				// translate-manage-action-import, translate-manage-action-conflict,
				// translate-manage-action-ignore, translate-manage-action-fuzzy
				foreach ( $actions as $action ) {
					$label = $context->msg( "translate-manage-action-$action" )->escaped();
					$act[] = Html::rawElement(
						'label',
						[],
						Html::radio(
							self::escapeNameForPHP( "action-$type-$key" ),
							$action === $defaultAction,
							[ 'value' => $action ]
						) .
						"\u{00A0}" .
						$label
					);
				}

				$param = '<code class="mw-tmi-diff">' . htmlspecialchars( $key ) . '</code>';
				$name = $context->msg( 'translate-manage-import-diff' )
					->rawParams( $param, implode( ' ', $act ) )
					->escaped();

				$changed[] = self::makeSectionElement( $name, $type, $text );
			}
		}

		if ( !$process ) {
			$collection->filter( MessageCollection::FILTER_HAS_TRANSLATION, MessageCollection::INCLUDE_MATCHING );
			$keys = $collection->getMessageKeys();

			$diff = array_diff( $keys, array_keys( $messages ) );

			foreach ( $diff as $s ) {
				$para = '<code class="mw-tmi-deleted">' . htmlspecialchars( $s ) . '</code>';
				$name = $context->msg( 'translate-manage-import-deleted' )->rawParams( $para )->escaped();
				$text = Utilities::convertWhiteSpaceToHTML( $collection[$s]->translation() );
				$changed[] = self::makeSectionElement( $name, 'deleted', $text );
			}
		}

		if ( $process || ( $changed === [] && $code !== 'en' ) ) {
			if ( $changed === [] ) {
				$output->addWikiMsg( 'translate-manage-nochanges-other' );
			}

			if ( $changed === [] || !str_starts_with( end( $changed ), '<li>' ) ) {
				$changed[] = '<ul>';
			}

			$changed[] = '</ul>';

			$languageName = Utilities::getLanguageName( $code, $context->getLanguage()->getCode() );
			$message = $context
				->msg( 'translate-manage-import-done', $group->getId(), $group->getLabel(), $languageName )
				->parse();
			$changed[] = Html::successBox( $message );
			$output->addHTML( implode( "\n", $changed ) );
		} else {
			// END
			if ( $changed !== [] ) {
				if ( $code === 'en' ) {
					$output->addWikiMsg( 'translate-manage-intro-en' );
				} else {
					$lang = Utilities::getLanguageName(
						$code,
						$context->getLanguage()->getCode()
					);
					$output->addWikiMsg( 'translate-manage-intro-other', $lang );
				}
				$output->addHTML( Html::hidden( 'language', $code ) );
				$output->addHTML( implode( "\n", $changed ) );
				$output->addHTML( Html::submitButton( $context->msg( 'translate-manage-submit' )->text() ) );
			} else {
				$output->addWikiMsg( 'translate-manage-nochanges' );
			}
		}

		$output->addHTML( $this->doFooter() );

		return $allDone;
	}

	/**
	 * Perform an action on a given group/key/code
	 *
	 * @param string $action Options: 'import', 'conflict' or 'ignore'
	 * @param MessageGroup $group
	 * @param string $key Message key
	 * @param string $message Contents for the $key/code combination
	 * @return array Action result
	 */
	private function doAction(
		string $action,
		MessageGroup $group,
		string $key,
		string $message
	): array {
		global $wgTranslateDocumentationLanguageCode;

		$comment = '';
		$code = $this->getCode();
		$title = $this->makeTranslationTitle( $group, $key, $code );

		if ( $action === 'import' || $action === 'conflict' ) {
			if ( $action === 'import' ) {
				$comment = wfMessage( 'translate-manage-import-summary' )->inContentLanguage()->plain();
			} else {
				$comment = wfMessage( 'translate-manage-conflict-summary' )->inContentLanguage()->plain();
				$message = MessageHandle::makeFuzzyString( $message );
			}

			return self::doImport( $title, $message, $comment, $this->getUser(), $this->messageLocalizer );
		} elseif ( $action === 'ignore' ) {
			return [ 'translate-manage-import-ignore', $key ];
		} elseif ( $action === 'fuzzy' && $code !== 'en' &&
			$code !== $wgTranslateDocumentationLanguageCode
		) {
			$message = MessageHandle::makeFuzzyString( $message );

			return self::doImport( $title, $message, $comment, $this->getUser(), $this->messageLocalizer );
		} elseif ( $action === 'fuzzy' && $code === 'en' ) {
			return self::doFuzzy( $title, $message, $comment, $this->getUser(), $this->messageLocalizer );
		} else {
			throw new InvalidArgumentException( "Unhandled action $action" );
		}
	}

	protected function checkProcessTime(): bool {
		return (int)wfTimestamp() - $this->time >= self::MAX_PROCESSING_TIME;
	}

	/** @return string[] */
	private static function doImport(
		Title $title,
		string $message,
		string $summary,
		User $user,
		MessageLocalizer $messageLocalizer
	): array {
		$mwServices = MediaWikiServices::getInstance();
		$wikiPage = $mwServices->getWikiPageFactory()->newFromTitle( $title );
		$content = ContentHandler::makeContent( $message, $title );

		$updater = $wikiPage->newPageUpdater( $user )->setContent( SlotRecord::MAIN, $content );
		if ( $user->authorizeWrite( 'autopatrol', $title ) ) {
			$updater->setRcPatrolStatus( RecentChange::PRC_AUTOPATROLLED );
		}
		$updater->saveRevision( CommentStoreComment::newUnsavedComment( $summary ) );
		$status = $updater->getStatus();
		$success = $status->isOK();

		if ( $success ) {
			return [ 'translate-manage-import-ok',
				wfEscapeWikiText( $title->getPrefixedText() )
			];
		}

		$statusFormatter = $mwServices
			->getFormatterFactory()
			->getStatusFormatter( $messageLocalizer );
		$text = "Failed to import new version of page {$title->getPrefixedText()}\n";
		$text .= $statusFormatter->getWikiText( $status );
		throw new RuntimeException( $text );
	}

	/** @return string[] */
	public static function doFuzzy(
		Title $title,
		string $message,
		string $comment,
		?User $user,
		MessageLocalizer $messageLocalizer
	): array {
		$context = RequestContext::getMain();
		$services = MediaWikiServices::getInstance();

		if ( !$context->getUser()->isAllowed( 'translate-manage' ) ) {
			return [ 'badaccess-group0' ];
		}

		// Edit with fuzzybot if there is no user.
		if ( !$user ) {
			$user = FuzzyBot::getUser();
		}

		// Work on all subpages of base title.
		$handle = new MessageHandle( $title );
		$titleText = $handle->getKey();

		$revStore = $services->getRevisionStore();
		$dbw = $services->getDBLoadBalancer()->getConnection( DB_PRIMARY );
		$rows = $revStore->newSelectQueryBuilder( $dbw )
			->joinPage()
			->where( [
				'page_namespace' => $title->getNamespace(),
				'page_latest=rev_id',
				'page_title' . $dbw->buildLike( "$titleText/", $dbw->anyString() ),
			] )
			->caller( __METHOD__ )
			->fetchResultSet();

		$changed = [];
		$slots = $revStore->getContentBlobsForBatch( $rows, [ SlotRecord::MAIN ] )->getValue();

		foreach ( $rows as $row ) {
			global $wgTranslateDocumentationLanguageCode;

			$translationTitle = Title::makeTitle( (int)$row->page_namespace, $row->page_title );

			// No fuzzy for English original or documentation language code.
			if ( $translationTitle->getSubpageText() === 'en' ||
				$translationTitle->getSubpageText() === $wgTranslateDocumentationLanguageCode
			) {
				// Use imported text, not database text.
				$text = $message;
			} elseif ( isset( $slots[$row->rev_id] ) ) {
				$slot = $slots[$row->rev_id][SlotRecord::MAIN];
				$text = MessageHandle::makeFuzzyString( $slot->blob_data );
			} else {
				$text = MessageHandle::makeFuzzyString(
					Utilities::getTextFromTextContent(
						$revStore->newRevisionFromRow( $row )->getContent( SlotRecord::MAIN )
					)
				);
			}

			// Do actual import
			$changed[] = self::doImport(
				$translationTitle,
				$text,
				$comment,
				$user,
				$messageLocalizer
			);
		}

		// Format return text
		$text = '';
		foreach ( $changed as $c ) {
			$key = array_shift( $c );
			$text .= '* ' . $context->msg( $key, $c )->plain() . "\n";
		}

		return [ 'translate-manage-import-fuzzy', "\n" . $text ];
	}

	/**
	 * Given a group, message key and language code, creates a title for the
	 * translation page.
	 *
	 * @param MessageGroup $group
	 * @param string $key Message key
	 * @param string $code Language code
	 * @return Title
	 */
	private function makeTranslationTitle( MessageGroup $group, string $key, string $code ): Title {
		$ns = $group->getNamespace();

		return Title::makeTitleSafe( $ns, "$key/$code" );
	}

	/**
	 * Make section elements.
	 *
	 * @param string $legend Legend as raw html.
	 * @param string $type Contents of type class.
	 * @param string $content Contents as raw html.
	 * @param Language|null $lang The language in which the text is written.
	 * @param string[] $extraContainerClasses Additional classes to add to the section container
	 * @return string Section element as html.
	 */
	public static function makeSectionElement(
		string $legend,
		string $type,
		string $content,
		?Language $lang = null,
		array $extraContainerClasses = []
	): string {
		$containerParams = [ 'class' => "mw-tpt-sp-section mw-tpt-sp-section-type-{$type}" ];
		if ( $extraContainerClasses ) {
			$containerParams[ 'class' ] .= ' ' . implode( ' ', $extraContainerClasses );
		}
		$legendParams = [ 'class' => 'mw-tpt-sp-legend' ];
		$contentParams = [ 'class' => 'mw-tpt-sp-content' ];
		if ( $lang ) {
			$contentParams['dir'] = $lang->getDir();
			$contentParams['lang'] = $lang->getCode();
		}

		return Html::rawElement( 'div', $containerParams,
			Html::rawElement( 'div', $legendParams, $legend ) .
				Html::rawElement( 'div', $contentParams, $content )
		);
	}

	/**
	 * Escape name such that it validates as name and id parameter in html, and
	 * so that we can get it back with WebRequest::getVal(). Especially dot and
	 * spaces are difficult for the latter.
	 */
	private static function escapeNameForPHP( string $name ): string {
		$replacements = [
			'(' => '(OP)',
			' ' => '(SP)',
			"\t" => '(TAB)',
			'.' => '(DOT)',
			"'" => '(SQ)',
			"\"" => '(DQ)',
			'%' => '(PC)',
			'&' => '(AMP)',
		];

		return strtr( $name, $replacements );
	}
}
