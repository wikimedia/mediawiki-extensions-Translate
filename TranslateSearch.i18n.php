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

/** Finnish (suomi)
 * @author Stryn
 */
$messages['fi'] = array(
	'searchtranslations' => 'Etsi käännöksiä',
	'tux-sst-search-ph' => 'Etsi käännöksiä',
);

/** French (français)
 * @author Gomoko
 */
$messages['fr'] = array(
	'searchtranslations' => 'Recherche de traductions',
	'tux-sst-edit' => 'Modifier le message',
	'tux-sst-search' => 'Rechercher',
	'tux-sst-search-ph' => 'Recherche de traductions',
	'tux-sst-count' => '{{PLURAL:$1|Un résultat trouvé|$1 résultats trouvés}}',
	'tux-sst-facet-language' => 'Langues',
	'tux-sst-facet-group' => 'Groupes de message',
	'tux-sst-facet-orphan' => '(orphelin)',
	'tux-sst-nosolr-title' => 'Recherche indisponible',
	'tux-sst-nosolr-body' => 'Ce wiki n’a pas de service de recherche de traduction.',
);

/** Galician (galego)
 * @author Toliño
 */
$messages['gl'] = array(
	'searchtranslations' => 'Procurar nas traducións',
	'tux-sst-edit' => 'Editar a mensaxe',
	'tux-sst-search' => 'Procurar',
	'tux-sst-search-ph' => 'Procurar nas traducións',
	'tux-sst-count' => '{{PLURAL:$1|Atopouse 1 resultado|Atopáronse $1 resultados}}',
	'tux-sst-facet-language' => 'Linguas',
	'tux-sst-facet-group' => 'Grupos de mensaxes',
	'tux-sst-facet-orphan' => '(orfos)',
	'tux-sst-nosolr-title' => 'A procura non está dispoñible',
	'tux-sst-nosolr-body' => 'Este wiki non dispón dun servizo de procura de traducións.',
);

/** Hebrew (עברית)
 * @author Amire80
 */
$messages['he'] = array(
	'searchtranslations' => 'חיפוש בתרגומים',
	'tux-sst-edit' => 'עריכת הודעה',
	'tux-sst-search' => 'חיפוש',
	'tux-sst-search-ph' => 'חיפוש בתרגומים',
	'tux-sst-count' => '{{PLURAL:$1|נמצאה תוצאה אחת|נמצאו $1 תוצאות}}',
	'tux-sst-facet-language' => 'שפות',
	'tux-sst-facet-group' => 'קבוצות הודעות',
	'tux-sst-facet-orphan' => '(יתומים)',
	'tux-sst-nosolr-title' => 'החיפוש אינו זמין',
	'tux-sst-nosolr-body' => 'בוויקי הזה אין שירות חיפוש בתרגומים.',
);

/** Italian (italiano)
 * @author Beta16
 */
$messages['it'] = array(
	'searchtranslations' => 'Ricerca traduzioni',
	'tux-sst-edit' => 'Modifica messaggio',
	'tux-sst-search' => 'Cerca',
	'tux-sst-search-ph' => 'Cerca traduzioni',
	'tux-sst-count' => '{{PLURAL:$1|Un risultato trovato|$1 risultati trovati}}',
	'tux-sst-facet-language' => 'Lingue',
	'tux-sst-facet-group' => 'Gruppi di messaggi',
	'tux-sst-facet-orphan' => '(orfano)',
	'tux-sst-nosolr-title' => 'Ricerca non disponibile',
	'tux-sst-nosolr-body' => 'Questo wiki non ha un servizio di ricerca delle traduzioni.',
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

/** Korean (한국어)
 * @author 아라
 */
$messages['ko'] = array(
	'searchtranslations' => '번역 찾기',
	'tux-sst-edit' => '메시지 편집',
	'tux-sst-search' => '찾기',
	'tux-sst-search-ph' => '번역 찾기',
	'tux-sst-count' => '{{PLURAL:$1|결과 한 개를 찾았습니다|결과 $1개를 찾았습니다}}',
	'tux-sst-facet-language' => '언어',
	'tux-sst-facet-group' => '메시지 그룹',
	'tux-sst-facet-orphan' => '(외톨이)',
	'tux-sst-nosolr-title' => '찾기를 사용할 수 없음',
	'tux-sst-nosolr-body' => '이 위키는 번역 찾기 서비스가 없습니다.',
);

/** Macedonian (македонски)
 * @author Bjankuloski06
 */
$messages['mk'] = array(
	'searchtranslations' => 'Пребарување на преводи',
	'tux-sst-edit' => 'Уреди порака',
	'tux-sst-search' => 'Пребарај',
	'tux-sst-search-ph' => 'Пребарајте преводи',
	'tux-sst-count' => '{{PLURAL:$1|Пронајден е еден резултат|Пронајдени се $1 резултати}}',
	'tux-sst-facet-language' => 'Јазици',
	'tux-sst-facet-group' => 'Групи на пораки',
	'tux-sst-facet-orphan' => '(осамена)',
	'tux-sst-nosolr-title' => 'Пребарувањето е недостапно',
	'tux-sst-nosolr-body' => 'Ова вики нема пребарувач.',
);

/** Occitan (occitan)
 * @author Cedric31
 */
$messages['oc'] = array(
	'searchtranslations' => 'Recèrca de traduccions',
	'tux-sst-edit' => 'Modificar lo messatge',
	'tux-sst-search' => 'Recercar',
	'tux-sst-facet-language' => 'Lengas',
	'tux-sst-facet-group' => 'Gropes de messatge',
	'tux-sst-facet-orphan' => '(orfanèl)',
	'tux-sst-nosolr-title' => 'Recèrca indisponibla',
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

/** Vietnamese (Tiếng Việt)
 * @author Minh Nguyen
 */
$messages['vi'] = array(
	'searchtranslations' => 'Tìm bản dịch',
	'tux-sst-edit' => 'Sửa thông điệp',
	'tux-sst-search' => 'Tìm kiếm',
	'tux-sst-search-ph' => 'Tìm bản dịch',
	'tux-sst-count' => 'Tìm thấy $1 kết quả',
	'tux-sst-facet-language' => 'Ngôn ngữ',
	'tux-sst-facet-group' => 'Nhóm thông điệp',
	'tux-sst-facet-orphan' => '(mồ côi)',
	'tux-sst-nosolr-title' => 'Không thể tìm kiếm',
	'tux-sst-nosolr-body' => 'Wiki này không có công cụ tìm bản dịch.',
);
