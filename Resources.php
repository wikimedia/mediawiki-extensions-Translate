<?php

/**
 * JavaScript and CSS resource definitions.
 *
 * @file
 * @license GPL-2.0-or-later
 */

global $wgResourceModules;

$resourcePaths = [
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'Translate',
	'targets' => [ 'desktop', 'mobile' ],
];

$wgResourceModules['ext.translate'] = [
	'styles' => 'resources/css/ext.translate.css',
] + $resourcePaths;

$wgResourceModules['ext.translate.base'] = [
	'scripts' => 'resources/js/ext.translate.base.js',
	'dependencies' => [
		'ext.translate.hooks',
		'mediawiki.api',
		'mediawiki.util',
	],
	'messages' => [
		'translate-js-support-unsaved-warning',
	],
] + $resourcePaths;

$wgResourceModules['ext.translate.dropdownmenu'] = [
	'styles' => 'resources/css/ext.translate.dropdownmenu.css',
	'scripts' => 'resources/js/ext.translate.dropdownmenu.js',
] + $resourcePaths;

$wgResourceModules['ext.translate.editor'] = [
	'scripts' => [
		'resources/js/ext.translate.editor.helpers.js',
		'resources/js/ext.translate.editor.js',
		'resources/js/ext.translate.editor.shortcuts.js',
		'resources/js/ext.translate.pagemode.js',
		'resources/js/ext.translate.proofread.js',
	],
	'styles' => [
		'resources/css/ext.translate.editor.css',
		'resources/css/ext.translate.pagemode.css',
		'resources/css/ext.translate.proofread.css',
	],
	'dependencies' => [
		'ext.translate.base',
		'ext.translate.dropdownmenu',
		'ext.translate.hooks',
		'ext.translate.storage',
		'jquery.accessKeyLabel',
		'jquery.autosize',
		'jquery.makeCollapsible',
		'jquery.textSelection',
		'jquery.textchange',
		'mediawiki.Uri',
		'mediawiki.api',
		'mediawiki.api.parse',
		'mediawiki.jqueryMsg',
		'mediawiki.language',
		'mediawiki.user',
		'mediawiki.util',
	],
	'messages' => [
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
		'tux-editor-message-tools-show-editor',
		'tux-editor-message-tools-delete',
		'tux-editor-message-tools-history',
		'tux-editor-message-tools-translations',
		'tux-editor-message-tools-linktothis',
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
	],
] + $resourcePaths;

$wgResourceModules['ext.translate.groupselector'] = [
	'styles' => 'resources/css/ext.translate.groupselector.less',
	'scripts' => 'resources/js/ext.translate.groupselector.js',
	'dependencies' => [
		'ext.translate.base',
		'ext.translate.loader',
		'ext.translate.statsbar',
		'jquery.ui.position',
		'mediawiki.jqueryMsg',
	],
	'messages' => [
		'translate-msggroupselector-search-all',
		'translate-msggroupselector-search-placeholder',
		'translate-msggroupselector-search-recent',
		'translate-msggroupselector-view-subprojects',
	],
] + $resourcePaths;

$wgResourceModules['ext.translate.hooks'] = [
	'scripts' => 'resources/js/ext.translate.hooks.js',
] + $resourcePaths;

$wgResourceModules['ext.translate.legacy'] = [
	'styles' => 'resources/css/ext.translate.legacy.css',
] + $resourcePaths;

$wgResourceModules['ext.translate.loader'] = [
	'styles' => 'resources/css/ext.translate.loader.css',
] + $resourcePaths;

$wgResourceModules['ext.translate.messagetable'] = [
	'scripts' => 'resources/js/ext.translate.messagetable.js',
	'styles' => 'resources/css/ext.translate.messagetable.less',
	'dependencies' => [
		'ext.translate.base',
		'ext.translate.hooks',
		'ext.translate.loader',
		'ext.translate.parsers',
		'jquery.textchange',
		'jquery.throttle-debounce',
		'mediawiki.Uri',
		'mediawiki.jqueryMsg',
		'mediawiki.util',
	],
	'messages' => [
		'api-error-badtoken',
		'api-error-emptypage',
		'api-error-unknownerror',
		'tpt-unknown-page',
		'translate-edit-title',
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
	],
] + $resourcePaths;

$wgResourceModules['ext.translate.messagewebimporter'] = [
	'styles' => 'resources/css/ext.translate.messagewebimporter.css',
] + $resourcePaths;

$wgResourceModules['ext.translate.multiselectautocomplete'] = [
	'scripts' => 'resources/js/ext.translate.multiselectautocomplete.js',
	'dependencies' => [
		'jquery.ui.autocomplete',
	],
	'targets' => [ 'desktop' ],
] + $resourcePaths;

$wgResourceModules['ext.translate.navitoggle'] = [
	'skinScripts' => [
		'vector' => 'resources/js/ext.translate.navitoggle.js',
	],
	'skinStyles' => [
		'vector' => 'resources/css/ext.translate.navitoggle.css',
	],
] + $resourcePaths;

$wgResourceModules['ext.translate.pagetranslation.uls'] = [
	'scripts' => 'resources/js/ext.translate.pagetranslation.uls.js',
	'dependencies' => [
		'ext.uls.mediawiki',
		'mediawiki.util',
	],
] + $resourcePaths;

$wgResourceModules['ext.translate.parsers'] = [
	'scripts' => 'resources/js/ext.translate.parsers.js',
	'dependencies' => [
		'mediawiki.util',
	],
] + $resourcePaths;

$wgResourceModules['ext.translate.quickedit'] = [
	'styles' => 'resources/css/ext.translate.quickedit.css',
] + $resourcePaths;

$wgResourceModules['ext.translate.selecttoinput'] = [
	'scripts' => 'resources/js/ext.translate.selecttoinput.js',
] + $resourcePaths;

$wgResourceModules['ext.translate.special.aggregategroups'] = [
	'scripts' => 'resources/js/ext.translate.special.aggregategroups.js',
	'dependencies' => [
		'jquery.ui.autocomplete',
		'mediawiki.api',
		'mediawiki.util',
	],
	'messages' => [
		'tpt-aggregategroup-add',
		'tpt-aggregategroup-edit-description',
		'tpt-aggregategroup-edit-name',
		'tpt-aggregategroup-remove-confirm',
		'tpt-aggregategroup-update',
		'tpt-aggregategroup-update-cancel',
		'tpt-invalid-group',
	],
	'targets' => [ 'desktop' ],
] + $resourcePaths;

$wgResourceModules['ext.translate.special.aggregategroups.styles'] = [
	'styles' => 'resources/css/ext.translate.special.aggregategroups.css',
] + $resourcePaths;

$wgResourceModules['ext.translate.special.importtranslations'] = [
	'scripts' => 'resources/js/ext.translate.special.importtranslations.js',
	'dependencies' => [
		'jquery.ui.autocomplete',
	],
	'targets' => [ 'desktop' ],
] + $resourcePaths;

$wgResourceModules['ext.translate.special.languagestats'] = [
	'scripts' => 'resources/js/ext.translate.special.languagestats.js',
	'styles' => 'resources/css/ext.translate.special.languagestats.css',
	'messages' => [
		'translate-langstats-collapse',
		'translate-langstats-collapseall',
		'translate-langstats-expand',
		'translate-langstats-expandall',
	],
	'dependencies' => 'jquery.tablesorter',
	'targets' => [ 'desktop' ],
] + $resourcePaths;

$wgResourceModules['ext.translate.special.managegroups'] = [
	'styles' => 'resources/css/ext.translate.special.managegroups.css',
] + $resourcePaths;

$wgResourceModules['ext.translate.special.managetranslatorsandbox'] = [
	'scripts' => 'resources/js/ext.translate.special.managetranslatorsandbox.js',
	'dependencies' => [
		'ext.translate.loader',
		'ext.translate.translationstashstorage',
		'ext.uls.mediawiki',
		'jquery.ui.dialog',
		'mediawiki.api',
		'mediawiki.jqueryMsg',
		'mediawiki.language',
	],
	'messages' => [
		'tsb-accept-all-button-label',
		'tsb-accept-button-label',
		'tsb-reject-confirmation',
		'tsb-accept-confirmation',
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
	],
	'targets' => [ 'desktop' ],
] + $resourcePaths;

$wgResourceModules['ext.translate.special.managetranslatorsandbox.styles'] = [
	'styles' => 'resources/css/ext.translate.special.managetranslatorsandbox.css',
] + $resourcePaths;

$wgResourceModules['ext.translate.special.pagemigration'] = [
	'scripts' => 'resources/js/ext.translate.special.pagemigration.js',
	'dependencies' => [
		'jquery.ajaxdispatcher',
		'mediawiki.api',
		'mediawiki.Title',
		'mediawiki.ui',
		'mediawiki.ui.button',
	],
	'messages' => [
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
		'tpt-unknown-page',
	],
] + $resourcePaths;

$wgResourceModules['ext.translate.special.pagemigration.styles'] = [
	'styles' => 'resources/css/ext.translate.special.pagemigration.css',
] + $resourcePaths;

$wgResourceModules['ext.translate.special.pagepreparation'] = [
	'scripts' => 'resources/js/ext.translate.special.pagepreparation.js',
	'dependencies' => [
		'mediawiki.RegExp',
		'mediawiki.Title',
		'mediawiki.diff.styles',
		'mediawiki.api',
		'mediawiki.jqueryMsg',
		'mediawiki.ui',
	],
	'messages' => [
		'pp-already-prepared-message',
		'pp-pagename-missing',
		'pp-prepare-message',
		'pp-save-button-label',
		'pp-save-message',
	],
] + $resourcePaths;

$wgResourceModules['ext.translate.special.pagepreparation.styles'] = [
	'styles' => 'resources/css/ext.translate.special.pagepreparation.css',
] + $resourcePaths;

$wgResourceModules['ext.translate.special.pagetranslation'] = [
	'scripts' => 'resources/js/ext.translate.special.pagetranslation.js',
	'dependencies' => [
		'ext.translate.multiselectautocomplete',
		'mediawiki.ui.button',
		'mediawiki.Uri',
		'user.tokens',
	],
	'targets' => [ 'desktop' ],
] + $resourcePaths;

$wgResourceModules['ext.translate.special.pagetranslation.styles'] = [
	'styles' => 'resources/css/ext.translate.special.pagetranslation.css',
] + $resourcePaths;

$wgResourceModules['ext.translate.special.searchtranslations'] = [
	'scripts' => 'resources/js/ext.translate.special.searchtranslations.js',
	'dependencies' => [
		'ext.translate.editor',
		'ext.translate.groupselector',
		'ext.uls.mediawiki',
		'mediawiki.Uri',
		'mediawiki.language',
	],
	'messages' => [
		'translate-search-more-groups-info',
		'translate-search-more-languages-info',
	],
] + $resourcePaths;

$wgResourceModules['ext.translate.special.searchtranslations.operatorsuggest'] = [
	'scripts' => 'resources/js/ext.translate.special.operatorsuggest.js',
	'dependencies' => [
		'jquery.ui.autocomplete',
	],
	'targets' => [ 'desktop' ],
] + $resourcePaths;

$wgResourceModules['ext.translate.special.searchtranslations.styles'] = [
	'styles' => 'resources/css/ext.translate.special.searchtranslations.css',
] + $resourcePaths;

$wgResourceModules['ext.translate.special.supportedlanguages'] = [
	'styles' => 'resources/css/ext.translate.special.supportedlanguages.css',
] + $resourcePaths;

$wgResourceModules['ext.translate.special.translate'] = [
	'scripts' => 'resources/js/ext.translate.special.translate.js',
	'dependencies' => [
		'ext.translate.base',
		'ext.translate.editor',
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
	],
	'messages' => [
		'tpt-discouraged-language-content',
		'tpt-discouraged-language-force-content',
		'tpt-discouraged-language-force-header',
		'tpt-discouraged-language-header',
		'tux-editor-proofreading-hide-own-translations',
		'tux-editor-proofreading-show-own-translations',
	],
] + $resourcePaths;

$wgResourceModules['ext.translate.special.translate.styles'] = [
	'styles' => 'resources/css/ext.translate.special.translate.css',
] + $resourcePaths;

$wgResourceModules['ext.translate.special.translationstash'] = [
	'scripts' => 'resources/js/ext.translate.special.translationstash.js',
	'styles' => 'resources/css/ext.translate.special.translationstash.css',
	'dependencies' => [
		'ext.translate.editor',
		'ext.translate.messagetable',
		'ext.translate.translationstashstorage',
		'ext.uls.mediawiki',
		'mediawiki.api',
		'mediawiki.language',
	],
	'messages' => [
		'translate-translationstash-skip-button-label',
		'translate-translationstash-translations',
		'tsb-limit-reached-body',
		'tsb-limit-reached-title',
	],
] + $resourcePaths;

$wgResourceModules['ext.translate.special.translationstats'] = [
	'scripts' => 'resources/js/ext.translate.special.translationstats.js',
] + $resourcePaths;

$wgResourceModules['ext.translate.statsbar'] = [
	'styles' => 'resources/css/ext.translate.statsbar.css',
	'scripts' => 'resources/js/ext.translate.statsbar.js',
	'messages' => [
		'translate-statsbar-tooltip',
		'translate-statsbar-tooltip-with-fuzzy',
	],
] + $resourcePaths;

$wgResourceModules['ext.translate.storage'] = [
	'scripts' => 'resources/js/ext.translate.storage.js',
] + $resourcePaths;

$wgResourceModules['ext.translate.tabgroup'] = [
	'styles' => 'resources/css/ext.translate.tabgroup.css',
] + $resourcePaths;

$wgResourceModules['ext.translate.tag.languages'] = [
	'styles' => 'resources/css/ext.translate.tag.languages.css',
] + $resourcePaths;

$wgResourceModules['ext.translate.translationstashstorage'] = [
	'scripts' => 'resources/js/ext.translate.translationstashstorage.js',
	'dependencies' => [
		'mediawiki.api',
	],
] + $resourcePaths;

$wgResourceModules['ext.translate.workflowselector'] = [
	'styles' => 'resources/css/ext.translate.workflowselector.css',
	'scripts' => 'resources/js/ext.translate.workflowselector.js',
	'messages' => [
		'translate-workflow-set-doing',
		'translate-workflow-state-',
		'translate-workflowstatus',
	],
	'dependencies' => [
		'ext.translate.dropdownmenu',
		'mediawiki.api',
	],
] + $resourcePaths;

// Third party module
$wgResourceModules['jquery.ajaxdispatcher'] = [
	'scripts' => 'resources/js/jquery.ajaxdispatcher.js',
] + $resourcePaths;

$wgResourceModules['jquery.autosize'] = [
	'scripts' => 'resources/js/jquery.autosize.js',
] + $resourcePaths;

$wgResourceModules['jquery.textchange'] = [
	'scripts' => 'resources/js/jquery.textchange.js',
] + $resourcePaths;
