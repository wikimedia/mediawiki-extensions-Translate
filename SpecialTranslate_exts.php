<?php

abstract class MessageClass {

	protected $label = 'none';
	protected $id    = 'none';

	function __construct() {}
	function getLabel() { return $this->label; }
	function getId() { return $this->id; }
	abstract function export(&$array);
	abstract function getArray();
	abstract function hasMessages();
	function fill(&$array) {}

	function validateLine($m, &$comment) {
		if ( $m['ignored'] ) { return false; }
		$fallback = STools::thisOrElse( $m['infbfile'], $m['enmsg'] );

		if ( $m['optional'] ) {
			if ( $m['msg'] !== $fallback ) {
				$comment = "#optional";
				return true;
			} else {
				return false;
			}
		}
		if ( $m['msg'] === $fallback ) {
			if ( $m['defined'] ) {
				$comment = "#identical but defined";
				return true;
			} else {
				return "\n";
			}
		}

		return true;
	}

	function exportLine($key, $m, $pad = false) {
		$comment = '';
		$result = $this->validateLine($m, $comment);
		if ( $result === false ) { return ''; }
		if ( is_string( $result ) ) { return $result; }

		$key = "'$key' ";
		if ($pad) while ( strlen($key) < $pad ) { $key .= ' '; }
		$txt = "$key=> '" . preg_replace( "/(?<!\\\\)'/", "\'", $m['msg']) . "',$comment\n";
		return $txt;
	}

}

class CoreMessageClass extends MessageClass {
	protected $label = 'Core system messages';
	protected $id    = 'core';

	function hasMessages() {
		return true;
	}

	function export(&$array) {
		$txt = "\$messages = array(\n";
		foreach( $array as $key => $m ) {
			$txt .= $this->exportLine($key, $m, 24);
		}
		$txt .= ");";
		return $txt;
	}

	function getArray() {
		return Language::getMessagesFor('en');
	}

	function fill(&$array) {
		global $wgLang;
		$l = new languages();

		foreach ($l->getOptionalMessages() as $optMsg) {
			$array[$optMsg]['optional'] = true;
		}

		foreach ($l->getIgnoredMessages() as $optMsg) {
			$array[$optMsg]['ignored'] = true;
		}

		$infile = STools::getMessagesInFile( $wgLang->getCode() );
		$infbfile = null;
		if ( Language::getFallbackFor( $wgLang->getCode() ) ) {
			$infbfile = STools::getMessagesInFile( Language::getFallbackFor( $wgLang->getCode() ) );
		}

		foreach ( $array as $key => $value ) {
			$array[$key]['extension'] = false;
			$array[$key]['infile'] = isset( $infile[$key] ) ? $infile[$key] : null;
			$array[$key]['infbfile'] = isset( $infbfile[$key] ) ? $infbfile[$key] : null;
		}
	}
}

abstract class ExtensionMessageClass extends MessageClass {
	protected $arrName      = false;
	protected $msgArray     = null;
	protected $functionName = false;
	protected $messageFile  = null;

	protected $exportStart = '$$ARRAY[\'$CODE\'] = array(';
	protected $exportEnd   = ');';
	protected $exportPrefix= '';
	protected $exportPad   = false;
	protected $exportLineP = "\t";

	function __construct( $tryLoad ) {
		global $wgTranslateExtensionDirectory;
		if ( $this->messageFile ) {
			$fullPath = $wgTranslateExtensionDirectory . $this->messageFile;
		} else {
			$fullPath = false;
		}

		if ( $this->arrName ) {
			global ${$this->arrName};
			if ( isset( ${$this->arrName} ) ) {
				$this->msgArray = ${$this->arrName};
			} elseif ( $tryLoad && $fullPath && file_exists( $fullPath ) ) {
				@include_once( $fullPath );
				if ( isset( ${$this->arrName} ) ) {
					$this->msgArray = ${$this->arrName};
					// These messages may not be in the cache, make sure they are now
					STools::addMessagesToCache( $this->msgArray );
				}
			}

		} elseif ( $this->functionName ) {
			if ( function_exists( $this->functionName ) ) {
				$this->msgArray = call_user_func( $this->functionName );
			} elseif ( $tryLoad && $fullPath && file_exists( $fullPath ) ) {
				@include_once( $fullPath );
				if ( function_exists( $this->functionName ) ) {
					$this->msgArray = call_user_func( $this->functionName );
					// These messages may not be in the cache, make sure they are now
					STools::addMessagesToCache( $this->msgArray );
				}
			}
		}

	}

	function hasMessages() {
		return $this->msgArray !== null;
	}
		
	function export(&$array) {
		global $wgLang;
		$code = $wgLang->getCode();
		$txt = $this->exportPrefix . str_replace(
			array( '$ARRAY', '$CODE' ),
			array( $this->arrName, $code ),
			$this->exportStart ) . "\n";

		foreach ($this->msgArray['en'] as $key => $msg) {
			$txt .= $this->exportLineP . $this->exportLine($key, $array[$key], $this->exportPad);
		}
		$txt .= $this->exportPrefix . $this->exportEnd;
		return $txt;
	}

	function getArray($code = 'en') {
		if ( isset( $this->msgArray[$code] ) ) {
			return $this->msgArray[$code];
		}
		return array();
	}

	function fill(&$array) {
		global $wgLang;

		$code = $wgLang->getCode();
		$infile = isset( $this->msgArray[$code] ) ? $this->msgArray[$code] : null;

		$infbfile = null;
		$code = Language::getFallbackFor( $code );
		if ( $code ) {
			$infbfile = isset( $this->msgArray[$code] ) ? $this->msgArray[$code] : null;
		}

		foreach ( $array as $key => $value ) {
			$array[$key]['infile'] = isset( $infile[$key] ) ? $infile[$key] : null;
			$array[$key]['infbfile'] = isset( $infbfile[$key] ) ? $infbfile[$key] : null;
		}
	}

}


class AjaxShowEditorsMessageClass extends ExtensionMessageClass {
	protected $label = 'Extension: Ajax Show Editors';
	protected $id    = 'ext-ajaxshoweditors';

	protected $arrName     = 'wgAjaxShowEditorsMessages';
	protected $messageFile = 'AjaxShowEditors/AjaxShowEditors.i18n.php';
}

class AntiSpoofMessageClass extends ExtensionMessageClass {
	protected $label = 'Extension: Anti Spoof';
	protected $id    = 'ext-antispoof';

	protected $arrName     = 'wgAntiSpoofMessages';
	protected $messageFile = 'AntiSpoof/AntiSpoof_i18n.php';

	protected $exportPad   = 26;
}

class BadImageMessageClass extends ExtensionMessageClass {
	protected $label = 'Extension: Bad Image';
	protected $id    = 'ext-badimage';

	protected $functionName = 'efBadImageMessages';
	protected $messageFile  = 'BadImage/BadImage.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class BookInformationMessageClass extends ExtensionMessageClass {
	protected $label   = 'Extension: BookInformation';
	protected $id      = 'ext-bookinformation';

	protected $functionName = 'efBookInformationMessages';
	protected $messageFile  = 'BookInformation/BookInformation.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class CheckUserMessageClass extends ExtensionMessageClass {
	protected $label   = 'Extension: CheckUser';
	protected $id      = 'ext-checkuser';

	protected $arrName     = 'wgCheckUserMessages';
	protected $messageFile = 'CheckUser/CheckUser.i18n.php';

	protected $exportPad   = 25;
}

class ConfirmEditMessageClass extends ExtensionMessageClass {
	protected $label = 'Extension: ConfirmEdit';
	protected $id    = 'ext-confirmedit';

	protected $arrName     = 'wgConfirmEditMessages';
	protected $messageFile = 'ConfirmEdit/ConfirmEdit.i18n.php';

	protected $exportPad   = 30;
}

class ContributorsMessageClass extends ExtensionMessageClass {
	protected $label   = 'Extension: Contributors';
	protected $id      = 'ext-contributors';

	protected $functionName = 'efContributorsMessages';
	protected $messageFile  = 'Contributors/Contributors.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class CountEditsMessageClass extends ExtensionMessageClass {
	protected $label   = 'Extension: CountEdits';
	protected $id      = 'ext-countedits';

	protected $functionName = 'efCountEditsMessages';
	protected $messageFile  = 'CountEdits/CountEdits.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class CrossNamespaceLinksMessageClass extends ExtensionMessageClass {
	protected $label = 'Extension: Cross Namespace Links';
	protected $id    = 'ext-crossnamespacelinks';

	protected $arrName     = 'wgCrossNamespaceLinksMessages';
	protected $messageFile = 'CrossNamespaceLinks/CrossNamespaceLinks.i18n.php';

	protected $exportPad   = 30;
}

class DuplicatorMessageClass extends ExtensionMessageClass {
	protected $label = 'Extension: Duplicator';
	protected $id    = 'ext-duplicator';

	protected $functionName = 'efDuplicatorMessages';
	protected $messageFile  = 'Duplicator/Duplicator.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class FancyCaptchaMessageClass extends ExtensionMessageClass {
	protected $label   = 'Extension: Fancy Captcha';
	protected $id      = 'ext-fancycaptcha';

	protected $functionName = 'efFancyCaptchaMessages';
	protected $messageFile  = 'ConfirmEdit/FancyCaptcha.i18n.php';

	protected $exportStart = '\'$CODE\' => array(';
	protected $exportPrefix= '';
	protected $exportLineP = '';
	protected $exportEnd   = '),';
}

class RenameUserMessageClass extends ExtensionMessageClass {
	protected $label = 'Extension: RenameUser';
	protected $id    = 'ext-renameuser';

	protected $arrName     = 'wgRenameuserMessages';
	protected $messageFile = 'Renameuser/SpecialRenameuser.i18n.php';

	function fill(&$array) {
		parent::fill(&$array);
		$array['renameuserlogentry']['ignored'] = true;
	}
}

class TranslateMessageClass extends ExtensionMessageClass {
	protected $label = 'Extension: Translate';
	protected $id    = 'ext-translate';

	protected $arrName     = 'wgTranslateMessages';
	protected $messageFile = 'Translate/SpecialTranslate.i18n.php';
}


class FreeColMessageClass extends MessageClass {

	protected $label = 'External: FreeCol';
	protected $id    = 'out-freecol';
	protected $prefix= 'freecol-';

	protected $msgArray = null;
	private   $fileDir  = 'freecol/';

	public function __construct( $tryLoad ) {
		if ( !$tryLoad ) { return; }
		global $IP, $wgLang;
		$filenameEN = $this->fileDir . 'FreeColMessages.properties';

		if ( file_exists( $filenameEN ) ) {
			$linesEN = file( $filenameEN );
		} else {
			return;
		}
		
		$code = $wgLang->getCode();
		$filenameXX = $this->fileDir . "FreeColMessages_$code.properties";

		$linesXX = false;
		if ( file_exists( $filenameXX ) ) {
			$linesXX = file( $filenameXX );
		}

		$this->msgArray = array();

		foreach ( $linesEN as $line ) {
			if ( !strpos( $line, '=' ) ) { continue; }
			list( $key, $string ) = explode( '=', $line, 2 );
			$this->msgArray['en'][$this->prefix . $key] = trim($string);
		}

		if ( !$linesXX) { return; }
		foreach ( $linesXX as $line ) {
			if ( !strpos( $line, '=' ) ) { continue; }
			list( $key, $string ) = explode( '=', $line, 2 );
			$this->msgArray[$code][$this->prefix . $key] = trim($string);
		}

	}

	public function export(&$array) {
		global $wgSitename, $wgRequest;
		$txt = '# Exported on ' . wfTimestamp(TS_ISO_8601) . ' from ' . $wgSitename . "\n# " .
			$wgRequest->getFullRequestURL() . "\n#\n";

		foreach ($array as $key => $value) {
			list(, $key) = explode( '-', $key, 2);
			$comment = '';
			$result = $this->validateLine($value, $comment);
			if ( $result === false ) { continue; }
			if ( is_string( $result ) ) { continue; }

			$txt .= $key . '=' . rtrim( $value['msg'] ) . "\n";
		}
		return $txt;
	}

	function fill(&$array) {
		global $wgLang;
		$code = $wgLang->getCode();

		foreach ( $array as $key => $value ) {
			$infile = STools::thisOrElse( @$this->msgArray[$code][$key], null );
			$statmsg = STools::thisOrElse( $infile, $this->msgArray['en'][$key] );
			$msg = wfMsg( $key . STools::getLanguage() );
			if ( wfEmptyMsg( $key. STools::getLanguage(), $msg ) ) { $msg = $statmsg; }
			$array[$key]['enmsg'] = $this->msgArray['en'][$key];
			$array[$key]['statmsg'] = $statmsg;
			$array[$key]['msg'] = $msg;
			$array[$key]['extension'] = true;
			$array[$key]['infile'] = $infile;
			$array[$key]['infbfile'] = null;
		}
	}

	function getArray($code = 'en') {
		if ( isset( $this->msgArray[$code] ) ) {
			return $this->msgArray[$code];
		}
		return array();
	}

	function hasMessages() { return $this->msgArray !== null; }

}

function efInitializeExtensionClasses() {
	global $wgTranslateEC, $wgTranslateAC, $wgTranslateTryLoad;

	$classes = array();
	foreach ($wgTranslateAC as $id => $class) {
		if ( in_array( $id, $wgTranslateEC, true ) ) {
			$classes[] = new $class($wgTranslateTryLoad);
		}
	}

	return $classes;
}

?>