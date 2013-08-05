<?php
/**
 * Contains helper class for interface parts that aid translations in doing
 * their thing.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2010-2013 Niklas Laxström
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
	 * Tries to determine to which group this message belongs. It tries to get
	 * group id from loadgroup GET-paramater, but fallbacks to messageIndex file
	 * if no valid group was provided.
	 *
	 * @param MessageHandle $handle
	 * @param string $groupId
	 * @return MessageGroup which the key belongs to, or null.
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

		if ( $suggestions === 'async' ) {
			$all['translation-memory'] = array( $this, 'getLazySuggestionBox' );
		} elseif ( $suggestions === 'only' ) {
			return (string)$this->callBox(
				'translation-memory',
				$all['translation-memory'],
				array( 'lazy' )
			);
		} elseif ( $suggestions === 'checks' ) {
			$request = RequestContext::getMain()->getRequest();
			$this->translation = $request->getText( 'translation' );

			return (string)$this->callBox( 'check', $all['check'] );
		}

		if ( $this->group instanceof RecentMessageGroup ) {
			$all['last-diff'] = array( $this, 'getLastDiff' );
		}

		$boxes = array();
		foreach ( $all as $type => $cb ) {
			$box = $this->callBox( $type, $cb );
			if ( $box ) {
				$boxes[$type] = $box;
			}
		}

		wfRunHooks( 'TranslateGetBoxes', array( $this->group, $this->handle, &$boxes ) );

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
	 * @since 2012-01-04
	 */
	public function callBox( $type, $cb, $params = array() ) {
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
			'translation-memory' => array( $this, 'getSuggestionBox' ),
			'translation-diff' => array( $this, 'getPageDiff' ),
			'separator' => array( $this, 'getSeparatorBox' ),
			'documentation' => array( $this, 'getDocumentationBox' ),
			'definition' => array( $this, 'getDefinitionBox' ),
			'check' => array( $this, 'getCheckBox' ),
		);
	}

	/**
	 * Returns suggestions from a translation memory.
	 * @param $serviceName
	 * @param $config
	 * @throws TranslationHelperException
	 * @return string Html snippet which contains the suggestions.
	 */
	protected function getTTMServerBox( $serviceName, $config ) {
		$this->mustHaveDefinition();
		$this->mustBeTranslation();

		$source = $this->group->getSourceLanguage();
		$code = $this->handle->getCode();
		$definition = $this->getDefinition();
		$TTMServer = TTMServer::factory( $config );
		$suggestions = $TTMServer->query( $source, $code, $definition );
		if ( count( $suggestions ) === 0 ) {
			throw new TranslationHelperException( 'No suggestions' );
		}

		return $suggestions;
	}

	/**
	 * Returns suggestions from a translation memory.
	 * @param $serviceName
	 * @param $config
	 * @throws TranslationHelperException
	 * @return string Html snippet which contains the suggestions.
	 */
	protected function getRemoteTTMServerBox( $serviceName, $config ) {
		$this->mustHaveDefinition();
		$this->mustBeTranslation();

		self::checkTranslationServiceFailure( $serviceName );

		$source = $this->group->getSourceLanguage();
		$code = $this->handle->getCode();
		$definition = $this->getDefinition();
		$params = array(
			'format' => 'json',
			'action' => 'ttmserver',
			'sourcelanguage' => $source,
			'targetlanguage' => $code,
			'text' => $definition,
			'*', // Because we hate IE
		);

		wfProfileIn( 'TranslateWebServiceRequest-' . $serviceName );
		$json = Http::get( wfAppendQuery( $config['url'], $params ) );
		wfProfileOut( 'TranslateWebServiceRequest-' . $serviceName );

		$response = FormatJson::decode( $json, true );

		if ( $json === false ) {
			// Most likely a timeout or other general error
			self::reportTranslationServiceFailure( $serviceName );
			throw new TranslationHelperException( 'No reply from remote server' );
		} elseif ( !is_array( $response ) ) {
			error_log( __METHOD__ . ': Unable to parse reply: ' . strval( $json ) );
			throw new TranslationHelperException( 'Malformed reply from remote server' );
		}

		if ( !isset( $response['ttmserver'] ) ) {
			self::reportTranslationServiceFailure( $serviceName );
			throw new TranslationHelperException( 'Unexpected reply from remote server' );
		}

		$suggestions = $response['ttmserver'];
		if ( count( $suggestions ) === 0 ) {
			throw new TranslationHelperException( 'No suggestions' );
		}

		return $suggestions;
	}

	/// Since 2012-03-05
	protected function formatTTMServerSuggestions( $data ) {
		$sugFields = array();

		foreach ( $data as $serviceWrapper ) {
			$config = $serviceWrapper['config'];
			$suggestions = $serviceWrapper['suggestions'];

			foreach ( $suggestions as $s ) {
				$tooltip = wfMessage( 'translate-edit-tmmatch-source', $s['source'] )->plain();
				$text = wfMessage(
					'translate-edit-tmmatch',
					sprintf( '%.2f', $s['quality'] * 100 )
				)->plain();
				$accuracy = Html::element( 'span', array( 'title' => $tooltip ), $text );
				$legend = array( $accuracy => array() );

				$TTMServer = TTMServer::factory( $config );
				if ( $TTMServer->isLocalSuggestion( $s ) ) {
					$title = Title::newFromText( $s['location'] );
					$symbol = isset( $config['symbol'] ) ? $config['symbol'] : '•';
					$legend[$accuracy][] = self::ajaxEditLink( $title, $symbol );
				} else {
					if ( $TTMServer instanceof RemoteTTMServer ) {
						$displayName = $config['displayname'];
					} else {
						$wiki = WikiMap::getWiki( $s['wiki'] );
						$displayName = $wiki->getDisplayName() . ': ' . $s['location'];
					}

					$params = array(
						'href' => $TTMServer->expandLocation( $s ),
						'target' => '_blank',
						'title' => $displayName,
					);

					$symbol = isset( $config['symbol'] ) ? $config['symbol'] : '‣';
					$legend[$accuracy][] = Html::element( 'a', $params, $symbol );
				}

				$suggestion = $s['target'];
				$text = $this->suggestionField( $suggestion );
				$params = array( 'class' => 'mw-sp-translate-edit-tmsug' );

				// Group identical suggestions together
				if ( isset( $sugFields[$suggestion] ) ) {
					$sugFields[$suggestion][2] = array_merge_recursive( $sugFields[$suggestion][2], $legend );
				} else {
					$sugFields[$suggestion] = array( $text, $params, $legend );
				}
			}
		}

		$boxes = array();
		foreach ( $sugFields as $field ) {
			list( $text, $params, $label ) = $field;
			$legend = array();

			foreach ( $label as $acc => $links ) {
				$legend[] = $acc . ' ' . implode( " ", $links );
			}

			$legend = implode( ' | ', $legend );
			$boxes[] = Html::rawElement(
				'div',
				$params,
				self::legend( $legend ) . $text . self::clear()
			) . "\n";
		}

		// Limit to three best
		$boxes = array_slice( $boxes, 0, 3 );
		$result = implode( "\n", $boxes );

		return $result;
	}

	/**
	 * @return string
	 * @throws MWException
	 */
	public function getSuggestionBox() {
		global $wgTranslateTranslationServices;

		$handlers = array(
			'microsoft' => 'getMicrosoftSuggestion',
			'apertium' => 'getApertiumSuggestion',
			'yandex' => 'getYandexSuggestion',
		);

		$errors = '';
		$boxes = array();
		$TTMSSug = array();
		foreach ( $wgTranslateTranslationServices as $name => $config ) {
			$type = $config['type'];

			if ( !isset( $config['timeout'] ) ) {
				$config['timeout'] = 3;
			}

			$method = null;
			if ( isset( $handlers[$type] ) ) {
				$method = $handlers[$type];

				try {
					$boxes[] = $this->$method( $name, $config );
				} catch ( TranslationHelperException $e ) {
					$errors .= "<!-- Box $name not available: {$e->getMessage()} -->\n";
				}
				continue;
			}

			$server = TTMServer::factory( $config );
			if ( $server instanceof RemoteTTMServer ) {
				$method = 'getRemoteTTMServerBox';
			} elseif ( $server instanceof ReadableTTMServer ) {
				$method = 'getTTMServerBox';
			}

			if ( !$method ) {
				throw new MWException( __METHOD__ . ": Unsupported type {$config['type']}" );
			}

			try {
				$TTMSSug[$name] = array(
					'config' => $config,
					'suggestions' => $this->$method( $name, $config ),
				);
			} catch ( TranslationHelperException $e ) {
				$errors .= "<!-- Box $name not available: {$e->getMessage()} -->\n";
			}
		}

		if ( count( $TTMSSug ) ) {
			array_unshift( $boxes, $this->formatTTMServerSuggestions( $TTMSSug ) );
		}

		// Remove nulls and falses
		$boxes = array_filter( $boxes );

		// Enclose if there is more than one box
		if ( count( $boxes ) ) {
			$sep = Html::element( 'hr', array( 'class' => 'mw-translate-sep' ) );

			return $errors . TranslateUtils::fieldset(
				wfMessage( 'translate-edit-tmsugs' )->escaped(),
				implode( "$sep\n", $boxes ),
				array( 'class' => 'mw-translate-edit-tmsugs' )
			);
		} else {
			return $errors;
		}
	}

	protected static function makeGoogleQueryParams( $definition, $pair, $config ) {
		global $wgSitename, $wgVersion, $wgProxyKey;

		$app = "$wgSitename (MediaWiki $wgVersion; Translate " . TRANSLATE_VERSION . ")";
		$context = RequestContext::getMain();
		$options = array();
		$options['timeout'] = $config['timeout'];

		$options['postData'] = array(
			'q' => $definition,
			'v' => '1.0',
			'langpair' => $pair,
			// Unique but not identifiable
			'userip' => sha1( $wgProxyKey . $context->getUser()->getName() ),
			'x-application' => $app,
		);

		if ( $config['key'] ) {
			$options['postData']['key'] = $config['key'];
		}

		return $options;
	}

	protected function getMicrosoftSuggestion( $serviceName, $config ) {
		$this->mustHaveDefinition();
		self::checkTranslationServiceFailure( $serviceName );

		$code = $this->handle->getCode();
		$definition = trim( strval( $this->getDefinition() ) );
		$definition = self::wrapUntranslatable( $definition );

		$memckey = wfMemckey( 'translate-tmsug-badcodes-' . $serviceName );
		$unsupported = wfGetCache( CACHE_ANYTHING )->get( $memckey );

		if ( isset( $unsupported[$code] ) ) {
			throw new TranslationHelperException( 'Unsupported language' );
		}

		$options = array();
		$options['timeout'] = $config['timeout'];

		$params = array(
			'text' => $definition,
			'to' => $code,
		);

		if ( isset( $config['key'] ) ) {
			$params['appId'] = $config['key'];
		} else {
			throw new TranslationHelperException( 'API key is not set' );
		}

		$url = $config['url'] . '?' . wfArrayToCgi( $params );
		$url = wfExpandUrl( $url );

		$options['method'] = 'GET';

		$req = MWHttpRequest::factory( $url, $options );

		wfProfileIn( 'TranslateWebServiceRequest-' . $serviceName );
		$status = $req->execute();
		wfProfileOut( 'TranslateWebServiceRequest-' . $serviceName );

		if ( !$status->isOK() ) {
			$error = $req->getContent();
			if ( strpos( $error, 'must be a valid language' ) !== false ) {
				$unsupported[$code] = true;
				wfGetCache( CACHE_ANYTHING )->set( $memckey, $unsupported, 60 * 60 * 8 );
				throw new TranslationHelperException( 'Unsupported language code' );
			}

			if ( $error ) {
				error_log( __METHOD__ . ': Http::get failed:' . $error );
			} else {
				error_log( __METHOD__ . ': Unknown error, grr' );
			}
			// Most likely a timeout or other general error
			self::reportTranslationServiceFailure( $serviceName );
		}

		$ret = $req->getContent();
		$text = preg_replace( '~<string.*>(.*)</string>~', '\\1', $ret );
		$text = Sanitizer::decodeCharReferences( $text );
		$text = self::unwrapUntranslatable( $text );
		$text = $this->suggestionField( $text );

		return Html::rawElement( 'div', array(), self::legend( $serviceName ) . $text . self::clear() );
	}

	protected static function wrapUntranslatable( $text ) {
		$text = str_replace( "\n", "!N!", $text );
		$wrap = '<span class="notranslate">\0</span>';
		$pattern = '~%[^% ]+%|\$\d|{VAR:[^}]+}|{?{(PLURAL|GRAMMAR|GENDER):[^|]+\||%(\d\$)?[sd]~';
		$text = preg_replace( $pattern, $wrap, $text );

		return $text;
	}

	protected static function unwrapUntranslatable( $text ) {
		$text = str_replace( '!N!', "\n", $text );
		$text = preg_replace( '~<span class="notranslate">(.*?)</span>~', '\1', $text );

		return $text;
	}

	protected function getApertiumSuggestion( $serviceName, $config ) {
		self::checkTranslationServiceFailure( $serviceName );

		$page = $this->handle->getKey();
		$code = $this->handle->getCode();
		$ns = $this->handle->getTitle()->getNamespace();

		$memckey = wfMemckey( 'translate-tmsug-pairs-' . $serviceName );
		$pairs = wfGetCache( CACHE_ANYTHING )->get( $memckey );

		if ( !$pairs ) {

			$pairs = array();
			$json = Http::get( $config['pairs'], 5 );
			$response = FormatJson::decode( $json );

			if ( $json === false ) {
				self::reportTranslationServiceFailure( $serviceName );
			} elseif ( !is_object( $response ) ) {
				error_log( __METHOD__ . ': Unable to parse reply: ' . strval( $json ) );
				throw new TranslationHelperException( 'Malformed reply from remote server' );
			}

			foreach ( $response->responseData as $pair ) {
				$source = $pair->sourceLanguage;
				$target = $pair->targetLanguage;
				if ( !isset( $pairs[$target] ) ) {
					$pairs[$target] = array();
				}
				$pairs[$target][$source] = true;
			}

			wfGetCache( CACHE_ANYTHING )->set( $memckey, $pairs, 60 * 60 * 24 );
		}

		if ( isset( $config['codemap'][$code] ) ) {
			$code = $config['codemap'][$code];
		}

		$code = str_replace( '-', '_', wfBCP47( $code ) );

		if ( !isset( $pairs[$code] ) ) {
			throw new TranslationHelperException( 'Unsupported language' );
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

			wfProfileIn( 'TranslateWebServiceRequest-' . $serviceName );
			$json = Http::post( $config['url'], $options );
			wfProfileOut( 'TranslateWebServiceRequest-' . $serviceName );

			$response = FormatJson::decode( $json );
			if ( $json === false || !is_object( $response ) ) {
				self::reportTranslationServiceFailure( $serviceName );
			} elseif ( $response->responseStatus !== 200 ) {
				error_log( __METHOD__ .
						" (HTTP {$response->responseStatus}) with ($serviceName ($candidate|$code)): " .
						$response->responseDetails
				);
			} else {
				$sug = Sanitizer::decodeCharReferences( $response->responseData->translatedText );
				$sug = trim( $sug );
				$sug = $this->suggestionField( $sug );
				$suggestions[] = Html::rawElement( 'div',
					array( 'title' => $text ),
					self::legend( "$serviceName ($candidate)" ) . $sug . self::clear()
				);
			}
		}

		if ( !count( $suggestions ) ) {
			throw new TranslationHelperException( 'No suggestions' );
		}

		$divider = Html::element( 'div', array( 'style' => 'margin-bottom: 0.5ex' ) );

		return implode( "$divider\n", $suggestions );
	}

	protected function getYandexSuggestion( $serviceName, $config ) {
		self::checkTranslationServiceFailure( $serviceName );

		$page = $this->handle->getKey();
		$code = $this->handle->getCode();
		$ns = $this->handle->getTitle()->getNamespace();

		$memckey = wfMemckey( 'translate-tmsug-pairs-' . $serviceName );
		$pairs = wfGetCache( CACHE_ANYTHING )->get( $memckey );

		if ( !$pairs ) {
			$pairs = array();
			$json = Http::get( $config['pairs'], $config['timeout'] );
			$response = FormatJson::decode( $json );

			if ( $json === false ) {
				self::reportTranslationServiceFailure( $serviceName );
			} elseif ( !is_object( $response ) ) {
				error_log( __METHOD__ . ': Unable to parse reply: ' . strval( $json ) );
				throw new TranslationHelperException( 'Malformed reply from remote server' );
			}

			foreach ( $response->dirs as $pair ) {
				list( $source, $target ) = explode( '-', $pair );
				if ( !isset( $pairs[$target] ) ) {
					$pairs[$target] = array();
				}
				$pairs[$target][$source] = true;
			}

			$weights = array_flip( $config['langorder'] );
			$cmpLangs = function ( $lang1, $lang2 ) use ( $weights ) {
				$weight1 = isset( $weights[$lang1] ) ? $weights[$lang1] : PHP_INT_MAX;
				$weight2 = isset( $weights[$lang2] ) ? $weights[$lang2] : PHP_INT_MAX;

				if ( $weight1 === $weight2 ) {
					return 0;
				}

				return ( $weight1 < $weight2 ) ? -1 : 1;
			};

			foreach ( $pairs as &$langs ) {
				uksort( $langs, $cmpLangs );
			}

			wfGetCache( CACHE_ANYTHING )->set( $memckey, $pairs, 60 * 60 * 24 );
		}

		if ( !isset( $pairs[$code] ) ) {
			throw new TranslationHelperException( 'Unsupported language' );
		}

		$suggestions = array();

		foreach ( $pairs[$code] as $candidate => $unused ) {
			$text = TranslateUtils::getMessageContent( $page, $candidate, $ns );
			if ( $text === null || TranslateEditAddons::hasFuzzyString( $text ) ) {
				continue;
			}

			$title = Title::makeTitleSafe( $ns, "$page/$candidate" );
			if ( $title && TranslateEditAddons::isFuzzy( $title ) ) {
				continue;
			}

			$options = array(
				'timeout' => $config['timeout'],
				'postData' => array(
					'lang' => "$candidate-$code",
					'text' => $text,
				)
			);
			wfProfileIn( 'TranslateWebServiceRequest-' . $serviceName );
			$json = Http::post( $config['url'], $options );
			wfProfileOut( 'TranslateWebServiceRequest-' . $serviceName );
			$response = FormatJson::decode( $json );

			if ( $json === false || !is_object( $response ) ) {
				self::reportTranslationServiceFailure( $serviceName );
			} elseif ( $response->code !== 200 ) {
				error_log( __METHOD__ . " (HTTP {$response->code}) with ($serviceName ($candidate|$code))" );
			} else {
				$sug = Sanitizer::decodeCharReferences( $response->text[0] );
				$sug = $this->suggestionField( $sug );
				$suggestions[] = Html::rawElement( 'div',
					array( 'title' => $text ),
					self::legend( "$serviceName ($candidate)" ) . $sug . self::clear()
				);
				if ( count( $suggestions ) === $config['langlimit'] ) {
					break;
				}
			}
		}

		if ( $suggestions === array() ) {
			throw new TranslationHelperException( 'No suggestions' );
		}

		$divider = Html::element( 'div', array( 'style' => 'margin-bottom: 0.5ex' ) );

		return implode( "$divider\n", $suggestions );
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
				'lang' => $sl->getCode(),
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

		$formattedChecks = $tux
			? FormatJson::encode( array() )
			: Html::element( 'div', array( 'class' => 'mw-translate-messagechecks' ) );

		$page = $this->handle->getKey();
		$translation = $this->getTranslation();
		$code = $this->handle->getCode();
		$en = $this->getDefinition();

		if ( strval( $translation ) === '' ) {
			return $formattedChecks;
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

			$context = RequestContext::getMain();
			$label = TranslateUtils::getLanguageName( $fbcode, $context->getLanguage()->getCode() ) .
				$context->msg( 'word-separator' )->text() .
				$context->msg( 'parentheses', wfBCP47( $fbcode ) )->text();

			$target = Title::makeTitleSafe( $ns, "$page/$fbcode" );
			if ( $target ) {
				$label = self::ajaxEditLink( $target, htmlspecialchars( $label ) );
			}

			$dialogID = $this->dialogID();
			$id = Sanitizer::escapeId( "other-$fbcode-$dialogID" );

			$params = array( 'class' => 'mw-translate-edit-item' );

			$display = TranslateUtils::convertWhiteSpaceToHTML( $text );
			$display = Html::rawElement( 'div', array(
					'lang' => $fbcode,
					'dir' => Language::factory( $fbcode )->getDir() ),
				$display
			);

			$contents = self::legend( $label ) . "\n" . $this->adder( $id, $fbcode ) .
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

		$gettext = $this->formatGettextComments();
		if ( $info !== null && $gettext ) {
			$info .= Html::element( 'hr' );
		}
		$info .= $gettext;

		// The information is most likely in English
		$divAttribs = array( 'dir' => 'ltr', 'lang' => 'en', 'class' => 'mw-content-ltr' );

		if ( strval( $info ) === '' ) {
			$info = $context->msg( 'translate-edit-no-information' )->text();
			$class = 'mw-sp-translate-edit-noinfo';
			$lang = $context->getLanguage();
			// The message saying that there's no info, should be translated
			$divAttribs = array( 'dir' => $lang->getDir(), 'lang' => $lang->getCode() );
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

	protected function formatGettextComments() {
		if ( !$this->handle->isValid() ) {
			return '';
		}

		// We need to get the primary group to get the correct file
		// So $group can be different from $this->group
		$group = $this->handle->getGroup();
		if ( !$group instanceof FileBasedMessageGroup ) {
			return '';
		}

		$ffs = $group->getFFS();
		if ( $ffs instanceof GettextFFS ) {
			global $wgContLang;
			$mykey = $wgContLang->lcfirst( $this->handle->getKey() );
			$mykey = str_replace( ' ', '_', $mykey );
			$data = $ffs->read( $group->getSourceLanguage() );
			$help = $data['TEMPLATE'][$mykey]['comments'];
			// Do not display an empty comment. That's no help and takes up unnecessary space.
			$conf = $group->getConfiguration();
			if ( isset( $conf['BASIC']['codeBrowser'] ) ) {
				$out = '';
				$pattern = $conf['BASIC']['codeBrowser'];
				$pattern = str_replace( '%FILE%', '\1', $pattern );
				$pattern = str_replace( '%LINE%', '\2', $pattern );
				$pattern = "[$pattern \\1:\\2]";
				foreach ( $help as $type => $lines ) {
					if ( $type === ':' ) {
						$files = '';
						foreach ( $lines as $line ) {
							$files .= ' ' . preg_replace( '/([^ :]+):(\d+)/', $pattern, $line );
						}
						$out .= "<nowiki>#:</nowiki> $files<br />";
					} else {
						foreach ( $lines as $line ) {
							$out .= "<nowiki>#$type</nowiki> $line<br />";
						}
					}
				}

				return "$out";
			}
		}

		return '';
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

		$db = wfGetDB( DB_MASTER );
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
		$oldtext = $oldrev->getText();
		$newtext = Revision::newFromTitle( $definitionTitle, $latestRevision )->getText();

		if ( $oldtext === $newtext ) {
			return null;
		}

		$diff = new DifferenceEngine;
		if ( method_exists( 'DifferenceEngine', 'setTextLanguage' ) ) {
			$diff->setTextLanguage( $this->group->getSourceLanguage() );
		}
		$diff->setText( $oldtext, $newtext );
		$diff->setReducedLineNumbers();
		$diff->showDiffStyle();

		return $diff->getDiff(
			wfMessage( 'tpt-diff-old' )->escaped(),
			wfMessage( 'tpt-diff-new' )->escaped()
		);
	}

	protected function getLastDiff() {
		// Shortcuts
		$title = $this->handle->getTitle();
		$latestRevId = $title->getLatestRevID();
		$previousRevId = $title->getPreviousRevisionID( $latestRevId );

		$latestRev = Revision::newFromTitle( $title, $latestRevId );
		$previousRev = Revision::newFromTitle( $title, $previousRevId );

		$diffText = '';

		if ( $latestRev && $previousRev ) {
			$latest = $latestRev->getText();
			$previous = $previousRev->getText();
			if ( $previous !== $latest ) {
				$diff = new DifferenceEngine;
				if ( method_exists( 'DifferenceEngine', 'setTextLanguage' ) ) {
					$diff->setTextLanguage( $this->getTargetLanguage() );
				}
				$diff->setText( $previous, $latest );
				$diff->setReducedLineNumbers();
				$diff->showDiffStyle();
				$diffText = $diff->getDiff( false, false );
			}
		}

		if ( !$latestRev ) {
			return null;
		}

		$context = RequestContext::getMain();
		$user = $latestRev->getUserText( Revision::FOR_THIS_USER, $context->getUser() );
		$comment = $latestRev->getComment();

		if ( $diffText === '' ) {
			if ( strval( $comment ) !== '' ) {
				$text = $context->msg( 'translate-dynagroup-byc', $user, $comment )->escaped();
			} else {
				$text = $context->msg( 'translate-dynagroup-by', $user )->escaped();
			}
		} else {
			if ( strval( $comment ) !== '' ) {
				$text = $context->msg( 'translate-dynagroup-lastc', $user, $comment )->escaped();
			} else {
				$text = $context->msg( 'translate-dynagroup-last', $user )->escaped();
			}
		}

		return TranslateUtils::fieldset(
			$text,
			$diffText,
			array( 'class' => 'mw-sp-translate-latestchange' )
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
	 * @return null|string
	 */
	public function getLazySuggestionBox() {
		$this->mustBeKnownMessage();
		if ( !$this->handle->getCode() ) {
			return null;
		}

		$url = SpecialPage::getTitleFor( 'Translate', 'editpage' )->getLocalUrl( array(
			'suggestions' => 'only',
			'page' => $this->handle->getTitle()->getPrefixedDbKey(),
			'loadgroup' => $this->group->getId(),
		) );
		$url = Xml::encodeJsVar( $url );

		$id = Sanitizer::escapeId( 'tm-lazysug-' . $this->dialogID() );
		$target = self::jQueryPathId( $id );

		$script = Html::inlineScript( "jQuery($target).load($url)" );
		$spinner = Html::element( 'div', array( 'class' => 'mw-ajax-loader' ) );

		return Html::rawElement( 'div', array( 'id' => $id ), $script . $spinner );
	}

	/**
	 * @return string
	 */
	public function dialogID() {
		$hash = sha1( $this->handle->getTitle()->getPrefixedDbKey() );

		return substr( $hash, 0, 4 );
	}

	/**
	 * @param string $source jQuery selector for element containing the source
	 * @param string|Language $lang Language code or object
	 * @return string
	 */
	public function adder( $source, $lang ) {
		if ( !$this->editMode ) {
			return '';
		}
		$target = self::jQueryPathId( $this->getTextareaId() );
		$source = self::jQueryPathId( $source );
		$dir = wfGetLangObj( $lang )->getDir();
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
	 * @param $text string
	 * @return string
	 */
	public function suggestionField( $text ) {
		static $counter = 0;

		$code = $this->getTargetLanguage();

		$counter++;
		$dialogID = $this->dialogID();
		$id = Sanitizer::escapeId( "tmsug-$dialogID-$counter" );
		$contents = Html::rawElement( 'div', array( 'lang' => $code,
				'dir' => Language::factory( $code )->getDir() ),
			TranslateUtils::convertWhiteSpaceToHTML( $text ) );
		$contents .= $this->wrapInsert( $id, $text );

		return $this->adder( $id, $code ) . "\n" . $contents;
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

	/**
	 * How many failures during failure period need to happen to consider
	 * the service being temporarily off-line. */
	protected static $serviceFailureCount = 5;
	/**
	 * How long after the last detected failure we clear the status and
	 * try again.
	 */
	protected static $serviceFailurePeriod = 900;

	/**
	 * Checks whether the given service has exceeded failure count
	 * @param $service string
	 * @throws TranslationHelperException
	 */
	public static function checkTranslationServiceFailure( $service ) {
		$key = wfMemckey( "translate-service-$service" );
		$value = wfGetCache( CACHE_ANYTHING )->get( $key );
		if ( !is_string( $value ) ) {
			return;
		}
		list( $count, $failed ) = explode( '|', $value, 2 );

		if ( $failed + ( 2 * self::$serviceFailurePeriod ) < wfTimestamp() ) {
			if ( $count >= self::$serviceFailureCount ) {
				error_log( "Translation service $service (was) restored" );
			}
			wfGetCache( CACHE_ANYTHING )->delete( $key );

			return;
		} elseif ( $failed + self::$serviceFailurePeriod < wfTimestamp() ) {
			/* We are in suspicious mode and one failure is enough to update
			 * failed timestamp. If the service works however, let's use it.
			 * Previous failures are forgotten after another failure period
			 * has passed */
			return;
		}

		if ( $count >= self::$serviceFailureCount ) {
			throw new TranslationHelperException( "web service $service is temporarily disabled" );
		}
	}

	/**
	 * Increases the failure count for a given service
	 * @param $service
	 * @throws TranslationHelperException
	 */
	public static function reportTranslationServiceFailure( $service ) {
		$key = wfMemckey( "translate-service-$service" );
		$value = wfGetCache( CACHE_ANYTHING )->get( $key );
		if ( !is_string( $value ) ) {
			$count = 0;
		} else {
			list( $count, ) = explode( '|', $value, 2 );
		}

		$count += 1;
		$failed = wfTimestamp();
		wfGetCache( CACHE_ANYTHING )->set( $key, "$count|$failed", self::$serviceFailurePeriod * 5 );

		if ( $count == self::$serviceFailureCount ) {
			error_log( "Translation service $service suspended" );
		} elseif ( $count > self::$serviceFailureCount ) {
			error_log( "Translation service $service still suspended" );
		}

		throw new TranslationHelperException( "web service $service failed to provide valid response" );
	}

	public static function addModules( OutputPage $out ) {
		$modules = array( 'ext.translate.quickedit' );
		wfRunHooks( 'TranslateBeforeAddModules', array( &$modules ) );
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
	protected function mustBeTranslation() {
		if ( !$this->handle->getCode() ) {
			throw new TranslationHelperException( 'editing source language' );
		}
	}

	/// @since 2012-01-04
	protected function mustHaveDefinition() {
		if ( strval( $this->getDefinition() ) === '' ) {
			throw new TranslationHelperException( 'message does not have definition' );
		}
	}
}

/**
 * Translation helpers can throw this exception when they cannot do
 * anything useful with the current message. This helps in debugging
 * why some fields are not shown. See also helpers in TranslationHelpers:
 * - mustBeKnownMessage()
 * - mustBeTranslation()
 * - mustHaveDefinition()
 * @since 2012-01-04 (Renamed in 2012-07-24 to fix typo in name)
 */
class TranslationHelperException extends MWException {
}
