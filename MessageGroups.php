<?php

abstract class MessageGroup {
	/**
	 * Human-readable name of this group
	 */
	protected $label  = 'none';

	/**
	 * Group-wide unique id of this group. Used also for sorting.
	 */
	protected $id     = 'none';

	/**
	 * Cache of loaded messages.
	 */
	protected $mcache = null;

	/**
	 * Meta groups consist of multiple groups or parts of other groups. This info
	 * is used on many places, like when creating message index.
	 */
	protected $meta   = false;

	/**
	 * Returns a human readable name of this group.
	 */
	public function getLabel() { return $this->label; }

	/**
	 * Returns a unique id of this group.
	 */
	public function getId() { return $this->id; }

	/**
	 * Returns true is this a meta group.
	 */
	public function isMeta() { return $this->meta; }

	/**
	 * In this function message group should add translations from the stored file
	 * for language code $code and it's fallback language, if used.
	 *
	 * @param $messages MessageCollection
	 * @param $code Language code.
	 */
	public abstract function fill( MessageCollection $messages, $code );

	/**
	 * In this function message group can specify some messages to be optional or
	 * ignored.
	 */
	public function getBools() {
		return array(
			'optional' => $this->optional,
			'ignored' => $this->ignored,
		);
	}
	protected $optional = array();
	protected $ignored = array();

	/**
	 * In this function message group should export messages in relevant format.
	 *
	 * @param $array Reference of MessageArray.
	 * @param $code Language code.
	 */
	public function export( MessageCollection $messages, $code ) {
		return 'Not supported';
	}

	/**
	 * In this function message group should export messages in whole-file format,
	 * if applicable. Default implementation just calls $this->export().
	 *
	 * @param $messages MessageCollection
	 * @param $code Language code.
	 * @param $authors 1-D array of authors that have edited messages in wiki.
	 */
	public function exportToFile( MessageCollection $messages, $code, $authors ) {
		return $this->export( $messages, $code );
	}

	/**
	 * This function returns array of typoe key => definition of all messages
	 * this message group handles.
	 *
	 * @return Array of messages definitions indexed by key.
	 */
	public abstract function getDefinitions();

	/**
	 * Returns of stored translation of message specified by the $key in language
	 * code $code.
	 *
	 * @param $key Key of the message.
	 * @param $code Language code.
	 * @return Stored translation or null.
	 */
	public abstract function getMessage( $key, $code );


	/**
	 * Returns path to the file where translation of language code $code are.
	 *
	 * @return Path to the file or false if not applicable.
	 */
	public function getMessageFile( $code ) { return false; }

	/**
	 * Resets the cache to free memory.
	 */
	public function reset() {
		$this->mcache = array();
	}

	/**
	 * Preprocesses MessageArray to suitable format and filters things that should
	 * not be exported.
	 *
	 * @param $array Reference of MessageArray.
	 */
	public function makeExportArray( MessageCollection $messages ) {
		// We copy only relevant translations to this new array
		$new = array();
		foreach( $messages as $key => $m ) {
			# CASE1: ignored
			if ( $m->ignored ) continue;

			$translation = $m->translation;
			# CASE2: no translation
			if ( $translation === null ) continue;

			# Remove fuzzy markings before export
			$translation = str_replace( TRANSLATE_FUZZY, '', $translation );

			# CASE3: optional messages; accept only if different
			if ( $m->optional && $translation === $m->definition ) continue;

			# CASE4: don't export non-translations unless translated in wiki
			if ( !$m->pageExists && $translation === $m->definition ) continue;


			# Otherwise it's good
			$new[$key] = $translation;
		}

		return $new;
	}

	/**
	 * Formats list of authors nicely.
	 *
	 * @param $authors List of authors
	 */
	protected function formatAuthors( $authors ) {
		$s = array();
		foreach ( $authors as $a ) {
			$s[] = " * @author $a";
		}
		return implode( "\n", $s );
	}

}

class CoreMessageGroup extends MessageGroup {
	protected $label = 'MediaWiki messages';
	protected $id    = 'core';

	public function getMessageFile( $code ) {
		$code = ucfirst( str_replace( '-', '_', $code ) );
		return "Messages$code.php";
	}

	public function getMessage( $key, $code ) {
		$messages = $this->loadMessages( $code );
		return isset( $messages[$key] ) ? $messages[$key] : null;
	}

	function export( MessageCollection $messages, $code ) {
		list( $output, ) = MessageWriter::writeMessagesArray( $this->makeExportArray( $messages ), false );
		return $output;
	}

	public function exportToFile( MessageCollection $messages, $code, $authors ) {
		$filename = Language::getMessagesFileName( $code );

		$messages = $this->export( $messages, $code );
		$name = TranslateUtils::getLanguageName( $code );
		$native = TranslateUtils::getLanguageName( $code, true );
		$authors = array_unique( array_merge( $this->getAuthorsFromFile( $filename ), $authors ) );
		$translators = $this->formatAuthors( $authors );
		$other = $this->getOther( $filename );
		return <<<CODE
<?php
/** $name ($native)
 *
 * @addtogroup Language
 *
$translators
 */

$other

$messages
CODE;
	}


	function getDefinitions() {
		return Language::getMessagesFor( 'en' );
	}

	public function getBools() {
		$l = new languages();
		return array(
			'optional' => $l->getOptionalMessages(),
			'ignored'  => $l->getIgnoredMessages(),
		);
	}

	private function loadMessages( $code ) {
		if ( !isset($this->mcache[$code]) ) {
			$file = Language::getMessagesFileName( $code );
			if ( !file_exists( $file ) ) {
				$this->mcache[$code] = null;
			} else {
				require( $file );
				return $this->mcache[$code] = isset( $messages ) ? $messages : null;
			}
		}

		return $this->mcache[$code];
	}

	public function fill( MessageCollection $messages, $code ) {
		$infile = $this->loadMessages( $code );
		$infbfile = null;
		if ( Language::getFallbackFor( $code ) ) {
			$infbfile = $this->loadMessages( Language::getFallbackFor( $code ) );
		}

		foreach ( $messages->keys() as $key ) {
			if ( isset($infile[$key]) ) {
				$messages[$key]->infile = $infile[$key];
			}
			if ( $infbfile && isset($infbfile[$key]) ) {
				$messages[$key]->fallback = $infbfile[$key];
			}
		}
	}

	/**
	 * Reads all \@author tags from the file and returns array of authors.
	 *
	 * @param $filename From which file to get the authors.
	 * @return Array of authors.
	 */
	private function getAuthorsFromFile( $filename ) {
		if ( !file_exists( $filename ) ) { return array(); }
		$contents = file_get_contents( $filename );
		$m = array();
		$count = preg_match_all( '/@author (.*)/', $contents, $m );
		return $m[1];
	}

	private function getOther( $filename ) {
		if ( !file_exists( $filename ) ) { return ''; }
		$contents = file_get_contents( $filename );

		/** FIXME: handle the case where the first comment is missing */
		$dollarstart = strpos( $contents, '$' );

		$start = strpos( $contents, '*/' );
		$end = strpos( $contents, '$messages' );
		if ( $start === false ) return '';
		if ( $start === $end ) return '';
		$start += 2; // Get over the comment ending
		if ( $end === false ) return trim( substr( $contents, $start ) );
		return trim( substr( $contents, $start, $end-$start ) );
	}

}

abstract class ExtensionMessageGroup extends MessageGroup {
	/**
	 * Name of the array where all messages are stored, if applicable.
	 */
	protected $arrName      = false;
	/**
	 * Name of the function which returns all messages, if applicable.
	 */
	protected $functionName = false;
	/**
	 * Path to the file where array or function is defined, relative to extensions
	 * root directory.
	 */
	protected $messageFile  = null;

	/**
	 * The syntax of array definition. $ARRAY is replaced with $this->arrName and
	 * $CODE is replaced with exported language.
	 */
	protected $exportStart = '$$ARRAY[\'$CODE\'] = array(';
	/**
	 * The syntax of array closure.
	 */
	protected $exportEnd   = ');';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";

	/*
	 * Append (mw ext) to extension labels. This doesn't break sorting.
	 */
	public function getLabel() { return $this->label . " (mw ext)"; }

	public function getMessageFile( $code ) { return $this->messageFile; }

	public function getMessage( $key, $code ) {
		$this->load( $code );
		return isset( $this->mcache[$code][$key] ) ? $this->mcache[$code][$key] : null;
	}

	/**
	 * This function loads messages for given language for further use.
	 *
	 * @param $code Language code
	 * @throws MWException If loading fails.
	 */
	protected function load( $code ) {
		// Check if we have already loaded all messages
		if ( isset($this->mcache['en']) ) return;

		// If not, load them now
		$cache = $this->loadMessages( $code );
		if ( $cache === null ) {
			throw new MWException( "Unable to load messages for $code in {$this->label}" );
		}
		$this->mcache = $cache;
	}

	protected function getPath( $code ) {
		global $wgTranslateExtensionDirectory;
		if ( $this->messageFile ) {
			$fullPath = $wgTranslateExtensionDirectory . $this->messageFile;
		} else {
			throw new MWException( 'Message file not defined' );
		}
		return $fullPath;
	}

	/**
	 * This function is a wrapper which calls the real loader depending on which
	 * kind of structure the messages are in this extension.
	 *
	 * @param $code Language code of messages to be loaded.
	 * @returns Array
	 */
	protected function loadMessages( $code ) {
		$path = $this->getPath( $code );

		if ( $this->arrName ) {
			return $this->loadFromVariable( $path );
		} elseif ( $this->functionName ) {
			return $this->loadFromFunction( $path );
		}
	}

	/**
	 * This function loads a variable from given php file and returns it.
	 *
	 * @param $path Path to php file that is passed to include().
	 * @return Contents of the variable or null if it is not set or file does not
	 * exist.
	 */
	private function loadFromVariable( $path ) {
		if ( file_exists( $path ) ) {
			include( $path );
			if ( isset( ${$this->arrName} ) ) {
				return ${$this->arrName};
			}
		}

		return null;
	}

	/**
	 * This function includes the file given in $path if needed, and calls the
	 * defined function and returns it return value.
	 *
	 * @param $path Path to php file that is passed to include().
	 * @return Return value of the function or null if the function is not defined
	 * or file does not exist.
	 */
	private function loadFromFunction( $path ) {
		// Try first calling it directly
		if ( function_exists( $this->functionName ) ) {
			return call_user_func( $this->functionName );
		} elseif ( file_exists( $path ) ) {
			include( $path );
			if ( function_exists( $this->functionName ) ) {
				return call_user_func( $this->functionName );
			}
		}

		return null;
	}

	function export( MessageCollection $messages, $code ) {
		// Replace variables from definition
		$txt = $this->exportPrefix . str_replace(
			array( '$ARRAY', '$CODE' ),
			array( $this->arrName, $code ),
			$this->exportStart ) . "\n";

		// Use the same function that rebuildLanguage.php uses
		$txt .= MessageWriter::writeMessagesBlock( false,
			$this->makeExportArray( $messages ), array(), $this->exportLineP
		);

		// Remove the last newline, not needed here
		$txt = substr( $txt, 0, -1 );
		$txt .= $this->exportPrefix . $this->exportEnd;

		return $txt;
	}

	public function exportToFile( MessageCollection $messages, $code, $authors ) {
		$x = $this->export( $messages, $code );
		$name = TranslateUtils::getLanguageName( $code );
		$native = TranslateUtils::getLanguageName( $code, true );
		$translators = $this->formatAuthors( $authors );
		if ( $translators !== '' ) {
			$translators = "\n$translators\n";
		}
		return <<<CODE
/** $name ($native)$translators */
$x
CODE;
	}


	function getDefinitions() {
		$this->load( 'en' );
		return $this->mcache['en'];
	}

	public function fill( MessageCollection $messages, $code ) {
		$this->load( $code );

		$fbcode = Language::getFallbackFor( $code );
		if ( $fbcode ) {
			$this->load( $fbcode );
		}

		foreach ( $messages->keys() as $key ) {
			if ( isset($this->mcache[$code][$key]) ) {
				$messages[$key]->infile = $this->mcache[$code][$key];
			}
			if ( isset($this->mcache[$fbcode][$key]) ) {
				$messages[$key]->fallback = $this->mcache[$fbcode][$key];
			}
		}
	}

}

class MultipleFileMessageGroup extends ExtensionMessageGroup {
	protected $filePattern = false;

	public function getMessageFile( $code ) {
		return str_replace( '$CODE', $code, $this->filePattern );
	}


	protected function load( $code ) {
		if ( isset($this->mcache[$code]) ) return;
		$cache = $this->loadMessages( $code );
		if ( $cache === null ) {
			if ( $code === 'en' ) {
				throw new MWException( "Unable to load messages for $code in {$this->label}" );
			} else {
				$cache = array();
			}
		}
		$this->mcache[$code] = $cache;
	}

	protected function getPath( $code ) {
		// Many extensions use non-regular filename for english messages. We use the
		// single filename for that.
		if ( $code === 'en' ) {
			return parent::getPath( 'en' );
		}

		// And for other files we use pattern, where $CODE is replaced with language
		// code.
		global $wgTranslateExtensionDirectory;
		$fullPath = false;
		if ( $this->filePattern ) {
			$filename = str_replace( '$CODE', $code, $this->filePattern );
			$fullPath = $wgTranslateExtensionDirectory . $filename;
		}
		return $fullPath;
	}

}

class CoreMostUsedMessageGroup extends CoreMessageGroup {
	protected $label = 'MediaWiki messages (most used)';
	protected $id    = 'core-mostused';
	protected $meta  = true;

	public function export( MessageCollection $messages, $code ) { return 'Not supported'; }
	public function exportToFile( MessageCollection $messages, $code, $authors ) { return 'Not supported'; }


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

	protected function load( $code ) {
		$this->init();
		foreach ( $this->classes as $class ) {
			$class->load( $code );
		}
	}

	public function getMessage( $key, $code ) {
		$this->load( $code );
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

	function export( MessageCollection $messages, $code ) {
		$this->init();
		$ret = '';
		foreach ( $this->classes as $class ) {
			$subArray = new MessageCollection;
			$subArray->addMany( $messages->intersect_key( $class->getDefinitions() ) );
			$ret .= $class->export( $subArray, $code ) . "\n\n\n";
		}
		return $ret;
	}

	function exportToFile( MessageCollection $messages, $code, $authors ) {
		$this->init();
		$ret = '';
		foreach ( $this->classes as $class ) {
			$subArray = new MessageCollection;
			$subArray->addMany( $messages->intersect_key( $class->getDefinitions() ) );
			$ret .= $class->exportToFile( $subArray, $code, array() ) . "\n\n\n";
		}
		return $ret;
	}

	function fill( MessageCollection $messages, $code ) {
		$this->init();
		foreach ( $this->classes as $class ) {
			$class->fill( $messages, $code );
		}
	}

	function getBools() {
		$this->init();
		$bools = array();
		foreach ( $this->classes as $class ) {
			$bools = array_merge_recursive( $bools, $class->getBools() );
		}
		return $bools;
	}

	public function reset() {
		foreach ( $this->classes as $class ) {
			$class->reset();
		}
	}

}

class AllWikimediaExtensionsGroup extends AllMediawikiExtensionsGroup {
	protected $label = 'Extensions used by Wikimedia';
	protected $id    = 'ext-0-wikimedia';
	protected $meta  = true;

	protected $classes = null;

	protected $wmfextensions = array(
		'ext-antispoof',
		'ext-assertedit',
		'ext-boardvote',
		'ext-bookinformation',
		'ext-categorytree',
		'ext-checkuser',
		'ext-cite',
		'ext-citespecial',
		'ext-confirmedit',
		'ext-crossnamespacelinks',
		'ext-deletedcontribs',
		'ext-dismissablesitenotice',
		'ext-expandtemplates',
		'ext-filepath',
		'ext-gadgets',
		'ext-imagemap',
		'ext-inputbox',
		'ext-intersection',
		'ext-linksearch',
		'ext-lucenesearch',
		'ext-makebot',
		'ext-makesysop',
		'ext-newuserlog',
		'ext-nuke',
		'ext-ogghandler',
		'ext-oversight',
		'ext-proofreadpage',
		'ext-quiz',
		'ext-renameuser',
		'ext-scanset',
		'ext-sitematrix',
		'ext-spamblacklist',
		'ext-syntaxhighlightgeshi',
		'ext-usernameblacklist',
	);

	protected function init() {
		if ( $this->classes === null ) {
			$this->classes = MessageGroups::singleton()->getGroups();
			$this->classes = array_intersect_key(
				$this->classes,
				array_flip( $this->wmfextensions )
			);
		}
	}
}

class AdvancedRandomMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Advanced Random';
	protected $id    = 'ext-advancedrandom';

	protected $arrName     = 'messages';
	protected $messageFile = 'AdvancedRandom/SpecialAdvancedRandom.i18n.php';
}

class AjaxShowEditorsMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Ajax Show Editors';
	protected $id    = 'ext-ajaxshoweditors';

	protected $arrName     = 'wgAjaxShowEditorsMessages';
	protected $messageFile = 'AjaxShowEditors/AjaxShowEditors.i18n.php';
}

class AntiSpoofMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Anti Spoof';
	protected $id    = 'ext-antispoof';

	protected $arrName     = 'wgAntiSpoofMessages';
	protected $messageFile = 'AntiSpoof/AntiSpoof_i18n.php';
}

class AssertEditMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Assert Edit';
	protected $id    = 'ext-assertedit';

	protected $arrName     = 'messages';
	protected $messageFile = 'AssertEdit/AssertEdit.i18n.php';
}

class AsksqlMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Asksql';
	protected $id    = 'ext-asksql';

	protected $functionName = 'efAsksqlMessages';
	protected $messageFile  = 'Asksql/Asksql.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class BackAndForthMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Back and Forth';
	protected $id    = 'ext-backandforth';

	protected $functionName = 'efBackAndForthMessages';
	protected $messageFile  = 'BackAndForth/BackAndForth.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class BadImageMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Bad Image';
	protected $id    = 'ext-badimage';

	protected $functionName = 'efBadImageMessages';
	protected $messageFile  = 'BadImage/BadImage.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class BlahtexMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Blahtex';
	protected $id    = 'ext-blahtex';

	protected $arrName     = 'messages';
	protected $messageFile = 'Blahtex/Blahtex.i18n.php';
}

class BlockTitlesMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Block Titles';
	protected $id    = 'ext-blocktitles';

	protected $functionName = 'efBlockTitlesMessages';
	protected $messageFile  = 'BlockTitles/BlockTitles.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class BoardVoteMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Board Vote';
	protected $id      = 'ext-boardvote';

	protected $arrName     = 'wgBoardVoteMessages';
	protected $messageFile = 'BoardVote/BoardVote.i18n.php';

	protected $ignored = array( 'boardvote_footer' );
}

class BookInformationMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Book Information';
	protected $id      = 'ext-bookinformation';

	protected $functionName = 'efBookInformationMessages';
	protected $messageFile  = 'BookInformation/BookInformation.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class CategoryTreeExtensionGroup extends MultipleFileMessageGroup {
	protected $label = 'Category Tree';
	protected $id    = 'ext-categorytree';

	protected $arrName      = 'messages';
	protected $messageFile  = 'CategoryTree/CategoryTree.i18n.php';
	protected $filePattern  = 'CategoryTree/CategoryTree.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportEnd   = ');';
}

class CentralAuthMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Central Auth';
	protected $id      = 'ext-centralauth';

	protected $arrName     = 'wgCentralAuthMessages';
	protected $messageFile = 'CentralAuth/CentralAuth.i18n.php';
}

class ChangeAuthorMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Change Author';
	protected $id    = 'ext-changeauthor';

	protected $arrName     = 'allMessages';
	protected $messageFile = 'ChangeAuthor/ChangeAuthor.i18n.php';

	protected $exportPrefix= "\t";
	protected $exportStart = '\'$CODE\' => array(';
	protected $exportLineP = "\t\t";
	protected $exportEnd   = "),";

	protected $ignored = array( 'changeauthor-short', 'changeauthor-logpagetext' );
}

class CheckUserMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Check User';
	protected $id      = 'ext-checkuser';

	protected $arrName     = 'wgCheckUserMessages';
	protected $messageFile = 'CheckUser/CheckUser.i18n.php';
}

class ChemFunctionsMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Chemistry';
	protected $id    = 'ext-chemistry';

	protected $arrName     = 'wgChemFunctions_Messages';
	protected $messageFile = 'Chemistry/ChemFunctions.i18n.php';

	protected $ignored = array( 'chemFunctions_SearchExplanation' );
	protected $optional = array(
		'chemFunctions_EINECS',
		'chemFunctions_CHEBI',
		'chemFunctions_PubChem',
		'chemFunctions_SMILES',
		'chemFunctions_InChI',
		'chemFunctions_RTECS',
		'chemFunctions_KEGG',
		'chemFunctions_DrugBank',
	);
}

class CiteMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Cite';
	protected $id    = 'ext-cite';

	protected $arrName     = 'messages';
	protected $messageFile = 'Cite/Cite.i18n.php';

	protected $optional = array(
		'cite_reference_link_key_with_num',
		'cite_reference_link_prefix',
		'cite_reference_link_suffix',
		'cite_references_link_prefix',
		'cite_references_link_suffix',
		'cite_reference_link',
		'cite_references_link_one',
		'cite_references_link_many',
		'cite_references_link_many_format',
		'cite_references_link_many_format_backlink_labels',
		'cite_references_link_many_sep',
		'cite_references_link_many_and',
	);
	protected $ignored = array(
		'cite_references_prefix',
		'cite_references_suffix',
	);
}

class CiteSpecialMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Cite (special page)';
	protected $id      = 'ext-citespecial';

	protected $arrName     = 'wgSpecialCiteMessages';
	protected $messageFile = 'Cite/SpecialCite.i18n.php';

	protected $ignored = array(
		'cite_text',
	);
}

class CommentSpammerMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Comment Spammer';
	protected $id    = 'ext-commentspammer';

	protected $functionName = 'efCommentSpammerMessages';
	protected $messageFile  = 'CommentSpammer/CommentSpammer.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportEnd   = '),';
}

class ConfirmAccountMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Confirm Account';
	protected $id      = 'ext-confirmaccount';

	protected $arrName     = 'wgConfirmAccountMessages';
	protected $messageFile = 'ConfirmAccount/ConfirmAccount.i18n.php';
}

class ConfirmEditMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Confirm Edit';
	protected $id    = 'ext-confirmedit';

	protected $arrName     = 'messages';
	protected $messageFile = 'ConfirmEdit/ConfirmEdit.i18n.php';
}

class ConfirmEditFancyCaptchaMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'ConfirmEdit Fancy Captcha';
	protected $id      = 'ext-confirmeditfancycaptcha';

	protected $arrName = 'messages';
	protected $messageFile  = 'ConfirmEdit/FancyCaptcha.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class ContactPageExtensionGroup extends MultipleFileMessageGroup {
	protected $label = 'Contact Page';
	protected $id    = 'ext-contactpage';

	protected $arrName      = 'messages';
	protected $messageFile  = 'ContactPage/ContactPage.i18n.php';
	protected $filePattern  = 'ContactPage/ContactPage.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportLineP = "\t";
	protected $exportEnd   = ');';
}

class ContributionScoresMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Contribution Scores';
	protected $id    = 'ext-contributionscores';

	protected $arrName     = 'messages';
	protected $messageFile = 'ContributionScores/ContributionScores.i18n.php';
}

class ContributionseditcountMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Contributions Edit Count';
	protected $id      = 'ext-contributionseditcount';

	protected $functionName = 'efContributionseditcountMessages';
	protected $messageFile  = 'Contributionseditcount/Contributionseditcount.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportEnd   = '),';
}

class ContributorsMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Contributors';
	protected $id      = 'ext-contributors';

	protected $functionName = 'efContributorsMessages';
	protected $messageFile  = 'Contributors/Contributors.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class CountEditsMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Count Edits';
	protected $id      = 'ext-countedits';

	protected $functionName = 'efCountEditsMessages';
	protected $messageFile  = 'CountEdits/CountEdits.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class CrossNamespaceLinksMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Cross Namespace Links';
	protected $id    = 'ext-crossnamespacelinks';

	protected $arrName     = 'wgCrossNamespaceLinksMessages';
	protected $messageFile = 'CrossNamespaceLinks/SpecialCrossNamespaceLinks.i18n.php';
}

class DeletedContribsMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Deleted Contributions';
	protected $id    = 'ext-deletedcontribs';

	protected $arrName     = 'wgDeletedContribsMessages';
	protected $messageFile = 'DeletedContributions/DeletedContributions.i18n.php';
}

class DesysopMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Desysop';
	protected $id    = 'ext-desysop';

	protected $arrName     = 'wgDesysopMessages';
	protected $messageFile = 'Desysop/SpecialDesysop.i18n.php';
}

class DismissableSiteNoticeMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Dismissable SiteNotice';
	protected $id    = 'ext-dismissablesitenotice';

	protected $arrName     = 'wgDismissableSiteNoticeMessages';
	protected $messageFile = 'DismissableSiteNotice/DismissableSiteNotice.i18n.php';

	protected $ignored = array(
		'sitenotice_id',
	);
}

class DuplicatorMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Duplicator';
	protected $id    = 'ext-duplicator';

	protected $functionName = 'efDuplicatorMessages';
	protected $messageFile  = 'Duplicator/Duplicator.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class EditcountMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Edit Count';
	protected $id    = 'ext-editcount';

	protected $functionName = 'efSpecialEditcountMessages';
	protected $messageFile  = 'Editcount/SpecialEditcount.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class EvalMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Eval';
	protected $id    = 'ext-eval';

	protected $arrName     = 'messages';
	protected $messageFile = 'Eval/SpecialEval.i18n.php';
}

class ExpandTemplatesMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Expand Templates';
	protected $id    = 'ext-expandtemplates';

	protected $arrName     = 'wgExpandTemplatesMessages';
	protected $messageFile = 'ExpandTemplates/ExpandTemplates.i18n.php';
}

class FarmerMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Farmer';
	protected $id    = 'ext-farmer';

	protected $arrName     = 'messages';
	protected $messageFile = 'Farmer/Farmer.i18n.php';

	protected $optional = array(
		'farmercreateurl',
	);
	protected $ignored = array(
		'farmerwikiurl',
		'farmerinterwikiurl',
	);
}

class FCKeditorExtensionGroup extends MultipleFileMessageGroup {
	protected $label = 'FCKeditor';
	protected $id    = 'ext-fckeditor';

	protected $arrName      = 'allMessages';
	protected $messageFile  = 'FCKeditor/FCKeditor.i18n.en.php';
	protected $filePattern  = 'FCKeditor/FCKeditor.i18n.$CODE.php';

	protected $exportStart = '$allMessages = array(';
	protected $exportEnd   = ');';
}

class FlaggedRevsMessageGroup extends MultipleFileMessageGroup {
	protected $label   = 'Flagged Revs';
	protected $id      = 'ext-flaggedrevs';

	protected $arrName     = 'messages';
	protected $messageFile = 'FlaggedRevs/Language/FlaggedRevsPage.i18n.en.php';
	protected $filePattern = 'FlaggedRevs/Language/FlaggedRevsPage.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportEnd   = ');';
}

class FlaggedRevsMakeReviewerMessageGroup extends MultipleFileMessageGroup {
	protected $label = 'Flagged Revs Make Reviewer';
	protected $id    = 'ext-flaggedrevsmakereviewer';

	protected $arrName     = 'messages';
	protected $messageFile = 'FlaggedRevs/Language/MakeReviewer.i18n.en.php';
	protected $filePattern = 'FlaggedRevs/Language/MakeReviewer.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportEnd   = ');';
}

class FormatEmailMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Format Email';
	protected $id    = 'ext-formatemail';

	protected $arrName     = 'messages';
	protected $messageFile = 'FormatEmail/FormatEmail.i18n.php';

	protected $ignored = array(
		'email_header',
	);
}

class FilePathMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'File Path';
	protected $id      = 'ext-filepath';

	protected $arrName     = 'wgFilepathMessages';
	protected $messageFile = 'Filepath/SpecialFilepath.i18n.php';
}

class FindSpamMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Find Spam';
	protected $id    = 'ext-findspam';

	protected $arrName     = 'messages';
	protected $messageFile = 'FindSpam/FindSpam.i18n.php';
}

class GadgetsExtensionGroup extends MultipleFileMessageGroup {
	protected $label = 'Gadgets';
	protected $id    = 'ext-gadgets';

	protected $arrName      = 'messages';
	protected $messageFile  = 'Gadgets/Gadgets.i18n.php';
	protected $filePattern  = 'Gadgets/Gadgets.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportEnd   = ');';

	protected $ignored = array(
		'gadgets-definition',
	);
}

class GiveRollbackMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Give Rollback';
	protected $id      = 'ext-giverollback';

	protected $functionName = 'efGiveRollbackMessages';
	protected $messageFile  = 'GiveRollback/GiveRollback.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class IconMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Icon';
	protected $id    = 'ext-icon';

	protected $arrName     = 'messages';
	protected $messageFile = 'Icon/Icon.i18n.php';
}

class ImageMapMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Image Map';
	protected $id    = 'ext-imagemap';

	protected $functionName = 'efImageMapMessages';
	protected $messageFile  = 'ImageMap/ImageMap.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';

	protected $ignored = array(
		'imagemap_desc_types',
	);
}

class ImportFreeImagesMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Import Free Images';
	protected $id    = 'ext-importfreeimages';

	protected $functionName = 'efImportFreeImagesMessages';
	protected $messageFile  = 'ImportFreeImages/ImportFreeImages.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= "\t\t";
	protected $exportLineP = "\t\t\t";
	protected $exportEnd   = '),';
}

class InputBoxMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Input Box';
	protected $id    = 'ext-inputbox';

	protected $functionName = 'efInputBoxMessages';
	protected $messageFile  = 'inputbox/InputBox.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class InspectCacheMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Inspect Cache';
	protected $id    = 'ext-inspectcache';

	protected $arrName     = 'messages';
	protected $messageFile = 'InspectCache/InspectCache.i18n.php';
}

class IntersectionMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Intersection';
	protected $id    = 'ext-intersection';

	protected $arrName     = 'messages';
	protected $messageFile = 'intersection/DynamicPageList.i18n.php';
}

class InterwikiMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Interwiki Edit Page';
	protected $id    = 'ext-interwiki';

	protected $arrName     = 'wgSpecialInterwikiMessages';
	protected $messageFile = 'Interwiki/SpecialInterwiki.i18n.php';

	protected $optional = array(
		'interwiki_defaulturl',
		'interwiki_local',
		'interwiki_trans',
	);
	protected $ignored = array(
		'interwiki_logentry',
		'interwiki_url',
	);
}

class LatexDocMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Latex Doc';
	protected $id    = 'ext-latexdoc';

	protected $arrName     = 'messages';
	protected $messageFile = 'LatexDoc/LatexDoc.i18n.php';
}

class LinkSearchMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Link Search';
	protected $id    = 'ext-linksearch';

	protected $arrName     = 'wgLinkSearchMessages';
	protected $messageFile = 'LinkSearch/LinkSearch.i18n.php';
}

class LiquidThreadsMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Liquid Threads';
	protected $id    = 'ext-liquidthreads';

	protected $arrName     = 'messages';
	protected $messageFile = 'LiquidThreads/Lqt.i18n.php';

	protected $ignored = array(
		'lqt_header_warning_before_big',
	);
}

class LookupUserMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Lookup User';
	protected $id    = 'ext-lookupuser';

	protected $arrName     = 'messages';
	protected $messageFile = 'LookupUser/LookupUser.i18n.php';
}

class LuceneSearchMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Lucene Search';
	protected $id    = 'ext-lucenesearch';

	protected $arrName     = 'wgLuceneSearchMessages';
	protected $messageFile = 'LuceneSearch/LuceneSearch.i18n.php';

	protected $ignored = array(
		'searchaliases',
		'searchnearmatch',
	);
}

class MakeBotMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Make Bot';
	protected $id    = 'ext-makebot';

	protected $functionName = 'efMakebotMessages';
	protected $messageFile  = 'Makebot/Makebot.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class MakeSysopMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Make Sysop';
	protected $id    = 'ext-makesysop';

	protected $arrName     = 'messages';
	protected $messageFile = 'Makesysop/SpecialMakesysop.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class MathStatMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Math Stat';
	protected $id    = 'ext-mathstat';

	protected $arrName     = 'wgMathStatFunctionsMessages';
	protected $messageFile = 'MathStatFunctions/MathStatFunctions.i18n.php';
}

class MediaFunctionsMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Media Functions';
	protected $id      = 'ext-mediafunctions';

	protected $functionName = 'efMediaFunctionsMessages';
	protected $messageFile  = 'MediaFunctions/MediaFunctions.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class MicroIDMessageGroup extends ExtensionMessageGroup {
	protected $label = 'MicroID';
	protected $id    = 'ext-microid';

	protected $arrName     = 'messages';
	protected $messageFile = 'MicroID/MicroID.i18n.php';
}

class MiniDonationMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Mini Donation';
	protected $id    = 'ext-minidonation';

	protected $arrName     = 'wgMiniDonationMessages';
	protected $messageFile = 'MiniDonation/MiniDonation.i18n.php';
}

class MinimumNameLengthMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Minimum Name Length';
	protected $id      = 'ext-minimumnamelength';

	protected $functionName = 'efMinimumNameLengthMessages';
	protected $messageFile  = 'MinimumNameLength/MinimumNameLength.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class MiniPreviewExtensionGroup extends MultipleFileMessageGroup {
	protected $label = 'Mini Preview';
	protected $id    = 'ext-minipreview';

	protected $arrName      = 'messages';
	protected $messageFile  = 'MiniPreview/MiniPreview.i18n.php';
	protected $filePattern  = 'MiniPreview/MiniPreview.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportEnd   = ');';
}

class MultiUploadMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Multi Upload';
	protected $id    = 'ext-multiupload';

	protected $arrName     = 'messages';
	protected $messageFile = 'MultiUpload/SpecialMultipleUpload.i18n.php';
}

class NetworkAuthMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Network Auth';
	protected $id    = 'ext-networkauth';

	protected $arrName     = 'messages';
	protected $messageFile = 'NetworkAuth/NetworkAuth.i18n.php';

	protected $optional = array(
		'networkauth-name',
		'networkauth-purltext',
	);
}

class NewestPagesMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Newest Pages';
	protected $id    = 'ext-newestpages';

	protected $functionName = 'efNewestPagesMessages';
	protected $messageFile = 'NewestPages/NewestPages.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class NewuserLogMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Newuser Log';
	protected $id    = 'ext-newuserlog';

	protected $arrName     = 'wgNewuserlogMessages';
	protected $messageFile = 'Newuserlog/Newuserlog.i18n.php';

	protected $exportLineP = "\t";

	protected $ignored = array(
		'newuserlogentry',
		'newuserlog-create-text',
	);
}

class NewUserNotifMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'New User Notification';
	protected $id      = 'ext-newusernotif';

	protected $functionName = 'efNewUserNotifMessages';
	protected $messageFile  = 'NewUserNotif/NewUserNotif.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class NukeMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Nuke';
	protected $id    = 'ext-nuke';

	protected $functionName = 'SpecialNukeMessages';
	protected $messageFile  = 'Nuke/SpecialNuke.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= "\t";
	protected $exportLineP = "\t\t";
	protected $exportEnd   = '),';
}

class OggHandlerMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Ogg Handler';
	protected $id    = 'ext-ogghandler';

	protected $arrName     = 'messages';
	protected $messageFile = 'OggHandler/OggHandler.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= "\t";
	protected $exportLineP = "\t\t";
	protected $exportEnd   = '),';

	protected $optional = array(
		'ogg-player-cortado',
		'ogg-player-vlc-mozilla',
		'ogg-player-vlc-activex',
		'ogg-player-quicktime-mozilla',
		'ogg-player-quicktime-activex',
	);
}

class OpenIDMessageGroup extends ExtensionMessageGroup {
	protected $label = 'OpenID';
	protected $id    = 'ext-openid';

	protected $arrName     = 'messages';
	protected $messageFile = 'OpenID/OpenID.i18n.php';
}

class OversightMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Oversight';
	protected $id      = 'ext-oversight';

	protected $functionName = 'efHideRevisionMessages';
	protected $messageFile  = 'Oversight/HideRevision.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class PageByMessageGroup extends MultipleFileMessageGroup {
	protected $label = 'Page By';
	protected $id    = 'ext-pageby';

	protected $arrName      = 'messages';
	protected $messageFile  = 'PageBy/PageBy.i18n.php';
	protected $filePattern  = 'PageBy/PageBy.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportEnd   = ');';
}

class PasswordResetMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Password Reset';
	protected $id    = 'ext-passwordreset';

	protected $arrName     = 'allMessages';
	protected $messageFile = 'PasswordReset/PasswordReset.i18n.php';

	protected $exportPrefix= "\t";
	protected $exportStart = '\'$CODE\' => array(';
	protected $exportLineP = "\t\t";
	protected $exportEnd   = "),";
}

class ParserFunctionsMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Parser Functions';
	protected $id      = 'ext-parserfunctions';

	protected $functionName = 'efParserFunctionsMessages';
	protected $messageFile  = 'ParserFunctions/ParserFunctions.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class PatrollerMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Patroller';
	protected $id      = 'ext-patroller';

	protected $functionName = 'efPatrollerMessages';
	protected $messageFile  = 'Patroller/Patroller.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class PdfHandlerMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Pdf Handler';
	protected $id      = 'ext-pdfhandler';

	protected $functionName = 'efPdfHandlerMessages';
	protected $messageFile  = 'PdfHandler/PdfHandler.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= "\t\t";
	protected $exportLineP = "\t\t\t";
	protected $exportEnd   = '),';
}

class PlayerMessageGroup extends MultipleFileMessageGroup {
	protected $label = 'Player';
	protected $id    = 'ext-player';

	protected $arrName      = 'messages';
	protected $messageFile  = 'Player/Player.i18n.php';
	protected $filePattern  = 'Player/Player.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportEnd   = ');';

	protected $ignored = array(
		'player-pagetext',
	);
}

class PostCommentMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Post Comment';
	protected $id    = 'ext-postcomment';

	protected $arrName     = 'messages';
	protected $messageFile = 'Postcomment/SpecialPostcomment.i18n.php';
}

class PovWatchMessageGroup extends ExtensionMessageGroup {
	protected $label = 'POV Watch';
	protected $id    = 'ext-povwatch';

	protected $arrName     = 'messages';
	protected $messageFile = 'PovWatch/PovWatch.i18n.php';
}

class ProfileMonitorMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Profile Monitor';
	protected $id      = 'ext-profilemonitor';

	protected $functionName = 'efProfileMonitorMessages';
	protected $messageFile  = 'ProfileMonitor/ProfileMonitor.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class ProofreadPageMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Proofread Page';
	protected $id    = 'ext-proofreadpage';

	protected $arrName     = 'messages';
	protected $messageFile = 'ProofreadPage/ProofreadPage.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= "\t";
	protected $exportLineP = "\t\t";
	protected $exportEnd   = '),';
}

class ProtectSectionMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Protect Section';
	protected $id    = 'ext-protectsection';

	protected $arrName     = 'messages';
	protected $messageFile = 'ProtectSection/ProtectSection.i18n.php';
}

class PurgeMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Purge';
	protected $id    = 'ext-purge';

	protected $arrName     = 'messages';
	protected $messageFile = 'Purge/Purge.i18n.php';
}

class QuizMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Quiz';
	protected $id    = 'ext-quiz';

	protected $arrName     = 'wgQuizMessages';
	protected $messageFile = 'Quiz/Quiz.i18n.php';

	protected $exportPrefix= "\t";
	protected $exportStart = '\'$CODE\' => array(';
	protected $exportLineP = "\t\t";
	protected $exportEnd   = "),";
}

class RandomInCategoryMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Random in Category';
	protected $id    = 'ext-randomincategory';

	protected $arrName     = 'messages';
	protected $messageFile = 'RandomInCategory/SpecialRandomincategory.i18n.php';
}

class RegexBlockMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Regex Block';
	protected $id    = 'ext-regexblock';

	protected $arrName     = 'messages';
	protected $messageFile = 'regexBlock/regexBlock.i18n.php';
}

class RenameUserMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Rename User';
	protected $id    = 'ext-renameuser';

	protected $arrName     = 'messages';
	protected $messageFile = 'Renameuser/SpecialRenameuser.i18n.php';
}

class ResignMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Resign';
	protected $id    = 'ext-resign';

	protected $functionName = 'efResignMessages';
	protected $messageFile  = 'Resign/SpecialResign.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class ReviewMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Review';
	protected $id    = 'ext-review';

	protected $arrName     = 'messages';
	protected $messageFile = 'Review/Review.i18n.php';
}

class SeealsoMessageGroup extends ExtensionMessageGroup {
	protected $label = 'See also';
	protected $id    = 'ext-seealso';

	protected $arrName     = 'messages';
	protected $messageFile = 'Seealso/Seealso.i18n.php';
}

class ScanSetMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Scan Set';
	protected $id    = 'ext-scanset';

	protected $arrName     = 'messages';
	protected $messageFile = 'ScanSet/ScanSet.i18n.php';
}

class SelectCategoryExtensionGroup extends MultipleFileMessageGroup {
	protected $label = 'Select Category';
	protected $id    = 'ext-selectcategory';

	protected $arrName      = 'messages';
	protected $messageFile  = 'SelectCategory/i18n/SelectCategory.i18n.php';
	protected $filePattern  = 'SelectCategory/i18n/SelectCategory.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportEnd   = ');';
}

class ShowProcesslistMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Show Processlist';
	protected $id    = 'ext-showprocesslist';

	protected $arrName     = 'messages';
	protected $messageFile = 'ShowProcesslist/ShowProcesslist.i18n.php';
}

class SignDocumentMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Sign Document';
	protected $id    = 'ext-signdocument';

	protected $arrName     = 'allMessages';
	protected $messageFile = 'SignDocument/SignDocument.i18n.php';

	protected $exportPrefix= "\t";
	protected $exportStart = '\'$CODE\' => array(';
	protected $exportLineP = "\t\t";
	protected $exportEnd   = "),";
}

class SignDocumentSpecialCreateMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Sign Document Special Create';
	protected $id    = 'ext-signdocumentspecialcreate';

	protected $arrName     = 'allMessages';
	protected $messageFile = 'SignDocument/SpecialCreateSignDocument.i18n.php';

	protected $exportPrefix= "\t";
	protected $exportStart = '\'$CODE\' => array(';
	protected $exportLineP = "\t\t";
	protected $exportEnd   = "),";
}

class SignDocumentSpecialMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Sign Document Special';
	protected $id    = 'ext-signdocumentspecial';

	protected $arrName     = 'allMessages';
	protected $messageFile = 'SignDocument/SpecialSignDocument.i18n.php';

	protected $exportPrefix= "\t";
	protected $exportStart = '\'$CODE\' => array(';
	protected $exportLineP = "\t\t";
	protected $exportEnd   = "),";
}

class SiteMatrixMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Site Matrix';
	protected $id    = 'ext-sitematrix';

	protected $arrName     = 'wgSiteMatrixMessages';
	protected $messageFile = 'SiteMatrix/SiteMatrix.i18n.php';
}

class SmoothGalleryExtensionGroup extends MultipleFileMessageGroup {
	protected $label = 'Smooth Gallery';
	protected $id    = 'ext-smoothgallery';

	protected $arrName      = 'messages';
	protected $messageFile  = 'SmoothGallery/SmoothGallery.i18n.php';
	protected $filePattern  = 'SmoothGallery/SmoothGallery.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportEnd   = ');';

	protected $ignored = array(
		'smoothgallery-pagetext',
	);
}

class SpamBlacklistMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Spam Blacklist';
	protected $id    = 'ext-spamblacklist';

	protected $arrName = 'messages';
	protected $messageFile  = 'SpamBlacklist/SpamBlacklist.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class SpamDiffToolMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Spam Diff Tool';
	protected $id    = 'ext-spamdifftool';

	protected $arrName     = 'messages';
	protected $messageFile = 'SpamDiffTool/SpamDiffTool.i18n.php';
}

class SpamRegExMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Spam Regex';
	protected $id    = 'ext-spamregex';

	protected $arrName     = 'messages';
	protected $messageFile = 'SpamRegex/SpamRegex.i18n.php';
}

class SpecialFileListMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Special File List';
	protected $id    = 'ext-specialfilelist';

	protected $arrName     = 'messages';
	protected $messageFile = 'SpecialFileList/SpecialFilelist.i18n.php';
}

class SpecialFormMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Special Form';
	protected $id    = 'ext-specialform';

	protected $arrName     = 'SpecialFormMessages';
	protected $messageFile = 'SpecialForm/SpecialForm.i18n.php';

	protected $exportPrefix= "\t";
	protected $exportStart = '\'$CODE\' => array(';
	protected $exportLineP = "\t\t";
	protected $exportEnd   = "),";

	protected $ignored = array(
		'formtemplatepattern',
	);
}

class StalePagesMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Stale Pages';
	protected $id    = 'ext-stalepages';

	protected $arrName     = 'messages';
	protected $messageFile = 'StalePages/StalePages.i18n.php';
}

class SyntaxHighlight_GeSHiMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Syntax Highlight GeSHi';
	protected $id    = 'ext-syntaxhighlightgeshi';

	protected $functionName = 'efSyntaxHighlight_GeSHiMessages';
	protected $messageFile  = 'SyntaxHighlight_GeSHi/SyntaxHighlight_GeSHi.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class TalkHereExtensionGroup extends MultipleFileMessageGroup {
	protected $label = 'Talk Here';
	protected $id    = 'ext-talkhere';

	protected $arrName      = 'messages';
	protected $messageFile  = 'TalkHere/TalkHere.i18n.php';
	protected $filePattern  = 'TalkHere/TalkHere.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportEnd   = ');';

	protected $ignored = array(
		'talkhere-headtext',
		'talkhere-afterinput',
		'talkhere-afterform',
	);
}

class TemplateLinkMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Template Link';
	protected $id    = 'ext-templatelink';

	protected $arrName     = 'allMessages';
	protected $messageFile = 'TemplateLink/TemplateLink.i18n.php';

	protected $exportPrefix= "\t";
	protected $exportStart = '\'$CODE\' => array(';
	protected $exportLineP = "\t\t";
	protected $exportEnd   = "),";
}

class TidyTabMessageGroup extends ExtensionMessageGroup {
	protected $label = 'TidyTab';
	protected $id    = 'ext-tidytab';

	protected $arrName     = 'messages';
	protected $messageFile = 'TidyTab/Tidy.i18n.php';
}

class ThrottleMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Throttle';
	protected $id    = 'ext-throttle';

	protected $arrName     = 'messages';
	protected $messageFile = 'Throttle/UserThrottle.i18n.php';
}

class TitleBlacklistMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Title Blacklist';
	protected $id    = 'ext-titleblacklist';

	protected $arrName     = 'messages';
	protected $messageFile = 'TitleBlacklist/TitleBlacklist.i18n.php';
}

class TodoMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Todo';
	protected $id    = 'ext-todo';

	protected $arrName     = 'messages';
	protected $messageFile = 'Todo/SpecialTodo.i18n.php';
}

class TodoTasksMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Todo Tasks';
	protected $id    = 'ext-todotasks';

	protected $arrName     = 'allMessages';
	protected $messageFile = 'TodoTasks/SpecialTaskList.i18n.php';

	protected $exportPrefix= "\t";
	protected $exportStart = '\'$CODE\' => array(';
	protected $exportLineP = "\t\t";
	protected $exportEnd   = "),";
}

class TranslateMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Translate';
	protected $id    = 'ext-translate';

	protected $arrName     = 'messages';
	protected $messageFile = 'Translate/Translate.i18n.php';
}

class UserContactLinksMessageGroup extends ExtensionMessageGroup {
	protected $label = 'User Contact Links';
	protected $id    = 'ext-usercontactlinks';

	protected $arrName     = 'messages';
	protected $messageFile = 'UserContactLinks/UserSignature.i18n.php';
}

class UserImagesMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'User Images';
	protected $id      = 'ext-userimages';

	protected $functionName = 'efUserImagesMessages';
	protected $messageFile  = 'UserImages/UserImages.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class UserMergeMessageGroup extends ExtensionMessageGroup {
	protected $label = 'User Merge';
	protected $id    = 'ext-usermerge';

	protected $arrName     = 'usermergeMessages';
	protected $messageFile = 'UserMerge/UserMerge.i18n.php';

	protected $exportPrefix= "\t";
	protected $exportStart = '\'$CODE\' => array(';
	protected $exportLineP = "\t\t";
	protected $exportEnd   = "),";
}

class UsernameBlacklistMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Username Blacklist';
	protected $id      = 'ext-usernameblacklist';

	protected $functionName = 'efUsernameBlacklistMessages';
	protected $messageFile  = 'UsernameBlacklist/UsernameBlacklist.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class UserRightsNotifMessageGroup extends ExtensionMessageGroup {
	protected $label = 'User Rights Notification';
	protected $id    = 'ext-userrightsnotif';

	protected $arrName     = 'messages';
	protected $messageFile = 'UserRightsNotif/UserRightsNotif.i18n.php';
}

class VoteMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Vote';
	protected $id      = 'ext-vote';

	protected $functionName = 'efVoteMessages';
	protected $messageFile  = 'Vote/Vote.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class WatchersMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Watchers';
	protected $id    = 'ext-watchers';

	protected $arrName     = 'messages';
	protected $messageFile = 'Watchers/Watchers.i18n.php';
}

class WebStoreMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Web Store';
	protected $id    = 'ext-webstore';

	protected $arrName     = 'messages';
	protected $messageFile = 'WebStore/WebStore.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= "\t";
	protected $exportLineP = "\t\t";
	protected $exportEnd   = '),';
}

class WhoIsWatchingMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Who Is Watching';
	protected $id    = 'ext-whoiswatching';

	protected $arrName     = 'allMessages';
	protected $messageFile = 'WhoIsWatching/SpecialWhoIsWatching.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= "\t";
	protected $exportLineP = "\t\t";
	protected $exportEnd   = '),';
}

class WikidataLanguageManagerMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Wikidata Language Manager';
	protected $id    = 'ext-wikidatalanguagemanager';

	protected $arrName     = 'wgLanguageManagerMessages';
	protected $messageFile = 'Wikidata/SpecialLanguages.i18n.php';
}

class WikidataOmegaWikiDataSearchMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Wikidata OmegaWiki Data Search';
	protected $id    = 'ext-wikidataomegawikidatasearch';

	protected $arrName     = 'wgDataSearchMessages';
	protected $messageFile = 'Wikidata/OmegaWiki/SpecialDatasearch.i18n.php';
}

class FreeColMessageGroup extends MessageGroup {

	protected $label = 'FreeCol (open source game)';
	protected $id    = 'out-freecol';
	protected $prefix= 'freecol-';

	private   $fileDir  = 'freecol/';

	public function __construct() {
		global $wgTranslateExtensionDirectory;
		$this->fileDir = $wgTranslateExtensionDirectory . 'freecol/';
	}

	public function getMessage( $key, $code ) {
		$this->load( $code );
		return isset( $this->mcache[$code][$key] ) ? $this->mcache[$code][$key] : null;
	}

	private function load( $code ) {
		if ( $code == 'en' ) {
			$filenameXX = $this->fileDir . "FreeColMessages.properties";
		} else {
			$filenameXX = $this->fileDir . "freecol_$code";
		}

		$linesXX = false;
		if ( file_exists( $filenameXX ) ) {
			$linesXX = file( $filenameXX );
		} else {
			# No such localisation, fall out
			return;
		}


		if ( !$linesXX) { return; }
		foreach ( $linesXX as $line ) {
			if ( !strpos( $line, '=' ) ) { continue; }
			list( $key, $string ) = explode( '=', $line, 2 );
			$this->mcache[$code][$this->prefix . $key] = trim($string);
		}

	}

	public function export( MessageCollection $messages, $code ) {
		global $wgSitename, $wgRequest;
		$txt = '# Exported on ' . wfTimestamp(TS_ISO_8601) . ' from ' . $wgSitename . "\n# " .
			$wgRequest->getFullRequestURL() . "\n#\n";

		$array = $this->makeExportArray( $messages );
		foreach ($array as $key => $translation) {
			list(, $key) = explode( '-', $key, 2);
			$txt .= $key . '=' . rtrim( $translation ) . "\n";
		}
		return $txt;
	}


	function fill( MessageCollection $messages, $code ) {
		$this->load( $code );

		foreach ( $messages->keys() as $key ) {
			if ( isset($this->mcache[$code][$key]) ) {
				$messages[$key]->infile = $this->mcache[$code][$key];
			}
		}
	}

	function getDefinitions() {
		$this->load('en');
		return $this->mcache['en'];
	}

}

class MessageGroups {
	public static function getGroup( $id ) {
		global $wgTranslateEC, $wgTranslateAC;
		if ( in_array( $id, $wgTranslateEC) ) {
			return new $wgTranslateAC[$id];
		} else {
			return null;
		}
	}

	public $classes = array();
	private function __construct() {
		global $wgTranslateEC, $wgTranslateAC;
		foreach ($wgTranslateAC as $id => $class) {
			if ( in_array( $id, $wgTranslateEC, true ) ) {
				$this->classes[$id] = new $class;
			}
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
