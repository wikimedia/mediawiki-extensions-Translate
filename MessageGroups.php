<?php

abstract class MessageGroup {
	protected $label = 'none';
	protected $id    = 'none';
	protected $mcache= array();

	/** Returns a human readable name of this class */
	function getLabel() { return $this->label; }

	/** Returns a unique id used to identify this class */
	function getId() { return $this->id; }

	/** Is this a real message group or just some meta group */
	function isMeta() { return false; }

	/** Message Classes can fill up missing properties */
	function fill( &$array, $code ) {}

	/** Called when user exports the messages */
	abstract function export( &$array, $code );

	public function exportToFile( &$array, $code, $authors ) {
		return $this->export( $array, $code );
	}

	/** Return array of key => messages for requested language, or empty array */
	abstract function getDefinitions();

	abstract function getMessage( $key, $code );

	function getMessageGrouping() {
		return array( $this->getLabel(), array_keys($this->getDefinitions()) );
	}

	function getMessageFile( $code ) { return ''; }

	/** Checks if the message should be exported. Returns false if not,
	 *  true if yes and updates $comment.
	 */
	function validateLine($m, &$comment) {
		if ( $m['ignored'] ) { return false; }
		$fallback = isset($m['fallback']) ? $m['fallback'] : $m['definition'];

		$translation = $m['database'];
		if ( $translation === null ) {
			$translation = $m['infile'];
		}

		if ( $translation === null ) { return false; }

		if ( strpos($translation, TRANSLATE_FUZZY) !== false ) { return false; }

		if ( $m['optional'] ) {
			if ( $translation !== $fallback ) {
				$comment = "#optional";
				return true;
			} else {
				return false;
			}
		}
		if ( $translation === $fallback ) {
			if ( $m['pageexists'] ) {
				$comment = "#identical but defined";
				return true;
			} else {
				return false;
			}
		}

		return true;
	}

	public function makeMessageArray( &$array ) {
		$new = array();
		foreach( $array as $key => $m ) {
			# CASE1: ignored
			if ( $m['ignored'] ) continue;

			$translation = $m['database'] !== null ? $m['database'] : $m['infile'];
			# CASE2: no translation
			if ( $translation === null ) continue;

			# CASE3: optional messages; accept only if different
			if ( $m['optional'] && $translation === $m['definition'] ) continue;

			# CASE4: don't export non-translations unless translated in wiki
			if( !$m['pageexists'] && $translation === $m['definition'] ) continue;

			# Remove fuzzy markings before export
			$translation = str_replace( TRANSLATE_FUZZY, '', $translation );

			# Otherwise it's good
			$new[$key] = $translation;
		}

		return $new;
	}

	/** Returns php and whitespace formatted key => message line or null */
	function exportLine($key, $m, $pad = false) {
		$comment = '';
		$result = $this->validateLine($m, $comment);
		if ( $result === false ) { return null; }

		$translation = $m['database'] ? $m['database'] : $m['infile'];

		$key = "'$key' ";
		if ($pad) while ( strlen($key) < $pad ) { $key .= ' '; }
		$txt = "$key=> '" . preg_replace( "/(?<!\\\\)'/", "\'", $translation) . "',$comment\n";
		return $txt;
	}

	public function fillBools( &$array ) {}
}

class CoreMessageGroup extends MessageGroup {
	protected $label = 'MediaWiki messages';
	protected $id    = 'core';

	public function getMessageFile( $code ) { return "Messages$code.php"; }

	public function getMessage( $key, $code ) {
		$messages = $this->loadMessages( $code );
		return isset( $messages[$key] ) ? $messages[$key] : null;
	}

	function export( &$array, $code ) {
		$x = MessageWriter::writeMessagesArray( $this->makeMessageArray( &$array ), false );
		return $x[0];
	}

	public function exportToFile( &$array, $code, $authors ) {
		$x = MessageWriter::writeMessagesArray( $this->makeMessageArray( &$array ), false );
		$name = TranslateUtils::getLanguageName( $code );
		$native = TranslateUtils::getLanguageName( $code, true );
		$authors = array_unique( array_merge( $this->getAuthorsFromFile( $code ), $authors ) );
		$translators = $this->formatAuthors( $authors );
		$other = $this->getOther( $code );
		return <<<CODE
<?php
/** $name ($native)
 *
 * @addtogroup Language
 *
$translators
 */

$other

$x[0]
CODE;
	}


	function getDefinitions() {
		return Language::getMessagesFor( 'en' );
	}

	public function fillBools( &$array ) {
		$l = new languages();

		foreach ($l->getOptionalMessages() as $optMsg) {
			if (!isset($array[$optMsg])) continue;
			$array[$optMsg]['optional'] = true;
		}

		foreach ($l->getIgnoredMessages() as $optMsg) {
			if (!isset($array[$optMsg])) continue;
			$array[$optMsg]['ignored'] = true;
		}
	}

	/* Cache of read messages */
	private static $mCache = array();
	private function loadMessages( $code ) {
		if ( !isset(self::$mCache[$code]) ) {
			$file = Language::getMessagesFileName( $code );
			if ( !file_exists( $file ) ) {
				self::$mCache[$code] = null;
			} else {
				require( $file );
				return self::$mCache[$code] = isset( $messages ) ? $messages : null;
			}
		}

		return self::$mCache[$code];
	}

	function fill( &$array, $code ) {
		$infile = $this->loadMessages( $code );
		$infbfile = null;
		if ( Language::getFallbackFor( $code ) ) {
			$infbfile = $this->loadMessages( Language::getFallbackFor( $code ) );
		}

		foreach ( $array as $key => $value ) {
			if ( isset($infile[$key]) ) {
				$array[$key]['infile'] = $infile[$key];
			}
			if ( $infbfile && isset($infbfile[$key]) ) {
				$array[$key]['fallback'] = $infbfile[$key];
			}
		}
	}

	private function formatAuthors( $authors ) {
		$s = array();
		foreach ( $authors as $a ) {
			$s[] = " * @author $a";
		}
		return implode( "\n", $s );
	}

	private function getAuthorsFromFile( $code ) {
		$filename = Language::getMessagesFileName( $code );
		if ( !file_exists( $filename ) ) { return array(); }
		$contents = file_get_contents( $filename );
		$m = array();
		$count = preg_match_all( '/@author (.*)/', $contents, $m );
		return $m[1];
	}

	private function getOther( $code ) {
		$filename = Language::getMessagesFileName( $code );
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
	protected $arrName      = false;
	protected $mcache     = null;
	protected $functionName = false;
	protected $messageFile  = null;

	protected $exportStart = '$$ARRAY[\'$CODE\'] = array(';
	protected $exportEnd   = ');';
	protected $exportPrefix= '';
	protected $exportPad   = false;
	protected $exportLineP = "\t";

	public function getLabel() { return $this->label . " (mw ext)"; }

	public function getMessageFile( $code ) { return $this->messageFile; }

	public function getMessage( $key, $code ) {
		$this->load( $code );
		return isset( $this->mcache[$code][$key] ) ? $this->mcache[$code][$key] : null;
	}

	protected function load( $code ) {
		if ( isset($this->mcache[$code]) ) return;
		$this->mcache = $this->loadMessages( $code );
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

	protected function loadMessages( $code ) {
		$path = $this->getPath( $code );

		if ( $this->arrName ) {
			return $this->loadFromVariable( $path );
		} elseif ( $this->functionName ) {
			return $this->loadFromFunction( $path );
		}

	}

	private function loadFromVariable( $path ) {
		if ( file_exists( $path ) ) {
			include( $path );
			if ( isset( ${$this->arrName} ) ) {
				return ${$this->arrName};
			} else {
				throw new MWException( "Variable {$this->arrName} is not defined" );
			}
		}
	}

	private function loadFromFunction( $path ) {
		if ( function_exists( $this->functionName ) ) {
			return call_user_func( $this->functionName );
		} elseif ( file_exists( $path ) ) {
			include( $path );
			if ( function_exists( $this->functionName ) ) {
				return call_user_func( $this->functionName );
			} else {
				throw new MWException( "Function {$this->functionName} is not defined" );
			}
		}
	}

	function export( &$array, $code ) {
		$txt = $this->exportPrefix . str_replace(
			array( '$ARRAY', '$CODE' ),
			array( $this->arrName, $code ),
			$this->exportStart ) . "\n";

		foreach ($this->mcache['en'] as $key => $msg) {
			if ( !isset( $array[$key] ) ) { continue; }
			$line = $this->exportLine($key, $array[$key], $this->exportPad);
			if ( $line !== null ) {
				$txt .= $this->exportLineP . $line;
			}
		}
		$txt .= $this->exportPrefix . $this->exportEnd;
		return $txt;
	}

	function getDefinitions() {
		$this->load( 'en' );
		return $this->mcache['en'];
	}

	function fill( &$array, $code ) {
		$this->load( $code );

		$fbcode = Language::getFallbackFor( $code );
		if ( $fbcode ) {
			$this->load( $fbcode );
		}

		foreach ( $array as $key => $value ) {
			if ( isset($this->mcache[$code][$key]) ) {
				$array[$key]['infile'] = $this->mcache[$code][$key];
			}
			if ( isset($this->mcache[$fbcode][$key]) ) {
				$array[$key]['infbfile'] = $this->mcache[$fbcode][$key];
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
		$this->mcache[$code] = $this->loadMessages( $code );
	}

	protected function getPath( $code ) {
		if ( $code === 'en' ) {
			return parent::getPath( 'en' );
		}

		global $wgTranslateExtensionDirectory;
		$fullPath = false;
		if ( $this->filePattern ) {
			$filename = str_replace( '$CODE', $code, $this->filePattern );
			$fullPath = $wgTranslateExtensionDirectory . $filename;
		}
		return $fullPath;
	}

}

class Core500MessageGroup extends CoreMessageGroup {
	protected $label = 'MediaWiki messages top 500';
	protected $id    = 'core-500';

	public function isMeta() { return true; }
	public function getMessageFile( $code ) { return '-'; }
	function export( &$array, $code ) { return '-'; }
	public function exportToFile( &$array, $code, $authors ) { return '-'; }

	function getDefinitions() {
		$data = file_get_contents( dirname(__FILE__) . '/wikimedia-500.txt' );
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

	private $classes = null;

	public function isMeta() { return true; }

	private function init() {
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

	function export( &$array, $code ) {
		$this->init();
		$ret = '';
		foreach ( $this->classes as $class ) {
			$ret .= $class->export( &$array, $code ) . "\n\n\n";
		}
		return $ret;
	}

	function fill( &$array, $code ) {
		$this->init();
		foreach ( $this->classes as $class ) {
			$class->fill( &$array, $code );
		}
	}

	function fillBools( &$array ) {
		$this->init();
		foreach ( $this->classes as $class ) {
			$class->fillBools( &$array );
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

	protected $exportPad   = 26;
}

class AssertEditMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Assert Edit';
	protected $id    = 'ext-assertedit';

	protected $arrName     = 'messages';
	protected $messageFile = 'AssertEdit/AssertEdit.i18n.php';

	protected $exportPad   = 22;
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

	protected $exportPad   = 19;
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

	protected $exportPad   = 40;
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

	protected $exportPad   = 26;

	function fillBools( &$array ) {
		$array['boardvote_footer']['ignored'] = true;
	}
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

	protected $exportPad   = 39;
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

	function fillBools( &$array ) {
		$array['changeauthor-short']['ignored'] = true;
		$array['changeauthor-logpagetext']['ignored'] = true;
	}
}

class CheckUserMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Check User';
	protected $id      = 'ext-checkuser';

	protected $arrName     = 'wgCheckUserMessages';
	protected $messageFile = 'CheckUser/CheckUser.i18n.php';

	protected $exportPad   = 25;
}

class ChemFunctionsMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Chemistry';
	protected $id    = 'ext-chemistry';

	protected $arrName     = 'wgChemFunctions_Messages';
	protected $messageFile = 'Chemistry/ChemFunctions.i18n.php';

	function fillBools( &$array ) {
		$array['ChemFunctions_SearchExplanation']['ignored'] = true;
	}
}

class CiteSpecialMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Cite (special page)';
	protected $id      = 'ext-citespecial';

	protected $arrName     = 'wgSpecialCiteMessages';
	protected $messageFile = 'Cite/SpecialCite.i18n.php';

	protected $exportPad   = 20;

	function fillBools( &$array ) {
		$array['cite_text']['ignored'] = true;
	}
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

	protected $exportPad   = 30;
}

class ConfirmEditMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Confirm Edit';
	protected $id    = 'ext-confirmedit';

	protected $arrName     = 'messages';
	protected $messageFile = 'ConfirmEdit/ConfirmEdit.i18n.php';

	protected $exportPad   = 30;
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
	protected $label   = 'Contributionseditcount';
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

	protected $exportPad   = 30;
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

	protected $exportPad   = 23;
}

class DismissableSiteNoticeMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Dismissable SiteNotice';
	protected $id    = 'ext-dismissablesitenotice';

	protected $arrName     = 'wgDismissableSiteNoticeMessages';
	protected $messageFile = 'DismissableSiteNotice/DismissableSiteNotice.i18n.php';

	function fillBools( &$array ) {
		$array['sitenotice_id']['ignored'] = true;
	}
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

	protected $exportPad   = 14;
}

class ExpandTemplatesMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Expand Templates';
	protected $id    = 'ext-expandtemplates';

	protected $arrName     = 'wgExpandTemplatesMessages';
	protected $messageFile = 'ExpandTemplates/ExpandTemplates.i18n.php';

	protected $exportPad   = 35;
}

class FancyCaptchaMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Fancy Captcha';
	protected $id      = 'ext-fancycaptcha';

	protected $arrName = 'messages';
	protected $messageFile  = 'ConfirmEdit/FancyCaptcha.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
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

	protected $exportPad   = 28;
}

class FormatEmailMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Format Email';
	protected $id    = 'ext-formatemail';

	protected $arrName     = 'messages';
	protected $messageFile = 'FormatEmail/FormatEmail.i18n.php';

	function fillBools( &$array ) {
		$array['email_header']['ignored'] = true;
	}
}

class FilePathMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'File Path';
	protected $id      = 'ext-filepath';

	protected $arrName     = 'wgFilepathMessages';
	protected $messageFile = 'Filepath/SpecialFilepath.i18n.php';

	protected $exportPad   = 18;
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

class ImageMapMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Image Map';
	protected $id    = 'ext-imagemap';

	protected $functionName = 'efImageMapMessages';
	protected $messageFile  = 'ImageMap/ImageMap.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';

	protected $exportPad   = 32;

	function fillBools( &$array ) {
		$array['imagemap_desc_types']['ignored'] = true;
	}
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

	protected $exportPad   = 32;
}

class InputBoxMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Input Box';
	protected $id    = 'ext-inputbox';

	protected $functionName = 'efInputBoxMessages';
	protected $messageFile  = 'inputbox/InputBox.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';

	protected $exportPad   = 26;
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

	protected $exportPad   = 24;

	function fillBools( &$array ) {
		$array['interwiki_logentry']['ignored'] = true;
		$array['interwiki_url']['optional'] = true;
	}
}

class LinkSearchMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Link Search';
	protected $id    = 'ext-linksearch';

	protected $arrName     = 'wgLinkSearchMessages';
	protected $messageFile = 'LinkSearch/LinkSearch.i18n.php';

	protected $exportPad   = 19;
}

class LiquidThreadsMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Liquid Threads';
	protected $id    = 'ext-liquidthreads';

	protected $arrName     = 'messages';
	protected $messageFile = 'LiquidThreads/Lqt.i18n.php';
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

	protected $exportPad   = 24;

	function fillBools( &$array ) {
		$array['searchaliases']['ignored'] = true;
		$array['searchnearmatch']['ignored'] = true;
	}
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

	protected $exportPad   = 26;
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

class MakeValidateMessageGroup extends MultipleFileMessageGroup {
	protected $label = 'Make Validate';
	protected $id    = 'ext-makevalidate';

	protected $arrName     = 'messages';
	protected $messageFile = 'FlaggedRevs/Language/Makevalidate.i18n.en.php';
	protected $filePattern = 'FlaggedRevs/Language/Makevalidate.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportEnd   = ');';


	protected $exportPad   = 32;
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

	protected $exportPad   = 31;
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

	protected $exportPad   = 20;
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
	protected $exportPad   = 27;

	function fillBools( &$array ) {
		$array['newuserlogentry']['ignored'] = true;
		$array['newuserlog-create-text']['ignored'] = true;
	}
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

	protected $exportPad   = 21;
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

	function fillBools( &$array ) {
		$array['ogg-player-cortado']['optional'] = true;
		$array['ogg-player-vlc-mozilla']['optional'] = true;
		$array['ogg-player-vlc-activex']['optional'] = true;
		$array['ogg-player-quicktime-mozilla']['optional'] = true;
		$array['ogg-player-quicktime-activex']['optional'] = true;
	}
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

class PicturePopupMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'PicturePopup';
	protected $id      = 'ext-picturepopup';

	protected $functionName = 'efPicturePopupMessages';
	protected $messageFile  = 'PicturePopup/PicturePopup.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
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

	function fillBools( &$array ) {
		$array['player-pagetext']['ignored'] = true;
	}
}

class PostCommentMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Post Comment';
	protected $id    = 'ext-postcomment';

	protected $arrName     = 'messages';
	protected $messageFile = 'Postcomment/SpecialPostcomment.i18n.php';
}

class ProfileMonitorMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'ProfileMonitor';
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

	protected $exportPad   = 28;
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

class RegexBlockMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Regex Block';
	protected $id    = 'ext-regexblock';

	protected $arrName     = 'messages';
	protected $messageFile = 'regexBlock/regexBlock.i18n.php';
}

class RenameUserMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Rename User';
	protected $id    = 'ext-renameuser';

	protected $arrName     = 'wgRenameuserMessages';
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

class SpecialCreateSignDocumentMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Special Create Sign Document';
	protected $id    = 'ext-specialcreatesigndocument';

	protected $arrName     = 'allMessages';
	protected $messageFile = 'SignDocument/SpecialCreateSignDocument.i18n.php';

	protected $exportPrefix= "\t";
	protected $exportStart = '\'$CODE\' => array(';
	protected $exportLineP = "\t\t";
	protected $exportEnd   = "),";
}

class SpecialSignDocumentMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Special Sign Document';
	protected $id    = 'ext-specialsigndocument';

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

	protected $exportPad   = 13;
}

class SmoothGalleryExtensionGroup extends MultipleFileMessageGroup {
	protected $label = 'Smooth Gallery';
	protected $id    = 'ext-smoothgallery';

	protected $arrName      = 'messages';
	protected $messageFile  = 'SmoothGallery/SmoothGallery.i18n.php';
	protected $filePattern  = 'SmoothGallery/SmoothGallery.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportEnd   = ');';

	function fillBools( &$array ) {
		$array['smoothgallery-pagetext']['ignored'] = true;
	}
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

class SpecialFormMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Special Form';
	protected $id    = 'ext-specialform';

	protected $arrName     = 'SpecialFormMessages';
	protected $messageFile = 'SpecialForm/SpecialForm.i18n.php';

	protected $exportPrefix= "\t";
	protected $exportStart = '\'$CODE\' => array(';
	protected $exportLineP = "\t\t";
	protected $exportEnd   = "),";

	function fillBools( &$array ) {
		$array['formtemplatepattern']['ignored'] = true;
	}
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

	function fillBools( &$array ) {
		$array['talkhere-headtext']['ignored'] = true;
		$array['talkhere-afterinput']['ignored'] = true;
		$array['talkhere-afterform']['ignored'] = true;
	}
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

class TitleBlacklistMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Title Blacklist';
	protected $id      = 'ext-titleblacklist';

	protected $functionName = 'efGetTitleBlacklistMessages';
	protected $messageFile  = 'TitleBlacklist/TitleBlacklist.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= "\t\t";
	protected $exportLineP = "\t\t\t";
	protected $exportEnd   = '),';
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

	protected $exportPad   = 26;
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

	protected $exportPad   = 26;
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

	protected $exportPad   = 25;
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
	protected $exportPad   = 30;
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

	protected $mcache = array();
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

	public function export( &$array, $code ) {
		global $wgSitename, $wgRequest;
		$txt = '# Exported on ' . wfTimestamp(TS_ISO_8601) . ' from ' . $wgSitename . "\n# " .
			$wgRequest->getFullRequestURL() . "\n#\n";

		foreach ($array as $key => $m) {
			list(, $key) = explode( '-', $key, 2);
			$comment = '';
			$result = $this->validateLine($m, $comment);
			if ( $result === false ) { continue; }
			if ( is_string( $result ) ) { continue; }

			$translation = $m['database'] ? $m['database'] : $m['infile'];

			$txt .= $key . '=' . rtrim( $translation ) . "\n";
		}
		return $txt;
	}


	function fill( &$array, $code ) {
		$this->load( $code );

		foreach ( $array as $key => $value ) {
			$array[$key]['definition'] = $this->mcache['en'][$key];
			if ( isset($this->mcache[$code][$key]) ) {
				$array[$key]['infile'] = $this->mcache[$code][$key];
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

