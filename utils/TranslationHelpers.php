<?php
/**
 * Contains helper class for interface parts that aid translations in doing
 * their thing.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
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
	/** @var TranslationAidDataProvider */
	private $dataProvider;
	/**
	 * The group object of the message (or null if there isn't any)
	 * @var MessageGroup|null
	 */
	protected $group;
	/**
	 * The current translation.
	 * @var string
	 */
	private $translation;
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
		$this->dataProvider = new TranslationAidDataProvider( $this->handle );
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
	 * @return string
	 */
	public function getTextareaId() {
		return $this->textareaId;
	}

	/**
	 * Enable or disable extra help for editing.
	 * @param bool $mode
	 */
	public function setEditMode( $mode = true ) {
		$this->editMode = $mode;
	}

	/**
	 * Gets the message definition.
	 * @return string
	 */
	public function getDefinition() {
		$this->mustBeKnownMessage();

		$obj = new MessageDefinitionAid(
			$this->group,
			$this->handle,
			RequestContext::getMain(),
			$this->dataProvider
		);

		return $obj->getData()['value'];
	}

	/**
	 * Gets the current message translation. Fuzzy messages will be marked as
	 * such unless translation is provided manually.
	 * @return string
	 */
	public function getTranslation() {
		if ( $this->translation === null ) {
			$obj = new CurrentTranslationAid(
				$this->group,
				$this->handle,
				RequestContext::getMain(),
				$this->dataProvider
			);
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
	 * Returns block element HTML snippet that contains the translation aids.
	 * Not all boxes are shown all the time depending on whether they have
	 * any information to show and on configuration variables.
	 * @return string Block level HTML snippet or empty string.
	 */
	public function getBoxes() {
		// Box filter
		$all = $this->getBoxNames();

		$boxes = [];
		foreach ( $all as $type => $cb ) {
			$box = $this->callBox( $type, $cb );
			if ( $box ) {
				$boxes[$type] = $box;
			}
		}

		Hooks::run( 'TranslateGetBoxes', [ $this->group, $this->handle, &$boxes ] );

		if ( count( $boxes ) ) {
			return Html::rawElement(
				'div',
				[ 'class' => 'mw-sp-translate-edit-fields' ],
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
	public function callBox( $type, $cb, array $params = [] ) {
		try {
			return call_user_func_array( $cb, $params );
		} catch ( TranslationHelperException $e ) {
			return "<!-- Box $type not available: {$e->getMessage()} -->";
		}
	}

	public function getBoxNames(): array {
		return [
			'documentation' => [ $this, 'getDocumentationBox' ],
			'definition' => [ $this, 'getDefinitionBox' ],
		];
	}

	public function getDefinitionBox() {
		$this->mustHaveDefinition();
		$en = $this->getDefinition();

		$linkTag = self::ajaxEditLink( $this->handle->getTitle(), $this->group->getLabel() );
		$label =
			wfMessage( 'translate-edit-definition' )->escaped() .
				wfMessage( 'word-separator' )->escaped() .
				wfMessage( 'parentheses' )->rawParams( $linkTag )->escaped();

		// Source language object
		$sl = Language::factory( $this->group->getSourceLanguage() );

		$dialogID = $this->dialogID();
		$id = Sanitizer::escapeIdForAttribute( "def-$dialogID" );
		$msg = $this->adder( $id, $sl ) . "\n" . Html::rawElement( 'div',
			[
				'class' => 'mw-translate-edit-deftext',
				'dir' => $sl->getDir(),
				'lang' => $sl->getHtmlCode(),
			],
			TranslateUtils::convertWhiteSpaceToHTML( $en )
		);

		$msg .= $this->wrapInsert( $id, $en );

		$class = [ 'class' => 'mw-sp-translate-edit-definition' ];

		return TranslateUtils::fieldset( $label, $msg, $class );
	}

	public function getDocumentationBox() {
		global $wgTranslateDocumentationLanguageCode;

		if ( !$wgTranslateDocumentationLanguageCode ) {
			throw new TranslationHelperException( 'Message documentation language code is not defined' );
		}

		$context = RequestContext::getMain();
		$page = $this->handle->getKey();
		$ns = $this->handle->getTitle()->getNamespace();

		$title = $this->handle->getTitleForLanguage( $wgTranslateDocumentationLanguageCode );
		$edit = self::ajaxEditLink(
			$title,
			$context->msg( 'translate-edit-contribute' )->text()
		);
		$info = TranslateUtils::getMessageContent( $page, $wgTranslateDocumentationLanguageCode, $ns );

		$class = 'mw-sp-translate-edit-info';

		// The information is most likely in English
		$divAttribs = [ 'dir' => 'ltr', 'lang' => 'en', 'class' => 'mw-content-ltr' ];

		if ( (string)$info === '' ) {
			$info = $context->msg( 'translate-edit-no-information' )->plain();
			$class = 'mw-sp-translate-edit-noinfo';
			$lang = $context->getLanguage();
			// The message saying that there's no info, should be translated
			$divAttribs = [ 'dir' => $lang->getDir(), 'lang' => $lang->getHtmlCode() ];
		}
		$class .= ' mw-sp-translate-message-documentation';

		$contents = $context->getOutput()->parseInlineAsInterface( $info );

		return TranslateUtils::fieldset(
			$context->msg( 'translate-edit-information' )->rawParams( $edit )->escaped(),
			Html::rawElement( 'div', $divAttribs, $contents ), [ 'class' => $class ]
		);
	}

	public function dialogID(): string {
		$hash = sha1( $this->handle->getTitle()->getPrefixedDBkey() );

		return substr( $hash, 0, 4 );
	}

	/**
	 * @param string $source jQuery selector for element containing the source
	 * @param Language $lang
	 * @return string
	 */
	public function adder( $source, $lang ) {
		if ( !$this->editMode ) {
			return '';
		}
		$target = self::jQueryPathId( $this->getTextareaId() );
		$source = self::jQueryPathId( $source );
		$dir = $lang->getDir();
		$params = [
			'onclick' => "jQuery($target).val(jQuery($source).text()).focus(); return false;",
			'href' => '#',
			'title' => wfMessage( 'translate-use-suggestion' )->text(),
			'class' => 'mw-translate-adder mw-translate-adder-' . $dir,
		];

		return Html::element( 'a', $params, '↓' );
	}

	/**
	 * @param string|int $id
	 * @param string $text
	 * @return string
	 */
	public function wrapInsert( $id, $text ) {
		return Html::element( 'pre', [ 'id' => $id, 'style' => 'display: none;' ], $text );
	}

	/**
	 * Ajax-enabled message editing link.
	 * @param Title $target Title of the target message.
	 * @param string $text Link text for Linker::link()
	 * @return string HTML link
	 */
	public static function ajaxEditLink( Title $target, $text ) {
		$handle = new MessageHandle( $target );
		$uri = TranslateUtils::getEditorUrl( $handle );
		$link = Html::element(
			'a',
			[ 'href' => $uri ],
			$text
		);

		return $link;
	}

	/**
	 * Escapes $id such that it can be used in jQuery selector.
	 * @param string $id
	 * @return string
	 */
	public static function jQueryPathId( $id ) {
		$id = preg_replace( '/[^A-Za-z0-9_-]/', '\\\\$0', $id );

		return Xml::encodeJsVar( "#$id" );
	}

	public static function addModules( OutputPage $out ) {
		$out->addModuleStyles( 'ext.translate.quickedit' );

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
