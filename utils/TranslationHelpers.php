<?php

class TranslationHelpers {
	protected $title;
	protected $page;
	protected $targetLanguage;
	protected $group;
	protected $translation;
	protected $definition;

	public function __construct( Title $title ) {
		$this->title = $title;
		$this->init();
	}

	protected function init() {
		$title = $this->title;
		list( $page, $code ) = self::figureMessage( $title );
		
		$this->page = $page;
		$this->targetLanguage = $code;
		$this->group = self::getMessageGroup( $title->getNamespace(), $page );
	}

	public static function figureMessage( Title $title ) {
		$text = $title->getDBkey();
		$pos = strrpos( $text, '/' );
		if ( $pos === false ) {
			$code = '';
			$key = $text;
		} else {
			$code = substr( $text, $pos + 1 );
			$key = substr( $text, 0, $pos );
		}
		return array( $key, $code );
	}

	/**
	 * Tries to determine to which group this message belongs. It tries to get
	 * group id from loadgroup GET-paramater, but fallbacks to messageIndex file
	 * if no valid group was provided.
	 * @param $namespace  int  The namespace where the page is.
	 * @param $key  string     The message key we are interested in.
	 * @return MessageGroup which the key belongs to, or null.
	 */
	protected static function getMessageGroup( $namespace, $key ) {
		global $wgRequest;
		$group = $wgRequest->getText( 'loadgroup', '' );
		$mg = MessageGroups::getGroup( $group );

		# If we were not given group
		if ( $mg === null ) {
			$group = TranslateUtils::messageKeyToGroup( $namespace, $key );
			if ( $group ) {
				$mg = MessageGroups::getGroup( $group );
			}
		}

		return $mg;
	}


	public function getDefinition() {
		if ( $this->definition !== null ) return $this->definition;
		$this->definition = $this->group->getMessage( $this->page, 'en' );
		return $this->definition;
	}

	public function getTranslation() {
		if ( $this->translation !== null ) return $this->translation;

		// Shoter names
		$page = $this->page;
		$code = $this->targetLanguage;

		// Try database first
		$translation = TranslateUtils::getMessageContent(
			$page, $code, $this->group->getNamespace()
		);

		if ( $translation !== null ) {
			if ( !TranslateEditAddons::hasFuzzyString( $translation ) && TranslateEditAddons::isFuzzy( $this->title ) ) {
				$translation = TRANSLATE_FUZZY . $translation;
			}
		} elseif ( !$this->group instanceof FileBasedMessageGroup ) {
			// Then try to load from files (old groups)
			$translation = $this->group->getMessage( $page, $code );
		} else {
			// Nothing to prefil
			$translation = '';
		}
		$this->translation = $translation;
		return $translation;
	}

	public function getBoxes( $types = null ) {
		if ( !$this->group ) return '';

		// Box filter
		$all = array(
			'other-languages' => array( $this, 'getOtherLanguagesBox' ),
			'translation-memory' => array( $this, 'getTmBox' ),
			'separator' => array( $this, 'getSeparatorBox' ),
			'documenation' => array( $this, 'getDocumentationBox' ),
			'definition' => array( $this, 'getDefinitionBox' ),
			'check' => array( $this, 'getCheckBox' ),
		);
		if ( $types !== null ) foreach ( $types as $type ) unset( $all[$type] );

		$boxes = array();
		foreach ( $all as $type => $cb ) {
			$box = call_user_func( $cb );
			if ( $box ) $boxes[$type] = $box;
		}

		if ( count( $boxes ) ) {
			return Html::rawElement( 'div', array( 'class' => 'mw-sp-translate-edit-fields' ), implode( "\n\n", $boxes ) );
		} else {
			throw new MWException( "no boxes" );
		}
	}

	/**
	 * Returns suggestions from a translation memory.
	 * @return Html fieldset snippet which contains the suggestions.
	 */
	protected function getTmBox() {
		global $wgTranslateTM;
		if ( $wgTranslateTM === false ) return null;

		// Needed data
		$code = $this->targetLanguage;
		$definition = $this->getDefinition();

		$boxes = array();

		// Fetch suggestions
		$server = $wgTranslateTM['server'];
		$port   = $wgTranslateTM['port'];
		$timeout = $wgTranslateTM['timeout'];
		$def = rawurlencode( $definition );
		$url = "$server:$port/tmserver/en/$code/unit/$def";
		$suggestions = Http::get( $url, $timeout );

		// Parse suggestions, but limit to three (in case there would be more)
		if ( $suggestions !== false ) {
			$suggestions = json_decode( $suggestions, true );
			$suggestions = array_slice( $suggestions, 0, 3 );
			foreach ( $suggestions as $s ) {
				$label = wfMsgHtml( 'translate-edit-tmmatch' , sprintf( '%.2f', $s['quality'] ) );
				$text = TranslateUtils::convertWhiteSpaceToHTML( $s['target']  );

				$text = TranslateUtils::convertWhiteSpaceToHTML( $text );
				$params = array( 'class' => 'mw-sp-translate-edit-tmsug', 'title' => $s['source'] );
				$boxes[] = Html::rawElement( 'div', $params, self::legend( $label ) . $text . self::clear() );
			}
		}

		// Enclose if there is more than one box
		if ( count( $boxes ) ) {
			$sep = Html::element( 'hr', array( 'class' => 'mw-translate-sep' ) );
			return TranslateUtils::fieldset( wfMsgHtml( 'translate-edit-tmsugs' ),
				implode( "$sep\n", $boxes ), array( 'class' => 'mw-translate-edit-tmsugs' ) );
		} else {
			return null;
		}
	}

	protected function getDefinitionBox() {
		$en = $this->getDefinition();
		if ( $en === null ) return null;

		global $wgUser;
		$label = " ()";
		$title = $wgUser->getSkin()->link(
			SpecialPage::getTitleFor( 'Translate' ),
			htmlspecialchars( $this->group->getLabel() ),
			array(),
			array(
				'group' => $this->group->getId(),
				'language' => $this->targetLanguage
			)
		);

		$label =
			wfMsg( 'translate-edit-definition' ) .
			wfMsg( 'word-separator' ) .
			wfMsg( 'parentheses', $title );

		$msg = Html::rawElement( 'span',
			array( 'class' => 'mw-translate-edit-deftext' ),
			TranslateUtils::convertWhiteSpaceToHTML( $en )
		);

		$class = array( 'class' => 'mw-sp-translate-edit-definition mw-translate-edit-definition' );
		return TranslateUtils::fieldset( $label, $msg, $class );
	}

	protected function getCheckBox() {
		global $wgTranslateDocumentationLanguageCode;

		$page = $this->page;
		$translation = $this->getTranslation();
		$code = $this->targetLanguage;
		$en = $this->getDefinition();

		if ( strval( $translation ) === '' ) return null;
		if ( $code === $wgTranslateDocumentationLanguageCode ) return null;

		$checker = $this->group->getChecker();
		if ( !$checker ) return null;

		$message = new FatMessage( $page, $en );
		// Take the contents from edit field as a translation
		$message->setTranslation( $translation );

		$checks = $checker->checkMessage( $message, $code );
		if ( !count( $checks ) ) return null;

		$checkMessages = array();
		foreach ( $checks as $checkParams ) {
			array_splice( $checkParams, 1, 0, 'parseinline' );
			$checkMessages[] = call_user_func_array( 'wfMsgExt', $checkParams );
		}

		return TranslateUtils::fieldset(
			wfMsgHtml( 'translate-edit-warnings' ), implode( '<hr />', $checkMessages ),
			array( 'class' => 'mw-sp-translate-edit-warnings' )
		);
	}

	protected function getOtherLanguagesBox() {
		global $wgLang, $wgUser;

		$code = $this->targetLanguage;
		$page = $this->page;
		$ns = $this->title->getNamespace();

		$boxes = array();
		foreach ( self::getFallbacks( $code ) as $fbcode ) {
			$text = TranslateUtils::getMessageContent( $page, $fbcode, $ns );
			if ( $text === null ) continue;

			$label =
				TranslateUtils::getLanguageName( $fbcode, false, $wgLang->getCode() ) .
				wfMsg( 'word-separator' ) .
				wfMsg( 'parentheses', wfBCP47( $fbcode ) );

			$target = Title::makeTitleSafe( $ns, "$page/$fbcode" );
			if ( $target ) {
				$label = self::editLink( $target,
					htmlspecialchars( $label ), array( 'action' => 'edit' )
				);
			}

			$text = TranslateUtils::convertWhiteSpaceToHTML( $text );
			$params = array( 'class' => 'mw-translate-edit-item' );
			$boxes[] = Html::rawElement( 'div', $params, self::legend( $label ) . $text . self::clear() );
		}

		if ( count( $boxes ) ) {
			$sep = Html::element( 'hr', array( 'class' => 'mw-translate-sep' ) );
			return TranslateUtils::fieldset( wfMsgHtml( 'translate-edit-in-other-languages' , $page ),
				implode( "$sep\n", $boxes ), array( 'class' => 'mw-sp-translate-edit-inother' ) );
		}

		return null;
	}

	public function getSeparatorBox() {
		return Html::element( 'div', array( 'class' => 'mw-translate-edit-extra' ) );
	}

	public function getDocumentationBox() {
		global $wgTranslateDocumentationLanguageCode, $wgUser, $wgOut;

		if ( !$wgTranslateDocumentationLanguageCode ) return null;
		$page = $this->page;
		$ns = $this->title->getNamespace();

		$title = Title::makeTitle( $ns, $page . '/' . $wgTranslateDocumentationLanguageCode );
		$edit = self::editLink( $title, wfMsgHtml( 'translate-edit-contribute' ), array( 'action' => 'edit' ) );
		$info = TranslateUtils::getMessageContent( $page, $wgTranslateDocumentationLanguageCode, $ns );

		$class = 'mw-sp-translate-edit-info';
		if ( $info === null ) {
			$info = wfMsg( 'translate-edit-no-information' );
			$class = 'mw-sp-translate-edit-noinfo';
		}

		if ( $this->group instanceof GettextMessageGroup ) {
			$reader = $this->group->getReader( 'en' );
			if ( $reader ) {
				global $wgContLang;
				$mykey = $wgContLang->lcfirst( $this->page );
				$data = $reader->parseFile();
				$help = GettextFormatWriter::formatcomments( @$data[$mykey]['comments'], false, @$data[$mykey]['flags'] );
				$info .= "<hr /><pre>$help</pre>";
			}
		}

		$class .= ' mw-sp-translate-message-documentation';

		$contents = $wgOut->parse( $info );
		// Remove whatever block element wrapup the parser likes to add
		$contents = preg_replace( '~^<([a-z]+)>(.*)</\1>$~us', '\2', $contents );
		return TranslateUtils::fieldset(
			wfMsgHtml( 'translate-edit-information', $edit , $page ), $contents, array( 'class' => $class )
		);

	}

	protected static function legend( $label ) {
		return Html::rawElement( 'div', array( 'class' => 'mw-translate-legend' ), $label );
	}

	protected static function clear() {
		return Html::element( 'div', array( 'style' => 'clear:both;' ) );
	}

	protected static function getFallbacks( $code ) {
		global $wgUser, $wgTranslateLanguageFallbacks;

		// User preference has the final say
		$preference = $wgUser->getOption( 'translate-editlangs' );
		if ( $preference !== 'default' ) {
			$fallbacks = array_map( 'trim', explode( ',', $preference ) );
			foreach ( $fallbacks as $k => $v ) if ( $v === $code ) unset( $fallbacks[$k] );
			return $fallbacks;
		}

		// Global configuration settings
		$fallbacks = array();
		if ( isset( $wgTranslateLanguageFallbacks[$code] ) ) {
			$fallbacks = (array) $wgTranslateLanguageFallbacks[$code];
		}

		// And the real fallback
		// TODO: why only one?
		$realFallback = $code ? Language::getFallbackFor( $code ) : false;
		if ( $realFallback && $realFallback !== 'en' ) {
			$fallbacks = array_merge( array( $realFallback ), $fallbacks );
		}

		return $fallbacks;
	}

	protected function doBox( $msg, $code, $title = false, $makelink = false ) {
		global $wgUser, $wgLang;

		$name = TranslateUtils::getLanguageName( $code, false, $wgLang->getCode() );
		$code = wfBCP47( $code );

		$skin = $wgUser->getSkin();

		$attributes = array();
		if ( !$title ) {
			$attributes['class'] = 'mw-sp-translate-in-other-big';
		} elseif ( $code === 'en' ) {
			$attributes['class'] = 'mw-sp-translate-edit-definition';
		} else {
			$attributes['class'] = 'mw-sp-translate-edit-committed';
		}
		if ( mb_strlen( $msg ) < 100 && !$title ) {
			$attributes['class'] = 'mw-sp-translate-in-other-small';
		}

		$msg = TranslateUtils::convertWhiteSpaceToHTML( $msg );

		if ( !$title ) $title = "$name ($code)";

		if ( $makelink ) {
			$linkTitle = Title::newFromText( $makelink );
			$title = $skin->link(
				$linkTitle,
				htmlspecialchars( $title ),
				array(),
				array( 'action' => 'edit' )
			);
		}

		return TranslateUtils::fieldset( $title, Html::element( 'span', null, $msg ), $attributes );
	}


	public static function editLink( $target, $text, $params = array() ) {
		global $wgUser;

		$jsEdit = TranslationEditPage::jsEdit( $target );

		return $wgUser->getSkin()->link( $target, $text, $jsEdit, $params );
	}
}