<?php

/**
 * Provides the nice boxes that aid the translators to do their job.
 * Boxes contain definition, documentation, other languages, translation memory
 * suggestions, highlighted changes etc.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2010 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
class TranslationHelpers {
	/**
	 * Title of the message
	 */
	protected $title;
	/**
	 * Name of the message without namespace or language code.
	 */
	protected $page;
	/**
	 * The language we are translating into.
	 */
	protected $targetLanguage;
	/**
	 * The group object of the message (or null if there isn't any)
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
	 * @param Title $title Title of a page that holds a translation.
	 */
	public function __construct( Title $title ) {
		$this->title = $title;
		$this->init();
	}

	/**
	 * Initializes member variables.
	 */
	protected function init() {
		$title = $this->title;
		list( $page, $code ) = TranslateEditAddons::figureMessage( $title );

		$this->page = $page;
		$this->targetLanguage = $code;
		$this->group = self::getMessageGroup( $title->getNamespace(), $page );
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
	 * Gets the message definition.
	 * @return String
	 */
	public function getDefinition() {
		if ( $this->definition !== null ) {
			return $this->definition;
		}

		if ( $this->group === null ) {
			return;
		}

		$this->definition = $this->group->getMessage( $this->page, 'en' );

		return $this->definition;
	}

	/**
	 * Gets the current message translation. Fuzzy messages will be marked as
	 * such unless translation is provided manually.
	 * @return String
	 */
	public function getTranslation() {
		if ( $this->translation !== null ) {
			return $this->translation;
		}

		// Shorter names
		$page = $this->page;
		$code = $this->targetLanguage;
		$group = $this->group;

		// Try database first
		$translation = TranslateUtils::getMessageContent(
			$page, $code, $this->title->getNamespace()
		);

		if ( $translation !== null ) {
			if ( !TranslateEditAddons::hasFuzzyString( $translation ) && TranslateEditAddons::isFuzzy( $this->title ) ) {
				$translation = TRANSLATE_FUZZY . $translation;
			}
		} elseif ( $group && !$group instanceof FileBasedMessageGroup ) {
			// Then try to load from files (old groups)
			$translation = $group->getMessage( $page, $code );
		} else {
			// Nothing to prefil
			$translation = '';
		}

		$this->translation = $translation;

		return $translation;
	}

	/**
	 * Manual override for the translation. If not given or it is null, the code
	 * will try to fetch it automatically.
	 * @param $translation String or null
	 */
	public function setTranslation( $translation ) {
		$this->translation = $translation;
	}

	/**
	 * Returns block element HTML snippet that contains the translation aids.
	 * Not all boxes are shown all the time depending on whether they have
	 * any information to show and on configuration variables.
	 * @return String. Block level HTML snippet or empty string.
	 */
	public function getBoxes( $suggestions = 'sync' ) {
		// Box filter
		$all = array(
			'other-languages' => array( $this, 'getOtherLanguagesBox' ),
			'translation-memory' => array( $this, 'getSuggestionBox' ),
			'translation-diff' => array( $this, 'getPageDiff' ),
			'page-translation' => array( $this, 'getTranslationPageDiff' ),
			'separator' => array( $this, 'getSeparatorBox' ),
			'documenation' => array( $this, 'getDocumentationBox' ),
			'definition' => array( $this, 'getDefinitionBox' ),
			'check' => array( $this, 'getCheckBox' ),
		);

		if ( $suggestions === 'async' ) {
			$all['translation-memory'] = array( $this, 'getLazySuggestionBox' );
		} elseif( $suggestions === 'only' ) {
			return (string) call_user_func( $all['translation-memory'], 'lazy' );
		}

		$boxes = array();
		foreach ( $all as $type => $cb ) {
			$box = call_user_func( $cb );

			if ( $box ) {
				$boxes[$type] = $box;
			}
		}

		if ( count( $boxes ) ) {
			return Html::rawElement( 'div', array( 'class' => 'mw-sp-translate-edit-fields' ), implode( "\n\n", $boxes ) );
		} else {
			return '';
		}
	}

	/**
	 * Returns suggestions from a translation memory.
	 * @return Html snippet which contains the suggestions.
	 */
	protected function getTmBox( $serviceName, $config ) {
		if ( !$this->targetLanguage ) {
			return null;
		}

		if ( strval( $this->getDefinition() ) === '' ) {
			return null;
		}

		if ( self::checkTranslationServiceFailure( $serviceName ) ) {
			return null;
		}

		// Needed data
		$code = $this->targetLanguage;
		$definition = $this->getDefinition();
		$ns = $this->title->getNsText();

		// Fetch suggestions
		$server = $config['server'];
		$port   = $config['port'];
		$timeout = $config['timeout'];
		$def = rawurlencode( $definition );
		$url = "$server:$port/tmserver/en/$code/unit/$def";
		$suggestions = Http::get( $url, $timeout );

		$sugFields = array();
		// Parse suggestions, but limit to three (in case there would be more)
		$boxes = array();

		if ( $suggestions !== false ) {
			$suggestions = FormatJson::decode( $suggestions, true );

			foreach ( $suggestions as $s ) {
				// No use to suggest them what they are currently viewing
				if ( $s['context'] === "$ns:{$this->page}" ) {
					continue;
				}

				$accuracy = wfMsgHtml( 'translate-edit-tmmatch' , sprintf( '%.2f', $s['quality'] ) );
				$legend = array( $accuracy => array() );

				$source_page = Title::newFromText( $s['context'] . "/$code" );
				if ( $source_page ) {
					$legend[$accuracy][] = self::ajaxEditLink( $source_page, '•' );
				}

				$text = $this->suggestionField( $s['target'] );
				$params = array( 'class' => 'mw-sp-translate-edit-tmsug', 'title' => $s['source'] );

				if ( isset( $sugFields[$s['target']] ) ) {
					$sugFields[$s['target']][2] = array_merge_recursive( $sugFields[$s['target']][2], $legend );
				} else {
					$sugFields[$s['target']] = array( $text, $params, $legend );
				}
			}

			foreach ( $sugFields as $field ) {
				list( $text, $params, $label ) = $field;
				$legend = array();

				foreach ( $label as $acc => $links ) {
					$legend[] = $acc . ' ' . implode( " ", $links );
				}

				$legend = implode( ' | ', $legend );
				$boxes[] = Html::rawElement( 'div', $params, self::legend( $legend ) . $text . self::clear() ) . "\n";
			}
		} else {
			// Assume timeout
			self::reportTranslationSerficeFailure( $serviceName );
		}

		$boxes = array_slice( $boxes, 0, 3 );
		$result = implode( "\n", $boxes );

		// Limit to three max
		return $result;
	}

	protected function getSuggestionBox( $async = false ) {
		global $wgTranslateTranslationServices;

		$boxes = array();
		foreach ( $wgTranslateTranslationServices as $name => $config ) {
			if ( $async === 'async' ) {
				$config['timeout'] = $config['timeout-async'];
			} else {
				$config['timeout'] = $config['timeout-sync'];
			}

			if ( $config['type'] === 'tmserver' ) {
				$boxes[] = $this->getTmBox( $name, $config );
			} elseif( $config['type'] === 'google' ) {
				$boxes[] = $this->getGoogleSuggestion( $name, $config );
			} elseif( $config['type'] === 'apertium' ) {
				$boxes[] = $this->getApertiumSuggestion( $name, $config );
			} else {
				throw new MWException( __METHOD__ . ": Unsupported type {$config['type']}" );
			}
		}

		// Remove nulls and falses
		$boxes = array_filter( $boxes );

		// Enclose if there is more than one box
		if ( count( $boxes ) ) {
			$sep = Html::element( 'hr', array( 'class' => 'mw-translate-sep' ) );
			return TranslateUtils::fieldset( wfMsgHtml( 'translate-edit-tmsugs' ),
				implode( "$sep\n", $boxes ), array( 'class' => 'mw-translate-edit-tmsugs' ) );
		} else {
			return null;
		}
	}

	protected function getGoogleSuggestion( $serviceName, $config ) {
		global $wgMemc;

		if ( self::checkTranslationServiceFailure( $serviceName ) ) {
			return null;
		}

		$code = $this->targetLanguage;
		$definition = trim( strval( $this->getDefinition() ) );
		$definition = str_replace( "\n", "<newline/>", $definition );

		$memckey = wfMemckey( 'translate-tmsug-badcodes-' . $serviceName );
		$unsupported = $wgMemc->get( $memckey );

		if ( $definition === '' || isset( $unsupported[$code] ) ) {
			return null;
		}

		/* There is 5000 *character* limit, but encoding needs to be taken into
		 * account. Not sure if this applies also to post method. */
		if ( strlen( rawurlencode( $definition ) ) > 4900 ) {
			return null;
		}

		$options = self::makeGoogleQueryParams( $definition, "en|$code", $config );
		$json = Http::post( $config['url'], $options );
		$response = FormatJson::decode( $json );

		if ( $json === false ) {
				wfWarn(  __METHOD__ . ': Http::get failed' );
				// Most likely a timeout or other general error
				self::reportTranslationSerficeFailure( $serviceName );

				return null;
		} elseif ( !is_object( $response ) ) {
				wfWarn(  __METHOD__ . ': Unable to parse reply: ' . strval( $json ) );
				error_log(  __METHOD__ . ': Unable to parse reply: ' . strval( $json ) );

				return null;
		}

		if ( $response->responseStatus === 200 ) {
			$text = Sanitizer::decodeCharReferences( $response->responseData->translatedText );
			$text = str_replace( "<newline/>", "\n", $text );
			$text = $this->suggestionField( $text );
			return Html::rawElement( 'div', null, self::legend( $serviceName ) . $text . self::clear() );
		} elseif ( $response->responseDetails === 'invalid translation language pair' ) {
			$unsupported[$code] = true;
			$wgMemc->set( $memckey, $unsupported, 60 * 60 * 8 );
		} else {
			// Unknown error, assume the worst
			self::reportTranslationSerficeFailure( $serviceName );
			wfWarn(  __METHOD__ . "($serviceName): " . $response->responseDetails );
			error_log( __METHOD__ . "($serviceName): " . $response->responseDetails );
			return null;
		}
	}

	protected static function makeGoogleQueryParams( $definition, $pair, $config ) {
		global $wgSitename, $wgVersion, $wgProxyKey;
		$options = array();
		$options['timeout'] = $config['timeout'];

		$options['postData'] = array(
			'q' => $definition,
			'v' => '1.0',
			'langpair' => $pair,
			// Unique but not identifiable
			'userip' => sha1( $wgProxyKey . wfGetIp() ),
			'x-application' => "$wgSitename (MediaWiki $wgVersion; Translate " . TRANSLATE_VERSION . ")",
		);

		if ( $config['key'] ) {
			$options['postData']['key'] = $config['key'];
		}

		return $options;
	}

	protected function getApertiumSuggestion( $serviceName, $config ) {
		global $wgMemc;

		if ( self::checkTranslationServiceFailure( $serviceName ) ) {
			//return null;
		}

		$page = $this->page;
		$code = $this->targetLanguage;
		$ns = $this->title->getNamespace();

		$memckey = wfMemckey( 'translate-tmsug-pairs-' . $serviceName );
		$pairs = $wgMemc->get( $memckey );

		if ( !$pairs ) {

			$pairs = array();
			$json = Http::get( $config['pairs'], 5 );
			$response = FormatJson::decode( $json );

			if ( $json === false ) {
				self::reportTranslationSerficeFailure( $serviceName );
				return null;
			} elseif ( !is_object( $response ) ) {
				error_log(  __METHOD__ . ': Unable to parse reply: ' . strval( $json ) );
				return null;
			}

			foreach ( $response->responseData as $pair ) {
				$source = $pair->sourceLanguage;
				$target = $pair->targetLanguage;
				if ( !isset( $pairs[$target] ) ) {
					$pairs[$target] = array();
				}
				$pairs[$target][$source] = true;
			}

			$wgMemc->set( $memckey, $pairs, 60 * 60 * 24 );
		}

		if ( isset( $config['codemap'][$code] ) ) {
			$code = $config['codemap'][$code];
		}

		$code = str_replace( '-', '_', wfBCP47( $code ) );

		if ( !isset( $pairs[$code] ) ) {
			return;
		}

		$suggestions = array();

		$codemap = array_flip( $config['codemap'] );
		foreach ( $pairs[$code] as $candidate => $unused ) {
			$mwcode = str_replace( '_', '-', strtolower( $candidate ) );

			if ( isset( $codemap[$mwcode] ) ) {
				$mwcode = $codemap[$mwcode];
			}

			$text = TranslateUtils::getMessageContent( $page, $mwcode, $ns );
			if ( $text === null || TranslateEditAddons::hasFuzzyString( $text ) ) {
				continue;
			}

			$title = Title::makeTitleSafe( $ns, "$page/$mwcode" );
			if ( $title && TranslateEditAddons::isFuzzy( $title ) ) {
				continue;
			}

			$options = self::makeGoogleQueryParams( $text, "$candidate|$code", $config );
			$options['postData']['format'] = 'html';
			$json = Http::post( $config['url'], $options );
			$response = FormatJson::decode( $json );
			if ( $json === false || !is_object( $response ) ) {
				self::reportTranslationSerficeFailure( $serviceName );
				break; // Too slow, back off
			} elseif ( $response->responseStatus !== 200 ) {
				error_log( __METHOD__ . " with ($serviceName ($candidate)): " . $response->responseDetails );
			} else {
				$sug = Sanitizer::decodeCharReferences( $response->responseData->translatedText );
				$sug = $this->suggestionField( $sug );
				$suggestions[] = Html::rawElement( 'div',
					array( 'title' => $text ),
					self::legend( "$serviceName ($candidate)" ) . $sug . self::clear()
				);
			}
		}

		if ( !count( $suggestions ) ) {
			return null;
		}

		$divider = Html::element( 'div', array( 'style' => 'margin-bottom: 0.5ex' ) );
		return implode( "$divider\n", $suggestions );
	}

	protected function getDefinitionBox() {
		$en = $this->getDefinition();
		if ( $en === null ) {
			return null;
		}

		global $wgUser;

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

		$dialogID = $this->dialogID();
		$id = Sanitizer::escapeId( "def-$dialogID" );
		$msg = $this->adder( $id ) . "\n" . Html::rawElement( 'span',
			array( 'class' => 'mw-translate-edit-deftext' ),
			TranslateUtils::convertWhiteSpaceToHTML( $en )
		);

		$msg .= Html::element( 'pre', array( 'id' => $id, 'style' => 'display: none;' ), $en );

		$class = array( 'class' => 'mw-sp-translate-edit-definition mw-translate-edit-definition' );

		return TranslateUtils::fieldset( $label, $msg, $class );
	}

	protected function getCheckBox() {
		global $wgTranslateDocumentationLanguageCode;

		if ( $this->group === null ) {
			return;
		}

		$page = $this->page;
		$translation = $this->getTranslation();
		$code = $this->targetLanguage;
		$en = $this->getDefinition();

		if ( strval( $translation ) === '' ) {
			return null;
		}

		if ( $code === $wgTranslateDocumentationLanguageCode ) {
			return null;
		}

		$checker = $this->group->getChecker();
		if ( !$checker ) {
			return null;
		}

		$message = new FatMessage( $page, $en );
		// Take the contents from edit field as a translation
		$message->setTranslation( $translation );

		$checks = $checker->checkMessage( $message, $code );
		if ( !count( $checks ) ) {
			return null;
		}

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
			if ( $text === null ) {
				continue;
			}

			$label =
				TranslateUtils::getLanguageName( $fbcode, false, $wgLang->getCode() ) .
				wfMsg( 'word-separator' ) .
				wfMsg( 'parentheses', wfBCP47( $fbcode ) );

			$target = Title::makeTitleSafe( $ns, "$page/$fbcode" );
			if ( $target ) {
				$label = self::ajaxEditLink( $target, htmlspecialchars( $label ) );
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

		if ( !$wgTranslateDocumentationLanguageCode ) {
			return null;
		}

		$page = $this->page;
		$ns = $this->title->getNamespace();

		$title = Title::makeTitle( $ns, $page . '/' . $wgTranslateDocumentationLanguageCode );
		$edit = self::ajaxEditLink( $title, wfMsgHtml( 'translate-edit-contribute' ) );
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

	protected function getPageDiff() {
		if ( $this->group instanceof WikiPageMessageGroup ) {
			return null;
		}

		// Shortcuts
		$code = $this->targetLanguage;
		$key = $this->page;

		$definitionTitle = Title::makeTitleSafe( $this->title->getNamespace(), "$key/en" );
		if ( !$definitionTitle || !$definitionTitle->exists() ) {
			return null;
		}

		$db = wfGetDB( DB_MASTER );
		$id = $db->selectField( 'revtag_type', 'rtt_id',
			array( 'rtt_name' => 'tp:transver' ), __METHOD__ );

		$conds = array(
			'rt_page' => $this->title->getArticleId(),
			'rt_type' => $id,
			'rt_revision' => $this->title->getLatestRevID(),
		);

		$latestRevision = $definitionTitle->getLatestRevID();

		$translationRevision =  $db->selectField( 'revtag', 'rt_value', $conds, __METHOD__ );
		if ( $translationRevision === false ) {
			return null;
		}

		$oldtext = Revision::newFromTitle( $definitionTitle, $translationRevision )->getText();
		$newtext = Revision::newFromTitle( $definitionTitle, $latestRevision )->getText();

		if ( $oldtext === $newtext ) {
			return null;
		}

		$diff = new DifferenceEngine;
		$diff->setText( $oldtext, $newtext );
		$diff->setReducedLineNumbers();
		$diff->showDiffStyle();

		return $diff->getDiff( wfMsgHtml( 'tpt-diff-old' ), wfMsgHtml( 'tpt-diff-new' ) );
	}

	protected function getTranslationPageDiff() {
		global $wgEnablePageTranslation;

		if ( !$wgEnablePageTranslation ) {
			return null;
		}

		if ( !$this->group instanceof WikiPageMessageGroup ) {
			return null;
		}

		// Shortcuts
		$code = $this->targetLanguage;
		$key = $this->page;

		// TODO: encapsulate somewhere
		$page = TranslatablePage::newFromTitle( $this->group->title );
		$rev = $page->getTransRev( "$key/$code" );
		$latest = $page->getMarkedTag();
		if ( $rev === $latest ) {
			return null;
		}

		$oldpage = TranslatablePage::newFromRevision( $this->group->title, $rev );
		$oldtext = $newtext = null;
		foreach ( $oldpage->getParse()->getSectionsForSave() as $section ) {
			if ( $this->group->title->getPrefixedDBKey() . '/' . $section->id === $key ) {
				$oldtext = $section->getTextForTrans();
			}
		}

		foreach ( $page->getParse()->getSectionsForSave() as $section ) {
			if ( $this->group->title->getPrefixedDBKey() . '/' . $section->id === $key ) {
				$newtext = $section->getTextForTrans();
			}
		}

		if ( $oldtext === $newtext ) {
			return null;
		}

		$diff = new DifferenceEngine;
		$diff->setText( $oldtext, $newtext );
		$diff->setReducedLineNumbers();
		$diff->showDiffStyle();

		return $diff->getDiff( wfMsgHtml( 'tpt-diff-old' ), wfMsgHtml( 'tpt-diff-new' ) );
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

	public function getLazySuggestionBox() {
		global $wgScript;

		if ( $this->group === null || !$this->targetLanguage ) {
			return null;
		}

		$url = SpecialPage::getTitleFor( 'Translate', 'editpage' )->getLocalUrl( array(
			'suggestions' => 'only',
			'page' => $this->title->getPrefixedDbKey(),
			'loadgroup' => $this->group->getId(),
		) );
		$url = Xml::escapeJsString( $url );

		$dialogID = $this->dialogID();
		$id = Sanitizer::escapeId( "tm-lazysug-$dialogID" );

		$script = Html::inlineScript( "jQuery('#$id').load( \"$url\" )" );
		$spinner = Html::element( 'div', array( 'class' => 'mw-ajax-loader' ) );
		return Html::rawElement( 'div', array( 'id' => $id ), $script.$spinner );
	}

	public function dialogID() {
		return sha1( $this->title->getPrefixedDbKey() );
	}

	public function adder( $source ) {
			$target = Xml::escapeJsString( $this->getTextareaId() );
			$source = Xml::escapeJsString( $source );
			$params = array(
				'onclick' => "jQuery('#$target').val(jQuery('#$source').text()).focus(); return false;",
				'href' => '#',
				'title' => wfMsg( 'translate-use-suggestion' )
			);

			return Html::element( 'a', $params, '↓' );
	}

	public function suggestionField( $text ) {
		static $counter = 0;

		$counter++;
		$dialogID = $this->dialogID();
		$id = Sanitizer::escapeId( "tmsug-$dialogID-$counter" );
		$contents = TranslateUtils::convertWhiteSpaceToHTML( $text );
		$contents .= Html::element( 'pre', array( 'id' => $id, 'style' => 'display: none;' ), $text );

		return $this->adder( $id ) . "\n" . $contents;
	}

	/**
	 * Ajax-enabled message editing link.
	 * @param $target Title: Title of the target message.
	 * @param $text String: Link text for Linker::link()
	 * @return link
	 */
	public static function ajaxEditLink( $target, $text ) {
		global $wgUser;

		list( $page, ) = TranslateEditAddons::figureMessage( $target );
		$group = TranslateUtils::messageKeyToGroup( $target->getNamespace(), $page );

		$params = array();
		$params['action'] = 'edit';
		$params['loadgroup'] = $group;

		$jsEdit = TranslationEditPage::jsEdit( $target, $group );

		return $wgUser->getSkin()->link( $target, $text, $jsEdit, $params );
	}

	/**
	 * How many failures during failure period need to happen to consider
	 * the service being temporarily off-line. */
	protected static $serviceFailureCount = 5;
	/**
	 * How long after the last detected failure we clear the status and
	 * try again.
	 */
	protected static $serviceFailurePeriod = 300;

	/**
	 * Checks whether the given service has exceeded failure count */
	public static function checkTranslationServiceFailure( $service ) {
		global $wgMemc;

		$key = wfMemckey( "translate-service-$service" );

		// Both false and null are converted to zero, which is desirable
		return intval( $wgMemc->get( $key ) ) >= self::$serviceFailureCount;
	}

	/**
	 * Increases the failure count for a given service */
	public static function reportTranslationSerficeFailure( $service ) {
		global $wgMemc;

		$key = wfMemckey( "translate-service-$service" );
		// Both false and null are converted to zero, which is desirable.
		/* FIXME: not atomic, but the default incr() implemention seems to
		 * ignore expiry time */
		$count = intval( $wgMemc->get( $key ) );
		$wgMemc->set( $key, $count + 1, self::$serviceFailurePeriod );

		/* By using >= we expose if something is still increasing failure
		 * count if we are over the limit */
		if ( $count + 1 >= self::$serviceFailureCount ) {
			$language = Language::factory( 'en' );
			$period = $language->formatTimePeriod( self::$serviceFailurePeriod );
			error_log( "Translation service $service suspended for $period" );
		}
	}
}
