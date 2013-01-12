<?php
/**
 * Translations for the TranslationSearch special page.
 *
 * @file
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

$messages = array();

/** English
 * @author Nike
 */
$messages['en'] = array(
	'searchtranslations' => 'Search translations',

	'tux-sst-edit' => 'Edit message',

	'tux-sst-search' => 'Search',
	'tux-sst-search-ph' => 'Search translations',
	'tux-sst-count' => '{{PLURAL:$1|One result found|$1 results found}}',

	'tux-sst-facet-language' => 'Languages',
	'tux-sst-facet-group' => 'Message groups',
	'tux-sst-facet-orphan' => '(orphan)',

	'tux-sst-nosolr-title' => 'Search unavailable',
	'tux-sst-nosolr-body' => 'This wiki does not have a translation search service.',
);

/** Message documentation (Message documentation)
 * @author Nike
 * @author Shirayuki
 */
$messages['qqq'] = array(
	'searchtranslations' => 'Name of the special page.
{{Identical|Search translation}}',
	'tux-sst-edit' => 'A link text.
{{Identical|Edit message}}',
	'tux-sst-search' => 'A button text.
{{Identical|Search}}',
	'tux-sst-search-ph' => 'Placeholder text in input field.
{{Identical|Search translation}}',
	'tux-sst-count' => '$1 is the number of search results',
	'tux-sst-facet-language' => 'Label for a facet in [[Special:TranslationSearch]].
{{Identical|Language}}',
	'tux-sst-facet-group' => 'Label for a facet in [[Special:TranslationSearch]].
{{Identical|Message group}}',
	'tux-sst-facet-orphan' => "Name for group of search results that don't belong to any known message groups.
{{Identical|Orphan}}",
	'tux-sst-nosolr-title' => 'Used as title of error page.

The body of error message is {{msg-mw|Tux-sst-nosolr-body}}.',
	'tux-sst-nosolr-body' => 'Used as error message.

The page title for this message is {{msg-mw|Tux-sst-nosolr-title}}.',
);

/** German (Deutsch)
 * @author Metalhead64
 */
$messages['de'] = array(
	'searchtranslations' => 'Übersetzungen suchen',
	'tux-sst-edit' => 'Nachricht bearbeiten',
	'tux-sst-search' => 'Suchen',
	'tux-sst-search-ph' => 'Übersetzungen suchen',
	'tux-sst-count' => '{{PLURAL:$1|Ein Ergebnis gefunden|$1 Ergebnisse gefunden}}',
	'tux-sst-facet-language' => 'Sprachen',
	'tux-sst-facet-group' => 'Nachrichtengruppen',
	'tux-sst-facet-orphan' => '(verwaist)',
	'tux-sst-nosolr-title' => 'Suche nicht verfügbar',
	'tux-sst-nosolr-body' => 'Dieses Wiki hat keinen Übersetzungssuchservice.',
);

/** Japanese (日本語)
 * @author Shirayuki
 */
$messages['ja'] = array(
	'searchtranslations' => '翻訳の検索',
	'tux-sst-edit' => 'メッセージを編集',
	'tux-sst-search' => '検索',
	'tux-sst-search-ph' => '翻訳の検索',
	'tux-sst-count' => '{{PLURAL:$1|$1 件見つかりました}}',
	'tux-sst-facet-language' => '言語',
	'tux-sst-facet-group' => 'メッセージ群',
	'tux-sst-facet-orphan' => '(孤立)',
	'tux-sst-nosolr-title' => '検索は利用できません',
	'tux-sst-nosolr-body' => 'このウィキには翻訳の検索サービスはありません。',
);

/** Piedmontese (Piemontèis)
 * @author Dragonòt
 */
$messages['pms'] = array(
	'searchtranslations' => 'Serca tradussion',
	'tux-sst-edit' => 'Modìfica mëssagi',
	'tux-sst-search' => 'Sërca',
	'tux-sst-search-ph' => 'Serca tradussion',
	'tux-sst-count' => '{{PLURAL:$1|Un arzultà trovà|$1 arzultà trovà}}',
	'tux-sst-facet-language' => 'Lenghe',
	'tux-sst-facet-group' => 'Partìe ëd mëssagi',
	'tux-sst-facet-orphan' => '(orfanel)',
	'tux-sst-nosolr-title' => 'Serca pa disponìbil',
	'tux-sst-nosolr-body' => "La wiki a l'ha pa un sërvissi d'arserca ëd tradussion.",
);
