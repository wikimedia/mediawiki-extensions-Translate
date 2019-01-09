<?php
/**
 * Class which encapsulates message importing. It scans for changes (new, changed, deleted),
 * displays them in pretty way with diffs and finally executes the actions the user choices.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2009-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0-or-later
 */

/**
 * Class which encapsulates message importing. It scans for changes (new, changed, deleted),
 * displays them in pretty way with diffs and finally executes the actions the user choices.
 */
class MessageWebImporter {
	/**
	 * @var Title
	 */
	protected $title;

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var MessageGroup
	 */
	protected $group;
	protected $code;
	protected $time;

	/**
	 * @var OutputPage
	 */
	protected $out;

	/**
	 * Maximum processing time in seconds.
	 */
	protected $processingTime = 43;

	/**
	 * @param Title|null $title
	 * @param MessageGroup|string|null $group
	 * @param string $code
	 */
	public function __construct( Title $title = null, $group = null, $code = 'en' ) {
		$this->setTitle( $title );
		$this->setGroup( $group );
		$this->setCode( $code );
	}

	/**
	 * Wrapper for consistency with SpecialPage
	 *
	 * @return Title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param Title $title
	 */
	public function setTitle( Title $title ) {
		$this->title = $title;
	}

	/**
	 * @return User
	 */
	public function getUser() {
		return $this->user ?: RequestContext::getMain()->getUser();
	}

	/**
	 * @param User $user
	 */
	public function setUser( User $user ) {
		$this->user = $user;
	}

	/**
	 * @return MessageGroup
	 */
	public function getGroup() {
		return $this->group;
	}

	/**
	 * Group is either MessageGroup object or group id.
	 * @param MessageGroup|string $group
	 */
	public function setGroup( $group ) {
		if ( $group instanceof MessageGroup ) {
			$this->group = $group;
		} else {
			$this->group = MessageGroups::getGroup( $group );
		}
	}

	/**
	 * @return string
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * @param string $code
	 */
	public function setCode( $code = 'en' ) {
		$this->code = $code;
	}

	/**
	 * @return string
	 */
	protected function getAction() {
		return $this->getTitle()->getFullURL();
	}

	/**
	 * @return string
	 */
	protected function doHeader() {
		$formParams = [
			'method' => 'post',
			'action' => $this->getAction(),
			'class' => 'mw-translate-manage'
		];

		return Xml::openElement( 'form', $formParams ) .
			Html::hidden( 'title', $this->getTitle()->getPrefixedText() ) .
			Html::hidden( 'token', $this->getUser()->getEditToken() ) .
			Html::hidden( 'process', 1 );
	}

	/**
	 * @return string
	 */
	protected function doFooter() {
		return '</form>';
	}

	/**
	 * @return bool
	 */
	protected function allowProcess() {
		$request = RequestContext::getMain()->getRequest();

		return $request->wasPosted()
			&& $request->getBool( 'process', false )
			&& $this->getUser()->matchEditToken( $request->getVal( 'token' ) );
	}

	/**
	 * @return array
	 */
	protected function getActions() {
		if ( $this->code === 'en' ) {
			return [ 'import', 'fuzzy', 'ignore' ];
		}

		return [ 'import', 'conflict', 'ignore' ];
	}

	/**
	 * @param bool $fuzzy
	 * @param string $action
	 * @return string
	 */
	protected function getDefaultAction( $fuzzy, $action ) {
		if ( $action ) {
			return $action;
		}

		return $fuzzy ? 'conflict' : 'import';
	}

	public function execute( $messages ) {
		$context = RequestContext::getMain();
		$this->out = $context->getOutput();

		// Set up diff engine
		$diff = new DifferenceEngine;
		$diff->showDiffStyle();
		$diff->setReducedLineNumbers();

		// Check whether we do processing
		$process = $this->allowProcess();

		// Initialise collection
		$group = $this->getGroup();
		$code = $this->getCode();
		$collection = $group->initCollection( $code );
		$collection->loadTranslations();

		$this->out->addHTML( $this->doHeader() );

		// Initialise variable to keep track whether all changes were imported
		// or not. If we're allowed to process, initially assume they were.
		$alldone = $process;

		// Determine changes for each message.
		$changed = [];

		foreach ( $messages as $key => $value ) {
			$fuzzy = $old = null;

			if ( isset( $collection[$key] ) ) {
				// This returns null if no existing translation is found
				$old = $collection[$key]->translation();
			}

			// No changes at all, ignore
			if ( (string)$old === (string)$value ) {
				continue;
			}

			if ( $old === null ) {
				// We found a new translation for this message of the
				// current group: import it.
				$action = 'import';
				self::doAction(
					$action,
					$group,
					$key,
					$code,
					$value
				);

				// Show the user that we imported the new translation
				$para = '<code class="mw-tmi-new">' . htmlspecialchars( $key ) . '</code>';
				$name = $context->msg( 'translate-manage-import-new' )->rawParams( $para )
					->escaped();
				$text = TranslateUtils::convertWhiteSpaceToHTML( $value );
				$changed[] = self::makeSectionElement( $name, 'new', $text );
			} else {
				$oldContent = ContentHandler::makeContent( $old, $diff->getTitle() );
				$newContent = ContentHandler::makeContent( $value, $diff->getTitle() );
				$diff->setContent( $oldContent, $newContent );
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
						if ( !isset( $this->time ) ) {
							$this->time = wfTimestamp();
						}

						// We have all the necessary information on this changed
						// translation: actually process the message
						$messageKeyAndParams = self::doAction(
							$action,
							$group,
							$key,
							$code,
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
								->numParams( $this->processingTime )->parse();
							$changed[] = "<li>$message</li></ul>";
						}

						continue;
					}
				}

				// We are not processing messages, or no longer, or this was an
				// unactionable translation. We will eventually return false
				$alldone = false;

				// Prepare to ask the user what to do with this message
				$actions = $this->getActions();
				$defaction = $this->getDefaultAction( $fuzzy, $action );

				$act = [];

				// Give grep a chance to find the usages:
				// translate-manage-action-import, translate-manage-action-conflict,
				// translate-manage-action-ignore, translate-manage-action-fuzzy
				foreach ( $actions as $action ) {
					$label = $context->msg( "translate-manage-action-$action" )->text();
					$name = self::escapeNameForPHP( "action-$type-$key" );
					$id = Sanitizer::escapeId( "action-$key-$action" );
					$act[] = Xml::radioLabel( $label, $name, $action, $id, $action === $defaction );
				}

				$param = '<code class="mw-tmi-diff">' . htmlspecialchars( $key ) . '</code>';
				$name = $context->msg( 'translate-manage-import-diff' )
					->rawParams( $param, implode( ' ', $act ) )
					->escaped();

				$changed[] = self::makeSectionElement( $name, $type, $text );
			}
		}

		if ( !$process ) {
			$collection->filter( 'hastranslation', false );
			$keys = $collection->getMessageKeys();

			$diff = array_diff( $keys, array_keys( $messages ) );

			foreach ( $diff as $s ) {
				$para = '<code class="mw-tmi-deleted">' . htmlspecialchars( $s ) . '</code>';
				$name = $context->msg( 'translate-manage-import-deleted' )->rawParams( $para )->escaped();
				$text = TranslateUtils::convertWhiteSpaceToHTML( $collection[$s]->translation() );
				$changed[] = self::makeSectionElement( $name, 'deleted', $text );
			}
		}

		if ( $process || ( $changed === [] && $code !== 'en' ) ) {
			if ( $changed === [] ) {
				$this->out->addWikiMsg( 'translate-manage-nochanges-other' );
			}

			if ( $changed === [] || strpos( end( $changed ), '<li>' ) !== 0 ) {
				$changed[] = '<ul>';
			}

			$message = $context->msg( 'translate-manage-import-done' )->parse();
			$changed[] = "<li>$message</li></ul>";
			$this->out->addHTML( implode( "\n", $changed ) );
		} else {
			// END
			if ( $changed !== [] ) {
				if ( $code === 'en' ) {
					$this->out->addWikiMsg( 'translate-manage-intro-en' );
				} else {
					$lang = TranslateUtils::getLanguageName(
						$code,
						$context->getLanguage()->getCode()
					);
					$this->out->addWikiMsg( 'translate-manage-intro-other', $lang );
				}
				$this->out->addHTML( Html::hidden( 'language', $code ) );
				$this->out->addHTML( implode( "\n", $changed ) );
				$this->out->addHTML( Xml::submitButton( $context->msg( 'translate-manage-submit' )->text() ) );
			} else {
				$this->out->addWikiMsg( 'translate-manage-nochanges' );
			}
		}

		$this->out->addHTML( $this->doFooter() );

		return $alldone;
	}

	/**
	 * Perform an action on a given group/key/code
	 *
	 * @param string $action Options: 'import', 'conflict' or 'ignore'
	 * @param MessageGroup $group Group object
	 * @param string $key Message key
	 * @param string $code Language code
	 * @param string $message Contents for the $key/code combination
	 * @param string $comment Edit summary (default: empty) - see Article::doEdit
	 * @param User|null $user User that will make the edit (default: null - RequestContext user).
	 *        See Article::doEdit.
	 * @param int $editFlags Integer bitfield: see Article::doEdit
	 * @throws MWException
	 * @return string Action result
	 */
	public static function doAction( $action, $group, $key, $code, $message, $comment = '',
		$user = null, $editFlags = 0
	) {
		global $wgTranslateDocumentationLanguageCode;

		$title = self::makeTranslationTitle( $group, $key, $code );

		if ( $action === 'import' || $action === 'conflict' ) {
			if ( $action === 'import' ) {
				$comment = wfMessage( 'translate-manage-import-summary' )->inContentLanguage()->plain();
			} else {
				$comment = wfMessage( 'translate-manage-conflict-summary' )->inContentLanguage()->plain();
				$message = self::makeTextFuzzy( $message );
			}

			return self::doImport( $title, $message, $comment, $user, $editFlags );
		} elseif ( $action === 'ignore' ) {
			return [ 'translate-manage-import-ignore', $key ];
		} elseif ( $action === 'fuzzy' && $code !== 'en' &&
			$code !== $wgTranslateDocumentationLanguageCode
		) {
			$message = self::makeTextFuzzy( $message );

			return self::doImport( $title, $message, $comment, $user, $editFlags );
		} elseif ( $action === 'fuzzy' && $code === 'en' ) {
			return self::doFuzzy( $title, $message, $comment, $user, $editFlags );
		} else {
			throw new MWException( "Unhandled action $action" );
		}
	}

	protected function checkProcessTime() {
		return wfTimestamp() - $this->time >= $this->processingTime;
	}

	/**
	 * @throws MWException
	 * @param Title $title
	 * @param string $message
	 * @param string $summary
	 * @param User|null $user
	 * @param int $editFlags
	 * @return array
	 */
	public static function doImport( $title, $message, $summary, $user = null, $editFlags = 0 ) {
		$wikiPage = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( $message, $title );
		$status = $wikiPage->doEditContent( $content, $summary, $editFlags, false, $user );
		$success = $status->isOK();

		if ( $success ) {
			return [ 'translate-manage-import-ok',
				wfEscapeWikiText( $title->getPrefixedText() )
			];
		}

		$text = "Failed to import new version of page {$title->getPrefixedText()}\n";
		$text .= "{$status->getWikiText()}";
		throw new MWException( $text );
	}

	/**
	 * @param Title $title
	 * @param string $message
	 * @param string $comment
	 * @param User $user
	 * @param int $editFlags
	 * @return array|String
	 */
	public static function doFuzzy( $title, $message, $comment, $user, $editFlags = 0 ) {
		$context = RequestContext::getMain();

		if ( !$context->getUser()->isAllowed( 'translate-manage' ) ) {
			return $context->msg( 'badaccess-group0' )->text();
		}

		$dbw = wfGetDB( DB_MASTER );

		// Work on all subpages of base title.
		$handle = new MessageHandle( $title );
		$titleText = $handle->getKey();

		$conds = [
			'page_namespace' => $title->getNamespace(),
			'page_latest=rev_id',
			'rev_text_id=old_id',
			'page_title' . $dbw->buildLike( "$titleText/", $dbw->anyString() ),
		];

		$rows = $dbw->select(
			[ 'page', 'revision', 'text' ],
			[ 'page_title', 'page_namespace', 'old_text', 'old_flags' ],
			$conds,
			__METHOD__
		);

		// Edit with fuzzybot if there is no user.
		if ( !$user ) {
			$user = FuzzyBot::getUser();
		}

		// Process all rows.
		$changed = [];
		foreach ( $rows as $row ) {
			global $wgTranslateDocumentationLanguageCode;

			$ttitle = Title::makeTitle( $row->page_namespace, $row->page_title );

			// No fuzzy for English original or documentation language code.
			if ( $ttitle->getSubpageText() === 'en' ||
				$ttitle->getSubpageText() === $wgTranslateDocumentationLanguageCode
			) {
				// Use imported text, not database text.
				$text = $message;
			} else {
				$text = Revision::getRevisionText( $row );
				$text = self::makeTextFuzzy( $text );
			}

			// Do actual import
			$changed[] = self::doImport(
				$ttitle,
				$text,
				$comment,
				$user,
				$editFlags
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
	public static function makeTranslationTitle( $group, $key, $code ) {
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
	 * @return string Section element as html.
	 */
	public static function makeSectionElement( $legend, $type, $content, $lang = null ) {
		$containerParams = [ 'class' => "mw-tpt-sp-section mw-tpt-sp-section-type-{$type}" ];
		$legendParams = [ 'class' => 'mw-tpt-sp-legend' ];
		$contentParams = [ 'class' => 'mw-tpt-sp-content' ];
		if ( $lang ) {
			$contentParams['dir'] = $lang->getDir();
			$contentParams['lang'] = $lang->getCode();
		}

		$output = Html::rawElement( 'div', $containerParams,
			Html::rawElement( 'div', $legendParams, $legend ) .
				Html::rawElement( 'div', $contentParams, $content )
		);

		return $output;
	}

	/**
	 * Prepends translation with fuzzy tag and ensures there is only one of them.
	 *
	 * @param string $message Message content
	 * @return string Message prefixed with TRANSLATE_FUZZY tag
	 */
	public static function makeTextFuzzy( $message ) {
		$message = str_replace( TRANSLATE_FUZZY, '', $message );

		return TRANSLATE_FUZZY . $message;
	}

	/**
	 * Escape name such that it validates as name and id parameter in html, and
	 * so that we can get it back with WebRequest::getVal(). Especially dot and
	 * spaces are difficult for the latter.
	 * @param string $name
	 * @return string
	 */
	public static function escapeNameForPHP( $name ) {
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

		/* How nice of you PHP. No way to split array into keys and values in one
		 * function or have str_replace which takes one array? */

		return str_replace( array_keys( $replacements ), array_values( $replacements ), $name );
	}
}
