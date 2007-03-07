<?php
if (!defined('MEDIAWIKI')) die();
/**
 * An extension to ease the translation of Mediawiki
 *
 * @addtogroup Extensions
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2006-2007, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

$wgExtensionFunctions[] = 'wfSpecialTranslate';
$wgExtensionCredits['specialpage'][] = array(
	'name' => 'Translate',
	'version' => '3.0',
	'author' => 'Niklas Laxström',
	'url' => 'http://nike.users.idler.fi/betawiki',
	'description' => 'Special page for translating Mediawiki and beyond'
);

$wgAutoloadClasses['languages'] = $IP . '/maintenance/language/languages.inc';

/** AC = Available classes */
$wgTranslateAC = array(
'core' => 'CoreMessageClass',
'ext-ajaxshoweditors' => 'AjaxShowEditorsMessageClass',
'ext-antispoof' => 'AntiSpoofMessageClass',
'ext-badimage' => 'BadImageMessageClass',
'ext-bookinformation' => 'BookInformationMessageClass',
'ext-checkuser' => 'CheckUserMessageClass',
'ext-confirmedit' => 'ConfirmEditMessageClass',
'ext-contributors' => 'ContributorsMessageClass',
'ext-countedits' => 'CountEditsMessageClass',
'ext-crossnamespacelinks' => 'CrossNamespaceLinksMessageClass',
'ext-duplicator' => 'DuplicatorMessageClass',
'ext-fancycaptcha' => 'FancyCaptchaMessageClass',
'ext-renameuser' => 'RenameUserMessageClass',
'ext-translate' => 'TranslateMessageClass',
'out-freecol' => 'FreeColMessageClass',
);

/** EC = Enabled classes */
$wgTranslateEC = array();
$wgTranslateEC[] = 'core';

/** Normally extension messages are assumed to be loaded already. If this
 *  variable is set to true, classes try to load the messages if not available.
 */
$wgTranslateTryLoad = false;

/** Where to look for extension files */
$wgTranslateExtensionDirectory = "$IP/extensions/";

# Internationalisation file
require_once( 'SpecialTranslate.i18n.php' );

# Message types (ugly?)
require_once( 'maintenance/language/messageTypes.inc' );


# Register the special page
if ( !function_exists( 'extAddSpecialPage' ) ) {
	require( dirname(__FILE__) . '/../ExtensionFunctions.php' );
}
extAddSpecialPage( dirname(__FILE__) . '/SpecialTranslate_body.php', 'Translate', 'SpecialTranslate' );

require_once( 'SpecialTranslate_edit.php' );

//extAddSpecialPage( dirname(__FILE__) . '/SpecialMagic.php', 'Magic', 'SpecialMagic' );

global $wgHooks;

# Hook Edit page
$poks = new SpecialTranslateEditTools();
$wgHooks['EditPage::showEditForm:initial'][] = array( $poks, 'addTools' );
$wgHooks['SkinTemplateSetupPageCss'][] = 'wfSpecialTranslateAddCss';

function wfSpecialTranslateAddCss($css) {

	$css .=
<<<CSSXYZ
/* Special:Translate */
.mw-special-translate-table {
	width: 100%;
}

.mw-special-translate-table th {
	background-color: #b2b2ff;
}

.mw-special-translate-table tr.orig {
	background-color: #ffe2e2;
}

.mw-special-translate-table tr.new {
	background-color: #e2ffe2;
}

.mw-special-translate-table tr.def {
	background-color: #f0f0ff;
}

.mw-special-translate-table tr.ign {
	background-color: #202020;
}

.mw-special-translate-table tr.opt {
	background-color: #F2F200;
}

CSSXYZ;
	return true;

}

function wfSpecialTranslate() {
	# Add messages for this extension
	global $wgMessageCache, $wgTranslateMessages;
	foreach( $wgTranslateMessages as $key => $value ) {
		$wgMessageCache->addMessages( $wgTranslateMessages[$key], $key );
	}
}

class STools {
	static public function indexOf( $array, $index ) {
		return $array[$index];
	}

	static public function prettyCode( $code ) {
		return ucfirst(strtolower(str_replace('-', '_', $code)));
	}

	static public function thisOrElse( $candidate, $fallback ) {
		if ( $candidate === null || $candidate === false ) {
			return $fallback;
		} else {
			return $candidate;
		}
	}

	static public function getMessagesInFile( $code ) {
		$file = Language::getMessagesFileName( $code );
		if ( !file_exists( $file ) ) {
			return null;
		} else {
			require( $file );
			return isset( $messages ) ? $messages : null;
		}
	}

	static public function getLanguage() {
		global $wgLang, $wgContLang;
		static $language = false;
		if ( !$language ) {
			if( $wgLang->getCode() != $wgContLang->getCode() ) {
				$language = '/' . $wgLang->getCode();
			} else {
				$language = '';
			}
		}
		return $language;
	}

	static public function addMessagesToCache( $array ) {
		global $wgMessageCache;
		foreach( array_keys($array) as $key ) {
			$wgMessageCache->addMessages( $array[$key], $key );
		}
	}

}

?>
