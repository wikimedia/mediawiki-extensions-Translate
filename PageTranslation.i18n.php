<?php
/**
 * Translations of Page Translation feature of Translate extension.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

$messages = array();

/** English
 * @author Nike
 */
$messages['en'] = array(
	'pagetranslation' => 'Page translation',
	'right-pagetranslation' => 'Mark versions of pages for translation',
	'tpt-desc' => 'Extension for translating content pages',
	'tpt-section' => 'Section:',
	'tpt-section-new' => 'New section:',

	'tpt-diff-old' => 'Previous text',
	'tpt-diff-new' => 'New text',
	'tpt-submit' => 'Mark this version for translation',
	
	# Specific page on the special page
	'tpt-badtitle' => 'Page name given ($1) is not a valid title',
	'tpt-oldrevision' => '$2 is not the latest version of the page $1.
Only latest versions can be marked for translation.',
	'tpt-notsuitable' => 'Page $1 is not suitable for translation.
Make sure it has <nowiki><translate></nowiki> tags and has a valid syntax.',
	'tpt-saveok' => 'The page "$1" has been marked up for translation with $2 translatable sections.
The page can now be <span class="plainlinks">[$3 translated]</span>.',
	'tpt-badsect' => '"$1" is not a valid name for section $2.',
	'tpt-deletedsections' => 'The following sections wil no longer be used',
	'tpt-showpage-intro' => 'Below are listed new, existing and deleted sections.
Before marking this version for translation, check that the changes to sections are minimised to avoid unnecessary work for translators.',
	'tpt-mark-summary' => 'Marked this version for translation',
	'tpt-edit-failed' => 'Could not update the page: $1',
	'tpt-insert-failed' => 'Could not add sections to the database.',
	'tpt-already-marked' => 'The latest version of this page has already been marked for translation.',

	# Page list on the special page
	'tpt-list-nopages' => 'No pages are marked for translation nor ready to be marked for translation.',
	'tpt-old-pages' => 'Some version of these pages have been marked for translation.',
	'tpt-new-pages' => 'These pages contain text with translation tags, but no version of these pages are currently marked for translation.',
	'tpt-rev-latest' => 'latest version',
	'tpt-rev-old' => 'version $1',
	'tpt-rev-mark-new' => 'mark this version for translation',
	'tpt-translate-this' => 'translate this page',

	# Source and translation page headers
	'translate-tag-translate-link-desc' => 'Translate this page',
	'translate-tag-markthis' => 'Mark this page for translation',
	'translate-tag-legend' => 'Legend:',
	'translate-tag-legend-fallback' => 'Translation in other language',
	'translate-tag-legend-fuzzy' => 'Outdated translation',

	'tpt-target-page' => 'This page cannot be updated manually.
This page is a translation of page [[$1]] and the translation can be updated using [$2 the translation tool].',
	'tpt-unknown-page' => 'This namespace is reserved for content page translations.
The page you are trying to edit does not seem to correspond any page marked for translation.'
);

/** Dutch (Nederlands)
 * @author Siebrand
 */
$messages['nl'] = array(
	'pagetranslation' => 'Paginavertaling',
	'tpt-section' => 'Sectie:',
	'tpt-section-new' => 'Nieuwe sectie:',
	'tpt-diff-old' => 'Vorige tekst',
	'tpt-diff-new' => 'Nieuwe tekst',
	'tpt-rev-latest' => 'meest recente versie',
	'translate-tag-translate-link-desc' => 'Deze pagina vertalen',
	'translate-tag-legend' => 'Legenda:',
	'translate-tag-legend-fallback' => 'Vertaling in een andere taal',
	'translate-tag-legend-fuzzy' => 'Verouderde vertaling',
	'tpt-target-page' => 'Deze pagina kan niet handmatig worden bijgewerkt manually.
Deze pagina is een vertaling van de pagina [[$1]].
De vertaling kan bijgewerkt worden via de [$2 vertaalhulpmiddellen].',
	'tpt-unknown-page' => "Deze naamruimte is gereserveerd voor de vertalingen van van pagina's.
De pagina die u probeert te bewerken lijkt niet overeen te komen met een te vertalen pagina.",
);

