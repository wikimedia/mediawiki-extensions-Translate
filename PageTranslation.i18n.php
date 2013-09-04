<?php
/**
 * Translations of Page Translation feature of Translate extension.
 *
 * @file
 * @license GPL-2.0+
 */

$messages = array();

/** English
 * @author Nike
 */
$messages['en'] = array(
	'pagetranslation' => 'Page translation',
	'right-pagetranslation' => 'Mark versions of pages for translation',
	'action-pagetranslation' => 'manage translatable pages',

	'tpt-desc' => 'Extension for translating content pages',
	'tpt-section' => 'Translation unit $1',
	'tpt-section-new' => 'New translation unit.
Name: $1',
	'tpt-section-deleted' => 'Translation unit $1',
	'tpt-template' => 'Page template',
	'tpt-templatediff' => 'The page template has changed.',

	'tpt-diff-old' => 'Previous text',
	'tpt-diff-new' => 'New text',
	'tpt-submit' => 'Mark this version for translation',

	'tpt-sections-oldnew' => 'New and existing translation units',
	'tpt-sections-deleted' => 'Deleted translation units',
	'tpt-sections-template' => 'Translation page template',

	'tpt-action-nofuzzy' => 'Do not invalidate translations',

	# Specific page on the special page
	'tpt-badtitle' => 'Page name given ($1) is not a valid title',
	'tpt-nosuchpage' => 'Page $1 does not exist',
	'tpt-oldrevision' => '$2 is not the latest version of the page [[$1]].
Only latest versions can be marked for translation.',
	'tpt-notsuitable' => 'Page $1 is not suitable for translation.
Make sure it has <nowiki><translate></nowiki> tags and has a valid syntax.',
	'tpt-saveok' => 'The page [[$1]] has been marked up for translation with $2 {{PLURAL:$2|translation unit|translation units}}.
The page can now be <span class="plainlinks">[$3 translated]</span>.',
	'tpt-offer-notify' => 'You can <span class="plainlinks">[$1 notify translators]</span> about this page.',
	'tpt-badsect' => '"$1" is not a valid name for translation unit $2.',
	'tpt-showpage-intro' => 'Below new, existing and deleted translation units are listed.
Before marking this version for translation, check that the changes to translation units are minimized to avoid unnecessary work for translators.',
	'tpt-mark-summary' => 'Marked this version for translation',
	'tpt-edit-failed' => 'Could not update the page: $1',
	'tpt-duplicate' => 'Translation unit name $1 is used more than once.',
	'tpt-already-marked' => 'The latest version of this page has already been marked for translation.',
	'tpt-unmarked' => 'Page $1 is no longer marked for translation.',

	# Page list on the special page
	'tpt-list-nopages' => 'No pages are marked for translation or ready to be marked for translation.',

	'tpt-new-pages-title' => 'Pages proposed for translation',
	'tpt-old-pages-title' => 'Pages in translation',
	'tpt-other-pages-title' => 'Broken pages',
	'tpt-discouraged-pages-title' => 'Discouraged pages',

	'tpt-new-pages' => '{{PLURAL:$1|This page contains|These pages contain}} text with translation tags,
but no version of {{PLURAL:$1|this page is|these pages are}} currently marked for translation.',
	'tpt-old-pages' => 'Some version of {{PLURAL:$1|this page has|these pages have}} been marked for translation.',
	'tpt-other-pages' => '{{PLURAL:$1|An old version of this page is|Older versions of these pages are}} marked for translation,
but the latest {{PLURAL:$1|version|versions}} cannot be marked for translation.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|This page has|These pages have}} been discouraged from further translation.',

	'tpt-select-prioritylangs' => 'Comma-separated list of priority language codes:',
	'tpt-select-prioritylangs-force' => 'Prevent translations to languages other than the priority languages',
	'tpt-select-prioritylangs-reason' => 'Reason:',
	'tpt-sections-prioritylangs' => 'Priority languages',

	'tpt-rev-mark' => 'mark for translation',
	'tpt-rev-unmark' => 'remove from translation',
	'tpt-rev-discourage' => 'discourage',
	'tpt-rev-encourage' => 'restore',

	'tpt-rev-mark-tooltip' => 'Mark the latest version of this page for translation.',
	'tpt-rev-unmark-tooltip' => 'Remove this page from translation.',
	'tpt-rev-discourage-tooltip' => 'Discourage further translations on this page.',
	'tpt-rev-encourage-tooltip' => 'Restore this page to normal translation.',

	# Source and translation page headers
	'translate-tag-translate-link-desc' => 'Translate this page',
	'translate-tag-markthis' => 'Mark this page for translation',
	'translate-tag-markthisagain' => 'This page has <span class="plainlinks">[$1 changes]</span> since it was last <span class="plainlinks">[$2 marked for translation]</span>.',
	'translate-tag-hasnew' => 'This page contains <span class="plainlinks">[$1 changes]</span> which are not marked for translation.',
	'tpt-translation-intro' => 'This page is a <span class="plainlinks">[$1 translated version]</span> of a page [[$2]] and the translation is $3% complete.',

	'tpt-languages-legend' => 'Other languages:',
	'tpt-languages-separator' => '&#160;•&#160;',
	'tpt-languages-zero' => 'Start translation for this language',
	'tpt-tab-translate' => 'Translate',

	'tpt-target-page' => 'This page cannot be updated manually.
This page is a translation of the page [[$1]] and the translation can be updated using [$2 the translation tool].',
	'tpt-unknown-page' => 'This namespace is reserved for content page translations.
The page you are trying to edit does not seem to correspond any page marked for translation.',
	'tpt-translation-restricted' => 'Translation of this page to this language has been prevented by a translation administrator.

Reason: $1',
	'tpt-discouraged-language-force' => "'''This page cannot be translated to $2.'''

A translation administrator decided that this page can only be translated to $3.",
	'tpt-discouraged-language' => "'''Translating to $2 is not a priority for this page.'''

A translation administrator decided to focus the translation efforts on $3.",
	'tpt-discouraged-language-reason' => 'Reason: $1',

	'tpt-priority-languages' => 'A translation administrator has set the priority languages for this group to $1.',
	'tpt-render-summary' => 'Updating to match new version of source page',

	'tpt-download-page' => 'Export page with translations',
	'aggregategroups' => 'Aggregate groups',
	'tpt-aggregategroup-add' => 'Add',
	'tpt-aggregategroup-save' => 'Save',
	'tpt-aggregategroup-add-new' => 'Add a new aggregate group',
	'tpt-aggregategroup-new-name' => 'Name:',
	'tpt-aggregategroup-new-description' => 'Description (optional):',
	'tpt-aggregategroup-remove-confirm' => 'Are you sure you want to delete this aggregate group?',
	'tpt-aggregategroup-invalid-group' => 'Group does not exist',

	'pt-parse-open' => 'Unbalanced &lt;translate> tag.
Translation template: <pre>$1</pre>',
	'pt-parse-close' => 'Unbalanced &lt;/translate> tag.
Translation template: <pre>$1</pre>',
	'pt-parse-nested' => 'Nested &lt;translate> translation units are not allowed.
Tag text: <pre>$1</pre>',
	'pt-shake-multiple' => 'Multiple translation unit markers for one translation unit.
Translation unit text: <pre>$1</pre>',
	'pt-shake-position' => 'Translation unit markers in unexpected position.
Translation unit text: <pre>$1</pre>',
	'pt-shake-empty' => 'Empty translation unit for marker "$1".',

	# logging system
	'log-description-pagetranslation' => 'Log for actions related to the page translation system',
	'log-name-pagetranslation' => 'Page translation log',

	'logentry-pagetranslation-mark' => '$1 {{GENDER:$2|marked}} $3 for translation',
	'logentry-pagetranslation-unmark' => '$1 {{GENDER:$2|removed}} $3 from translation',

	'logentry-pagetranslation-moveok' => '$1 {{GENDER:$2|completed}} renaming of translatable page $3 to $4',
	'logentry-pagetranslation-movenok' => '$1 {{GENDER:$2|encountered}} a problem while moving page $3 to $4',

	'logentry-pagetranslation-deletefok' => '$1 {{GENDER:$2|completed}} deletion of translatable page $3',
	'logentry-pagetranslation-deletefnok' => '$1 {{GENDER:$2|failed}} to delete $3 which belongs to translatable page $4',
	'logentry-pagetranslation-deletelok' => '$1 {{GENDER:$2|completed}} deletion of translation page $3',
	'logentry-pagetranslation-deletelnok' => '$1 {{GENDER:$2|failed}} to delete $3 which belongs to translation page $4',

	'logentry-pagetranslation-encourage' => '$1 {{GENDER:$2|encouraged}} translation of $3',
	'logentry-pagetranslation-discourage' => '$1 {{GENDER:$2|discouraged}} translation of $3',

	'logentry-pagetranslation-prioritylanguages-unset' => '$1 {{GENDER:$2|removed}} priority languages from translatable page $3',
	'logentry-pagetranslation-prioritylanguages' => '$1 {{GENDER:$2|set}} the priority languages for translatable page $3 to $5',
	'logentry-pagetranslation-prioritylanguages-force' => '$1 {{GENDER:$2|limited}} languages for translatable page $3 to $5',

	'logentry-pagetranslation-associate' => '$1 {{GENDER:$2|added}} translatable page $3 to aggregate group $4',
	'logentry-pagetranslation-dissociate' => '$1 {{GENDER:$2|removed}} translatable page $3 from aggregate group $4',

	# move page replacement
	'pt-movepage-title' => 'Move translatable page "$1"',
	'pt-movepage-blockers' => 'The translatable page cannot be moved to a new name because of the following {{PLURAL:$1|error|errors}}:',
	'pt-movepage-block-base-exists' => 'The target translatable page "[[:$1]]" exists.',
	'pt-movepage-block-base-invalid' => 'The target translatable page name is not a valid title.',
	'pt-movepage-block-tp-exists' => 'The target translation page "[[:$2]]" exists.',
	'pt-movepage-block-tp-invalid' => 'The target translation page title for "[[:$1]]" would be invalid (too long?).',
	'pt-movepage-block-section-exists' => 'The target page "[[:$2]]" for the translation unit exists.',
	'pt-movepage-block-section-invalid' => 'The target page title for "[[:$1]]" for the translation unit would be invalid (too long?).',
	'pt-movepage-block-subpage-exists' => 'The target subpage "[[:$2]]" exists.',
	'pt-movepage-block-subpage-invalid' => 'The target subpage title for "[[:$1]]" would be invalid (too long?).',

	'pt-movepage-list-pages' => 'List of pages to move',
	'pt-movepage-list-translation' => 'Translation {{PLURAL:$1|page|pages}}',
	'pt-movepage-list-section' => 'Translation unit {{PLURAL:$1|page|pages}}',
	'pt-movepage-list-other' => 'Other sub{{PLURAL:$1|page|pages}}',
	'pt-movepage-list-count' => 'In total $1 {{PLURAL:$1|page|pages}} to move.',

	'pt-movepage-legend' => 'Move translatable page',
	'pt-movepage-current' => 'Current name:',
	'pt-movepage-new' => 'New name:',
	'pt-movepage-reason' => 'Reason:',
	'pt-movepage-subpages' => 'Move all subpages',

	'pt-movepage-action-check' => 'Check if the move is possible',
	'pt-movepage-action-perform' => 'Do the move',
	'pt-movepage-action-other' => 'Change target',

	'pt-movepage-intro' => 'This special page allows you to move pages which are marked for translation.
The move action will not be instant, because many pages will need to be moved.
While the pages are being moved, it is not possible to interact with the pages in question.
Failures will be logged in the [[Special:Log/pagetranslation|page translation log]] and they need to be repaired by hand.',

	'pt-movepage-logreason' => 'Part of translatable page "$1".',
	'pt-movepage-started' => 'The base page is now moved.
Please check the [[Special:Log/pagetranslation|page translation log]] for errors and completion message.',

	'pt-locked-page' => 'This page is locked because the translatable page is currently being moved.',

	'pt-deletepage-lang-title' => 'Deleting translation page "$1".',
	'pt-deletepage-full-title' => 'Deleting translatable page "$1".',

	'pt-deletepage-invalid-title' => 'The specified page is not valid.',
	'pt-deletepage-invalid-text' => 'The specified page is not a translatable page nor a translation page.',

	'pt-deletepage-action-check' => 'List pages to be deleted',
	'pt-deletepage-action-perform' => 'Do the deletion',
	'pt-deletepage-action-other' => 'Change target',

	'pt-deletepage-lang-legend' => 'Delete translation page',
	'pt-deletepage-full-legend' => 'Delete translatable page',
	'pt-deletepage-any-legend' => 'Delete translatable page or translation page',
	'pt-deletepage-current' => 'Page name:',
	'pt-deletepage-reason' => 'Reason:',
	'pt-deletepage-subpages' => 'Delete all subpages',

	'pt-deletepage-list-pages' => 'List of pages to delete',
	'pt-deletepage-list-translation' => 'Translation pages',
	'pt-deletepage-list-section' => 'Translation unit pages',
	'pt-deletepage-list-other' => 'Other subpages',
	'pt-deletepage-list-count' => 'In total $1 {{PLURAL:$1|page|pages}} to delete.',

	'pt-deletepage-full-logreason' => 'Part of translatable page "$1".',
	'pt-deletepage-lang-logreason' => 'Part of translation page "$1".',
	'pt-deletepage-started' => 'Please check the [[Special:Log/pagetranslation|page translation log]] for errors and completion message.',

	'pt-deletepage-intro' => 'This special page allows you delete a whole translatable page, or an individual translation page in a language.
The delete action will not be instant, because all the pages depending on them will also be deleted.
Failures will be logged in the [[Special:Log/pagetranslation|page translation log]] and they need to be repaired by hand.',
);

/** Message documentation (Message documentation)
 * @author Amire80
 * @author Darth Kule
 * @author EugeneZelenko
 * @author Fryed-peach
 * @author Liangent
 * @author Lloffiwr
 * @author Mormegil
 * @author Nemo bis
 * @author Nike
 * @author Purodha
 * @author Raymond
 * @author Shirayuki
 * @author Siebrand
 * @author Slboat
 * @author Umherirrender
 */
$messages['qqq'] = array(
	'pagetranslation' => '{{doc-special|PageTranslation}}
[[Image:Page translation admin view.png|thumb|Admin view]]',
	'right-pagetranslation' => '{{doc-right|pagetranslation}}',
	'action-pagetranslation' => '{{doc-action|pagetranslation}})',
	'tpt-desc' => '{{desc|name=Translate - Page Translation|url=http://www.mediawiki.org/wiki/Extension:Translate/PageTranslation}}',
	'tpt-section' => '[[File:Page_translation_mark_view.png|thumb|Page translation]]
A screenshot of the translation administration page is available.

Parameters:
* $1 - the identifier of the unit, or the string "Page display title" (special unit identifier for page title; hard-coded)',
	'tpt-section-new' => '[[File:Page_translation_mark_view.png|thumb|Page translation]]
A screenshot of the translation administration page is available.

Parameters:
* $1 - the identifier of the unit, or the string "Page display title" (special unit identifier for page title; hard-coded)',
	'tpt-section-deleted' => 'See definition on [[mw:Help:Extension:Translate/Glossary]].

Parameters:
* $1 - the identifier of the unit, or the string "Page display title" (special unit identifier for page title; hard-coded)',
	'tpt-template' => 'See definition on [[mw:Help:Extension:Translate/Glossary]].',
	'tpt-templatediff' => 'See definition on [[mw:Help:Extension:Translate/Glossary]].',
	'tpt-sections-oldnew' => '"New and existing" refers to the sum of: (a) new translation units in a translatable page, plus (b) the already existing ones from previous version of a translatable page.',
	'tpt-sections-deleted' => 'See definition on [[mw:Help:Extension:Translate/Glossary]].',
	'tpt-sections-template' => '[[File:Page_translation_mark_view.png|thumb|Page translation]]
The template used by translations of the translatable page, extracted from the source text. Shown on the translation administration page; a screenshot is available.',
	'tpt-action-nofuzzy' => 'See [[mw:Help:Extension:Translate/Page translation administration#Changing the source text]] for context.',
	'tpt-badtitle' => 'Parameters:
* $1 - page title',
	'tpt-nosuchpage' => 'Parameters:
* $1 - page title',
	'tpt-oldrevision' => 'Error message displayed when trying to mark an older page revision for translation. Parameters:
* $1 is a page title.
* $2 is a page link.',
	'tpt-notsuitable' => '{{doc-important|Do not translate "<code>&lt;nowiki>&lt;translate>&lt;/nowiki></code>".}}
Parameters:
* $1 - page title
* $2 - (Unused) revision ID',
	'tpt-saveok' => '* $1 - page title
* $2 - count of sections which can be used with PLURAL
* $3 - URL',
	'tpt-offer-notify' => 'Message displayed on [[Special:PageTranslation]] after marking a page for translation when the marking user also has right to notify translators.

Parameters:
* $1 - a URL to [[Special:NotifyTranslators]] with the marked page preselected',
	'tpt-badsect' => 'See definition on [[mw:Help:Extension:Translate/Glossary]].
Parameters:
* $1 - the identifier of the unit
* $2 - the number of the unit',
	'tpt-showpage-intro' => 'See definition on [[mw:Help:Extension:Translate/Glossary]].',
	'tpt-mark-summary' => 'This message is used as an edit summary.',
	'tpt-edit-failed' => 'Used as error message. Parameters:
* $1 - page title',
	'tpt-duplicate' => 'Used as error message.

See definition on [[mw:Help:Extension:Translate/Glossary]].

Parameters:
* $1 - translation unit ID (name)',
	'tpt-already-marked' => 'See definition on [[mw:Help:Extension:Translate/Glossary]].',
	'tpt-unmarked' => 'Used as success message.

Translate this as "Page $1 has been unmarked for translation".

See definition on [[mw:Help:Extension:Translate/Glossary]].

Parameters:
* $1 - page title',
	'tpt-list-nopages' => 'See definition on [[mw:Help:Extension:Translate/Glossary]].',
	'tpt-new-pages-title' => 'Header in [[Special:PageTranslation]] [[Image:Page translation admin view.png|thumb|Admin view]]',
	'tpt-old-pages-title' => 'Header in [[Special:PageTranslation]] [[Image:Page translation admin view.png|thumb|Admin view]]',
	'tpt-other-pages-title' => 'Header in [[Special:PageTranslation]] [[Image:Page translation admin view.png|thumb|Admin view]]',
	'tpt-discouraged-pages-title' => 'Header in [[Special:PageTranslation]] [[Image:Page translation admin view.png|thumb|Admin view]]',
	'tpt-new-pages' => '$1 is the number of pages in the following list. [[Image:Page translation admin view.png|thumb|Admin view]]',
	'tpt-old-pages' => 'The words "some version" refer to "one version of the page", or "a single version of each of the pages", respectively. Each page can have either one or none of its versions marked for translaton.
* $1 - the number of pages
[[Image:Page translation admin view.png|thumb|Admin view]]',
	'tpt-other-pages' => '$1 is the number of pages in the following list. [[Image:Page translation admin view.png|thumb|Admin view]]',
	'tpt-discouraged-pages' => '$1 is the number of pages in the following list. [[Image:Page translation admin view.png|thumb|Admin view]]',
	'tpt-select-prioritylangs' => 'Label for the input box to enter preferred languages',
	'tpt-select-prioritylangs-force' => 'Label for the checkbox to make the translation restriction',
	'tpt-select-prioritylangs-reason' => 'Label for the textbox to enter reason for restriction.
{{Identical|Reason}}',
	'tpt-sections-prioritylangs' => 'Section title in [[Special:PageTranslation]].
{{Identical|Priority language}}',
	'tpt-rev-mark' => 'Possible page action and link text in [[Special:PageTranslation]]. In parenthesis after page name. [[Image:Page translation admin view.png|thumb|Admin view]]',
	'tpt-rev-unmark' => 'Possible page action and link text in [[Special:PageTranslation]]. In parenthesis after page name. [[Image:Page translation admin view.png|thumb|Admin view]]',
	'tpt-rev-discourage' => 'Possible page action and link text in [[Special:PageTranslation]]. In parenthesis after page name. [[Image:Page translation admin view.png|thumb|Admin view]]',
	'tpt-rev-encourage' => 'Possible page action and link text in [[Special:PageTranslation]]. In parenthesis after page name. [[Image:Page translation admin view.png|thumb|Admin view]]
{{Identical|Restore}}',
	'tpt-rev-mark-tooltip' => 'Tooltip for page action link text in [[Special:PageTranslation]] [[Image:Page translation admin view.png|thumb|Admin view]]',
	'tpt-rev-unmark-tooltip' => 'Tooltip for page action link text in [[Special:PageTranslation]] [[Image:Page translation admin view.png|thumb|Admin view]]',
	'tpt-rev-discourage-tooltip' => 'Tooltip for page action link text in [[Special:PageTranslation]] [[Image:Page translation admin view.png|thumb|Admin view]]',
	'tpt-rev-encourage-tooltip' => 'Tooltip for page action link text in [[Special:PageTranslation]] [[Image:Page translation admin view.png|thumb|Admin view]]',
	'translate-tag-translate-link-desc' => 'Link at the top of translatable pages, see [[mw:Help:Extension:Translate/Translation example]] for context.',
	'translate-tag-markthis' => 'See definition on [[mw:Help:Extension:Translate/Glossary]].',
	'translate-tag-markthisagain' => '"has changes" is to be understood as "has been altered/edited".

Parameters:
* $1 - a link which points to the diff
* $2 - a link which points to ...',
	'translate-tag-hasnew' => '"has changes" is to be understood as "has been altered/edited". $1 is a URL to view changes.',
	'tpt-translation-intro' => 'Header of a translation page (see definition on [[mw:Help:Extension:Translate/Glossary]]).

Parameters:
* $1 - URL
* $2 - page title
* $3 - translation percentage',
	'tpt-languages-legend' => 'The caption of a language selector displayed using <code>&lt;languages /&gt;</code>, e.g. on [[Project list]].
{{Identical|Otherlanguages}}',
	'tpt-languages-separator' => '{{optional}}',
	'tpt-languages-zero' => 'Tooltip for a link in &lt;language /> when language is included because it is a priority language, but translation does not yet exists. It links directly to the translation view.',
	'tpt-tab-translate' => 'Used as label for the tab.

Replaces the edit tab with translation tab for translation pages.
{{Identical|Translate}}',
	'tpt-target-page' => 'Message displayed when trying to edit a translatable page directly. Parameters:
* $1 is the translatable page
* $2 is a link to the translation tool for the translatable page.',
	'tpt-unknown-page' => 'See [[mw:Help:Extension:Translate/Translation example]] for context on page translation feature.',
	'tpt-translation-restricted' => 'Error message shown to user when translation to a language which is restricted by translation admin.

Parameters:
* $1 - reason',
	'tpt-discouraged-language-force' => 'Error shown along with group description if the language is prevented from translation for the selected language.

* $2 is the language that to which the user asked to translate the page.
* $3 is the names of the translation languages.',
	'tpt-discouraged-language' => 'Warning shown along with group description if the language is discouraged from translation for the selected language.

* $2 is the language that to which the user asked to translate the page.
* $3 is the names of the translation languages.',
	'tpt-discouraged-language-reason' => '$1 is the reason for the priority language definition.
Used only if a reason was provided in the following messages:
* {{msg-mw|tpt-discouraged-language-force}}
* {{msg-mw|tpt-discouraged-language}}
{{Identical|Reason}}',
	'tpt-priority-languages' => 'Message to be shown before the messagestats table. $1 is a comma-separated list of language codes.',
	'aggregategroups' => '{{doc-special|AggregateGroups}}',
	'tpt-aggregategroup-add' => 'Label for the button to add a new page to aggregate group in [[Special:AggregateGroups]].
{{Identical|Add}}',
	'tpt-aggregategroup-save' => 'Label for the button to save a new aggregate group in [[Special:AggregateGroups]].
{{Identical|Save}}',
	'tpt-aggregategroup-add-new' => 'Label for the link that gives a form to enter new group details in [[Special:AggregateGroups]]',
	'tpt-aggregategroup-new-name' => 'Label for the name field in [[Special:AggregateGroups]].
{{Identical|Name}}',
	'tpt-aggregategroup-new-description' => 'Label for the description field in [[Special:AggregateGroups]].
{{Identical|Description}}',
	'tpt-aggregategroup-remove-confirm' => 'Confirmation message shown while user tried to delete an aggregate group in [[Special:AggregateGroups]]',
	'tpt-aggregategroup-invalid-group' => 'Show on [[Special:AggregateGroups]] after remove button of a group, if the stored group id does not match any currently known groups.',
	'pt-parse-open' => 'Error shown after an attempt to mark a page for translation, see [[mw:Help:Extension:Translate/Page translation administration]] for context.

"Translation template" is the structure of a translation page, where the place for the translations of each section is marked with a placeholder.

Parameters:
* $1 - translation template
See also:
* {{msg-mw|Pt-parse-close}}',
	'pt-parse-close' => 'Error shown after an attempt to mark a page for translation, see [[mw:Help:Extension:Translate/Page translation administration]] for context.

"Translation template" is the structure of a translation page, where the place for the translations of each section is marked with a placeholder.

Parameters:
* $1 - translation template
See also:
* {{msg-mw|Pt-parse-open}}',
	'pt-parse-nested' => 'Error shown after an attempt to mark a page for translation, see [[mw:Help:Extension:Translate/Page translation administration]] for context.

See definitions on [[mw:Help:Extension:Translate/Glossary]].

Parameters:
* $1 - translation unit (=section) text',
	'pt-shake-multiple' => 'Each translation unit (=section) can only contain one marker.

Parameters:
* $1 - translation unit (=section) text',
	'pt-shake-position' => 'Error shown after an attempt to mark a page for translation, see [[mw:Help:Extension:Translate/Page translation administration]] for context.

See definitions on [[mw:Help:Extension:Translate/Glossary]].

Parameters:
* $1 - translation unit (=section) text',
	'pt-shake-empty' => 'Translation unit (=section) is empty except for the translation marker (=<nowiki><!--T:1--></nowiki>).

Parameters:
* $1 - translation unit ID',
	'log-description-pagetranslation' => 'Description of a log type',
	'log-name-pagetranslation' => '{{doc-logpage}}',
	'logentry-pagetranslation-mark' => '{{logentry|[[Special:Log/pagetranslation]]}}',
	'logentry-pagetranslation-unmark' => '{{logentry|[[Special:Log/pagetranslation]]}}',
	'logentry-pagetranslation-moveok' => '{{logentry|[[Special:Log/pagetranslation]]}}',
	'logentry-pagetranslation-movenok' => '{{logentry|[[Special:Log/pagetranslation]]}}',
	'logentry-pagetranslation-deletefok' => '{{logentry|[[Special:Log/pagetranslation]]}}',
	'logentry-pagetranslation-deletefnok' => '{{logentry|[[Special:Log/pagetranslation]]}}',
	'logentry-pagetranslation-deletelok' => '{{logentry|[[Special:Log/pagetranslation]]}}',
	'logentry-pagetranslation-deletelnok' => '{{logentry|[[Special:Log/pagetranslation]]}}',
	'logentry-pagetranslation-encourage' => '{{logentry|[[Special:Log/pagetranslation]]}}',
	'logentry-pagetranslation-discourage' => '{{logentry|[[Special:Log/pagetranslation]]}}',
	'logentry-pagetranslation-prioritylanguages-unset' => '{{logentry|[[Special:Log/pagetranslation]]}}',
	'logentry-pagetranslation-prioritylanguages' => '{{logentry|[[Special:Log/pagetranslation]]}}
* $5 is list of languages (A, B and C)',
	'logentry-pagetranslation-prioritylanguages-force' => '{{logentry|[[Special:Log/pagetranslation]]}}
* $5 is list of languages (A, B and C)',
	'logentry-pagetranslation-associate' => '{{logentry|[[Special:Log/pagetranslation]]}}
* $4 is the name of the aggregate group',
	'logentry-pagetranslation-dissociate' => '{{logentry|[[Special:Log/pagetranslation]]}}
* $4 is the name of the aggregate group',
	'pt-movepage-title' => 'Used as page title.

See definition on [[mw:Help:Extension:Translate/Glossary]].

Parameters:
* $1 - old page title',
	'pt-movepage-blockers' => 'See definition on [[mw:Help:Extension:Translate/Glossary]].

Followed by any of the following error messages:
* {{msg-mw|Pt-movepage-block-base-exists}}
* {{msg-mw|Pt-movepage-block-base-invalid}}
* {{msg-mw|Pt-movepage-block-tp-exists}}
* {{msg-mw|Pt-movepage-block-tp-invalid}}
* {{msg-mw|Pt-movepage-block-section-exists}}
* {{msg-mw|Pt-movepage-block-section-invalid}}
* {{msg-mw|Pt-movepage-block-subpage-exists}}
* {{msg-mw|Pt-movepage-block-subpage-invalid}}

Parameters:
* $1 - number of error messages',
	'pt-movepage-block-base-exists' => 'Error message to indicate a base page exists and a translatable page cannot be renamed. Parameters:
* $1 is a pre-existing page name.',
	'pt-movepage-block-base-invalid' => 'The "target page" is the new title of the translatable page, see definition on [[mw:Help:Extension:Translate/Glossary]].',
	'pt-movepage-block-tp-exists' => 'translation page is a translated version of a translatable page.

Parameters:
* $1 - (Unused) old page title
* $2 - new page title
{{Related|Pt-movepage-block-exists}}',
	'pt-movepage-block-tp-invalid' => 'This message may mean:
* The user tried to move the page "[[:$1]]" to a new page title.
* If successful, the page, along with the translation units will be moved to the target pages.
* But the destination page title for the translation page would be invalid (too long?).

See definition on [[mw:Help:Extension:Translate/Glossary]].

Parameters:
* $1 - old page title (translatable page title)
{{Related|Pt-movepage-block-invalid}}',
	'pt-movepage-block-section-exists' => 'Section page is a translation of one section. Translation page consists of many translation sections.

Parameters:
* $1 - (Unused) old page title
* $2 - new page title
{{Related|Pt-movepage-block-exists}}',
	'pt-movepage-block-section-invalid' => 'This message may mean:
* The user tried to move the page "[[:$1]]" to a new page title.
* If successful, the page, along with the translation units will be moved to the target pages.
* But the destination page title for the translation unit would be invalid (too long?).
Parameters:
* $1 - old page title (translatable page title)
{{Related|Pt-movepage-block-invalid}}',
	'pt-movepage-block-subpage-exists' => 'Subpage is here any subpage of translation page, which is not a translated version of the translatable page.

Parameters:
* $1 - (Unused) old page title
* $2 - new page title
{{Related|Pt-movepage-block-exists}}',
	'pt-movepage-block-subpage-invalid' => 'This message may mean:
* The user tried to move the page "[[:$1]]" to a new page title.
* If successful, the page, along with the translation units will be moved to the target pages.
* But the destination subpage title for the translation unit would be invalid (too long?).
Parameters:
* $1 - old page title (translatable page title)
{{Related|Pt-movepage-block-invalid}}',
	'pt-movepage-list-pages' => 'Used as section header.

See definition on [[mw:Help:Extension:Translate/Glossary]].

Parameters:
* $1 - (Unused) number of old pages to move
{{Related|Pt-movepage-list}}',
	'pt-movepage-list-translation' => 'Used as section header.

See definition on [[mw:Help:Extension:Translate/Glossary]].

Parameters:
* $1 - number of translation pages
{{Related|Pt-movepage-list}}',
	'pt-movepage-list-section' => 'Used as section header.

See definition on [[mw:Help:Extension:Translate/Glossary]].

Parameters:
* $1 - number of translation unit pages (section pages)
{{Related|Pt-movepage-list}}',
	'pt-movepage-list-other' => 'Header of a list of additional subpages (other than translation pages) of the translatable page being moved, when the user selected the option to move subpages as well.

See definition on [[mw:Help:Extension:Translate/Glossary]].

Parameters:
* $1 - number of subpages
{{Related|Pt-movepage-list}}',
	'pt-movepage-list-count' => 'Used to indicate how many pages will be moved.

Parameters:
* $1 - number of pages
See also:
* {{msg-mw|Pt-deletepage-list-count}}',
	'pt-movepage-legend' => 'See definition on [[mw:Help:Extension:Translate/Glossary]].',
	'pt-movepage-reason' => '{{Identical|Reason}}',
	'pt-movepage-action-other' => "Button label on the special page 'Move translateable page'. See [[mw:File:Translate_manual_-_Page_example_-_21._Move_confirm.png|screenshot]].",
	'pt-movepage-intro' => 'See definitions on [[mw:Help:Extension:Translate/Glossary]].',
	'pt-movepage-logreason' => 'Used as summary.

See definition on [[mw:Help:Extension:Translate/Glossary]].

Parameters:
* $1 - translatable-page title',
	'pt-locked-page' => 'See definition on [[mw:Help:Extension:Translate/Glossary]].',
	'pt-deletepage-lang-title' => 'Used as page title.

See definition on [[mw:Help:Extension:Translate/Glossary]].

See also:
* {{msg-mw|Pt-deletepage-full-title}}',
	'pt-deletepage-full-title' => 'Used as page title.

See definition on [[mw:Help:Extension:Translate/Glossary]].

See also:
* {{msg-mw|Pt-deletepage-lang-title}}',
	'pt-deletepage-invalid-text' => 'See definition on [[mw:Help:Extension:Translate/Glossary]].',
	'pt-deletepage-action-check' => 'This is a button label. "List" is an imperative verb.',
	'pt-deletepage-action-perform' => "Submit button on special page 'Deleting translatable page'. See [[mw:File:Translate_manual_-_Page_example_-_25._Delete_confirm.png|screenshot]].

i think it's mean delete right now.",
	'pt-deletepage-action-other' => "Button label on the special page 'Deleting translatable page'. See [[mw:File:Translate_manual_-_Page_example_-_25._Delete_confirm.png|screenshot]].",
	'pt-deletepage-lang-legend' => 'See definition on [[mw:Help:Extension:Translate/Glossary]].',
	'pt-deletepage-full-legend' => 'See definition on [[mw:Help:Extension:Translate/Glossary]].',
	'pt-deletepage-any-legend' => 'See definitions on [[mw:Help:Extension:Translate/Glossary]].',
	'pt-deletepage-current' => '{{Identical|Page name}}',
	'pt-deletepage-reason' => '{{Identical|Reason}}',
	'pt-deletepage-subpages' => "Checkbox label on special page 'Deleting translatable page'. see [[mw:File:Translate_manual_-_Page_example_-_25._Delete_confirm.png|screenshot]].",
	'pt-deletepage-list-translation' => 'See definition on [[mw:Help:Extension:Translate/Glossary]].',
	'pt-deletepage-list-section' => "Heading in special page 'Deleting translatable page'. See [[mw:File:Translate_manual_-_Page_example_-_25._Delete_confirm.png|screenshot]].",
	'pt-deletepage-list-count' => 'Used to indicate how many pages will be deleted.

Parameters:
* $1 - number of pages
See also:
* {{msg-mw|Pt-movepage-list-count}}',
	'pt-deletepage-full-logreason' => 'Used as summary.

See definition on [[mw:Help:Extension:Translate/Glossary]].

Parameters:
* $1 - translatable-page title
See also:
* {{msg-mw|Pt-deletepage-lang-logreason}}',
	'pt-deletepage-lang-logreason' => 'Used as summary.

See definition on [[mw:Help:Extension:Translate/Glossary]].

Parameters:
* $1 - translatable-page title
See also:
* {{msg-mw|Pt-deletepage-full-logreason}}',
);

/** Afrikaans (Afrikaans)
 * @author Ansumang
 * @author Naudefj
 * @author පසිඳු කාවින්ද
 */
$messages['af'] = array(
	'pagetranslation' => 'Bladsyvertaling',
	'right-pagetranslation' => 'Merk weergawes van bladsye vir vertaling',
	'tpt-desc' => 'Uitbreiding vir die vertaal van wikibladsye',
	'tpt-section' => 'Vertaaleenheid $1',
	'tpt-section-new' => 'Nuwe vertaaleenheid.
Naam: $1',
	'tpt-section-deleted' => 'Vertaaleenheid $1',
	'tpt-template' => 'Bladsysjabloon',
	'tpt-templatediff' => 'Die bladsysjabloon was gewysig.',
	'tpt-diff-old' => 'Vorige teks',
	'tpt-diff-new' => 'Nuwe teks',
	'tpt-submit' => 'Merk die weergawe vir vertaling',
	'tpt-sections-oldnew' => 'Nuwe en bestaande vertaaleenhede',
	'tpt-sections-deleted' => 'Verwyderde vertaaleenhede',
	'tpt-sections-template' => 'Vertaalbladsjabloon',
	'tpt-action-nofuzzy' => 'Het vertalings ongeldig nie',
	'tpt-badtitle' => "Die naam verskaf ($1) is nie 'n geldige bladsynaam nie",
	'tpt-nosuchpage' => 'Bladsy $1 bestaan nie.',
	'tpt-oldrevision' => '$2 is nie die nuutste weergawe van die bladsy [[$1]] nie.
Slegs die nuutste weergawe kan vir vertaling gemerk word.',
	'tpt-notsuitable' => 'Die bladsy $1 is nie geskik om vir vertaling gemerk te word nie.
Sorg dat dit die etiket <nowiki><translate></nowiki> bevat en dat die sintaks daarvan korrek is.',
	'tpt-saveok' => 'Die bladsy [[$1]] is gemerk vir vertaling met $2 uitstaande {{PLURAL:$2|vertaaleenheid|vertaaleenhede}}.
Die bladsy kan nou <span class="plainlinks">[$3 vertaal]</span> word.',
	'tpt-badsect' => '"$1" is nie \'n geldige naam vir vertaaleenheid $2 nie.',
	'tpt-showpage-intro' => 'Hieronder word nuwe, bestaande en verwyderde afdelings gelys.
Alvorens u die weergawe vir vertaling merk, maak seker dat die veranderinge geminimeer word om onnodig werk vir vertalers te voorkom.', # Fuzzy
	'tpt-mark-summary' => 'Merk die weergawe vir vertaling',
	'tpt-edit-failed' => 'Die bladsy "$1" kon nie bygewerk word nie.',
	'tpt-already-marked' => 'Die nuutste weergawe van die bladsy is reeds gemerk vir vertaling.',
	'tpt-unmarked' => 'Bladsy $1 is nie meer vir vertaling gemerk nie.',
	'tpt-list-nopages' => 'Geen bladsye is vir vertaling gemerk of is reg om vir vertaling gemerk te word nie.',
	'tpt-old-pages-title' => 'Bladsye in vertaling',
	'tpt-other-pages-title' => 'Gebreekte bladsye',
	'tpt-discouraged-pages-title' => 'ontmoedig bladsye',
	'tpt-new-pages' => 'Hierdie {{PLURAL:$1|bladsy bevat|bladsye bevat}} teks met vertalings-etikette, maar geen weergawe van die {{PLURAL:$1|bladsy|bladsye}} is vir vertaling gemerk nie.',
	'tpt-old-pages' => "'n Weergawe van die {{PLURAL:$1|bladsy|bladsye}} is reeds vir vertaling gemerk.",
	'tpt-rev-mark' => 'merk vir vertaling',
	'tpt-rev-unmark' => 'verwyder van vertaling',
	'tpt-rev-discourage' => 'ontmoedig',
	'tpt-rev-encourage' => 'herstel',
	'tpt-rev-mark-tooltip' => 'Merk die nuutste weergawe van hierdie bladsy vir vertaling.',
	'tpt-rev-unmark-tooltip' => 'Verwyder hierdie bladsy van die vertaling.',
	'tpt-rev-discourage-tooltip' => 'Ontmoedig verdere vertalings van hierdie bladsy.',
	'tpt-rev-encourage-tooltip' => 'Herstel hierdie bladsy na normaal vertaling.',
	'translate-tag-translate-link-desc' => 'Vertaal die bladsy',
	'translate-tag-markthis' => 'Merk die bladsy vir vertaling',
	'translate-tag-markthisagain' => 'Hierdie bladsy is <span class="plainlinks">[$1 kere gewysig]</span> sedert dit laas <span class="plainlinks">[$2 vir vertaling gemerk was]</span>.',
	'translate-tag-hasnew' => 'Daar is <span class="plainlinks">[$1 wysigings]</span> aan die bladsy gemaak wat nie vir vertaling gemerk is nie.',
	'tpt-translation-intro' => 'Die bladsy is \'n <span class="plainlinks">[$1 vertaalde weergawe]</span> van bladsy [[$2]]. Die vertaling van die bladsy is $3% voltooi.',
	'tpt-languages-legend' => 'Ander tale:',
	'tpt-target-page' => "Hierdie bladsy kan nie handmatig gewysig word nie.
Die bladsy is 'n vertaling van die bladsy [[$1]].
Die vertaling kan bygewerk word via die [$2 vertaalgereedskap].",
	'tpt-unknown-page' => 'Hierdie naamruimte is gereserveer vir die vertalings van bladsye.
Die bladsy wat u probeer wysig kom nie ooreen met een wat vir vertaling gemerk is nie.',
	'tpt-render-summary' => "Besig met bewerkings vanweë 'n nuwe basisweergawe van die bronblad",
	'tpt-download-page' => 'Eksporteer bladsy met vertalings',
	'pt-shake-empty' => 'Leë afdeling vir merker $1.', # Fuzzy
	'pt-movepage-reason' => 'Rede:',
	'pt-deletepage-current' => 'Bladsynaam:',
	'pt-deletepage-reason' => 'Rede:',
);

/** Arabic (العربية)
 * @author Meno25
 * @author OsamaK
 * @author ترجمان05
 * @author روخو
 */
$messages['ar'] = array(
	'pagetranslation' => 'ترجمة صفحة',
	'right-pagetranslation' => 'عّلم نسخًا م هذه الصفحة للترجمة',
	'tpt-desc' => 'امتداد لترجمة محتويات الصفحات',
	'tpt-section' => 'وحدة الترجمة $1',
	'tpt-section-new' => 'وحدة ترجمة جديدة.
الاسم: $1',
	'tpt-section-deleted' => 'وحدة الترجمة $1',
	'tpt-template' => 'قالب صفحة',
	'tpt-templatediff' => 'تغيّر قالب الصفحة.',
	'tpt-diff-old' => 'نص سابق',
	'tpt-diff-new' => 'نص جديد',
	'tpt-submit' => 'علّم هذه النسخة للترجمة',
	'tpt-sections-oldnew' => 'وحدات الترجمة الجديدة والموجودة',
	'tpt-sections-deleted' => 'وحدات الترجمة المحذوفة',
	'tpt-sections-template' => 'قالب صفحة ترجمة',
	'tpt-badtitle' => 'اسم الصّفحة المعطى ($1) ليس عنوانا صحيحا',
	'tpt-nosuchpage' => 'الصفحة $1 غير موجودة',
	'tpt-oldrevision' => '$2 ليست آخر نسخة للصّفحة [[$1]].
فقط آخر النسخ يمكن أن تؤشّر للترجمة.',
	'tpt-notsuitable' => 'الصفحة $1 غير مناسبة للترجمة.
تأكد أن لها وسم <nowiki><translate></nowiki> وأن لها صياغة صحيحة.',
	'tpt-saveok' => 'الصفحة [[$1]] تم التعليم عليها للترجمة ب $2 {{PLURAL:$2|وحدة ترجمة|وحدات ترجمة}}.
الصفحة يمكن الآن <span class="plainlinks">[$3 ترجمتها]</span>.',
	'tpt-badsect' => '"$1" ليس اسمًا صحيحًا لوحدة الترجمة $2.',
	'tpt-showpage-intro' => 'أدناه تُسرد الأقسام الجديدة والموجودة والمحذوفة.
قبل تعليم هذه النسخة للترجمة، تحقق من أن التغييرات على الأقسام مُقلّلة لتفادي العمل غير الضروري من المترجمين.', # Fuzzy
	'tpt-mark-summary' => 'علَّم هذه النسخة للترجمة',
	'tpt-edit-failed' => 'تعذّر تحديث الصفحة: $1',
	'tpt-already-marked' => 'آخر نسخة من هذه الصفحة مُعلّمة بالفعل للترجمة.',
	'tpt-unmarked' => 'الصفحة $1 لم تعد مُعلّمة للترجمة',
	'tpt-list-nopages' => 'لا صفحات مُعلّمة للترجمة أو جاهزة للتعليم للترجمة.',
	'tpt-new-pages-title' => 'صفحات مقترحة للترجمة',
	'tpt-old-pages-title' => 'صفحات تحت الترجمة',
	'tpt-new-pages' => '{{PLURAL:$1|هذه الصفحة تحتوي|هذه الصفحات تحتوي}} على نص بوسوم ترجمة، لكن لا نسخة من {{PLURAL:$1|هذه الصفحة|هذه الصفحات}} معلمة حاليا للترجمة.',
	'tpt-old-pages' => 'إحدى نسخ {{PLURAL:$1||هذه الصفحة|هاتان الصفحتان|هذه الصفحات}} عُلّمت للترجمة.',
	'tpt-select-prioritylangs-reason' => 'السبب:',
	'tpt-rev-unmark' => 'إزالة هذه الصفحة من الترجمة', # Fuzzy
	'tpt-rev-encourage' => 'استرجاع',
	'translate-tag-translate-link-desc' => 'ترجم هذه الصفحة',
	'translate-tag-markthis' => 'علّم هذه الصفحة للترجمة',
	'translate-tag-markthisagain' => 'هذه الصفحة بها <span class="plainlinks">[$1 تغيير]</span> منذ تم <span class="plainlinks">[$2 تعليمها للترجمة]</span> لآخر مرة.',
	'translate-tag-hasnew' => 'هذه الصفحة تحتوي على <span class="plainlinks">[$1 تغييرات]</span> غير معلمة للترجمة.',
	'tpt-translation-intro' => 'هذه الصفحة هي <span class="plainlinks">[$1 نسخة مترجمة]</span> لصفحة [[$2]] والترجمة مكتملة ومحدثة بنسبة $3%.',
	'tpt-languages-legend' => 'لغات أخرى:',
	'tpt-target-page' => 'لا يمكن تحديث هذه الصفحة يدويًا.
هذه الصفحة ترجمة لصفحة [[$1]] ويمكن تحديث الترجمة باستخدام [$2 أداة الترجمة].',
	'tpt-unknown-page' => 'هذا النطاق محجوز لترجمات صفحات المحتوى.
الصفحة التي تحاول تعديلها لا يبدو أنها تتبع أي صفحة معلمة للترجمة.',
	'tpt-render-summary' => 'تحديث لمطابقة نسخة صفحة المصدر الجديدة',
	'tpt-download-page' => 'صدّر الصفحة مع الترجمات',
	'tpt-aggregategroup-add' => 'أضف',
	'tpt-aggregategroup-save' => 'احفظ',
	'tpt-aggregategroup-new-name' => 'الاسم:',
	'pt-movepage-block-tp-exists' => 'صفحة الهدف المترجمة [[:$2]] موجودة.',
	'pt-movepage-list-pages' => 'قائمة الصفحات التي ستنقل',
	'pt-movepage-list-translation' => 'صفحات الترجمة', # Fuzzy
	'pt-movepage-list-other' => 'صفحات فرعية أخرى', # Fuzzy
	'pt-movepage-current' => 'الاسم الحالي:',
	'pt-movepage-new' => 'الاسم الجديد:',
	'pt-movepage-reason' => 'السبب:',
	'pt-movepage-subpages' => 'انقل جميع الصفحات الفرعية',
	'pt-movepage-action-check' => 'تحقق اذا كان النقل ممكنا',
	'pt-movepage-action-perform' => 'لا تنقل',
	'pt-movepage-action-other' => 'تغيير الهدف',
	'pt-deletepage-action-other' => 'غيّر الهدف',
	'pt-deletepage-current' => 'اسم الصفحة:',
	'pt-deletepage-reason' => 'السبب:',
	'pt-deletepage-subpages' => 'أحذف جميع الصفحات الفرعية',
	'pt-deletepage-list-translation' => 'صفحات الترجمة',
	'pt-deletepage-list-other' => 'صفحات فرعية أخرى',
);

/** Aramaic (ܐܪܡܝܐ)
 * @author Basharh
 */
$messages['arc'] = array(
	'pagetranslation' => 'ܬܘܪܓܡܐ ܕܦܐܬܐ',
	'aggregategroups' => 'ܐܠܐܡ ܟܢܘܫܬ̈ܐ',
);

/** Egyptian Spoken Arabic (مصرى)
 * @author Meno25
 */
$messages['arz'] = array(
	'pagetranslation' => 'ترجمه صفحة',
	'right-pagetranslation' => 'عّلم نسخًا م هذه الصفحه للترجمة',
	'tpt-desc' => 'امتداد لترجمه محتويات الصفحات',
	'tpt-section' => 'وحده الترجمه $1',
	'tpt-section-new' => 'وحده ترجمه جديده.
الاسم: $1',
	'tpt-section-deleted' => 'وحده الترجمه $1',
	'tpt-template' => 'قالب صفحة',
	'tpt-templatediff' => 'تغيّر قالب الصفحه.',
	'tpt-diff-old' => 'نص سابق',
	'tpt-diff-new' => 'نص جديد',
	'tpt-submit' => 'علّم هذه النسخه للترجمة',
	'tpt-sections-oldnew' => 'وحدات الترجمه الجديده والموجودة',
	'tpt-sections-deleted' => 'وحدات الترجمه المحذوفة',
	'tpt-sections-template' => 'قالب صفحه ترجمة',
	'tpt-badtitle' => 'اسم الصّفحه المعطى ($1) ليس عنوانا صحيحا',
	'tpt-oldrevision' => '$2 ليست آخر نسخه للصّفحه [[$1]].
فقط آخر النسخ يمكن أن تؤشّر للترجمه.',
	'tpt-notsuitable' => 'الصفحه $1 غير مناسبه للترجمه.
تأكد أن لها وسم <nowiki><translate></nowiki> وأن لها صياغه صحيحه.',
	'tpt-saveok' => 'الصفحه [[$1]] تم التعليم عليها للترجمه ب $2 {{PLURAL:$2|وحده ترجمة|وحدات ترجمة}}.
الصفحه يمكن الآن <span class="plainlinks">[$3 ترجمتها]</span>.',
	'tpt-badsect' => '"$1" ليس اسمًا صحيحًا لوحده الترجمه $2.',
	'tpt-showpage-intro' => 'أدناه تُسرد الأقسام الجديده والموجوده والمحذوفه.
قبل تعليم هذه النسخه للترجمه، تحقق من أن التغييرات على الأقسام مُقلّله لتفادى العمل غير الضرورى من المترجمين.', # Fuzzy
	'tpt-mark-summary' => 'علَّم هذه النسخه للترجمة',
	'tpt-edit-failed' => 'تعذّر تحديث الصفحة: $1',
	'tpt-already-marked' => 'آخر نسخه من هذه الصفحه مُعلّمه بالفعل للترجمه.',
	'tpt-list-nopages' => 'لا صفحات مُعلّمه للترجمه أو جاهزه للتعليم للترجمه.',
	'tpt-new-pages' => '{{PLURAL:$1|هذه الصفحه تحتوي|هذه الصفحات تحتوي}} على نص بوسوم ترجمه، لكن لا نسخه من {{PLURAL:$1|هذه الصفحة|هذه الصفحات}} معلمه حاليا للترجمه.',
	'tpt-old-pages' => 'إحدى نسخ {{PLURAL:$1||هذه الصفحة|هاتان الصفحتان|هذه الصفحات}} عُلّمت للترجمه.',
	'translate-tag-translate-link-desc' => 'ترجمه هذه الصفحة',
	'translate-tag-markthis' => 'علّم هذه الصفحه للترجمة',
	'translate-tag-markthisagain' => 'هذه الصفحه بها <span class="plainlinks">[$1 تغيير]</span> منذ تم <span class="plainlinks">[$2 تعليمها للترجمة]</span> لآخر مره.',
	'translate-tag-hasnew' => 'هذه الصفحه تحتوى على <span class="plainlinks">[$1 تغييرات]</span> غير معلمه للترجمه.',
	'tpt-translation-intro' => 'هذه الصفحه هى <span class="plainlinks">[$1 نسخه مترجمة]</span> لصفحه [[$2]] والترجمه مكتمله ومحدثه بنسبه $3%.',
	'tpt-languages-legend' => 'لغات أخرى:',
	'tpt-target-page' => 'لا يمكن تحديث هذه الصفحه يدويًا.
هذه الصفحه ترجمه لصفحه [[$1]] ويمكن تحديث الترجمه باستخدام [$2 أداه الترجمة].',
	'tpt-unknown-page' => 'هذا النطاق محجوز لترجمات صفحات المحتوى.
الصفحه التى تحاول تعديلها لا يبدو أنها تتبع أى صفحه معلمه للترجمه.',
	'tpt-render-summary' => 'تحديث لمطابقه نسخه صفحه المصدر الجديدة',
	'tpt-download-page' => 'صدّر الصفحه مع الترجمات',
);

/** Assamese (অসমীয়া)
 * @author Bishnu Saikia
 * @author Chaipau
 */
$messages['as'] = array(
	'pagetranslation' => 'পৃষ্ঠা ভাঙনি',
	'tpt-template' => 'পৃষ্ঠা সাঁচ',
	'tpt-diff-old' => 'আগৰ পাঠ্য',
	'tpt-diff-new' => 'নতুন পাঠ্য',
	'tpt-submit' => 'এই সংস্কৰণ ভাঙনিৰ বাবে বাচক',
	'tpt-other-pages-title' => 'সংযোগহীন পৃষ্ঠাসমূহ',
	'tpt-discouraged-pages-title' => 'নিৰুত্সাহজনক পৃষ্ঠাসমূহ',
	'tpt-select-prioritylangs-reason' => 'কাৰণ:',
	'tpt-sections-prioritylangs' => 'প্ৰাথমিক ভাষাসমূহ',
	'tpt-rev-discourage' => 'নিৰুত্সাহ',
	'tpt-rev-encourage' => 'পুনঃসংস্থাপন কৰক',
	'translate-tag-translate-link-desc' => 'এই পৃষ্ঠা ভাঙনি কৰক',
	'tpt-languages-legend' => 'অন্য ভাষা:',
	'tpt-languages-zero' => 'এই ভাষাৰ অনুবাদ আৰম্ভ কৰক',
	'tpt-discouraged-language-reason' => 'কাৰণ: $1',
	'tpt-aggregategroup-add' => 'যোগ কৰক',
	'tpt-aggregategroup-save' => 'সাঁচি থওক',
	'tpt-aggregategroup-new-name' => 'নাম:',
	'tpt-aggregategroup-new-description' => 'বিৱৰণ (বৈকল্পিক):',
	'tpt-aggregategroup-invalid-group' => 'এই গোট পোৱা নগ’ল',
	'pt-movepage-list-translation' => 'ভাঙনি পৃষ্ঠাসমূহ', # Fuzzy
	'pt-movepage-current' => 'সাম্প্ৰতিক নাম:',
	'pt-movepage-new' => 'নতুন নাম:',
	'pt-movepage-reason' => 'কাৰণ:',
	'pt-movepage-action-perform' => 'স্থানান্তৰ নকৰিব',
	'pt-deletepage-current' => 'পৃষ্ঠাৰ নাম:',
	'pt-deletepage-reason' => 'কাৰণ:',
);

/** Asturian (asturianu)
 * @author Esbardu
 * @author Xuacu
 */
$messages['ast'] = array(
	'pagetranslation' => 'Traducción de páxines',
	'right-pagetranslation' => 'Marcar versiones de páxines pa traducir',
	'action-pagetranslation' => 'alministrar les páxines traducibles',
	'tpt-desc' => 'Estensión pa traducir páxines de conteníu',
	'tpt-section' => 'Unidá de traducción $1',
	'tpt-section-new' => 'Nueva unidá de traducción.
Nome: $1',
	'tpt-section-deleted' => 'Unidá de traducción $1',
	'tpt-template' => 'Plantía de páxina',
	'tpt-templatediff' => 'La plantía de páxina camudó.',
	'tpt-diff-old' => 'Testu anterior',
	'tpt-diff-new' => 'Testu nuevu',
	'tpt-submit' => 'Marcar esta versión pa traducir',
	'tpt-sections-oldnew' => 'Unidaes de traducción nueves e esistentes',
	'tpt-sections-deleted' => 'Unidaes de traducción desaniciaes',
	'tpt-sections-template' => 'Plantía de páxina de traducción',
	'tpt-action-nofuzzy' => 'Nun invalidar les traducciones',
	'tpt-badtitle' => 'El nome que-y disti a la páxina ("$1") nun ye un títulu válidu',
	'tpt-nosuchpage' => 'La páxina $1 nun esiste',
	'tpt-oldrevision' => '$2 nun y la cabera versión de la páxina [[$1]].
Sólo les caberes versiones se puen marcar pa traducir.',
	'tpt-notsuitable' => 'La páxina "$1" nun ye válida pa traducir.
Comprueba que tenga les etiquetes <nowiki><translate></nowiki> y una sintaxis válida.',
	'tpt-saveok' => 'A páxina [[$1]] marcose pa traducir con {{PLURAL:$2|una unidá de traducción|$2 unidaes de traducción}}.
La páxina agora se pue <span class="plainlinks">[$3 traducir]</span>.',
	'tpt-offer-notify' => 'Pue <span class="plainlinks">[$1 avisar a los traductores]</span> sobre esta páxina.',
	'tpt-badsect' => '"$1" nun ye un nome válidu pa la unidá de traducción $2.',
	'tpt-showpage-intro' => 'Abaxo ta la llista de les unidaes de traducción nueves, esistentes y desaniciaes.
Enantes de marcar esta versión pa traducir, comprueba que los cambios fechos nes unidaes de traducción seyan mínimos pa evitar trabayu innecesariu de los traductores.',
	'tpt-mark-summary' => 'Marcó esta versión pa traducir',
	'tpt-edit-failed' => 'Nun se pudo anovar la páxina: $1',
	'tpt-duplicate' => "El nome de la unidá de traducción  $1 s'utiliza más d'una vegada.",
	'tpt-already-marked' => "La cabera versión d'esta páxina yá se marcó pa traducir.",
	'tpt-unmarked' => 'La páxina "$1" yá nun ta marcada pa traducir.',
	'tpt-list-nopages' => 'Nun hai páxina dala marcada pa traducir nin preparada pa marcase pa traducir.',
	'tpt-new-pages-title' => 'Páxines propuestes pa traducción',
	'tpt-old-pages-title' => 'Páxines en traducción',
	'tpt-other-pages-title' => 'Páxines frañaes',
	'tpt-discouraged-pages-title' => 'Páxines desaconseyaes',
	'tpt-new-pages' => "{{PLURAL:$1|Esta páxina contién|Estes páxines contienen}} testu con etiquetes de traducción, pero denguna versión {{PLURAL:$1|d'esta páxina|d'estes páxines}} ta marcada pa traducir anguaño.",
	'tpt-old-pages' => "Dalguna versión {{PLURAL:$1|d'esta páxina|d'estes páxines}} se marcó pa traducir.",
	'tpt-other-pages' => "Hai {{PLURAL:$1|una versión vieya d'esta páxina marcada|delles versiones vieyes d'estes páxines marcaes}} pa traducir, pero {{PLURAL:$1|a cabera versión|les caberes versiones}} nun se {{PLURAL:$1|pue|pueden}} marcar pa traducir.",
	'tpt-discouraged-pages' => "Ta desaconseyao facer más traducciones {{PLURAL:$1|d'esta páxina|d'estes páxines}}.",
	'tpt-select-prioritylangs' => 'Llista de códigos de les llingües prioritaries separtaos por comes:',
	'tpt-select-prioritylangs-force' => 'Torgar les traducciones a llingües distintes de les prioritaries',
	'tpt-select-prioritylangs-reason' => 'Motivu:',
	'tpt-sections-prioritylangs' => 'Llingües prioritaries',
	'tpt-rev-mark' => 'marcar pa traducir',
	'tpt-rev-unmark' => 'desaniciar de la traducción',
	'tpt-rev-discourage' => 'desaconseyar',
	'tpt-rev-encourage' => 'restaurar',
	'tpt-rev-mark-tooltip' => "Marcar la cabera versión d'esta páxina pa traducir.",
	'tpt-rev-unmark-tooltip' => 'Desaniciar esta páxina de la traducción.',
	'tpt-rev-discourage-tooltip' => "Desaconseyar más traducciones d'esta páxina.",
	'tpt-rev-encourage-tooltip' => 'Restaurar esta páxina a traducción normal.',
	'translate-tag-translate-link-desc' => 'Traducir esta páxina',
	'translate-tag-markthis' => 'Marcar esta páxina pa traducir',
	'translate-tag-markthisagain' => 'Esta páxina tien <span class="plainlinks">[$1 cambios]</span> dende que se <span class="plainlinks">[$2 marcó pa traducir]</span> la última vegada.',
	'translate-tag-hasnew' => 'Esta páxina contién <span class="plainlinks">[$1 cambios]</span> que nun tan marcaos pa traducir.',
	'tpt-translation-intro' => 'Esta páxina ye una <span class="plainlinks">[$1 versión traducida]</span> de la páxina «[[$2]]» y la traducción ta completada nún $3%.',
	'tpt-languages-legend' => 'Otres llingües:',
	'tpt-languages-zero' => 'Principiar la traducción nesta llingua',
	'tpt-tab-translate' => 'Traducir',
	'tpt-target-page' => 'Esta páxina nun se pue anovar manualmente.
Esta páxina ye una traducción de la páxina [[$1]] y la traducción pue anovase usando [$2 la ferramienta de traducción].',
	'tpt-unknown-page' => 'Esti espaciu de nomes ta acutáu pa les traducciones de les páxines de conteníu.
La páxina que tas intentando editar paez que nun correspuende con denguna páxina marcada pa traducir.',
	'tpt-translation-restricted' => "Un alministrador de traducciones torgó la traducción d'esta páxina a esta llingua.

Motivu: $1",
	'tpt-discouraged-language-force' => 'Un alministrador de traducciones llendó les llingües a les que se pue traducir esta páxina. Esta llingua nun ta ente elles.

Motivu: $1',
	'tpt-discouraged-language' => 'Esta llingua nun ta ente les llingües prioritaries que definió pa la páxina un alministrador.

Motivu: $1',
	'tpt-discouraged-language-reason' => 'Motivu: $1',
	'tpt-priority-languages' => "Un alministrador de traducciones definió les llingües prioritaries d'esti grupu como $1.",
	'tpt-render-summary' => 'Anovando pa casar cola nueva versión de la páxina orixinal',
	'tpt-download-page' => 'Esportar la páxina con traducciones',
	'aggregategroups' => "Grupos d'agregación",
	'tpt-aggregategroup-add' => 'Amestar',
	'tpt-aggregategroup-save' => 'Guardar',
	'tpt-aggregategroup-add-new' => "Amestar un nuevu grupu d'agregación",
	'tpt-aggregategroup-new-name' => 'Nome:',
	'tpt-aggregategroup-new-description' => 'Descripción (opcional):',
	'tpt-aggregategroup-remove-confirm' => '¿Tas seguru de que quies desaniciar esti grupu agregáu?',
	'tpt-aggregategroup-invalid-group' => 'El grupu nun esiste',
	'pt-parse-open' => 'Etiqueta &lt;translate> desequilibrada.
Plantía de traducción: <pre>$1</pre>',
	'pt-parse-close' => 'Etiqueta &lt;/translate> desequilibrada.
Plantía de traducción: <pre>$1</pre>',
	'pt-parse-nested' => 'Nun se permiten unidaes de traducción &lt;translate> añeraes.
Testu de la etiqueta: <pre>$1</pre>',
	'pt-shake-multiple' => "Marcadores d'unidá de traducción múltiples pa una unidá de traducción.
Testu de la unidá de traducción: <pre>$1</pre>",
	'pt-shake-position' => "Marcadores d'unidá de traducción en posición inesperada.
Testu de la unidá de traducción: <pre>$1</pre>",
	'pt-shake-empty' => 'Unidá de traducción balera pal marcador «$1».',
	'log-description-pagetranslation' => 'Rexistru de les aiciones rellacionaes col sistema de traducción de páxines',
	'log-name-pagetranslation' => 'Rexistru de traducción de páxines',
	'logentry-pagetranslation-mark' => '$1 {{GENDER:$2|marcó}} $3 pa traducir',
	'logentry-pagetranslation-unmark' => '$1 {{GENDER:$2|quitó}} $3 de les páxines a traducir',
	'logentry-pagetranslation-moveok' => "$1 {{GENDER:$2|completó}}'l renomáu de la páxina traducible $3 a $4",
	'logentry-pagetranslation-movenok' => '$1 {{GENDER:$2|alcontróse}} un problema al mover $3 a $4',
	'logentry-pagetranslation-deletefok' => "$1 {{GENDER:$2|completó}}'l desaniciu de la páxina traducible $3",
	'logentry-pagetranslation-deletefnok' => '$1 {{GENDER:$2|nun pudo}} desaniciar $3 que pertenez a la páxina traducible $4',
	'logentry-pagetranslation-deletelok' => "$1 {{GENDER:$2|completó}}'l desaniciu de la páxina de traducción $3",
	'logentry-pagetranslation-deletelnok' => '$1 {{GENDER:$2|nun pudo}} desaniciar $3 que pertenez a la páxina de traducción $4',
	'logentry-pagetranslation-encourage' => '$1 {{GENDER:$2|afaló}} la traducción de $3',
	'logentry-pagetranslation-discourage' => '$1 {{GENDER:$2|desaconseyó}} traducir $3',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1 {{GENDER:$2|desanició}} les llingües prioritaries de la páxina traducible $3',
	'logentry-pagetranslation-prioritylanguages' => '$1 {{GENDER:$2|configuró}} les llingües prioritaries pa la páxina traducible $3 a $5',
	'logentry-pagetranslation-prioritylanguages-force' => '$1 {{GENDER:$2|llimitó}} les llingües pa la páxina traducible $3 a $5',
	'logentry-pagetranslation-associate' => '$1 {{GENDER:$2|amestó}} la páxina traducible $3 al grupu agregáu $4',
	'logentry-pagetranslation-dissociate' => '$1 {{GENDER:$2|desanició}} la páxina traducible $3 del grupu agregáu $4',
	'pt-movepage-title' => 'Treslladar la páxina traducible $1',
	'pt-movepage-blockers' => 'Nun se pue treslladar la páxina traducible a un nome nuevu {{PLURAL:$1|pol siguiente error|polos siguientes errores}}:',
	'pt-movepage-block-base-exists' => 'La páxina traducible de destín «[[:$1]]» yá esiste.',
	'pt-movepage-block-base-invalid' => 'El nome de la páxina traducible de destín nun ye un títulu válidu.',
	'pt-movepage-block-tp-exists' => 'La páxina de traducción de destín [[:$2]] yá esiste.',
	'pt-movepage-block-tp-invalid' => 'El títulu de la páxina de traducción de destín pa [[:$1]] sedría inválidu (¿demasiao llargu?).',
	'pt-movepage-block-section-exists' => 'La páxina de destín [[:$2]] de la unidá de traducción yá esiste.',
	'pt-movepage-block-section-invalid' => 'El títulu de la páxina de destín pa «[[:$1]]» de la unidá de traducción sedría inválidu (¿demasiao llargu?).',
	'pt-movepage-block-subpage-exists' => 'La subpáxina de destín [[:$2]] yá esiste.',
	'pt-movepage-block-subpage-invalid' => 'El títulu de la subpáxina de destín pa [[:$1]] sedría inválidu (¿demasiao llargu?).',
	'pt-movepage-list-pages' => 'Llista de páxines a treslladar',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Páxina|Páxines}} de traducción',
	'pt-movepage-list-section' => '{{PLURAL:$1|Páxina|Páxines}} de la unidá de traducción',
	'pt-movepage-list-other' => '{{PLURAL:$1|Otra subpáxina|Otres subpáxines}}',
	'pt-movepage-list-count' => 'En total $1 {{PLURAL:$1|páxina|páxines}} a treslladar.',
	'pt-movepage-legend' => 'Treslladar la páxina traducible',
	'pt-movepage-current' => 'Nome actual:',
	'pt-movepage-new' => 'Nome nuevu:',
	'pt-movepage-reason' => 'Motivu:',
	'pt-movepage-subpages' => 'Treslladar toles subpáxines',
	'pt-movepage-action-check' => "Comprobar si'l treslláu ye posible",
	'pt-movepage-action-perform' => 'Facer el treslláu',
	'pt-movepage-action-other' => 'Camudar el destín',
	'pt-movepage-intro' => "Esta páxina especial te permite treslladar páxines que tan marcaes pa traducir.
L'aición de treslláu nun sedrá inmediata, porque ye necesario mover munches páxines.
Mentanto se treslladen les páxines, nun ye posible interactuar coles mesmes.
Los fallos quedarán rexistraos nel [[Special:Log/pagetranslation|rexistru de traducción de páxines]] y tendrán de reparase a mano.",
	'pt-movepage-logreason' => 'Parte de la páxina traducible $1.',
	'pt-movepage-started' => 'La páxina base ta treslladada.
Por favor, mira nel [[Special:Log/pagetranslation|rexistru de traducción de páxines]] pa ver los errores y mensaxe de completáu.',
	'pt-locked-page' => 'Esta páxina ta bloquiada porque ta treslladandose la páxina traducible.',
	'pt-deletepage-lang-title' => 'Desaniciando la páxina de traducción $1.',
	'pt-deletepage-full-title' => 'Desaniciando la páxina traducible $1.',
	'pt-deletepage-invalid-title' => 'La páxina especificada nun ye válida.',
	'pt-deletepage-invalid-text' => 'La páxina especificada nun ye una páxina traducible nin una páxina de traducción.',
	'pt-deletepage-action-check' => 'Llista de páxines a desaniciar',
	'pt-deletepage-action-perform' => 'Facer el desaniciu',
	'pt-deletepage-action-other' => 'Camudar el destín',
	'pt-deletepage-lang-legend' => 'Desaniciar la páxina de traducción',
	'pt-deletepage-full-legend' => 'Desaniciar la páxina traducible',
	'pt-deletepage-any-legend' => 'Desaniciar la páxina traducible o la páxina de traducción',
	'pt-deletepage-current' => 'Nome de la páxina:',
	'pt-deletepage-reason' => 'Motivu:',
	'pt-deletepage-subpages' => 'Desaniciar toles subpáxines',
	'pt-deletepage-list-pages' => 'Llista de páxines a desaniciar',
	'pt-deletepage-list-translation' => 'Páxines de traducción',
	'pt-deletepage-list-section' => 'Páxines de la unidá de traducción',
	'pt-deletepage-list-other' => 'Otres subpáxines',
	'pt-deletepage-list-count' => 'En total $1 {{PLURAL:$1|páxina|páxines}} a desaniciar.',
	'pt-deletepage-full-logreason' => 'Parte de la páxina traducible $1.',
	'pt-deletepage-lang-logreason' => 'Parte de la páxina de traducción $1.',
	'pt-deletepage-started' => 'Por favor, mira nel [[Special:Log/pagetranslation|rexistru de traducción de páxines]] pa ver los errores y mensaxe de completáu.',
	'pt-deletepage-intro' => "Esta páxina especial te permite desaniciar una páxina traducible entera o una páxina individual de traducción a una llingua.
L'aición de desaniciu nun sedrá inmediata, porque tamién se desaniciarán toles páxines que dependan d'elles.
Los fallos quedarán rexistraos nel [[Special:Log/pagetranslation|rexistru de traducción de páxines]] y tendrán de reparase a mano.",
);

/** Azerbaijani (azərbaycanca)
 * @author Cekli829
 * @author Khan27
 */
$messages['az'] = array(
	'pagetranslation' => 'Tərcümə səhifəsi',
	'right-pagetranslation' => 'Tərcümə üçün səhifənin versiyalarını işarələ',
	'action-pagetranslation' => 'tərcümə oluna bilən səhifələri idarə et',
	'tpt-desc' => 'Məzmun səhifələrin tərcüməsi üçün əlavə olundu',
	'tpt-section' => 'Tərcümə bölümü $1',
	'tpt-section-new' => 'Yeni tərcümə bölümü.
Ad: $1',
	'tpt-section-deleted' => 'Tərcümə bölümü $1',
	'tpt-template' => 'Səhifə şablonu',
	'tpt-templatediff' => 'Səhifə şablonu dəyişdirildi.',
	'tpt-diff-old' => 'Əvvəlki mətn',
	'tpt-diff-new' => 'Yeni mətn',
	'tpt-submit' => 'Bu versiyanı tərcümə üçün işarələ',
	'tpt-sections-oldnew' => 'Yeni və mövcud tərcümə bölümləri',
	'tpt-sections-deleted' => 'Tərcümə bölümləri silindi',
	'tpt-sections-template' => 'Tərcümə səhifəsi şablonu',
	'tpt-action-nofuzzy' => 'Tərcümələri etibarsız etməyin',
	'tpt-badtitle' => 'Verilmiş səhifə adı ($1) etibarlı ad deyil',
	'tpt-nosuchpage' => '$1 səhifəsi mövcud deyil',
	'tpt-oldrevision' => '$2 [[$1]] səhifəsinin son versiyası deyil.
Yalnız ən son versiyalar tərcümə üçün işarə oluna bilər.',
	'tpt-discouraged-language-reason' => 'Təsvir: $1',
	'tpt-download-page' => 'Tərcüməli səhifələri köçür',
	'aggregategroups' => 'Aqreqat qrupları',
	'tpt-aggregategroup-add' => 'Əlavə et',
	'tpt-aggregategroup-save' => 'Saxla',
	'tpt-aggregategroup-add-new' => 'Yeni aqreqat qrupu əlavə et',
	'tpt-aggregategroup-new-name' => 'Ad:',
	'tpt-aggregategroup-new-description' => 'Açıqlama (istəyə bağlı):',
	'tpt-aggregategroup-remove-confirm' => 'Bu aqreqat qrupunu silmək istədiyindən əminsən?',
	'tpt-aggregategroup-invalid-group' => 'Qrup mövcud deyil',
	'pt-movepage-new' => 'Yeni ad:',
	'pt-movepage-reason' => 'Səbəb:',
	'pt-deletepage-current' => 'Səhifə adı:',
	'pt-deletepage-reason' => 'Səbəb:',
);

/** South Azerbaijani (تورکجه)
 * @author Ebrahimi-amir
 * @author Mousa
 */
$messages['azb'] = array(
	'pagetranslation' => 'صحیفه چئویرمه‌سی',
	'right-pagetranslation' => 'صحیفه‌لرین نوسخه‌لرینی چئویرمک اوچون نیشانلا',
	'action-pagetranslation' => 'چئویرمه‌لی صحیفه‌لری ایداره ائت',
	'tpt-desc' => 'مقاله‌لری چئویرمک اوچون اوزانتی',
	'tpt-section' => 'چئویرمه بیریمی $1',
	'tpt-section-new' => 'یئنی چئویرمه بیریمی.
آد: $1',
	'tpt-section-deleted' => 'چئویرمه بیریمی $1',
	'tpt-template' => 'صحیفه شابلونو',
	'tpt-templatediff' => 'صحیفه شابلونو دَییشدیریلیب‌دیر.',
	'tpt-diff-old' => 'قاباقکی یازی',
	'tpt-diff-new' => 'یئنی یازی',
	'tpt-submit' => 'بو نوسخه‌نی چئویرمگه نیشانلا',
	'tpt-sections-oldnew' => 'یئنی و اولان چئویرمه بیریملری',
	'tpt-sections-deleted' => 'سیلینمیش چئویرمه بیریملری',
	'tpt-sections-template' => 'چئویرمک صحیفه‌سی شابلونو',
	'tpt-action-nofuzzy' => 'چئویرمه‌لری اعتیبارسیز ائتمه',
	'tpt-badtitle' => 'وئریلمیش صحیفه آدی ($1) گئچرلی بیر باشلیق دئییل',
	'tpt-nosuchpage' => '$1 صحیفه‌سی یوخدور',
	'tpt-oldrevision' => '$2، [[$1]] صحیفه‌سینین سون نوسخه‌سی دئییل.
یالنیز سون وئرسیالاری چئویرمگه نیشانلاماق اولا بیلر.',
	'tpt-notsuitable' => '$1 صحیفه‌سی چئویرمگه اویغون دئییل.
آرخایین اولون اونون <nowiki><translate></nowiki> اِتیکِتلری و گئچرلی سینتکسی واردیر.',
	'tpt-saveok' => '[[$1]] صحیفه‌سی {{PLURAL:$2|بیر|$2}} چئویرمک بیریمی‌له چئویرمگه نیشانلانیب‌دیر.
بو صحیفه‌نی ایندی <span class="plainlinks">[$3 چئویرمک اولا بیلر]</span>.',
	'tpt-badsect' => '$2 چئویرمک بیریمی اوچون «$1» اویغون آد دئییل.',
	'tpt-showpage-intro' => 'آشاغیدا یئنی، اولان و سیلینن چئویرمه بیریملری لیست اولوبلار.
بو نوسخه‌نی چئویرمگه نیشانلاماقدان قاباق، باخین کی چئویرمک بیریملرینه اولان دَییشیکلیکلر ان آز اولسون کی چئویرنلره آرتیق گرکسیز ایشین قاباغی آلینسین.',
	'tpt-mark-summary' => 'بو نوسخه‌نی چئویرمگه نیشانلادی',
	'tpt-edit-failed' => 'صحیفه گونجل‌لننمه‌دی: $1',
	'tpt-duplicate' => '$1 چئویرمک بیریمی آدی بیر دفعه‌دن چوخ ایشلنیب‌دیر.',
	'tpt-already-marked' => 'بو صحیفه‌نین سون نوسخه‌سی قاباقجادان چئویرمگه نیشانلانیب‌دیر.',
	'tpt-unmarked' => '$1 صحیفه‌سی داها چئویرمگه نیشانلانماییب‌دیر.',
	'tpt-list-nopages' => 'هئچ بیر صحیفه چئویرمگه نیشانلانماییب‌دیر یادا چئویرمگه حاضیر دئییل.',
	'tpt-new-pages-title' => 'چئویرمگه اؤنریلن صحیفه‌لر',
	'tpt-old-pages-title' => 'چئویرمه‌ده صحیفه‌لر',
	'tpt-other-pages-title' => 'سینمیش صحیفه‌لر',
	'tpt-discouraged-pages-title' => 'چئویرمه‌سی اؤنریلمه‌ین صحیفه‌لر',
	'tpt-new-pages' => 'بو {{PLURAL:$1|صحیفه‌ده|صحیفه‌لرده}} چئویرمک اِتیکِتلری اولان یازیلار واردیر، اما ایندی بو {{PLURAL:$1|صحیفه‌نین|صحیفه‌لرین}} هئچ بیر {{PLURAL:$1|نوسخه‌سی|نوسخه‌لری}} چئویرمگه نیشانلانماییب‌دیر.',
	'tpt-old-pages' => 'بو {{PLURAL:$1|صحیفه‌نین|صحیفه‌لرین}} بعضی نوسخه‌لری چئویرمگه نیشانلانیب‌دیر.',
	'tpt-other-pages' => 'بو {{PLURAL:$1|صحیفه‌نین بیر اسکی نوسخه‌سی|صحیفه‌لرین اسکی نوسخه‌لری}} چئویرمگه نیشانلانیب‌دیر،
اما سون {{PLURAL:$1|نوسخه چئویرمگه نیشانلانا بیلمز|نوسخه‌لر چئویرمگه نیشانلانا بیلمزلر}}.',
	'tpt-discouraged-pages' => 'بو {{PLURAL:$1|صحیفه‌نین|صحیفه‌لرین}} داها چئویرمگی توصیه اولونمور.',
	'tpt-select-prioritylangs' => 'کاما ایله آیریلمیش اؤنجه‌لیک دیل کودلاری:',
	'tpt-select-prioritylangs-force' => 'اؤنجه‌لیک دیل‌لردن سونراکی دیل‌لره چئویرمه‌نین قاباغینی آل',
	'tpt-select-prioritylangs-reason' => 'ندن:',
	'tpt-sections-prioritylangs' => 'اؤنجه‌لیک دیل‌لری',
	'tpt-rev-mark' => 'چئویرمگه نیشانلا',
	'tpt-rev-unmark' => 'چئویرمک‌دن سیل',
	'tpt-rev-discourage' => 'توصیه ائتمه',
	'tpt-rev-encourage' => 'قایتار',
	'tpt-rev-mark-tooltip' => 'بو صحیفه‌نین سون نوسخه‌سینی چئویرمگه نیشانلا.',
	'tpt-rev-unmark-tooltip' => 'بو صحیفه‌نی چئویرمک‌دن سیل.',
	'tpt-rev-discourage-tooltip' => 'بو صحیفه‌یه داها آرتیق چئویرمگی توصیه ائتمه.',
	'tpt-rev-encourage-tooltip' => 'بو صحیفه‌نی نورمال چئویرمگه قایتار.',
	'translate-tag-translate-link-desc' => 'یو لاییحه‌نی چئویر',
	'translate-tag-markthis' => 'بو صحیفه‌نی چئویرمگه نیشانلا',
	'translate-tag-markthisagain' => 'بو صحیفه‌نین سون دفعه <span class="plainlinks">[$2 چئویرمگه نیشانلان]</span>اندان بویانا <span class="plainlinks">[$1 دَییشیکلیکلر]</span>ی واردیر.',
	'translate-tag-hasnew' => 'بو صحیفه‌نین <span class="plainlinks">[$1 دَییشیکلیکلری]</span> واردیر کی چئویرمگه نیشانلانماییب‌لار.',
	'tpt-translation-intro' => 'بو صحیفه [[$2]] صحیفه‌سینین <span class="plainlinks">[$1 چئویریلمیش نوسخه‌سی]</span>‌دیر و $3٪ چئویرمگی قاباغا گئدیب‌دیر.',
	'tpt-languages-legend' => 'آیری دیل‌لر:',
	'tpt-languages-zero' => 'بو دیله چئویرمگه باشلا',
	'tpt-target-page' => 'بو صحیفه‌نی ال ایله گونج‌لَمک اولماز.
بو صحیفه [[$1]] صحیفه‌سینین چئویرمه‌سیدیر و [$2 چئویرمک آراجی] ایله چئویریله بیلر.',
	'tpt-unknown-page' => 'بو آدفضاسی مقاله‌لری چئویرمگه رِزِرو اولوب‌دور.
سیز چئویرمگه چالیشدیغینیز صحیفه، هئچ بیر چئویرمگه نیشانلانمیش صحیفه‌یه مطابق نظره گلمیر.',
	'tpt-translation-restricted' => 'بو صحیفه‌نین بو دیله چئویرمه‌سی بیر چئویرمک ایداره‌چیسی ایله قاباغی آلینیب‌دیر.

ندن: $1',
	'tpt-discouraged-language-force' => "'''بو صحیفه $2-ه چئویریله بیلمز.'''

بیر چئویرمک ایداره‌چیسی بئله قرار آلیب کی بو صحیفه یالنیز $3-ه چئویریله بیلر.",
	'tpt-discouraged-language' => "'''بو صحیفه‌نی $2-ه چئویرمک بیر اؤنجه‌لیک دئییل.'''

بیر چئویرمک ایداره‌چیسی بئله قرار آلیب کی چئویرمک تمرکزی $3-ه اولسون.",
	'tpt-discouraged-language-reason' => 'ندن: $1',
	'tpt-priority-languages' => 'بیر چئویرمک ایداره‌چیسی، بو قروپون اؤنجه‌لیک دیلینی $1 سئچیب‌دیر.',
	'tpt-render-summary' => 'قایناق صحیفه‌نین یئنی نوسخه‌سی ایله تطبیق اوچون گونجل‌لنیر',
	'tpt-download-page' => 'صحیفه‌نی چئویرمه‌لرله ائشیگه چیخارت',
	'aggregategroups' => 'بیرلشدیریلمیش قروپلار',
	'tpt-aggregategroup-add' => 'آرتیر',
	'tpt-aggregategroup-save' => 'قئید ائت',
	'tpt-aggregategroup-add-new' => 'بیر یئنی بیرلشمه قروپو آرتیر',
	'tpt-aggregategroup-new-name' => 'آد:',
	'tpt-aggregategroup-new-description' => 'توضیح (ایستگه باغلی):',
	'tpt-aggregategroup-remove-confirm' => 'بو بیرلشمه قروپونو سیلمکدن آرخایینسینیز؟',
	'tpt-aggregategroup-invalid-group' => 'قروپ یوخدور',
	'pt-parse-open' => 'بالانس اولمامیش &lt;translate> اِتیکِتی.
چئویرمک شابلونو: <pre>$1</pre>',
	'pt-parse-close' => 'بالانس اولمامیش &lt;/translate> اِتیکِتی.
چئویرمک شابلونو: <pre>$1</pre>',
	'pt-parse-nested' => 'بیر بیری ایچینده اولان &lt;translate> چئویرمه بیریملرینه ایجازه یوخدور.
اِتیکِت یازیسی: <pre>$1</pre>',
	'pt-shake-multiple' => 'بیر چئویرمه بیریمی اوچون، چوخلو چئویرمه بیریم نیشانلایانلاری.
چئویرمه بیریم یازیسی: <pre>$1</pre>',
	'pt-shake-position' => 'گؤزلنیمه‌ین یئرده چوخلو بیریم نیشانلایانلاری.
چئویرمه بیریم یازیسی: <pre>$1</pre>',
	'pt-shake-empty' => '«$1» نیشانلایانی اوچون بوش چئویرمه بیریمی.',
	'log-description-pagetranslation' => 'صحیفه چئویرمه سیستِمینه ایلگیلی ایشلرین قئیدلری',
	'log-name-pagetranslation' => 'صحیفه چئویرمک قئیدلری',
	'logentry-pagetranslation-mark' => '$1، $3-ی چئویرمگه {{GENDER:$2|نیشانلاندیریب}}',
	'logentry-pagetranslation-unmark' => '$1، $3-ی چئویرمک‌دن {{GENDER:$2|سیلدی}}',
	'pt-movepage-title' => '«$1» چئویریله بیلن صحیفه‌نین آدینی دَییشدیر',
	'pt-movepage-blockers' => 'بو {{PLURAL:$1|خطا|خطالار}} اوچون چئویریله بیلن صحیفه‌نین آدی دَییشدیریلنمیر:',
	'pt-movepage-block-base-exists' => '«[[:$1]]» هدف چئویریله بیلن صحیفه، قاباقجادان واردیر.',
	'pt-movepage-block-base-invalid' => 'هدف چئویریله بیلن صحیفه‌نین آدی، گئچرلی بیر باشلیق دئییل.',
	'pt-movepage-block-tp-exists' => '«[[:$2]]» هدف چئویرمک صحیفه‌سی قاباقجادان واردیر.',
	'pt-movepage-block-tp-invalid' => '«[[:$1]]» اوچون هدف چئویرمک صحیفه‌سی باشلیغی گئچرسیز اولار (چوخ اوزون؟).',
	'pt-movepage-block-section-exists' => 'چئویرمه بیریمی اوچون «[[:$2]]» هدف صحیفه‌سی قاباقجادان واردیر.',
	'pt-movepage-block-section-invalid' => 'چئویرمک بیریمی اوچون «[[:$1]]»-ه هدف صحیفه باشلیغی گئچرسیز اولار (چوخ اوزون؟).',
	'pt-movepage-block-subpage-exists' => '«[[:$2]]» هدف آلت‌صحیفه‌سی یوخدور.',
	'pt-movepage-block-subpage-invalid' => '«[[:$1]]» اوچون هدف آلت‌صحیفه باشلیغی گئچرسیز اولار (چوخ اوزون؟).',
	'pt-movepage-list-pages' => 'آدینی دَییشدیره‌جک صحیفه‌لرین لیستی',
	'pt-movepage-list-translation' => 'چئویرمک {{PLURAL:$1|صحیفه‌سی|صحیفه‌لری}}',
	'pt-movepage-list-section' => 'چئویرمک بیریم {{PLURAL:$1|صحیفه‌سی|صحیفه‌لری}}',
	'pt-movepage-list-other' => 'آیری آلت‌{{PLURAL:$1|صحیفه|صحیفه‌لر}}',
	'pt-movepage-list-count' => 'توپلام‌دا آدینی دَییشدیرمگه {{PLURAL:$1|بیر|$1}} صحیفه.',
	'pt-movepage-legend' => 'دَییشدیریله بیلن صحیفه‌نین آدینی دَییشدیر',
	'pt-movepage-current' => 'ایندیکی آد:',
	'pt-movepage-new' => 'یئنی آد:',
	'pt-movepage-reason' => 'ندن:',
	'pt-movepage-subpages' => 'بوتون آلت‌صحیفه‌لرین آدلارینی دَییشدیر',
	'pt-movepage-action-check' => 'آدی دَییشدیرمگین ایمکانی اولماغینی یوخلا',
	'pt-movepage-action-perform' => 'آدی دَییشدیر',
	'pt-movepage-action-other' => 'هدفی دَییشدیر',
	'pt-movepage-intro' => 'بو اؤزل صحیفه سیزه ایجازه وئریر چئویرمگه نیشانلانان صحیفه‌لرین آدلارینی دَییشدیره‌سینیز.
آدی دَییشدیرمک ایشی، بیر آن‌دا اولمایاجاق، نییه کی چوخلو صحیفه‌لرین آدلاری دَییشدیرمک گرکلی اولا بیلر.
صحیفه‌لرین آدلاری دَییشدیریلنده، او صحیفه‌لرله ایشله‌مک ایمکانی اولماز.
موفقیت‌سیزلیکلر [[Special:Log/pagetranslation|صحیفه چئویرمک قئیدلرینده]] قئید اولوناجاقلار و اونلاری ال ایله دوزلتمک گرکلی‌دیر.',
	'pt-movepage-logreason' => '«$1» چئویریله بیلن صحیفه‌نین پارچاسی.',
	'pt-movepage-started' => 'اساس صحیفه ایندی آدی دَییشدیریلیب‌دیر.
لوطفاً خطالار و قورتارماق مئساژلاری اوچون [[Special:Log/pagetranslation|صحیفه چئویرمک قئیدلری]]نی یوخلایین.',
	'pt-locked-page' => 'ایندی چئویریله بیلن صحیفه‌نین آدی دَییشدیلماقدا اولماغینا گؤره، بو صحیفه قیفیل‌لانیب‌دیر.',
	'pt-deletepage-lang-title' => '«$1» چئویرمک صحیفه‌سی سیلینیر.',
	'pt-deletepage-full-title' => '«$1» چئویریله بیلن صحیفه سیلینیر.',
	'pt-deletepage-invalid-title' => 'بیلیندیریلمیش صحیفه گئچرسیزدیر.',
	'pt-deletepage-invalid-text' => 'بیلندیریلمیش صحیفه نه چئویرمک صحیفه‌سی‌دیر و نه چئویریله بیلن صحیفه.',
	'pt-deletepage-action-check' => 'سیلینه‌جک صحیفه‌لری لیست ائت',
	'pt-deletepage-action-perform' => 'سیلمه‌نی ائت',
	'pt-deletepage-action-other' => 'هدفی دَییشدیر',
	'pt-deletepage-lang-legend' => 'چئویرمک صحیفه‌سینی سیل',
	'pt-deletepage-full-legend' => 'چئویریله بیلن صحیفه‌نی سیل',
	'pt-deletepage-any-legend' => 'چئویریله بیلن صحیفه یادا چئویرمک صحیفه‌سینی سیل',
	'pt-deletepage-current' => 'صحیفه آدی:',
	'pt-deletepage-reason' => 'ندن:',
	'pt-deletepage-subpages' => 'بوتون آلت‌صحیفه‌لری سیل',
	'pt-deletepage-list-pages' => 'سیلینه‌جک صحیفه‌لرین لیستی',
	'pt-deletepage-list-translation' => 'چئویرمک صحیفه‌لری',
	'pt-deletepage-list-section' => 'چئویرمک بیریم صحیفه‌لری',
	'pt-deletepage-list-other' => 'آیری آلت‌صحیفه‌لر',
	'pt-deletepage-list-count' => 'توپلام‌دا سیلمگه {{PLURAL:$1|بیر|$1}} صحیفه.',
	'pt-deletepage-full-logreason' => '«$1» چئویریله بیلن صحیفه‌نین پارچاسی.',
	'pt-deletepage-lang-logreason' => '«$1» چئویرمک صحیفه‌سینین پارچاسی.',
	'pt-deletepage-started' => 'لوطفاً خطالار و قورتارماقلار مئساژلاری اوچون [[Special:Log/pagetranslation|صحیفه چئویرمک قئیدلری]]نه باخین.',
	'pt-deletepage-intro' => 'بو اؤزل صحیفه سیزه ایجازه وئریر بیر بوتون چئویریله بیلن صحیفه‌نی، یا دا بیر دیل‌ده بیر تک چئویرمک صحیفه‌سینی سیله‌سینیز.
سیلمک ایشی بیر آن‌دا اولمایاجاق‌دیر، نییه کی اونلارا دایانان بوتون صحیفه‌لر ده گرک سیلینسینلر.
موفقیت‌سیزلیکلر [[Special:Log/pagetranslation|صحیفه چئویرمک قئیدلری]]نده قئید اولوناجاقلار و اونلاری ال ایله دوزلتمک گرکلی‌دیر.',
);

/** Bashkir (башҡортса)
 * @author Haqmar
 */
$messages['ba'] = array(
	'pt-movepage-list-other' => 'Башҡа эске биттәр', # Fuzzy
	'pt-movepage-legend' => 'Тәржемә итеп булған биттәрҙең исемен үҙгәртергә',
	'pt-movepage-current' => 'Хәҙерге исеме:',
	'pt-movepage-new' => 'Яңы исеме:',
	'pt-movepage-reason' => 'Сәбәп:',
	'pt-movepage-subpages' => 'Бар эске биттәрҙең исемен үҙгәртергә',
	'pt-movepage-action-perform' => 'Исемен үҙгәртергә',
	'pt-movepage-action-other' => 'Маҡсатты үҙгәртергә',
	'pt-deletepage-action-check' => 'Юйыласаҡ биттәр исемлеге',
	'pt-deletepage-action-perform' => 'Юйырға',
	'pt-deletepage-action-other' => 'Маҡсатты үҙгәртергә',
	'pt-deletepage-lang-legend' => 'Тәржемә битен юйырға',
	'pt-deletepage-full-legend' => 'Тәржемә итеп булған битте юйырға',
);

/** Bavarian (Boarisch)
 * @author Mucalexx
 */
$messages['bar'] = array(
	'pagetranslation' => 'Seiten ywersétzen',
	'right-pagetranslation' => "Seitenversión fyr d' Ywersétzung markirn",
	'tpt-desc' => 'Daméglichts Ywersétzen voh Inhoidsseiten',
	'tpt-section' => 'Ywersétzungsoahheit $1',
	'tpt-section-new' => 'Neiche Ywersétzungsoahheit. Nåm $1',
	'tpt-section-deleted' => 'Ywersétzungsoahheit $1',
	'tpt-template' => 'Seitenvurlog',
	'tpt-templatediff' => 'Dé Seitenvurlog hod sé gänderd.',
	'tpt-diff-old' => 'Vuriger Text',
	'tpt-diff-new' => 'Neicher Text',
	'tpt-submit' => 'Dé Versión do zur Ywersétzung markirn',
	'tpt-sections-oldnew' => 'Neiche und vurhånderne Ywersétzungsoahheiten',
	'tpt-sections-deleted' => 'Gléschde Ywersétzungsoahheiten',
	'tpt-sections-template' => 'Ywersétzungsseitenvurlog',
	'tpt-action-nofuzzy' => "Sétz d' Ywersétzungen néd ausser Kroft",
	'tpt-badtitle' => 'Da ågeewerne Seitennåm „$1“ is koah gütiger Titl néd',
	'tpt-nosuchpage' => 'Dé Seiten $1 existird néd',
	'tpt-oldrevision' => "$2 is néd d' létzde Versión voh derer Seiten [[$1]].
Netter d' létzde Versión kå zur Ywersétzung markird wern.",
	'tpt-notsuitable' => 'Dé Seiten $1 is néd zum Ywersétzen geignet.
Stö sicher, daas a <nowiki><translate></nowiki>-Tag und gütige Syntax vawendt werd.',
	'tpt-languages-legend' => 'Ånderne Sproochen:',
	'pt-deletepage-any-legend' => 'Ywersétzbore óder ywersétzde Seiten léschen', # Fuzzy
	'pt-deletepage-current' => 'Seitennåm',
	'pt-deletepage-reason' => 'Grund:',
	'pt-deletepage-subpages' => 'Olle Unterseiten léschen',
	'pt-deletepage-list-pages' => "Listen voh dé z' léschenden Seiten",
	'pt-deletepage-list-translation' => 'Ywersétzde Seiten',
	'pt-deletepage-list-section' => 'Obschnitsseiten', # Fuzzy
	'pt-deletepage-list-other' => 'Weiderne Unterseiten',
	'pt-deletepage-list-count' => "Insgsåmt gibts $1 z' léschende {{PLURAL:$1|Seiten|Seiten}}.",
	'pt-deletepage-full-logreason' => 'Teil voh da ywersétzborn Seiten $1.',
	'pt-deletepage-lang-logreason' => 'Teil voh da ywersétzden Seiten $1.',
	'pt-deletepage-started' => "Bittscheh 's [[Special:Log/pagetranslation|Ywersétzungs-Logbuach]] noch Feelern und Ausfiarungsnoochrichten priaffm.",
);

/** Bikol Central (Bikol Central)
 * @author Geopoet
 */
$messages['bcl'] = array(
	'pagetranslation' => 'Dakit-taramon kan pahina',
	'right-pagetranslation' => 'Markahi an mga bersyon kan mga pahina para sa dakit-taramon',
	'tpt-desc' => 'Ekstensyon para sa pagdadakit-taramon kan mga laman nin mga pahina',
	'tpt-section' => 'Yunit kan dakit-taramon $1',
	'tpt-section-new' => 'Bagong yunit kan dakit-taramon.
Pangaran: $1',
	'tpt-section-deleted' => 'Yunit kan dakit-taramon $1',
	'tpt-template' => 'Panguyog kan pahina',
	'tpt-templatediff' => 'An panguyog kan pahina pinagbago.',
	'tpt-diff-old' => 'Dating teksto',
	'tpt-diff-new' => 'Baguhong teksto',
	'tpt-submit' => 'Markahi ining bersyon para sa pagdakit-taramon',
	'tpt-sections-oldnew' => 'Baguhon asin dati nang yaon na mga yunit kan dakit-taramon',
	'tpt-sections-deleted' => 'Pinagpurang mga yunit kan dakit-taramon',
	'tpt-sections-template' => 'Panguyog kan pahina nin dakit-taramon',
	'tpt-action-nofuzzy' => 'Dae pag-imbalidohon an mga dakit-taramon',
	'tpt-badtitle' => 'Ngaran kan pahinang pinagtao ($1) bakong balidong titulo',
	'tpt-nosuchpage' => 'An pahina $1 bakong eksistido',
	'tpt-oldrevision' => 'An $2 bako an pinakabaguhong bersyon kan pahina [[$1]].
An mga pinakabaguhong bersyon sana an puwedeng markahan para sa dakit-taramon.',
	'tpt-notsuitable' => 'An pahina $1 bakong naaangay para sa dakit-taramon.
Himoong segurado na ini igwang <nowiki><translate></nowiki> mga tatak asin igwa nin balidong sintaks.',
	'tpt-saveok' => 'An pahina [[$1]] pinagmarkahan pra sa dakit-taramon na igwang $2 {{PLURAL:$2|yunit kan dakit-taramon|mga yunit kan dakit-taramon}}.
An pahina mapuwede ngunyan na magin <span class="plainlinks">[$3 pinagdakit-taramon]</span>.',
	'tpt-badsect' => 'An "$1" bakong balidong ngaran para sa yunit kan dakit-taramon $2.',
	'tpt-showpage-intro' => 'Yaon sa ibaba an bago, dati na asin pinagburang yunit nin mga dakit-taramon an nagkarilista.
Bago mamarkahan nin bersyon para sa pagdakit-taramon, aramon mo na an mga kaliwatan pasiring sa mga yunit nin dakit-taramon pinagminimisa tanganing likayan an bakong kaipuhanan na trabaho para sa mga translador.',
	'tpt-mark-summary' => 'Markado ining bersyon para sa pagdakit-taramon',
	'tpt-edit-failed' => 'Dae mapanumpayan an pahina: $1',
	'tpt-duplicate' => 'Pangaran kan yunit nin pagdakit-taramon na $1 ginamit nang sobra nin sarong beses.',
	'tpt-already-marked' => 'An pinakahuring bersyon kaining pahina pinagmarkahan na para sa pagdakit-taramon.',
	'tpt-unmarked' => 'An pahina $1 bako na pong markado para sa pagdakit-taramon.',
	'tpt-list-nopages' => 'Mayong mga pahina na markado para sa pagdakit-taramon ni naka-andam na tanganing markado para sa pagdakit-taramon.',
	'tpt-new-pages-title' => 'Mga pahinang pinaghurot para sa pagdakit-taramon',
	'tpt-old-pages-title' => 'Mga pahina na yaon sa pagdakit-taramon',
	'tpt-other-pages-title' => 'Nagkaparasang mga pahina',
	'tpt-discouraged-pages-title' => 'Dae pinagtutugutan na mga pahina',
	'tpt-new-pages' => '{{PLURAL:$1|Ining pahina naglalaman nin|Ining mga pahina naglalaman nin}} teksto na igwa nin mga markang pandakit-taramon, alagad mayong bersyon kan {{PLURAL:$1|ining pahina na|ining mga pahina na}} sa presente markado para sa pagdakit-taramon.',
	'tpt-old-pages' => 'An ibang bersyon kan {{PLURAL:$1|ining pahina igwa nin|ining mga pahina igwa nin}} pinagmarkahan para sa pagdakit-taramon.',
	'tpt-other-pages' => '{{PLURAL:$1|An lumaong bersyon kaining pahina iyo an|An pinakalumaong mga bersyon kaining mga pahina iyo an mga}} markado para sa pagdakit-taramon, alagad an pinakahuri {{PLURAL:$1|bersyon|mga bersyon}} dae mapuwedeng pagmarkahan para sa pagdakit-taramon.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Ining pahina|Ining mga pahina}} dae pinagtutugutan sa dagos na pagdakit-taramon.',
	'tpt-select-prioritylangs' => 'Lista na pinagpalaen nin kama kan mga koda nin pangenot na lengguwahe:',
	'tpt-select-prioritylangs-force' => 'Dae pinagtutugot na mga pagdakit-taramon sa ibang mga lengguwahe kesa pangenot na mga lengguwahe', # Fuzzy
	'tpt-select-prioritylangs-reason' => 'Kadahilanan:',
	'tpt-sections-prioritylangs' => 'Pangenot na mga lengguwahe',
	'tpt-rev-mark' => 'markahan para sa pagdakit-taramon',
	'tpt-rev-unmark' => 'haleon gikan sa pagdakit-taramon',
	'tpt-rev-discourage' => 'Dae pagtugutan',
	'tpt-rev-encourage' => 'balikon',
	'tpt-rev-mark-tooltip' => 'Markahan an pinakahuring bersyon kaining pahina para sa padakit-taramon.',
	'tpt-rev-unmark-tooltip' => 'Haleon ining pahina para sa pagdakit-taramon.',
	'tpt-rev-discourage-tooltip' => 'Dae pagtugutan an dagos na pagdadakit-taramon kaining pahina.',
	'tpt-rev-encourage-tooltip' => 'Balikon ining pahina sa normal na pagdakit-taramon.',
	'translate-tag-translate-link-desc' => 'Ipagdakit-taramon ining pahina',
	'translate-tag-markthis' => 'Markahan ining pahina para ipagdakit-taramon',
	'translate-tag-markthisagain' => 'Ining pahina igwa nin <span class="plainlinks">[$1 mga kaliwatan]</span> poon pa kaitong huri ining <span class="plainlinks">[$2 pinagmarkahan para ipagdakit-taramon]</span>.',
	'translate-tag-hasnew' => 'Ining pahina igwa nin <span class="plainlinks">[$1 mga kaliwatan]</span> na dae pinagmarkahan para ipagdakit-taramon.',
	'tpt-translation-intro' => 'Ining pahina sarong <span class="plainlinks">[$1 pinagdakit-taramon na bersyon]</span> kan pahina [[$2]] asin an pagdakit-taramon na $3% nakumpleto na.',
	'tpt-languages-legend' => 'Ibang mga lengguwahe:',
	'tpt-languages-zero' => 'Magpoon sa pagdakit-taramon para sa lengguwaheng ini',
	'tpt-target-page' => 'Ining pahina dae puwedeng manwal na pagpanumpayan.
Ining pahina sarong dakit-taramon kan pahina [[$1]] asin an pinagdakit-taramon mapuwedeng panumbayan na gamit an [$2 an gamit sa pagdakit-taramon].',
	'tpt-unknown-page' => 'Ining espasyong ngaran nakareserba para sa pahina kan laman nin mga dakit-taramon.
An pahina na saimong boot na pagliliwaton garo habong magtutugot sa arinman na pahinang markado para ipagdakit-taramon.',
	'tpt-translation-restricted' => 'An pagdakit-taramon kaining pahina sa lengguwaheng ini pinagpupugulan kan administrador nin pagdakit-taramon.

Rason: $1',
	'tpt-discouraged-language-force' => "'''Ining pahina dae puwedeng ipagdakit-taramon sa $2.'''

An administrador kan pagdakit-taramon nagdesisyon na ining pahina mapuwedeng sanang ipagdakit-taramon sa $3.",
	'tpt-discouraged-language' => "'''An pagdadakit-taramon sa $2 bakong prayoridad para sa pahinang ini.'''

An administrador kan pagdakit-taramon nagdesisyon na itutok an gibong pagdakit-taramon sa $3.",
	'tpt-discouraged-language-reason' => 'Rason: $1',
	'tpt-priority-languages' => 'An administrador kan pagdakit-taramon ikinaag an prayoridad na mga lengguwahe para kaining grupo sa $1.',
	'tpt-render-summary' => 'Panunumpayan tanganing ipagtugma sa baguhong bersyon kan ginikanang pahina',
	'tpt-download-page' => 'Salimbagong pahina na igwang mga pinagdakit-taramon',
	'aggregategroups' => 'Sinurumpay na mga grupo',
	'tpt-aggregategroup-add' => 'Dugangan',
	'tpt-aggregategroup-save' => 'Ipagtagama',
	'tpt-aggregategroup-add-new' => 'Dugangan nin sarong baguhon na sinurumpay na grupo',
	'tpt-aggregategroup-new-name' => 'An pangaran:',
	'tpt-aggregategroup-new-description' => 'Paglaladawan (puwedeng mayo kaini):',
	'tpt-aggregategroup-remove-confirm' => 'Segurado kan na gusto mong puraon ining sinurumpay na grupo?',
	'tpt-aggregategroup-invalid-group' => 'An grupo bakong eksistido',
	'pt-parse-open' => 'Bakong balansiyadong &lt;translate> marka.
Panguyog sa pagdakit-taramon: <pre>$1</pre>',
	'pt-parse-close' => 'Bakong balansiyadong &lt;/translate> marka.
Panguyog sa pagdakit-taramon: <pre>$1</pre>',
	'pt-parse-nested' => 'Pinagsalagang &lt;translate> mga yunit nin pagdakit-taramon dae itinutugot.
Markang teksto: <pre>$1</pre>',
	'pt-shake-multiple' => 'Mga marka nin dagmangang yunit nin pagdakit-taramon para sa sarong yunit nin pagdakit-taramon.
Teksto sa yunit nin pagdakit-taramon: <pre>$1</pre>',
	'pt-shake-position' => 'Mga marka kan yunit nin dakit-taramon sa bakong pinag-aasahan na posisyon.
Teksto sa yunit in pagdakit-taramon: <pre>$1</pre>',
	'pt-shake-empty' => 'Mayong laman na yunit kan dakit-taramon para sa paramarka na "$1".',
	'log-description-pagetranslation' => 'Magtala para sa mga aksyon na minasumpay sa sistema kan pahina nin dakit-taramon',
	'log-name-pagetranslation' => 'Talaan kan dakit-taramong pahina',
);

/** Belarusian (беларуская)
 * @author Тест
 */
$messages['be'] = array(
	'pt-movepage-reason' => 'Прычына:',
);

/** Belarusian (Taraškievica orthography) (беларуская (тарашкевіца)‎)
 * @author EugeneZelenko
 * @author Jim-by
 * @author Renessaince
 * @author Wizardist
 */
$messages['be-tarask'] = array(
	'pagetranslation' => 'Пераклад старонкі',
	'right-pagetranslation' => 'пазначаць вэрсіяў старонак для перакладу',
	'action-pagetranslation' => 'кіраваньне перакладам старонак',
	'tpt-desc' => 'Пашырэньне для перакладу старонак зьместу',
	'tpt-section' => 'Адзінка перакладу $1',
	'tpt-section-new' => 'Новая адзінка перакладу. Назва: $1',
	'tpt-section-deleted' => 'Адзінка перакладу $1',
	'tpt-template' => 'Старонка шаблёну',
	'tpt-templatediff' => 'Старонка шаблёну была зьменена.',
	'tpt-diff-old' => 'Папярэдні тэкст',
	'tpt-diff-new' => 'Новы тэкст',
	'tpt-submit' => 'Пазначыць гэту вэрсію для перакладу',
	'tpt-sections-oldnew' => 'Новыя і існуючыя адзінкі перакладу',
	'tpt-sections-deleted' => 'Выдаленыя адзінкі перакладу',
	'tpt-sections-template' => 'Шаблён старонкі перакладу',
	'tpt-action-nofuzzy' => 'Не бракаваць пераклады',
	'tpt-badtitle' => 'Пададзеная назва старонкі ($1) не зьяўляецца слушнай',
	'tpt-nosuchpage' => 'Старонка $1 не існуе',
	'tpt-oldrevision' => '$2 не зьяўляецца апошняй вэрсіяй старонкі [[$1]].
Толькі апошнія вэрсіі могуць пазначацца для перакладу.',
	'tpt-notsuitable' => 'Старонка $1 ня можа быць перакладзеная.
Упэўніцеся, што яна ўтрымлівае тэгі <nowiki><translate></nowiki> і мае слушны сынтаксіс.',
	'tpt-saveok' => 'Старонка «$1» была пазначаная для перакладу з $2 {{PLURAL:$2|адзінкай перакладу|адзінкамі перакладу|адзінкамі перакладу}}.
Зараз старонка можа быць <span class="plainlinks">[$3 перакладзеная]</span>.',
	'tpt-offer-notify' => 'Вы можаце <span class="plainlinks">[$1 паведаміць перакладчыкам]</span> пра гэтую старонку.',
	'tpt-badsect' => '«$1» не зьяўляецца слушнай назвай для адзінкі перакладу $2.',
	'tpt-showpage-intro' => 'Ніжэй знаходзяцца новыя, існуючыя і выдаленыя сэкцыі.
Перад пазначэньнем гэтай вэрсіі для перакладу, праверце зьмены ў сэкцыях для таго, каб пазьбегнуць непатрэбнай працы для перакладчыкаў.',
	'tpt-mark-summary' => 'Пазначыў гэтую вэрсію для перакладу',
	'tpt-edit-failed' => 'Немагчыма абнавіць старонку: $1',
	'tpt-duplicate' => 'Назва адзінкі перакладу «$1» скарыстаная больш за адзін раз.',
	'tpt-already-marked' => 'Апошняя вэрсія гэтай старонкі ўжо была пазначана для перакладу.',
	'tpt-unmarked' => 'Старонка $1 болей не пазначаная для перакладу.',
	'tpt-list-nopages' => 'Старонкі для перакладу не пазначаныя альбо не падрыхтаваныя.',
	'tpt-new-pages-title' => 'Старонкі, прапанаваныя да перакладу',
	'tpt-old-pages-title' => 'Старонкі на стадыі перакладу',
	'tpt-other-pages-title' => 'Сапсаваныя старонкі',
	'tpt-discouraged-pages-title' => 'Адхіленыя старонкі',
	'tpt-new-pages' => '{{PLURAL:$1|Гэта старонка ўтрымлівае|Гэтыя старонкі ўтрымліваюць}} тэкст з тэгамі перакладу, але {{PLURAL:$1|пазначанай для перакладу вэрсіі гэтай старонкі|пазначаных для перакладу вэрсіяў гэтых старонак}} няма.',
	'tpt-old-pages' => 'Некаторыя вэрсіі {{PLURAL:$1|гэтай старонкі|гэтых старонак}} былі пазначаны для перакладу.',
	'tpt-other-pages' => '{{PLURAL:$1|Старая вэрсія гэтай старонкі пазначаная|Старыя вэрсіі гэтых старонак пазначаныя}} для перакладу, але {{PLURAL:$1|апошняя вэрсія ня можа быць пазначаная|апошнія вэрсіі ня могуць быць пазначаныя}} для перакладу.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Гэтай старонцы|Гэтым старонкам}} было адхілена ў далейшым перакладзе.',
	'tpt-select-prioritylangs' => 'Сьпіс прыярытэтных кодаў моваў, падзеленых коскамі:',
	'tpt-select-prioritylangs-force' => 'Запабегчы перакладам на адрозныя ад прыярытэтных мовы',
	'tpt-select-prioritylangs-reason' => 'Прычына:',
	'tpt-sections-prioritylangs' => 'Прыярытэтныя мовы',
	'tpt-rev-mark' => 'пазначыць да перакладу',
	'tpt-rev-unmark' => 'выдаліць зь перакладу',
	'tpt-rev-discourage' => 'адхіліць',
	'tpt-rev-encourage' => 'аднавіць',
	'tpt-rev-mark-tooltip' => 'Пазначыць апошнюю вэрсію старонкі да перакладу.',
	'tpt-rev-unmark-tooltip' => 'Выдаліць гэтую старонку зь перакладу.',
	'tpt-rev-discourage-tooltip' => 'Адхіліць далейшыя пераклады гэтай старонкі.',
	'tpt-rev-encourage-tooltip' => 'Аднавіць гэтую старонку да звычайнага перакладу.',
	'translate-tag-translate-link-desc' => 'Перакласьці гэту старонку',
	'translate-tag-markthis' => 'Пазначыць гэту старонку для перакладу',
	'translate-tag-markthisagain' => 'Гэта старонка ўтрымлівае <span class="plainlinks">[$1 зьмены]</span> пасьля апошняй <span class="plainlinks">[$2 пазнакі для перакладу]</span>.',
	'translate-tag-hasnew' => 'Гэта старонка ўтрымлівае <span class="plainlinks">[$1 зьмены]</span> не пазначаныя для перакладу.',
	'tpt-translation-intro' => 'Гэтая старонка — <span class="plainlinks">[$1 перакладзеная вэрсія]</span> старонкі [[$2]]. Пераклад завершаны на $3%.',
	'tpt-languages-legend' => 'Іншыя мовы:',
	'tpt-languages-zero' => 'Пачаць пераклад на гэтую мову',
	'tpt-tab-translate' => 'Перакладаць',
	'tpt-target-page' => 'Гэта старонка ня можа быць абноўлена ўручную.
Гэта старонка зьяўляецца перакладам старонкі [[$1]], пераклад можа быць абноўлены з выкарыстаньнем [$2 інструмэнта перакладу].',
	'tpt-unknown-page' => 'Гэта прастора назваў зарэзэрваваная для перакладаў старонак зьместу.
Старонка, якую Вы спрабуеце рэдагаваць, верагодна не зьвязана зь якой-небудзь старонкай пазначанай для перакладу.',
	'tpt-translation-restricted' => 'Пераклад гэтай старонкі на дадзеную мову быў папярэджаны адміністратарам паракладаў.

Прычына: $1',
	'tpt-discouraged-language-force' => "'''Гэтая старонка ня можа быць перакладзеная на мову $2.'''

Адміністратар перакладу вырашыў, што гэтая старонка можа быць перакладзеная толькі на мовы: $3.",
	'tpt-discouraged-language' => "'''Пераклад на мову $2 не зьяўляецца прыярытэтным.'''

Адміністратар перакладу вырашыў сканцэнтраваць перакладніцкія высілкі на мовах $3.",
	'tpt-discouraged-language-reason' => 'Прычына: $1',
	'tpt-priority-languages' => 'Адміністратар перакладаў вызначыў прыярытэтныя мовы для гэтай групы: $1.',
	'tpt-render-summary' => 'Абнаўленьне для адпаведнасьці новай вэрсіі крынічнай старонкі',
	'tpt-download-page' => 'Экспартаваць старонку з перакладамі',
	'aggregategroups' => 'Абагульняльныя групы',
	'tpt-aggregategroup-add' => 'Дадаць',
	'tpt-aggregategroup-save' => 'Захаваць',
	'tpt-aggregategroup-add-new' => 'Дадаць новую абагульняльную групу',
	'tpt-aggregategroup-new-name' => 'Назва:',
	'tpt-aggregategroup-new-description' => 'Апісаньне (неабавязкова):',
	'tpt-aggregategroup-remove-confirm' => 'Вы ўпэўненыя, што жадаеце выдаліць гэтую абагульняльную групу?',
	'tpt-aggregategroup-invalid-group' => 'Група не існуе',
	'pt-parse-open' => 'Незбалянсаваны тэг &lt;translate>.
Шаблён перакладу: <pre>$1</pre>',
	'pt-parse-close' => 'Незбалянсаваны тэг &lt;/translate>.
Шаблён перакладу: <pre>$1</pre>',
	'pt-parse-nested' => 'Укладзеныя сэкцыі &lt;translate> не дазволеныя.
Тэкст тэгу: <pre>$1</pre>',
	'pt-shake-multiple' => 'Некалькі маркераў сэкцыяў у адной сэкцыі.
Тэкст сэкцыі: <pre>$1</pre>',
	'pt-shake-position' => 'Меткі сэкцыі перакладу ў нечаканых пазыцыях.
Тэкст сэкцыі: <pre>$1</pre>',
	'pt-shake-empty' => 'Пустая сэкцыя перакладу для меткі «$1».',
	'log-description-pagetranslation' => 'Журнал для дзеяньняў зьвязаных з сыстэмай перакладу старонак',
	'log-name-pagetranslation' => 'Журнал перакладу старонак',
	'logentry-pagetranslation-mark' => '$1 {{GENDER:$2|пазначыў|пазначыла}} $3 для перакладу',
	'logentry-pagetranslation-unmark' => '$1 {{GENDER:$2|выкрасьліў|выкрасьліла}} $3 зь перакладаў',
	'logentry-pagetranslation-moveok' => '$1 {{GENDER:$2|выканаў|выканала}} перайменаваньне перакладальнай старонкі з $3 у $4',
	'logentry-pagetranslation-movenok' => '$1 {{GENDER:$2|сутыкнуўся|сутыкнулася}} з праблемай у часе пераносу старонкі з $3 у $4',
	'logentry-pagetranslation-deletefok' => '$1 {{GENDER:$2|выдаліў|выдаліла}} перакладальную старонку $3',
	'logentry-pagetranslation-deletefnok' => '$1 {{GENDER:$2|ня змог|не змагла}} выдаліць $3, якая належыць да перакладальнай старонкі $4',
	'logentry-pagetranslation-deletelok' => '$1 {{GENDER:$2|выдаліў|выдаліла}} перакладальную старонку $3',
	'logentry-pagetranslation-deletelnok' => '$1 {{GENDER:$2|ня змог|не змагла}} выдаліць старонку «$3», якая належыць да перакладальнай старонкі «$4»',
	'logentry-pagetranslation-encourage' => '$1 {{GENDER:$2|дазволіў|дазволіла}} пераклад $3',
	'logentry-pagetranslation-discourage' => '$1 {{GENDER:$2|не дазволіў|не дазволіла}} пераклад $3',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1 {{GENDER:$2|выдаліў|выдаліла}} прыярытэтныя мовы зь перакладальнай старонкі $3',
	'logentry-pagetranslation-prioritylanguages' => '$1 {{GENDER:$2|вызначыў|вызначыла}} прыярытэтныя мовы для перакладальнай старонкі $3: $5',
	'logentry-pagetranslation-prioritylanguages-force' => '$1 {{GENDER:$2|абмежаваў|абмежавала}} мовы для перакладальнай старонкі $3 да $5',
	'logentry-pagetranslation-associate' => '$1 {{GENDER:$2|дадаў|дадала}} перакладальную старонку $3 да агрэгаванай групы $4',
	'logentry-pagetranslation-dissociate' => '$1 {{GENDER:$2|выдаліў|выдаліла}} перакладальную старонку $3 з агрэгаванай групы $4',
	'pt-movepage-title' => 'Перанесьці старонку $1, якую магчыма перакласьці',
	'pt-movepage-blockers' => 'Немагчыма перанесьці старонкі, якія магчыма перакладаць, з-за {{PLURAL:$1|наступнай памылкі|наступных памылак}}:',
	'pt-movepage-block-base-exists' => 'Існуе мэтавая перакладальная старонка «[[:$1]]».',
	'pt-movepage-block-base-invalid' => 'Мэтавая перакладальная старонка мае няслушную назву.',
	'pt-movepage-block-tp-exists' => 'Мэтавая старонка перакладу [[:$2]] існуе.',
	'pt-movepage-block-tp-invalid' => 'Мэтавая назва старонкі да перакладу [[:$1]] будзе няслушнай (занадта доўгая?)',
	'pt-movepage-block-section-exists' => 'Інсуе мэтавая старонка «[[:$2]]» для сэкцыі перакладу.',
	'pt-movepage-block-section-invalid' => 'Мэтавая назва старонкі [[:$1]] для адзінкі перакладу будзе няслушнай (занадта доўгая?).',
	'pt-movepage-block-subpage-exists' => 'Мэтавая падстаронка [[:$2]] існуе.',
	'pt-movepage-block-subpage-invalid' => 'Мэтавая назва падстаронкі [[:$1]] будзе няслушнай (занадта доўгая?).',
	'pt-movepage-list-pages' => 'Сьпіс старонак да пераносу',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Старонка|Старонкі}} да перакладу',
	'pt-movepage-list-section' => '{{PLURAL:$1|Старонка|Старонкі}} адзінкі перакладу',
	'pt-movepage-list-other' => '{{PLURAL:$1|Іншая падстаронка|Іншыя падстаронкі}}',
	'pt-movepage-list-count' => '$1 {{PLURAL:$1|старонка|старонкі|старонак}} для пераносу.',
	'pt-movepage-legend' => 'Перанесьці старонкі, якія магчыма перакласьці',
	'pt-movepage-current' => 'Цяперашняя назва:',
	'pt-movepage-new' => 'Новая назва:',
	'pt-movepage-reason' => 'Прычына:',
	'pt-movepage-subpages' => 'Перанесьці ўсе падстаронкі',
	'pt-movepage-action-check' => 'Праверыць, ці магчымы перанос',
	'pt-movepage-action-perform' => 'Перанесьці',
	'pt-movepage-action-other' => 'Зьмяніць мэту',
	'pt-movepage-intro' => 'Гэтая спэцыяльная старонка дазваляе пераносіць старонкі, пазначаныя да перакладу.
Перанос не адбудзецца імгненна, таму што спатрэбіцца пераносіць шмат старонак.
Падчас пераносу маніпуляцыя са старонкамі будзе немагчымая.
Усе памылкі падчас пераносу будуць занесеныя ў [[Special:Log/pagetranslation|журнал перакладу старонак]], і будзе патрэбная іх ручная апрацоўка.',
	'pt-movepage-logreason' => 'Частка старонкі $1, якую магчыма перакласьці.',
	'pt-movepage-started' => 'Асноўная старонка перанесеная.
Праверце [[Special:Log/pagetranslation|журнал перакладаў старонак]] наконт памылак і паведамленьня пра выкананьне.',
	'pt-locked-page' => 'Гэтая старонка заблякаваная з-за працэсу пераносу старонкі, якую магчыма перакласьці.',
	'pt-deletepage-lang-title' => 'Выдаленьне старонкі перакладу $1.',
	'pt-deletepage-full-title' => 'Выдаленьне старонкі $1, якую магчыма перакладаць.',
	'pt-deletepage-invalid-title' => 'Пазначаная няслушная старонка.',
	'pt-deletepage-invalid-text' => 'Пазначаная старонка не зьяўляецца ні перакладальнай старонкай, ані перакладам.',
	'pt-deletepage-action-check' => 'Сьпіс старонак да выдаленьня',
	'pt-deletepage-action-perform' => 'Выканаць выдаленьне',
	'pt-deletepage-action-other' => 'Зьмяніць мэту',
	'pt-deletepage-lang-legend' => 'Выдаліць старонку перакладу',
	'pt-deletepage-full-legend' => 'Выдаліць старонку, якую магчыма перакладаць',
	'pt-deletepage-any-legend' => 'Выдаліць перакладальную старонку або яе пераклад',
	'pt-deletepage-current' => 'Назва старонкі:',
	'pt-deletepage-reason' => 'Прычына:',
	'pt-deletepage-subpages' => 'Выдаліць усе падстаронкі',
	'pt-deletepage-list-pages' => 'Сьпіс старонак да выдаленьня',
	'pt-deletepage-list-translation' => 'Старонкі перакладаў',
	'pt-deletepage-list-section' => 'Старонкі адзінкі перакладу',
	'pt-deletepage-list-other' => 'Іншыя падстаронкі',
	'pt-deletepage-list-count' => 'Агулам $1 {{PLURAL:$1|старонка|старонкі|старонак}} да выдаленьня.',
	'pt-deletepage-full-logreason' => 'Частка старонкі $1, якую магчыма перакласьці.',
	'pt-deletepage-lang-logreason' => 'Частка перакладзенай старонкі $1.',
	'pt-deletepage-started' => 'Калі ласка, праверце [[Special:Log/pagetranslation|старонку журнала перакладаў]] адносна памылак і паведамленьняў пра выкананьне.',
	'pt-deletepage-intro' => 'Гэтая спэцыяльная старонка дазваляе Вам выдаляць цэлыя перакладальныя старонкі, альбо перакладзеныя на іншыя мовы.
Выдаленьне ня здарыцца хутка, таму што залежныя старонкі таксама будуць выдаленыя.
Памылкі будуць запратакаляваныя на [[Special:Log/pagetranslation|старонцы журналу перакладаў]] і патрабуюць выпраўленьня ўручную.',
);

/** Bulgarian (български)
 * @author DCLXVI
 * @author පසිඳු කාවින්ද
 */
$messages['bg'] = array(
	'tpt-diff-old' => 'Предишен текст',
	'tpt-diff-new' => 'Нов текст',
	'tpt-nosuchpage' => 'Страницата „$1“ не съществува',
	'tpt-select-prioritylangs-reason' => 'Причина:',
	'translate-tag-translate-link-desc' => 'Превеждане на тази страница',
	'tpt-languages-legend' => 'Други езици:',
	'tpt-discouraged-language-reason' => 'Причина: $1',
	'tpt-download-page' => 'Изнасяне на страница с преводите',
	'tpt-aggregategroup-add' => 'Добавяне',
	'tpt-aggregategroup-save' => 'Съхраняване',
	'tpt-aggregategroup-new-name' => 'Име:',
	'tpt-aggregategroup-invalid-group' => 'Групата не съществува',
	'pt-movepage-list-other' => 'Други подстраници', # Fuzzy
	'pt-movepage-current' => 'Текущо име:',
	'pt-movepage-new' => 'Ново име:',
	'pt-movepage-reason' => 'Причина:',
	'pt-movepage-subpages' => 'Преместване на всички подстраници',
	'pt-deletepage-action-perform' => 'Извършване на изтриването',
	'pt-deletepage-reason' => 'Причина:',
	'pt-deletepage-subpages' => 'Изтриване на всички подстраници',
	'pt-deletepage-list-other' => 'Други подстраници',
);

/** Bengali (বাংলা)
 * @author Aftab1995
 * @author Bellayet
 */
$messages['bn'] = array(
	'pagetranslation' => 'পাতা অনুবাদ',
	'tpt-diff-old' => 'পূর্বের লেখা',
	'tpt-diff-new' => 'নতুন লেখা',
	'tpt-select-prioritylangs-reason' => 'কারণ:',
	'tpt-rev-encourage' => 'পুনরুদ্ধার',
	'translate-tag-translate-link-desc' => 'এই পাতা অনুবাদ করুন',
	'translate-tag-markthis' => 'অনুবাদের জন্য এই পাতা চিহ্নিত করুন',
	'tpt-languages-legend' => 'অন্য ভাষা:',
	'tpt-tab-translate' => 'অনুবাদ',
	'tpt-discouraged-language-reason' => 'কারণ: $1',
	'tpt-aggregategroup-add' => 'যোগ',
	'tpt-aggregategroup-save' => 'সংরক্ষণ',
	'tpt-aggregategroup-new-name' => 'নাম:',
	'tpt-aggregategroup-new-description' => 'বিবরণ (ঐচ্ছিক):',
	'log-name-pagetranslation' => 'পাতা অনুবাদ লগ্',
	'pt-movepage-current' => 'বর্তমান নাম:',
	'pt-movepage-new' => 'নতুন নাম:',
	'pt-movepage-reason' => 'কারণ:',
	'pt-deletepage-current' => 'পাতার নাম:',
	'pt-deletepage-reason' => 'কারণ:',
);

/** Tibetan (བོད་ཡིག)
 * @author Freeyak
 */
$messages['bo'] = array(
	'pagetranslation' => 'ཤོག་ངོས་ཡིག་སྒྱུར།',
	'tpt-diff-old' => 'ཡིག་འབྲུ་གོང་མ།',
	'tpt-diff-new' => 'ཡིག་འབྲུ་གསར་བ།',
	'translate-tag-translate-link-desc' => 'ཤོག་ངོས་འདི་བསྒྱུར་བ།',
	'tpt-languages-legend' => 'སྐད་རིགས་གཞན།',
	'pt-movepage-list-translation' => 'ཡིག་སྒྱུར་ཤོག་ངོས།', # Fuzzy
	'pt-movepage-legend' => 'བསྒྱུར་རུང་བའི་ཤོག་ངོས་སྤོར་བ།',
	'pt-movepage-current' => 'ད་ཡོད་མིང་།',
	'pt-movepage-new' => 'མིང་གསར་བ།',
	'pt-movepage-reason' => 'རྒྱུ་མཚན།',
);

/** Breton (brezhoneg)
 * @author Fohanno
 * @author Fulup
 * @author Y-M D
 */
$messages['br'] = array(
	'pagetranslation' => 'Troidigezh ur bajenn',
	'right-pagetranslation' => 'Merkañ stummoù pajennoù evit ma vefent troet',
	'action-pagetranslation' => "Merañ ar pajennoù a c'haller treiñ",
	'tpt-desc' => 'Astenn evit treiñ pajennoù gant danvez',
	'tpt-section' => 'Unanenn treiñ $1',
	'tpt-section-new' => 'Unvez treiñ nevez.
Anv : $1',
	'tpt-section-deleted' => 'Unanenn dreiñ $1',
	'tpt-template' => 'Patrom pajenn',
	'tpt-templatediff' => 'Kemmet eo patrom ar bajenn.',
	'tpt-diff-old' => 'Testenn gent',
	'tpt-diff-new' => 'Testenn nevez',
	'tpt-submit' => 'Merkañ ar stumm-mañ da vezañ troet',
	'tpt-sections-oldnew' => 'Unvezioù treiñ kozh ha nevez',
	'tpt-sections-deleted' => 'Unvezioù treiñ diverket',
	'tpt-sections-template' => 'Patrom pajenn dreiñ',
	'tpt-action-nofuzzy' => 'Chom hep diwiriekaat an droidigezhioù',
	'tpt-badtitle' => "N'eo ket reizh titl anv ar bajenn ($1) zo bet lakaet",
	'tpt-nosuchpage' => "N'eus ket eus ar bajenn $1.",
	'tpt-oldrevision' => "N'eo ket $2 stumm diwezhañ ar bajenn [[$1]].
N'eus nemet ar stummoù diwezhañ a c'hall bezañ merket evit bezañ troet.",
	'tpt-notsuitable' => "N'haller ket treiñ ar bajenn $1.
Gwiria ez eus balizennoù <nowiki><translate></nowiki> enni hag ez eo reizh an ereadurezh anezhi.",
	'tpt-saveok' => 'Merket eo bet ar bajenn [[$1]] evit bezañ troet gant $2 {{PLURAL:$2|unanenn dreiñ|unanenn dreiñ}}.
Gallout a ra ar bajenn bezañ <span class="plainlinks">[$3 troet]</span> bremañ.',
	'tpt-badsect' => 'Direizh eo an anv "$1" evit un unanenn dreiñ $2.',
	'tpt-showpage-intro' => "A-is emañ rollet an troidigezhioù nevez, ar re zo anezho hag ar re bet diverket.
Kent merkañ ar stumm-mañ evit an treiñ, gwiriait mat n'eus ket bet nemeur a gemmoù er rannbennadoù kuit da bourchas labour aner d'an droourien.", # Fuzzy
	'tpt-mark-summary' => 'Merket eo bet ar stumm-mañ da vezañ troet',
	'tpt-edit-failed' => "N'eus ket bet gallet hizivaat ar bajenn : $1",
	'tpt-duplicate' => 'Implijet eo bet meur a wezh anv an unvez treiñ $1.',
	'tpt-already-marked' => 'Merket eo bet ar stumm diwezhañ eus ar bajenn-mañ da vezañ troet dija.',
	'tpt-unmarked' => "N'eo ket merket ken ar bajenn $1 evit bezañ troet.",
	'tpt-list-nopages' => "N'eus pajenn ebet merket da vezañ troet na prest da vezañ merket da vezañ troet.",
	'tpt-new-pages-title' => "Pajennoù a c'haller da dreiñ",
	'tpt-old-pages-title' => 'Pajennoù emeur o treiñ',
	'tpt-other-pages-title' => 'Pajennoù torr',
	'tpt-discouraged-pages-title' => 'Pajennoù dizerbedet',
	'tpt-new-pages' => "{{PLURAL:$1|Er bajenn-mañ|Er pajennoù-mañ}} ez eus testennoù enno balizennoù treiñ, met stumm ebet eus ar {{PLURAL:$1|bajenn-mañ|pajennoù-mañ}} n'eo bet merket da vezañ troet.",
	'tpt-old-pages' => 'Stummoù zo eus ar {{PLURAL:$1|bajenn-mañ|pajennoù-mañ}} zo bet merket da vezañ troet.',
	'tpt-other-pages' => "Merket ez eus bet da vezañ troet {{PLURAL:$1|ur stumm kozh eus ar bajenn-mañ|stummoù koshoc'h eus ar pajennoù-mañ}};
ar {{PLURAL:$1|stumm|stummoù}} diwezhañ avat n'hallont ket bezañ merket da vezañ troet.",
	'tpt-discouraged-pages' => "Dizerbedet eo treiñ ar {{PLURAL:$1|bajenn-mañ|pajennoù-mañ}} pelloc'h.",
	'tpt-select-prioritylangs' => "Roll kodoù ar yezhoù d'ober ganto da gentañ, dispartiet gant skejoù :",
	'tpt-select-prioritylangs-reason' => 'Abeg :',
	'tpt-sections-prioritylangs' => 'Yezhoù pouezusañ',
	'tpt-rev-mark' => 'merkañ da vezañ troet',
	'tpt-rev-unmark' => 'Lemel a-ziwar ar roll treiñ',
	'tpt-rev-discourage' => 'dizerbediñ',
	'tpt-rev-encourage' => 'assevel',
	'tpt-rev-mark-tooltip' => 'Merkañ stumm diwezhañ ar bajenn-mañ evel stumm da vezañ troet.',
	'tpt-rev-unmark-tooltip' => 'Lemel ar bajenn-mañ a-ziwar ar roll treiñ.',
	'tpt-rev-discourage-tooltip' => "Dizerbediñ treiñ ar bajenn-mañ pelloc'h.",
	'tpt-rev-encourage-tooltip' => 'Adlakaat ar bajenn-mañ war ar roll treiñ normal.',
	'translate-tag-translate-link-desc' => 'Treiñ ar bajenn-mañ',
	'translate-tag-markthis' => 'Merkañ ar bajenn-mañ evit an treiñ',
	'translate-tag-markthisagain' => 'Er bajenn-mañ ez eus bet <span class="plainlinks">[$1 kemm]</span> abaoe m\'eo bet <span class="plainlinks">[$2 merket da vezañ troet]</span>.',
	'translate-tag-hasnew' => 'Er bajenn-mañ ez eus <span class="plainlinks">[$1 kemm]</span> ha n\'int ket bet merket da vezañ troet.',
	'tpt-translation-intro' => 'Ur stumm <span class="plainlinks">[$1 troet]</span> eus ar bajenn [[$2]] eo ar bajenn-mañ; kaset ez eus bet da benn $3% eus an droidigezh anezhi, ha diouzh an deiz emañ.',
	'tpt-languages-legend' => 'Yezhoù all :',
	'tpt-languages-zero' => 'Stagañ gant an troidigezhioù evit ar yezh-se',
	'tpt-target-page' => "N'hall ket ar bajenn-mañ bezañ hizivaet gant an dorn.
Ur stumm troet eus [[$1]] eo ar bajenn-mañ; gallout a ra bezañ hizivaet en ur implijout [$2 an ostilh treiñ].",
	'tpt-unknown-page' => "Miret eo an esaouenn anv-mañ evit troidigezh ar pajennoù.
Ar bajenn hoc'h eus klasket kemm ne seblant ket klotañ gant pajenn ebet bet merket evit bezañ troet.",
	'tpt-discouraged-language-reason' => 'Abeg : $1',
	'tpt-render-summary' => 'Hizivadenn da glotañ gant stumm nevez mammenn ar bajenn',
	'tpt-download-page' => 'Ezporzhiañ ar bajenn gant an troidigezhioù',
	'tpt-aggregategroup-add' => 'Ouzhpennañ',
	'tpt-aggregategroup-save' => 'Enrollañ',
	'tpt-aggregategroup-new-name' => 'Anv :',
	'tpt-aggregategroup-new-description' => 'Deskrivadur (diret) :',
	'tpt-aggregategroup-invalid-group' => "N'eus ket eus ar strollad-mañ",
	'pt-parse-open' => 'Balizenn &lt;translate> digempouez.
Patrom treiñ : <pre>$1</pre>',
	'pt-parse-close' => 'Balizenn &lt;/translate> digempouez.
Patrom treiñ  <pre>$1</pre>',
	'pt-parse-nested' => "N'eo ket aotreet ar rannbennadoù &lt;translate> empret an eil en egile.
Testenn ar valizenn : <pre>$1</pre>",
	'pt-shake-multiple' => 'Merkerioù rannbennadoù lies evit ur rannbennad.
Testenn ar rannbennad : <pre>$1</pre>', # Fuzzy
	'pt-shake-position' => "Merkerioù rannbennad lec'hiet drol.
Testenn ar rannbennad : <pre>$1</pre>", # Fuzzy
	'pt-shake-empty' => "Rannbennad c'houllo evit ar merker $1.", # Fuzzy
	'log-description-pagetranslation' => 'Marilh an obererezhioù liammet gant sistem treiñ pajennoù',
	'log-name-pagetranslation' => 'Marilh troidigezhioù pajennoù',
	'pt-movepage-title' => 'Fiñval ar bajenn da dreiñ $1',
	'pt-movepage-blockers' => "Ar bajenn da dreiñ na c'hell ket bezañ adanvet en abeg d'ar fazi{{PLURAL:$1||où}} da-heul :",
	'pt-movepage-block-base-exists' => 'Bez ez eus eus ar bajenn diazez moned [[:$1]].', # Fuzzy
	'pt-movepage-block-base-invalid' => 'Ar bajenn diazez moned en deus un titl direizh.', # Fuzzy
	'pt-movepage-block-tp-exists' => 'Bez ez eus eus ar bajenn treiñ moned [[:$2]].',
	'pt-movepage-block-tp-invalid' => 'Direizh e vefe titl ar bajenn treiñ moned evit [[:$1]] (re hir ?).',
	'pt-movepage-block-section-exists' => 'Bez ez eus ar ran eus ar bajenn voned [[:$2]].', # Fuzzy
	'pt-movepage-block-section-invalid' => 'Direizh e vefe titl rann ar bajenn voned evit [[:$1]] (re hir ?).', # Fuzzy
	'pt-movepage-block-subpage-exists' => 'Bez ez eus eus an is-pajenn voned [[:$2]].',
	'pt-movepage-block-subpage-invalid' => 'Direizh e vefe titl an is-pajenn voned evit [[:$1]] (re hir ?).',
	'pt-movepage-list-pages' => 'Roll ar pajennoù da fiñval',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Pajenn|Pajennoù}} treiñ',
	'pt-movepage-list-section' => 'Pajennoù{{PLURAL:$1|}} e rann',
	'pt-movepage-list-other' => 'Ispajenn{{PLURAL:$1||où}} all',
	'pt-movepage-list-count' => "$1 {{PLURAL:bajenn|pajenn}} da zilec'hiañ en holl.",
	'pt-movepage-legend' => 'Fiñval ar bajenn da dreiñ',
	'pt-movepage-current' => 'Anv red :',
	'pt-movepage-new' => 'Anv nevez :',
	'pt-movepage-reason' => 'Abeg :',
	'pt-movepage-subpages' => 'Fiñval an holl is-pajennoù',
	'pt-movepage-action-check' => 'Gwiriekaat ha posupl eo adenvel',
	'pt-movepage-action-perform' => 'Adenvel',
	'pt-movepage-action-other' => 'Kemmañ ar moned',
	'pt-movepage-intro' => "Gant ar bajenn dibar-mañ e c'hallit adenvel ar pajennoù merket da vezañ troet.
Ne zeuio ket da wir diouzhtu an adenvel rak ret e vo dilec'hiañ kalz a bajennoù.
Amzer dilec'hiañ ar pajennoù ne vo ket posupl c'hoari ganto.
Er [[Special:Log/pagetranslation|page marilh treiñ]] e vo enrollet ar mankoù adenvel; eno e vo deoc'h o reizhañ gant an dorn.",
	'pt-movepage-logreason' => 'Rann eus ar bajenn da dreiñ $1.',
	'pt-movepage-started' => 'Adanvet eo bet ar bajenn diazez.
Mar plij gwiriit [[Special:Log/pagetranslation|pajenn marilh an troidigezhioù]] evit kempenn ar fazioù, mar bez, ha lenn ar gemennadenn glozañ.',
	'pt-locked-page' => "Prennet eo ar bajenn-mañ dre m' emeur oc'h adenvel ar bajenn da dreiñ.",
	'pt-deletepage-lang-title' => 'O tiverkañ ar bajenn dreiñ $1.',
	'pt-deletepage-full-title' => 'O tiverkañ ar bajenn da dreiñ $1.',
	'pt-deletepage-invalid-title' => 'Faziek eo ar bajenn spisaet.',
	'pt-deletepage-invalid-text' => "N'eus ket eus ar bajenn spisaet ur bajenn da vezañ troet, nag un droidigezh anezhi.", # Fuzzy
	'pt-deletepage-action-check' => 'Rollañ ar pajennoù da vezañ diverket',
	'pt-deletepage-action-perform' => 'Diverkañ',
	'pt-deletepage-action-other' => 'Kemmañ ar moned',
	'pt-deletepage-lang-legend' => 'Diverkañ ar bajenn dreiñ',
	'pt-deletepage-full-legend' => "Diverkañ ar bajenn a c'haller treiñ",
	'pt-deletepage-any-legend' => 'Diverkañ ar bajenn da vezañ troet pe troidigezh ar bajenn da vezañ troet', # Fuzzy
	'pt-deletepage-current' => 'Anv ar bajenn :',
	'pt-deletepage-reason' => 'Abeg :',
	'pt-deletepage-subpages' => 'Diverkañ an holl ispajennoù',
	'pt-deletepage-list-pages' => 'Roll ar pajennoù da ziverkañ',
	'pt-deletepage-list-translation' => 'Pajennoù treiñ',
	'pt-deletepage-list-section' => 'Pajennoù elfennoù treiñ',
	'pt-deletepage-list-other' => 'Ispajennoù all',
	'pt-deletepage-list-count' => '$1 {{PLURAL:bajenn|pajenn}} da ziverkañ en holl.',
	'pt-deletepage-full-logreason' => 'Rann eus ar bajenn da dreiñ $1.',
	'pt-deletepage-lang-logreason' => 'Rann eus ar bajenn da dreiñ $1.',
);

/** Bosnian (bosanski)
 * @author CERminator
 */
$messages['bs'] = array(
	'pagetranslation' => 'Prijevod stranice',
	'right-pagetranslation' => 'Označanje verzija stranica za prevođenje',
	'tpt-desc' => 'Proširenje za prevođenje stranica sadržaja',
	'tpt-section' => 'Jedinica prevođenja $1',
	'tpt-section-new' => 'Nova jedinica prevođenja. Naziv: $1',
	'tpt-section-deleted' => 'Jedinica prevođenja $1',
	'tpt-template' => 'Šablon stranice',
	'tpt-templatediff' => 'Šablon stranice se izmijenio.',
	'tpt-diff-old' => 'Prethodni tekst',
	'tpt-diff-new' => 'Novi tekst',
	'tpt-submit' => 'Označi ovu verziju za prevođenje',
	'tpt-sections-oldnew' => 'Nove i postojeće prevodilačke jedinice',
	'tpt-sections-deleted' => 'Obrisane prevodilačke jedinice',
	'tpt-sections-template' => 'Šablon stranice prevođenja',
	'tpt-action-nofuzzy' => 'Ne poništavajte prevode',
	'tpt-badtitle' => 'Zadano ime stranice ($1) nije valjan naslov',
	'tpt-nosuchpage' => 'Stranica $1 ne postoji',
	'tpt-oldrevision' => '$2 nije posljednja verzija stranice [[$1]].
Jedino posljednje verzije se mogu označiti za prevođenje.',
	'tpt-notsuitable' => 'Stranica $1 nije pogodna za prevođenje.
Provjerite da postoje oznake <nowiki><translate></nowiki> i da ima valjanu sintaksu.',
	'tpt-saveok' => 'Stranica [[$1]] je označena za prevođenje sa $2 {{PLURAL:$2|prevodilačkom jedinicom|prevodilačke jedinice|prevodilačkih jedinica}}.
Stranica se sad može <span class="plainlinks">[$3 prevoditi]</span>.',
	'tpt-badsect' => '"$1" nije valjano ime za jedinicu prevođenja $2.',
	'tpt-showpage-intro' => 'Ispod su navedene nove, postojeće i obrisane sekcije.
Prije nego što označite ovu verziju za prevođenje, provjerite da su izmjene sekcija minimizirane da bi se spriječio nepotrebni rad prevodioca.', # Fuzzy
	'tpt-mark-summary' => 'Ova vezija označena za prevođenje',
	'tpt-edit-failed' => 'Nije moguće ažurirati stranicu: $1',
	'tpt-already-marked' => 'Posljednja verzija ove stranice je već označena za prevođenje.',
	'tpt-unmarked' => 'Stranica $1 više nije označena za prevođenje.',
	'tpt-list-nopages' => 'Nijedna stranica nije označena za prevođenje niti je spremna za označavanje.',
	'tpt-new-pages' => '{{PLURAL:$1|Ova stranica sadrži|Ove stranice sadrže}} tekst sa oznakama prijevoda, ali nijedna od verzija {{PLURAL:$1|ove stranice|ovih stranica}} nije trenutno označena za prevođenje.',
	'tpt-old-pages' => 'Neke verzije {{PLURAL:$1|ove stranice|ovih stranica}} su označene za prevođenje.',
	'tpt-other-pages' => '{{PLURAL:$1|Stara verzija ove stranice je označena|Stare verzije ovih stranica su označene}} za prevođenje,
ali {{PLURAL:$1|posljednja verzija ne može|posljednje verzije ne mogu}} biti {{PLURAL:$1|označena|označene}} za prevođenje.',
	'tpt-rev-unmark' => 'ukloni ovu stranicu iz prevođenja', # Fuzzy
	'translate-tag-translate-link-desc' => 'Prevedi ovu stranicu',
	'translate-tag-markthis' => 'Označi ovu stranicu za prevođenje',
	'translate-tag-markthisagain' => 'Ova stranica ima <span class="plainlinks">[$1 izmjena]</span> od kako je posljednji put <span class="plainlinks">[$2 označena za prevođenje]</span>.',
	'translate-tag-hasnew' => 'Ova stranica sadrži <span class="plainlinks">[$1 izmjena]</span> koje nisu označene za prevođenje.',
	'tpt-translation-intro' => 'Ova stranica je <span class="plainlinks">[$1 prevedena verzija]</span> stranice [[$2]] a prijevod je $3% dovršen i ažuriran.',
	'tpt-languages-legend' => 'Drugi jezici:',
	'tpt-target-page' => 'Ova stranica ne može biti ručno ažurirana.
Ova stranica je prijevod stranice [[$1]] a prevodi se mogu ažurirati putem [$2 alata za prevođenje].',
	'tpt-unknown-page' => 'Ovaj imenski prostor je rezervisan za prevode stranica sadržaja.
Stranica koju pokušavate uređivati ne odgovara nekoj od stranica koje su označene za prevođenje.',
	'tpt-render-summary' => 'Ažuriram na novu verziju izvorne stranice',
	'tpt-download-page' => 'Izvezi stranicu sa prijevodima',
	'pt-parse-open' => 'Neuravnotežena &lt;translate> oznaka.
Šablon za prevođenje: <pre>$1</pre>',
	'pt-parse-close' => 'Neuravnotežena &lt;/translate> oznaka.
Šablon za prevođenje: <pre>$1</pre>',
	'pt-parse-nested' => 'Uklopljene &lt;translate> sekcije nisu dozvoljene.
Tekst oznake: <pre>$1</pre>', # Fuzzy
	'pt-shake-multiple' => 'Veći broj oznaka sekcija za istu sekciju.
Tekst sekcije: <pre>$1</pre>', # Fuzzy
	'pt-shake-position' => 'Oznake sekcija na nepredviđenoj poziciji.
Tekst sekcije: <pre>$1</pre>', # Fuzzy
	'pt-shake-empty' => 'Prazna sekcija za marker $1.', # Fuzzy
	'log-description-pagetranslation' => 'Zapisnik akcije vezanih za sistem prevođenja stranica',
	'log-name-pagetranslation' => 'Zapisnik prijevoda stranice',
	'pt-movepage-title' => 'Premještanje stranice za prevođenje $1',
	'pt-movepage-blockers' => 'Stranica koja se može prevoditi ne može biti premještena na novo ime zbog {{PLURAL:$1|slijedeće greške|slijedećih grešaka}}:',
	'pt-movepage-block-base-exists' => 'Ciljna bazna stranica [[:$1]] postoji.', # Fuzzy
	'pt-movepage-block-base-invalid' => 'Ciljna bazna stranica nije valjan naslov.', # Fuzzy
	'pt-movepage-block-tp-exists' => 'Ciljna stranica za prijevod [[:$2]] postoji.',
	'pt-movepage-block-tp-invalid' => 'Naslov ciljne stranice za prijevod za [[:$1]] bi bio nevaljan (predugačak?).',
	'pt-movepage-block-section-exists' => 'Ciljna sekcija stranice [[:$2]] postoji.', # Fuzzy
	'pt-movepage-block-section-invalid' => 'Naslov ciljne sekcije za [[:$1]] bi bio nevaljan (predugačak?).', # Fuzzy
	'pt-movepage-block-subpage-exists' => 'Ciljna podstranica [[:$2]] postoji.',
	'pt-movepage-block-subpage-invalid' => 'Naslov ciljne podstranice za [[:$1]] bi bio nevaljan (predugačak?).',
	'pt-movepage-list-pages' => 'Spisak stranica za premještanje',
	'pt-movepage-list-translation' => 'Stranice za prijevod', # Fuzzy
	'pt-movepage-list-section' => 'Stranice sekcije', # Fuzzy
	'pt-movepage-list-other' => 'Druge podstranice', # Fuzzy
	'pt-movepage-list-count' => 'Ukupno $1 {{PLURAL:$1|stranica|stranice|stranica}} za premještanje.',
	'pt-movepage-legend' => 'Premjesti stranicu koja se prevodi',
	'pt-movepage-current' => 'Trenutni naziv:',
	'pt-movepage-new' => 'Novi naziv:',
	'pt-movepage-reason' => 'Razlog:',
	'pt-movepage-subpages' => 'Premjesti sve podstranice',
	'pt-movepage-action-check' => 'Provjeri da li je moguće premještanje',
	'pt-movepage-action-perform' => 'Izvrši premještanje',
	'pt-movepage-action-other' => 'Promijeni cilj',
	'pt-movepage-intro' => 'Ova posebna stranica vam omogućava da premještate stranice koje su obilježene za prevođenje.
Akcija premještanja neće biti odmah, jer mnoge stranice trebaju biti premještene.
Dok se stranice premještaju, neće biti mogućnosti koristiti se s tim stranicama.
Greške će biti zapisane u [[Special:Log/pagetranslation|zapisnik prevođenja stranice]] te se one moraju ispravljati ručno.',
	'pt-movepage-logreason' => 'Dio stranice koja se prevodi $1.',
	'pt-movepage-started' => 'Osnovna stranica se sad premješta.
Molimo provjerite [[Special:Log/pagetranslation|zapisnik prevoda stranice]] za greške i poruke završetka.',
	'pt-locked-page' => 'Ova stranica je zaključana jer se stranica za prevođenje sada premješta.',
	'pt-deletepage-lang-title' => 'Brisanje stranice za prevođenje $1.',
	'pt-deletepage-action-check' => 'Spisak stranica za brisanje',
	'pt-deletepage-action-perform' => 'Izvrši brisanje',
	'pt-deletepage-action-other' => 'Promijeni cilj',
	'pt-deletepage-current' => 'Naslov stranice:',
	'pt-deletepage-reason' => 'Razlog:',
	'pt-deletepage-subpages' => 'Obriši sve podstranice',
	'pt-deletepage-list-pages' => 'Spisak stranica za brisanje',
	'pt-deletepage-list-translation' => 'Stranice za prijevod',
	'pt-deletepage-list-section' => 'Stranice sekcije', # Fuzzy
	'pt-deletepage-list-other' => 'Druge podstranice',
	'pt-deletepage-full-logreason' => 'Dio stranice koja se prevodi $1.',
	'pt-deletepage-lang-logreason' => 'Dio stranice za prevođenje $1.',
);

/** Buginese (ᨅᨔ ᨕᨘᨁᨗ)
 * @author Kurniasan
 */
$messages['bug'] = array(
	'translate-tag-translate-link-desc' => "Tare'juma iyyedé leppa",
);

/** Catalan (català)
 * @author Jordi Roqué
 * @author SMP
 * @author Solde
 * @author Toniher
 * @author පසිඳු කාවින්ද
 */
$messages['ca'] = array(
	'pagetranslation' => "Traducció d'una pàgina",
	'right-pagetranslation' => 'Marcar versions de pàgines per a traduir',
	'action-pagetranslation' => 'gestiona les pàgines traduïbles',
	'tpt-desc' => 'Extensió per a traduir les pàgines de contingut',
	'tpt-section' => 'Unitat de traducció $1',
	'tpt-section-new' => 'Nova unitat de traducció. Nom: $1',
	'tpt-diff-old' => 'Text anterior',
	'tpt-diff-new' => 'Text nou',
	'tpt-badtitle' => 'El nom de pàgina donat ($1) no és un títol vàlid',
	'tpt-notsuitable' => 'La pàgina $1 no està preparada per a la seva traducció.
Assegureu-vos que té les etiquetes <nowiki><translate></nowiki> i una sintaxi vàlida.',
	'tpt-rev-encourage' => 'restaura',
	'translate-tag-translate-link-desc' => 'Traduir aquesta pàgina',
	'tpt-languages-legend' => 'Altres idiomes:',
	'tpt-aggregategroup-add' => 'Afegeix',
	'tpt-aggregategroup-save' => 'Desa',
	'tpt-aggregategroup-new-name' => 'Nom:',
	'pt-movepage-title' => 'Mou la pàgina traduïble $1',
	'pt-movepage-blockers' => "La pàgina traduïble no pot ser reanomenada a causa {{PLURAL:$1|de l'error següent|dels errors següents}}:",
	'pt-movepage-block-base-exists' => 'La pàgina base de destinació [[:$1]] ja existeix.', # Fuzzy
	'pt-movepage-block-base-invalid' => 'La pàgina base de destinació no té un títol vàlid.', # Fuzzy
	'pt-movepage-block-tp-exists' => 'La pàgina de traducció de destinació [[:$2]] ja existeix.',
	'pt-movepage-block-tp-invalid' => 'El títol de la pàgina de traducció de destinació [[:$1]] no seria vàlid (potser seria massa llarg).',
	'pt-movepage-block-section-exists' => 'La pàgina de secció de destinació [[:$2]] ja existeix.', # Fuzzy
	'pt-movepage-block-section-invalid' => 'El títol de la pàgina de secció de destinació [[:$1]] no seria vàlid (potser seria massa llarg).', # Fuzzy
	'pt-movepage-block-subpage-exists' => 'La subpàgina de destinació [[:$2]] ja existeix.',
	'pt-movepage-block-subpage-invalid' => 'El títol de la subpàgina de destinació [[:$1]] no seria vàlid (potser seria massa llarg).',
	'pt-movepage-list-pages' => 'Llista de pàgines per moure',
	'pt-movepage-list-translation' => 'Pàgines de traducció', # Fuzzy
	'pt-movepage-list-section' => 'Pàgines de secció', # Fuzzy
	'pt-movepage-list-other' => 'Altres subpàgines', # Fuzzy
	'pt-movepage-list-count' => 'En total, $1 {{PLURAL:$1|pàgina|pàgines}} a moure.',
	'pt-movepage-legend' => 'Mou la pàgina traduïble',
	'pt-movepage-current' => 'Nom actual:',
	'pt-movepage-new' => 'Nom nou:',
	'pt-movepage-reason' => 'Motiu:',
	'pt-movepage-subpages' => 'Mou totes les subpàgines',
	'pt-movepage-action-check' => 'Verifica si és possible el trasllat',
	'pt-movepage-action-perform' => 'Fes el trasllat',
	'pt-movepage-action-other' => 'Canvia la destinació',
	'pt-movepage-intro' => "Aquesta pàgina especial permet desplaçar pàgines que estan marcades per a la traducció.
El trasllat no serà instantani, perquè moltes pàgines hauran de ser mogudes.
Mentre s'estiguin traslladant les pàgines no serà possible interaccionar amb les pàgines en qüestió.
Els errors sortiran indicats al [[Special:Log/pagetranslation|registre de traducció de pàgines]] i hauran d'ésser reparats a mà.",
	'pt-movepage-logreason' => 'Part de la pàgina a traduir $1.',
	'pt-movepage-started' => 'La pàgina base està traslladada.
Comproveu el [[Special:Log/pagetranslation|registre de traducció de pàgines]] pels errors i el missatge de finalització.',
	'pt-locked-page' => 'Aquesta pàgina està bloquejada perquè la pàgina a traduir està en un procés de trasllat.',
	'pt-deletepage-reason' => 'Raó:',
);

/** Chechen (нохчийн)
 * @author Sasan700
 * @author Умар
 */
$messages['ce'] = array(
	'tpt-diff-new' => 'Керла йоза',
	'tpt-languages-legend' => 'Кхин меттанаш:',
	'pt-movepage-current' => 'Карара цӀе:',
	'pt-movepage-new' => 'Керла цӀе:',
	'pt-movepage-reason' => 'Бахьан:',
	'pt-movepage-action-other' => 'Хийца Ӏалашо',
	'pt-deletepage-action-perform' => 'Кхочушдé дӀаяккхар',
	'pt-deletepage-action-other' => 'Хийца Ӏалашо',
	'pt-deletepage-current' => 'АгӀона цӀе:',
);

/** Sorani Kurdish (کوردی)
 * @author Asoxor
 * @author Calak
 * @author Marmzok
 * @author Muhammed taha
 * @author رزگار
 */
$messages['ckb'] = array(
	'pagetranslation' => 'وەرگێڕانی پەڕە',
	'tpt-template' => 'داڕێژەی پەڕە',
	'tpt-templatediff' => 'داڕێژەی لاپەڕەکە گۆڕاوە.',
	'tpt-diff-old' => 'دەقی پێشوو',
	'tpt-diff-new' => 'دەقی نوێ',
	'tpt-submit' => 'نیشان‌کردنی ئەم وەشانە بۆ وەرگێڕان',
	'tpt-sections-template' => 'داڕێژی لاپەڕەی وەرگێڕان',
	'tpt-nosuchpage' => 'پەڕەی "$1" بوونی نیه‌',
	'tpt-mark-summary' => 'نیشانکردنی ئەم وەشانە بۆ وەرگێڕان',
	'tpt-already-marked' => 'دوایین وەشانی ئەم لاپەڕەیە لە پێش‌دا بۆ وەرگێڕان نیشان کراوە.',
	'tpt-select-prioritylangs-reason' => 'هۆکار:',
	'tpt-rev-encourage' => 'گەڕاندنەوە',
	'translate-tag-translate-link-desc' => 'ئەم پەڕەیە وەربگێڕە',
	'translate-tag-markthis' => 'نیشان‌کردنی ئەم لاپەڕەیە بۆ وەرگێڕان',
	'tpt-languages-legend' => 'زمانەکانی دیکە:',
	'tpt-aggregategroup-add' => 'زێدەبکە',
	'tpt-aggregategroup-save' => 'پاشەکەوتی بکە',
	'tpt-aggregategroup-new-name' => 'ناو:',
	'pt-movepage-new' => 'ناوی نوێ:',
	'pt-movepage-reason' => 'هۆکار:',
	'pt-deletepage-current' => 'ناوی پەڕە:',
	'pt-deletepage-reason' => 'هۆکار:',
);

/** Czech (česky)
 * @author Chmee2
 * @author Janet11
 * @author Littledogboy
 * @author Matěj Grabovský
 * @author Michaelbrabec
 * @author Mormegil
 * @author Vks
 */
$messages['cs'] = array(
	'pagetranslation' => 'Překlad stránek',
	'right-pagetranslation' => 'Označování verzí stránek pro překlad',
	'action-pagetranslation' => 'spravovat přeložitelné stránky',
	'tpt-desc' => 'Rozšíření pro překládání stránek s obsahem',
	'tpt-section' => 'Část překladu $1',
	'tpt-section-new' => 'Nová část překladu.
Název: $1',
	'tpt-section-deleted' => 'Část překladu $1',
	'tpt-template' => 'Šablona stránky',
	'tpt-templatediff' => 'Šablona stránky se změnila.',
	'tpt-diff-old' => 'Předchozí text',
	'tpt-diff-new' => 'Nový text',
	'tpt-submit' => 'Označit tuto verzi pro překlad',
	'tpt-sections-oldnew' => 'Nové a existující části překladu',
	'tpt-sections-deleted' => 'Smazané části překladu',
	'tpt-sections-template' => 'Šablona stránky pro překlad',
	'tpt-action-nofuzzy' => 'Nezneplatňovat překlady',
	'tpt-badtitle' => 'Zadaný název stránky ($1) je neplatný',
	'tpt-nosuchpage' => 'Stránka $1 neexistuje',
	'tpt-oldrevision' => '$2 není nejnovější verze stránky [[$1]].
Pro překlad je možné označit pouze nejnovější verze.',
	'tpt-notsuitable' => 'Stránka $1 není vhodná pro překlad.
Ujistěte se, že obsahuje značky <code><nowiki><translate></nowiki></code> a má platnou syntaxi.',
	'tpt-saveok' => 'Stránka [[$1]] byla označena pro překlad {{PLURAL:$2|s $2 částí překladu|se $2 částmi překladu|s $2 částmi překladu}}.
Tato stránka může být nyní <span class="plainlinks">[$3 přeložena]</span>.',
	'tpt-badsect' => '„$1“ není platný název části překladu $2.',
	'tpt-showpage-intro' => 'Níže jsou uvedeny nové, současné a smazané části.
Než tuto verzi označíte pro překlad, zkontrolujte, že změny částí jsou minimální, abyste zabránili zbytečné práci překladatelů.',
	'tpt-mark-summary' => 'Tato verze je označená pro překlad',
	'tpt-edit-failed' => 'Nelze aktualizovat stránku: $1',
	'tpt-already-marked' => 'Nejnovější verze této stránky už byla označena pro překlad.',
	'tpt-unmarked' => 'Stránka $1 už není označena k překladu.',
	'tpt-list-nopages' => 'Žádné stránky nejsou označeny pro překlad nebo na to nejsou připraveny.',
	'tpt-new-pages-title' => 'Stránky navržené k překladu',
	'tpt-old-pages-title' => 'Překládané stránky',
	'tpt-other-pages-title' => 'Rozbité stránky',
	'tpt-discouraged-pages-title' => 'Nedoporučené stránky',
	'tpt-new-pages' => '{{PLURAL:$1|Tato stránka obsahuje|Tyto stránky obsahují}} text se značkami pro překlad, ale žádná verze {{PLURAL:$1|této stránky|těchto stránek}} není aktuálně označena pro překlad.',
	'tpt-old-pages' => 'Některé verze {{PLURAL:$1|této stránky|těchto stránek}} byly označeny pro překlad.',
	'tpt-other-pages' => 'Starší verze {{PLURAL:$1|této stránky je označena|těchto stránek jsou označeny}} pro překlad,
ale nejnovější verze {{PLURAL:$1|nemůže být k překladu označena|nemohou být k překladu označeny}}.',
	'tpt-discouraged-pages' => 'Další překlady {{PLURAL:$1|této stránky|těchto stránek}} se nedoporučují.',
	'tpt-select-prioritylangs' => 'Čárkou oddělený seznam kódů prioritních jazyků:',
	'tpt-select-prioritylangs-force' => 'Zakázat překlady do jiných než prioritních jazyků',
	'tpt-select-prioritylangs-reason' => 'Důvod:',
	'tpt-sections-prioritylangs' => 'Prioritní jazyky',
	'tpt-rev-mark' => 'označit pro překlad',
	'tpt-rev-unmark' => 'odstranit z překladu',
	'tpt-rev-discourage' => 'nedoporučit',
	'tpt-rev-encourage' => 'Obnovit',
	'tpt-rev-mark-tooltip' => 'Označí nejnovější verzi této stránky k překladu.',
	'tpt-rev-unmark-tooltip' => 'Odstranit tuto stránku z překladu.',
	'tpt-rev-discourage-tooltip' => 'Nedoporučí další překlady této stránky.',
	'tpt-rev-encourage-tooltip' => 'Vrátí tuto stránku k normálnímu překladu.',
	'translate-tag-translate-link-desc' => 'Přeložit tuto stránku',
	'translate-tag-markthis' => 'Označit tuto stránku pro překlad',
	'translate-tag-markthisagain' => 'Tato stránka byla <span class="plainlinks">[$1 změněna]</span> od posledního <span class="plainlinks">[$2 označení pro překlad]</span>.',
	'translate-tag-hasnew' => 'Tato stránka obsahuje <span class="plainlinks">[$1 změny]</span>, které nebyly označeny pro překlad.',
	'tpt-translation-intro' => 'Toto je <span class="plainlinks">[$1 přeložená verze]</span> stránky [[$2]], překlad je úplný a aktuální na $3 %.',
	'tpt-languages-legend' => 'Jiné jazyky:',
	'tpt-languages-zero' => 'Začít překlad do tohoto jazyka',
	'tpt-tab-translate' => 'Přeložit',
	'tpt-target-page' => 'Tuto stránku nelze ručně aktualizovat.
Tato stránka je překladem stránky [[$1]] a překlad lze aktualizovat pomocí [$2 nástroje pro překlad].',
	'tpt-unknown-page' => 'Tento jmenný prostor je vyhrazen pro překlady stránek s obsahem.
Zdá se, že stránka, kterou se pokoušíte upravovat, neodpovídá žádné stránce označené pro překlad.',
	'tpt-translation-restricted' => 'Správce překladů zabránil překladu této stránky do tohoto jazyka.

Zdůvodnění: $1',
	'tpt-discouraged-language-force' => "'''Tuto stránku nelze překládat do jazyka $2.'''

Správce překladů se rozhodl, že tuto stránku lze překládat pouze do $3.",
	'tpt-discouraged-language' => "'''Překlad do jazyka $2 není pro tuto stránku prioritní.'''

Správce překladů se rozhodl zaměřit překladatelské úsilí na $3.",
	'tpt-discouraged-language-reason' => 'Zdůvodnění: $1',
	'tpt-priority-languages' => 'Správce překladů nastavil prioritní jazyky pro tuto skupinu na $1.',
	'tpt-render-summary' => 'Stránka aktualizována, aby odpovídala nové verzi zdrojové stránky',
	'tpt-download-page' => 'Exportovat stránky s překlady',
	'tpt-aggregategroup-add' => 'Přidat',
	'tpt-aggregategroup-save' => 'Uložit',
	'tpt-aggregategroup-new-name' => 'Jméno:',
	'tpt-aggregategroup-new-description' => 'Popis (nepovinné):',
	'tpt-aggregategroup-invalid-group' => 'Skupina neexistuje',
	'log-description-pagetranslation' => 'Protokol úkonů souvisejících se systémem překladu stránek',
	'log-name-pagetranslation' => 'Kniha překladů stránek',
	'pt-movepage-list-pages' => 'Seznam stránek k přesunutí',
	'pt-movepage-list-translation' => 'Překlad {{PLURAL:$1|stránky|stránek}}',
	'pt-movepage-list-section' => 'Sekce {{PLURAL:$1|stránky|stránek}}',
	'pt-movepage-list-other' => 'Další {{PLURAL:$1|podstránka|podstránky}}',
	'pt-movepage-list-count' => 'Celkem  $1   {{PLURAL:$1| stránka|stránek}} k přesunutí.',
	'pt-movepage-legend' => 'Přesunout přeložitelnou stránku',
	'pt-movepage-current' => 'Současný název:',
	'pt-movepage-new' => 'Nový název:',
	'pt-movepage-reason' => 'Důvod:',
	'pt-movepage-subpages' => 'Přesunout všechny podstránky',
	'pt-movepage-action-check' => 'Zkontrolovat, zda je přesun možný',
	'pt-movepage-action-perform' => 'Přesunout',
	'pt-movepage-action-other' => 'Změnit cíl',
	'pt-deletepage-reason' => 'Důvod:',
	'pt-deletepage-subpages' => 'Odstranit všechny podstránky',
	'pt-deletepage-list-pages' => 'Seznam stránek ke smazání',
);

/** Church Slavic (словѣ́ньскъ / ⰔⰎⰑⰂⰡⰐⰠⰔⰍⰟ)
 * @author ОйЛ
 */
$messages['cu'] = array(
	'tpt-aggregategroup-new-name' => 'имѧ :',
);

/** Welsh (Cymraeg)
 * @author Lloffiwr
 */
$messages['cy'] = array(
	'pagetranslation' => 'Cyfieithu tudalen',
	'tpt-section' => 'Adran gyfieithu rhif $1',
	'tpt-section-deleted' => 'Adran gyfieithu rhif $1',
	'tpt-diff-old' => 'Y testun cynt',
	'tpt-diff-new' => 'Y testun newydd',
	'tpt-other-pages-title' => 'Tudalennau toredig',
	'tpt-select-prioritylangs-reason' => 'Rheswm:',
	'tpt-sections-prioritylangs' => 'Blaenoriaethau ymhlith yr ieithoedd',
	'tpt-languages-legend' => 'Ieithoedd eraill:',
	'tpt-discouraged-language-reason' => 'Rheswm: $1',
	'tpt-aggregategroup-add' => 'Ychwaneger',
	'tpt-aggregategroup-save' => 'Cadwer',
	'tpt-aggregategroup-new-name' => 'Enw:',
	'tpt-aggregategroup-new-description' => 'Disgrifiad (dewisol):',
	'log-name-pagetranslation' => 'Lòg cyfieithu tudalennau',
	'pt-movepage-list-pages' => "Rhestr y tudalennau i'w symud",
	'pt-movepage-list-translation' => '{{PLURAL:$1||Tudalen gyfieithu|Tudalennau cyfieithu}}',
	'pt-movepage-list-section' => '{{PLURAL:$1||Tudalen|Tudalennau}} uned gyfieithu',
	'pt-movepage-list-other' => '{{PLURAL:$1|Isdudalen arall|Isdudalen arall|Isdudalennau eraill}}',
	'pt-movepage-list-count' => "Cyfanswm y tudalennau i'w symud yw {{PLURAL:$1|$1}}.",
	'pt-movepage-legend' => 'Symud tudalen y gellir ei chyfieithu',
	'pt-movepage-current' => 'Enw cyfredol:',
	'pt-movepage-new' => 'Enw newydd:',
	'pt-movepage-reason' => 'Rheswm:',
	'pt-movepage-subpages' => 'Symud pob isdudalen',
	'pt-movepage-action-check' => 'Cadarnhau bod symud y dudalen yn bosibl',
	'pt-movepage-action-perform' => 'Symuder',
	'pt-movepage-action-other' => 'Dewis tudalen wahanol',
	'pt-deletepage-action-perform' => 'Dileer',
	'pt-deletepage-action-other' => 'Dewis tudalen wahanol',
	'pt-deletepage-current' => "Enw'r dudalen:",
	'pt-deletepage-reason' => 'Rheswm:',
	'pt-deletepage-subpages' => "Dileu'r holl isdudalennau",
	'pt-deletepage-list-pages' => "Rhestr y tudalennau i'w dileu",
	'pt-deletepage-list-translation' => 'Tudalennau cyfieithu',
	'pt-deletepage-list-section' => 'Tudalennau uned gyfieithu',
	'pt-deletepage-list-other' => 'Isdudalennau eraill',
	'pt-deletepage-list-count' => "Cyfanswm y tudalennau i'w dileu yw {{PLURAL:$1|$1}}.",
);

/** Danish (dansk)
 * @author Byrial
 * @author Christian List
 * @author Emilkris33
 * @author Kaare
 * @author Peter Alberti
 * @author Purodha
 */
$messages['da'] = array(
	'pagetranslation' => 'Sideoversættelse',
	'right-pagetranslation' => 'Markere versioner af sider for oversættelse',
	'action-pagetranslation' => 'håndter oversætbare sider',
	'tpt-desc' => 'Udvidelse til oversættelse af indholdssider',
	'tpt-section' => 'Oversættelsesenhed $1',
	'tpt-section-new' => 'Ny oversættelsesenhed.
Navn: $1',
	'tpt-section-deleted' => 'Oversættelsesenhed $1',
	'tpt-template' => 'Sideskabelon',
	'tpt-templatediff' => 'Sideskabelonen er blevet ændret.',
	'tpt-diff-old' => 'Forrige tekst',
	'tpt-diff-new' => 'Ny tekst',
	'tpt-submit' => 'Markér denne version for oversættelse',
	'tpt-sections-oldnew' => 'Nye og eksisterende oversættelsesenheder',
	'tpt-sections-deleted' => 'Slettede oversættelsesenheder',
	'tpt-sections-template' => 'Skabelon til oversættelsesside',
	'tpt-action-nofuzzy' => 'Ugyldiggør ikke oversættelser.',
	'tpt-badtitle' => 'Det angivne sidenavn ($1) er ikke en gyldig titel',
	'tpt-nosuchpage' => 'Siden $1 findes ikke',
	'tpt-oldrevision' => '$2 er ikke den seneste version af siden [[$1]].
Kun den seneste version kan markeres for oversættelse.',
	'tpt-notsuitable' => 'Siden $1 er ikke parat til oversættelse.
Sørg for at den har <nowiki><translate></nowiki>-tags og en gyldig syntaks.',
	'tpt-saveok' => 'Siden [[$1]] er blevet markeret til oversættelse med $2 {{PLURAL:$2|oversættelsesenhed|oversættelsesenheder}}.
Siden kan nu <span class="plainlinks">[$3 oversættes]</span>.',
	'tpt-offer-notify' => 'Du kan <span class="plainlinks">[$1 underrette oversættere]</span> om denne side.',
	'tpt-badsect' => '"$1" er ikke et gyldig navn for oversættelsesenhed $2.',
	'tpt-showpage-intro' => 'Herunder listes der nye, eksisterende og slettede oversættelsesenheder.
Før denne version markeres til oversættelse, skal du kontrollere, at ændringerne i oversættelsesenhederne er minimeret for at undgå at give oversætterne unødigt arbejde.',
	'tpt-mark-summary' => 'Markerede denne version for oversættelse',
	'tpt-edit-failed' => 'Kunne ikke opdatere siden: $1',
	'tpt-duplicate' => 'Oversættelsesenhedsnavnet $1 anvendes mere end en gang.',
	'tpt-already-marked' => 'Den seneste version af denne side er allerede markeret for oversættelse.',
	'tpt-unmarked' => 'Siden $1 er ikke længere markeret til oversættelse.',
	'tpt-list-nopages' => 'Ingen sider er markeret for oversættelse eller parate til at blive markeret for oversættelse.',
	'tpt-new-pages-title' => 'Sider foreslået til oversættelse',
	'tpt-old-pages-title' => 'Sider som oversættes',
	'tpt-other-pages-title' => 'Fejlbehæftede sider',
	'tpt-discouraged-pages-title' => 'Frarådede sider',
	'tpt-new-pages' => '{{PLURAL:$1|Denne side|Disse sider}} indeholder tekst med oversættelsestags, men ingen version af {{PLURAL:$1|siden|siderne}} er i øjeblikket markeret for oversættelse.',
	'tpt-old-pages' => 'En version af {{PLURAL:$1|denne side|disse sider}} er markeret for oversættelse.',
	'tpt-other-pages' => '{{PLURAL:$1|En gammel version af denne side er|Ældre versioner af disse sider er}} markeret til oversættelse,
men {{PLURAL:$1|den seneste version|de seneste versioner}} kan ikke mærkes til oversættelse.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Denne side|Disse sider}} er blevet frarådet yderligere oversættelse.',
	'tpt-select-prioritylangs' => 'Liste over sprogkoder for prioriterede sprog, adskilt med kommaer:',
	'tpt-select-prioritylangs-force' => 'Forhindre oversættelse til andre sprog end de prioriterede sprog',
	'tpt-select-prioritylangs-reason' => 'Begrundelse:',
	'tpt-sections-prioritylangs' => 'Prioriterede sprog',
	'tpt-rev-mark' => 'marker til oversættelse',
	'tpt-rev-unmark' => 'fjern fra oversættelse',
	'tpt-rev-discourage' => 'fraråd',
	'tpt-rev-encourage' => 'gendan',
	'tpt-rev-mark-tooltip' => 'Marker den seneste version af denne side til oversættelse.',
	'tpt-rev-unmark-tooltip' => 'Fjern denne side fra oversættelse.',
	'tpt-rev-discourage-tooltip' => 'Fraråd yderligere oversættelse af denne side.',
	'tpt-rev-encourage-tooltip' => 'Gendan denne side til normal oversættelse.',
	'translate-tag-translate-link-desc' => 'Oversæt denne side',
	'translate-tag-markthis' => 'Markér denne side for oversættelse',
	'translate-tag-markthisagain' => 'Denne side er <span class="plainlinks">[$1 ændret]</span> siden den sidst blev <span class="plainlinks">[$2 markeret for oversættelse]</span>.',
	'translate-tag-hasnew' => 'Denne side indeholder <span class="plainlinks">[$1 ændringer]</span> som ikke er markeret for oversættelse.',
	'tpt-translation-intro' => 'Denne side er en <span class="plainlinks">[$1 oversat version]</span> af siden [[$2]], og oversættelsen er $3 % komplet.',
	'tpt-languages-legend' => 'Andre sprog:',
	'tpt-languages-zero' => 'Begynd på oversættelsen til dette sprog',
	'tpt-tab-translate' => 'Oversæt',
	'tpt-target-page' => 'Denne side kan ikke opdateres manuelt.
Siden er en oversættelse af siden [[$1]] og oversættelsen kan opdateres ved at bruge [$2 oversættelsesværktøjet].',
	'tpt-unknown-page' => 'Dette navnerum er reserveret til oversættelser af indholdssider.
Siden som du prøver at redigere, ser ikke ud til at svare til nogen side markeret for oversættelse.',
	'tpt-translation-restricted' => 'Oversættelse af denne side til dette sprog blev forhindret af en oversættelsesadministrator.

Årsag: $1',
	'tpt-discouraged-language-force' => "'''Denne side kan ikke oversættes til $2.'''

En oversættelsesadministrator besluttede at denne side kun kan oversættes til $3.",
	'tpt-discouraged-language' => "'''Oversættelse til $2 er ikke en prioritet for denne side.'''

En oversættelsesadministrator besluttede at fokusere oversættelsesarbejdet på $3.",
	'tpt-discouraged-language-reason' => 'Begrundelse: $1',
	'tpt-priority-languages' => 'En oversættelsesadministrator har sat prioritetssprogene for denne gruppe til $1.',
	'tpt-render-summary' => 'Opdaterer for at passe til en ny version af kildesiden',
	'tpt-download-page' => 'Eksportér side med oversættelser',
	'aggregategroups' => 'Samlegrupper',
	'tpt-aggregategroup-add' => 'Tilføj',
	'tpt-aggregategroup-save' => 'Gem',
	'tpt-aggregategroup-add-new' => 'Tilføj en ny samlegruppe',
	'tpt-aggregategroup-new-name' => 'Navn:',
	'tpt-aggregategroup-new-description' => 'Beskrivelse (valgfri):',
	'tpt-aggregategroup-remove-confirm' => 'Er du sikker på, at du vil slette denne samlegruppe?',
	'tpt-aggregategroup-invalid-group' => 'Gruppen findes ikke',
	'pt-parse-open' => 'Ubalanceret &lt;translate> tag.
Oversættelse skabelon: <pre>$1</pre>',
	'pt-parse-close' => 'Ubalanceret &lt;/translate> tag.
Oversættelse skabelon: <pre>$1</pre>',
	'pt-parse-nested' => 'Indlejrede &lt;translate>-oversættelsesenheder er ikke tilladt.
Tagtekst: <pre>$1</pre>',
	'pt-shake-multiple' => 'Flere oversættelsesenhedsmarkører til en oversættelsesenhed.
Oversættelsesenhedstekst: <pre>$1</pre>',
	'pt-shake-position' => 'Oversættelsesenhedsmarkører på uventet position.
Oversættelsesenhedstekst: <pre>$1</pre>',
	'pt-shake-empty' => 'Tom oversættelsesenhed for markøren "$1".',
	'log-description-pagetranslation' => 'Log for handlinger i forbindelse med side oversættelses systemet',
	'log-name-pagetranslation' => 'Sideoversættelseslog',
	'logentry-pagetranslation-mark' => '$1 {{GENDER:$2|markerede}} $3 til oversættelse',
	'logentry-pagetranslation-unmark' => '$1 {{GENDER:$2|fjernede}} $3 fra oversættelse',
	'logentry-pagetranslation-moveok' => '$1 {{GENDER:$2|afsluttede}} omdøbning af den oversætbare side $3 til $4',
	'logentry-pagetranslation-movenok' => '$1 {{GENDER:$2|stødte på}} et problem under flytning af siden $3 til $4',
	'logentry-pagetranslation-deletefok' => '$1 {{GENDER:$2|afsluttede}} sletning af den oversætbare side $3',
	'logentry-pagetranslation-deletefnok' => '$1 {{GENDER:$2|kunne ikke}} slette $3 der tilhører den oversætbare side $4',
	'logentry-pagetranslation-deletelok' => '$1 {{GENDER:$2|afsluttede}} sletning af den oversætbare side $3',
	'logentry-pagetranslation-deletelnok' => '$1 {{GENDER:$2|kunne ikke}} slette $3 der tilhører oversættelsesside $4',
	'logentry-pagetranslation-encourage' => '$1 {{GENDER:$2|opmuntrede}} oversættelse af $3',
	'logentry-pagetranslation-discourage' => '$1 {{GENDER:$2|frarådede}} oversættelse af $3',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1 {{GENDER:$2|fjernede}} prioritetssprog fra den oversætbare side $3',
	'logentry-pagetranslation-prioritylanguages' => '$1 {{GENDER:$2|satte}} prioritetssprogene for den oversætbare side $3 til $5',
	'logentry-pagetranslation-prioritylanguages-force' => '$1 {{GENDER:$2|begrænsede}} sprogene for den oversætbare side $3 til $5',
	'logentry-pagetranslation-associate' => '$1 {{GENDER:$2|tilføjede}} den oversætbare side $3 til den samlede gruppe $4',
	'logentry-pagetranslation-dissociate' => '$1 {{GENDER:$2|fjernede}} den oversætbare side $3 fra den samlede gruppe $4',
	'pt-movepage-title' => 'Flyt oversætbare side $1',
	'pt-movepage-blockers' => 'Den oversætbare side kan ikke flyttes til et nyt navn på grund af følgende {{PLURAL:$1|fejl|fejl}}:',
	'pt-movepage-block-base-exists' => 'Den oversætbare målside "[[:$1]]" findes.',
	'pt-movepage-block-base-invalid' => 'Navnet på den oversætbare målside er ikke en gyldig titel.',
	'pt-movepage-block-tp-exists' => 'Mål oversættelsessiden [[:$2]] findes.',
	'pt-movepage-block-tp-invalid' => 'Mål oversættelses side titlen for [[:$1]] ville være ugyldig (for lang?).',
	'pt-movepage-block-section-exists' => 'Målsiden "[[:$2]]" hørende til oversættelsesenheden findes.',
	'pt-movepage-block-section-invalid' => 'Målsidens titel for "[[:$1]]" til oversættelsesenheden ville blive ugyldig (for lang?).',
	'pt-movepage-block-subpage-exists' => 'Mål undersiden [[:$2]] findes.',
	'pt-movepage-block-subpage-invalid' => 'Mål underside titlen for [[:$1]] ville være ugyldig (for lang?).',
	'pt-movepage-list-pages' => 'Liste over sider til at flytte',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Oversættelsesside|Oversættelsessider}}',
	'pt-movepage-list-section' => '{{PLURAL:$1|Oversættelsesenhedsside|Oversættelsesenhedssider}}',
	'pt-movepage-list-other' => '{{PLURAL:$1|Anden underside|Andre undersider}}',
	'pt-movepage-list-count' => 'I alt $1 {{PLURAL:$1|side|sider}} til at flytte.',
	'pt-movepage-legend' => 'Flyt oversætbare side',
	'pt-movepage-current' => 'Nuværende navn:',
	'pt-movepage-new' => 'Nyt navn:',
	'pt-movepage-reason' => 'Årsag:',
	'pt-movepage-subpages' => 'Flyt alle undersider',
	'pt-movepage-action-check' => 'Tjek om flytningen er muligt',
	'pt-movepage-action-perform' => 'Gennemfør flytningen',
	'pt-movepage-action-other' => 'Skift mål',
	'pt-movepage-intro' => 'Denne speciale side tillader dig at flytte sider, der er markeret til oversættelse.
Flytningen vil ikke være øjeblikkelig, fordi mange sider skal flyttes.
Mens siderne bliver flyttet, er det ikke muligt at interagere med de omtalte sider.
Fejl vil blive logget på [[Special:Log/pagetranslation|sideoversættelsesloggen]], og de skal repareres manuelt.',
	'pt-movepage-logreason' => 'Del af oversætbar side $1.',
	'pt-movepage-started' => 'Base siden er nu flyttet.
Husk at tjekke [[Special:Log/pagetranslation|siden oversættelsen log]] for fejl og færdiggørelses besked.',
	'pt-locked-page' => 'Denne side er låst, fordi den oversætbare side, der aktuelt er ved at blive flyttet.',
	'pt-deletepage-lang-title' => 'Sletter oversættelses side $1.',
	'pt-deletepage-full-title' => 'Sletter oversætbar side $1.',
	'pt-deletepage-invalid-title' => 'Den angivne side er ikke gyldig.',
	'pt-deletepage-invalid-text' => 'Den angivne side er ikke en oversætbar side eller en oversættelsesside.',
	'pt-deletepage-action-check' => 'List sider der skal slettes',
	'pt-deletepage-action-perform' => 'Udfør sletningen',
	'pt-deletepage-action-other' => 'Skift mål',
	'pt-deletepage-lang-legend' => 'Slet oversættelses side',
	'pt-deletepage-full-legend' => 'Slet oversætbar side',
	'pt-deletepage-any-legend' => 'Slet en oversætbar side eller en oversættelsesside',
	'pt-deletepage-current' => 'Sidenavn:',
	'pt-deletepage-reason' => 'Årsag:',
	'pt-deletepage-subpages' => 'Slet alle undersider',
	'pt-deletepage-list-pages' => 'Liste over sider til at slette',
	'pt-deletepage-list-translation' => 'Oversættelses sider',
	'pt-deletepage-list-section' => 'Oversættelsesenhedssider',
	'pt-deletepage-list-other' => 'Andre undersider',
	'pt-deletepage-list-count' => 'I alt $1 {{PLURAL:$1|side|sider}} til at slette.',
	'pt-deletepage-full-logreason' => 'Del af oversætbar side $1.',
	'pt-deletepage-lang-logreason' => 'En del af oversættelses side $1 .',
	'pt-deletepage-started' => 'Tjek venligst [[Special:Log/pagetranslation|side oversættelses log]] for fejl og færdiggørelses besked.',
	'pt-deletepage-intro' => 'Med denne specielle side kan du slette en hel oversætbar side eller en individuel oversættelsesside.
Sletningen vil ikke ske med det samme, fordi mange afhængige sider også vil blive slettet.
Fejl vil blive registreret i [[Special:Log/pagetranslation|side oversættelses log]], og de skal repareres i manuelt.',
);

/** German (Deutsch)
 * @author ChrisiPK
 * @author Imre
 * @author Kghbln
 * @author MF-Warburg
 * @author McDutchie
 * @author Metalhead64
 * @author Purodha
 * @author Shirayuki
 * @author The Evil IP address
 * @author Umherirrender
 * @author Vogone
 */
$messages['de'] = array(
	'pagetranslation' => 'Seiten übersetzen',
	'right-pagetranslation' => 'Seitenversionen zur Übersetzung freigeben',
	'action-pagetranslation' => 'übersetzbare Seiten zu verwalten',
	'tpt-desc' => 'Ermöglicht das Übersetzen von Inhaltsseiten',
	'tpt-section' => 'Übersetzungseinheit $1',
	'tpt-section-new' => 'Neue Übersetzungseinheit. Name: $1',
	'tpt-section-deleted' => 'Übersetzungseinheit $1',
	'tpt-template' => 'Seitenvorlage',
	'tpt-templatediff' => 'Die Seitenvorlage hat sich geändert.',
	'tpt-diff-old' => 'Vorheriger Text',
	'tpt-diff-new' => 'Neuer Text',
	'tpt-submit' => 'Diese Version zur Übersetzung freigeben',
	'tpt-sections-oldnew' => 'Neue und vorhandene Übersetzungseinheiten',
	'tpt-sections-deleted' => 'Gelöschte Übersetzungseinheiten',
	'tpt-sections-template' => 'Übersetzungsseitenvorlage',
	'tpt-action-nofuzzy' => 'Die Übersetzungen nicht als veraltet markieren',
	'tpt-badtitle' => 'Der angegebene Seitenname „$1“ ist kein gültiger Titel',
	'tpt-nosuchpage' => 'Die Seite „$1“ ist nicht vorhanden',
	'tpt-oldrevision' => '$2 ist nicht die letzte Version der Seite [[$1]].
Nur die letzte Version kann zur Übersetzung freigegeben werden.',
	'tpt-notsuitable' => 'Die Seite $1 ist nicht zum Übersetzen geeignet.
Stelle sicher, dass ein <nowiki><translate></nowiki>-Tag und gültige Syntax verwendet wird.',
	'tpt-saveok' => 'Die Seite [[$1]] wurde mit {{PLURAL:$2|einem übersetzbaren Abschnitt|$2 übersetzbaren Abschnitten}} zur Übersetzung freigegeben.
Diese Seite kann nun <span class="plainlinks">[$3 übersetzt]</span> werden.',
	'tpt-offer-notify' => 'Du kannst über diese Seite <span class="plainlinks">[$1 Übersetzer benachrichtigen]</span>.',
	'tpt-badsect' => '„$1“ ist kein gültiger Name für Übersetzungseinheit $2.',
	'tpt-showpage-intro' => 'Untenstehend sind neue, vorhandene und gelöschte Übersetzungseinheiten aufgelistet.
Bevor du diese Version zur Übersetzung freigibst, stelle bitte sicher, dass die Änderungen an den Übersetzungseinheiten minimal sind. Damit verhinderst du unnötige Arbeit für die Übersetzer.',
	'tpt-mark-summary' => 'Diese Seite wurde zum Übersetzen freigegeben',
	'tpt-edit-failed' => 'Seite kann nicht aktualisiert werden: $1',
	'tpt-duplicate' => 'Der Übersetzungseinheitname $1 wird mehr als einmal verwendet.',
	'tpt-already-marked' => 'Die letzte Version dieser Seite wurde bereits zum Übersetzen freigegeben.',
	'tpt-unmarked' => 'Seite $1 ist nicht länger als zu Übersetzen markiert.',
	'tpt-list-nopages' => 'Es sind keine Seiten zum Übersetzen freigegeben und auch nicht vorbereitet, um freigegeben werden zu können.',
	'tpt-new-pages-title' => 'Zur Übersetzung vorgeschlagene Seiten',
	'tpt-old-pages-title' => 'Zu übersetzende Seiten',
	'tpt-other-pages-title' => 'Fehlerhafte Seiten',
	'tpt-discouraged-pages-title' => 'Von der Übersetzung zurückgezogene Seiten',
	'tpt-new-pages' => '{{PLURAL:$1|Diese Seite beinhaltet|Diese Seiten beinhalten}} Text zum Übersetzen. Es wurde aber noch keine Version dieser {{PLURAL:$1|Seite|Seiten}} zum Übersetzen freigegeben.',
	'tpt-old-pages' => 'Eine Version dieser {{PLURAL:$1|Seite|Seiten}} wurde zur Übersetzung freigegeben.',
	'tpt-other-pages' => 'Veraltete Versionen {{PLURAL:$1|dieser Seite|dieser Seiten}} sind zur Übersetzung freigegeben.
Die neueste Version kann hingegen nicht zur Übersetzung freigegeben werden.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Diese Seite wurde|Diese Seiten wurden}} von der Übersetzung zurückgezogen.',
	'tpt-select-prioritylangs' => 'Komma-getrennte Liste der Codes der zu priorisierenden Sprachen:',
	'tpt-select-prioritylangs-force' => 'Übersetzungen in andere Sprachen als die priorisierte Sprache verhindern',
	'tpt-select-prioritylangs-reason' => 'Grund:',
	'tpt-sections-prioritylangs' => 'Priorisierte Sprachen',
	'tpt-rev-mark' => 'Zum Übersetzen freigeben',
	'tpt-rev-unmark' => 'Freigabe zum Übersetzen entfernen',
	'tpt-rev-discourage' => 'Freigabe zurückziehen',
	'tpt-rev-encourage' => 'Freigabe wiederherstellen',
	'tpt-rev-mark-tooltip' => 'Die letzte Version dieser Seite zum Übersetzen freigeben.',
	'tpt-rev-unmark-tooltip' => 'Die Freigabe zum Übersetzen dieser Seite entfernen.',
	'tpt-rev-discourage-tooltip' => 'Die Freigabe für weitere Übersetzungen dieser Seite zurückziehen.',
	'tpt-rev-encourage-tooltip' => 'Die Freigabe zum Übersetzen dieser Seite wiederherstellen.',
	'translate-tag-translate-link-desc' => 'Diese Seite übersetzen',
	'translate-tag-markthis' => 'Diese Seite zur Übersetzung freigeben',
	'translate-tag-markthisagain' => 'Diese Seite wurde <span class="plainlinks">[$1 bearbeitet]</span>, nachdem sie zuletzt <span class="plainlinks">[$2 zur Übersetzung freigegeben]</span> wurde.',
	'translate-tag-hasnew' => 'Diese Seite enthält <span class="plainlinks">[$1 Bearbeitungen]</span>, die nicht zum Übersetzen freigegeben sind.',
	'tpt-translation-intro' => 'Diese Seite ist eine <span class="plainlinks">[$1 übersetzte Version]</span> der Seite [[$2]] und die Übersetzung ist zu $3 % abgeschlossen und aktuell.',
	'tpt-languages-legend' => 'Andere Sprachen:',
	'tpt-languages-zero' => 'Mit dem Übersetzen in diese Sprache anfangen',
	'tpt-tab-translate' => 'Übersetzen',
	'tpt-target-page' => 'Diese Seite kann nicht manuell aktualisiert werden.
Diese Seite ist eine Übersetzung der Seite [[$1]] und die Übersetzung kann mithilfe des [$2 Übersetzungswerkzeuges] aktualisiert werden.',
	'tpt-unknown-page' => 'Dieser Namensraum ist für das Übersetzen von Wikiseiten reserviert.
Die Seite, die gerade bearbeitet wird, hat keine Verbindung zu einer übersetzbaren Seite.',
	'tpt-translation-restricted' => 'Das Übersetzen dieser Seite in diese Sprache wurde durch einen Übersetzungsadministrator deaktiviert.

Grund: $1',
	'tpt-discouraged-language-force' => 'Ein Übersetzungsadministrator hat die Sprachen eingeschränkt, in die diese Seite übersetzt werden kann. Diese Sprache befindet sich nicht unter den zulässigen Sprachen.

Grund: $1',
	'tpt-discouraged-language' => 'Diese Sprache befindet sich nicht unter den von einem Übersetzungsadministrator priorisierten Sprachen für die Übersetzung dieser Seite.

Grund: $1',
	'tpt-discouraged-language-reason' => 'Grund: $1',
	'tpt-priority-languages' => 'Ein Übersetzungsadministrator hat die priorisierte Sprachen für diese Nachrichtengruppe auf $1 festgelegt.',
	'tpt-render-summary' => 'Übernehme Bearbeitung einer neuen Version der Quellseite',
	'tpt-download-page' => 'Seite mit Übersetzungen exportieren',
	'aggregategroups' => 'Zusammenfassende Nachrichtengruppen',
	'tpt-aggregategroup-add' => 'Hinzufügen',
	'tpt-aggregategroup-save' => 'Speichern',
	'tpt-aggregategroup-add-new' => 'Eine neue Hauptnachrichtengruppe hinzufügen',
	'tpt-aggregategroup-new-name' => 'Name:',
	'tpt-aggregategroup-new-description' => 'Beschreibung (optional):',
	'tpt-aggregategroup-remove-confirm' => 'Bist Du sicher, dass Du diese Gruppe löschen möchtest?',
	'tpt-aggregategroup-invalid-group' => 'Die Gruppe ist nicht vorhanden',
	'pt-parse-open' => 'Eine &lt;translate&gt;-Markierung hat kein Gegenstück.
Übersetzungsvorlage: <pre>$1</pre>',
	'pt-parse-close' => 'Eine &lt;/translate>-Markierung hat kein Gegenstück.
Übersetzungsvorlage: <pre>$1</pre>',
	'pt-parse-nested' => 'Verschachtelte &lt;translate>-Übersetzungseinheiten sind nicht möglich.
Text des Tags: <pre>$1</pre>',
	'pt-shake-multiple' => 'Mehrere Übersetzungseinheitenmarker für eine Übersetzungseinheit.
Text der Übersetzungseinheit: <pre>$1</pre>',
	'pt-shake-position' => 'Übersetzungseinheitenmarker befinden sich an unerwarteter Stelle.
Text der Übersetzungseinheit: <pre>$1</pre>',
	'pt-shake-empty' => 'Die Übersetzungseinheit für Marker „$1“ ist leer.',
	'log-description-pagetranslation' => 'Logbuch der Änderungen im Zusammenhang mit dem Übersetzungssystem für Seiten',
	'log-name-pagetranslation' => 'Übersetzungs-Logbuch',
	'logentry-pagetranslation-mark' => '$1 {{GENDER:$2|markierte}} die Seite „$3“ zum Übersetzen',
	'logentry-pagetranslation-unmark' => '$1 {{GENDER:$2|entfernte}} die Seite $3 aus dem Übersetzungssystem',
	'logentry-pagetranslation-moveok' => '$1 {{GENDER:$2|schloss}} die Umbenennung der übersetzbaren Seite von $3 in $4 ab',
	'logentry-pagetranslation-movenok' => '$1 {{GENDER:$2|hatte}} ein Problem beim Verschieben der Seite von $3 nach $4',
	'logentry-pagetranslation-deletefok' => '$1 {{GENDER:$2|schloss}} die Löschung der übersetzbaren Seite $3 ab',
	'logentry-pagetranslation-deletefnok' => '$1 {{GENDER:$2|konnte}} die Seite $3 nicht löschen, die zur übersetzbaren Seite $4 gehört',
	'logentry-pagetranslation-deletelok' => '$1 {{GENDER:$2|schloss}} die Löschung der Übersetzungsseite $3 ab',
	'logentry-pagetranslation-deletelnok' => '$1 {{GENDER:$2|konnte}} die Seite $3 nicht löschen, die zur Übersetzungsseite $4 gehört',
	'logentry-pagetranslation-encourage' => '$1 {{GENDER:$2|empfahl}} die Übersetzung der Seite $3',
	'logentry-pagetranslation-discourage' => '$1 {{GENDER:$2|riet}} von der Übersetzung der Seite $3 ab',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1 {{GENDER:$2|entfernte}} die priorisierten Sprachen von der übersetzbaren Seite $3',
	'logentry-pagetranslation-prioritylanguages' => '$1 {{GENDER:$2|legte}} die priorisierten Sprachen für die übersetzbare Seite $3 auf $5 fest',
	'logentry-pagetranslation-prioritylanguages-force' => '$1 {{GENDER:$2|begrenzte}} die Sprachen für die übersetzbare Seite $3 auf $5',
	'logentry-pagetranslation-associate' => '$1 {{GENDER:$2|fügte}} die übersetzbare Seite $3 zur zusammengefassten Gruppe $4 hinzu',
	'logentry-pagetranslation-dissociate' => '$1 {{GENDER:$2|entfernte}} die übersetzbare Seite $3 von der zusammengefassten Gruppe $4',
	'pt-movepage-title' => 'Die Übersetzungsseite $1 verschieben',
	'pt-movepage-blockers' => 'Die zum Übersetzen vorgesehene Seite konnte aufgrund {{PLURAL:$1|folgendes Fehlers|folgender Fehler}} nicht zur neuen Bezeichnung verschoben werden:',
	'pt-movepage-block-base-exists' => 'Die übersetzbare Zielseite „[[:$1]]“ existiert bereits.',
	'pt-movepage-block-base-invalid' => 'Der Name der übersetzbaren Zielseite hat keine gültige Bezeichnung.',
	'pt-movepage-block-tp-exists' => 'Die Übersetzungsseite [[:$2]] existiert bereits.',
	'pt-movepage-block-tp-invalid' => 'Die Zielbezeichnung der Übersetzungsseite für [[:$1]] wäre ungültig (zu lang?).',
	'pt-movepage-block-section-exists' => 'Die Seite „[[:$2]]“ zur Übersetzungseinheit ist bereits vorhanden.',
	'pt-movepage-block-section-invalid' => 'Die Zielseite der Übersetzungseinheit für „[[:$1]]“ wäre ungültig (zu lang?).',
	'pt-movepage-block-subpage-exists' => 'Die Unterseite [[:$2]] existiert bereits.',
	'pt-movepage-block-subpage-invalid' => 'Die Zielbezeichnung der Unterseite für [[:$1]] wäre ungültig (zu lang?).',
	'pt-movepage-list-pages' => 'Liste der zu verschiebenden Seiten',
	'pt-movepage-list-translation' => 'Übersetzte {{PLURAL:$1|Seite|Seiten}}',
	'pt-movepage-list-section' => '{{PLURAL:$1|Seite|Seiten}} der Übersetzungseinheiten',
	'pt-movepage-list-other' => 'Weitere {{PLURAL:$1|Unterseite|Unterseiten}}',
	'pt-movepage-list-count' => 'Insgesamt gibt es $1 zu verschiebende {{PLURAL:$1|Seite|Seiten}}.',
	'pt-movepage-legend' => 'Übersetzungsseite verschieben',
	'pt-movepage-current' => 'Aktueller Seitenname:',
	'pt-movepage-new' => 'Neuer Seitenname:',
	'pt-movepage-reason' => 'Grund:',
	'pt-movepage-subpages' => 'Alle Unterseiten verschieben',
	'pt-movepage-action-check' => 'Überprüfung, ob die Verschiebung möglich ist',
	'pt-movepage-action-perform' => 'Verschiebung durchführen',
	'pt-movepage-action-other' => 'Ziel ändern',
	'pt-movepage-intro' => 'Diese Spezialseite ermöglicht es Seiten zu verschieben, die zur Übersetzung gekennzeichnet wurden.
Die Verschiebung wird nicht unverzüglich erfolgen, da dabei viele Seiten zu verschieben sind.
Während des Verschiebevorgangs ist es nicht möglich, die entsprechenden Seiten zu nutzen.
Verschiebefehler werden im [[Special:Log/pagetranslation|Übersetzungs-Logbuch]] aufgezeichnet und müssen manuell korrigiert werden.',
	'pt-movepage-logreason' => 'Teil der übersetzbaren Seite $1.',
	'pt-movepage-started' => 'Die Basisseite wurde nunmehr verschoben.
Bitte prüfe das [[Special:Log/pagetranslation|Übersetzungs-Logbuch]] auf Fehlermeldungen, bzw. die Vollzugsnachricht.',
	'pt-locked-page' => 'Diese Seite ist gesperrt, da die Übersetzungsseite momentan verschoben wird.',
	'pt-deletepage-lang-title' => 'Löschen der übersetzten Seite $1.',
	'pt-deletepage-full-title' => 'Löschen der übersetzbaren Seite $1.',
	'pt-deletepage-invalid-title' => 'Die angegebene Seite ist ungültig.',
	'pt-deletepage-invalid-text' => 'Die angegebene Seite ist weder eine übersetzbare Seite noch eine Übersetzungsseite.',
	'pt-deletepage-action-check' => 'Zu löschende Seiten auflisten',
	'pt-deletepage-action-perform' => 'Löschung ausführen',
	'pt-deletepage-action-other' => 'Das Ziel ändern',
	'pt-deletepage-lang-legend' => 'Übersetzte Seite löschen',
	'pt-deletepage-full-legend' => 'Übersetzbare Seite löschen',
	'pt-deletepage-any-legend' => 'Übersetzbare Seite oder Übersetzungsseite löschen',
	'pt-deletepage-current' => 'Seitenname:',
	'pt-deletepage-reason' => 'Grund:',
	'pt-deletepage-subpages' => 'Alle Unterseiten löschen',
	'pt-deletepage-list-pages' => 'Liste der zu löschenden Seiten',
	'pt-deletepage-list-translation' => 'Übersetzte Seiten',
	'pt-deletepage-list-section' => 'Seiten der Übersetzungseinheiten',
	'pt-deletepage-list-other' => 'Weitere Unterseiten',
	'pt-deletepage-list-count' => 'Insgesamt gibt es $1 zu löschende {{PLURAL:$1|Seite|Seiten}}.',
	'pt-deletepage-full-logreason' => 'Teil der übersetzbaren Seite $1.',
	'pt-deletepage-lang-logreason' => 'Teil der übersetzten Seite $1.',
	'pt-deletepage-started' => 'Bitte das [[Special:Log/pagetranslation|Übersetzungs-Logbuch]] nach Fehlern und Ausführungsnachrichten prüfen.',
	'pt-deletepage-intro' => 'Diese Spezialseite ermöglicht die Löschung einer ganzen übersetzbaren Seite oder einer individuellen Übersetzungsseite in einer Sprache.
Die Ausführung erfolgt nicht unmittelbar, da auch alle dazugehörigen Seiten gelöscht werden.
Fehler werden im [[Special:Log/pagetranslation|Übersetzungs-Logbuch]] aufgezeichnet und müssen nachträglich manuell berichtigt werden.',
);

/** German (formal address) (Deutsch (Sie-Form)‎)
 * @author Imre
 * @author Kghbln
 * @author Purodha
 * @author The Evil IP address
 * @author Umherirrender
 */
$messages['de-formal'] = array(
	'tpt-action-nofuzzy' => 'Setzen Sie die Übersetzungen nicht außer Kraft',
	'tpt-notsuitable' => 'Die Seite $1 ist nicht zum Übersetzen geeignet.
Stellen Sie sicher, dass ein <nowiki><translate></nowiki>-Tag und gültige Syntax verwendet wird.',
	'tpt-showpage-intro' => 'Untenstehend sind neue, vorhandene und gelöschte Übersetzungseinheiten aufgelistet.
Bevor Sie diese Version zur Übersetzung freigeben, stellen Sie bitte sicher, dass die Änderungen an den Übersetzungseinheiten minimal sind. Damit verhindern Sie unnötige Arbeit für die Übersetzer.',
	'pt-movepage-started' => 'Die Basisseite wurde nunmehr verschoben.
Bitte prüfen Sie das [[Special:Log/pagetranslation|Übersetzungs-Logbuch]] auf Fehlermeldungen, bzw. die Vollzugsnachricht.',
);

/** Zazaki (Zazaki)
 * @author Erdemaslancan
 */
$messages['diq'] = array(
	'pagetranslation' => 'Pela açarnayışi',
	'tpt-section' => 'Yewronê açarnayışê $1',
	'tpt-section-new' => 'Yewena Açarnayış de newan.
Name: $1',
	'tpt-section-deleted' => 'Yewronê açarnayışê $1',
	'tpt-template' => 'Pela şabloni',
	'tpt-diff-old' => 'Metno verên',
	'tpt-diff-new' => 'Metno newe',
	'tpt-old-pages-title' => 'Pela açarnayışi',
	'tpt-other-pages-title' => 'Pela şahtiyayi',
	'tpt-discouraged-pages-title' => 'Vatenena pelayan',
	'tpt-select-prioritylangs-reason' => 'Sebeb:',
	'translate-tag-translate-link-desc' => 'Na perer açarnê',
	'tpt-languages-legend' => 'Zıwanê bini:',
	'tpt-discouraged-language-reason' => 'Sebeb: $1',
	'aggregategroups' => 'Grubi pêro',
	'tpt-aggregategroup-add' => 'Deke',
	'tpt-aggregategroup-save' => 'Star ke',
	'tpt-aggregategroup-new-name' => 'Name:',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Pera|Perê}} açarnayışi',
	'pt-movepage-list-other' => '{{PLURAL:$1|Pera bin|Perê bini}}',
	'pt-movepage-current' => 'Nameyo raverde:',
	'pt-movepage-new' => 'Nameyo newe:',
	'pt-movepage-reason' => 'Sebeb:',
	'pt-deletepage-action-other' => 'Etiketan bivurne',
	'pt-deletepage-current' => 'Nameyê pele:',
	'pt-deletepage-reason' => 'Sebeb:',
	'pt-deletepage-list-translation' => 'Peleyê açarnayışin',
	'pt-deletepage-list-other' => 'Bınpeley bini',
);

/** Lower Sorbian (dolnoserbski)
 * @author Michawiki
 */
$messages['dsb'] = array(
	'pagetranslation' => 'Pśełožowanje bokow',
	'right-pagetranslation' => 'Wersije bokow za pśełožowanje markěrowaś',
	'action-pagetranslation' => 'pśełožujobne boki zastojaś',
	'tpt-desc' => 'Rozšyrjenje za pśełožowanje wopśimjeśowych bokow',
	'tpt-section' => 'Pśełožowańska jadnotka $1',
	'tpt-section-new' => 'Nowa pśełožowańska jadnotka. Mě: $1',
	'tpt-section-deleted' => 'Pśełožowańska jadnotka $1',
	'tpt-template' => 'Bokowa pśedłoga',
	'tpt-templatediff' => 'Bokowa pśedłoga jo se změniła.',
	'tpt-diff-old' => 'Pśedchadny tekst',
	'tpt-diff-new' => 'Nowy tekst',
	'tpt-submit' => 'Toś tu wersiju za pśełožowanje markěrowaś',
	'tpt-sections-oldnew' => 'Nowe a eksistowace pśełožowańske jadnotki',
	'tpt-sections-deleted' => 'Wulašowane pśełožowańske jadnotki',
	'tpt-sections-template' => 'Pśedłoga pśełožowańskego boka',
	'tpt-action-nofuzzy' => 'Njeanulěruj pśełožki',
	'tpt-badtitle' => 'Pódane bokowe mě ($1) njejo płaśiwy titel',
	'tpt-nosuchpage' => 'Bok $1 njeeksistěrujo',
	'tpt-oldrevision' => '$2 njejo aktualna wersija boka [[$1]].
Jano aktualne wersije daju se za pśełožowanje markěrowaś.',
	'tpt-notsuitable' => 'Bok $1 njejo gódny za pśełožowanje.
Zawěsć, až ma toflicki <nowiki><translate></nowiki> a płaśiwu syntaksu.',
	'tpt-saveok' => 'Bok [[$1]] jo se markěrował za pśełožowanje z $2 {{PLURAL:$2|pśełožujobneju jadnotku|pśełožujobnyma jadnotkoma|pśełožujobnymi jadnotkami|pśełožujobnymi jadnotkami}}. Bok móže se něnto <span class="plainlinks">[$3 pśełožowaś]</span>.',
	'tpt-badsect' => '"$1" njejo płaśiwe mě za pśełožowańsku jadnotku $2.',
	'tpt-showpage-intro' => 'Dołojce su nowe, eksistěrujuce a wulašowane pśełožowańske jadnotki nalicone.
Nježli až markěrujoš toś tu wersiju za pśełožowanje, pśekontrolěruj, lěc změny na pśełožowańskich jadnotkach su zminiměrowane, aby se wobinuł njetrěbne źěło za pśełožowarjow.',
	'tpt-mark-summary' => 'Jo toś tu wersiju za pśełožowanje markěrował',
	'tpt-edit-failed' => 'Toś ten bok njejo se dał aktualizěrowaś: $1',
	'tpt-duplicate' => 'Mě pśełožkoweje jadnotki $1 wužywa se wěcej ako jaden raz.',
	'tpt-already-marked' => 'Aktualna wersija toś togo boka jo južo za pśełožowanje markěrowana.',
	'tpt-unmarked' => 'Bok $1 wěcej njejo za pśełožowanje markěrowany.',
	'tpt-list-nopages' => 'Žedne boki njejsu za pśełožowanje markěrowane ani su gótowe, aby se za pśełožowanje markěrowali.',
	'tpt-new-pages-title' => 'Boki naraźone za pśełožowanje',
	'tpt-old-pages-title' => 'Boki, kótarež se pśełožuju',
	'tpt-other-pages-title' => 'Wobškóźone boki',
	'tpt-discouraged-pages-title' => 'Wuzamknjone boki',
	'tpt-new-pages' => '{{PLURAL:$1|Toś ten bok wopśimujo|Toś tej boka wopśumujotej|Toś te boki wopśimuju|Toś te boki wopśimuju}} tekst z pśełožowańskimi toflickami, ale žedna wersija {{PLURAL:$1|toś togo boka|toś teju bokowu|toś tych bokow|toś tych bokow}} njejo tuchylu za pśełožowanje markěrowana.',
	'tpt-old-pages' => 'Někaka wersija {{PLURAL:$1|toś togo boka|toś teju bokowu|toś tych bokow|toś tych bokow}} jo se za pśełožowanje markěrowała.',
	'tpt-other-pages' => '{{PLURAL:$1|Stara wersija toś togo boka|Starej wersiji toś teju bokowu|Stare wersije toś tych bokow}} jo za pśełožowanje markěrowana,
ale nejnowša {{PLURAL:$1|wersija njedajo|wersiji njedajotej|wersije njedaju}} se za pśełožowanje markěrowaś.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Toś ten bok|Toś tej boka|Toś te boki|Toś te boki}} {{PLURAL:$1|jo|stej|su|su}} se wót dalšnego pśełoženja {{PLURAL:$1|wuzamknuła|wuzamknułej|wuzamknuli|wuzamknuli}}.',
	'tpt-select-prioritylangs' => 'Lisćina rěcnych kodow primarnych rěcow źělonych pśez komu:',
	'tpt-select-prioritylangs-force' => 'Pśełožkam do drugich ako primarnych rěcow zajźowaś',
	'tpt-select-prioritylangs-reason' => 'Pśicyna:',
	'tpt-sections-prioritylangs' => 'Primarne rěcy',
	'tpt-rev-mark' => 'za pśełožowanje markěrowaś',
	'tpt-rev-unmark' => 'wót pśełožowanja wuzamknuś',
	'tpt-rev-discourage' => 'wuzamknuś',
	'tpt-rev-encourage' => 'wótnowiś',
	'tpt-rev-mark-tooltip' => 'Nejnowšu wersiju toś togo boka za pśełožowanje markěrowaś.',
	'tpt-rev-unmark-tooltip' => 'Toś ten bok z pśełoženja wótpóraś',
	'tpt-rev-discourage-tooltip' => 'Dalšne pśełožki na toś tom boku wuzamknuś.',
	'tpt-rev-encourage-tooltip' => 'Toś ten bok za normalne pśełožowanje wótnowiś.',
	'translate-tag-translate-link-desc' => 'Toś ten bok pśełožyś',
	'translate-tag-markthis' => 'Toś ten bok za pśełožowanje markěrowaś',
	'translate-tag-markthisagain' => 'Toś ten bok ma <span class="plainlinks">[$1 {{PLURAL:$1|změnu|změnje|změny|změnow}}]</span>, wót togo casa, ako jo se slědny raz <span class="plainlinks">[$2 za pśełožowanje markěrował]</span>.',
	'translate-tag-hasnew' => 'Toś ten bok wopśimujo <span class="plainlinks">[$1 {{PLURAL:$1|změnu, kótaraž njejo markěrowana|změnje, kótarejž njejstej markěrowanej|změny, kótare njejsu markěrowane|změnow, kótarež njejsu markěrowane}}]</span> za pśełožowanje.',
	'tpt-translation-intro' => 'Toś ten bok jo <span class="plainlinks">[$1 pśełožona wersija]</span> boka [[$2]] a $3 % pśełožka jo dogótowane a pśełožk jo aktualny.',
	'tpt-languages-legend' => 'Druge rěcy:',
	'tpt-languages-zero' => 'Pśełožowanje za toś tu rěc zachopiś',
	'tpt-target-page' => 'Toś ten bok njedajo se manuelnje aktualizěrowaś.
Toś ten bok jo pśełožk boka [[$1]] a pśełožk dajo se z pomocu [$2 Pśełožyś] aktualizěrowaś.',
	'tpt-unknown-page' => 'Toś ten mjenjowy rum jo za pśełožki wopśimjeśowych bokow wuměnjony.
Zda se, až bok, kótaryž wopytujoš wobźěłaś, njewótpowědujo bokoju, kótaryž jo za pśełožowanje markěrowany.',
	'tpt-translation-restricted' => 'Pśełožowański administrator jo pśełožowanjeju toś togo boka  do toś teje rěcy jo zajźował.

Pśicyna: $1',
	'tpt-discouraged-language-force' => 'Pśełožowański administrator jo rěcy wobgranicował, do kótarychž toś ten bok dajo se pśełožyś. Toś rěc njejo mjazy toś tymi rěcami:

Pśicyna: $1',
	'tpt-discouraged-language' => 'Toś ta rěc njejo mjazy primarnymi rěcami, kótarež pśełožowański administrator jo za ten toś bok póstajił.

Pśicyna: $1',
	'tpt-discouraged-language-reason' => 'Pśicyna: $1',
	'tpt-priority-languages' => 'Pśełožowański administrator jo primarne rěcy za toś tu kupku ako $1 nastajił.',
	'tpt-render-summary' => 'Aktualizacija pó nowej wersiji žrědłowego boka',
	'tpt-download-page' => 'Bok z pśełožkami eksportěrowaś',
	'aggregategroups' => 'Metakupki',
	'tpt-aggregategroup-add' => 'Pśidaś',
	'tpt-aggregategroup-save' => 'Składowaś',
	'tpt-aggregategroup-add-new' => 'Nowu zespominańsku kupku pśidaś',
	'tpt-aggregategroup-new-name' => 'Mě:',
	'tpt-aggregategroup-new-description' => 'Wopisanje (opcionalne):',
	'tpt-aggregategroup-remove-confirm' => 'Coš toś tu kupku napšawdu lašowaś?',
	'tpt-aggregategroup-invalid-group' => 'Kupka njeeksistěrujo',
	'pt-parse-open' => 'Asymetriska toflicka &lt;translate>.
Pśełožowańska pśedłoga: <pre>$1</pre>',
	'pt-parse-close' => 'Asymetriska toflicka &lt;/translate>.
Pśełožowańska pśedłoga: <pre>$1</pre>',
	'pt-parse-nested' => 'Zakašćikowane pśełožowańske jadnotki &lt;translate&gt; njejsu dowólone.
Tekst toflicki: <pre>$1</pre>',
	'pt-shake-multiple' => 'Někotare marki pśełožowańskich jadnotkow za jadnu pśełožowańsku jadnotku.
Tekst pśełožowańskeje jadnotki: <pre>$1</pre>',
	'pt-shake-position' => 'Marki pśełožowańskich jadnotkow na njewócakowanem městnje.
Tekst pśełožowańskeje jadnotki: <pre>$1</pre>',
	'pt-shake-empty' => 'Prozna pśełožowańska jadnotka za marku "$1".',
	'log-description-pagetranslation' => 'Protokol za akcije w zwisku z pśełožowańskim systemom',
	'log-name-pagetranslation' => 'Protokol pśełožkow',
	'logentry-pagetranslation-mark' => '$1 jo $3 za pśełožowanje {{GENDER:$2|markěrował|markrowała}}',
	'logentry-pagetranslation-unmark' => '$1 jo $3 z pśełožowanja {{GENDER:$2|wópórał|wótpórała}}',
	'logentry-pagetranslation-moveok' => '$1 jo pśemjenjowanje pśełožujobnego boka $3 do $4 {{GENDER:$2|dokóńcył|dokóńcyła}}',
	'logentry-pagetranslation-movenok' => '$1 jo pśi pśesuwanju boka $3 do $4 na problem {{GENDER:$2|starcył|starcyła}}',
	'logentry-pagetranslation-deletefok' => '$1 jo lašowanje pśełožujobnego boka $3 {{GENDER:$2|dokóńcył|dokóńcyła}}',
	'logentry-pagetranslation-deletefnok' => '$1 njejo {{GENDER:$2|mógał|mógła}} $3 wulašowaś, kótaryž słuša k pśełožujobnemu bokoju $4',
	'logentry-pagetranslation-deletelok' => '$1 jo lašowanje pśełožowańskego boka $3 {{GENDER:$2|dokóńcył|dokóńcyła}}',
	'logentry-pagetranslation-deletelnok' => '$1 njejo {{GENDER:$2|mógał|mógła}} $3 wulašowaś, kótaryž słuša k pśełožowańskemu bokoju $4',
	'logentry-pagetranslation-encourage' => '$1 jo pśełožowanje boka $3 {{GENDER:$2|dopórucył|dopórucyła}}',
	'logentry-pagetranslation-discourage' => '$1 jo wót pśełožowanja boka $3 {{GENDER:$2|wótraźił|wótraźiła}}',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1 jo primarne rěcy z pśełožujobnego boka $3 {{GENDER:$2|wótpórał|wótpórała}}',
	'logentry-pagetranslation-prioritylanguages' => '$1 jo primarne rěcy za pśełožujobny bok $3 na $5 {{GENDER:$2|stajił|stajiła}}',
	'logentry-pagetranslation-prioritylanguages-force' => '$1 jo rěcy za pśełožujobny bok $3 na $5 {{GENDER:$2|wobgranicował|wobgranicowała}}',
	'logentry-pagetranslation-associate' => '$1 jo pśełožujobny bok $3 metakupce $4 {{GENDER:$2|pśidał|pśidała}}',
	'logentry-pagetranslation-dissociate' => '$1 jo pśełožujobny bok $3 z metakupki $4 {{GENDER:$2|wótpórał|wótpórała}}',
	'pt-movepage-title' => 'Psełožujobny bok $1 psésunuś',
	'pt-movepage-blockers' => 'Pśełožujobny bok njedajo se dla {{PLURAL:$1|slědujuceje zmólki|slědujuceju zmólkowu|slědujucych zmólkow|slědujucych zmólkow}} do nowego mjenja pśesunuś:',
	'pt-movepage-block-base-exists' => 'Celowy pśełožowański bok [[:$1]] eksistěrujo.',
	'pt-movepage-block-base-invalid' => 'Mě celowego pśełožujobnego boka njejo płaśiwy titel.',
	'pt-movepage-block-tp-exists' => 'Celowy pśełožowański bok [[:$2]] eksistěrujo.',
	'pt-movepage-block-tp-invalid' => 'Titel celowego pśełožowańskego boka za [[:$1]] by był njepłaśiwy (pśedłujki?).',
	'pt-movepage-block-section-exists' => 'Celowy bok "[[:$2]]" za pśełožowańsku jadnotku eksistěrujo.',
	'pt-movepage-block-section-invalid' => 'Titel celowego boka za "[[:$1]]" za pśełožowańsku jadnotku by był njepłaśiwy (pśedłujki?).',
	'pt-movepage-block-subpage-exists' => 'Celowy pódbok [[:$2]] eksistěrujo.',
	'pt-movepage-block-subpage-invalid' => 'Titel celowego pódboka za [[:$1]] by był njepłaśiwy (pśedłuki?).',
	'pt-movepage-list-pages' => 'Lisćina bokow, kótarež maju se pśesunuś',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Pśełožowański bok|Pśełožowańskej boka|Pśełožowańske boki}}',
	'pt-movepage-list-section' => '{{PLURAL:$1|Bok|Boka|Boki}} pśełožowańskich jadnotkow',
	'pt-movepage-list-other' => '{{PLURAL:$1|Drugi pódbok|Drugej pódboka|Druge pódboki}}',
	'pt-movepage-list-count' => 'Dogromady {{PLURAL:$1|ma se $1 bok|matej se $1 boka|maju se $1 boki|ma se $1 bokow}} pśesunuś.',
	'pt-movepage-legend' => 'Pśełožujobny bok pśesunuś',
	'pt-movepage-current' => 'Aktualne mě:',
	'pt-movepage-new' => 'Nowe mě:',
	'pt-movepage-reason' => 'Pśicyna:',
	'pt-movepage-subpages' => 'Wšykne pódboki pśesunuś',
	'pt-movepage-action-check' => 'Kontrolěrowaś, lěc pśesunjenje jo móžno',
	'pt-movepage-action-perform' => 'Pśesunuś',
	'pt-movepage-action-other' => 'Cel změniś',
	'pt-movepage-intro' => 'Toś ten specialny bok dowólujo śi boki pśesunuś, kótarež sz za pśełožk markěrowane.
Pśesunjenje njebuźo se ned staś, dokulaž wjele bokow musy se pśesunuś.

Mjaztym až boki se pśesuwaju,  njejo móžno z wótpowědnymi bokami interagěrowaś.
Zmólki budu se protokolěrowaś w [[Special:Log/pagetranslation|pséłožowańskem protokolu]] a muse se manuelnje wótpóraś.',
	'pt-movepage-logreason' => 'Źěl pśełožujobnego boka "$1".',
	'pt-movepage-started' => 'Zakładny bok jo něnto pśesunjony.
Pšosym pśekontrolěruj [[Special:Log/pagetranslation|pśełožowański protokol boka]] za zmólkami a zdźělenje wuwjeźenja.',
	'pt-locked-page' => 'Toś ten bok jo se zastajił, dokulaž pśełožujobny bok se rowno pśesuwa.',
	'pt-deletepage-lang-title' => 'Pśełožony bok $1 so lašujo.',
	'pt-deletepage-full-title' => 'Pśełožujobny bok $1 so lašujo.',
	'pt-deletepage-invalid-title' => 'Pódany bok njejo płaśiwy.',
	'pt-deletepage-invalid-text' => 'Pódany bok njejo ani pśełožujobny bok ani pśełožowański bok.',
	'pt-deletepage-action-check' => 'Boki nalicyś, kótarež maju se wulašowaś',
	'pt-deletepage-action-perform' => 'Lašowaś',
	'pt-deletepage-action-other' => 'Cel změniś',
	'pt-deletepage-lang-legend' => 'Pśełožony bok wulašowaś',
	'pt-deletepage-full-legend' => 'Pśełožujobny bok wulašowaś',
	'pt-deletepage-any-legend' => 'Přełožujobny bok abo pśełožowański bok wulašowaś',
	'pt-deletepage-current' => 'Mě boka:',
	'pt-deletepage-reason' => 'Pśicyna:',
	'pt-deletepage-subpages' => 'Wšykne pódboki lašowaś:',
	'pt-deletepage-list-pages' => 'Lisćina bokow, kótarež maju se wulašowaś',
	'pt-deletepage-list-translation' => 'Pśełožowańske boki',
	'pt-deletepage-list-section' => 'Boki pśełožowańskich jadnotkow',
	'pt-deletepage-list-other' => 'Druge pódboki',
	'pt-deletepage-list-count' => 'Dogromady {{PLURAL:$1|ma se $1 bok|matej se $1 boka|maju se $1 boki|ma se $1 bokow}} wulašowaś.',
	'pt-deletepage-full-logreason' => 'Źěl pśełožujobnego boka $1.',
	'pt-deletepage-lang-logreason' => 'Źěl pśełožonego boka "$1".',
	'pt-deletepage-started' => 'Pšosym pśekontrolěruj [[Special:Log/pagetranslation|pśełožowański protokol boka]] za zmólkami a zdźělenjami wuwjeźenja.',
	'pt-deletepage-intro' => 'Toś ten specialny bok śi zmóžnja, aby wulašował ceły pśełožujobne bok abo  jadnotliwy přełožowański bok w rěcy wulašował.
Lašowanje njestanjo se ned, dokulaž wšykne boki, kótarež k njomu słušaju,  muse se wulašowaś.
Zmólki budu se w  [[Special:Log/pagetranslation|protokolu pśełožkow]] protokolěrowaś a wóne muse se manuelnje pórěźiś.',
);

/** Ewe (eʋegbe)
 * @author Natsubee
 */
$messages['ee'] = array(
	'translate-tag-translate-link-desc' => 'Ɖe axa sia gɔme',
	'tpt-languages-legend' => 'Gbe bubuwo:',
);

/** Greek (Ελληνικά)
 * @author Crazymadlover
 * @author Dead3y3
 * @author Flyax
 * @author Lou
 * @author Protnet
 * @author ZaDiak
 */
$messages['el'] = array(
	'pagetranslation' => 'Μετάφραση σελίδων',
	'right-pagetranslation' => 'Σήμανση εκδόσεων σελίδων προς μετάφραση',
	'action-pagetranslation' => 'διαχειριστείτε σελίδες προς μετάφραση',
	'tpt-desc' => 'Επέκταση για μετάφραση σελίδων περιεχομένου',
	'tpt-section' => 'Μεταφραστική ενότητα $1',
	'tpt-section-new' => 'Νέα μεταφραστική ενότητα.
Όνομα: $1',
	'tpt-section-deleted' => 'Μεταφραστική ενότητα $1',
	'tpt-template' => 'Πρότυπο σελίδας',
	'tpt-templatediff' => 'Το πρότυπο σελίδας έχει αλλάξει.',
	'tpt-diff-old' => 'Προηγούμενο κείμενο',
	'tpt-diff-new' => 'Νέο κείμενο',
	'tpt-submit' => 'Σήμανση αυτής της έκδοσης για μετάφραση',
	'tpt-sections-oldnew' => 'Νέες και υπάρχουσες μεταφραστικές ενότητες',
	'tpt-sections-deleted' => 'Διαγεγραμμένες μεταφραστικές ενότητες',
	'tpt-sections-template' => 'Πρότυπο σελίδας μετάφρασης',
	'tpt-action-nofuzzy' => 'Να μην γίνει ακύρωση των μεταφράσεων',
	'tpt-badtitle' => 'Ο τίτλος σελίδας που δόθηκε ($1) δεν είναι έγκυρος τίτλος',
	'tpt-nosuchpage' => 'Η σελίδα $1 δεν υπάρχει',
	'tpt-oldrevision' => 'Το $2 δεν είναι η τελευταία έκδοση της σελίδας [[$1]].
Μόνο οι πιο πρόσφατες εκδόσεις μπορούν να επισημανθούν για μετάφραση.',
	'tpt-notsuitable' => 'Η σελίδα $1 δεν είναι κατάλληλη για μετάφραση.
Βεβαιωθείτε ότι έχει τις ετικέτες <nowiki><translate></nowiki> και έχει έγκυρη σύνταξη.',
	'tpt-saveok' => 'Η σελίδα [[$1]] έχει σημανθεί για μετάφραση με $2 {{PLURAL:$2|μεταφραστική ενότητα|μεταφραστικές ενότητες}}.
Η σελίδα μπορεί τώρα να <span class="plainlinks">[$3 μεταφραστεί]</span>.',
	'tpt-offer-notify' => 'Μπορείτε να <span class="plainlinks">[$1 στείλετε ειδοποίηση στους μεταφραστές]</span> για αυτήν τη σελίδα.',
	'tpt-badsect' => 'Το «$1» δεν είναι έγκυρο όνομα για τη μονάδα μετάφρασης $2.',
	'tpt-showpage-intro' => 'Παρακάτω παρατίθενται οι νέες, οι υφιστάμενες και οι διαγεγραμμένες μεταφραστικές ενότητες.
Προτού σημανθεί αυτή η έκδοση για μετάφραση, ελέγξτε ότι έχουν ελαχιστοποιηθεί οι αλλαγές στις μεταφραστικές ενότητες για την αποφυγή περιττής εργασίας από τους μεταφραστές.',
	'tpt-mark-summary' => 'Αυτή η έκδοση σημάνθηκε για μετάφραση',
	'tpt-edit-failed' => 'Δεν ήταν δυνατό να ενημερωθεί η σελίδα: $1',
	'tpt-duplicate' => 'Το όνομα μεταφραστικής ενότητας $1 χρησιμοποιείται περισσότερες από μία φορές.',
	'tpt-already-marked' => 'Η τελευταία έκδοση της σελίδας έχει ήδη σημανθεί προς μετάφραση.',
	'tpt-unmarked' => 'Η σελίδα $1 δεν έχει πλέον σήμανση για μετάφραση.',
	'tpt-list-nopages' => 'Δεν υπάρχουν σελίδες που να έχουν σημανθεί προς μετάφραση ή να είναι έτοιμες για σήμανση προς μετάφραση.',
	'tpt-new-pages-title' => 'Σελίδες που προτείνονται για μετάφραση',
	'tpt-old-pages-title' => 'Σελίδες υπό μετάφραση',
	'tpt-other-pages-title' => 'Προβληματικές σελίδες',
	'tpt-discouraged-pages-title' => 'Σελίδες στις οποίες αποθαρρύνεται η μετάφραση.',
	'tpt-new-pages' => '{{PLURAL:$1|Αυτή η σελίδα περιέχει|Αυτές οι σελίδες περιέχουν}} κείμενο με ετικέτες μετάφρασης,
αλλά καμία έκδοση {{PLURAL:$1|αυτής της σελίδας|αυτών των σελίδων}} δεν έχει επί του παρόντος σήμανση για μετάφραση.',
	'tpt-old-pages' => '{{PLURAL:$1|Κάποια έκδοση αυτής της σελίδας έχει|Κάποιες εκδόσεις αυτών των σελίδων έχουν}} σημανθεί για μετάφραση.',
	'tpt-other-pages' => '{{PLURAL:$1|Μια παλιά έκδοση αυτής της σελίδας έχει|Παλαιότερες εκδόσεις αυτών των σελίδες έχουν}} σημανθεί για μετάφραση,
αλλά η τελευταία {{PLURAL:$1|της|τους}} έκδοση δεν μπορεί να σημανθεί για μετάφραση.',
	'tpt-discouraged-pages' => 'Περαιτέρω μετάφραση {{PLURAL:$1|αυτής της σελίδας|αυτών των σελίδων}} έχει αποθαρρυνθεί.',
	'tpt-select-prioritylangs' => 'Λίστα χωρισμένη με κόμματα των κωδικών γλώσσας που έχουν προτεραιότητα:',
	'tpt-select-prioritylangs-force' => 'Να αποτρέπονται μεταφράσεις σε άλλες γλώσσες πέραν των γλωσσών που έχουν προτεραιότητα',
	'tpt-select-prioritylangs-reason' => 'Αιτία:',
	'tpt-sections-prioritylangs' => 'Γλώσσες που έχουν προτεραιότητα',
	'tpt-rev-mark' => 'σήμανση για μετάφραση',
	'tpt-rev-unmark' => 'αφαίρεση από τη μετάφραση',
	'tpt-rev-discourage' => 'αποθάρρυνση',
	'tpt-rev-encourage' => 'αποκατάσταση',
	'tpt-rev-mark-tooltip' => 'Σήμανση της τελευταίας έκδοσης αυτής της σελίδας για μετάφραση.',
	'tpt-rev-unmark-tooltip' => 'Αφαίρεση αυτής της σελίδας από τη μετάφραση.',
	'tpt-rev-discourage-tooltip' => 'Αποθάρρυνση περαιτέρω μεταφράσεων σε αυτή τη σελίδα.',
	'tpt-rev-encourage-tooltip' => 'Επαναφορά αυτής της σελίδας σε κανονική μετάφραση.',
	'translate-tag-translate-link-desc' => 'Μεταφράστε αυτήν τη σελίδα',
	'translate-tag-markthis' => 'Σήμανση αυτής της σελίδας για μετάφραση',
	'translate-tag-markthisagain' => 'Αυτή η σελίδα έχει <span class="plainlinks">[$1 αλλαγές]</span> από την τελευταία φορά που είχε <span class="plainlinks">[$2 σημανθεί για μετάφραση]</span>.',
	'translate-tag-hasnew' => 'Αυτή η σελίδα περιέχει <span class="plainlinks">[$1 αλλαγές]</span> που δεν έχουν σημανθεί για μετάφραση.',
	'tpt-translation-intro' => 'Αυτή η σελίδα είναι μια <span class="plainlinks">[$1 μεταφρασμένη έκδοση]</span> της σελίδας [[$2]] και η μετάφραση είναι $3% ολοκληρωμένη.',
	'tpt-languages-legend' => 'Άλλες γλώσσες:',
	'tpt-languages-zero' => 'Έναρξη μετάφρασης για αυτήν τη γλώσσα',
	'tpt-tab-translate' => 'Μετάφραση',
	'tpt-target-page' => 'Αυτή η σελίδα δεν μπορεί να ενημερωθεί με το χέρι.
Αυτή η σελίδα είναι μετάφραση της σελίδας [[$1]] και η μετάφραση μπορεί να ενημερωθεί χρησιμοποιώντας [$2 το εργαλείο μετάφρασης].',
	'tpt-unknown-page' => 'Αυτός ο ονοματοχώρος προορίζεται για μεταφράσεις σελίδων περιεχομένου.
Η σελίδα που προσπαθείτε να επεξεργαστείτε δεν φαίνεται να αντιστοιχεί σε σελίδα με σήμανση για μετάφραση.',
	'tpt-translation-restricted' => 'Η μετάφραση αυτής της σελίδας σε αυτήν τη γλώσσα έχει αποτραπεί από έναν διαχειριστή μετάφρασης.

Αιτιολογία: $1',
	'tpt-discouraged-language-force' => '«Αυτή η σελίδα δεν μπορεί να μεταφραστεί σε $2».

Ένας διαχειριστής μετάφρασης έχει επιλέξει για αυτήν τη σελίδα να μπορεί να μεταφραστεί μόνο σε $3.',
	'tpt-discouraged-language' => '«Η μετάφραση σε $2 δεν αποτελεί προτεραιότητα για αυτήν τη σελίδα».

Ένας διαχειριστής μετάφρασης έχει επιλέξει να επικεντρωθούν όλες οι μεταφραστικές προσπάθειες στα $3.',
	'tpt-discouraged-language-reason' => 'Αιτία: $1',
	'tpt-priority-languages' => 'Ένας διαχειριστής μετάφρασης έχει ορίσει ως γλώσσες που έχουν προτεραιότητα τα $1 για αυτήν την ομάδα.',
	'tpt-render-summary' => 'Γίνεται ενημέρωση για να αντιστοιχεί στη νέα έκδοση της πηγαίας σελίδας',
	'tpt-download-page' => 'Εξαγωγή της σελίδας με τις μεταφράσεις',
	'aggregategroups' => 'Συγκεντρωτικές ομάδες',
	'tpt-aggregategroup-add' => 'Προσθήκη',
	'tpt-aggregategroup-save' => 'Αποθήκευση',
	'tpt-aggregategroup-add-new' => 'Προσθήκη νέας συγκεντρωτικής ομάδας',
	'tpt-aggregategroup-new-name' => 'Όνομα:',
	'tpt-aggregategroup-new-description' => 'Περιγραφή (προαιρετική):',
	'tpt-aggregategroup-remove-confirm' => 'Είστε σίγουροι ότι θέλετε να διαγράψετε αυτήν τη συγκεντρωτική ομάδα;',
	'tpt-aggregategroup-invalid-group' => 'Η ομάδα δεν υπάρχει',
	'pt-parse-open' => 'Ορφανή ετικέτα &lt;translate>.
Πρότυπο μετάφρασης: <pre>$1</pre>',
	'pt-parse-close' => 'Ορφανή ετικέτα &lt;/translate>.
Πρότυπο μετάφρασης: <pre>$1</pre>',
	'pt-parse-nested' => 'Δεν επιτρέπονται εμφωλευμένες μεταφραστικές ενότητες &lt;translate>.
Κείμενο ετικέτας: <pre>$1</pre>',
	'pt-shake-multiple' => 'Πολλαπλοί δείκτες μεταφραστικών ενοτήτων για μία μεταφραστική ενότητα.
Κείμενο μεταφραστικής ενότητας: <pre>$1</pre>',
	'pt-shake-position' => 'Δείκτες μεταφραστικών ενοτήτων σε μη αναμενόμενη θέση.
Κείμενο μεταφραστικής ενότητας: <pre>$1</pre>',
	'pt-shake-empty' => 'Κενή μεταφραστική ενότητα για το δείκτη «$1».',
	'log-description-pagetranslation' => 'Αρχείο καταγραφής για ενέργειες που σχετίζονται με το σύστημα μετάφρασης',
	'log-name-pagetranslation' => 'Καταγραφή μετάφρασης σελίδων',
	'logentry-pagetranslation-mark' => '{{GENDER:$2|Ο|Η}} $1 σήμανε τη σελίδα $3 για μετάφραση',
	'logentry-pagetranslation-unmark' => '{{GENDER:$2|Ο|Η}} $1 αφαίρεσε τη σελίδα $3 από τη μετάφραση',
	'logentry-pagetranslation-moveok' => '{{GENDER:$2|Ο|Η}} $1 ολοκλήρωσε τη μετονομασία της προς μετάφραση σελίδας $3 σε $4',
	'logentry-pagetranslation-movenok' => '{{GENDER:$2|Ο|Η}} $1 αντιμετώπισε ένα πρόβλημα κατά τη μετονομασία της σελίδας $3 σε $4',
	'logentry-pagetranslation-deletefok' => '{{GENDER:$2|Ο|Η}} $1 ολοκλήρωσε τη διαγραφή της προς μετάφραση σελίδας $3',
	'logentry-pagetranslation-deletefnok' => '{{GENDER:$2|Ο|Η}} $1 απέτυχε να διαγράψει τη σελίδα $3 που ανήκει στην προς μετάφραση σελίδα $4',
	'logentry-pagetranslation-deletelok' => '{{GENDER:$2|Ο|Η}} $1 ολοκλήρωσε τη διαγραφή της σελίδας μετάφρασης $3',
	'logentry-pagetranslation-deletelnok' => '{{GENDER:$2|Ο|Η}} $1 απέτυχε να διαγράψει τη σελίδα $3 που ανήκει στη σελίδα μετάφρασης $4',
	'logentry-pagetranslation-encourage' => '{{GENDER:$2|Ο|Η}} $1 ενθάρρυνε τη μετάφραση της σελίδας $3',
	'logentry-pagetranslation-discourage' => '{{GENDER:$2|Ο|Η}} $1 αποθάρρυνε τη μετάφραση της σελίδας $3',
	'logentry-pagetranslation-prioritylanguages-unset' => '{{GENDER:$2|Ο|Η}} $1 αφαίρεσε γλώσσες που έχουν προτεραιότητα από την προς μετάφραση σελίδα $3',
	'logentry-pagetranslation-prioritylanguages' => '{{GENDER:$2|Ο|Η}} $1 έθεσε ως γλώσσες που έχουν προτεραιότητα για την προς μετάφραση σελίδα $3 τα $5',
	'logentry-pagetranslation-prioritylanguages-force' => '{{GENDER:$2|Ο|Η}} $1 περιόρισε τις γλώσσες για την προς μετάφραση σελίδα $3 στα $5',
	'logentry-pagetranslation-associate' => '{{GENDER:$2|Ο|Η}} $1 προσέθεσε την προς μετάφραση σελίδα $3 στη συγκεντρωτική ομάδα $4',
	'logentry-pagetranslation-dissociate' => '{{GENDER:$2|Ο|Η}} $1 αφαίρεσε την προς μετάφραση σελίδα $3 από τη συγκεντρωτική ομάδα $4',
	'pt-movepage-title' => 'Μετακίνηση της προς μετάφραση σελίδας «$1»',
	'pt-movepage-blockers' => 'Η προς μετάφραση σελίδα δεν μπορεί να μετακινηθεί σε νέο όνομα λόγω {{PLURAL:$1|του ακόλουθου σφάλματος|των ακόλουθων σφαλμάτων}}:',
	'pt-movepage-block-base-exists' => 'Η προς μετάφραση σελίδα προορισμού «[[:$1]]» υπάρχει.',
	'pt-movepage-block-base-invalid' => 'Το όνομα της προς μετάφρασης σελίδας προορισμού δεν είναι έγκυρος τίτλος.',
	'pt-movepage-block-tp-exists' => 'Η προς μετάφραση σελίδα προορισμού «[[:$2]]» υπάρχει.',
	'pt-movepage-block-tp-invalid' => 'Ο τίτλος της προς μετάφρασης σελίδας προορισμού «[[:$1]]» δεν είναι έγκυρος (πολύ μεγάλος;).',
	'pt-movepage-block-section-exists' => 'Η σελίδα προορισμού «[[:$2]]» για τη μεταφραστική ενότητα υπάρχει.',
	'pt-movepage-block-section-invalid' => 'Ο τίτλος της σελίδας προορισμού «[[:$1]]» για τη μεταφραστική ενότητα δεν είναι έγκυρος (πολύ μεγάλος;).',
	'pt-movepage-block-subpage-exists' => 'Η υποσελίδα προορισμού «[[:$2]]» υπάρχει.',
	'pt-movepage-block-subpage-invalid' => 'Ο τίτλος της υποσελίδας προορισμού «[[:$1]]» δεν είναι έγκυρος (πολύ μεγάλος;).',
	'pt-movepage-list-pages' => 'Κατάλογος σελίδων προς μετακίνηση',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Σελίδα|Σελίδες}} μετάφρασης',
	'pt-movepage-list-section' => '{{PLURAL:$1|Σελίδα μεταφραστικής ενότητας|Σελίδες μεταφραστικών ενοτήτων}}',
	'pt-movepage-list-other' => '{{PLURAL:$1|Άλλη υποσελίδα|Άλλες υποσελίδες}}',
	'pt-movepage-list-count' => 'Συνολικά $1 {{PLURAL:$1|σελίδα|σελίδες}} προς μετακίνηση.',
	'pt-movepage-legend' => 'Μετακίνηση προς μετάφραση σελίδας',
	'pt-movepage-current' => 'Τρέχον όνομα:',
	'pt-movepage-new' => 'Νέο όνομα:',
	'pt-movepage-reason' => 'Αιτία:',
	'pt-movepage-subpages' => 'Μετακίνηση όλων των υποσελίδων',
	'pt-movepage-action-check' => 'Έλεγχος αν η μετακίνηση είναι εφικτή',
	'pt-movepage-action-perform' => 'Εκτέλεση μετακίνησης',
	'pt-movepage-action-other' => 'Αλλαγή προορισμού',
	'pt-movepage-intro' => 'Αυτή η ειδική σελίδα σας επιτρέπει να μετακινήσετε σελίδες που έχουν σημανθεί για μετάφραση.
Η ενέργεια μετακίνησης δεν θα είναι άμεση, επειδή θα χρειαστεί να μετακινηθούν πολλές σελίδες.
Κατά τη διάρκεια της μετακίνησης, δεν είναι δυνατή η αλληλεπίδραση με τις εν λόγω σελίδες.
Οι αποτυχίες θα καταγραφούν στο [[Special:Log/pagetranslation|αρχείο καταγραφής των σελίδων μετάφρασης]] και θα πρέπει να επιδιορθωθούν με το χέρι.',
	'pt-movepage-logreason' => 'Τμήμα της προς μετάφραση σελίδας «$1».',
	'pt-movepage-started' => 'Η σελίδα βάσης έχει τώρα μετακινηθεί.
Παρακαλούμε ελέγξτε τη [[Special:Log/pagetranslation|σελίδα καταγραφών των σελίδων μετάφρασης]] για σφάλματα και μήνυμα ολοκλήρωσης.',
	'pt-locked-page' => 'Αυτή η σελίδα είναι κλειδωμένη επειδή η προς μετάφραση σελίδα βρίσκεται αυτή τη στιγμή υπό μετακίνηση.',
	'pt-deletepage-lang-title' => 'Γίνεται διαγραφή της σελίδας μετάφρασης «$1».',
	'pt-deletepage-full-title' => 'Γίνεται διαγραφή της προς μετάφραση σελίδας«$1».',
	'pt-deletepage-invalid-title' => 'Η καθορισμένη σελίδα δεν είναι έγκυρη.',
	'pt-deletepage-invalid-text' => 'Η καθορισμένη σελίδα δεν είναι ούτε σελίδα προς μετάφραση ούτε σελίδα μετάφρασης.',
	'pt-deletepage-action-check' => 'Λίστα με σελίδες για διαγραφή',
	'pt-deletepage-action-perform' => 'Εκτέλεση διαγραφής',
	'pt-deletepage-action-other' => 'Αλλαγή προορισμού',
	'pt-deletepage-lang-legend' => 'Διαγραφή σελίδας μετάφρασης',
	'pt-deletepage-full-legend' => 'Διαγραφή προς μετάφραση σελίδας',
	'pt-deletepage-any-legend' => 'Διαγραφή προς μετάφραση σελίδας ή σελίδας μετάφρασης',
	'pt-deletepage-current' => 'Όνομα σελίδας:',
	'pt-deletepage-reason' => 'Αιτία:',
	'pt-deletepage-subpages' => 'Διαγραφή όλων των υποσελίδων',
	'pt-deletepage-list-pages' => 'Κατάλογος σελίδων προς διαγραφή',
	'pt-deletepage-list-translation' => 'Σελίδες μετάφρασης',
	'pt-deletepage-list-section' => 'Σελίδες μεταφραστικών ενοτήτων',
	'pt-deletepage-list-other' => 'Άλλες υποσελίδες',
	'pt-deletepage-list-count' => 'Συνολικά $1 {{PLURAL:$1|σελίδα|σελίδες}} προς διαγραφή.',
	'pt-deletepage-full-logreason' => 'Τμήμα της προς μετάφραση σελίδας «$1».',
	'pt-deletepage-lang-logreason' => 'Τμήμα της σελίδας μετάφρασης «$1».',
	'pt-deletepage-started' => 'Παρακαλούμε ελέγξτε το [[Special:Log/pagetranslation|αρχείο καταγραφών των σελίδων μετάφρασης]] για σφάλματα και μήνυμα ολοκλήρωσης.',
	'pt-deletepage-intro' => 'Αυτή η ειδική σελίδα σας επιτρέπει να διαγράψετε είτε ολόκληρη σελίδα προς μετάφραση, είτε μια μεμονωμένη σελίδα μετάφρασης σε κάποια γλώσσα.
Η ενέργεια διαγραφής δεν θα είναι άμεση, επειδή θα διαγραφούν επίσης και όλες οι σελίδες που εξαρτώνται από αυτές.
Οι αποτυχίες θα καταγραφούν στο [[Special:Log/pagetranslation|αρχείο καταγραφών των σελίδων μετάφρασης]] και θα πρέπει να επιδιορθωθούν με το χέρι.',
);

/** British English (British English)
 * @author Shirayuki
 * @author Thehelpfulone
 */
$messages['en-gb'] = array(
	'tpt-showpage-intro' => 'Below new, existing and deleted translation units are listed.
Before marking this version for translation, check that the changes to translation units are minimised to avoid unnecessary work for translators.',
	'logentry-pagetranslation-moveok' => '$1 {{GENDER:$2|completed}} renaming of translatable page $3 to $4',
	'logentry-pagetranslation-deletefnok' => '$1 {{GENDER:$2|failed}} to delete $3 which belongs to translatable page $4',
	'logentry-pagetranslation-prioritylanguages' => '$1 {{GENDER:$2|set}} the priority languages for translatable page $3 to $5',
);

/** Esperanto (Esperanto)
 * @author Anakmalaysia
 * @author ArnoLagrange
 * @author Blahma
 * @author Yekrats
 */
$messages['eo'] = array(
	'pagetranslation' => 'Paĝa traduko',
	'right-pagetranslation' => 'Marki versiojn de paĝoj por traduki',
	'tpt-desc' => 'Kromprogramo por tradukado de enhavaj paĝoj',
	'tpt-section' => 'Tradukada unuo $1',
	'tpt-section-new' => 'Nova tradukada unuo.
Nomo: $1',
	'tpt-section-deleted' => 'Tradukada unuo $1',
	'tpt-template' => 'Paĝa ŝablono',
	'tpt-templatediff' => 'La paĝa ŝablono estis ŝanĝita.',
	'tpt-diff-old' => 'Antaŭa teksto',
	'tpt-diff-new' => 'Nova teksto',
	'tpt-submit' => 'Marki ĉi tiun version por traduki',
	'tpt-sections-oldnew' => 'Novaj kaj ekzistantaj tradukaĵoj',
	'tpt-sections-deleted' => 'Forigitaj tradukadaj unuoj',
	'tpt-sections-template' => 'Ŝablono por tradukada paĝo',
	'tpt-action-nofuzzy' => 'Ne malvalidigu tradukojn.',
	'tpt-badtitle' => 'La provizita paĝnomo ($1) ne estas valida titolo',
	'tpt-nosuchpage' => 'La paĝo $1 ne ekzistas.',
	'tpt-oldrevision' => '$2 ne estas la lasta versio de la paĝo [[$1]].
Nur la lasta versio de la paĝo povas esti markita por esti tradukita.',
	'tpt-notsuitable' => 'Paĝo $1 ne taŭgas por traduki.
Certigu ke ĝi havas etikedojn <nowiki><translate></nowiki> kaj havas validan sintakson.',
	'tpt-saveok' => 'La paĝo [[$1]] estis markita por esti tradukita kun $2 traduk{{PLURAL:$2|ero|eroj}}.
La paĝo povas nun esti <span class="plainlinks">[$3 tradukita]</span>.',
	'tpt-badsect' => '« $1 » ne estas valida nomo por tradukero $2.',
	'tpt-mark-summary' => 'Markis ĉi tiun version por traduki.',
	'tpt-edit-failed' => 'Ne eblis ĝisdatigi la paĝon: $1',
	'tpt-old-pages-title' => 'Paĝoj en traduko',
	'tpt-other-pages-title' => 'Rompitaj paĝoj',
	'tpt-discouraged-pages-title' => 'Malinstigitaj paĝoj',
	'tpt-select-prioritylangs-reason' => 'Kialo:',
	'tpt-sections-prioritylangs' => 'Primadaj lingvoj',
	'tpt-rev-mark' => 'marki por traduki',
	'tpt-rev-unmark' => 'forigi el traduko',
	'tpt-rev-discourage' => 'malinstigi',
	'tpt-rev-encourage' => 'restarigi',
	'translate-tag-translate-link-desc' => 'Traduki ĉi tiun paĝon',
	'translate-tag-markthis' => 'Marki ĉi tiun paĝon por tradukado',
	'tpt-languages-legend' => 'Aliaj lingvoj:',
	'tpt-languages-zero' => 'Ektraduki por ĉi tiu lingvo',
	'tpt-discouraged-language-reason' => 'Kialo: $1',
	'tpt-download-page' => 'Eksporti paĝon kun tradukoj',
	'tpt-aggregategroup-add' => 'Aldoni',
	'tpt-aggregategroup-save' => 'Konservi',
	'tpt-aggregategroup-new-name' => 'Nomo:',
	'tpt-aggregategroup-new-description' => 'Priskribo (nedevige):',
	'tpt-aggregategroup-invalid-group' => 'La grupo ne ekzistas',
	'log-name-pagetranslation' => 'Protokolo pri paĝaj tradukoj',
	'pt-movepage-title' => 'Movi la tradukeblan paĝon "$1"',
	'pt-movepage-blockers' => 'La tradukebla paĝo ne povis esti movita al nova nomo pro la {{PLURAL:$1|sekva eraro|sekvaj eraroj}}:',
	'pt-movepage-block-base-exists' => 'La cela tradukebla paĝo "[[:$1]]" ekzistas.',
	'pt-movepage-block-base-invalid' => 'Nomo de la cela tradukebla paĝo ne estas valida titolo.',
	'pt-movepage-block-tp-exists' => 'La cela tradukpaĝo "[[:$2]]" ekzistas.',
	'pt-movepage-block-tp-invalid' => 'Titolo de la cela tradukpaĝo por "[[:$1]]" estus malvalida (tro longa?).',
	'pt-movepage-block-section-exists' => 'La celpaĝo "[[:$2]]" de la traduka unuo ekzistas.',
	'pt-movepage-block-section-invalid' => 'Nomo de la celpaĝo por "[[:$1]]" de la traduka unuo estus malvalida (tro longa?).',
	'pt-movepage-block-subpage-exists' => 'La cela subpaĝo "[[:$2]]" ekzistas.',
	'pt-movepage-block-subpage-invalid' => 'Nomo de la cela subpaĝo por "[[:$1]]" estus malvalida (tro longa?).',
	'pt-movepage-list-pages' => 'Listo de movotaj paĝoj',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Traduka paĝo|Tradukaj paĝoj}}',
	'pt-movepage-list-section' => '{{PLURAL:$1|Paĝo|Paĝoj}} de tradukaj unuoj',
	'pt-movepage-list-other' => '{{PLURAL:$1|Alia subpaĝo|Aliaj subpaĝoj}}',
	'pt-movepage-list-count' => 'Entute $1 {{PLURAL:$1|paĝo|paĝoj}} por movi.',
	'pt-movepage-legend' => 'Movi tradukeblan paĝon',
	'pt-movepage-current' => 'Nuna nomo:',
	'pt-movepage-new' => 'Nova nomo:',
	'pt-movepage-reason' => 'Kialo:',
	'pt-movepage-subpages' => 'Movi ĉiujn subpaĝojn',
	'pt-movepage-action-check' => 'Kontroli ĉu la movo fareblas',
	'pt-movepage-action-perform' => 'Fari la movon',
	'pt-movepage-action-other' => 'Ŝanĝi celon',
	'pt-movepage-intro' => 'Tiu ĉi speciala paĝo permesas al vi movi paĝojn markitajn por traduko.
La movo ne efektiviĝos tuj, ĉar necesos movi multajn paĝojn.
Dum paĝoj estas movataj, ne eblas pri ili labori.
Eventualaj fiaskoj estos protokolitaj en [[Special:Log/pagetranslation|protokolo pri paĝotradukado]] kaj ilin necesos ripari permane.',
	'pt-movepage-logreason' => 'Parto de tradukebla paĝo "$1".',
	'pt-movepage-started' => 'La baza paĝo nun estas movita.
Bonvolu kontroli la [[Special:Log/pagetranslation|protokolon pri paĝotradukado]] por eraroj kaj mesaĝo pri kompletiĝo.',
	'pt-locked-page' => 'Tiu ĉi paĝo estas ŝlosita ĉar la tradukebla paĝo nuntempe estas movata.',
	'pt-deletepage-lang-title' => 'Foriganta la tradukpaĝon "$1".',
	'pt-deletepage-full-title' => 'Foriganta la tradukeblan paĝon "$1".',
	'pt-deletepage-invalid-title' => 'La specifita paĝo ne estas valida.',
	'pt-deletepage-invalid-text' => 'La specifita paĝo ne estas tradukebla paĝo nek tradukpaĝo.',
	'pt-deletepage-action-check' => 'Listigi forigotajn paĝojn',
	'pt-deletepage-action-perform' => 'Fari la forigon',
	'pt-deletepage-action-other' => 'Ŝanĝi celon:',
	'pt-deletepage-lang-legend' => 'Forigi tradukpaĝon',
	'pt-deletepage-full-legend' => 'Forigi tradukeblan paĝon',
	'pt-deletepage-any-legend' => 'Forigi tradukeblan paĝon aŭ tradukpaĝon',
	'pt-deletepage-current' => 'Nomo de paĝo:',
	'pt-deletepage-reason' => 'Kialo:',
	'pt-deletepage-subpages' => 'Forigi ĉiujn subpaĝojn',
	'pt-deletepage-list-pages' => 'Listo de forigotaj paĝoj',
	'pt-deletepage-list-translation' => 'Tradukpaĝoj',
	'pt-deletepage-list-section' => 'Paĝoj de tradukada unuo',
	'pt-deletepage-list-other' => 'Aliaj subpaĝoj',
	'pt-deletepage-list-count' => 'Entute $1 {{PLURAL:$1|paĝo|paĝoj}} por forigi.',
	'pt-deletepage-full-logreason' => 'Pato de la tradukebla paĝo "$1".',
	'pt-deletepage-lang-logreason' => 'Pato de la tradukpaĝo "$1".',
	'pt-deletepage-started' => 'Bonvolu rekontroli la [[Special:Log/pagetranslation|protokolon pri paĝotradukado]] por eraroj kaj mesaĝo pri kompletiĝo.',
	'pt-deletepage-intro' => 'En tiu ĉi speciala paĝo vi povas forigi tutan tradukeblan paĝon aŭ individuan tradukpaĝon en iu lingvo.
La forigo ne efektiviĝos tuj, ĉar ĉiuj dependaj paĝoj estos ankaŭ forigitaj.
Fiaskoj estos protokolitaj en la [[Special:Log/pagetranslation|protokolo pri paĝotradukado]] kaj ilin necesos ripari permane.',
);

/** Spanish (español)
 * @author Antur
 * @author Armando-Martin
 * @author Crazymadlover
 * @author Dalton2
 * @author Dferg
 * @author Diego Grez
 * @author Imre
 * @author MarcoAurelio
 * @author McDutchie
 * @author Mor
 * @author Purodha
 * @author Sanbec
 * @author Translationista
 * @author Vivaelcelta
 */
$messages['es'] = array(
	'pagetranslation' => 'Traducción de página',
	'right-pagetranslation' => 'Marcar versiones de páginas para traducción',
	'action-pagetranslation' => 'administrar páginas traducibles',
	'tpt-desc' => 'Extensiones para traducir páginas de contenido',
	'tpt-section' => 'Unidad de traducción $1',
	'tpt-section-new' => 'Nueva unidad de traducción. 
Nombre: $1',
	'tpt-section-deleted' => 'Unidad de traducción $1',
	'tpt-template' => 'Plantilla de página',
	'tpt-templatediff' => 'La plantilla de página ha cambiado.',
	'tpt-diff-old' => 'Texto previo',
	'tpt-diff-new' => 'Nuevo texto',
	'tpt-submit' => 'Marcar esta versión para traducción',
	'tpt-sections-oldnew' => 'Unidades de traducción nuevas y existentes',
	'tpt-sections-deleted' => 'Unidades de traducción borradas',
	'tpt-sections-template' => 'Plantilla de página de traducción',
	'tpt-action-nofuzzy' => 'No invalidar traducciones',
	'tpt-badtitle' => 'El nombre de página dado ($1) no es un título válido',
	'tpt-nosuchpage' => 'Página $1 no existe',
	'tpt-oldrevision' => '$2 no es la última versión de la página [[$1]].
Solamente las últimas versiones pueden ser marcadas para traducción',
	'tpt-notsuitable' => 'La página $1 no es adecuada para traducción.
Asegúrate que tiene etiquetas <nowiki><translate></nowiki> y tiene una sintaxis válida.',
	'tpt-saveok' => 'La página [[$1]] ha sido marcada para traducción con $2 {{PLURAL:$2|unidad de traducción |unidades de traducción}}.
La página puede ser ahora <span class="plainlinks">[$3 traducida]</span>.',
	'tpt-badsect' => '"$1" no es un nombre válido para una unidad de traducción $2.',
	'tpt-showpage-intro' => 'Debajo están listadas las unidades de traducción nuevas, existentes y borradas.
Antes de marcar esta versión para traducción, verifica que los cambios a las unidades de traducción son mínimos para evitar trabajo innecesario a los traductores.',
	'tpt-mark-summary' => 'Marcada esta sección para traducción',
	'tpt-edit-failed' => 'No pudo actualizar la página : $1',
	'tpt-duplicate' => 'El nombre de la unidad de traducción  $1 es utilizado más de una vez.',
	'tpt-already-marked' => 'La última versión de esta página ya ha sido marcada para traducción.',
	'tpt-unmarked' => 'Página $1 ya no está marcada para traducción.',
	'tpt-list-nopages' => 'Ninguna página está marcada para traducción ni lista para ser marcada para traducción.',
	'tpt-new-pages-title' => 'Páginas propuestas para traducción',
	'tpt-old-pages-title' => 'Páginas en curso de traducción',
	'tpt-other-pages-title' => 'Páginas rotas',
	'tpt-discouraged-pages-title' => 'Páginas desaconsejadas',
	'tpt-new-pages' => '{{PLURAL:$1|Esta página contiene|Estas páginas contienen}} texto con etiquetas de traducción, pero ninguna versión de {{PLURAL:$1|esta página est|estas páginas están}} actualmente marcadas para traducción.',
	'tpt-old-pages' => 'Alguna versión de {{PLURAL:$1|esta página|estas páginas han}} sido marcadas para traducción.',
	'tpt-other-pages' => 'Versión antigua de {{PLURAL:$1|esta página está|estas páginas están}} marcadas para traducción,
pero la última versión no puede ser marcada para traducción.',
	'tpt-discouraged-pages' => 'Se desaconseja la traducción adicional de {{PLURAL:$1|esta página|estas páginas}}.',
	'tpt-select-prioritylangs' => 'Lista de códigos de idioma prioritarios separados por comas:',
	'tpt-select-prioritylangs-force' => 'Impedir las traducciones a otros idiomas distintos de los prioritarios',
	'tpt-select-prioritylangs-reason' => 'Motivo:',
	'tpt-sections-prioritylangs' => 'Idiomas prioritarios',
	'tpt-rev-mark' => 'marcar para traducción',
	'tpt-rev-unmark' => 'eliminar de la traducción',
	'tpt-rev-discourage' => 'desaconsejar',
	'tpt-rev-encourage' => 'restaurar',
	'tpt-rev-mark-tooltip' => 'Marcar la última versión de esta página para traducción.',
	'tpt-rev-unmark-tooltip' => 'Eliminar esta página de la traducción',
	'tpt-rev-discourage-tooltip' => 'Desaconsejar nuevas traducciones de esta página.',
	'tpt-rev-encourage-tooltip' => 'Restaurar esta página para traducción normal.',
	'translate-tag-translate-link-desc' => 'Traducir esta página',
	'translate-tag-markthis' => 'Marcar esta página para traducción',
	'translate-tag-markthisagain' => 'Esta página tiene <span class="plainlinks">[$1 cambios]</span> desde la última vez que fue <span class="plainlinks">[$2 marcada para traducción]</span>.',
	'translate-tag-hasnew' => 'Esta página contiene <span class="plainlinks">[$1 cambios]</span> los cuales no han sido marcados para traducción.',
	'tpt-translation-intro' => 'Esta página es una <span class="plainlinks">[$1 versión traducida]</span> de la página [[$2]]. La traducción está completa al $3%.',
	'tpt-languages-legend' => 'Otros idiomas:',
	'tpt-languages-zero' => 'Iniciar la traducción para este idioma',
	'tpt-target-page' => 'Esta página no puede ser actualizada manualmente.
Esta página es una traducción de la página [[$1]] y la traducción puede ser actualizada usando [$2 la herramienta de traducción].',
	'tpt-unknown-page' => 'Este espacio de nombre está reservado para traducciones de páginas de contenido.
La página que estás tratando de editar no parece corresponder con alguna página marcada para traducción.',
	'tpt-translation-restricted' => 'La traducción de esta página a este idioma ha sido impedida por un administrador de traducción.

Motivo de la restricción: $1',
	'tpt-discouraged-language-force' => 'Un administrador de traducción ha limitado los idiomas a los que esta página puede ser traducida. Este idioma no está entre ellos.

Razón: $1',
	'tpt-discouraged-language' => 'Este idioma no está entre los idiomas prioritarios establecidos por un administrador de traducción para esta página.

Razón: $1',
	'tpt-discouraged-language-reason' => 'Motivo: $1',
	'tpt-priority-languages' => 'Un administrador de traducciones ha definido los idiomas prioritarios de este grupo: $1.',
	'tpt-render-summary' => 'Actualizando para hallar una nueva versión de la página fuente',
	'tpt-download-page' => 'Exportar página con traducciones',
	'aggregategroups' => 'Grupos de agregación',
	'tpt-aggregategroup-add' => 'Añadir',
	'tpt-aggregategroup-save' => 'Guardar',
	'tpt-aggregategroup-add-new' => 'Añadir un nuevo grupo de agregación',
	'tpt-aggregategroup-new-name' => 'Nombre:',
	'tpt-aggregategroup-new-description' => 'Descripción (opcional):',
	'tpt-aggregategroup-remove-confirm' => '¿Está seguro que desea eliminar este grupo agregado?',
	'tpt-aggregategroup-invalid-group' => 'El grupo no existe',
	'pt-parse-open' => 'Etiqueta &lt;translate> desequilibrada.
Plantilla de traducción: <pre>$1</pre>',
	'pt-parse-close' => 'Etiqueta &lt;/translate> desequilibrada.
Plantilla de traducción: <pre>$1</pre>',
	'pt-parse-nested' => 'No se permiten &lt;translate> unidades de traducción anidadas.
Texto de etiqueta: <pre>$1</pre>',
	'pt-shake-multiple' => 'Múltiples marcadores de unidades de traducción para una unidad de traducción.
Texto de unidad de traducción: <pre>$1</pre>',
	'pt-shake-position' => 'Marcadores de unidad de traducción en posición inesperada.
Texto de unidad de traducción: <pre>$1</pre>',
	'pt-shake-empty' => 'Unidad de traducción vacía para el marcador $1.',
	'log-description-pagetranslation' => 'Registro para acciones relacionadas al sistema de traducción de página',
	'log-name-pagetranslation' => 'Registro de traducción de página',
	'pt-movepage-title' => 'Trasladar la página traducible $1',
	'pt-movepage-blockers' => 'La página traducible no puede ser movida a un nuevo nombre por los siguientes {{PLURAL:$1|error|errores}}:',
	'pt-movepage-block-base-exists' => 'Existe la página traducible de destino "[[:$1]]".',
	'pt-movepage-block-base-invalid' => 'El nombre de la página traducible de destino no es un título válido.',
	'pt-movepage-block-tp-exists' => 'La página de traducción de destino [[:$2]] existe.',
	'pt-movepage-block-tp-invalid' => 'El título de la página de traducción de destino para [[:$1]] sería inválido (demasiado largo?).',
	'pt-movepage-block-section-exists' => 'La unidad de traducción de la página de destino [[:$2]] existe.',
	'pt-movepage-block-section-invalid' => 'El título de unidad de traducción de la página de destino "[[:$1]]" sería inválido (¿demasiado largo?).',
	'pt-movepage-block-subpage-exists' => 'La subpágina de destino [[:$2]] existe.',
	'pt-movepage-block-subpage-invalid' => 'El título de subpágina de destino para [[:$1]] sería inválido (demasiado largo?).',
	'pt-movepage-list-pages' => 'Lista de páginas a trasladar',
	'pt-movepage-list-translation' => '{{PLURAL:$1|página|páginas}} de traducción',
	'pt-movepage-list-section' => '{{PLURAL:$1|página de unidad|páginas de unidades}} de traducción',
	'pt-movepage-list-other' => '{{PLURAL:$1|Otra subpágina|Otras subpáginas}}',
	'pt-movepage-list-count' => 'En total, $1 {{PLURAL:$1|página|páginas}} a trasladar.',
	'pt-movepage-legend' => 'Trasladar página traducible',
	'pt-movepage-current' => 'Nombre actual:',
	'pt-movepage-new' => 'Nuevo nombre:',
	'pt-movepage-reason' => 'Razón:',
	'pt-movepage-subpages' => 'Trasladar todas las subpáginas',
	'pt-movepage-action-check' => 'Verificar si el movimiento es posible',
	'pt-movepage-action-perform' => 'Hacer el movimiento',
	'pt-movepage-action-other' => 'Cambiar destino',
	'pt-movepage-intro' => 'Esta página especial permite trasladar páginas que están marcadas para su traducción.
La acción de traslado no será instantánea, porque necesitarán trasladarse muchas páginas.
Mientras las páginas estén siendo trasladadas, no es posible interactuar con las páginas en cuestión.
Los fallos serán registrados en el [[Special:Log/pagetranslation|registro de traducción de páginas]] y necesitarán ser reparados manualmente.',
	'pt-movepage-logreason' => 'Parte de la página traducible $1.',
	'pt-movepage-started' => 'La página base se ha trasladado.
Por favor verifica el [[Special:Log/pagetranslation|registro de traducción de página]] para errores y mensaje de conclusión.',
	'pt-locked-page' => 'Esta página está bloqueada porque la página traducible está siendo trasladada actualmente.',
	'pt-deletepage-lang-title' => 'Eliminar la página de traducción $1.',
	'pt-deletepage-full-title' => 'Eliminar la página traducible $1.',
	'pt-deletepage-invalid-title' => 'La página especificada no es válida.',
	'pt-deletepage-invalid-text' => 'La página especificada no es una página traducible ni una página de traducción.',
	'pt-deletepage-action-check' => 'Lista de páginas a borrar',
	'pt-deletepage-action-perform' => 'Realizar la eliminación',
	'pt-deletepage-action-other' => 'Cambiar el destino',
	'pt-deletepage-lang-legend' => 'Eliminar la página de traducción',
	'pt-deletepage-full-legend' => 'Eliminar la página traducible',
	'pt-deletepage-any-legend' => 'Eliminar la página traducible o la traducción de la página',
	'pt-deletepage-current' => 'Nombre de la página:',
	'pt-deletepage-reason' => 'Razón:',
	'pt-deletepage-subpages' => 'Eliminar todas las subpáginas',
	'pt-deletepage-list-pages' => 'Lista de páginas para eliminar',
	'pt-deletepage-list-translation' => 'Páginas de traducción',
	'pt-deletepage-list-section' => 'Páginas de unidades de traducción',
	'pt-deletepage-list-other' => 'Otras subpáginas',
	'pt-deletepage-list-count' => 'En total $1 {{PLURAL:$1|página|páginas}} a eliminar.',
	'pt-deletepage-full-logreason' => 'Parte de la página traducible $1.',
	'pt-deletepage-lang-logreason' => 'Parte de la página de traducción $1.',
	'pt-deletepage-started' => 'Compruebe los errores y los mensajes de conclusión en el [[Special:Log/pagetranslation|registro de traducción de páginas]].',
	'pt-deletepage-intro' => 'Esta página especial te permite eliminar páginas traducibles enteras o una página de traducción individual a un idioma. La eliminación no será instantánea, porque deben borrarse todas las páginas dependientes de ella. 
Los fallos se registrarán en el [[Special:Log/pagetranslation|registro de traducción de la página]] y tendrán que ser reparados a mano.',
);

/** Estonian (eesti)
 * @author Avjoska
 * @author Ker
 * @author Pikne
 */
$messages['et'] = array(
	'pagetranslation' => 'Lehekülje tõlkimine',
	'right-pagetranslation' => 'Märkida lehekülje versioone tõlkimiseks',
	'tpt-desc' => 'Sisulehekülgede tõlkimise lisa',
	'tpt-section' => 'Tõlkeüksus $1',
	'tpt-section-new' => 'Uus tõlkeüksus.
Nimi: $1',
	'tpt-section-deleted' => 'Tõlkeüksus $1',
	'tpt-template' => 'Lehekülje mall',
	'tpt-templatediff' => 'Leheküljemall on muutunud.',
	'tpt-diff-old' => 'Eelnev tekst',
	'tpt-diff-new' => 'Uus tekst',
	'tpt-submit' => 'Märgi see versioon tõlkimiseks',
	'tpt-sections-oldnew' => 'Uued ja olemasolevad tõlkeüksused',
	'tpt-sections-deleted' => 'Kustutatud tõlkeüksused',
	'tpt-sections-template' => 'Tõlkelehekülje mall',
	'tpt-badtitle' => 'Pealkiri ($1) ei sobi.',
	'tpt-nosuchpage' => 'Lehekülge $1 pole',
	'tpt-oldrevision' => '$2 pole lehekülje [[$1]] uusim versioon.
Ainult uusimaid versioone saab märkida tõlkimiseks.',
	'tpt-notsuitable' => 'Lehekülg $1 ei sobi tõlkimiseks.
Veendu, et see sisaldab <nowiki><translate></nowiki>-silte ja selle süntaks on õige.',
	'tpt-saveok' => '{{PLURAL:$2|Ühe|$2}} tõlkeüksusega lehekülg [[$1]] on märgitud tõlkimiseks.
Lehekülge saab nüüd <span class="plainlinks">[$3 tõlkida]</span>.',
	'tpt-badsect' => '"$1" ei sobi tõlkeüksuse $2 nimeks.',
	'tpt-showpage-intro' => 'Allpool on loetletud uued, olemasolevad ja kustutatud tõlkeüksused.
Enne selle versiooni märkimist tõlkimiseks, veendu palun, et tõlkeüksustes tehtud muudatused on võimalikult väikesed, et tõlkijad ei peaks tegema tarbetut tööd.',
	'tpt-mark-summary' => 'See versioon on märgitud tõlkimiseks',
	'tpt-edit-failed' => 'Lehekülje uuendamine ei õnnestunud: $1',
	'tpt-duplicate' => 'Tõlkeüksust nimega $1 kasutatakse rohkem kui ühel korral.',
	'tpt-already-marked' => 'Selle lehekülje uusim versioon juba on tõlkimiseks märgitud.',
	'tpt-unmarked' => 'Lehekülg $1 pole enam märgitud tõlkimiseks.',
	'tpt-list-nopages' => 'Ükski lehekülg pole märgitud tõlkimiseks ega ole valmis, et märkida ta tõlkimiseks.',
	'tpt-new-pages-title' => 'Tõlkimiseks esitatud leheküljed',
	'tpt-old-pages-title' => 'Tõlgitavad leheküljed',
	'tpt-other-pages-title' => 'Katkised leheküljed',
	'tpt-discouraged-pages-title' => 'Kasutusest välja jäetud leheküljed',
	'tpt-new-pages' => '{{PLURAL:$1|See lehekülg sisaldab|Need leheküljed sisaldavad}} tõlkesiltidega teksti,
aga ükski {{PLURAL:$1|selle lehekülje|nende lehekülgede}} versioon pole praegu märgitud tõlkimiseks.',
	'tpt-old-pages' => 'Mõned {{PLURAL:$1|selle lehekülje|nende lehekülgede}} versioonid on märgitud tõlkimiseks.',
	'tpt-other-pages' => '{{PLURAL:$1|Selle lehekülje vana versioon|Nende lehekülgede vanad versioonid}} on märgitud tõlkimiseks
ja {{PLURAL:$1|uusimat versiooni|uusimaid versioone}} ei saa tõlgitavaks teha.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Selle lehekülje|Nende lehekülgede}} edasist tõlkimist ei soovita.',
	'tpt-select-prioritylangs' => 'Olulisemate keelte koodide komaga eraldatud loetelu:',
	'tpt-select-prioritylangs-force' => 'Enneta tõlkimist teistesse keeltesse peale olulisemate keelte',
	'tpt-select-prioritylangs-reason' => 'Põhjus:',
	'tpt-sections-prioritylangs' => 'Olulisemad keeled',
	'tpt-rev-mark' => 'märgi tõlkimiseks',
	'tpt-rev-unmark' => 'eemalda tõlkimisest',
	'tpt-rev-discourage' => 'hoidu uutest tõlgetest',
	'tpt-rev-encourage' => 'ennista',
	'tpt-rev-mark-tooltip' => 'Märgi selle lehekülje viimane versioon tõlkimiseks.',
	'tpt-rev-unmark-tooltip' => 'Eemalda see lehekülg tõlkimisest.',
	'tpt-rev-discourage-tooltip' => 'Olgu selle lehekülje edasine tõlkimine soovimatu',
	'tpt-rev-encourage-tooltip' => 'Ennista see lehekülg harilikuks tõlkimiseks',
	'translate-tag-translate-link-desc' => 'Tõlgi see leht',
	'translate-tag-markthisagain' => 'Seda lehekülge on <span class="plainlinks">[$1 muudetud]</span> pärast seda, kui see viimati <span class="plainlinks">[$2 tõlkimiseks märgiti]</span>.',
	'translate-tag-hasnew' => 'See lehekülg sisaldab <span class="plainlinks">[$1 muudatusi]</span>, mida pole märgitud tõlkimiseks.',
	'tpt-translation-intro' => 'See on lehekülje [[$2]] <span class="plainlinks">[$1 tõlgitud versioon]</span> ja tõlkest on valmis $3%.',
	'tpt-languages-legend' => 'Teistes keeltes:',
	'tpt-languages-zero' => 'Alusta sellesse keelde tõlkimist',
	'tpt-target-page' => 'Seda lehekülge ei saa käsitsi uuendada.
See lehekülg on lehekülje [[$1]] tõlge ja tõlget saab uuendada [$2 tõlkeriista] abil.',
	'tpt-unknown-page' => 'See nimeruum on sisulehekülgede tõlkimiseks.
Lehekülg, mida redigeerida üritad, ei paista olevat seotud ühegi tõlkimiseks märgitud leheküljega.',
	'tpt-translation-restricted' => 'Tõlkeadministraator on tõkestanud selle lehekülje tõlkimise sellesse keelde.

Põhjus: $1',
	'tpt-discouraged-language-force' => "'''Seda lehekülge ei saa tõlkida $2 keelde.'''

Tõlkeadministraator otsustas, et seda lehekülge saab tõlkida vaid järgmistesse keeltesse: $3.",
	'tpt-discouraged-language' => "'''Selle lehekülje tõlkimine $2 keelde pole oluline.'''

Tõlkeadministraator otsustas, et keskendutakse järgmistesse keeltesse tõlkimisele: $3.",
	'tpt-discouraged-language-reason' => 'Põhjus: $1',
	'tpt-priority-languages' => 'Tõlkeadministraator määras, et on oluline tõlkida see rühm järgmistesse keeltesse: $1.',
	'tpt-render-summary' => 'Uuendatud, et vastata lähtelehekülje uuele versioonile',
	'aggregategroups' => 'Ühendrühmad',
	'tpt-aggregategroup-add' => 'Lisa',
	'tpt-aggregategroup-save' => 'Salvesta',
	'tpt-aggregategroup-add-new' => 'Lisa uus ühendrühm',
	'tpt-aggregategroup-new-name' => 'Nimi:',
	'tpt-aggregategroup-new-description' => 'Kirjeldus (valikuline):',
	'tpt-aggregategroup-remove-confirm' => 'Kas oled kindel, et soovid selle ühendrühma kustutada?',
	'tpt-aggregategroup-invalid-group' => 'Rühma pole',
	'pt-parse-open' => 'Puudub sildile &lt;translate> vastav lõpusilt.
Tõlkemall: <pre>$1</pre>',
	'pt-parse-close' => 'Puudub sildile &lt;/translate> vastav algussilt.
Tõlkemall: <pre>$1</pre>',
	'pt-parse-nested' => 'Pesastatud &lt;translate>-tõlkeüksused pole lubatud.
Sildi tekst: <pre>$1</pre>',
	'pt-shake-multiple' => 'Ühel tõlkeüksusel on mitu tähist.
Tõlkeüksuse tekst: <pre>$1</pre>',
	'pt-shake-position' => 'Tõlkeüksuse tähis on ootamatu koha peal.
Tõlkeüksuse tekst: <pre>$1</pre>',
	'pt-shake-empty' => 'Tõlkeüksus tähisega "$1" on tühi.',
	'log-description-pagetranslation' => 'Lehekülgede tõlkesüsteemiga seotud toimingute logi',
	'log-name-pagetranslation' => 'Lehekülgede tõlkelogi',
	'pt-movepage-title' => 'Tõlgitava lehekülje "$1" teisaldamine',
	'pt-movepage-blockers' => '{{PLURAL:$1|Järgmise tõrke|Järgmiste tõrgete}} tõttu ei saa tõlgitavat lehekülge uue pealkirja alla teisaldada:',
	'pt-movepage-block-base-exists' => 'Sihtkohaks määratud tõlgitav lehekülg "[[:$1]]" on olemas.',
	'pt-movepage-block-base-invalid' => 'Sihtkohaks määratud tõlgitava lehekülje pealkiri ei sobi.',
	'pt-movepage-block-tp-exists' => 'Sihtkohaks määratud tõlkelehekülg "[[:$2]]" on olemas.',
	'pt-movepage-block-tp-invalid' => 'Lehekülje "[[:$1]]" sihtkohaks määratud tõlkelehekülje pealkiri oleks vigane (liiga pikk?).',
	'pt-movepage-block-section-exists' => 'Tõlkeüksuse sihtkohaks määratud lehekülg "[[:$2]]" on olemas.',
	'pt-movepage-block-section-invalid' => 'Tõlkeüksuse sihtkohaks määratud lehekülje "[[:$1]]" pealkiri oleks vigane (liiga pikk?).',
	'pt-movepage-block-subpage-exists' => 'Sihtkohaks määratud alamlehekülg "[[:$2]]" on olemas.',
	'pt-movepage-block-subpage-invalid' => 'Lehekülje "[[:$1]]" sihtkohaks määratud alamlehekülje pealkiri oleks vigane (liiga pikk?).',
	'pt-movepage-list-pages' => 'Teisaldamisele kuuluvate lehekülgede loend',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Tõlkelehekülg|Tõlkeleheküljed}}',
	'pt-movepage-list-section' => 'Tõlkeüksuse {{PLURAL:$1|lehekülg|leheküljed}}',
	'pt-movepage-list-other' => '{{PLURAL:$1|Muu alamlehekülg|Muud alamleheküljed}}',
	'pt-movepage-list-count' => 'Teisaldamisele {{PLURAL:$1|kuulub kokku üks lehekülg|kuuluvad kokku $1 lehekülge}}.',
	'pt-movepage-legend' => 'Tõlgitava lehekülje teisaldamine',
	'pt-movepage-current' => 'Praegune nimi:',
	'pt-movepage-new' => 'Uus nimi:',
	'pt-movepage-reason' => 'Põhjus:',
	'pt-movepage-subpages' => 'Teisalda kõik alamleheküljed',
	'pt-movepage-action-check' => 'Kontrolli, kas teisaldamine on võimalik',
	'pt-movepage-action-perform' => 'Teisalda',
	'pt-movepage-action-other' => 'Muuda sihtkohta',
	'pt-movepage-intro' => 'See erilehekülg võimaldab teisaldada lehekülgi, mis on märgitud tõlkimiseks.
Toiming pole kohene, sest teisaldada tuleb palju lehekülgi.
Teisaldamise ajal pole võimalik kõnealustel lehekülgedel midagi teha.
Nurjumised logitakse [[Special:Log/pagetranslation|lehekülgede tõlkelogisse]] ja need tuleb käsitsi parandada.',
	'pt-movepage-logreason' => 'Osa tõlgitavast leheküljest $1.',
	'pt-movepage-started' => 'See põhilehekülg on nüüd teisaldatud.
Palun kontrolli, kas [[Special:Log/pagetranslation|lehekülgede tõlkelogis]] on tõrkeid ja teade lõpulejõudmise kohta.',
	'pt-locked-page' => 'See lehekülg on lukus, sest tõlgitavat lehekülge teisaldatakse parasjagu.',
	'pt-deletepage-lang-title' => 'Tõlkelehekülje "$1" kustutamine',
	'pt-deletepage-full-title' => 'Tõlgitava lehekülje "$1" kustutamine',
	'pt-deletepage-invalid-title' => 'Määratud lehekülg pole sobiv.',
	'pt-deletepage-invalid-text' => 'Määratud lehekülg pole tõlgitav lehekülg ega tõlkelehekülg.',
	'pt-deletepage-action-check' => 'Loetle kustutamisele kuuluvad leheküljed',
	'pt-deletepage-action-perform' => 'Kustuta',
	'pt-deletepage-action-other' => 'Muuda sihtlehekülge',
	'pt-deletepage-lang-legend' => 'Tõlkelehekülje kustutamine',
	'pt-deletepage-full-legend' => 'Tõlgitava lehekülje kustutamine',
	'pt-deletepage-any-legend' => 'Tõlgitava lehekülje või tõlkelehekülje kustutamine',
	'pt-deletepage-current' => 'Lehekülje nimi:',
	'pt-deletepage-reason' => 'Põhjus:',
	'pt-deletepage-subpages' => 'Kustuta kõik alamleheküljed',
	'pt-deletepage-list-pages' => 'Kustutamisele kuuluvate lehekülgede loend',
	'pt-deletepage-list-translation' => 'Tõlkeleheküljed',
	'pt-deletepage-list-section' => 'Tõlkeüksuse leheküljed',
	'pt-deletepage-list-other' => 'Muud alamlehed',
	'pt-deletepage-list-count' => 'Kustutamisele {{PLURAL:$1|kuulub kokku üks lehekülg|kuuluvad kokku $1 lehekülge}}.',
	'pt-deletepage-full-logreason' => 'Osa tõlgitavast leheküljest $1.',
	'pt-deletepage-lang-logreason' => 'Osa tõlkeleheküljest $1.',
	'pt-deletepage-started' => 'Palun kontrolli, kas [[Special:Log/pagetranslation|lehekülgede tõlkelogis]] on tõrkeid ja teade lõpulejõudmise kohta.',
	'pt-deletepage-intro' => 'See erilehekülg võimaldab kustutada terve tõlgitava lehekülje või ühe keele üksiku tõlkelehekülje.
Toiming pole kohene, sest kõik neist sõltuvad leheküljed kustutatakse samuti.
Nurjumised logitakse [[Special:Log/pagetranslation|lehekülgede tõlkelogisse]] ja need tuleb käsitsi parandada.',
);

/** Basque (euskara)
 * @author An13sa
 * @author Kobazulo
 * @author පසිඳු කාවින්ද
 */
$messages['eu'] = array(
	'pagetranslation' => 'Orrialdearen itzulpena',
	'tpt-section-new' => 'Itzulpen unitate berria.
Izena: $1',
	'tpt-section-deleted' => '$1 itzulpen unitatea',
	'tpt-template' => 'Orrialde txantiloia',
	'tpt-diff-old' => 'Aurreko testua',
	'tpt-diff-new' => 'Testu berria',
	'tpt-edit-failed' => 'Ezin izan da orrialdea eguneratu: $1',
	'tpt-select-prioritylangs-reason' => 'Arrazoia:',
	'tpt-rev-encourage' => 'Leheneratu',
	'translate-tag-translate-link-desc' => 'Itzuli orri hau',
	'tpt-languages-legend' => 'Beste hizkuntzak:',
	'tpt-discouraged-language-reason' => 'Arrazoia: $1',
	'tpt-aggregategroup-add' => 'Gehitu',
	'tpt-aggregategroup-save' => 'Gorde',
	'tpt-aggregategroup-new-name' => 'Izena:',
	'pt-movepage-list-translation' => 'Itzulpen orrialdeak', # Fuzzy
	'pt-movepage-list-other' => 'Bestelako azpiorrialdeak', # Fuzzy
	'pt-movepage-current' => 'Oraingo izena:',
	'pt-movepage-new' => 'Izen berria:',
	'pt-movepage-reason' => 'Arrazoia:',
	'pt-movepage-subpages' => 'Azpiorrialde guztiak mugitu',
	'pt-deletepage-current' => 'Orriaren izena:',
	'pt-deletepage-reason' => 'Arrazoia:',
);

/** Persian (فارسی)
 * @author Dalba
 * @author Huji
 * @author Mjbmr
 * @author ZxxZxxZ
 * @author پاناروما
 */
$messages['fa'] = array(
	'pagetranslation' => 'ترجمهٔ صفحه',
	'right-pagetranslation' => 'علامت‌گذاری نسخه‌های صفحه برای ترجمه',
	'action-pagetranslation' => 'مدیریت صفحه‌های ترجمه‌پذیر',
	'tpt-desc' => 'افزونه‌ای برای ترجمهٔ صفحه‌های محتوایی',
	'tpt-section' => 'واحد ترجمهٔ $1',
	'tpt-section-new' => 'واحد جدید ترجمه.
نام: $1',
	'tpt-section-deleted' => 'واحد ترجمهٔ $1',
	'tpt-template' => 'قالب صفحه',
	'tpt-templatediff' => 'قالب صفحه تغییر کرده‌است.',
	'tpt-diff-old' => 'متن قبلی',
	'tpt-diff-new' => 'متن جدید',
	'tpt-submit' => 'علامت‌گذاری این نسخه برای ترجمه',
	'tpt-sections-oldnew' => 'واحدهای جدید و موجود ترجمه',
	'tpt-sections-deleted' => 'واحدهای حذف‌شدهٔ ترجمه',
	'tpt-sections-template' => 'الگوی ترجمهٔ صفحه',
	'tpt-action-nofuzzy' => 'عدم ابطال ترجمه‌ها',
	'tpt-badtitle' => 'نام صفحهٔ داده‌شده ($1) عنوان معتبری نیست',
	'tpt-nosuchpage' => 'صفحهٔ $1 وجود ندارد',
	'tpt-oldrevision' => '$2 آخرین نسخهٔ صفحهٔ [[$1]] نیست.
فقط آخرین نسخه‌ها می‌توانند برای ترجمه علامت‌گذاری شوند.',
	'tpt-notsuitable' => 'صفحهٔ $1 برای ترجمه مناسب نیست.
مطمئن شوید برچسب <nowiki><translate></nowiki> و نحو مناسبی دارد.',
	'tpt-select-prioritylangs-reason' => 'دلیل:',
	'tpt-rev-mark' => 'علامت‌گذاری برای ترجمه',
	'tpt-rev-unmark' => 'حذف از ترجمه',
	'tpt-rev-discourage' => 'دلسرد',
	'tpt-rev-encourage' => 'احیا',
	'tpt-rev-unmark-tooltip' => 'حذف این صفحه از ترجمه.',
	'translate-tag-translate-link-desc' => 'ترجمه این پروژه',
	'translate-tag-markthis' => 'علامت‌گذاری این صفحه برای ترجمه',
	'tpt-languages-legend' => 'زبان‌های دیگر:',
	'tpt-languages-zero' => 'شروع ترجمه برای این زبان',
	'tpt-target-page' => 'این صفحه به صورت دستی به روز نمی‌شود.
این صفحه یک ترجمه‌ای از صفحهٔ [[$1]] است و ترجمه را می‌توان از طریق [$2 ابزار ترجمه] به روز کرد.',
	'tpt-discouraged-language-reason' => 'دلیل: $1',
	'tpt-aggregategroup-add' => 'افزودن',
	'tpt-aggregategroup-save' => 'ذخیره',
	'tpt-aggregategroup-new-name' => 'نام:',
	'tpt-aggregategroup-new-description' => 'توضیحات (اختیاری):',
	'tpt-aggregategroup-invalid-group' => 'گروه وجود ندارد',
	'log-name-pagetranslation' => 'سیاههٔ ترجمهٔ صفحه',
	'pt-movepage-list-pages' => 'فهرست صفحه‌ها برای انتقال',
	'pt-movepage-list-translation' => '{{PLURAL:$1|صفحهٔ|صفحه‌های}} ترجمه',
	'pt-movepage-list-section' => '{{PLURAL:$1|صفحهٔ|صفحه‌های}} واحد ترجمه',
	'pt-movepage-list-other' => 'زیر{{PLURAL:$1|صفحهٔ|صفحه‌های}} دیگر',
	'pt-movepage-legend' => 'انتقال صفحهٔ قابل ترجمه',
	'pt-movepage-current' => 'نام فعلی:',
	'pt-movepage-new' => 'نام جدید:',
	'pt-movepage-reason' => 'دلیل:',
	'pt-movepage-subpages' => 'انتقال همهٔ زیرصفحه‌ها',
	'pt-movepage-action-check' => 'بررسی کن که انتقال ممکن باشد',
	'pt-movepage-action-perform' => 'منتقل کن',
	'pt-movepage-action-other' => 'تغییر هدف',
	'pt-deletepage-invalid-title' => 'صفحه مشخص شده معتبر نیست.',
	'pt-deletepage-action-check' => 'فهرست صفحه‌ها برای حذف',
	'pt-deletepage-action-perform' => 'انجام حذف',
	'pt-deletepage-action-other' => 'تغییر هدف',
	'pt-deletepage-lang-legend' => 'حذف صفحهٔ ترجمه',
	'pt-deletepage-full-legend' => 'حذف صحفهٔ قابل ترجمه',
	'pt-deletepage-any-legend' => 'حذف صفحهٔ قابل ترجمه یا ترجمهٔ صفحهٔ قابل ترجمه', # Fuzzy
	'pt-deletepage-current' => 'نام صفحه:',
	'pt-deletepage-reason' => 'دلیل:',
	'pt-deletepage-subpages' => 'حذف تمام زیرصفحه‌ها',
	'pt-deletepage-list-pages' => 'فهرست صفحه‌هایی که حذف می‌شوند',
	'pt-deletepage-list-translation' => 'صفحه‌های ترجمه',
	'pt-deletepage-list-section' => 'صفحه‌های واحد ترجمه',
	'pt-deletepage-list-other' => 'زیرصفحه‌های دیگر',
	'pt-deletepage-list-count' => 'در کل $1 {{PLURAL:$1|صفحه|صفحه}} حذف می‌شوند.',
);

/** Finnish (suomi)
 * @author Beluga
 * @author Cimon Avaro
 * @author Crt
 * @author Lliehu
 * @author Nedergard
 * @author Nike
 * @author Olli
 * @author Silvonen
 * @author VezonThunder
 * @author ZeiP
 */
$messages['fi'] = array(
	'pagetranslation' => 'Sivujen kääntäminen',
	'right-pagetranslation' => 'Merkitä sivuja käännettäviksi',
	'action-pagetranslation' => 'hallita käännettäviä sivuja',
	'tpt-desc' => 'Laajennus sisältösivujen kääntämiseen.',
	'tpt-section' => 'Käännösosio $1',
	'tpt-section-new' => 'Uusi käännösosio.
Nimi: $1',
	'tpt-section-deleted' => 'Käännösosio $1',
	'tpt-template' => 'Sivun mallipohja',
	'tpt-templatediff' => 'Sivun mallipohja on muuttunut.',
	'tpt-diff-old' => 'Aikaisempi teksti',
	'tpt-diff-new' => 'Uusi teksti',
	'tpt-submit' => 'Merkitse tämä versio käännettäväksi',
	'tpt-sections-oldnew' => 'Uudet ja olemassa olevat käännösosiot',
	'tpt-sections-deleted' => 'Poistetut käännösosiot',
	'tpt-sections-template' => 'Käännössivun mallipohja',
	'tpt-action-nofuzzy' => 'Älä merkitse käännöksiä vanhentuneiksi',
	'tpt-badtitle' => 'Sivun nimi ($1) ei ole kelvollinen otsikko',
	'tpt-nosuchpage' => 'Sivua $1 ei ole olemassa',
	'tpt-oldrevision' => '$2 ei ole uusin versio sivusta [[$1]].
Ainoastaan uusin versio voidaan merkitä käännettäviksi.',
	'tpt-notsuitable' => 'Sivu $1 ei sovellu käännettäväksi.
Varmista, että sivu sisältää &lt;translate>-merkinnät ja että siinä ei ole ole syntaksivirheitä.',
	'tpt-saveok' => 'Sivu [[$1]] on merkitty käännettäväksi ja se sisältää $2 {{PLURAL:$2|käännösosion|käännösosiota}}.
Sivu voidaan nyt <span class="plainlinks">[$3 kääntää]</span>.',
	'tpt-badsect' => '”$1” ei ole kelpo nimi käännösosiolle $2.',
	'tpt-showpage-intro' => 'Alempana listattu uusia, nykyisiä ja poistettavia osioita.
Ennen kuin merkitset tämän version käännettäväksi, tarkista, että muutokset osioihin on minimoitu, jotta kääntäjille ei aiheudu tarpeetonta työtä.', # Fuzzy
	'tpt-mark-summary' => 'Tämä versio merkittiin käännettäväksi',
	'tpt-edit-failed' => 'Ei voitu tallentaa muutosta sivulle: $1',
	'tpt-duplicate' => 'Käännösosion nimeä $1 on käytetty useammin kuin kerran.',
	'tpt-already-marked' => 'Viimeisin versio tästä sivusta on jo merkitty käännettäväksi.',
	'tpt-unmarked' => 'Sivu $1 ei ole enää käännettävänä.',
	'tpt-list-nopages' => 'Yhtään sivua ei ole merkitty käännettäväksi eikä yhtään sivua ole valmiina käännettäväksi merkitsemistä varten.',
	'tpt-new-pages-title' => 'Käännettäväksi ehdotetut sivut',
	'tpt-old-pages-title' => 'Käännettävät sivut',
	'tpt-other-pages-title' => 'Rikkoutuneet sivut',
	'tpt-discouraged-pages-title' => 'Sivut, joita ei enää suositella käännettäväksi',
	'tpt-new-pages' => '{{PLURAL:$1|Tämä sivu sisältää|Nämä sivut sisältävät}} tekstiä, joka on valmis merkittäväksi kääntämistä varten,
mutta mikään versio {{PLURAL:$1|tästä sivusta|näistä sivuista}} ei ole tällä hetkellä merkitty käännettäväksi.',
	'tpt-old-pages' => 'Jokin versio {{PLURAL:$1|tästä sivusta on|näistä sivuista on}} merkitty käännettäväksi.',
	'tpt-other-pages' => 'Vanha versio {{PLURAL:$1|tästä sivusta|näistä sivuista}} on merkitty käännettäväksi,
mutta viimeisintä versiota ei voi merkitä käännettäväksi.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Tätä sivua|Näitä sivuja}} ei enää suositella käännettävän.',
	'tpt-select-prioritylangs' => 'Pilkuin erotettu lista ensisijaisista kielikoodeista:',
	'tpt-select-prioritylangs-reason' => 'Syy:',
	'tpt-sections-prioritylangs' => 'Ensisijaiset kielet',
	'tpt-rev-mark' => 'merkitse käännettäväksi',
	'tpt-rev-unmark' => 'poista käännettävistä sivuista',
	'tpt-rev-discourage' => 'vältä uusia käännöksiä',
	'tpt-rev-encourage' => 'palauta',
	'tpt-rev-mark-tooltip' => 'Merkitse tämän sivun viimeisin versio käännettäväksi.',
	'tpt-rev-unmark-tooltip' => 'Poista tämän sivun käännösominaisuus.',
	'tpt-rev-discourage-tooltip' => 'Piilota sivu käännösjärjestelmästä, jotta uusia käännöksiä ei enää tehtäisi.',
	'tpt-rev-encourage-tooltip' => 'Palauta tämä sivu käännösjärjestelmään.',
	'translate-tag-translate-link-desc' => 'Käännä tämä sivu',
	'translate-tag-markthis' => 'Merkitse tämä sivu käännettäväksi',
	'translate-tag-markthisagain' => 'Tähän sivuun on tehty <span class="plainlinks">[$1 muutoksia]</span> sen jälkeen kun se viimeksi <span class="plainlinks">[$2 merkittiin käännettäväksi]</span>.',
	'translate-tag-hasnew' => 'Tämä sivu sisältää <span class="plainlinks">[$1 muutoksia],</span> joita ei ole merkitty käännettäväksi.',
	'tpt-translation-intro' => 'Tämä on <span class="plainlinks">[$1 käännetty versio]</span> sivusta [[$2]], ja käännös on $3&nbsp;% valmis.',
	'tpt-languages-legend' => 'Muut kielet:',
	'tpt-languages-zero' => 'Aloita käännös tälle kielelle',
	'tpt-target-page' => 'Tätä sivua ei voi muokata tavalliseen tapaan.
Tämä sivu on käännös sivusta [[$1]] ja käännöstä voi päivittää käyttämällä [$2 käännöstyökalua].',
	'tpt-unknown-page' => 'Tämä nimiavaruus on varattu sisältösivujen käännöksille.
Sivu, jota yrität muokata, ei näytä vastaavan mitään sivua, joka on merkitty käännettäväksi.',
	'tpt-translation-restricted' => 'Käännösylläpitäjä on estänyt tämän sivun kääntämisen tälle kielelle.

Syy: $1',
	'tpt-discouraged-language-force' => "'''Tätä sivua ei voi kääntää kielelle $2.'''

Käännösylläpitäjä on päättänyt, että tämän sivun voi kääntää vain kielille $3.",
	'tpt-discouraged-language-reason' => 'Syy: $1',
	'tpt-render-summary' => 'Päivittäminen vastaamaan uutta versiota lähdesivusta',
	'tpt-download-page' => 'Sivun vienti käännösten kera',
	'aggregategroups' => 'Kokoelmaryhmät',
	'tpt-aggregategroup-add' => 'Lisää',
	'tpt-aggregategroup-save' => 'Tallenna',
	'tpt-aggregategroup-add-new' => 'Lisää uusi kokoelmaryhmä',
	'tpt-aggregategroup-new-name' => 'Nimi',
	'tpt-aggregategroup-new-description' => 'Kuvaus (vapaaehtoinen):',
	'tpt-aggregategroup-remove-confirm' => 'Haluatko varmasti poistaa tämän kokoelmaryhmän?',
	'tpt-aggregategroup-invalid-group' => 'Ryhmää ei ole',
	'pt-parse-open' => 'Sulkematon &lt;translate>-tägi.
Käännöspohja: <pre>$1</pre>',
	'pt-parse-close' => 'Avaamaton &lt;/translate>-tägi.
Käännöspohja: <pre>$1</pre>',
	'pt-parse-nested' => 'Sisäkkäiset &lt;translate>-tägit eivät ole sallittuja.
Käännettävä teksti: <pre>$1</pre>',
	'pt-shake-multiple' => 'Enemmän kuin yksi käännösosiotunniste käännösosiolla.
Käännösosion teksti: <pre>$1</pre>',
	'pt-shake-position' => 'Käännösosiotunniste on odottamattomassa paikassa.
Käännösosion teksti: <pre>$1</pre>',
	'pt-shake-empty' => 'Käännösosio $1 sisältää vain tunnisteen.',
	'log-description-pagetranslation' => 'Tämä loki sisältää sivunkäännösominaisuuteen liittyviä tapahtumia.',
	'log-name-pagetranslation' => 'Sivunkääntöloki',
	'logentry-pagetranslation-mark' => '$1 {{GENDER:$2|merkitsi}} sivun $3 käännettäväksi',
	'pt-movepage-title' => 'Käännettävän sivun $1 siirtäminen',
	'pt-movepage-blockers' => 'Käännettävää sivua ei voi siirtää uudelle nimelle {{PLURAL:$1|seuraavasta syystä|seuraavista syistä}}:',
	'pt-movepage-block-base-exists' => 'Kohdesivu [[:$1]] on olemassa.', # Fuzzy
	'pt-movepage-block-base-invalid' => 'Kohdesivun nimi ei ole kelvollinen.', # Fuzzy
	'pt-movepage-block-tp-exists' => 'Käännössivu [[:$2]] on olemassa.',
	'pt-movepage-block-tp-invalid' => 'Käännössivun [[:$1]] uusi nimi ei ole kelvollinen (liian pitkä?)',
	'pt-movepage-block-section-exists' => 'Käännösosiosivu [[:$2]] on olemassa.', # Fuzzy
	'pt-movepage-block-section-invalid' => 'Käännösosiosivun [[:$1]] uusi nimi ei ole kelvollinen (liian pitkä?)', # Fuzzy
	'pt-movepage-block-subpage-exists' => 'Alasivu [[:$2]] on olemassa.',
	'pt-movepage-block-subpage-invalid' => 'Alasivun [[:$1]] uusi nimi ei ole kelvollinen (liian pitkä?)',
	'pt-movepage-list-pages' => 'Lista siirrettävistä sivuista',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Käännössivu|Käännössivut}}',
	'pt-movepage-list-section' => '{{PLURAL:$1|Käännösosiosivut}}',
	'pt-movepage-list-other' => '{{PLURAL:$1|Muu alasivu|Muut alasivut}}',
	'pt-movepage-list-count' => 'Yhteensä $1 {{PLURAL:$1|siirrettävä sivu|siirrettävää sivua}}.',
	'pt-movepage-legend' => 'Siirrä käännettävä sivu',
	'pt-movepage-current' => 'Nykyinen nimi',
	'pt-movepage-new' => 'Uusi nimi',
	'pt-movepage-reason' => 'Syy',
	'pt-movepage-subpages' => 'Siirrä kaikki alasivut',
	'pt-movepage-action-check' => 'Tarkista, onko sivun siirtäminen mahdollista',
	'pt-movepage-action-perform' => 'Tee siirto',
	'pt-movepage-action-other' => 'Vaihda kohde',
	'pt-movepage-intro' => 'Tällä toimintosivulla voit siirtää käännettäväksi merkittyjä sivuja.
Siirto ei tapahdu heti, koska useita sivuja täytyy siirtää.
Sivut ovat lukittuna siirron ajan.
Epäonnistuneet siirrot tallennetaan [[Special:Log/pagetranslation|sivunkääntölokiin]] ja ne täytyy korjata käsin.',
	'pt-movepage-logreason' => 'Osa käännettävää sivua $1.',
	'pt-movepage-started' => 'Käännettävän sivun perussivu on siirretty.
Tarkista mahdolliset virheet ja valmistumisviestit [[Special:Log/pagetranslation|sivunkääntölokista]].',
	'pt-locked-page' => 'Tämä sivu on lukittu, koska käännettävän sivun siirtäminen on kesken.',
	'pt-deletepage-lang-title' => 'Poistetaan käännössivu $1.',
	'pt-deletepage-full-title' => 'Poistetaan käännettävissä oleva sivu $1.',
	'pt-deletepage-invalid-title' => 'Määritetty sivu ei kelpaa.',
	'pt-deletepage-invalid-text' => 'Sivu ei ole käännettävissä oleva sivu tai sellaisen käännös.',
	'pt-deletepage-action-check' => 'Luetteloi poistettavat sivut',
	'pt-deletepage-action-perform' => 'Suorita poisto',
	'pt-deletepage-action-other' => 'Vaihda kohdetta',
	'pt-deletepage-lang-legend' => 'Poista käännössivu',
	'pt-deletepage-full-legend' => 'Poista käännettävissä oleva sivu',
	'pt-deletepage-any-legend' => 'Poista käännettävissä oleva sivu tai sellaisen käännös',
	'pt-deletepage-current' => 'Sivun nimi',
	'pt-deletepage-reason' => 'Syy',
	'pt-deletepage-subpages' => 'Poista kaikki alasivut',
	'pt-deletepage-list-pages' => 'Poistettavien sivujen luettelo',
	'pt-deletepage-list-translation' => 'Käännössivut',
	'pt-deletepage-list-section' => 'Käännösosiosivut',
	'pt-deletepage-list-other' => 'Muut alasivut',
	'pt-deletepage-list-count' => 'Yhteensä $1 {{PLURAL:$1|poistettava sivu|poistettavaa sivua}}.',
	'pt-deletepage-full-logreason' => 'Osa käännettävää sivua $1.',
	'pt-deletepage-lang-logreason' => 'Osa käännössivua $1.',
	'pt-deletepage-started' => 'Virheet ja valmistusviesti löytyvät [[Special:Log/pagetranslation|sivunkääntölokista]].',
	'pt-deletepage-intro' => 'Tämän toimintosivun avulla voit poistaa koko käännettävän sivun tai tietynkieliset käännökset.
Poisto ei tapahdu välittömästi, sillä useita sivuja täytyy poistaa.
Virheet merkitään [[Special:Log/pagetranslation|sivunkääntölokiin]] ja ne täytyy korjata käsin.',
);

/** French (français)
 * @author Cquoi
 * @author Crochet.david
 * @author DavidL
 * @author Gomoko
 * @author Grondin
 * @author Houcinee1
 * @author IAlex
 * @author Linedwell
 * @author Peter17
 * @author Purodha
 * @author Sherbrooke
 * @author Tititou36
 * @author Urhixidur
 * @author Verdy p
 * @author Wyz
 * @author Y-M D
 */
$messages['fr'] = array(
	'pagetranslation' => 'Traduction de pages',
	'right-pagetranslation' => 'Marquer des versions de pages pour être traduites',
	'action-pagetranslation' => 'gérer les pages traduisibles',
	'tpt-desc' => 'Extension pour traduire des pages de contenu',
	'tpt-section' => 'Unité de traduction $1',
	'tpt-section-new' => 'Nouvelle unité de traduction. Nom : $1',
	'tpt-section-deleted' => 'Unité de traduction $1',
	'tpt-template' => 'Modèle de page',
	'tpt-templatediff' => 'Le modèle de page a changé.',
	'tpt-diff-old' => 'Texte précédent',
	'tpt-diff-new' => 'Nouveau texte',
	'tpt-submit' => 'Marquer cette version pour être traduite',
	'tpt-sections-oldnew' => 'Unités de traduction nouvelles et existantes',
	'tpt-sections-deleted' => 'Unités de traduction supprimées',
	'tpt-sections-template' => 'Modèle de page de traduction',
	'tpt-action-nofuzzy' => 'Ne pas invalider les traductions',
	'tpt-badtitle' => 'Le nom de page donné ($1) n’est pas un titre valide',
	'tpt-nosuchpage' => "La page $1 n'existe pas",
	'tpt-oldrevision' => '$2 n’est pas la dernière version de la page [[$1]].
Seule la dernière version de la page peut être marquée pour être traduite.',
	'tpt-notsuitable' => 'La page $1 n’est pas susceptible d’être traduite.
Assurez-vous qu’elle contienne la balise <nowiki><translate></nowiki> et qu’elle ait une syntaxe correcte.',
	'tpt-saveok' => 'La page [[$1]] a été marquée pour être traduite avec $2 {{PLURAL:$2|unité|unités}} de traduction.
La page peut être <span class="plainlinks">[$3 traduite]</span> dès maintenant.',
	'tpt-offer-notify' => 'Vous pouvez <span class="plainlinks">[$1 notifier les traducteurs]</span> au sujet de cette page.',
	'tpt-badsect' => '« $1 » n’est pas un nom valide pour une unité de traduction $2.',
	'tpt-showpage-intro' => 'Ci-dessous, les nouvelles traductions, celles existantes et supprimées.
Avant de marquer ces versions pour être traduites, vérifier que les modifications aux sections sont minimisées pour éviter du travail inutile aux traducteurs.',
	'tpt-mark-summary' => 'Cette version a été marquée pour être traduite',
	'tpt-edit-failed' => 'Impossible de mettre à jour la page $1',
	'tpt-duplicate' => "Le nom de l'unité traduction $1 est utilisé plus d'une fois.",
	'tpt-already-marked' => 'La dernière version de cette page a déjà été marquée pour être traduite.',
	'tpt-unmarked' => "La page $1 n'est plus marquée pour être traduite.",
	'tpt-list-nopages' => 'Aucune page n’a été marquée pour être traduite ni n’est prête à l’être.',
	'tpt-new-pages-title' => 'Pages proposées à la traduction',
	'tpt-old-pages-title' => 'Pages en cours de traduction',
	'tpt-other-pages-title' => 'Pages erronées',
	'tpt-discouraged-pages-title' => 'Pages découragées',
	'tpt-new-pages' => '{{PLURAL:$1|Cette page contient|Ces pages contiennent}} du texte avec des balises de traduction, mais aucune version de {{PLURAL:$1|cette page n’est marquée pour être traduite|ces pages ne sont marquées pour être traduites}}.',
	'tpt-old-pages' => 'Des versions de {{PLURAL:$1|cette page|ces pages}} ont été marquées pour être traduites.',
	'tpt-other-pages' => 'Une ancienne version de {{PLURAL:$1|la page suivante|chacune des pages suivantes}} a été marquée pour être traduite,
mais {{PLURAL:$1|sa dernière version|leur dernière version respective}} ne peut pas être marquée ainsi :',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Cette page a été découragée|Ces pages ont été découragées}} à être {{PLURAL:$1|traduite|traduites}} davantage.',
	'tpt-select-prioritylangs' => 'Liste de codes de langue prioritaire séparés par des virgules:',
	'tpt-select-prioritylangs-force' => 'Empêcher les traductions en des langues autres que les langues prioritaires',
	'tpt-select-prioritylangs-reason' => 'Motif :',
	'tpt-sections-prioritylangs' => 'Langues prioritaires',
	'tpt-rev-mark' => 'marquer pour traduction',
	'tpt-rev-unmark' => 'supprimer de la traduction',
	'tpt-rev-discourage' => 'décourager',
	'tpt-rev-encourage' => 'rétablir',
	'tpt-rev-mark-tooltip' => 'Marquer la version la plus récente de cette page pour la traduction.',
	'tpt-rev-unmark-tooltip' => 'Supprimer cette page de la traduction.',
	'tpt-rev-discourage-tooltip' => 'Dissuader les futures traductions sur cette page.',
	'tpt-rev-encourage-tooltip' => 'Rétablir cette page en traduction normale.',
	'translate-tag-translate-link-desc' => 'Traduire cette page',
	'translate-tag-markthis' => 'Marquer cette page pour être traduite',
	'translate-tag-markthisagain' => 'Cette page a eu <span class="plainlinks">[$1 des modifications]</span> depuis qu’elle a été dernièrement <span class="plainlinks">[$2 marquée pour être traduite]</span>.',
	'translate-tag-hasnew' => 'Cette page contient <span class="plainlinks">[$1 des modifications]</span> qui ne sont pas marquées pour la traduction.',
	'tpt-translation-intro' => 'Cette page est une <span class="plainlinks">[$1 traduction]</span> de la page [[$2]] et la traduction est complétée à $3 % et à jour.',
	'tpt-languages-legend' => 'Autres langues :',
	'tpt-languages-zero' => 'Commencer la traduction pour cette langue',
	'tpt-tab-translate' => 'Traduire',
	'tpt-target-page' => 'Cette page ne peut pas être mise à jour manuellement.
Elle est une version traduite de [[$1]] et la traduction peut être mise à jour en utilisant [$2 l’outil de traduction].',
	'tpt-unknown-page' => 'Cet espace de noms est réservé pour la traduction de pages.
La page que vous essayé de modifier ne semble correspondre à aucune page marquée pour être traduite.',
	'tpt-translation-restricted' => 'La traduction de cette page dans cette langue a été empêchée par un administrateur des traductions.

Motif: $1',
	'tpt-discouraged-language-force' => 'Un administrateur des traductions a limité les langues dans lesquelles cette page peut être traduite. Cette langue ne fait pas partie de celles-ci.

Motif: $1',
	'tpt-discouraged-language' => 'La langue vers laquelle les messages listés dans cette page peuvent être traduits ne fait pas partie des langues prioritaires définies par un administrateur des traductions.

Motif : $1',
	'tpt-discouraged-language-reason' => 'Raison : $1',
	'tpt-priority-languages' => 'Un administrateur de traduction a défini les langues prioritaire pour ce groupe : $1 .',
	'tpt-render-summary' => 'Mise à jour pour être en accord avec la nouvelle version de la source de la page',
	'tpt-download-page' => 'Exporter la page avec ses traductions',
	'aggregategroups' => "Groupes d'agrégation",
	'tpt-aggregategroup-add' => 'Ajouter',
	'tpt-aggregategroup-save' => 'Enregistrer',
	'tpt-aggregategroup-add-new' => "Ajouter un nouveau groupe d'agrégation",
	'tpt-aggregategroup-new-name' => 'Nom:',
	'tpt-aggregategroup-new-description' => 'Description (facultative):',
	'tpt-aggregategroup-remove-confirm' => 'Êtes-vous sûr de vouloir supprimer ce groupe agrégé?',
	'tpt-aggregategroup-invalid-group' => "Le groupe n'existe pas",
	'pt-parse-open' => 'Balise &lt;translate> asymétrique.
Modèle de traduction : <pre>$1</pre>',
	'pt-parse-close' => 'Balise &lt;/translate> asymétrique.
Modèle de traduction : <pre>$1</pre>',
	'pt-parse-nested' => 'Les sections &lt;translate> imbriquées ne sont pas autorisées.
Texte de la balise : <pre>$1</pre>',
	'pt-shake-multiple' => 'Marqueurs de section multiples pour une section.
Texte de la section : <pre>$1</pre>',
	'pt-shake-position' => 'Marqueurs de section à une position inattendue.
Texte de la section : <pre>$1</pre>',
	'pt-shake-empty' => 'Section vide pour le marqueur "$1".',
	'log-description-pagetranslation' => 'Journal des actions liées au système de traduction de pages',
	'log-name-pagetranslation' => 'Journal des traductions de pages',
	'logentry-pagetranslation-mark' => '$1 {{GENDER:$2|a marqué}} $3 à traduire',
	'logentry-pagetranslation-unmark' => '$1 {{GENDER:$2|a supprimé}} $3 des traductions à faire',
	'logentry-pagetranslation-moveok' => '$1 {{GENDER:$2|a terminé}} le renommage d’une page traduisible $3 en $4',
	'logentry-pagetranslation-movenok' => '$1 {{GENDER:$2|a rencontré}} un problème en déplaçant la page $3 vers $4',
	'logentry-pagetranslation-deletefok' => '$1 {{GENDER:$2|a terminé}} la suppression d’une page traduisible $3',
	'logentry-pagetranslation-deletefnok' => '$1 {{GENDER:$2|n’a pas réussi}} à supprimer $3 qui appartient à la page traduisible $4',
	'logentry-pagetranslation-deletelok' => '$1 {{GENDER:$2|a terminé}} la suppression d’une page traduisible $3',
	'logentry-pagetranslation-deletelnok' => '$1 {{GENDER:$2|n’a pas réussi}} à supprimer $3 qui appartient à la page traduisible $4',
	'logentry-pagetranslation-encourage' => '$1 {{GENDER:$2|a encouragé}} la traduction de $3',
	'logentry-pagetranslation-discourage' => '$1 {{GENDER:$2|a découragé}} la traduction de $3',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1 {{GENDER:$2|a supprimé}} les langues prioritaires pour la page traduisible $3',
	'logentry-pagetranslation-prioritylanguages' => '$1 {{GENDER:$2|a fixé}} les langues prioritaires pour la page traduisible $3 à $5',
	'logentry-pagetranslation-prioritylanguages-force' => '$1 {{GENDER:$2|a limité}} les langues pour la page traduisible $3 à $5',
	'logentry-pagetranslation-associate' => '$1 {{GENDER:$2|a ajouté}} la page traduisible $3 au groupe agrégé $4',
	'logentry-pagetranslation-dissociate' => '$1 {{GENDER:$2|a supprimé}} la page traduisible $3 du groupe agrégé $4',
	'pt-movepage-title' => 'Déplacer la page à traduire $1',
	'pt-movepage-blockers' => 'La page à traduire ne peut pas être renommée à cause {{PLURAL:$1|de l’erreur suivante|des erreurs suivantes}} :',
	'pt-movepage-block-base-exists' => 'La page traduisible cible "[[:$1]]" existe.',
	'pt-movepage-block-base-invalid' => "Le nom de la page traduisible cible n'est pas un titre correct.",
	'pt-movepage-block-tp-exists' => 'La page de traduction cible [[:$2]] existe.',
	'pt-movepage-block-tp-invalid' => 'Le titre de la page de traduction cible pour [[:$1]] serait incorrect (trop long ?).',
	'pt-movepage-block-section-exists' => 'La page cible « [[:$2]] » pour la section existe.',
	'pt-movepage-block-section-invalid' => 'Le titre de section de page cible pour « [[:$1]] » serait incorrect (trop long ?).',
	'pt-movepage-block-subpage-exists' => 'La sous-page cible [[:$2]] existe.',
	'pt-movepage-block-subpage-invalid' => 'Le titre de la sous-page cible pour [[:$1]] serait incorrect (trop long ?).',
	'pt-movepage-list-pages' => 'Liste des pages à déplacer',
	'pt-movepage-list-translation' => '{{PLURAL:$1|page|pages}} de traduction',
	'pt-movepage-list-section' => "{{PLURAL:$1|page|pages}} d'unités de traduction",
	'pt-movepage-list-other' => '{{PLURAL:$1|Autre sous-page|Autres sous-pages}}',
	'pt-movepage-list-count' => '$1 {{PLURAL:$1|page|pages}} à déplacer au total.',
	'pt-movepage-legend' => 'Déplacer la page à traduire',
	'pt-movepage-current' => 'Nom actuel :',
	'pt-movepage-new' => 'Nouveau nom :',
	'pt-movepage-reason' => 'Motif :',
	'pt-movepage-subpages' => 'Renommer toutes les sous-pages',
	'pt-movepage-action-check' => 'Vérifier si le renommage est possible',
	'pt-movepage-action-perform' => 'Renommer',
	'pt-movepage-action-other' => 'Changer la cible',
	'pt-movepage-intro' => "Cette page spéciale vous permet de renommer des pages qui sont marquées pour être traduites.
L’action de renommage ne sera pas immédiate, car de nombreuses pages devront être déplacées.
Pendant que les pages sont déplacées, il n'est pas possible d’interagir avec elles.
Les échecs seront enregistrés dans le [[Special:Log/pagetranslation|journal de traduction]] et devront être corrigés manuellement.",
	'pt-movepage-logreason' => 'Extrait de la page à traduire $1.',
	'pt-movepage-started' => 'La page de base est à présent renommée.
Veuillez vérifier le [[Special:Log/pagetranslation|journal des traductions]] pour repérer d’éventuelles erreurs et lire le message de complétion.',
	'pt-locked-page' => 'Cette page est verrouillée parce que la page à traduire est en cours de renommage.',
	'pt-deletepage-lang-title' => 'Suppression de la page de traduction $1.',
	'pt-deletepage-full-title' => 'Suppression de la page à traduire $1.',
	'pt-deletepage-invalid-title' => "La page spécifiée n'est pas valide.",
	'pt-deletepage-invalid-text' => "La page spécifiée n'est pas une page à traduire, ni une traduction de celle-ci.",
	'pt-deletepage-action-check' => 'Lister les pages à supprimer',
	'pt-deletepage-action-perform' => 'Faire la suppression',
	'pt-deletepage-action-other' => 'Changer la cible',
	'pt-deletepage-lang-legend' => 'Supprimer la page traduite',
	'pt-deletepage-full-legend' => 'Supprimer la page à traduire',
	'pt-deletepage-any-legend' => 'Supprimer la page à traduire ou la page de traduction',
	'pt-deletepage-current' => 'Nom de la page :',
	'pt-deletepage-reason' => 'Motif :',
	'pt-deletepage-subpages' => 'Supprimer tous les sous-pages',
	'pt-deletepage-list-pages' => 'Liste des pages à supprimer',
	'pt-deletepage-list-translation' => 'Pages de traduction',
	'pt-deletepage-list-section' => "Pages d'unités de traduction",
	'pt-deletepage-list-other' => 'Autres sous-pages',
	'pt-deletepage-list-count' => 'Au total, $1 {{PLURAL:$1|page|pages}} à supprimer.',
	'pt-deletepage-full-logreason' => 'Partie de la page à traduire $1.',
	'pt-deletepage-lang-logreason' => 'Partie de la page de traduction $1.',
	'pt-deletepage-started' => 'Veuillez vérifier le [[Special:Log/pagetranslation|journal des traductions]] pour les erreurs et le message de la fin.',
	'pt-deletepage-intro' => "Cette page spéciale vous permet de supprimer une page traduisible entièrement, ou une page traduisible individuelle dans une langue.
L'action de suppression n'est pas instantanée, car plusieurs pages dépendantes de celle-ci seront aussi supprimées.
Les échecs seront inscrits dans le [[Special:Log/pagetranslation|journal des traductions]] et ils doivent être corrigés à la main.",
);

/** Franco-Provençal (arpetan)
 * @author ChrisPtDe
 * @author Purodha
 */
$messages['frp'] = array(
	'pagetranslation' => 'Traduccion de pâges',
	'right-pagetranslation' => 'Marcar des vèrsions de pâges por étre traduites',
	'tpt-desc' => 'Èxtension por traduire des pâges de contegnu',
	'tpt-section' => 'Unitât de traduccion $1',
	'tpt-section-new' => 'Novèla unitât de traduccion.
Nom : $1',
	'tpt-section-deleted' => 'Unitât de traduccion $1',
	'tpt-template' => 'Modèlo de pâge',
	'tpt-templatediff' => 'Lo modèlo de pâge at changiê.',
	'tpt-diff-old' => 'Tèxto devant',
	'tpt-diff-new' => 'Tèxto novél',
	'tpt-submit' => 'Marcar ceta vèrsion por étre traduita',
	'tpt-sections-oldnew' => 'Unitâts de traduccion novèles et ègzistentes',
	'tpt-sections-deleted' => 'Unitâts de traduccion suprimâyes',
	'tpt-sections-template' => 'Modèlo de pâge de traduccion',
	'tpt-action-nofuzzy' => 'Pas envalidar les traduccions',
	'tpt-badtitle' => 'Lo nom de pâge balyê ($1) est pas un titro valido',
	'tpt-nosuchpage' => 'La pâge $1 ègziste pas',
	'tpt-oldrevision' => '$2 est pas la dèrriére vèrsion de la pâge [[$1]].
Solament la dèrriére vèrsion de la pâge pôt étre marcâye por étre traduita.',
	'tpt-notsuitable' => 'La pâge $1 sè préte pas por étre traduita.
Assurâd-vos que contegne la balisa <nowiki><translate></nowiki> et pués qu’èye na sintaxa justa.',
	'tpt-saveok' => 'La pâge [[$1]] est étâye marcâye por étre traduita avouéc $2 unitât{{PLURAL:$2||s}} de traduccion.
La pâge pôt étre <span class="plainlinks">[$3 traduita]</span> dês ora.',
	'tpt-badsect' => '« $1 » est pas un nom valido por na unitât de traduccion $2.',
	'tpt-showpage-intro' => 'Ce-desot les novèles traduccions, celes ègzistentes et pués celes suprimâyes.
Devant que marcar ceta vèrsion por étre traduita, controlâd que los changements a les sèccions sont petiôts por èvitar de travâly inutilo ux traductors.', # Fuzzy
	'tpt-mark-summary' => 'Ceta vèrsion est étâye marcâye por étre traduita',
	'tpt-edit-failed' => 'Empossiblo de betar a jorn la pâge : $1',
	'tpt-already-marked' => 'La dèrriére vèrsion de ceta pâge est ja étâye marcâye por étre traduita.',
	'tpt-unmarked' => 'La pâge $1 est pas més marcâye por étre traduita.',
	'tpt-list-nopages' => 'Niona pâge est étâye marcâye por étre traduita ou ben est prèsta por l’étre.',
	'tpt-new-pages-title' => 'Pâges proposâyes por étre traduites',
	'tpt-old-pages-title' => 'Pâges en cors de traduccion',
	'tpt-other-pages-title' => 'Pâges câsses',
	'tpt-discouraged-pages-title' => 'Pâges dècoragiêyes',
	'tpt-new-pages' => '{{PLURAL:$1|Ceta pâge contint|Cetes pâges contegnont}} de tèxto avouéc des balises de traduccion,
mas niona vèrsion de {{PLURAL:$1|ceta pâge est marcâye por étre traduita|cetes pâges sont marcâyes por étre traduites}}.',
	'tpt-old-pages' => 'Des vèrsions de {{PLURAL:$1|ceta pâge|cetes pâges}} sont étâyes marcâyes por étre traduites.',
	'tpt-other-pages' => '{{PLURAL:$1|Na vielye vèrsion de ceta pâge est étâye marcâye por étre traduita|Des vielyes vèrsions de cetes pâges sont étâyes marcâyes por étre traduites}},
mas {{PLURAL:$1|la dèrriére vèrsion pôt pas étre marcâye|les dèrriéres vèrsions pôvont pas étre marcâyes}} d’ense.',
	'tpt-select-prioritylangs-reason' => 'Rêson :',
	'tpt-rev-mark' => 'marcar por étre traduita',
	'tpt-rev-unmark' => 'enlevar de la traduccion',
	'tpt-rev-discourage' => 'dècoragiér',
	'tpt-rev-encourage' => 'refâre',
	'translate-tag-translate-link-desc' => 'Traduire ceta pâge',
	'translate-tag-markthis' => 'Marcar ceta pâge por étre traduita',
	'translate-tag-markthisagain' => 'Ceta pâge at avu des <span class="plainlinks">[$1 changements]</span> dês qu’est étâye <span class="plainlinks">[$2 marcâye dèrriérement por étre traduita]</span>.',
	'translate-tag-hasnew' => 'Ceta pâge contint des <span class="plainlinks">[$1 changements]</span> que sont pas marcâs por la traduccion.',
	'tpt-translation-intro' => 'Ceta pâge est na <span class="plainlinks">[$1 traduccion]</span> de la pâge [[$2]] et la traduccion est complètâye a $3 % et pués a jorn.',
	'tpt-languages-legend' => 'Ôtres lengoues :',
	'tpt-target-page' => 'Ceta pâge pôt pas étre betâye a jorn a la man.
El est na traduccion de la pâge [[$1]] et la traduccion pôt étre betâye a jorn en empleyent l’[$2 outil de traduccion].',
	'tpt-unknown-page' => 'Cet’èspâço de noms est resèrvâ por la traduccion de pâges de contegnu.
La pâge que vos tâchiéd de changiér semble corrèspondre a gins de pâge marcâye por étre traduita.',
	'tpt-discouraged-language-reason' => 'Rêson : $1',
	'tpt-render-summary' => 'Misa a jorn por étre en acôrd avouéc la novèla vèrsion de la pâge sôrsa',
	'tpt-download-page' => 'Èxportar la pâge avouéc les sines traduccions',
	'tpt-aggregategroup-add' => 'Apondre',
	'tpt-aggregategroup-save' => 'Encartar',
	'tpt-aggregategroup-new-name' => 'Nom :',
	'tpt-aggregategroup-new-description' => 'Dèscripcion (u chouèx) :',
	'tpt-aggregategroup-invalid-group' => 'Lo groupo ègziste pas',
	'pt-parse-open' => 'Balisa &lt;translate> asimètrica.
Modèlo de traduccion : <pre>$1</pre>',
	'pt-parse-close' => 'Balisa &lt;/translate> asimètrica.
Modèlo de traduccion : <pre>$1</pre>',
	'pt-parse-nested' => 'Les sèccions &lt;translate> embrecâyes sont pas ôtorisâyes.
Tèxto de la balisa : <pre>$1</pre>', # Fuzzy
	'pt-shake-multiple' => 'Un mouél de marcors de sèccion por yona sèccion.
Tèxto de la sèccion : <pre>$1</pre>', # Fuzzy
	'pt-shake-position' => 'Marcors de sèccion a na posicion emprèvua.
Tèxto de la sèccion : <pre>$1</pre>', # Fuzzy
	'pt-shake-empty' => 'Sèccion voueda por lo marcor « $1 ».', # Fuzzy
	'log-description-pagetranslation' => 'Jornal de les accions liyêyes u sistèmo de traduccion de pâges',
	'log-name-pagetranslation' => 'Jornal de les traduccions de pâges',
	'pt-movepage-title' => 'Dèplaciér la pâge traduisibla « $1 »',
	'pt-movepage-blockers' => 'La pâge traduisibla pôt pas étre renomâye a côsa de {{PLURAL:$1|ceta fôta|cetes fôtes}} :',
	'pt-movepage-block-base-exists' => 'La pâge de bâsa ciba « [[:$1]] » ègziste.', # Fuzzy
	'pt-movepage-block-base-invalid' => 'La pâge de bâsa ciba at un titro fôx.', # Fuzzy
	'pt-movepage-block-tp-exists' => 'La pâge de traduccion ciba « [[:$2]] » ègziste.',
	'pt-movepage-block-tp-invalid' => 'Lo titro de la pâge de traduccion ciba por « [[:$1]] » serêt fôx (trop long ?).',
	'pt-movepage-block-section-exists' => 'La pâge de sèccion ciba « [[:$2]] » ègziste.', # Fuzzy
	'pt-movepage-block-section-invalid' => 'Lo titro de la pâge de sèccion ciba por « [[:$1]] » serêt fôx (trop long ?).', # Fuzzy
	'pt-movepage-block-subpage-exists' => 'La sot-pâge ciba « [[:$2]] » ègziste.',
	'pt-movepage-block-subpage-invalid' => 'Lo titro de la sot-pâge ciba por « [[:$1]] » serêt fôx (trop long ?).',
	'pt-movepage-list-pages' => 'Lista de les pâges a dèplaciér',
	'pt-movepage-list-translation' => 'Pâges de traduccion', # Fuzzy
	'pt-movepage-list-section' => 'Pâges de sèccion', # Fuzzy
	'pt-movepage-list-other' => 'Ôtres sot-pâges', # Fuzzy
	'pt-movepage-list-count' => 'En tot $1 pâge{{PLURAL:$1||s}} a dèplaciér.',
	'pt-movepage-legend' => 'Dèplaciér la pâge traduisibla',
	'pt-movepage-current' => 'Nom d’ora :',
	'pt-movepage-new' => 'Novél nom :',
	'pt-movepage-reason' => 'Rêson :',
	'pt-movepage-subpages' => 'Renomar totes les sot-pâges',
	'pt-movepage-action-check' => 'Controlar se lo changement de nom est possiblo',
	'pt-movepage-action-perform' => 'Renomar',
	'pt-movepage-action-other' => 'Changiér la ciba',
	'pt-movepage-logreason' => 'Partia de la pâge traduisibla « $1 ».',
	'pt-movepage-started' => 'Ora la pâge de bâsa est renomâye.
Volyéd controlar lo [[Special:Log/pagetranslation|jornal de les traduccions de pâges]] por repèrar des fôtes et por liére lo mèssâjo d’avance.',
	'pt-locked-page' => 'Ceta pâge est vèrrolyêye perce que la pâge traduisibla est aprés étre renomâye.',
	'pt-deletepage-lang-title' => 'Suprèssion de la pâge de traduccion « $1 ».',
	'pt-deletepage-full-title' => 'Suprèssion de la pâge traduisibla « $1 ».',
	'pt-deletepage-invalid-title' => 'La pâge spècifiâye est pas valida.',
	'pt-deletepage-action-check' => 'Listar les pâges a suprimar',
	'pt-deletepage-action-perform' => 'Fâre la suprèssion',
	'pt-deletepage-action-other' => 'Changiér la ciba',
	'pt-deletepage-lang-legend' => 'Suprimar la pâge de traduccion',
	'pt-deletepage-full-legend' => 'Suprimar la pâge traduisibla',
	'pt-deletepage-current' => 'Nom de la pâge :',
	'pt-deletepage-reason' => 'Rêson :',
	'pt-deletepage-subpages' => 'Suprimar totes les sot-pâges',
	'pt-deletepage-list-pages' => 'Lista de les pâges a suprimar',
	'pt-deletepage-list-translation' => 'Pâges de traduccion',
	'pt-deletepage-list-section' => 'Pâges de sèccion', # Fuzzy
	'pt-deletepage-list-other' => 'Ôtres sot-pâges',
	'pt-deletepage-list-count' => 'En tot $1 pâge{{PLURAL:$1||s}} a suprimar.',
	'pt-deletepage-full-logreason' => 'Partia de la pâge traduisibla « $1 ».',
	'pt-deletepage-lang-logreason' => 'Partia de la pâge de traduccion « $1 ».',
);

/** Friulian (furlan)
 * @author Klenje
 */
$messages['fur'] = array(
	'translate-tag-translate-link-desc' => 'Tradûs cheste pagjine',
	'tpt-languages-legend' => 'Altris lenghis:',
	'tpt-aggregategroup-save' => 'Salve',
	'tpt-aggregategroup-new-name' => 'Non:',
	'pt-movepage-reason' => 'Reson:',
);

/** Irish (Gaeilge)
 * @author පසිඳු කාවින්ද
 */
$messages['ga'] = array(
	'tpt-select-prioritylangs-reason' => 'Fáth:',
	'tpt-aggregategroup-save' => 'Sábháil',
	'tpt-aggregategroup-new-name' => 'Ainm:',
	'pt-deletepage-reason' => 'Fáth:',
);

/** Galician (galego)
 * @author Toliño
 */
$messages['gl'] = array(
	'pagetranslation' => 'Tradución de páxinas',
	'right-pagetranslation' => 'Marcar as versións de páxinas para seren traducidas',
	'action-pagetranslation' => 'administrar as páxinas traducibles',
	'tpt-desc' => 'Extensión para traducir contidos de páxinas',
	'tpt-section' => 'Unidade de tradución $1',
	'tpt-section-new' => 'Nova unidade de tradución. Nome: $1',
	'tpt-section-deleted' => 'Unidade de tradución $1',
	'tpt-template' => 'Modelo de páxina',
	'tpt-templatediff' => 'Cambiou o modelo de páxina.',
	'tpt-diff-old' => 'Texto anterior',
	'tpt-diff-new' => 'Texto novo',
	'tpt-submit' => 'Marcar esta versión para ser traducida',
	'tpt-sections-oldnew' => 'Unidades de tradución novas e existentes',
	'tpt-sections-deleted' => 'Unidades de tradución borradas',
	'tpt-sections-template' => 'Modelo de páxina de tradución',
	'tpt-action-nofuzzy' => 'Non invalidar as traducións',
	'tpt-badtitle' => 'O nome de páxina dado ("$1") non é un título válido',
	'tpt-nosuchpage' => 'A páxina "$1" non existe',
	'tpt-oldrevision' => '$2 non é a última versión da páxina "[[$1]]".
Só as últimas versións poden ser marcadas para seren traducidas.',
	'tpt-notsuitable' => 'A páxina "$1" non é válida para ser traducida.
Comprobe que teña as etiquetas <nowiki><translate></nowiki> e mais unha sintaxe válida.',
	'tpt-saveok' => 'A páxina "[[$1]]" foi marcada para ser traducida, {{PLURAL:$2|cunha unidade de tradución|con $2 unidades de tradución}}.
A páxina agora pode ser <span class="plainlinks">[$3 traducida]</span>.',
	'tpt-offer-notify' => 'Pode <span class="plainlinks">[$1 notificar aos tradutores]</span> sobre esta páxina.',
	'tpt-badsect' => '"$1" non é un nome válido para a unidade de tradución $2.',
	'tpt-showpage-intro' => 'A continuación están listadas as unidades de tradución novas, existentes e borradas.
Antes de marcar esta versión para ser traducida, comprobe que as modificacións feitas ás unidades de tradución foron minimizadas para evitarlles traballo innecesario aos tradutores.',
	'tpt-mark-summary' => 'Marcou esta versión para ser traducida',
	'tpt-edit-failed' => 'Non se puido actualizar a páxina: $1',
	'tpt-duplicate' => 'O nome da unidade de tradución "$1" úsase máis dunha vez.',
	'tpt-already-marked' => 'A última versión desta páxina xa foi marcada para ser traducida.',
	'tpt-unmarked' => 'A páxina "$1" xa non está marcada para traducir.',
	'tpt-list-nopages' => 'Non hai ningunha páxina marcada para ser traducida, nin preparada para ser marcada para ser traducida.',
	'tpt-new-pages-title' => 'Páxinas propostas para a súa tradución',
	'tpt-old-pages-title' => 'Páxinas en tradución',
	'tpt-other-pages-title' => 'Páxinas rotas',
	'tpt-discouraged-pages-title' => 'Páxinas rexeitadas',
	'tpt-new-pages' => '{{PLURAL:$1|Esta páxina contén|Estas páxinas conteñen}} texto con etiquetas de tradución, pero ningunha versión {{PLURAL:$1|desta páxina|destas páxinas}} está actualmente marcada para ser traducida.',
	'tpt-old-pages' => 'Algunha versión {{PLURAL:$1|desta páxina|destas páxinas}} foi marcada para ser traducida.',
	'tpt-other-pages' => '{{PLURAL:$1|Hai marcada para traducir unha a versión vella desta páxina|Hai marcadas para traducir algunhas versións vellas destas páxinas}}, pero {{PLURAL:$1|a última versión|as últimas versións}} non se {{PLURAL:$1|pode|poden}} marcar.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Esta páxina foi rexeitada|Estas páxinas foron rexeitadas}} e xa non se solicita a súa tradución.',
	'tpt-select-prioritylangs' => 'Lista dos códigos das linguas prioritarias (separados por comas):',
	'tpt-select-prioritylangs-force' => 'Impedir as traducións noutras linguas que non sexan as prioritarias',
	'tpt-select-prioritylangs-reason' => 'Motivo:',
	'tpt-sections-prioritylangs' => 'Linguas prioritarias',
	'tpt-rev-mark' => 'marcar para traducir',
	'tpt-rev-unmark' => 'eliminar da tradución',
	'tpt-rev-discourage' => 'rexeitar',
	'tpt-rev-encourage' => 'restaurar',
	'tpt-rev-mark-tooltip' => 'Marcar a última versión desta páxina para a súa tradución.',
	'tpt-rev-unmark-tooltip' => 'Eliminar esta páxina da tradución.',
	'tpt-rev-discourage-tooltip' => 'Rexeitar máis traducións desta páxina.',
	'tpt-rev-encourage-tooltip' => 'Restaurar esta páxina á tradución normal.',
	'translate-tag-translate-link-desc' => 'Traducir esta páxina',
	'translate-tag-markthis' => 'Marcar esta páxina para a súa tradución',
	'translate-tag-markthisagain' => 'Esta páxina sufriu <span class="plainlinks">[$1 cambios]</span> desde que foi <span class="plainlinks">[$2 marcada para a súa tradución]</span> por última vez.',
	'translate-tag-hasnew' => 'Esta páxina contén <span class="plainlinks">[$1 cambios]</span> que non están marcados para a súa tradución.',
	'tpt-translation-intro' => 'Esta páxina é unha <span class="plainlinks">[$1 versión traducida]</span> da páxina "[[$2]]" e a tradución está completada e actualizada ao $3%.',
	'tpt-languages-legend' => 'Outras linguas:',
	'tpt-languages-zero' => 'Comezar a tradución nesta lingua',
	'tpt-tab-translate' => 'Traducir',
	'tpt-target-page' => 'Esta páxina non se pode actualizar manualmente.
Esta páxina é unha tradución da páxina "[[$1]]" e a tradución pódese actualizar usando [$2 a ferramenta de tradución].',
	'tpt-unknown-page' => 'Este espazo de nomes está reservado para traducións de páxinas de contido.
A páxina que está intentando editar parece non corresponder a algunha páxina marcada para ser traducida.',
	'tpt-translation-restricted' => 'Un administrador de traducións impediu a tradución da páxina nesta lingua.

Motivo: $1',
	'tpt-discouraged-language-force' => 'Un administrador de traducións limitou as linguas nas que se pode traducir a páxina. Esta lingua non está entre elas.

Motivo: $1',
	'tpt-discouraged-language' => 'Esta lingua non está entre as linguas prioritarias que un administrador definiu para a páxina.

Motivo: $1',
	'tpt-discouraged-language-reason' => 'Motivo: $1',
	'tpt-priority-languages' => 'Un administrador de traducións definiu as linguas prioritarias deste grupo a $1.',
	'tpt-render-summary' => 'Actualizando para coincidir coa nova versión da páxina de orixe',
	'tpt-download-page' => 'Exportar a páxina coas traducións',
	'aggregategroups' => 'Grupos de agregación',
	'tpt-aggregategroup-add' => 'Engadir',
	'tpt-aggregategroup-save' => 'Gardar',
	'tpt-aggregategroup-add-new' => 'Engadir un novo grupo de agregación',
	'tpt-aggregategroup-new-name' => 'Nome:',
	'tpt-aggregategroup-new-description' => 'Descrición (opcional):',
	'tpt-aggregategroup-remove-confirm' => 'Está seguro de querer borrar o grupo de agregación?',
	'tpt-aggregategroup-invalid-group' => 'O grupo non existe',
	'pt-parse-open' => 'Etiqueta &lt;translate> desequilibrada.
Modelo de tradución: <pre>$1</pre>',
	'pt-parse-close' => 'Etiqueta &lt;/translate> desequilibrada.
Modelo de tradución: <pre>$1</pre>',
	'pt-parse-nested' => 'Non se permiten as unidades de tradución &lt;translate> aniñadas.
Texto da etiqueta: <pre>$1</pre>',
	'pt-shake-multiple' => 'Hai demasiados marcadores de unidade de tradución para unha única unidade.
Texto da unidade de tradución: <pre>$1</pre>',
	'pt-shake-position' => 'Os marcadores de unidade de tradución atópanse nunha posición inesperada.
Texto da unidade de tradución: <pre>$1</pre>',
	'pt-shake-empty' => 'Unidade de tradución baleira para o marcador "$1".',
	'log-description-pagetranslation' => 'Rexistro de accións e operacións relacionadas co sistema de tradución de páxinas',
	'log-name-pagetranslation' => 'Rexistro de páxinas de tradución',
	'logentry-pagetranslation-mark' => '$1 {{GENDER:$2|marcou}} "$3" para a súa tradución',
	'logentry-pagetranslation-unmark' => '$1 {{GENDER:$2|eliminou}} "$3" das páxinas para traducir',
	'logentry-pagetranslation-moveok' => '$1 {{GENDER:$2|completou}} o cambio de nome da páxina traducible "$3" a "$4"',
	'logentry-pagetranslation-movenok' => '$1 {{GENDER:$2|atopou}} un problema ao mover a páxina "$3" a "$4"',
	'logentry-pagetranslation-deletefok' => '$1 {{GENDER:$2|completou}} o borrado da páxina traducible "$3"',
	'logentry-pagetranslation-deletefnok' => '$1 {{GENDER:$2|tivo}} un problema ao borrar "$3", que pertence á páxina traducible "$4"',
	'logentry-pagetranslation-deletelok' => '$1 {{GENDER:$2|completou}} o borrado da páxina de tradución "$3"',
	'logentry-pagetranslation-deletelnok' => '$1 {{GENDER:$2|tivo}} un problema ao borrar "$3", que pertence á páxina de tradución "$4"',
	'logentry-pagetranslation-encourage' => '$1 {{GENDER:$2|estimulou}} a tradución de "$3"',
	'logentry-pagetranslation-discourage' => '$1 {{GENDER:$2|desalentou}} a tradución de "$3"',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1 {{GENDER:$2|eliminou}} as linguas prioritarias da páxina traducible "$3"',
	'logentry-pagetranslation-prioritylanguages' => '$1 {{GENDER:$2|definiu}} as linguas prioritarias da páxina traducible "$3" a $5',
	'logentry-pagetranslation-prioritylanguages-force' => '$1 {{GENDER:$2|limitou}} as linguas da páxina traducible "$3" a $5',
	'logentry-pagetranslation-associate' => '$1 {{GENDER:$2|engadiu}} a páxina traducible "$3" ao grupo de agregación "$4"',
	'logentry-pagetranslation-dissociate' => '$1 {{GENDER:$2|eliminou}} a páxina traducible "$3" do grupo de agregación "$4"',
	'pt-movepage-title' => 'Mover a páxina traducible "$1"',
	'pt-movepage-blockers' => 'Non se pode trasladar a páxina traducible a un novo nome debido {{PLURAL:$1|ao seguinte erro|aos seguintes erros}}:',
	'pt-movepage-block-base-exists' => 'Existe a páxina traducible de destino "[[:$1]]".',
	'pt-movepage-block-base-invalid' => 'O nome da páxina traducible de destino ten un título incorrecto.',
	'pt-movepage-block-tp-exists' => 'Existe a páxina de tradución de destino "[[:$2]]".',
	'pt-movepage-block-tp-invalid' => 'O título da páxina de tradución de destino para "[[:$1]]" é incorrecto (quizais sexa longo de máis).',
	'pt-movepage-block-section-exists' => 'Existe a páxina de destino "[[:$2]]" para a unidade de tradución.',
	'pt-movepage-block-section-invalid' => 'O título da páxina de destino para "[[:$1]]" para a unidade de tradución é incorrecto (quizais sexa longo de máis).',
	'pt-movepage-block-subpage-exists' => 'Existe a subpáxina de destino "[[:$2]]".',
	'pt-movepage-block-subpage-invalid' => 'O título da subpáxina de destino para "[[:$1]]" é incorrecto (quizais sexa longo de máis).',
	'pt-movepage-list-pages' => 'Lista de páxinas a mover',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Páxina|Páxinas}} de tradución',
	'pt-movepage-list-section' => '{{PLURAL:$1|Páxina|Páxinas}} de unidade de tradución',
	'pt-movepage-list-other' => '{{PLURAL:$1|Outra subpáxina|Outras subpáxinas}}',
	'pt-movepage-list-count' => 'En total, $1 {{PLURAL:$1|páxina|páxinas}} a mover.',
	'pt-movepage-legend' => 'Mover a páxina traducible',
	'pt-movepage-current' => 'Nome actual:',
	'pt-movepage-new' => 'Novo nome:',
	'pt-movepage-reason' => 'Motivo:',
	'pt-movepage-subpages' => 'Mover todas as subpáxinas',
	'pt-movepage-action-check' => 'Comprobar se o traslado é posible',
	'pt-movepage-action-perform' => 'Realizar o traslado',
	'pt-movepage-action-other' => 'Cambiar o destino',
	'pt-movepage-intro' => 'Esta páxina especial permite mover páxinas que están marcadas para a súa tradución.
A acción de traslado non será inmediata porque é necesario mover moitas outras páxinas.
Mentres as páxinas son trasladadas, non é posible traballar nelas.
Os erros quedarán rexistrados no [[Special:Log/pagetranslation|rexistro de páxinas de tradución]] e deberán ser reparados manualmente.',
	'pt-movepage-logreason' => 'Parte da páxina traducible "$1".',
	'pt-movepage-started' => 'Estase a mover a páxina base.
Comprobe o [[Special:Log/pagetranslation|rexistro de páxinas de tradución]] por se houbese algún erro e para ler as mensaxes de conclusión.',
	'pt-locked-page' => 'Esta páxina está bloqueada porque se está a mover a páxina traducible.',
	'pt-deletepage-lang-title' => 'Borrar a páxina de tradución "$1".',
	'pt-deletepage-full-title' => 'Borrar a páxina traducible "$1".',
	'pt-deletepage-invalid-title' => 'A páxina especificada non é válida.',
	'pt-deletepage-invalid-text' => 'A páxina especificada non é unha páxina traducible nin unha páxina de tradución.',
	'pt-deletepage-action-check' => 'Lista de páxinas a borrar',
	'pt-deletepage-action-perform' => 'Realizar o borrado',
	'pt-deletepage-action-other' => 'Cambiar o destino',
	'pt-deletepage-lang-legend' => 'Borrar a páxina de tradución',
	'pt-deletepage-full-legend' => 'Borrar a páxina traducible',
	'pt-deletepage-any-legend' => 'Borrar a páxina traducible ou a páxina de tradución',
	'pt-deletepage-current' => 'Nome da páxina:',
	'pt-deletepage-reason' => 'Motivo:',
	'pt-deletepage-subpages' => 'Borrar todas as subpáxinas',
	'pt-deletepage-list-pages' => 'Lista de páxinas a borrar',
	'pt-deletepage-list-translation' => 'Páxinas de tradución',
	'pt-deletepage-list-section' => 'Páxinas de unidade de tradución',
	'pt-deletepage-list-other' => 'Outras subpáxinas',
	'pt-deletepage-list-count' => 'En total, $1 {{PLURAL:$1|páxina|páxinas}} a borrar.',
	'pt-deletepage-full-logreason' => 'Parte da páxina traducible "$1".',
	'pt-deletepage-lang-logreason' => 'Parte da páxina de tradución "$1".',
	'pt-deletepage-started' => 'Comprobe os erros e as mensaxes de conclusión no [[Special:Log/pagetranslation|rexistro de páxinas de tradución]].',
	'pt-deletepage-intro' => 'Esta páxina especial permite borrar por completo páxinas traducibles ou páxinas de tradución individuais nunha lingua.
A acción de borrado non será inmediata porque cómpre eliminar todas as páxinas dependentes delas.
Os erros quedarán rexistrados no [[Special:Log/pagetranslation|rexistro de páxinas de tradución]] e terán que arranxarse manualmente.',
);

/** Swiss German (Alemannisch)
 * @author Als-Chlämens
 * @author Als-Holder
 * @author Purodha
 */
$messages['gsw'] = array(
	'pagetranslation' => 'Sytenibersetzig',
	'right-pagetranslation' => 'D Syte, wu sotte ibersetzt wäre, markiere',
	'action-pagetranslation' => 'ibersetzbari Syte z verwalte',
	'tpt-desc' => 'Erwyterig fir d Iberstzig vu Inhaltssyte',
	'tpt-section' => 'Iberstzigs-Abschnitt $1',
	'tpt-section-new' => 'Neje Iberstzigs-Abschnitt. Name: $1',
	'tpt-section-deleted' => 'Ibersetzigs-Abschnitt $1',
	'tpt-template' => 'Sytevorlag',
	'tpt-templatediff' => 'D Sytevorlag het sich gänderet.',
	'tpt-diff-old' => 'Vorige Tekscht',
	'tpt-diff-new' => 'Neje Tekscht',
	'tpt-submit' => 'Die Version zum Ibersetze markiere',
	'tpt-sections-oldnew' => 'Neji un vorhandeni Ibersetzigs-Abschnitt',
	'tpt-sections-deleted' => 'Gleschti Ibersetzigs-Abschnitt',
	'tpt-sections-template' => 'Ibersetzigs-Sytevorlag',
	'tpt-action-nofuzzy' => 'Setz d Ibersetzige nit usser Chraft',
	'tpt-badtitle' => 'Dr Sytename, wu Du aagee hesch ($1), isch kei giltige Sytename',
	'tpt-nosuchpage' => 'D Syte $1 git s nit',
	'tpt-oldrevision' => '$2 isch nit di letscht Version vu dr Syte [[$1]].
Nume di letschte Versione chenne zum Iberseze markiert wäre.',
	'tpt-notsuitable' => 'D Syte $1 cha nit iberstez wäre.
Stell sicher, ass si <nowiki><translate></nowiki>-Markierige un e giltige Syntax het.',
	'tpt-saveok' => 'D Syte [[$1]] isch zum Ibersetze markiert wore mit $2 {{PLURAL:$2|Ibersetzigs-Abschnitt|Ibersetzigs-Abschnitt}}.
D Syte cha jetz <span class="plainlinks">[$3 ibersetzt]</span> wäre.',
	'tpt-badsect' => '"$1" isch kei giltige Name fir dr Iberstzigs-Abschnitt $2.',
	'tpt-showpage-intro' => 'Unte sin Abschnitt ufglischtet, wu nej sin, sonigi wu s git un sonigi wu s nit git.
Voreb Du die Version zum Ibersetze frejgisch, iberprief, ass d Änderige an dr Abschnitt gring ghalte sin go uunetigi Arbed bi dr Ibersetzig vermyde.',
	'tpt-mark-summary' => 'het die Versione zum Ibersetze markiert',
	'tpt-edit-failed' => 'Cha d Syte nit aktualisiere: $1',
	'tpt-duplicate' => 'Dr Ibersetzigseinheitsname $1 wird meh wie eimol brucht.',
	'tpt-already-marked' => 'Di letscht Version vu däre Syte isch scho zum Ibersetze markiert wore.',
	'tpt-unmarked' => 'D Syte $1 isch nit lenger markiert, ass sie mueß ibersetzt wäre.',
	'tpt-list-nopages' => 'S sin kei Syte zum Ibersetze markiert wore un sin au no keini Syte fertig, wu chennte zum Ibersetze markiert wäre',
	'tpt-new-pages-title' => 'Fir e Ibersetzig vorgschlaani Syte',
	'tpt-old-pages-title' => 'Z Ibersetze',
	'tpt-other-pages-title' => 'Fählerhafti Syte',
	'tpt-discouraged-pages-title' => 'Zruckzoge',
	'tpt-new-pages' => '{{PLURAL:$1|In däre Syte|In däne Syte}} het s Tekscht mit Ibersetzigs-Markierige, aber zur Zyt isch kei Version {{PLURAL:$1|däre Syte|däne Syte}} zum Ibersetze markiert.',
	'tpt-old-pages' => '{{PLURAL:$1|E Version vu däre Syte isch|E paar Versione vu däne Syte sin}} zum Ibersetze markiert wore',
	'tpt-other-pages' => '{{PLURAL:$1|En alti Version vu däre Syte isch markiert, ass si mueß|Alti Versione vu däne Syte sin markiert, ass si mien}} ibersetzt wäre.
Di {{PLURAL:$1|nejscht Version cha dergege nit markiert wäre, ass si mueß|nejschte Versione chenne dergege nit markiert wäre, ass sin mien}} ibersetzt wäre.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Die Syte isch|Die Syten sin}} vu dr Ibersetzig zruckzoge wore.',
	'tpt-select-prioritylangs' => 'Komma-trännti Lischt vu dr priorisirte Sprochcode:',
	'tpt-select-prioritylangs-force' => 'Ibersetzige in nit priorisierti Sproche verhindere', # Fuzzy
	'tpt-select-prioritylangs-reason' => 'Grund:',
	'tpt-sections-prioritylangs' => 'Priorisierti Sproche',
	'tpt-rev-mark' => 'Zum Ibersetze freigee',
	'tpt-rev-unmark' => 'die Syte vum Ibersetze zruckneh',
	'tpt-rev-discourage' => 'Frejgab zrucksetze',
	'tpt-rev-encourage' => 'widerhärstelle',
	'tpt-rev-mark-tooltip' => 'Di letscht Version vu däre Syte zum Ibersetze frejgee.',
	'tpt-rev-unmark-tooltip' => 'D Frejgab zum Ibersetze vu dä#re Syte uuseneh.',
	'tpt-rev-discourage-tooltip' => 'D Frejgab fir wyteri Ibersetzige vu däre Syte zruckzie.',
	'tpt-rev-encourage-tooltip' => 'D Frejgab zum Ibersetze vu däre Syre widerhärstelle.',
	'translate-tag-translate-link-desc' => 'Die Syte ibersetze',
	'translate-tag-markthis' => 'Die Syte zum ibersetze markiere',
	'translate-tag-markthisagain' => 'An däre Syte het s <span class="plainlinks">[$1 Änderige]</span> gee, syt si s lescht Mol <span class="plainlinks">[$2 zum Ibersetze markiert wore isch]</span>.',
	'translate-tag-hasnew' => 'In däre Syte het s <span class="plainlinks">[$1 Änderige]</span>, wu nit zum Ibersetze markiert sin.',
	'tpt-translation-intro' => 'Die Syte isch e <span class="plainlinks">[$1 ibersetzti Version]</span> vun ere Syte [[$2]] un d Ibersetzig isch zue $3% vollständig un aktuäll.',
	'tpt-languages-legend' => 'Anderi Sproche:',
	'tpt-languages-zero' => 'Mit em Ibersetze in die Sproch aafange',
	'tpt-target-page' => 'Die Syte cha nit vu Hand aktualisiert wäre.
Die Syte isch e Ibersetzig vu dr Syte [[$1]] un d Ibersetzig cha aktualisert wäre mit em [$2 Ibersetzigstool].',
	'tpt-unknown-page' => 'Dää Namensruum isch reserviert fir Ibersetzige vu Inhaltssyte.
D Syte, wu Du witt bearbeite, ghert schyns zue keire Syte, wu zum Ibersetze markiert isch.',
	'tpt-translation-restricted' => 'S Ibersetze vu däre Syte in die Sproch isch vun eme Ibersetzigsadministrator deaktiviert wore.

Grund: $1',
	'tpt-discouraged-language-force' => "'''Die Syte cha nit in $2 ibersetzt wäre.'''

En Ibersetzigsadministrator het entschide, dass die Syte nume in $3 cha ibersetzt wäre.",
	'tpt-discouraged-language' => "'''En Ibersetzig in $2 isch kei Prioritet vu däre Syte.'''

En Ibersetzigsadministrator het entschide, dass die Syte vor allem in $3 sott ibersetzt wäre.",
	'tpt-discouraged-language-reason' => 'Grund: $1',
	'tpt-priority-languages' => 'En Ibersetzigsadministrator het di priorisierte Sproche fir die Nochrichtegruppe uf $1 feschtgleit.',
	'tpt-render-summary' => 'Aktualisiere zum e neji Version vu dr Quällsyte z finde',
	'tpt-download-page' => 'Syte mit Ibersetzige exportiere',
	'aggregategroups' => 'Sammelgruppe',
	'tpt-aggregategroup-add' => 'Zuefiege',
	'tpt-aggregategroup-save' => 'Spychere',
	'tpt-aggregategroup-add-new' => 'E neji Hauptnochrichtegruppe zuefiege',
	'tpt-aggregategroup-new-name' => 'Name:',
	'tpt-aggregategroup-new-description' => 'Bschrybig (optional):',
	'tpt-aggregategroup-remove-confirm' => 'Bisch sicher, ass Du die Gruppe witt lesche?',
	'tpt-aggregategroup-invalid-group' => 'Gruppe git s nit',
	'pt-parse-open' => 'Uasymmetrischi &lt;translate&gt;-Markierig.
Ibersetzigsvorlag: <pre>$1</pre>',
	'pt-parse-close' => 'Uusymmetrischi &lt;&#47;translate&gt;-Markierig.
Ibersetzigsvorlag: <pre>$1</pre>',
	'pt-parse-nested' => 'Verschachtleti &lt;translate&gt;-Ibersetzigseinheite sin nit megli.
Text vu dr Markierig: <pre>$1</pre>',
	'pt-shake-multiple' => 'Mehreri Ibersetzigseinheitesmarker fir ei Ibersetzigseinheit.
Text vu drIbersetzigseinheit: <pre>$1</pre>',
	'pt-shake-position' => 'S het Ibersetzigseinheitemarker an ere nit erwartete Stell.
Text vu dr Ibersetzigseinheit: <pre>$1</pre>',
	'pt-shake-empty' => 'Ibersetzigseinheit fir dr Marker„$1“ isch läär.',
	'log-description-pagetranslation' => 'Logbuech vu dr Änderige im Zämmehang mit em Ibersetzigssyschtem',
	'log-name-pagetranslation' => 'Sytenibersetzigs-Logbuech',
	'pt-movepage-title' => 'D Ibersetzigssyte $1 verschiebe',
	'pt-movepage-blockers' => 'Di ibersetzbar Syte het wäge {{PLURAL:$1|däm Fähler|däne Fähler}} nit nit uf dr nej Name chenne verschobe wäre:',
	'pt-movepage-block-base-exists' => 'D Basissyte [[:$1]] git s scho.', # Fuzzy
	'pt-movepage-block-base-invalid' => 'D Basissyte het kei giltige Name.', # Fuzzy
	'pt-movepage-block-tp-exists' => 'D Ibersetzigssyte [[:$2]] git s scho.',
	'pt-movepage-block-tp-invalid' => 'Dr Ziilname vu dr Ibersetzigssyte fir [[:$1]] wär nit giltig (z lang?).',
	'pt-movepage-block-section-exists' => 'D Syte [[:$2]] zue dr Ibersetzigseinheit git s scho.',
	'pt-movepage-block-section-invalid' => 'Dr Ziilname vu dr Ibersetzigseinheitesyte fir [[:$1]] wär nit giltig (z lang?).',
	'pt-movepage-block-subpage-exists' => 'D Untersyte [[:$2]] git s scho.',
	'pt-movepage-block-subpage-invalid' => 'Dr Ziilname vu dr Untersyte fir [[:$1]] wär nit giltig (z lang?).',
	'pt-movepage-list-pages' => 'Lischt vu dr Syte, wu mien verschobe wäre',
	'pt-movepage-list-translation' => 'Ibersetzigssyte', # Fuzzy
	'pt-movepage-list-section' => 'Syte vu dr Ibersetzigseinheite', # Fuzzy
	'pt-movepage-list-other' => 'Anderi Untersyte', # Fuzzy
	'pt-movepage-list-count' => 'Insgsamt git s $1 Syte, wu {{PLURAL:$1|mueß|mien}} verschobe wäre.',
	'pt-movepage-legend' => 'Ibersetzigssyte verschiebe',
	'pt-movepage-current' => 'Aktuälle Sytename:',
	'pt-movepage-new' => 'Neje Sytename:',
	'pt-movepage-reason' => 'Grund:',
	'pt-movepage-subpages' => 'Alli Untersyte verschiebe',
	'pt-movepage-action-check' => 'Iberpriefig, eb d Verschiebig megli isch',
	'pt-movepage-action-perform' => 'Verschiebig durfiere',
	'pt-movepage-action-other' => 'Ziil ändere',
	'pt-movepage-intro' => 'Die Spezialsyte macht s megli Syte z verschiebe, wu fir d Ibersetzig zeichnet sin.
D Verschiebig chunnt nit sofort, wel vil Syte derby mien verschobe wäre.
Bim Verschiebigsvorgang isch s nit megli, die Syte z nutze.
Verschiebigsfähler wäre im [[Special:Log/pagetranslation|Ibersetzigs-Logbuech]] ufzeichnet un mien vu Hand verbesseret wäre.',
	'pt-movepage-logreason' => 'Teil vu dr Ibersetzigssyte $1.',
	'pt-movepage-started' => 'D Basissyte isch jetz verschobe wore.
Bitte prief s [[Special:Log/pagetranslation|Ibersetzigs-Logbuech]] uf Fählermäldige un d Vollzugsnochricht.',
	'pt-locked-page' => 'Die Syte isch gsperrt, wel d Ibersetzigssyte zurzyt verschobe wird.',
	'pt-deletepage-lang-title' => 'Übersetzigs-Syte $1 wird glöscht',
	'pt-deletepage-full-title' => 'Übersetzbari Syte $1 wird glöscht.',
	'pt-deletepage-invalid-title' => 'Die Syte, wo aagee hesch, isch nit gültig.',
	'pt-deletepage-invalid-text' => 'Die Syte, wo aagee hesch, isch weder e übersetzbari Syte, noch e Übersetzig.', # Fuzzy
	'pt-deletepage-action-check' => 'Syte ufflischte, wo glöscht werde sölle',
	'pt-deletepage-action-perform' => 'Löschig durefiere',
	'pt-deletepage-action-other' => 'Ziil ändere',
	'pt-deletepage-lang-legend' => 'Ibersetzigs-Syte lesche',
	'pt-deletepage-full-legend' => 'Ibersetzbari Syte lesche',
	'pt-deletepage-any-legend' => 'Übersetzbari oder übersetzti Syte lösche', # Fuzzy
	'pt-deletepage-current' => 'Sytename:',
	'pt-deletepage-reason' => 'Grund:',
	'pt-deletepage-subpages' => 'Alli Untersyte lösche',
	'pt-deletepage-list-pages' => 'Lischt vu dr Syte, wu mien glöscht wäre',
	'pt-deletepage-list-translation' => 'Ibersetzigssyte',
	'pt-deletepage-list-section' => 'Syte vu dr Ibersetzigseinheite',
	'pt-deletepage-list-other' => 'Anderi Untersyte',
	'pt-deletepage-list-count' => 'Insgsamt git s $1 Syte, wu {{PLURAL:$1|mueß|mien}} glöscht wäre.',
	'pt-deletepage-full-logreason' => 'Teil vu dr übersetzbare Syte $1.',
	'pt-deletepage-lang-logreason' => 'Teil vu dr übersetzte Syte $1.',
	'pt-deletepage-started' => 'Bitte due s [[Special:Log/pagetranslation|Übersetzigs-Logbuech]] uff Fääler un Ussfierigsnoochrichte überpriefe.',
	'pt-deletepage-intro' => 'Die Spezialsyte ermöglicht s Lösche vo ganze übersetbare Syte oder Übersetzige in ei Sprooch.
D Ussfierig vo Löschig passiert nit unmittelbar, wyl vili Syte übersetzt werde mien.
Fääler werde im [[Special:Log/pagetranslation|Übersetzigs-Logbuech]] uffzeichnet un mien noochträgli manuell berichtigt werde.', # Fuzzy
);

/** Gujarati (ગુજરાતી)
 * @author Ashok modhvadia
 * @author Dsvyas
 */
$messages['gu'] = array(
	'pagetranslation' => 'પાનું ભાષાંતરણ',
	'right-pagetranslation' => 'ભાષાંતર માટેનાં પાનાઓનાં સંસ્કરણો ચિહ્નિત કરો',
	'tpt-section' => 'ભાષાંતર એકમ $1',
	'tpt-section-new' => 'નવું ભાષાંતર એકમ. નામ: $1',
	'tpt-section-deleted' => 'ભાષાંતર એકમ $1',
	'tpt-template' => 'પાનાં ઢાંચો',
	'tpt-templatediff' => 'પાનાંનો ઢાંચો બદલાયો છે.',
	'tpt-diff-old' => 'પહેલાંનું લખાણ',
	'tpt-diff-new' => 'નવું લખાણ',
	'tpt-submit' => 'આ સંસ્કરણને ભાષાંતર માટે ચિહ્નિત કરો',
	'tpt-sections-oldnew' => 'નવાં અને વિદ્યમાન ભાષાંતર એકમો',
	'tpt-sections-deleted' => 'રદ કરાયેલા ભાષાંતર એકમો',
	'tpt-sections-template' => 'ભાષાંતર પાના ઢાંચો',
	'tpt-badtitle' => 'પાનાને અપાયેલું ($1) નામ પ્રમાણભૂત મથાળું નથી',
	'tpt-oldrevision' => '$2 એ પાનાં [[$1]] નું આધુનિક સંસ્કરણ નથી.

ફક્ત આધુનિક સંસ્કરણનેજ ભાષાંતર માટે ચિહ્નિત કરી શકાશે.',
	'tpt-notsuitable' => 'પાનું $1 ભાષાંતર માટે યોગ્ય નથી.

ખાતરી કરો કે તે <nowiki><translate></nowiki> ટેગ અને પ્રમાણભૂત વાક્યરચના ધરાવે છે.',
	'tpt-badsect' => '"$1" એ ભાષાંતર એકમ $2 માટેનું પ્રમાણભૂત નામ નથી.',
	'tpt-mark-summary' => 'આ સંસ્કરણને ભાષાંતર માટે ચિહ્નિત કરાયું',
	'tpt-edit-failed' => 'પાનાં: $1 ને અદ્યતન બનાવી શકાયું નહીં.',
	'tpt-already-marked' => 'આ પાનાનું આધુનિક સંસ્કરણ અગાઉથીજ ભાષાંતર માટે ચિહ્નિત થઇ ચુક્યું છે.',
	'tpt-list-nopages' => 'કોઈ પાનાં ભાષાંતર માટે ચિહ્નિત કરેલા નથી કે ન તો કોઈ પાનું ભાષાંતર માટે ચિહ્નિત થવા માટે તૈયાર છે.',
	'tpt-new-pages' => '{{PLURAL:$1|આ પાના|આ પાનાઓ}} ભાષાંતર ટેગ શાથેનું લખાણ ધરાવે છે, પરંતુ {{PLURAL:$1|આ પાના|આ પાનાઓ}}નું હાલનું સંસ્કરણ ભાષાંતર માટે ચિહ્નિત કરાયેલ નથી.',
	'tpt-old-pages' => '{{PLURAL:$1|આ પાના|આ પાનાં}}નાં કેટલાક સંસ્કરણ ભાષાંતર માટે ચિહ્નિત કરાયેલા છે.',
	'translate-tag-translate-link-desc' => 'આ પાનાનું ભાષાંતર કરો',
	'translate-tag-markthis' => 'આ પાનાંને ભાષાંતર માટે ચિહ્નિત કરો',
	'tpt-languages-legend' => 'અન્ય ભાષાઓ:',
);

/** Manx (Gaelg)
 * @author Shimmin Beg
 */
$messages['gv'] = array(
	'pt-movepage-reason' => 'Fa:',
);

/** Hausa (Hausa)
 */
$messages['ha'] = array(
	'pt-movepage-reason' => 'Dalili:',
);

/** Hebrew (עברית)
 * @author Amire80
 * @author Deror avi
 * @author Inkbug
 * @author Rotemliss
 * @author YaronSh
 */
$messages['he'] = array(
	'pagetranslation' => 'תרגום דפים',
	'right-pagetranslation' => 'סימון גרסאות של הדפים לתרגום',
	'action-pagetranslation' => 'לנהל דפים שאפשר לתרגם',
	'tpt-desc' => 'הרחבה לתרגום דפי תוכן',
	'tpt-section' => 'יחידת תרגום $1',
	'tpt-section-new' => 'יחידת תרגום חדשה.
שם: $1',
	'tpt-section-deleted' => 'יחידת תרגום $1',
	'tpt-template' => 'תבנית הדף',
	'tpt-templatediff' => 'תבנית הדף שונתה.',
	'tpt-diff-old' => 'הטקסט הקודם',
	'tpt-diff-new' => 'טקסט חדש',
	'tpt-submit' => 'סימון גרסה זו לתרגום',
	'tpt-sections-oldnew' => 'יחידות תרגום חדשות וקיימות',
	'tpt-sections-deleted' => 'יחידות תרגום שנמחקו',
	'tpt-sections-template' => 'תבנית דף תרגום',
	'tpt-action-nofuzzy' => 'לא לפסול תרגומים',
	'tpt-badtitle' => 'שם הדף שניתן ($1) אינו כותרת תקינה',
	'tpt-nosuchpage' => 'הדף $1 אינו קיים',
	'tpt-oldrevision' => '$2 היא לא הגרסה האחרונה של הדף [[$1]].
רק הגרסאות האחרונות יכולות להיות מסומנות לתרגום.',
	'tpt-notsuitable' => 'הדף $1 אינו מתאים לתרגום.
אנא ודאו שהוא מכיל תגיות <nowiki><translate></nowiki> ושהתחביר שלו תקין.',
	'tpt-saveok' => 'הדף [[$1]] סומן לתרגום עם {{PLURAL:$2|יחידת תרגום אחת|$2 יחידות תרגום}}.
עכשיו אפשר <span class="plainlinks">[$3 לתרגם]</span> את הדף.',
	'tpt-offer-notify' => 'באפשרותך <span class="plainlinks">[$1 להודיע למתרגמים]</span> על הדף הזה.',
	'tpt-badsect' => 'השם "$1" אינו שם תקין ליחידת התרגום $2.',
	'tpt-showpage-intro' => 'להלן רשימת יחידות תרגום חדשות, קיימות ומחוקות.
לפני סימון גרסה זו לתרגום, בדקו שהשינויים ליחידות התרגום קטנים ככל שאפשר, כדי למנוע עבודה מיותרת של מתרגמים.',
	'tpt-mark-summary' => 'גרסה זו סומנה לתרגום',
	'tpt-edit-failed' => 'לא ניתן לעדכן את הדף: $1',
	'tpt-duplicate' => 'נעשה שימוש מרובה בשם יחידת התרגום $1.',
	'tpt-already-marked' => 'הגרסה העדכנית ביותר של דף זה כבר סומנה לתרגום.',
	'tpt-unmarked' => 'הדף $1 כבר אינו מסומן לתרגום.',
	'tpt-list-nopages' => 'אין דפים המסומנים לתרגום וגם לא דפים המוכנים להיות מסומנים לתרגום.',
	'tpt-new-pages-title' => 'דפים שהוצעו לתרגום',
	'tpt-old-pages-title' => 'דפים בתרגום',
	'tpt-other-pages-title' => 'דפים מקולקלים',
	'tpt-discouraged-pages-title' => 'דפים לא מומלצים',
	'tpt-new-pages' => '{{PLURAL:$1|הדף הזה מכיל|הדפים האלה מכילים}} טקסט עם תגי תרגום,
אבל שום גרסה {{PLURAL:$1|דף זה|הדפים האלה}} מסומנת כעת לתרגום.',
	'tpt-old-pages' => '{{PLURAL:$1|גרסה מסוימת|גרסאות מסוימות}} של {{PLURAL:$1|דף זה סומנה|דפים אלה סומנו}} לתרגום.',
	'tpt-other-pages' => '{{PLURAL:$1|גרסה ישנה של דף זה סומנה|גרסאות ישנות של דפים אלה סומנו}} לתרגום,
אבל {{PLURAL:$1|הגרסה האחרונה אינה יכולה להיות מסומנת|הגרסאות האחרונות אינן יכולות להיות מסומנות}} לתרגום.',
	'tpt-discouraged-pages' => 'מומלץ לא לתרגם את {{PLURAL:$1|הדף הזה|הדפים האלה}}',
	'tpt-select-prioritylangs' => 'רשימת מופרדת בפסיקים של קודי שפות מועדפות:',
	'tpt-select-prioritylangs-force' => 'למנוע תרגום לשפות שאינן מוגדרות כמועדפות',
	'tpt-select-prioritylangs-reason' => 'סיבה:',
	'tpt-sections-prioritylangs' => 'שפות מועדפות',
	'tpt-rev-mark' => 'לסמן לתרגום',
	'tpt-rev-unmark' => 'הסרה מהתרגום',
	'tpt-rev-discourage' => 'לסמן כלא מומלץ',
	'tpt-rev-encourage' => 'שחזור',
	'tpt-rev-mark-tooltip' => 'סימון הגרסה האחרונה של דף זה לתרגום',
	'tpt-rev-unmark-tooltip' => 'להסרת דף זה מרשימת התרגום.',
	'tpt-rev-discourage-tooltip' => 'להמליץ לא לתרגם את הדף הזה עוד.',
	'tpt-rev-encourage-tooltip' => 'לשחזר את הדף הזה לתרגום רגיל.',
	'translate-tag-translate-link-desc' => 'תרגום דף זה',
	'translate-tag-markthis' => 'סימון דף זה לתרגום',
	'translate-tag-markthisagain' => 'בדף הזה יש <span class="plainlinks">[$1 שינויים]</span> שנעשו מאז שהוא <span class="plainlinks">[$2 סומן לתרגום]</span> בפעם האחרונה.',
	'translate-tag-hasnew' => 'דף זה מכיל <span class="plainlinks">[$1 שינויים]</span> שאינם מסומנים לתרגום.',
	'tpt-translation-intro' => 'הדף הזה הוא <span class="plainlinks">[$1 גרסה מתורגמת]</span> של הדף [[$2]] והתרגום שלם ב־$3%.',
	'tpt-languages-legend' => 'שפות אחרות:',
	'tpt-languages-zero' => 'להתחיל לתרגום לשפה הזאת',
	'tpt-tab-translate' => 'תרגום',
	'tpt-target-page' => 'לא ניתן לעדכן דף זה ידנית.
דף זה הוא תרגום של הדף [[$1]] וניתן לעדכן את התרגום באמצעות [$2 כלי התרגום].',
	'tpt-unknown-page' => 'מרחב שם זה שמור לצורך תרגומי דפי התוכן.
הדף אותו אתם מנסים לערוך אינו תואם לאף דף המסומן לתרגום.',
	'tpt-translation-restricted' => 'מנהל תרגומים נעל את תרגום קבוצת ההודעות הזאת לשפה הזאת.

סיבה להגבלה: $1',
	'tpt-discouraged-language-force' => 'מנהל תרגומים הגביל את השפות שאפשר לתרגם אליהן את הדף הזה. השפה הזאת לא נכללת בהן.

סיבה: $1',
	'tpt-discouraged-language' => 'השפה הזאת היא לא אחת השפות המועדפות לתרגום כפי שהגדיר מנהל תרגומים עבור הדף הזה.

סיבה: $1',
	'tpt-discouraged-language-reason' => 'סיבה: $1',
	'tpt-priority-languages' => 'מנהל תרגום הגדיר שהשפות המועדפות לקבוצה הזאת הן $1.',
	'tpt-render-summary' => 'עדכון להתאמת הגרסה החדשה של דף המקור',
	'tpt-download-page' => 'ייצוא דף עם תרגומים',
	'aggregategroups' => 'קבוצות משולבות',
	'tpt-aggregategroup-add' => 'הוספה',
	'tpt-aggregategroup-save' => 'שמירה',
	'tpt-aggregategroup-add-new' => 'הוספת קבוצה משולבת חדשה',
	'tpt-aggregategroup-new-name' => 'שם:',
	'tpt-aggregategroup-new-description' => 'תיאור (לא חובה):',
	'tpt-aggregategroup-remove-confirm' => 'האם ברצונך באמת למחוק את הקבוצה המשולבת הזאת?',
	'tpt-aggregategroup-invalid-group' => 'הקבוצה אינה קיימת',
	'pt-parse-open' => 'תג &lt;translate> לא מאוזן.
תבנית תרגום: <pre>$1</pre>',
	'pt-parse-close' => 'תג &lt;/translate> לא מאוזן.
תבנית תרגום: <pre>$1</pre>',
	'pt-parse-nested' => 'קטעי &lt;translate> מקוננים אינם מורשים.
תוכן התג: <pre>$1</pre>',
	'pt-shake-multiple' => 'סמני יחידות תרגום מרובים עבור קטע אחד.
טקסט יחידת התרגום: <pre>$1</pre>',
	'pt-shake-position' => 'סמני יחידות תרגום במיקום בלתי־צפוי.
תוכן היחידת התרגום: <pre>$1</pre>',
	'pt-shake-empty' => 'יחידת תרגום ריקה עבור סמן "$1".',
	'log-description-pagetranslation' => 'יומן של פעולות שמיוחדות למערכת תרגום דפים',
	'log-name-pagetranslation' => 'יומן תרגום דפים',
	'logentry-pagetranslation-mark' => '$1 {{GENDER:$2|סימן|סימנה}} את הדף $3 לתרגום',
	'logentry-pagetranslation-unmark' => '$1 {{GENDER:$2|הוציא|הוציאה}} את הדף $3 ממצב תרגום',
	'logentry-pagetranslation-moveok' => '$1 {{GENDER:$2|השלים|השלימה}} את שינוי השם של הדף ההניתן לתרגום $3 אל $4',
	'logentry-pagetranslation-movenok' => '$1 {{GENDER:$2|נתקל|נתקלה}} בבעיה בעת העברת הדף $3 לשם $4',
	'logentry-pagetranslation-deletefok' => '$1 {{GENDER:$2|השלים|השלימה}} את המחיקה של הדף הניתן לתרגום $3',
	'logentry-pagetranslation-deletefnok' => '$1 {{GENDER:$2|נכשל|נכשלה|נכשל}} במחיקת $3 אשר שייך לדף המתורגם $4',
	'logentry-pagetranslation-deletelok' => '$1 {{GENDER:$2|השלים|השלימה}} את המחיקה של הדף הניתן לתרגום $3',
	'logentry-pagetranslation-deletelnok' => '$1 {{GENDER:$2|נכשל|נכשלה|נכשל}} במחיקת $3 אשר שייך לדף התרגום $4',
	'logentry-pagetranslation-encourage' => '$1 {{GENDER:$2|עודד|עודדה}} את התרגום של $3',
	'logentry-pagetranslation-discourage' => '$1 {{GENDER:$2|המליץ|המליצה}} לא לתרגם את $3',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1 {{GENDER:$2|הסיר|הסירה}} שפות מועדפות מהדף הניתן לתרגום $3',
	'logentry-pagetranslation-prioritylanguages' => '$1 {{GENDER:$2|הגדיר|הגדירה}} שהשפות המועדפות לדף $3 הן $5',
	'logentry-pagetranslation-prioritylanguages-force' => '$1 {{GENDER:$2|הגביל|הגבילה}} את התרגום של הדף $3 אל השפות הבאות: $5',
	'logentry-pagetranslation-associate' => '$1 {{GENDER:$2|הוסיף|הוסיפה}} את הדף הניתן לתרגום $3 לקבוצה המשולבת $4',
	'logentry-pagetranslation-dissociate' => '$1 {{GENDER:$2|הוציא|הוציאה}} את הדף הניתן לתרגום $3 מהקבוצה המשולבת $4',
	'pt-movepage-title' => 'להעביר את הדף הניתן לתרגום $1',
	'pt-movepage-blockers' => 'דף שניתן לתרגום אינו יכול להיות מועבר לשם חדש בגלל {{PLURAL:$1|השגיאה הבאה|השגיאות הבאות}}:',
	'pt-movepage-block-base-exists' => 'כבר קיים דף לתרגום בשם [[:$1]].',
	'pt-movepage-block-base-invalid' => 'לדף התרגום המיועד אין כותרת תקינה.',
	'pt-movepage-block-tp-exists' => 'דף התרגום המיועד [[:$2]] קיים.',
	'pt-movepage-block-tp-invalid' => 'כותרת דף התרגום המיועד עבור [[:$1]] אינה תקינה (אולי ארוכה מדי).',
	'pt-movepage-block-section-exists' => 'דף יחידת התרגום המיועד [[:$2]] קיים.',
	'pt-movepage-block-section-invalid' => 'כותרת הדף המיועדת עבור "[[:$1]]" ליחידת התרגום תהיה בלתי־תקינה (אולי ארוכה מדי?).',
	'pt-movepage-block-subpage-exists' => 'דף המשנה המיועד [[:$2]] קיים.',
	'pt-movepage-block-subpage-invalid' => 'כותרת דף המשנה המיועד עבור [[:$1]] אינה תקינה (אולי ארוכה מדי).',
	'pt-movepage-list-pages' => 'רשימת הדפים להעביר',
	'pt-movepage-list-translation' => '{{PLURAL:$1|דף|דפי}} תרגום',
	'pt-movepage-list-section' => '{{PLURAL:$1|דף|דפי}} יחידת תרגום',
	'pt-movepage-list-other' => '{{PLURAL:$1|דף משנה אחר|דפי משנה אחרים}}',
	'pt-movepage-list-count' => 'בסך הכול יש {{PLURAL:$1|דף אחד|$1 דפים}} להעברה.',
	'pt-movepage-legend' => 'העברת דף שאפשר לתרגום',
	'pt-movepage-current' => 'השם הנוכחי:',
	'pt-movepage-new' => 'השם החדש:',
	'pt-movepage-reason' => 'סיבה:',
	'pt-movepage-subpages' => 'העברת כל עמודי המשנה',
	'pt-movepage-action-check' => 'לבדוק אם ההעברה אפשרית',
	'pt-movepage-action-perform' => 'לבצע את ההעברה',
	'pt-movepage-action-other' => 'שינוי יעד',
	'pt-movepage-intro' => 'דף מיוחד זה מאפשר לך להעביר דפים מסומנים לתרגום.
פעולת ההעברה אינה מידית, מכיוון שצריך להעביר דפים רבים.
בזמן שהדפים מועברים, לא ניתן לקיים שום קשר אִתם.
כשלים יירשמו ב[[Special:Log/pagetranslation|יומן תרגום דפים]], ויהיה צריך לתקן אותם באופן ידני.',
	'pt-movepage-logreason' => 'חלק מהדף הניתן לתרגום $1.',
	'pt-movepage-started' => 'עכשיו דף הבסיס הועבר.
נא לבדוק את השגיאות ואת הודעת ההשלמה ב[[Special:Log/pagetranslation|יומן תרגום הדפים]].',
	'pt-locked-page' => 'הדף הזה נעול כי הדף הניתן לתרגום מועבר כעת.',
	'pt-deletepage-lang-title' => 'מחיקת דף התרגום $1.',
	'pt-deletepage-full-title' => 'מחיקת הדף הניתן לתרגום $1.',
	'pt-deletepage-invalid-title' => 'הדף השצוין אינו תקין.',
	'pt-deletepage-invalid-text' => 'הדף שצוין אינו דף ניתן לתרגום או תרגום של דף כזה.',
	'pt-deletepage-action-check' => 'רשימת דפים למחיקה',
	'pt-deletepage-action-perform' => 'לבצע את המחיקה',
	'pt-deletepage-action-other' => 'שינוי היעד',
	'pt-deletepage-lang-legend' => 'מחיקת דף תרגום',
	'pt-deletepage-full-legend' => 'מחיקת דף ניתן לתרגום',
	'pt-deletepage-any-legend' => 'מחיקת דף ניתן לתרגום או תרגום של דף כזה',
	'pt-deletepage-current' => 'שם הדף:',
	'pt-deletepage-reason' => 'סיבה:',
	'pt-deletepage-subpages' => 'מחק את כל דפי המשנה',
	'pt-deletepage-list-pages' => 'רשימת דפים למחיקה',
	'pt-deletepage-list-translation' => 'דפי תרגום',
	'pt-deletepage-list-section' => 'דפי יחידת תרגום',
	'pt-deletepage-list-other' => 'דפי משנה אחרים',
	'pt-deletepage-list-count' => 'סך הכול {{PLURAL:$1|דף אחד|$1 דפים}} למחוק.',
	'pt-deletepage-full-logreason' => 'חלק מהדף הניתן לתרגום $1.',
	'pt-deletepage-lang-logreason' => 'חלק מדך התרגום $1.',
	'pt-deletepage-started' => 'נא לבדוק את השגיאות ואת הודעת ההשלמה ב[[Special:Log/pagetranslation|יומן תרגום הדפים]]',
	'pt-deletepage-intro' => 'הדך המיוחד הזה מאפשר לך למחוק בשלמותם דפים ניתנים לתרגום או תרגומים שלהם לשפה כלשהי.
פעולת המחיקה לא תהיה מידית, כי יש למחוק את כל הדפים התלויים בהם.
הכישלונות יירשמו ב[[Special:Log/pagetranslation|יומן תרגום דפים]] ויהיה צריך לתקן אותם ידנית.',
);

/** Hindi (हिन्दी)
 * @author Ansumang
 * @author Siddhartha Ghai
 */
$messages['hi'] = array(
	'pagetranslation' => 'पृष्ठ अनुवाद',
	'tpt-section' => 'अनुवाद यूनिट $1',
	'tpt-template' => 'पृष्ठ साँचा',
	'tpt-diff-old' => 'पूर्व लेख',
	'tpt-diff-new' => 'नया लेख',
	'tpt-other-pages-title' => 'टूटा पृष्ठ',
	'pt-movepage-list-translation' => 'अनुवाद पृष्ठ', # Fuzzy
	'pt-movepage-list-section' => 'अनुभाग पृष्ठ', # Fuzzy
	'pt-movepage-list-other' => 'अन्य उपपृष्ठ', # Fuzzy
	'pt-movepage-current' => 'सद्य सदस्यनाम:',
	'pt-movepage-new' => 'नया नाम:',
	'pt-movepage-reason' => 'कारण:',
	'pt-deletepage-current' => 'पृष्ठ नाम:',
	'pt-deletepage-reason' => 'कारण:',
	'pt-deletepage-list-pages' => 'पृष्ठ तालिका हटाने के लिए',
	'pt-deletepage-list-translation' => 'अनुवाद पृष्ठ',
	'pt-deletepage-list-section' => 'अनुभाग पृष्ठ', # Fuzzy
	'pt-deletepage-list-other' => 'अन्य उपपृष्ठ',
);

/** Croatian (hrvatski)
 * @author Ex13
 * @author Herr Mlinka
 * @author SpeedyGonsales
 */
$messages['hr'] = array(
	'pagetranslation' => 'Prijevod stranice',
	'right-pagetranslation' => 'Označi inačice stranica za prijevod',
	'tpt-desc' => 'Proširenje za prevođenje sadržaja stranica',
	'tpt-section' => 'Grupa za prijevod $1',
	'tpt-section-new' => 'Nova grupa za prijevod.
Ime: $1',
	'tpt-section-deleted' => 'Grupa za prijevod $1',
	'tpt-template' => 'Predložak stranice',
	'tpt-templatediff' => 'Predložak stranice je promijenjen.',
	'tpt-diff-old' => 'Prethodni tekst',
	'tpt-diff-new' => 'Novi tekst',
	'tpt-submit' => 'Označi ovu verziju za prijevod',
	'tpt-sections-oldnew' => 'Novi i postojeći prijevodi',
	'tpt-sections-deleted' => 'Obrisane grupe prijevoda',
	'tpt-sections-template' => 'Predložak stranice za prijevod',
	'tpt-nosuchpage' => 'Stranica $1 ne postoji',
	'translate-tag-translate-link-desc' => 'Prevedi ovu stranicu',
	'translate-tag-markthis' => 'Označi ovu stranicu za prijevod',
	'tpt-languages-legend' => 'Drugi jezici:',
	'pt-movepage-list-pages' => 'Popis stranica za premještanje',
	'pt-movepage-list-other' => 'Ostale podstranice', # Fuzzy
	'pt-movepage-current' => 'Trenutačni naziv:',
	'pt-movepage-new' => 'Novi naziv:',
	'pt-movepage-reason' => 'Razlog:',
	'pt-movepage-subpages' => 'Premjesti sve podstranice',
	'pt-movepage-action-check' => 'Provjeri je li premještanje moguće',
	'pt-movepage-action-perform' => 'Premjesti',
	'pt-movepage-action-other' => 'Promijeni cilj',
	'pt-movepage-intro' => 'Ova posebna stranica omogućava vam premještanje stranica koje su označene za prijevod.
Premještanje nije trenutačno, jer mnoge stranice treba premjestiti.
Red poslova će se koristiti za premještanje stranica.
Dok se stranice premještaju, nije moguće raditi na stranicama u pitanju.
Kvarovi/pogreške biti će prijavljene u evidenciji prijevoda i trebaju se ručno popraviti.', # Fuzzy
	'pt-movepage-logreason' => 'Dio prevodive stranice $1.',
);

/** Upper Sorbian (hornjoserbsce)
 * @author Michawiki
 */
$messages['hsb'] = array(
	'pagetranslation' => 'Přełožowanje strony',
	'right-pagetranslation' => 'Wersije strony za přełožowanje markěrować',
	'action-pagetranslation' => 'přełožujomne strony zrjadować',
	'tpt-desc' => 'Rozšěrjenje za přełožowanje wobsahowych stronow',
	'tpt-section' => 'Přełožowanska jednotka $1',
	'tpt-section-new' => 'Nowa přełožowanska jednotka. Mjeno: $1',
	'tpt-section-deleted' => 'Přełožowanska jednotka $1',
	'tpt-template' => 'Předłoha strony',
	'tpt-templatediff' => 'Předłoha strony je so změniła.',
	'tpt-diff-old' => 'Předchadny tekst',
	'tpt-diff-new' => 'Nowy tekst',
	'tpt-submit' => 'Tutu wersiju za přełožowanje markěrować',
	'tpt-sections-oldnew' => 'Nowe a eksistowace přełožowanske jednotki',
	'tpt-sections-deleted' => 'Wušmórnjene přełožowanske jednotki',
	'tpt-sections-template' => 'Předłoha přełožowanskeje strony',
	'tpt-action-nofuzzy' => 'Njeanuluj přełožki',
	'tpt-badtitle' => 'Podate mjeno strony ($1) płaćiwy titul njeje',
	'tpt-nosuchpage' => 'Strona $1 njeeksistuje',
	'tpt-oldrevision' => '$2 aktualna wersija strony [[$1]] njeje.
Jenož aktualne wersije hodźa so za přełožowanje markěrować.',
	'tpt-notsuitable' => 'Strona $1 za přełožowanje přihódna njeje.
Zaswěsć, zo ma taflički <nowiki><translate></nowiki> a płaćiwu syntaksu.',
	'tpt-saveok' => 'Strona [[$1]] je so za přełožowanje z $2 {{PLURAL:$2|přełožujomnej jednotku|přełožujomnej jednotkomaj|přełožujomnymi jednotkami|přełožujomnymi jednotkami}} markěrowała.
Strona hodźi so nětko <span class="plainlinks">[$3 přełožować]</span>.',
	'tpt-badsect' => '"$1" płaćiwe mjeno za přełožowansku jednotku $2 njeje.',
	'tpt-showpage-intro' => 'Deleka su nowe, eksistowace a zhašane přełožowanske jednotki nalistowane.
Prjedy hač tutu wersiju za přełožowanje markěruješ, kontroluj, hač změny přełožowanskich jednotkow su miniměrowane, zo by njetrěbne dźěło za přełožowarjow wobešoł.',
	'tpt-mark-summary' => 'Je tutu wersiju za přełožowanje markěrował',
	'tpt-edit-failed' => 'Strona njeda so aktualizować: $1',
	'tpt-duplicate' => 'Mjeno přełožkoweje jednotki $1 so wjace hač jedyn raz wužiwa.',
	'tpt-already-marked' => 'Akutalna wersija tuteje strony je so hižo za přełožowanje markěrowała.',
	'tpt-unmarked' => 'Strona $1 hižo njeje za přełožowanje markěrowana.',
	'tpt-list-nopages' => 'Strony njejsu ani za přełožowanje markěrowali ani njejsu hotowe za přełožowanje.',
	'tpt-new-pages-title' => 'Strony namjetowane za přełožk',
	'tpt-old-pages-title' => 'Strony, kotrež so přełožuja',
	'tpt-other-pages-title' => 'Wobškodźene strony',
	'tpt-discouraged-pages-title' => 'Wuzamknjene strony',
	'tpt-new-pages' => '{{PLURAL:$1|Tuta strona wobsahuje|Tutej stronje|Tute strony wobsahuja|Tute strony wobsahuja}} tekst z přełožowanskimi tafličkimi, ale žana wersija {{PLURAL:$1|tuteje strony|tuteju stronow|tutych stronow|tutych stronow}} njeje tuchwilu za přełožowanje markěrowana.',
	'tpt-old-pages' => 'Někajka wersija {{PLURAL:$1|tuteje strony|tuteju stronow|tutych stronow|tutych stronow}} je so za přełožowanje markěrowała.',
	'tpt-other-pages' => 'Stara wersija {{PLURAL:$1|tuteje strony|tuteju stronow|tutych stronow|tutych stronow}} je za přełožowanje markěrowana,
ale aktualna wersija njehodźi so za přełožowanje markěrować.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Tuta strona|Tutej stronje|Tute strony|Tute strony}} {{PLURAL:$1|bu|buštej|buchu|buchu}} wot dalšeho přełoženja {{PLURAL:$1|wuzamknjena|wuzamknjenej|wuzamknjene|wuzamknjene}}.',
	'tpt-select-prioritylangs' => 'Lisćina rěčnych kodow primarnych rěčow dźělenych přez komu:',
	'tpt-select-prioritylangs-force' => 'Přełožkam do druhich rěčow hač primarnych rěčow zadźěwać',
	'tpt-select-prioritylangs-reason' => 'Přičina:',
	'tpt-sections-prioritylangs' => 'Primarne rěče',
	'tpt-rev-mark' => 'za přełožowanje markěrować',
	'tpt-rev-unmark' => 'z přełoženja wotstronić',
	'tpt-rev-discourage' => 'wuzamknyć',
	'tpt-rev-encourage' => 'wobnowić',
	'tpt-rev-mark-tooltip' => 'Najnowšu wersiju tuteje strony za přełožowanje markěrować.',
	'tpt-rev-unmark-tooltip' => 'Tutu stronu z přełoženja wotstronić',
	'tpt-rev-discourage-tooltip' => 'Dalše přełožki na tutej stronje wuzamknyć.',
	'tpt-rev-encourage-tooltip' => 'Tutu stronu za normalne přełoženje wobnowić.',
	'translate-tag-translate-link-desc' => 'Tutu stronu přełožić',
	'translate-tag-markthis' => 'Tutu stronu za přełožowanje markěrować',
	'translate-tag-markthisagain' => 'Tuta strona ma <span class="plainlinks">[$1 {{PLURAL:$1|změnu|změnje|změny|změnow}}]</span>, wot toho zo, bu posledni raz <span class="plainlinks">[$2 za přełožowanje markěrowana]</span>.',
	'translate-tag-hasnew' => 'Tuta strona wobsahuje <span class="plainlinks">[$1 {{PLURAL:$1|změna, kotraž njeje markěrowana|změnje, kotrejž njejstej markěrowanej|změny, kotrež njejsu markěrowane|změnow, kotrež njejsu markěrowane}}]</span> za přełožowanje.',
	'tpt-translation-intro' => 'Tuta strona je <span class="plainlinks">[$1 přełožena wersija]</span> strony [[$2]] a $3 % přełožka je dokónčene a přełožk je aktualny.',
	'tpt-languages-legend' => 'Druhe rěče:',
	'tpt-languages-zero' => 'Přełožowanje za tutu rěč započeć',
	'tpt-target-page' => 'Tuta strona njeda so manulenje aktualizować.
Tuta strona je přełožk strony [[$1]] a přełožk hodźi so z pomocu [$2 Přełožić] aktualizować.',
	'tpt-unknown-page' => 'Tutón mjenowy rum je za přełožki wobsahowych stronow wuměnjeny.
Strona, kotruž pospytuješ wobdźěłać, po wšěm zdaću stronje markěrowanej za přełožowanje njewotpowěduje.',
	'tpt-translation-restricted' => 'Přełožowanski administrator je přełožowanju tuteje strony do tuteje rěče zadźěwał.

Přičina: $1',
	'tpt-discouraged-language-force' => 'Přełožowanski administrator je rěče wobmjezował, do kotrychž tuta strona da so přełožić. Tuta rěč mjez tutymi rěčemi njeje:

Přičina: $1',
	'tpt-discouraged-language' => 'Tuta rěč njeje mjez primarnymi rěčemi, kotrež přełožowanski administrator je za tutu stronu postajił.

Přičina: $1',
	'tpt-discouraged-language-reason' => 'Přičina: $1',
	'tpt-priority-languages' => 'Přełožowanski administrator je primarne rěče za tutu skupinu jako $1 nastajił.',
	'tpt-render-summary' => 'Aktualizacija po nowej wersiji žórłoweje strony',
	'tpt-download-page' => 'Stronu z přełožkami eksportować',
	'aggregategroups' => 'Skupiny zjednoćić',
	'tpt-aggregategroup-add' => 'Přidać',
	'tpt-aggregategroup-save' => 'Składować',
	'tpt-aggregategroup-add-new' => 'Nowu zjimansku skupinu přidać',
	'tpt-aggregategroup-new-name' => 'Mjeno:',
	'tpt-aggregategroup-new-description' => 'Wopisanje (opcionalne):',
	'tpt-aggregategroup-remove-confirm' => 'Chceš tutu skupinu woprawdźe zhašeć?',
	'tpt-aggregategroup-invalid-group' => 'Skupina njeeksistuje',
	'pt-parse-open' => 'Asymetriska taflička &lt;translate>.
Přełožowanska předłoha: <pre>$1</pre>',
	'pt-parse-close' => 'Asymetriska taflička &lt;/translate>.
Přełožowanska předłoha: <pre>$1</pre>',
	'pt-parse-nested' => 'Zakšćikowane přełožowanske jednotki &lt;translate> njejsu dowolene.
Tekst taflički: <pre>$1</pre>',
	'pt-shake-multiple' => 'Wjacore marki přełožowanskich jednotkow za jednu přełožowansku jednotku.
Tekst přełožowanskeje jednotki: <pre>$1</pre>',
	'pt-shake-position' => 'Marki přełožowanskich jednotkow na njewočakowanym městnje.
Tekst přełožowanskeje jednotki: <pre>$1</pre>',
	'pt-shake-empty' => 'Prózdna přełožowanska jednotka za marku "$1".',
	'log-description-pagetranslation' => 'Protokol za akcije w zwisku z přełožowanskim systemom',
	'log-name-pagetranslation' => 'Protokol přełožkow',
	'logentry-pagetranslation-mark' => '$1 je $3 za přełožowanje {{GENDER:$2|markěrował|markrowała}}',
	'logentry-pagetranslation-unmark' => '$1 je $3 z přełožowanja {{GENDER:$2|wotstronił|wotstroniła}}',
	'logentry-pagetranslation-moveok' => '$1 je přemjenowanje přełožujomneje strony $3 do $4 {{GENDER:$2|dokónčił|dokónčiła}}',
	'logentry-pagetranslation-movenok' => '$1 je při přesuwanju strony $3 do $4 na problem {{GENDER:$2|storčił|storčiła}}',
	'logentry-pagetranslation-deletefok' => '$1 je hašenje přełožujomneje strony $3 {{GENDER:$2|wotzamknył|wotzamknyła}}',
	'logentry-pagetranslation-deletefnok' => '$1 {{GENDER:$2|njemóžeše}} $3 zhašeć, kotraž k přełožujomnej stronje $4 słuša',
	'logentry-pagetranslation-deletelok' => '$1 je hašenje přełožujomneje strony $3 {{GENDER:$2|wotzamknył|wotzamknyła}}',
	'logentry-pagetranslation-deletelnok' => '$1 {{GENDER:$2|njemóžeše}} $3 zhašeć, kotraž k přełožowanskej stronje $4 słuša',
	'logentry-pagetranslation-encourage' => '$1 je přełožowanje strony $3 {{GENDER:$2|doporučił|doporučiła}}',
	'logentry-pagetranslation-discourage' => '$1 je wot přełožowanja strony $3 {{GENDER:$2|wotradźił|wotradźiła}}',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1 je primarne rěče z přełožujomneje strony $3 {{GENDER:$2|wotstronił|wotstroniła}}',
	'logentry-pagetranslation-prioritylanguages' => '$1 je primarne rěče za přełožujomnu stronu $3 na $5 {{GENDER:$2|stajił|stajiła}}',
	'logentry-pagetranslation-prioritylanguages-force' => '$1 je rěče za přełožujomnu stronu $3 na $5 {{GENDER:$2|wobmjezował|wobmjezowała}}',
	'logentry-pagetranslation-associate' => '$1 je přełožujomnu stronu $3 metaskupinje $4 {{GENDER:$2|přidał|přidała}}',
	'logentry-pagetranslation-dissociate' => '$1 je přełožujomnu stronu $3 z metaskupiny $4 {{GENDER:$2|wotstronił|wotstroniła}}',
	'pt-movepage-title' => 'Přełožujomnu stronu $1 přesunyć',
	'pt-movepage-blockers' => 'Přełožujomna strona njeda so {{PLURAL:$1|slědowaceho zmylka|slědowaceju zmylkow|slědowacych zmylkow|slědowacych zmylkow}} dla do noweho mjena přesunyć:',
	'pt-movepage-block-base-exists' => 'Cilowa přełožujomna strona "[[:$1]]" eksistuje.',
	'pt-movepage-block-base-invalid' => 'Mjeno ciloweje přełožujomneje strony płaćiwy titul njeje.',
	'pt-movepage-block-tp-exists' => 'Cilowa přełožowanska strona [[:$2]] eksistuje.',
	'pt-movepage-block-tp-invalid' => 'Titul ciloweje přełožowanskeje strony za [[:$1]] by płaćiwy był (předołho?).',
	'pt-movepage-block-section-exists' => 'Cilowa strona "[[:$2]]" za přełožowansku jednotku eksistuje.',
	'pt-movepage-block-section-invalid' => 'Titul ciloweje strony za "[[:$1]]" za přełožowansku jednotku by njepłaćiwy był (předołho?).',
	'pt-movepage-block-subpage-exists' => 'Cilowa podstrona [[:$2]] eksistuje.',
	'pt-movepage-block-subpage-invalid' => 'Titul ciloweje podstrony za [[:$1]] by płaćiwy był (předołho?).',
	'pt-movepage-list-pages' => 'Lisćina strony, kotrež maja so přesunyć',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Přełožowanska strona|Přełožowanskej stronje|Přełožowanske strony}}',
	'pt-movepage-list-section' => '{{PLURAL:$1|Strona|Stronje|Strony}} přełožowanskich jednotkow',
	'pt-movepage-list-other' => '{{PLURAL:$1|Druha podstrona|Druhej podstronje|Druhe podstrony}}',
	'pt-movepage-list-count' => 'W cyłku {{PLURAL:$1|ma so $1 strona|matej so $1 stronje|maja so $1 strony|ma so $1 stronow}} přesunyć.',
	'pt-movepage-legend' => 'Přełožujomnu stronu přesunyć',
	'pt-movepage-current' => 'Aktualne mjeno:',
	'pt-movepage-new' => 'Nowe mjeno:',
	'pt-movepage-reason' => 'Přičina:',
	'pt-movepage-subpages' => 'Wšě podstrony přesunyć',
	'pt-movepage-action-check' => 'Kontrolować, hač přesunjenje je móžno',
	'pt-movepage-action-perform' => 'Přesunyć',
	'pt-movepage-action-other' => 'Cil změnić',
	'pt-movepage-intro' => 'Tuta specialna strona zmóžnja přesuwanje stronow, kotrež su za přełožowanje markěrowane.
Přesunjenje so hnydom njestawa, dokelž wjele stronow dyrbi so přesunyć.
Při přesuwanju stronow njeje móžno, z wotpowědnymi stronami do zwiska stupić.
Zmylki budu so w [[Special:Log/pagetranslation|přełožowanskim protokolu strony]] protokolować  a dyrbja so manuelnje skorigować.',
	'pt-movepage-logreason' => 'Dźěl přełožujomneje strony $1.',
	'pt-movepage-started' => 'Zakładna strona je nětko přesunjena.
Prošu skontroluj [[Special:Log/pagetranslation|přełožowanski protokol strony]] za zmylkami a zdźělenku wukonjenja.',
	'pt-locked-page' => 'Tuta strona je zawrjena, dokelž přełožujomna strona so runje přesuwa.',
	'pt-deletepage-lang-title' => 'Přełožena strona $1 so haša.',
	'pt-deletepage-full-title' => 'Přełožujomna strona $1 so haša.',
	'pt-deletepage-invalid-title' => 'Podata strona płaćiwa njeje.',
	'pt-deletepage-invalid-text' => 'Podata strona ani přełožujomna strona ani přełožowanska strona njeje.',
	'pt-deletepage-action-check' => 'Strony nalistować, kotrež maja so zhašeć',
	'pt-deletepage-action-perform' => 'Zhašeć',
	'pt-deletepage-action-other' => 'Cil změnić',
	'pt-deletepage-lang-legend' => 'Přełoženu stronu wušmórnyć',
	'pt-deletepage-full-legend' => 'Přełožujomnu stronu wušmórnyć',
	'pt-deletepage-any-legend' => 'Přełožujomnu stronu abo přełožowansku stronu zhašeć',
	'pt-deletepage-current' => 'Mjeno strony:',
	'pt-deletepage-reason' => 'Přičina:',
	'pt-deletepage-subpages' => 'Wšě podstrony wušmórnyć',
	'pt-deletepage-list-pages' => 'Lisćina stronow, kotrež maja so zhašeć',
	'pt-deletepage-list-translation' => 'Přełožene strony',
	'pt-deletepage-list-section' => 'Strony přełožowanskich jednotkow',
	'pt-deletepage-list-other' => 'Druhe podstrony',
	'pt-deletepage-list-count' => 'W cyłku {{PLURAL:$1|ma so $1 strona|matej so $1 stronje|maja so $1 strony|ma so $1 stronow}} zhašeć.',
	'pt-deletepage-full-logreason' => 'Dźěl přełožujomneje strony $1.',
	'pt-deletepage-lang-logreason' => 'Dźěl přełoženeje strony $1.',
	'pt-deletepage-started' => 'Prošu přepruwuj [[Special:Log/pagetranslation|protokol přełožkow]] za zmylkami a wuwjedźenskimi zdźělenkami.',
	'pt-deletepage-intro' => 'Tuta specialna strona ći zmóžnja, cyłu přełožujomnnu stronu abo  jednotliwu přełožwansku stronu w rěči zhašeć.
Zhašenje njestanje so hnydom, dokelž wšě strony, kotrež z njej zwisuja, dyrbja so tež zhašeć.
Zmylki budu so w  [[Special:Log/pagetranslation|protokolu přełožkow]] protokolować a wone dyrbja so manuelnje porjedźić.',
);

/** Haitian (Kreyòl ayisyen)
 * @author Boukman
 */
$messages['ht'] = array(
	'pagetranslation' => 'Tradiksyon paj yo',
	'right-pagetranslation' => 'Make vèsyon paj yo pou tradui',
	'tpt-desc' => 'Ekstansyon pou tradui paj kontni yo',
	'tpt-section' => 'Inite tradiksyon $1',
	'tpt-section-new' => 'Nouvo inite tradiksyon.
Non: $1',
	'tpt-section-deleted' => 'Inite tradiksyon $1',
	'tpt-template' => 'Modèl pou paj',
	'tpt-templatediff' => 'Modèl pou paj la chanje',
	'tpt-diff-old' => 'Teks presedan',
	'tpt-diff-new' => 'Nouvo tèks',
	'tpt-submit' => 'Make vèsyon sa pou tradui',
	'tpt-sections-oldnew' => 'Inite tradiksyon ki deja egziste ak nouvo yo',
	'tpt-sections-deleted' => 'Inite tradiksyon ki efase',
	'tpt-sections-template' => 'Modèl pou paj tradiksyon',
	'tpt-action-nofuzzy' => 'Pa rann tradiksyon envalid',
	'tpt-badtitle' => 'Non ou bay pou paj ($1) pa yon tit ki bon',
	'tpt-nosuchpage' => 'Paj $1 pa egziste',
	'tpt-oldrevision' => '$2 se pa dènye vèsyon paj [[$1]].
Se sèlman dènye vèsyon ki kapab make pou tradui.',
	'tpt-notsuitable' => 'Paj $1 pa bon pou tradui.
Asire w li gen etikèt <nowiki><translate></nowiki> epi ke li gen yon sentaks ki bon.',
	'tpt-saveok' => 'Paj [[$1]] te make pou yo tradui l ak 2 {{PLURAL:$2|inite tradiksyon|inite tradiksyon yo}}.
Paj sa kapab <span class="plainlinks">[$3 tradui]</span> kounye a.',
	'tpt-badsect' => '"$1" pa yon bon non pou inite tradiksyon $2.',
	'tpt-showpage-intro' => 'Anba, gen yon lis tout sèksyon ki nouvo, sa ki egzsite ak sa ki te efase yo.
Anvan ou make vèsyon sa pou yo tradui, verifye ki chanjman nan seksyon yo pa anpil, yon fason pou pa bay tradiktè yo travay ki pa nesesè.', # Fuzzy
	'tpt-mark-summary' => 'Make vèsyon sa pou tradui',
	'tpt-edit-failed' => 'Pa t kapab mete paj sa ajou: $1',
	'tpt-already-marked' => 'Dènye vèsyon paj sa te make pou yo tradui l deja.',
	'tpt-unmarked' => 'Paj $1 pa make pou tradui ankò.',
	'tpt-list-nopages' => 'Pa gen okenn paj ki make pou tradui oubyen ki pare pou sa.',
	'tpt-new-pages' => '{{PLURAL:$1|Paj sa genyen|Paj sa yo genyen}} teks ak baliz tradiksyon, men pa gen okenn vèsyon {{PLURAL:$1|paj sa|paj sa yo}} ki make pou tradui.',
	'tpt-old-pages' => 'Kèk nan vèsyon {{PLURAL:$1|paj sa|paj sa yo}} te make pou tradui.',
	'tpt-other-pages' => '{{PLURAL:$1|Yon ansyen vèsyon paj sa a|Ansyen vèsyon paj sa yo}} make pou tradui,
men dènye {{PLURAL:$1|vèsyon|vèsyon yo}} pa ka make pou tradui.',
	'tpt-rev-unmark' => 'Retire paj sa nan tradiksyon', # Fuzzy
	'translate-tag-translate-link-desc' => 'Tradui paj sa a',
	'translate-tag-markthis' => 'Make paj sa pou tradui',
	'translate-tag-markthisagain' => 'Paj sa te <span class="plainlinks">[$1 chanje]</span> depi li te <span class="plainlinks">[$2 make pou tradui]</span>.',
	'translate-tag-hasnew' => 'Paj sa genyen <span class="plainlinks">[$1 chanjman]</span> ki pa make pou tradui.',
	'tpt-translation-intro' => 'Paj sa a, se yon <span class="plainlinks">[$1 vèsyon ki tradui]</span> de paj [[$2]], epi tradiksyon a fèt a $3%.',
	'tpt-languages-legend' => 'Lòt lang yo:',
	'tpt-target-page' => 'Paj sa a, se yon tradiksyon paj [[$1]] epi ou kapab mete a jou tradiksyon an lè ou itilize [$2 zouti tradiksyon an].',
	'tpt-unknown-page' => 'Espas non sa a rezève pou tradiksyon paj yo.
Paj w ap eseye modifye pa sanble koresponn ak yon paj ki make pou tradiksyon.',
	'tpt-render-summary' => 'N ap mete ajou pou nou genyen nouvo vèsyon paj sous la.',
	'tpt-download-page' => 'Ekspòte paj ki gen tradiksyon',
	'pt-parse-open' => 'Baliz &lt;translate> pa balanse.
Modèle tradiksyon: <pre>$1</pre>',
	'pt-parse-close' => 'Baliz &lt;/translate> pa balanse.
Modèle tradiksyon: <pre>$1</pre>',
	'pt-parse-nested' => 'Seksyon enbrike &lt;translate> pa otorize.
Teks baliz la: <pre>$1</pre>', # Fuzzy
	'pt-movepage-list-pages' => 'Lis paj yo pou deplase',
	'pt-movepage-list-translation' => 'Paj tradiksyon', # Fuzzy
	'pt-movepage-list-section' => 'Paj seksyon', # Fuzzy
	'pt-movepage-list-other' => 'Lòt sou-paj', # Fuzzy
	'pt-movepage-list-count' => '$1 {{PLURAL:$1|paj|paj}} total pou deplase.',
	'pt-movepage-legend' => 'Deplase paj ki ka tradui.',
	'pt-movepage-current' => 'Non aktyèl:',
	'pt-movepage-new' => 'Nouvo non:',
	'pt-movepage-reason' => 'Poukisa:',
	'pt-movepage-subpages' => 'Deplase tout sou-paj yo',
	'pt-movepage-action-check' => 'Gade si deplasman an posib',
	'pt-movepage-action-perform' => 'Fè deplasman an',
	'pt-movepage-action-other' => 'Chanje sib',
);

/** Hungarian (magyar)
 * @author Dani
 * @author Dj
 * @author Glanthor Reviol
 * @author Misibacsi
 * @author Xbspiro
 */
$messages['hu'] = array(
	'pagetranslation' => 'Lap fordítása',
	'right-pagetranslation' => 'Lapok változatainak megjelölése fordítandónak',
	'action-pagetranslation' => 'fordítható oldalak kezelése',
	'tpt-desc' => 'Kiterjesztés lapok fordításához',
	'tpt-section' => '$1 fordítási egység',
	'tpt-section-new' => 'Új fordítási egység.
Név: $1',
	'tpt-section-deleted' => '$1 fordítási egység',
	'tpt-template' => 'Lapsablon',
	'tpt-templatediff' => 'A lapsablon megváltozott.',
	'tpt-diff-old' => 'Előző szöveg',
	'tpt-diff-new' => 'Új szöveg',
	'tpt-submit' => 'A változat megjelölése fordításra.',
	'tpt-sections-oldnew' => 'Új és meglevő fordítási egységek',
	'tpt-sections-deleted' => 'Törölt fordítási egységek',
	'tpt-sections-template' => 'Fordítási lapsablonok',
	'tpt-action-nofuzzy' => 'Ne érvénytelenítse a fordításokat',
	'tpt-badtitle' => 'A megadott lapnév ($1) nem érvényes cím',
	'tpt-nosuchpage' => 'A(z) $1 lap nem létezik.',
	'tpt-oldrevision' => '$2 nem a(z) [[$1]] lap legutolsó változata.
Csak a legfrissebb változatok jelölhetőek meg fordításra.',
	'tpt-notsuitable' => 'A(z) $1 lap nem alkalmas a fordításra.
Ellenőrizd, hogy szerepelnek-e benne <nowiki><translate></nowiki> tagek, és helyes-e a szintaxisa.',
	'tpt-saveok' => 'A(z) [[$1]] lap $2 fordítási egységgel megjelölve fordításra.
A lap mostantól <span class="plainlinks">[$3 lefordítható]</span>.',
	'tpt-badsect' => '„$1” nem érvényes név a(z) $2 fordítási egységnek.',
	'tpt-showpage-intro' => 'Alább az új, már létező és törölt szakaszok felsorolása látható.
Mielőtt fordításra jelölöd ezt a változatot, ellenőrizd hogy a szakaszok változásai minimálisak, elkerülendő a felesleges munkát a fordítóknak.',
	'tpt-mark-summary' => 'Változat megjelölve fordításra',
	'tpt-edit-failed' => 'Nem sikerült frissíteni a lapot: $1',
	'tpt-already-marked' => 'A lap legutolsó verziója már meg van jelölve fordításra.',
	'tpt-unmarked' => 'A(z) $1 lap most már nincs megjelölve fordításra.',
	'tpt-list-nopages' => 'Nincsenek sem fordításra kijelölt, sem kijelölésre kész lapok.',
	'tpt-new-pages-title' => 'Fordításra jelölt lapok',
	'tpt-old-pages-title' => 'Fordítás alatt lévő lapok',
	'tpt-other-pages-title' => 'Hibás lapok',
	'tpt-discouraged-pages-title' => 'Nem javasolt lapok',
	'tpt-new-pages' => '{{PLURAL:$1|Ez a lap tartalmaz|Ezek a lapok tartalmaznak}} fordítási tagekkel ellátott szöveget, de jelenleg egyik {{PLURAL:$1|változata|változatuk}} sincs megjelölve fordításra.',
	'tpt-old-pages' => '{{PLURAL:$1|Ennek a lapnak|Ezeknek a lapoknak}} néhány változata meg van jelölve fordításra.',
	'tpt-other-pages' => 'A lap korábbi {{PLURAL:$1|változata|változatai}} fordíthatónak voltak megjelölve, de a legutóbbi {{PLURAL:$1|változatot|változatokat}} nem lehet megjelölni fordításra.',
	'tpt-select-prioritylangs-reason' => 'Ok:',
	'tpt-sections-prioritylangs' => 'Kiemelt nyelvek',
	'tpt-rev-mark' => 'megjelölés fordításra',
	'tpt-rev-unmark' => 'lap eltávolítása a fordításból',
	'tpt-rev-discourage' => 'nem javasolt',
	'tpt-rev-encourage' => 'visszaállít',
	'translate-tag-translate-link-desc' => 'A lap fordítása',
	'translate-tag-markthis' => 'Lap megjelölése fordításra',
	'translate-tag-markthisagain' => 'Ezen a lapon történtek <span class="plainlinks">[$1 változtatások]</span>, mióta utoljára <span class="plainlinks">[$2 megjelölték fordításra]</span>.',
	'translate-tag-hasnew' => 'Ez a lap tartalmaz <span class="plainlinks">[$1 változtatásokat]</span>, amelyek nincsenek fordításra jelölve.',
	'tpt-translation-intro' => 'Ez a(z) [[$2]] lap egy <span class="plainlinks">[$1 lefordított változata]</span>, és a fordítás $3%-a kész és friss.',
	'tpt-languages-legend' => 'Más nyelvek:',
	'tpt-target-page' => 'Ezt a lapot nem lehet kézzel frissíteni.
A(z) [[$1]] lap fordítása, és a fordítását [$2 a fordítás segédeszköz] segítségével lehet frissíteni.',
	'tpt-unknown-page' => 'Ez a névtér a tartalmi lapok fordításainak van fenntartva.
A lap, amit szerkeszteni próbálsz, úgy tűnik hogy nem egyezik egy fordításra jelölt lappal sem.',
	'tpt-discouraged-language' => "'''$2 — a fordítás erre a nyelvre nem a legfontosabb feladat.'''

Az adminisztrátori javaslat szerint a legfontosabb nyelvek a következők: $3.

Kérjük, ha beszéled ezek közül valamelyiket, fontold meg, hogy inkább arra a nyelvre fordítasz előbb: munkádnak így többek láthatják hasznát.",
	'tpt-render-summary' => 'Frissítés, hogy megegyezzen a forráslap új változatával',
	'tpt-download-page' => 'Lap exportálása fordításokkal együtt',
	'aggregategroups' => 'Összesített csoportok',
	'tpt-aggregategroup-add' => 'Hozzáad',
	'tpt-aggregategroup-save' => 'Mentés',
	'tpt-aggregategroup-add-new' => 'Új egyesített csoport hozzáadása',
	'tpt-aggregategroup-new-name' => 'Név:',
	'tpt-aggregategroup-new-description' => 'Leírás (opcionális):',
	'tpt-aggregategroup-remove-confirm' => 'Biztosan törölni szeretné ezt az összesített csoportot?',
	'tpt-aggregategroup-invalid-group' => 'Csoport nem létezik',
	'pt-parse-open' => 'Páratlan &lt;translate> tag.
Fordítási sablon: <pre>$1</pre>',
	'pt-parse-close' => 'Páratlan &lt;/translate> tag.
Fordítási sablon: <pre>$1</pre>',
	'pt-parse-nested' => 'Egymásba ágyazott &lt;translate> szakaszok nem engedélyezettek.
Elem szövege: <pre>$1</pre>',
	'log-description-pagetranslation' => 'A lapfordító rendszerhez kapcsolódó műveletek naplója',
	'log-name-pagetranslation' => 'Oldalfordítási napló',
	'pt-movepage-title' => 'A(z) $1 fordítható lap átnevezése',
	'pt-movepage-blockers' => 'Nem lehet átnevezni a fordítható lapot az új névre a következő {{PLURAL:$1|hiba|hibák}} miatt:',
	'pt-movepage-list-pages' => 'Átnevezendő lapok listája',
	'pt-movepage-list-translation' => 'Fordítható lapok', # Fuzzy
	'pt-movepage-list-section' => 'Fordítási egység lapok', # Fuzzy
	'pt-movepage-list-other' => 'További allapok', # Fuzzy
	'pt-movepage-list-count' => 'Összesen {{PLURAL:$1|egy|$1}} lapot kell átnevezni.',
	'pt-movepage-legend' => 'Fordítható lap átnevezése',
	'pt-movepage-current' => 'Jelenlegi név:',
	'pt-movepage-new' => 'Új név:',
	'pt-movepage-reason' => 'Indoklás:',
	'pt-movepage-subpages' => 'Összes allap átnevezése',
	'pt-movepage-action-check' => 'Ellenőrizze, hogy az átnevezés lehetséges-e',
	'pt-movepage-action-perform' => 'Átnevezés végrehajtása',
	'pt-movepage-action-other' => 'Cél megváltoztatása',
	'pt-movepage-logreason' => 'A(z) $1 fordítható lap része',
	'pt-deletepage-action-perform' => 'Törlés végrehajtása',
	'pt-deletepage-action-other' => 'Cél megváltoztatása',
	'pt-deletepage-lang-legend' => 'Fordítási lap törlése',
	'pt-deletepage-full-legend' => 'Fordítható lap törlésre',
	'pt-deletepage-current' => 'Lap neve:',
	'pt-deletepage-reason' => 'Ok:',
	'pt-deletepage-subpages' => 'Összes allap törlése',
	'pt-deletepage-list-pages' => 'Törlendő lapok listája',
);

/** Interlingua (interlingua)
 * @author McDutchie
 */
$messages['ia'] = array(
	'pagetranslation' => 'Traduction de paginas',
	'right-pagetranslation' => 'Marcar versiones de paginas pro traduction',
	'action-pagetranslation' => 'gerer paginas traducibile',
	'tpt-desc' => 'Extension pro traducer paginas de contento',
	'tpt-section' => 'Unitate de traduction $1',
	'tpt-section-new' => 'Nove unitate de traduction. Nomine: $1',
	'tpt-section-deleted' => 'Unitate de traduction $1',
	'tpt-template' => 'Patrono de pagina',
	'tpt-templatediff' => 'Le patrono del pagina ha cambiate.',
	'tpt-diff-old' => 'Texto anterior',
	'tpt-diff-new' => 'Texto nove',
	'tpt-submit' => 'Marcar iste version pro traduction',
	'tpt-sections-oldnew' => 'Unitates de traduction nove e existente',
	'tpt-sections-deleted' => 'Unitates de traduction delite',
	'tpt-sections-template' => 'Patrono de pagina de traduction',
	'tpt-action-nofuzzy' => 'Non invalidar traductiones',
	'tpt-badtitle' => 'Le nomine de pagina specificate ($1) non es un titulo valide',
	'tpt-nosuchpage' => 'Le pagina $1 non existe',
	'tpt-oldrevision' => '$2 non es le version le plus recente del pagina [[$1]].
Solmente le versiones le plus recente pote esser marcate pro traduction.',
	'tpt-notsuitable' => 'Le pagina $1 non es traducibile.
Assecura que illo contine etiquettas <nowiki><translate></nowiki> e ha un syntaxe valide.',
	'tpt-saveok' => 'Le pagina [[$1]] ha essite marcate pro traduction con $2 {{PLURAL:$2|unitate|unitates}} de traduction.
Le pagina pote ora esser <span class="plainlinks">[$3 traducite]</span>.',
	'tpt-offer-notify' => 'Tu pote <span class="plainlinks">[$1 notificar le traductores]</span> sur iste pagina.',
	'tpt-badsect' => '"$1" non es un nomine valide pro le unitate de traduction $2.',
	'tpt-showpage-intro' => 'In basso es listate le unitates de traduction nove, existente e delite.
Ante de marcar iste version pro traduction, verifica que le modificationes al unitates de traduction sia minimisate pro evitar labor innecessari pro traductores.',
	'tpt-mark-summary' => 'Marcava iste version pro traduction',
	'tpt-edit-failed' => 'Non poteva actualisar le pagina: $1',
	'tpt-duplicate' => 'Le nomine de unitate de traduction "$1" es usate plus de un vice.',
	'tpt-already-marked' => 'Le version le plus recente de iste pagina ha jam essite marcate pro traduction.',
	'tpt-unmarked' => 'Le pagina $1 non es plus marcate pro traduction.',
	'tpt-list-nopages' => 'Il non ha paginas marcate pro traduction, ni paginas preparate pro isto.',
	'tpt-new-pages-title' => 'Paginas proponite pro traduction',
	'tpt-old-pages-title' => 'Paginas in traduction',
	'tpt-other-pages-title' => 'Paginas defectuose',
	'tpt-discouraged-pages-title' => 'Paginas discoragiate',
	'tpt-new-pages' => 'Iste {{PLURAL:$1|pagina|paginas}} contine texto con etiquettas de traduction, ma nulle version de iste {{PLURAL:$1|pagina|paginas}} es actualmente marcate pro traduction.',
	'tpt-old-pages' => 'Alcun {{PLURAL:$1|version de iste pagina|versiones de iste paginas}} ha essite marcate pro traduction.',
	'tpt-other-pages' => '{{PLURAL:$1|Un ancian version de iste pagina|Ancian versiones de iste paginas}} es marcate pro traduction,
ma le ultime {{PLURAL:$1|version|versiones}} non pote esser marcate pro traduction.',
	'tpt-discouraged-pages' => 'Le ulterior traduction de iste {{PLURAL:$1|pagina|paginas}} es discoragiate.',
	'tpt-select-prioritylangs' => 'Lista de linguas prioritari separate per commas:',
	'tpt-select-prioritylangs-force' => 'Impedir le traduction in linguas non prioritari',
	'tpt-select-prioritylangs-reason' => 'Motivo:',
	'tpt-sections-prioritylangs' => 'Linguas prioritari',
	'tpt-rev-mark' => 'marcar pro traduction',
	'tpt-rev-unmark' => 'remover del traduction',
	'tpt-rev-discourage' => 'discoragiar',
	'tpt-rev-encourage' => 'restaurar',
	'tpt-rev-mark-tooltip' => 'Marcar le ultime version de iste pagina pro traduction.',
	'tpt-rev-unmark-tooltip' => 'Remover iste pagina del traduction.',
	'tpt-rev-discourage-tooltip' => 'Discoragiar ulterior traductiones de iste pagina.',
	'tpt-rev-encourage-tooltip' => 'Restaurar iste pagina al traduction normal.',
	'translate-tag-translate-link-desc' => 'Traducer iste pagina',
	'translate-tag-markthis' => 'Marcar iste pagina pro traduction',
	'translate-tag-markthisagain' => 'Iste pagina ha <span class="plainlinks">[$1 modificationes]</span> depost le ultime vice que illo esseva <span class="plainlinks">[$2 marcate pro traduction]</span>.',
	'translate-tag-hasnew' => 'Iste pagina contine <span class="plainlinks">[$1 modificationes]</span> le quales non ha essite marcate pro traduction.',
	'tpt-translation-intro' => 'Iste pagina es un <span class="plainlinks">[$1 version traducite]</span> de un pagina [[$2]] e le traduction es complete e actual a $3%.',
	'tpt-languages-legend' => 'Altere linguas:',
	'tpt-languages-zero' => 'Comenciar le traduction in iste lingua',
	'tpt-tab-translate' => 'Traducer',
	'tpt-target-page' => 'Iste pagina non pote esser actualisate manualmente.
Iste pagina es un traduction del pagina [[$1]] e le traduction pote esser actualisate con le [$2 instrumento de traduction].',
	'tpt-unknown-page' => 'Iste spatio de nomines es reservate pro traductiones de paginas de contento.
Le pagina que tu vole modificar non pare corresponder con alcun pagina marcate pro traduction.',
	'tpt-translation-restricted' => 'Le traduction de iste pagina in iste lingua ha essite impedite per un administrator de traductiones.

Motivo: $1',
	'tpt-discouraged-language-force' => 'Un administrator de traductiones ha limitate le linguas in le quales iste pagina pote esser traducite. Iste lingua non es inter le linguas permittite.

Motivo: $1',
	'tpt-discouraged-language' => 'Iste lingua non es inter le linguas prioritari definite per le administrator de traductiones pro iste pagina.

Motivo: $1',
	'tpt-discouraged-language-reason' => 'Motivo: $1',
	'tpt-priority-languages' => 'Un administrator de traduction ha definite le linguas prioritari pro iste gruppo como $1.',
	'tpt-render-summary' => 'Actualisation a un nove version del pagina de origine',
	'tpt-download-page' => 'Exportar pagina con traductiones',
	'aggregategroups' => 'Gruppos aggregate',
	'tpt-aggregategroup-add' => 'Adder',
	'tpt-aggregategroup-save' => 'Salveguardar',
	'tpt-aggregategroup-add-new' => 'Adder un nove gruppo aggregate',
	'tpt-aggregategroup-new-name' => 'Nomine:',
	'tpt-aggregategroup-new-description' => 'Description (optional):',
	'tpt-aggregategroup-remove-confirm' => 'Es tu secur de voler deler iste gruppo aggregate?',
	'tpt-aggregategroup-invalid-group' => 'Gruppo non existe',
	'pt-parse-open' => 'Etiquetta &lt;translate> asymmetric.
Patrono de traduction: <pre>$1</pre>',
	'pt-parse-close' => 'Etiquetta &lt;/translate> asymmetric.
Patrono de traduction: <pre>$1</pre>',
	'pt-parse-nested' => 'Le unitates de traduction &lt;translate> annidate non es permittite.
Texto del etiquetta: <pre>$1</pre>',
	'pt-shake-multiple' => 'Il ha multiple marcatores de unitate de traduction pro un sol unitate de traduction.
Texto del unitate de traduction: <pre>$1</pre>',
	'pt-shake-position' => 'Il ha marcatores de unitate de traduction in un position inexpectate.
Texto del unitate de traduction: <pre>$1</pre>',
	'pt-shake-empty' => 'Unitate de traduction vacue pro le marcator "$1".',
	'log-description-pagetranslation' => 'Registro de actiones ligate al systema de traduction de paginas',
	'log-name-pagetranslation' => 'Registro de traduction de paginas',
	'logentry-pagetranslation-mark' => '$1 {{GENDER:$2|marcava}} $3 pro traduction',
	'logentry-pagetranslation-unmark' => '$1 {{GENDER:$2|removeva}} $3 del paginas a traducer',
	'logentry-pagetranslation-moveok' => '$1 {{GENDER:$2|completava}} le renomination del pagina traducibile $3 a $4',
	'logentry-pagetranslation-movenok' => '$1 {{GENDER:$2|incontrava}} un problema durante le renomination del pagina $3 a $4',
	'logentry-pagetranslation-deletefok' => '$1 {{GENDER:$2|completava}} le deletion del pagina traducibile $3',
	'logentry-pagetranslation-deletefnok' => '$1 {{GENDER:$2|non succedeva}} a deler $3 le qual pertine al pagina traducibile $4',
	'logentry-pagetranslation-deletelok' => '$1 {{GENDER:$2|completava}} le deletion del pagina traducibile $3',
	'logentry-pagetranslation-deletelnok' => '$1 {{GENDER:$2|non succedeva}} a deler $3 le qual pertine al pagina traducibile $4',
	'logentry-pagetranslation-encourage' => '$1 {{GENDER:$2|incoragiava}} le traduction de $3',
	'logentry-pagetranslation-discourage' => '$1 {{GENDER:$2|discoragiava}} le traduction de $3',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1 {{GENDER:$2|removeva}} linguas prioritari del pagina traducibile $3',
	'logentry-pagetranslation-prioritylanguages' => '$1 {{GENDER:$2|definiva}} le linguas prioritari del pagina traducibile $3 como $5',
	'logentry-pagetranslation-prioritylanguages-force' => '$1 {{GENDER:$2|limitava}} le linguas prioritari del pagina traducibile $3 a $5',
	'logentry-pagetranslation-associate' => '$1 {{GENDER:$2|addeva}} le pagina traducibile $3 al gruppo aggregate $4',
	'logentry-pagetranslation-dissociate' => '$1 {{GENDER:$2|removeva}} le pagina traducibile $3 del gruppo aggregate $4',
	'pt-movepage-title' => 'Renominar le pagina traducibile $1',
	'pt-movepage-blockers' => 'Le pagina traducibile non pote esser renominate a causa del sequente {{PLURAL:$1|error|errores}}:',
	'pt-movepage-block-base-exists' => 'Le pagina traducibile de destination "[[:$1]]" jam existe.',
	'pt-movepage-block-base-invalid' => 'Le nomine del pagina traducibile de destination non es un titulo valide.',
	'pt-movepage-block-tp-exists' => 'Le pagina de traduction de destination [[:$2]] existe.',
	'pt-movepage-block-tp-invalid' => 'Le titulo del pagina de traduction de destination pro [[:$1]] esserea invalide (troppo longe?).',
	'pt-movepage-block-section-exists' => 'Le pagina de destination "[[:$2]]" pro le unitate de traduction jam existe.',
	'pt-movepage-block-section-invalid' => 'Le titulo del pagina de destination pro "[[:$1]]" pro le unitate de traduction esserea invalide (troppo longe?).',
	'pt-movepage-block-subpage-exists' => 'Le subpagina de destination [[:$2]] existe.',
	'pt-movepage-block-subpage-invalid' => 'Le titulo del subpagina de destination pro [[:$1]] esserea invalide (troppo longe?).',
	'pt-movepage-list-pages' => 'Lista de paginas a renominar',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Pagina|Paginas}} de traduction',
	'pt-movepage-list-section' => '{{PLURAL:$1|Pagina|Paginas}} de unitate de traduction',
	'pt-movepage-list-other' => 'Altere sub{{PLURAL:$1|pagina|paginas}}',
	'pt-movepage-list-count' => 'In total $1 {{PLURAL:$1|pagina|paginas}} a renominar.',
	'pt-movepage-legend' => 'Renominar pagina traducibile',
	'pt-movepage-current' => 'Nomine actual:',
	'pt-movepage-new' => 'Nove nomine:',
	'pt-movepage-reason' => 'Motivo:',
	'pt-movepage-subpages' => 'Renominar tote le subpaginas',
	'pt-movepage-action-check' => 'Verificar si le renomination es possibile',
	'pt-movepage-action-perform' => 'Facer le renomination',
	'pt-movepage-action-other' => 'Cambiar destination',
	'pt-movepage-intro' => 'Iste pagina special permitte renominar paginas marcate pro traduction.
Le renomination non essera instantanee, proque il essera necessari renominar multe paginas.
Durante le renomination del paginas, il non es possibile interager con le paginas in question.
Le fallimentos essera registrate in le [[Special:Log/pagetranslation|registro de traduction de paginas]] e illos necessita reparation manual.',
	'pt-movepage-logreason' => 'Parte del pagina traducibile $1.',
	'pt-movepage-started' => 'Le pagina de base ha essite renominate.
Per favor verifica le [[Special:Log/pagetranslation|registro de traductiones de paginas]] pro reparar eventual errores e leger le message de completion.',
	'pt-locked-page' => 'Iste pagina es serrate proque le pagina traducibile es actualmente in curso de renomination.',
	'pt-deletepage-lang-title' => 'Le pagina traducite $1 es delite.',
	'pt-deletepage-full-title' => 'Le pagina traducibile $1 es delite.',
	'pt-deletepage-invalid-title' => 'Le pagina specificate non es valide.',
	'pt-deletepage-invalid-text' => 'Le pagina specificate es ni traducibile ni un traduction.',
	'pt-deletepage-action-check' => 'Listar paginas a deler',
	'pt-deletepage-action-perform' => 'Exequer le deletion',
	'pt-deletepage-action-other' => 'Cambiar destination',
	'pt-deletepage-lang-legend' => 'Deler pagina traducite',
	'pt-deletepage-full-legend' => 'Deler pagina traducibile',
	'pt-deletepage-any-legend' => 'Deler pagina traducibile o de traduction',
	'pt-deletepage-current' => 'Nomine del pagina:',
	'pt-deletepage-reason' => 'Motivo:',
	'pt-deletepage-subpages' => 'Deler tote le subpaginas',
	'pt-deletepage-list-pages' => 'Lista de paginas a deler',
	'pt-deletepage-list-translation' => 'Paginas traducite',
	'pt-deletepage-list-section' => 'Paginas de unitate de traduction',
	'pt-deletepage-list-other' => 'Altere subpaginas',
	'pt-deletepage-list-count' => 'In total $1 {{PLURAL:$1|pagina|paginas}} a deler.',
	'pt-deletepage-full-logreason' => 'Parte del pagina traducibile $1.',
	'pt-deletepage-lang-logreason' => 'Parte del pagina traducite $1.',
	'pt-deletepage-started' => 'Per favor verifica in le [[Special:Log/pagetranslation|registro de traduction de paginas]] le existentia de errores e del message de completion.',
	'pt-deletepage-intro' => 'Iste pagina special permitte deler un tote pagina traducibile o un pagina de traduction individual in un certe lingua.
Le deletion non essera instantanee perque tote le paginas que depende de illos essera delite tamben.
Fallimentos essera registrate in le [[Special:Log/pagetranslation|registro de traduction de paginas]] e requirera reparation manual.',
);

/** Indonesian (Bahasa Indonesia)
 * @author Anakmalaysia
 * @author Bennylin
 * @author Farras
 * @author Irwangatot
 * @author IvanLanin
 * @author Rex
 * @author පසිඳු කාවින්ද
 */
$messages['id'] = array(
	'pagetranslation' => 'Penerjemahan halaman',
	'right-pagetranslation' => 'Menandai revisi-revisi halaman untuk diterjemahkan',
	'tpt-desc' => 'Ekstensi untuk menerjemahkan halaman-halaman isi',
	'tpt-section' => 'Unit penerjemahan $1',
	'tpt-section-new' => 'Unit penerjemahan baru. Nama: $1',
	'tpt-section-deleted' => 'Unit penerjemahan $1',
	'tpt-template' => 'Templat halaman',
	'tpt-templatediff' => 'Templat halaman telah diubah.',
	'tpt-diff-old' => 'Teks sebelumnya',
	'tpt-diff-new' => 'Teks baru',
	'tpt-submit' => 'Tandai revisi ini untuk diterjemahkan',
	'tpt-sections-oldnew' => 'Unit-unit penerjemahan baru dan yang telah ada',
	'tpt-sections-deleted' => 'Unit penerjemahan yang dihapus',
	'tpt-sections-template' => 'Templat halaman penerjemahan',
	'tpt-action-nofuzzy' => 'Jangan membatalkan terjemahan',
	'tpt-badtitle' => 'Nama halaman yang diberikan ($1) tidak valid',
	'tpt-nosuchpage' => 'Halaman $1 tidak ada',
	'tpt-oldrevision' => '$2 bukan revisi terakhir dari halaman [[$1]].
Hanya revisi terakhir yang dapat ditandai untuk diterjemahkan.',
	'tpt-notsuitable' => 'Halaman $1 tidak dapat diterjemahkan.
Pastikan bahwa halaman ini memiliki tag <nowiki><translate></nowiki> dan memiliki sintaksis yang valid.',
	'tpt-saveok' => 'Halaman [[$1]] telah ditandai untuk diterjemahkan dengan $2 {{PLURAL:$2|unit penerjemahan|unit penerjemahan}}.
Halaman ini sekarang dapat <span class="plainlinks"[$3 diterjemahkan]</span>.',
	'tpt-badsect' => '"$1" bukanlah nama yang valid untuk unit penerjemahan $2.',
	'tpt-showpage-intro' => 'Berikut adalah daftar bagian baru, bagian yang telah ada, dan bagian yang dihapus.
Sebelum menandai revisi ini untuk diterjemahkan, harap periksa agar perubahan ke bagian-bagian dapat diminimalisasi guna menghindarkan para penerjemah dari melakukan pekerjaan yang tidak diperlukan.', # Fuzzy
	'tpt-mark-summary' => 'Menandai revisi ini untuk diterjemahkan',
	'tpt-edit-failed' => 'Tidak dapat memperbarui halaman: $1',
	'tpt-already-marked' => 'Revisi terakhir halaman ini telah ditandai untuk diterjemahkan.',
	'tpt-unmarked' => 'Halaman $1 tidak lagi ditandai untuk diterjemahkan.',
	'tpt-list-nopages' => 'Tidak ada halaman yang ditandai untuk diterjemahkan atau siap ditandai untuk diterjemahkan.',
	'tpt-new-pages' => '{{PLURAL:$1|Halaman ini berisikan|Halaman-halaman ini berisikan}} teks dengan tag terjemahan, tetapi tidak ada versi {{PLURAL:$1|halaman ini|halaman-halaman ini}} yang sudah ditandai untuk diterjemahkan.',
	'tpt-old-pages' => 'Beberapa revisi dari {{PLURAL:$1|halaman ini|halaman-halaman ini}} telah ditandai untuk diterjemahkan.',
	'tpt-other-pages' => '{{PLURAL:$1|Versi lama dari halaman ini|Versi lama dari halaman ini}} ditandai untuk diterjemahkan,
tetapi {{PLURAL:$1|versi|versi}} terakhir tidak dapat ditandai untuk diterjemahkan.',
	'tpt-rev-unmark' => 'singkirkan halaman ini dari penerjemahan', # Fuzzy
	'translate-tag-translate-link-desc' => 'Terjemahkan halaman ini',
	'translate-tag-markthis' => 'Tandai halaman ini untuk diterjemahkan',
	'translate-tag-markthisagain' => 'Halaman ini telah diubah <span class="plainlinks">[$1 kali]</span> sejak terakhir <span class="plainlinks">[$2 ditandai untuk diterjemahkan]</span>.',
	'translate-tag-hasnew' => 'Halaman ini berisikan <span class="plainlinks">[$1 revisi]</span> yang tidak ditandai untuk diterjemahkan.',
	'tpt-translation-intro' => 'Halaman ini adalah sebuah <span class="plainlinks">[$1 versi terjemahan]</span> dari halaman [[$2]] dan terjemahannya telah selesai $3% dari sumber terkini.',
	'tpt-languages-legend' => 'Bahasa lain:',
	'tpt-target-page' => 'Halaman ini tidak dapat diperbarui secara manual.
Halaman ini adalah terjemahan dari halaman [[$1]] dan terjemahannya dapat diperbarui menggunakan [$2 peralatan penerjemahan].',
	'tpt-unknown-page' => 'Ruang nama ini dicadangkan untuk terjemahan halaman isi.
Halaman yang ingin Anda sunting ini tampaknya tidak memiliki hubungan dengan halaman mana pun yang ditandai untuk diterjemahkan.',
	'tpt-render-summary' => 'Memperbarui ke revisi terbaru halaman sumber',
	'tpt-download-page' => 'Ekspor halaman dengan terjemahan',
	'pt-parse-open' => 'Tag &lt;translate> tidak seimbang.
Templat terjemahan: <pre>$1</pre>',
	'pt-parse-close' => 'Tag &lt;/translate> tidak seimbang.
Templat terjemahan: <pre>$1</pre>',
	'pt-parse-nested' => 'Bagian &lt;translate> bersarang tidak diizinkan.
Teks tanda: <pre>$1</pre>', # Fuzzy
	'pt-shake-multiple' => 'Penanda bagian ganda untuk satu bagian.
Teks bagian: <pre>$1</pre>', # Fuzzy
	'pt-shake-position' => 'Penanda bagian di tempat tak terduka.
Teks bagian: <pre>$1</pre>', # Fuzzy
	'pt-shake-empty' => 'Bagian kosong untuk penanda $1.', # Fuzzy
	'log-description-pagetranslation' => 'Log tindakan yang berhubungan dengan sistem penerjemahan halaman',
	'log-name-pagetranslation' => 'Log penerjemahan halaman',
	'pt-movepage-title' => 'Pindahkan halaman yang dapat diterjemahkan $1',
	'pt-movepage-blockers' => 'Halaman yang dapat diterjemahkan tidak dapat dipindahkan ke nama baru karena {{PLURAL:$1|kesalahan|kesalahan}} berikut:',
	'pt-movepage-block-base-exists' => 'Halaman dasar target [[:$1]] ditemukan.', # Fuzzy
	'pt-movepage-block-base-invalid' => 'Halaman dasar target memiliki judul yang tidak sah.', # Fuzzy
	'pt-movepage-block-tp-exists' => 'Halaman penerjemahan target [[:$2]] ditemukan.',
	'pt-movepage-block-tp-invalid' => 'Judul halaman penerjemahan target untuk [[:$1]] salah (terlalu panjang?).',
	'pt-movepage-block-section-exists' => 'Halaman bagian target [[:$2]] ditemukan.', # Fuzzy
	'pt-movepage-block-section-invalid' => 'Judul halaman bagian target untuk [[:$1]] salah (terlalu panjang?).', # Fuzzy
	'pt-movepage-block-subpage-exists' => 'Subhalaman taget [[:$2]] ditemukan.',
	'pt-movepage-block-subpage-invalid' => 'Judul subhalaman target untuk [[:$1]] salah (terlalu panjang?).',
	'pt-movepage-list-pages' => 'Daftar halaman yang akan dipindahkan',
	'pt-movepage-list-translation' => 'Halaman penerjemahan', # Fuzzy
	'pt-movepage-list-section' => 'Halaman bagian', # Fuzzy
	'pt-movepage-list-other' => 'Subhalaman lain', # Fuzzy
	'pt-movepage-list-count' => 'Secara keseluruhan ada $1 {{PLURAL:$1|halaman|halaman}} yang akan dipindahkan.',
	'pt-movepage-legend' => 'Pindahkan halaman yang dapat diterjemahkan',
	'pt-movepage-current' => 'Nama sekarang:',
	'pt-movepage-new' => 'Nama baru:',
	'pt-movepage-reason' => 'Alasan:',
	'pt-movepage-subpages' => 'Pindahkan semua subhalaman',
	'pt-movepage-action-check' => 'Periksa apabila langkah ini memungkinkan',
	'pt-movepage-action-perform' => 'Lakukan langkah ini',
	'pt-movepage-action-other' => 'Ubah target',
	'pt-movepage-intro' => 'Halaman istimewa ini memungkinkan Anda untuk memindahkan halaman yang ditandai untuk diterjemahkan.
Tindakan pemindahan tidak akan berlangsung seketika karena banyak halaman yang perlu dipindahkan.
Saat halaman dipindahkan, tidak dimungkinkan untuk berinteraksi dengan halaman yang bersangkutan.
Kegagalan akan dicatat di [[Special:Log/pagetranslation|log terjemahan halaman]] dan perlu diperbaiki secara manual.',
	'pt-movepage-logreason' => 'Bagian dari halaman yang dapat diterjemahkan $1.',
	'pt-movepage-started' => 'Halaman dasar telah dipindahkan.
Silakan periksa [[Special:Log/pagetranslation|log penerjemahan halaman]] untuk pesan kesalahan dan penyelesaian.',
	'pt-locked-page' => 'Halaman ini dikunci karena halaman yang dapat diterjemahkan saat ini sedang dipindahkan.',
	'pt-deletepage-reason' => 'Alasan:',
);

/** Igbo (Igbo)
 * @author Ukabia
 */
$messages['ig'] = array(
	'pagetranslation' => 'Ihü kuwariala na asụsụ ozor',
	'tpt-template' => 'Àtụ ihü',
	'tpt-diff-new' => 'Mpkurụ edemede ohúrù',
	'translate-tag-translate-link-desc' => 'Kùwáría ihüá na asụsụ ozor',
	'tpt-languages-legend' => 'Asụsụ ndi ozor:',
	'pt-movepage-list-other' => 'Ihü-íme-ihü nke ozor', # Fuzzy
	'pt-movepage-current' => 'Áhà nke di ùbwá:',
	'pt-movepage-new' => 'Áhà ọhúrù:',
	'pt-movepage-reason' => 'Mgbághapụtà:',
);

/** Icelandic (íslenska)
 * @author Snævar
 */
$messages['is'] = array(
	'translate-tag-translate-link-desc' => 'Þýða þessa síðu',
	'tpt-translation-intro' => 'Þessi síða er <span class="plainlinks">[$1 þýdd útgáfa]</span> af síðunni [[$2]] og þýðingu hennar er $3% lokið.',
	'tpt-languages-legend' => 'Önnur tungumál:',
	'log-name-pagetranslation' => 'Þýðingarskrá',
);

/** Italian (italiano)
 * @author Aushulz
 * @author Beta16
 * @author Civvì
 * @author Darth Kule
 * @author F. Cosoleto
 * @author Gianfranco
 * @author Nemo bis
 * @author VittGam
 * @author Ximo17
 */
$messages['it'] = array(
	'pagetranslation' => 'Traduzione pagine',
	'right-pagetranslation' => 'Segna le pagine come da tradurre',
	'action-pagetranslation' => 'gestire le pagine traducibili',
	'tpt-desc' => 'Estensione per la traduzione di pagine',
	'tpt-section' => 'Elemento $1 della traduzione',
	'tpt-section-new' => 'Nuovo elemento della traduzione.
Nome: $1',
	'tpt-section-deleted' => 'Elemento $1 della traduzione',
	'tpt-template' => 'Modello della pagina',
	'tpt-templatediff' => 'Il modello della pagina è cambiato.',
	'tpt-diff-old' => 'Testo precedente',
	'tpt-diff-new' => 'Testo successivo',
	'tpt-submit' => 'Segna questa versione per la traduzione',
	'tpt-sections-oldnew' => 'Elementi della traduzione nuovi ed esistenti',
	'tpt-sections-deleted' => 'Elementi della traduzione cancellati',
	'tpt-sections-template' => 'Modello della pagina di traduzione',
	'tpt-action-nofuzzy' => 'Non invalidare le traduzioni',
	'tpt-badtitle' => 'Il nome fornito per la pagina ($1) non è un titolo valido',
	'tpt-nosuchpage' => 'La pagina $1 non esiste',
	'tpt-oldrevision' => "$2 non è l'ultima versione della pagina [[$1]].
Solo le ultime versioni possono essere segnate per la traduzione.",
	'tpt-notsuitable' => 'La pagina $1 non è adatta per la traduzione.
Assicurarsi che abbia i tag <nowiki><translate></nowiki> e una sintassi valida.',
	'tpt-saveok' => 'La pagina [[$1]] è stata segnalata per la traduzione con $2 {{PLURAL:$2|elemento di traduzione|elementi di traduzione}}.
La pagina può ora essere <span class="plainlinks">[$3 tradotta]</span>.',
	'tpt-offer-notify' => 'Puoi <span class="plainlinks">[$1 notificare ai traduttori]</span> questa pagina.',
	'tpt-badsect' => '"$1" non è un nome valido per l\'elemento $2 della traduzione.',
	'tpt-showpage-intro' => 'Di seguito sono elencate gli elementi di traduzione nuovi, esistenti e cancellati.
Prima di segnare questa versione per la traduzione, controllare che i cambiamenti per gli elementi di traduzione siano ridotti al minimo per evitare lavoro superfluo ai traduttori.',
	'tpt-mark-summary' => 'Versione segnata per la traduzione',
	'tpt-edit-failed' => 'Impossibile aggiornare la pagina: $1',
	'tpt-duplicate' => "Il nome dell'elemento di traduzione $1 è usato più di una volta.",
	'tpt-already-marked' => "L'ultima versione di questa pagina è già stata segnata per la traduzione.",
	'tpt-unmarked' => 'La pagina $1 non è più segnata per la traduzione.',
	'tpt-list-nopages' => 'Nessuna pagina è segnata per la traduzione oppure è pronta per essere segnata per la traduzione.',
	'tpt-new-pages-title' => 'Pagine proposte per la traduzione',
	'tpt-old-pages-title' => 'Pagine in traduzione',
	'tpt-other-pages-title' => 'Pagine corrotte',
	'tpt-discouraged-pages-title' => 'Pagine scoraggiate',
	'tpt-new-pages' => '{{PLURAL:$1|Questa pagina contiene|Queste pagine contengono}} testo con tag di traduzione,
ma al momento nessuna versione di {{PLURAL:$1|questa pagina|queste pagine}} è segnata per la traduzione.',
	'tpt-old-pages' => 'Alcune versioni di {{PLURAL:$1|questa pagina|queste pagine}} sono state segnate per la traduzione.',
	'tpt-other-pages' => "{{PLURAL:$1|Una vecchia versione di questa pagina è segnata|Delle vecchie versioni di queste pagine sono segnate}} per la traduzione,
ma {{PLURAL:$1|l'ultima versione non può essere segnata|le ultime versioni non possono essere segnate}} per la traduzione.",
	'tpt-discouraged-pages' => "L'ulteriore traduzione di {{PLURAL:$1|questa pagina|queste pagine}} è scoraggiata.",
	'tpt-select-prioritylangs' => 'Elenco dei codici lingua prioritari separati da virgole:',
	'tpt-select-prioritylangs-force' => 'Evitare le traduzioni in lingue non ritenute prioritarie',
	'tpt-select-prioritylangs-reason' => 'Motivo:',
	'tpt-sections-prioritylangs' => 'Lingue prioritarie',
	'tpt-rev-mark' => 'segna per la traduzione',
	'tpt-rev-unmark' => 'rimuovi dalla traduzione',
	'tpt-rev-discourage' => 'scoraggia',
	'tpt-rev-encourage' => 'ripristina',
	'tpt-rev-mark-tooltip' => "Segna l'ultima versione di questa pagina come da tradurre.",
	'tpt-rev-unmark-tooltip' => 'Rimuovi questa pagina dalla traduzione.',
	'tpt-rev-discourage-tooltip' => 'Scoraggia ulteriori traduzioni di questa pagina.',
	'tpt-rev-encourage-tooltip' => 'Ripristina la traduzione ordinaria di questa pagina.',
	'translate-tag-translate-link-desc' => 'Traduci questa pagina',
	'translate-tag-markthis' => 'Segna questa pagina per la traduzione',
	'translate-tag-markthisagain' => 'Questa pagina è stata <span class="plainlinks">[$1 modificata]</span> da quando era stata <span class="plainlinks">[$2 segnata per la traduzione]</span>.',
	'translate-tag-hasnew' => 'Questa pagina contiene delle <span class="plainlinks">[$1 modifiche]</span> che non sono segnate per la traduzione.',
	'tpt-translation-intro' => 'Questa pagina è una <span class="plainlinks">[$1 versione tradotta]</span> della pagina [[$2]]; la traduzione è completa e aggiornata al $3&nbsp;%.',
	'tpt-languages-legend' => 'Altre lingue:',
	'tpt-languages-zero' => 'Inizia a tradurre in questa lingua',
	'tpt-tab-translate' => 'Traduci',
	'tpt-target-page' => 'Questa pagina non può essere aggiornata manualmente. Questa pagina è una traduzione della pagina [[$1]] e la traduzione può essere aggiornata tramite [$2 lo strumento di traduzione].',
	'tpt-unknown-page' => 'Questo namespace è riservato alle traduzioni del contenuto delle pagine.
La pagina che stai cercando di modificare non sembra corrispondere ad alcuna pagina segnata per la traduzione.',
	'tpt-translation-restricted' => "La traduzione di questa pagina in questa lingua è stata impedita dall'amministratore.

Motivo: $1",
	'tpt-discouraged-language-force' => "L'amministratore ha limitato le lingue in cui questa pagina può essere tradotta. Questa lingua non è compresa tra esse.

Motivo: $1",
	'tpt-discouraged-language' => "Questa lingua non è impostata come prioritaria dall'amministratore delle traduzioni per questa pagina.

Motivo: $1",
	'tpt-discouraged-language-reason' => 'Motivo: $1',
	'tpt-priority-languages' => "L'amministratore ha impostato le lingue prioritarie per questo gruppo in $1.",
	'tpt-render-summary' => 'Aggiornamento come da nuova versione della pagina di origine',
	'tpt-download-page' => 'Esporta la pagina con le traduzioni',
	'aggregategroups' => 'Gruppi aggregati',
	'tpt-aggregategroup-add' => 'Aggiungi',
	'tpt-aggregategroup-save' => 'Salva',
	'tpt-aggregategroup-add-new' => 'Aggiungi un nuovo gruppo aggregato',
	'tpt-aggregategroup-new-name' => 'Nome:',
	'tpt-aggregategroup-new-description' => 'Descrizione (opzionale):',
	'tpt-aggregategroup-remove-confirm' => 'Sei sicuro di voler cancellare questo gruppo aggregato?',
	'tpt-aggregategroup-invalid-group' => 'Il gruppo non esiste',
	'pt-parse-open' => 'Marcatore &lt;translate> sbilanciato.
Struttura della traduzione: <pre>$1</pre>',
	'pt-parse-close' => 'Marcatore &lt;/translate> sbilanciato.
Struttura della traduzione: <pre>$1</pre>',
	'pt-parse-nested' => 'Non sono ammessi elementi di traduzione &lt;translate> nidificati.
Testo del marcatore: <pre>$1</pre>',
	'pt-shake-multiple' => "Sono presenti più marcatori di elementi di traduzione per un singolo elemento.
Testo dell'elemento di traduzione: <pre>$1</pre>",
	'pt-shake-position' => 'Sono presenti marcatori di elementi di traduzione in una posizione inaspettata.
Testo della sezione: <pre>$1</pre>',
	'pt-shake-empty' => 'Elemento di traduzione vuoto per il marcatore $1.',
	'log-description-pagetranslation' => 'Registro per le azioni inerenti al sistema di traduzione delle pagine',
	'log-name-pagetranslation' => 'Traduzioni di pagine',
	'logentry-pagetranslation-mark' => '$1 {{GENDER:$2|ha contrassegnato}} $3 per la traduzione',
	'logentry-pagetranslation-unmark' => '$1 {{GENDER:$2|ha rimosso}} $3 dalla traduzione',
	'logentry-pagetranslation-moveok' => '$1 {{GENDER:$2|ha eseguito}} lo spostamento della pagina traducibile $3 a $4',
	'logentry-pagetranslation-movenok' => '$1 {{GENDER:$2|ha incontrato}} un problema nello spostamento di $3 a $4',
	'logentry-pagetranslation-deletefok' => '$1 {{GENDER:$2|ha eseguito}} la cancellazione della pagina traducibile $3',
	'logentry-pagetranslation-deletefnok' => '$1 {{GENDER:$2|ha incontrato}} un problema nella cancellazione di $3 che appartiene alla pagina da tradurre $4',
	'logentry-pagetranslation-deletelok' => '$1 {{GENDER:$2|ha eseguito}} la cancellazione della pagina di traduzione $3',
	'logentry-pagetranslation-deletelnok' => '$1 {{GENDER:$2|ha incontrato}} un problema nella cancellazione di $3 che appartiene alla pagina da tradurre $4',
	'logentry-pagetranslation-encourage' => '$1 {{GENDER:$2|ha incoraggiato}} la traduzione di $3',
	'logentry-pagetranslation-discourage' => '$1 {{GENDER:$2|ha scoraggiato}} la traduzione di $3',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1 {{GENDER:$2|ha rimosso}} le lingue prioritarie dalla pagina da tradurre $3',
	'logentry-pagetranslation-prioritylanguages' => '$1 {{GENDER:$2|ha impostato}} le lingue prioritarie $5 alla pagina da tradurre $3',
	'logentry-pagetranslation-prioritylanguages-force' => '$1 {{GENDER:$2|ha limitato}} le lingue a $5 per la pagina da tradurre $3',
	'logentry-pagetranslation-associate' => '$1 {{GENDER:$2|ha aggiunto}} la pagina traducibile $3 al gruppo aggregato $4',
	'logentry-pagetranslation-dissociate' => '$1 {{GENDER:$2|ha rimosso}} la pagina traducibile $3 dal gruppo aggregato $4',
	'pt-movepage-title' => 'Sposta la pagina traducibile $1',
	'pt-movepage-blockers' => 'Questa pagina da tradurre non è stata spostata a un nuovo nome per {{PLURAL:$1|il seguente errore|i seguenti errori}}:',
	'pt-movepage-block-base-exists' => 'La pagina base di destinazione [[:$1]] esiste già.',
	'pt-movepage-block-base-invalid' => 'La pagina base di destinazione non ha un titolo valido.',
	'pt-movepage-block-tp-exists' => 'La pagina di traduzione di destinazione [[:$2]] esiste già.',
	'pt-movepage-block-tp-invalid' => 'Il titolo di destinazione della pagina di traduzione di [[:$1]] sarebbe invalido (troppo lungo?).',
	'pt-movepage-block-section-exists' => "La pagina di destinazione dell'elemento di traduzione [[:$2]] esiste già.",
	'pt-movepage-block-section-invalid' => "Il titolo di destinazione della pagina dell'elemento di traduzione di [[:$1]] sarebbe invalido (troppo lungo?).",
	'pt-movepage-block-subpage-exists' => 'La sottopagina di destinazione [[:$2]] esiste già.',
	'pt-movepage-block-subpage-invalid' => 'Il titolo della sottopagina di destinazione di [[:$1]] sarebbe invalido (troppo lungo?).',
	'pt-movepage-list-pages' => 'Elenco di pagine da spostare',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Pagina|Pagine}} di traduzione',
	'pt-movepage-list-section' => '{{PLURAL:$1|Pagina|Pagine}} degli elementi di traduzione',
	'pt-movepage-list-other' => '{{PLURAL:$1|Altra sottopagina|Altre sottopagine}}',
	'pt-movepage-list-count' => '$1 {{PLURAL:$1|pagina|pagine}} in tutto da spostare.',
	'pt-movepage-legend' => 'Sposta pagina traducibile',
	'pt-movepage-current' => 'Nome attuale:',
	'pt-movepage-new' => 'Nuovo nome:',
	'pt-movepage-reason' => 'Motivo:',
	'pt-movepage-subpages' => 'Sposta tutte le sottopagine',
	'pt-movepage-action-check' => 'Verifica se lo spostamento è possibile',
	'pt-movepage-action-perform' => 'Esegui lo spostamento',
	'pt-movepage-action-other' => 'Modifica destinazione',
	'pt-movepage-intro' => 'Questa pagina speciale ti permette di spostare pagine segnate come da tradurre.
Lo spostamento non sarà istantaneo, perché serve spostare molte pagine.
Mentre le pagine vengono spostate, non è possibile interagire con esse.
Gli errori sono riportati nel [[Special:Log/pagetranslation|registro delle traduzioni di pagine]] e devono essere corretti a mano.',
	'pt-movepage-logreason' => 'Parte della pagina traducibile $1',
	'pt-movepage-started' => 'La pagina base è stata spostata.
Controlla il [[Special:Log/pagetranslation|registro delle traduzioni di pagine]] per verificare il messaggio di completamento ed eventuali errori.',
	'pt-locked-page' => 'Questa pagina è protetta perché la pagina traducibile sta per essere spostata.',
	'pt-deletepage-lang-title' => 'Cancellazione della pagina di traduzione $1.',
	'pt-deletepage-full-title' => 'Cancellazione della pagina traducibile $1 in corso.',
	'pt-deletepage-invalid-title' => 'La pagina specificata non è valida.',
	'pt-deletepage-invalid-text' => 'La pagina indicata non è una pagina da tradurre né una pagina di traduzione.',
	'pt-deletepage-action-check' => 'Elenca le pagine da cancellare',
	'pt-deletepage-action-perform' => 'Esegui la cancellazione',
	'pt-deletepage-action-other' => 'Modifica destinazione',
	'pt-deletepage-lang-legend' => 'Cancella pagina di traduzione',
	'pt-deletepage-full-legend' => 'Cancella la pagina traducibile',
	'pt-deletepage-any-legend' => 'Cancella una pagina traducibile o una pagina di traduzione',
	'pt-deletepage-current' => 'Nome della pagina:',
	'pt-deletepage-reason' => 'Motivo:',
	'pt-deletepage-subpages' => 'Cancella tutte le sottopagine',
	'pt-deletepage-list-pages' => 'Elenco di pagine da cancellare',
	'pt-deletepage-list-translation' => 'Pagine di traduzione',
	'pt-deletepage-list-section' => 'Pagine degli elementi di traduzione',
	'pt-deletepage-list-other' => 'Altre sottopagine',
	'pt-deletepage-list-count' => '$1 {{PLURAL:$1|pagina|pagine}} in tutto da cancellare.',
	'pt-deletepage-full-logreason' => 'Parte della pagina traducibile $1',
	'pt-deletepage-lang-logreason' => 'Parte della pagina di traduzione $1',
	'pt-deletepage-started' => 'Controlla il [[Special:Log/pagetranslation|registro delle traduzioni di pagine]] per verificare il messaggio di completamento ed eventuali errori.',
	'pt-deletepage-intro' => 'Questa pagina speciale ti consente di cancellare del tutto una pagina traducibile o una sua traduzione in una lingua.
La cancellazione non sarà istantanea, perché anche tutte le pagine che dipendono da quella dovranno essere cancellate.
Gli errori sono riportati nel [[Special:Log/pagetranslation|registro delle traduzioni di pagine]] e devono essere corretti a mano.',
);

/** Japanese (日本語)
 * @author Aotake
 * @author Fryed-peach
 * @author Shirayuki
 * @author Whym
 * @author 青子守歌
 */
$messages['ja'] = array(
	'pagetranslation' => 'ページ翻訳',
	'right-pagetranslation' => 'ページの版を翻訳対象に指定',
	'action-pagetranslation' => '翻訳対象ページの管理',
	'tpt-desc' => '通常ページの本文を翻訳するための拡張機能',
	'tpt-section' => '翻訳単位 $1',
	'tpt-section-new' => '新しい翻訳単位。
名前: $1',
	'tpt-section-deleted' => '翻訳単位 $1',
	'tpt-template' => 'ページの雛型',
	'tpt-templatediff' => 'ページの雛型が変更されました。',
	'tpt-diff-old' => '前のテキスト',
	'tpt-diff-new' => '新しいテキスト',
	'tpt-submit' => 'この版を翻訳対象に指定',
	'tpt-sections-oldnew' => '新規または既存の翻訳単位',
	'tpt-sections-deleted' => '削除された翻訳単位',
	'tpt-sections-template' => '翻訳ページの雛型',
	'tpt-action-nofuzzy' => '翻訳を失効させない',
	'tpt-badtitle' => '指定したページ名 ($1) は無効です',
	'tpt-nosuchpage' => 'ページ $1 は存在しません',
	'tpt-oldrevision' => '$2 はページ [[$1]] の最新版ではありません。
翻訳対象に指定できるのは最新版のみです。',
	'tpt-notsuitable' => 'ページ $1 は翻訳に対応していません。
<nowiki><translate></nowiki> タグが含まれていて、かつ文法的に正しいことをを確認してください。',
	'tpt-saveok' => 'ページ [[$1]] は翻訳対象に指定されており、$2 {{PLURAL:$2|個の翻訳単位}}を含んでいます。
このページを<span class="plainlinks">[$3 翻訳]</span>できます。',
	'tpt-offer-notify' => 'このページについて<span class="plainlinks">[$1 翻訳者に通知]</span>できます。',
	'tpt-badsect' => '「$1」は翻訳単位$2の名前として有効ではありません。',
	'tpt-showpage-intro' => '以下は、新規・既存の、または削除された翻訳単位の一覧です。
この版を翻訳対象に指定する前に、翻訳単位の変更を最小限にすることで不要な翻訳作業を回避できないか確認してください。',
	'tpt-mark-summary' => 'この版を翻訳対象に指定しました',
	'tpt-edit-failed' => 'ページを更新できませんでした: $1',
	'tpt-duplicate' => '翻訳単位名 $1 は、複数回使用されています。',
	'tpt-already-marked' => 'このページの最新版は既に翻訳対象に指定されています。',
	'tpt-unmarked' => 'ページ $1 を翻訳対象から除去しました。',
	'tpt-list-nopages' => '翻訳対象に指定されているページがないか、翻訳対象に指定する準備ができているページがありません。',
	'tpt-new-pages-title' => '翻訳が提案されているページ',
	'tpt-old-pages-title' => '翻訳対象ページ',
	'tpt-other-pages-title' => '壊れたページ',
	'tpt-discouraged-pages-title' => '翻訳が中止されたページ',
	'tpt-new-pages' => '{{PLURAL:$1|このページ|これらのページ}}は本文に翻訳タグを含んでいますが、
{{PLURAL:$1|このページ|これらのページ}}には現在、翻訳対象に指定されている版がありません。',
	'tpt-old-pages' => '{{PLURAL:$1|このページ|これらのページ}}には翻訳対象に指定された版があります。',
	'tpt-other-pages' => '{{PLURAL:$1|このページの古い版|このページの複数の古い版}}が翻訳対象に指定されていますが、
最新の{{PLURAL:$1|版}}は翻訳対象に指定できません。',
	'tpt-discouraged-pages' => '{{PLURAL:$1|このページ|これらのページ}}の翻訳は中止されたため、これ以上の翻訳は不要です。',
	'tpt-select-prioritylangs' => '優先言語のコードを列挙 (カンマ区切り):',
	'tpt-select-prioritylangs-force' => '優先言語以外への翻訳を禁止',
	'tpt-select-prioritylangs-reason' => '理由:',
	'tpt-sections-prioritylangs' => '優先言語',
	'tpt-rev-mark' => '翻訳対象に指定',
	'tpt-rev-unmark' => '翻訳対象から除去',
	'tpt-rev-discourage' => '翻訳中止',
	'tpt-rev-encourage' => '復元',
	'tpt-rev-mark-tooltip' => 'このページの最新版を翻訳対象に指定します。',
	'tpt-rev-unmark-tooltip' => 'このページを翻訳対象から除去します。',
	'tpt-rev-discourage-tooltip' => 'このページのこれ以上の翻訳を中止します。',
	'tpt-rev-encourage-tooltip' => 'このページを通常の翻訳に復元します。',
	'translate-tag-translate-link-desc' => 'このページを翻訳',
	'translate-tag-markthis' => 'このページを翻訳対象に指定',
	'translate-tag-markthisagain' => 'このページには、最後に<span class="plainlinks">[$2 翻訳対象に指定]</span>された時点以降の<span class="plainlinks">[$1 変更]</span>があります。',
	'translate-tag-hasnew' => 'このページには翻訳対象に指定されていない<span class="plainlinks">[$1 変更]</span>があります。',
	'tpt-translation-intro' => 'このページはページ [[$2]] を「<span class="plainlinks">[$1 翻訳]</span>」したものです。翻訳は $3% 完了しています。',
	'tpt-languages-legend' => '他言語での翻訳:',
	'tpt-languages-zero' => 'この言語について翻訳を開始',
	'tpt-tab-translate' => '翻訳',
	'tpt-target-page' => 'このページは手動では更新できません。
このページはページ [[$1]] の翻訳版であり、[$2 翻訳ツール]を使用して更新できます。',
	'tpt-unknown-page' => 'この名前空間はコンテンツ ページの翻訳のために予約されています。
編集しようとしているページに対応する翻訳対象ページが存在しないようです。',
	'tpt-translation-restricted' => '翻訳管理者がこのページのこの言語への翻訳を禁止しています。

理由: $1',
	'tpt-discouraged-language-force' => "'''このページは $2 に翻訳できません。'''

翻訳管理者がこのページの翻訳先言語を $3 のみに制限しています。",
	'tpt-discouraged-language' => "'''このページの$2への翻訳は重要ではありません。'''
翻訳管理者が$3への翻訳作業に重点を置くことを決めました。",
	'tpt-discouraged-language-reason' => '理由: $1',
	'tpt-priority-languages' => '翻訳管理者が、このメッセージ群の優先言語を $1 に設定しました。',
	'tpt-render-summary' => '翻訳元ページの新版に適合するように更新',
	'tpt-download-page' => '翻訳付きでページを書き出し',
	'aggregategroups' => '集約群',
	'tpt-aggregategroup-add' => '追加',
	'tpt-aggregategroup-save' => '保存',
	'tpt-aggregategroup-add-new' => '新しい集約群を追加',
	'tpt-aggregategroup-new-name' => '名前:',
	'tpt-aggregategroup-new-description' => '説明 (省略可):',
	'tpt-aggregategroup-remove-confirm' => 'この集約群を本当に削除しますか?',
	'tpt-aggregategroup-invalid-group' => 'グループは存在しません',
	'pt-parse-open' => '&lt;translate> タグの対応がとれていません。
翻訳の雛型: <pre>$1</pre>',
	'pt-parse-close' => '&lt;/translate> タグの対応がとれていません。
翻訳の雛型: <pre>$1</pre>',
	'pt-parse-nested' => '&lt;translate> 翻訳単位の入れ子は許されません。
タグの内容: <pre>$1</pre>',
	'pt-shake-multiple' => '1 つの翻訳単位に対して、複数の翻訳単位マーカーがあります。
翻訳単位の内容: <pre>$1</pre>',
	'pt-shake-position' => '予期しない位置に翻訳単位マーカーがあります。
翻訳単位の内容: <pre>$1</pre>',
	'pt-shake-empty' => 'マーカー「$1」に対応する翻訳単位が空です。',
	'log-description-pagetranslation' => 'ページ翻訳システムに関連する操作の記録',
	'log-name-pagetranslation' => 'ページ翻訳記録',
	'logentry-pagetranslation-mark' => '$1 が $3 を翻訳対象に{{GENDER:$2|指定}}',
	'logentry-pagetranslation-unmark' => '$1 が $3 を翻訳から{{GENDER:$2|除去}}',
	'logentry-pagetranslation-moveok' => '$1 が翻訳対象ページ $3 の名前を $4 に変更{{GENDER:$2|完了}}',
	'logentry-pagetranslation-movenok' => '$1 がページ $3 を $4 に移動させる際に問題が{{GENDER:$2|発生}}',
	'logentry-pagetranslation-deletefok' => '$1 が翻訳対象ページ $3 の削除を{{GENDER:$2|完了}}',
	'logentry-pagetranslation-deletefnok' => '$1 が翻訳対象ページ $4 に属する $3 の削除に{{GENDER:$2|失敗}}',
	'logentry-pagetranslation-deletelok' => '$1 が翻訳ページ $3 の削除を{{GENDER:$2|完了}}',
	'logentry-pagetranslation-deletelnok' => '$1 が翻訳ページ $4 に属する $3 の削除に{{GENDER:$2|失敗}}',
	'logentry-pagetranslation-encourage' => '$1 が $3 の翻訳を{{GENDER:$2|復元}}',
	'logentry-pagetranslation-discourage' => '$1 が $3 の翻訳を{{GENDER:$2|中止}}',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1 が翻訳対象ページ $3 から優先言語を{{GENDER:$2|除去}}',
	'logentry-pagetranslation-prioritylanguages' => '$1 が翻訳対象ページ $3 の優先言語を $5 に{{GENDER:$2|設定}}',
	'logentry-pagetranslation-prioritylanguages-force' => '$1 が翻訳対象ページ $3 の言語を $5 に{{GENDER:$2|制限}}',
	'logentry-pagetranslation-associate' => '$1 が翻訳対象ページ $3 を集約群 $4 に{{GENDER:$2|追加}}',
	'logentry-pagetranslation-dissociate' => '$1 が翻訳対象ページ $3 を集約群 $4 から{{GENDER:$2|除去}}',
	'pt-movepage-title' => '翻訳対象ページ「$1」の移動',
	'pt-movepage-blockers' => '以下の{{PLURAL:$1|エラー}}が発生したため、翻訳対象ページを新しいページ名に移動できません:',
	'pt-movepage-block-base-exists' => '移動先の翻訳対象ページ「[[:$1]]」は既に存在します。',
	'pt-movepage-block-base-invalid' => '移動先の翻訳対象ページの名前が無効です。',
	'pt-movepage-block-tp-exists' => '移動先の翻訳ページ「[[:$2]]」は既に存在します。',
	'pt-movepage-block-tp-invalid' => '「[[:$1]]」の移動先の翻訳ページの名前が無効です (長すぎる?)。',
	'pt-movepage-block-section-exists' => '移動先の翻訳単位ページ「[[:$2]]」は既に存在します。',
	'pt-movepage-block-section-invalid' => '「[[:$1]]」の移動先の翻訳単位ページ名が無効です (長すぎる?)。',
	'pt-movepage-block-subpage-exists' => '移動先の下位ページ「[[:$2]]」は既に存在します。',
	'pt-movepage-block-subpage-invalid' => '「[[:$1]]」の移動先の下位ページ名が無効です (長すぎる?)。',
	'pt-movepage-list-pages' => '移動するページの一覧',
	'pt-movepage-list-translation' => '翻訳{{PLURAL:$1|ページ}}',
	'pt-movepage-list-section' => '翻訳単位{{PLURAL:$1|ページ}}',
	'pt-movepage-list-other' => 'その他の下位{{PLURAL:$1|ページ}}',
	'pt-movepage-list-count' => '合計 $1 ページを移動',
	'pt-movepage-legend' => '翻訳対象ページの移動',
	'pt-movepage-current' => '現在の名前:',
	'pt-movepage-new' => '新しい名前:',
	'pt-movepage-reason' => '理由:',
	'pt-movepage-subpages' => '下位ページをすべて移動',
	'pt-movepage-action-check' => '移動できるかどうかチェック',
	'pt-movepage-action-perform' => '移動を実行',
	'pt-movepage-action-other' => '対象を変更',
	'pt-movepage-intro' => 'この特別ページでは、翻訳対象に指定されているページを移動できます。
多くのページを移動する必要があるため、移動操作はすぐには完了しません。
ページの移動中は、そのページの操作はできません。
失敗した場合は、その[[Special:Log/pagetranslation|ページの翻訳記録]]に記録されるため、手動で修正する必要があります。',
	'pt-movepage-logreason' => '翻訳対象ページ「$1」の一部。',
	'pt-movepage-started' => '基底ページが移動されました。
[[Special:Log/pagetranslation|ページの翻訳記録]]で、エラーや完了メッセージを確認してください。',
	'pt-locked-page' => '翻訳対象ページが現在移動中のため、このページはロックされています。',
	'pt-deletepage-lang-title' => '翻訳ページ「$1」を削除中。',
	'pt-deletepage-full-title' => '翻訳対象ページ「$1」を削除中。',
	'pt-deletepage-invalid-title' => '指定したページは無効です。',
	'pt-deletepage-invalid-text' => '指定したページは、翻訳対象ページでも翻訳ページでもありません。',
	'pt-deletepage-action-check' => '削除するページを列挙',
	'pt-deletepage-action-perform' => '削除を実行',
	'pt-deletepage-action-other' => '対象を変更',
	'pt-deletepage-lang-legend' => '翻訳ページの削除',
	'pt-deletepage-full-legend' => '翻訳対象ページの削除',
	'pt-deletepage-any-legend' => '翻訳対象ページまたは翻訳ページの削除',
	'pt-deletepage-current' => 'ページ名:',
	'pt-deletepage-reason' => '理由:',
	'pt-deletepage-subpages' => '下位ページをすべて削除',
	'pt-deletepage-list-pages' => '削除するページの一覧',
	'pt-deletepage-list-translation' => '翻訳ページ',
	'pt-deletepage-list-section' => '翻訳単位ページ',
	'pt-deletepage-list-other' => 'その他の下位ページ',
	'pt-deletepage-list-count' => '合計 $1 ページを削除',
	'pt-deletepage-full-logreason' => '翻訳対象ページ「$1」の一部。',
	'pt-deletepage-lang-logreason' => '翻訳ページ「$1」の一部。',
	'pt-deletepage-started' => '[[Special:Log/pagetranslation|ページの翻訳記録]]で、エラーや完了メッセージを確認してください。',
	'pt-deletepage-intro' => 'この特別ページでは、翻訳対象ページ全体、または指定した言語への翻訳ページを個別に削除できます。
依存関係があるページもすべて削除するため、削除操作はすぐには完了しません。
失敗した場合は[[Special:Log/pagetranslation|ページの翻訳記録]]に記録されるので、手動で修正する必要があります。',
);

/** Jamaican Creole English (Patois)
 * @author Yocahuna
 */
$messages['jam'] = array(
	'pagetranslation' => 'Piej chranslieshan',
	'right-pagetranslation' => 'Maak voerjan a piejdem fi chranslieshan',
	'tpt-desc' => 'Extenshan fi chransliet kantent piejdem',
	'tpt-section' => 'Chranslieshan yuunit $1',
	'tpt-section-new' => 'New chranslieshan yuunit.
Niem: $1',
	'tpt-section-deleted' => 'Chranslieshan yuunit $1',
	'tpt-template' => 'Piej templit',
	'tpt-templatediff' => 'Di piej templit chienj',
	'tpt-diff-old' => 'Priivos tex',
	'tpt-diff-new' => 'Nyuu tex',
	'tpt-submit' => 'Maak dis voerjan fi chranslieshan',
	'tpt-sections-oldnew' => 'Nyuu ahn egzisin chranslieshan yuunit',
	'tpt-sections-deleted' => 'Chranslieshan yuunit wa diliit',
	'tpt-sections-template' => 'Chranslieshan piej templit',
	'tpt-action-nofuzzy' => 'No invalidiet no chranslieshan',
	'tpt-badtitle' => 'Piej niem yu gi ($1) a no valid taikl',
	'tpt-nosuchpage' => 'No piej ($1) no egzis',
	'tpt-oldrevision' => '$2 a no di lietis voerjan a di piej [[$1]].
Onggl lietis voerjan kiahn maak fi chranslieshan.',
	'tpt-notsuitable' => 'Piej $1 no suutobl fi chranslieshan.
Mek shuor se iab <nowiki><translate></nowiki> tag ahn gat valid sintax.',
	'tpt-saveok' => 'Di piej [[$1]] maakop fi chranslieshan wid $2 {{PLURAL:$2|chranslieshan yuunit|chranslieshan yuunit}}.
Di piej kiahn nou get <span class="plainlinks">[$3 chransliet]</span>.',
	'tpt-badsect' => '"$1" a no valid niem fi chranslieshan yuunit $2.',
	'tpt-showpage-intro' => 'Nyuu, egzisin ahn diliitid sekshan lis biluo.
Bifuo yu maak dis voerjan fi chranslieshan, chek se di chienj to sekshandem minimaiz fi avaid anesiseri wok fi chranslietadem.', # Fuzzy
	'tpt-mark-summary' => 'Dis voerjan maak fi chranslieshan',
	'tpt-edit-failed' => 'Kudn opdiet di piej: $1',
	'tpt-already-marked' => 'Di lietis voerjan a dis piej don maak fi chranslieshan aredi.',
	'tpt-unmarked' => 'Piej $1 no langa maak fi chranslieshan.',
	'tpt-list-nopages' => 'No piej no maak fi chranslieshan nar redi fi maak fi chranslieshan.',
	'tpt-old-pages' => 'Som voerjan a {{PLURAL:$1|dis piej|demaya piej}} don maak fi chranslieshan.',
);

/** Javanese (Basa Jawa)
 * @author NoiX180
 * @author Pras
 */
$messages['jv'] = array(
	'pagetranslation' => 'Terjemahan kaca',
	'right-pagetranslation' => 'Tandhai vèrsi kaca kanggo terjemahan',
	'tpt-desc' => 'Èkstènsi kanggo nerjemahaké kaca kontèn',
	'tpt-section' => 'Unit terjemahan $1',
	'tpt-section-new' => 'Unit terjemahan anyar.
Jeneng: $1',
	'tpt-section-deleted' => 'Unit terjemahan $1',
	'tpt-template' => 'Templat kaca',
	'tpt-templatediff' => 'Templat kaca wis diganti.',
	'tpt-diff-old' => 'Tèks sakdurungé',
	'tpt-diff-new' => 'Tèks anyar',
	'tpt-submit' => 'Tandhai vèrsi iki kanggo terjemahan',
	'tpt-sections-oldnew' => 'Unit terjemahan anyar lan sing wis ana',
	'tpt-sections-deleted' => 'Unit terjemahan sing wis dibusak',
	'tpt-sections-template' => 'Témplat kaca terjemahan',
	'tpt-badtitle' => 'Jeneng kaca sing diawèhaké ($1) dudu judhul sing sah',
	'tpt-nosuchpage' => 'Kaca $1 ora ana',
	'tpt-oldrevision' => '$2 dudu vèrsi pungkasan saka kaca [[$1]].
Namung vèrsi pungkasan sing bisa ditandhani kanggo terjemahan.',
	'tpt-notsuitable' => 'Kaca $1 ora cocok diterjemahaké.
Pesthekaké kuwi nduwèni tag <nowiki><translate></nowiki> lan sintaks sing sah.',
	'tpt-badsect' => '"$1" dudu jeneng sing sah kanggo unit terjemahan $2.',
	'tpt-mark-summary' => 'Tandhai vèrsi iki kanggo terjemahan',
	'tpt-edit-failed' => 'Ora bisa nganyari kaca: $1',
	'tpt-duplicate' => 'Jeneng unit terjemahan $1 dianggo luwih saka pisan.',
	'tpt-already-marked' => 'Vèrsi pungkasan kaca iki wis ditandhai kanggo terjemahan.',
	'tpt-unmarked' => 'Kaca $1 ora manèh ditandhani kanggo terjemahan.',
	'tpt-list-nopages' => 'Ora ana kaca sing ditandhai kanggo terjemahan utawa siap ditandhai kanggo terjemahan.',
	'tpt-new-pages-title' => 'Kaca sing ditawakaké kanggo terjemahan',
	'tpt-old-pages-title' => 'Kaca nèng terjemahan',
	'tpt-other-pages-title' => 'Kaca rusak',
	'tpt-new-pages' => '{{PLURAL:$1|Kaca iki kaisi|Kaca iki kaisi}} tèks mawa tag terjemahan,
nangung ora ana vèrsi {{PLURAL:$1|kaca iki|kaca iki}} lagi ditandhai kanggo terjemahan.',
	'tpt-old-pages' => 'Sebagéyan vèrsi {{PLURAL:$1|kaca iki|kaca iki}} wis ditandhai kanggo terjemahan.',
	'tpt-other-pages' => '{{PLURAL:$1|Vèrsi lawas kaca iki|Vèrsi lawas kaca iki}} ditandhai kanggo terjemahan,
namung {{PLURAL:$1|vèrsi|vèrsi}} pungkasan ora bisa ditandhai kanggo terjemahan.',
	'tpt-select-prioritylangs' => 'Dhaptar kodhe basa prioritas diwatesi nganggo koma:',
	'tpt-select-prioritylangs-force' => 'Tolak terjemahan nèng basa liya kajaba basa prioritas', # Fuzzy
	'tpt-select-prioritylangs-reason' => 'Alesan:',
	'tpt-sections-prioritylangs' => 'Basa prioritas',
	'tpt-rev-mark' => 'tandhai kanggo terjemahan',
	'tpt-rev-unmark' => 'busak saka terjemahan',
	'tpt-rev-encourage' => 'balèkaké',
	'tpt-rev-mark-tooltip' => 'Tandhai vèrsi pungkasan kaca iki kanggo terjemahan.',
	'tpt-rev-unmark-tooltip' => 'Busak kaca iki saka terjemahan.',
	'tpt-rev-encourage-tooltip' => 'Balekaké kaca iki nèng terjemahan biasa.',
	'translate-tag-translate-link-desc' => 'Terjemahaké kaca iki',
	'translate-tag-markthis' => 'Tandhai kaca iki kanggo terjemahan',
	'translate-tag-markthisagain' => 'Kaca iki nduwèni <span class="plainlinks">[$1 owahan]</span> kawit pungkasan <span class="plainlinks">[$2 ditandhai kanggo terjemahan]</span>.',
	'translate-tag-hasnew' => 'Kaca iki kaisi <span class="plainlinks">[$1 owahan]</span> sing ora ditandhai kanggo terjemahan.',
	'tpt-translation-intro' => 'Kaca iki <span class="plainlinks">[$1 vèrsi sing wis diterjemahaké]</span> saka kaca [[$2]] lan terjemahan wis rampung $3%.',
	'tpt-languages-legend' => 'Basa liya:',
	'tpt-languages-zero' => 'Lekasi terjemahan kanggo basa iki',
	'tpt-target-page' => 'Kaca iki ora bisa dianyari manual.
Kaca iki kaca terjemahan [[$1]] lan terjemahan bisa dianyari nganggo [$2 prangkat terjemahan].',
	'tpt-unknown-page' => 'Bilik jeneng iki dicadhangaké kanggo terjemahan kaca kontèn.
Kaca sing arep Sampéyan sunting kayané ora ana kaitané karo kaca sing ditandhai kanggo terjemahan.',
	'tpt-translation-restricted' => 'Terjemahan kaca iki nèng basa iki ditolak déning pangurus terjemahan.

Alesan: $1',
	'tpt-discouraged-language-force' => "'''Kaca iki ora bisa diterjemahaké nèng $2.'''

Pangurus terjemahan milih supaya kaca iki namung bisa diterjemahaké nèng $3.",
	'tpt-discouraged-language' => "'''Nerjemahaké nèng $2 dudu prioritas kanggo kaca iki.'''

Pangurus terjemahan milih fokus nèng upaya terjemahan nèng $3.",
	'tpt-discouraged-language-reason' => 'Alesan: $1',
	'tpt-priority-languages' => 'Pangurus terjemahan nyetèl basa prioritas kanggo klompok iki dadi $1.',
	'tpt-render-summary' => 'Nganyari kanggo nyocokaké vèrsi anyar kaca sumber',
	'tpt-download-page' => 'Èkspor kaca mawa terjemahan',
	'tpt-aggregategroup-add' => 'Tambah',
	'tpt-aggregategroup-save' => 'Simpen',
	'tpt-aggregategroup-add-new' => 'Tambah klompok agrégat anyar',
	'tpt-aggregategroup-new-name' => 'Jeneng:',
	'tpt-aggregategroup-new-description' => 'Katrangan (pilihan):',
	'tpt-aggregategroup-remove-confirm' => 'Sampéyan yakin arep mbusak klompok agrégat iki?',
	'tpt-aggregategroup-invalid-group' => 'Klompok ora ana',
	'pt-parse-open' => 'Tag &lt;translate> ora imbang.
Templat terjemahan: <pre>$1</pre>',
	'pt-parse-close' => 'Tag &lt;/translate> ora imbang.
Templat terjemahan: <pre>$1</pre>',
	'log-description-pagetranslation' => 'Log laku sing ana kaitané karo sistem terjemahan kaca',
	'log-name-pagetranslation' => 'Log terjemahan kaca',
	'pt-movepage-title' => 'Pindhah kaca "$1" sing bisa diterjemahaké',
	'pt-movepage-blockers' => 'Kaca sing bisa diterjemahaké ora bisa dipindhah nèng jeneng anyar amarga {{PLURAL:$1|kasalahan|kasalahan}} iki:',
	'pt-movepage-block-base-exists' => 'Kaca dhasar patujon "[[:$1]]" ana.', # Fuzzy
	'pt-movepage-block-base-invalid' => 'Kaca dhasar patujon dudu judhul sing sah.', # Fuzzy
	'pt-movepage-block-tp-exists' => 'Kaca terjemahan patujon "[[:$2]]" ana.',
	'pt-movepage-block-tp-invalid' => 'Judhul kaca terjemahan patujon kanggo "[[:$1]]" ora sah (kadawan?).',
	'pt-movepage-block-section-exists' => 'Kaca sèksi patujon "[[:$2]]" ana.', # Fuzzy
	'pt-movepage-block-section-invalid' => 'Judhul kaca sèksi patujon kanggo "[[:$1]]" ora sah (kadawan?).', # Fuzzy
	'pt-movepage-block-subpage-exists' => 'Subkaca patujon "[[:$2]]" ana.',
	'pt-movepage-block-subpage-invalid' => 'Judhul kaca subkkaca patujon kanggo "[[:$1]]" ora sah (kadawan?).',
	'pt-movepage-list-pages' => 'Daptar kaca sing arep dipindhah',
	'pt-movepage-list-translation' => 'Kaca terjemahan', # Fuzzy
	'pt-movepage-list-section' => 'Kaca sèksi', # Fuzzy
	'pt-movepage-list-other' => 'Subkaca liya', # Fuzzy
	'pt-movepage-list-count' => 'Kabèhé $1 {{PLURAL:$1|kaca|kaca}} sing arep dipindhah.',
	'pt-movepage-legend' => 'Pindhah kaca sing bisa diterjemahaké',
	'pt-movepage-current' => 'Jeneng saiki:',
	'pt-movepage-new' => 'Jeneng anyar:',
	'pt-movepage-reason' => 'Alesan:',
	'pt-movepage-subpages' => 'Pindhak kabèh subkaca',
	'pt-movepage-action-check' => 'Priksa yèn pamindhahan bisa dilakokaké',
	'pt-movepage-action-perform' => 'Pindhahaké',
	'pt-movepage-action-other' => 'Ganti patujon',
	'pt-movepage-intro' => 'Kaca astamiwa iki nglilakaké Sampéyan mindhahaké kaca sing ditandhai kanggo terjemahan.
Pamidhahan ora bakal gelis, amarga akèh kaca sing kudu dipindhahaké.
Nalika kaca dipindhahaké, ora bisa interaksi karo kaca sing dimaksud.
Kagagalan bakal dilebokaké nèng [[Special:Log/pagetranslation|log terjemahan kaca]] lan ora perlu dibenahi manual.',
	'pt-movepage-logreason' => 'Bagéyan kaca "$1" sing bisa diterhemahaké.',
	'pt-movepage-started' => 'Kaca dhasar saiki wis dipindhah.
Mangga priksa [[Special:Log/pagetranslation|log pamindhahan kaca]] kanggo layang kasalahan lan parampungan.',
	'pt-locked-page' => 'Kaca iki digembok amarga kaca sing bisa diterjemahaké saiki wis dipindhah.',
	'pt-deletepage-lang-title' => 'Mbusak kaca "$1" sing bisa diterjemahaké.',
	'pt-deletepage-full-title' => 'Mbusak kaca "$1" sing bisa diterjemahaké.',
	'pt-deletepage-invalid-title' => 'Kaca sing dimaksud ora sah.',
	'pt-deletepage-invalid-text' => 'Kaca sing dimaksud dudu kaca sing bisa diterjemahaké utawa terjemahan saka kuwi.', # Fuzzy
	'pt-deletepage-action-check' => 'Daptar kaca sing arep dibusak',
	'pt-deletepage-action-perform' => 'Busak',
	'pt-deletepage-action-other' => 'Ganti patujon',
	'pt-deletepage-lang-legend' => 'Busak kaca terjemahan',
	'pt-deletepage-full-legend' => 'Busak kaca sing bisa diterjemahaké',
	'pt-deletepage-any-legend' => 'Busak kaca sing bisa diterjemahaké utawa terjemahan saka kaca sing bisa diterjemahaké', # Fuzzy
	'pt-deletepage-current' => 'Jeneng kaca:',
	'pt-deletepage-reason' => 'Alesan:',
	'pt-deletepage-subpages' => 'Busak kabèh subkaca',
	'pt-deletepage-list-pages' => 'Daptar kaca sing arep dibusak',
	'pt-deletepage-list-translation' => 'Kaca terjemahan',
	'pt-deletepage-list-section' => 'Kaca sèksi', # Fuzzy
	'pt-deletepage-list-other' => 'Subkaca liya',
	'pt-deletepage-list-count' => 'Kabèhé $1 {{PLURAL:$1|kaca|kaca}} sing arep dibusak.',
	'pt-deletepage-full-logreason' => 'Bagéyan kaca "$1" sing bisa diterhemahaké.',
	'pt-deletepage-lang-logreason' => 'Bagéyan kaca terjemahan "$1".',
	'pt-deletepage-started' => 'Mangga priksa [[Special:Log/pagetranslation|log terjemahan kaca]] kanggo layang kasalahan lan parampungan.',
);

/** Georgian (ქართული)
 * @author BRUTE
 * @author David1010
 * @author Temuri rajavi
 */
$messages['ka'] = array(
	'pagetranslation' => 'გვერდის თარგმანი',
	'tpt-section' => 'თარგმნის ბლოკი $1',
	'tpt-section-new' => 'თარგმნის ახალი ბლოკი.
სახელი: $1',
	'tpt-section-deleted' => 'თარგმნის ბლოკი $1',
	'tpt-template' => 'გვერდის თარგი',
	'tpt-diff-old' => 'წინა ტექსტი',
	'tpt-diff-new' => 'ახალი ტექსტი',
	'tpt-sections-template' => 'თარგმნის გვერდის თარგი',
	'tpt-old-pages-title' => 'გვერდები თარგმნის პროცესში',
	'tpt-other-pages-title' => 'დაზიანებული გვერდები',
	'tpt-select-prioritylangs-reason' => 'მიზეზი:',
	'tpt-sections-prioritylangs' => 'პრიორიტეტული ენები',
	'tpt-rev-mark' => 'თარგმნისათვის მონიშვნა',
	'tpt-rev-discourage' => 'გამორიცხვა',
	'tpt-rev-encourage' => 'აღდგენა',
	'translate-tag-translate-link-desc' => 'ამ გვერდის თარგმნა',
	'tpt-languages-legend' => 'სხვა ენები:',
	'tpt-discouraged-language-reason' => 'მიზეზი: $1',
	'tpt-aggregategroup-add' => 'დამატება',
	'tpt-aggregategroup-save' => 'შენახვა',
	'tpt-aggregategroup-new-name' => 'სახელი:',
	'tpt-aggregategroup-new-description' => 'აღწერა (არასავალდებულო):',
	'tpt-aggregategroup-invalid-group' => 'ჯგუფი არ არსებობს',
	'log-name-pagetranslation' => 'გვერდის თარგმნის ჟურნალი',
	'pt-movepage-block-subpage-exists' => 'სამიზნე ქვეგვერდი "[[:$2]]" უკვე არსებობს.',
	'pt-movepage-list-pages' => 'გადასატანი გვერდების სია',
	'pt-movepage-list-translation' => 'სათარგმნი {{PLURAL:$1|გვერდი|გვერდები}}',
	'pt-movepage-list-other' => 'სხვა ქვე{{PLURAL:$1|გვერდი|გვერდები}}',
	'pt-movepage-current' => 'მიმდინარე სახელი:',
	'pt-movepage-new' => 'ახალი სახელი:',
	'pt-movepage-reason' => 'მიზეზი:',
	'pt-movepage-subpages' => 'ყველა ქვეგვერდის გადატანა',
	'pt-movepage-action-other' => 'მიზნის შეცვლა',
	'pt-deletepage-action-other' => 'სამიზნის შეცვლა',
	'pt-deletepage-current' => 'გვერდის სახელი:',
	'pt-deletepage-reason' => 'მიზეზი:',
	'pt-deletepage-subpages' => 'ყველა ქვეგვერდის წაშლა',
	'pt-deletepage-list-translation' => 'სათარგმნი გვერდები',
	'pt-deletepage-list-other' => 'სხვა ქვეგვერდები',
);

/** Адыгэбзэ (Адыгэбзэ)
 * @author Тамэ Балъкъэрхэ
 */
$messages['kbd-cyrl'] = array(
	'tpt-diff-old' => 'Ипэ ит текстыр',
	'tpt-diff-new' => 'ТекстыщIэ',
	'translate-tag-translate-link-desc' => 'НапэкIуэцIыр зэхъуэкIын',
	'tpt-languages-legend' => 'НэгъуэщIыбзэхэр:',
);

/** Khmer (ភាសាខ្មែរ)
 * @author គីមស៊្រុន
 * @author វ័ណថារិទ្ធ
 */
$messages['km'] = array(
	'pagetranslation' => 'ការ​បក​ប្រែ​ទំព័រ​',
	'tpt-section' => 'ឯកតាបកប្រែ $1',
	'tpt-section-new' => 'ឯកតាបកប្រែថ្មី។
ឈ្មោះ៖ $1',
	'tpt-section-deleted' => 'ឯកតាបកប្រែ $1',
	'tpt-template' => 'គំរូទំព័រ',
	'tpt-templatediff' => 'គំរូ​ទំព័រ​បានផ្លាស់ប្តូរ​។',
	'tpt-diff-old' => 'អត្ថបទ​​ពីមុន​',
	'tpt-diff-new' => 'អត្ថបទ​ថ្មី​',
	'tpt-submit' => 'សម្គាល់​កំណែ​នេះ​សម្រាប់​ការបកប្រែ​',
	'tpt-sections-oldnew' => 'ឯកតាបកប្រែថ្មីនិងចាស់',
	'tpt-sections-deleted' => 'ឯកតាបកប្រែដែលត្រូវបានលុប',
	'tpt-sections-template' => 'គំរូ​ទំព័រ​បកប្រែ​',
	'tpt-badtitle' => 'ឈ្មោះ​ទំព័រ​សម្រាប់ ($1) គឺមិនមែន​ជា​ចំនងជើង​ត្រឹមត្រូវ​',
	'tpt-mark-summary' => 'បាន​សម្គាល់​កំណែ​នេះ​សម្រាប់​បកប្រែ​',
	'tpt-edit-failed' => 'មិនអាច​បន្ទាន់សម័យ​ទំព័រ​៖ $1',
	'tpt-already-marked' => 'កំណែ​ចុងក្រោយ​នៃទំព័រ​នេះ​ត្រូវបាន​សម្គាល់​ទុកសម្រាប់​បកប្រែ​។',
	'translate-tag-translate-link-desc' => 'បកប្រែទំព័រនេះ',
	'translate-tag-markthis' => 'សម្គាល់​ទំព័រ​​នេះ​សម្រាប់​ការបកប្រែ​',
	'tpt-languages-legend' => 'ជាភាសាដទៃទៀត៖',
);

/** Kannada (ಕನ್ನಡ)
 * @author Nayvik
 */
$messages['kn'] = array(
	'translate-tag-translate-link-desc' => 'ಈ ಪುಟವನ್ನು ಅನುವಾದಿಸಿ',
	'tpt-languages-legend' => 'ಇತರ ಭಾಷೆಗಳು:',
	'pt-movepage-reason' => 'ಕಾರಣ:',
);

/** Korean (한국어)
 * @author Freebiekr
 * @author Kwj2772
 * @author 아라
 */
$messages['ko'] = array(
	'pagetranslation' => '문서 번역',
	'right-pagetranslation' => '번역에 대한 문서의 버전 표시',
	'action-pagetranslation' => '번역 가능한 문서 관리',
	'tpt-desc' => '내용 문서를 번역하기 위한 확장 기능',
	'tpt-section' => '번역 단위 $1',
	'tpt-section-new' => '새 번역 단위입니다.
이름: $1',
	'tpt-section-deleted' => '번역 단위 $1',
	'tpt-template' => '문서 틀',
	'tpt-templatediff' => '문서 틀이 바뀌었습니다.',
	'tpt-diff-old' => '이전 텍스트',
	'tpt-diff-new' => '새 텍스트',
	'tpt-submit' => '번역에 대해 이 버전 표시',
	'tpt-sections-oldnew' => '새로 및 기존 번역 단위',
	'tpt-sections-deleted' => '삭제된 번역 단위',
	'tpt-sections-template' => '번역 문서 틀',
	'tpt-action-nofuzzy' => '번역을 무효화하지 마세요',
	'tpt-badtitle' => '주어진 문서 이름($1)은 올바른 제목이 아닙니다',
	'tpt-nosuchpage' => '$1 문서가 존재하지 않습니다',
	'tpt-oldrevision' => '$2 버전은 [[$1]] 문서의 최신 버전이 아닙니다.
최신 버전만 번역에서 표시할 수 있습니다.',
	'tpt-notsuitable' => '$1 문서는 번역에 적합하지 않습니다.
<nowiki><translate></nowiki> 태그가 있고 올바른 문법인지 확인하세요.',
	'tpt-saveok' => '[[$1]] 문서는 $2 {{PLURAL:$2|번역 단위}}로 번역에 대해 표시했습니다.
문서는 지금 <span class="plainlinks">[$3 번역]</span>할 수 있습니다.',
	'tpt-offer-notify' => '이 문서에 대해 <span class="plainlinks">[$1 번역자 알림]</span>을 받을 수 있습니다.',
	'tpt-badsect' => '"$1"(은)는 $2 번역 유닉에 대해 올바른 이름이 아닙니다.',
	'tpt-showpage-intro' => '다음은 새로와 기존, 삭제된 번역 단위가 나열되어 있습니다.
번역에 대한 이 버전을 표시하기 전에 번역 단위에 대한 바뀜이 번역에 대한 불필요한 작업을 피하기 위해 최소화되어 있는지 확인하세요.',
	'tpt-mark-summary' => '번역에 대해 이 버전 표시함',
	'tpt-edit-failed' => '문서를 업데이트를 할 수 없습니다: $1',
	'tpt-duplicate' => '$1 번역 단위 이름은 한 번 이상 사용합니다.',
	'tpt-already-marked' => '이 문서의 최신 버전은 번역에 대해 이미 표시했습니다.',
	'tpt-unmarked' => '$1 문서는 번역에 대해 더 이상 표시하지 않습니다.',
	'tpt-list-nopages' => '문서가 번역에 대해 표시하거나 번역에 대해 표시할 준비가 없습니다.',
	'tpt-new-pages-title' => '문서 번역에 대한 제안',
	'tpt-old-pages-title' => '번역한 문서',
	'tpt-other-pages-title' => '잘못된 문서',
	'tpt-discouraged-pages-title' => '없어진 문서',
	'tpt-new-pages' => '{{PLURAL:$1|이 문서는}} 번역 태그로 글자를 포함합니다,
하지만 현재 번역에서 표시한 {{PLURAL:$1|이 문서}}의 버전이 없습니다.',
	'tpt-old-pages' => '번역으로 표시한 {{PLURAL:$1|이 문서}}의 일부 버전입니다.',
	'tpt-other-pages' => '{{PLURAL:$1|이 문서의 오래된 버전}}은 번역에서 표시했습니다,
하지만 최신 {{PLURAL:$1|버전}}은 번역에서 표시할 수 없었습니다.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|이 문서는}} 추가 번역에서 없어졌습니다.',
	'tpt-select-prioritylangs' => '우선 언어 코드의 쉼표로 구분한 목록:',
	'tpt-select-prioritylangs-force' => '우선 언어 이외의 언어로 번역 방지',
	'tpt-select-prioritylangs-reason' => '이유:',
	'tpt-sections-prioritylangs' => '우선 언어',
	'tpt-rev-mark' => '번역한 것으로 표시',
	'tpt-rev-unmark' => '번역에서 제거',
	'tpt-rev-discourage' => '번역 중단',
	'tpt-rev-encourage' => '복구',
	'tpt-rev-mark-tooltip' => '번역에 대한 이 문서의 최신 버전을 표시합니다.',
	'tpt-rev-unmark-tooltip' => '번역에서 이 문서를 제거합니다.',
	'tpt-rev-discourage-tooltip' => '이 문서에 대한 추가적인 번역을 중단합니다.',
	'tpt-rev-encourage-tooltip' => '이 문서를 정상적인 번역으로 복원합니다.',
	'translate-tag-translate-link-desc' => '이 문서 번역하기',
	'translate-tag-markthis' => '이 문서를 번역 대상으로 표시',
	'translate-tag-markthisagain' => '이 문서에는 최근 <span class="plainlinks">[$2 번역 대상으로 표시]</span>한 이후 <span class="plainlinks">[$1 바뀜]</span>이 있습니다.',
	'translate-tag-hasnew' => '이 문서에는 번역 대상으로 표시하지 않은 <span class="plainlinks">[$1 바뀜]</span>을 포함합니다.',
	'tpt-translation-intro' => '이 문서는 [[$2]] 문서를 <span class="plainlinks">[$1 번역한 것]</span>이며 번역은 $3% 완료했습니다.',
	'tpt-languages-legend' => '다른 언어:',
	'tpt-languages-zero' => '이 언어에 대한 번역 시작',
	'tpt-tab-translate' => '번역하기',
	'tpt-target-page' => '이 문서를 수동으로 업데이트할 수 없습니다.
이 문서는 [[$1]]의 번역이며 번역은 [$2 번역 도구]를 사용하여 업데이트할 수 있습니다.',
	'tpt-unknown-page' => '이 이름공간은 콘텐츠 페이지 번역에 대해 예약되어 있습니다.
편집하려고 하는 문서는 번역에 대해 표시한 모든 문서와 일치하지 않는 것 같습니다.',
	'tpt-translation-restricted' => '이 언어로의 이 문서의 번역은 번역 관리자에 의해 차단되었습니다.

이유: $1',
	'tpt-discouraged-language-force' => "'''이 문서는 $2(으)로 번역할 수 없습니다.'''

번역 관리자는 이 문서가 $3(으)로 번역할 수 있도록 결정했습니다.",
	'tpt-discouraged-language' => "'''$2로 번역하는 것은 이 문서에 대해 우선 순위가 아닙니다.'''

번역 관리자는 $3에 번역 노력을 집중하기로 결정합니다.",
	'tpt-discouraged-language-reason' => '이유: $1',
	'tpt-priority-languages' => '번역 관리자는 $1(으)로 이 그룹에 대해 우선 언어를 설정합니다.',
	'tpt-render-summary' => '원본 문서의 새 버전에 맞게 업데이트',
	'tpt-download-page' => '번역 문서 내보내기',
	'aggregategroups' => '집계 그룹',
	'tpt-aggregategroup-add' => '추가',
	'tpt-aggregategroup-save' => '저장',
	'tpt-aggregategroup-add-new' => '새 집계 그룹 추가',
	'tpt-aggregategroup-new-name' => '이름:',
	'tpt-aggregategroup-new-description' => '설명 (선택):',
	'tpt-aggregategroup-remove-confirm' => '이 총 그룹을 삭제하겠습니까?',
	'tpt-aggregategroup-invalid-group' => '그룹이 존재하지 않습니다',
	'pt-parse-open' => '불균형한 &lt;translate> 태그입니다.
번역 틀: <pre>$1</pre>',
	'pt-parse-close' => '불균형한 &lt;/translate> 태그입니다.
번역 틀: <pre>$1</pre>',
	'pt-parse-nested' => '중첩한 &lt;translate> 번역 단위는 허용하지 않습니다.
태그 텍스트: <pre>$1</pre>',
	'pt-shake-multiple' => '한 번역 단위에 여러 번역 단위를 표시했습니다.
번역 단위 텍스트: <pre>$1</pre>',
	'pt-shake-position' => '예상하지 않은 위치에 번역 단위를 표시했습니다.
번역 단위 텍스트: <pre>$1</pre>',
	'pt-shake-empty' => '"$1" 표시에 대한 빈 번역 단위입니다.',
	'log-description-pagetranslation' => '문서 번역 시스템에 관련된 작업에 대한 기록',
	'log-name-pagetranslation' => '문서 번역 기록',
	'logentry-pagetranslation-mark' => '$1 사용자가 번역으로 $3(을)를 {{GENDER:$2|표시했습니다}}',
	'logentry-pagetranslation-unmark' => '$1 사용자가 번역에서 $3(을)를 {{GENDER:$2|제거했습니다}}',
	'logentry-pagetranslation-moveok' => '$1 사용자가 $3 번역 가능한 문서를 $4 문서로 이름 바꾸기를 {{GENDER:$2|완료했습니다}}',
	'logentry-pagetranslation-movenok' => '$1 사용자가 $3 문서를 $4 문서로 옮기는 동안 문제가 {{GENDER:$2|발생했습니다}}',
	'logentry-pagetranslation-deletefok' => '$1 사용자가 $3 번역 가능한 문서의 삭제를 {{GENDER:$2|완료했습니다}}',
	'logentry-pagetranslation-deletefnok' => '$1 사용자가 $4 번역 가능한 문서에 속한 $3 문서를 삭제하는 데 {{GENDER:$2|실패했습니다}}',
	'logentry-pagetranslation-deletelok' => '$1 사용자가 $3 번역 문서의 삭제를 {{GENDER:$2|완료했습니다}}',
	'logentry-pagetranslation-deletelnok' => '$1 사용자가 $4 번역 문서에 속한 $3 문서를 삭제하는 데 {{GENDER:$2|실패했습니다}}',
	'logentry-pagetranslation-encourage' => '$1 사용자가 $3의 번역을 {{GENDER:$2|되살렸습니다}}',
	'logentry-pagetranslation-discourage' => '$1 사용자가 $3의 번역을 {{GENDER:$2|중단했습니다}}',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1 사용자가 $3 번역 가능한 문서에서 우선 언어를 {{GENDER:$2|제거했습니다}}',
	'logentry-pagetranslation-prioritylanguages' => '$1 사용자가 $3 번역 가능한 문서에 대한 우선 언어를 $5로 {{GENDER:$2|설정했습니다}}',
	'logentry-pagetranslation-prioritylanguages-force' => '$1 사용자가 $3 번역 가능한 문서에 대한 언어를 $5로 {{GENDER:$2|제한했습니다}}',
	'logentry-pagetranslation-associate' => '$1 사용자가 $3 번역 가능한 문서를 $4 집계 그룹에 {{GENDER:$2|추가했습니다}}',
	'logentry-pagetranslation-dissociate' => '$1 사용자가 $3 번역 가능한 문서를 $4 집계 그룹에서 {{GENDER:$2|제거했습니다}}',
	'pt-movepage-title' => '"$1" 번역 가능한 문서 이동',
	'pt-movepage-blockers' => '번역 가능한 문서는 다음 {{PLURAL:$1|오류}} 때문에 새 이름으로 옮길 수 없습니다:',
	'pt-movepage-block-base-exists' => '"[[:$1]]" 대상 번역 가능한 문서가 존재합니다.',
	'pt-movepage-block-base-invalid' => '대상 번역 가능한 문서 이름은 올바른 제목이 아닙니다.',
	'pt-movepage-block-tp-exists' => '"[[:$2]]" 대상 번역 문서가 존재합니다.',
	'pt-movepage-block-tp-invalid' => '"[[:$1]]"에 대한 대상 번역 문서 제목은 올바르지 않습니다. (너무 길어요?)',
	'pt-movepage-block-section-exists' => '번역 단위에 대한 "[[:$2]]" 대상 문서가 존재합니다.',
	'pt-movepage-block-section-invalid' => '번역 단위에 대한 "[[:$1]]"에 대한 대상 부분 문서 제목은 올바르지 않습니다. (너무 길어요?)',
	'pt-movepage-block-subpage-exists' => '"[[:$2]]" 대상 하위 문서가 존재합니다.',
	'pt-movepage-block-subpage-invalid' => '"[[:$1]]"에 대한 대상 하위 문서 제목은 올바르지 않습니다. (너무 길어요?)',
	'pt-movepage-list-pages' => '이동할 문서 목록',
	'pt-movepage-list-translation' => '번역 {{PLURAL:$1|문서}}',
	'pt-movepage-list-section' => '번역 단위 {{PLURAL:$1|문서}}',
	'pt-movepage-list-other' => '다른 하위 {{PLURAL:$1|문서}}',
	'pt-movepage-list-count' => '이동할 {{PLURAL:$1|문서}} 총 $1개입니다.',
	'pt-movepage-legend' => '번역 가능한 문서 이동',
	'pt-movepage-current' => '현재 이름:',
	'pt-movepage-new' => '새 이름:',
	'pt-movepage-reason' => '이유:',
	'pt-movepage-subpages' => '모든 하위 문서 이동',
	'pt-movepage-action-check' => '옮길 수 있는지 확인',
	'pt-movepage-action-perform' => '이동하기',
	'pt-movepage-action-other' => '대상 바꾸기',
	'pt-movepage-intro' => '이 특수 문서는 번역에 대해 표시한 문서를 이동할 수 있습니다.
많은 문서가 이동해야 하기 때문에 즉시 이동 작업이 되지 않습니다.
문서를 이동하는 동안 질문의 문서와 상호 작용하는 것은 불가능합니다.
실패하면 [[Special:Log/pagetranslation|문서 번역 기록]]에 기록되고 직접 복구할 필요가 있습니다.',
	'pt-movepage-logreason' => '"$1" 번역 가능한 문서의 부분입니다.',
	'pt-movepage-started' => '기본 페이지가 지금 옮겨졌습니다.
오류에 대해 [[Special:Log/pagetranslation|문서 번역 기록]]을 확인하고 메시지를 완료하세요.',
	'pt-locked-page' => '이 문서는 번역 가능한 문서가 현재 이동하고 있기 때문에 잠겨 있습니다.',
	'pt-deletepage-lang-title' => '"$1" 번역 문서를 삭제하고 있습니다.',
	'pt-deletepage-full-title' => '"$1" 번역 가능한 문서를 삭제하고 있습니다.',
	'pt-deletepage-invalid-title' => '지정한 문서가 올바르지 않습니다.',
	'pt-deletepage-invalid-text' => '지정한 문서는 번역 가능한 문서도 번역 문서도 아닙니다.',
	'pt-deletepage-action-check' => '삭제될 문서 목록',
	'pt-deletepage-action-perform' => '삭제하기',
	'pt-deletepage-action-other' => '대상 바꾸기',
	'pt-deletepage-lang-legend' => '번역 문서 삭제',
	'pt-deletepage-full-legend' => '번역 가능한 문서 삭제',
	'pt-deletepage-any-legend' => '번역 가능한 문서 또는 번역 문서 삭제',
	'pt-deletepage-current' => '문서 이름:',
	'pt-deletepage-reason' => '이유:',
	'pt-deletepage-subpages' => '모든 하위 문서 삭제',
	'pt-deletepage-list-pages' => '삭제할 문서 목록',
	'pt-deletepage-list-translation' => '번역 문서',
	'pt-deletepage-list-section' => '번역 단위 문서',
	'pt-deletepage-list-other' => '다른 하위 문서',
	'pt-deletepage-list-count' => '삭제할 {{PLURAL:$1|문서}} 총 $1개입니다.',
	'pt-deletepage-full-logreason' => '"$1" 번역 가능한 문서의 부분입니다.',
	'pt-deletepage-lang-logreason' => '"$1" 번역 문서의 부분입니다.',
	'pt-deletepage-started' => '오류와 메시지 완료를 위한 [[Special:Log/pagetranslation|문서 번역 기록]]를 확인하세요.',
	'pt-deletepage-intro' => '이 특수 문서는 전체 번역 가능한 문서 또는 언어의 개별 번역 문서를 삭제할 수 있습니다.
번역에 따라 모든 문서가 삭제되기 때문에 즉시 삭제 작업이 되지 않습니다.
실패하면 [[Special:Log/pagetranslation|문서 번역 기록]]에 기록되고 직접 복구해야 합니다.',
);

/** Colognian (Ripoarisch)
 * @author Purodha
 */
$messages['ksh'] = array(
	'pagetranslation' => 'Sigge Övversäze',
	'right-pagetranslation' => 'Donn Versione vun Sigge för et Övversäze makeere',
	'action-pagetranslation' => 'övversäzbaa Sigge ze verwallde',
	'tpt-desc' => 'Projrammzohsatz för Sigge vum Enhalt vum Wiki ze övversäze.',
	'tpt-section' => 'Knubbel $1 för ze Övversäze',
	'tpt-section-new' => 'Ene neue Knubbel för ze Övversäze: $1',
	'tpt-section-deleted' => 'Knubbel $1 för ze Övversäze',
	'tpt-template' => 'Siggeschabloon',
	'tpt-templatediff' => 'De Siggeschabloon hät sesch jeändert.',
	'tpt-diff-old' => 'Dä vörrijje Täx',
	'tpt-diff-new' => 'Dä neue Täx',
	'tpt-submit' => 'Donn hee di Version för et Övversäze makeere',
	'tpt-sections-oldnew' => 'De Knubbelle för ze Övversäze (Jez neu, un de älldere, zosamme)',
	'tpt-sections-deleted' => 'Fottjeschmeße Knubbelle för et Övversäze',
	'tpt-sections-template' => 'Övversäzungßsiggschabloon',
	'tpt-action-nofuzzy' => 'Donn de Övversäzunge nit als övverhollt makeere',
	'tpt-badtitle' => 'Dä Name „$1“ es keine jöltijje Tittel för en Sigg',
	'tpt-nosuchpage' => 'De Sigg „$1“ jidd_et nit.',
	'tpt-oldrevision' => '„$2“ es nit de neuste Version fun dä Sigg „[[$1]]“, ävver bloß de neuste kam_mer för et Övversäze makeere.',
	'tpt-notsuitable' => 'Di Sigg „$1“ paß nit för et Övversäze. Maach <code><nowiki><translate></nowiki></code>-Makeerunge erin, un looer dat de Süntax shtemmp.',
	'tpt-saveok' => 'De Sigg „$1“ es för ze Övversäze makeet. Doh dren {{PLURAL:$2|es eine Knubbel|sinn_er $2 Knubbelle|es ävver keine Knubbel}} för ze Övversäze. Di Sigg kam_mer <span class="plainlinks">[$3 jäz övversäze]</span>.',
	'tpt-badsect' => '„$1“ es kein jöltejje Name för dä Knubbel zom Övversäze $2.',
	'tpt-showpage-intro' => 'Hee dronger sen Övversäzongsaffschnedde opjeleß, di eruß jenumme woode, di neu sin, un di noch doh sin. Ih dat De hee di Version för ze Övversäze makeere deihß, loor drop, dat esu winnisch wi müjjelesch Änderonge aan Övversäzongsaffschnedde doh sin, öm dä Övversäzere et Levve leisch ze maache.',
	'tpt-mark-summary' => 'Han di Version för ze Övversäze makeet',
	'tpt-edit-failed' => 'Kunnt de Sigg „$1“ nit ändere',
	'tpt-duplicate' => 'Dä Name „$1“ för ene Knubbel kütt mieh wi eijmohl vör.',
	'tpt-already-marked' => 'De neuste Version vun dä Sigg es ald för zem Övversäze makeet.',
	'tpt-unmarked' => 'De Sigg „$1“ es nit ieh för ze övversäze makeet.',
	'tpt-list-nopages' => 'Et sinn_er kein Sigge för zem Övversäze makeet, un et sin och kein doh, wo esu en Makeerunge eren künnte.',
	'tpt-new-pages-title' => 'Sigge vörjeschonn för et Övversäze',
	'tpt-old-pages-title' => 'Sigge zom Övversäze',
	'tpt-other-pages-title' => 'Kapodde Sigge',
	'tpt-discouraged-pages-title' => 'Sigge för nit mieh ze övversäze',
	'tpt-new-pages' => '{{PLURAL:$1|Di Sigg hät|Di Sigge han|Kein Sigg hät}} ene <code lang="en">translation</code>-Befähl en sesch, ävve kei Version dofun es för ze Övversäze makeet.',
	'tpt-old-pages' => 'En Version vun hee dä {{PLURAL:$1|Sigg|Sigge|-}} es för zem Övversäze makeet.',
	'tpt-other-pages' => '{{PLURAL:$1|En ällder Version vun heh dä Sigg es|$1 ällder Versione vun heh dä Sigg sin}} för et Övversäze frei jejovve, ävver de neuste Version löht sesh nit frei jävve.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Di Sigg sullt|Di Sigge sullte|Kein Sigg sullt}} nit mieh övversaz wääde.',
	'tpt-select-prioritylangs' => 'De Leß met de Köözelle för de vörjetrocke Schprooche, met Kommas dozwesche:',
	'tpt-select-prioritylangs-force' => 'Bloß noh de vörjetrocke Schprooche Övversäzze',
	'tpt-select-prioritylangs-reason' => 'Jrond:',
	'tpt-sections-prioritylangs' => 'Vörjetrocke Schprooche',
	'tpt-rev-mark' => 'zom Övversäze freijävve',
	'tpt-rev-unmark' => 'Donn heh di Sigg vum Övversäze ußschleeße',
	'tpt-rev-discourage' => 'vum Övversäze ußnämme',
	'tpt-rev-encourage' => 'wider zerök holle',
	'tpt-rev-mark-tooltip' => 'Donn de neuste Version vun dä Sigg för et Övversäzze freijävve',
	'tpt-rev-unmark-tooltip' => 'Donn di Sigg vum Övversäzze ußschleeße.',
	'tpt-rev-discourage-tooltip' => 'Di Sigg sullt nit mieh wigger övversaz wääde. Donn dat faßhallde.',
	'tpt-rev-encourage-tooltip' => 'Donn di Sigg wider wi jewöhmliesch för et Övversäze frei jävve.',
	'translate-tag-translate-link-desc' => 'Don di Sigg hee övversäze',
	'translate-tag-markthis' => 'Donn heh di Sigg för et Övversäze makeere',
	'translate-tag-markthisagain' => 'Hee di Sigg es <span class="plainlinks">[$1 jeändert woode]</span> zick se et läz <span class="plainlinks">[$2 för ze Övversäze]</span> makeet woode es.',
	'translate-tag-hasnew' => 'Hee di Sigg <span class="plainlinks">[$1 es jeändert woode]</span>, es ävver nit för ze Övversäze makeet woode.',
	'tpt-translation-intro' => 'Hee di Sigg es en <span class="plainlinks">[$1 övversaz Version]</span> vun dä Sigg „[[$2]]“ un es zoh $3% jedonn un om aktoälle Shtandt.',
	'tpt-languages-legend' => 'Ander Schprooche:',
	'tpt-languages-zero' => 'Donn ens loßlääje mem Övversäze en heh di Schprooch',
	'tpt-target-page' => 'Hee di Sigg kam_mer nit vun Hand ändere. Dat hee es en Översäzungß_Sigg vun dä Sigg [[$1]]. De Övversäzung kam_mer övver däm Wiki sing [$2 Övversäzungß_Wärkzüsch] op der neußte Shtand bränge.',
	'tpt-unknown-page' => 'Dat Appachtemang hee es för Sigge vum Enhallt vum Wiki ze Övversäze jedaach. Di Sigg, di de jraad ze ändere versöhks, schingk ävver nit met ööhnds en Sigg ze donn ze han, di för zem Övversäze makeet es.',
	'tpt-translation-restricted' => 'Et en di Schprooch hät ene Verwallder vum Övversäzze för heh di Sigg verbodde.

Jrond: $1',
	'tpt-discouraged-language-force' => "'''Heh di Sigg kam_mer nit op $2 övversäze.'''

Ene Verwallder vum Övversäzze hät faßjelaat, dat di Sigg bloß op $3 övversaz wääde sull.",
	'tpt-discouraged-language' => "'''Heh di Sigg op $2 övversäze hät keine Vörrang.'''

Ene Verwallder vum Övversäzze hät faßjelaat, dat di Sigg en de Houpsaach op $3 övversaz wääde sull.",
	'tpt-discouraged-language-reason' => 'Jrond: $1',
	'tpt-priority-languages' => 'Ene Verwallder vum Övversäzze hät de Houpschprooche för heh di Jropp op $1 jesaz.',
	'tpt-render-summary' => 'Ändere, öm op de neue Version fun de Ojinaal_Sigg ze kumme',
	'tpt-download-page' => 'Sigge met Övversäzunge expotteere',
	'aggregategroups' => 'Sammeljroppe',
	'tpt-aggregategroup-add' => 'Dobei donn',
	'tpt-aggregategroup-save' => 'Faßhalde',
	'tpt-aggregategroup-add-new' => 'Donn en neu Sammeljropp derbei',
	'tpt-aggregategroup-new-name' => 'Dä Name vun dä Jropp:',
	'tpt-aggregategroup-new-description' => 'Wat en dä Sammeljrobb es (kann läddesch blieve):',
	'tpt-aggregategroup-remove-confirm' => 'Wells De di Sammeljropp verhaftesch fott schmiiße?',
	'tpt-aggregategroup-invalid-group' => 'Di Jropp jidd_et nit',
	'pt-parse-open' => 'En &lt;translate&gt; es ohne Jääjeshtöck.
De Siggeschabloon för ze övversäze: <pre>$1</pre>',
	'pt-parse-close' => 'En &lt;/translate&gt; es ohne Jääjeshtöck.
De Siggeschabloon för ze övversäze: <pre>$1</pre>',
	'pt-parse-nested' => 'En einem &lt;translate> Övversäzongsaffschned kann nit noch eine su ene Affschned dren shteishe.
Dä Täx vun dä Makeerung es: <pre>$1</pre>',
	'pt-shake-multiple' => 'Mieh wi eine Makeerung för dersellve Övversäzongsaffschned es nit müjjelesh.
Dä Täx vun däm Övversäzongsaffschned es: <pre>$1</pre>',
	'pt-shake-position' => 'Makeerunge för Övversäzongsaffschnedde sin aan dä Pusizjuhn nit müjjelesh.
Dä Täx vun däm Affschned es: <pre>$1</pre>',
	'pt-shake-empty' => 'Em Övversäzongsaffschned met dä Makeerong „$1“ es nix dren.',
	'log-description-pagetranslation' => 'Logbooch för di Saache, di mem Sigge Övversäze ze donn han',
	'log-name-pagetranslation' => 'Logbooch vum Sigge Övversäze',
	'logentry-pagetranslation-mark' => '{{GENDER:$2|Dä|Dat|Dä Metmaacher|De|Dat}} $1 hät di Sigg „$3“ zum Övversäze freijejovve.',
	'logentry-pagetranslation-unmark' => '{{GENDER:$2|Dä|Dat|Dä Metmaacher|De|Dat}} $1 hät di Sigg „$3“ vum Övversäze ußjenumme.',
	'logentry-pagetranslation-moveok' => 'Et Ömbenänne vun dä övversäzbaare Sigg „$3“ op „$4“ es fäädesch, wat {{GENDER:$2|vum|vum|vumm Metmaacher|vun dä|vum}} $1 aanjeschtüßße wood.',
	'logentry-pagetranslation-movenok' => 'Et Ömbenänne vun dä övversäzbaare Sigg „$3“ op „$4“ es donäve jejange, {{GENDER:$2|wat dä|wat dat|wat dä Metmaacher|wat de|wadd et}} $1 aanjeschtüßße hatt.',
	'logentry-pagetranslation-deletefok' => 'Et Fottschmiiße vun dä övversäzbaare Sigg „$3“ es fäädesch, wat {{GENDER:$2|vum|vum|vumm Metmaacher|vun dä|vum}} $1 aanjeschtüßße wood.',
	'logentry-pagetranslation-deletefnok' => 'Di Sigg „$3“ — ene Deil vun dä övversäzbaare Sigg „$4“ — fottzeschmiiße, es donäve jejange, {{GENDER:$2|wat dä|wat dat|wat dä Metmaacher|wat de|wadd et}} $1 aanjeschtüßße hatt.',
	'logentry-pagetranslation-deletelok' => 'Et Fottschmiiße vun dä Övversäzongs_Sigg „$3“ es fäädesch, wat {{GENDER:$2|vum|vum|vumm Metmaacher|vun dä|vum}} $1 aanjeschtüßße wood.',
	'logentry-pagetranslation-deletelnok' => 'Di Sigg „$3“ — ene Deil vun dä Övversäzongs_Sigg „$4“ — fottzeschmiiße, es donäve jejange, {{GENDER:$2|wat dä|wat dat|wat dä Metmaacher|wat de|wadd et}} $1 aanjeschtüßße hatt.',
	'logentry-pagetranslation-encourage' => '{{GENDER:$2|Dä|Dat|Dä Metmaacher|De|Dat}} $1 schleit di Sigg „$3“ zom Övversäze för.',
	'logentry-pagetranslation-discourage' => '{{GENDER:$2|Dä|Dat|Dä Metmaacher|De|Dat}} $1 schleit vör, di Sigg „$3“ nit ze övversäze.',
	'logentry-pagetranslation-prioritylanguages-unset' => '{{GENDER:$2|Dä|Dat|Dä Metmaacher|De|Dat}} $1 hät de vörjetrock Schprooche för et Övveräzonge för di Sigg „$3“ fottjeschmeße.',
	'logentry-pagetranslation-prioritylanguages' => '{{GENDER:$2|Dä|Dat|Dä Metmaacher|De|Dat}} $1 hät de vörjetrock Schprooche för et Övveräzonge för di Sigg „$3“ op $5 jesaz.',
	'logentry-pagetranslation-prioritylanguages-force' => '{{GENDER:$2|Dä|Dat|Dä Metmaacher|De|Dat}} $1 hät de Övveräzonge för di Sigg „$3“ beschrängk op $5.',
	'logentry-pagetranslation-associate' => '{{GENDER:$2|Dä|Dat|Dä Metmaacher|De|Dat}} $1 hät di övveräzbaa Sigg „$3“ en di Sammeljropp „$4“ jedonn.',
	'logentry-pagetranslation-dissociate' => '{{GENDER:$2|Dä|Dat|Dä Metmaacher|De|Dat}} $1 hät di övveräzbaa Sigg „$3“ uß dä Sammeljropp „$4“ erußjehollt.',
	'pt-movepage-title' => 'De övversäzbaa Sigg „$1“ ömnänne',
	'pt-movepage-blockers' => 'Di övversäbaa Sigg künne mer nit ömbenänne. {{PLURAL:$1|Der Jrond es:|De Jrönd sin:|Mer weße ävver kein Jrönd doför.}}',
	'pt-movepage-block-base-exists' => 'De övversäzbaa Zielsigg „[[:$1]]“ jidd_et ald.',
	'pt-movepage-block-base-invalid' => 'Di aanjejovve Zielsigg hät keine jölteje Siggetittel.',
	'pt-movepage-block-tp-exists' => 'De övversäzbaa Zielsigg „[[:$2]]“ jidd_et ald.',
	'pt-movepage-block-tp-invalid' => 'De aanjejovve övversäzbaa Zielsigg iere Tittel för „[[:$1]]“ wöhr nit jöltejsch, Velleisch zoh lang?',
	'pt-movepage-block-section-exists' => 'En Zielsigg met dämm Övversäzongsafschned „[[:$2]]“ jidd_et ald.',
	'pt-movepage-block-section-invalid' => 'Dä Tittel för di Sigg för dä Övversäzongsafschned för „[[:$1]]“ wöhr nit jöltejsch, Velleisch zoh lang?',
	'pt-movepage-block-subpage-exists' => 'De Ziel_Ongersigg „[[:$2]]“ jidd_et ald.',
	'pt-movepage-block-subpage-invalid' => 'Dä Tittel för de Onger_Sigg för „[[:$1]]“ wöhr nit jöltejsch, Velleisch zoh lang?',
	'pt-movepage-list-pages' => 'De Leß met dä Sigge zom Ömbenänne',
	'pt-movepage-list-translation' => 'Övversaz {{PLURAL:$1|Sigg|Sigge}}',
	'pt-movepage-list-section' => 'Övversäzongsaffschnets_{{PLURAL:$1|Sigg|Sigge}}',
	'pt-movepage-list-other' => 'Ander Onger_{{PLURAL:$1|Sigg|Sigge}}',
	'pt-movepage-list-count' => 'Ensjesamp ham_mer {{PLURAL:$1|ein Sigg|$1 Sigge|kein Sigg}} för ömzenänne.',
	'pt-movepage-legend' => 'Övversäzbaa Sigg ömnänne',
	'pt-movepage-current' => 'Der Name em Momang:',
	'pt-movepage-new' => 'Der neue Name:',
	'pt-movepage-reason' => 'För et Logbooch, der Aanlaß:',
	'pt-movepage-subpages' => 'De Ongersigge all met ömnänne',
	'pt-movepage-action-check' => 'Fengk erus, ov dat Ömnänne müjjlesch es',
	'pt-movepage-action-perform' => 'Ömnänne!',
	'pt-movepage-action-other' => 'Ander Zieltittel',
	'pt-movepage-intro' => 'Heh di Extrasigg löht Desh Sigge ömdäufe, di för et Övversäze frei jejovve sin.
Dat jeiht nit en einem Rötsch, weil ene Pöngel Sigge un -Deile ömjenannt wääde möße.
Em MediaWiki sing <i lang="en"> [http://www.mediawiki.org/wiki/Manual:Job_queue job queue] </i> weed doför jebruch.
Su lang, wi de Sigge ömjenannt wääde, kam_mer met dänne nix söns maache.
Fähler kumme en et [[Special:Log/pagetranslation|{{int:pt-log-name}}]] un möße vun Hand opjerühmp wääde.',
	'pt-movepage-logreason' => 'Deil vun dä övversäzbaa Sigg „$1“',
	'pt-movepage-started' => 'Di Sigg weed jäz ömjenannt.
Don op jede Fall em [[Special:Log/pagetranslation|{{int:pt-log-name}}]] noh Fähler loore, un dat dat öhndlesch aan et Eng jekumme es.',
	'pt-locked-page' => 'Dat Stöck heh is jesperrt, däm sing övversäbaa Sigg weed nämmisch jrad ömbenannt.',
	'pt-deletepage-lang-title' => 'De Övversäzongssigg „$1“ fottschmieße',
	'pt-deletepage-full-title' => 'De övversäzbaa Sigg „$1“ fottschmieße.',
	'pt-deletepage-invalid-title' => 'Di aanjejovve Sigg es nit jöltesch.',
	'pt-deletepage-invalid-text' => 'Di aanjejovve Sigg es kein övversäbaa Sigg un och kein Övversäzong vun einer.',
	'pt-deletepage-action-check' => 'Don de Sigge opleßte, di fott sulle',
	'pt-deletepage-action-perform' => 'Maach nu fott!',
	'pt-deletepage-action-other' => 'Nemm ene andere Zieltittel',
	'pt-deletepage-lang-legend' => 'Övversäzongssigg fottschmieße',
	'pt-deletepage-full-legend' => 'En övversäzbaa Sigg fottschmieße',
	'pt-deletepage-any-legend' => 'En övversäzbaa Sigg udder övversaz Sigg fottschmieße',
	'pt-deletepage-current' => 'Dä Sigg iere Tittel:',
	'pt-deletepage-reason' => 'Der Jrond:',
	'pt-deletepage-subpages' => 'Schmieß all de Ongersigge fott',
	'pt-deletepage-list-pages' => 'De Leß met dä Sigge zom Fottschmieße',
	'pt-deletepage-list-translation' => 'Övversaz Sigge',
	'pt-deletepage-list-section' => 'Övversäzongsaffschnets_Sigge',
	'pt-deletepage-list-other' => 'Ander Ongersigge',
	'pt-deletepage-list-count' => 'Ensjesamp ham_mer {{PLURAL:$1|ein Sigg|$1 Sigge|kein Sigg}} för fottzeschmieße.',
	'pt-deletepage-full-logreason' => 'Deil vun dä övversäzbaa Sigg „$1“',
	'pt-deletepage-lang-logreason' => 'Deil vun dä övversaz Sigg „$1“',
	'pt-deletepage-started' => 'Loor em [[Special:Log/pagetranslation|Logbooch vun de Övversäzonge]] noh Fähler un Nohreeschte.',
	'pt-deletepage-intro' => 'Heh di Söndersigg määd et müjjelesch. kumplätte övversäbaa Sigge udder Övversäzonge en en beshtemmpte Shprooch fottzeschmieße.
Dat Fottschmieße dohrt e Wielsche, weil alle dervun affhängeje Sigge derbei och fott jeschmeße wääde möße.
Fähler kumme en et [[Special:Log/pagetranslation|Logbooch vum Övversäze]] un möße vun Hand jraadjeröck wääde.',
);

/** Kurdish (Latin script) (Kurdî (latînî)‎)
 * @author George Animal
 * @author Gomada
 */
$messages['ku-latn'] = array(
	'pagetranslation' => 'Wergera rûpelê',
	'right-pagetranslation' => 'Versiyonên rûpelên ji bo wergerê nîşan bike',
	'tpt-diff-old' => 'Nivîsa pêşî',
	'tpt-diff-new' => 'Nivîsa nû',
	'tpt-submit' => 'Vê versiyonê ji bo wergerê îşaret bike',
	'tpt-nosuchpage' => 'Rûpela $1 tune.',
	'tpt-select-prioritylangs-reason' => 'Sedem:',
	'translate-tag-translate-link-desc' => 'Vê rûpelê werrgerîne',
	'translate-tag-markthis' => 'Vê rûpelê ji bo wergerê îşaret bike',
	'tpt-languages-legend' => 'Zimanên din:',
	'tpt-download-page' => 'Rûpela bi wergeran bişîne',
	'tpt-aggregategroup-add' => 'Lê zêde bike',
	'tpt-aggregategroup-new-name' => 'Nav:',
	'pt-movepage-list-translation' => 'Rûpelên wergerê', # Fuzzy
	'pt-movepage-list-other' => 'Binrûpelên din', # Fuzzy
	'pt-movepage-current' => 'Navê niha:',
	'pt-movepage-new' => 'Navê nû:',
	'pt-movepage-reason' => 'Sedem:',
	'pt-movepage-subpages' => 'Hemû binrûpelan bigerîne',
	'pt-deletepage-full-legend' => 'Rûpela wergerê jê bibe',
	'pt-deletepage-current' => 'Navê rûpelê:',
	'pt-deletepage-reason' => 'Sedem:',
	'pt-deletepage-subpages' => 'Hemû binrûpelan jê bibe',
	'pt-deletepage-list-other' => 'Binrûpelên din',
);

/** Kirghiz (Кыргызча)
 * @author Викиней
 */
$messages['ky'] = array(
	'pagetranslation' => 'Которуу барагы',
	'tpt-template' => 'Калып барагы',
	'tpt-diff-new' => 'Жаңы текст',
	'tpt-select-prioritylangs-reason' => 'Себеп:',
	'tpt-sections-prioritylangs' => 'Артыкчылыктуу тилдер',
	'translate-tag-translate-link-desc' => 'Бул баракты которуу',
	'translate-tag-markthis' => 'Бул баракты которуу үчүн белгилөө',
	'tpt-languages-legend' => 'Башка тилдер:',
	'tpt-languages-zero' => 'Ушул тилге которууну баштоо',
	'tpt-discouraged-language-reason' => 'Себеп: $1',
	'tpt-aggregategroup-save' => 'Сактоо',
	'tpt-aggregategroup-new-name' => 'Аталышы:',
	'tpt-aggregategroup-new-description' => 'Баяндамасы (милдеттүү эмес):',
	'pt-movepage-current' => 'Азыркы аты:',
	'pt-movepage-new' => 'Жаңы аты:',
	'pt-movepage-reason' => 'Себеби:',
	'pt-deletepage-current' => 'Барактын аты:',
	'pt-deletepage-reason' => 'Себеп:',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Les Meloures
 * @author Purodha
 * @author Robby
 */
$messages['lb'] = array(
	'pagetranslation' => 'Iwwersetzung vun der Säit',
	'right-pagetranslation' => 'Versioune vu Säite fir Iwwersetzung markéieren',
	'action-pagetranslation' => 'Iwwersetzbar Säit geréieren',
	'tpt-desc' => "Erweiderung fir inhaltlech Säiten z'iwwersetzen",
	'tpt-section' => 'Iwwersetzungseenheet $1',
	'tpt-section-new' => 'Numm: $1',
	'tpt-section-deleted' => 'Iwwersetzungseenheet $1',
	'tpt-template' => 'Säiteschabloun',
	'tpt-templatediff' => "D'Säiteschabloun gouf geännert.",
	'tpt-diff-old' => 'Viregen Text',
	'tpt-diff-new' => 'Neien Text',
	'tpt-submit' => "Dës Versioun fir d'Iwwersetze markéieren",
	'tpt-sections-oldnew' => 'Nei an Iwwersetzungseeenheeten déi et scho gëtt',
	'tpt-sections-deleted' => 'Geläschten Iwwersetzungseenheeten',
	'tpt-sections-template' => 'Iwwersetzung Säiteschabloun',
	'tpt-action-nofuzzy' => 'Invalidéiert keng Iwwersetzungen',
	'tpt-badtitle' => 'De Säitennumm deen ugi gouf ($1) ass kee valabelen Titel',
	'tpt-nosuchpage' => "D'Säit $1 gëtt et net",
	'tpt-oldrevision' => "$2 ass net déi lescht Versioun vun der Säit [[$1]].
Nëmmen déi lescht Versioune kënne fir d'Iwwersetzung markéiert ginn.",
	'tpt-notsuitable' => "D'Säit $1 ass net geeegent fir iwwersat ze ginn.
Vergewëssert Iech ob se <nowiki><translate></nowiki>-Taggen  an eng valabel Syntax huet.",
	'tpt-saveok' => 'D\'Säit [[$1]] gouf fir d\'Iwwersetzung mat $2 {{PLURAL:$2|Iwwersetzungseenheet|Iwwersetzungseenheete}} markéiert.
D\'Säit kann elo <span class="plainlinks">[$3 iwwersat]</span> ginn.',
	'tpt-offer-notify' => 'Dir kënnt iwwer dës Säit <span class="plainlinks">[$1 Iwwersetzer informéieren]</span>.',
	'tpt-badsect' => '"$1" ass kee valbelen Numm fir d\'Iwwersetzungseenheet $2.',
	'tpt-showpage-intro' => "Ënnendrënner stinn déi nei, aktuell a geläschten Abschnitter.
Ier Dir dës Versioun fir d'iwwersetze markéiert, kuckt w.e.g. no datt d'Ännerunge vun den Abschnitter déi iwwersat solle ginn op e Minimum reduzéiert gi fir onnëtz Aarbecht vun den Iwwersetzer ze vermeiden.",
	'tpt-mark-summary' => "huet dës Versioun fir d'Iwwersetzung markéiert",
	'tpt-edit-failed' => "D'Säit $1 konnt net aktualiséiert ginn",
	'tpt-duplicate' => 'Den Numm $1 vun der Iwwersetzungwseenheet gëtt méi wéi eemol benotzt.',
	'tpt-already-marked' => "Déilescht Versioun vun dëser Säit gouf scho fir d'Iwwersetzung markéiert.",
	'tpt-unmarked' => "D'Säit $1 ass net méi fir z'iwwersetze markéiert.",
	'tpt-list-nopages' => "Et si keng Säite fir d'Iwwersetzung markéiert respektiv fäerdeg fir fir d'Iwersetzung markéiert ze ginn.",
	'tpt-new-pages-title' => "Säiten déi fir d'Iwwersetzung virgeschlo goufen",
	'tpt-old-pages-title' => 'Säiten déi iwwersat ginn',
	'tpt-other-pages-title' => 'Futtis Säiten',
	'tpt-discouraged-pages-title' => 'Säiten déi vun der Iwwersetzung zréckgezu sinn',
	'tpt-new-pages' => "Op {{PLURAL:$1|dëser Säit|dëse Säiten}} ass Text mat Iwwersetzungs-Markéierungen, awer keng Versioun vun {{PLURAL:$1|dëser Säit|dëse Säiten}} ass elo fir d'Iwwersetze  markéiert.",
	'tpt-old-pages' => "Eng Versioun vun {{PLURAL:$1|dëser Säit|dëse Säite}} gouf fir d'Iwwersetze markéiert.",
	'tpt-other-pages' => "Al Versioun vun {{PLURAL:$1|dëser Säit|dëse Säite}} sinn als z'iwwesetze markéiert,
awer déi lescht Versioun kann fir d'Iwwersetzung markéiert ginn.",
	'tpt-discouraged-pages' => '{{PLURAL:$1|Dës Säit gouf|Dës Säite goufe}} vun der Lëscht vun de recommandéierten Iwwersetzungen erofgeholl.',
	'tpt-select-prioritylangs' => 'Komma-getrennte Lëscht vun de prioritäre Sproochcoden:',
	'tpt-select-prioritylangs-force' => 'Iwwersetzungen an aner Sprooche wéi déi prioritär Sprooche verhënneren',
	'tpt-select-prioritylangs-reason' => 'Grond:',
	'tpt-sections-prioritylangs' => 'Prioritär Sproochen',
	'tpt-rev-mark' => "markéiere fir z'iwwersetzen",
	'tpt-rev-unmark' => 'Vum Iwwersetzen ewechhuelen',
	'tpt-rev-discourage' => 'Vun der Iwwersetzung zréckzéien',
	'tpt-rev-encourage' => 'restauréieren',
	'tpt-rev-mark-tooltip' => "Déi rezentst Versioun vun dëser Säit fir d'Iwwersetze markéieren.",
	'tpt-rev-unmark-tooltip' => 'Dës Säit vum Iwwersetzen ewechhuelen.',
	'tpt-rev-discourage-tooltip' => 'Weider Iwwersetzunge vun dëser Säit net méi ënnerstëtzten.',
	'tpt-rev-encourage-tooltip' => "Dës Säit nees fir d'Iwwersetze fräiginn",
	'translate-tag-translate-link-desc' => 'Dës Säit iwwersetzen',
	'translate-tag-markthis' => "Dës Säit fir d'Iwwersetzung markéieren",
	'translate-tag-markthisagain' => 'Dës Säit huet <span class="plainlinks">[$1 Ännerungen]</span> zënter datt se fir d\'lescht <span class="plainlinks">[$2 fir d\'Iwwersetzung markéiert gouf]</span>.',
	'translate-tag-hasnew' => 'Op dëser Säit si(nn)s <span class="plainlinks">[$1 Ännerungen]</span> déi net fir d\'iwwersetzung markéiert sinn.',
	'tpt-translation-intro' => 'Dës Säit ass eng <span class="plainlinks">[$1 iwwersate Versioun]</span> vun der Säit [[$2]] an d\'Iwweersetzung ass zu $3 % ofgeschloss an aktuell.',
	'tpt-languages-legend' => 'aner Sproochen:',
	'tpt-languages-zero' => 'Iwwersetzung fir dës Sprooch ufänken',
	'tpt-tab-translate' => 'Iwwersetzen',
	'tpt-target-page' => "Dës Säit kann net manuell aktualiséiert ginn.
Dës Säit ass eng Iwwersetzung vun der Säit [[$1]] an d'Iwwersetzung ka mat Hëllef vun der [$2 Iwwersetzungs-Fonctioun] aktulaiséiert ginn.",
	'tpt-unknown-page' => "Dësen Nummraum ass fir d'Iwwersetze vu Säite mat Inhalt reservéiert.
D'Säit, déi Dir versicht z'änneren, schéngt net mat enger Säit déi fir d'Iwwersetzung markéiert ass ze korrespondéieren.",
	'tpt-translation-restricted' => "D'Iwwersetze vun dëser Säit an dës Sprooch gouf vun engem Iwwersetzungs-Administrateur gespaart.

Grond: $1",
	'tpt-discouraged-language-force' => "'''Dës Säit kann net op $2 iwwersat ginn.'''

En Iwwersetzungs-Administrateur huet decidéiert datt dës Säit nëmmen op $3 iwwersat ka ginn.",
	'tpt-discouraged-language' => "'''D'Iwwersetzung op $2 ass keng Prioritéit fir dës Säit.'''

En Iwwersetzungs-Administrateur huet decidéiert fir d'Iwwersetzungs-Efforten op $3 ze konzentréieren.",
	'tpt-discouraged-language-reason' => 'Grond: $1',
	'tpt-priority-languages' => 'En Iwwersetzungs-Administrateur huet déi prioritiséiert Sprooche fir dëse Grupp op $1 agestallt.',
	'tpt-render-summary' => 'Aktualiséieren fir mat der neier Versioun vun der Quellsäit iwwereneenzestëmmen',
	'tpt-download-page' => 'Säit mat Iwwersetzungen exportéieren',
	'aggregategroups' => 'Gruppen zesummeleeën',
	'tpt-aggregategroup-add' => 'Derbäisetzen',
	'tpt-aggregategroup-save' => 'Späicheren',
	'tpt-aggregategroup-add-new' => 'Eng nei zesummegeluechte Grupp derbäisetzen',
	'tpt-aggregategroup-new-name' => 'Numm:',
	'tpt-aggregategroup-new-description' => 'Beschreiwung (optional):',
	'tpt-aggregategroup-remove-confirm' => 'Sidd Dir sécher datt Dir dëse Grupp läsche wëllt?',
	'tpt-aggregategroup-invalid-group' => 'De Grupp gëtt et net',
	'pt-parse-open' => 'Netsymetreschen &lt;translate&gt;-Tag.
Iwwersetzungsschabloun: <pre>$1</pre>',
	'pt-parse-close' => 'Netsymetreschen &lt;&#47;translate&gt;-Tag.
Iwwersetzungsschabloun: <pre>$1</pre>',
	'pt-parse-nested' => 'Verschachtelt &lt;translate&gt;-Iwweraetzungs-Eenheete sinn net méiglech.
Text vum Tag: <pre>$1</pre>',
	'pt-shake-multiple' => 'E puer Iwwersetzungs-Eenheete-Markéierungen fir eng Iwwersetzungs-Eenheet.
Text vun der Iwwersetzungs-Eenheet: <pre>$1</pre>',
	'pt-shake-position' => 'Markéierungen fir Iwwersetzungseenheeten op enger onerwaarter Plaz.
Text fir Iwwersetzungseenheet: <pre>$1</pre>',
	'pt-shake-empty' => 'Iwwersetzungs-Eenheete fir Marker $1 eidelmaachen.',
	'log-description-pagetranslation' => 'Logbuch vun den Aktiounee a Verbindung mat dem System vun der Säiteniwwersetzung',
	'log-name-pagetranslation' => 'Logbuch vun de Säiteniwwersetzungen',
	'logentry-pagetranslation-mark' => "$1 {{GENDER:$2|huet}} d'Säit $3 markéiert fir z'iwwersetzen",
	'logentry-pagetranslation-unmark' => "$1 {{GENDER:$2|huet}} d'Säit $3 aus der Lëscht vun den Iwwersetzungen erausgeholl",
	'logentry-pagetranslation-movenok' => '$1 {{GENDER:$2|hat}} e Problem beim Réckele vun der Säit $3 op $4',
	'logentry-pagetranslation-deletefok' => "$1 {{GENDER:$2|huet}} d'iwwersetzbar Säit $3 geläscht",
	'logentry-pagetranslation-deletelok' => "$1 {{GENDER:$2|huet}} d'Läsche vun der Iwwersetzungssäit $3 ofgeschloss",
	'logentry-pagetranslation-encourage' => "$1 {{GENDER:$2|huet}} d'Iwwersetzung vun $3 recommandéiert",
	'pt-movepage-title' => 'Déi iwwersetzbar Säit $1 réckelen',
	'pt-movepage-blockers' => 'déi iwwersetzbar Säit kann net op den neien Numm geréckelt gi wéinst {{PLURAL:$1|dësem|dëse}} Feeler:',
	'pt-movepage-block-base-exists' => 'D\'Ziliwwersetzungssäit "[[:$1]]" gëtt et schonn.',
	'pt-movepage-block-base-invalid' => 'Den Numm vun der Ziliiwersetzungssäit huet kee valabelen Titel.',
	'pt-movepage-block-tp-exists' => "D'Iwwersetzungszilsäit [[:$2]] gëtt et schonn.",
	'pt-movepage-block-tp-invalid' => 'Den Numm vun der iwwersater Zilsäit fir [[:$1]] wier net valabel (ze laang?).',
	'pt-movepage-block-section-exists' => "Den Zilabschnitt ''[[:$2]]'' fir dës Iwwersetzungs-Eenheet gëtt et schonn.",
	'pt-movepage-block-section-invalid' => "Den Numm vun der Iwwersetzungs-Eenheet vun der Zilsäit fir ''[[:$1]]'' wier net valabel (ze laang?).",
	'pt-movepage-block-subpage-exists' => "D'Zil-Ënnersäit [[:$2]] gëtt et schonn.",
	'pt-movepage-block-subpage-invalid' => 'Den Titel vun der Zil-Ënnersäit fir [[:$1]] wier net valabel (ze laang?).',
	'pt-movepage-list-pages' => 'Lëscht vun de Säite fir ze réckelen',
	'pt-movepage-list-translation' => 'Iwwersetzung {{PLURAL:$1|Säit|Säiten}}',
	'pt-movepage-list-section' => 'Iwwersetzungseenheet {{PLURAL:$1|Säit|Säiten}}',
	'pt-movepage-list-other' => 'Aner Ënner{{PLURAL:$1|säit|säiten}}',
	'pt-movepage-list-count' => 'Am ganzen $1 {{PLURAL:$1|Säit|Säite}} fir ze réckelen.',
	'pt-movepage-legend' => 'Iwwersetzbar Säit réckelen',
	'pt-movepage-current' => 'Aktuellen Numm:',
	'pt-movepage-new' => 'Neien Numm:',
	'pt-movepage-reason' => 'Grond:',
	'pt-movepage-subpages' => 'All Ënnersäite réckelen',
	'pt-movepage-action-check' => "Nokucken ob d'Réckele méiglech ass",
	'pt-movepage-action-perform' => 'Réckelen',
	'pt-movepage-action-other' => 'Zil änneren',
	'pt-movepage-intro' => "Dës Spezialsäit erméiglecht Iech et fir Säiten déi fir d'Iwwersetzung markéiert sinn ze réckelen.
D'Réckelaktioun gëtt net direkt gemaach wëll vill Säite geréckelt musse ginn.
D'Job-Queue gëtt fir d'Réckele vun de Säite benotzt.
Da wann d'Säite geréckelt ginn ass et net méiglech mat deene Säiten déi grad geréckelt ginn ze schaffen.
Wann et net fonctionnéiert gëtt dat am [[Special:Log/pagetranslation|Iwwersetzungs-Logbuch]] festgehal an et muss vun Hand reparéiert ginn.",
	'pt-movepage-logreason' => 'Deel vun der iwwersetzbarer Säit $1.',
	'pt-movepage-started' => "D'Basissäit ass elo geréckelt.
Kuckt w.e.g. d'[[Special:Log/pagetranslation|Logbuch vun den Iwwersetzunge]] fir Feelermeldungen respektiv d'Meldung datt alles ok ass.",
	'pt-locked-page' => 'Dës Säit ass gespaart wëll déi iwwersetzbar Säit elo geréckelt gëtt.',
	'pt-deletepage-lang-title' => "D'Iwwersetzungssäit $1 gëtt geläscht.",
	'pt-deletepage-full-title' => 'Déi iwwersetzbar Säit $1 gëtt geläscht.',
	'pt-deletepage-invalid-title' => 'Déi spezifizéiert Säit ass net valabel.',
	'pt-deletepage-invalid-text' => 'Déi Säit déi Dir uginn hutt ass keng iwwersetzbar Säit a keng Iwwersetzungssäit.',
	'pt-deletepage-action-check' => 'Säiten déi geläscht solle ginn opzielen',
	'pt-deletepage-action-perform' => 'Elo läschen',
	'pt-deletepage-action-other' => 'Zil änneren',
	'pt-deletepage-lang-legend' => 'Iwwersetzungssäit läschen',
	'pt-deletepage-full-legend' => 'Iwwersetzbar Säit läschen',
	'pt-deletepage-any-legend' => "Säit fir z'iwwersetzen oder Iwwersetzung vun enger Säit läschen",
	'pt-deletepage-current' => 'Numm vun der Säit:',
	'pt-deletepage-reason' => 'Grond:',
	'pt-deletepage-subpages' => 'All Ënnersäite läschen',
	'pt-deletepage-list-pages' => 'Lëscht vun de Säite fir ze läschen',
	'pt-deletepage-list-translation' => 'Iwwersetzungssäiten',
	'pt-deletepage-list-section' => 'Iwwersetzungseenheet vu Säiten',
	'pt-deletepage-list-other' => 'Aner Ënnersäiten',
	'pt-deletepage-list-count' => 'Am ganzen $1 {{PLURAL:$1|Säit|Säite}} fir ze läschen.',
	'pt-deletepage-full-logreason' => 'Deel vun der iwwersetzbarer Säit $1.',
	'pt-deletepage-lang-logreason' => 'Deel vun der iwwersater Säit $1.',
	'pt-deletepage-started' => "Kuckt w.e.g. d'[[Special:Log/pagetranslation|Logbuch vun den Iwwersetzunge]] fir Feelermeldungen respektiv d'Meldung datt alles ok ass, no.",
	'pt-deletepage-intro' => "Dës Spezialsäit erméiglecht et eng ganz iwwersetzbar Säit oder eng individuell Iwwersetzungssäit an enger Sprooch ze läschen.
D'Läschaktioun gesäit een net direkt well all d'Säiten déi dovun ofhänken och geläscht ginn.
Feeler ginn am [[Special:Log/pagetranslation|Iwwersetzungs-Logbuch]] agedro a mussen duerno manuell gefléckt ginn.",
);

/** Ganda (Luganda)
 * @author Kizito
 */
$messages['lg'] = array(
	'translate-tag-translate-link-desc' => 'Vvuunula olupapula luno',
	'tpt-languages-legend' => 'Nnimi ndala:',
);

/** Lithuanian (lietuvių)
 * @author Eitvys200
 * @author Mantak111
 */
$messages['lt'] = array(
	'pagetranslation' => 'Puslapio vertimas',
	'action-pagetranslation' => 'valdyti išverstus puslapius',
	'tpt-template' => 'Puslapio šablonas',
	'tpt-templatediff' => 'Pasikeitė puslapio šablonas.',
	'tpt-diff-old' => 'Ankstesnis tekstas',
	'tpt-diff-new' => 'Naujas tekstas',
	'tpt-sections-deleted' => 'Ištrinti vertimo vienetai',
	'tpt-sections-template' => 'Vertimo puslapio šablonas',
	'tpt-badtitle' => 'duotas puslapio pavadinimas ($1) nėra gera antraštė',
	'tpt-nosuchpage' => 'Puslapio $1 neegzistuoja',
	'tpt-mark-summary' => 'Ši versija pažymėta vertimui',
	'tpt-edit-failed' => 'Nepavyko atnaujinti puslapio: $1',
	'tpt-already-marked' => 'Šio puslapio naujausia versija jau yra pažymėta vertimui.',
	'tpt-unmarked' => 'Puslapis $1 nebėra pažymėtas vertimams.',
	'tpt-old-pages-title' => 'Puslapių vertimas',
	'tpt-other-pages-title' => 'Sugadinti puslapiai',
	'tpt-select-prioritylangs-reason' => 'Priežastis:',
	'tpt-sections-prioritylangs' => 'Pirmaujančios kalbos',
	'tpt-rev-mark' => 'Pažymėti vertimui',
	'tpt-rev-unmark' => 'pašalinti iš vertimo',
	'tpt-rev-encourage' => 'atkurti',
	'translate-tag-translate-link-desc' => 'Versti šį puslapį',
	'translate-tag-markthis' => 'Pažymėti šį puslapį vertimui',
	'tpt-languages-legend' => 'Kitos kalbos:',
	'tpt-languages-zero' => 'Pradėti šios kalbos vertimą',
	'tpt-discouraged-language-reason' => 'Priežastis: $1',
	'tpt-download-page' => 'Puslapyje eksportuojamas su vertimais',
	'aggregategroups' => 'Bendros grupės',
	'tpt-aggregategroup-add' => 'Pridėti',
	'tpt-aggregategroup-save' => 'Išsaugoti',
	'tpt-aggregategroup-add-new' => 'Pridėti naują bendrą grupę',
	'tpt-aggregategroup-new-name' => 'Vardas:',
	'tpt-aggregategroup-new-description' => 'Aprašymas (neprivaloma):',
	'tpt-aggregategroup-remove-confirm' => 'Ar tikrai norite naikinti šią bendrą grupę?',
	'tpt-aggregategroup-invalid-group' => 'Grupės nėra',
	'log-name-pagetranslation' => 'Puslapio vertimo žurnalas',
	'logentry-pagetranslation-mark' => '$1 {{GENDER:$2|pažymėtas}} $3 vertimui',
	'logentry-pagetranslation-unmark' => '$1 {{GENDER:$2|ištrintas}} $3 iš vertimo',
	'pt-movepage-legend' => 'Perkelti išverčiamą puslapį',
	'pt-movepage-current' => 'Dabartinis pavadinimas:',
	'pt-movepage-new' => 'Naujas pavadinimas:',
	'pt-movepage-reason' => 'Priežastis:',
	'pt-movepage-subpages' => 'Perkelti visus subpuslapius',
	'pt-movepage-action-check' => 'Patikrinkite, ar perkelti yra įmanoma',
	'pt-movepage-action-perform' => 'Perkelti',
	'pt-movepage-action-other' => 'Pakeisti taikinį',
	'pt-deletepage-action-check' => 'Sąrašą puslapių kurie turi būti ištrinti.',
	'pt-deletepage-action-perform' => 'Padaryti trynimą',
	'pt-deletepage-action-other' => 'Pakeisti taikinį',
	'pt-deletepage-lang-legend' => 'Ištrinti vertimo puslapį',
	'pt-deletepage-full-legend' => 'Ištrinti išverčiama puslapį',
	'pt-deletepage-current' => 'Puslapio pavadinimas:',
	'pt-deletepage-reason' => 'Priežastis:',
	'pt-deletepage-subpages' => 'Ištrinti visus subpuslapius',
	'pt-deletepage-list-pages' => 'Sąrašas ištrinti puslapius',
	'pt-deletepage-list-translation' => 'Vertimo puslapiai',
	'pt-deletepage-list-section' => 'Vertimo vieneto puslapiai',
	'pt-deletepage-list-other' => 'Kiti subpuslapiai',
	'pt-deletepage-full-logreason' => 'Dalis išverčiamo puslapio $1.',
	'pt-deletepage-lang-logreason' => 'Dalis vertimo puslapio $1.',
);

/** Latgalian (latgaļu)
 * @author Dark Eagle
 */
$messages['ltg'] = array(
	'tpt-diff-new' => 'Jauns teksts',
	'tpt-languages-legend' => 'Cytys volūdys:',
	'pt-movepage-new' => 'Jauna pasauka:',
	'pt-movepage-reason' => 'Īmesle:',
);

/** Latvian (latviešu)
 * @author Papuass
 */
$messages['lv'] = array(
	'tpt-template' => 'Lapas veidne',
	'tpt-templatediff' => 'Lapas veidne tika izmainīta.',
	'tpt-nosuchpage' => 'Lapa $1 nepastāv',
	'tpt-select-prioritylangs-reason' => 'Iemesls:',
	'tpt-sections-prioritylangs' => 'Prioritārās valodas',
	'translate-tag-translate-link-desc' => 'Tulkot šo lapu',
	'tpt-languages-legend' => 'Citas valodas:',
	'tpt-tab-translate' => 'Tulkot',
	'tpt-discouraged-language-reason' => 'Iemesls: $1',
	'tpt-aggregategroup-add' => 'Pievienot',
	'tpt-aggregategroup-save' => 'Saglabāt',
	'tpt-aggregategroup-new-name' => 'Nosaukums:',
	'tpt-aggregategroup-new-description' => 'Apraksts (nav obligāts):',
	'log-name-pagetranslation' => 'Lapu tulkošanas žurnāls',
	'pt-movepage-list-pages' => 'Pārvietojamo lapu saraksts',
	'pt-movepage-list-translation' => 'Tulkojuma {{PLURAL:$1|lapa|lapas}}',
	'pt-movepage-list-other' => '{{PLURAL:$1|Cita apakšlapa|Citas apakšlapas}}',
	'pt-movepage-current' => 'Esošais nosaukums:',
	'pt-movepage-new' => 'Jaunais nosaukums:',
	'pt-movepage-reason' => 'Iemesls:',
	'pt-movepage-subpages' => 'Pārvietot visas apakšlapas',
	'pt-movepage-action-check' => 'Pārbaudīt, vai ir iespējams pārvietot',
	'pt-movepage-action-perform' => 'Nepārvietot',
	'pt-movepage-action-other' => 'Mainīt mērķi',
	'pt-movepage-logreason' => 'Daļa no tulkojamas lapas $1.',
	'pt-deletepage-current' => 'Lapas nosaukums:',
	'pt-deletepage-reason' => 'Iemesls:',
	'pt-deletepage-subpages' => 'Dzēst visas apakšlapas',
	'pt-deletepage-list-pages' => 'Dzēšamo lapu saraksts',
	'pt-deletepage-list-translation' => 'Tulkojuma lapas',
	'pt-deletepage-list-other' => 'Citas apakšlapas',
);

/** Literary Chinese (文言)
 * @author Yanteng3
 */
$messages['lzh'] = array(
	'tpt-aggregategroup-save' => '存',
);

/** Malagasy (Malagasy)
 * @author Jagwar
 */
$messages['mg'] = array(
	'right-pagetranslation' => 'Mamamarika ny santiônam-pejy hodikaina',
);

/** Minangkabau (Baso Minangkabau)
 * @author Iwan Novirion
 */
$messages['min'] = array(
	'tpt-languages-legend' => 'Baso lain:',
	'pt-movepage-intro' => 'Laman istimewa ko mamungkinan Sanak untuak mamindahan laman nan ditandoi untuak ditajamahan.
Tindakan pamindahan indak akan balangsuang sakatika dek banyak laman nan paralu dipindahan.
Sangkek laman dipindahan, indak dimungkinan untuak barinteraksi jo laman nan basangkutan.
Kagagalan akan dicatat di [[Special:Log/pagetranslation|log tajamahan laman]] dan paralu dipelokan sacaro manual.',
);

/** Macedonian (македонски)
 * @author Bjankuloski06
 * @author Brest
 */
$messages['mk'] = array(
	'pagetranslation' => 'Превод на страници',
	'right-pagetranslation' => 'Обележување на верзии на страници за преведување',
	'action-pagetranslation' => 'раководење со преводливи страници',
	'tpt-desc' => 'Додаток за преведување на страници со содржини',
	'tpt-section' => 'Преводна единица $1',
	'tpt-section-new' => 'Нова преводна единица.
Назив: $1',
	'tpt-section-deleted' => 'Преводна единица $1',
	'tpt-template' => 'Шаблон за страница',
	'tpt-templatediff' => 'Шаблонот за страницата е променет.',
	'tpt-diff-old' => 'Претходен текст.',
	'tpt-diff-new' => 'Нов текст',
	'tpt-submit' => 'Обележи ја оваа верзија на преводот',
	'tpt-sections-oldnew' => 'Нови и постоечки преводни единици',
	'tpt-sections-deleted' => 'Избришани преводни едници',
	'tpt-sections-template' => 'Шаблон за страница со превод',
	'tpt-action-nofuzzy' => 'Не поништувај преводи',
	'tpt-badtitle' => 'Даденото име на страницата ($1) е погрешен наслов',
	'tpt-nosuchpage' => 'Страницата $1 не постои',
	'tpt-oldrevision' => '$2 не е најнова верзија на страницата [[$1]].
Само најновите верзии можат да се обележуваат за преведување.',
	'tpt-notsuitable' => 'Страницата $1 не е погодна за преведување.
Проверете дали има ознаки <nowiki><translate></nowiki> и дали има правилна синтакса.',
	'tpt-saveok' => 'Оваа страница [[$1]] е обележана за преведување со $2 {{PLURAL:$2|преводна единица|преводни единици}}.
Страницата сега може да се <span class="plainlinks">[$3 преведува]</span>.',
	'tpt-offer-notify' => 'Можете да ги <span class="plainlinks">[$1 известите преведувачите]</span> за оваа страница.',
	'tpt-badsect' => '„$1“ е погрешно име за преводната единица $2.',
	'tpt-showpage-intro' => 'Подолу се наведени нови, постоечки и избришани преводни единици.
Пред да ја обележите оваа верзија за преведување, проверете дали промените во деловите се сведени на минимум со што би се избегнала непотреба работа за преведувачите.',
	'tpt-mark-summary' => 'Ја означи оваа верзија за преведување',
	'tpt-edit-failed' => 'Не можев да ја обновам страницата: $1',
	'tpt-duplicate' => 'Името $1 се користи кај повеќе од една преводна единица.',
	'tpt-already-marked' => 'Најновата верзија на оваа страница е веќе обележана за преведување.',
	'tpt-unmarked' => 'Страницата $1 повеќе не е означена за преведување.',
	'tpt-list-nopages' => 'Нема пораки обележани за преведување, ниту страници готови за обележување за да бидат преведени.',
	'tpt-new-pages-title' => 'Страници предложени за преведување',
	'tpt-old-pages-title' => 'Страници за преведување',
	'tpt-other-pages-title' => 'Расипани страници',
	'tpt-discouraged-pages-title' => 'Непрепорачани страници',
	'tpt-new-pages' => '{{PLURAL:$1|Оваа страница содржи|Овие страници содржат}} текст со ознаки за преведување, но моментално нема верзија на {{PLURAL:$1|оваа страница|овие страници}} која е обележана за преведување.',
	'tpt-old-pages' => 'Извесна верзија на {{PLURAL:$1|оваа страница|овие страници}} е обележана за преведување.',
	'tpt-other-pages' => '{{PLURAL:$1|Стара верзија на оваа страница е означена за преведување|Постари верзии на оваа страница се означени за преведување}},
но {{PLURAL:$1|најновата верзија не може да се означи|најновите верзии не можат да се означат}} за преведување.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Се препорачува оваа страница повеќе да не се преведува|Се препорачува овие страници повеќе да не се преведуваат}}.',
	'tpt-select-prioritylangs' => 'Список на кодови на приоритетните јазици, одделени со запирка:',
	'tpt-select-prioritylangs-force' => 'Спречи преведување на јазици што не се приоритетни',
	'tpt-select-prioritylangs-reason' => 'Причина:',
	'tpt-sections-prioritylangs' => 'Приоритетни јазици',
	'tpt-rev-mark' => 'означи за преведување',
	'tpt-rev-unmark' => 'отстрани од преводот',
	'tpt-rev-discourage' => 'непрепорачана',
	'tpt-rev-encourage' => 'врати',
	'tpt-rev-mark-tooltip' => 'Означи ја последната верзија на страницава како „за преведување“',
	'tpt-rev-unmark-tooltip' => 'Отстрани ја страницава од преводот.',
	'tpt-rev-discourage-tooltip' => 'Постави ја страницата како непрепорачана за понатамошното преведување.',
	'tpt-rev-encourage-tooltip' => 'Врати ја страницата на нормално преведување.',
	'translate-tag-translate-link-desc' => 'Преведете ја страницава',
	'translate-tag-markthis' => "Обележи ја оваа страница со 'за преведување'",
	'translate-tag-markthisagain' => 'Оваа страница има <span class="plainlinks">[$1 промени]</span> од последниот пат кога <span class="plainlinks">[$2 обележана за преведување]</span>.',
	'translate-tag-hasnew' => 'Оваа страница содржи <span class="plainlinks">[$1 промени]</span> кои не се обележани за преведување.',
	'tpt-translation-intro' => 'Оваа страница е <span class="plainlinks">[$1 преведена верзија]</span> на страницата [[$2]], а преводот е $3% потполн и тековен.',
	'tpt-languages-legend' => 'Други јазици:',
	'tpt-languages-separator' => '&#160;•&#160;',
	'tpt-languages-zero' => 'Почнете превод на овој јазик',
	'tpt-tab-translate' => 'Преведи',
	'tpt-target-page' => 'Оваа страница не може да се обнови рачно.
Страницава е превод на страницата [[$1]], а преводот може да се обнови само со помош на [$2 алатката за преведување].',
	'tpt-unknown-page' => 'Овој именски простор е резервиран за преводи на содржински страници.
Страницата која се обидувате да ја уредите не соодветствува со ниедна страница обележана за преведување.',
	'tpt-translation-restricted' => 'Преведувањето на страницата на овој јазик е спречено од преводен администратор.

Причина: $1',
	'tpt-discouraged-language-force' => 'Преводен администратор ги ограничи јазиците на кои може да се преведе оваа страница. Овој јазик не е меѓу нив.

Причина: $1',
	'tpt-discouraged-language' => 'Овој јазик не е меѓу приоритетните јазици на оваа страница што ги задал администратор

Причина: $1',
	'tpt-discouraged-language-reason' => 'Причина: $1',
	'tpt-priority-languages' => 'Преводен администратор на групата ѝ ги зададе приоритетните јазици: $1.',
	'tpt-render-summary' => 'Обнова за усогласување со новата верзија на изворната страница',
	'tpt-download-page' => 'Извези страница со преводи',
	'aggregategroups' => 'Збирни групи',
	'tpt-aggregategroup-add' => 'Додај',
	'tpt-aggregategroup-save' => 'Зачувај',
	'tpt-aggregategroup-add-new' => 'Додај нова збирна група',
	'tpt-aggregategroup-new-name' => 'Назив:',
	'tpt-aggregategroup-new-description' => 'Опис (незадолжително):',
	'tpt-aggregategroup-remove-confirm' => 'Дали сте сигурни дека сакате да ја избришете оваа збирна група?',
	'tpt-aggregategroup-invalid-group' => 'Групата не постои',
	'pt-parse-open' => 'Неврамнотежена &lt;translate> ознака.
Шаблон за преводот: <pre>$1</pre>',
	'pt-parse-close' => 'Неврамнотежена &lt;/translate> ознака.
Шаблон за преводот: <pre>$1</pre>',
	'pt-parse-nested' => 'Не се дозволени гвнездени &lt;translate> преводни единици.
Текст на ознаката: <pre>$1</pre>',
	'pt-shake-multiple' => 'Повеќекратни означувачи за преводни единици во во една единица.
Текст на единицата: <pre>$1</pre>',
	'pt-shake-position' => 'Неочекувана положба на означувачите за преводни единици.
Текст во преводната единица: <pre>$1</pre>',
	'pt-shake-empty' => 'Празна преводна единица за означувачот „$1“.',
	'log-description-pagetranslation' => 'Дневник на дејства кои се однесуваат на системот за превод на страници',
	'log-name-pagetranslation' => 'Дневник на преводи на страници',
	'logentry-pagetranslation-mark' => '$1 {{GENDER:$2|ја означи}} $3 за преведување',
	'logentry-pagetranslation-unmark' => '$1 {{GENDER:$2|ја отстрани}} $3 од преведувањето',
	'logentry-pagetranslation-moveok' => '$1 {{GENDER:$2|го заврши}} преименувањето на преводливата страница $3 во $4',
	'logentry-pagetranslation-movenok' => '$1 {{GENDER:$2|наиде}} на проблем при преместувањето на страницата $3 на $4',
	'logentry-pagetranslation-deletefok' => '$1 {{GENDER:$2|го заврши}} бришењето на преводливата страница $3',
	'logentry-pagetranslation-deletefnok' => '$1 {{GENDER:$2|не успеа}} да ја избрише $3, што ѝ припаѓа на преводливата страница $4',
	'logentry-pagetranslation-deletelok' => '$1 {{GENDER:$2|заврши}} со бришењето на преводната страница $3',
	'logentry-pagetranslation-deletelnok' => '$1 {{GENDER:$2|не успеа}} да ја избрише $3, што ѝ припаѓа на преводната страница $4',
	'logentry-pagetranslation-encourage' => '$1 {{GENDER:$2|препорача}} да се преведе $3',
	'logentry-pagetranslation-discourage' => '$1 {{GENDER:$2|препорача}} да не се преведува $3',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1 {{GENDER:$2|отстрани}} приоритетни јазици од преводливата страница $3',
	'logentry-pagetranslation-prioritylanguages' => '$1 {{GENDER:$2|ги зададе}} приоритетните јазици од преводливата страница $3: $5',
	'logentry-pagetranslation-prioritylanguages-force' => '$1 {{GENDER:$2|ги ограничи}} јазиците на преводливата страница $3 на $5',
	'logentry-pagetranslation-associate' => '$1 {{GENDER:$2|ја додаде}} преводливата страница $3 во збирната група $4',
	'logentry-pagetranslation-dissociate' => '$1 {{GENDER:$2|ја острани}} преводливата страница $3 од збирната група $4',
	'pt-movepage-title' => 'Преместување на преводливата страница $1',
	'pt-movepage-blockers' => 'Преводливата страница не може да се премести на нов наслов заради {{PLURAL:$1|следнава грешка|следниве грешки}}:',
	'pt-movepage-block-base-exists' => 'Целната преводлива страница „[[:$1]]“ постои.',
	'pt-movepage-block-base-invalid' => 'Целната преводлива страница не претставува важечки наслов.',
	'pt-movepage-block-tp-exists' => 'Целната страница со превод [[:$2]] постои.',
	'pt-movepage-block-tp-invalid' => 'Насловот на целната страница за превод на [[:$1]] би била неважечка (предолга?).',
	'pt-movepage-block-section-exists' => 'Целната страница „[[:$2]]“ за преводната единица постои.',
	'pt-movepage-block-section-invalid' => 'Целниот наслов на страницата за „[[:$1]]“ за преводната единица би била неважечки (предолг?).',
	'pt-movepage-block-subpage-exists' => 'Целната потстраница [[:$2]] постои.',
	'pt-movepage-block-subpage-invalid' => 'Насловот на целната потстраница на [[:$1]] би била неважечка (предолга?).',
	'pt-movepage-list-pages' => 'Список на страници за преместување',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Преводна страница|Преводни страници}}',
	'pt-movepage-list-section' => '{{PLURAL:$1|Страница на преводна единица|Страници на преводни единици}}',
	'pt-movepage-list-other' => '{{PLURAL:$1|Друга потстраница|Други потстраници}}',
	'pt-movepage-list-count' => 'Вкупно $1 {{PLURAL:$1|страница|страници}} за преместување.',
	'pt-movepage-legend' => 'Премести преводлива страница',
	'pt-movepage-current' => 'Сегашен назив:',
	'pt-movepage-new' => 'Нов назив:',
	'pt-movepage-reason' => 'Причина:',
	'pt-movepage-subpages' => 'Премести ги сите потстраници',
	'pt-movepage-action-check' => 'Провери дали преместувањето е изводливо',
	'pt-movepage-action-perform' => 'Изврши преместување',
	'pt-movepage-action-other' => 'Смени цел',
	'pt-movepage-intro' => 'Оваа специјална страница ви овозможува да преместувате страници обележани за преведување.
Самото преместување нема да се случи веднаш, бидејќи треба да се преместат голем број на страници.
Преместувањето ќе се води по редица на задачи.
Додека се преместуваат страниците, со нив нема да може да се работи.
Неуспешните ќе бидат заведени во [[Special:Log/pagetranslation|дневникот на преводи на страници]] и тие ќе треба да се поправаат рачно.',
	'pt-movepage-logreason' => 'Дел од преводливата страница $1.',
	'pt-movepage-started' => 'Страницата сега е преместена.
Проверете дали [[Special:Log/pagetranslation|дневникот на преводи на страници]] има пријавено грешки и порака за завршена задача.',
	'pt-locked-page' => 'Оваа страница е заклучена бидејќи е во тек преместување на преводлива страница.',
	'pt-deletepage-lang-title' => 'Бришење на страницата со превод $1.',
	'pt-deletepage-full-title' => 'Бришење на преводливата страница $1.',
	'pt-deletepage-invalid-title' => 'Наведената страница е неважечка.',
	'pt-deletepage-invalid-text' => 'Наведената страница не е преводлива, ниту пак страница за преведување.',
	'pt-deletepage-action-check' => 'Список на страници за бришење',
	'pt-deletepage-action-perform' => 'Изврши го бришењето',
	'pt-deletepage-action-other' => 'Смени цел',
	'pt-deletepage-lang-legend' => 'Избриши ја страницата за превод',
	'pt-deletepage-full-legend' => 'Избриши преводлива страница',
	'pt-deletepage-any-legend' => 'Избриши преводлива страница или страница за превод',
	'pt-deletepage-current' => 'Име на страницата:',
	'pt-deletepage-reason' => 'Причина:',
	'pt-deletepage-subpages' => 'Избриши ги сите потстраници',
	'pt-deletepage-list-pages' => 'Список на страници за бришење',
	'pt-deletepage-list-translation' => 'Страници со превод',
	'pt-deletepage-list-section' => 'Страници за преводни единици',
	'pt-deletepage-list-other' => 'Други потстраници',
	'pt-deletepage-list-count' => 'Вкупно $1 {{PLURAL:$1|страница|страници}} за бришење.',
	'pt-deletepage-full-logreason' => 'Дел од преводливата страница $1.',
	'pt-deletepage-lang-logreason' => 'Дел од страницата со превод $1.',
	'pt-deletepage-started' => 'Погледајте го [[Special:Log/pagetranslation|дневникот со преводи на страници]] за грешки и порака при завршувањето.',
	'pt-deletepage-intro' => 'Оваа специјална страница овозможува бришење на цела преводлива страница или поединечна страница за превод на некој јазик.
Бришењето не делува веднаш, бидејќи ќе се бришат и сите страници што зависат од неа.
Неуспешните обиди ќе се заведуваат во [[Special:Log/pagetranslation|дневникот на страници за превод]] и ќе треба да се исправаат рачно.',
);

/** Malayalam (മലയാളം)
 * @author Kavya Manohar
 * @author Praveenp
 * @author Santhosh.thottingal
 */
$messages['ml'] = array(
	'pagetranslation' => 'താളിന്റെ പരിഭാഷ',
	'tpt-template' => 'താൾ ഫലകം',
	'tpt-diff-old' => 'പഴയ എഴുത്ത്',
	'tpt-diff-new' => 'പുതിയ എഴുത്ത്',
	'tpt-badtitle' => 'താളിനു നൽകിയ പേര് ($1) സാധുവായ തലക്കെട്ട് അല്ല',
	'tpt-nosuchpage' => '$1 എന്ന താൾ നിലവിലില്ല.',
	'tpt-edit-failed' => 'താൾ പുതുക്കാൻ കഴിഞ്ഞില്ല: $1',
	'tpt-old-pages-title' => 'പരിഭാഷയിലുള്ള താളുകൾ',
	'tpt-other-pages-title' => 'പൊട്ടിയ താളുകൾ',
	'tpt-select-prioritylangs-reason' => 'കാരണം:',
	'tpt-rev-discourage' => 'നിരുത്സാഹപ്പെടുത്തുക',
	'tpt-rev-encourage' => 'പുനഃസ്ഥാപിക്കുക',
	'translate-tag-translate-link-desc' => 'ഈ താൾ പരിഭാഷപ്പെടുത്തുക',
	'tpt-translation-intro' => 'ഈ താൾ [[$2]] എന്ന താളിന്റെ <span class="plainlinks">[$1 പരിഭാഷ]</span> ആണ്, പരിഭാഷ $3% പൂർണ്ണമാണ്.',
	'tpt-languages-legend' => 'മറ്റു ഭാഷകൾ:',
	'tpt-languages-zero' => 'ഈ ഭാഷയിൽ പരിഭാഷ തുടങ്ങുക',
	'tpt-tab-translate' => 'പരിഭാഷപ്പെടുത്തുക',
	'tpt-target-page' => 'ഈ താൾ താങ്കൾക്ക് പുതുക്കാൻ കഴിയില്ല.
ഈ താൾ [[$1]] എന്ന താളിന്റെ പരിഭാഷയാണ്, പരിഭാഷ പുതുക്കാൻ [$2 പരിഭാഷാ ഉപകരണം] ഉപയോഗിക്കുക.',
	'tpt-discouraged-language-reason' => 'കാരണം: $1',
	'tpt-aggregategroup-add' => 'കൂട്ടിച്ചേർക്കുക',
	'tpt-aggregategroup-save' => 'സേവ് ചെയ്യുക',
	'tpt-aggregategroup-new-name' => 'പേര്:',
	'tpt-aggregategroup-new-description' => 'വിവരണം (ഐച്ഛികം):',
	'log-name-pagetranslation' => 'താൾ പരിഭാഷാ രേഖ',
	'pt-movepage-block-subpage-exists' => 'ലക്ഷ്യം വെച്ച ഉപതാൾ [[:$2]] നിലവിലുണ്ട്.',
	'pt-movepage-list-pages' => 'മാറ്റേണ്ട താളുകളുടെ പട്ടിക',
	'pt-movepage-list-translation' => 'പരിഭാഷാ{{PLURAL:$1|താൾ|താളുകൾ}}',
	'pt-movepage-list-section' => 'ഉപവിഭാഗ താളുകൾ', # Fuzzy
	'pt-movepage-list-other' => 'മറ്റ് ഉപതാളുകൾ', # Fuzzy
	'pt-movepage-legend' => 'പരിഭാഷപ്പെടുത്താവുന്ന താൾ നീക്കുക',
	'pt-movepage-current' => 'ഇപ്പോഴത്തെ പേര്:',
	'pt-movepage-new' => 'പുതിയ പേര്:',
	'pt-movepage-reason' => 'കാരണം:',
	'pt-movepage-subpages' => 'എല്ലാ ഉപതാളുകളും മാറ്റുക',
	'pt-movepage-action-check' => 'മാറ്റൽ സാദ്ധ്യമാണോയെന്നു പരിശോധിക്കുക',
	'pt-movepage-action-perform' => 'മാറ്റുക',
	'pt-movepage-action-other' => 'ലക്ഷ്യം മാറ്റുക',
	'pt-deletepage-action-check' => 'മായ്ക്കേണ്ട താളുകളുടെ പട്ടിക നൽകുക',
	'pt-deletepage-action-perform' => 'മായ്ക്കൽ നടപ്പിൽ വരുത്തുക',
	'pt-deletepage-action-other' => 'ലക്ഷ്യം മാറ്റുക',
	'pt-deletepage-lang-legend' => 'പരിഭാഷാ താൾ മായ്ക്കുക',
	'pt-deletepage-full-legend' => 'പരിഭാഷപ്പെടുത്താവുന്ന താൾ മായ്ക്കുക',
	'pt-deletepage-current' => 'താളിന്റെ പേര്:',
	'pt-deletepage-reason' => 'കാരണം:',
	'pt-deletepage-subpages' => 'എല്ലാ ഉപതാളുകളും മായ്ക്കുക',
	'pt-deletepage-list-pages' => 'മായ്ക്കേണ്ട താളുകളുടെ പട്ടിക',
	'pt-deletepage-list-translation' => 'പരിഭാഷാ താളുകൾ',
	'pt-deletepage-list-section' => 'ഉപവിഭാഗ താളുകൾ', # Fuzzy
	'pt-deletepage-list-other' => 'മറ്റ് ഉപതാളുകൾ',
);

/** Mongolian (монгол)
 * @author Chinneeb
 */
$messages['mn'] = array(
	'pt-movepage-reason' => 'Шалтгаан:',
);

/** Marathi (मराठी)
 * @author V.narsikar
 * @author Vb2055
 * @author संतोष दहिवळ
 */
$messages['mr'] = array(
	'pagetranslation' => 'भाषांतराची पाने.',
	'right-pagetranslation' => 'Mark versions of pages for translation',
	'action-pagetranslation' => 'manage translatable pages',
	'tpt-desc' => 'Extension for translating content pages',
	'tpt-section' => '$1 चे भाषांतर',
	'tpt-offer-notify' => 'आपण या पानाबाबत <span class="plainlinks">[$1 भाषांतरकारांना सूचना देउ शकता]</span>',
	'tpt-tab-translate' => 'भाषांतर करा',
);

/** Malay (Bahasa Melayu)
 * @author Anakmalaysia
 */
$messages['ms'] = array(
	'pagetranslation' => 'Penterjemahan laman',
	'right-pagetranslation' => 'Menandai versi-versi laman untuk diterjemah',
	'action-pagetranslation' => 'menguruskan halaman-halaman yang boleh diterjemah',
	'tpt-desc' => 'Sambungan untuk menterjemah laman-laman kandungan',
	'tpt-section' => 'Unit penterjemahan $1',
	'tpt-section-new' => 'Unit penterjemahan baru.
Nama: $1',
	'tpt-section-deleted' => 'Unit penterjemahan $1',
	'tpt-template' => 'Templat laman',
	'tpt-templatediff' => 'Templat laman telah berubah.',
	'tpt-diff-old' => 'Teks sebelumnya',
	'tpt-diff-new' => 'Teks baru',
	'tpt-submit' => 'Tandai versi ini untuk diterjemah',
	'tpt-sections-oldnew' => 'Unit penterjemahan yang baru dan sedia ada',
	'tpt-sections-deleted' => 'Unit penterjemahan yang dihapuskan',
	'tpt-sections-template' => 'Templat laman penterjemahan',
	'tpt-action-nofuzzy' => 'Jangan taksahkan terjemahan',
	'tpt-badtitle' => 'Nama laman yang diberikan ($1) bukan tajuk yang sah',
	'tpt-nosuchpage' => 'Halaman $1 tidak wujud',
	'tpt-oldrevision' => '$2 bukan versi terkini laman [[$1]].
Hanya versi terkini boleh ditandai untuk penterjemahan.',
	'tpt-notsuitable' => 'Laman $1 tidak sesuai untuk diterjemah.
Pastikan ia ada tag <nowiki><translate></nowiki> dan sintaks yang sah.',
	'tpt-saveok' => 'Laman [[$1]] telah ditandai untuk penterjemahan dengan $2 unit penterjemahan.
Laman ini kini boleh <span class="plainlinks">[$3 diterjemah]</span>.',
	'tpt-offer-notify' => 'Anda boleh <span class="plainlinks">[$1 memaklumkan para penterjemah]</span> tentang halaman ini.',
	'tpt-badsect' => '"$1" bukan nama yang sah untuk unit penterjemahan $2.',
	'tpt-showpage-intro' => 'Di bawah tersenarainya unit-unit terjemahan yang baru, sedia ada dan terhapus.
Sebelum menandai versi ini untuk diterjemah, pastikan supaya perubahan kepada unit terjemahan diminimumkan untuk mengelakkan beban yang tidak perlu untuk penterjemah.',
	'tpt-mark-summary' => 'Menandakan versi ini untuk diterjemah',
	'tpt-edit-failed' => 'Laman ini tidak dapat dikemas kini: $1',
	'tpt-duplicate' => 'Nama unit terjemahan $1 terguna lebih daripada sekali.',
	'tpt-already-marked' => 'Versi terkini laman ini sudah ditandai untuk diterjemah.',
	'tpt-unmarked' => 'Laman $1 tidak lagi ditandai untuk diterjemah.',
	'tpt-list-nopages' => 'Tiadanya laman yang ditandai untuk diterjemah atau sedia ditandai untuk diterjemah.',
	'tpt-new-pages-title' => 'Laman yang diusulkan untuk diterjemah',
	'tpt-old-pages-title' => 'Laman yang sedang diterjemah',
	'tpt-other-pages-title' => 'Laman yang rosak',
	'tpt-discouraged-pages-title' => 'Laman yang ditegah',
	'tpt-new-pages' => '{{PLURAL:$1|Laman|Laman-laman}} ini mengandungi teks dengan tag penterjemahan,
tetapi tiada versi yang ditandai untuk diterjemah.',
	'tpt-old-pages' => 'Suatu versi {{PLURAL:$1|laman|laman-laman}} ini telah ditandai untuk diterjemah.',
	'tpt-other-pages' => '{{PLURAL:$1|Satu versi lama laman|Versi lama laman-laman}} ini ditandai untuk diterjemah,
tetapi {{PLURAL:$1|versi|versi-versi}} terkini tidak boleh ditandai untuk diterjemah.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Laman|Laman-laman}} ini telah ditegah daripada mendapat penterjemahan selanjutnya.',
	'tpt-select-prioritylangs' => 'Senarai kod bahasa keutamaan yang diasingkan dengan koma:',
	'tpt-select-prioritylangs-force' => 'Larang terjemahan ke bahasa-bahasa selain bahasa keutamaan',
	'tpt-select-prioritylangs-reason' => 'Sebab:',
	'tpt-sections-prioritylangs' => 'Bahasa keutamaan',
	'tpt-rev-mark' => 'tempah untuk penterjemahan',
	'tpt-rev-unmark' => 'gugurkan daripada penterjemahan',
	'tpt-rev-discourage' => 'tegah',
	'tpt-rev-encourage' => 'pulihkan',
	'tpt-rev-mark-tooltip' => 'Tempah versi terbaru laman ini untuk diterjemahkan.',
	'tpt-rev-unmark-tooltip' => 'Gugurkan laman ini daripada penterjemahan.',
	'tpt-rev-discourage-tooltip' => 'Tegah penterjemahan lanjutan bagi laman ini.',
	'tpt-rev-encourage-tooltip' => 'Pulihkan laman ini kepada penterjemahan biasa.',
	'translate-tag-translate-link-desc' => 'Terjemahkan laman ini',
	'translate-tag-markthis' => 'Tandai laman ini untuk diterjemah',
	'translate-tag-markthisagain' => 'Laman ini mengalami <span class="plainlinks">[$1 perubahan]</span> sejak kali terakhir <span class="plainlinks">[$2 ditandai untuk diterjemah]</span>.',
	'translate-tag-hasnew' => 'Laman ini mengalami <span class="plainlinks">[$1 perubahan]</span> yang belum ditandai untuk diterjemah.',
	'tpt-translation-intro' => 'Laman ini merupakan <span class="plainlinks">[$1 versi terjemahan]</span> laman [[$2]] dan penterjemahannya $3% siap.',
	'tpt-languages-legend' => 'Bahasa lain:',
	'tpt-languages-separator' => '&#160;•&#160;',
	'tpt-languages-zero' => 'Mulakan terjemahan dalam bahasa ini',
	'tpt-tab-translate' => 'Terjemah',
	'tpt-target-page' => 'Laman ini tidak boleh dikemaskini secara manual.
Laman ini merupakan terjemahan laman [[$1]], dan terjemahannya boleh dikemas kini dengan menggunakan [$2 alatan penterjemahan].',
	'tpt-unknown-page' => 'Ruang nama ini ditempah untuk penterjemahan laman kandungan.
Laman yang anda cuba sunting itu nampaknya tidak berpadan dengan sebarang laman yang ditandai untuk diterjemah.',
	'tpt-translation-restricted' => 'Penterjemahan halaman ini kepada bahasa ini disekat oleh pentadbir penterjemahan.

Sebab: $1',
	'tpt-discouraged-language-force' => 'Pentadbir penterjemahan telah mengehadkan bahasa-bahasa yang mana halaman ini boleh diterjemahkan. Bahasa ini bukan salah satu bahasa yang dibenarkan.

Sebab: $1',
	'tpt-discouraged-language' => 'Bahasa ini bukan bahasa keutamaan yang ditetapkan oleh pentadbir penterjemahan untuk halaman ini.

Sebab: $1',
	'tpt-discouraged-language-reason' => 'Sebab: $1',
	'tpt-priority-languages' => 'Seorang pentadbir penterjemahan telah menetapkan $1 sebagai bahasa keutamaan kumpulan ini.',
	'tpt-render-summary' => 'Mengemas kini agar sepadan dengan versi baru laman sumber',
	'tpt-download-page' => 'Eksport laman dengan terjemahan',
	'aggregategroups' => 'Kumpulan agregat',
	'tpt-aggregategroup-add' => 'Tambahkan',
	'tpt-aggregategroup-save' => 'Simpan',
	'tpt-aggregategroup-add-new' => 'Tambahkan kumpulan agregat baru',
	'tpt-aggregategroup-new-name' => 'Nama:',
	'tpt-aggregategroup-new-description' => 'Keterangan (tidak wajib):',
	'tpt-aggregategroup-remove-confirm' => 'Adakah anda benar-benar ingin menghapuskan kumpulan agregat ini?',
	'tpt-aggregategroup-invalid-group' => 'Kumpulan tidak wujud',
	'pt-parse-open' => 'Tag &lt;translate> tidak seimbang.
Templat penterjemahan: <pre>$1</pre>',
	'pt-parse-close' => 'Tag &lt;/translate> tidak seimbang.
Templat penterjemahan: <pre>$1</pre>',
	'pt-parse-nested' => 'Unit terjemahan &lt;translate> yang tersarang tidak dibenarkan.
Teks tag: <pre>$1</pre>',
	'pt-shake-multiple' => 'Berbilang penanda bahagian untuk satu unit terjemahan.
Teks unit terjemahan: <pre>$1</pre>',
	'pt-shake-position' => 'Unit terjemahan di kedudukan yang tidak dijangka.
Teks unit terjemahan: <pre>$1</pre>',
	'pt-shake-empty' => 'Unit terjemahan kosong untuk penanda "$1".',
	'log-description-pagetranslation' => 'Log untuk tindakan yang berkaitan dengan sistem penterjemahan laman',
	'log-name-pagetranslation' => 'Log penterjemahan laman',
	'logentry-pagetranslation-mark' => '$1 {{GENDER:$2|menanda}} $3 untuk diterjemahkan',
	'logentry-pagetranslation-unmark' => '$1 {{GENDER:$2|membuang}} $3 dari penterjemahan',
	'logentry-pagetranslation-moveok' => '$1 {{GENDER:$2|selesai}} menukar nama halaman boleh terjemah $3 ke dalam $4',
	'logentry-pagetranslation-movenok' => '$1 {{GENDER:$2|menghadapi}} masalah semasa mengalihkan halaman $3 ke $4',
	'logentry-pagetranslation-deletefok' => '$1 {{GENDER:$2|selesai}} menghapuskan halaman boleh terjemah $3',
	'logentry-pagetranslation-deletefnok' => '$1 {{GENDER:$2|gagal}} menghapuskan $3 yang tergolong dalam halaman boleh terjemah $4',
	'logentry-pagetranslation-deletelok' => '$1 {{GENDER:$2|selesai}} menghapuskan halaman penterjemahan $3',
	'logentry-pagetranslation-deletelnok' => '$1 {{GENDER:$2|gagal}} menghapuskan $3 yang tergolong dalam halaman penterjemahan $4',
	'logentry-pagetranslation-encourage' => '$1 {{GENDER:$2|mengesyorkan}} terjemahan untuk $3',
	'logentry-pagetranslation-discourage' => '$1 {{GENDER:$2|menegah}} terjemahan untuk $3',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1 {{GENDER:$2|menggugurkan}} bahasa keutamaan dari halaman boleh terjemah $3',
	'logentry-pagetranslation-prioritylanguages' => '$1 {{GENDER:$2|menetapkan}} $5 sebagai bahasa keutamaan untuk halaman boleh terjemah $3',
	'logentry-pagetranslation-prioritylanguages-force' => '$1 {{GENDER:$2|mengehadkan}} bahasa-bahasa untuk halaman boleh terjemah $3 kepada $5',
	'logentry-pagetranslation-associate' => '$1 {{GENDER:$2|menambahkan}} halaman boleh terjemah $3 ke dalam kumpulan agregat $4',
	'logentry-pagetranslation-dissociate' => '$1 {{GENDER:$2|membuang}} halaman boleh terjemah $3 dari kumpulan agregat $4',
	'pt-movepage-title' => 'Alihkan laman boleh terjemah $1',
	'pt-movepage-blockers' => 'Laman boleh terjemah ini tidak boleh dipindahkan ke nama baru atas {{PLURAL:$1|ralat|ralat-ralat}} yang berikut:',
	'pt-movepage-block-base-exists' => 'Halaman boleh terjemah sasaran "[[:$1]]" wujud.',
	'pt-movepage-block-base-invalid' => 'Halaman boleh terjemah sasaran bukan tajuk yang sah.',
	'pt-movepage-block-tp-exists' => 'Laman penterjemahan sasaran [[:$2]] wujud.',
	'pt-movepage-block-tp-invalid' => 'Tajuk laman penterjemahan sasaran untuk [[:$1]] adalah tidak sah (terlalu panjang?).',
	'pt-movepage-block-section-exists' => 'Halaman sasaran "[[:$2]]" untuk unit terjemahan itu wujud.',
	'pt-movepage-block-section-invalid' => 'Tajuk halaman sasaran "[[:$1]]" untuk unit terjemahan adalah itu tidak sah (terlalu panjang?).',
	'pt-movepage-block-subpage-exists' => 'Sublaman sasaran [[:$2]] wujud.',
	'pt-movepage-block-subpage-invalid' => 'Tajuk sublaman sasaran untuk [[:$1]] adalah tidak sah (terlalu panjang?).',
	'pt-movepage-list-pages' => 'Senarai laman untuk dipindahkan',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Halaman|Halaman-halaman}} penterjemahan',
	'pt-movepage-list-section' => '{{PLURAL:$1|Halaman|Halaman-halaman}} unit penterjemahan',
	'pt-movepage-list-other' => '{{PLURAL:$1|Subhalaman|Subhalaman-subhalaman}} yang lain',
	'pt-movepage-list-count' => 'Sejumlah $1 laman untuk dipindahkan.',
	'pt-movepage-legend' => 'Pindahkan laman yang boleh diterjemah',
	'pt-movepage-current' => 'Nama sekarang:',
	'pt-movepage-new' => 'Nama baru:',
	'pt-movepage-reason' => 'Sebab:',
	'pt-movepage-subpages' => 'Pindahkan semua sublaman',
	'pt-movepage-action-check' => 'Periksa sama ada langkah ini boleh dilakukan',
	'pt-movepage-action-perform' => 'Lakukan pemindahan',
	'pt-movepage-action-other' => 'Tukar sasaran',
	'pt-movepage-intro' => 'Laman khas ini membolehkan anda untuk memindahkan laman-laman yang ditandai untuk diterjemah.
Tindakan pemindahan itu tidak meninggalkan kesan segera, kerana banyak laman yang perlu dipindahkan.
Sementara laman-laman berkenaan dipindahkan, anda tidak boleh berinteraksi dengan laman-laman yang terlibat.
Kegagalan akan dilogkan dalam [[Special:Log/pagetranslation|log penterjemahan laman]] dan perlu dibaiki dengan tangan.',
	'pt-movepage-logreason' => 'Sebahagian laman boleh terjemah $1.',
	'pt-movepage-started' => 'Laman asas kini telah dipindahkan.
Sila periksa [[Special:Log/pagetranslation|log penterjemahan laman]] untuk ralat dan mesej penyiapan.',
	'pt-locked-page' => 'Laman ini dikunci kerana laman boleh terjemah yang terlibat kini sedang dipindahkan.',
	'pt-deletepage-lang-title' => 'Menghapuskan laman penterjemahan $1.',
	'pt-deletepage-full-title' => 'Menghapuskan laman boleh terjemah $1.',
	'pt-deletepage-invalid-title' => 'Laman yang dinyatakan itu tidak sah.',
	'pt-deletepage-invalid-text' => 'Halaman yang dinyatakan bukan halaman yang boleh diterjemah atau halaman terjemahan.',
	'pt-deletepage-action-check' => 'Senarai laman yang ingin dihapuskan',
	'pt-deletepage-action-perform' => 'Lakukan penghapusan',
	'pt-deletepage-action-other' => 'Tukar sasaran',
	'pt-deletepage-lang-legend' => 'Hapuskan laman penterjemahan',
	'pt-deletepage-full-legend' => 'Hapuskan laman yang boleh diterjemah',
	'pt-deletepage-any-legend' => 'Hapuskan halaman yang boleh diterjemah atau halaman terjemahan',
	'pt-deletepage-current' => 'Nama laman:',
	'pt-deletepage-reason' => 'Sebab:',
	'pt-deletepage-subpages' => 'Hapuskan semua sublaman',
	'pt-deletepage-list-pages' => 'Senarai laman untuk dihapuskan',
	'pt-deletepage-list-translation' => 'Laman penterjemahan',
	'pt-deletepage-list-section' => 'Halaman unit terjemahan',
	'pt-deletepage-list-other' => 'Sublaman lain',
	'pt-deletepage-list-count' => 'Sejumlah $1 laman untuk dihapuskan.',
	'pt-deletepage-full-logreason' => 'Sebahagian laman boleh terjemah $1.',
	'pt-deletepage-lang-logreason' => 'Sebahagian laman penterjemahan $1.',
	'pt-deletepage-started' => 'Sila periksa [[Special:Log/pagetranslation|log penterjemahan laman]] untuk ralat dan mesej penyiapan.',
	'pt-deletepage-intro' => 'Laman khas ini membolehkan anda menghapuskan seluruh laman boleh terjemah atau halaman terjemahan individu dalam sesebuah bahasa.
Tindakan penghapusan itu tidak meninggalkan kesan serta-merta kerana semua halaman yang bergantung padanya akan turut dihapuskan.
Kegagalan akan dilogkan dalam [[Special:Log/pagetranslation|log penterjemahan laman]] dan perlu dibaiki dengan tangan.',
);

/** Maltese (Malti)
 * @author Chrisportelli
 */
$messages['mt'] = array(
	'pagetranslation' => 'Traduzzjoni tal-paġni',
	'tpt-old-pages' => "Xi verżjonijiet ta' {{PLURAL:$1|din il-paġna ġiet immarkata|dawn il-paġni ġew immarkati}} għat-traduzzjoni.",
	'tpt-languages-legend' => 'Lingwi oħra:',
	'tpt-aggregategroup-add' => 'Żid',
	'tpt-aggregategroup-save' => 'Salva',
);

/** Erzya (эрзянь)
 * @author Botuzhaleny-sodamo
 */
$messages['myv'] = array(
	'tpt-diff-old' => 'Икелень текст',
	'tpt-diff-new' => 'Од текст',
	'translate-tag-translate-link-desc' => 'Йутавтык те лопанть',
	'tpt-languages-legend' => 'Лия кельтне:',
);

/** Nahuatl (Nāhuatl)
 * @author Fluence
 */
$messages['nah'] = array(
	'translate-tag-translate-link-desc' => 'Tictlahtōlcuepāz inīn zāzanilli',
);

/** Norwegian Bokmål (norsk bokmål)
 * @author Audun
 * @author Laaknor
 * @author Nghtwlkr
 * @author Njardarlogar
 * @author Purodha
 */
$messages['nb'] = array(
	'pagetranslation' => 'Sideoversetting',
	'right-pagetranslation' => 'Merk versjoner av sider for oversettelse',
	'action-pagetranslation' => 'behandle oversettbare sider',
	'tpt-desc' => 'Utvidelse for oversetting av innholdssider',
	'tpt-section' => 'Oversettelsesenhet $1',
	'tpt-section-new' => 'Ny oversettelsesenhet.
Navn: $1',
	'tpt-section-deleted' => 'Oversettelsesenhet $1',
	'tpt-template' => 'Sidemal',
	'tpt-templatediff' => 'Sidemalen har blitt endret.',
	'tpt-diff-old' => 'Forrige tekst',
	'tpt-diff-new' => 'Ny tekst',
	'tpt-submit' => 'Marker denne versjonen for oversetting',
	'tpt-sections-oldnew' => 'Nye og eksisterende oversettelsesenheter',
	'tpt-sections-deleted' => 'Slettede oversettelsesenheter',
	'tpt-sections-template' => 'Mal for oversettelsesside',
	'tpt-action-nofuzzy' => 'Ikke ugyldiggjør oversettelser',
	'tpt-badtitle' => 'Det angitte sidenavnet ($1) er ikke en gyldig tittel',
	'tpt-nosuchpage' => 'Siden $1 finnes ikke',
	'tpt-oldrevision' => '$2 er ikke den siste versjonen av siden [[$1]].
Kun siste versjoner kan bli markert for oversettelse.',
	'tpt-notsuitable' => 'Side $1 er ikke egnet for oversettelse.
Sjekk at siden har <nowiki><translate></nowiki>-merket og har en gyldig syntaks.',
	'tpt-saveok' => 'Siden [[$1]] har blitt markert for oversettelse med {{PLURAL:$2|én oversettelsesenhet|$2 oversettelsesenheter}}.
Den kan nå <span class="plainlinks">[$3 oversettes]</span>.',
	'tpt-badsect' => '«$1» er ikke et gyldig navn for oversettelsesenheten $2.',
	'tpt-showpage-intro' => 'Nedenfor listes nye, eksisterende og slettede avsnitt opp.
Før denne versjonen merkes for oversettelse, sjekk at endringene i avsnittene er minimert for å unngå unødvendig arbeid for oversetterne.',
	'tpt-mark-summary' => 'Markerte denne versjonen for oversettelse',
	'tpt-edit-failed' => 'Kunne ikke oppdatere siden: $1',
	'tpt-duplicate' => 'Oversettelsens enhetsnavn $1 er brukt mer enn en gang.',
	'tpt-already-marked' => 'Den siste versjonen av denne siden har allerede blitt markert for oversettelse.',
	'tpt-unmarked' => 'Siden $1 er ikke lenger markert for oversettelse.',
	'tpt-list-nopages' => 'Ingen sider er markert for oversettelse, eller er klare for å bli markert for oversettelse.',
	'tpt-new-pages-title' => 'Sider foreslått for oversettelse',
	'tpt-old-pages-title' => 'Sider som oversettes',
	'tpt-other-pages-title' => 'Ødelagte sider',
	'tpt-discouraged-pages-title' => 'Frarådede sider',
	'tpt-new-pages' => '{{PLURAL:$1|Denne siden|Disse sidene}} inneholder tekst med oversettelsesmerker, men ingen versjon av {{PLURAL:$1|denne siden|disse sidene}} er for tiden markert for oversettelse.',
	'tpt-old-pages' => 'En versjon av {{PLURAL:$1|denne siden|disse sidene}} har blitt markert for oversettelse.',
	'tpt-other-pages' => '{{PLURAL:$1|En gammel versjon av denne siden|Eldre versjoner av disse sidene}} er markert for oversettelse, men den siste versjonen kan ikke markeres for oversettelse.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Denne siden|Disse sidene}} frarådes videre oversettelse.',
	'tpt-select-prioritylangs' => 'Kommaseparert liste over prioriterte språkkoder:',
	'tpt-select-prioritylangs-force' => 'Forhindre oversettelser til andre språk enn de prioriterte språkene', # Fuzzy
	'tpt-select-prioritylangs-reason' => 'Årsak:',
	'tpt-sections-prioritylangs' => 'Prioriterte språk',
	'tpt-rev-mark' => 'merk for oversetting',
	'tpt-rev-unmark' => 'fjern fra oversetting',
	'tpt-rev-discourage' => 'fraråd',
	'tpt-rev-encourage' => 'gjenopprett',
	'tpt-rev-mark-tooltip' => 'Merk siste versjon av denne siden for oversetting.',
	'tpt-rev-unmark-tooltip' => 'Fjern denne siden fra oversetting.',
	'tpt-rev-discourage-tooltip' => 'Fraråd videre oversetting av denne siden.',
	'tpt-rev-encourage-tooltip' => 'Gjenopprett denne siden til vanlig oversetting.',
	'translate-tag-translate-link-desc' => 'Oversett denne siden',
	'translate-tag-markthis' => 'Merk denne siden for oversettelse',
	'translate-tag-markthisagain' => 'Denne siden har hatt <span class="plainlinks">[$1 endringer]</span> siden den sist ble <span class="plainlinks">[$2 markert for oversettelse]</span>.',
	'translate-tag-hasnew' => 'Denne siden inneholder <span class="plainlinks">[$1 endringer]</span> som ikke har blitt markert for oversettelse.',
	'tpt-translation-intro' => 'Denne siden er en <span class="plainlinks">[$1 oversatt versjon]</span> av en side [[$2]] og oversettelsen er $3% ferdig og oppdatert.',
	'tpt-languages-legend' => 'Andre språk:',
	'tpt-languages-zero' => 'Begynn oversettelse for dette språket',
	'tpt-target-page' => 'Denne siden kan ikke oppdateres manuelt.
Denne siden er en oversettelse av siden [[$1]] og oversettelsen kan bli oppdatert ved å bruke [$2 oversettelsesverktøyet].',
	'tpt-unknown-page' => 'Dette navnerommet er reservert for oversettelser av innholdssider.
Denne siden som du prøver å redigere ser ikke ut til å samsvare med noen av sidene som er markert for oversettelse.',
	'tpt-translation-restricted' => 'Oversettelse av denne siden til dette språket har blitt forhindret av en oversettelsesadministrator.

Årsak: $1',
	'tpt-discouraged-language-force' => 'En oversettelsesadministrator har begrenset språkene denne siden kan oversettes til. Dette språket er ikke blant disse språkene.

Årsak: $1',
	'tpt-discouraged-language' => 'Dette språket er ikke blant prioritetsspråkene som er satt av en oversettelsesadministrator for denne siden.

Årsak: $1',
	'tpt-discouraged-language-reason' => 'Årsak: $1',
	'tpt-priority-languages' => 'En oversettelsesadministrator har satt prioritetsspråkene for denne gruppen til $1.',
	'tpt-render-summary' => 'Oppdaterer for å svare til ny versjon av kildesiden',
	'tpt-download-page' => 'Eksporter side med oversettelser',
	'aggregategroups' => 'Samlingsgrupper',
	'tpt-aggregategroup-add' => 'Legg til',
	'tpt-aggregategroup-save' => 'Lagre',
	'tpt-aggregategroup-add-new' => 'Legg til en ny samlet gruppe',
	'tpt-aggregategroup-new-name' => 'Navn:',
	'tpt-aggregategroup-new-description' => 'Beskrivelse (valgfri):',
	'tpt-aggregategroup-remove-confirm' => 'Er du sikker på at du ønsker å slette denne gruppa?',
	'tpt-aggregategroup-invalid-group' => 'Gruppa eksisterer ikke',
	'pt-parse-open' => 'Ubalansert &lt;translate>-element.
Oversettelsesmal: <pre>$1</pre>',
	'pt-parse-close' => 'Ubalansert &lt;/translate>-element.
Oversettelsesmal: <pre>$1</pre>',
	'pt-parse-nested' => 'Nøstede &lt;translate>-seksjoner er ikke tillatt.
Elementtekst: <pre>$1</pre>',
	'pt-shake-multiple' => 'Flere avsnittsmarkører for én seksjon.
Seksjonstekst: <pre>$1</pre>',
	'pt-shake-position' => 'Seksjonsmarkører i uventede posisjoner.
Seksjonstekst: <pre>$1</pre>',
	'pt-shake-empty' => 'Tøm seksjon for markør «$1».',
	'log-description-pagetranslation' => 'Logg over handlinger relatert til systemet for sideoversettelser',
	'log-name-pagetranslation' => 'Logg for sideoversettelser',
	'pt-movepage-title' => 'Flytt oversettbar side $1',
	'pt-movepage-blockers' => 'Den oversettbare siden kan ikke flyttes til et nytt navn på grunn av følgende {{PLURAL:$1|feil}}:',
	'pt-movepage-block-base-exists' => 'Målgrunnsiden [[:$1]] finnes.', # Fuzzy
	'pt-movepage-block-base-invalid' => 'Målgrunnsiden er ikke en gyldig tittel.', # Fuzzy
	'pt-movepage-block-tp-exists' => 'Måloversettelsessiden [[:$2]] finnes.',
	'pt-movepage-block-tp-invalid' => 'Måloversettelsessidetittelen for [[:$1]] ville vært ugyldig (for lang?).',
	'pt-movepage-block-section-exists' => 'Målavsnittssiden [[:$2]] finnes fra før.',
	'pt-movepage-block-section-invalid' => 'Målavsnittssidetittelen for [[:$1]] ville vært ugyldig (for lang?).',
	'pt-movepage-block-subpage-exists' => 'Målundersiden [[:$2]] finnes.',
	'pt-movepage-block-subpage-invalid' => 'Målundersidetittelen for [[:$1]] ville vært ugyldig (for lang?).',
	'pt-movepage-list-pages' => 'Liste over sider å flytte',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Oversettelsesside|Oversettelsessider}}',
	'pt-movepage-list-section' => '{{PLURAL:$1|Avsnittsside|Avsnittssider}}',
	'pt-movepage-list-other' => '{{PLURAL:$1|Annen underside|Andre undersider}}',
	'pt-movepage-list-count' => 'Totalt $1 {{PLURAL:$1|side|sider}} å flytte.',
	'pt-movepage-legend' => 'Flytt oversettbar side',
	'pt-movepage-current' => 'Nåværende navn:',
	'pt-movepage-new' => 'Nytt navn:',
	'pt-movepage-reason' => 'Årsak:',
	'pt-movepage-subpages' => 'Flytt alle undersider',
	'pt-movepage-action-check' => 'Kontroller om flyttingen er mulig',
	'pt-movepage-action-perform' => 'Utfør flyttingen',
	'pt-movepage-action-other' => 'Endre mål',
	'pt-movepage-intro' => 'Denne spesialsiden tillater deg å flytte sider som er markert for oversettelse.
Flyttehandlingen vil ikke skje umiddelbart fordi mange sider må flyttes.
Mens sidene flyttes er det ikke mulig å samhandle med gjeldende sider.
Feil vil bli logget i [[Special:Log/pagetranslation|sideoversettelsesloggen]] og de må repareres for hånd.',
	'pt-movepage-logreason' => 'Del av oversettbar side $1.',
	'pt-movepage-started' => 'Basesiden har nå blitt flyttet.
Kontroller [[Special:Log/pagetranslation|sideoversettelsesloggen]] for feil- og fullføringsmeldinger.',
	'pt-locked-page' => 'Denne siden er låst fordi oversettelsessiden blir flyttet nå.',
	'pt-deletepage-lang-title' => 'Sletter den oversettbare siden $1.',
	'pt-deletepage-full-title' => 'Sletter den oversettbare siden $1.',
	'pt-deletepage-invalid-title' => 'Den angitte siden er ikke gyldig.',
	'pt-deletepage-invalid-text' => 'Den angitte siden er ikke en oversettbar side eller en oversettelse av den.', # Fuzzy
	'pt-deletepage-action-check' => 'List opp sider som skal slettes',
	'pt-deletepage-action-perform' => 'Utfør slettingen',
	'pt-deletepage-action-other' => 'Endre mål',
	'pt-deletepage-lang-legend' => 'Slett oversettbar side',
	'pt-deletepage-full-legend' => 'Slett oversettbar side',
	'pt-deletepage-any-legend' => 'Slett oversettbar side eller oversettelse av oversettbar side', # Fuzzy
	'pt-deletepage-current' => 'Sidenavn:',
	'pt-deletepage-reason' => 'Årsak:',
	'pt-deletepage-subpages' => 'Slett alle undersider',
	'pt-deletepage-list-pages' => 'Liste over sider å slette',
	'pt-deletepage-list-translation' => 'Oversettelsessider',
	'pt-deletepage-list-section' => 'Seksjonssider',
	'pt-deletepage-list-other' => 'Andre undersider',
	'pt-deletepage-list-count' => 'Totalt $1 {{PLURAL:$1|side|sider}} å slette.',
	'pt-deletepage-full-logreason' => 'Del av den oversettbare siden $1.',
	'pt-deletepage-lang-logreason' => 'Del av oversettelsessiden $1.',
	'pt-deletepage-started' => 'Sjekk [[Special:Log/pagetranslation|sideoversettelsesloggen]] for feil- og fullføringsmeldinger.',
	'pt-deletepage-intro' => 'Denne spesialsiden lar deg slette hele oversettbare sider eller oversettelser till ett språk.
Slettingen vil ikke være umiddelbar, fordi mange sider må slettes.
Feil vil logges i [[Special:Log/pagetranslation|sideoversettelsesloggen]], og må fikses manuelt.', # Fuzzy
);

/** Dutch (Nederlands)
 * @author Kippenvlees1
 * @author SPQRobin
 * @author Siebrand
 */
$messages['nl'] = array(
	'pagetranslation' => 'Paginavertaling',
	'right-pagetranslation' => "Versies van pagina's voor de vertaling markeren",
	'action-pagetranslation' => "vertaalbare pagina's te beheren",
	'tpt-desc' => "Uitbreiding voor het vertalen van wikipagina's",
	'tpt-section' => 'Vertaaleenheid $1',
	'tpt-section-new' => 'Nieuwe vertaaleenheid.
Naam: $1',
	'tpt-section-deleted' => 'Vertaaleenheid $1',
	'tpt-template' => 'Paginasjabloon',
	'tpt-templatediff' => 'Het paginasjabloon is gewijzigd.',
	'tpt-diff-old' => 'Vorige tekst',
	'tpt-diff-new' => 'Nieuwe tekst',
	'tpt-submit' => 'Deze versie voor vertaling markeren',
	'tpt-sections-oldnew' => 'Nieuwe en bestaande vertaaleenheden',
	'tpt-sections-deleted' => 'Verwijderde vertaaleenheden',
	'tpt-sections-template' => 'Vertaalpaginasjabloon',
	'tpt-action-nofuzzy' => 'Vertalingen niet als verouderd markeren',
	'tpt-badtitle' => 'De opgegeven paginanaam ($1) is geen geldige paginanaam',
	'tpt-nosuchpage' => 'Pagina "$1" bestaat niet',
	'tpt-oldrevision' => '$2 is niet de meest recente versie van de pagina "[[$1]]".
Alleen de meest recente versie kan voor vertaling gemarkeerd worden.',
	'tpt-notsuitable' => 'De pagina "$1" kan niet voor vertaling gemarkeerd worden.
Zorg ervoor dat de labels <nowiki><translate></nowiki> geplaatst zijn en dat deze juist zijn toegevoegd.',
	'tpt-saveok' => 'De pagina [[$1]] is gemarkeerd voor vertaling met $2 te vertalen {{PLURAL:$2|vertaaleenheid|vertaaleenheden}}.
De pagina kan nu  <span class="plainlinks">[$3 vertaald]</span> worden.',
	'tpt-badsect' => '"$1" is geen geldige naam voor vertaaleenheid $2.',
	'tpt-showpage-intro' => 'Hieronder zijn nieuwe, bestaande en verwijderde vertaaleenheden opgenomen.
Controleer voordat u deze versie voor vertaling markeert of de wijzigingen aan de vertaaleenheden zo klein mogelijk zijn om onnodig werk voor vertalers te voorkomen.',
	'tpt-mark-summary' => 'Heeft deze versie voor vertaling gemarkeerd',
	'tpt-edit-failed' => 'De pagina "$1" kon niet bijgewerkt worden.',
	'tpt-duplicate' => 'De vertaaleenheid "$1" wordt meer dan eens gebruikt.',
	'tpt-already-marked' => 'De meest recente versie van deze pagina is al gemarkeerd voor vertaling.',
	'tpt-unmarked' => 'Pagina "$1" is niet langer te vertalen.',
	'tpt-list-nopages' => "Er zijn geen pagina's gemarkeerd voor vertaling, noch klaar om gemarkeerd te worden voor vertaling.",
	'tpt-new-pages-title' => 'Voorgesteld voor vertaling',
	'tpt-old-pages-title' => 'Te vertalen',
	'tpt-other-pages-title' => 'Kapot',
	'tpt-discouraged-pages-title' => 'Ontmoedigd',
	'tpt-new-pages' => "Deze {{PLURAL:$1|pagina bevat|pagina's bevatten}} tekst met vertalingslabels, maar van deze {{PLURAL:$1|pagina|pagina's}} is geen versie gemarkeerd voor vertaling.",
	'tpt-old-pages' => "Er is al een versie van deze {{PLURAL:$1|pagina|pagina's}} gemarkeerd voor vertaling.",
	'tpt-other-pages' => '{{PLURAL:$1|Een oude versie van deze pagina is|Oude versies van deze pagina zijn}} gemarkeerd voor vertaling,
maar de laatste {{PLURAL:$1|versie kan|versies kunnen}} niet gemarkeerd worden voor vertaling.',
	'tpt-discouraged-pages' => "Voor deze {{PLURAL:$1|pagina|pagina's}} wordt vertalen ontmoedigd.",
	'tpt-select-prioritylangs' => "Prioriteitstalen (taalcodes door komma's gescheiden):",
	'tpt-select-prioritylangs-force' => 'Vertaling beperken tot alleen deze prioriteitstalen',
	'tpt-select-prioritylangs-reason' => 'Reden:',
	'tpt-sections-prioritylangs' => 'Prioriteitstalen',
	'tpt-rev-mark' => 'voor vertaling markeren',
	'tpt-rev-unmark' => 'als te vertalen pagina verwijderen',
	'tpt-rev-discourage' => 'ontmoedigen',
	'tpt-rev-encourage' => 'herstellen',
	'tpt-rev-mark-tooltip' => 'De laatste versie van deze pagina voor vertaling markeren.',
	'tpt-rev-unmark-tooltip' => 'Deze pagina niet langer laten vertalen.',
	'tpt-rev-discourage-tooltip' => 'Vertalen van deze pagina ontmoedigen.',
	'tpt-rev-encourage-tooltip' => 'Normale vertaling van deze pagina opnieuw instellen.',
	'translate-tag-translate-link-desc' => 'Deze pagina vertalen',
	'translate-tag-markthis' => 'Deze pagina voor vertaling markeren',
	'translate-tag-markthisagain' => 'Deze pagina is <span class="plainlinks">[$1 gewijzigd]</span> sinds deze voor het laatst <span class="plainlinks">[$2 voor vertaling gemarkeerd]</span> is geweest.',
	'translate-tag-hasnew' => 'Aan deze pagina zijn <span class="plainlinks">[$1 wijzigingen]</span> gemaakt die niet voor vertaling zijn gemarkeerd.',
	'tpt-translation-intro' => 'Deze pagina is een <span class="plainlinks">[$1 vertaalde versie]</span> van de pagina [[$2]] en de vertaling is $3% compleet en bijgewerkt.',
	'tpt-languages-legend' => 'Andere talen:',
	'tpt-languages-zero' => 'Vertaling voor deze taal starten',
	'tpt-target-page' => 'Deze pagina kan niet handmatig worden bijgewerkt.
Deze pagina is een vertaling van de pagina [[$1]].
De vertaling kan bijgewerkt worden via de [$2 vertaalhulpmiddelen].',
	'tpt-unknown-page' => "Deze naamruimte is gereserveerd voor de vertalingen van van pagina's.
De pagina die u probeert te bewerken lijkt niet overeen te komen met een te vertalen pagina.",
	'tpt-translation-restricted' => 'De vertaling van deze pagina naar deze taal is onmogelijk gemaakt door de vertalingenbeheerder.

Reden: $1',
	'tpt-discouraged-language-force' => 'Een vertalingenbeheerder heeft de talen waarin deze pagina vertaald kan worden beperkt. Deze taal maakt geen deel uit van die talen.

Reden: $1',
	'tpt-discouraged-language' => 'De vertalingenbeheerder heeft deze taal niet als prioriteitstaal ingesteld voor deze pagina.

Reden: $1',
	'tpt-discouraged-language-reason' => 'Reden: $1',
	'tpt-priority-languages' => 'Een vertalingenbeheerder heeft de prioriteitstalen voor deze groep ingesteld op $1.',
	'tpt-render-summary' => 'Bijgewerkt vanwege een nieuwe basisversie van de bronpagina',
	'tpt-download-page' => 'Pagina met vertalingen exporteren',
	'aggregategroups' => 'Samengevoegde groepen',
	'tpt-aggregategroup-add' => 'Toevoegen',
	'tpt-aggregategroup-save' => 'Opslaan',
	'tpt-aggregategroup-add-new' => 'Nieuwe samengevoegde groep toevoegen',
	'tpt-aggregategroup-new-name' => 'Naam:',
	'tpt-aggregategroup-new-description' => 'Beschrijving (optioneel):',
	'tpt-aggregategroup-remove-confirm' => 'Weet u zeker dat u deze samengestelde groep wilt verwijderen?',
	'tpt-aggregategroup-invalid-group' => 'De groep bestaat niet',
	'pt-parse-open' => 'Ongebalanceerd label &lt;translate>.
Vertaalsjabloon: <pre>$1</pre>',
	'pt-parse-close' => 'Ongebalanceerd label &lt;translate>.
Vertaalsjabloon: <pre>$1</pre>',
	'pt-parse-nested' => 'Geneste vertaaleenheden met &lt;translate> zijn niet toegestaan.
Labeltekst: <pre>$1</pre>',
	'pt-shake-multiple' => 'Meerdere markeringen vertaaleenheden voor een enkele vertaaleeneheid aangetroffen.
Tekst vertaaleenheid: <pre>$1</pre>',
	'pt-shake-position' => 'Markeringen voor vertaaleenheden op een onverwachte plaats.
Tekst vertaaleenheid: <pre>$1</pre>',
	'pt-shake-empty' => 'Lege vertaaleenheid voor markering $1.',
	'log-description-pagetranslation' => 'Logboek voor handelingen gerelateerd aan het paginavertalingsysteem',
	'log-name-pagetranslation' => 'Logboek paginavertaling',
	'logentry-pagetranslation-mark' => '$1 {{GENDER:$2|heeft}} $3 gemarkeerd voor vertaling',
	'logentry-pagetranslation-unmark' => '$1 {{GENDER:$2|heeft}} $3 als te vertalen pagina verwijderd',
	'logentry-pagetranslation-moveok' => '$1 {{GENDER:$2|heeft}} de vertaalbare pagina $3 hernoemd naar $4',
	'logentry-pagetranslation-movenok' => '$1 {{GENDER:$2|is}} een probleem tegengekomen tijdens het hernoemen van de vertaalbare pagina $3 naar $4',
	'logentry-pagetranslation-deletefok' => '$1 {{GENDER:$2|heeft}} de vertaalbare pagina $3 verwijderd',
	'logentry-pagetranslation-deletefnok' => '$1 {{GENDER:$2|heeft}} $3 niet kunnen verwijderen die hoort bij de vertaalbare pagina $4',
	'logentry-pagetranslation-deletelok' => '$1 {{GENDER:$2|heeft}} de vertaalde pagina $3 verwijderd',
	'logentry-pagetranslation-deletelnok' => '$1 {{GENDER:$2|heeft}} $3 niet kunnen verwijderen die hoort bij de vertaalde pagina $4',
	'logentry-pagetranslation-encourage' => '$1 {{GENDER:$2|heeft}} vertaling van $3 aangemoedigd',
	'logentry-pagetranslation-discourage' => '$1 {{GENDER:$2|heeft}} vertaling van $3 ontmoedigd',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1 {{GENDER:$2|heeft}} prioriteitstalen verwijderd van de vertaalbare pagina $3',
	'logentry-pagetranslation-prioritylanguages' => '$1 {{GENDER:$2|heeft}} de prioriteitstalen ingesteld van de vertaalbare pagina $3 naar $5',
	'logentry-pagetranslation-prioritylanguages-force' => '$1 {{GENDER:$2|heeft}} talen beperkt voor de vertaalbare pagina $3 naar $5',
	'logentry-pagetranslation-associate' => '$1 {{GENDER:$2|heeft}} de vertaalbare pagina $3 toegevoegd aan de samengestelde groep $4',
	'logentry-pagetranslation-dissociate' => '$1 {{GENDER:$2|heeft}} de vertaalbare pagina $3 verwijderd uit de samengestelde groep $4',
	'pt-movepage-title' => 'Te vertalen pagina $1 hernoemen',
	'pt-movepage-blockers' => 'De te vertalen pagina kan niet hernoemd worden vanwege de volgende {{PLURAL:$1|foutmelding|foutmeldingen}}:',
	'pt-movepage-block-base-exists' => 'De vertaalbare doelpagina "[[:$1]]" bestaat al.',
	'pt-movepage-block-base-invalid' => 'De vertaalbare doelpaginanaam is geen geldige paginanaam.',
	'pt-movepage-block-tp-exists' => 'De te vertalen doelpagina [[:$2]] bestaat al.',
	'pt-movepage-block-tp-invalid' => 'De te vertalen doelpaginanaam voor [[:$1]] is ongeldig (te lang?).',
	'pt-movepage-block-section-exists' => 'De doelpagina voor de vertaaleenheid "[[:$2]]" bestaat al.',
	'pt-movepage-block-section-invalid' => 'De doelpagina voor "[[:$1]]" voor de vertaaleenheid is ongeldig (te lang?).',
	'pt-movepage-block-subpage-exists' => 'De doelsubpagina [[:$2]] bestaat al.',
	'pt-movepage-block-subpage-invalid' => 'De doelsubpaginanaam voor [[:$1]] is ongeldig (te lang?).',
	'pt-movepage-list-pages' => "Lijst van te hernoemen pagina's",
	'pt-movepage-list-translation' => "Te vertalen {{PLURAL:$1|pagina|pagina's}}",
	'pt-movepage-list-section' => "{{PLURAL:$1|Pagina|Pagina's}} voor vertaaleenheden",
	'pt-movepage-list-other' => "Overige sub{{PLURAL:$1|pagina|pagina's}}",
	'pt-movepage-list-count' => "In totaal {{PLURAL:$1|is er $1 pagina|zijn er $1 pagina's}} te hernoemen.",
	'pt-movepage-legend' => 'Te vertalen pagina hernoemen',
	'pt-movepage-current' => 'Huidige naam:',
	'pt-movepage-new' => 'Nieuwe naam:',
	'pt-movepage-reason' => 'Reden:',
	'pt-movepage-subpages' => "Alle subpagina's hernoemen",
	'pt-movepage-action-check' => 'Controleren of hernoemen mogelijk is',
	'pt-movepage-action-perform' => 'Hernoemen',
	'pt-movepage-action-other' => 'Doel wijzigen',
	'pt-movepage-intro' => "Via deze speciale pagina kunt u een te vertalen pagina's hernoemen.
Dit wordt niet direct gedaan, omdat het mogelijk is dat heel veel pagina's hernoemd moeten worden.
Terwijl de pagina's worden hernoemd, is het niet mogelijk handelingen uit te voeren op betrokken pagina's.
In het [[Special:Log/pagetranslation|logboek paginavertaling]] worden fouten opgeslagen die op een later moment handmatig hersteld kunnen worden.",
	'pt-movepage-logreason' => 'Onderdeel van te vertalen pagina $1.',
	'pt-movepage-started' => 'De basispagina is nu hernoemd.
Kijk in het [[Special:Log/pagetranslation|logboek paginavertaling]] na of er fouten zijn gemeld en of de complete handeling is afgerond.',
	'pt-locked-page' => 'Deze pagina kan niet gewijzigd worden omdat de te vertalen pagina op dit moment hernoemd wordt.',
	'pt-deletepage-lang-title' => 'De vertaalde pagina $1 wordt verwijderd.',
	'pt-deletepage-full-title' => 'De vertaalbare pagina $1 wordt verwijderd.',
	'pt-deletepage-invalid-title' => 'De opgegeven pagina is ongeldig.',
	'pt-deletepage-invalid-text' => 'De opgegeven pagina is geen vertaalbare pagina en ook geen vertaalde pagina.',
	'pt-deletepage-action-check' => "Lijst met te verwijderen pagina's",
	'pt-deletepage-action-perform' => 'Doorgaan met verwijderen',
	'pt-deletepage-action-other' => 'Doel wijzigen',
	'pt-deletepage-lang-legend' => 'Vertaalde pagina verwijderen',
	'pt-deletepage-full-legend' => 'Vertaalbare pagina verwijderen',
	'pt-deletepage-any-legend' => 'De vertaalbare of vertaalde pagina verwijderen',
	'pt-deletepage-current' => 'Paginanaam:',
	'pt-deletepage-reason' => 'Reden:',
	'pt-deletepage-subpages' => "Alle subpagina's verwijderen",
	'pt-deletepage-list-pages' => "Lijst met te verwijderen pagina's",
	'pt-deletepage-list-translation' => "Vertaalde pagina's",
	'pt-deletepage-list-section' => "Pagina's voor vertaaleenheden",
	'pt-deletepage-list-other' => "Andere subpagina's",
	'pt-deletepage-list-count' => "In totaal {{PLURAL:$1|wordt er $1 pagina|worden er $1 pagina's}} verwijderd.",
	'pt-deletepage-full-logreason' => 'Onderdeel van te vertalen pagina $1.',
	'pt-deletepage-lang-logreason' => 'Onderdeel van de vertaalde pagina $1.',
	'pt-deletepage-started' => 'Controleer het [[Special:Log/pagetranslation|Logboek paginavertaling]] op fouten en of de opdracht is afgerond.',
	'pt-deletepage-intro' => "Via deze pagina kunt u vertaalbare pagina's of vertaalde pagina's in een taal verwijderen.
Het verwijderen vindt niet per direct plaats, omdat het mogelijk is dat vele pagina's verwijderd moeten worden.
Fouten worden opgenomen in het [[Special:Log/pagetranslation|Logboek paginavertaling]] en deze moeten handmatig gecorrigeerd worden.",
);

/** Norwegian Nynorsk (norsk nynorsk)
 * @author Eirik
 * @author Frokor
 * @author Gunnernett
 * @author Harald Khan
 * @author Njardarlogar
 */
$messages['nn'] = array(
	'pagetranslation' => 'Sideomsetjing',
	'right-pagetranslation' => 'Merk versjonar av sider for omsetjing',
	'tpt-desc' => 'Utviding for omsetjing av innhaldssider',
	'tpt-section' => 'Omsetjingseining $1',
	'tpt-section-new' => 'Ny omsetjingseining. Namn: $1',
	'tpt-section-deleted' => 'Omsetjingseining $1',
	'tpt-template' => 'Sidemal',
	'tpt-templatediff' => 'Sidemalen har vorte endra.',
	'tpt-diff-old' => 'Førre teksten',
	'tpt-diff-new' => 'Ny tekst',
	'tpt-submit' => 'Merk denne versjonen for omsetjing',
	'tpt-sections-oldnew' => 'Nye og eksisterande omsetjingseiningar',
	'tpt-sections-deleted' => 'Sletta omsetjingseiningar',
	'tpt-sections-template' => 'Mal for omsetjingsside',
	'tpt-badtitle' => 'Det gjevne sidenamnet ($1) er ikkje ein gyldig tittel',
	'tpt-nosuchpage' => 'Sida $1 finst ikkje',
	'tpt-oldrevision' => '$2 er ikkje den siste versjonen av sida [[$1]].
Berre siste versjonar kan verta markert for omsetjing.',
	'tpt-notsuitable' => 'Side $1 er ikkje høveleg for omsetjing.
Sjekk at sida er merkt med <nowiki><translate></nowiki>-merke og har ein gyldig syntaks.',
	'tpt-saveok' => 'Sida [[$1]] er vorten merkt for omsetjing med {{PLURAL:$2|éi omsetjingseining|$2 omsetjingseiningar}}. Ho kan no verta <span class="plainlinks">[$3 sett om]</span>.',
	'tpt-badsect' => '«$1» er ikkje eit gyldig namn for omsetjingseininga $2.',
	'tpt-mark-summary' => 'Markerte denne versjonen for omsetjing',
	'tpt-edit-failed' => 'Kunne ikkje oppdatera sida: $1',
	'tpt-already-marked' => 'Den siste versjonen av denne sida har allereie vorte markert for omsetjing.',
	'tpt-list-nopages' => 'Ingen sider er markerte for omsetjing, eller klar til å verta markert for omsetjing.',
	'tpt-discouraged-pages-title' => 'Frårådde sider',
	'tpt-new-pages' => '{{PLURAL:$1|Sida|Sidene}} inneheld tekst med omsetjingsmerke, men ingen versjon av {{PLURAL:$1|henne|dei}} er for tida merkt for omsetjing.',
	'tpt-old-pages' => 'Ein versjon av {{PLURAL:$1|denne sida|desse sidene}} er vorten merkt for omsetjing.',
	'tpt-other-pages' => '{{PLURAL:$1|Ein gamal versjon av sida er merkt|Eldre versjonar av sidene er merkte}}  for omsetjing, men {{PLURAL:$1|den siste versjonen|dei siste versjonane}} kan ikkje merkast for omsetjing.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Denne sida|Desse sidene}} er frårådde vidare omsetjing.',
	'tpt-select-prioritylangs-reason' => 'Årsak:',
	'tpt-sections-prioritylangs' => 'Prioriterte språk',
	'tpt-rev-mark' => 'merk for omsetjing',
	'tpt-rev-unmark' => 'fjerna frå omsetjing',
	'tpt-rev-discourage' => 'råd frå',
	'tpt-rev-encourage' => 'attoppretta',
	'tpt-rev-mark-tooltip' => 'Merk den siste versjonen av sida for omsetjing',
	'tpt-rev-unmark-tooltip' => 'Fjerna sida frå omsetjing.',
	'tpt-rev-discourage-tooltip' => 'Råd frå vidare omsetjing av sida.',
	'tpt-rev-encourage-tooltip' => 'Attoppretta sida for normal omsetjing',
	'translate-tag-translate-link-desc' => 'Set om sida',
	'translate-tag-markthis' => 'Merk denne sida for omsetjing',
	'translate-tag-markthisagain' => 'Sida har <span class="plainlinks">[$1 vorten endra]</span> sidan ho sist vart <span class="plainlinks">[$2 merkt for omsetjing]</span>.',
	'translate-tag-hasnew' => 'Sida inneheld <span class="plainlinks">[$1 endringar]</span> som ikkje er merkte for omsetjing.',
	'tpt-translation-intro' => 'Sida er ein <span class="plainlinks">[$1 omsett versjon]</span> av sida [[$2]], og omsetjinga er $3% ferdig.',
	'tpt-languages-legend' => 'Andre språk:',
	'tpt-languages-zero' => 'Byrja omsetjing for dette språket',
	'tpt-translation-restricted' => 'Omsetjing av sida til dette språket er stogga av ein omsetjingsadministrator.

Årsak: $1',
	'tpt-discouraged-language-force' => "'''Sida kan ikkje setjast om til $2.'''

Ein omsetjingsadministrator har avgjort at sida berre kan setjast om til $3.",
	'tpt-discouraged-language-reason' => 'Årsak: $1',
	'tpt-render-summary' => 'Oppdatering for å svara til ny versjon av kjeldesida',
	'tpt-download-page' => 'Eksporter side med omsetjingar',
	'tpt-aggregategroup-add' => 'Legg til',
	'tpt-aggregategroup-save' => 'Lagra',
	'tpt-aggregategroup-new-name' => 'Namn:',
	'log-description-pagetranslation' => 'Logg over handlingar i sideomsetjingssystemet',
	'log-name-pagetranslation' => 'Sideomsetjingslogg',
	'pt-movepage-list-pages' => 'Liste over sider som skal flyttast',
	'pt-movepage-list-translation' => 'Omsetjingssider', # Fuzzy
	'pt-movepage-list-other' => 'Andre undersider', # Fuzzy
	'pt-movepage-list-count' => 'Totalt {{PLURAL:$1|éi side|$1 sider}} å flytta.',
	'pt-movepage-legend' => 'Flytt side som kan setjast om',
	'pt-movepage-current' => 'Namn no:',
	'pt-movepage-new' => 'Nytt namn:',
	'pt-movepage-reason' => 'Årsak:',
	'pt-movepage-subpages' => 'Flytt alle undersider',
	'pt-movepage-action-check' => 'Sjå om flyttinga er mogeleg',
	'pt-movepage-action-perform' => 'Utfør flyttinga',
	'pt-movepage-action-other' => 'Endra mål',
	'pt-deletepage-action-perform' => 'Utfør slettinga',
	'pt-deletepage-action-other' => 'Endra mål',
	'pt-deletepage-lang-legend' => 'Sletta omsetbar side',
	'pt-deletepage-current' => 'Sidenamn:',
	'pt-deletepage-reason' => 'Årsak:',
	'pt-deletepage-subpages' => 'Sletta alle undersider',
	'pt-deletepage-list-pages' => 'Liste over sider som skal slettast',
	'pt-deletepage-list-other' => 'Andre undersider',
);

/** Occitan (occitan)
 * @author Cedric31
 */
$messages['oc'] = array(
	'pagetranslation' => 'Traduccion de paginas',
	'right-pagetranslation' => 'Marcar de versions de paginas per èsser traduchas',
	'tpt-desc' => 'Extension per tradusir de paginas de contengut',
	'tpt-section' => 'Unitat de traduccion $1',
	'tpt-section-new' => 'Unitat de traduccion novèla. Nom : $1',
	'tpt-section-deleted' => 'Unitat de traduccion $1',
	'tpt-template' => 'Modèl de pagina',
	'tpt-templatediff' => 'Lo modèl de pagina a cambiat.',
	'tpt-diff-old' => 'Tèxte precedent',
	'tpt-diff-new' => 'Tèxte novèl',
	'tpt-submit' => 'Marcar aquesta version per èsser tradusida',
	'tpt-sections-oldnew' => 'Unitats de traduccion novèlas e existentas',
	'tpt-sections-deleted' => 'Unitats de traduccion suprimidas',
	'tpt-sections-template' => 'Modèl de pagina de traduccion',
	'tpt-badtitle' => 'Lo nom de pagina donada ($1) es pas un títol valid',
	'tpt-oldrevision' => '$2 es pas la darrièra version de la pagina [[$1]].
Sola la darrièra version de la pagina pòt èsser marcada per èsser tradusida.',
	'tpt-notsuitable' => "La pagina $1 conven pas per èsser tradusida.
Siatz segur(a) que conten la balisa <nowiki><translate></nowiki> e qu'a una sintaxi corrècta.",
	'tpt-saveok' => 'La pagina [[$1]] es estada marcada per èsser tradusida amb $2 {{PLURAL:$2|unitat de traduccion|unitats de traduccion}}.
La pagina pòt èsser <span class="plainlinks">[$3 tradusida]</span> tre ara.',
	'tpt-badsect' => '« $1 » es pas un nom valid per una unitat de traduccion $2.',
	'tpt-showpage-intro' => "Çaijós, las traduccions novèlas, las qu'existisson e las suprimidas.
Abans de marcar aquestas versions per èsser traduchas, verificatz que las modificacions a las seccions son minimizadas per evitar de trabalh inutil als traductors.", # Fuzzy
	'tpt-mark-summary' => 'Aquesta version es estada marcada per èsser tradusida',
	'tpt-edit-failed' => 'Impossible de metre a jorn la pagina $1',
	'tpt-already-marked' => "La darrièra version d'aquesta pagina ja es estada marcada per èsser tradusida.",
	'tpt-list-nopages' => "Cap de pagina es pas estada marcada per èsser tradusida o prèsta per l'èsser.",
	'tpt-new-pages' => "{{PLURAL:$1|Aquesta pagina conten|Aquestas paginas contenon}} de tèxte amb de balisas de traduccion, mas cap de version d'{{PLURAL:$1|aquesta pagina es pas marcada per èsser tradusida|aquestas paginas son pas marcadas per èsser tradusidas}}.",
	'tpt-old-pages' => "De versions d'{{PLURAL:$1|aquesta pagina|aquestas paginas}} son estadas marcadas per èsser traduchas.",
	'tpt-select-prioritylangs-reason' => 'Motiu :',
	'tpt-sections-prioritylangs' => 'Lengas prioritàrias',
	'tpt-rev-mark' => 'marcar per traduccion',
	'tpt-rev-unmark' => 'suprimir de la traduccion',
	'tpt-rev-discourage' => 'descoratjar',
	'tpt-rev-encourage' => 'restablir',
	'translate-tag-translate-link-desc' => 'Tradusir aquesta pagina',
	'translate-tag-markthis' => 'Marcar aquesta pagina per èsser tradusida',
	'translate-tag-markthisagain' => 'Aquesta pagina a agut <span class="plainlinks">[$1 de modificacions]</span> dempuèi qu’es estada darrièrament <span class="plainlinks">[$2 marcada per èsser tradusida]</span>.',
	'translate-tag-hasnew' => 'Aquesta pagina conten <span class="plainlinks">[$1 de modificacions]</span> que son pas marcadas per la traduccion.',
	'tpt-translation-intro' => 'Aquesta pagina es una <span class="plainlinks">[$1 traduccion]</span> de la pagina [[$2]] e la traduccion es completada a $3 % e a jorn.',
	'tpt-languages-legend' => 'Autras lengas :',
	'tpt-target-page' => "Aquesta pagina pòt pas èsser mesa a jorn manualament.
Es una version tradusida de [[$1]] e la traduccion pòt èsser mesa a jorn en utilizant [$2 l'aisina de traduccion].",
	'tpt-unknown-page' => "Aqueste espaci de noms es reservat per la traduccion de paginas.
La pagina qu'ensajatz de modificar sembla pas correspondre a cap de pagina marcada per èsser tradusida.",
	'tpt-render-summary' => 'Mesa a jorn per èsser en acòrd amb la version novèla de la font de la pagina',
	'tpt-download-page' => 'Exportar la pagina amb sas traduccions',
	'tpt-aggregategroup-add' => 'Apondre',
	'tpt-aggregategroup-save' => 'Enregistrar',
	'tpt-aggregategroup-new-name' => 'Nom :',
	'pt-movepage-list-translation' => '{{PLURAL:$1|pagina|paginas}} de traduccion',
	'pt-movepage-new' => 'Nom novèl :',
	'pt-movepage-reason' => 'Motiu :',
	'pt-movepage-action-perform' => 'Tornar nomenar',
	'pt-movepage-action-other' => 'Cambiar la cibla',
	'pt-deletepage-current' => 'Nom de la pagina :',
	'pt-deletepage-reason' => 'Motiu :',
);

/** Oriya (ଓଡ଼ିଆ)
 * @author Ansumang
 * @author Jnanaranjan Sahu
 */
$messages['or'] = array(
	'pagetranslation' => 'ପୃଷ୍ଠା ଅନୁବାଦ',
	'tpt-template' => 'ପୃଷ୍ଠା ଛାଞ୍ଚ',
	'tpt-languages-legend' => 'ଅଲଗା ଭାଷାସବୁ:',
	'pt-movepage-list-translation' => 'ଅନୁବାଦ ପୃଷ୍ଠାସବୁ', # Fuzzy
	'pt-movepage-list-other' => 'ଅନ୍ୟ ଉପପୃଷ୍ଠାସବୁ', # Fuzzy
	'pt-movepage-legend' => 'ଅନୁବାଦ ହୋଇପାରୁଥିବା ପୃଷ୍ଠାଗୁଡିକୁ ଘୁଞ୍ଚାଇବେ',
	'pt-movepage-current' => 'ବର୍ତମାନର ନାମ',
	'pt-movepage-new' => 'ନୂଆ  ନାମ',
	'pt-movepage-reason' => 'କାରଣ :',
	'pt-deletepage-current' => 'ପୃଷ୍ଠା ନାମ:',
	'pt-deletepage-reason' => 'କାରଣ:',
	'pt-deletepage-list-section' => 'ଅନୁଭାଗ ପୃଷ୍ଠାସବୁ', # Fuzzy
);

/** Pampanga (Kapampangan)
 * @author Val2397
 */
$messages['pam'] = array(
	'tpt-template' => '↓Bulung Ulma',
	'tpt-aggregategroup-add' => '↓Dagdag',
	'tpt-aggregategroup-save' => '↓Isikap',
	'tpt-aggregategroup-add-new' => '↓Magdagdag a bayung piabeng lupung',
	'tpt-aggregategroup-new-name' => '↓Lagiu:',
);

/** Deitsch (Deitsch)
 * @author Xqt
 */
$messages['pdc'] = array(
	'pagetranslation' => 'Iwwersetzing vun Bledder',
	'tpt-template' => 'Moddel fer des Blatt',
	'translate-tag-translate-link-desc' => 'Des Blatt iwwersetze',
	'tpt-languages-legend' => 'Annere Schprooche:',
	'pt-movepage-new' => 'Neier Naame:',
	'pt-movepage-reason' => 'Grund:',
	'pt-deletepage-current' => 'Naame vum Blatt:',
	'pt-deletepage-reason' => 'Grund:',
);

/** Pälzisch (Pälzisch)
 * @author Manuae
 */
$messages['pfl'] = array(
	'pagetranslation' => 'Saide iwasedze',
	'right-pagetranslation' => 'Gschischd vunde Saide fas Iwasedze kennzaischne',
	'tpt-desc' => 'Eameschlischds Iwasedze vun Inhaldssaide',
	'tpt-section' => 'Iwasedzungsoihaid „$1“',
	'tpt-section-new' => 'Naiji Iwasedzungsoihaid
Noame: $1',
	'tpt-section-deleted' => 'Iwasedzungsoihaid „$1“',
	'tpt-template' => 'Saidevoalaach',
	'tpt-templatediff' => "Die Saidevoalaach hodsisch g'änad",
	'tpt-diff-old' => 'Vorische Tegschd',
	'tpt-diff-new' => 'Naije Tegschd',
	'tpt-submit' => 'Die Ausgab fas Iwasedze kennzaischne',
	'tpt-sections-oldnew' => 'Naiji un bschdejendi Iwasedzungsoihaide',
	'tpt-sections-deleted' => "G'leschdi Iwasedzungsoihaide",
	'tpt-sections-template' => 'Voalaach fa Iwasedzungssaide',
	'tpt-action-nofuzzy' => 'Iwasedzunge ned als ugildisch eagläre',
	'tpt-badtitle' => 'De oagewene Saidenoame „$1“ ischn ugildische Tidl',
	'tpt-nosuchpage' => 'Said $1 gibds ned',
	'tpt-oldrevision' => '$2 isch ned die naischdi Ausgab vunde Said [[$1]].
Bloß die naischd Ausgab konn fas Iwasedze kennzaischnd werre.',
	'tpt-notsuitable' => "Die Said $1 isch ned fas Iwasedze g'aischnd.
Saida sicha, dasses än <nowiki><translate></nowiki>-Uffschrifd  unän gildischi Sadsuffbau bnudzd werd.",
	'tpt-saveok' => 'Die Said [[$1]] isch midm iwasedzbari {{PLURAL:$2|Abschnidd|$2 Abschnidd}} fas Iwasedze kennzaischnd worre.
Die Said konn donn <span class="plainlinks">[$3 iwasedzd]</span> werre.',
	'tpt-badsect' => '"$1" isch nedn gildischi Noame fa Iwasedzungsoihaid $2.',
	'tpt-showpage-intro' => "Unne sin naiji voahoandeni un g'leschdi B'raisch uffglischded.
Vorm Kennzaischen vunde Ausgab fas Iwasedze, iwabrief, das die Änarunge vunde Abschnidd a klä sin, dmidma unedischi Erwed schbaare dud.", # Fuzzy
	'tpt-mark-summary' => 'Die Said fas Iwasedze kennzaischne',
	'tpt-edit-failed' => 'Said konn ned agdualisiad werre: $1',
	'tpt-duplicate' => 'De Noame vun der Iwasedzungoihaid $1 werd efda als ämol bnudzd.',
	'tpt-already-marked' => 'Die ledschd Ausgab vunde Said isch schun fas Iwasedze kennzaischnd worre.',
	'tpt-unmarked' => 'Said $1 isch nemme fas Iwasedze kennzaischnd.',
	'tpt-list-nopages' => 'Sin kä Saide fas Iwasedze fraigewe un a ned ferdisch, um fraigewe werre zu kenne.',
	'tpt-new-pages-title' => 'Saide, wu fas Iwasedze voagschlache worre sin',
	'tpt-old-pages-title' => 'Saide fas Iwasedze',
	'tpt-other-pages-title' => 'Kapudde Saide',
	'tpt-discouraged-pages-title' => "Abg'rodeni Saide",
	'tpt-new-pages' => "Die {{PLURAL:$1|Said hodn|Saide hawen'än}} Tegschd fas Iwasedze. S'isch awa noch kä Ausgab vunde {{PLURAL:$1|Said|Saide}} fas Iwasedze kennzaischnd worre.",
	'tpt-old-pages' => 'Ä Ausgab vunde {{PLURAL:$1|Said|Saide}} isch fas Iwasedze kennzaischend worre.',
	'tpt-other-pages' => 'Ä aldi Ausgab vunde {{PLURAL:$1|Said|Saide}} sin fas Iwasedze kennzaischnd worre.
Die naischd Ausgab konn awa ned fa ä Iwasedzung kennzaischnd werre.',
	'tpt-discouraged-pages' => "Vunde {{PLURAL:$1|Said|Saide}} isch die Iwasedzung abg'broche worre.",
	'tpt-select-prioritylangs' => 'Komma gdrenndi Lischd vun voaroangischi Schboochcode:',
	'tpt-select-prioritylangs-force' => 'Vahinas Iwasedze in oanare als die voaroangischi Schbrooche', # Fuzzy
	'tpt-select-prioritylangs-reason' => 'Grund:',
	'tpt-sections-prioritylangs' => 'Voaroangischi Schbrooche',
	'tpt-rev-mark' => 'Fas Iwasedze fraigewe',
	'tpt-rev-unmark' => 'Fraigab fas Iwasedze wegnemme',
	'tpt-rev-discourage' => "Abg'rode",
	'tpt-rev-encourage' => 'Widdaheaschdelle',
	'tpt-rev-mark-tooltip' => 'Die ledschd Ausgab vunde Said fas Iwasedze kennzaischne',
	'tpt-rev-unmark-tooltip' => 'Die Said fas Iwasedze wegnemme.',
	'tpt-rev-discourage-tooltip' => 'Rod vuna Iwasedzung vunde Said ab.',
	'tpt-rev-encourage-tooltip' => "S'Iwasedze vunde Said widaheaschdelle.",
	'translate-tag-translate-link-desc' => 'Said iwasedze',
	'translate-tag-markthis' => 'Said fas Iwasedze kennzaischne',
	'translate-tag-markthisagain' => 'Onde Said isch <span class="plainlinks">[$1 gschaffd worre]</span>, nochdemmase <span class="plainlinks">[$2 fas Iwasedz fraigewe]</span> kabd hod.',
	'translate-tag-hasnew' => 'Onde Said hods <span class="plainlinks">[$1 Eawede]</span>, wu ned fas Iwasedze fraigewe worre sin.',
	'tpt-translation-intro' => 'Die Said isch ä <span class="plainlinks">[$1 iwasedzdi Fassung]</span> vunde Said [[$2]] un die Iwasedzung isch zu $3 % ferdisch.',
	'tpt-languages-legend' => 'Onare Schbroche',
	'tpt-languages-zero' => 'Midm Iwasedze in die Schbrooch oafonge',
	'tpt-target-page' => 'Die Said konned vun Hoand agdualisiad werre.
Die Said ischä Iwasedzung vunde Said [[$1]] un die Iwasedzung koama midm [$2 Iwasedzungsweagzaisch] agdualisiere.',
	'tpt-unknown-page' => "D'Noamensraum isch fas Iwasedze vun Wikisaide reswawiad.
Die Said, wu grad schaffe duschd, enschbrischd käna iwasedbari Said.",
	'tpt-translation-restricted' => "S'Iwasedze vunde Said in die Schbrooch isch duaschn Administrator vahinad worre.

Grund: $1",
	'tpt-discouraged-language-force' => "S'Iwasedze vunde Said in Schbrooche isch duaschn Administrator oigschrängd worre. Die Schbrooch isch laida ned unade ealaubde Schbrooche.

Grund: $1",
	'tpt-discouraged-language' => 'Die Schbrooch isch käni vunde voaroangischi Schbrooche, wuen Administrator va die Said gsedzd kabd hod.

Grund: $1',
	'tpt-priority-languages' => 'Än Administrator hoddie voaroangischi Schbrooche fa die Grubb uff $1 gsedzd.',
	'tpt-render-summary' => 'Iwanemm die naijschd Ausgab vunde Qwellsaid',
	'tpt-download-page' => 'Said mide Iwasedzung ausgewe',
	'aggregategroups' => 'Grubbe zsommefasse',
	'tpt-aggregategroup-add' => 'Dzufiesche',
	'tpt-aggregategroup-save' => 'Schbaischare',
	'tpt-aggregategroup-add-new' => 'Ä naiji zsommegfasdi Grubb dzufiesche',
	'tpt-aggregategroup-new-name' => 'Noame:',
	'tpt-aggregategroup-new-description' => 'Bschraiwung (fraiwillisch)',
	'tpt-aggregategroup-remove-confirm' => 'Bischda sischa, dass die zsommegfasdi Grubb lesche wilschd?',
	'tpt-aggregategroup-invalid-group' => 'Die Grubb hods ned',
	'pt-parse-open' => 'Ä &lt;translate&gt;-Uffschrifd hodkä Gescheschdigg.
Iwasedzungsvorlaach: <pre>$1</pre>',
	'pt-parse-close' => 'Än&lt;translate&gt;-Uffschrifd hodkä Gescheschdigg.
Iwasedzungsvorlaach: <pre>$1</pre>',
	'pt-parse-nested' => 'Vaschacheldi &lt;translate&gt;-Abschnidd sined meschlisch.
Text vunde Uffschrifd: <pre>$1</pre>', # Fuzzy
	'pt-shake-multiple' => "Mehrare Abschnittszaische fa'n Abschnidd.
Tegschd vum Abschnidd: <pre>$1</pre>", # Fuzzy
	'pt-shake-position' => 'Abschnittszaische onär ueawadede Schdell.
Tegschd vum Abschnidd: <pre>$1</pre>', # Fuzzy
	'pt-shake-empty' => 'De Abschnid fas Zaische $1 isch lea.', # Fuzzy
	'log-description-pagetranslation' => 'Logbuch fa Änarunge, wus Iwasedzungssischdem fa Saide noidud',
	'log-name-pagetranslation' => 'Iwasedzungs-Logbuch',
	'pt-movepage-title' => 'Bweesch die iwasedzba Said $1',
	'pt-movepage-blockers' => 'Die iwasedzba Said kon {{PLURAL:$1|weschm|weschede}} Fehla ned uffde naije Noame bweschd werre:',
	'pt-movepage-block-base-exists' => 'Die Zielbasis Said [[:$1]] hods schun.', # Fuzzy
	'pt-movepage-block-base-invalid' => 'Die Grundsaid hod kän gildische Tidl.', # Fuzzy
	'pt-movepage-block-tp-exists' => 'Die Iwasedzungsaid [[:$2]] hods schun.',
	'pt-movepage-block-tp-invalid' => 'Die Iwasedzung vum Saidetitl fa [[:$1]] deed ned gildisch soi (zu loang?).',
	'pt-movepage-block-section-exists' => 'Die Abschniddsaid [[:$2]] hods schun.', # Fuzzy
	'pt-movepage-block-section-invalid' => 'Die Iwasedzung vunde Abschniddsaid fa [[:$1]] deed ned gildisch soi (zu loang?).', # Fuzzy
	'pt-movepage-block-subpage-exists' => 'Die Unasaid [[:$2]] hods schun.',
	'pt-movepage-block-subpage-invalid' => 'De Saidetitl fa [[:$1]] deed ned gildisch soi (zu loang?).',
	'pt-movepage-list-pages' => 'Lisch vunde Saide, wu zu vaschiewe sin',
	'pt-movepage-list-translation' => 'Iwasedzdi {{PLURAL:$1|Said|Saide}}',
	'pt-movepage-list-section' => 'Iwwasezdi Grubb {{PLURAL:$1|Said|Saide}}',
	'pt-movepage-list-other' => 'Waidari Una{{PLURAL:$1|said|saide}}',
	'pt-movepage-list-count' => 'Gsomd hods $1 {{PLURAL:$1|Said|Saide}} fas vaschiewe.',
	'pt-movepage-legend' => 'Bweesch die iwasedzba Said',
	'pt-movepage-current' => 'Agduelle Noame:',
	'pt-movepage-new' => 'Naije Noame:',
	'pt-movepage-reason' => 'Grund:',
	'pt-movepage-subpages' => 'Beweesch alli Unsasaide',
	'pt-movepage-action-check' => 'Iwabrief, obs Vaschiewe meschlisch isch',
	'pt-movepage-action-perform' => 'Vaschiebs',
	'pt-movepage-action-other' => 'Änas Ziel',
	'pt-movepage-intro' => 'Die Schbezialsaid eameschlischds Saide zu vaschiewe, wu fas Iwasedze gkennzaischnd worre sin.
Die Vaschiewung gehdned glaisch, wails viel Saide hod.
Werendm Vaschiewe komma die Saide ned bnudze.
Fehla werre im [[Special:Log/pagetranslation|Iwasedzungs-Logbuch]] uffgschriewe un missn vun Hond vaänad werre.',
	'pt-movepage-logreason' => 'Deel vunde iwasedzbari Said $1.',
	'pt-movepage-started' => 'Die Grundsaid isch vaschowe worre.
Briefs [[Special:Log/pagetranslation|Übersetzungs-Logbuch]] uff Nochrischde vun Fehla- unde Ferdischschdellung.',
	'pt-locked-page' => 'Die Said isch gsischad, wail die Iwasetzungssaid grad vaschowe werd.',
	'pt-deletepage-lang-title' => 'Lesche vunde iwasedzdi Saide $1.',
	'pt-deletepage-full-title' => 'Lesche vunde iwasedzbari Saide $1.',
	'pt-deletepage-invalid-title' => 'Die oagewe Said isch ugildisch.',
	'pt-deletepage-invalid-text' => 'Die ogewe Said konned iwwasezd werre un isch a kä Iwasedzung.',
	'pt-deletepage-action-check' => 'Zaisch leschbari Saide',
	'pt-deletepage-action-perform' => 'Leschs',
	'pt-deletepage-action-other' => 'Änas Ziel',
	'pt-deletepage-lang-legend' => 'Iwasedzdi Said lesche',
	'pt-deletepage-full-legend' => 'Iwasedzbari Said lesche',
	'pt-deletepage-any-legend' => 'Iwasedzbari oda iwasedzdi Said lesche',
	'pt-deletepage-current' => 'Saidenoame:',
	'pt-deletepage-reason' => 'Grund:',
	'pt-deletepage-subpages' => 'Lesch alli Unasaide',
	'pt-deletepage-list-pages' => 'Lisch vunde Saide, wu zu lesche sin',
	'pt-deletepage-list-translation' => 'Iwasedzdi Saide',
	'pt-deletepage-list-section' => 'Iwwasedzungs-Grubbsaide',
	'pt-deletepage-list-other' => 'Waidari Unasaide',
	'pt-deletepage-list-count' => 'Gsomd hods $1 {{PLURAL:$1|Said|Saide}} fas lesche.',
	'pt-deletepage-full-logreason' => 'Deel vunde iwasedzbari Said $1.',
	'pt-deletepage-lang-logreason' => 'Deel vunde iwasedzde Said $1.',
	'pt-deletepage-started' => 'Iwabriefs [[Special:Log/pagetranslation|Iwasedzungs-Logbuch]] noch Fehla un Nochrischde fas Feadischschdelle.',
	'pt-deletepage-intro' => "Die Schbezialsaid konn alli iwasedzbari oda iwasedzdi Saide vunär Schbrooch lesche.
S'Lesche werd ned glaisch gmachd, wenns viel Saide sin.
Fehla werren im [[Special:Log/pagetranslation|Iwasedzungs-Logbuch]] oigdraache un missn vun Hoand b'rischdischd werre.", # Fuzzy
);

/** Polish (polski)
 * @author Amire80
 * @author BeginaFelicysym
 * @author Chrumps
 * @author Deejay1
 * @author Equadus
 * @author Leinad
 * @author Olgak85
 * @author Sp5uhe
 * @author ToSter
 * @author WTM
 * @author Woytecr
 */
$messages['pl'] = array(
	'pagetranslation' => 'Tłumaczenie strony',
	'right-pagetranslation' => 'Oznaczanie wersji stron do przetłumaczenia',
	'action-pagetranslation' => 'zarządzanie stronami do tłumaczenia',
	'tpt-desc' => 'Rozszerzenie pozwalające tłumaczyć strony treści',
	'tpt-section' => 'Jednostka tłumaczenia $1',
	'tpt-section-new' => 'Nowa jednostka tłumaczenia.
Nazwa – $1',
	'tpt-section-deleted' => 'Jednostka tłumaczenia $1',
	'tpt-template' => 'Szablon strony',
	'tpt-templatediff' => 'Szablon strony został zmieniony.',
	'tpt-diff-old' => 'Poprzedni tekst',
	'tpt-diff-new' => 'Nowy tekst',
	'tpt-submit' => 'Oznacz tę wersję do przetłumaczenia',
	'tpt-sections-oldnew' => 'Nowe i istniejące jednostki tłumaczenia',
	'tpt-sections-deleted' => 'Usunięte jednostki tłumaczenia',
	'tpt-sections-template' => 'Szablon strony tłumaczenia',
	'tpt-action-nofuzzy' => 'Nie unieważniaj tłumaczeń',
	'tpt-badtitle' => 'Podana nazwa strony ($1) nie jest dozwolonym tytułem',
	'tpt-nosuchpage' => 'Strona $1 nie istnieje',
	'tpt-oldrevision' => '$2 nie jest najnowszą wersją strony [[$1]].
Tylko najnowsze wersje mogą być oznaczane do tłumaczenia.',
	'tpt-notsuitable' => 'Strona $1 nie nadaje się do tłumaczenia.
Upewnij się, że ma znaczniki <nowiki><translate></nowiki> i właściwą składnię.',
	'tpt-saveok' => 'Strona [[$1]] została oznaczona do tłumaczenia razem z $2 {{PLURAL:$2|jednostką|jednostkami}} tłumaczenia.
Można ją teraz <span class="plainlinks">[$3 przetłumaczyć]</span>.',
	'tpt-badsect' => '„$1” nie jest dozwoloną nazwą jednostki tłumaczenia $2.',
	'tpt-showpage-intro' => 'Poniżej wypisane są nowe, istniejące i usunięte sekcje.
Przed oznaczeniem tej wersji do tłumaczenia, aby uniknąć niepotrzebnej pracy tłumaczy, sprawdź czy zmiany w sekcjach zostały zminimalizowane.',
	'tpt-mark-summary' => 'Oznaczono tę wersję do tłumaczenia',
	'tpt-edit-failed' => 'Nie udało się zaktualizować strony $1',
	'tpt-duplicate' => 'Nazwa jednostki tłumaczenia  $1  jest używana więcej niż jeden raz.',
	'tpt-already-marked' => 'Najnowsza wersja tej strony już wcześniej została oznaczona do tłumaczenia.',
	'tpt-unmarked' => 'Strona $1 nie będzie dłużej oznaczona jako przeznaczona do tłumaczenia.',
	'tpt-list-nopages' => 'Nie oznaczono stron do tłumaczenia i nie ma stron gotowych do oznaczenia do tłumaczenia.',
	'tpt-new-pages-title' => 'Strony proponowane do tłumaczenia',
	'tpt-old-pages-title' => 'Strony będące w tłumaczeniu',
	'tpt-other-pages-title' => 'Uszkodzone strony',
	'tpt-discouraged-pages-title' => 'Strony odradzane',
	'tpt-new-pages' => '{{PLURAL:$1|Ta strona zawiera|Te strony zawierają}} tekst ze znacznikami tłumaczenia, ale żadna wersja {{PLURAL:$1|tej strony|tych stron}} nie jest aktualnie oznaczona do tłumaczenia.',
	'tpt-old-pages' => 'Niektóre wersje {{PLURAL:$1|tej strony|tych stron}} zostały oznaczone do tłumaczenia.',
	'tpt-other-pages' => '{{PLURAL:$1|Stara wersja tej strony jest oznaczona jako przeznaczona|Stare wersje tych stron są oznaczone jako przeznaczone}} do tłumaczenia, ale {{PLURAL:$1|jej aktualna wersja nie może zostać oznaczona jako przeznaczona|ich aktualne wersje nie mogą zostać oznaczone jako przeznaczone}} do tłumaczenia.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Ta strona jest odradzana|Te strony są odradzane}} do dalszego tłumaczenia.',
	'tpt-select-prioritylangs' => 'Kody języków priorytetowych rozdzielone przecinkami:',
	'tpt-select-prioritylangs-force' => 'Zapobieganie tłumaczeniom na języki inne niż priorytetowe',
	'tpt-select-prioritylangs-reason' => 'Powód:',
	'tpt-sections-prioritylangs' => 'Języki priorytetowe',
	'tpt-rev-mark' => 'oznacz do tłumaczenia',
	'tpt-rev-unmark' => 'usuń z tłumaczenia',
	'tpt-rev-discourage' => 'zniechęcić',
	'tpt-rev-encourage' => 'Przywracanie',
	'tpt-rev-mark-tooltip' => 'Oznacz najnowszą wersję tej strony do tłumaczenia.',
	'tpt-rev-unmark-tooltip' => 'Usuń tę stronę z tłumaczenia.',
	'tpt-rev-discourage-tooltip' => 'Zniechęć do dalszych tłumaczeń na tej stronie.',
	'tpt-rev-encourage-tooltip' => 'Przywrócić tę stronę do zwykłego tłumaczenia.',
	'translate-tag-translate-link-desc' => 'Przetłumacz tę stronę',
	'translate-tag-markthis' => 'Oznacz tę stronę do tłumaczenia',
	'translate-tag-markthisagain' => 'Ta strona została zmieniona <span class="plainlinks">[$1 razy]</span>, od kiedy ostatnio była <span class="plainlinks">[$2 oznaczona do tłumaczenia]</span>.',
	'translate-tag-hasnew' => 'Ta strona zawiera <span class="plainlinks">[$1 zmiany]</span>, które nie zostały oznaczone do tłumaczenia.',
	'tpt-translation-intro' => 'Ta strona to <span class="plainlinks">[$1 przetłumaczona wersja]</span> strony [[$2]], a tłumaczenie jest ukończone lub aktualne w $3%.',
	'tpt-languages-legend' => 'Inne języki:',
	'tpt-languages-zero' => 'Rozpocznij tłumaczenie na ten język',
	'tpt-tab-translate' => 'Przetłumacz',
	'tpt-target-page' => 'Ta strona nie może zostać zaktualizowana ręcznie.
Jest ona tłumaczeniem strony [[$1]], a tłumaczenie może zostać zmienione za pomocą [$2 narzędzia tłumacza].',
	'tpt-unknown-page' => 'Ta przestrzeń nazw jest zarezerwowana dla tłumaczeń stron z zawartością.
Strona, którą próbujesz edytować, prawdopodobnie nie odpowiada żadnej stronie oznaczonej do tłumaczenia.',
	'tpt-translation-restricted' => 'Tłumaczenie tej strony na ten język zostało zablokowane przez administratora tłumaczenia.

Powód: $1',
	'tpt-discouraged-language-force' => 'Administrator tłumaczenia ograniczył języki, na które ta strona może być tłumaczona. Ten język do nich nie należy.

Powód: $1',
	'tpt-discouraged-language' => 'Ten język nie należy do języków priorytetowych ustawionych przez administratora tłumaczenia dla tej strony.

Powód: $1',
	'tpt-discouraged-language-reason' => 'Powód: $1',
	'tpt-priority-languages' => 'Administrator tłumaczenia ustawił języki priorytetowe dla tej grupy jako $1 .',
	'tpt-render-summary' => 'Aktualizowanie w celu dopasowania nowej wersji strony źródłowej',
	'tpt-download-page' => 'Wyeksportuj stronę z tłumaczeniami',
	'aggregategroups' => 'Grupy zbiorcze',
	'tpt-aggregategroup-add' => 'Dodaj',
	'tpt-aggregategroup-save' => 'Zapisz',
	'tpt-aggregategroup-add-new' => 'Dodaj nową grupę zbiorczą',
	'tpt-aggregategroup-new-name' => 'Nazwa:',
	'tpt-aggregategroup-new-description' => 'Opis (opcjonalnie):',
	'tpt-aggregategroup-remove-confirm' => 'Na pewno usunąć tę grupę agregacji?',
	'tpt-aggregategroup-invalid-group' => 'Grupa nie istnieje',
	'pt-parse-open' => 'Niezrównoważony znacznik &lt;translate>.
Szablon tłumaczenia – <pre>$1</pre>',
	'pt-parse-close' => 'Niezrównoważony znacznik &lt;/translate>.
Szablon tłumaczenia – <pre>$1</pre>',
	'pt-parse-nested' => 'Zagnieżdżanie jednostek tłumaczenia &lt;translate> nie jest dopuszczalne.
Tekst znacznika: <pre>$1</pre>',
	'pt-shake-multiple' => 'Wiele wyróżników jednostki tłumaczenia dla jednej jednostki tłumaczenia.
Tekst jednostki tłumaczenia: <pre>$1</pre>',
	'pt-shake-position' => 'Wyróżniki jednostki tłumaczenia w nieoczekiwanym miejscu.
Tekst jednostki tłumaczenia: <pre>$1</pre>',
	'pt-shake-empty' => 'Pusta jednostka tłumaczenia dla wyróżnika $1.',
	'log-description-pagetranslation' => 'Rejestr działań związanych z systemem tłumaczenia stron',
	'log-name-pagetranslation' => 'Rejestr tłumaczenia stron',
	'logentry-pagetranslation-mark' => '$1 {{GENDER:$2|oznaczył|oznaczył}} $3 do tłumaczenia',
	'logentry-pagetranslation-unmark' => '$1 {{GENDER:$2|usunął|usunęła}} $3 z tłumaczenia',
	'logentry-pagetranslation-encourage' => '$1 {{GENDER:$2|zachęcił|zachęciła}} do tłumaczenia $3',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1 {{GENDER:$2|usunął|usunęła}} języki priorytetowe ze strony przeznaczonej do tłumaczenia $3',
	'logentry-pagetranslation-prioritylanguages' => '$1 {{GENDER:$2|ustawił|ustawiła}} języki priorytetowe dla strony przeznaczonej do tłumaczenia $3 do $5',
	'logentry-pagetranslation-prioritylanguages-force' => '$1 {{GENDER:$2|ograniczył|ograniczyła}} języki dla strony przeznaczonej do tłumaczenia $3 do $5',
	'logentry-pagetranslation-associate' => '$1 {{GENDER:$2|dodał|dodała}} stronę przeznaczoną do tłumaczenia $3 do połączonej grupy $4',
	'logentry-pagetranslation-dissociate' => '$1 {{GENDER:$2|usunął|usunęła}} stronę przeznaczoną do tłumaczenia $3 z połączonej grupy $4',
	'pt-movepage-title' => 'Przenieś przetłumaczalną stronę $1',
	'pt-movepage-blockers' => 'Przetłumaczalna strona nie może zostać przeniesiona pod nową nazwę ponieważ {{PLURAL:$1|wystąpił następujący błąd|wystąpiły następujące błędy:}}',
	'pt-movepage-block-base-exists' => 'Istnieje docelowa strona przeznaczona do tłumaczenia [[:$1]].',
	'pt-movepage-block-base-invalid' => 'Nazwa docelowej strony do tłumaczenia nie jest poprawnym tytułem.',
	'pt-movepage-block-tp-exists' => 'Istnieje docelowa strona tłumaczenia [[:$2]].',
	'pt-movepage-block-tp-invalid' => 'Nazwa docelowej strony tłumaczenia [[:$1]] może być nieprawidłowa. Może jest zbyt długa?',
	'pt-movepage-block-section-exists' => 'Istnieje docelowa strona [[:$2]] dla jednostki tłumaczenia.',
	'pt-movepage-block-section-invalid' => 'Nazwa docelowej strony [[:$1]] dla jednostki tłumaczenia jest nieprawidłowa. Może jest zbyt długa?',
	'pt-movepage-block-subpage-exists' => 'Docelowa podstrona [[:$2]] istnieje.',
	'pt-movepage-block-subpage-invalid' => 'Nazwa docelowej podstrony [[:$1]] jest nieprawidłowa. Może jest zbyt długa?',
	'pt-movepage-list-pages' => 'Lista stron do przeniesienia',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Strona|Strony}} do przetłumaczenia',
	'pt-movepage-list-section' => '{{PLURAL:$1|Strona|Strony}} jednostki tłumaczenia',
	'pt-movepage-list-other' => '{{PLURAL:$1|Inna podstrona|Inne podstrony}}',
	'pt-movepage-list-count' => 'W sumie do przeniesienia {{PLURAL:$1|jest $1 strona|są $1 strony|jest $1 stron}}.',
	'pt-movepage-legend' => 'Przenieś przetłumaczalną stronę',
	'pt-movepage-current' => 'Obecna nazwa',
	'pt-movepage-new' => 'Nowa nazwa',
	'pt-movepage-reason' => 'Powód',
	'pt-movepage-subpages' => 'Przenieś wszystkie podstrony',
	'pt-movepage-action-check' => 'Sprawdź czy przeniesienie jest wykonalne',
	'pt-movepage-action-perform' => 'Przenieś',
	'pt-movepage-action-other' => 'Zmiana celu',
	'pt-movepage-intro' => 'Ta strona specjalna umożliwia przenoszenie stron, które zostały oznaczone jako wymagające tłumaczenia.
Działanie przenoszenia nie jest natychmiastowe, ponieważ wiele stron wymaga przenoszenia.
Podczas gdy strony są przenoszone, nie jest możliwa praca z tymi stronami poprzez zapytania.
Błędy zostaną odnotowane na [[Special:Log/pagetranslation|stronie rejestru tłumaczeń]] i muszą zostać naprawione ręcznie.',
	'pt-movepage-logreason' => 'Część przetłumaczalnej strony $1.',
	'pt-movepage-started' => 'Strona bazowa jest teraz przenoszona. 
Proszę sprawdzić na [[Special:Log/pagetranslation|stronie rejestru tłumaczeń]] czy nie wystąpiły błędy oraz komunikat o zakończeniu operacji.',
	'pt-locked-page' => 'Ta strona jest zablokowana ponieważ jest przygotowana do przeniesienia.',
	'pt-deletepage-lang-title' => 'Usuwanie strony tłumaczenia  $1.',
	'pt-deletepage-full-title' => 'Usuwanie strony do tłumaczenia  $1.',
	'pt-deletepage-invalid-title' => 'Wybrana strona nie jest poprawna.',
	'pt-deletepage-invalid-text' => 'Wybrana strona nie jest przeznaczona do tłumaczenia ani nie jest stroną przetłumaczoną.',
	'pt-deletepage-action-check' => 'Pokaż spis stron, które mają zostać usunięte',
	'pt-deletepage-action-perform' => 'Usuń',
	'pt-deletepage-action-other' => 'Zmiana celu',
	'pt-deletepage-lang-legend' => 'Usuwanie strony tłumaczenia',
	'pt-deletepage-full-legend' => 'Usunąć stronę przeznaczoną do tłumaczenia',
	'pt-deletepage-any-legend' => 'Usuń stronę przeznaczoną do tłumaczenia lub tłumaczenie takiej strony',
	'pt-deletepage-current' => 'Tytuł strony',
	'pt-deletepage-reason' => 'Powód',
	'pt-deletepage-subpages' => 'Usuń wszystkie podstrony',
	'pt-deletepage-list-pages' => 'Lista stron do usunięcia',
	'pt-deletepage-list-translation' => 'Strony tłumaczeń',
	'pt-deletepage-list-section' => 'Strony jednostki tłumaczenia',
	'pt-deletepage-list-other' => 'Inne podstrony',
	'pt-deletepage-list-count' => 'W sumie  $1 {{PLURAL:$1|strona|strony|stron}} do usunięcia.',
	'pt-deletepage-full-logreason' => 'Część strony do tłumaczenia  $1.',
	'pt-deletepage-lang-logreason' => 'Część strony tłumaczenia  $1.',
	'pt-deletepage-started' => 'Sprawdź [[Special:Log/pagetranslation|dziennik strony tłumaczenia]] pod względem błędów i komunikatów zakończenia.',
	'pt-deletepage-intro' => 'Ta specjalna strona pozwala na usuwanie całych stron do tłumaczenia lub tłumaczenia na jeden z języków.
Akcja usuwania nie będzie błyskawiczna, ponieważ będzie musiało być usunięte wiele stron.
Błędy będą rejestrowane [[Special:Log/pagetranslation|w dzienniku tłumaczenia strony]] i muszą one być naprawione ręcznie.', # Fuzzy
);

/** Piedmontese (Piemontèis)
 * @author Borichèt
 * @author Dragonòt
 */
$messages['pms'] = array(
	'pagetranslation' => 'Tradussion dle pàgine',
	'right-pagetranslation' => 'Marché le version dle pàgine për la tradussion',
	'action-pagetranslation' => 'gestì le pàgine da volté',
	'tpt-desc' => 'Estension për fé la tradussion dle pàgine ëd contnù',
	'tpt-section' => 'Unità ëd tradussion $1',
	'tpt-section-new' => 'Neuva unità ëd tradussion.
Nòm: $1',
	'tpt-section-deleted' => 'Unità ëd tradussion $1',
	'tpt-template' => 'Model ëd pàgina',
	'tpt-templatediff' => "Ël model dla pàgina a l'é cangià.",
	'tpt-diff-old' => 'Test ëd prima',
	'tpt-diff-new' => 'Test neuv',
	'tpt-submit' => 'Marca costa version për la tradussion',
	'tpt-sections-oldnew' => 'Unità ëd tradussion neuve e esistente',
	'tpt-sections-deleted' => 'Unità ëd tradussion eliminà',
	'tpt-sections-template' => 'Model ëd pàgina ëd tradussion',
	'tpt-action-nofuzzy' => 'Invalidé nen le tradussion',
	'tpt-badtitle' => "Ël nòm dàit a la pàgina ($1) a l'é pa un tìtol bon",
	'tpt-nosuchpage' => 'La pàgina $1 a esist pa',
	'tpt-oldrevision' => "$2 a l'é nen l'ùltima version dla pàgina [[$1]].
Mach j'ùltime version a peulo esse marcà për la tradussion.",
	'tpt-notsuitable' => "La pàgina $1 a va nen bin për la tradussion.
Ch'a contròla ch'a l'abia le tichëtte <nowiki><translate></nowiki> e na sintassi bon-a.",
	'tpt-saveok' => 'La pàgina [[$1]] a l\'é stàita marcà për la tradussion con $2 {{PLURAL:$2|unità ëd tradussion|unità ëd tradussion}}.
Adess la pàgina a peul esse <span class="plainlinks">[$3 voltà]</span>.',
	'tpt-badsect' => "«$1» a l'é pa un nòm bon për l'unità ëd tradussion $2.",
	'tpt-showpage-intro' => "Sì-sota a son listà j'unità ëd tradussion neuve, esistente e sganfà.
Prima ëd marché costa version për la tradussion, controlé che le modìfiche a j'unità ëd tradussion a sio minimisà për evité dël travaj inùtil ai tradutor.",
	'tpt-mark-summary' => "Costa version a l'é stàita marcà për la tradussion",
	'tpt-edit-failed' => "Impossìbil d'agiorné la pàgina: $1",
	'tpt-duplicate' => "Ël nòm dl'unità ëd tradussion $1 a l'é dovrà pi che na vira.",
	'tpt-already-marked' => "L'ùltima version ëd sa pàgina a l'é stàita già marcà për la tradussion.",
	'tpt-unmarked' => "La pàgina $1 a l'é pi nen marcà për la tradussion.",
	'tpt-list-nopages' => 'A-i é gnun-a pàgina marcà për la tradussion ni pronta për esse marcà për la tradussion.',
	'tpt-new-pages-title' => 'Pàgine proponùe për la tradussion',
	'tpt-old-pages-title' => 'Pàgine an tradussion',
	'tpt-other-pages-title' => 'Pàgine cioche',
	'tpt-discouraged-pages-title' => 'Pàgine dëscoragià',
	'tpt-new-pages' => "{{PLURAL:$1|Sa pàgina a conten|Se pàgine a conten-o}} dël test con la tichëtta ëd tradussion, ma gnun-a version ëd {{PLURAL:$1|costa pàgina|coste pàgine}} a l'é al moment marcà për la tradussion.",
	'tpt-old-pages' => 'Chèiche version ëd {{PLURAL:$1|costa pàgine|coste pàgine}} a son ëstàite marcà për la tradussion.',
	'tpt-other-pages' => "{{PLURAL:$1|Na veja version ëd costa pàgina a l'é|Dle veje version ëd coste pàgine a son}} marcà për la tradussion,
ma {{PLURAL:$1|l'ùltima version a peul|j'ùltime version a peulo}} pa esse marcà për la tradussion.",
	'tpt-discouraged-pages' => "{{PLURAL:$1|Costa pàgina a l'é stàita|Coste pagine a son ëstaite}} dëscoragià da avèj d'àutre tradussion.",
	'tpt-select-prioritylangs' => "Lista dij còdes prioritari ëd le lenghe separà da 'd vìrgole:",
	'tpt-select-prioritylangs-force' => 'Ampedì le tradussion an lenghe diferente da le lenghe prioritarie',
	'tpt-select-prioritylangs-reason' => 'Rason:',
	'tpt-sections-prioritylangs' => 'Lenghe prioritarie',
	'tpt-rev-mark' => 'marca për tradussion',
	'tpt-rev-unmark' => 'gava da la tradussion',
	'tpt-rev-discourage' => 'dëscoragia',
	'tpt-rev-encourage' => "buté 'me ch'a l'era",
	'tpt-rev-mark-tooltip' => "Marché l'ùltima version ëd costa pàgina për la tradussion.",
	'tpt-rev-unmark-tooltip' => 'Gava costa pàgina da la tradussion.',
	'tpt-rev-discourage-tooltip' => "Dëscoragé d'àutre tradussion su costa pagina.",
	'tpt-rev-encourage-tooltip' => 'Ripristiné costa pàgina an tradussion normal.',
	'translate-tag-translate-link-desc' => 'Fé la tradussion ëd sa pàgina',
	'translate-tag-markthis' => 'Marca costa pàgina për la tradussion',
	'translate-tag-markthisagain' => 'Costa pàgina a l\'ha avù <span class="plainlinks">[$1 cangiament]</span> da cand a l\'é stàita <span class="plainlinks">[$2 marcà për la tradussion]</span> l\'ùltima vira.',
	'translate-tag-hasnew' => 'Costa pàgina a conten <span class="plainlinks">[$1 cangiament]</span> ch\'a son pa marcà për la tradussion.',
	'tpt-translation-intro' => 'Sta pàgina-sì a l\'é na <span class="plainlinks">[$1 vërsion traduvùa]</span> ëd na pàgina [[$2]] e la tradussion a l\'é $3% completa e agiornà.',
	'tpt-languages-legend' => 'Àutre lenghe:',
	'tpt-languages-zero' => 'Ancamin-a la tradussion për sta lenga',
	'tpt-target-page' => "Sta pàgina-sì a peul pa esse modificà a man.
Sta pàgina-sì a l'é na tradussion ëd la pàgina [[$1]] e la tradussion a peul esse modificà an dovrand [$2 l'utiss ëd tradussion].",
	'tpt-unknown-page' => "Sto spassi nominal-sì a l'é riservà për tradussion ëd pàgine ëd contnù.
La pàgina ch'it preuve a modifiché a smija pa ch'a corisponda a na pàgina marcà për tradussion.",
	'tpt-translation-restricted' => "La tradussion dë sta pàgina an costa lenga a l'é stàita ampedìa da n'aministrator dle tradussion.

Rason: $1",
	'tpt-discouraged-language-force' => "'''Costa pàgina a peul pa esse voltà an $2.'''

N'aministrator dle tradussion a l'ha decidù che costa pàgina a peul mach esse voltà an $3.",
	'tpt-discouraged-language' => "'''La tradussion an $2 a l'é pa na priorità për costa pàgina.'''

N'aministrator dle tradussion a l'ha decidù d'adressé jë sfòrs ëd tradussion su $3.",
	'tpt-discouraged-language-reason' => 'Rason: $1',
	'tpt-priority-languages' => "N'aministrator ëd tradussion a l'ha ampostà le lenghe prioritarie për sta partìa a $1.",
	'tpt-render-summary' => 'Modifiché për esse com la neuva version dla pàgina sorgiss',
	'tpt-download-page' => 'Espòrta pàgina con tradussion',
	'aggregategroups' => 'Partìe agregà',
	'tpt-aggregategroup-add' => 'Gionta',
	'tpt-aggregategroup-save' => 'Salva',
	'tpt-aggregategroup-add-new' => 'Gionta na neuva partìa agregà',
	'tpt-aggregategroup-new-name' => 'Nòm:',
	'tpt-aggregategroup-new-description' => 'Descrission (opsional):',
	'tpt-aggregategroup-remove-confirm' => "É-lo sicur ëd vorèj scancelé sta partìa d'agregà?",
	'tpt-aggregategroup-invalid-group' => 'La partìa a esist pa',
	'pt-parse-open' => 'Tichëtta &lt;translate> pa bilansà.
Stamp ëd viragi: <pre>$1</pre>',
	'pt-parse-close' => 'Tichëtta &lt;/translate> pa bilansà.
Stamp ëd viragi: <pre>$1</pre>',
	'pt-parse-nested' => "J'unità ëd tradussion &lt;translate> anidà a son pa përmëttùe.
Test ëd la tichëtta: <pre>$1</pre>",
	'pt-shake-multiple' => "Marcador mùltipl d'unità ëd tradussion për un-a unità ëd tradussion.
Test ëd l'unità ëd tradussion: <pre>$1</pre>",
	'pt-shake-position' => "Marcador d'unità ëd tradussion an na posission pa spetà.
Test ëd l'unità ëd tradussion: <pre>$1</pre>",
	'pt-shake-empty' => 'Unità ëd tradussion veuida për ël marcador "$1".',
	'log-description-pagetranslation' => "Registr ëd j'assion colegà al sistema ëd tradussion ëd pàgine",
	'log-name-pagetranslation' => 'Registr dle tradussion ëd pàgine',
	'pt-movepage-title' => 'Tramudé la pàgina da volté $1',
	'pt-movepage-blockers' => 'La pàgina da volté a peul pa esse tramudà a un nòm neuv a motiv ëd {{PLURAL:$1|cost eror|costi eror}}:',
	'pt-movepage-block-base-exists' => 'La pàgina voltàbil pontà "[[:$1]]" a esist.',
	'pt-movepage-block-base-invalid' => "Ël nòm dla pàgina voltàbil pontà a l'é pa un tìtol bon.",
	'pt-movepage-block-tp-exists' => 'La pàgina ëd viragi pontà [[:$2]] a esist.',
	'pt-movepage-block-tp-invalid' => 'Ël tìtol ëd la pàgina ëd viragi pontà për [[:$1]] a podrìa esse pa bon (tròp longh?).',
	'pt-movepage-block-section-exists' => "La pàgina bërsaj «[[:$2]]» për l'unità ëd tradussion a esist.",
	'pt-movepage-block-section-invalid' => "Ël tìtol ëd la pàgina bërsaj për «[[:$1]]» për l'unità ëd tradussion a smijërìa nen bon (tròp longh?).",
	'pt-movepage-block-subpage-exists' => 'La sotpàgina pontà [[:$2]] a esist.',
	'pt-movepage-block-subpage-invalid' => 'Ël tìtol ëd la sotpàgina pontà për [[:$1]] a podrìa esse pa bon (tròp longh?).',
	'pt-movepage-list-pages' => 'Lista dle pàgine da tramudé',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Pàgina|Pàgine}} ëd tradussion',
	'pt-movepage-list-section' => "{{PLURAL:$1|Pàgina|Pàgine}} d'unità ëd tradussion",
	'pt-movepage-list-other' => '{{PLURAL:$1|Àutra sot-pàgina|Àutre sot-pàgine}}',
	'pt-movepage-list-count' => 'An total $1 {{PLURAL:$1|pàgina|pàgine}} da tramudé.',
	'pt-movepage-legend' => 'Tramudé la pàgina da volté',
	'pt-movepage-current' => 'Nòm corent:',
	'pt-movepage-new' => 'Nòm neuv:',
	'pt-movepage-reason' => 'Rason:',
	'pt-movepage-subpages' => 'Tramuda tute le sotpàgine',
	'pt-movepage-action-check' => "Contròla s'a l'é possìbil tramudé",
	'pt-movepage-action-perform' => 'Fé ël tramud',
	'pt-movepage-action-other' => 'Cangé ël bërsaj',
	'pt-movepage-intro' => "Sta pàgina special a-j përmët ëd tramudé dle pàgine ch'a son marcà për la tradussion.
L'assion ëd tramud a sarà pa d'amblé, përchè tante pàgine a dovran esse tramudà.
Antramentre che le pàgine a son tramudà, a l'é nen possìbil anteragì con cole pàgine.
J'eror a saran registrà ant ël [[Special:Log/pagetranslation|registr ëd tradussion ëd le pàgine]] e a dovran esse rangià a man.",
	'pt-movepage-logreason' => 'Tòch ëd la pàgina da volté $1.',
	'pt-movepage-started' => "La pàgina base adess a l'é tramudà.
Për piasì, ch'a contròla ël [[Special:Log/pagetranslation|registr ëd tradussion dle pàgine]] për eror e mëssagi ëd completament.",
	'pt-locked-page' => "Cota pàgina a l'é blocà përchè la pàgina da volté a l'é an camin ch'as tramuda.",
	'pt-deletepage-lang-title' => 'Scancelassion ëd la pàgina ëd tradussion $1.',
	'pt-deletepage-full-title' => 'Scancelassion ëd la pàgina da volté $1.',
	'pt-deletepage-invalid-title' => "La pàgina spessificà a l'é pa bon-a.",
	'pt-deletepage-invalid-text' => "La pàgina specificà a l'é nen na pàgina da volté ni na pàgina ëd tradussion.",
	'pt-deletepage-action-check' => 'Listé le pàgine da scancelé',
	'pt-deletepage-action-perform' => 'Fé la scancelassion',
	'pt-deletepage-action-other' => 'Cangé ël bërsaj',
	'pt-deletepage-lang-legend' => 'Scancelé la pàgina ëd tradussion',
	'pt-deletepage-full-legend' => 'Scancelé la pàgina da volté',
	'pt-deletepage-any-legend' => 'Scancelé la pàgina da volté o la pàgina ëd tradussion',
	'pt-deletepage-current' => 'Nòm ëd la pàgina:',
	'pt-deletepage-reason' => 'Rason:',
	'pt-deletepage-subpages' => 'Scancelé tute le sot-pàgine',
	'pt-deletepage-list-pages' => 'Lista dle pàgine da scancelé',
	'pt-deletepage-list-translation' => 'Pàgine ëd tradussion',
	'pt-deletepage-list-section' => "Pàgine dj'unità ëd tradussion",
	'pt-deletepage-list-other' => 'Àutre sot-pàgine',
	'pt-deletepage-list-count' => 'An total $1 {{PLURAL:$1|pàgina|pàgine}} da scancelé.',
	'pt-deletepage-full-logreason' => 'Tòch ëd la pàgina da volté $1.',
	'pt-deletepage-lang-logreason' => 'Tòch ëd la pàgina ëd tradussion $1.',
	'pt-deletepage-started' => "Për piasì, ch'a contròla ël [[Special:Log/pagetranslation|registr ëd tradussion dle pàgine]] për j'eror e ël mëssagi ëd completament.",
	'pt-deletepage-intro' => "Costa pàgina special a-j përmët dë scancelé na qualsëssìa pàgina, o na pàgina individual ëd tradussion ant na lenga.
L'assion dë scancelassion a sarà pa imedià, përché tute le pàgine dipendente da cola a saran ëdcò scancelà.
J'eror a saran registrà ant ël [[Special:Log/pagetranslation|registr ëd le tradussion ëd le pàgine]]  e a devo esse rangià a man.",
);

/** Pashto (پښتو)
 * @author Ahmed-Najib-Biabani-Ibrahimkhel
 */
$messages['ps'] = array(
	'pagetranslation' => 'د مخ ژباړه',
	'tpt-template' => 'د مخ کينډۍ',
	'tpt-templatediff' => 'د مخ کينډۍ بدلون موندلی.',
	'tpt-diff-old' => 'پخوانی متن',
	'tpt-diff-new' => 'نوی متن',
	'tpt-sections-template' => 'د ژباړې د مخ کينډۍ',
	'tpt-nosuchpage' => 'د $1 په نوم کوم مخ نشته',
	'tpt-old-pages-title' => 'د ژباړې مخونه',
	'tpt-other-pages-title' => 'مات مخونه',
	'tpt-select-prioritylangs-reason' => 'سبب:',
	'tpt-sections-prioritylangs' => 'د لومړيتوب ژبې',
	'translate-tag-translate-link-desc' => 'همدا مخ ژباړل',
	'translate-tag-markthis' => 'همدا مخ د ژباړې لپاره په نښه کول',
	'tpt-languages-legend' => 'نورې ژبې:',
	'tpt-discouraged-language-reason' => 'سبب: $1',
	'aggregategroups' => 'ډلې غونډول',
	'tpt-aggregategroup-add' => 'ورګډول',
	'tpt-aggregategroup-save' => 'خوندي کول',
	'tpt-aggregategroup-new-name' => 'نوم:',
	'pt-movepage-list-pages' => 'د لېږدون د مخونو لړليک',
	'pt-movepage-list-translation' => 'د ژباړې {{PLURAL:$1|مخ|مخونه}}',
	'pt-movepage-list-section' => 'د ژباړې د څپرکي {{PLURAL:$1|مخ|مخونه}}',
	'pt-movepage-list-other' => '{{PLURAL:$1|بل څېرمه مخ|نور څېرمه مخونه}}',
	'pt-movepage-current' => 'اوسنی نوم:',
	'pt-movepage-new' => 'نوی نوم:',
	'pt-movepage-reason' => 'سبب:',
	'pt-movepage-subpages' => 'ټول واړه مخونه لېږدول',
	'pt-movepage-action-perform' => 'لېږد ترسره کول',
	'pt-movepage-action-other' => 'موخه بدلول',
	'pt-deletepage-action-perform' => 'ړنګېدنه ترسره کول',
	'pt-deletepage-action-other' => 'موخه بدلول',
	'pt-deletepage-current' => 'د مخ نوم:',
	'pt-deletepage-reason' => 'سبب:',
	'pt-deletepage-subpages' => 'ټول واړه مخونه ړنګول',
	'pt-deletepage-list-translation' => 'د ژباړې مخونه',
	'pt-deletepage-list-section' => 'د څپرکي د مخونو ژباړه',
	'pt-deletepage-list-other' => 'نور واړه مخونه',
);

/** Portuguese (português)
 * @author Giro720
 * @author Hamilton Abreu
 * @author Luckas
 * @author Malafaya
 * @author SandroHc
 * @author Vivaelcelta
 * @author Waldir
 */
$messages['pt'] = array(
	'pagetranslation' => 'Tradução de páginas',
	'right-pagetranslation' => 'Marcar versões de páginas para tradução',
	'tpt-desc' => 'Extensão para traduzir páginas de conteúdo',
	'tpt-section' => 'Unidade de tradução $1',
	'tpt-section-new' => 'Nova unidade de tradução. Nome: $1',
	'tpt-section-deleted' => 'Unidade de tradução $1',
	'tpt-template' => 'Modelo de página',
	'tpt-templatediff' => 'O modelo de página foi modificado.',
	'tpt-diff-old' => 'Texto anterior',
	'tpt-diff-new' => 'Texto novo',
	'tpt-submit' => 'Marcar esta versão para tradução',
	'tpt-sections-oldnew' => 'Unidades de tradução novas e existentes',
	'tpt-sections-deleted' => 'Unidades de tradução eliminadas',
	'tpt-sections-template' => 'Modelo de página de tradução',
	'tpt-action-nofuzzy' => 'Não invalidar traduções',
	'tpt-badtitle' => 'O nome de página fornecido ($1) não é um título válido',
	'tpt-nosuchpage' => 'A página $1 não existe',
	'tpt-oldrevision' => '$2 não é a versão mais recente da página [[$1]].
Apenas as últimas versões podem ser marcadas para tradução.',
	'tpt-notsuitable' => 'A página $1 não é adequada para tradução.
Certifique-se de que a mesma contém os elementos <nowiki><translate></nowiki> e tem uma sintaxe válida.',
	'tpt-saveok' => 'A página [[$1]] foi marcada para tradução com $2 {{PLURAL:$2|unidade|unidades}} de tradução.
A página pode agora ser <span class="plainlinks">[$3 traduzida]</span>.',
	'tpt-badsect' => '"$1" não é um nome válido para a unidade de tradução $2.',
	'tpt-showpage-intro' => 'Abaixo estão listadas seções novas, existentes e apagadas.
Antes de marcar esta versão para tradução, verifique que as alterações às unidades de tradução são minimizadas para evitar trabalho desnecessário para os tradutores.',
	'tpt-mark-summary' => 'Marcou esta versão para tradução',
	'tpt-edit-failed' => 'Não foi possível atualizar a página: $1',
	'tpt-already-marked' => 'A versão mais recente desta página já foi marcada para tradução.',
	'tpt-unmarked' => 'A página $1 já não está marcada para tradução.',
	'tpt-list-nopages' => 'Não existem páginas marcadas para tradução, nem prontas a ser marcadas para tradução.',
	'tpt-new-pages' => "{{PLURAL:$1|Esta página contém|Estas páginas contêm}} texto com ''tags'' de tradução, mas nenhuma versão {{PLURAL:$1|da página|das páginas}} está presentemente marcada para tradução.",
	'tpt-old-pages' => 'Uma versão {{PLURAL:$1|desta página|destas páginas}} foi marcada para tradução.',
	'tpt-other-pages' => '{{PLURAL:$1|A versão anterior desta página está marcada|Versões anteriores destas páginas estão marcadas}} para tradução, mas a última versão não pode ser marcada para tradução.',
	'tpt-select-prioritylangs-reason' => 'Motivo:',
	'tpt-sections-prioritylangs' => 'Línguas prioritárias',
	'tpt-rev-mark' => 'marcar para tradução',
	'tpt-rev-unmark' => 'remover das páginas para tradução',
	'tpt-rev-discourage' => 'desencorajar',
	'tpt-rev-encourage' => 'restaurar',
	'translate-tag-translate-link-desc' => 'Traduzir esta página',
	'translate-tag-markthis' => 'Marcar esta página para tradução',
	'translate-tag-markthisagain' => 'Esta página tem <span class="plainlinks">[$1 alterações]</span> desde a última vez que foi <span class="plainlinks">[$2 marcada para tradução]</span>.',
	'translate-tag-hasnew' => 'Esta página contém <span class="plainlinks">[$1 alterações]</span> que não estão marcadas para tradução.',
	'tpt-translation-intro' => 'Esta página é uma <span class="plainlinks">[$1 versão traduzida]</span> da página [[$2]] e a tradução está $3% completa e atualizada.',
	'tpt-languages-legend' => 'Outras línguas:',
	'tpt-languages-zero' => 'Iniciar a tradução para este idioma',
	'tpt-target-page' => 'Esta página não pode ser atualizada manualmente.
Ela é uma tradução da página [[$1]] e a tradução pode ser atualizada usando [$2 a ferramenta de tradução].',
	'tpt-unknown-page' => 'Este espaço nominal está reservado para traduções de páginas de conteúdo.
A página que está a tentar editar não parece corresponder a nenhuma página marcada para tradução.',
	'tpt-discouraged-language-reason' => 'Motivo: $1',
	'tpt-render-summary' => 'Atualizando para corresponder à nova versão da página fonte',
	'tpt-download-page' => 'Exportar a página com traduções',
	'aggregategroups' => 'Grupos agregadores',
	'tpt-aggregategroup-add' => 'Adicionar',
	'tpt-aggregategroup-save' => 'Salvar',
	'tpt-aggregategroup-add-new' => 'Adiciona um novo grupo agregador',
	'tpt-aggregategroup-new-name' => 'Nome:',
	'tpt-aggregategroup-new-description' => 'Descrição (opcional):',
	'tpt-aggregategroup-remove-confirm' => 'Tens a certeza que desejas eliminar este grupo agregador?',
	'tpt-aggregategroup-invalid-group' => 'Grupo inexistente',
	'pt-parse-open' => 'O elemento &lt;translate> está desequilibrado.
Modelo de tradução: <pre>$1</pre>',
	'pt-parse-close' => 'O elemento &lt;/translate> está desequilibrado.
Modelo de tradução: <pre>$1</pre>',
	'pt-parse-nested' => 'Não são permitidas secções &lt;translate> cruzadas.
Texto do elemento: <pre>$1</pre>', # Fuzzy
	'pt-shake-multiple' => 'Vários marcadores de secção para uma secção.
Texto da secção: <pre>$1</pre>', # Fuzzy
	'pt-shake-position' => 'Marcadores de secção encontram-se numa posição inesperada.
Texto da secção: <pre>$1</pre>', # Fuzzy
	'pt-shake-empty' => 'Unidade de tradução vazia para o marcador "$1".',
	'log-description-pagetranslation' => 'Registo para operações relacionadas com o sistema de tradução de páginas',
	'log-name-pagetranslation' => 'Registo de tradução de páginas',
	'pt-movepage-title' => 'Mover a página traduzível $1',
	'pt-movepage-blockers' => 'A página traduzível não pode ser movida para outro nome devido {{PLURAL:$1|ao seguinte erro|aos seguintes erros}}:',
	'pt-movepage-block-base-exists' => 'A página base de destino [[:$1]] existe.', # Fuzzy
	'pt-movepage-block-base-invalid' => 'A página base de destino não tem um título válido.', # Fuzzy
	'pt-movepage-block-tp-exists' => 'A página de tradução de destino [[:$2]] existe.',
	'pt-movepage-block-tp-invalid' => 'O título da página de tradução de destino para [[:$1]] seria inválido (talvez demasiado longo).',
	'pt-movepage-block-section-exists' => 'A página da secção de destino [[:$2]] existe.', # Fuzzy
	'pt-movepage-block-section-invalid' => 'O título da página da secção de destino para [[:$1]] seria inválido (talvez demasiado longo).', # Fuzzy
	'pt-movepage-block-subpage-exists' => 'A subpágina de destino [[:$2]] existe.',
	'pt-movepage-block-subpage-invalid' => 'O título da subpágina de destino para [[:$1]] seria inválido (talvez demasiado longo).',
	'pt-movepage-list-pages' => 'Lista de páginas para serem movidas',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Página|Páginas}} de tradução',
	'pt-movepage-list-section' => '{{PLURAL:$1|Página|Páginas}} de seção',
	'pt-movepage-list-other' => '{{PLURAL:$1|Outra subpágina|Outras subpáginas}}',
	'pt-movepage-list-count' => 'No total, $1 {{PLURAL:$1|página para ser movida|páginas para serem movidas}}.',
	'pt-movepage-legend' => 'Mover página traduzível',
	'pt-movepage-current' => 'Nome atual:',
	'pt-movepage-new' => 'Nome novo:',
	'pt-movepage-reason' => 'Motivo:',
	'pt-movepage-subpages' => 'Mover todas as subpáginas',
	'pt-movepage-action-check' => 'Verificar se a movimentação é possível',
	'pt-movepage-action-perform' => 'Realizar a movimentação',
	'pt-movepage-action-other' => 'Alterar o destino',
	'pt-movepage-intro' => 'Esta página especial permite-lhe mover páginas que estão marcadas para tradução.
A operação de movimentação não é instantânea, porque será necessário mover muitas páginas.
Enquanto estas estão a ser movidas, não é possível interagir com as páginas em questão.
As falhas serão registadas no [[Special:Log/pagetranslation|registo de tradução de páginas]] e necessitam de ser reparadas manualmente.',
	'pt-movepage-logreason' => 'Parte da página traduzível $1.',
	'pt-movepage-started' => 'A página base foi movida.
Verifique no [[Special:Log/pagetranslation|registo de tradução de páginas]] se ocorreram erros e se existe a mensagem de conclusão, por favor.',
	'pt-locked-page' => 'Está página está bloqueada porque a página traduzível está a ser movida.',
	'pt-deletepage-lang-title' => 'A eliminar a página traduzida $1.',
	'pt-deletepage-full-title' => 'A eliminar a página traduzível $1.',
	'pt-deletepage-invalid-title' => 'A página especificada é inválida.',
	'pt-deletepage-invalid-text' => 'A página especificada não é uma página traduzível nem uma página traduzida.',
	'pt-deletepage-action-check' => 'Listar as páginas para eliminar',
	'pt-deletepage-action-perform' => 'Eliminar',
	'pt-deletepage-action-other' => 'Alterar o destino',
	'pt-deletepage-lang-legend' => 'Eliminar a página traduzida',
	'pt-deletepage-full-legend' => 'Eliminar a página traduzível',
	'pt-deletepage-any-legend' => 'Eliminar a página traduzível ou página de tradução',
	'pt-deletepage-current' => 'Nome da página:',
	'pt-deletepage-reason' => 'Motivo:',
	'pt-deletepage-subpages' => 'Eliminar todas as subpáginas',
	'pt-deletepage-list-pages' => 'Lista das páginas para eliminar',
	'pt-deletepage-list-translation' => 'Páginas traduzidas',
	'pt-deletepage-list-section' => 'Páginas de unidades de tradução',
	'pt-deletepage-list-other' => 'Outras subpáginas',
	'pt-deletepage-list-count' => '$1 {{PLURAL:$1|página|páginas}} para eliminar, no total.',
	'pt-deletepage-full-logreason' => 'Parte da página traduzível $1.',
	'pt-deletepage-lang-logreason' => 'Parte da página traduzida $1.',
	'pt-deletepage-started' => 'Verifique a existência de erros ou de uma mensagem de sucesso no [[Special:Log/pagetranslation|registo de traduções]].',
	'pt-deletepage-intro' => 'Esta página especial permite eliminar páginas traduzíveis ou as traduções para uma língua.
A eliminação não é instantânea, porque será necessário eliminar muitas páginas.
Os problemas que ocorrerem serão registados no [[Special:Log/pagetranslation|registo de traduções]] e podem exigir reparação manual.', # Fuzzy
);

/** Brazilian Portuguese (português do Brasil)
 * @author Eduardo.mps
 * @author Giro720
 * @author Helder.wiki
 * @author Heldergeovane
 * @author Luckas
 * @author 555
 */
$messages['pt-br'] = array(
	'pagetranslation' => 'Tradução de páginas',
	'right-pagetranslation' => 'Marca versões de páginas para tradução',
	'action-pagetranslation' => 'gerir páginas traduzíveis',
	'tpt-desc' => 'Extensão para traduzir páginas de conteúdo',
	'tpt-section' => 'Unidade de tradução $1',
	'tpt-section-new' => 'Nova unidade de tradução.
Nome: $1',
	'tpt-section-deleted' => 'Unidade de tradução $1',
	'tpt-template' => 'Modelo de página',
	'tpt-templatediff' => 'O modelo de página foi modificado.',
	'tpt-diff-old' => 'Texto anterior',
	'tpt-diff-new' => 'Novo texto',
	'tpt-submit' => 'Marcar esta versão para tradução',
	'tpt-sections-oldnew' => 'Unidades de tradução novas e existentes',
	'tpt-sections-deleted' => 'Unidades de tradução apagadas',
	'tpt-sections-template' => 'Modelo de página de tradução',
	'tpt-action-nofuzzy' => 'Não invalidar traduções',
	'tpt-badtitle' => 'O nome de página dado ($1) não é um título válido',
	'tpt-nosuchpage' => 'A página $1 não existe',
	'tpt-oldrevision' => '$2 não é a versão atual da página [[$1]].
Apenas as versões atuais pode ser marcadas para tradução.',
	'tpt-notsuitable' => 'A página $1 não está adequada para tradução.
Tenha certeza que ela tenha marcas <nowiki><translate></nowiki> e sintaxe válida.',
	'tpt-saveok' => 'A página [[$1]] foi marcada para tradução com $2 {{PLURAL:$2|unidade|unidades}} de tradução.
A página já pode ser <span class="plainlinks">[$3 traduzida]</span>.',
	'tpt-badsect' => '"$1" não é um nome válido para a unidade de tradução $2.',
	'tpt-showpage-intro' => 'A seguir estão listadas as unidades de tradução novas, existentes e removidas.
Antes de marcar esta versão para tradução, verifique se as mudanças nas unidades de tradução foram minimizadas, para que seja evitado trabalho desnecessário aos tradutores.',
	'tpt-mark-summary' => 'Marcou esta versão para tradução',
	'tpt-edit-failed' => 'Não foi possível atualizar a página: $1',
	'tpt-duplicate' => '$1 é usado como nome de unidade de tradução mais de uma vez.',
	'tpt-already-marked' => 'A versão atual desta página já foi marcada para tradução.',
	'tpt-unmarked' => 'A página $1 deixou de estar marcada para tradução.',
	'tpt-list-nopages' => 'Não há páginas nem marcadas para tradução, nem prontas para serem marcadas para tradução.',
	'tpt-new-pages-title' => 'Páginas propostas para tradução',
	'tpt-old-pages-title' => 'Páginas em tradução',
	'tpt-other-pages-title' => 'Páginas com problemas',
	'tpt-discouraged-pages-title' => 'Páginas de tradução desnecessária',
	'tpt-new-pages' => '{{PLURAL:$1|Esta página contém|Estas páginas contêm}} texto com marcas de tradução,
mas nenhuma versão {{PLURAL:$1|desta página|destas páginas}} está marcada para tradução neste momento.',
	'tpt-old-pages' => 'Alguma versão {{PLURAL:$1|desta página foi marcada|destas páginas foram marcadas}} para tradução.',
	'tpt-other-pages' => '{{PLURAL:$1|Uma versão anterior desta página está marcada|Versões anteriores desta página estão marcadas}} para tradução,
mas {{PLURAL:$1|a última versão não pode ser marcada|as últimas versões não podem ser marcadas}} para tradução.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Esta página deixou|Estas páginas deixaram}} de ser relevantes para novas traduções.',
	'tpt-select-prioritylangs' => 'Códigos de idiomas prioritários, separados por vírgulas:',
	'tpt-select-prioritylangs-force' => 'Impedir traduções para idiomas que não sejam os prioritários', # Fuzzy
	'tpt-select-prioritylangs-reason' => 'Motivo:',
	'tpt-sections-prioritylangs' => 'Idiomas prioritários',
	'tpt-rev-mark' => 'marcar para traduzir',
	'tpt-rev-unmark' => 'remover das traduções',
	'tpt-rev-discourage' => 'desmarcar de traduzir',
	'tpt-rev-encourage' => 'retomar traduções',
	'tpt-rev-mark-tooltip' => 'Sinaliza para tradução a edição mais recente desta página.',
	'tpt-rev-unmark-tooltip' => 'Retira a página da listagem das que podem ser traduzidas.',
	'tpt-rev-discourage-tooltip' => 'Faz com que a página não receba novas traduções.',
	'tpt-rev-encourage-tooltip' => 'Faz com que a página possa voltar a ser traduzida.',
	'translate-tag-translate-link-desc' => 'Traduzir esta página',
	'translate-tag-markthis' => 'Marcar esta página para tradução',
	'translate-tag-markthisagain' => 'Esta página tem <span class="plainlinks">[$1 alterações]</span> desde a última vez em que ela foi <span class="plainlinks">[$2 marcada para tradução]</span>.',
	'translate-tag-hasnew' => 'Esta página contém <span class="plainlinks">[$1 alterações]</span> que não estão marcadas para tradução.',
	'tpt-translation-intro' => 'Esta página é uma <span class="plainlinks">[$1 versão traduzida]</span> da página [[$2]]. Sua tradução está $3% completa.',
	'tpt-languages-legend' => 'Outros idiomas:',
	'tpt-languages-zero' => 'Iniciar a tradução para este idioma',
	'tpt-target-page' => 'Esta página não pode ser atualizada manualmente.
Esta página é uma tradução da página [[$1]]. Sua tradução pode ser atualizada usando [$2 a ferramenta de tradução].',
	'tpt-unknown-page' => 'Este espaço nominal é reservado para traduções de páginas de conteúdo.
A página que você está tentando editar não aparenta corresponder a nenhuma página marcada para tradução.',
	'tpt-translation-restricted' => 'Um coordenador de traduções desautorizou que esta página seja traduzida para este idioma.

Motivo: $1',
	'tpt-discouraged-language-force' => 'Um coordenador de traduções limitou os idiomas que poderão receber tradução desta página e este não é um dos que foram definidos.

Motivo: $1',
	'tpt-discouraged-language' => 'Este idioma não é um dos definidos como prioritários por um coordenador de traduções.

Motivo: $1',
	'tpt-discouraged-language-reason' => 'Motivo: $1',
	'tpt-priority-languages' => 'Um coordenador de traduções definiu como idiomas prioritários para este grupo $1.',
	'tpt-render-summary' => 'Atualizando para corresponder à nova versão da página de origem',
	'tpt-download-page' => 'Exportar página e suas traduções',
	'aggregategroups' => 'Grupos agregadores',
	'tpt-aggregategroup-add' => 'Adicionar',
	'tpt-aggregategroup-save' => 'Salvar',
	'tpt-aggregategroup-add-new' => 'Adiciona um novo grupo agregador',
	'tpt-aggregategroup-new-name' => 'Nome:',
	'tpt-aggregategroup-new-description' => 'Descrição (opcional):',
	'tpt-aggregategroup-remove-confirm' => 'Tem certeza que deseja remover este grupo agregador?',
	'tpt-aggregategroup-invalid-group' => 'Grupo inexistente',
	'pt-parse-open' => 'O elemento &lt;translate> está desequilibrado.
Modelo de tradução: <pre>$1</pre>',
	'pt-parse-close' => 'O elemento &lt;/translate> está desequilibrado.
Modelo de tradução: <pre>$1</pre>',
	'pt-parse-nested' => 'Não são permitidas seções &lt;translate> cruzadas.
Texto do elemento: <pre>$1</pre>', # Fuzzy
	'pt-shake-multiple' => 'Vários marcadores de seção para uma seção.
Texto da seção: <pre>$1</pre>', # Fuzzy
	'pt-shake-position' => 'Os marcadores de seção estão em uma posição inesperada.
Texto da seção: <pre>$1</pre>', # Fuzzy
	'pt-shake-empty' => 'Seção em branco para o marcador $1.', # Fuzzy
	'log-description-pagetranslation' => 'Registro para operações relacionadas com o sistema de tradução de páginas',
	'log-name-pagetranslation' => 'Registro de tradução de páginas',
	'pt-movepage-title' => 'Mover a página traduzível $1',
	'pt-movepage-blockers' => 'A página traduzível não pode ser movida para outro nome devido {{PLURAL:$1|ao seguinte erro|aos seguintes erros}}:',
	'pt-movepage-block-base-exists' => 'Existe a página traduzível de destino "[[:$1]]".',
	'pt-movepage-block-base-invalid' => 'O nome da página traduzível de destino não é um título válido.',
	'pt-movepage-block-tp-exists' => 'A página de tradução de destino [[:$2]] já existe.',
	'pt-movepage-block-tp-invalid' => 'O título da página de tradução de destino para [[:$1]] seria inválido (talvez muito longo).',
	'pt-movepage-block-section-exists' => 'A página da seção de destino [[:$2]] já existe.', # Fuzzy
	'pt-movepage-block-section-invalid' => 'O título da página da seção de destino para [[:$1]] seria inválido (talvez muito longo).', # Fuzzy
	'pt-movepage-block-subpage-exists' => 'A subpágina de destino [[:$2]] já existe.',
	'pt-movepage-block-subpage-invalid' => 'O título da subpágina de destino para [[:$1]] seria inválido (talvez muito longo).',
	'pt-movepage-list-pages' => 'Lista de páginas para serem movidas',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Página|Páginas}} de tradução',
	'pt-movepage-list-section' => '{{PLURAL:$1|Página de unidade|Páginas de unidades}} de tradução',
	'pt-movepage-list-other' => '{{PLURAL:$1|Outra subpágina|Outras subpáginas}}',
	'pt-movepage-list-count' => 'Há, no total, $1 {{PLURAL:$1|página para ser movida|páginas para serem movidas}}.',
	'pt-movepage-legend' => 'Mover página traduzível',
	'pt-movepage-current' => 'Nome atual:',
	'pt-movepage-new' => 'Novo nome:',
	'pt-movepage-reason' => 'Motivo:',
	'pt-movepage-subpages' => 'Mover todas as subpáginas',
	'pt-movepage-action-check' => 'Verificar se a movimentação é possível',
	'pt-movepage-action-perform' => 'Realizar a movimentação',
	'pt-movepage-action-other' => 'Alterar o destino',
	'pt-movepage-intro' => 'Esta página especial permite mover páginas que estão marcadas para tradução.
A operação de movimentação não é instantânea, porque será necessário mover muitas páginas.
Enquanto estiverem sendo movidas, não será possível interagir com as páginas em questão.
As falhas serão registradas no [[Special:Log/pagetranslation|registro de tradução de páginas]] e precisarão ser reparadas manualmente.',
	'pt-movepage-logreason' => 'Parte da página traduzível $1.',
	'pt-movepage-started' => 'A página base foi movida.
Verifique no [[Special:Log/pagetranslation|registro de tradução de páginas]] eventuais mensagens de erro e/ou de atividade concluída.',
	'pt-locked-page' => 'Está página está bloqueada porque a página traduzível está sendo movida.',
	'pt-deletepage-lang-title' => 'Eliminar a página traduzível $1.',
	'pt-deletepage-full-title' => 'Eliminando a página traduzível $1.',
	'pt-deletepage-invalid-title' => 'A página especificada é inválida.',
	'pt-deletepage-invalid-text' => 'A página especificada não é uma página traduzível nem uma página traduzida.',
	'pt-deletepage-action-check' => 'Listar páginas para eliminar',
	'pt-deletepage-action-perform' => 'Eliminar',
	'pt-deletepage-action-other' => 'Alterar o destino',
	'pt-deletepage-lang-legend' => 'Elimina a página traduzida',
	'pt-deletepage-full-legend' => 'Elimina a página traduzível',
	'pt-deletepage-any-legend' => 'Eliminar a página traduzível ou a página de tradução',
	'pt-deletepage-current' => 'Nome da página:',
	'pt-deletepage-reason' => 'Motivo:',
	'pt-deletepage-subpages' => 'Eliminar todas as subpáginas',
	'pt-deletepage-list-pages' => 'Lista de páginas para eliminar',
	'pt-deletepage-list-translation' => 'Páginas de tradução',
	'pt-deletepage-list-section' => 'Páginas de unidades de tradução',
	'pt-deletepage-list-other' => 'Outras subpáginas',
	'pt-deletepage-list-count' => 'No total, $1 {{PLURAL:$1|página|páginas}} para serem eliminadas.',
	'pt-deletepage-full-logreason' => 'Parte da página traduzível $1.',
	'pt-deletepage-lang-logreason' => 'Parte da página traduzida $1.',
	'pt-deletepage-started' => 'Verifique no [[Special:Log/pagetranslation|registro de tradução de páginas]] eventuais mensagens de erro e/ou de atividade concluída.',
	'pt-deletepage-intro' => 'Esta página especial permite eliminar todas as páginas de uma página traduzível ou apenas as traduções de um idioma.
A eliminação não será instantânea por serem muitas as páginas integrantes de cada conjunto.
Os erros serão reportados no [[Special:Log/pagetranslation|registro de tradução de páginas]] e precisarão ser corrigidos manualmente.', # Fuzzy
);

/** Romansh (rumantsch)
 * @author Gion-andri
 */
$messages['rm'] = array(
	'pagetranslation' => 'Translaziun da paginas',
	'tpt-diff-old' => 'Ultim text',
	'tpt-diff-new' => 'Nov text',
	'tpt-languages-legend' => 'Autras linguas:',
);

/** Romanian (română)
 * @author Firilacroco
 * @author KlaudiuMihaila
 * @author Minisarm
 * @author Stelistcristi
 */
$messages['ro'] = array(
	'pagetranslation' => 'Traducere pagini',
	'right-pagetranslation' => 'Marchează versiuni ale paginilor pentru a fi traduse',
	'tpt-desc' => 'Extensie pentru traducerea conținutului paginilor',
	'tpt-section' => 'Unitate de traducere $1',
	'tpt-section-new' => 'Unitate de traducere nouă.
Nume: $1',
	'tpt-section-deleted' => 'Unitate de traducere $1',
	'tpt-template' => 'Șablon pagină',
	'tpt-templatediff' => 'Formatul paginii a fost schimbat.',
	'tpt-diff-old' => 'Text precedent',
	'tpt-diff-new' => 'Text nou',
	'tpt-submit' => 'Marchează această versiune pentru traducere',
	'tpt-sections-oldnew' => 'Unități de traducere noi și existente',
	'tpt-sections-deleted' => 'Unități de traducere șterse',
	'tpt-sections-template' => 'Format de pagină de traducere',
	'tpt-action-nofuzzy' => 'Nu invalida traduceri',
	'tpt-badtitle' => 'Numele de pagină dat ($1) nu este un titlu valid',
	'tpt-nosuchpage' => 'Pagina $1 nu există',
	'tpt-oldrevision' => '$2 nu este cea mai recentă versiune a paginii [[$1]].
Doar cele mai recente versiuni pot fi marcate pentru traducere.',
	'tpt-notsuitable' => 'Pagina $1 nu se califică pentru traducere.
Asigurați-vă că are eticheta <nowiki><translate></nowiki> și are o sintaxă validă.',
	'tpt-badsect' => '„$1” nu este un nume valid pentru unitatea de traducere $2.',
	'tpt-mark-summary' => 'Marcat această versiune pentru traducere',
	'tpt-edit-failed' => 'Pagina nu a putut fi actualizată: $1',
	'tpt-already-marked' => 'Ultima versiune a acestei pagini a fost deja marcată pentru traducere.',
	'tpt-unmarked' => 'Pagina $1 nu mai este marcată pentru traducere.',
	'tpt-list-nopages' => 'Nici o pagină nu este marcată pentru traducere sau gata să fie marcată pentru traducere.',
	'tpt-new-pages-title' => 'Pagini propuse pentru traducere',
	'tpt-old-pages-title' => 'Pagini în curs de traducere',
	'tpt-other-pages-title' => 'Pagini eronate',
	'tpt-discouraged-pages-title' => 'Pagini descurajate',
	'tpt-old-pages' => 'Unele versiuni ale {{PLURAL:$1|acestei pagini|acestor pagini}} au fost marcate pentru traducere.',
	'tpt-select-prioritylangs' => 'Listă de coduri de limbă prioritară separate prin virgulă:',
	'tpt-select-prioritylangs-reason' => 'Motiv:',
	'tpt-sections-prioritylangs' => 'Limbi prioritare',
	'tpt-rev-encourage' => 'restaurare',
	'tpt-rev-unmark-tooltip' => 'Elimină această pagină de la traducere.',
	'translate-tag-translate-link-desc' => 'Tradu această pagină',
	'translate-tag-markthis' => 'Marchează această pagină pentru traducere',
	'tpt-translation-intro' => 'Această pagină reprezintă <span class="plainlinks">[$1 versiunea tradusă]</span> a paginii [[$2]], procesul de traducere fiind completat în proporție de $3%.',
	'tpt-languages-legend' => 'Alte limbi:',
	'tpt-discouraged-language-reason' => 'Motiv: $1',
	'tpt-aggregategroup-add' => 'Adaugă',
	'tpt-aggregategroup-save' => 'Salvează',
	'tpt-aggregategroup-add-new' => 'Adaugă un grup de agregare nou',
	'tpt-aggregategroup-new-name' => 'Nume:',
	'tpt-aggregategroup-new-description' => 'Descriere (opțională):',
	'tpt-aggregategroup-remove-confirm' => 'Sigur doriți să ștergeți acest grup de agregare?',
	'tpt-aggregategroup-invalid-group' => 'Grupul nu există',
	'log-name-pagetranslation' => 'Jurnal traducere pagini',
	'pt-movepage-title' => 'Mută pagina traductibilă „$1”',
	'pt-movepage-list-pages' => 'Listă de pagini de mutat',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Pagină|Pagini}} de traducere',
	'pt-movepage-list-other' => '{{PLURAL:$1|Altă subpagină|Alte subpagini}}',
	'pt-movepage-list-count' => 'În total, $1 {{PLURAL:$1|pagină|pagini|de pagini}} de redenumit.',
	'pt-movepage-legend' => 'Mută pagina traductibilă',
	'pt-movepage-current' => 'Nume actual:',
	'pt-movepage-new' => 'Nume nou:',
	'pt-movepage-reason' => 'Motiv:',
	'pt-movepage-subpages' => 'Redenumește toate subpaginile',
	'pt-movepage-action-check' => 'Verifică dacă modificarea este posibilă',
	'pt-movepage-action-perform' => 'Redenumește',
	'pt-movepage-action-other' => 'Schimbă ținta',
	'pt-movepage-logreason' => 'Parte a paginii traductibile $1.',
	'pt-deletepage-invalid-title' => 'Pagina specificată nu este validă.',
	'pt-deletepage-action-perform' => 'Efectuați ștergerea',
	'pt-deletepage-action-other' => 'Schimbați ținta',
	'pt-deletepage-lang-legend' => 'Șterge pagina traducerii',
	'pt-deletepage-full-legend' => 'Șterge pagina traductibilă',
	'pt-deletepage-any-legend' => 'Șterge pagina traductibilă sau pagina de traducere',
	'pt-deletepage-current' => 'Numele paginii:',
	'pt-deletepage-reason' => 'Motiv:',
	'pt-deletepage-subpages' => 'Ștergeți toate subpaginile',
	'pt-deletepage-list-pages' => 'Listă de pagini de șters',
	'pt-deletepage-list-translation' => 'Pagini de traducere',
	'pt-deletepage-list-other' => 'Alte subpagini',
	'pt-deletepage-list-count' => 'În total, $1 {{PLURAL:$1|pagină|pagini|de pagini}} de șters.',
	'pt-deletepage-full-logreason' => 'Parte a paginii traductibile $1.',
	'pt-deletepage-lang-logreason' => 'Parte a paginii de traducere „$1”.',
);

/** tarandíne (tarandíne)
 * @author Joetaras
 */
$messages['roa-tara'] = array(
	'pagetranslation' => 'Pàgene de traduzione',
	'right-pagetranslation' => 'Signe le revisiune de le pàggene pe traduzione',
	'action-pagetranslation' => 'gestisce le pàggene traducibbele',
	'tpt-desc' => 'Estenzione pa traduzione de le pàggene de condenute',
	'tpt-section' => 'Aunità de traduzione $1',
	'tpt-section-new' => 'Nova unità de traduzione.
Nome: $1',
	'tpt-section-deleted' => 'Unità de traduzione $1',
	'tpt-template' => "Pàgene d'u template",
	'tpt-templatediff' => "'U template d'a pàgene ha cangiate.",
	'tpt-diff-old' => 'Teste precedende',
	'tpt-diff-new' => 'Teste nuève',
	'tpt-submit' => 'Signe sta versione pa traduzione',
	'tpt-sections-oldnew' => 'Aunità de traduzione nuève e esistende',
	'tpt-sections-deleted' => 'Aunità de traduziune scangellate',
	'tpt-sections-template' => "Tempalte d'a pàgene de traduzione",
	'tpt-action-nofuzzy' => 'Nò invalidà le traduziune',
	'tpt-badtitle' => "'U nome d'a pàgene date ($1) non g'è 'nu titole valide",
	'tpt-nosuchpage' => "Pàgene $1 non g'esiste",
	'tpt-oldrevision' => "$2 non g'è l'urtema versione d'a pàgene [[$1]].
Sulamende le urteme versiune ponne essere signate pa traduzione.",
	'tpt-badsect' => '"$1" non g\'è \'nu nome valide pe l\'aunità de traduzione $2.',
	'tpt-mark-summary' => 'Signate sta versione pa traduzione',
	'tpt-edit-failed' => "Non ge pozze aggiornà 'a pàgene: $1",
	'tpt-duplicate' => "'U nome $1 de l'aunità de traduzione ha state ausate cchiù de 'na vote.",
	'tpt-already-marked' => "L'urtema versione de sta pàgene ha state ggià signate pa traduzione",
	'tpt-unmarked' => "Pàggene $1 non g'è cchiù signate pa traduzione.",
	'tpt-new-pages-title' => 'Pàggene proposte pa traduzione',
	'tpt-old-pages-title' => 'Pàggene in traduzione',
	'tpt-other-pages-title' => 'Pàggene scuasciate',
	'tpt-discouraged-pages-title' => 'Pàggene da scettà',
	'tpt-select-prioritylangs-reason' => 'Mutive:',
	'tpt-sections-prioritylangs' => 'Lènghe cu priorità',
	'tpt-rev-mark' => 'signe da traducere',
	'tpt-rev-unmark' => "live da 'a traduzione",
	'tpt-rev-discourage' => 'scoragge',
	'tpt-rev-encourage' => 'repristine',
	'translate-tag-translate-link-desc' => 'Traduce sta vosce',
	'translate-tag-markthis' => 'Signe sta pàgene pa traduzione',
	'tpt-languages-legend' => 'Otre lènghe:',
	'tpt-languages-separator' => '&#160;•&#160;',
	'tpt-languages-zero' => "Accuminze 'a traduzione pe sta lènghe",
	'tpt-discouraged-language-reason' => 'Mutive: $1',
	'aggregategroups' => 'Gruppe aggregate',
	'tpt-aggregategroup-add' => 'Aggiunge',
	'tpt-aggregategroup-save' => 'Reggìstre',
	'tpt-aggregategroup-add-new' => "Aggiunge 'nu gruppe aggregate",
	'tpt-aggregategroup-new-name' => 'Nome:',
	'tpt-aggregategroup-new-description' => 'Descrizione (opzionale):',
	'tpt-aggregategroup-remove-confirm' => 'Sì secure ca vuè ccu scangille stu gruppe aggregate?',
	'tpt-aggregategroup-invalid-group' => "'U gruppe non g'esiste",
	'log-name-pagetranslation' => 'Archivije de le traduziune de le pàggene',
	'logentry-pagetranslation-encourage' => "$1 {{GENDER:$2|'ngoragge}} 'a traduzione de $3",
	'logentry-pagetranslation-discourage' => "$1 {{GENDER:$2|scoragge}} 'a traduzione de $3",
	'pt-movepage-title' => 'Spuèste \'a pàgene traducibbile "$1"',
	'pt-movepage-list-pages' => 'Elenghe de le pàggene da spustà',
	'pt-movepage-list-count' => 'In totale $1 {{PLURAL:$1|pàgene|pàggene}} da spustà.',
	'pt-movepage-current' => 'Nome de mò:',
	'pt-movepage-new' => 'Nome nuève:',
	'pt-movepage-reason' => 'Mutive:',
	'pt-movepage-subpages' => 'Spuèste tutte le sottopàggene',
	'pt-movepage-action-perform' => "Fà 'u spostamende",
	'pt-movepage-action-other' => "Cange 'a destinazione",
	'pt-deletepage-action-other' => "Cange 'a destinazione",
	'pt-deletepage-lang-legend' => "Scangille 'a pàgene de traduzione",
	'pt-deletepage-full-legend' => "Scangille 'a pàgene traducibbele",
	'pt-deletepage-current' => "Nome d'a pàgene:",
	'pt-deletepage-reason' => 'Mutive:',
	'pt-deletepage-subpages' => 'Scangille tutte le sottopàggene',
	'pt-deletepage-list-pages' => 'Elenghe de le pàggene da scangellà',
	'pt-deletepage-list-translation' => 'Pàggene de traduzione',
	'pt-deletepage-list-other' => 'Otre sottopàggene',
);

/** Russian (русский)
 * @author Amire80
 * @author Askarmuk
 * @author DR
 * @author Eugrus
 * @author Express2000
 * @author Ferrer
 * @author G0rn
 * @author Grigol
 * @author Hypers
 * @author KPu3uC B Poccuu
 * @author Kaganer
 * @author Lockal
 * @author Purodha
 * @author Александр Сигачёв
 */
$messages['ru'] = array(
	'pagetranslation' => 'Перевод страниц',
	'right-pagetranslation' => 'отметка версий страниц для перевода',
	'action-pagetranslation' => 'управлять переводимыми страницами',
	'tpt-desc' => 'Расширение для перевода содержимого страниц',
	'tpt-section' => 'Блок перевода $1',
	'tpt-section-new' => 'Новый блок перевода. Название: $1',
	'tpt-section-deleted' => 'Элемент перевода $1',
	'tpt-template' => 'Страничный шаблон',
	'tpt-templatediff' => 'Этот страничный шаблон изменён.',
	'tpt-diff-old' => 'Предыдущий текст',
	'tpt-diff-new' => 'Новый текст',
	'tpt-submit' => 'Отметить эту версию для перевода',
	'tpt-sections-oldnew' => 'Новые и существующие элементы перевода',
	'tpt-sections-deleted' => 'Удалённые элементы перевода',
	'tpt-sections-template' => 'Шаблон страницы перевода',
	'tpt-action-nofuzzy' => 'Не помечать переводы как устаревшие',
	'tpt-badtitle' => 'Указанное название страницы ($1) не является допустимым',
	'tpt-nosuchpage' => 'Страница «$1» не существует.',
	'tpt-oldrevision' => '$2 не является последней версией страницы [[$1]].
Только последние версии могут быть отмечены для перевода.',
	'tpt-notsuitable' => 'Страница $1 является неподходящей для перевода.
Убедитесь, что она имеет теги <nowiki><translate></nowiki> и правильный синтаксис.',
	'tpt-saveok' => 'Страница [[$1]] был отмечена для перевода, она содержит $2 {{PLURAL:$2|блок перевода|блока перевода|блоков переводов}}.
Теперь страницу можно <span class="plainlinks">[$3 переводить]</span>.',
	'tpt-offer-notify' => 'Вы можете <span class="plainlinks">[$1 уведомить переводчиков]</span> об этой странице.',
	'tpt-badsect' => '«$1» не является допустимым названием для блока перевода $2.',
	'tpt-showpage-intro' => 'Ниже приведены новые, существующие и удалённые разделы.
Прежде чем пометить эту версию как доступную для перевода, убедитесь, что изменения в переводимых элементах будут минимальны, чтобы минимизировать объём ненужной работы переводчикам.',
	'tpt-mark-summary' => 'Отметить эту версию для перевода',
	'tpt-edit-failed' => 'Невозможно обновить эту страницу: $1',
	'tpt-duplicate' => 'Перевод элемента с названием  $1  используется более одного раза.',
	'tpt-already-marked' => 'Последняя версия этой страницы уже была отмечена для перевода.',
	'tpt-unmarked' => 'Страница $1 больше не отмечена для перевода.',
	'tpt-list-nopages' => 'Нет страниц, отмеченных для перевода, а также нет страниц готовых к отметке.',
	'tpt-new-pages-title' => 'Страницы, предложенные для перевода',
	'tpt-old-pages-title' => 'Страницы в переводе',
	'tpt-other-pages-title' => 'Повреждённые страницы',
	'tpt-discouraged-pages-title' => 'Отклонённые страницы',
	'tpt-new-pages' => '{{PLURAL:$1|Эта страница содержит|Эти страницы содержат}} текст с тегами перевода, но ни одна из версий {{PLURAL:$1|этой страницы|этих страниц}} не отмечена для перевода.',
	'tpt-old-pages' => 'Некоторые версии {{PLURAL:$1|этой страницы|этих страниц}} были отмечены для перевода.',
	'tpt-other-pages' => '{{PLURAL:$1|Старая версия этой страницы отмечена|Старые версии этих страниц отмечены}} для перевода,
но последняя версия не может быть отмечена для перевода.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Этой странице|Этим страницам}} было отказано в дальнейшем переводе.',
	'tpt-select-prioritylangs' => 'Предпочитаемые языки (коды языков, разделенные запятой):',
	'tpt-select-prioritylangs-force' => 'Предотвратить переводы на другие языки, помимо приоритетных',
	'tpt-select-prioritylangs-reason' => 'Причина:',
	'tpt-sections-prioritylangs' => 'Приоритетные языки',
	'tpt-rev-mark' => 'пометить для перевода',
	'tpt-rev-unmark' => 'убрать из перевода',
	'tpt-rev-discourage' => 'исключить',
	'tpt-rev-encourage' => 'восстановить',
	'tpt-rev-mark-tooltip' => 'Отметить последнюю версию этой страницы для перевода.',
	'tpt-rev-unmark-tooltip' => 'Исключить эту страницу из перевода.',
	'tpt-rev-discourage-tooltip' => 'Предотвратить дальнейшие переводы на этой странице.',
	'tpt-rev-encourage-tooltip' => 'Восстановить эту страницу для обычного перевода.',
	'translate-tag-translate-link-desc' => 'Перевести эту страницу',
	'translate-tag-markthis' => 'Отметить эту страницу для перевода',
	'translate-tag-markthisagain' => 'Эта страница была <span class="plainlinks">[$1 изменена]</span> с момента последней <span class="plainlinks">[$2 отметки о переводе]</span>.',
	'translate-tag-hasnew' => 'На этой странице были произведены <span class="plainlinks">[$1 изменения]</span>, не отмеченные для перевода.',
	'tpt-translation-intro' => 'Эта страница является <span class="plainlinks">[$1 переводом]</span> страницы [[$2]]. Перевод актуален и выполнен на $3%.',
	'tpt-languages-legend' => 'Другие языки:',
	'tpt-languages-zero' => 'Начать перевод на этот язык',
	'tpt-tab-translate' => 'Перевести',
	'tpt-target-page' => 'Эта страница не может быть обновлена вручную.
Это перевод страницы [[$1]], перевод может быть обновлён с помощью специального [$2 инструмента перевода].',
	'tpt-unknown-page' => 'Это пространство имён зарезервировано для переводов текстов страниц.
Страница, которую вы пытаетесь изменить, не соответствует какой-либо странице, отмеченной для перевода.',
	'tpt-translation-restricted' => 'Перевод этой страницы на данный язык был предотвращен администратором перевода.


Причина: $1',
	'tpt-discouraged-language-force' => 'Администратор перевода ограничил список языков, на которые может быть переведена данная страница. Данный язык не входит в этот список.


Причина: $1',
	'tpt-discouraged-language' => 'Данный язык не входит в список приоритетных для данной страницы, установленных администратором перевода

Причина: $1',
	'tpt-discouraged-language-reason' => 'Причина: $1',
	'tpt-priority-languages' => 'Администратор перевода установил $1 в качестве приоритетного языка для этой группы.',
	'tpt-render-summary' => 'Обновление для соответствия новой версии исходной страницы.',
	'tpt-download-page' => 'Экспортировать страницу с переводами',
	'aggregategroups' => 'Агрегированные группы',
	'tpt-aggregategroup-add' => 'Добавить',
	'tpt-aggregategroup-save' => 'Сохранить',
	'tpt-aggregategroup-add-new' => 'Добавить новую агрегированную группу',
	'tpt-aggregategroup-new-name' => 'Название:',
	'tpt-aggregategroup-new-description' => 'Описание (необязательно):',
	'tpt-aggregategroup-remove-confirm' => 'Вы правда хотите удалить агрегированную группу?',
	'tpt-aggregategroup-invalid-group' => 'Группа не существует.',
	'pt-parse-open' => 'Несбалансированный тег &lt;translate>.
Шаблон перевода: <pre>$1</pre>',
	'pt-parse-close' => 'Несбалансированный тег &lt;translate>.
Шаблон перевода: <pre>$1</pre>',
	'pt-parse-nested' => 'Недопустимы вложенные разделы &lt;translate>.
Текст тега: <pre>$1</pre>',
	'pt-shake-multiple' => 'Несколько маркеров раздела в одном разделе.
Текст раздела: <pre>$1</pre>',
	'pt-shake-position' => 'Неожиданное положение маркеров разделов.
Текст раздела: <pre>$1</pre>',
	'pt-shake-empty' => 'Пустой раздел для маркера «$1».',
	'log-description-pagetranslation' => 'Журнал для действий, связанных с системой перевода страниц',
	'log-name-pagetranslation' => 'Журнал перевода страниц',
	'logentry-pagetranslation-mark' => '$1 {{GENDER:$2|пометил|пометила}} страницу «$3» как доступную для перевода',
	'logentry-pagetranslation-unmark' => '$1 {{GENDER:$2|удалил|удалила}} страницу «$3» из списка доступных для перевода',
	'logentry-pagetranslation-moveok' => '$1 {{GENDER:$2|переименовал|переименовала}} доступную для перевода страницу «$3» в «$4»',
	'logentry-pagetranslation-movenok' => '$1 {{GENDER:$2|столкнулся|столкнулась}} с проблемой при переименовании страницы «$3» в «$4»',
	'logentry-pagetranslation-deletefok' => '$1 {{GENDER:$2|удалил|удалила}} доступную для перевода страницу «$3»',
	'logentry-pagetranslation-deletefnok' => '$1 {{GENDER:$2|не смог|не смогла}} удалить «$3», относящуюся к доступной для перевода странице «$4»',
	'logentry-pagetranslation-deletelok' => '$1 {{GENDER:$2|удалил|удалила}} страницу перевода «$3»',
	'logentry-pagetranslation-deletelnok' => '$1 {{GENDER:$2|не смог|не смогла}} удалить «$3», относящуюся к странице перевода «$4»',
	'logentry-pagetranslation-encourage' => '$1 {{GENDER:$2|проверил|проверила}} перевод страницы «$3»',
	'logentry-pagetranslation-discourage' => '$1 {{GENDER:$2|снял|сняла}} отметку проверки с перевода страницы «$3»',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1 {{GENDER:$2|удалил|удалила}} приоритетные языки с доступной для перевода страницы «$3»',
	'logentry-pagetranslation-prioritylanguages' => '$1 {{GENDER:$2|задал|задала}} для доступной для перевода страницы «$3» следующий список приоритетных языков: $5',
	'logentry-pagetranslation-prioritylanguages-force' => '$1 {{GENDER:$2|ограничил|ограничила}} для доступной для перевода страницы «$3» список языков: $5',
	'logentry-pagetranslation-associate' => '$1 {{GENDER:$2|добавил|добавила}} доступную для перевода страницу «$3» в агрегированную группу «$4»',
	'logentry-pagetranslation-dissociate' => '$1 {{GENDER:$2|удалил|удалила}} доступную для перевода страницу «$3» из агрегированной группы «$4»',
	'pt-movepage-title' => 'Переименование доступной для перевода страницы $1',
	'pt-movepage-blockers' => 'Страница с возможностью перевода не может быть переименована из-за {{PLURAL:$1|следующей ошибки|следующих ошибок}}:',
	'pt-movepage-block-base-exists' => 'Целевая страница «[[:$1]]» уже существует.',
	'pt-movepage-block-base-invalid' => 'Недопустимое название основной целевой страницы.',
	'pt-movepage-block-tp-exists' => 'Перевод целевой страницы [[:$2]] уже существует.',
	'pt-movepage-block-tp-invalid' => 'Название перевода целевой страницы [[:$1]] будет считаться недействительным (возможно, слишком длинное).',
	'pt-movepage-block-section-exists' => 'Раздел целевой страницы "[[:$2]]" уже существует.',
	'pt-movepage-block-section-invalid' => 'Название раздела целевой страницы "[[:$1]]" будет считаться недействительным (возможно, слишком длинным).',
	'pt-movepage-block-subpage-exists' => 'Целевая подстраница [[:$2]] уже существует.',
	'pt-movepage-block-subpage-invalid' => 'Название целевой подстраницы [[:$1]] будет считаться недействительным (возможно, слишком длинным).',
	'pt-movepage-list-pages' => 'Список страниц для переименования',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Страница|Страницы}} перевода',
	'pt-movepage-list-section' => 'Разделы {{PLURAL:$1|переводимой страницы|переводимых страниц}}',
	'pt-movepage-list-other' => '{{PLURAL:$1|Другая подстраница|Другие подстраницы}}',
	'pt-movepage-list-count' => 'Всего переименовать $1 {{PLURAL:$1|страницу|страницы|страниц}}.',
	'pt-movepage-legend' => 'Переименование переводимых страниц',
	'pt-movepage-current' => 'Текущее название:',
	'pt-movepage-new' => 'Новое название:',
	'pt-movepage-reason' => 'Причина:',
	'pt-movepage-subpages' => 'Переименовать все подстраницы',
	'pt-movepage-action-check' => 'Проверить, возможно ли переименование',
	'pt-movepage-action-perform' => 'Произвести переименование',
	'pt-movepage-action-other' => 'Изменить цель',
	'pt-movepage-intro' => 'Эта служебная страница позволяет переименовывать страницы, отмеченные для перевода.
Переименование не будет произведено одномоментно, так как требуется сменить название многим страницам.
Во время процесса переименования пропадает возможность взаимодействия с этими страницами.
Возникшие проблемы будут записаны в [[Special:Log/pagetranslation|журнал]], их нужно будет исправить вручную.',
	'pt-movepage-logreason' => 'Часть переводимой страницы $1.',
	'pt-movepage-started' => 'Основная страница переименована.
Пожалуйста, проверьте [[Special:Log/pagetranslation|журнал переводимых страниц]] на наличие ошибок.',
	'pt-locked-page' => 'Эта страница заблокирована, так как переводимая страница сейчас переименовывается.',
	'pt-deletepage-lang-title' => 'Удаление страницы перевода «$1».',
	'pt-deletepage-full-title' => 'Удаление доступной для перевода страницы $1.',
	'pt-deletepage-invalid-title' => 'Указана неверная страница.',
	'pt-deletepage-invalid-text' => 'Указанная страница не относится к числу доступных для перевода страниц или их переводов.',
	'pt-deletepage-action-check' => 'Список подлежащих удалению страниц',
	'pt-deletepage-action-perform' => 'Выполнить удаление',
	'pt-deletepage-action-other' => 'Изменить цель',
	'pt-deletepage-lang-legend' => 'Удалить страницу с переводом',
	'pt-deletepage-full-legend' => 'Удалить доступную для перевода страницу',
	'pt-deletepage-any-legend' => 'Удалить доступную для перевода страницу или её перевод',
	'pt-deletepage-current' => 'Название страницы:',
	'pt-deletepage-reason' => 'Причина',
	'pt-deletepage-subpages' => 'Удалить все подстраницы',
	'pt-deletepage-list-pages' => 'Список страниц для удаления',
	'pt-deletepage-list-translation' => 'Страницы перевода',
	'pt-deletepage-list-section' => 'Страницы разделов перевода',
	'pt-deletepage-list-other' => 'Другие подстраницы',
	'pt-deletepage-list-count' => 'В сумме $1 {{PLURAL:$1|страница|страниц}} для перевода.',
	'pt-deletepage-full-logreason' => 'Часть доступной для перевода страницы «$1».',
	'pt-deletepage-lang-logreason' => 'Часть страницы перевода «$1».',
	'pt-deletepage-started' => 'Пожалуйста, проверьте [[Special:Log/pagetranslation|журнал перевода страниц]] на предмет сообщений об ошибках и успешных завершениях.',
	'pt-deletepage-intro' => 'Это специальная страница позволяет вам удалить целую страницу, доступную для перевода, или переводы на определённый язык.
Действие по удалению не будет выполнено сразу же, так как в очереди на удаление будут все зависящие от них страницы.
Сбои будут отмечены в [[Special:Log/pagetranslation|журнале перевода страниц]] и должны быть устранены вручную.',
);

/** Rusyn (русиньскый)
 * @author Gazeb
 */
$messages['rue'] = array(
	'pagetranslation' => 'Переклад сторінок',
	'right-pagetranslation' => 'Означованя верзій сторінок про переклад',
	'tpt-desc' => 'Росшыріня про перекладаня сторінок з обсягом',
	'tpt-section' => 'Блок перекладу $1',
	'tpt-section-new' => 'Новый блок перекладу.
Назва: $1',
	'tpt-section-deleted' => 'Блок перекладу $1',
	'tpt-template' => 'Шаблона сторінкы',
	'tpt-templatediff' => 'Шаблона сторінкы зміненый.',
	'tpt-diff-old' => 'Попереднїй текст',
	'tpt-diff-new' => 'Новый текст',
	'tpt-submit' => 'Означіти тоту верзію про переклад',
	'tpt-sections-oldnew' => 'Новы і екзістуючі сторінкы перекладу',
	'tpt-sections-deleted' => 'Змазаны части сторінок',
	'tpt-sections-template' => 'Шаблона сторінкы перекладу',
	'tpt-nosuchpage' => 'Сторінка $1 не екзістує',
	'tpt-oldrevision' => '$2 не є найновша верзія сторінкы [[$1]].
Про переклад є можне означіти лем найновшы сторінкы.',
	'translate-tag-translate-link-desc' => 'Перекласти тоту сторінку',
	'translate-tag-markthis' => 'Означіти тоту сторінку про переклад',
	'tpt-languages-legend' => 'Іншы языкы:',
	'pt-movepage-new' => 'Нова назва:',
	'pt-movepage-reason' => 'Причіна:',
	'pt-movepage-subpages' => 'Переменовати вшыткы підсторінкы',
	'pt-movepage-action-other' => 'Змінити ціль',
);

/** Sanskrit (संस्कृतम्)
 * @author Ansumang
 */
$messages['sa'] = array(
	'pt-movepage-reason' => 'कारणम् :',
);

/** Sakha (саха тыла)
 * @author HalanTul
 */
$messages['sah'] = array(
	'pagetranslation' => 'Сирэйдэри тылбаастааһын',
	'right-pagetranslation' => 'Тылбаастанар сирэйдэр барылларын бэлиэтээһин',
	'tpt-desc' => 'Сирэй ис хоһоонун тылбаастыырга кэҥэтии',
	'tpt-section' => 'Тылбаас единицата $1',
	'tpt-section-new' => 'Тылбаас саҥа единицата.
Аата: $1',
	'tpt-section-deleted' => 'Тылбаас элэмиэнэ $1',
	'tpt-template' => 'Сирэй халыыба',
	'tpt-templatediff' => 'Бу сирэй халыыба уларытыллыбыт (уларытылынна).',
	'tpt-diff-old' => 'Бу иннинээҕи тиэкис',
	'tpt-diff-new' => 'Саҥа тиэкис',
	'tpt-submit' => 'Бу барылы тылбаастыырга бэлиэтээһин',
	'tpt-sections-oldnew' => 'Тылбаас саҥа уонна уруккуттан баар элэмиэннэрэ',
	'tpt-sections-deleted' => 'Тылбаас сотуллубут элэмиэннэрэ',
	'tpt-sections-template' => 'Тылбаас сирэйин халыыба',
	'tpt-badtitle' => 'Сирэй ыйыллыбыт аата ($1) аат буолар кыаҕа суох',
	'tpt-oldrevision' => '$2 [[$1]] сирэй бүтэһик барыла буолбатах.
Сирэйдэр бүтэһик эрэ барыллара тылбааска бэлиэтэниэхтэрин сөп.',
	'tpt-notsuitable' => '$1 сирэй тылбаастыырга табыгаһа суох.
<nowiki><translate></nowiki> тиэктээҕин уонна синтаксииһэ сөпкө суруллубутун бэрэбиэркэлээ.',
	'tpt-saveok' => '[[$1]] сирэй тылбаастанарга бэлиэтэммит, кини иһигэр {{PLURAL:$2|биир тылбаастаныахтаах этии|$2 тылбаастаныахтаах этии}} баар.
Билигин сирэйи <span class="plainlinks">[$3 тылбаастыахха]</span> сөп.',
	'tpt-badsect' => '"$1" диэн аат $2 тылбаас единицатын аатыгар сөп түбэспэт.',
	'tpt-showpage-intro' => 'Манна саҥа, билигин баар уонна сотуллубут тылбаастаныахтаах тыллар уонна этиилэр көстөллөр.
Бу барылы тылбаастаныахтаах курдук бэлиэтиэҥ иннинэ уларытыыҥ төһө кыалларынан аҕыйах буоларын ситиһэ сатаа, ол тылбаасчыттар үлэлэрин аҕыйатыа.',
	'tpt-mark-summary' => 'Бу барылы тылбастаныахтаах курдук бэлиэтииргэ',
	'tpt-edit-failed' => 'Бу сирэйи саҥардар табыллыбата: $1',
);

/** ꢱꣃꢬꢵꢯ꣄ꢡ꣄ꢬꢵ (ꢱꣃꢬꢵꢯ꣄ꢡ꣄ꢬꢵ)
 * @author MooRePrabu
 */
$messages['saz'] = array(
	'pt-movepage-current' => 'ꢂꢡ꣄ꢡꢵ ꢥꢵꢮ꣄',
	'pt-movepage-new' => 'ꢥꣁꢮ꣄ꢮꣁ ꢥꢵꢮ꣄',
);

/** Sicilian (sicilianu)
 * @author Aushulz
 */
$messages['scn'] = array(
	'pt-movepage-reason' => 'Mutivu:',
	'pt-deletepage-reason' => 'Mutivu:',
);

/** Sinhala (සිංහල)
 * @author චතුනි අලහප්පෙරුම
 * @author තඹරු විජේසේකර
 * @author පසිඳු කාවින්ද
 * @author බිඟුවා
 * @author ශ්වෙත
 */
$messages['si'] = array(
	'pagetranslation' => 'පිටුව පරිවර්තනය',
	'right-pagetranslation' => 'පරිවර්තනය සඳහා පිටුවල අනුවාද සලකුණු කරන්න',
	'action-pagetranslation' => 'පරිවර්තනය කල හැකි පිටු කළමනාකරණය කරන්න',
	'tpt-desc' => 'අන්තර්ගත පිටු පරිවර්තනය කිරීම සඳහා විස්තීරණය',
	'tpt-section' => '$1 පරිවර්තන ඒකකය',
	'tpt-section-new' => 'නව පරිවර්තන ඒකකය.
නම: $1',
	'tpt-section-deleted' => '$1 පරිවර්තන ඒකකය',
	'tpt-template' => 'පිටු සැකිල්ල',
	'tpt-templatediff' => 'පිටු සැකිල්ල වෙනස් වී ඇත',
	'tpt-diff-old' => 'පූර්ව පෙළ',
	'tpt-diff-new' => 'නව පෙළ',
	'tpt-submit' => 'මෙම අනුවාදය පරිවර්තනය සඳහා සලකුණු කරගන්න',
	'tpt-sections-oldnew' => 'නව හා දැනට පවත්නා පරිවර්තන ඒකක',
	'tpt-sections-deleted' => 'මකාදැමුණු පරිවර්තන ඒකක',
	'tpt-sections-template' => 'පරිවර්තන පිටුව සැකිල්ල',
	'tpt-action-nofuzzy' => 'පරිනර්තන අවලංගු නොකරන්න',
	'tpt-badtitle' => 'දී ඇති පිටු නාමය ($1) නීතික මාතෘකාවක් නොවේ',
	'tpt-nosuchpage' => '$1 පිටුව නොපවතියි',
	'tpt-oldrevision' => '$2 යනු [[$1]] පිටුවෙහි නවතම අනුවාදය නොවේ.
නවතම අනුවාදයන් පමණක් පරිවර්තනය සඳහා තෝරාගත හැක.',
	'tpt-notsuitable' => '$1 පිටුව පරිවර්තනය සඳහා සුදුසු නොවේ.
එය සතුව <nowiki><translate></nowiki> ටැගයන් පැවතීම සහ එය සතුව නීතික වින්‍යාසයක් ඇතිබව සහතික කරන්න.',
	'tpt-saveok' => '{{PLURAL:$2|එක් පරිවර්තන ඒකකයක්|පරිවර්තන ඒකක $2 ක්}} හා සමගින් පරිවර්තනය කෙරුමට [[$1]] පිටුව ‍සලකුණු කොට ඇත.
මෙම පිටුව දැන් <span class="plainlinks">[$3 පරිවර්තනය කල හැක]</span>.',
	'tpt-badsect' => '"$1" යනු $2 පරිවර්තන ඒකකය සඳහා නීතික මාතෘකාවක් නොවේ.',
	'tpt-mark-summary' => 'පරිවර්තනය සඳහා මෙම අනුවාදය සලකුණු කරන ලදී',
	'tpt-edit-failed' => 'පිටුව යාවත්කාලීන කල නොහැක: $1',
	'tpt-duplicate' => '$1 පරිවතන ඒකක නාමය එකකට වඩා භාවිතා කර ඇත.',
	'tpt-already-marked' => 'මෙම පිටුවෙහි නවතම අනුවාදය පරිවර්තනය සඳහා දැනටමත් සලකුණු කොට ඇත.',
	'tpt-unmarked' => '$1 පිටුව පරිවර්තනය සඳහා තවදුරටත් සලකුණු කර නොමැත.',
	'tpt-new-pages-title' => 'පරිවර්තනය සඳහා යෝජිත පිටු',
	'tpt-old-pages-title' => 'පරිවර්තනයේ ඇති පිටු',
	'tpt-other-pages-title' => 'බිඳුණු පිටු',
	'tpt-discouraged-pages-title' => 'අධෛර්යකල පිටු',
	'tpt-old-pages' => 'පරිවර්තනය සඳහා {{PLURAL:$1|මෙම පිටුවේ|මෙම පිටුවල}} සමහර අනුවාදයන් සලකුණු කරන ලදී.',
	'tpt-select-prioritylangs' => 'කොමාවෙන්-වෙන්වූ ප්‍රමුඛතා භාෂා කේතයන් ලැයිස්තුව:',
	'tpt-select-prioritylangs-force' => 'ප්‍රමුඛතා භාෂාවන්ට වඩා වෙන භාෂාවන්ට කරන පරිවර්තන වලක්වන්න',
	'tpt-select-prioritylangs-reason' => 'හේතුව:',
	'tpt-sections-prioritylangs' => 'ප්‍රමුඛතා භාෂාවන්',
	'tpt-rev-mark' => 'පරිවර්තනය සඳහා සලකුණු කරන්න',
	'tpt-rev-unmark' => 'පරිවර්තනයෙන් ඉවත් කරන්න',
	'tpt-rev-discourage' => 'අධෛර්ය කරන්න',
	'tpt-rev-encourage' => 'නැවත පිහිටුවන්න',
	'tpt-rev-mark-tooltip' => 'පරිවර්තනය සඳහා මෙම පිටුවෙහි නවතම අනුවාදය සලකුණු කරන්න.',
	'tpt-rev-unmark-tooltip' => 'මෙම පිටුව පරිවර්තනයෙන් ඉවත් කරන්න.',
	'tpt-rev-discourage-tooltip' => 'මෙම පිටුවෙහි ඉදිරි පරිවර්තනයන් අධෛර්ය කරන්න.',
	'tpt-rev-encourage-tooltip' => 'සාමාන්‍ය පරිවර්තනය වෙත මෙම පිටුව නැවත පිහිටුවන්න.',
	'translate-tag-translate-link-desc' => 'මෙම පිටුව පරිවර්තනය කරන්න',
	'translate-tag-markthis' => 'පරිවර්තනය සඳහා මෙම පිටුව සලකුණු කරන්න',
	'translate-tag-hasnew' => 'මෙම පිටුවේ අඩංගු වන <span class="plainlinks">[$1 වෙනස්කම්]</span> පරිවර්තනය සඳහා සලකුණු කොට නොමැත.',
	'tpt-languages-legend' => 'වෙනත් භාෂා:',
	'tpt-languages-zero' => 'මෙම භාෂාව සඳහා පරිවර්තනය අරඹන්න',
	'tpt-discouraged-language-reason' => 'හේතුව: $1',
	'tpt-render-summary' => 'මූලාශ්‍ර පිටුවේ නව අනුවාදය වෙත ගැලපීම සඳහා යාවත්කාලීන කරමින්',
	'tpt-download-page' => 'පරිවර්තනය සහිත පිටුව නිර්යාත කරන්න',
	'aggregategroups' => 'සමස්ත කාණ්ඩයන්',
	'tpt-aggregategroup-add' => 'එක් කරන්න',
	'tpt-aggregategroup-save' => 'සුරකින්න',
	'tpt-aggregategroup-add-new' => 'නව ඓක්‍යය කාණ්ඩයක් එක් කරන්න',
	'tpt-aggregategroup-new-name' => 'නම:',
	'tpt-aggregategroup-new-description' => 'විස්තරය (අමතර):',
	'tpt-aggregategroup-invalid-group' => 'කාණ්ඩය නොපවතියි',
	'pt-shake-empty' => '$1 ලකුණුකරණය සඳහා හිස් කාණ්ඩය.', # Fuzzy
	'log-name-pagetranslation' => 'පිටු පරිවර්තන ලඝු සටහන',
	'pt-movepage-title' => 'පරිවර්තනය කල හැකි $1 පිටුව ගෙනයන්න',
	'pt-movepage-block-base-exists' => 'ඉලක්කගත ආධාරක පිටුව [[:$1]] දැනටමත් පවතියි.', # Fuzzy
	'pt-movepage-block-base-invalid' => 'ඉලක්කගත ආධාරක පිටුව වලංගු මාතෘකාවක් නොවේ.', # Fuzzy
	'pt-movepage-block-tp-exists' => 'ඉලක්කගත පරිවර්තනමය පිටුව [[:$2]] දැනටමත් පවතියි.',
	'pt-movepage-block-section-exists' => 'ඉලක්කගත අංශ පිටුව [[:$2]] දැනටමත් පවතියි.', # Fuzzy
	'pt-movepage-block-subpage-exists' => 'ඉලක්කගත උපපිටුව [[:$2]] දැනටමත් පවතියි.',
	'pt-movepage-list-pages' => 'ගෙනයාමට ඇති පිටු ලැයිස්තුව',
	'pt-movepage-list-translation' => 'පරිවර්තන පිටු', # Fuzzy
	'pt-movepage-list-section' => 'කාණ්ඩ පිටු', # Fuzzy
	'pt-movepage-list-other' => 'වෙනත් උපපිටු', # Fuzzy
	'pt-movepage-list-count' => 'එකතුව වශයෙන් {{PLURAL:$1|පිටු|පිටු}} $1 ක් ගෙන යාමට ඇත.',
	'pt-movepage-legend' => 'පරිවර්තනය කල හැකි පිටුව ගෙනයන්න',
	'pt-movepage-current' => 'වත්මන් නාමය:',
	'pt-movepage-new' => 'නව නම:',
	'pt-movepage-reason' => 'හේතුව:',
	'pt-movepage-subpages' => 'සියලුම උපපිටු ගෙනයන්න',
	'pt-movepage-action-check' => 'ගෙනයාම කළහැකි දැයි පිරික්සන්න',
	'pt-movepage-action-perform' => 'ගෙනයන්න',
	'pt-movepage-action-other' => 'ඉලක්කය මාරු කරන්න',
	'pt-movepage-logreason' => '$1 පරිවර්තනය කල හැකි පිටුවෙහි කොටසක්.',
	'pt-movepage-started' => 'පදනම් පිටුව දැන් ගෙනයන ලදී.
සම්පූර්ණ පණිවුඩය සහ දෝෂයන් සඳහා කරුණාකර [[Special:Log/pagetranslation|පිටු පරිවර්තන ලඝු සටහන]] පිරික්සන්න.',
	'pt-locked-page' => 'මෙම පිටුව අගුළුදමා ඇත මන්ද පරිවර්තනමය පිටුව ගෙනයමින් පවතියි.',
	'pt-deletepage-lang-title' => '$1 පරිවර්තන පිටුව මකමින්.',
	'pt-deletepage-full-title' => '$1 පරිවර්තනය කල හැකි පිටුව මකමින්.',
	'pt-deletepage-invalid-title' => 'විශේෂණය කෙරූ පිටුව වලංගු නොවේ.',
	'pt-deletepage-invalid-text' => 'විශේෂණය කෙරූ පිටුව පරිවර්තනය කල නොහැකි හෝ පරිවර්තනමය නොවේ.', # Fuzzy
	'pt-deletepage-action-check' => 'මැකීමට ඇති පිටු ලැයිස්තුගත කරන්න',
	'pt-deletepage-action-perform' => 'මැකීම සිදු කරන්න',
	'pt-deletepage-action-other' => 'ඉලක්කය වෙනස් කරන්න',
	'pt-deletepage-lang-legend' => 'පරිවර්තන පිටුව මකමින්',
	'pt-deletepage-full-legend' => 'පරිවර්තනය කල හැකි පිටුව මකන්න',
	'pt-deletepage-any-legend' => 'පරිවර්තනය කළහැකි හෝ පරිවර්තනය කළහැකි පරිවර්තන පිටුවක් මකන්න', # Fuzzy
	'pt-deletepage-current' => 'පිටු නාමය:',
	'pt-deletepage-reason' => 'හේතුව:',
	'pt-deletepage-subpages' => 'සියලුම උපපිටු මකන්න',
	'pt-deletepage-list-pages' => 'මැකීමට ඇති පිටු ලැයිස්තුව',
	'pt-deletepage-list-translation' => 'පරිවර්තන පිටු',
	'pt-deletepage-list-section' => 'කාණ්ඩ පිටු', # Fuzzy
	'pt-deletepage-list-other' => 'වෙනත් උපපිටු',
	'pt-deletepage-list-count' => 'එකතුව වශයෙන් {{PLURAL:$1|පිටු|පිටු}} $1 ක් මැකීමට ඇත.',
	'pt-deletepage-full-logreason' => '$1 පරිවර්තනමය පිටුවෙහි කොටසක්.',
	'pt-deletepage-lang-logreason' => '$1 පරිවර්තන පිටුවෙහි කොටසක්.',
	'pt-deletepage-started' => 'සම්පූර්ණ පණිවුඩය සහ දෝෂයන් සඳහා කරුණාකර [[Special:Log/pagetranslation|පිටු පරිවර්තන ලඝු සටහන]] පිරික්සන්න.',
);

/** Slovak (slovenčina)
 * @author Helix84
 * @author Kusavica
 * @author Mormegil
 * @author Rudko
 * @author Teslaton
 */
$messages['sk'] = array(
	'pagetranslation' => 'Preklad stránky',
	'right-pagetranslation' => 'Označiť verzie stránok na preklad',
	'tpt-desc' => 'Rozšírenie na preklad stránok s obsahom',
	'tpt-section' => 'Jednotka prekladu $1',
	'tpt-section-new' => 'Nová jednotka prekladu.
Názov: $1',
	'tpt-section-deleted' => 'Jednotka prekladu $1',
	'tpt-template' => 'Šablóna stránky',
	'tpt-templatediff' => 'Šablóna stránky sa zmenila.',
	'tpt-diff-old' => 'Predošlý text',
	'tpt-diff-new' => 'Nový text',
	'tpt-submit' => 'Označiť túto verziu na preklad',
	'tpt-sections-oldnew' => 'Nové a existujúce jednotky prekladu',
	'tpt-sections-deleted' => 'Zmazané jednotky prekladu',
	'tpt-sections-template' => 'Šablóna stránky na preklad',
	'tpt-badtitle' => 'Zadaný názov stránky ($1) nie je platný',
	'tpt-nosuchpage' => 'Stránka $1 neexistuje',
	'tpt-oldrevision' => '$2 nie je najnovšia verzia stránky [[$1]].
Na preklad je možné označiť iba posledné verzie stránok.',
	'tpt-notsuitable' => 'Stránka $1 nie je vhodná na preklad.
Uistite sa, že obsahuje značky <nowiki><translate></nowiki> a má platnú syntax.',
	'tpt-saveok' => 'Stránka [[$1]] bola označená na preklad s $2 {{PLURAL:$2|jednotkou prekladu, ktorú|jednotkami prekladu, ktoré}} možno preložiť.
Túto stránku je teraz možné <span class="plainlinks">[$3 preložiť]</span>.',
	'tpt-badsect' => '„$1“ nie je platný názov jednotky prekladu $2.',
	'tpt-showpage-intro' => 'Dolu sú uvedené nové, súčasné a zmazané sekcie,
Predtým než túto verziu označíte na preklad skontrolujte, že zmeny sekcií sú minimálne aby ste zabránili zbytočnej práci prekladateľov.', # Fuzzy
	'tpt-mark-summary' => 'Táto verzia je označená na preklad',
	'tpt-edit-failed' => 'Nebolo možné aktualizovať stránku: $1',
	'tpt-already-marked' => 'Najnovšia verzia tejto stránky už bola označená na preklad.',
	'tpt-list-nopages' => 'Žiadne stránky nie sú označené na preklad alebo na to nie sú pripravené.',
	'tpt-new-pages' => '{{PLURAL:$1|Táto stránka obsahuje|Tieto stránky obsahujú}} text so značkami na preklad, ale žiadna verzia {{PLURAL:$1|tejto stránky|týchto stránok}} nie je označená na preklad.',
	'tpt-old-pages' => 'Niektoré verzie {{PLURAL:$1|tejto stránky|týchto stránok}} boli označené na preklad.',
	'translate-tag-translate-link-desc' => 'Preložiť túto stránku',
	'translate-tag-markthis' => 'Označiť túto stránku na preklad',
	'translate-tag-markthisagain' => 'Táto stránka obsahuje <span class="plainlinks">[$1 {{PLURAL:$1|zmenu|zmeny|zmien}}]</span> odkedy bola naposledy <span class="plainlinks">[$2 označená na preklad]</span>.',
	'translate-tag-hasnew' => 'Táto stránka obsahuje <span class="plainlinks">[$1 zmeny]</span>, ktoré nie sú označené na preklad.',
	'tpt-translation-intro' => 'Táto stránka je <span class="plainlinks">[$1 preloženou verziou]</span> stránky [[$2]] a preklad je hotový a aktuálny na $3 %.',
	'tpt-languages-legend' => 'Iné jazyky:',
	'tpt-target-page' => 'Túto stránku nemožno aktualizovať ručne.
Táto stránka je prekladom stránky [[$1]] a preklad možno aktualizovať pomocou [$2 nástroja na preklad].',
	'tpt-unknown-page' => 'Tento menný priestor je vyhradený na preklady stránok s obsahom.
Zdá sa, že stránka, ktorú sa pokúšate upravovať nezodpovedá žiadnej stránke označenej na preklad.',
	'tpt-render-summary' => 'Aktualizácia na novú verziu zdrojovej stránky',
	'tpt-download-page' => 'Exportovať stránky s prekladmi',
	'tpt-aggregategroup-save' => 'Uložiť',
);

/** Slovenian (slovenščina)
 * @author Dbc334
 * @author Eleassar
 * @author Irena Plahuta
 * @author Smihael
 */
$messages['sl'] = array(
	'pagetranslation' => 'Prevajanje strani',
	'right-pagetranslation' => 'Označi različice strani za prevajanje',
	'action-pagetranslation' => 'upravljanje prevedljivih strani',
	'tpt-desc' => 'Razširitev za prevajanje vsebine strani',
	'tpt-section' => 'Prevajalna enota $1',
	'tpt-section-new' => 'Nove prevajalna enota.
Ime: $1',
	'tpt-section-deleted' => 'Prevajalna enota $1',
	'tpt-template' => 'Predloga strani',
	'tpt-templatediff' => 'Predloga te strani se je spremenila.',
	'tpt-diff-old' => 'Prejšnje besedilo',
	'tpt-diff-new' => 'Novo besedilo',
	'tpt-submit' => 'Označi to različico za prevajanje',
	'tpt-sections-oldnew' => 'Nove in obstoječe prevajalske enote',
	'tpt-sections-deleted' => 'Izbrisane prevajalske enote',
	'tpt-sections-template' => 'Prevod predloge strani',
	'tpt-action-nofuzzy' => 'Ne označuj prevodov kot ohlapne',
	'tpt-badtitle' => 'Dano ime strani ($1) ni veljaven naslov',
	'tpt-nosuchpage' => 'Stran $1 ne obstaja',
	'tpt-oldrevision' => '$2 ni najnovejša različics strani [[$1]].
Samo zadnje različice se lahko označi za prevod.',
	'tpt-notsuitable' => 'Stran $1 ni primerna za prevod.
Prepričajte se, da ima oznake <nowiki><translate></nowiki> in veljavno sintakso.',
	'tpt-saveok' => 'Stran [[$1]] je bila označena za prevod z $2 {{PLURAL:$2|prevajalsko enoto|prevajalskima enotama|prevajalskimi enotami}}.
Stran je sedaj mogoče <span class="plainlinks">[$3 prevesti]</span>.',
	'tpt-badsect' => '»$1« ni veljavno ime za prevajalsko enoto $2.',
	'tpt-showpage-intro' => 'Spodaj so navedene nove, obstoječe in izbrisane prevajalne enote.
Pred označitvijo te redakcije za prevajanje preverite, da so spremembe prevajalnih enot čim manjše, saj tako prevajalcem prihranite nepotrebno delo.',
	'tpt-mark-summary' => 'Označil to različico za prevajanje',
	'tpt-edit-failed' => 'Ni mogoče posodobiti strani: $1',
	'tpt-duplicate' => 'Ime prevajalne enote $1 se uporablja več kot enkrat.',
	'tpt-already-marked' => 'Najnovejša različica te strani je že bila označena za prevajanje.',
	'tpt-unmarked' => 'Stran $1 ni več označena za prevajanje.',
	'tpt-list-nopages' => 'Nobena stran ni označena za prevajanje, niti pripravljena, da se označi za prevajanje.',
	'tpt-new-pages-title' => 'Strani, predlagane za prevajanje',
	'tpt-old-pages-title' => 'Strani v prevajanju',
	'tpt-other-pages-title' => 'Poškodovane strani',
	'tpt-discouraged-pages-title' => 'Zatrte strani',
	'tpt-new-pages' => '{{PLURAL:$1|Ta stran vsebuje|Ti strani vsebujeta|Te strani vsebujejo}} besedilo z oznakami za prevajanje,
vendar trenutno ni nobena različica {{PLURAL:$1|te strani|teh strani}} označena za prevajanje.',
	'tpt-old-pages' => 'Nekatere različice {{PLURAL:$1|te strani|teh strani}} so bile označene za prevajanje.',
	'tpt-other-pages' => '{{PLURAL:$1|Stara različica te strani je bila označena|Stari različici teh strani sta bili označeni|Stare različice teh strani so bile označene}} za prevajanje,
vendar {{PLURAL:$1|trenutne različice|trenutnih različic}} ni mogoče označiti za prevajanje.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Naslednja stran je zatrta|Naslednji strani sta zatrti|Naslednje strani so zatrte}} pred nadaljnjimi prevodi.',
	'tpt-select-prioritylangs' => 'Z vejico ločen seznam kod prednostnih jezikov:',
	'tpt-select-prioritylangs-force' => 'Prepreči prevajanje v jezike, ki niso prednostni jeziki',
	'tpt-select-prioritylangs-reason' => 'Razlog:',
	'tpt-sections-prioritylangs' => 'Prednostni jeziki',
	'tpt-rev-mark' => 'Označi za prevajanje',
	'tpt-rev-unmark' => 'odstrani iz prevoda',
	'tpt-rev-discourage' => 'zatri',
	'tpt-rev-encourage' => 'obnovi',
	'tpt-rev-mark-tooltip' => 'Označi zadnjo različico strani za prevajanje.',
	'tpt-rev-unmark-tooltip' => 'Odstranite stran iz prevajanja.',
	'tpt-rev-discourage-tooltip' => 'Zatri nadaljnje prevode strani.',
	'tpt-rev-encourage-tooltip' => 'Obnovite stran na običajni prevod.',
	'translate-tag-translate-link-desc' => 'Prevedi to stran',
	'translate-tag-markthis' => 'Označi to stran za prevajanje',
	'translate-tag-markthisagain' => 'Ta stran ima <span class="plainlinks">[$1 sprememb]</span> odkar je bila nazadnje <span class="plainlinks">[$2 označena za prevajanje]</span>.',
	'translate-tag-hasnew' => 'Stran vsebuje <span class="plainlinks">[$1 spremembe]</span>, ki niso označene za prevajanje.',
	'tpt-translation-intro' => 'Ta stran je <span class="plainlinks">[$1 prevedena različica]</span> strani [[$2]] in prevod je $3 % dokončan.',
	'tpt-languages-legend' => 'Drugi jeziki:',
	'tpt-languages-zero' => 'Prični s prevajanjem v ta jezik',
	'tpt-target-page' => 'Te strani ni mogoče ročno posodobiti.
Ta stran je prevod strani [[$1]], njen prevod lahko posodobite z uporabo [$2 prevajalskega orodja].',
	'tpt-unknown-page' => 'Ta imenski prostor je pridržan za prevode vsebinskih strani.
Stran, ki jo poskušate urediti, ne ustreza nobeni strani označeni za prevajanje.',
	'tpt-discouraged-language-reason' => 'Razlog: $1',
	'tpt-render-summary' => 'Posodabljanje za ujemanje nove različice izvorne strani',
	'tpt-download-page' => 'Izvozi stran s prevodi',
	'tpt-aggregategroup-add' => 'Dodaj',
	'tpt-aggregategroup-save' => 'Shrani',
	'tpt-aggregategroup-new-name' => 'Ime:',
	'tpt-aggregategroup-new-description' => 'Opis (izbirno):',
	'tpt-aggregategroup-invalid-group' => 'Skupina ne obstaja',
	'pt-parse-open' => 'Neizenačena etiketa &lt;translate>.
Prevajalna predloga: <pre>$1</pre>',
	'pt-parse-close' => 'Neizenačena etiketa &lt;/translate>.
Prevajalna predloga: <pre>$1</pre>',
	'pt-parse-nested' => 'Gnezdene prevajalne enote &lt;translate> niso dovoljene.
Besedilo etikete: <pre>$1</pre>',
	'pt-shake-multiple' => 'Več označevalcev prevajalnih enot za eno prevajalno enoto.
Besedilo prevajalne enote: <pre>$1</pre>',
	'pt-shake-position' => 'Označevalci prevajalnih enot na nepričakovanem položaju.
Besedilo prevajalne enote: <pre>$1</pre>',
	'pt-shake-empty' => 'Prazna prevajalna enota označevalec »$1«.',
	'log-description-pagetranslation' => 'Dnevnik dejanj, ki so povezana s sistemom prevajanja strani',
	'log-name-pagetranslation' => 'Dnevnik prevajanja strani',
	'pt-movepage-title' => 'Premakni prevedljivo stran $1',
	'pt-movepage-blockers' => 'Prevedljive strani ni mogoče prestaviti na novo ime zaradi {{PLURAL:$1|naslednje napake|naslednjih napak}}:',
	'pt-movepage-block-base-exists' => 'Ciljna prevedljiva stran »[[:$1]]« obstaja.',
	'pt-movepage-block-base-invalid' => 'Ime ciljne prevedljive strani ni veljaven naslov.',
	'pt-movepage-block-tp-exists' => 'Ciljna stran s prevodom [[:$2]] obstaja.',
	'pt-movepage-block-tp-invalid' => 'Naslov ciljne strani s prevodom za [[:$1]] bi bil neveljaven (predolg?).',
	'pt-movepage-block-section-exists' => 'Ciljna stran prevajalne enote »[[:$2]]« obstaja.',
	'pt-movepage-block-section-invalid' => 'Naslov ciljne strani »[[:$1]]« za prevajalno enoto bi bil neveljaven (predolg?).',
	'pt-movepage-block-subpage-exists' => 'Ciljna podstran [[:$2]] obstaja.',
	'pt-movepage-block-subpage-invalid' => 'Naslov ciljne podstrani [[:$1]] bi bil neveljaven (predolg?).',
	'pt-movepage-list-pages' => 'Seznam strani za prestavitev',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Stran|Strani}} s prevodi',
	'pt-movepage-list-section' => '{{PLURAL:$1|Stran|Strani}} prevajalnih enot',
	'pt-movepage-list-other' => '{{PLURAL:$1|Ostala podstran|Ostali podstrani|Ostale podstrani}}',
	'pt-movepage-list-count' => 'Skupno je za prestaviti $1 {{PLURAL:$1|stran|strani}}.',
	'pt-movepage-legend' => 'Premakni prevedljivo stran',
	'pt-movepage-current' => 'Trenutno ime:',
	'pt-movepage-new' => 'Novo ime:',
	'pt-movepage-reason' => 'Razlog:',
	'pt-movepage-subpages' => 'Prestavi vse podstrani',
	'pt-movepage-action-check' => 'Preveri, če je prestavitev mogoča',
	'pt-movepage-action-perform' => 'Izvedi prestavitev',
	'pt-movepage-action-other' => 'Spremeni cilj',
	'pt-movepage-intro' => 'Ta posebna stran omogoča prestavljanje strani, ki so označene za prevajanje.
Dejanje prestavitve ne bo izvedeno takoj, saj bo potrebno prestaviti veliko strani.
Medtem ko se strani premikajo, ne bo mogoče delovati na straneh v obravnavi.
Neuspehi bodo zabeleženi v [[Special:Log/pagetranslation|dnevniku strani prevodov]] in jih je potrebno ročno popraviti.',
	'pt-movepage-logreason' => 'Del prevedljive strani $1.',
	'pt-movepage-started' => 'Izhodna stran je prestavljena.
Prosimo, preverite [[Special:Log/pagetranslation|dnevnik strani prevodov]] za napake in sporočila o dokončanju.',
	'pt-locked-page' => 'Stran je zaklenjena, ker se prevedljiva stran trenutno prestavlja.',
	'pt-deletepage-lang-title' => 'Brisanje strani pravoda $1.',
	'pt-deletepage-full-title' => 'Brisanje prevedljive strani $1.',
	'pt-deletepage-invalid-title' => 'Določena stran ni veljavna.',
	'pt-deletepage-invalid-text' => 'Izbrana stran ni niti prevedljiva stran niti stran s prevodom.',
	'pt-deletepage-action-check' => 'Navedi strani za izbris',
	'pt-deletepage-action-perform' => 'Izvedi izbris',
	'pt-deletepage-action-other' => 'Spremeni cilj',
	'pt-deletepage-lang-legend' => 'Izbriši stran prevoda',
	'pt-deletepage-full-legend' => 'Izbriši prevedljivo stran',
	'pt-deletepage-any-legend' => 'Izbriši prevedljivo stran ali prevod strani',
	'pt-deletepage-current' => 'Naslov strani:',
	'pt-deletepage-reason' => 'Razlog:',
	'pt-deletepage-subpages' => 'Izbriši vse podstrani',
	'pt-deletepage-list-pages' => 'Seznam strani za izbris',
	'pt-deletepage-list-translation' => 'Strani prevodov',
	'pt-deletepage-list-section' => 'Strani prevajalnih enot',
	'pt-deletepage-list-other' => 'Druge podstrani',
	'pt-deletepage-list-count' => 'Skupno je za izbrisati $1 {{PLURAL:$1|stran|strani}}.',
	'pt-deletepage-full-logreason' => 'Del prevedljive strani $1.',
	'pt-deletepage-lang-logreason' => 'Del strani prevoda $1.',
	'pt-deletepage-started' => 'Prosimo, preverite [[Special:Log/pagetranslation|dnevnik prevajanja strani]] za napake in sporočilo o dokončanju.',
	'pt-deletepage-intro' => 'Ta posebna stran vam omogoča izbris celotne prevedljive strani ali individualne strani s prevodom v nekem jeziku.
Dejanje izbrisa ne bo izvedeno takoj, ker je treba izbrisati tudi vse strani, ki so od njih odvisne.
Neuspehi bodo zabeleženi v [[Special:Log/pagetranslation|dnevniku prevajanja strani]] in jih morate urediti ročno.',
);

/** Somali (Soomaaliga)
 * @author Abshirdheere
 */
$messages['so'] = array(
	'tpt-discouraged-language' => "'''Turjumidda $2 Maaha muhiimadda koowaad ee Boggaan.'''

Maamulka waxa uu muhiimad uu siiyey turjumidda $3.",
);

/** Serbian (Cyrillic script) (српски (ћирилица)‎)
 * @author Rancher
 * @author Милан Јелисавчић
 * @author Михајло Анђелковић
 */
$messages['sr-ec'] = array(
	'pagetranslation' => 'Превод странице',
	'right-pagetranslation' => 'означавање издања страница за превод',
	'tpt-section' => 'Преводилачка јединица $1',
	'tpt-section-new' => 'Нова преводилачка јединица.
Назив: $1',
	'tpt-section-deleted' => 'Преводилачка јединица $1',
	'tpt-template' => 'Шаблон странице',
	'tpt-templatediff' => 'Шаблон странице је измењен.',
	'tpt-diff-old' => 'Претходни текст',
	'tpt-diff-new' => 'Следећи текст',
	'tpt-submit' => 'Означи ову верзију за превод',
	'tpt-sections-oldnew' => 'Нове и постојеће преводилачке јединице',
	'tpt-sections-deleted' => 'Обрисане преводилачке јединице',
	'tpt-sections-template' => 'Шаблон странице за превођење',
	'tpt-action-nofuzzy' => 'Не поништавајте преводе',
	'tpt-nosuchpage' => 'Страница $1 не постоји',
	'tpt-new-pages-title' => 'Предложене странице за превођење',
	'tpt-select-prioritylangs-reason' => 'Разлог:',
	'tpt-sections-prioritylangs' => 'Приоритетни језици',
	'tpt-rev-mark' => 'означи за превођење',
	'tpt-rev-unmark' => 'уклони из превода',
	'tpt-rev-discourage' => 'непрепоручено',
	'tpt-rev-encourage' => 'врати',
	'tpt-rev-mark-tooltip' => 'Означи последњу верзију странице као „за превођење“.',
	'tpt-rev-unmark-tooltip' => 'Уклони страницу из превода.',
	'tpt-rev-discourage-tooltip' => 'Постави страницу као непрепоручену за даљње превођење.',
	'tpt-rev-encourage-tooltip' => 'Врати страницу за нормално превођење.',
	'translate-tag-translate-link-desc' => 'Преведите ову страницу',
	'translate-tag-markthis' => 'Означи страницу као „за превођење“',
	'tpt-translation-intro' => 'Ово је <span class="plainlinks">[$1 преведена верзија]</span> странице [[$2]]. Превод је $3% завршен.',
	'tpt-languages-legend' => 'Остали језици:',
	'tpt-languages-separator' => '&#160;•&#160;',
	'tpt-languages-zero' => 'Почните превођења на овом језику',
	'tpt-discouraged-language-reason' => 'Разлог: $1',
	'tpt-aggregategroup-add' => 'Додај',
	'tpt-aggregategroup-save' => 'Сачувај',
	'tpt-aggregategroup-new-name' => 'Назив:',
	'tpt-aggregategroup-new-description' => 'Опис (необавезно):',
	'tpt-aggregategroup-invalid-group' => 'Група не постоји',
	'log-name-pagetranslation' => 'Историја превода страница',
	'pt-movepage-title' => 'Премештање преводиве странице $1',
	'pt-movepage-block-base-exists' => 'Циљна основна страница [[:$1]] постоји.', # Fuzzy
	'pt-movepage-block-base-invalid' => 'Циљ основне странице не представља исправан наслов.', # Fuzzy
	'pt-movepage-block-tp-exists' => 'Циљна страница за превод [[:$2]] постоји.',
	'pt-movepage-block-tp-invalid' => 'Наслов циљне странице за превод за [[:$1]] био би неисправан (предугачак?).',
	'pt-movepage-block-section-exists' => 'Циљна страница за поднаслов [[:$2]] постоји.', # Fuzzy
	'pt-movepage-block-section-invalid' => 'Наслов циљне странице за поднаслов за [[:$1]] био би неисправан (предугачак?).', # Fuzzy
	'pt-movepage-block-subpage-exists' => 'Циљна подстраница [[:$2]] постоји.',
	'pt-movepage-block-subpage-invalid' => 'Наслов циљне подстранице за [[:$1]] био би неисправан (предугачак?).',
	'pt-movepage-list-pages' => 'Списак страница за премештање',
	'pt-movepage-list-translation' => 'Странице за превод', # Fuzzy
	'pt-movepage-list-section' => 'Странице за поднаслове', # Fuzzy
	'pt-movepage-list-other' => 'Друге подстранице', # Fuzzy
	'pt-movepage-list-count' => 'Укупно $1 {{PLURAL:$1|страница|странице|страница}} за премештање.',
	'pt-movepage-legend' => 'Премести преводиву страницу',
	'pt-movepage-current' => 'Текући назив:',
	'pt-movepage-new' => 'Нови назив:',
	'pt-movepage-reason' => 'Разлог:',
	'pt-movepage-subpages' => 'Премести све подстранице',
	'pt-movepage-action-check' => 'Провери да ли је премештање изводљиво',
	'pt-movepage-action-perform' => 'Премести',
	'pt-movepage-action-other' => 'Промени циљ',
	'pt-deletepage-action-check' => 'Наведи странице за брисање',
	'pt-deletepage-action-perform' => 'Изврши брисање',
	'pt-deletepage-action-other' => 'Промени циљ',
	'pt-deletepage-lang-legend' => 'Обриши страницу превода',
	'pt-deletepage-full-legend' => 'Обриши преводиву страницу',
	'pt-deletepage-current' => 'Назив странице:',
	'pt-deletepage-reason' => 'Разлог:',
	'pt-deletepage-subpages' => 'Обриши све подстранице',
	'pt-deletepage-list-pages' => 'Списак страница за брисање',
	'pt-deletepage-list-translation' => 'Странице за превођење',
	'pt-deletepage-list-other' => 'Остале подстранице',
	'pt-deletepage-list-count' => 'Укупно $1 {{PLURAL:$1|страница|странице|страница}} за брисање.', # Fuzzy
);

/** Serbian (Latin script) (srpski (latinica)‎)
 * @author Michaello
 * @author Rancher
 */
$messages['sr-el'] = array(
	'right-pagetranslation' => 'označavanje izdanja stranica za prevod',
	'tpt-diff-old' => 'Prethodni tekst',
	'tpt-diff-new' => 'Sledeći tekst',
	'tpt-submit' => 'Označi ovu verziju za prevod',
	'tpt-rev-mark' => 'označi za prevođenje',
	'tpt-rev-unmark' => 'ukloni iz prevoda',
	'tpt-rev-discourage' => 'nepreporučeno',
	'tpt-rev-encourage' => 'vrati',
	'tpt-rev-mark-tooltip' => 'Označi poslednju verziju stranice kao „za prevođenje“.',
	'tpt-rev-unmark-tooltip' => 'Ukloni stranicu iz prevoda.',
	'tpt-rev-discourage-tooltip' => 'Postavi stranicu kao nepreporučenu za daljnje prevođenje.',
	'tpt-rev-encourage-tooltip' => 'Vrati stranicu za normalno prevođenje.',
	'translate-tag-translate-link-desc' => 'Prevedite ovu stranu',
	'translate-tag-markthis' => 'Označi stranicu kao „za prevođenje“',
	'tpt-translation-intro' => 'Ova stranica je <span class="plainlinks">[$1 prevedeno izdanje]</span> stranice [[$2]]. Prevod je $3% završen.',
	'tpt-languages-separator' => '&#160;•&#160;',
	'log-name-pagetranslation' => 'Istorija prevoda stranice',
	'pt-movepage-title' => 'Premeštanje prevodive stranice $1',
	'pt-movepage-block-base-exists' => 'Ciljna osnovna stranica [[:$1]] postoji.', # Fuzzy
	'pt-movepage-block-base-invalid' => 'Cilj osnovne stranice ne predstavlja ispravan naslov.', # Fuzzy
	'pt-movepage-block-tp-exists' => 'Ciljna stranica za prevod [[:$2]] postoji.',
	'pt-movepage-block-tp-invalid' => 'Naslov ciljne stranice za prevod za [[:$1]] bio bi neispravan (predugačak?).',
	'pt-movepage-block-subpage-exists' => 'Ciljna podstranica [[:$2]] postoji.',
	'pt-movepage-block-subpage-invalid' => 'Naslov ciljne podstranice za [[:$1]] bio bi neispravan (predugačak?).',
	'pt-movepage-list-pages' => 'Spisak stranica za premeštanje',
	'pt-movepage-list-translation' => 'Stranice za prevod', # Fuzzy
	'pt-movepage-list-other' => 'Druge podstranice', # Fuzzy
	'pt-movepage-list-count' => 'Ukupno $1 {{PLURAL:$1|stranica|stranice|stranica}} za premeštanje.', # Fuzzy
	'pt-movepage-legend' => 'Premesti prevodivu stranicu',
	'pt-movepage-current' => 'Tekući naziv:',
	'pt-movepage-new' => 'Novi naziv:',
	'pt-movepage-reason' => 'Razlog:',
	'pt-movepage-subpages' => 'Premesti sve podstranice',
	'pt-movepage-action-check' => 'Proveri da li je premeštanje izvodljivo',
	'pt-movepage-action-perform' => 'Premesti',
	'pt-movepage-action-other' => 'Promeni cilj',
);

/** Seeltersk (Seeltersk)
 * @author Pyt
 */
$messages['stq'] = array(
	'translate-tag-translate-link-desc' => 'Disse Siede uursätte',
);

/** Sundanese (Basa Sunda)
 * @author Kandar
 */
$messages['su'] = array(
	'pagetranslation' => 'Alihbasa kaca',
	'tpt-diff-old' => 'Téks saméméhna',
	'tpt-diff-new' => 'Téks anyar',
	'tpt-nosuchpage' => 'Kaca $1 euweuh.',
	'pt-movepage-current' => 'Ngaran ayeuna:',
	'pt-movepage-new' => 'Ngaran anyar:',
	'pt-movepage-reason' => 'Alesan:',
	'pt-movepage-subpages' => 'Pindahkeun sakabéh subkaca',
	'pt-movepage-action-check' => 'Pariksa susuganan bisa dipindahkeun',
	'pt-movepage-action-perform' => 'Pindahkeun',
	'pt-movepage-action-other' => 'Ganti tujul',
);

/** Swedish (svenska)
 * @author Dafer45
 * @author Fluff
 * @author Jopparn
 * @author M.M.S.
 * @author Najami
 * @author Rotsee
 * @author WikiPhoenix
 */
$messages['sv'] = array(
	'pagetranslation' => 'Sidöversättning',
	'right-pagetranslation' => 'Märk versioner av sidor för översättning',
	'action-pagetranslation' => 'hantera översättningsbara sidor',
	'tpt-desc' => 'Programtillägg för översättning av innehållssidor',
	'tpt-section' => 'Översättningsenhet $1',
	'tpt-section-new' => 'Ny översättningsenhet. Namn: $1',
	'tpt-section-deleted' => 'Översättningsenhet $1',
	'tpt-template' => 'Sidmall',
	'tpt-templatediff' => 'Sidmallen har ändrats.',
	'tpt-diff-old' => 'Föregående text',
	'tpt-diff-new' => 'Ny text',
	'tpt-submit' => 'Märk den här versionen för översättning',
	'tpt-sections-oldnew' => 'Nya och existerande översättningsenheter',
	'tpt-sections-deleted' => 'Raderade översättningsenheter',
	'tpt-sections-template' => 'Mall för översättningssida',
	'tpt-action-nofuzzy' => 'Ogiltigförklara inte översättningar',
	'tpt-badtitle' => 'Det angivna sidnammet ($1) är inte en giltlig titel',
	'tpt-nosuchpage' => 'Sidan $1 finns inte',
	'tpt-oldrevision' => '$2 är inte den senaste versionen av sidan [[$1]].
Endast den senaste versionen kan märkas för översättning.',
	'tpt-notsuitable' => 'Sidan $1 är inte redo för översättning.
Se till att sidan har <nowiki><translate></nowiki>-taggar och att syntaxen är giltlig.',
	'tpt-saveok' => 'Sidan [[$1]] har märkts för översättning med {{PLURAL:$2|en översättning|$2 översättningar}}. Sidan kan nu <span class="plainlinks">[$3 översättas]</span>.',
	'tpt-badsect' => '"$1" är inte ett giltligt namn för översättningen $2.',
	'tpt-showpage-intro' => 'Nedanför finns nya, existerande och raderade översättningsenheter uppradade.
Innan den här versionen märks för översättning, kontrollera att ändringarna i översättningsenheterna är minimala för att undvika extra arbete för översättarna.',
	'tpt-mark-summary' => 'Den här versionen är märkt för översättning',
	'tpt-edit-failed' => 'Sidan "$1" kunde inte uppdateras.',
	'tpt-duplicate' => 'Översättningsenhetsnamnet $1 används mer än en gång.',
	'tpt-already-marked' => 'Den senaste versionen av den här sidan har redan märkts för översättning.',
	'tpt-unmarked' => 'Sidan $1 är inte längre markerad för översättning.',
	'tpt-list-nopages' => 'Det finns inga sidor som är märkta för översättning eller är klara att märkas för översättning.',
	'tpt-new-pages-title' => 'Sidor föreslagna för översättning',
	'tpt-old-pages-title' => 'Sidor i översättning',
	'tpt-other-pages-title' => 'Trasiga sidor',
	'tpt-discouraged-pages-title' => 'Förhindrade sidor',
	'tpt-new-pages' => '{{PLURAL:$1|Den här sidan|De här sidorna}} innehåller text med översättningstaggar, men ingen version av {{PLURAL:$1|den här sidan|de här sidorna}} är märkt för översättning.',
	'tpt-old-pages' => 'En version av {{PLURAL:$1|den här sidan|de här sidorna}} har märkts för översättning.',
	'tpt-other-pages' => '{{PLURAL:$1|En gammal version av den här sidan är markerad|Äldre versioner av dessa sidor är markerade}} för översättning,
men {{PLURAL:$1|den senaste versionen|de senaste versionerna}} kan inte markeras för översättning.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Denna sida|Dessa sidor}} har förhindrats från vidare översättning.',
	'tpt-select-prioritylangs' => 'Kommaseparerad lista över prioriterade språkkoder:',
	'tpt-select-prioritylangs-force' => 'Förhindra översättningar på andra språk än de prioriterade språken',
	'tpt-select-prioritylangs-reason' => 'Anledning:',
	'tpt-sections-prioritylangs' => 'Prioriterade språk',
	'tpt-rev-mark' => 'markera för översättning',
	'tpt-rev-unmark' => 'ta bort från översättning',
	'tpt-rev-discourage' => 'förhindra',
	'tpt-rev-encourage' => 'återställ',
	'tpt-rev-mark-tooltip' => 'Markera den senaste versionen av denna sida för översättning.',
	'tpt-rev-unmark-tooltip' => 'Ta bort denna sida från översättning.',
	'tpt-rev-discourage-tooltip' => 'Förhindra vidare översättning på denna sida.',
	'tpt-rev-encourage-tooltip' => 'Återställ denna sida till vanlig översättning.',
	'translate-tag-translate-link-desc' => 'Översätt den här sidan',
	'translate-tag-markthis' => 'Märk den här sidan för översättning',
	'translate-tag-markthisagain' => 'Den här sidan har <span class="plainlinks">[$1 förändringar]</span> sedan den senast <span class="plainlinks">[$2 märktes för översättning]</span>.',
	'translate-tag-hasnew' => 'Den här sidan innehåller <span class="plainlinks">[$1 förändringar]</span> som inte är märkta för översättning.',
	'tpt-translation-intro' => 'Det här är en <span class="plainlinks">[$1 översatt version]</span> av sidan [[$2]]. Översättningen är till $3% färdig och uppdaterad.',
	'tpt-languages-legend' => 'Andra språk:',
	'tpt-languages-zero' => 'Starta översättning för detta språk',
	'tpt-target-page' => 'Den här sidan kan inte uppdateras manuellt. Den här sidan är en översättning av [[$1]] och översättningen kan uppdateras genom att använda [$2 översättningsverktyget].',
	'tpt-unknown-page' => 'Den här namnrymden är reserverad för översättningar av sidor. Sidan du försöker redigera verkar inte stämma överens med någon sida som är märkt för översättning.',
	'tpt-translation-restricted' => 'Översättningar av denna sida har förhindrats av en översättningsadministratör.

Anledningar: $1',
	'tpt-discouraged-language-force' => 'En översättningsadministratör har begränsat språken denna sida kan översättas till. Detta språk är inte en av dessa språk.

Anledning: $1',
	'tpt-discouraged-language' => 'Detta språk är inte bland de prioritetsspråk som är inställda av en översättningsadminstratör för denna sida.

Anledning: $1',
	'tpt-discouraged-language-reason' => 'Anledning: $1',
	'tpt-priority-languages' => 'En översättningsadministratör har ställt in prioritetsspråken för denna grupp till $1.',
	'tpt-render-summary' => 'Uppdaterar för att matcha den nya versionen av källpaketet',
	'tpt-download-page' => 'Exportera sidan med översättningar',
	'aggregategroups' => 'Samla grupper',
	'tpt-aggregategroup-add' => 'Lägg till',
	'tpt-aggregategroup-save' => 'Spara',
	'tpt-aggregategroup-add-new' => 'Lägg till en ny samlad grupp',
	'tpt-aggregategroup-new-name' => 'Namn:',
	'tpt-aggregategroup-new-description' => 'Beskrivning (valfri):',
	'tpt-aggregategroup-remove-confirm' => 'Är du säker på att du vill radera denna samlingsgrupp?',
	'tpt-aggregategroup-invalid-group' => 'Gruppen finns inte',
	'pt-parse-open' => 'Obalanserad &lt;translate>-tagg.
Översättningsmall: <pre>$1</pre>',
	'pt-parse-close' => 'Obalanserad &lt;/translate>-tagg.
Översättningsmall: <pre>$1</pre>',
	'pt-shake-empty' => 'Tom översättningsenhet för markör $1.',
	'log-name-pagetranslation' => 'Sidöversättningslogg',
	'logentry-pagetranslation-mark' => '$1 {{GENDER:$2|markerade}} $3 för översättning',
	'logentry-pagetranslation-unmark' => '$1 {{GENDER:$2|tog bort}} $3 för översättning',
	'logentry-pagetranslation-moveok' => '$1 {{GENDER:$2|slutförde}} namnändringen av översättningssidan $3 till $4',
	'logentry-pagetranslation-movenok' => '$1 {{GENDER:$2|stöte på}} ett problem när sidan $3 skulle flyttas till $4',
	'logentry-pagetranslation-deletefok' => '$1 {{GENDER:$2|slutförde}} raderingen av översättningssidan $3',
	'logentry-pagetranslation-deletefnok' => '$1 {{GENDER:$2|misslyckades}} att radera $3 som tillhör översättningssidan $4',
	'logentry-pagetranslation-deletelok' => '$1 {{GENDER:$2|slutförde}} radering av översättningssidan $3',
	'pt-movepage-title' => 'Flytta översättningsbar sida $1',
	'pt-movepage-blockers' => 'Den översättningsbara sidan kan inte flyttas till ett nytt namn på grund av följande {{PLURAL:$1|fel|fel}}:',
	'pt-movepage-list-pages' => 'Lista över sidor att flytta',
	'pt-movepage-list-translation' => 'Översättnings{{PLURAL:$1|sida|sidor}}',
	'pt-movepage-list-section' => 'Översättningsenhets{{PLURAL:$1|sida|sidor}}',
	'pt-movepage-list-other' => '{{PLURAL:$1|Annan undersida|Andra undersidor}}',
	'pt-movepage-list-count' => 'Totalt $1 {{PLURAL:$1|sida|sidor}} att flytta.',
	'pt-movepage-legend' => 'Flytta översättningsbar sida',
	'pt-movepage-current' => 'Nuvarande namn:',
	'pt-movepage-new' => 'Nytt namn:',
	'pt-movepage-reason' => 'Orsak:',
	'pt-movepage-subpages' => 'Flytta alla undersidor',
	'pt-movepage-action-check' => 'Kontrollera om flytten är möjligt',
	'pt-movepage-action-perform' => 'Genomför flytten',
	'pt-movepage-action-other' => 'Ändra mål',
	'pt-movepage-logreason' => 'Del av översättningsbar sida $1.',
	'pt-locked-page' => 'Denna sida är låst eftersom den översättningsbara sidan håller på att flyttas.',
	'pt-deletepage-lang-title' => 'Raderar översättningssida $1.',
	'pt-deletepage-full-title' => 'Raderar översättningsbar sida $1.',
	'pt-deletepage-invalid-title' => 'Den angivna sidan är inte giltig.',
	'pt-deletepage-invalid-text' => 'Den angivna sidan är varken en översättbar sida eller en översättningssida.',
	'pt-deletepage-action-check' => 'Lista över sidor som ska tas bort',
	'pt-deletepage-action-perform' => 'Utför raderingen',
	'pt-deletepage-action-other' => 'Ändra mål',
	'pt-deletepage-lang-legend' => 'Radera översättningssida',
	'pt-deletepage-full-legend' => 'Radera översättningsbar sida',
	'pt-deletepage-any-legend' => 'Radera översättbar sida eller översättningssida',
	'pt-deletepage-current' => 'Sidnamn:',
	'pt-deletepage-reason' => 'Anledning:',
	'pt-deletepage-subpages' => 'Radera alla undersidor',
	'pt-deletepage-list-pages' => 'Lista över sidor att radera',
	'pt-deletepage-list-translation' => 'Översättningssidor',
	'pt-deletepage-list-section' => 'Översättningsenhetssidor',
	'pt-deletepage-list-other' => 'Andra undersidor',
	'pt-deletepage-list-count' => 'Totalt $1 {{PLURAL:$1|sida|sidor}} att radera.',
	'pt-deletepage-full-logreason' => 'Del av översättningsbar sida $1.',
	'pt-deletepage-lang-logreason' => 'Del av översättningssida $1.',
);

/** Swahili (Kiswahili)
 * @author Kwisha
 */
$messages['sw'] = array(
	'tpt-select-prioritylangs-reason' => 'Sababu:',
	'tpt-rev-discourage' => 'vunja moyo',
	'tpt-rev-encourage' => 'rejesha',
	'translate-tag-translate-link-desc' => 'Tafsiri ukurasa huu',
	'tpt-languages-legend' => 'Lugha zingine:',
	'tpt-discouraged-language-reason' => 'Sababu: $1',
	'tpt-aggregategroup-add' => 'Ongeza',
	'tpt-aggregategroup-save' => 'Hifadhi',
);

/** Tamil (தமிழ்)
 * @author Karthi.dr
 * @author Shanmugamp7
 * @author TRYPPN
 * @author மதனாஹரன்
 */
$messages['ta'] = array(
	'pagetranslation' => 'பக்கத்தின் மொழிபெயர்ப்பு',
	'right-pagetranslation' => 'மொழிபெயர்ப்புக்காக  பக்கங்களின்  பதிப்புகளை குறியிடு',
	'tpt-desc' => 'உள்ளடக்க பக்கங்களை மொழிபெயர்க்க  விரிவாக்கம்',
	'tpt-section' => 'மொழிபெயர்ப்பு அலகு$1',
	'tpt-section-new' => 'புதிய மொழிபெயர்ப்பு அலகு.
பெயர்:$1',
	'tpt-section-deleted' => 'மொழிபெயர்ப்பு அலகு$1',
	'tpt-template' => 'பக்கத்தின் வார்ப்புரு',
	'tpt-templatediff' => 'பக்க வார்ப்புரு மாற்றப்பட்டுள்ளது.',
	'tpt-diff-old' => 'முந்தைய சொற்றொடர்',
	'tpt-diff-new' => 'புதிய சொற்றொடர்',
	'tpt-submit' => 'இந்த பதிப்பை மொழிபெயர்ப்புக்காக குறியிடு',
	'tpt-sections-oldnew' => 'புதிய மற்றும் தற்போதுள்ள மொழிபெயர்ப்பு பிரிவுகள்',
	'tpt-sections-deleted' => 'நீக்கப்பட்ட மொழிபெயர்ப்பு பிரிவுகள்',
	'tpt-sections-template' => 'மொழிபெயர்ப்பு பக்க வார்ப்புரு',
	'tpt-action-nofuzzy' => 'மொழிபெயர்ப்புகளை செல்லத்தாகாததாக்க வேண்டாம்.',
	'tpt-badtitle' => ' தரப்பட்ட பக்க பெயர் ( $1 ) செல்லத்தக்க தலைப்பு இல்லை',
	'tpt-nosuchpage' => 'பக்க  $1  இல்லை',
	'tpt-badsect' => 'மொழிபெயர்ப்பு அலகு   $2 க்கு \'\'$1 "ஒரு செல்லத்தக்க பெயர்  அல்ல.',
	'tpt-edit-failed' => '$1 பக்கத்தை இற்றைப்படுத்த இயலவில்லை.',
	'tpt-new-pages-title' => 'மொழிபெயர்ப்புக்குப் பரிந்துரைக்கப்பட்டுள்ள பக்கங்கள்',
	'tpt-old-pages-title' => 'மொழிபெயர்க்கப்பட்டு வரும் பக்கங்கள்',
	'tpt-other-pages-title' => 'உடைந்த பக்கங்கள்',
	'tpt-select-prioritylangs-reason' => 'காரணம்:',
	'tpt-sections-prioritylangs' => 'முன்னுரிமை தரப்பட்ட மொழிகள்',
	'tpt-rev-mark' => 'மொழிபெயர்ப்புக்காக குறியிடு',
	'tpt-rev-unmark' => 'மொழிபெயர்ப்பிலிருந்து நீக்கு',
	'tpt-rev-discourage' => 'அதையரியப்படுத்து',
	'tpt-rev-encourage' => 'மீட்டமை',
	'tpt-rev-mark-tooltip' => 'இப்பக்கத்தின் சமீபத்திய பதிப்பை மொழிபெயர்ப்புக்காக குறியிடு.',
	'tpt-rev-unmark-tooltip' => 'இப்பக்கத்தை  மொழிபெயர்ப்பில் இருந்து நீக்கவும்.',
	'tpt-rev-discourage-tooltip' => 'இந்த பக்கத்தில் மேலும் செய்யப்படும் மொழிபெயப்புகளை அதையரியப்படுத்து',
	'tpt-rev-encourage-tooltip' => 'இப்பக்கத்தை சராசரி மொழிபெயர்ப்புக்கு மீட்டெடுக்கவும்.',
	'translate-tag-translate-link-desc' => 'இப்பக்கத்தை மொழிபெயர்க்கவும்',
	'translate-tag-markthis' => 'இந்த பக்கத்தை மொழிபெயர்ப்புக்காக குறியிடு',
	'tpt-languages-legend' => 'மற்ற மொழிகள்:',
	'tpt-languages-zero' => 'இம் மொழியின் சிறிய மொழிபெயர்ப்பு',
	'tpt-translation-restricted' => 'இப்பக்கத்தை இந்த மொழிக்கு மொழிபெயர்ப்பது மொழிபெயர்ப்பு நிருவாகி ஒருவரால் காக்கப்பட்டுள்ளது.

காரணம்: $1',
	'tpt-discouraged-language-reason' => 'காரணம்: $1',
	'tpt-aggregategroup-add' => 'சேர்',
	'tpt-aggregategroup-save' => 'சேமி',
	'tpt-aggregategroup-new-name' => 'பெயர்:',
	'tpt-aggregategroup-invalid-group' => 'இந்தக் குழு இல்லை.',
	'pt-movepage-list-pages' => 'நகர்த்த வேண்டிய பக்கங்களின் பட்டியல்',
	'pt-movepage-list-translation' => 'மொழிபெயர்ப்பு பக்கங்கள்', # Fuzzy
	'pt-movepage-list-section' => 'பிரிவு பக்கங்கள்', # Fuzzy
	'pt-movepage-list-other' => 'மற்ற துணைப்பக்கங்கள்', # Fuzzy
	'pt-movepage-list-count' => 'மொத்தம்  $1   {{PLURAL:$1|பக்கம் |பக்கங்கள்}} நகர்த்த.',
	'pt-movepage-legend' => 'மொழிபெயர்க்கதகுந்த பக்கத்தை நகர்த்து',
	'pt-movepage-current' => 'நடப்பு பெயர்:',
	'pt-movepage-new' => 'புதிய பெயர்:',
	'pt-movepage-reason' => 'காரணம்:',
	'pt-movepage-subpages' => 'எல்லா துணைப்பக்கங்களையும் நகர்த்து',
	'pt-movepage-action-check' => 'இந்த நகர்த்தல் சாத்தியமானதா என சரிபார்',
	'pt-movepage-action-perform' => 'நகர்த்தலை செய்யவும்',
	'pt-movepage-action-other' => 'இலக்கை மாற்று',
	'pt-movepage-logreason' => 'மொழிபெயர்க்க தகுந்த பக்கத்தின் பகுதி $1.',
	'pt-movepage-started' => 'அடிப்படை பக்கம் இப்போது நகர்த்தப்பட்டுள்ளது.
தயவுகூர்ந்து  [[Special:Log/pagetranslation|பக்க மொழிபெயர்ப்பு குறிப்பேடு]] ல்  பிழைகளை மற்றும் நிறைவு தகவலை சரிபார்க்கவும்.',
	'pt-locked-page' => 'இப்பக்கம் பூட்டப்பட்டுள்ளது ஏனெனில் மொழிபெயர்க்கத்தகுந்த பக்கம்  தற்போது  நகர்த்தப்பட்டது.',
	'pt-deletepage-lang-title' => 'மொழிபெயர்ப்பு பக்கம்  $1 நீக்கப்படுகிறது.',
	'pt-deletepage-full-title' => 'மொழிபெயர்க்கதகுந்த பக்கம் $1 நீக்கப்படுகிறது',
	'pt-deletepage-invalid-title' => 'குறிப்பிட்ட பக்கம் செல்லத்தக்கது அல்ல.',
	'pt-deletepage-invalid-text' => 'குறிப்பிட்ட பக்கம் ஒரு மொழிபெயர்ப்புசெய்யத்தகுந்த பக்கம் அல்லது அதன் மொழிபெயர்ப்பு அல்ல', # Fuzzy
	'pt-deletepage-action-check' => 'நீக்க வேண்டிய பக்கங்களை பட்டியலிடு',
	'pt-deletepage-action-perform' => 'நீக்கலை செய்யவும்',
	'pt-deletepage-action-other' => 'இலக்கை மாற்று',
	'pt-deletepage-lang-legend' => 'மொழிபெயர்ப்பு பக்கத்தை நீக்கு',
	'pt-deletepage-full-legend' => 'மொழிபெயர்க்கதகுந்த பக்கத்தை நீக்கு',
	'pt-deletepage-any-legend' => 'மொழிபெயர்க்கத்தகுந்த பக்கம் அல்லது மொழிபெயர்க்கத்தகுந்த பக்கத்தின் மொழிபெயர்ப்பை நீக்கு', # Fuzzy
	'pt-deletepage-current' => 'பக்கப் பெயர்:',
	'pt-deletepage-reason' => 'காரணம்:',
	'pt-deletepage-subpages' => 'எல்லா துணைப்பக்கங்களையும் நீக்கு',
	'pt-deletepage-list-pages' => 'நீக்கபடவேண்டிய பக்கங்களின் பட்டியல்',
	'pt-deletepage-list-translation' => 'மொழிபெயர்ப்பு பக்கங்கள்',
	'pt-deletepage-list-section' => 'பிரிவு பக்கங்கள்', # Fuzzy
	'pt-deletepage-list-other' => 'மற்ற துணைப்பக்கங்கள்',
	'pt-deletepage-list-count' => 'மொத்தம்  $1   {{PLURAL:$1|பக்கம் |பக்கங்கள்}} நீக்கப்பட.',
	'pt-deletepage-full-logreason' => 'மொழிபெயர்க்க தகுந்த பக்கத்தின் பகுதி $1.',
	'pt-deletepage-lang-logreason' => 'மொழிபெயர்ப்பு பக்கத்தின் பகுதி  $1 .',
	'pt-deletepage-started' => 'தயவுகூர்ந்து  [[Special:Log/pagetranslation|பக்க மொழிபெயர்ப்பு குறிப்பேடு]] ல்  பிழைகளை மற்றும் நிறைவு தகவலை சரிபார்க்கவும், .',
);

/** Telugu (తెలుగు)
 * @author Kiranmayee
 * @author Veeven
 */
$messages['te'] = array(
	'pagetranslation' => 'పేజీ అనువాదం',
	'right-pagetranslation' => 'పేజీల కూర్పులను అనువాదానికై గుర్తించడం',
	'tpt-desc' => 'విషయపు పేజీలను అనువదించడానికై పొడగింత',
	'tpt-section' => 'అనువాద విభాగం $1',
	'tpt-section-new' => 'కొత్త అనువాద విభాగం. పేరు: $1',
	'tpt-section-deleted' => 'అనువాద విభాగము $1',
	'tpt-template' => 'పేజీ మూస',
	'tpt-diff-old' => 'గత పాఠ్యం',
	'tpt-diff-new' => 'కొత్త పాఠ్యం',
	'tpt-sections-template' => 'అనువాద పేజీ మూస',
	'tpt-badtitle' => 'ఇచ్చిన పేజీ పేరు ($1) సరైన శీర్షిక కాదు',
	'tpt-nosuchpage' => '$1 అనే పుట లేనే లేదు',
	'tpt-edit-failed' => 'పేజీని తాజాకరించలేకపోయాం: $1',
	'tpt-already-marked' => 'ఈ పేజీ యొక్క సరికొత్త కూర్పుని ఇప్పటికే అనువాదానికై గుర్తించారు.',
	'tpt-select-prioritylangs-reason' => 'కారణం:',
	'tpt-sections-prioritylangs' => 'ప్రాధాన్య భాషలు',
	'translate-tag-translate-link-desc' => 'ఈ పేజీని అనువదించండి',
	'translate-tag-markthis' => 'ఈ పేజీని అనువాదం కొరకు గుర్తించు',
	'translate-tag-markthisagain' => 'చివరిసారి <span class="plainlinks">[$2 అనువాదానికి గుర్తించినప్పటి నుండి]</span> ఈ పేజీకి <span class="plainlinks">[$1 మార్పులు]</span> జరిగాయి.',
	'tpt-languages-legend' => 'ఇతర భాషలు:',
	'tpt-aggregategroup-add' => 'చేర్చు',
	'tpt-aggregategroup-save' => 'భద్రపరచు',
	'tpt-aggregategroup-new-name' => 'పేరు:',
	'tpt-aggregategroup-new-description' => 'వివరణ (ఐచ్చికం):',
	'log-name-pagetranslation' => 'పేజీ అనువాదాల చిట్టా',
	'pt-movepage-block-subpage-exists' => 'ఆ లక్ష్యిత ఉపపుట [[:$2]] ఉనికిలో ఉంది.',
	'pt-movepage-list-pages' => 'తరలించాల్సిన పుటల యొక్క జాబితా',
	'pt-movepage-list-translation' => 'అనువాద పుటలు', # Fuzzy
	'pt-movepage-list-other' => 'ఇతర ఉపపుటలు', # Fuzzy
	'pt-movepage-list-count' => 'మొత్తం తరలించాల్సినవి $1 {{PLURAL:$1|పుట|పుటలు}}.',
	'pt-movepage-current' => 'ప్రస్తుత పేరు:',
	'pt-movepage-new' => 'కొత్త పేరు:',
	'pt-movepage-reason' => 'కారణం:',
	'pt-deletepage-current' => 'పేజీ పేరు:',
	'pt-deletepage-reason' => 'కారణం:',
	'pt-deletepage-subpages' => 'అన్ని ఉపపేజీలను తొలగించు',
	'pt-deletepage-list-translation' => 'అనువాద పేజీలు',
	'pt-deletepage-list-other' => 'ఇతర ఉపపేజీలు',
);

/** Thai (ไทย)
 * @author Ans
 * @author Passawuth
 * @author Woraponboonkerd
 */
$messages['th'] = array(
	'pagetranslation' => 'การแปลภาษา',
	'right-pagetranslation' => 'กำหนดให้รุ่นปรับปรุงนี้เพื่อการแปลภาษา',
	'tpt-desc' => 'ส่วนเพิ่มเติมสำหรับหน้าที่มีการแปลเนื้อหา',
	'tpt-section' => 'หน่วยการแปล $1',
	'tpt-section-new' => 'หน่วยการแปลใหม่

ชื่อ: $1',
	'tpt-section-deleted' => 'หน่วยการแปล $1',
	'tpt-template' => 'แม่แบบของหน้า',
	'tpt-templatediff' => 'แม่แบบของหน้านี้ได้ถูกเปลี่ยนแปลงแล้ว',
	'tpt-diff-old' => 'อักษรก่อนหน้า',
	'tpt-diff-new' => 'คำใหม่',
	'tpt-submit' => 'กำหนดให้รุ่นนี้เพื่อการแปลภาษา',
	'tpt-sections-oldnew' => 'หน่วยการแปลใหม่และที่มีอยู่เดิมแล้ว',
	'tpt-sections-deleted' => 'หน่วยการแปลที่ถูกลบแล้ว',
	'tpt-sections-template' => 'แม่แบบหน้าการแปลภาษา',
	'tpt-badtitle' => 'ชื่อหน้าที่กำหนดมานั้น ($1) ไม่ใช่ชื่อหน้าที่ถูกต้อง',
	'tpt-nosuchpage' => 'ไม่มีหน้า $1',
	'tpt-oldrevision' => '$2 ไม่ใช่รุ่นปรับปรุงล่าสุดของหน้าชื่อ[[$1]]

เฉพาะรุ่นปรับปรุงล่าสุดเท่านั้นที่สา่มารถกำหนดเพื่อการแปลภาษา',
	'tpt-notsuitable' => 'หน้า $1 นั้นไม่เมาะสมในการแปลภาษา

ตรวจสอบให้แน่ใจว่ามีแท็ก <nowiki><translate></nowiki> อยู่และมีประโยคของโค้ดที่ถูกต้อง',
	'tpt-saveok' => 'หน้า [[$1]] ได้ถูกกำหนดไว้สำหรับการแปลภาษากับหน่วยการแปลภาษา $2 หน่วย

หน้านี้สามารถ<span class="plainlinks">[$3 เริ่มแปลภาษาได้แล้ว]</span>',
	'tpt-badsect' => '"$1" ไม่ใช่ชื่อที่ถูกต้องสำหรับหน่วยการแปลภาษา $2',
	'tpt-showpage-intro' => 'ส่วนที่มีการเพิ่มใหม่, มีอยู่เดิม และที่ถูกลบไปแล้วนั้นปรากฎด้านล่างนี้
ก่อนที่จะทำให้รุ่นปรับปรุงนี้สำหรับการแปลภาษา ตรวจสอบให้แน่ใจว่าการเปลี่ยนแปลงของส่วนต่างๆ ได้ถูกลดลงมาเพื่อเป็นการหลีกเลี่ยงงานที่ไม่จำเป็นของผู้แปลภาษา', # Fuzzy
	'tpt-mark-summary' => 'กำหนดให้รุ่นปรับปรุงนี้สำหรับการแปลภาษา',
	'tpt-edit-failed' => 'ไม่สามารถปรับปรุงหน้า: $1 ได้',
	'tpt-already-marked' => 'รุ่นปรับปรุงล่าสุดของหน้านี้ได้ถูกกำหนดเพื่อการแปลภาษาแล้ว',
	'tpt-list-nopages' => 'ไม่มีหน้าใดๆ ที่ถูกกำหนดเพื่อการแปลภาษา หรือพร้อมที่จะถูกกำหนดเพื่อการแปลภาษา',
	'tpt-new-pages' => '{{PLURAL:$1|หน้านี้|หน้าเหล่านี้}} มีที่คั่นสำหรับการแปลภาษาอยู่ แต่ไม่มีรุ่นปรับปรุงใดๆ เลยของ{{PLURAL:$1|หน้านี้|หน้าแหล่านี้}} ที่ได้ถูกกำหนดเพื่อการแปลภาษา',
	'tpt-old-pages' => 'รุ่นปรับปรุงบางรุ่นของ{{PLURAL:$1|หน้านี้|หน้าต่างๆ เหล่านี้}} ได้ถูกกำหนดเพื่อการแปลภาษาแล้ว',
	'tpt-rev-unmark' => 'ลบหน้านี้จากการแปล', # Fuzzy
	'translate-tag-translate-link-desc' => 'แปลหน้านี้',
	'translate-tag-markthis' => 'กำหนดให้หน้านี้เพื่อการแปลภาษา',
	'translate-tag-markthisagain' => 'หน้านี้มี<span class="plainlinks">[$1 ความเปลี่ยนแปลง]</span> นับตั้งแต่ครั้งสุดท้ายที่<span class="plainlinks">[$2 ถูกกำหนดเพื่อการแปลภาษา]</span>.',
	'translate-tag-hasnew' => 'หน้านี้มี<span class="plainlinks">[$1 ความเปลี่ยนแปลง]</span> ที่ไม่ได้ถูกกำหนดเพื่อการแปลภาษา',
	'tpt-translation-intro' => 'หน้านี้คือ<span class="plainlinks">[$1 รุ่นปรับปรุงที่เริ่มแปลแล้ว]</span> ของ [[$2]] และการแปลภาษาเสร็จสิ้นแล้ว $3 เปอร์เซ็นต์ของทั้งหมดและเป็นรุ่นล่าสุด',
	'tpt-languages-legend' => 'ภาษาอื่นๆ:',
	'tpt-target-page' => 'หน้านี้ไม่สามารถถูกปรับปรุงตามปกติได้

หน้านี้เป็นหน้าการแปลของหน้า[[$1]] และสามารถปรับปรุงการแปลได้โดยใช้[เครื่องมือการแปล $2]',
	'tpt-render-summary' => 'กำลังอัพเดตเพื่อทำให้ตรงกันกับรุ่นปรับปรุงใหม่ของหน้่าโค้ดหลัก',
	'tpt-download-page' => 'ส่งหน้าออกไปพร้อมการแปลภาษา',
);

/** Turkmen (Türkmençe)
 * @author Hanberke
 */
$messages['tk'] = array(
	'pagetranslation' => 'Terjime sahypasy',
);

/** Tagalog (Tagalog)
 * @author AnakngAraw
 */
$messages['tl'] = array(
	'pagetranslation' => 'Salinwika ng pahina',
	'right-pagetranslation' => 'Tatakan ang mga bersyon ng mga pahinang isasalinwika',
	'tpt-desc' => 'Dugtong para sa pagsasalinwika ng mga pahina ng nilalaman',
	'tpt-section' => 'Yunit ng salinwika $1',
	'tpt-section-new' => 'Bagong yunit ng salinwika.
Pangalan: $1',
	'tpt-section-deleted' => 'Yunit ng salinwika $1',
	'tpt-template' => 'Suleras ng pahina',
	'tpt-templatediff' => 'Nabago na ang suleras ng pahina.',
	'tpt-diff-old' => 'Naunang teksto',
	'tpt-diff-new' => 'Bagong teksto',
	'tpt-submit' => 'Tatakan ang bersyong ito para isalinwika',
	'tpt-sections-oldnew' => 'Bago at umiiral ng mga yunit ng salinwika',
	'tpt-sections-deleted' => 'Naburang mga yunit ng salinwika',
	'tpt-sections-template' => 'Suleras ng pahina ng salinwika',
	'tpt-action-nofuzzy' => 'Huwag hindi tanggapin ang mga salinwika',
	'tpt-badtitle' => 'Ang pangalan ng pahinang ibinigay ($1) ay isang hindi tanggap na pamagat',
	'tpt-nosuchpage' => 'Hindi umiiral ang pahinang $1',
	'tpt-oldrevision' => 'Ang $2 ay hindi ang pinakabagong bersyon ng pahinang [[$1]].
Tanging pinakabagong mga bersyong lang ang tatatakan para sa pagsasalinwika.',
	'tpt-notsuitable' => 'Hindi angkop ang pahinang $1 para sa pagsasalinwika.
Tiyaking mayroon itong mga tatak na <nowiki><translate></nowiki> at may isang tanggap na sintaks.',
	'tpt-saveok' => 'Nilagyang ng tanda ang pahinang [[$1]] para sa pagsasalinwika na may $2 na {{PLURAL:$2|yunit ng salinwika|mga yunit ng salinwika}}.
Maaari na ngayong <span class="plainlinks">[$3 isalinwika]</span> ang pahina.',
	'tpt-badsect' => 'Ang $1" ay isang hindi tanggap na pangalan para sa yunit ng salinwikang $2.',
	'tpt-showpage-intro' => 'Nakatala sa ibaba ang bago, umiiral at naburang mga yunit ng salinwika.
Bago tatakan ang bersyong ito para isalinwika, suriing nakauntian ang mga pagbabago sa mga yunit ng salinwika upang maiwasan ang hindi kailangang gawain para sa mga tagapagsalinwika.',
	'tpt-mark-summary' => 'Tinatakan ang bersyong ito para isalinwika',
	'tpt-edit-failed' => 'Hindi maisapanahon ang pahina:  $1',
	'tpt-duplicate' => 'Ang pangalan ng yunit ng salinwika na $1 ay ginagamit nang mas marami kaysa sa isa.',
	'tpt-already-marked' => 'Ang huling bersyon ng pahinang ito ay natatakan na para sa pagsasalinwika.',
	'tpt-unmarked' => 'Ang pahinang $1 ay hindi na tinatakan para sa pagsasalinwika.',
	'tpt-list-nopages' => 'Walang mga pahinang tinatakan para sa pagsasalinwika o nakahanda upang markahan para sa pagsasalinwika.',
	'tpt-new-pages-title' => 'Mga pahinang ipinanukala para sa pagsasalinwika',
	'tpt-old-pages-title' => 'Mga pahinang nasa pagsasalinwika',
	'tpt-other-pages-title' => 'Patid na mga pahina',
	'tpt-discouraged-pages-title' => 'Mga pahinang hindi hinihimok',
	'tpt-new-pages' => '{{PLURAL:$1|Naglalaman ang pahinang ito|Naglalaman ang mga pahinang ito}} ng tekstong may mga tatak ng pagsasalinwika,
ngunit walang bersyon na {{PLURAL:$1|ang pahinang ito|ang mga pahinang ito}} ay kasalukuyang tinatakan para sa pagsasalinwika.',
	'tpt-old-pages' => 'Ilang bersyon ng {{PLURAL:$1|pahinang ito|mga pahinang ito}} ay natatakan na para sa pagsasalinwika.',
	'tpt-other-pages' => '{{PLURAL:$1|Isang lumang bersyon ng pahinang ito ang|Mas lumang mga bersyon ng mga pahinang ito ang}} tinatakan para sa pagsasalinwika,
subalit ang pinakabagong {{PLURAL:$1|bersyon|mga bersyon}} ay hindi matatatakan para sa pagsasalinwika.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Ang pahinang ito|Ang mga pahinang ito}} ay hindi na hinihimok na magkaroon ng karagdagan pang pagsasalinwika.',
	'tpt-select-prioritylangs' => 'Talaan ng mga kodigo ng mga wikang dapat unahin na pinaghihiwa-hiwalay ng mga kuwit:',
	'tpt-select-prioritylangs-force' => 'Iwasan ang mga pagsasalinwika papunta sa ibang mga wika kaysa sa mga wikang dapat unahin', # Fuzzy
	'tpt-select-prioritylangs-reason' => 'Dahilan:',
	'tpt-sections-prioritylangs' => 'Mga wikang nararapat na unahin',
	'tpt-rev-mark' => 'minarkahan para sa pagsasalinwika',
	'tpt-rev-unmark' => 'alisin mula sa pagsasalinwika',
	'tpt-rev-discourage' => 'huwag himukin',
	'tpt-rev-encourage' => 'papanumbalikin',
	'tpt-rev-mark-tooltip' => 'Markahan ang pinakahuling bersiyon ng pahinang ito para sa pagsasalinwika.',
	'tpt-rev-unmark-tooltip' => 'Alisin ang pahinang ito mula sa pagsasalinwika.',
	'tpt-rev-discourage-tooltip' => 'Huwag nang himukin ang karagdagan pang mga pagsasalinwika sa pahinang ito.',
	'tpt-rev-encourage-tooltip' => 'Papanumbalikin ang pahinang ito papunta sa normal na pagsasalinwika.',
	'translate-tag-translate-link-desc' => 'Isalinwika ang pahinang ito',
	'translate-tag-markthis' => 'Tatakan ang pahinang ito para isalinwika',
	'translate-tag-markthisagain' => 'Ang pahinang ito ay may <span class="plainlinks">[$1 mga pagbabago]</span> mula pa noong huli itong <span class="plainlinks">[$2 tinatakan para isalinwika]</span>.',
	'translate-tag-hasnew' => 'Naglalaman ang pahinang ito ng <span class="plainlinks">[$1 mga pagbabagong]</span> hindi tinatakan para isalinwika.',
	'tpt-translation-intro' => 'Ang pahinang ito ay isang <span class="plainlinks">[$1 naisalinwikang bersyon]</span> ng isang pahina [[$2]] at ang salinwika ay $3% kumpleto na.',
	'tpt-languages-legend' => 'Iba pang mga wika:',
	'tpt-languages-separator' => '&#160;•&#160;',
	'tpt-languages-zero' => 'Simulan ang pagsasalinwika para sa wikang ito',
	'tpt-target-page' => 'Hindi maaaring kinakamay na maisapanahon ang pahinang ito.
Ang pahinang ito ay isang salinwika ng pahinang [[$1]] at maisasapanahon ang salinwika sa pamamagitan ng [$2 kasangkapang pansalinwika].',
	'tpt-unknown-page' => 'Nakalaan ang puwang na pampangalang ito para sa mga salinwika ng pahina ng nilalaman.
Tila hindi tumutugma ang pahinang sinusubukan mong baguhin sa anumang pahinang natatakan para sa pagsasalinwika.',
	'tpt-translation-restricted' => 'Ang pagsasalinwika ng pahinang ito na papunta sa wikang ito ay pinigilan ng isang tagapangasiwa ng salinwika.

Dahilan: $1',
	'tpt-discouraged-language-force' => "'''Ang pahinang ito ay hindi maisasalinwika upang maging $2.'''

Isang tagapangasiwa ng pagsasalinwika ang nagpasya na ang pahinang ito ay maisasalinwika lamang papunta sa $3.",
	'tpt-discouraged-language' => "'''Ang pagsasalinwika papunta sa $2 ay hindi isang nararapat na unahin para sa pahinang ito.'''

Isang tagapangasiwa ng salinwika ang nagpasyang ituon ang mga pagsisikap ng pagsasalinwika sa $3.",
	'tpt-discouraged-language-reason' => 'Dahilan: $1',
	'tpt-priority-languages' => 'Isang tagapangasiwa ng salinwika ang nagtakda ng mga wikang nararapat na unahin para sa pangkat na ito upang maging $1.',
	'tpt-render-summary' => 'Isinasapanahon upang tumugma sa bagong bersyon ng pinagmulang pahina',
	'tpt-download-page' => 'Iluwas ang pahinang may mga pagsasalinwika',
	'aggregategroups' => 'Mga pangkat na pinagsama-sama',
	'tpt-aggregategroup-add' => 'Idagdag',
	'tpt-aggregategroup-save' => 'Sagipin',
	'tpt-aggregategroup-add-new' => 'Magdagdag ng isang bagong pangkat na pinagsama',
	'tpt-aggregategroup-new-name' => 'Pangalan:',
	'tpt-aggregategroup-new-description' => 'Paglalarawan (maaaring wala nito):',
	'tpt-aggregategroup-remove-confirm' => 'Nakatitiyak ka bang nais mong burahin ang pinagsamang pangkat na ito?',
	'tpt-aggregategroup-invalid-group' => 'Hindi umiiral ang pangkat',
	'pt-parse-open' => 'Hindi magkatimbang na tatak na &lt;translate>.
Suleras ng pagsasalinwika:  <pre>$1</pre>',
	'pt-parse-close' => 'Hindi magkatimbang na tatak na &lt;translate>.
Suleras ng pagsasalinwika:  <pre>$1</pre>',
	'pt-parse-nested' => 'Hindi pinapayagan ang nakapugad na mga yunit ng salinwika &lt;translate>.
Teksto ng tatak: <pre>$1</pre>',
	'pt-shake-multiple' => 'Mga pananda ng maramihang yunit ng salinwika para sa isang yunit ng salinwika.
Teksto ng yunit ng salinwika: <pre>$1</pre>',
	'pt-shake-position' => 'Mga pananda ng yunit ng salinwika sa loob ng posisyong hindi inaasahan.
Teksto ng yunit ng salinwika: <pre>$1</pre>',
	'pt-shake-empty' => 'Yunit ng salinwika na walang laman para sa panandang "$1".',
	'log-description-pagetranslation' => 'Itala para sa mga gawaing may kaugnayan sa sistema ng pagsasalinwika ng pahina',
	'log-name-pagetranslation' => 'Tala ng pagsasalinwika ng pahina',
	'pt-movepage-title' => 'Ilipat ang maisasalinwikang pahinang $1',
	'pt-movepage-blockers' => 'Hindi malilipat ang maisasalinwikang pahina papunta sa bagong pangalan dahil sa sumusunod na {{PLURAL:$1|kamalian|mga kamalian}}:',
	'pt-movepage-block-base-exists' => 'Umiiral ang puntiryang batayang pahina na [[:$1]].', # Fuzzy
	'pt-movepage-block-base-invalid' => 'Hindi isang tanggap na pamagat ang puntiryang batayang pahina.', # Fuzzy
	'pt-movepage-block-tp-exists' => 'Umiiral ang puntiryang pahina ng salinwika na [[:$2]].',
	'pt-movepage-block-tp-invalid' => 'Ang pinupukol na pamagat ng pahinang maisasalinwika para sa [[:$1]] ay hindi matatanggap (napakahaba?).',
	'pt-movepage-block-section-exists' => 'Umiiral ang pahina ng yunit ng salinwikang pinupukol na "[[:$2]]".',
	'pt-movepage-block-section-invalid' => 'Ang pamagat ng pahina ng seksiyong pinupukol para sa "[[:$1]]" na para sa yunit ng salinwika ay hindi magiging katanggap-tanggap (napakahaba?).',
	'pt-movepage-block-subpage-exists' => 'Umiiral ang pinupukol na kabahaging pahinang [[:$2]].',
	'pt-movepage-block-subpage-invalid' => 'Ang pinupukol na pamagat ng kabahaging pahina para sa [[:$1]] ay hindi matatanggap (napakahaba?).',
	'pt-movepage-list-pages' => 'Talaan ng mga pahinang ililipat',
	'pt-movepage-list-translation' => 'Mga pahina ng salinwika', # Fuzzy
	'pt-movepage-list-section' => 'Mga pahina ng yunit ng salinwika', # Fuzzy
	'pt-movepage-list-other' => 'Iba pang kabahaging mga pahina', # Fuzzy
	'pt-movepage-list-count' => 'Sa kabuuan ay $1 ang {{PLURAL:$1|pahina|mga pahina}}ng ililipat.',
	'pt-movepage-legend' => 'Ilipat ang pahinang maisasalinwika',
	'pt-movepage-current' => 'Kasalukuyang pangalan:',
	'pt-movepage-new' => 'Bagong pangalan:',
	'pt-movepage-reason' => 'Dahilan:',
	'pt-movepage-subpages' => 'Ilipat ang lahat ng kabahaging mga pahina',
	'pt-movepage-action-check' => 'Suriin kung maaari ang paglilipat',
	'pt-movepage-action-perform' => 'Gawin ang paglipat',
	'pt-movepage-action-other' => 'Baguhin ang pinupukol',
	'pt-movepage-intro' => 'Ang natatanging pahinang ito ay nagpapahintulot sa iyong mailipat ang mga pahinang minarkahan para sa pagsasalinwika.
Ang galaw ng paglipat ay hindi magiging kaagad-agad, dahil maraming mga pahina ang kailangang ilipat.
Habang inililipat ang mga pahina, hindi maaaring kasalamuhain ang mga pahinang tinutukoy.
Ang mga kabiguan ay itatala sa loob ng [[Special:Log/pagetranslation|talaan ng pagsasalinwika ng pahina]] at nangangailangan sila ng kinakamay na pagkukumpuni.',
	'pt-movepage-logreason' => 'Bahagi ng maisasalinwikang pahinang $1.',
	'pt-movepage-started' => 'Nailipat na ngayon ang pahinang batayan.
Pakisuri ang [[Special:Log/pagetranslation|talaan ng pagsasalinwika ng pahina]] para sa mga kamalian at mensahe ng pagkakabuo.',
	'pt-locked-page' => 'Ikinandao ang pahinang ito dahil ang pahinang maisasalinwika ay kasalukuyang inililipat.',
	'pt-deletepage-lang-title' => 'Binubura ang pahina ng salinwikang $1.',
	'pt-deletepage-full-title' => 'Binubura ang maisasalinwikang pahina na $1.',
	'pt-deletepage-invalid-title' => 'Hindi katanggap-tanggap ang tinukoy na pahina.',
	'pt-deletepage-invalid-text' => 'Ang tinukoy na pahina ay hindi isang pahinang maisasalinwika o salinwika kaya nito.', # Fuzzy
	'pt-deletepage-action-check' => 'Ilista ang mga pahinang buburahin',
	'pt-deletepage-action-perform' => 'Gawin ang pagbura',
	'pt-deletepage-action-other' => 'Baguhin ang puntirya',
	'pt-deletepage-lang-legend' => 'Burahin ang pahina ng salinwika',
	'pt-deletepage-full-legend' => 'Burahin ang pahinang maisasalinwika',
	'pt-deletepage-any-legend' => 'Burahin ang maisasalinwikang pahina o salinwika ng maisasalinwikang pahina', # Fuzzy
	'pt-deletepage-current' => 'Pangalan ng pahina:',
	'pt-deletepage-reason' => 'Dahilan:',
	'pt-deletepage-subpages' => 'Burahin ang lahat ng kabahaging mga pahina',
	'pt-deletepage-list-pages' => 'Talaan ng mga pahinang buburahin',
	'pt-deletepage-list-translation' => 'Mga pahina ng salinwika',
	'pt-deletepage-list-section' => 'Mga pahina ng yunit ng salinwika',
	'pt-deletepage-list-other' => 'Iba pang kabahaging mga pahina',
	'pt-deletepage-list-count' => 'Sa kabuuan ay $1 ang {{PLURAL:$1|pahina|mga pahina}}ng buburahin.',
	'pt-deletepage-full-logreason' => 'Bahagi ng maisasalinwikang pahinang $1.',
	'pt-deletepage-lang-logreason' => 'Bahagi ng pahina ng salinwikang $1.',
	'pt-deletepage-started' => 'Pakisuri ang [[Special:Log/pagetranslation|talaan ng pagsasalinwika ng pahina]] para sa mga kamalian at mensahe ng pagkakabuo.',
	'pt-deletepage-intro' => 'Nagpapahintulot sa iyo ang pahinang ito na magbura ng kabuuan ng mga pahinang maisasalinwika o mga pagsasalinwika na papunta sa isang wika.
Ang kilos ng pagbura ay hindi magiging kaagad, dahil maraming mga pahina ang kakailanganing burahin.
Ang mga kabiguan ay itatala sa loob ng [[Special:Log/pagetranslation|tala ng pagsasalinwika ng pahina]] at kakailanganing kinakamay ang pagkukumpuni ng mga ito.', # Fuzzy
);

/** Turkish (Türkçe)
 * @author Emperyan
 * @author Incelemeelemani
 * @author Joseph
 * @author Karduelis
 * @author Suelnur
 * @author Vito Genovese
 */
$messages['tr'] = array(
	'pagetranslation' => 'Çeviri sayfası',
	'right-pagetranslation' => 'Sayfa sürümlerini çeviri için işaretler',
	'tpt-desc' => 'İçerik sayfalarının çevirisi için eklenti',
	'tpt-section' => 'Çeviri birimi $1',
	'tpt-section-new' => 'Yeni çeviri birimi.
Ad: $1',
	'tpt-section-deleted' => 'Çeviri birimi $1',
	'tpt-template' => 'Sayfa şablonu',
	'tpt-templatediff' => 'Sayfa şablonu değişti.',
	'tpt-diff-old' => 'Önceki metin',
	'tpt-diff-new' => 'Yeni metin',
	'tpt-submit' => 'Bu sürümü çeviri için işaretle',
	'tpt-sections-oldnew' => 'Yeni ve mevcut çeviri birimleri',
	'tpt-sections-deleted' => 'Silinen çeviri birimleri',
	'tpt-sections-template' => 'Çeviri sayfası şablonu',
	'tpt-badtitle' => 'Verilen sayfa adı ($1) geçerli bir başlık değil',
	'tpt-oldrevision' => '$2, [[$1]] sayfasının en son sürümü değil.
Sadece en son sürümler çeviri için işaretlenebilir.',
	'tpt-saveok' => '[[$1]] adlı sayfa $2 {{PLURAL:$2|çeviri birimi|çeviri birimi}} ile çeviri için işaretlenmiş.
Sayfa artık <span class="plainlinks">[$3 çevrilebilir]</span>.',
	'tpt-badsect' => '"$1", $2 çeviri birimi için geçerli bir ad değil.',
	'tpt-showpage-intro' => 'Aşağıda yeni, mevcut ve silinmiş çeviri birimleri listelenmiştir.
Bu sürümü çeviri için işaretlemeden önce, çevirmenlere gereksiz iş çıkarmamak için çeviri birimlerinde yapılan değişikliklerin asgari seviyede olduğundan emin olun.',
	'tpt-mark-summary' => 'Bu sürüm çeviri için işaretlendi',
	'tpt-edit-failed' => 'Sayfa güncellenemedi: $1',
	'tpt-already-marked' => 'Bu sayfanın en son sürümü çeviri için işaretlenmiş.',
	'tpt-list-nopages' => 'Çeviri için işaretlenen ya da işaretlenmeye hazır olan herhangi bir sayfa bulunmuyor.',
	'tpt-old-pages-title' => 'Çeviri sayfası',
	'tpt-old-pages' => '{{PLURAL:$1|Bu sayfanın|Bu sayfaların}} bazı sürümleri çeviri için işaretlenmiş.',
	'translate-tag-translate-link-desc' => 'Bu sayfayı çevir',
	'translate-tag-markthis' => 'Bu sayfayı çeviri için işaretle',
	'translate-tag-hasnew' => 'Bu sayfa, çeviri için işaretlenmemiş <span class="plainlinks">[$1 değişiklik]</span> içeriyor.',
	'tpt-languages-legend' => 'Diğer diller:',
	'tpt-render-summary' => 'Kaynak sayfanın yeni sürümü ile eşleme için güncelleniyor',
	'tpt-download-page' => 'Çevirileri olan sayfayı dışa aktar',
	'tpt-aggregategroup-add' => 'Ekle',
	'tpt-aggregategroup-save' => 'Kaydet',
	'tpt-aggregategroup-new-name' => 'Ad:',
	'tpt-aggregategroup-new-description' => 'Açıklama (isteğe bağlı):',
	'pt-movepage-list-other' => 'Diğer alt {{PLURAL:$1|sayfalar|sayfalar}}',
	'pt-movepage-current' => 'Geçerli adı:',
	'pt-movepage-new' => 'Yeni adı:',
	'pt-movepage-subpages' => 'Tüm alt sayfaları taşı',
	'pt-deletepage-current' => 'Sayfa adı:',
	'pt-deletepage-subpages' => 'Tüm alt sayfaları sil',
);

/** Tatar (Cyrillic script) (татарча)
 * @author Ильнар
 * @author Рашат Якупов
 */
$messages['tt-cyrl'] = array(
	'pagetranslation' => 'Битләр тәрҗемәсе',
	'tpt-diff-new' => 'Яңа текст',
	'translate-tag-translate-link-desc' => 'Бу битне тәрҗемә итү',
	'tpt-translation-intro' => 'Әлеге бит [[$2]] сәхифәсенең <span class="plainlinks">[$1 тәрҗемәсе булып тора]</span>. Тәрҗемә $3% башкарылган.',
);

/** Central Atlas Tamazight (ⵜⴰⵎⴰⵣⵉⵖⵜ)
 * @author Tifinaghes
 */
$messages['tzm'] = array(
	'pagetranslation' => 'ⵜⴰⵙⵓⵖⵍⵜ ⵏ ⵜⴰⵙⵏⴰ',
	'tpt-template' => 'ⵜⴰⵍⵖⴰ ⵏ ⵜⴰⵙⵏⴰ',
	'tpt-diff-new' => 'ⴰⴹⵔⵉⵙ ⴰⵎⴰⵢⵏⵓ',
	'tpt-select-prioritylangs-reason' => 'ⴰⵙⵔⴰⴳ:',
	'tpt-languages-legend' => 'ⵜⵓⵜⵍⴰⵢⵉⵏ ⵢⴰⴹⵏⵉ:',
	'tpt-aggregategroup-add' => 'ⵔⵏⵓ',
	'tpt-aggregategroup-save' => 'ⵣⵎⵎⴻⵎ',
	'tpt-aggregategroup-new-name' => 'ⴰⵙⵙⴰⵖ:',
	'pt-movepage-current' => 'ⴰⵙⵙⴰⵖ ⵏ ⵖⵉⵍⴰ:',
	'pt-movepage-new' => 'ⴰⵙⵙⴰⵖ ⴰⵎⴰⵢⵏⵓ:',
	'pt-movepage-reason' => 'ⴰⵙⵔⴰⴳ:',
	'pt-deletepage-current' => 'ⴰⵙⵙⴰⵖ ⵏ ⵜⴰⵙⵏⴰ:',
	'pt-deletepage-reason' => 'ⴰⵙⵔⴰⴳ:',
);

/** Uyghur (Arabic script) (ئۇيغۇرچە)
 * @author Sahran
 */
$messages['ug-arab'] = array(
	'pagetranslation' => 'بەت تەرجىمە',
	'tpt-section' => '$1 تەرجىمە بۆلىكى',
	'tpt-section-new' => 'يېڭى تەرجىمە بۆلىكى.
ئاتى: $1',
	'tpt-section-deleted' => '$1 تەرجىمە بۆلىكى',
	'tpt-template' => 'بەت قېلىپى',
	'tpt-templatediff' => 'بەت قېلىپى ئۆزگەردى.',
	'tpt-diff-old' => 'ئالدىنقى تېكست',
	'tpt-diff-new' => 'يېڭى تېكست',
	'tpt-old-pages-title' => 'تەرجىمە قىلىۋاتقان بەتلەر',
	'tpt-other-pages-title' => 'بۇزۇلغان بەتلەر',
	'tpt-discouraged-pages-title' => 'تەۋسىيە قىلىنمايدىغان بەتلەر',
	'tpt-select-prioritylangs' => 'پەش بىلەن ئايرىلغان ئالدىنلىق تىل تىزىمى كودى:',
	'tpt-select-prioritylangs-force' => 'ئالدىنلىق تىلدىن باشقا تىلغا تەرجىمە قىلىشنىڭ ئالدىنى ئالىدۇ', # Fuzzy
	'tpt-select-prioritylangs-reason' => 'سەۋەب:',
	'tpt-sections-prioritylangs' => 'ئالدىنلىق تىل',
	'tpt-rev-mark' => 'تەرجىمە بەلگىسى',
	'tpt-rev-unmark' => 'تەرجىمىدىن چىقىرىۋەت',
	'tpt-rev-discourage' => 'توسالغۇ',
	'tpt-rev-encourage' => 'ئەسلىگە كەلتۈر',
	'tpt-rev-unmark-tooltip' => 'تەرجىمىدىن بۇ بەتنى چىقىرىۋەت',
	'tpt-rev-discourage-tooltip' => 'بۇ بەتنى يەنىمۇ ئىلگىرىلەپ تەرجىمە قىلىشتىكى توسالغۇ',
	'tpt-rev-encourage-tooltip' => 'بۇ بەتنى ئادەتتىكى تەرجىمە ھالىتىگە ئەسلىگە كەلتۈرىدۇ.',
	'translate-tag-translate-link-desc' => 'بۇ بەتنى تەرجىمە قىل',
	'translate-tag-markthis' => 'تەرجىمە ئۈچۈن بۇ بەتكە بەلگە سال',
	'tpt-languages-legend' => 'باشقا تىل',
	'tpt-discouraged-language-reason' => 'سەۋەپ: $1',
	'tpt-priority-languages' => 'تەرجىمە باشقۇرغۇچى بۇ گۇرۇپپا ئالدىن تەرجىمە قىلىدىغان تىلنى $1 غا تەڭشىدى.',
	'tpt-render-summary' => 'ئەسلى بەت بىلەن ماسلىشىدىغان يېڭى نەشرىگە يېڭىلاۋاتىدۇ',
	'tpt-download-page' => 'تەرجىمىسى بار بەتنى چىقار',
	'aggregategroups' => 'توپلانما گۇرۇپپا',
	'tpt-aggregategroup-add' => 'قوش',
	'tpt-aggregategroup-save' => 'ساقلا',
	'tpt-aggregategroup-add-new' => 'يېڭى بىر توپلانما گۇرۇپپا قوش',
	'tpt-aggregategroup-new-name' => 'ئاتى:',
	'tpt-aggregategroup-new-description' => 'چۈشەندۈرۈش (تاللاشچان):',
	'tpt-aggregategroup-remove-confirm' => 'راستلا بۇ توپلانما گۇرۇپپىنى ئۆچۈرەمسىز؟',
	'tpt-aggregategroup-invalid-group' => 'گۇرۇپپا مەۋجۇت ئەمەس',
	'pt-movepage-list-pages' => 'يۆتكەيدىغان بەتلەرنىڭ تىزىمى',
	'pt-movepage-list-translation' => 'تەرجىمە قىلىدىغان بەتلەر', # Fuzzy
	'pt-movepage-list-section' => 'بۆلەك بەتلەر', # Fuzzy
	'pt-movepage-list-other' => 'باشقا تارماق بەتلەر', # Fuzzy
	'pt-movepage-list-count' => 'جەمئى {{PLURAL:$1|بەت|بەت}} يۆتكىدى.',
	'pt-movepage-legend' => 'تەرجىمە قىلغىلى بولىدىغان بەتنى يۆتكە',
	'pt-movepage-current' => 'نۆۋەتتىكى ئاتى:',
	'pt-movepage-new' => 'يېڭى ئات:',
	'pt-movepage-reason' => 'سەۋەب:',
	'pt-movepage-subpages' => 'ھەممە تارماق بەتنى يۆتكە',
	'pt-movepage-action-check' => 'يۆتكەشچانلىقىنى تەكشۈر',
	'pt-movepage-action-perform' => 'يۆتكەشنى جەزملە',
	'pt-movepage-action-other' => 'نىشاننى ئۆزگەرت',
	'pt-deletepage-lang-title' => 'تەرجىمە بەت "$1" نى ئۆچۈرىدۇ.',
	'pt-deletepage-full-title' => 'تەرجىمە قىلغىلى بولىدىغان بەت $1 نى ئۆچۈرىدۇ.',
	'pt-deletepage-invalid-title' => 'بەلگىلەنگەن بەت ئىناۋەتلىك ئەمەس.',
	'pt-deletepage-invalid-text' => 'بەلگىلەنگەن بەت تەرجىمە قىلغىلى بولىدىغان بەت بولمىسىمۇ ئۇنى تەرجىمە قىلىدۇ.', # Fuzzy
	'pt-deletepage-action-check' => 'ئۆچۈرىدىغان تىزىم بەتلەر',
	'pt-deletepage-action-perform' => 'ئۆچۈر',
	'pt-deletepage-action-other' => 'نىشاننى ئۆزگەرت',
	'pt-deletepage-lang-legend' => 'تەرجىمە بەتنى ئۆچۈر',
	'pt-deletepage-full-legend' => 'تەرجىمە قىلغىلى بولىدىغان بەتنى ئۆچۈر',
	'pt-deletepage-any-legend' => 'تەرجىمە قىلغىلى بولىدىغان بەتنى ئۆچۈر ياكى تەرجىمە قىلغىلى بولىدىغان بەتنىڭ تەرجىمىسى', # Fuzzy
	'pt-deletepage-current' => 'بەت ئاتى:',
	'pt-deletepage-reason' => 'سەۋەب:',
	'pt-deletepage-subpages' => 'ھەممە تارماق بەتنى ئۆچۈر',
	'pt-deletepage-list-translation' => 'تەرجىمە قىلىدىغان بەتلەر',
	'pt-deletepage-list-other' => 'باشقا تارماق بەتلەر',
);

/** Ukrainian (українська)
 * @author A1
 * @author AS
 * @author Ahonc
 * @author Andriykopanytsia
 * @author Base
 * @author Hypers
 * @author NickK
 * @author Olvin
 * @author Prima klasy4na
 * @author Riwnodennyk
 * @author Ата
 * @author Тест
 */
$messages['uk'] = array(
	'pagetranslation' => 'Переклад сторінок',
	'right-pagetranslation' => 'Позначення версій сторінок для перекладу',
	'action-pagetranslation' => 'керування сторінками, що можна перекладати',
	'tpt-desc' => 'Розширення для перекладу статей',
	'tpt-section' => 'Блок перекладу $1',
	'tpt-section-new' => 'Новий блок перекладу.
Назва: $1',
	'tpt-section-deleted' => 'Блок перекладу $1',
	'tpt-template' => 'Шаблон сторінки',
	'tpt-templatediff' => 'Шаблон сторінки змінений.',
	'tpt-diff-old' => 'Попередній текст',
	'tpt-diff-new' => 'Новий текст',
	'tpt-submit' => 'Позначити цю версію для перекладу',
	'tpt-sections-oldnew' => 'Нові та існуючі блоки перекладу',
	'tpt-sections-deleted' => 'Вилучені блоки перекладу',
	'tpt-sections-template' => 'Шаблон сторінки перекладу',
	'tpt-action-nofuzzy' => 'Не відмічати переклади як застарілі',
	'tpt-badtitle' => 'Зазначена назва сторінки ($1) недопустима',
	'tpt-nosuchpage' => 'Сторінки $1 не існує',
	'tpt-oldrevision' => '$2 не є останньою версією сторінки [[$1]].
Тільки останні версії можуть бути відмічені для перекладу.',
	'tpt-notsuitable' => 'Сторінка $1 не підходить для перекладу.
Переконайтеся, що вона містить теги <nowiki><translate></nowiki> і має вірний синтаксис.',
	'tpt-saveok' => 'Сторінка [[$1]] була відмічена для перекладу і містить $2 {{PLURAL:$2|блок перекладу|блоки перекладу|блоків перекладу}}.
Тепер сторінку можна <span class="plainlinks">[$3 перекладати]</span>.',
	'tpt-offer-notify' => 'Ви можете <span class="plainlinks">[$1 повідомити перекладачів]</span> про цю сторінку.',
	'tpt-badsect' => '«$1» не є припустимою назвою для частини перекладів $2.',
	'tpt-showpage-intro' => "Нижче наведені нові, існуючі та видалені одиниці перекладу.
Перед тим, які відмітити цю версію для перекладу, переконайтесь, що зміни в одиницях перекладу будуть мінімальними, щоб уникнути необов'язкової роботи для перекладачів.",
	'tpt-mark-summary' => 'Позначено цю версію для перекладу',
	'tpt-edit-failed' => 'Не вдалося оновити сторінку: $1',
	'tpt-duplicate' => 'Переклад елементу із назвою $1 вжито більше одного разу.',
	'tpt-already-marked' => 'Остання версія цієї сторінки вже була відмічена для перекладу.',
	'tpt-unmarked' => 'Сторінка $1 більше не відмічена для перекладу.',
	'tpt-list-nopages' => 'Немає сторінок, відмічених для перекладу, або готових бути відміченими для перекладу.',
	'tpt-new-pages-title' => 'Сторінки, запропоновані для перекладу',
	'tpt-old-pages-title' => 'Сторінки в процесі перекладу',
	'tpt-other-pages-title' => 'Пошкоджені сторінки',
	'tpt-discouraged-pages-title' => 'Відключені сторінки',
	'tpt-new-pages' => '{{PLURAL:$1|Ця сторінка містить|Ці сторінки містять}} текст з тегами перекладу, але жодна з версій {{PLURAL:$1|цієї сторінки|цих сторінок}} не відмічена для перекладу.',
	'tpt-old-pages' => 'Деякі версії {{PLURAL:$1|цієї сторінки|цих сторінок}} були відмічені для перекладу.',
	'tpt-other-pages' => '{{PLURAL:$1|Стара версія цієї сторінки відмічена|Старі версії цих сторінок відмічені}} для перекладу,
але {{PLURAL:$1|остання версія не може бути відмічена|останні версії не можуть бути відмічені}} для перекладу.',
	'tpt-discouraged-pages' => 'Подальший переклад {{PLURAL:$1|цієї сторінки|цих сторінок}} припинено.',
	'tpt-select-prioritylangs' => 'Перелік кодів пріоритетних мов (відокремлюються комою)',
	'tpt-select-prioritylangs-force' => 'Запобігати переклад іншими мовами, крім пріоритетних',
	'tpt-select-prioritylangs-reason' => 'Причина:',
	'tpt-sections-prioritylangs' => 'Пріоритет мов',
	'tpt-rev-mark' => 'позначити для перекладу',
	'tpt-rev-unmark' => 'вилучити з перекладу',
	'tpt-rev-discourage' => 'виключити',
	'tpt-rev-encourage' => 'відновити',
	'tpt-rev-mark-tooltip' => 'Відзначити останню версію цієї сторінки для перекладу.',
	'tpt-rev-unmark-tooltip' => 'Прибрати цю сторінку з перекладу',
	'tpt-rev-discourage-tooltip' => 'Запобігти подальшим перекладам цієї сторінки',
	'tpt-rev-encourage-tooltip' => 'Відновити цю сторінку для звичайного перекладу',
	'translate-tag-translate-link-desc' => 'Перекласти цю сторінку',
	'translate-tag-markthis' => 'Позначити цю сторінку для перекладу',
	'translate-tag-markthisagain' => 'На цій сторінці було здійснено <span class="plainlinks">[$1 змін]</span> з моменту, коли ця сторінка була востаннє <span class="plainlinks">[$2 відмічена до перекладу]</span>.',
	'translate-tag-hasnew' => 'На цій сторінці було здійснено <span class="plainlinks">[$1 зміни]</span>, які не відмічені для перекладу.',
	'tpt-translation-intro' => 'Ця сторінка є <span class="plainlinks">[$1 перекладом]</span> сторінки [[$2]]. Переклад виконано на $3%.',
	'tpt-languages-legend' => 'Інші мови:',
	'tpt-languages-zero' => 'Розпочати переклад цією мовою',
	'tpt-tab-translate' => 'Перекласти',
	'tpt-target-page' => 'Ця сторінка не може бути оновлена вручну.
Це – переклад сторінки [[$1]] і його можна оновити за допомогою [$2 засобу перекладу].',
	'tpt-unknown-page' => 'Цей простір імен зарезервовано для перекладів текстів сторінок.
Сторінка, яку ви намагаєтесь редагувати, скоріше за все, не відповідає жодній сторінці, відміченій для перекладу.',
	'tpt-translation-restricted' => 'Адміністратор перекладу заборонив переклад цієї сторінки такою мовою.

Причина: $1',
	'tpt-discouraged-language-force' => "'''Ця сторінка не може бути перекладена мовою $2.'''

Адміністратор перекладу вирішив, що ця сторінка може бути перекладена лише такими мовами: $3",
	'tpt-discouraged-language' => "'''Переклад мовою $2 не є пріоритетним для цієї сторінки.'''

Адміністратор вирішив зосередити зусилля на перекладі такими мовами: $3.",
	'tpt-discouraged-language-reason' => 'Причина:$1',
	'tpt-priority-languages' => 'Адміністратор перекладу визначив для цієї групи пріоритетні мови $1.',
	'tpt-render-summary' => 'Оновлення для відповідності новій версії вихідної сторінки',
	'tpt-download-page' => 'Експортувати сторінку з перекладами',
	'aggregategroups' => 'Загальні групи',
	'tpt-aggregategroup-add' => 'Додати',
	'tpt-aggregategroup-save' => 'Зберегти',
	'tpt-aggregategroup-add-new' => 'Додати нову загальну групу',
	'tpt-aggregategroup-new-name' => 'Назва:',
	'tpt-aggregategroup-new-description' => "Опис (необов'язково):",
	'tpt-aggregategroup-remove-confirm' => 'Ви дійсно бажаєте видалити цю загальну групу?',
	'tpt-aggregategroup-invalid-group' => 'Група не існує',
	'pt-parse-open' => 'Незбалансований тег &lt;translate>.
Шаблон перекладу: <pre>$1</pre>',
	'pt-parse-close' => 'Незбалансований тег &lt;/translate>.
Шаблон перекладу: <pre>$1</pre>',
	'pt-parse-nested' => 'Вкладати одну одиницю перекладу &lt;translate> в іншу не допускається.
Текст тегу: <pre>$1</pre>',
	'pt-shake-multiple' => 'Декілька маркерів одиниці перекладу для однієї одиниці.
Текст одиниці перекладу: <pre>$1</pre>',
	'pt-shake-position' => 'Маркери одиниці перекладу в неочікуваному місці.
Текст одиниці перекладу: <pre>$1</pre>',
	'pt-shake-empty' => 'Порожня одиниця перекладу під маркером "$1".',
	'log-description-pagetranslation' => "Журнал для дій, пов'язаних з системою перекладу сторінок.",
	'log-name-pagetranslation' => 'Журнал перекладу сторінок',
	'logentry-pagetranslation-mark' => '$1 {{GENDER:$2|позначив|позначила}} $3 для перекладу',
	'logentry-pagetranslation-unmark' => '$1 {{GENDER:$2|вилучив|вилучила}} $3 з перекладу',
	'logentry-pagetranslation-moveok' => '$1 {{GENDER:$2|здійснив|здійснила}} перейменування перекладабельної сторінки $3 на $4',
	'logentry-pagetranslation-movenok' => '$1 {{GENDER:$2|зіштовхнувся|зіштовхнулася}} із проблемою під час перейменування сторінки $3 на $4',
	'logentry-pagetranslation-deletefok' => '$1 {{GENDER:$2|здійснив|здійснила}} вилучення перекладабельної сторінки $3',
	'logentry-pagetranslation-deletefnok' => '$1 {{GENDER:$2|не зміг|не змогла}} вилучити $3, що належить до перекладабельної сторінки $4',
	'logentry-pagetranslation-deletelok' => '$1 {{GENDER:$2|здійснив|здійснила}} вилучення сторінки-перекладу $3',
	'logentry-pagetranslation-deletelnok' => '$1 {{GENDER:$2|не зміг|не змогла}} вилучити $3, що належить до сторінки-перекладу $4',
	'logentry-pagetranslation-encourage' => '$1 {{GENDER:$2|дозволив|дозволила}} переклад $3',
	'logentry-pagetranslation-discourage' => '$1 {{GENDER:$2|заборонив|заборонила}} переклад $3',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1 {{GENDER:$2|вилучив|вилучила}} пріоритетні мови з перекладабельної сторінки $3',
	'logentry-pagetranslation-prioritylanguages' => '$1 {{GENDER:$2|встановив|встановила}} пріоритетні мови для перекладабельної сторінки $3: $5',
	'logentry-pagetranslation-prioritylanguages-force' => '$1 {{GENDER:$2|обмежив|обмежила}} мови для перекладабельної сторінки $3 до $5',
	'logentry-pagetranslation-associate' => '$1 {{GENDER:$2|додав|додала}} перекладабельну сторінку $3 до агрегованої групи $4',
	'logentry-pagetranslation-dissociate' => '$1 {{GENDER:$2|вилучив|вилучила}} перекладабельну сторінку $3 з агрегованої групи $4',
	'pt-movepage-title' => 'Перемістити сторінку $1, доступну для перекладу',
	'pt-movepage-blockers' => 'Сторінка перекладу не може бути перейменована через {{PLURAL:$1|таку помилку|такі помилки}}:',
	'pt-movepage-block-base-exists' => 'Перекладабельна цільова сторінка «[[:$1]]» вже існує.',
	'pt-movepage-block-base-invalid' => 'Недопустима назва для основної кінцевої перекладної сторінки.',
	'pt-movepage-block-tp-exists' => 'Переклад кінцевої сторінки [[:$2]] вже існує.',
	'pt-movepage-block-tp-invalid' => 'Назва перекладу кінцевої сторінки [[:$1]] буде неправильною (можливо, занадто довга?).',
	'pt-movepage-block-section-exists' => 'Цільова сторінка "[[:$2]]" для одиниці перекладу уже існує.',
	'pt-movepage-block-section-invalid' => 'Назва цільової сторінки "[[:$1]]" для одиниці перекладу буде неправильною (можливо, занадто довга?).',
	'pt-movepage-block-subpage-exists' => 'Кінцева підсторінка [[:$2]] вже існує.',
	'pt-movepage-block-subpage-invalid' => 'Назва кінцевої підсторінки [[:$1]] буде неправильною (можливо, занадто довга?).',
	'pt-movepage-list-pages' => 'Список сторінок для перейменування',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Сторінка|Сторінки}} перекладу',
	'pt-movepage-list-section' => '{{PLURAL:$1|Сторінка|Сторінки}} одиниці перекладу',
	'pt-movepage-list-other' => '{{PLURAL:$1|Інша підсторінка|Інші підсторінки}}',
	'pt-movepage-list-count' => 'Усього перемістити $1 {{PLURAL:$1|сторінку|сторінки|сторінок}}.',
	'pt-movepage-legend' => 'Перемістити сторінку, доступну для перекладу',
	'pt-movepage-current' => "Поточне ім'я:",
	'pt-movepage-new' => 'Нова назва:',
	'pt-movepage-reason' => 'Причина:',
	'pt-movepage-subpages' => 'Перемістити всі підсторінки',
	'pt-movepage-action-check' => 'Перевірити, чи можливе переміщення',
	'pt-movepage-action-perform' => 'Виконати переміщення',
	'pt-movepage-action-other' => 'Змінити ціль',
	'pt-movepage-intro' => 'Ця службова сторінка дозволяє перейменовувати сторінки, позначені для перекладу.
Дія не буде миттєвою, оскільки потрібно перейменувати багато сторінок.
Під час перейменування сторінок взаємодіяти з ними неможливо.
Помилки буде записано в [[Special:Log/pagetranslation|журналі перекладу сторінок]] і їх потрібно буде виправити вручну.',
	'pt-movepage-logreason' => 'Частина сторінки, що перекладається, $1.',
	'pt-movepage-started' => 'Основна сторінка тепер переміщена.
Будь ласка, перевірте [[Special:Log/pagetranslation|журнал перекладу сторінок]] на наявність помилок і повідомлення про завершення.',
	'pt-locked-page' => 'Ця сторінка заблокована, оскільки в даний момент відбувається переміщення сторінки, що перекладається.',
	'pt-deletepage-lang-title' => 'Вилучення сторінки перекладу  $1.',
	'pt-deletepage-full-title' => 'Видалення сторінки доступної для перекладу  $1 .',
	'pt-deletepage-invalid-title' => 'Зазначена сторінка є недійсною.',
	'pt-deletepage-invalid-text' => 'Зазначена сторінка недоступна для перекладу і не є перекладом.',
	'pt-deletepage-action-check' => 'Список сторінок, які будуть вилучені',
	'pt-deletepage-action-perform' => 'Виконати вилучення',
	'pt-deletepage-action-other' => 'Змінити ціль',
	'pt-deletepage-lang-legend' => 'Вилучити сторінку перекладу',
	'pt-deletepage-full-legend' => 'Вилучити сторінку перекладу',
	'pt-deletepage-any-legend' => 'Вилучити сторінку для перекладу або її переклад',
	'pt-deletepage-current' => 'Назва сторінки:',
	'pt-deletepage-reason' => 'Причина:',
	'pt-deletepage-subpages' => 'Вилучити всі підсторінки',
	'pt-deletepage-list-pages' => 'Список сторінок для вилучення',
	'pt-deletepage-list-translation' => 'Сторінки перекладу',
	'pt-deletepage-list-section' => 'Сторінки одиниці перекладу',
	'pt-deletepage-list-other' => 'Інші підсторінки',
	'pt-deletepage-list-count' => 'Усього вилучити $1 {{PLURAL:$1|сторінку|сторінки|сторінок}}.',
	'pt-deletepage-full-logreason' => 'Частина сторінки для перекладу, $1.',
	'pt-deletepage-lang-logreason' => 'Частина сторінки перекладу $1.',
	'pt-deletepage-started' => 'Будь ласка, перевірте [[Special:Log/pagetranslation|журнал перекладу сторінок]] на наявність помилок і повідомлення про завершення.',
	'pt-deletepage-intro' => 'Ця службова сторінка дозволяє Вам вилучати сторінки, призначені для перекладу, разом з перекладами або ж вилучати переклади визначеною мовою.
Ця дія не буде миттєвою, бо потребуватиме вилучення багатьох залежних сторінок.
Якщо вилучення буде невдалим, його буде записано в [[Special:Log/pagetranslation|журналі перекладу]] і такі випадки потрібно буде усунути вручну.',
);

/** Urdu (اردو)
 * @author පසිඳු කාවින්ද
 */
$messages['ur'] = array(
	'pagetranslation' => 'صفحہ ترجمہ',
	'tpt-template' => 'صفحہ کے سانچے',
	'tpt-diff-old' => 'پچھلے ٹیکسٹ',
	'tpt-diff-new' => 'نئے متن',
	'tpt-sections-template' => 'ترجمہ صفحہ سانچے',
	'tpt-action-nofuzzy' => 'ترجمہ باطل نہیں ہوتا',
	'tpt-new-pages-title' => 'صفحات کے ترجمہ کے لئے رشتہ آيا',
	'tpt-old-pages-title' => 'ترجمہ میں صفحات',
	'tpt-other-pages-title' => 'ٹوٹ کے صفحات',
	'tpt-select-prioritylangs-reason' => 'وجہ:',
	'tpt-sections-prioritylangs' => 'ترجیح کی زبانیں',
	'tpt-rev-mark' => 'ترجمہ کے لئے نشان زد کریں',
	'tpt-rev-unmark' => 'ترجمہ سے حذف کریں',
	'tpt-rev-encourage' => 'بحال',
	'tpt-rev-mark-tooltip' => 'تازہ ترین ورژن کے ترجمہ کے لئے اس صفحے نشان زد کریں.',
	'tpt-rev-unmark-tooltip' => 'اس صفحے کو ترجمہ سے حذف کریں ۔',
	'tpt-rev-encourage-tooltip' => 'عمومی ترجمہ کرنے کے لئے اس صفحے کو بحال.',
	'translate-tag-translate-link-desc' => 'اس صفحہ کا ترجمہ',
	'translate-tag-markthis' => 'ترجمہ کے لئے اس صفحے نشان زد کریں',
	'tpt-languages-zero' => 'اس زبان کے لئے ترجمہ شروع',
	'tpt-download-page' => 'ترجمے کے ساتھ اس صفحے کی برآمد',
	'tpt-aggregategroup-add' => 'شامل کریں',
	'tpt-aggregategroup-save' => 'محفوظ کریں',
	'tpt-aggregategroup-add-new' => 'ایک نیا میرا گروپ شامل کریں',
	'tpt-aggregategroup-new-name' => 'نام:',
	'tpt-aggregategroup-invalid-group' => 'گروپ موجود نہیں',
	'log-name-pagetranslation' => 'صفحہ ترجمہ لاگ ان کریں',
	'pt-movepage-list-pages' => 'منتقل کرنے کے لئے صفحات کی فہرست',
	'pt-movepage-list-translation' => 'ترجمہ صفحات', # Fuzzy
	'pt-movepage-list-section' => 'سیکشن کے صفحات', # Fuzzy
	'pt-movepage-legend' => 'ترجمہ صفحہ منتقل',
	'pt-movepage-current' => 'موجودہ نام:',
	'pt-movepage-new' => 'نیا نام:',
	'pt-movepage-reason' => 'وجہ:',
	'pt-movepage-action-perform' => 'اقدام کرتے ہیں',
	'pt-movepage-action-other' => 'تبدیلی کا ہدف',
	'pt-deletepage-action-check' => 'فہرست صفحات کو حذف کیا کرنے کے لئے',
	'pt-deletepage-action-perform' => 'خارج کرتے ہیں',
	'pt-deletepage-action-other' => 'تبدیلی کا ہدف',
	'pt-deletepage-lang-legend' => 'ترجمہ صفحہ کو خارج',
	'pt-deletepage-full-legend' => 'ترجمہ صفحہ کو خارج',
	'pt-deletepage-current' => 'صفحہ کا نام:',
	'pt-deletepage-reason' => 'وجہ:',
	'pt-deletepage-list-pages' => 'خارج کرنے کے لئے صفحات کی فہرست',
	'pt-deletepage-list-translation' => 'ترجمہ صفحات',
	'pt-deletepage-list-section' => 'سیکشن کے صفحات', # Fuzzy
);

/** Uzbek (oʻzbekcha)
 * @author CoderSI
 */
$messages['uz'] = array(
	'aggregategroups' => 'Agregat guruhlar',
	'log-name-pagetranslation' => 'Sahifalarni tarjima qilish qaydlari',
);

/** vèneto (vèneto)
 * @author Candalua
 */
$messages['vec'] = array(
	'translate-tag-translate-link-desc' => 'Tradusi sta pagina',
);

/** Veps (vepsän kel’)
 * @author Игорь Бродский
 */
$messages['vep'] = array(
	'pagetranslation' => 'Lehtpoliden kändmine',
	'right-pagetranslation' => 'Znamoita lehpoliden versijad kändmižen täht',
	'tpt-desc' => 'Ližaprogramm lehtpoliden südäimištod kätes.',
	'tpt-section' => 'Kändmižühtnik $1',
	'tpt-section-new' => "Uz' kändmižühtnik. Nimi: $1",
	'tpt-section-deleted' => 'Kändmižühtnik $1',
	'tpt-template' => 'Lehtpolen šablon',
	'tpt-templatediff' => 'Nece lehtpolen šablon om toižetanus.',
	'tpt-diff-old' => 'Edeline tekst',
	'tpt-diff-new' => "Uz' tekst",
	'tpt-submit' => 'Znamoita nece versii kändmižen täht.',
	'tpt-sections-oldnew' => 'Uded da olijad kändmižühtnikad',
	'tpt-sections-template' => 'Kändmižen lehtpolen šablon',
	'tpt-nosuchpage' => 'Ei ole mugošt lehtpol\'t: "$1".',
	'tpt-new-pages-title' => 'Lehtpoled kändmižen täht',
	'tpt-old-pages-title' => "Lehtpoled, kudambad kätas nügüd'",
	'tpt-other-pages-title' => 'Traudüd lehtpoled',
	'tpt-rev-encourage' => 'endištada',
	'tpt-rev-mark-tooltip' => "Znamoita necen lehtpolen jäl'gmäine versii kändmižen täht",
	'translate-tag-translate-link-desc' => "Käta nece lehtpol'",
	'translate-tag-markthis' => "Znamoita nece lehtpol' kändmižen täht.",
	'tpt-languages-legend' => 'Toižed keled:',
	'tpt-download-page' => "Eksportiruida lehtpol' kändusidenke",
	'pt-movepage-list-other' => 'Toižed alalehtpoled', # Fuzzy
	'pt-movepage-current' => 'Olii nimi:',
	'pt-movepage-new' => "Uz' nimi:",
	'pt-movepage-reason' => 'Sü:',
	'pt-movepage-action-perform' => 'Udesnimitada',
	'pt-movepage-action-other' => 'Vajehtada met',
	'pt-deletepage-action-other' => 'Vajehtada met',
	'pt-deletepage-current' => 'Lehtpolen nimi:',
	'pt-deletepage-reason' => 'Sü:',
	'pt-deletepage-list-other' => 'Toižed alalehtpoled',
);

/** Vietnamese (Tiếng Việt)
 * @author Minh Nguyen
 * @author Trần Nguyễn Minh Huy
 * @author Vinhtantran
 */
$messages['vi'] = array(
	'pagetranslation' => 'Biên dịch trang',
	'right-pagetranslation' => 'Đánh dấu các phiên bản của trang là cần dịch',
	'action-pagetranslation' => 'quản lý các trang dịch được',
	'tpt-desc' => 'Phần mở rộng để dịch trang nội dung',
	'tpt-section' => 'Đơn vị dịch thuật $1',
	'tpt-section-new' => 'Đơn vị dịch thuật mới.
Tên: $1',
	'tpt-section-deleted' => 'Đơn vị dịch thuật $1',
	'tpt-template' => 'Mẫu trang',
	'tpt-templatediff' => 'Mẫu trang đã thay đổi.',
	'tpt-diff-old' => 'Văn bản trước',
	'tpt-diff-new' => 'Văn bản mới',
	'tpt-submit' => 'Đánh dấu phiên bản này là cần dịch',
	'tpt-sections-oldnew' => 'Các đơn vị dịch thuật mới và hiện có',
	'tpt-sections-deleted' => 'Các đơn vị dịch thuật đã bị xóa',
	'tpt-sections-template' => 'Bản mẫu trang dịch',
	'tpt-action-nofuzzy' => 'Đừng làm mất hiệu lực bản dịch',
	'tpt-badtitle' => 'Tên trang cung cấp ($1) không phải là tên đúng',
	'tpt-nosuchpage' => 'Trang $1 không tồn tại',
	'tpt-oldrevision' => '$2 không phải là phiên bản mới của trang [[$1]]/
Chỉ có các phiên bản mới nhất mới có thể đánh dấu cần dịch được.',
	'tpt-notsuitable' => 'Trang $1 không phù hợp để dịch thuật.
Hãy đảm bảo là nó có thẻ <nowiki><translate></nowiki> và có cú pháp đúng.',
	'tpt-saveok' => 'Trang [[$1]] đã được đánh dấu chờ dịch với $2 đơn vị dịch thuật.
Bạn có thể <span class="plainlinks">[$3 dịch]</span> trang ngay bây giờ.',
	'tpt-offer-notify' => 'Bạn có thể <span class="plainlinks">[$1 báo các biên dịch viên]</span> về trang này.',
	'tpt-badsect' => '“$1” không phải là tên hợp lệ cho đơn vị dịch thuật $2.',
	'tpt-showpage-intro' => 'Dưới đây là các đơn vị dịch thuật mới, đang tồn tại, hoặc đã bị xóa.
Trước khi đánh dấu phiên bản này chờ dịch, hãy kiểm tra những thay đổi tại các đơn vị dịch thuật đã được thu gọn lại để tránh công việc không cần thiết cho biên dịch viên chưa.',
	'tpt-mark-summary' => 'Đánh dấu phiên bản này là cần dịch',
	'tpt-edit-failed' => 'Không thể cập nhật trang: $1',
	'tpt-duplicate' => 'Tên đơn vị dịch $1 được sử dụng hơn một lần.',
	'tpt-already-marked' => 'Phiên bản mới nhất của trang này đã được đánh dấu cần dịch rồi.',
	'tpt-unmarked' => 'Trang $1 không còn đánh dấu là cần dịch.',
	'tpt-list-nopages' => 'Chưa có trang này được đánh dấu cần dịch hoặc chưa sẵn sàng để được đánh dấu cần dịch.',
	'tpt-new-pages-title' => 'Các trang cần dịch',
	'tpt-old-pages-title' => 'Các trang đang được dịch',
	'tpt-other-pages-title' => 'Các trang hỏng',
	'tpt-discouraged-pages-title' => 'Các trang được khuyên để yên',
	'tpt-new-pages' => '{{PLURAL:$1|Trang|Các trang}} này có chứa văn bản có thẻ cần dịch, nhưng không có phiên bản nào của {{PLURAL:$1|nó|chúng}} được đánh dấu cần dịch.',
	'tpt-old-pages' => 'Một phiên bản nào đó của {{PLURAL:$1||các}} trang này đã được đánh dấu cần dịch.',
	'tpt-other-pages' => '{{PLURAL:$1|Một|Những}} phiên bản trước của trang này được đánh dấu là cần dịch, nhưng {{PLURAL:$1|phiên bản|các phiên bản}} gần đây nhất không thể được đánh dấu là cần dịch.',
	'tpt-discouraged-pages' => '{{PLURAL:$1|Trang|Các trang}} này đã được khuyên để yên không cần dịch tiếp.',
	'tpt-select-prioritylangs' => 'Danh sách các mã ngôn ngữ quan trọng phân tách bằng dấu phẩy:',
	'tpt-select-prioritylangs-force' => 'Không cho phép dịch ra các ngôn ngữ không quan trọng',
	'tpt-select-prioritylangs-reason' => 'Lý do:',
	'tpt-sections-prioritylangs' => 'Ngôn ngữ quan trọng',
	'tpt-rev-mark' => 'đánh dấu cần dịch',
	'tpt-rev-unmark' => 'bỏ dấu cần dịch',
	'tpt-rev-discourage' => 'khuyên để yên',
	'tpt-rev-encourage' => 'khuyên dịch tiếp',
	'tpt-rev-mark-tooltip' => 'Đánh dấu phiên bản mới nhất của trang này là cần dịch.',
	'tpt-rev-unmark-tooltip' => 'Bỏ dấu cần dịch khỏi trang này.',
	'tpt-rev-discourage-tooltip' => 'Khuyên để yên bản dịch hiện hành của trang này.',
	'tpt-rev-encourage-tooltip' => 'Khuyên tiếp tục dịch trang này bình thường.',
	'translate-tag-translate-link-desc' => 'Dịch trang này',
	'translate-tag-markthis' => 'Đánh dấu trang này là cần dịch',
	'translate-tag-markthisagain' => 'Trang này có <span class="plainlinks">[$1 thay đổi]</span> từ khi nó được <span class="plainlinks">[$2 đánh dấu cần dịch]</span> lần cuối.',
	'translate-tag-hasnew' => 'Trang này có <span class="plainlinks">[$1 thay đổi]</span> chưa được đánh dấu cần dịch.',
	'tpt-translation-intro' => 'Trang này là một <span class="plainlinks">[$1 bản dịch]</span> của trang [[$2]] và bản dịch đã hoàn thành $3%.',
	'tpt-languages-legend' => 'Ngôn ngữ khác:',
	'tpt-languages-zero' => 'Bắt đầu bản dịch trong ngôn ngữ này',
	'tpt-tab-translate' => 'Biên dịch',
	'tpt-target-page' => 'Trang này không thể cập nhật bằng tay.
Nó là một bản dịch của trang [[$1]] và có thể cập nhật bản dịch bằng cách sử dụng [$2 công cụ dịch thuật].',
	'tpt-unknown-page' => 'Không gian tên này được dành cho các bản dịch trang nội dung.
Trang bạn muốn sửa đổi dường như không tương ứng với trang nào đã được đánh dấu cần dịch.',
	'tpt-translation-restricted' => 'Một người quản lý biên dịch không cho phép dịch trang ra ngôn ngữ này.

Lý do: $1',
	'tpt-discouraged-language-force' => 'Một người quản lý biên dịch chỉ cho phép biên dịch trang ra một số ngôn ngữ, không bao gồm ngôn ngữ này.

Lý do: $1',
	'tpt-discouraged-language' => 'Ngôn ngữ này không phải là một trong những ngôn ngữ quan trọng theo người quản lý biên dịch của trang này.

Lý do: $1',
	'tpt-discouraged-language-reason' => 'Lý do: $1',
	'tpt-priority-languages' => 'Một người quản lý biên dịch đã đặt các ngôn ngữ quan trọng của nhóm này là $1.',
	'tpt-render-summary' => 'Cập nhật đến phiên bản mới của trang nguồn',
	'tpt-download-page' => 'Xuất trang cùng các bản dịch',
	'aggregategroups' => 'Nhóm tập hợp',
	'tpt-aggregategroup-add' => 'Thêm',
	'tpt-aggregategroup-save' => 'Lưu',
	'tpt-aggregategroup-add-new' => 'Thêm nhóm tập hợp mới',
	'tpt-aggregategroup-new-name' => 'Tên:',
	'tpt-aggregategroup-new-description' => 'Miêu tả (tùy chọn):',
	'tpt-aggregategroup-remove-confirm' => 'Bạn có chắc muốn xóa nhóm hợp này?',
	'tpt-aggregategroup-invalid-group' => 'Nhóm không tồn tại',
	'pt-parse-open' => 'Thẻ &lt;translate> không đều.
Bản mẫu thông dịch: <pre>$1</pre>',
	'pt-parse-close' => 'Thẻ &lt;/translate> không đều.
Bản mẫu thông dịch: <pre>$1</pre>',
	'pt-parse-nested' => 'Không được phép bỏ đơn vị dịch thuật &lt;translate> trong đơn vị dịch thuật khác.
Văn bản thẻ: <pre>$1</pre>',
	'pt-shake-multiple' => 'Nhiều dấu hiệu cho một đơn vị dịch thuật.
Văn bản của đơn vị dịch thuật: <pre>$1</pre>',
	'pt-shake-position' => 'Dấu hiệu đơn vị dịch thuật ở vị trí không mong đợi.
Văn bản của đơn vị dịch thuật: <pre>$1</pre>',
	'pt-shake-empty' => 'Dấu hiệu “$1” có đơn vị dịch thuật rỗng.',
	'log-description-pagetranslation' => 'Nhật trình các tác vụ co liên quan đến hệ thống dịch trang',
	'log-name-pagetranslation' => 'Nhật trình dịch trang',
	'logentry-pagetranslation-mark' => '$1 {{GENDER:$2}}đã đánh dấu $3 là cần được dịch',
	'logentry-pagetranslation-unmark' => '$1 {{GENDER:$2}}đã gỡ đánh dấu cần dịch khỏi $3',
	'logentry-pagetranslation-moveok' => '$1 {{GENDER:$2}}đã hoàn thành đổi tên của trang dịch được $3 thành $4',
	'logentry-pagetranslation-movenok' => '$1 {{GENDER:$2}}đã gặp vấn đề trong khi di chuyển $3 đến $4',
	'logentry-pagetranslation-deletefok' => '$1 {{GENDER:$2}}đã hoàn thành xóa trang dịch được $3',
	'logentry-pagetranslation-deletefnok' => '$1 {{GENDER:$2}}đã gặp thất bại khi xóa $3 trực thuộc trang dịch được $4',
	'logentry-pagetranslation-deletelok' => '$1 {{GENDER:$2}}đã hoàn thành xóa trang dịch được $3',
	'logentry-pagetranslation-deletelnok' => '$1 {{GENDER:$2}}đã gặp thất bại khi xóa $3 trực thuộc trang dịch được $4',
	'logentry-pagetranslation-encourage' => '$1 {{GENDER:$2}}đã khuyến khích dịch $3',
	'logentry-pagetranslation-discourage' => '$1 {{GENDER:$2}}đã ngừng khuyến khích dịch $3',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1 {{GENDER:$2}}đã dời các ngôn ngữ ưu tiên khỏi trang dịch được $3',
	'logentry-pagetranslation-prioritylanguages' => '$1 {{GENDER:$2}}đã đặt các ngôn ngữ ưu tiên cho trang dịch được $3–$5.',
	'logentry-pagetranslation-prioritylanguages-force' => '$1 {{GENDER:$2}}đã giới hạn các ngôn ngữ của trang dịch được $3 ra $5',
	'logentry-pagetranslation-associate' => '$1 {{GENDER:$2}}đã thêm trang dịch được $3 và nhóm tập hợp $4',
	'logentry-pagetranslation-dissociate' => '$1 {{GENDER:$2}}đã rút trang dịch được $3 khỏi nhóm tập tin $4',
	'pt-movepage-title' => 'Di chuyển trang dịch được $1',
	'pt-movepage-blockers' => 'Trang dịch được không thể được đổi tên vì {{PLURAL:$1|lỗi|các lỗi}} sau:',
	'pt-movepage-block-base-exists' => 'Bản gốc của trang đích dịch được “[[:$1]]” tồn tại.',
	'pt-movepage-block-base-invalid' => 'Trang đích dịch được có tên không hợp lệ.',
	'pt-movepage-block-tp-exists' => 'Bản dịch của trang đích [[:$2]] tồn tại.',
	'pt-movepage-block-tp-invalid' => 'Bản dịch của trang đích [[:$1]] có tên không hợp lệ (có lẽ dài quá).',
	'pt-movepage-block-section-exists' => 'Trang đích của đơn vị dịch thuật, “[[:$2]]”, đã tồn tại.',
	'pt-movepage-block-section-invalid' => 'Trang đích của đơn vị dịch thuật, “[[:$1]]”, có tên không hợp lệ (có lẽ dài quá).',
	'pt-movepage-block-subpage-exists' => 'Trang con của trang đích “[[:$2]]” đã tồn tại.',
	'pt-movepage-block-subpage-invalid' => 'Trang con của trang đích “[[:$1]]” có tên không hợp lệ (có lẽ dài quá).',
	'pt-movepage-list-pages' => 'Danh sách trang để di chuyển',
	'pt-movepage-list-translation' => '{{PLURAL:$1|Trang|Các trang}} biên dịch',
	'pt-movepage-list-section' => '{{PLURAL:$1|Trang|Các trang}} đơn vị dịch thuật',
	'pt-movepage-list-other' => '{{PLURAL:$1|Trang|Các trang}} con khác',
	'pt-movepage-list-count' => 'Tổng cộng có $1 trang để di chuyển.',
	'pt-movepage-legend' => 'Di chuyển trang dịch được',
	'pt-movepage-current' => 'Tên hiện hành:',
	'pt-movepage-new' => 'Tên mới:',
	'pt-movepage-reason' => 'Lý do:',
	'pt-movepage-subpages' => 'Di chuyển các trang con',
	'pt-movepage-action-check' => 'Kiểm tra có thể di chuyển',
	'pt-movepage-action-perform' => 'Di chuyển',
	'pt-movepage-action-other' => 'Thay đổi trang đích',
	'pt-movepage-intro' => 'Trang đặc biệt này cho phép bạn di chuyển các trang được đánh dấu là cần dịch.
Tác vụ này sẽ không được thực hiện ngay vì cần di chuyển nhiều trang một lúc.
Trong khi các trang đang được di chuyển, không thể tương tác các trang đó.
Những vụ thất bại sẽ được ghi vào [[Special:Log/pagetranslation|nhật trình dịch trang]]; các trang được ảnh hưởng sẽ cần được sửa đổi bằng tay.',
	'pt-movepage-logreason' => 'Một phần của trang dịch được $1.',
	'pt-movepage-started' => 'Trang gốc đã được di chuyển.
Xin hãy kiểm tra những lỗi hay thông điệp kết quả thành công trong [[Special:Log/pagetranslation|nhật trình dịch trang]].',
	'pt-locked-page' => 'Trang này bị khóa vì trang dịch được hiện đang được di chuyển.',
	'pt-deletepage-lang-title' => 'Đang xóa trang dịch $1.',
	'pt-deletepage-full-title' => 'Đang xóa trang dịch được $1.',
	'pt-deletepage-invalid-title' => 'Trang đã chỉ định là không hợp lệ.',
	'pt-deletepage-invalid-text' => 'Trang đã chỉ định không phải là trang dịch được mà cũng không phải là trang biên dịch.',
	'pt-deletepage-action-check' => 'Danh sách các trang sẽ được xóa',
	'pt-deletepage-action-perform' => 'Thực hiện xóa',
	'pt-deletepage-action-other' => 'Thay đổi trang đích',
	'pt-deletepage-lang-legend' => 'Xóa trang dịch',
	'pt-deletepage-full-legend' => 'Xóa trang dịch được',
	'pt-deletepage-any-legend' => 'Xóa trang dịch được hoặc trang biên dịch',
	'pt-deletepage-current' => 'Tên trang:',
	'pt-deletepage-reason' => 'Lý do:',
	'pt-deletepage-subpages' => 'Xóa mọi trang con',
	'pt-deletepage-list-pages' => 'Danh sách các trang sẽ được xóa',
	'pt-deletepage-list-translation' => 'Các trang dịch',
	'pt-deletepage-list-section' => 'Trang đơn vị dịch thuật',
	'pt-deletepage-list-other' => 'Các trang con khác',
	'pt-deletepage-list-count' => 'Tổng cộng $1 trang sẽ được xóa.',
	'pt-deletepage-full-logreason' => 'Một phần của trang dịch được $1.',
	'pt-deletepage-lang-logreason' => 'Một phần của trang dịch $1.',
	'pt-deletepage-started' => 'Xin hãy kiểm tra những lỗi hay thông điệp kết quả thành công trong [[Special:Log/pagetranslation|nhật trình dịch trang]].',
	'pt-deletepage-intro' => 'Trang đặc biệt này cho phép bạn xóa toàn bộ trang dịch được hoặc một trang biên dịch trong một ngôn ngữ nào đó.
Tác vụ xóa sẽ không được thực hiện ngay, bởi vì tất cả mọi trang dựa vào nó cũng sẽ bị xóa.
Các thất bại được ghi vào [[Special:Log/pagetranslation|nhật trình dịch trang]] sẽ cần phải được sửa bằng tay.',
);

/** Volapük (Volapük)
 * @author Smeira
 */
$messages['vo'] = array(
	'translate-tag-translate-link-desc' => 'Tradutön padi at',
);

/** Wu (吴语)
 */
$messages['wuu'] = array(
	'pt-movepage-reason' => '理由：',
);

/** Yiddish (ייִדיש)
 * @author Imre
 * @author פוילישער
 * @author පසිඳු කාවින්ද
 */
$messages['yi'] = array(
	'pagetranslation' => 'בלאט טײַטש',
	'right-pagetranslation' => 'מארקירן ווערסיעס פון בלעטער פאר איבערזעצונג',
	'action-pagetranslation' => 'פֿארוואלטן איבערזעצבאַרע בלעטער',
	'tpt-desc' => 'פארברייטערונג פאר איבערזעצן אינהאלט בלעטער',
	'tpt-section' => 'איבערזעצונג איינהייט $1',
	'tpt-section-new' => 'נײַע איבערזעצונג איינהייט.
נאמען: $1',
	'tpt-section-deleted' => 'איבערזעצונג איינהייט $1',
	'tpt-template' => 'בלאט מוסטער',
	'tpt-templatediff' => 'דער בלאט מוסטער האט זיך געענדערט.',
	'tpt-diff-old' => 'פֿריערדיגער טעקסט',
	'tpt-diff-new' => 'נײַער טעקסט',
	'tpt-submit' => 'מארקירן די ווערסיע פאר איבערזעצונג',
	'tpt-sections-oldnew' => 'נײַע און עקסיסטירנדע איבערזעצונג איינהייטן',
	'tpt-sections-deleted' => 'אויסגעמעקטע איבערזעצונג איינהייטן',
	'tpt-sections-template' => 'איבערזעצונג בלאט מוסטער',
	'tpt-action-nofuzzy' => "נישט פסל'ן איבערזעצונגען",
	'tpt-badtitle' => 'געגעבענער בלאט נאמען ($1) איז נישט קיין גילטיקער טיטל',
	'tpt-nosuchpage' => 'בלאט $1 עקזיסטירט נישט',
	'tpt-oldrevision' => '$2 איז נישט די לעצטע ווערסיע פונעם בלאט [[$1]].
נאר לעצטע ווערסיעס קען מען מארקירן פאר איבערזעצונג.',
	'tpt-notsuitable' => 'בלאט $1 פאסט נישט איבערצוזעצן.
פארזיכערט אז ער האט <nowiki><translate></nowiki> טאַגן און האט א גילטיקן סינטאקס.',
	'tpt-saveok' => 'דער בלאט [[$1]] איז געווארן מארקירט פאר איבערזעצן מיט $2 {{PLURAL:$2|איבערזעצונג אפשניט|איבערזעצונג אפשניטן}}.
דער בלאט קען מען אצינד <span class="plainlinks">[$3 איבערזעצן]</span>.',
	'tpt-badsect' => '"$1" איז נישט קיין גילטיקער נאמען פאר איבערזעצונג איינהייט $2.',
	'tpt-mark-summary' => 'מארקירט די ווערסיע פאר איבערזעצונג',
	'tpt-edit-failed' => 'האט נישט געקענט דערהיינטיקן דעם בלאט: $1',
	'tpt-duplicate' => 'איבערזעצונג אפשניט נאמען $1 געניצט מער ווי איין מאל.',
	'tpt-already-marked' => 'די לעצטע ווערסיע פון דעם בלאט איז שוין געווארן מארקירט איבערצוזעצן.',
	'tpt-unmarked' => 'בלאט $1 מער נישט מארקירט איבערצוזעצן.',
	'tpt-list-nopages' => 'קיין בלעטער נישט מארקירט צום איבערזעצן אדער גרייט צו ווערן מארקירט צום איבערזעצן.',
	'tpt-new-pages-title' => 'בלעטער פארגעשטעלט איבערצוזעצן',
	'tpt-old-pages-title' => 'בלעטער איבערצוזעצן',
	'tpt-other-pages-title' => 'צעבראכענע בלעטער',
	'tpt-discouraged-pages-title' => 'צוריקגעצויגענע בלעטער',
	'tpt-old-pages' => 'א ווערסיע פון {{PLURAL:$1|דעם בלאט איז|די בלעטער זענען}} געווארן מארקירט פאר איבערזעצונג.',
	'tpt-select-prioritylangs-reason' => 'אורזאַך:',
	'tpt-sections-prioritylangs' => 'פריאריזירטע שפראכן',
	'tpt-rev-mark' => 'מארקירן פאר איבערזעצונג',
	'tpt-rev-unmark' => 'אוועקנעמען פון איבערזעצונג',
	'tpt-rev-encourage' => 'אויפֿריכטן',
	'tpt-rev-mark-tooltip' => 'מארקירן די לעצטע ווערזיע פון דעם בלאט פאר איבערזעצן.',
	'tpt-rev-unmark-tooltip' => 'אוועקנעמען דעם בלאט פון איבערזעצן.',
	'tpt-rev-encourage-tooltip' => 'שטעלט צוריק דעם בלאט פאר נארמאלער איבערזעצונג.',
	'translate-tag-translate-link-desc' => 'פֿאַרטײַטשט דעם בלאַט',
	'translate-tag-markthis' => 'מארקירן דעם בלאט פאר איבערזעצונג',
	'translate-tag-markthisagain' => 'דער בלאַט האט <span class="plainlinks">[ $1 ענדערונגען]</span> זינט ער איז לעצט געווארן <span class="plainlinks">[ $2 אנגעצייכנט פֿאַר איבערזעצונג].</span>',
	'translate-tag-hasnew' => 'דער בלאַט אַנטהאַלט  <span class="plainlinks">[ $1 ענדערונגען]</span> וואָס זענען נישט אנגעצייכנט פֿאַר איבערזעצונג.',
	'tpt-translation-intro' => 'דער דאזיקער בלאט איז א <span class="plainlinks">[$1 איבערגעזעצטע ווערסיע]</span> פון דעם בלאט [[$2]] און די איבערזעצונג איז $3% פארענדיקט.',
	'tpt-languages-legend' => 'אנדערע שפראַכן:',
	'tpt-languages-zero' => 'אנהייבן איבערזעצן די דאזיקע שפראך',
	'tpt-target-page' => 'מען קען נישט דערהיינטיקן דעם בלאט מאנועל.
דער בלאט איז אן איבערזעצונג פונעם בלאט [[$1]] און מען קען דערהיינטיקן די איבערזעצונג מיט די [$2 איבערזעצונג געצייג].',
	'tpt-discouraged-language-reason' => 'אורזאך: $1',
	'tpt-download-page' => 'עקספארטירן בלאט מיט איבערזעצונגען',
	'tpt-aggregategroup-add' => 'צולייגן',
	'tpt-aggregategroup-save' => 'אויפֿהיטן',
	'tpt-aggregategroup-new-name' => 'נאָמען:',
	'tpt-aggregategroup-new-description' => 'באשרייבונג (אפציאנאל):',
	'tpt-aggregategroup-remove-confirm' => 'איר זענט זיכער אז איר ווילט אויסמעקן די גרופע?',
	'tpt-aggregategroup-invalid-group' => 'גרופע עקזיסטירט נישט',
	'log-name-pagetranslation' => 'בלאט איבערזעצונג לאגבוך',
	'logentry-pagetranslation-mark' => '$1 {{GENDER:$2|מארקירט}} $3 איבערצוזעצן',
	'logentry-pagetranslation-unmark' => '$1 {{GENDER:$2|אראפגענומען}} $3 פון איבערזעצן',
	'pt-movepage-title' => 'באוועגן איבערזעצבארן בלאט "$1"',
	'pt-movepage-block-base-exists' => 'דער איבערזעצבאר צילבלאט "[[:$1]]" עקזיסטירט.',
	'pt-movepage-block-base-invalid' => 'דער נאמען פונעם איבערזעצבארן צילבלאט איז נישט קיין גילטיקער טיטל.',
	'pt-movepage-block-tp-exists' => 'דער ציל אונטערבלאט "[[:$2]]" עקזיסטירט.',
	'pt-movepage-block-subpage-exists' => 'דער ציל אונטערבלאט "[[:$2]]" עקזיסטירט.',
	'pt-movepage-list-pages' => 'רשימה פון בלעטער צו באַוועגן',
	'pt-movepage-list-translation' => 'טײַטש  {{PLURAL:$1|בלאַט|בלעטער}}',
	'pt-movepage-list-section' => 'איבערזעצונג איינהייט {{PLURAL:$1|בלאַט|בלעטער}}',
	'pt-movepage-list-other' => 'אנדערע אונטער{{PLURAL:$1|בלאַט|בלעטער}}',
	'pt-movepage-list-count' => 'אינגאנצן $1 {{PLURAL:$1|בלאט|בלעטער}} צו באוועגן.',
	'pt-movepage-legend' => 'באוועגן איבערזעצבארן בלאט',
	'pt-movepage-current' => 'אקטועלער נאמען:',
	'pt-movepage-new' => 'נײַער נאָמען:',
	'pt-movepage-reason' => 'אורזאַך:',
	'pt-movepage-subpages' => 'באוועגן אלע אונטערבלעטער',
	'pt-movepage-action-check' => 'קאנטראלירט צי די באוועגונג איז מעגלעך',
	'pt-movepage-action-perform' => 'פֿירט אויס די באוועגונג',
	'pt-movepage-action-other' => 'ענדערט ציל',
	'pt-movepage-logreason' => 'טייל פון איבערזעצבארן בלאט "$1".',
	'pt-deletepage-lang-title' => 'אויסמעקן איבערזעצונג בלאט "$1".',
	'pt-deletepage-invalid-title' => 'דער ספעציפירטער בלאט איז נישט גילטיק.',
	'pt-deletepage-action-check' => 'מאכט א רשימה פון בלעטער צו ווערן אויסגעמעקט',
	'pt-deletepage-action-perform' => 'אויספירן אויסמעקונג',
	'pt-deletepage-action-other' => 'ענדערן ציל',
	'pt-deletepage-lang-legend' => 'אויסמעקן איבערזעצונג בלאט',
	'pt-deletepage-full-legend' => 'אויסמעקן איבערזעצבארן בלאט',
	'pt-deletepage-any-legend' => 'אויסמעקן איבערזעצבארן בלאט אדער איבערזעצונג בלאט',
	'pt-deletepage-current' => 'בלאַט נאָמען:',
	'pt-deletepage-reason' => 'אורזאַך:',
	'pt-deletepage-subpages' => 'אויסמעקן אלע אונטערבלעטער',
	'pt-deletepage-list-pages' => 'רשימה פון בלעטער אויסצומעקן',
	'pt-deletepage-list-translation' => 'איבערזעצונג בלעטער',
	'pt-deletepage-list-section' => 'איבערזעצונג איינהייט בלעטער',
	'pt-deletepage-list-other' => 'אנדערע אונטערבלעטער',
	'pt-deletepage-list-count' => 'אינגאנצן $1 {{PLURAL:$1|בלאט|בלעטער}} אויסצומעקן.',
	'pt-deletepage-full-logreason' => 'טייל פון איבערזעצבארן בלאט $1.',
	'pt-deletepage-lang-logreason' => 'טייל פון איבערזעצונג בלאט $1.',
);

/** Simplified Chinese (中文（简体）‎)
 * @author Anakmalaysia
 * @author Chenxiaoqino
 * @author Dimension
 * @author Gzdavidwong
 * @author Hydra
 * @author Hzy980512
 * @author Li3939108
 * @author Liangent
 * @author Linforest
 * @author Liuxinyu970226
 * @author Mys 721tx
 * @author PhiLiP
 * @author Shirayuki
 * @author Slboat
 * @author Supaiku
 * @author TianyinLee
 * @author Xiaomingyan
 * @author Yfdyh000
 * @author 阿pp
 */
$messages['zh-hans'] = array(
	'pagetranslation' => '页面翻译',
	'right-pagetranslation' => '标记翻译的页面版本',
	'action-pagetranslation' => '管理可翻译页面',
	'tpt-desc' => '用于翻译内容页面的扩展',
	'tpt-section' => '翻译单元$1',
	'tpt-section-new' => '新翻译单元。
名字：$1',
	'tpt-section-deleted' => '翻译单元$1',
	'tpt-template' => '页面模板',
	'tpt-templatediff' => '页面模板已改变。',
	'tpt-diff-old' => '上一版本文字',
	'tpt-diff-new' => '下一版本文字',
	'tpt-submit' => '标记此版本进行翻译',
	'tpt-sections-oldnew' => '新的和现存的翻译单元',
	'tpt-sections-deleted' => '已删除的翻译模块',
	'tpt-sections-template' => '翻译页面模版',
	'tpt-action-nofuzzy' => '不要使翻译作废',
	'tpt-badtitle' => '页面名称 ($1) 不是一个有效的标题',
	'tpt-nosuchpage' => '页面$1 不存在。',
	'tpt-oldrevision' => '$2 不是最新版本的页面 [[$1]]。
只有最新版本可以将标记进行翻译。',
	'tpt-notsuitable' => '页$1不适合翻译。
请确保它具有 <nowiki><translate></nowiki> 标记，并具有有效的语法。',
	'tpt-saveok' => '[[$1]]页面已被标记将进行翻译，一共$2个翻译单位。
本页面现已可以<span class="plainlinks">[$3 翻译]</span>。',
	'tpt-offer-notify' => '您可以<span class="plainlinks">[$1 通告关于此页面的翻译]</span>。',
	'tpt-badsect' => '“$1”对于$2翻译单位不是有效的名称。',
	'tpt-showpage-intro' => '以下列出新创、现存及已删除的翻译单元。
将此版本标记进行翻译之前，请检查来确定该部分极少受修改，以便翻译员避免得到多余的工作。',
	'tpt-mark-summary' => '此版本已被标记将进行翻译',
	'tpt-edit-failed' => '无法更新该页面：$1',
	'tpt-duplicate' => '翻译单位名称$1已被使用超过一次。',
	'tpt-already-marked' => '此页面的最新版本已经已标记进行翻译。',
	'tpt-unmarked' => '$1页不再被标记进行翻译。',
	'tpt-list-nopages' => '没有被标记进行翻译或者准备被标记进行翻译的页面。',
	'tpt-new-pages-title' => '提议翻译的页面',
	'tpt-old-pages-title' => '正在翻译的页面',
	'tpt-other-pages-title' => '损坏的页面',
	'tpt-discouraged-pages-title' => '不建议的页面',
	'tpt-new-pages' => '以下{{PLURAL:$1|此|这些}}页面包含具有翻译标记的文本，
但没有被标记进行翻译的版本。',
	'tpt-old-pages' => '以下{{PLURAL:$1|此|这些}}页面有被标记进行翻译的版本。',
	'tpt-other-pages' => '以下{{PLURAL:$1|此|这些}}页面有旧版本被标记进行翻译，
但最新版本不得标记进行翻译。',
	'tpt-discouraged-pages' => '{{PLURAL:$1|该|这些}}页面不需要继续翻译。',
	'tpt-select-prioritylangs' => '优先语言代码的逗号分隔型列表',
	'tpt-select-prioritylangs-force' => '不允许向优先语言以外的语言的翻译',
	'tpt-select-prioritylangs-reason' => '原因：',
	'tpt-sections-prioritylangs' => '优先语言',
	'tpt-rev-mark' => '标记进行翻译',
	'tpt-rev-unmark' => '从翻译中删除',
	'tpt-rev-discourage' => '不建议',
	'tpt-rev-encourage' => '恢复',
	'tpt-rev-mark-tooltip' => '标记本页的最新版本进行翻译。',
	'tpt-rev-unmark-tooltip' => '从翻译中删除此页。',
	'tpt-rev-discourage-tooltip' => '不要再进一步翻译此页。',
	'tpt-rev-encourage-tooltip' => '将此页面恢复正常翻译模式。',
	'translate-tag-translate-link-desc' => '翻译本页',
	'translate-tag-markthis' => '标记此页面将进行翻译',
	'translate-tag-markthisagain' => '此页面最近被<span class="plainlinks">[$2 标记(点击可进行导入)]</span>进行翻译以来，已经过一些<span class="plainlinks">[$1 更改]</span>。',
	'translate-tag-hasnew' => '此页面有未被标记进行翻译的<span class="plainlinks">[$1 更改]</span>。',
	'tpt-translation-intro' => '本页是页面[[$2]]的<span class="plainlinks">[$1 翻译版本]</span>，翻译工作已完成$3%。',
	'tpt-languages-legend' => '其他语言：',
	'tpt-languages-zero' => '开始这种语言的翻译',
	'tpt-tab-translate' => '翻译',
	'tpt-target-page' => '本页面无法手动更新。
本页面是[[$1]]页面的翻译版，可以使用[$2 翻译工具]来更新该翻译。',
	'tpt-unknown-page' => '此命名空间是保留给内容页面翻译。
您尝试编辑的页面似乎没有对应任何被标记进行翻译的页面。',
	'tpt-translation-restricted' => '此页面到这种语言的翻译，已被翻译管理员禁止。

原因：$1',
	'tpt-discouraged-language-force' => '翻译管理员限制了翻译此页时所能采用的语言。这种语言不在这些语言之列。

原因：$1',
	'tpt-discouraged-language' => '这种语言不在此页的翻译管理员所设置的优先语言之列。

原因：$1',
	'tpt-discouraged-language-reason' => '原因：$1',
	'tpt-priority-languages' => '翻译管理员已将该组需优先翻译的语言设为$1。',
	'tpt-render-summary' => '更新以匹配源页面内容的新版本',
	'tpt-download-page' => '汇出含翻译的页面',
	'aggregategroups' => '聚合组',
	'tpt-aggregategroup-add' => '添加',
	'tpt-aggregategroup-save' => '保存',
	'tpt-aggregategroup-add-new' => '添加新的聚合组',
	'tpt-aggregategroup-new-name' => '名称：',
	'tpt-aggregategroup-new-description' => '说明 （可选）：',
	'tpt-aggregategroup-remove-confirm' => '确实要删除此聚合组吗？',
	'tpt-aggregategroup-invalid-group' => '组别不存在',
	'pt-parse-open' => '&lt;translate>标签不平衡。
翻译模板：<pre>$1</pre>',
	'pt-parse-close' => '&lt;/translate>标签不平衡。
翻译模板：<pre>$1</pre>',
	'pt-parse-nested' => '不允许嵌套&lt;translate>翻译单元。
标签文本：$1',
	'pt-shake-multiple' => '单一翻译单元含多个翻译单元标记。
翻译单元文本：<pre>$1</pre>',
	'pt-shake-position' => '翻译单元标记在意外位置。
翻译单元文本：<pre>$1</pre>',
	'pt-shake-empty' => '为“$1”的翻译单位标记是空的。',
	'log-description-pagetranslation' => '页面翻译系统的对应活动日志',
	'log-name-pagetranslation' => '页面翻译日志',
	'logentry-pagetranslation-mark' => '$1将$3{{GENDER:$2|标记为}}需要翻译',
	'logentry-pagetranslation-unmark' => '$1将$3从翻译中{{GENDER:$2|除去}}',
	'logentry-pagetranslation-moveok' => '$1{{GENDER:$2|完成了}}翻译页面$3到$4的重命名',
	'logentry-pagetranslation-movenok' => '$1{{GENDER:$2|遇到了}}在移动页面$3到$4时遇到了一个问题',
	'logentry-pagetranslation-deletefok' => '$1{{GENDER:$2|完成了}}翻译页面$3的删除',
	'logentry-pagetranslation-deletefnok' => '$1删除属于翻译页面$4的$3{{GENDER:$2|失败}}',
	'logentry-pagetranslation-deletelok' => '$1{{GENDER:$2|完成了}}翻译页面$3的删除',
	'logentry-pagetranslation-deletelnok' => '$1删除属于翻译页面$4的$3{{GENDER:$2|失败}}',
	'logentry-pagetranslation-encourage' => '$1{{GENDER:$2|支持}}$3的翻译',
	'logentry-pagetranslation-discourage' => '$1{{GENDER:$2|不支持}}$3的翻译',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1已从翻译的页面$3的优先语言中被{{GENDER:$2|移除}}',
	'logentry-pagetranslation-prioritylanguages' => '$1{{GENDER:$2|设置}}了此翻译页面的优先语言从$3到$5',
	'logentry-pagetranslation-prioritylanguages-force' => '$1认为$3到$5对此翻译页面的贡献{{GENDER:$2|很有限}}',
	'logentry-pagetranslation-associate' => '$1{{GENDER:$2|添加}}了翻译页面$3至信息组$4',
	'logentry-pagetranslation-dissociate' => '$1已在信息组$4的翻译页面$3中{{GENDER:$2|移除}}',
	'pt-movepage-title' => '移动可翻译页面$1',
	'pt-movepage-blockers' => '可翻译页面因下列$1错误无法移动至新名称：',
	'pt-movepage-block-base-exists' => '可翻译的目标页面“[[:$1]]”已存在。',
	'pt-movepage-block-base-invalid' => '可翻译的目标页面名称不是一个有效标题。',
	'pt-movepage-block-tp-exists' => '目标翻译页面[[:$2]]存在。',
	'pt-movepage-block-tp-invalid' => '[[:$1]]的目标翻译页面的标题无效（可能太长）。',
	'pt-movepage-block-section-exists' => '为目标页面“[[:$2]]”的翻译单位存在。',
	'pt-movepage-block-section-invalid' => '目标页面为 “[[:$1]]” 的翻译单元可能无效（太长？）。',
	'pt-movepage-block-subpage-exists' => '目标子页面“[[:$2]]”存在。',
	'pt-movepage-block-subpage-invalid' => '[[:$1]]的子页面标题无效（可能太长）。',
	'pt-movepage-list-pages' => '需移动页面的列表',
	'pt-movepage-list-translation' => '翻译{{PLURAL:$1|页面|页面}}',
	'pt-movepage-list-section' => '翻译单元{{PLURAL:$1|页面|页面}}',
	'pt-movepage-list-other' => '其他子{{PLURAL:$1|页面|页面}}',
	'pt-movepage-list-count' => '共移动$1个页面。',
	'pt-movepage-legend' => '移动可翻译页面',
	'pt-movepage-current' => '当前名称：',
	'pt-movepage-new' => '新名称：',
	'pt-movepage-reason' => '原因：',
	'pt-movepage-subpages' => '移动所有子页面',
	'pt-movepage-action-check' => '检查是否可以移动',
	'pt-movepage-action-perform' => '确认移动',
	'pt-movepage-action-other' => '更改目标',
	'pt-movepage-intro' => '本特殊页面允许您移动被标记进行翻译的页面。
此移动操作将不会一瞬间，因为有很多页面要移动。
当页面移动中，不能与该页面交互。
任何移动失败将在[[Special:Log/pagetranslation|页面翻译日志]]记录，并且需要手动修理。',
	'pt-movepage-logreason' => '可翻译页面$1 的部分。',
	'pt-movepage-started' => '基页面现已移动。
请检查[[Special:Log/pagetranslation|页面翻译日志]]内的错误和完成消息。',
	'pt-locked-page' => '此页面已被锁定，因为可翻译页面正在被移动。',
	'pt-deletepage-lang-title' => '删除翻译网页 $1。',
	'pt-deletepage-full-title' => '删除可翻译网页 $1。',
	'pt-deletepage-invalid-title' => '指定的页不是有效的。',
	'pt-deletepage-invalid-text' => '指定的页面不是一个可翻译页面或一个翻译信息页面。',
	'pt-deletepage-action-check' => '要删除的列表页',
	'pt-deletepage-action-perform' => '立即删除',
	'pt-deletepage-action-other' => '更改目标',
	'pt-deletepage-lang-legend' => '删除翻译页面',
	'pt-deletepage-full-legend' => '删除可翻译页面',
	'pt-deletepage-any-legend' => '删除可翻译的页面或翻译信息的页面',
	'pt-deletepage-current' => '页面名称：',
	'pt-deletepage-reason' => '原因：',
	'pt-deletepage-subpages' => '删除所有子页面',
	'pt-deletepage-list-pages' => '若要删除的页面列表',
	'pt-deletepage-list-translation' => '翻译页面',
	'pt-deletepage-list-section' => '翻译单元页面',
	'pt-deletepage-list-other' => '其他子页面',
	'pt-deletepage-list-count' => '共删除$1个页面。',
	'pt-deletepage-full-logreason' => '翻译页面$1的一部分。',
	'pt-deletepage-lang-logreason' => '翻译页面$1的一部分。',
	'pt-deletepage-started' => '请检查[[Special:Log/pagetranslation|页面翻译日志]]内的错误和完成消息。',
	'pt-deletepage-intro' => '本特殊页面允许您删除一种语言中一整个可翻译页面或单个翻译页面。
因为所有相关页会一并删除，此操作不会即时完成。
失败操作记录于[[Special:Log/pagetranslation|页面翻译日志]]中并且需要手动修理。',
);

/** Traditional Chinese (中文（繁體）‎)
 * @author Anakmalaysia
 * @author Liangent
 * @author Mark85296341
 * @author Simon Shek
 * @author TianyinLee
 * @author Waihorace
 * @author Wrightbus
 */
$messages['zh-hant'] = array(
	'pagetranslation' => '頁面翻譯',
	'right-pagetranslation' => '為翻譯標記頁面的版本',
	'action-pagetranslation' => '管理可翻譯頁面',
	'tpt-desc' => '用於翻譯內容頁面的擴展',
	'tpt-section' => '翻譯單元$1',
	'tpt-section-new' => '新翻譯單元。
名字：$1',
	'tpt-section-deleted' => '翻譯單元$1',
	'tpt-template' => '頁面模板',
	'tpt-templatediff' => '頁面模板已改變。',
	'tpt-diff-old' => '上一個文字',
	'tpt-diff-new' => '下一個文字',
	'tpt-submit' => '標記此版本的翻譯',
	'tpt-sections-oldnew' => '新的和現存的翻譯單元',
	'tpt-sections-deleted' => '已刪除的翻譯單元',
	'tpt-sections-template' => '翻譯頁面模版',
	'tpt-action-nofuzzy' => '不要使翻譯作廢',
	'tpt-badtitle' => '頁面名稱 ($1) 不是一個有效的標題',
	'tpt-nosuchpage' => '頁面$1不存在。',
	'tpt-oldrevision' => '$2 不是最新版本的頁面 [[$1]]。
最新版本只可以將標記進行翻譯。',
	'tpt-notsuitable' => '頁$1不適合翻譯。
請確保它具有 <nowiki><translate></nowiki> 標記，並具有有效的語法。',
	'tpt-saveok' => '[[$1]]頁面已被標記將進行翻譯，一共$2個翻譯單元。
本頁面現已可以<span class="plainlinks">[$3 翻譯]</span>。',
	'tpt-offer-notify' => '你可以<span class="plainlinks">[ $1 通知譯者]</span>翻譯此頁。',
	'tpt-badsect' => '「$1」對於$2翻譯單元不是有效的名稱。',
	'tpt-showpage-intro' => '以下列出新創、現存及已刪除的部分。
將此版本標記進行翻譯之前，請檢查來確定該部分極少受修改，以便翻譯員避免得到多餘的工作。',
	'tpt-mark-summary' => '標記此版本的翻譯',
	'tpt-edit-failed' => '無法更新該頁面：$1',
	'tpt-duplicate' => '翻譯單元名稱 $1 已被使用超過一次。',
	'tpt-already-marked' => '此頁面的最新版本已經已標記進行翻譯。',
	'tpt-unmarked' => '$1頁不再被標記進行翻譯。',
	'tpt-list-nopages' => '沒有被標記進行翻譯或者準備被標記進行翻譯的頁面。',
	'tpt-new-pages-title' => '提議翻譯的頁面',
	'tpt-old-pages-title' => '正在翻譯的頁面',
	'tpt-other-pages-title' => '損壞的頁面',
	'tpt-discouraged-pages-title' => '不推薦的頁面',
	'tpt-new-pages' => '以下{{PLURAL:$1|此|這些}}頁麵包含具有翻譯標記的文本，
但沒有被標記進行翻譯的版本。',
	'tpt-old-pages' => '以下{{PLURAL:$1|此|這些}}頁面有被標記進行翻譯的版本。',
	'tpt-other-pages' => '以下{{PLURAL:$1|此|這些}}頁面有舊版本被標記進行翻譯，
但最新版本不得標記進行翻譯。',
	'tpt-discouraged-pages' => '以下{{PLURAL:$1| |這些}}頁面不需要更多翻譯。',
	'tpt-select-prioritylangs' => '優先語言代碼的逗號分隔型列表',
	'tpt-select-prioritylangs-force' => '防止翻譯成優先語言以外的語言',
	'tpt-select-prioritylangs-reason' => '原因：',
	'tpt-sections-prioritylangs' => '優先語言',
	'tpt-rev-mark' => '標記進行翻譯',
	'tpt-rev-unmark' => '從翻譯中刪除',
	'tpt-rev-discourage' => '挫折',
	'tpt-rev-encourage' => '恢復',
	'tpt-rev-mark-tooltip' => '標記本頁的最新版本進行翻譯。',
	'tpt-rev-unmark-tooltip' => '從翻譯中刪除此頁。',
	'tpt-rev-discourage-tooltip' => '不要在此頁上進行更多翻譯。',
	'tpt-rev-encourage-tooltip' => '將此頁面恢復正常翻譯模式。',
	'translate-tag-translate-link-desc' => '翻譯本頁',
	'translate-tag-markthis' => '標記此頁面的翻譯',
	'translate-tag-markthisagain' => '此頁面<span class="plainlinks">[$2 最近被標記進行翻譯]</span>以來，已經過一些<span class="plainlinks">[$1 更改]</span>。',
	'translate-tag-hasnew' => '此頁面有未被標記進行翻譯的<span class="plainlinks">[$1 更改]</span>。',
	'tpt-translation-intro' => '此頁面是[[$2]]頁面的<span class="plainlinks">[$1 翻譯版本]</span>，而該翻譯工作已經$3%完成。',
	'tpt-languages-legend' => '其他語言：',
	'tpt-languages-zero' => '開始這種語言的翻譯',
	'tpt-tab-translate' => '翻譯',
	'tpt-target-page' => '本頁面無法手動更新。
本頁面是[[$1]]頁面的翻譯版，可以使用[$2 翻譯工具]來更新該翻譯。',
	'tpt-unknown-page' => '此命名空間是保留給內容頁面翻譯。
您嘗試編輯的頁面似乎沒有對應任何被標記進行翻譯的頁面。',
	'tpt-translation-restricted' => '此頁面到這種語言的翻譯，已被翻譯管理員禁止。

原因：$1',
	'tpt-discouraged-language-force' => "''''此頁面不能翻譯為 $2 。'''

翻譯管理員限制只能把此頁為$3",
	'tpt-discouraged-language' => "''''翻譯成$2不在本頁的優先之例。'''

翻譯管理員已設置本頁優先翻譯成$3。",
	'tpt-discouraged-language-reason' => '原因：$1',
	'tpt-priority-languages' => '翻譯管理員已將該組需優先翻譯的語言設為$1。',
	'tpt-render-summary' => '要匹配的源頁的新版本更新',
	'tpt-download-page' => '匯出含翻譯的頁面',
	'aggregategroups' => '聚合組',
	'tpt-aggregategroup-add' => '添加',
	'tpt-aggregategroup-save' => '儲存',
	'tpt-aggregategroup-add-new' => '添加新的聚合組',
	'tpt-aggregategroup-new-name' => '名稱：',
	'tpt-aggregategroup-new-description' => '說明 （可選）：',
	'tpt-aggregategroup-remove-confirm' => '確實要刪除此聚合組嗎？',
	'tpt-aggregategroup-invalid-group' => '組別不存在',
	'pt-parse-open' => '&lt;translate>標籤不平衡。
翻譯模板：<pre>$1</pre>',
	'pt-parse-close' => '&lt;/translate>標籤不平衡。
翻譯模板：<pre>$1</pre>',
	'pt-parse-nested' => '不允許嵌套&lt;translate>翻譯單元。
標記文本：<pre>$1</pre>',
	'pt-shake-multiple' => '單一翻譯單元含多個翻譯單元標記。
翻譯單元文本：<pre>$1</pre>',
	'pt-shake-position' => '翻譯單元標記在意外位置。
翻譯單元文本：<pre>$1</pre>',
	'pt-shake-empty' => '空的翻譯單元標記為「$1」。',
	'log-description-pagetranslation' => '與有關的網頁翻譯系統操作日誌',
	'log-name-pagetranslation' => '網頁翻譯日誌',
	'logentry-pagetranslation-mark' => '$1{{GENDER:$2|標記}}$3為可翻譯',
	'logentry-pagetranslation-unmark' => '$1{{GENDER:$2|移除}}$3為可翻譯',
	'logentry-pagetranslation-moveok' => '$1{{GENDER:$2|完成}}重命名可翻譯頁面$3到$4',
	'logentry-pagetranslation-movenok' => '$1移動$3到$4期間{{GENDER:$2|出現}}問題',
	'logentry-pagetranslation-deletefok' => '$1{{GENDER:$2|完成}}刪除可翻譯頁面$3',
	'logentry-pagetranslation-deletefnok' => '$1刪除可翻譯頁面$4的$3{{GENDER:$2|失敗}}',
	'logentry-pagetranslation-deletelok' => '$1{{GENDER:$2|完成}}刪除可翻譯頁面$3',
	'logentry-pagetranslation-deletelnok' => '$1刪除可翻譯頁面$4的$3{{GENDER:$2|失敗}}',
	'logentry-pagetranslation-encourage' => '$1{{GENDER:$2|鼓勵}}翻譯$3',
	'logentry-pagetranslation-discourage' => '$1{{GENDER:$2|不鼓勵}}翻譯$3',
	'logentry-pagetranslation-prioritylanguages-unset' => '$1{{GENDER:$2|移除}}可翻譯頁面$3的優先語言',
	'logentry-pagetranslation-prioritylanguages' => '$1{{GENDER:$2|設定}}可翻譯頁面$3的優先語言為$5',
	'logentry-pagetranslation-prioritylanguages-force' => '$1{{GENDER:$2|限制}}可翻譯頁面$3的語言為$5',
	'logentry-pagetranslation-associate' => '$1{{GENDER:$2|添加}}可翻譯頁面$3到$4',
	'logentry-pagetranslation-dissociate' => '$1從$4{{GENDER:$2|移除}}可翻譯頁面$3',
	'pt-movepage-title' => '移動可翻譯頁面「$1」',
	'pt-movepage-blockers' => '可翻譯頁面無法移動至新名稱，原因為以下這{{PLURAL:$1|個|些}}錯誤：',
	'pt-movepage-block-base-exists' => '可翻譯的目標頁面「[[:$1]]」存在。',
	'pt-movepage-block-base-invalid' => '可翻譯的目標頁面名稱不是一個有效的標題。',
	'pt-movepage-block-tp-exists' => '目標翻譯頁「[[:$2]]」存在。',
	'pt-movepage-block-tp-invalid' => '「[[:$1]]」為目標翻譯頁面的標題無效（太長？）。',
	'pt-movepage-block-section-exists' => '目標頁面「[[:$2]]」的翻譯單元存在。',
	'pt-movepage-block-section-invalid' => '目標頁面為「[[:$1]]」的翻譯單元可能無效（太長？）。',
	'pt-movepage-block-subpage-exists' => '目標子頁面「[[:$2]]」 存在。',
	'pt-movepage-block-subpage-invalid' => '「[[:$1]]」為目標子頁面的標題無效（太長？）。',
	'pt-movepage-list-pages' => '移動到頁面的列表',
	'pt-movepage-list-translation' => '翻譯{{PLURAL:$1|頁面|頁面}}',
	'pt-movepage-list-section' => '翻譯單元{{PLURAL:$1|頁面|頁面}}',
	'pt-movepage-list-other' => '其他子{{PLURAL:$1|頁面|頁面}}',
	'pt-movepage-list-count' => '總計$1個頁面即將移動。',
	'pt-movepage-legend' => '移動可翻譯頁面',
	'pt-movepage-current' => '當前的名稱：',
	'pt-movepage-new' => '新的名稱：',
	'pt-movepage-reason' => '原因：',
	'pt-movepage-subpages' => '移動所有子頁面',
	'pt-movepage-action-check' => '檢查是否可以移動',
	'pt-movepage-action-perform' => '確認移動',
	'pt-movepage-action-other' => '更改目標',
	'pt-movepage-intro' => '本特殊頁面允許您移動被標記進行翻譯的頁面。
此移動操作將不會一瞬間，因為有很多頁面要移動。
當頁面移動中，不能與該頁面交互。
任何移動失敗將在[[Special:Log/pagetranslation|頁面翻譯日誌]]記錄，並且需要手動修理。',
	'pt-movepage-logreason' => '可翻譯頁面「$1」的部分。',
	'pt-movepage-started' => '基頁面現已移動。
請檢查[[Special:Log/pagetranslation|頁面翻譯日誌]]內的錯誤和完成消息。',
	'pt-locked-page' => '此頁面已被鎖定，因為可翻譯頁面正在被移動。',
	'pt-deletepage-lang-title' => '刪除翻譯頁面「$1」。',
	'pt-deletepage-full-title' => '刪除可翻譯頁面「$1」。',
	'pt-deletepage-invalid-title' => '指定的頁不是有效的。',
	'pt-deletepage-invalid-text' => '指定的頁不是可翻譯頁面或翻譯訊息頁面。',
	'pt-deletepage-action-check' => '要刪除的頁面列表',
	'pt-deletepage-action-perform' => '確認刪除',
	'pt-deletepage-action-other' => '更改目標',
	'pt-deletepage-lang-legend' => '刪除翻譯頁面',
	'pt-deletepage-full-legend' => '刪除可翻譯頁面',
	'pt-deletepage-any-legend' => '刪除可翻譯頁面或翻譯訊息頁面',
	'pt-deletepage-current' => '頁面名稱：',
	'pt-deletepage-reason' => '原因：',
	'pt-deletepage-subpages' => '刪除所有子頁面',
	'pt-deletepage-list-pages' => '若要刪除的頁面列表',
	'pt-deletepage-list-translation' => '翻譯網頁',
	'pt-deletepage-list-section' => '翻譯單元頁面',
	'pt-deletepage-list-other' => '其他子頁面',
	'pt-deletepage-list-count' => '總計$1個頁面即將刪除。',
	'pt-deletepage-full-logreason' => '可翻譯頁面「$1」的一部分。',
	'pt-deletepage-lang-logreason' => '翻譯頁面「$1」的一部分。',
	'pt-deletepage-started' => '請檢查[[Special:Log/pagetranslation|頁面翻譯日誌]]內的錯誤和完成消息。',
	'pt-deletepage-intro' => '本特殊頁面允許您刪除一頁可翻譯的頁面、或單一種語言的其中一頁頁面的翻譯訊息頁面。
因為所有有關頁面將會被刪除，此刪除操作將不會一瞬間完成。
任何刪除失敗將在[[Special:Log/pagetranslation|頁面翻譯日誌]]記錄，並且需要手動修理。',
);
