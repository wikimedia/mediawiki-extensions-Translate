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

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'Translate',
	'version' => '4.0-rc4',
	'author' => 'Niklas Laxström',
	'description' => 'Special page for translating Mediawiki and beyond'
);

$dir = dirname(__FILE__) . '/';
$wgAutoloadClasses['TranslateTasks'] = $dir . 'TranslateTasks.php';
$wgAutoloadClasses['TranslateUtils'] = $dir . 'TranslateUtils.php';
$wgAutoloadClasses['MessageGroups'] = $dir . 'MessageGroups.php';
$wgAutoloadClasses['TranslateEditAddons'] = $dir . 'TranslateEditAddons.php';
$wgAutoloadClasses['languages'] = $IP . '/maintenance/language/languages.inc';
$wgExtensionMessagesFiles['Translate'] = $dir . 'Translate.i18n.php';

// Baah?
require_once( 'maintenance/language/messageTypes.inc' );

/**
 * If this variable is set to false, this extension will not touch any extension
 * files. If set to true, files are read and included to get messages.
 */
$wgTranslateTryLoad = false;

/** Where to look for extension files */
$wgTranslateExtensionDirectory = "$IP/extensions/";

/** AC = Available classes */
$wgTranslateAC = array(
'core'                      => 'CoreMessageGroup',
'ext-0-all'                 => 'AllMediawikiExtensionsGroup',
'ext-ajaxshoweditors'       => 'AjaxShowEditorsMessageGroup',
'ext-antispoof'             => 'AntiSpoofMessageGroup',
'ext-asksql'                => 'AsksqlMessageGroup',
'ext-backandforth'          => 'BackAndForthMessageGroup',
'ext-badimage'              => 'BadImageMessageGroup',
'ext-blocktitles'           => 'BlockTitlesMessageGroup',
'ext-boardvote'             => 'BoardVoteMessageGroup',
'ext-bookinformation'       => 'BookInformationMessageGroup',
'ext-categorytree'          => 'CategoryTreeExtensionGroup',
'ext-centralauth'           => 'CentralAuthMessageGroup',
'ext-checkuser'             => 'CheckUserMessageGroup',
'ext-citespecial'           => 'CiteSpecialMessageGroup',
'ext-confiraccount'         => 'ConfirmAccountMessageGroup',
'ext-confirmedit'           => 'ConfirmEditMessageGroup',
'ext-contactpage'           => 'ContactPageExtensionGroup',
'ext-contributors'          => 'ContributorsMessageGroup',
'ext-countedits'            => 'CountEditsMessageGroup',
'ext-crossnamespacelinks'   => 'CrossNamespaceLinksMessageGroup',
'ext-deletedcontribs'       => 'DeletedContribsMessageGroup',
'ext-desysop'               => 'DesysopMessageGroup',
'ext-dismissablesitenotice' => 'DismissableSiteNoticeMessageGroup',
'ext-duplicator'            => 'DuplicatorMessageGroup',
'ext-editcount'             => 'EditcountMessageGroup',
'ext-expandtemplates'       => 'ExpandTemplatesMessageGroup',
'ext-fancycaptcha'          => 'FancyCaptchaMessageGroup',
'ext-filepath'              => 'FilePathMessageGroup',
'ext-flaggedrevs'           => 'FlaggedRevsMessageGroup',
'ext-imagemap'              => 'ImageMapMessageGroup',
'ext-lucenesearch'          => 'LuceneSearchMessageGroup',
'ext-makebot'               => 'MakeBotMessageGroup',
'ext-makesysop'             => 'MakeSysopMessageGroup',
'ext-makevalidate'          => 'MakeValidateMessageGroup',
'ext-minidonation'          => 'MiniDonationMessageGroup',
'ext-minimumnamelength'     => 'MinimumNameLengthMessageGroup',
'ext-newestpages'           => 'NewestPagesMessageGroup',
'ext-newuserlog'            => 'NewuserLogMessageGroup',
'ext-ogghandler'            => 'OggHandlerMessageGroup',
'ext-patroller'             => 'PatrollerMessageGroup',
'ext-picturepopup'          => 'PicturePopupMessageGroup',
'ext-renameuser'            => 'RenameUserMessageGroup',
'ext-resign'                => 'ResignMessageGroup',
'ext-sitematrix'            => 'SiteMatrixMessageGroup',
'ext-translate'             => 'TranslateMessageGroup',
'ext-userimages'            => 'UserImagesMessageGroup',
'ext-usernameblacklist'     => 'UsernameBlacklistMessageGroup',
'ext-vote'                  => 'VoteMessageGroup',
'out-freecol'               => 'FreeColMessageGroup',
);

/** EC = Enabled classes */
$wgTranslateEC = array();
$wgTranslateEC[] = 'core';

/* Add specialpage */
if ( !function_exists( 'extAddSpecialPage' ) ) {
	require( dirname(__FILE__) . '/../ExtensionFunctions.php' );
}

extAddSpecialPage( dirname(__FILE__) . '/TranslatePage.php', 'Translate', 'SpecialTranslate' );

$wgHooks['EditPage::showEditForm:initial'][] = 'TranslateEditAddons::addTools';

