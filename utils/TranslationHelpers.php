<?php
/**
 * Contains helper class for interface parts that aid translations in doing
 * their thing.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Provides the nice boxes that aid the translators to do their job.
 * Boxes contain definition, documentation, other languages, translation memory
 * suggestions, highlighted changes etc.
 */
class TranslationHelpers {
	/**
	 * @var MessageHandle
	 * @since 2012-01-04
	 */
	protected $handle;
	/**
	 * The group object of the message (or null if there isn't any)
	 * @var MessageGroup
	 */
	protected $group;

	/**
	 * The current translation as a string.
	 */
	protected $translation;
	/**
	 * The message definition as a string.
	 */
	protected $definition;
	/**
	 * HTML id to the text area that contains the translation. Used to insert
	 * suggestion directly into the text area, for example.
	 */
	protected $textareaId = 'wpTextbox1';
	/**
	 * Whether to include extra tools to aid translating.
	 */
	protected $editMode = 'true';

	/**
	 * @param Title $title Title of a page that holds a translation.
	 * @param string $groupId Group id that should be used, otherwise autodetected from title.
	 */
	public function __construct( Title $title, $groupId ) {
		$this->handle = new MessageHandle( $title );
		$this->group = $this->getMessageGroup( $this->handle, $groupId );
	}

	/**
	 * Tries to determine to which group this message belongs. Falls back to the
	 * message index if valid group id was not supplied.
	 *
	 * @param MessageHandle $handle
	 * @param string $groupId
	 * @return MessageGroup|null Group the key belongs to, or null.
	 */
	protected function getMessageGroup( MessageHandle $handle, $groupId ) {
		$mg = MessageGroups::getGroup( $groupId );

		# If we were not given (a valid) group
		if ( $mg === null ) {
			$groupId = MessageIndex::getPrimaryGroupId( $handle );
			$mg = MessageGroups::getGroup( $groupId );
		}

		return $mg;
	}

	/**
	 * Gets the HTML id of the text area that contains the translation.
	 * @return String
	 */
	public function getTextareaId() {
		return $this->textareaId;
	}

	/**
	 * Sets the HTML id of the text area that contains the translation.
	 * @param $id String
	 */
	public function setTextareaId( $id ) {
		$this->textareaId = $id;
	}

	/**
	 * Enable or disable extra help for editing.
	 * @param $mode Boolean
	 */
	public function setEditMode( $mode = true ) {
		$this->editMode = $mode;
	}

	/**
	 * Gets the message definition.
	 * @return String
	 */
	public function getDefinition() {
		if ( $this->definition !== null ) {
			return $this->definition;
		}

		$this->mustBeKnownMessage();

		if ( method_exists( $this->group, 'getMessageContent' ) ) {
			$this->definition = $this->group->getMessageContent( $this->handle );
		} else {
			$this->definition = $this->group->getMessage(
				$this->handle->getKey(),
				$this->group->getSourceLanguage()
			);
		}

		return $this->definition;
	}

	/**
	 * Gets the current message translation. Fuzzy messages will be marked as
	 * such unless translation is provided manually.
	 * @return string
	 */
	public function getTranslation() {
		if ( $this->translation === null ) {
			$obj = new CurrentTranslationAid( $this->group, $this->handle, RequestContext::getMain() );
			$aid = $obj->getData();
			$this->translation = $aid['value'];

			if ( $aid['fuzzy'] ) {
				$this->translation = TRANSLATE_FUZZY . $this->translation;
			}
		}

		return $this->translation;
	}

	/**
	 * Manual override for the translation. If not given or it is null, the code
	 * will try to fetch it automatically.
	 * @param string|null $translation
	 */
	public function setTranslation( $translation ) {
		$this->translation = $translation;
	}

	/**
	 * Gets the linguistically correct language code for translation
	 */
	public function getTargetLanguage() {
		global $wgLanguageCode, $wgTranslateDocumentationLanguageCode;

		$code = $this->handle->getCode();
		if ( !$code ) {
			$this->mustBeKnownMessage();
			$code = $this->group->getSourceLanguage();
		}
		if ( $code === $wgTranslateDocumentationLanguageCode ) {
			return $wgLanguageCode;
		}

		return $code;
	}

	/**
	 * Returns block element HTML snippet that contains the translation aids.
	 * Not all boxes are shown all the time depending on whether they have
	 * any information to show and on configuration variables.
	 * @param $suggestions string
	 * @return String. Block level HTML snippet or empty string.
	 */
	public function getBoxes( $suggestions = 'sync' ) {
		// Box filter
		$all = $this->getBoxNames();

		if ( $suggestions === 'checks' ) {
			$request = RequestContext::getMain()->getRequest();
			$this->translation = $request->getText( 'translation' );

			return (string)$this->callBox( 'check', $all['check'] );
		}

		$boxes = array();
		foreach ( $all as $type => $cb ) {
			$box = $this->callBox( $type, $cb );
			if ( $box ) {
				$boxes[$type] = $box;
			}
		}

		Hooks::run( 'TranslateGetBoxes', array( $this->group, $this->handle, &$boxes ) );

		if ( count( $boxes ) ) {
			return Html::rawElement(
				'div',
				array( 'class' => 'mw-sp-translate-edit-fields' ),
				implode( "\n\n", $boxes )
			);
		} else {
			return '';
		}
	}

	/**
	 * Public since 2012-06-26
	 *
	 * @since 2012-01-04
	 * @param string $type
	 * @param callback $cb
	 * @param array $params
	 * @return mixed
	 */
	public function callBox( $type, $cb, array $params = array() ) {
		try {
			return call_user_func_array( $cb, $params );
		} catch ( TranslationHelperException $e ) {
			return "<!-- Box $type not available: {$e->getMessage()} -->";
		}
	}

	/**
	 * @return array
	 */
	public function getBoxNames() {
		return array(
			'other-languages' => array( $this, 'getOtherLanguagesBox' ),
			'translation-diff' => array( $this, 'getPageDiff' ),
			'separator' => array( $this, 'getSeparatorBox' ),
			'documentation' => array( $this, 'getDocumentationBox' ),
			'definition' => array( $this, 'getDefinitionBox' ),
			'check' => array( $this, 'getCheckBox' ),
		);
	}

	public function getDefinitionBox() {
		$this->mustHaveDefinition();
		$en = $this->getDefinition();

		$title = Linker::link(
			SpecialPage::getTitleFor( 'Translate' ),
			htmlspecialchars( $this->group->getLabel() ),
			array(),
			array(
				'group' => $this->group->getId(),
				'language' => $this->handle->getCode()
			)
		);

		$label =
			wfMessage( 'translate-edit-definition' )->text() .
				wfMessage( 'word-separator' )->text() .
				wfMessage( 'parentheses', $title )->text();

		// Source language object
		$sl = Language::factory( $this->group->getSourceLanguage() );

		$dialogID = $this->dialogID();
		$id = Sanitizer::escapeId( "def-$dialogID" );
		$msg = $this->adder( $id, $sl ) . "\n" . Html::rawElement( 'div',
			array(
				'class' => 'mw-translate-edit-deftext',
				'dir' => $sl->getDir(),
				'lang' => $sl->getHtmlCode(),
			),
			TranslateUtils::convertWhiteSpaceToHTML( $en )
		);

		$msg .= $this->wrapInsert( $id, $en );

		$class = array( 'class' => 'mw-sp-translate-edit-definition mw-translate-edit-definition' );

		return TranslateUtils::fieldset( $label, $msg, $class );
	}

	public function getTranslationDisplayBox() {
		$en = $this->getTranslation();
		if ( $en === null ) {
			return null;
		}
		$label = wfMessage( 'translate-edit-translation' )->text();
		$class = array( 'class' => 'mw-translate-edit-translation' );
		$msg = Html::rawElement( 'span',
			array( 'class' => 'mw-translate-edit-translationtext' ),
			TranslateUtils::convertWhiteSpaceToHTML( $en )
		);

		return TranslateUtils::fieldset( $label, $msg, $class );
	}

	public function getCheckBox() {
		$this->mustBeKnownMessage();

		global $wgTranslateDocumentationLanguageCode;

		$context = RequestContext::getMain();
		$title = $context->getOutput()->getTitle();
		list( $alias, ) = SpecialPageFactory::resolveAlias( $title->getText() );

		$tux = SpecialTranslate::isBeta( $context->getRequest() )
			&& $title->isSpecialPage()
			&& ( $alias === 'Translate' );

		$formattedChecks = $tux ?
			FormatJson::encode( array() ) :
			Html::element( 'div', array( 'class' => 'mw-translate-messagechecks' ) );

		$page = $this->handle->getKey();
		$translation = $this->getTranslation();
		$code = $this->handle->getCode();
		$en = $this->getDefinition();

		if ( (string)$translation === '' ) {
			return $formattedChecks;
		}

		if ( $code === $wgTranslateDocumentationLanguageCode ) {
			return $formattedChecks;
		}

		// We need to get the primary group of the message. It may differ from
		// the supplied group (aggregate groups, dynamic groups).
		$checker = $this->handle->getGroup()->getChecker();
		if ( !$checker ) {
			return $formattedChecks;
		}

		$message = new FatMessage( $page, $en );
		// Take the contents from edit field as a translation
		$message->setTranslation( $translation );

		$checks = $checker->checkMessage( $message, $code );
		if ( !count( $checks ) ) {
			return $formattedChecks;
		}

		$checkMessages = array();

		foreach ( $checks as $checkParams ) {
			$key = array_shift( $checkParams );
			$checkMessages[] = $context->msg( $key, $checkParams )->parse();
		}

		if ( $tux ) {
			$formattedChecks = FormatJson::encode( $checkMessages );
		} else {
			$formattedChecks = Html::rawElement(
				'div',
				array( 'class' => 'mw-translate-messagechecks' ),
				TranslateUtils::fieldset(
					$context->msg( 'translate-edit-warnings' )->escaped(),
					implode( '<hr />', $checkMessages ),
					array( 'class' => 'mw-sp-translate-edit-warnings' )
				)
			);
		}

		return $formattedChecks;
	}

	public function getOtherLanguagesBox() {
		$code = $this->handle->getCode();
		$page = $this->handle->getKey();
		$ns = $this->handle->getTitle()->getNamespace();

		$boxes = array();
		foreach ( self::getFallbacks( $code ) as $fbcode ) {
			$text = TranslateUtils::getMessageContent( $page, $fbcode, $ns );
			if ( $text === null ) {
				continue;
			}

			$fbLanguage = Language::factory( $fbcode );
			$context = RequestContext::getMain();
			$label = TranslateUtils::getLanguageName( $fbcode, $context->getLanguage()->getCode() ) .
				$context->msg( 'word-separator' )->text() .
				$context->msg( 'parentheses', $fbLanguage->getHtmlCode() )->text();

			$target = Title::makeTitleSafe( $ns, "$page/$fbcode" );
			if ( $target ) {
				$label = self::ajaxEditLink( $target, htmlspecialchars( $label ) );
			}

			$dialogID = $this->dialogID();
			$id = Sanitizer::escapeId( "other-$fbcode-$dialogID" );

			$params = array( 'class' => 'mw-translate-edit-item' );

			$display = TranslateUtils::convertWhiteSpaceToHTML( $text );
			$display = Html::rawElement( 'div', array(
					'lang' => $fbLanguage->getHtmlCode(),
					'dir' => $fbLanguage->getDir() ),
				$display
			);

			$contents = self::legend( $label ) . "\n" . $this->adder( $id, $fbLanguage ) .
				$display . self::clear();

			$boxes[] = Html::rawElement( 'div', $params, $contents ) .
				$this->wrapInsert( $id, $text );
		}

		if ( count( $boxes ) ) {
			$sep = Html::element( 'hr', array( 'class' => 'mw-translate-sep' ) );

			return TranslateUtils::fieldset(
				wfMessage(
					'translate-edit-in-other-languages',
					$page
				)->escaped(),
				implode( "$sep\n", $boxes ),
				array( 'class' => 'mw-sp-translate-edit-inother' )
			);
		}

		return null;
	}

	public function getSeparatorBox() {
		return Html::element( 'div', array( 'class' => 'mw-translate-edit-extra' ) );
	}

	public function getDocumentationBox() {
		global $wgTranslateDocumentationLanguageCode;

		if ( !$wgTranslateDocumentationLanguageCode ) {
			throw new TranslationHelperException( 'Message documentation language code is not defined' );
		}

		$context = RequestContext::getMain();
		$page = $this->handle->getKey();
		$ns = $this->handle->getTitle()->getNamespace();

		$title = Title::makeTitle( $ns, $page . '/' . $wgTranslateDocumentationLanguageCode );
		$edit = self::ajaxEditLink(
			$title,
			$context->msg( 'translate-edit-contribute' )->escaped()
		);
		$info = TranslateUtils::getMessageContent( $page, $wgTranslateDocumentationLanguageCode, $ns );

		$class = 'mw-sp-translate-edit-info';

		// The information is most likely in English
		$divAttribs = array( 'dir' => 'ltr', 'lang' => 'en', 'class' => 'mw-content-ltr' );

		if ( (string)$info === '' ) {
			$info = $context->msg( 'translate-edit-no-information' )->text();
			$class = 'mw-sp-translate-edit-noinfo';
			$lang = $context->getLanguage();
			// The message saying that there's no info, should be translated
			$divAttribs = array( 'dir' => $lang->getDir(), 'lang' => $lang->getHtmlCode() );
		}
		$class .= ' mw-sp-translate-message-documentation';

		$contents = $context->getOutput()->parse( $info );
		// Remove whatever block element wrapup the parser likes to add
		$contents = preg_replace( '~^<([a-z]+)>(.*)</\1>$~us', '\2', $contents );

		return TranslateUtils::fieldset(
			$context->msg( 'translate-edit-information' )->rawParams( $edit )->escaped(),
			Html::rawElement( 'div', $divAttribs, $contents ), array( 'class' => $class )
		);
	}

	protected function getPageDiff() {
		$this->mustBeKnownMessage();

		$title = $this->handle->getTitle();
		$key = $this->handle->getKey();

		if ( !$title->exists() ) {
			return null;
		}

		$definitionTitle = Title::makeTitleSafe( $title->getNamespace(), "$key/en" );
		if ( !$definitionTitle || !$definitionTitle->exists() ) {
			return null;
		}

		$db = TranslateUtils::getSafeReadDB();
		$conds = array(
			'rt_page' => $title->getArticleID(),
			'rt_type' => RevTag::getType( 'tp:transver' ),
		);
		$options = array(
			'ORDER BY' => 'rt_revision DESC',
		);

		$latestRevision = $definitionTitle->getLatestRevID();

		$translationRevision = $db->selectField( 'revtag', 'rt_value', $conds, __METHOD__, $options );
		if ( $translationRevision === false ) {
			return null;
		}

		// Using newFromId instead of newFromTitle, because the page might have been renamed
		$oldrev = Revision::newFromId( $translationRevision );
		if ( !$oldrev ) {
			// And someone might still have deleted it
			return null;
		}

		$oldtext = ContentHandler::getContentText( $oldrev->getContent() );
		$newContent = Revision::newFromTitle( $definitionTitle, $latestRevision )->getContent();
		$newtext = ContentHandler::getContentText( $newContent );

		if ( $oldtext === $newtext ) {
			return null;
		}

		$diff = new DifferenceEngine;
		if ( method_exists( 'DifferenceEngine', 'setTextLanguage' ) ) {
			$diff->setTextLanguage( $this->group->getSourceLanguage() );
		}

		$oldContent = ContentHandler::makeContent( $oldtext, $diff->getTitle() );
		$newContent = ContentHandler::makeContent( $newtext, $diff->getTitle() );

		$diff->setContent( $oldContent, $newContent );
		$diff->setReducedLineNumbers();
		$diff->showDiffStyle();

		return $diff->getDiff(
			wfMessage( 'tpt-diff-old' )->escaped(),
			wfMessage( 'tpt-diff-new' )->escaped()
		);
	}

	/**
	 * @param $label string
	 * @return string
	 */
	protected static function legend( $label ) {
		# Float it to the opposite direction
		return Html::rawElement( 'div', array( 'class' => 'mw-translate-legend' ), $label );
	}

	/**
	 * @return string
	 */
	protected static function clear() {
		return Html::element( 'div', array( 'style' => 'clear:both;' ) );
	}

	/**
	 * @param $code string
	 * @return array
	 */
	protected static function getFallbacks( $code ) {
		global $wgTranslateLanguageFallbacks;

		// User preference has the final say
		$user = RequestContext::getMain()->getUser();
		$preference = $user->getOption( 'translate-editlangs' );
		if ( $preference !== 'default' ) {
			$fallbacks = array_map( 'trim', explode( ',', $preference ) );
			foreach ( $fallbacks as $k => $v ) {
				if ( $v === $code ) {
					unset( $fallbacks[$k] );
				}
			}

			return $fallbacks;
		}

		// Global configuration settings
		$fallbacks = array();
		if ( isset( $wgTranslateLanguageFallbacks[$code] ) ) {
			$fallbacks = (array)$wgTranslateLanguageFallbacks[$code];
		}

		$list = Language::getFallbacksFor( $code );
		array_pop( $list ); // Get 'en' away from the end
		$fallbacks = array_merge( $list, $fallbacks );

		return array_unique( $fallbacks );
	}

	/**
	 * @return string
	 */
	public function dialogID() {
		$hash = sha1( $this->handle->getTitle()->getPrefixedDBkey() );

		return substr( $hash, 0, 4 );
	}

	/**
	 * @param string $source jQuery selector for element containing the source
	 * @param Language $lang Language object
	 * @return string
	 */
	public function adder( $source, $lang ) {
		if ( !$this->editMode ) {
			return '';
		}
		$target = self::jQueryPathId( $this->getTextareaId() );
		$source = self::jQueryPathId( $source );
		$dir = $lang->getDir();
		$params = array(
			'onclick' => "jQuery($target).val(jQuery($source).text()).focus(); return false;",
			'href' => '#',
			'title' => wfMessage( 'translate-use-suggestion' )->text(),
			'class' => 'mw-translate-adder mw-translate-adder-' . $dir,
		);

		return Html::element( 'a', $params, '↓' );
	}

	/**
	 * @param $id string|int
	 * @param $text string
	 * @return string
	 */
	public function wrapInsert( $id, $text ) {
		return Html::element( 'pre', array( 'id' => $id, 'style' => 'display: none;' ), $text );
	}

	/**
	 * Ajax-enabled message editing link.
	 * @param $target Title: Title of the target message.
	 * @param $text String: Link text for Linker::link()
	 * @return string HTML link
	 */
	public static function ajaxEditLink( $target, $text ) {
		$handle = new MessageHandle( $target );
		$groupId = MessageIndex::getPrimaryGroupId( $handle );

		$params = array();
		$params['action'] = 'edit';
		$params['loadgroup'] = $groupId;

		$jsEdit = TranslationEditPage::jsEdit( $target, $groupId, 'dialog' );

		return Linker::link( $target, $text, $jsEdit, $params );
	}

	/**
	 * Escapes $id such that it can be used in jQuery selector.
	 * @param $id string
	 * @return string
	 */
	public static function jQueryPathId( $id ) {
		$id = preg_replace( '/[^A-Za-z0-9_-]/', '\\\\$0', $id );

		return Xml::encodeJsVar( "#$id" );
	}

	public static function addModules( OutputPage $out ) {
		$modules = array( 'ext.translate.quickedit' );
		Hooks::run( 'TranslateBeforeAddModules', array( &$modules ) );
		$out->addModules( $modules );

		// Might be needed, but ajax doesn't load it
		// Globals :(
		$diff = new DifferenceEngine;
		$diff->showDiffStyle();
	}

	/// @since 2012-01-04
	protected function mustBeKnownMessage() {
		if ( !$this->group ) {
			throw new TranslationHelperException( 'unknown group' );
		}
	}

	/// @since 2012-01-04
	protected function mustHaveDefinition() {
		if ( (string)$this->getDefinition() === '' ) {
			throw new TranslationHelperException( 'message does not have definition' );
		}
	}
}

/**
 * Translation helpers can throw this exception when they cannot do
 * anything useful with the current message. This helps in debugging
 * why some fields are not shown. See also helpers in TranslationHelpers:
 * - mustBeKnownMessage()
 * - mustHaveDefinition()
 * @since 2012-01-04 (Renamed in 2012-07-24 to fix typo in name)
 */
class TranslationHelperException extends MWException {
}
