<?php
if (!defined('MEDIAWIKI')) die();
/**
 * An extension to ease the translation of Mediawiki and other projects.
 *
 * @addtogroup Extensions
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2006-2007, Niklas Laxström
 * @copyright Copyright © 2007, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

define( 'TRANSLATE_VERSION', '6.7' );

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'Translate',
	'version' => TRANSLATE_VERSION,
	'author' => 'Niklas Laxström, Siebrand Mazeland',
	'description' => 'Special page for translating Mediawiki and beyond',
	'url' => 'http://www.mediawiki.org/wiki/Extension:Translate',
);

$dir = dirname(__FILE__) . '/';
$wgAutoloadClasses['TranslateTasks'] = $dir . 'TranslateTasks.php';
$wgAutoloadClasses['TranslateUtils'] = $dir . 'TranslateUtils.php';

$wgAutoloadClasses['MessageGroups'] = $dir . 'MessageGroups.php';

$wgAutoloadClasses['MessageCollection'] = $dir . 'Message.php';
$wgAutoloadClasses['TMessage'] = $dir . 'Message.php';

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
'core-mostused'             => 'CoreMostUsedMessageGroup',
'ext-0-all'                 => 'AllMediawikiExtensionsGroup',
'ext-0-wikimedia'           => 'AllWikimediaExtensionsGroup',
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
'ext-cite'                  => 'CiteMessageGroup',
'ext-citespecial'           => 'CiteSpecialMessageGroup',
'ext-commentspammer'        => 'CommentSpammerMessageGroup',
'ext-confirmaccount'        => 'ConfirmAccountMessageGroup',
'ext-confirmedit'           => 'ConfirmEditMessageGroup',
'ext-confirmeditfancycaptcha' => 'ConfirmEditFancyCaptchaMessageGroup',
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
'ext-eval'                  => 'EvalMessageGroup',
'ext-expandtemplates'       => 'ExpandTemplatesMessageGroup',
'ext-farmer'                => 'FarmerMessageGroup',
'ext-fckeditor'             => 'FCKeditorExtensionGroup',
'ext-filepath'              => 'FilePathMessageGroup',
'ext-findspam'              => 'FindSpamMessageGroup',
'ext-flaggedrevs'           => 'FlaggedRevsMessageGroup',
'ext-flaggedrevsmakereviewer' => 'FlaggedRevsMakeReviewerMessageGroup',
'ext-formatemail'           => 'FormatEmailMessageGroup',
'ext-gadgets'               => 'GadgetsExtensionGroup',
'ext-giverollback'          => 'GiveRollbackMessageGroup',
'ext-icon'                  => 'IconMessageGroup',
'ext-imagemap'              => 'ImageMapMessageGroup',
'ext-importfreeimages'      => 'ImportFreeImagesMessageGroup',
'ext-inputbox'              => 'InputBoxMessageGroup',
'ext-inspectcache'          => 'InspectCacheMessageGroup',
'ext-intersection'          => 'IntersectionMessageGroup',
'ext-interwiki'             => 'InterwikiMessageGroup',
'ext-languageselector'      => 'LanguageSelectorMessageGroup',
'ext-latexdoc'              => 'LatexDocMessageGroup',
'ext-linksearch'            => 'LinkSearchMessageGroup',
'ext-liquidthreads'         => 'LiquidThreadsMessageGroup',
'ext-lookupuser'            => 'LookupUserMessageGroup',
'ext-lucenesearch'          => 'LuceneSearchMessageGroup',
'ext-makebot'               => 'MakeBotMessageGroup',
'ext-makesysop'             => 'MakeSysopMessageGroup',
'ext-mathstat'              => 'MathStatMessageGroup',
'ext-mediafunctions'        => 'MediaFunctionsMessageGroup',
'ext-microid'               => 'MicroIDMessageGroup',
'ext-minidonation'          => 'MiniDonationMessageGroup',
'ext-minimumnamelength'     => 'MinimumNameLengthMessageGroup',
'ext-minipreview'           => 'MiniPreviewExtensionGroup',
'ext-multiupload'           => 'MultiUploadMessageGroup',
'ext-networkauth'           => 'NetworkAuthMessageGroup',
'ext-newestpages'           => 'NewestPagesMessageGroup',
'ext-newuserlog'            => 'NewuserLogMessageGroup',
'ext-newusernotif'          => 'NewUserNotifMessageGroup',
'ext-nuke'                  => 'NukeMessageGroup',
'ext-ogghandler'            => 'OggHandlerMessageGroup',
#'ext-openid'                => 'OpenIDMessageGroup',
'ext-oversight'             => 'OversightMessageGroup',
'ext-pageby'                => 'PageByMessageGroup',
'ext-passwordreset'         => 'PasswordResetMessageGroup',
'ext-parserfunctions'       => 'ParserfunctionsMessageGroup',
'ext-patroller'             => 'PatrollerMessageGroup',
'ext-pdfhandler'            => 'PdfHandlerMessageGroup',
'ext-player'                => 'PlayerMessageGroup',
'ext-postcomment'           => 'PostCommentMessageGroup',
'ext-povwatch'              => 'PovWatchMessageGroup',
'ext-profilemonitor'        => 'ProfileMonitorMessageGroup',
'ext-proofreadpage'         => 'ProofreadPageMessageGroup',
'ext-protectsection'        => 'ProtectSectionMessageGroup',
'ext-purge'                 => 'PurgeMessageGroup',
'ext-quiz'                  => 'QuizMessageGroup',
'ext-randomincategory'      => 'RandomInCategoryMessageGroup',
'ext-regexblock'            => 'RegexBlockMessageGroup',
'ext-renameuser'            => 'RenameUserMessageGroup',
'ext-resign'                => 'ResignMessageGroup',
'ext-review'                => 'ReviewMessageGroup',
'ext-scanset'               => 'ScanSetMessageGroup',
'ext-seealso'               => 'SeealsoMessageGroup',
'ext-selectcategory'        => 'SelectCategoryExtensionGroup',
'ext-showprocesslist'       => 'ShowProcesslistMessageGroup',
'ext-signdocument'          => 'SignDocumentMessageGroup',
'ext-signdocumentspecial'   => 'SignDocumentSpecialMessageGroup',
'ext-signdocumentspecialcreate' => 'SignDocumentSpecialCreateMessageGroup',
'ext-sitematrix'            => 'SiteMatrixMessageGroup',
'ext-smoothgallery'         => 'SmoothGalleryExtensionGroup',
'ext-spamblacklist'         => 'SpamBlacklistMessageGroup',
'ext-spamdifftool'          => 'SpamDiffToolMessageGroup',
'ext-spamregex'             => 'SpamRegexMessageGroup',
'ext-specialfilelist'       => 'SpecialFileListMessageGroup',
'ext-specialform'           => 'SpecialFormMessageGroup',
'ext-stalepages'            => 'StalePagesMessageGroup',
'ext-syntaxhighlightgeshi'  => 'SyntaxHighlight_GeSHiMessageGroup',
'ext-talkhere'              => 'TalkHereExtensionGroup',
'ext-templatelink'          => 'TemplateLinkMessageGroup',
'ext-throttle'              => 'ThrottleMessageGroup',
'ext-tidytab'               => 'TidyTabMessageGroup',
'ext-titleblacklist'        => 'TitleBlacklistMessageGroup',
'ext-todo'                  => 'TodoMessageGroup',
'ext-todotasks'             => 'TodoTasksMessageGroup',
'ext-translate'             => 'TranslateMessageGroup',
'ext-usercontactlinks'      => 'UserContactLinksMessageGroup',
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
