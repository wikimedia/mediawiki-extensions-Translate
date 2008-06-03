<?php
if (!defined('MEDIAWIKI')) die();
/**
 * An extension to ease the translation of Mediawiki and other projects.
 *
 * @addtogroup Extensions
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2006-2008, Niklas Laxström
 * @copyright Copyright © 2007, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

define( 'TRANSLATE_VERSION', '8.41' );

$wgExtensionCredits['specialpage'][] = array(
	'name'           => 'Translate',
	'version'        => TRANSLATE_VERSION,
	'author'         => array( 'Niklas Laxström', 'Siebrand Mazeland' ),
	'description'    => '[[Special:Translate|Special page]] for translating Mediawiki and beyond',
	'descriptionmsg' => 'translate-desc',
	'url'            => 'http://www.mediawiki.org/wiki/Extension:Translate',
);

$dir = dirname(__FILE__) . '/';
$wgAutoloadClasses['TranslateTasks'] = $dir . 'TranslateTasks.php';
$wgAutoloadClasses['TaskOptions'] = $dir . 'TranslateTasks.php';

$wgAutoloadClasses['TranslateUtils'] = $dir . 'TranslateUtils.php';
$wgAutoloadClasses['HTMLSelector'] = $dir . 'TranslateUtils.php';
$wgAutoloadClasses['ResourceLoader'] = $dir . 'utils/ResourceLoader.php';
$wgAutoloadClasses['StringMatcher'] = $dir . 'utils/StringMatcher.php';

$wgAutoloadClasses['MessageChecks'] = $dir . 'MessageChecks.php';
$wgAutoloadClasses['MessageGroups'] = $dir . 'MessageGroups.php';

$wgAutoloadClasses['MessageCollection'] = $dir . 'Message.php';
$wgAutoloadClasses['TMessage'] = $dir . 'Message.php';

$wgAutoloadClasses['CoreExporter'] = $dir . 'Exporters.php';
$wgAutoloadClasses['StandardExtensionExporter'] = $dir . 'Exporters.php';
$wgAutoloadClasses['MultipleFileExtensionExporter'] = $dir . 'Exporters.php';

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

$wgAutoloadClasses['TranslatePreferences'] = $dir . 'TranslateUtils.php';
$wgHooks['UserToggles'][] = 'TranslatePreferences::TranslateUserToggles';

$wgAvailableRights[] = 'translate';

define( 'TRANSLATE_FUZZY', '!!FUZZY!!' );
define( 'TRANSLATE_INDEXFILE', $dir . 'messageindex.ser' );
define( 'TRANSLATE_CHECKFILE', $dir . 'messagecheck.ser' );

#
# Configuration variables
#

/** Where to look for extension files */
$wgTranslateExtensionDirectory = "$IP/extensions/";

/** Which other language translations are displayed to help translator */
$wgTranslateLanguageFallbacks = array();

/** Name of the fuzzer bot */
$wgTranslateFuzzyBotName = 'FuzzyBot';

/** Address to css if non-default or false */
$wgTranslateCssLocation = $wgScriptPath . '/extensions/Translate';

/** Language code for special documentation language */
$wgTranslateDocumentationLanguageCode = false;

/**
 * Two-dimensional array of languages that cannot be translated.
 * Input can be exact group name, first part before '-' or '*' for all.
 * Second dimension should be language code mapped to reason for disabling.
 * Reason is parsed as wikitext.
 *
 * Example:
 * $wgTranslateBlacklist = array(
 *     '*' => array( // All groups
 *         'en' => 'English is the source language.',
 *     ),
 *     'core' => array( // Exact group
 *         'mul' => 'Not a real language.',
 *     ),
 *     'ext' => array( // Wildcard-like group
 *         'mul' => 'Not a real language',
 *     ),
 * );
 */

$wgTranslateBlacklist = array();

/** AC = Available classes */
$wgTranslateAC = array(
'core'                      => 'CoreMessageGroup',
'core-mostused'             => 'CoreMostUsedMessageGroup',
'ext-0-all'                 => 'AllMediawikiExtensionsGroup',
'ext-0-wikimedia'           => 'AllWikimediaExtensionsGroup',
'ext-absenteelandlord'      => 'AbsenteeLandlordMessageGroup',
'ext-advancedrandom'        => 'AdvancedRandomMessageGroup',
'ext-ajaxquerypages'        => 'AjaxQueryPagesMessageGroup',
'ext-ajaxshoweditors'       => 'AjaxShowEditorsMessageGroup',
'ext-antibot'               => 'AntiBotMessageGroup',
'ext-antispoof'             => 'AntiSpoofMessageGroup',
'ext-apc'                   => 'ApcMessageGroup',
'ext-asksql'                => 'AsksqlMessageGroup',
'ext-assertedit'            => 'AssertEditMessageGroup',
'ext-authorprotect'         => 'AuthorProtectMessageGroup',
'ext-babel'                 => 'BabelMessageGroup',
'ext-backandforth'          => 'BackAndForthMessageGroup',
'ext-badimage'              => 'BadImageMessageGroup',
'ext-blahtex'               => 'BlahtexMessageGroup',
'ext-blocktitles'           => 'BlockTitlesMessageGroup',
'ext-boardvote'             => 'BoardVoteMessageGroup',
'ext-bookinformation'       => 'BookInformationMessageGroup',
'ext-breadcrumbs'           => 'BreadCrumbsMessageGroup',
'ext-call'                  => 'CallMessageGroup',
'ext-categoryintersection'  => 'CategoryIntersectionMessageGroup',
'ext-categorystepper'       => 'CategoryStepperMessageGroup',
'ext-categorytree'          => 'CategoryTreeMessageGroup',
'ext-catfeed'               => 'CatFeedMessageGroup',
'ext-centralauth'           => 'CentralAuthMessageGroup',
'ext-centralnotice'         => 'CentralNoticeMessageGroup',
'ext-changeauthor'          => 'ChangeAuthorMessageGroup',
'ext-charinsert'            => 'CharInsertMessageGroup',
'ext-checkuser'             => 'CheckUserMessageGroup',
'ext-chemistry'             => 'ChemFunctionsMessageGroup',
'ext-cite'                  => 'CiteMessageGroup',
'ext-citespecial'           => 'CiteSpecialMessageGroup',
'ext-cldr'                  => 'LanguageNamesMessageGroup',
'ext-cleanchanges'          => 'CleanChangesMessageGroup',
'ext-collection'            => 'CollectionMessageGroup',
'ext-commentpages'          => 'CommentPagesMessageGroup',
'ext-commentspammer'        => 'CommentSpammerMessageGroup',
'ext-configure'             => 'ConfigureMessageGroup',
'ext-confirmaccount'        => 'ConfirmAccountMessageGroup',
'ext-confirmedit'           => 'ConfirmEditMessageGroup',
'ext-confirmeditfancycaptcha' => 'ConfirmEditFancyCaptchaMessageGroup',
'ext-contactpage'           => 'ContactPageMessageGroup',
'ext-contributionscores'    => 'ContributionScoresMessageGroup',
'ext-contributionseditcount'=> 'ContributionseditcountMessageGroup',
'ext-contributors'          => 'ContributorsMessageGroup',
'ext-contributorsaddon'     => 'ContributorsAddonMessageGroup',
'ext-countedits'            => 'CountEditsMessageGroup',
'ext-crossnamespacelinks'   => 'CrossNamespaceLinksMessageGroup',
'ext-crosswikiblock'        => 'CrosswikiBlockMessageGroup',
'ext-crowdauthentication'   => 'CrowdAuthenticationMessageGroup',
'ext-datatransfer'          => 'DataTransferMessageGroup',
'ext-deletedcontribs'       => 'DeletedContribsMessageGroup',
'ext-didyoumean'            => 'DidYouMeanMessageGroup',
'ext-dismissablesitenotice' => 'DismissableSiteNoticeMessageGroup',
'ext-doublewiki'            => 'DoubleWikiMessageGroup',
'ext-dplforum'              => 'DPLForumMessageGroup',
'ext-duplicator'            => 'DuplicatorMessageGroup',
'ext-editcount'             => 'EditcountMessageGroup',
'ext-editmessages'          => 'EditMessagesMessageGroup',
'ext-editown'               => 'EditOwnMessageGroup',
'ext-editsubpages'          => 'EditSubpagesMessageGroup',
'ext-edituser'              => 'EditUserMessageGroup',
'ext-emailaddressimage'     => 'EmailAddressImageMessageGroup',
'ext-emailarticle'          => 'EmailArticleMessageGroup',
'ext-eval'                  => 'EvalMessageGroup',
'ext-expandtemplates'       => 'ExpandTemplatesMessageGroup',
'ext-farmer'                => 'FarmerMessageGroup',
'ext-findspam'              => 'FindSpamMessageGroup',
'ext-fixedimage'            => 'FixedImageMessageGroup',
'ext-fr-depreciationoversight' => 'FRDepreciationOversightMessageGroup',
'ext-fr-flaggedrevs'        => 'FRFlaggedRevsMessageGroup',
'ext-fr-flaggedrevsaliases' => 'FRFlaggedRevsAliasesMessageGroup',
'ext-fr-oldreviewedpages'   => 'FROldReviewedPagesMessageGroup',
'ext-fr-qualityoversight'   => 'FRQualityOversightMessageGroup',
'ext-fr-reviewedpages'      => 'FRReviewedPagesMessageGroup',
'ext-fr-stabilization'      => 'FRStabilizationMessageGroup',
'ext-fr-stablepages'        => 'FRStablePagesMessageGroup',
'ext-fr-stableversions'     => 'FRStableVersionsMessageGroup',
'ext-fr-unreviewedpages'    => 'FRUnreviewedPagesMessageGroup',
'ext-forcepreview'          => 'ForcePreviewMessageGroup',
'ext-formatemail'           => 'FormatEmailMessageGroup',
'ext-gadgets'               => 'GadgetsMessageGroup',
'ext-globalblocking'        => 'GlobalBlockingMessageGroup',
'ext-globalusage'           => 'GlobalUsageMessageGroup',
'ext-gnuplot'               => 'GnuplotMessageGroup',
'ext-googleanalytics'       => 'GoogleAnalyticsMessageGroup',
'ext-googlemaps'            => 'GoogleMapsMessageGroup',
'ext-gotocategory'          => 'GoToCategoryMessageGroup',
'ext-htmlets'               => 'HTMLetsMessageGroup',
'ext-i18ntags'              => 'I18nTagsMessageGroup',
'ext-icon'                  => 'IconMessageGroup',
'ext-imagemap'              => 'ImageMapMessageGroup',
'ext-imagetagging'          => 'ImageTaggingMessageGroup',
'ext-importfreeimages'      => 'ImportFreeImagesMessageGroup',
'ext-importusers'           => 'ImportUsersMessageGroup',
'ext-inputbox'              => 'InputBoxMessageGroup',
'ext-inspectcache'          => 'InspectCacheMessageGroup',
'ext-intersection'          => 'IntersectionMessageGroup',
'ext-interwiki'             => 'InterwikiMessageGroup',
'ext-invitations'           => 'InvitationsMessageGroup',
'ext-labeledsectiontransclusion' => 'LabeledSectionTransclusionMessageGroup',
'ext-languageselector'      => 'LanguageSelectorMessageGroup',
'ext-latexdoc'              => 'LatexDocMessageGroup',
'ext-linksearch'            => 'LinkSearchMessageGroup',
'ext-liquidthreads'         => 'LiquidThreadsMessageGroup',
'ext-lookupuser'            => 'LookupUserMessageGroup',
'ext-maintenance'           => 'MaintenanceMessageGroup',
'ext-mathstat'              => 'MathStatMessageGroup',
'ext-mediafunctions'        => 'MediaFunctionsMessageGroup',
'ext-metavidwiki'           => 'MetavidWikiMessageGroup',
'ext-mibbit'                => 'MibbitMessageGroup',
'ext-microid'               => 'MicroIDMessageGroup',
'ext-minidonation'          => 'MiniDonationMessageGroup',
'ext-minimumnamelength'     => 'MinimumNameLengthMessageGroup',
'ext-minipreview'           => 'MiniPreviewMessageGroup',
'ext-multiboilerplate'      => 'MultiBoilerplateMessageGroup',
'ext-multiupload'           => 'MultiUploadMessageGroup',
'ext-mwsearch'              => 'MWSearchMessageGroup',
'ext-navigationpopups'      => 'NavigationPopupsMessageGroup',
'ext-networkauth'           => 'NetworkAuthMessageGroup',
'ext-newestpages'           => 'NewestPagesMessageGroup',
'ext-news'                  => 'NewsMessageGroup',
'ext-newuserlog'            => 'NewuserLogMessageGroup',
'ext-newusermessage'        => 'NewUserMessageMessageGroup',
'ext-newusernotif'          => 'NewUserNotifMessageGroup',
'ext-nuke'                  => 'NukeMessageGroup',
'ext-oai'                   => 'OaiMessageGroup',
'ext-ogghandler'            => 'OggHandlerMessageGroup',
'ext-onlinestatus'          => 'OnlineStatusMessageGroup',
'ext-openid'                => 'OpenIDMessageGroup',
'ext-oversight'             => 'OversightMessageGroup',
'ext-pageby'                => 'PageByMessageGroup',
'ext-parserdifftest'        => 'ParserDiffTestMessageGroup',
'ext-parserfunctions'       => 'ParserfunctionsMessageGroup',
'ext-passwordreset'         => 'PasswordResetMessageGroup',
'ext-patroller'             => 'PatrollerMessageGroup',
'ext-pdfhandler'            => 'PdfHandlerMessageGroup',
'ext-piwik'                 => 'PiwikMessageGroup',
'ext-player'                => 'PlayerMessageGroup',
'ext-pnghandler'            => 'PNGHandlerMessageGroup',
'ext-poem'                  => 'PoemMessageGroup',
'ext-postcomment'           => 'PostCommentMessageGroup',
'ext-povwatch'              => 'PovWatchMessageGroup',
'ext-preloader'             => 'PreloaderMessageGroup',
'ext-profilemonitor'        => 'ProfileMonitorMessageGroup',
'ext-proofreadpage'         => 'ProofreadPageMessageGroup',
'ext-protectsection'        => 'ProtectSectionMessageGroup',
'ext-psinotocnum'           => 'PSINoTocNumMessageGroup',
'ext-purge'                 => 'PurgeMessageGroup',
'ext-purgecache'            => 'PurgeCacheMessageGroup',
'ext-quiz'                  => 'QuizMessageGroup',
'ext-randomimage'           => 'RandomImageMessageGroup',
'ext-randomincategory'      => 'RandomInCategoryMessageGroup',
'ext-randomrootpage'        => 'RandomRootpageMessageGroup',
'ext-regexblock'            => 'RegexBlockMessageGroup',
'ext-renameuser'            => 'RenameUserMessageGroup',
'ext-replacetext'           => 'ReplaceTextMessageGroup',
'ext-review'                => 'ReviewMessageGroup',
'ext-rightfunctions'        => 'RightFunctionsMessageGroup',
'ext-scanset'               => 'ScanSetMessageGroup',
'ext-seealso'               => 'SeealsoMessageGroup',
'ext-selectcategory'        => 'SelectCategoryMessageGroup',
'ext-semanticcalendar'      => 'SemanticCalendarMessageGroup',
'ext-semanticdrilldown'     => 'SemanticDrilldownMessageGroup',
'ext-semanticforms'         => 'SemanticFormsMessageGroup',
'ext-semanticmediawiki'     => 'SemanticMediaWikiMessageGroup',
'ext-showprocesslist'       => 'ShowProcesslistMessageGroup',
'ext-signdocument'          => 'SignDocumentMessageGroup',
'ext-signdocumentspecial'   => 'SignDocumentSpecialMessageGroup',
'ext-signdocumentspecialcreate' => 'SignDocumentSpecialCreateMessageGroup',
'ext-simpleantispam'        => 'SimpleAntiSpamMessageGroup',
'ext-sitematrix'            => 'SiteMatrixMessageGroup',
'ext-skinperpage'           => 'SkinPerPageMessageGroup',
'ext-smoothgallery'         => 'SmoothGalleryMessageGroup',
'ext-socialprofileuserboard' => 'SocialProfileUserBoardMessageGroup',
'ext-socialprofileuserprofile' => 'SocialProfileUserProfileMessageGroup',
'ext-socialprofileuserrelationship' => 'SocialProfileUserRelationshipMessageGroup',
'ext-spamblacklist'         => 'SpamBlacklistMessageGroup',
'ext-spamdifftool'          => 'SpamDiffToolMessageGroup',
'ext-spamregex'             => 'SpamRegexMessageGroup',
'ext-specialfilelist'       => 'SpecialFileListMessageGroup',
'ext-specialform'           => 'SpecialFormMessageGroup',
'ext-stalepages'            => 'StalePagesMessageGroup',
'ext-subpagelist3'          => 'SubPageList3MessageGroup',
'ext-syntaxhighlightgeshi'  => 'SyntaxHighlight_GeSHiMessageGroup',
'ext-tab0'                  => 'Tab0MessageGroup',
'ext-talkhere'              => 'TalkHereMessageGroup',
'ext-templatelink'          => 'TemplateLinkMessageGroup',
'ext-throttle'              => 'ThrottleMessageGroup',
'ext-tidytab'               => 'TidyTabMessageGroup',
'ext-timeline'              => 'TimelineMessageGroup',
'ext-titleblacklist'        => 'TitleBlacklistMessageGroup',
'ext-titlekey'              => 'TitleKeyMessageGroup',
'ext-todo'                  => 'TodoMessageGroup',
'ext-todotasks'             => 'TodoTasksMessageGroup',
'ext-tooltip'               => 'TooltipMessageGroup',
'ext-translate'             => 'TranslateMessageGroup',
'ext-torblock'              => 'TorBlockMessageGroup',
'ext-usagestatistics'       => 'UsageStatisticsMessageGroup',
'ext-usercontactlinks'      => 'UserContactLinksMessageGroup',
'ext-userimages'            => 'UserImagesMessageGroup',
'ext-usermerge'             => 'UserMergeMessageGroup',
'ext-usernameblacklist'     => 'UsernameBlacklistMessageGroup',
'ext-userrightsnotif'       => 'UserRightsNotifMessageGroup',
'ext-vote'                  => 'VoteMessageGroup',
'ext-watchers'              => 'WatchersMessageGroup',
'ext-watchsubpages'         => 'WatchSubpagesMessageGroup',
'ext-webstore'              => 'WebStoreMessageGroup',
'ext-whitelist'             => 'WhiteListMessageGroup',
'ext-whoiswatching'         => 'WhoIsWatchingMessageGroup',
'ext-wikidatalanguagemanager' => 'WikidataLanguageManagerMessageGroup',
'ext-wikihiero'             => 'WikihieroMessageGroup',
'ext-woopra'                => 'WoopraMessageGroup',
'ext-youtubeauthsub'        => 'YouTubeAuthSubMessageGroup',
'ext-yui'                   => 'YUIMessageGroup',
'out-freecol'               => 'FreeColMessageGroup',
'out-word2mediawikiplus'    => 'Word2MediaWikiPlusMessageGroup',
);

/** EC = Enabled classes */
$wgTranslateEC = array();
$wgTranslateEC[] = 'core';

/** CC = Custom classes */
$wgTranslateCC = array();

/** Tasks */
$wgTranslateTasks = array(
	'view'           => 'ViewMessagesTask',
	'untranslated'   => 'ViewUntranslatedTask',
	'optional'       => 'ViewOptionalTask',
	'review'         => 'ReviewMessagesTask',
	'reviewall'      => 'ReviewAllMessagesTask',
	'export-as-po'   => 'ExportasPoMessagesTask',
	'export'         => 'ExportMessagesTask',
	'export-to-file' => 'ExportToFileMessagesTask',
);

if ( $wgDebugComments ) {
	require_once( "$dir/utils/MemProfile.php" );
} else {
	function wfMemIn() {}
	function wfMemOut() {}
}
