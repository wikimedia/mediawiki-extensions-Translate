<?php

/**
 * JavaScript and CSS resource definitions.
 *
 * @file
 * @license GPL-2.0+
 */

global $wgResourceModules;

$resourcePaths = array(
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'Translate',
);

$wgResourceModules['ext.translate'] = array(
	'styles' => 'resources/css/ext.translate.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.base'] = array(
	'scripts' => 'resources/js/ext.translate.base.js',
	'dependencies' => array(
		'ext.translate.hooks',
		'mediawiki.api',
		'mediawiki.util',
	),
	'messages' => array(
		'translate-js-support-unsaved-warning',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.dropdownmenu'] = array(
	'styles' => 'resources/css/ext.translate.dropdownmenu.css',
	'scripts' => 'resources/js/ext.translate.dropdownmenu.js',
) + $resourcePaths;

$wgResourceModules['ext.translate.editor'] = array(
	'scripts' => array(
		'resources/js/ext.translate.editor.helpers.js',
		'resources/js/ext.translate.editor.js',
		'resources/js/ext.translate.editor.shortcuts.js',
		'resources/js/ext.translate.pagemode.js',
		'resources/js/ext.translate.proofread.js',
	),
	'styles' => array(
		'resources/css/ext.translate.editor.css',
		'resources/css/ext.translate.pagemode.css',
		'resources/css/ext.translate.proofread.css',
	),
	'dependencies' => array(
		'ext.translate.base',
		'ext.translate.dropdownmenu',
		'ext.translate.hooks',
		'ext.translate.storage',
		'jquery.autosize',
		'jquery.makeCollapsible',
		'jquery.textSelection',
		'jquery.textchange',
		'jquery.tipsy',
		'mediawiki.Uri',
		'mediawiki.api',
		'mediawiki.api.parse',
		'mediawiki.jqueryMsg',
		'mediawiki.user',
		'mediawiki.util',
	),
	'messages' => array(
		'translate-edit-askpermission',
		'translate-edit-nopermission',
		'tux-editor-add-desc',
		'tux-editor-ask-help',
		'tux-editor-cancel-button-label',
		'tux-editor-close-tooltip',
		'tux-editor-collapse-tooltip',
		'tux-editor-confirm-button-label',
		'tux-editor-discard-changes-button-label',
		'tux-editor-doc-editor-cancel',
		'tux-editor-doc-editor-placeholder',
		'tux-editor-doc-editor-save',
		'tux-editor-edit-desc',
		'tux-editor-expand-tooltip',
		'tux-editor-in-other-languages',
		'tux-editor-loading',
		'tux-editor-message-desc-less',
		'tux-editor-message-desc-more',
		'tux-editor-message-tools-delete',
		'tux-editor-message-tools-history',
		'tux-editor-message-tools-translations',
		'tux-editor-n-uses',
		'tux-editor-need-more-help',
		'tux-editor-outdated-warning',
		'tux-editor-outdated-warning-diff-link',
		'tux-editor-paste-original-button-label',
		'tux-editor-placeholder',
		'tux-editor-editsummary-placeholder',
		'tux-editor-proofread-button-label',
		'tux-editor-save-button-label',
		'tux-editor-save-failed',
		'tux-editor-shortcut-info',
		'tux-editor-skip-button-label',
		'tux-editor-suggestions-title',
		'tux-editor-tm-match',
		'tux-proofread-action-tooltip',
		'tux-proofread-edit-label',
		'tux-proofread-translated-by-self',
		'tux-session-expired',
		'tux-status-saving',
		'tux-status-translated',
		'tux-status-unsaved',
		'tux-save-unknown-error',
		'tux-warnings-hide',
		'tux-warnings-more',
		'spamprotectiontext',
	),
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.groupselector'] = array(
	'styles' => 'resources/css/ext.translate.groupselector.css',
	'scripts' => 'resources/js/ext.translate.groupselector.js',
	'position' => 'top',
	'dependencies' => array(
		'ext.translate.base',
		'ext.translate.loader',
		'ext.translate.statsbar',
		'jquery.ui.position',
		'mediawiki.jqueryMsg',
	),
	'messages' => array(
		'translate-msggroupselector-projects',
		'translate-msggroupselector-search-all',
		'translate-msggroupselector-search-placeholder',
		'translate-msggroupselector-search-recent',
		'translate-msggroupselector-view-subprojects',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.hooks'] = array(
	'scripts' => 'resources/js/ext.translate.hooks.js',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.legacy'] = array(
	'styles' => 'resources/css/ext.translate.legacy.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.loader'] = array(
	'styles' => 'resources/css/ext.translate.loader.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.messagetable'] = array(
	'scripts' => 'resources/js/ext.translate.messagetable.js',
	'styles' => 'resources/css/ext.translate.messagetable.less',
	'position' => 'bottom',
	'dependencies' => array(
		'ext.translate.base',
		'ext.translate.hooks',
		'ext.translate.loader',
		'ext.translate.parsers',
		'jquery.appear',
		'jquery.textchange',
		'mediawiki.Uri',
		'mediawiki.jqueryMsg',
		'mediawiki.util',
	),
	'messages' => array(
		'api-error-badtoken',
		'api-error-emptypage',
		'api-error-fuzzymessage',
		'api-error-invalidrevision',
		'api-error-owntranslation',
		'api-error-unknownerror',
		'api-error-unknownmessage',
		'tpt-unknown-page',
		'translate-edit-title',
		'translate-language-disabled',
		'tux-edit',
		'tux-empty-list-all',
		'tux-empty-list-all-guide',
		'tux-empty-list-other',
		'tux-empty-list-other-action',
		'tux-empty-list-other-guide',
		'tux-empty-list-other-link',
		'tux-empty-list-translated',
		'tux-empty-list-translated-action',
		'tux-empty-list-translated-guide',
		'tux-empty-no-messages-to-display',
		'tux-empty-no-outdated-messages',
		'tux-empty-nothing-new-to-proofread',
		'tux-empty-nothing-to-proofread',
		'tux-empty-show-optional-messages',
		'tux-empty-there-are-optional',
		'tux-empty-you-can-help-providing',
		'tux-empty-you-can-review-already-proofread',
		'tux-message-filter-advanced-button',
		'tux-message-filter-placeholder',
		'tux-message-filter-result',
		'tux-messagetable-loading-messages',
		'tux-messagetable-more-messages',
		'tux-status-fuzzy',
		'tux-status-optional',
		'tux-status-proofread',
		'tux-status-translated',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.messagewebimporter'] = array(
	'styles' => 'resources/css/ext.translate.messagewebimporter.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.multiselectautocomplete'] = array(
	'scripts' => 'resources/js/ext.translate.multiselectautocomplete.js',
	'dependencies' => array(
		'jquery.ui.autocomplete',
	),
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.navitoggle'] = array(
	'skinScripts' => array(
		'vector' => 'resources/js/ext.translate.navitoggle.js',
	),
	'skinStyles' => array(
		'vector' => 'resources/css/ext.translate.navitoggle.css',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.pagetranslation.uls'] = array(
	'scripts' => 'resources/js/ext.translate.pagetranslation.uls.js',
	'dependencies' => array(
		'ext.uls.mediawiki',
		'mediawiki.util',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.parsers'] = array(
	'scripts' => 'resources/js/ext.translate.parsers.js',
	'dependencies' => array(
		'mediawiki.util',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.quickedit'] = array(
	'scripts' => 'resources/js/ext.translate.quickedit.js',
	'styles' => 'resources/css/ext.translate.quickedit.css',
	'messages' => array( 'translate-js-nonext', 'translate-js-save-failed' ),
	'dependencies' => array(
		'ext.translate.hooks',
		'jquery.autosize',
		'jquery.form',
		'jquery.ui.dialog',
		'mediawiki.util',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.recentgroups'] = array(
	'scripts' => 'resources/js/ext.translate.recentgroups.js',
	'dependencies' => array(
		'es5-shim',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.selecttoinput'] = array(
	'scripts' => 'resources/js/ext.translate.selecttoinput.js',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.aggregategroups'] = array(
	'scripts' => 'resources/js/ext.translate.special.aggregategroups.js',
	'styles' => 'resources/css/ext.translate.special.aggregategroups.css',
	'position' => 'top',
	'dependencies' => array(
		'jquery.ui.autocomplete',
		'mediawiki.api',
		'mediawiki.util',
	),
	'messages' => array(
		'tpt-aggregategroup-add',
		'tpt-aggregategroup-edit-description',
		'tpt-aggregategroup-edit-name',
		'tpt-aggregategroup-remove-confirm',
		'tpt-aggregategroup-update',
		'tpt-aggregategroup-update-cancel',
		'tpt-invalid-group',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.special.importtranslations'] = array(
	'scripts' => 'resources/js/ext.translate.special.importtranslations.js',
	'dependencies' => array(
		'jquery.ui.autocomplete',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.special.languagestats'] = array(
	'scripts' => 'resources/js/ext.translate.special.languagestats.js',
	'styles' => 'resources/css/ext.translate.special.languagestats.css',
	'messages' => array(
		'translate-langstats-collapse',
		'translate-langstats-collapseall',
		'translate-langstats-expand',
		'translate-langstats-expandall',
	),
	'dependencies' => 'jquery.tablesorter',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.managegroups'] = array(
	'styles' => 'resources/css/ext.translate.special.managegroups.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.managetranslatorsandbox'] = array(
	'scripts' => 'resources/js/ext.translate.special.managetranslatorsandbox.js',
	'dependencies' => array(
		'ext.translate.loader',
		'ext.translate.translationstashstorage',
		'ext.uls.mediawiki',
		'jquery.ui.dialog',
		'mediawiki.api',
		'mediawiki.jqueryMsg',
		'mediawiki.language',
	),
	'messages' => array(
		'tsb-accept-all-button-label',
		'tsb-accept-button-label',
		'tsb-all-languages-button-label',
		'tsb-didnt-make-any-translations',
		'tsb-no-requests-from-new-users',
		'tsb-older-requests',
		'tsb-reject-all-button-label',
		'tsb-reject-button-label',
		'tsb-reminder-failed',
		'tsb-reminder-link-text',
		'tsb-reminder-sending',
		'tsb-reminder-sent',
		'tsb-reminder-sent-new',
		'tsb-request-count',
		'tsb-selected-count',
		'tsb-translations-current',
		'tsb-translations-source',
		'tsb-translations-user',
		'tsb-user-posted-a-comment',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.special.managetranslatorsandbox.styles'] = array(
	'styles' => 'resources/css/ext.translate.special.managetranslatorsandbox.css',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.pagemigration'] = array(
	'styles' => 'resources/css/ext.translate.special.pagemigration.css',
	'scripts' => 'resources/js/ext.translate.special.pagemigration.js',
	'dependencies' => array(
		'jquery.ajaxdispatcher',
		'mediawiki.api',
		'mediawiki.api.edit',
		'mediawiki.ui',
		'mediawiki.ui.button',
	),
	'messages' => array(
		'pm-add-icon-hover-text',
		'pm-delete-icon-hover-text',
		'pm-extra-units-warning',
		'pm-langcode-missing',
		'pm-old-translations-missing',
		'pm-page-does-not-exist',
		'pm-pagename-missing',
		'pm-pagetitle-invalid',
		'pm-pagetitle-missing',
		'pm-swap-icon-hover-text',
		'pm-on-import-message-text',
		'pm-on-save-message-text',
		'pm-savepages-button-label',
		'pm-cancel-button-label',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.special.pagepreparation'] = array(
	'styles' => 'resources/css/ext.translate.special.pagepreparation.css',
	'scripts' => 'resources/js/ext.translate.special.pagepreparation.js',
	'dependencies' => array(
		'mediawiki.RegExp',
		'mediawiki.Title',
		'mediawiki.action.history.diff',
		'mediawiki.api',
		'mediawiki.jqueryMsg',
		'mediawiki.ui',
	),
	'messages' => array(
		'pp-already-prepared-message',
		'pp-pagename-missing',
		'pp-prepare-message',
		'pp-save-button-label',
		'pp-save-message',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.special.pagetranslation'] = array(
	'scripts' => 'resources/js/ext.translate.special.pagetranslation.js',
	'dependencies' => array(
		'ext.translate.multiselectautocomplete',
		'mediawiki.ui.button',
		'mediawiki.Uri',
		'user.tokens',
	),
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.pagetranslation.styles'] = array(
	'styles' => 'resources/css/ext.translate.special.pagetranslation.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.searchtranslations'] = array(
	'scripts' => 'resources/js/ext.translate.special.searchtranslations.js',
	'dependencies' => array(
		'ext.translate.editor',
		'ext.translate.groupselector',
		'ext.uls.geoclient',
		'ext.uls.mediawiki',
		'mediawiki.Uri',
		'mediawiki.language',
	),
	'messages' => array(
		'translate-documentation-language',
		'translate-search-more-groups-info',
		'translate-search-more-languages-info',
	),
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.searchtranslations.operatorsuggest'] = array(
	'scripts' => 'resources/js/ext.translate.special.operatorsuggest.js',
	'dependencies' => array(
		'jquery.ui.autocomplete',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.special.searchtranslations.styles'] = array(
	'styles' => 'resources/css/ext.translate.special.searchtranslations.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.supportedlanguages'] = array(
	'styles' => 'resources/css/ext.translate.special.supportedlanguages.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.translate'] = array(
	'scripts' => 'resources/js/ext.translate.special.translate.js',
	'dependencies' => array(
		'ext.translate.base',
		'ext.translate.groupselector',
		'ext.translate.messagetable',
		'ext.translate.navitoggle',
		'ext.translate.recentgroups',
		'ext.translate.workflowselector',
		'jquery.uls.data',
		'mediawiki.Uri',
		'mediawiki.api',
		'mediawiki.api.parse',
		'mediawiki.jqueryMsg',
	),
	'messages' => array(
		'tpt-discouraged-language-content',
		'tpt-discouraged-language-force-content',
		'tpt-discouraged-language-force-header',
		'tpt-discouraged-language-header',
		'translate-documentation-language',
		'tux-editor-proofreading-hide-own-translations',
		'tux-editor-proofreading-show-own-translations',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.special.translate.legacy'] = array(
	'scripts' => 'resources/js/ext.translate.special.translate.legacy.js',
	'dependencies' => array(
		'mediawiki.api',
	),
	'messages' => array(
		'translate-messagereview-done',
		'translate-messagereview-failure',
		'translate-messagereview-progress',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.special.translate.styles'] = array(
	'styles' => 'resources/css/ext.translate.special.translate.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.translationstash'] = array(
	'scripts' => 'resources/js/ext.translate.special.translationstash.js',
	'styles' => 'resources/css/ext.translate.special.translationstash.css',
	'position' => 'top',
	'dependencies' => array(
		'ext.translate.editor',
		'ext.translate.messagetable',
		'ext.translate.translationstashstorage',
		'ext.uls.mediawiki',
		'mediawiki.api',
		'mediawiki.language',
	),
	'messages' => array(
		'translate-translationstash-skip-button-label',
		'translate-translationstash-translations',
		'tsb-limit-reached-body',
		'tsb-limit-reached-title',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.special.translationstats'] = array(
	'scripts' => 'resources/js/ext.translate.special.translationstats.js',
	'dependencies' => array(
		'jquery.ui.datepicker',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.statsbar'] = array(
	'styles' => 'resources/css/ext.translate.statsbar.css',
	'scripts' => 'resources/js/ext.translate.statsbar.js',
	'messages' => array(
		'translate-statsbar-tooltip',
		'translate-statsbar-tooltip-with-fuzzy',
	),
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.storage'] = array(
	'scripts' => 'resources/js/ext.translate.storage.js',
) + $resourcePaths;

$wgResourceModules['ext.translate.tabgroup'] = array(
	'styles' => 'resources/css/ext.translate.tabgroup.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.tag.languages'] = array(
	'styles' => 'resources/css/ext.translate.tag.languages.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.translationstashstorage'] = array(
	'scripts' => 'resources/js/ext.translate.translationstashstorage.js',
	'dependencies' => array(
		'mediawiki.api',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.workflowselector'] = array(
	'styles' => 'resources/css/ext.translate.workflowselector.css',
	'scripts' => 'resources/js/ext.translate.workflowselector.js',
	'messages' => array(
		'translate-workflow-set-doing',
		'translate-workflow-state-',
		'translate-workflowstatus',
	),
	'dependencies' => array(
		'ext.translate.dropdownmenu',
		'mediawiki.api',
	),
) + $resourcePaths;

// Third party module
$wgResourceModules['jquery.ajaxdispatcher'] = array(
	'scripts' => 'resources/js/jquery.ajaxdispatcher.js',
) + $resourcePaths;

$wgResourceModules['jquery.autosize'] = array(
	'scripts' => 'resources/js/jquery.autosize.js',
) + $resourcePaths;

$wgResourceModules['jquery.textchange'] = array(
	'scripts' => 'resources/js/jquery.textchange.js',
) + $resourcePaths;
