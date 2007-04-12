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
	'version' => '3.2',
	'author' => 'Niklas Laxström',
	'url' => 'http://nike.users.idler.fi/betawiki',
	'description' => 'Special page for translating Mediawiki and beyond'
);

$wgAutoloadClasses['languages'] = $IP . '/maintenance/language/languages.inc';

/** AC = Available classes */
$wgTranslateAC = array(
'core'                      => 'CoreMessageClass',
'ext-ajaxshoweditors'       => 'AjaxShowEditorsMessageClass',
'ext-antispoof'             => 'AntiSpoofMessageClass',
'ext-asksql'                => 'AsksqlMessageClass',
'ext-badimage'              => 'BadImageMessageClass',
'ext-boardvote'             => 'BoardVoteMessageClass',
'ext-bookinformation'       => 'BookInformationMessageClass',
'ext-centralauth'           => 'CentralAuthMessageClass',
'ext-checkuser'             => 'CheckUserMessageClass',
'ext-citespecial'           => 'CiteSpecialMessageClass',
'ext-confirmedit'           => 'ConfirmEditMessageClass',
'ext-contributors'          => 'ContributorsMessageClass',
'ext-countedits'            => 'CountEditsMessageClass',
'ext-crossnamespacelinks'   => 'CrossNamespaceLinksMessageClass',
'ext-desysop'               => 'DesysopMessageClass',
'ext-dismissablesitenotice' => 'DismissableSiteNoticeMessageClass',
'ext-duplicator'            => 'DuplicatorMessageClass',
'ext-editcount'             => 'EditcountMessageClass',
'ext-expandtemplates'       => 'ExpandTemplatesMessageClass',
'ext-fancycaptcha'          => 'FancyCaptchaMessageClass',
'ext-filepath'              => 'FilePathMessageClass',
'ext-flaggedrevs'           => 'FlaggedRevsMessageClass',
'ext-imagemap'              => 'ImageMapMessageClass',
'ext-lucenesearch'          => 'LuceneSearchMessageClass',
'ext-makebot'               => 'MakeBotMessageClass',
'ext-makesysop'             => 'MakeSysopMessageClass',
'ext-makevalidate'          => 'MakeValidateMessageClass',
'ext-minidonation'          => 'MiniDonationMessageClass',
'ext-minimumnamelength'     => 'MinimumNameLengthMessageClass',
'ext-newuserlog'            => 'NewuserLogMessageClass',
'ext-patroller'             => 'PatrollerMessageClass',
'ext-renameuser'            => 'RenameUserMessageClass',
'ext-sitematrix'            => 'SiteMatrixMessageClass',
'ext-translate'             => 'TranslateMessageClass',
'ext-userimages'            => 'UserImagesMessageClass',
'ext-usernameblacklist'     => 'UsernameBlacklistMessageClass',
'ext-vote'                  => 'VoteMessageClass',
'out-freecol'               => 'FreeColMessageClass',
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
# Try to figure out if <i>core</i> message class could do it
require_once( 'maintenance/language/messageTypes.inc' );


# Register the special page
if ( !function_exists( 'extAddSpecialPage' ) ) {
	require( dirname(__FILE__) . '/../ExtensionFunctions.php' );
}
extAddSpecialPage( dirname(__FILE__) . '/SpecialTranslate_body.php', 'Translate', 'SpecialTranslate' );

require_once( 'SpecialTranslate_edit.php' );

# Not yet
//extAddSpecialPage( dirname(__FILE__) . '/SpecialMagic.php', 'Magic', 'SpecialMagic' );

global $wgHooks;

# Hook Edit page
$poks = new SpecialTranslateEditTools();
$wgHooks['EditPage::showEditForm:initial'][] = array( $poks, 'addTools' );
$wgHooks['SkinTemplateSetupPageCss'][] = 'wfSpecialTranslateAddCss';

# TODO: Add only when viewing this page?
function wfSpecialTranslateAddCss($css) {

	$css .=
<<<CSSXYZ
/* Special:Translate */
.mw-sp-translate-table {
	width: 100%;
}

.mw-sp-translate-table th {
	background-color: #b2b2ff;
}

.mw-sp-translate-table tr.orig {
	background-color: #ffe2e2;
}

.mw-sp-translate-table tr.new {
	background-color: #e2ffe2;
}

.mw-sp-translate-table tr.def {
	background-color: #f0f0ff;
}

.mw-sp-translate-table tr.ign {
	background-color: #202020;
}

.mw-sp-translate-table tr.opt {
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
