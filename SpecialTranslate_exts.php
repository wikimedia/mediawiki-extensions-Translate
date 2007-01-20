<?php

global $wgHooks;
$wgHooks['SpecialTranslateAddMessageClass'][] = 'wfSpecialTranslateAddMessageClasses2';
function wfSpecialTranslateAddMessageClasses2($class) {
	$class[] = new RenameUserMessageClass();
	$class[] = new TranslateMessageClass();
	return true;
}

class RenameUserMessageClass extends MessageClass {

	protected $label = 'Extension: Rename user';
	protected $id    = 'ext-renameuser';
		
	function export(&$array) {
		global $wgLang;
		$code = $wgLang->getCode();
		$txt = "\$wgRenameuserMessages['$code'] = array(\n";

		$g1 = array( 'renameuser', 'renameuserold', 'renameusernew', 'renameusermove', 'renameusersubmit' );
		$g2 = array( 'renameusererrordoesnotexist', 'renameusererrorexists', 'renameusererrorinvalid', 'renameusererrortoomany', 'renameusersuccess' );
		$g3 = array( 'renameuser-page-exists', 'renameuser-page-moved', 'renameuser-page-unmoved' );
		$g4 = array( 'renameuserlogpage', 'renameuserlogpagetext', 'renameuserlog', 'renameuser-move-log' );

		foreach ($g1 as $msg) {
			$txt .= "\t" . $this->exportLine($msg, $array[$msg], 19);
		}
		$txt .= "\n";
		foreach ($g2 as $msg) {
			$txt .= "\t" . $this->exportLine($msg, $array[$msg], 30);
		}
		$txt .= "\n";
		foreach ($g3 as $msg) {
			$txt .= "\t" . $this->exportLine($msg, $array[$msg], 33);
		}
		$txt .= "\n";
		foreach ($g4 as $msg) {
			$txt .= "\t" . $this->exportLine($msg, $array[$msg], 24);
		}

		$txt .= ");";
		return $txt;
	}

	function getArray() {
		global $wgRenameuserMessages;
		return $wgRenameuserMessages['en'];
	}

	function fill(&$array) {
		$array['renameuserlogentry']['ignored'] = true;
	}

}

class TranslateMessageClass extends MessageClass {

	protected $label = 'Extension: Translate';
	protected $id    = 'ext-translate';
		
	function export(&$array) {
		global $wgLang;
		global $wgTranslateMessages;
		$code = $wgLang->getCode();
		$txt = "\$wgTranslateMessages['$code'] = array(\n";

		foreach ($wgTranslateMessages['en'] as $key => $msg) {
			$txt .= "\t" . $this->exportLine($key, $array[$key]);
		}
		$txt .= ");";
		return $txt;
	}

	function getArray() {
		global $wgTranslateMessages;
		return $wgTranslateMessages['en'];
	}

	function fill(&$array) {
		global $wgLang;
		global $wgTranslateMessages;

		$code = $wgLang->getCode();
		$infile = isset( $wgTranslateMessages[$code] ) ? $wgTranslateMessages[$code] : null;

		$infbfile = null;
		$code = $wgLang->getFallbackLanguageCode();
		if ( $code ) {
			$infbfile = isset( $wgTranslateMessages[$code] ) ? $wgTranslateMessages[$code] : null;
		}

		foreach ( $array as $key => $value ) {
			$array[$key]['infile'] = isset( $infile[$key] ) ? $infile[$key] : null;
			$array[$key]['infbfile'] = isset( $infbfile[$key] ) ? $infbfile[$key] : null;
		}
	}
}


class ConfirmEditMessageClass extends MessageClass {

	protected $label   = 'Extension: ConfirmEdit';
	protected $id      = 'ext-confirmedit';
	protected $arrName = 'wgConfirmEditMessages';
	protected $msgArray= null;
	#protected $msgFile = 'ConfirmEdit/ConfirmEdit.i18n.php';

	function __construct() {
		global ${$this->arrName};
		if ( isset( ${$this->arrName} ) ) {
			$this->msgArray = ${$this->arrName};
			$this->hook();
		}
	}

	protected function hook( ) {
		global $wgHooks;
		$wgHooks['SpecialTranslateAddMessageClass'][] = array( $this, 'addHook' );
	}

	function addHook($class) {
		$class[] = new self();
		return true;
	}

		
	function export(&$array) {
		global $wgLang;
		$code = $wgLang->getCode();
		$txt = "\$$this->arrName['$code'] = array(\n";

		foreach ($this->msgArray['en'] as $key => $msg) {
			$txt .= "\t" . $this->exportLine($key, $array[$key], 30);
		}
		$txt .= ");";
		return $txt;
	}

	function getArray() {
		return $this->msgArray['en'];
	}

	function fill(&$array) {
		global $wgLang;

		$code = $wgLang->getCode();
		$infile = isset( $this->msgArray[$code] ) ? $this->msgArray[$code] : null;

		$infbfile = null;
		$code = $wgLang->getFallbackLanguageCode();
		if ( $code ) {
			$infbfile = isset( $this->msgArray[$code] ) ? $this->msgArray[$code] : null;
		}

		foreach ( $array as $key => $value ) {
			$array[$key]['infile'] = isset( $infile[$key] ) ? $infile[$key] : null;
			$array[$key]['infbfile'] = isset( $infbfile[$key] ) ? $infbfile[$key] : null;
		}
	}
}

new ConfirmEditMessageClass();

class DuplicatorMessageClass extends ConfirmEditMessageClass {
	protected $label   = 'Extension: Duplicator';
	protected $id      = 'ext-duplicator';

	function __construct() {
		$this->msgArray = efDuplicatorMessages();
		$this->hook();
	}

	function addHook($class) {
		$class[] = new self();
		return true;
	}

	function export(&$array) {
		global $wgLang;
		$code = $wgLang->getCode();
		$txt = "'$code' => array(\n";

		$groups[3] = true;
		$groups[8] = true;
		$groups[9] = true;
		$groups[14] = true;
		$groups[19] = true;

		$i = 0;
		foreach ($this->msgArray['en'] as $key => $msg) {
			if ( isset($groups[$i++]) ) { $txt .= "\n"; }
			$txt .=  $this->exportLine($key, $array[$key]);
		}
		$txt .= "),";
		return $txt;
	}

}

new DuplicatorMessageClass();


?>