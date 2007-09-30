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
	'version' => '4.0',
	'author' => 'Niklas Laxström',
	'description' => 'Special page for translating Mediawiki and beyond'
);

$dir = dirname(__FILE__) . '/';
$wgAutoloadClasses['TranslateTasks'] = $dir . 'TranslateTasks.php';
$wgAutoloadClasses['TranslateUtils'] = $dir . 'TranslateUtils.php';
$wgAutoloadClasses['MessageGroups'] = $dir . 'MessageGroups.php';
$wgAutoloadClasses['TranslateEditAddons'] = $dir . 'TranslateEditAddons.php';
$wgAutoloadClasses['languages'] = $IP . '/maintenance/language/languages.inc';
$wgAutoloadClasses['SpecialTranslate'] = $dir . 'TranslatePage.php';
$wgAutoloadClasses['SpecialMagic'] = $dir . 'SpecialMagic.php';

$wgExtensionMessagesFiles['Translate'] = $dir . 'Translate.i18n.php';

$wgSpecialPages['Translate'] = 'SpecialTranslate';
$wgSpecialPages['Magic'] = 'SpecialMagic';

$wgHooks['EditPage::showEditForm:initial'][] = 'TranslateEditAddons::addTools';

#
# Configuration variables
#

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
'ext-chemistry'             => 'ChemFunctionsMessageGroup',
'ext-citespecial'           => 'CiteSpecialMessageGroup',
'ext-confiraccount'         => 'ConfirmAccountMessageGroup',
'ext-confirmedit'           => 'ConfirmEditMessageGroup',
'ext-contactpage'           => 'ContactPageExtensionGroup',
'ext-contributionscores'    => 'ContributionScoresMessageGroup',
'ext-contributionseditcount'=> 'ContributionseditcountMessageGroup',
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
'ext-gadgets'               => 'GadgetsExtensionGroup',
'ext-giverollback'          => 'GiveRollbackMessageGroup',
'ext-imagemap'              => 'ImageMapMessageGroup',
'ext-inputbox'              => 'InputBoxMessageGroup',
'ext-linksearch'            => 'LinkSearchMessageGroup',
'ext-lucenesearch'          => 'LuceneSearchMessageGroup',
'ext-makebot'               => 'MakeBotMessageGroup',
'ext-makesysop'             => 'MakeSysopMessageGroup',
'ext-makevalidate'          => 'MakeValidateMessageGroup',
'ext-mathstat'              => 'MathStatMessageGroup',
'ext-mediafunctions'        => 'MediaFunctionsMessageGroup',
'ext-minidonation'          => 'MiniDonationMessageGroup',
'ext-minimumnamelength'     => 'MinimumNameLengthMessageGroup',
'ext-minipreview'           => 'MiniPreviewExtensionGroup',
'ext-newestpages'           => 'NewestPagesMessageGroup',
'ext-newuserlog'            => 'NewuserLogMessageGroup',
'ext-newusernotif'          => 'NewUserNotifMessageGroup',
'ext-ogghandler'            => 'OggHandlerMessageGroup',
'ext-oversight'             => 'OversightMessageGroup',
'ext-pageby'                => 'PageByMessageGroup',
'ext-passwordreset'         => 'PasswordResetMessageGroup',
'ext-parserfunctions'       => 'ParserfunctionsMessageGroup',
'ext-patroller'             => 'PatrollerMessageGroup',
'ext-picturepopup'          => 'PicturePopupMessageGroup',
'ext-pdfhandler'            => 'PdfHandlerMessageGroup',
'ext-player'                => 'PlayerMessageGroup',
'ext-profilemonitor'        => 'ProfileMonitorMessageGroup',
'ext-proofreadpage'         => 'ProofreadPageMessageGroup',
'ext-quiz'                  => 'QuizMessageGroup',
'ext-renameuser'            => 'RenameUserMessageGroup',
'ext-resign'                => 'ResignMessageGroup',
'ext-selectcategory'        => 'SelectCategoryExtensionGroup',
'ext-signdocumenta'         => 'SignDocumentAMessageGroup',
'ext-signdocumentb'         => 'SignDocumentBMessageGroup',
'ext-signdocumentc'         => 'SignDocumentCMessageGroup',
'ext-sitematrix'            => 'SiteMatrixMessageGroup',
'ext-smoothgallery'         => 'SmoothGalleryExtensionGroup',
'ext-spamblacklist'         => 'SpamBlacklistMessageGroup',
'ext-syntaxhighlightgeshi'  => 'SyntaxHighlight_GeSHiMessageGroup',
'ext-talkhere'              => 'TalkHereExtensionGroup',
'ext-templatelink'          => 'TemplateLinkMessageGroup',
'ext-translate'             => 'TranslateMessageGroup',
'ext-userimages'            => 'UserImagesMessageGroup',
'ext-usermerge'             => 'UserMergeMessageGroup',
'ext-usernameblacklist'     => 'UsernameBlacklistMessageGroup',
'ext-vote'                  => 'VoteMessageGroup',
'ext-webstore'              => 'WebStoreMessageGroup',
'ext-wikidatalanguagemanager' => 'WikidataLanguageManagerMessageGroup',
'ext-wikidataomegawikidatasearch' => 'WikidataOmegaWikiDataSearchMessageGroup',
'out-freecol'               => 'FreeColMessageGroup',
);

/** EC = Enabled classes */
$wgTranslateEC = array();
$wgTranslateEC[] = 'core';

