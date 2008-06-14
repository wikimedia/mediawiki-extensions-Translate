<?php

abstract class MessageGroup {
	/**
	 * Human-readable name of this group
	 */
	protected $label  = 'none';
	public function getLabel() { return $this->label; }
	public function setLabel( $value ) { $this->label = $value; }

	/**
	 * Group-wide unique id of this group. Used also for sorting.
	 */
	protected $id     = 'none';
	public function getId() { return $this->id; }
	public function setId( $value ) { $this->id = $value; }

	/**
	 * List of messages that are hidden by default, but can still be translated if
	 * needed.
	 */
	protected $optional = array();
	public function getOptional() { return $this->optional; }
	public function setOptional( $value ) { $this->optional = $value; }

	/**
	 * List of messages that are always hidden and cannot be translated.
	 */
	protected $ignored = array();
	public function getIgnored() { return $this->ignored; }
	public function setIgnored( $value ) { $this->ignored = $value; }

	/**
	 * Returns a list of optional and ignored messages in 2-d array.
	 */
	public function getBools() {
		return array(
			'optional' => $this->optional,
			'ignored' => $this->ignored,
		);
	}

	/**
	 * Holds descripton of this group. Description is a wiki text snippet that
	 * gives information about this group to translators.
	 */
	protected $description = null;
	public function getDescription() { return $this->description; }
	public function setDescription( $value ) { $this->description = $value; }

	/**
	 * Meta groups consist of multiple groups or parts of other groups. This info
	 * is used on many places, like when creating message index.
	 */
	protected $meta   = false;
	public function isMeta() { return $this->meta; }
	public function setMeta( $value ) { $this->meta = $value; }

	/**
	 * To avoid key conflicts between groups or separated changed messages between
	 * brances one can set a message key mangler.
	 */
	protected $mangler = null;
	public function getMangler() { return $this->mangler; }
	public function setMangler( $value ) { $this->mangler = $value; }

	public $namespaces = array( NS_MEDIAWIKI, NS_MEDIAWIKI_TALK );

	public function getReader( $code ) {
		return null;
	}

	public function getWriter() {
		return new SimpleFormatWriter( $this );
	}

	public function load( $code ) {
		$reader = $this->getReader( $code );
		if ( $reader ) {
			return $reader->parseMessages( $this->mangler );
		}
	}

	/**
	 * This function returns array of type key => definition of all messages
	 * this message group handles.
	 *
	 * @return Array of messages definitions indexed by key.
	 */
	public function getDefinitions() {
		$defs = $this->load('en');
		if ( !is_array($defs) ) {
			throw new MWException( "Unable to load definitions for " . $this->getLabel() );
		}
		return $defs;
	}

	/**
	 * Returns of stored translation of message specified by the $key in language
	 * code $code.
	 *
	 * @param $key Key of the message.
	 * @param $code Language code.
	 * @return Stored translation or null.
	 */
	public function getMessage( $key, $code ) {
		if( !isset( $this->messages[$code] ) ) {
			$this->messages[$code] = $this->load( $code );
		}
		return isset( $this->messages[$code][$key] ) ? $this->messages[$code][$key] : null;
	}
	/**
	 * All the messages for this group, by language code.
	 */
	private $messages = array();

	/**
	 * In this function message group should add translations from the stored file
	 * for language code $code and it's fallback language, if used.
	 *
	 * @param $messages MessageCollection
	 */
	function fill( MessageCollection $messages ) {
		$cache = $this->load( $messages->code );
		foreach ( $messages->keys() as $key ) {
			if ( isset($cache[$key]) ) {
				$messages[$key]->infile = $cache[$key];
			}
		}
	}

	/**
	 * Returns path to the file where translation of language code $code are.
	 *
	 * @return Path to the file or false if not applicable.
	 */
	public function getMessageFile( $code ) { return false; }


	public function initCollection( $code ) {
		$collection = new MessageCollection( $code );
		$definitions = $this->getDefinitions();
		foreach ( $definitions as $key => $definition ) {
			$collection->add( new TMessage( $key, $definition ) );
		}

		$bools = $this->getBools();
		foreach ( $bools['optional'] as $key ) {
			if ( isset($collection[$key]) ) {
				$collection[$key]->optional = true;
			}
		}

		foreach ( $bools['ignored'] as $key ) {
			if ( isset($collection[$key]) ) {
				$collection[$key]->ignored = true;
			}
		}

		return $collection;
	}

	public function fillCollection( MessageCollection $collection ) {
		TranslateUtils::fillExistence( $collection, $this->namespaces );
		TranslateUtils::fillContents( $collection, $this->namespaces );
		$this->fill( $collection );
	}

	public function __construct() {
		$this->mangler = StringMatcher::emptyMatcher();
	}

	public static function factory( $label, $id ) {
		return null;
	}
}

class CoreMessageGroup extends MessageGroup {
	protected $label = 'MediaWiki messages';
	protected $id    = 'core';

	public function __construct() {
		parent::__construct();
		global $IP;
		$this->prefix = $IP . '/languages/messages';
		$this->metaDataPrefix = $IP . '/maintenance/language';
	}

	protected $prefix = '';
	public function getPrefix() { return $this->prefix; }
	public function setPrefix( $value ) { $this->prefix = $value; }

	protected $metaDataPrefix = '';
	public function getMetaDataPrefix() { return $this->metaDataPrefix; }
	public function setMetaDataPrefix( $value ) { $this->metaDataPrefix = $value; }

	public static function factory( $label, $id ) {
		$group = new CoreMessageGroup;
		$group->setLabel( $label );
		$group->setId( $id );
		return $group;
	}

	public function getMessageFile( $code ) {
		$code = ucfirst( str_replace( '-', '_', $code ) );
		return "Messages$code.php";
	}

	protected function getFileLocation( $code ) {
		return $this->prefix . '/' . $this->getMessageFile( $code );
	}

	public function getReader( $code ) {
		return new WikiFormatReader( $this->getFileLocation( $code ) );
	}

	public function getWriter() {
		return new WikiFormatWriter( $this );
	}

	public function getBools() {
		require( $this->getMetaDataPrefix() . '/messageTypes.inc' );
		return array(
			'optional' => $this->mangler->mangle( $wgOptionalMessages ),
			'ignored'  => $this->mangler->mangle( $wgIgnoredMessages ),
		);
	}

	public function load( $code ) {
		$file = $this->getFileLocation( $code );
		return $this->mangler->mangle(
			ResourceLoader::loadVariableFromPHPFile( $file, 'messages' )
		);
	}
}

class ExtensionMessageGroup extends MessageGroup {
	/**
	 * Name of the array where all messages are stored, if applicable.
	 */
	protected $arrName      = 'messages';
	public function getVariableName() { return $this->arrName; }
	public function setVariableName( $value ) { $this->arrName = $value; }

	/**
	 * Path to the file where array or function is defined, relative to extensions
	 * root directory defined by $wgTranslateExtensionDirectory.
	 */
	protected $messageFile  = null;
	public function getMessageFile( $code ) { return $this->messageFile; }
	public function setMessageFile( $value ) { $this->messageFile = $value; }

	public static function factory( $label, $id ) {
		$group = new ExtensionMessageGroup;
		$group->setLabel( $label );
		$group->setId( $id );
		return $group;
	}

	/**
	 * This function loads messages for given language for further use.
	 *
	 * @param $code Language code
	 * @throws MWException If loading fails.
	 */
	public function load( $code ) {
		$reader = $this->getReader( $code );
		$cache = $reader->parseMessages( $this->mangler );
		if ( $cache === null ) {
			throw new MWException( "Unable to load messages for $code in {$this->label}" );
		}
		if ( isset($cache[$code]) ) {
			return $cache[$code];
		} else {
			return null;
		}
	}

	protected function getPath( $code ) {
		global $wgTranslateExtensionDirectory;
		if ( $this->getMessageFile( $code ) ) {
			$fullPath = $wgTranslateExtensionDirectory . $this->getMessageFile( $code );
		} else {
			throw new MWException( 'Message file not defined' );
		}
		return $fullPath;
	}

	public function getReader( $code ) {
		$reader = new WikiExtensionFormatReader( $this->getPath( $code ) );
		$reader->variableName = $this->getVariableName();
		return $reader;
	}

	public function getWriter() {
		$writer = new WikiExtensionFormatWriter( $this );
		$writer->variableName = $this->getVariableName();
		return $writer;
	}
}

class CoreMostUsedMessageGroup extends CoreMessageGroup {
	protected $label = 'MediaWiki messages (most used)';
	protected $id    = 'core-mostused';
	protected $meta  = true;

	public function export( MessageCollection $messages ) { return 'Not supported'; }
	public function exportToFile( MessageCollection $messages, $authors ) { return 'Not supported'; }

	function getDefinitions() {
		$data = file_get_contents( dirname(__FILE__) . '/wikimedia-mostused.txt' );
		$messages = explode( "\n", $data );
		$contents = Language::getMessagesFor( 'en' );
		$definitions = array();
		foreach ( $messages as $key ) {
			if ( isset($contents[$key]) ) {
				$definitions[$key] = $contents[$key];
			}
		}
		return $definitions;
	}
}

class AllMediawikiExtensionsGroup extends ExtensionMessageGroup {
	protected $label = 'All extensions';
	protected $id    = 'ext-0-all';
	protected $meta  = true;

	protected $classes = null;

	// Don't add the (mw ext) thingie
	public function getLabel() { return $this->label; }

	protected function init() {
		if ( $this->classes === null ) {
			$this->classes = MessageGroups::singleton()->getGroups();
			foreach ( $this->classes as $index => $class ) {
				if ( (strpos( $class->getId(), 'ext-' ) !== 0) || $class->isMeta() ) {
					unset( $this->classes[$index] );
				}
			}
		}
	}

	public function load( $code ) {
		return null; // no-op
	}

	public function getMessage( $key, $code ) {
		$msg = null;
		foreach ( $this->classes as $class ) {
			$msg = $class->getMessage( $key, $code );
			if ( $msg !== null ) return $msg;
		}
		return null;
	}

	function getDefinitions() {
		$this->init();
		$array = array();
		foreach ( $this->classes as $class ) {
			$array = array_merge( $array, $class->getDefinitions() );
		}
		return $array;
	}

	function fill( MessageCollection $messages ) {
		$this->init();
		foreach ( $this->classes as $class ) {
			$class->fill( $messages );
		}
	}

	function getBools() {
		$this->init();
		$bools = array();
		foreach ( $this->classes as $class ) {
			$newbools = ( $class->getBools() );
			if ( count($newbools['optional']) || count($newbools['ignored']) ) {
				$bools = array_merge_recursive( $bools, $class->getBools() );
			}
		}
		return $bools;
	}
}

class AllWikimediaExtensionsGroup extends AllMediawikiExtensionsGroup {
	protected $label = 'Extensions used by Wikimedia';
	protected $id    = 'ext-0-wikimedia';
	protected $meta  = true;

	protected $classes = null;

	protected $wmfextensions = array(
		'ext-inputbox', // used on all wikis by all users
		'ext-cite',
		'ext-citespecial',
		'ext-newuserlog',
		'ext-confirmedit',
		'ext-confirmeditfancycaptcha',
		'ext-categorytree',
		'ext-dismissablesitenotice',
		'ext-expandtemplates',
		'ext-parserfunctions',
		'ext-crossnamespacelinks',
		'ext-ogghandler',
		'ext-imagemap',
		'ext-labeledsectiontransclusion',
		'ext-mwsearch',
		'ext-linksearch',
		'ext-sitematrix',
		'ext-gadgets',
		'ext-fixedimage',
		'ext-centralauth',
		'ext-syntaxhighlightgeshi', // limited UI use (Special:Version and errors in usage mostly)
		'ext-timeline',
		'ext-wikihiero',
		'ext-oai',
		'ext-poem',
		'ext-fr-depreciationoversight', // used on some wikis by all users
		'ext-fr-flaggedrevs',
		'ext-fr-flaggedrevsaliases',
		'ext-fr-oldreviewedpages',
		'ext-fr-qualityoversight',
		'ext-fr-reviewedpages',
		'ext-fr-stabilization',
		'ext-fr-stablepages',
		'ext-fr-stableversions',
		'ext-fr-unreviewedpages',
		'ext-doublewiki',
		'ext-intersection',
		'ext-proofreadpage',
		'ext-quiz',
		'ext-scanset',
		'ext-skinperpage',
		'ext-antispoof', // anti spam and such (usually all wikis)
		'ext-spamblacklist',
		'ext-simpleantispam',
		'ext-titleblacklist',
		'ext-titlekey',
		'ext-torblock',
		'ext-usernameblacklist',
		'ext-deletedcontribs', // sysop or higher only
		'ext-checkuser',
		'ext-nuke',
		'ext-oversight',
		'ext-renameuser',
		'ext-assertedit', // bots
		'ext-centralnotice', // used rarely
		'ext-parserdifftest', // used rarely (still needed?)
		'ext-boardvote', // used rarely
	);

	protected function init() {
		if ( $this->classes === null ) {
			$this->classes = array();
			$classes = MessageGroups::singleton()->getGroups();
			foreach ( $this->wmfextensions as $key ) {
				$this->classes[$key] = $classes[$key];
			}
		}
	}

	public function wmfextensions() {
		return $this->wmfextensions;
	}
}

class AllFlaggedRevsExtensionsGroup extends AllMediawikiExtensionsGroup {
	protected $label = 'All FlaggedRevs messages';
	protected $id    = 'ext-0-flaggedrevs';
	protected $meta  = true;

	protected $classes = null;

	protected $flaggedrevsextensions = array(
		'ext-fr-flaggedrevs',
		'ext-fr-depreciationoversight',
		'ext-fr-flaggedrevsaliases',
		'ext-fr-oldreviewedpages',
		'ext-fr-qualityoversight',
		'ext-fr-reviewedpages',
		'ext-fr-stabilization',
		'ext-fr-stablepages',
		'ext-fr-stableversions',
		'ext-fr-unreviewedpages',
	);

	protected function init() {
		if ( $this->classes === null ) {
			$this->classes = array();
			$classes = MessageGroups::singleton()->getGroups();
			foreach ( $this->flaggedrevsextensions as $key ) {
				$this->classes[$key] = $classes[$key];
			}
		}
	}
}

class Word2MediaWikiPlusMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Word2MediaWiki Plus';
	protected $id    =  'out-word2mediawikiplus';
	protected $messageFile = 'Translate/external/Word2MediaWikiPlus/Word2MediaWikiPlus.i18n.php';
}

class FreeColMessageGroup extends MessageGroup {
	protected $label = 'FreeCol (open source game)';
	protected $id    = 'out-freecol';
	protected $prefix= 'freecol-';

	protected $description = 'Before starting translating FreeCol to your language, please read [[Translating:FreeCol]] and ask ok from the FreeCol localisation coordinator. Freecol uses GPL-license.';

	private   $fileDir  = '__BUG__';

	protected $codeMap = array(
		'cs' => 'cs_CZ',
		'es' => 'es_ES',
		'it' => 'it_IT',
		'no' => 'nb_NO',
		'pl' => 'pl_PL',
		'sv' => 'sv_SE',
		'nl-be' => 'nl_BE',
	);

	protected $optional = array(
		'freecol-shipName.3.0', 'freecol-newColonyName.0.0', 'freecol-newColonyName.0.1',
		'freecol-newColonyName.0.2', 'freecol-newColonyName.0.3', 'freecol-newColonyName.0.4',
		'freecol-newColonyName.0.5', 'freecol-newColonyName.0.6', 'freecol-newColonyName.0.7',
		'freecol-newColonyName.0.8', 'freecol-newColonyName.0.9', 'freecol-newColonyName.0.10',
		'freecol-newColonyName.0.11', 'freecol-newColonyName.0.12', 'freecol-newColonyName.0.13',
		'freecol-newColonyName.0.14', 'freecol-newColonyName.0.15', 'freecol-newColonyName.0.16',
		'freecol-newColonyName.0.17', 'freecol-newColonyName.0.18', 'freecol-newColonyName.0.19',
		'freecol-newColonyName.0.20', 'freecol-newColonyName.0.21', 'freecol-newColonyName.0.22',
		'freecol-newColonyName.0.23', 'freecol-newColonyName.0.24', 'freecol-newColonyName.0.25',
		'freecol-newColonyName.0.26', 'freecol-newColonyName.0.27', 'freecol-newColonyName.0.28',
		'freecol-newColonyName.0.29', 'freecol-newColonyName.0.30', 'freecol-newColonyName.0.31',
		'freecol-newColonyName.1.0', 'freecol-newColonyName.1.1', 'freecol-newColonyName.1.2',
		'freecol-newColonyName.1.3', 'freecol-newColonyName.1.4', 'freecol-newColonyName.1.5',
		'freecol-newColonyName.1.6', 'freecol-newColonyName.1.7', 'freecol-newColonyName.1.8',
		'freecol-newColonyName.1.9', 'freecol-newColonyName.1.10', 'freecol-newColonyName.1.11',
		'freecol-newColonyName.1.12', 'freecol-newColonyName.1.13', 'freecol-newColonyName.1.14',
		'freecol-newColonyName.1.15', 'freecol-newColonyName.1.16', 'freecol-newColonyName.1.17',
		'freecol-newColonyName.1.18', 'freecol-newColonyName.1.19', 'freecol-newColonyName.1.20',
		'freecol-newColonyName.1.21', 'freecol-newColonyName.1.22', 'freecol-newColonyName.1.23',
		'freecol-newColonyName.1.24', 'freecol-newColonyName.1.25', 'freecol-newColonyName.1.26',
		'freecol-newColonyName.1.27', 'freecol-newColonyName.1.28', 'freecol-newColonyName.1.29',
		'freecol-newColonyName.1.30', 'freecol-newColonyName.1.31', 'freecol-newColonyName.1.32',
		'freecol-newColonyName.1.33', 'freecol-newColonyName.1.34', 'freecol-newColonyName.1.35',
		'freecol-newColonyName.2.0', 'freecol-newColonyName.2.1', 'freecol-newColonyName.2.2',
		'freecol-newColonyName.2.3', 'freecol-newColonyName.2.4', 'freecol-newColonyName.2.5',
		'freecol-newColonyName.2.6', 'freecol-newColonyName.2.7', 'freecol-newColonyName.2.8',
		'freecol-newColonyName.2.9', 'freecol-newColonyName.2.10', 'freecol-newColonyName.2.11',
		'freecol-newColonyName.2.12', 'freecol-newColonyName.2.13', 'freecol-newColonyName.2.14',
		'freecol-newColonyName.2.15', 'freecol-newColonyName.2.16', 'freecol-newColonyName.2.17',
		'freecol-newColonyName.2.18', 'freecol-newColonyName.2.19', 'freecol-newColonyName.2.20',
		'freecol-newColonyName.2.21', 'freecol-newColonyName.2.22', 'freecol-newColonyName.2.23',
		'freecol-newColonyName.2.24', 'freecol-newColonyName.2.25', 'freecol-newColonyName.2.26',
		'freecol-newColonyName.2.27', 'freecol-newColonyName.2.28', 'freecol-newColonyName.2.29',
		'freecol-newColonyName.2.30', 'freecol-newColonyName.2.31', 'freecol-newColonyName.2.32',
		'freecol-newColonyName.2.33', 'freecol-newColonyName.2.34', 'freecol-newColonyName.2.35',
		'freecol-newColonyName.2.36', 'freecol-newColonyName.2.37', 'freecol-newColonyName.2.38',
		'freecol-newColonyName.2.39', 'freecol-newColonyName.2.40', 'freecol-newColonyName.2.41',
		'freecol-newColonyName.2.42', 'freecol-newColonyName.2.43', 'freecol-newColonyName.2.44',
		'freecol-newColonyName.2.45', 'freecol-newColonyName.2.46', 'freecol-newColonyName.2.47',
		'freecol-newColonyName.2.48', 'freecol-newColonyName.2.49', 'freecol-newColonyName.2.50',
		'freecol-newColonyName.2.51', 'freecol-newColonyName.2.52', 'freecol-newColonyName.2.53',
		'freecol-newColonyName.2.54', 'freecol-newColonyName.2.55', 'freecol-newColonyName.2.56',
		'freecol-newColonyName.2.57', 'freecol-newColonyName.2.58', 'freecol-newColonyName.2.59',
		'freecol-newColonyName.2.60', 'freecol-newColonyName.2.61', 'freecol-newColonyName.2.62',
		'freecol-newColonyName.2.63', 'freecol-newColonyName.2.64', 'freecol-newColonyName.2.65',
		'freecol-newColonyName.3.0', 'freecol-newColonyName.3.1', 'freecol-newColonyName.3.2',
		'freecol-newColonyName.3.3', 'freecol-newColonyName.3.4', 'freecol-newColonyName.3.5',
		'freecol-newColonyName.3.6', 'freecol-newColonyName.3.7', 'freecol-newColonyName.3.8',
		'freecol-newColonyName.3.9', 'freecol-newColonyName.3.10', 'freecol-newColonyName.3.11',
		'freecol-newColonyName.3.12', 'freecol-newColonyName.3.13', 'freecol-newColonyName.3.14',
		'freecol-newColonyName.3.15', 'freecol-newColonyName.3.16', 'freecol-newColonyName.3.17',
		'freecol-newColonyName.3.18', 'freecol-newColonyName.3.19', 'freecol-newColonyName.3.20',
		'freecol-newColonyName.3.21', 'freecol-newColonyName.3.22', 'freecol-newColonyName.3.23',
		'freecol-newColonyName.3.24', 'freecol-newColonyName.3.25', 'freecol-newColonyName.3.26',
		'freecol-newColonyName.3.27', 'freecol-newColonyName.3.28', 'freecol-newColonyName.3.29',
		'freecol-newColonyName.3.30', 'freecol-newColonyName.3.31', 'freecol-newColonyName.3.32',
		'freecol-newColonyName.3.33', 'freecol-newColonyName.3.34', 'freecol-newColonyName.3.35',
		'freecol-newColonyName.3.36', 'freecol-newColonyName.3.37', 'freecol-newColonyName.3.38',
		'freecol-newColonyName.4.0', 'freecol-newColonyName.4.1', 'freecol-newColonyName.4.2',
		'freecol-newColonyName.4.3', 'freecol-newColonyName.4.4', 'freecol-newColonyName.4.5',
		'freecol-newColonyName.4.6', 'freecol-newColonyName.4.7', 'freecol-newColonyName.4.8',
		'freecol-newColonyName.4.9', 'freecol-newColonyName.4.10', 'freecol-newColonyName.4.11',
		'freecol-newColonyName.4.12', 'freecol-newColonyName.4.13', 'freecol-newColonyName.4.14',
		'freecol-newColonyName.4.15', 'freecol-newColonyName.4.16', 'freecol-newColonyName.4.17',
		'freecol-newColonyName.4.18', 'freecol-newColonyName.4.19', 'freecol-newColonyName.4.20',
		'freecol-newColonyName.4.21', 'freecol-newColonyName.4.22', 'freecol-newColonyName.4.23', 
		'freecol-newColonyName.4.24', 'freecol-newColonyName.4.25', 'freecol-newColonyName.4.26',
		'freecol-newColonyName.4.27', 'freecol-newColonyName.4.28', 'freecol-newColonyName.4.29',
		'freecol-model.nation.Portuguese.Europe',
		'freecol-model.nation.Portuguese.ruler', 'freecol-model.nation.refPortuguese.ruler',
		'freecol-model.nation.Dutch.ruler', 'freecol-model.nation.English.ruler', 'freecol-model.nation.French.ruler',
		'freecol-model.nation.Spanish.ruler', 'freecol-model.nation.Inca.ruler', 'freecol-model.nation.Aztec.ruler',
		'freecol-model.nation.Arawak.ruler', 'freecol-model.nation.Cherokee.ruler', 'freecol-model.nation.Iroquois.ruler',
		'freecol-model.nation.Sioux.ruler', 'freecol-model.nation.Apache.ruler', 'freecol-model.nation.Tupi.ruler',
		'freecol-model.nation.refDutch.ruler', 'freecol-model.nation.refEnglish.ruler', 'freecol-model.nation.refFrench.ruler',
		'freecol-model.nation.refSpanish.ruler', 'freecol-foundingFather.adamSmith.birthAndDeath', 'freecol-foundingFather.jacobFugger.birthAndDeath',
		'freecol-foundingFather.peterMinuit.birthAndDeath', 'freecol-foundingFather.peterStuyvesant.birthAndDeath', 'freecol-foundingFather.janDeWitt.birthAndDeath',
		'freecol-foundingFather.ferdinandMagellan.birthAndDeath', 'freecol-foundingFather.franciscoDeCoronado.birthAndDeath', 'freecol-foundingFather.hernandoDeSoto.birthAndDeath',
		'freecol-foundingFather.henryHudson.birthAndDeath', 'freecol-foundingFather.laSalle.birthAndDeath', 'freecol-foundingFather.hernanCortes.birthAndDeath',
		'freecol-foundingFather.georgeWashington.birthAndDeath', 'freecol-foundingFather.paulRevere.birthAndDeath', 'freecol-foundingFather.francisDrake.birthAndDeath',
		'freecol-foundingFather.johnPaulJones.birthAndDeath', 'freecol-foundingFather.thomasJefferson.birthAndDeath', 'freecol-foundingFather.pocahontas.birthAndDeath',
		'freecol-foundingFather.thomasPaine.birthAndDeath', 'freecol-foundingFather.simonBolivar.birthAndDeath', 'freecol-foundingFather.benjaminFranklin.birthAndDeath',
		'freecol-foundingFather.williamBrewster.birthAndDeath', 'freecol-foundingFather.williamPenn.birthAndDeath', 'freecol-foundingFather.fatherJeanDeBrebeuf.birthAndDeath',
		'freecol-foundingFather.juanDeSepulveda.birthAndDeath', 'freecol-foundingFather.bartolomeDeLasCasas.birthAndDeath',
		'freecol-foundingFather.adamSmith.name', 'freecol-foundingFather.jacobFugger.name', 'freecol-foundingFather.peterMinuit.name',
		'freecol-foundingFather.peterStuyvesant.name', 'freecol-foundingFather.janDeWitt.name', 'freecol-foundingFather.ferdinandMagellan.name',
		'freecol-foundingFather.franciscoDeCoronado.name', 'freecol-foundingFather.hernandoDeSoto.name', 'freecol-foundingFather.henryHudson.name',
		'freecol-foundingFather.laSalle.name', 'freecol-foundingFather.hernanCortes.name', 'freecol-foundingFather.georgeWashington.name',
		'freecol-foundingFather.paulRevere.name', 'freecol-foundingFather.francisDrake.name', 'freecol-foundingFather.johnPaulJones.name',
		'freecol-foundingFather.thomasJefferson.name', 'freecol-foundingFather.pocahontas.name', 'freecol-foundingFather.thomasPaine.name',
		'freecol-foundingFather.simonBolivar.name', 'freecol-foundingFather.benjaminFranklin.name', 'freecol-foundingFather.williamBrewster.name',
		'freecol-foundingFather.williamPenn.name', 'freecol-foundingFather.fatherJeanDeBrebeuf.name', 'freecol-foundingFather.juanDeSepulveda.name',
		'freecol-foundingFather.bartolomeDeLasCasas.name',
	);

	public function __construct() {
		parent::__construct();
		global $wgTranslateExtensionDirectory;
		$this->fileDir = $wgTranslateExtensionDirectory . 'freecol/';
		$this->mangler = new StringMatcher( $this->prefix, array( '*' ) );
	}

	public function getMessageFile( $code ) {
		if ( $code == 'en' ) {
			return 'FreeColMessages.properties';
		} else {
			if ( isset($this->codeMap[$code]) ) {
				$code = $this->codeMap[$code];
			}
			return "FreeColMessages_$code.properties";
		}
	}

	protected function getFileLocation( $code ) {
		return $this->fileDir . $this->getMessageFile( $code );
	}

	public function getReader( $code ) {
		return new JavaFormatReader( $this->getFileLocation( $code ) );
	}

	public function getWriter() {
		return new JavaFormatWriter( $this );
	}
}

class GettextMessageGroup extends MessageGroup {
	/**
	 * Name of the array where all messages are stored, if applicable.
	 */
	protected $potFile      = 'messages';
	public function getPotFile() { return $this->potFile; }
	public function setPotFile( $value ) { $this->potFile = $value; }

	protected $codeMap = array();


	protected $prefix = '';
	public function getPrefix() { return $this->prefix; }
	public function setPrefix( $value ) { $this->prefix = $value; }


	public function getMessageFile( $code ) {
		if ( $code == 'en' ) {
			return $this->getPotFile();
		} else {
			if ( isset($this->codeMap[$code]) ) {
				$code = $this->codeMap[$code];
			}
			return "$code.po";
		}
	}

	public static function factory( $label, $id ) {
		$group = new GettextMessageGroup;
		$group->setLabel( $label );
		$group->setId( $id );
		return $group;
	}


	public function getReader( $code ) {
		$reader = new GettextFormatReader( $this->getPrefix() . $this->getMessageFile( $code ) );
		if ( $code === 'en' )
			$reader->setPotMode( true );
		return $reader;
	}

	public function getWriter() {
		return new GettextFormatWriter( $this );
	}
}

class WikiMessageGroup extends MessageGroup {
	protected $source = null;

	/**
	 * Constructor.
	 *
	 * @param $id Unique id for this group.
	 * @param $source Mediawiki message that contains list of message keys.
	 */
	public function __construct( $id, $source ) {
		parent::__construct();
		$this->id = $id;
		$this->source = $source;
	}

	public function fill( MessageCollection $messages ) {
		return; // no-op
	}

	/* Fetch definitions from database */
	public function getDefinitions() {
		$definitions = array();
		/* In theory could have templates that are substitued */
		$contents = wfMsg( $this->source );
		$messages = preg_split( '/\s+/', $contents );
		foreach ( $messages as $message ) {
			if ( !$message ) continue;
			$definitions[$message] = wfMsgForContentNoTrans( $message );
		}
		return $definitions;
	}

	/**
	 * Returns of stored translation of message specified by the $key in language
	 * code $code.
	 *
	 * @param $key Key of the message.
	 * @param $code Language code.
	 * @return Stored translation or null.
	 */
	public function getMessage( $key, $code ) {
		global $wgContLang;
		$params = array();
		if ( $code && $wgContLang->getCode() !== $code ) {
			$key = "$key/$code";
		} else {
			$params[] = 'content';
		}
		$message = wfMsgExt( $key, $params );
		return wfEmptyMsg( $key, $message ) ? null : $message;
	}
}

class MessageGroups {
	public static function init() {
		static $loaded = false;
		if ( $loaded ) return;

		global $wgTranslateAddMWExtensionGroups;
		if ($wgTranslateAddMWExtensionGroups) {
			$a = new PremadeMediawikiExtensionGroups;
			$a->addAll();
		}

		global $wgTranslateCC;
		wfRunHooks('TranslatePostInitGroups', array( &$wgTranslateCC ) );
		$loaded = true;
	}

	public static function getGroup( $id ) {
		self::init();

		global $wgTranslateEC, $wgTranslateAC, $wgTranslateCC;
		if ( in_array( $id, $wgTranslateEC ) ) {
			$creater = $wgTranslateAC[$id];
			if ( is_array( $creater ) ) {
				return call_user_func( $creater, $id );
			} else {
				return new $creater;
			}
		} else {
			if ( array_key_exists( $id, $wgTranslateCC ) ) {
				if ( is_callable( $wgTranslateCC[$id] ) ) {
					return call_user_func( $wgTranslateCC[$id], $id );
				} else {
					return $wgTranslateCC[$id];
				}
			} else {
				return null;
			}
		}
	}

	public $classes = array();
	private function __construct() {
		self::init();
		global $wgTranslateEC, $wgTranslateCC;

		$all = array_merge( $wgTranslateEC, array_keys( $wgTranslateCC ) );
		sort( $all );
		foreach ( $all as $id ) {
			$g = self::getGroup( $id );
			$this->classes[$g->getId()] = $g;
		}
	}

	public static function singleton() {
		static $instance;
		if ( !$instance instanceof self ) {
			$instance = new self();
		}
		return $instance;
	}

	public function getGroups() {
		return $this->classes;
	}
}
