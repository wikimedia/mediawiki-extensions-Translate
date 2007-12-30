<?php

/**
 * This special page helps with the translations of MediaWiki features that are
 * not in the main messages array.
 */
class SpecialMagic extends SpecialPage {
	/** Message prefix for translations */
	const MSG = 'translate-magic-';

	const MODULE_SKIN      = 'skin';
	const MODULE_MAGIC     = 'words';
	const MODULE_SPECIAL   = 'special';
	const MODULE_NAMESPACE = 'namespace';

	const INDEX_OF_MODULE = 0;
	const INDEX_OF_LANGUAGE = 1;

	/** List of supported modules */
	private $aModules = array(
		self::MODULE_SPECIAL,
		self::MODULE_SKIN,
		self::MODULE_NAMESPACE,
		self::MODULE_MAGIC
	);

	/** Page options */
	private $options = array();
	private $defaults = array();
	private $nondefaults = array();

	public function __construct() {
		SpecialPage::SpecialPage( 'Magic' );
	}

	/**
	 * @see SpecialPage::getDescription
	 */
	function getDescription() {
		return wfMsg( self::MSG.'pagename' );
	}

	/**
	 * Returns xhtml output of the form
	 * GLOBALS: $wgLang, $wgTitle
	 */
	protected function getForm() {
		global $wgLang, $wgTitle, $wgScript;
		$line = wfMsgExt( self::MSG.'form', array( 'parse', 'replaceafter' ),
			TranslateUtils::languageSelector( $wgLang->getCode(), $this->options['language'] ),
			$this->moduleSelector( $this->options['module'] ),
			Xml::submitButton( wfMsg( self::MSG.'submit' ) )
		);

		$form = Xml::tags( 'form',
			array(
				'action' => $wgScript,
				'method' => 'get'
			),
			Xml::hidden( 'title', $wgTitle->getPrefixedText() ) .
			$line
		);
		return $form;
	}

	/**
	 * Helper function get module selector.
	 * Returns the xhtml-compatible select-element.
	 * @param $selectedId which value should be selected by default
	 * @return string
	 */
	protected function moduleSelector( $selectedId ) {
		$selector = new HTMLSelector( 'module', 'module', $selectedId );
		foreach( $this->aModules as $code ) {
			$selector->addOption( wfMsg( self::MSG . $code ), $code );
		}
		return $selector->getHTML();
	}

	/**
	 * Parser special page parameters from /-style input
	 * @param $params String of /-delimited params. First is module, second is language.
	 */
	protected function parseParams( $params, $options ) {
		$aParam = explode( '/', $params );
		if ( isset($aParam[self::INDEX_OF_MODULE]) ) {
			$options['module'] = $aParam[self::INDEX_OF_MODULE];
		}
		if ( isset($aParam[self::INDEX_OF_LANGUAGE]) ) {
			$options['language'] = $aParam[self::INDEX_OF_LANGUAGE];
		}
		return $options;
	}

	protected function setup( $parameters ) {
		global $wgUser, $wgRequest;

		$defaults = array(
		/* str  */ 'module'   => '',
		/* str  */ 'language' => $wgUser->getOption( 'language' ),
		/* bool */ 'export'   => false,
		/* bool */ 'savetodb' => false,
		);

		// Place where all non default variables will end
		$nondefaults = array();

		// Temporary store possible values parsed from parameters
		$options = $defaults;
		$options = $this->parseParams( $parameters, $options );
		foreach ( $options as $v => $t ) {
			if ( is_bool($t) ) {
				$r = $wgRequest->getBool( $v, $options[$v] );
			} elseif( is_int($t) ) {
				$r = $wgRequest->getInt( $v, $options[$v] );
			} elseif( is_string($t) ) {
				$r = $wgRequest->getText( $v, $options[$v] );
			}
			wfAppendToArrayIfNotDefault( $v, $r, $defaults, $nondefaults );
		}

		$this->defaults    = $defaults;
		$this->nondefaults = $nondefaults;
		$this->options     = $nondefaults + $defaults;
	}

	/**
	 * The special page running code
	 * GLOBALS: $wgWebRequest, $wgOut, $wgUser, $wgLang
	 */
	public function execute( $parameters ) {
		global $wgUser, $wgOut, $wgRequest, $wgLang;
		wfLoadExtensionMessages( 'Translate' );

		$this->setup( $parameters );
		$this->setHeaders();

		$wgOut->addHTML( $this->getForm() );
		$wgOut->addWikitext( wfMsg(self::MSG.'help') );

		if (!$this->options['module'] ) { return; }
		$o = null;
		switch ( $this->options['module'] ) {
			case 'alias':
			case self::MODULE_SPECIAL:
				$o = new SpecialPageAliasesCM( $this->options['language'] );
				break;
			case self::MODULE_MAGIC:
				$o = new MagicWordsCM( $this->options['language'] );
				break;
			case self::MODULE_SKIN:
				$o = new SkinNamesCM( $this->options['language'] );
				break;
			case self::MODULE_NAMESPACE:
				$o = new NamespaceCM( $this->options['language'] );
				break;

			default:
				return;
		}

		if ( $wgRequest->wasPosted() && $this->options['savetodb'] ) {
			if ( !$wgUser->isAllowed( 'translate' ) ) {
				$wgOut->permissionRequired( 'translate' );
				return;
			}

			$o->save( $wgRequest );
		}

		if ( $o instanceof ComplexMessages ) {
			if ( $this->options['export'] ) {
				$result = Xml::element( 'textarea', array( 'rows' => '30' ) , $o->export() );
			} else {
				$result = $o->output();
			}
		}

		$wgOut->addHTML( $result );
	}

}


abstract class ComplexMessages {
	const MSG = 'translate-magic-cm-';

	const LANG_MASTER   = 'en';
	const LANG_TARGET   = 'xx';
	const LANG_FALLBACK = 'fb';
	const LANG_CURRENT  = 'tb';

	protected $language = null;
	protected $id       = '__BUG__';
	protected $variable = '__BUG__';
	protected $aContent = null;
	protected $elementsInArray = true;
	protected $exportPad = 10;
	protected $databaseMsg = '__BUG__';
	protected $stripUnderscores = false;

	protected $tableAttributes = array(
		'class' => 'wikitable',
		'border' => '2',
		'cellpadding' => '4',
		'cellspacing' => '0',
		'style' => 'background-color: #F9F9F9; border: 1px #AAAAAA solid; border-collapse: collapse;',
	);

	public function __construct( $language ) {
		$this->language = $language;
	}

	public function getTitle() {
		wfMsg( self::MSG . $this->id );
	}

	#
	# Data retrieval
	#

	public function getArray() {
		if ( $this->aContent !== null ) return $this->aContent;

		$fallback = Language::getFallbackFor( $this->language );
		if ( $fallback === 'en' ) $fallback = false;

		$array = array();
		$array[self::LANG_MASTER] = self::readVariable( 'en', $this->variable );
		$array[self::LANG_TARGET] = self::readVariable( $this->language, $this->variable );
		$array[self::LANG_CURRENT] = $this->array_merge( $array[self::LANG_TARGET], $this->getSavedData() );
		if ( $fallback )
			$array[self::LANG_FALLBACK] = self::readVariable( $fallback, $this->variable );

		$this->aContent = $array;
		return $this->aContent;
	}

	protected function array_merge( $a1, $a2 ) {
		if ( $this->elementsInArray ) {
			return array_merge( $a1, $a2 );
		} else {
			foreach ( $a1 as $index => $value ) {
				if ( !isset($a2[$index][0]) || !$a2[$index][0] ) {
					$a2[$index] = $a1[$index];
				}
			}
			return $a2;
		}
	}

	/**
	 * Gets saved data from Mediawiki namespace
	 * @return Array
	 */
	protected function getSavedData() {
		$data = TranslateUtils::getMessageContent( $this->databaseMsg, $this->language );

		if ( !$data ) {
			return array();
		}

		$lines = explode( "\n", $data );
		$array = array();
		foreach ( $lines as $line ) {
			if ( ltrim( $line[0] ) === '#' || ltrim( $line[0] ) === '<') { continue; }

			$elements = explode( '=', $line, 2 );
			if ( count( $elements ) !== 2 ) { continue; }
			if ( trim( $elements[1] ) === '' ) { continue; }

			$array[(string)$elements[0]] = explode( ",", $elements[1] );
		}

		return $array;
	}

	/**
	 * Return an array of keys that can be used to iterate over all keys
	 * @return Array of keys for aContent
	 */
	protected function getIterator() {
		$array = $this->getArray();
		return array_keys($array[self::LANG_MASTER]);
	}

	protected function val( $type, $key ) {
		$array = $this->getArray();
		$subarray = @$array[$type][$key];
		if ( !$subarray || count( $subarray ) < 1 ) return array();
		return $subarray;
	}

	/**
	 * Reads variable from given language file
	 * @param $__code Language code
	 * @param $__variable Name of the variable that is read
	 * @return null or contents of the variable
	 */
	protected static function readVariable( $__code, $__variable ) {
		$$__variable = array(); # Initialize
		$__file = Language::getMessagesFileName($__code);
		if ( file_exists($__file) ) require( $__file ); # Include
		return $$__variable;
	}

	#
	# /Data retrieval
	#

	#
	# Output
	#

	/**
	 * GLOBALS: $wgRequest
	 */
	public function output() {
		global $wgRequest;

		$array = $this->getArray();
		$fb = isset($array[self::LANG_FALLBACK]);

		$table['start'] = Xml::openElement( 'table', $this->tableAttributes );
		$table['heading'] = Xml::element( 'th', array('colspan' => '4' ), $this->getTitle() );
		$table['subheading'][] = Xml::element( 'th', null, wfMsg(self::MSG.'original') );
		if ( $fb ) $table['subheading'][] = Xml::element( 'th', null, wfMsg(self::MSG.'fallback') );
		$table['subheading'][] = Xml::element( 'th', null, wfMsg(self::MSG.'current') );
		$table['subheading'][] = Xml::element( 'th', null, wfMsg(self::MSG.'to-be') );
		$table['headings'] =
			Xml::openElement( 'tr' ) .
			$table['heading'] .
			Xml::closeElement( 'tr' ) .
			Xml::openElement( 'tr' ) .
			implode( "\n", $table['subheading'] ) .
			Xml::closeElement( 'tr' );

		$aColumns = array();
		$aColumns[] = self::LANG_MASTER;
		if ( $fb ) $aColumns[] = self::LANG_FALLBACK;
		$aColumns[] = self::LANG_TARGET;

		foreach ( $this->getIterator() as $key ) {
			$rowContents = '';
			foreach ( $aColumns as $column ) {
				$rowContents .= Xml::element( 'td', null,
					$this->formatElement( $this->val($column, $key) ) );
			}

			$rowContents .= Xml::tags( 'td', null, $this->editElement( $key,
					$this->formatElement( $this->val(self::LANG_CURRENT, $key) ) ) );

			$table['row'][] = Xml::tags( 'tr', null, $rowContents );
		}

		$table['row'][] =
			Xml::tags( 'tr', null,
				Xml::tags( 'td', array( 'colspan' => $fb ? 4 : 3 ), $this->getButtons() )
			);

		$table['rows'] = implode( "\n", $table['row'] );
		$table['end'] = Xml::closeElement( 'table' );

		$finalTable = $table['start'] . $table['headings'] . $table['rows'] . $table['end'];
		return Xml::tags( 'form',
			array( 'method' => 'post', 'action' => $wgRequest->getRequestURL() ),
			$finalTable );
	}

	public function getButtons() {
		return Xml::submitButton( wfMsg(self::MSG.'save'), array( 'name' => 'savetodb' ) ) . Xml::submitButton(  wfMsg(self::MSG.'export'), array( 'name' => 'export') );
	}

	public function formatElement( $element ) {
		if (!count( $element ) ) return '';
		if ( is_array($element) ) $element = implode( ', ', $element );
		if ( $this->stripUnderscores ) {
			$element = str_replace('_', ' ', $element);
		}
		return $element;
	}

	function getKeyForEdit( $key ) {
		return Sanitizer::escapeId( 'sp-translate-magic-cm-' . $this->id . $key );
	}

	public function editElement( $key, $contents ) {
		return Xml::input( $this->getKeyForEdit( $key ) , 40, $contents );
	}

	#
	# /Output
	#

	#
	# Save to database
	#

	function getKeyForSave() {
		return $this->databaseMsg . '/' . $this->language;
	}

	function formatForSave( $request ) {
		$array = $this->getArray();

		$text = '';
		foreach ( $this->getIterator() as $key ) {
			$text .= $key . '=' . $request->getText( $this->getKeyForEdit( $key ) ) . "\n" ;
		}

		return trim($text);
	}

	public function save( $request ) {
		$title = Title::newFromText( 'MediaWiki:' . $this->getKeyForSave() );
		$article = new Article( $title );

		$data = "# DO NOT EDIT THIS PAGE DIRECTLY! Use [[Special:Magic]].\n<pre>\n" . $this->formatForSave( $request ) . "\n</pre>";

		$success = $article->doEdit( $data, wfMsgForContent(self::MSG.'updatedusing'), 0 );

		if ( !$success ) {
			throw new MWException( wfMsgHtml(self::MSG.'savefailed') );
		}

		/* Reset outdated array */
		$this->aContent = null;

	}

	#
	# !Save to database
	#

	#
	# Export
	#

	public function export() {
		$array = $this->getArray();
		$text[] = "\${$this->variable} = array(";
		foreach ( $this->getIterator() as $key ) {
			$temp = "\t'$key'";
			while ( strlen( $temp ) <= $this->exportPad ) { $temp .= ' '; }

			if ( count($this->val(self::LANG_CURRENT, $key)) ) {
				$normalized = array_map( array( $this, 'normalize' ), $this->val(self::LANG_CURRENT, $key ) );

				if ( $this->elementsInArray ) {
					$temp .= "=> array( ". implode( ', ', $normalized )." ),";
				} else {
					if ( count( $normalized ) > 1 ) {
						throw new MWException( 'Too many elements for ' . $this->id . '. Key: ' . $key );
					} else {
						$temp .= "=> $normalized[0],";
					}
				}
				$text[] = $temp;
			}
		}

		$text[] = ');';

		return implode("\n", $text);
	}

	/**
	 * Returns string with quotes that should be valid php
	 */
	protected function normalize( $data ) {
		# Escape quotes
		$data = preg_replace( "/(?<!\\\\)'/", "\'", trim($data));
		if ( $this->stripUnderscores ) {
			$data = str_replace(' ', '_', $data);
		}
		return "'$data'";
	}

	#
	# /Export
	#

}

class SpecialPageAliasesCM extends ComplexMessages {
	protected $id = SpecialMagic::MODULE_SPECIAL;
	protected $variable = 'specialPageAliases';
	protected $exportPad = 28;
	protected $databaseMsg = 'sp-translate-data-SpecialPageAliases';
	protected $stripUnderscores = true;
}

class SkinNamesCM extends ComplexMessages {
	protected $id = SpecialMagic::MODULE_SKIN;
	protected $variable = 'skinNames';
	protected $elementsInArray = false;
	protected $exportPad = 14;
	protected $databaseMsg = 'sp-translate-data-SkinNames';
}

class MagicWordsCM extends ComplexMessages {
	protected $id = SpecialMagic::MODULE_MAGIC;
	protected $variable = 'magicWords';
	protected $exportPad = 22;
	protected $databaseMsg = 'sp-translate-data-MagicWords';
}

class NamespaceCM extends ComplexMessages {
	protected $id = SpecialMagic::MODULE_NAMESPACE;
	protected $variable = 'namespaceNames';
	protected $elementsInArray = false;
	protected $databaseMsg = 'sp-translate-data-Namespaces';
	protected $stripUnderscores = true;

	private static $constans = array(
		-2 => 'NS_MEDIA',
		-1 => 'NS_SPECIAL',
		 0 => 'NS_MAIN',
		 1 => 'NS_TALK',
		 2 => 'NS_USER',
		 3 => 'NS_USER_TALK',
		 4 => 'NS_PROJECT',
		 5 => 'NS_PROJECT_TALK',
		 6 => 'NS_IMAGE',
		 7 => 'NS_IMAGE_TALK',
		 8 => 'NS_MEDIAWIKI',
		 9 => 'NS_MEDIAWIKI_TALK',
		10 => 'NS_TEMPLATE',
		11 => 'NS_TEMPLATE_TALK',
		12 => 'NS_HELP',
		13 => 'NS_HELP_TALK',
		14 => 'NS_CATEGORY',
		15 => 'NS_CATEGORY_TALK',
	);

	private static $pad = 18;

	/**
	 * Re-implemented
	 */
	public function export() {
		$array = $this->getArray();
		$output = array();
		foreach (self::$constans as $index => $constant) {
			if ( $index === NS_PROJECT ) {
				$output[] = "\t# NS_PROJECT set by \\\$wgMetaNamespace";
				continue;
			}

			$value = false;
			// Export main always (because it cannot be translated)
			if ( $index === NS_MAIN ) $value = '';

			if ( isset($array[self::LANG_CURRENT][$index][0]) ) {
				$value = $array[self::LANG_CURRENT][$index][0];
			}

			if ( $value === false ) continue;

			$nValue = $this->normalize( $value );
			$output[] = "\t" . str_pad( $constant, self::$pad ) . "=> $nValue,";
		}

		return "\$namespaceNames = array(\n" . implode( "\n" , $output ) . "\n);\n";
	}

}

?>
