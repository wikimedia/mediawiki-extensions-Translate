<?php
if (!defined('MEDIAWIKI')) die();
/**
 * An extension to ease the translation of Mediawiki
 *
 * @addtogroup Extensions
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2006-2007, Niklas Laxström
 * @copyright Copyright © 2007, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'Translate',
	'version' => '5.3',
	'author' => 'Niklas Laxström, Siebrand Mazeland',
	'description' => 'Special page for translating Mediawiki and beyond',
	'url' => 'http://www.mediawiki.org/wiki/Extension:Translate',
);

$dir = dirname(__FILE__) . '/';
$wgAutoloadClasses['TranslateTasks'] = $dir . 'TranslateTasks.php';
$wgAutoloadClasses['TranslateUtils'] = $dir . 'TranslateUtils.php';
$wgAutoloadClasses['MessageGroups'] = $dir . 'MessageGroups.php';
$wgAutoloadClasses['TranslateEditAddons'] = $dir . 'TranslateEditAddons.php';
$wgAutoloadClasses['languages'] = $IP . '/maintenance/language/languages.inc';
$wgAutoloadClasses['MessageWriter'] = $IP . '/maintenance/language/writeMessagesArray.inc';

$wgAutoloadClasses['SpecialTranslate'] = $dir . 'TranslatePage.php';
$wgAutoloadClasses['SpecialMagic'] = $dir . 'SpecialMagic.php';
$wgAutoloadClasses['SpecialTranslationChanges'] = $dir . 'SpecialTranslationChanges.php';


$wgExtensionMessagesFiles['Translate'] = $dir . 'Translate.i18n.php';

$wgSpecialPages['Translate'] = 'SpecialTranslate';
$wgSpecialPages['Magic'] = 'SpecialMagic';
$wgSpecialPages['TranslationChanges'] = 'SpecialTranslationChanges';

$wgHooks['EditPage::showEditForm:initial'][] = 'TranslateEditAddons::addTools';

define( 'TRANSLATE_FUZZY', '!!FUZZY!!' );
define( 'TRANSLATE_INDEXFILE', $dir . 'messageindex.ser' );

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

/** Which other language translations are displayed to help translator */
$wgTranslateLanguageFallbacks = array();

/** Name of the fuzzer bot */
$wgTranslateFuzzyBotName = 'FuzzyBot';

/** AC = Available classes */
$wgTranslateAC = array(
'core'                      => 'CoreMessageGroup',
'core-500'                  => 'Core500MessageGroup',
'ext-0-all'                 => 'AllMediawikiExtensionsGroup',
'ext-advancedrandom'        => 'AdvancedRandomMessageGroup',
'ext-ajaxshoweditors'       => 'AjaxShowEditorsMessageGroup',
'ext-antispoof'             => 'AntiSpoofMessageGroup',
'ext-assertedit'            => 'AssertEditMessageGroup',
'ext-asksql'                => 'AsksqlMessageGroup',
'ext-backandforth'          => 'BackAndForthMessageGroup',
'ext-badimage'              => 'BadImageMessageGroup',
'ext-blahtex'               => 'BlahtexMessageGroup',
'ext-blocktitles'           => 'BlockTitlesMessageGroup',
'ext-boardvote'             => 'BoardVoteMessageGroup',
'ext-bookinformation'       => 'BookInformationMessageGroup',
'ext-categorytree'          => 'CategoryTreeExtensionGroup',
'ext-centralauth'           => 'CentralAuthMessageGroup',
'ext-changeauthor'          => 'ChangeAuthorMessageGroup',
'ext-checkuser'             => 'CheckUserMessageGroup',
'ext-chemistry'             => 'ChemFunctionsMessageGroup',
'ext-citespecial'           => 'CiteSpecialMessageGroup',
'ext-commentspammer'        => 'CommentSpammerMessageGroup',
'ext-confirmaccount'        => 'ConfirmAccountMessageGroup',
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
'ext-eval'                  => 'EvalMessageGroup',
'ext-fancycaptcha'          => 'FancyCaptchaMessageGroup',
'ext-fckeditor'             => 'FCKeditorExtensionGroup',
'ext-filepath'              => 'FilePathMessageGroup',
'ext-flaggedrevs'           => 'FlaggedRevsMessageGroup',
'ext-gadgets'               => 'GadgetsExtensionGroup',
'ext-giverollback'          => 'GiveRollbackMessageGroup',
'ext-imagemap'              => 'ImageMapMessageGroup',
'ext-importfreeimages'      => 'ImportFreeImagesMessageGroup',
'ext-inputbox'              => 'InputBoxMessageGroup',
'ext-interwiki'             => 'InterwikiMessageGroup',
'ext-linksearch'            => 'LinkSearchMessageGroup',
'ext-liquidthreads'         => 'LiquidThreadsMessageGroup',
'ext-lookupuser'            => 'LookupUserMessageGroup',
'ext-lucenesearch'          => 'LuceneSearchMessageGroup',
'ext-makebot'               => 'MakeBotMessageGroup',
'ext-makesysop'             => 'MakeSysopMessageGroup',
'ext-makevalidate'          => 'MakeValidateMessageGroup',
'ext-mathstat'              => 'MathStatMessageGroup',
'ext-mediafunctions'        => 'MediaFunctionsMessageGroup',
'ext-minidonation'          => 'MiniDonationMessageGroup',
'ext-minimumnamelength'     => 'MinimumNameLengthMessageGroup',
'ext-minipreview'           => 'MiniPreviewExtensionGroup',
'ext-multiupload'           => 'MultiUploadMessageGroup',
'ext-newestpages'           => 'NewestPagesMessageGroup',
'ext-newuserlog'            => 'NewuserLogMessageGroup',
'ext-newusernotif'          => 'NewUserNotifMessageGroup',
'ext-nuke'                  => 'NukeMessageGroup',
'ext-ogghandler'            => 'OggHandlerMessageGroup',
'ext-oversight'             => 'OversightMessageGroup',
'ext-pageby'                => 'PageByMessageGroup',
'ext-passwordreset'         => 'PasswordResetMessageGroup',
'ext-parserfunctions'       => 'ParserfunctionsMessageGroup',
'ext-patroller'             => 'PatrollerMessageGroup',
'ext-picturepopup'          => 'PicturePopupMessageGroup',
'ext-pdfhandler'            => 'PdfHandlerMessageGroup',
'ext-player'                => 'PlayerMessageGroup',
'ext-postcomment'           => 'PostCommentMessageGroup',
'ext-profilemonitor'        => 'ProfileMonitorMessageGroup',
'ext-proofreadpage'         => 'ProofreadPageMessageGroup',
'ext-protectsection'        => 'ProtectSectionMessageGroup',
'ext-quiz'                  => 'QuizMessageGroup',
'ext-renameuser'            => 'RenameUserMessageGroup',
'ext-resign'                => 'ResignMessageGroup',
'ext-review'                => 'ReviewMessageGroup',
'ext-selectcategory'        => 'SelectCategoryExtensionGroup',
'ext-signdocument'          => 'SignDocumentMessageGroup',
'ext-specialcreatesigndocument' => 'SpecialCreateSignDocumentMessageGroup',
'ext-specialsigndocument'   => 'SpecialSignDocumentMessageGroup',
'ext-sitematrix'            => 'SiteMatrixMessageGroup',
'ext-smoothgallery'         => 'SmoothGalleryExtensionGroup',
'ext-spamblacklist'         => 'SpamBlacklistMessageGroup',
'ext-specialform'           => 'SpecialFormMessageGroup',
'ext-syntaxhighlightgeshi'  => 'SyntaxHighlight_GeSHiMessageGroup',
'ext-talkhere'              => 'TalkHereExtensionGroup',
'ext-templatelink'          => 'TemplateLinkMessageGroup',
'ext-titleblacklist'        => 'TitleBlacklistMessageGroup',
'ext-todotasks'             => 'TodoTasksMessageGroup',
'ext-translate'             => 'TranslateMessageGroup',
'ext-userimages'            => 'UserImagesMessageGroup',
'ext-usermerge'             => 'UserMergeMessageGroup',
'ext-usernameblacklist'     => 'UsernameBlacklistMessageGroup',
'ext-userrightsnotif'       => 'UserRightsNotifMessageGroup',
'ext-vote'                  => 'VoteMessageGroup',
'ext-watchers'              => 'WatchersMessageGroup',
'ext-webstore'              => 'WebStoreMessageGroup',
'ext-whoiswatching'         => 'WhoIsWatchingMessageGroup',
'ext-wikidatalanguagemanager' => 'WikidataLanguageManagerMessageGroup',
'ext-wikidataomegawikidatasearch' => 'WikidataOmegaWikiDataSearchMessageGroup',
'out-freecol'               => 'FreeColMessageGroup',
);

/** EC = Enabled classes */
$wgTranslateEC = array();
$wgTranslateEC[] = 'core';
