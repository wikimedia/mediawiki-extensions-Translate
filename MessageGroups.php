<?php

abstract class MessageGroup {
	protected $load = false;

	protected $label = 'none';
	protected $id    = 'none';

	function __construct( $load ) {
		$this->load = $load;
	}

	/** Returns a human readable name of this class */
	function getLabel() { return $this->label; }

	/** Returns a unique id used to identify this class */
	function getId() { return $this->id; }

	/** Message Classes can fill up missing properties */
	function fill( &$array, $code ) {}

	/** Called when user exports the messages */
	abstract function export( &$array, $code );

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
	protected $label = 'Core system messages';
	protected $id    = 'core';

	public function getMessageFile( $code ) { return "Messages$code.php"; }

	public function getMessage( $key, $code ) {
		$messages = $this->getMessagesInFile( $code );
		return isset( $messages[$key] ) ? $messages[$key] : null;
	}

	function export( &$array, $code ) {
		$txt = "\$messages = array(\n";
		foreach( $array as $key => $m ) {
			$txt .= $this->exportLine($key, $m, 30);
		}
		$txt .= ");";
		return $txt;
	}

	function getDefinitions() {
		return Language::getMessagesFor( 'en' );
	}

	public function fillBools( &$array ) {
		$l = new languages();

		foreach ($l->getOptionalMessages() as $optMsg) {
			$array[$optMsg]['optional'] = true;
		}

		foreach ($l->getIgnoredMessages() as $optMsg) {
			$array[$optMsg]['ignored'] = true;
		}
	}

	private function getMessagesInFile( $code ) {
		$file = Language::getMessagesFileName( $code );
		if ( !file_exists( $file ) ) {
			return null;
		} else {
			require( $file );
			return isset( $messages ) ? $messages : null;
		}
	}

	function fill( &$array, $code ) {
		$infile = $this->getMessagesInFile( $code );
		$infbfile = null;
		if ( Language::getFallbackFor( $code ) ) {
			$infbfile = $this->getMessagesInFile( Language::getFallbackFor( $code ) );
		}

		foreach ( $array as $key => $value ) {
			if ( isset($infile[$key]) ) {
				$array[$key]['infile'] = $infile[$key];
			}
			if ( isset($infbfile[$key]) ) {
				$array[$key]['fallback'] = $infbfile[$key];
			}
		}
	}
}

abstract class ExtensionMessageGroup extends MessageGroup {
	protected $arrName      = false;
	protected $msgArray     = null;
	protected $functionName = false;
	protected $messageFile  = null;

	protected $exportStart = '$$ARRAY[\'$CODE\'] = array(';
	protected $exportEnd   = ');';
	protected $exportPrefix= '';
	protected $exportPad   = false;
	protected $exportLineP = "\t";

	public function getMessageFile( $code ) { return $this->messageFile; }

	public function getMessage( $key, $code ) {
		$this->load( $code );
		return isset( $this->msgArray[$code][$key] ) ? $this->msgArray[$code][$key] : null;
	}

	protected function load( $code = '' ) {
		if ( $code === '' ) throw new MWException( 'load failed1' );;
		if ( isset($this->msgArray[$code]) ) return;

		$messages = $this->loadMessages( $code );
		if ( $messages !== null ) {
			$this->msgArray = $messages;
			return true;
		} else {
				throw new MWException( 'No messages returned for extension' . $this->getLabel() );
		}
		return false;
	}

	protected function getPath( $code = '' ) {
		global $wgTranslateExtensionDirectory;
		if ( $this->messageFile ) {
			$fullPath = $wgTranslateExtensionDirectory . $this->messageFile;
		} else {
			$fullPath = false;
		}
		return $fullPath;
	}

	protected function loadMessages( $code = '' ) {
		$messages = null;
		$path = $this->getPath( $code );

		if ( $this->arrName ) {
			return $this->loadFromVariable( $path );
		} elseif ( $this->functionName ) {
			return $this->loadFromFunction( $path );
		}

	}

	private function loadFromVariable( $path ) {
		if ( $this->load && $path && file_exists( $path ) ) {
			include( $path );
			if ( isset( ${$this->arrName} ) ) {
				return ${$this->arrName};
			}
		}
	}

	private function loadFromFunction( $path ) {
		if ( function_exists( $this->functionName ) ) {
			return call_user_func( $this->functionName );
		} elseif ( $this->load && $path && file_exists( $path ) ) {
			include( $path );
			if ( function_exists( $this->functionName ) ) {
				return call_user_func( $this->functionName );
			}
		}
	}

	function export( &$array, $code ) {
		$txt = $this->exportPrefix . str_replace(
			array( '$ARRAY', '$CODE' ),
			array( $this->arrName, $code ),
			$this->exportStart ) . "\n";

		foreach ($this->msgArray['en'] as $key => $msg) {
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
		if (!isset($this->msgArray['en'])) {
			throw new MWException( 'Missing messages for extension ' . $this->getId() );
		}
		return $this->msgArray['en'];
	}

	function fill( &$array, $code ) {
		$this->load( $code );

		$fbcode = Language::getFallbackFor( $code );
		if ( $fbcode ) {
			$this->load( $fbcode );
		}

		foreach ( $array as $key => $value ) {
			if ( isset($this->msgArray[$code][$key]) ) {
				$array[$key]['infile'] = $this->msgArray[$code][$key];
			}
			if ( isset($this->msgArray[$fbcode][$key]) ) {
				$array[$key]['infbfile'] = $this->msgArray[$fbcode][$key];
			}
		}
	}

}

class MultipleFileMessageGroup extends ExtensionMessageGroup {
	protected $filePattern = false;

	public function getMessageFile( $code ) {
		return str_replace( '$CODE', $code, $this->filePattern );
	}


	protected function load( $code = '' ) {
		if ( $code === '' ) return;
		if ( isset($this->msgArray[$code]) ) return;

		$messages = $this->loadMessages( $code );
		if ( $messages !== null ) {
			$this->msgArray[$code] = $messages;
			return true;
		}

		return false;
	}

	protected function getPath( $code = '' ) {
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

class AllMediawikiExtensionsGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: All extensions';
	protected $id    = 'ext-0-all';

	private $classes = null;

	private function init() {
		if ( $this->classes === null ) {
			$this->classes = MessageGroups::singleton()->getGroups();
			foreach ( $this->classes as $index => $class ) {
				if ( (strpos( $class->getId(), 'ext-' ) !== 0) || (strpos( $class->getId(), 'ext-0' ) === 0) ) {
					unset( $this->classes[$index] );
				}
			}
		}
	}

	protected function load( $code = '' ) {
		if ( $code === '' ) return;

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

class AjaxShowEditorsMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Ajax Show Editors';
	protected $id    = 'ext-ajaxshoweditors';

	protected $arrName     = 'wgAjaxShowEditorsMessages';
	protected $messageFile = 'AjaxShowEditors/AjaxShowEditors.i18n.php';
}

class AntiSpoofMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Anti Spoof';
	protected $id    = 'ext-antispoof';

	protected $arrName     = 'wgAntiSpoofMessages';
	protected $messageFile = 'AntiSpoof/AntiSpoof_i18n.php';

	protected $exportPad   = 26;
}

class AsksqlMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Asksql';
	protected $id    = 'ext-asksql';

	protected $functionName = 'efAsksqlMessages';
	protected $messageFile  = 'Asksql/Asksql.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';

	protected $exportPad   = 19;
}

class BackAndForthMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Back and Forth';
	protected $id    = 'ext-backandforth';

	protected $functionName = 'efBackAndForthMessages';
	protected $messageFile  = 'BackAndForth/BackAndForth.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class BadImageMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Bad Image';
	protected $id    = 'ext-badimage';

	protected $functionName = 'efBadImageMessages';
	protected $messageFile  = 'BadImage/BadImage.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class BlockTitlesMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Block Titles';
	protected $id    = 'ext-blocktitles';

	protected $functionName = 'efBlockTitlesMessages';
	protected $messageFile  = 'BlockTitles/BlockTitles.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class BoardVoteMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: Board Vote';
	protected $id      = 'ext-boardvote';

	protected $arrName     = 'wgBoardVoteMessages';
	protected $messageFile = 'BoardVote/BoardVote.i18n.php';

	protected $exportPad   = 26;

	function fillBools( &$array ) {
		$array['boardvote_footer']['ignored'] = true;
	}
}

class BookInformationMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: Book Information';
	protected $id      = 'ext-bookinformation';

	protected $functionName = 'efBookInformationMessages';
	protected $messageFile  = 'BookInformation/BookInformation.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class CategoryTreeExtensionGroup extends MultipleFileMessageGroup {
	protected $label = 'Extension: Category Tree';
	protected $id    = 'ext-categorytree';

	protected $arrName      = 'messages';
	protected $messageFile  = 'CategoryTree/CategoryTree.i18n.php';
	protected $filePattern  = 'CategoryTree/CategoryTree.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportEnd   = ');';

}

class CentralAuthMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: Central Auth';
	protected $id      = 'ext-centralauth';

	protected $arrName     = 'wgCentralAuthMessages';
	protected $messageFile = 'CentralAuth/CentralAuth.i18n.php';

	protected $exportPad   = 39;
}

class CheckUserMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: Check User';
	protected $id      = 'ext-checkuser';

	protected $arrName     = 'wgCheckUserMessages';
	protected $messageFile = 'CheckUser/CheckUser.i18n.php';

	protected $exportPad   = 25;
}

class ChemFunctionsMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Chemistry';
	protected $id    = 'ext-chemistry';

	protected $arrName     = 'wgChemFunctions_Messages';
	protected $messageFile = 'Chemistry/ChemFunctions.i18n.php';
}

class CiteSpecialMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: Cite (special page)';
	protected $id      = 'ext-citespecial';

	protected $arrName     = 'wgSpecialCiteMessages';
	protected $messageFile = 'Cite/SpecialCite.i18n.php';

	protected $exportPad   = 20;

	function fillBools( &$array ) {
		$array['cite_text']['ignored'] = true;
	}
}

class ConfirmAccountMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: Confirm Account';
	protected $id      = 'ext-confirmaccount';

	protected $arrName     = 'wgConfirmAccountMessages';
	protected $messageFile = 'ConfirmAccount/ConfirmAccount.i18n.php';

	protected $exportPad   = 30;
}

class ConfirmEditMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Confirm Edit';
	protected $id    = 'ext-confirmedit';

	protected $arrName     = 'wgConfirmEditMessages';
	protected $messageFile = 'ConfirmEdit/ConfirmEdit.i18n.php';

	protected $exportPad   = 30;
}

class ContactPageExtensionGroup extends MultipleFileMessageGroup {
	protected $label = 'Extension: Contact Page';
	protected $id    = 'ext-contactpage';

	protected $arrName      = 'messages';
	protected $messageFile  = 'ContactPage/ContactPage.i18n.php';
	protected $filePattern  = 'ContactPage/ContactPage.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportLineP = '';
	protected $exportEnd   = ');';

}

class ContributionScoresMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Contribution Scores';
	protected $id    = 'ext-contributionscores';

	protected $arrName     = 'allMessages';
	protected $messageFile = 'ContributionScores/ContributionScores.i18n.php';

	protected $exportLineP = "\t";
}

class ContributionseditcountMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: Contributionseditcount';
	protected $id      = 'ext-contributionseditcount';

	protected $functionName = 'efContributionseditcountMessages';
	protected $messageFile  = 'Contributionseditcount/Contributionseditcount.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportEnd   = '),';
}

class ContributorsMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: Contributors';
	protected $id      = 'ext-contributors';

	protected $functionName = 'efContributorsMessages';
	protected $messageFile  = 'Contributors/Contributors.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class CountEditsMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: Count Edits';
	protected $id      = 'ext-countedits';

	protected $functionName = 'efCountEditsMessages';
	protected $messageFile  = 'CountEdits/CountEdits.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class CrossNamespaceLinksMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Cross Namespace Links';
	protected $id    = 'ext-crossnamespacelinks';

	protected $arrName     = 'wgCrossNamespaceLinksMessages';
	protected $messageFile = 'CrossNamespaceLinks/SpecialCrossNamespaceLinks.i18n.php';

	protected $exportPad   = 30;
}

class DeletedContribsMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Deleted Contributions';
	protected $id    = 'ext-deletedcontribs';

	protected $arrName     = 'wgDeletedContribsMessages';
	protected $messageFile = 'DeletedContributions/DeletedContributions.i18n.php';
}

class DesysopMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Desysop';
	protected $id    = 'ext-desysop';

	protected $arrName     = 'wgDesysopMessages';
	protected $messageFile = 'Desysop/SpecialDesysop.i18n.php';

	protected $exportPad   = 23;
}

class DismissableSiteNoticeMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Dismissable SiteNotice';
	protected $id    = 'ext-dismissablesitenotice';

	protected $arrName     = 'wgDismissableSiteNoticeMessages';
	protected $messageFile = 'DismissableSiteNotice/DismissableSiteNotice.i18n.php';

	function fillBools( &$array ) {
		$array['sitenotice_id']['ignored'] = true;
	}
}

class DuplicatorMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Duplicator';
	protected $id    = 'ext-duplicator';

	protected $functionName = 'efDuplicatorMessages';
	protected $messageFile  = 'Duplicator/Duplicator.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class EditcountMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Edit Count';
	protected $id    = 'ext-editcount';

	protected $functionName = 'efSpecialEditcountMessages';
	protected $messageFile  = 'Editcount/SpecialEditcount.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class ExpandTemplatesMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Expand Templates';
	protected $id    = 'ext-expandtemplates';

	protected $arrName     = 'wgExpandTemplatesMessages';
	protected $messageFile = 'ExpandTemplates/ExpandTemplates.i18n.php';

	protected $exportPad   = 35;
}

class FancyCaptchaMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: Fancy Captcha';
	protected $id      = 'ext-fancycaptcha';

	protected $functionName = 'efFancyCaptchaMessages';
	protected $messageFile  = 'ConfirmEdit/FancyCaptcha.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class FlaggedRevsMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: Flagged Revs';
	protected $id      = 'ext-flaggedrevs';

	protected $arrName     = 'RevisionreviewMessages';
	protected $messageFile = 'FlaggedRevs/FlaggedRevsPage.i18n.php';

	protected $exportStart = '$RevisionreviewMessages[\'$CODE\'] = array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = ');';

	protected $exportPad   = 24;
}

class FilePathMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: File Path';
	protected $id      = 'ext-filepath';

	protected $arrName     = 'wgFilepathMessages';
	protected $messageFile = 'Filepath/SpecialFilepath.i18n.php';

	protected $exportPad   = 18;
}

class GadgetsExtensionGroup extends MultipleFileMessageGroup {
	protected $label = 'Extension: Gadgets';
	protected $id    = 'ext-gadgets';

	protected $arrName      = 'messages';
	protected $messageFile  = 'Gadgets/Gadgets.i18n.php';
	protected $filePattern  = 'Gadgets/Gadgets.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportEnd   = ');';
}

class GiveRollbackMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: Give Rollback';
	protected $id      = 'ext-giverollback';

	protected $functionName = 'efGiveRollbackMessages';
	protected $messageFile  = 'GiveRollback/GiveRollback.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class ImageMapMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Image Map';
	protected $id    = 'ext-imagemap';

	protected $functionName = 'efImageMapMessages';
	protected $messageFile  = 'ImageMap/ImageMap.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';

	protected $exportPad   = 32;

	function fillBools( &$array ) {
		$array['imagemap_desc_types']['ignored'] = true;
	}
}

class InputBoxMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Input Box';
	protected $id    = 'ext-inputbox';

	protected $functionName = 'efInputBoxMessages';
	protected $messageFile  = 'inputbox/InputBox.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';

	protected $exportPad   = 26;
}

class LinkSearchMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Link Search';
	protected $id    = 'ext-linksearch';

	protected $arrName     = 'wgLinkSearchMessages';
	protected $messageFile = 'LinkSearch/LinkSearch.i18n.php';

	protected $exportPad   = 19;
}

class LuceneSearchMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Lucene Search';
	protected $id    = 'ext-lucenesearch';

	protected $arrName     = 'wgLuceneSearchMessages';
	protected $messageFile = 'LuceneSearch/LuceneSearch.i18n.php';

	protected $exportPad   = 24;

	function fillBools( &$array ) {
		$array['searchnearmatch']['ignored'] = true;
	}
}

class MakeBotMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Make Bot';
	protected $id    = 'ext-makebot';

	protected $functionName = 'efMakebotMessages';
	protected $messageFile  = 'Makebot/Makebot.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';

	protected $exportPad   = 26;
}

class MakeSysopMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Make Sysop';
	protected $id    = 'ext-makesysop';

	protected $arrName     = 'wgMakesysopMessages';
	protected $messageFile = 'Makesysop/SpecialMakesysop.i18n.php';
}

class MakeValidateMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Make Validate';
	protected $id    = 'ext-makevalidate';

	protected $functionName = 'efMakeValidateMessages';
	protected $messageFile  = 'FlaggedRevs/Makevalidate.i18n.php';

	protected $exportStart = '$messages[\'$CODE\'] = array(';
	protected $exportPrefix= '';
	protected $exportLineP = "\t";
	protected $exportEnd   = ');';

	protected $exportPad   = 32;
}

class MathStatMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Math Stat';
	protected $id    = 'ext-mathstat';

	protected $arrName     = 'wgMathStatFunctionsMessages';
	protected $messageFile = 'MathStatFunctions/MathStatFunctions.i18n.php';
}

class MediaFunctionsMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: Media Functions';
	protected $id      = 'ext-mediafunctions';

	protected $functionName = 'efMediaFunctionsMessages';
	protected $messageFile  = 'MediaFunctions/MediaFunctions.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportLineP = '';
	protected $exportEnd   = '),';

	protected $exportPad   = 31;
}

class MiniDonationMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Mini Donation';
	protected $id    = 'ext-minidonation';

	protected $arrName     = 'wgMiniDonationMessages';
	protected $messageFile = 'MiniDonation/MiniDonation.i18n.php';
}

class MinimumNameLengthMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: Minimum Name Length';
	protected $id      = 'ext-minimumnamelength';

	protected $functionName = 'efMinimumNameLengthMessages';
	protected $messageFile  = 'MinimumNameLength/MinimumNameLength.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class MiniPreviewExtensionGroup extends MultipleFileMessageGroup {
	protected $label = 'Extension: Mini Preview';
	protected $id    = 'ext-minipreview';

	protected $arrName      = 'messages';
	protected $messageFile  = 'MiniPreview/MiniPreview.i18n.php';
	protected $filePattern  = 'MiniPreview/MiniPreview.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportEnd   = ');';
}

class NewestPagesMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Newest Pages';
	protected $id    = 'ext-newestpages';

	protected $functionName = 'efNewestPagesMessages';
	protected $messageFile = 'NewestPages/NewestPages.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class NewuserLogMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Newuser Log';
	protected $id    = 'ext-newuserlog';

	protected $arrName     = 'wgNewuserlogMessages';
	protected $messageFile = 'Newuserlog/Newuserlog.i18n.php';

	protected $exportPad   = 27;

	function fillBools( &$array ) {
		$array['newuserlogentry']['ignored'] = true;
		$array['newuserlog-create-text']['ignored'] = true;
	}
}

class NewUserNotifMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: New User Notification';
	protected $id      = 'ext-newusernotif';

	protected $functionName = 'efNewUserNotifMessages';
	protected $messageFile  = 'NewUserNotif/NewUserNotif.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class OggHandlerMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Ogg Handler';
	protected $id    = 'ext-ogghandler';

	protected $arrName     = 'messages';
	protected $messageFile = 'OggHandler/OggHandler.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= "\t";
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class OversightMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: Oversight';
	protected $id      = 'ext-oversight';

	protected $functionName = 'efHideRevisionMessages';
	protected $messageFile  = 'Oversight/HideRevision.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class PageByExtensionGroup extends MultipleFileMessageGroup {
	protected $label = 'Extension: Page By';
	protected $id    = 'ext-pageby';

	protected $arrName      = 'messages';
	protected $messageFile  = 'PageBy/PageBy.i18n.php';
	protected $filePattern  = 'PageBy/PageBy.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportEnd   = ');';

}

class ParserFunctionsMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: Parser Functions';
	protected $id      = 'ext-parserfunctions';

	protected $functionName = 'efParserFunctionsMessages';
	protected $messageFile  = 'ParserFunctions/ParserFunctions.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class PatrollerMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: Patroller';
	protected $id      = 'ext-patroller';

	protected $functionName = 'efPatrollerMessages';
	protected $messageFile  = 'Patroller/Patroller.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class PdfHandlerMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: Pdf Handler';
	protected $id      = 'ext-pdfhandler';

	protected $functionName = 'efPdfHandlerMessages';
	protected $messageFile  = 'PdfHandler/PdfHandler.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= "\t\t";
	protected $exportLineP = "\t\t\t";
	protected $exportEnd   = '),';
}

class PicturePopupMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: PicturePopup';
	protected $id      = 'ext-picturepopup';

	protected $functionName = 'efPicturePopupMessages';
	protected $messageFile  = 'PicturePopup/PicturePopup.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class PlayerMessageGroup extends MultipleFileMessageGroup {
	protected $label = 'Extension: Player';
	protected $id    = 'ext-player';

	protected $arrName      = 'messages';
	protected $messageFile  = 'Player/Player.i18n.php';
	protected $filePattern  = 'Player/Player.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportEnd   = ');';
}

class ProfileMonitorMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: ProfileMonitor';
	protected $id      = 'ext-profilemonitor';

	protected $functionName = 'efProfileMonitorMessages';
	protected $messageFile  = 'ProfileMonitor/ProfileMonitor.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class ProofreadPageMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Proofread Page';
	protected $id    = 'ext-proofreadpage';

	protected $arrName     = 'messages';
	protected $messageFile = 'ProofreadPage/ProofreadPage.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= "\t";
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class RenameUserMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Rename User';
	protected $id    = 'ext-renameuser';

	protected $arrName     = 'wgRenameuserMessages';
	protected $messageFile = 'Renameuser/SpecialRenameuser.i18n.php';

	function fillBools( &$array ) {
		$array['renameuserlogentry']['ignored'] = true;
	}
}

class ResignMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Resign';
	protected $id    = 'ext-resign';

	protected $functionName = 'efResignMessages';
	protected $messageFile  = 'Resign/SpecialResign.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class SelectCategoryExtensionGroup extends MultipleFileMessageGroup {
	protected $label = 'Extension: Select Category';
	protected $id    = 'ext-selectcategory';

	protected $arrName      = 'messages';
	protected $messageFile  = 'SelectCategory/i18n/SelectCategory.i18n.php';
	protected $filePattern  = 'SelectCategory/i18n/SelectCategory.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportEnd   = ');';
}

class SiteMatrixMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Site Matrix';
	protected $id    = 'ext-sitematrix';

	protected $arrName     = 'wgSiteMatrixMessages';
	protected $messageFile = 'SiteMatrix/SiteMatrix.i18n.php';

	protected $exportPad   = 13;
}

class SmoothGalleryExtensionGroup extends MultipleFileMessageGroup {
	protected $label = 'Extension: Smooth Gallery';
	protected $id    = 'ext-smoothgallery';

	protected $arrName      = 'messages';
	protected $messageFile  = 'SmoothGallery/SmoothGallery.i18n.php';
	protected $filePattern  = 'SmoothGallery/SmoothGallery.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportEnd   = ');';
}

class SpamBlacklistMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Spam Blacklist';
	protected $id    = 'ext-spamblacklist';

	protected $functionName = 'efSpamBlacklistMessages';
	protected $messageFile  = 'SpamBlacklist/SpamBlacklist.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class SyntaxHighlight_GeSHiMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Syntax Highlight GeSHi';
	protected $id    = 'ext-syntaxhighlightgeshi';

	protected $functionName = 'efSyntaxHighlight_GeSHiMessages';
	protected $messageFile  = 'SyntaxHighlight_GeSHi/SyntaxHighlight_GeSHi.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class TalkHereExtensionGroup extends MultipleFileMessageGroup {
	protected $label = 'Extension: Talk Here';
	protected $id    = 'ext-talkhere';

	protected $arrName      = 'messages';
	protected $messageFile  = 'TalkHere/TalkHere.i18n.php';
	protected $filePattern  = 'TalkHere/TalkHere.i18n.$CODE.php';

	protected $exportStart = '$messages = array(';
	protected $exportEnd   = ');';
}

class TranslateMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Translate';
	protected $id    = 'ext-translate';

	protected $arrName     = 'messages';
	protected $messageFile = 'Translate/Translate.i18n.php';
}

class UserImagesMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: User Images';
	protected $id      = 'ext-userimages';

	protected $functionName = 'efUserImagesMessages';
	protected $messageFile  = 'UserImages/UserImages.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class UsernameBlacklistMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: Username Blacklist';
	protected $id      = 'ext-usernameblacklist';

	protected $functionName = 'efUsernameBlacklistMessages';
	protected $messageFile  = 'UsernameBlacklist/UsernameBlacklist.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class VoteMessageGroup extends ExtensionMessageGroup {
	protected $label   = 'Extension: Vote';
	protected $id      = 'ext-vote';

	protected $functionName = 'efVoteMessages';
	protected $messageFile  = 'Vote/Vote.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class WebStoreMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Web Store';
	protected $id    = 'ext-webstore';

	protected $arrName     = 'messages';
	protected $messageFile = 'WebStore/WebStore.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= "\t";
	protected $exportLineP = "\t";
	protected $exportEnd   = '),';
}

class WikidataLanguageManagerMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Wikidata Language Manager';
	protected $id    = 'ext-wikidatalanguagemanager';

	protected $arrName     = 'wgLanguageManagerMessages';
	protected $messageFile = 'Wikidata/SpecialLanguages.i18n.php';
}

class WikidataOmegaWikiDataSearchMessageGroup extends ExtensionMessageGroup {
	protected $label = 'Extension: Wikidata OmegaWiki Data Search';
	protected $id    = 'ext-wikidataomegawikidatasearch';

	protected $arrName     = 'wgDataSearchMessages';
	protected $messageFile = 'Wikidata/OmegaWiki/SpecialDatasearch.i18n.php';
}

class FreeColMessageGroup extends MessageGroup {

	protected $label = 'External: FreeCol';
	protected $id    = 'out-freecol';
	protected $prefix= 'freecol-';

	protected $msgArray = null;
	private   $fileDir  = 'freecol/';

	public function __construct( $tryLoad ) {
		if ( !$tryLoad ) { return; }
		$filenameEN = $this->fileDir . 'FreeColMessages.properties';

		if ( file_exists( $filenameEN ) ) {
			$linesEN = file( $filenameEN );
		} else {
			return;
		}




		$this->msgArray = array();

		foreach ( $linesEN as $line ) {
			if ( !strpos( $line, '=' ) ) { continue; }
			list( $key, $string ) = explode( '=', $line, 2 );
			$this->msgArray['en'][$this->prefix . $key] = trim($string);
		}



	}

	public function getMessage( $key, $code ) {
		$this->load( $code );
		return isset( $this->msgArray[$code][$key] ) ? $this->msgArray[$code][$key] : null;
	}

	private function load( $code ) {
		#$filenameXX = $this->fileDir . "FreeColMessages_$code.properties";
		$filenameXX = $this->fileDir . "freecol_$code";

		$linesXX = false;
		if ( file_exists( $filenameXX ) ) {
			$linesXX = file( $filenameXX );
		}


		if ( !$linesXX) { return; }
		foreach ( $linesXX as $line ) {
			if ( !strpos( $line, '=' ) ) { continue; }
			list( $key, $string ) = explode( '=', $line, 2 );
			$this->msgArray[$code][$this->prefix . $key] = trim($string);
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
			$array[$key]['definition'] = $this->msgArray['en'][$key];
			if ( isset($this->msgArray[$code][$key]) ) {
				$array[$key]['infile'] = $this->msgArray[$code][$key];
			}
		}
	}

	function getDefinitions() {
		return $this->msgArray['en'];
	}

}

class MessageGroups {

	public $classes = array();

	private function __construct() {
		global $wgTranslateEC, $wgTranslateAC, $wgTranslateTryLoad;

		foreach ($wgTranslateAC as $id => $class) {
			if ( in_array( $id, $wgTranslateEC, true ) ) {
				$this->classes[] = new $class($wgTranslateTryLoad);
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

	public function &getGroups() {
		return $this->classes;
	}
}

