<?php
/**
 * Translations for the TranslationSearch special page.
 *
 * @file
 * @license GPL-2.0+
 */

$messages = array();

/** English
 * @author Nike
 */
$messages['en'] = array(
	'searchtranslations' => 'Search translations',

	'tux-sst-edit' => 'Edit translation',

	'tux-sst-search' => 'Search',
	'tux-sst-search-ph' => 'Search translations',
	'tux-sst-count' => '{{PLURAL:$1|One result found|$1 results found}}',

	'tux-sst-facet-language' => 'Languages',
	'tux-sst-facet-group' => 'Message groups',
	'tux-sst-facet-orphan' => '(orphan)',

	'tux-sst-nosolr-title' => 'Search unavailable',
	'tux-sst-nosolr-body' => 'This wiki does not have a translation search service.',
	'tux-sst-solr-offline-title' => 'Search unavailable',
	'tux-sst-solr-offline-body' => 'The search service is temporarily unavailable.',

	'tux-sst-next' => 'Next results',
	'tux-sst-prev' => 'Previous results',
);

/** Message documentation (Message documentation)
 * @author Nike
 * @author Shirayuki
 */
$messages['qqq'] = array(
	'searchtranslations' => '{{doc-special|SearchTranslations}}
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

The body of error message is {{msg-mw|Tux-sst-nosolr-body}}.
{{Identical|Search unavailable}}',
	'tux-sst-nosolr-body' => 'Used as error message.

The page title for this message is {{msg-mw|Tux-sst-nosolr-title}}.',
	'tux-sst-solr-offline-title' => 'Used as title of error page.

The body of error message is {{msg-mw|Tux-sst-solr-offline-body}}.
{{Identical|Search unavailable}}',
	'tux-sst-solr-offline-body' => 'Used as error message.

The page title for this message is {{msg-mw|tux-sst-solr-offline-title}}.',
	'tux-sst-next' => 'Link to browser more search results.

See also:
* {{msg-mw|Tux-sst-prev}}',
	'tux-sst-prev' => 'Link to browser more search results.

See also:
* {{msg-mw|Tux-sst-next}}',
);

/** Asturian (asturianu)
 * @author Xuacu
 */
$messages['ast'] = array(
	'searchtranslations' => 'Guetar nes traducciones',
	'tux-sst-edit' => 'Editar traducción',
	'tux-sst-search' => 'Guetar',
	'tux-sst-search-ph' => 'Guetar nes traducciones',
	'tux-sst-count' => '{{PLURAL:$1|Alcontrose un resultáu|Alcontráronse $1 resultaos}}',
	'tux-sst-facet-language' => 'Llingües',
	'tux-sst-facet-group' => 'Grupos de mensaxes',
	'tux-sst-facet-orphan' => '(güérfanos)',
	'tux-sst-nosolr-title' => 'La gueta nun ta disponible',
	'tux-sst-nosolr-body' => 'Esta wiki nun tien un serviciu de gueta de traducciones.',
	'tux-sst-solr-offline-title' => 'La gueta nun ta disponible',
	'tux-sst-solr-offline-body' => 'El serviciu de gueta nun ta disponible temporalmente.',
	'tux-sst-next' => 'Resultaos siguientes',
	'tux-sst-prev' => 'Resultaos anteriores',
);

/** Azerbaijani (azərbaycanca)
 * @author Khan27
 */
$messages['az'] = array(
	'searchtranslations' => 'Tərcümələri axtar',
	'tux-sst-edit' => 'Tərcümələri redaktə et',
	'tux-sst-search' => 'Axtar',
	'tux-sst-search-ph' => 'Tərcümələri axtar',
	'tux-sst-count' => '{{PLURAL:$1|Bir nəticə tapıldı|$1 nəticə tapıldı}}',
	'tux-sst-facet-language' => 'Dillər',
	'tux-sst-facet-group' => 'Mesaj qrupları',
	'tux-sst-facet-orphan' => '(yetim)',
	'tux-sst-nosolr-title' => 'Axtarış mümkün deyil',
	'tux-sst-nosolr-body' => 'Bu viki üçün tərcümə axtarış sistemi yoxdur.',
);

/** Belarusian (Taraškievica orthography) (беларуская (тарашкевіца)‎)
 * @author Wizardist
 */
$messages['be-tarask'] = array(
	'searchtranslations' => 'Пошук перакладаў',
	'tux-sst-edit' => 'Зьмяніць пераклад',
	'tux-sst-search' => 'Шукаць',
	'tux-sst-search-ph' => 'Пошук перакладаў',
	'tux-sst-count' => '{{PLURAL:$1|Знойдзены $1 вынік|Знойдзена $1 вынікі|Знойдзена $1 вынікаў}}',
	'tux-sst-facet-language' => 'Мовы',
	'tux-sst-facet-group' => 'Групы паведамленьняў',
	'tux-sst-facet-orphan' => '(сіраціна)',
	'tux-sst-nosolr-title' => 'Пошук недаступны',
	'tux-sst-nosolr-body' => 'Гэтая вікі ня мае службы пошуку перакладаў.',
	'tux-sst-solr-offline-title' => 'Пошук недаступны',
	'tux-sst-solr-offline-body' => 'Служба пошуку часова недаступная.',
	'tux-sst-next' => 'Наступныя вынікі',
	'tux-sst-prev' => 'Папярэднія вынікі',
);

/** Bengali (বাংলা)
 * @author Aftab1995
 * @author Nasir8891
 */
$messages['bn'] = array(
	'searchtranslations' => 'অনুবাদ অনুসন্ধান',
	'tux-sst-edit' => 'অনুবাদ সম্পাদনা',
	'tux-sst-search' => 'অনুসন্ধান',
	'tux-sst-search-ph' => 'অনুবাদ অনুসন্ধান',
	'tux-sst-count' => '{{PLURAL:$1|একটি ফলাফল পাওয়া গিয়েছে|$1টি ফলাফল পাওয়া গিয়েছে}}',
	'tux-sst-facet-language' => 'ভাষাসমূহ',
	'tux-sst-facet-group' => 'বার্তা সংকলন',
	'tux-sst-facet-orphan' => '(পিতৃহীন)',
	'tux-sst-nosolr-title' => 'অনুসন্ধান সম্ভব নয়',
	'tux-sst-nosolr-body' => 'এই উইকিতে অনুবাদ অনুসন্ধান সক্রিয় নেই।',
	'tux-sst-solr-offline-title' => 'অনুসন্ধান সক্রিয় নেই',
	'tux-sst-solr-offline-body' => 'অনুসন্ধান পরিষেবাটি সাময়িকভাবে অনুপলব্ধ।',
	'tux-sst-next' => 'পরবর্তী ফলাফল',
	'tux-sst-prev' => 'পূর্বের ফলাফল',
);

/** Breton (brezhoneg)
 * @author Fohanno
 */
$messages['br'] = array(
	'tux-sst-search' => 'Klask',
	'tux-sst-search-ph' => 'Klask troidigezhioù',
	'tux-sst-count' => "{{PLURAL:$1|Un disoc'h kavet|$1 disoc'h kavet}}",
	'tux-sst-facet-language' => 'Yezhoù',
	'tux-sst-facet-orphan' => '(emzivad)',
);

/** Chechen (нохчийн)
 * @author Умар
 */
$messages['ce'] = array(
	'tux-sst-search' => 'Лаха',
	'tux-sst-facet-language' => 'Меттанаш',
);

/** Church Slavic (словѣ́ньскъ / ⰔⰎⰑⰂⰡⰐⰠⰔⰍⰟ)
 * @author ОйЛ
 */
$messages['cu'] = array(
	'tux-sst-search' => 'ищи',
	'tux-sst-facet-language' => 'ѩꙁꙑци',
);

/** Danish (dansk)
 * @author Byrial
 */
$messages['da'] = array(
	'searchtranslations' => 'Søg oversættelser',
	'tux-sst-edit' => 'Redigér oversættelse',
	'tux-sst-search' => 'Søg',
	'tux-sst-search-ph' => 'Søg oversættelser',
	'tux-sst-count' => '{{PLURAL:$1|Ét resultat fundet|$1 resultater fundet}}',
	'tux-sst-facet-language' => 'Sprog',
	'tux-sst-facet-group' => 'Beskedgrupper',
	'tux-sst-facet-orphan' => '(ingen)',
	'tux-sst-nosolr-title' => 'Søgning er ikke tilgængelig',
	'tux-sst-nosolr-body' => 'Denne wiki har ikke mulighed for oversættelsessøgning.',
	'tux-sst-solr-offline-title' => 'Søgning er ikke tilgængelig',
	'tux-sst-solr-offline-body' => 'Søgning er midlertidig utilgængelig.',
	'tux-sst-next' => 'Næste resultater',
	'tux-sst-prev' => 'Forrige resultater',
);

/** German (Deutsch)
 * @author Metalhead64
 */
$messages['de'] = array(
	'searchtranslations' => 'Übersetzungen suchen',
	'tux-sst-edit' => 'Übersetzung bearbeiten',
	'tux-sst-search' => 'Suchen',
	'tux-sst-search-ph' => 'Übersetzungen suchen',
	'tux-sst-count' => '{{PLURAL:$1|Ein Ergebnis gefunden|$1 Ergebnisse gefunden}}',
	'tux-sst-facet-language' => 'Sprachen',
	'tux-sst-facet-group' => 'Nachrichtengruppen',
	'tux-sst-facet-orphan' => '(verwaist)',
	'tux-sst-nosolr-title' => 'Suche nicht verfügbar',
	'tux-sst-nosolr-body' => 'Dieses Wiki hat keinen Übersetzungssuchservice.',
	'tux-sst-solr-offline-title' => 'Die Suche ist nicht verfügbar',
	'tux-sst-solr-offline-body' => 'Der Suchdienst ist derzeit nicht verfügbar.',
	'tux-sst-next' => 'Nächste Ergebnisse',
	'tux-sst-prev' => 'Vorherige Ergebnisse',
);

/** Zazaki (Zazaki)
 * @author Mirzali
 */
$messages['diq'] = array(
	'tux-sst-facet-language' => 'Zıwani',
);

/** Lower Sorbian (dolnoserbski)
 * @author Michawiki
 */
$messages['dsb'] = array(
	'searchtranslations' => 'Pśełožki pytaś',
	'tux-sst-edit' => 'Pśełožk wobźěłaś',
	'tux-sst-search' => 'Pytaś',
	'tux-sst-search-ph' => 'Pśełožki pytaś',
	'tux-sst-count' => '{{PLURAL:$1|Jaden wuslědk namakany|$1 wuslědka namakanej|$1 wuslědki namakane|$1 wuslědkow namakane}}',
	'tux-sst-facet-language' => 'Rěcy',
	'tux-sst-facet-group' => 'Zdźěleńske kupki',
	'tux-sst-facet-orphan' => '(wósyrośone)',
	'tux-sst-nosolr-title' => 'Pytanje njestoj k dispoziciji',
	'tux-sst-nosolr-body' => 'Toś ten wiki njama słužbu za pytanje pśełožkow.',
	'tux-sst-solr-offline-title' => 'Pytanje njestoj k dispoziciji',
	'tux-sst-solr-offline-body' => 'Pytańska słužba njestoj tuchylu k dispoziciji.',
);

/** Estonian (eesti)
 * @author Pikne
 */
$messages['et'] = array(
	'searchtranslations' => 'Tõlgete otsimine',
	'tux-sst-edit' => 'Redigeeri tõlget',
	'tux-sst-search' => 'Otsi',
	'tux-sst-search-ph' => 'Otsi tõlkeid',
	'tux-sst-count' => '{{PLURAL:$1|Üks tulemus leitud|$1 tulemust leitud}}',
	'tux-sst-facet-language' => 'Keeled',
	'tux-sst-facet-group' => 'Sõnumirühmad',
	'tux-sst-facet-orphan' => '(orb)',
	'tux-sst-nosolr-title' => 'Otsing pole saadaval',
	'tux-sst-nosolr-body' => 'Sellel vikil pole tõlkeotsimisteenust.',
	'tux-sst-solr-offline-title' => 'Otsing pole saadaval',
	'tux-sst-solr-offline-body' => 'Otsimisteenus pole ajutiselt saadaval.',
	'tux-sst-next' => 'Järgmised tulemused',
	'tux-sst-prev' => 'Eelmised tulemused',
);

/** Basque (euskara)
 * @author An13sa
 */
$messages['eu'] = array(
	'tux-sst-edit' => 'Mezua aldatu', # Fuzzy
	'tux-sst-search' => 'Bilatu',
	'tux-sst-facet-language' => 'Hizkuntzak',
);

/** Finnish (suomi)
 * @author Crt
 * @author Nike
 * @author Silvonen
 * @author Stryn
 */
$messages['fi'] = array(
	'searchtranslations' => 'Etsi käännöksiä',
	'tux-sst-edit' => 'Muokkaa käännöstä',
	'tux-sst-search' => 'Hae',
	'tux-sst-search-ph' => 'Etsi käännöksiä',
	'tux-sst-count' => '{{PLURAL:$1|Yksi hakutulos|$1 hakutulosta}}',
	'tux-sst-facet-language' => 'Kielet',
	'tux-sst-facet-group' => 'Viestiryhmät',
	'tux-sst-facet-orphan' => '(orpo)',
	'tux-sst-nosolr-title' => 'Haku ei ole käytössä',
	'tux-sst-nosolr-body' => 'Hakupalvelu ei ole käytössä tässä wikissä.',
	'tux-sst-solr-offline-title' => 'Haku ei ole käytössä',
);

/** French (français)
 * @author Gomoko
 */
$messages['fr'] = array(
	'searchtranslations' => 'Recherche de traductions',
	'tux-sst-edit' => 'Modifier la traduction',
	'tux-sst-search' => 'Rechercher',
	'tux-sst-search-ph' => 'Recherche de traductions',
	'tux-sst-count' => '{{PLURAL:$1|Un résultat trouvé|$1 résultats trouvés}}',
	'tux-sst-facet-language' => 'Langues',
	'tux-sst-facet-group' => 'Groupes de message',
	'tux-sst-facet-orphan' => '(orphelin)',
	'tux-sst-nosolr-title' => 'Recherche indisponible',
	'tux-sst-nosolr-body' => 'Ce wiki n’a pas de service de recherche de traduction.',
	'tux-sst-solr-offline-title' => 'Recherche indisponible',
	'tux-sst-solr-offline-body' => 'Le service de recherche est temporairement indisponible.',
	'tux-sst-next' => 'Résultats suivants',
	'tux-sst-prev' => 'Résultats précédents',
);

/** Galician (galego)
 * @author Toliño
 */
$messages['gl'] = array(
	'searchtranslations' => 'Procurar nas traducións',
	'tux-sst-edit' => 'Editar a tradución',
	'tux-sst-search' => 'Procurar',
	'tux-sst-search-ph' => 'Procurar nas traducións',
	'tux-sst-count' => '{{PLURAL:$1|Atopouse 1 resultado|Atopáronse $1 resultados}}',
	'tux-sst-facet-language' => 'Linguas',
	'tux-sst-facet-group' => 'Grupos de mensaxes',
	'tux-sst-facet-orphan' => '(orfos)',
	'tux-sst-nosolr-title' => 'A procura non está dispoñible',
	'tux-sst-nosolr-body' => 'Este wiki non dispón dun servizo de procura de traducións.',
	'tux-sst-solr-offline-title' => 'A procura non está dispoñible',
	'tux-sst-solr-offline-body' => 'O servizo de procura non está dispoñible temporalmente.',
	'tux-sst-next' => 'Resultados seguintes',
	'tux-sst-prev' => 'Resultados anteriores',
);

/** Hebrew (עברית)
 * @author Amire80
 */
$messages['he'] = array(
	'searchtranslations' => 'חיפוש בתרגומים',
	'tux-sst-edit' => 'עריכת התרגום',
	'tux-sst-search' => 'חיפוש',
	'tux-sst-search-ph' => 'חיפוש בתרגומים',
	'tux-sst-count' => '{{PLURAL:$1|נמצאה תוצאה אחת|נמצאו $1 תוצאות}}',
	'tux-sst-facet-language' => 'שפות',
	'tux-sst-facet-group' => 'קבוצות הודעות',
	'tux-sst-facet-orphan' => '(יתומים)',
	'tux-sst-nosolr-title' => 'החיפוש אינו זמין',
	'tux-sst-nosolr-body' => 'בוויקי הזה אין שירות חיפוש בתרגומים.',
	'tux-sst-solr-offline-title' => 'החיפוש אינו זמין',
	'tux-sst-solr-offline-body' => 'זמנית שירות החיפוש אינו זמין.',
	'tux-sst-next' => 'התוצאות הבאות',
	'tux-sst-prev' => 'התוצאות הקודמות',
);

/** Upper Sorbian (hornjoserbsce)
 * @author Michawiki
 */
$messages['hsb'] = array(
	'searchtranslations' => 'Přełožki pytać',
	'tux-sst-edit' => 'Přełožk wobdźěłać',
	'tux-sst-search' => 'Pytać',
	'tux-sst-search-ph' => 'Přełožki pytać',
	'tux-sst-count' => '{{PLURAL:$1|Jedyn wuslědk namakany|$1 wuslědkaj namakanej|$1 wuslědki namakane|$1 wuslědkow namakane}}',
	'tux-sst-facet-language' => 'Rěče',
	'tux-sst-facet-group' => 'Zdźělenske skupiny',
	'tux-sst-facet-orphan' => '(wosyroćene)',
	'tux-sst-nosolr-title' => 'Pytanje k dispoziciji njesteji',
	'tux-sst-nosolr-body' => 'Tutón wiki nima słužbu za pytanje přełožkow.',
	'tux-sst-solr-offline-title' => 'Pytanje k dispoziciji njesteji',
	'tux-sst-solr-offline-body' => 'Pytanska słužba tuchwilu k dispoziciji njesteji.',
);

/** Interlingua (interlingua)
 * @author McDutchie
 */
$messages['ia'] = array(
	'searchtranslations' => 'Cercar traductiones',
	'tux-sst-edit' => 'Modificar traduction',
	'tux-sst-search' => 'Cercar',
	'tux-sst-search-ph' => 'Cerca in traductiones',
	'tux-sst-count' => '{{PLURAL:$1|Un resultato trovate|$1 resultatos trovate}}',
	'tux-sst-facet-language' => 'Linguas',
	'tux-sst-facet-group' => 'Gruppos de messages',
	'tux-sst-facet-orphan' => '(orphano)',
	'tux-sst-nosolr-title' => 'Recerca indisponibile',
	'tux-sst-nosolr-body' => 'Iste wiki non ha un servicio de recerca de traductiones.',
	'tux-sst-solr-offline-title' => 'Recerca indisponibile',
	'tux-sst-solr-offline-body' => 'Le servicio de recerca es temporarimente indisponibile.',
	'tux-sst-next' => 'Sequente resultatos',
	'tux-sst-prev' => 'Precedente resultatos',
);

/** Italian (italiano)
 * @author Beta16
 */
$messages['it'] = array(
	'searchtranslations' => 'Ricerca traduzioni',
	'tux-sst-edit' => 'Modifica traduzione',
	'tux-sst-search' => 'Cerca',
	'tux-sst-search-ph' => 'Cerca traduzioni',
	'tux-sst-count' => '{{PLURAL:$1|Un risultato trovato|$1 risultati trovati}}',
	'tux-sst-facet-language' => 'Lingue',
	'tux-sst-facet-group' => 'Gruppi di messaggi',
	'tux-sst-facet-orphan' => '(orfano)',
	'tux-sst-nosolr-title' => 'Ricerca non disponibile',
	'tux-sst-nosolr-body' => 'Questo wiki non ha un servizio di ricerca delle traduzioni.',
	'tux-sst-solr-offline-title' => 'Ricerca non disponibile',
	'tux-sst-solr-offline-body' => 'Il servizio di ricerca è temporaneamente non disponibile.',
	'tux-sst-next' => 'Risultati succesivi',
	'tux-sst-prev' => 'Risultati precedenti',
);

/** Japanese (日本語)
 * @author Shirayuki
 */
$messages['ja'] = array(
	'searchtranslations' => '翻訳の検索',
	'tux-sst-edit' => '翻訳を編集',
	'tux-sst-search' => '検索',
	'tux-sst-search-ph' => '翻訳の検索',
	'tux-sst-count' => '{{PLURAL:$1|$1 件見つかりました}}',
	'tux-sst-facet-language' => '言語',
	'tux-sst-facet-group' => 'メッセージ群',
	'tux-sst-facet-orphan' => '(孤立)',
	'tux-sst-nosolr-title' => '検索は利用できません',
	'tux-sst-nosolr-body' => 'このウィキには翻訳の検索サービスはありません。',
	'tux-sst-solr-offline-title' => '検索は利用できません',
	'tux-sst-solr-offline-body' => '検索サービスは一時的に利用できません。',
	'tux-sst-next' => '次の検索結果',
	'tux-sst-prev' => '前の検索結果',
);

/** Georgian (ქართული)
 * @author David1010
 */
$messages['ka'] = array(
	'searchtranslations' => 'თარგმანების ძიება',
	'tux-sst-edit' => 'თარგმანის რედაქტირება',
	'tux-sst-search' => 'ძიება',
	'tux-sst-search-ph' => 'თარგმანების ძიება',
	'tux-sst-count' => '{{PLURAL:$1|ნაპოვნია ერთი შედეგი|ნაპოვნია $1 შედეგი}}',
	'tux-sst-facet-language' => 'ენები',
	'tux-sst-facet-group' => 'შეტყობინების ჯგუფები',
);

/** Korean (한국어)
 * @author 아라
 */
$messages['ko'] = array(
	'searchtranslations' => '번역 찾기',
	'tux-sst-edit' => '번역 편집',
	'tux-sst-search' => '찾기',
	'tux-sst-search-ph' => '번역 찾기',
	'tux-sst-count' => '{{PLURAL:$1|결과 한 개를 찾았습니다|결과 $1개를 찾았습니다}}',
	'tux-sst-facet-language' => '언어',
	'tux-sst-facet-group' => '메시지 그룹',
	'tux-sst-facet-orphan' => '(외톨이)',
	'tux-sst-nosolr-title' => '찾기를 사용할 수 없음',
	'tux-sst-nosolr-body' => '이 위키는 번역 찾기 서비스가 없습니다.',
	'tux-sst-solr-offline-title' => '찾기를 사용할 수 없음',
	'tux-sst-solr-offline-body' => '찾기 서비스를 일시적으로 사용할 수 없습니다.',
	'tux-sst-next' => '다음 결과',
	'tux-sst-prev' => '이전 결과',
);

/** Karachay-Balkar (къарачай-малкъар)
 * @author Iltever
 */
$messages['krc'] = array(
	'tux-sst-facet-language' => 'Тилле',
);

/** Colognian (Ripoarisch)
 * @author Purodha
 */
$messages['ksh'] = array(
	'searchtranslations' => 'Övversäzonge söhke',
	'tux-sst-edit' => 'de Övversäzong ändere',
	'tux-sst-search' => 'Lohß jonn!',
	'tux-sst-search-ph' => 'Övversäzonge söhke',
	'tux-sst-count' => '{{PLURAL:$1|Eine|$1|Nix}} jefonge',
	'tux-sst-facet-language' => 'Schprooche',
	'tux-sst-facet-group' => 'Nohreeschtejroppe',
	'tux-sst-facet-orphan' => '(kein Jropp)',
	'tux-sst-nosolr-title' => 'Söhke es nit müjjelesch.',
	'tux-sst-nosolr-body' => 'En heh däm Wiki kammer nit noh Översäzonge söhke.',
	'tux-sst-solr-offline-title' => 'Söhke es nit müjjelesch.',
	'tux-sst-solr-offline-body' => 'Et Söhke es em Momang nit müjjelesch.',
	'tux-sst-next' => 'De nähkße Träffer',
	'tux-sst-prev' => 'De förrėje Träffer',
);

/** Kurdish (Latin script) (Kurdî (latînî)‎)
 * @author George Animal
 */
$messages['ku-latn'] = array(
	'searchtranslations' => 'Li wergeran bigere',
	'tux-sst-edit' => 'Wergerê biguherîne',
	'tux-sst-search' => 'Lêgerîn',
	'tux-sst-search-ph' => 'Li wergeran bigere',
	'tux-sst-count' => '{{PLURAL:$1|Encamek hat dîtin|$1 encam hatin dîtin}}',
	'tux-sst-facet-language' => 'Ziman',
	'tux-sst-facet-group' => 'Komên peyaman',
	'tux-sst-facet-orphan' => '(sêwî)',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Robby
 */
$messages['lb'] = array(
	'searchtranslations' => 'Iwwersetzunge sichen',
	'tux-sst-edit' => 'Iwwersetzung änneren',
	'tux-sst-search' => 'Sichen',
	'tux-sst-search-ph' => 'Iwwersetzunge sichen',
	'tux-sst-count' => '{{PLURAL:$1|Ee Resultat|$1 Resultater}} fonnt',
	'tux-sst-facet-language' => 'Sproochen',
	'tux-sst-facet-group' => 'Gruppe vu Messagen',
	'tux-sst-facet-orphan' => '(verwaist)',
	'tux-sst-nosolr-title' => 'Sichen ass net disponibel',
	'tux-sst-nosolr-body' => "Dës Wiki huet d'Sichfonctioun vun den Iwwersetzungen net.",
	'tux-sst-solr-offline-title' => 'Sichen ass net disponibel',
	'tux-sst-solr-offline-body' => "D'Sichfonctioun ass temporär net disponibel.",
	'tux-sst-next' => 'nächst Resultater',
	'tux-sst-prev' => 'Vireg Resultater',
);

/** Latvian (latviešu)
 * @author Papuass
 */
$messages['lv'] = array(
	'searchtranslations' => 'Meklēt tulkojumus',
	'tux-sst-edit' => 'Labot tulkojumu',
	'tux-sst-search' => 'Meklēt',
	'tux-sst-search-ph' => 'Meklēt tulkojumus',
	'tux-sst-count' => '{{PLURAL:$1|Viens rezultāts atrasts|$1 rezultāti atrasti}}',
	'tux-sst-facet-language' => 'Valodas',
	'tux-sst-nosolr-title' => 'Meklēšana nav pieejama',
	'tux-sst-solr-offline-title' => 'Meklēšana nav pieejama',
);

/** Macedonian (македонски)
 * @author Bjankuloski06
 */
$messages['mk'] = array(
	'searchtranslations' => 'Пребарување на преводи',
	'tux-sst-edit' => 'Уреди превод',
	'tux-sst-search' => 'Пребарај',
	'tux-sst-search-ph' => 'Пребарајте преводи',
	'tux-sst-count' => '{{PLURAL:$1|Пронајден е еден резултат|Пронајдени се $1 резултати}}',
	'tux-sst-facet-language' => 'Јазици',
	'tux-sst-facet-group' => 'Групи на пораки',
	'tux-sst-facet-orphan' => '(осамена)',
	'tux-sst-nosolr-title' => 'Пребарувањето е недостапно',
	'tux-sst-nosolr-body' => 'Ова вики нема пребарувач.',
	'tux-sst-solr-offline-title' => 'Пребарувањето е недостапно',
	'tux-sst-solr-offline-body' => 'Пребарувањето е привремено недостапно.',
	'tux-sst-next' => 'Следни резултати',
	'tux-sst-prev' => 'Претходни резултати',
);

/** Malay (Bahasa Melayu)
 * @author Anakmalaysia
 */
$messages['ms'] = array(
	'searchtranslations' => 'Cari terjemahan',
	'tux-sst-edit' => 'Suntingan terjemahan',
	'tux-sst-search' => 'Cari',
	'tux-sst-search-ph' => 'Cari terjemahan',
	'tux-sst-count' => '$1 hasil dijumpai',
	'tux-sst-facet-language' => 'Bahasa',
	'tux-sst-facet-group' => 'Message groups',
	'tux-sst-facet-orphan' => '(yatim)',
	'tux-sst-nosolr-title' => 'Tidak boleh mencari',
	'tux-sst-nosolr-body' => 'Wiki ini tiada ciri mencari terjemahan.',
	'tux-sst-solr-offline-title' => 'Tidak boleh mencari',
	'tux-sst-solr-offline-body' => 'Perkhidmatan pencarian tidak disediakan buat sementara waktu.',
	'tux-sst-next' => 'Hasil berikutnya',
	'tux-sst-prev' => 'Hasil terdahulu',
);

/** Dutch (Nederlands)
 * @author Siebrand
 */
$messages['nl'] = array(
	'searchtranslations' => 'Vertalingen zoeken',
	'tux-sst-edit' => 'Vertaling bewerken',
	'tux-sst-search' => 'Zoeken',
	'tux-sst-search-ph' => 'Vertalingen zoeken',
	'tux-sst-count' => '{{PLURAL:$1|Eén resultaat|$1 resultaten}} gevonden',
	'tux-sst-facet-language' => 'Talen',
	'tux-sst-facet-group' => 'Berichtengroepen',
	'tux-sst-facet-orphan' => '(wees)',
	'tux-sst-nosolr-title' => 'Zoeken is niet beschikbaar',
	'tux-sst-nosolr-body' => 'Deze wiki heeft geen dienst om vertalingen te zoeken.',
	'tux-sst-solr-offline-title' => 'Zoeken is niet beschikbaar',
	'tux-sst-solr-offline-body' => 'De zoekdienst is tijdelijk niet beschikbaar.',
	'tux-sst-next' => 'Volgende resultaten',
	'tux-sst-prev' => 'Vorige resultaten',
);

/** Norwegian Nynorsk (norsk nynorsk)
 * @author Njardarlogar
 */
$messages['nn'] = array(
	'searchtranslations' => 'Søk i omsetjingar',
	'tux-sst-edit' => 'Endra omsetjing',
	'tux-sst-search' => 'Søk',
	'tux-sst-search-ph' => 'Søk i omsetjingar',
	'tux-sst-count' => 'Fann {{PLURAL:$1|eitt|$1}} resultat',
	'tux-sst-facet-language' => 'Språk',
	'tux-sst-facet-group' => 'Meldingsgrupper',
	'tux-sst-nosolr-title' => 'Søket er ikkje tilgjengeleg',
	'tux-sst-nosolr-body' => 'Denne wikien har ikkje eit omsetjingssøk',
	'tux-sst-solr-offline-title' => 'Søket er ikkje tilgjengeleg',
	'tux-sst-solr-offline-body' => 'Søket er mellombels utilgjengeleg',
	'tux-sst-next' => 'Dei neste resultata',
	'tux-sst-prev' => 'Dei førre resultata',
);

/** Occitan (occitan)
 * @author Cedric31
 */
$messages['oc'] = array(
	'searchtranslations' => 'Recèrca de traduccions',
	'tux-sst-edit' => 'Modificar lo messatge', # Fuzzy
	'tux-sst-search' => 'Recercar',
	'tux-sst-facet-language' => 'Lengas',
	'tux-sst-facet-group' => 'Gropes de messatge',
	'tux-sst-facet-orphan' => '(orfanèl)',
	'tux-sst-nosolr-title' => 'Recèrca indisponibla',
);

/** Polish (polski)
 * @author Chrumps
 * @author Woytecr
 */
$messages['pl'] = array(
	'searchtranslations' => 'Szukaj tłumaczenia',
	'tux-sst-edit' => 'Edytuj tłumaczenie',
	'tux-sst-search' => 'Szukaj',
	'tux-sst-search-ph' => 'Szukaj tłumaczenia',
	'tux-sst-count' => 'Znaleziono {{PLURAL:$1|jeden wynik|$1 wyników}}',
	'tux-sst-facet-language' => 'Języki',
	'tux-sst-facet-group' => 'Grupa komunikatów',
	'tux-sst-nosolr-title' => 'Wyszukiwanie nie jest dostępne',
	'tux-sst-solr-offline-title' => 'Wyszukiwanie nie jest dostępne',
	'tux-sst-solr-offline-body' => 'Wyszukiwanie jest chwilowo niedostępne',
	'tux-sst-next' => 'Następne wyniki',
	'tux-sst-prev' => 'Poprzednie wyniki',
);

/** Piedmontese (Piemontèis)
 * @author Borichèt
 * @author Dragonòt
 */
$messages['pms'] = array(
	'searchtranslations' => 'Arserca ëd tradussion',
	'tux-sst-edit' => 'Modifiché la tradussion',
	'tux-sst-search' => 'Sërca',
	'tux-sst-search-ph' => 'Arserca ëd tradussion',
	'tux-sst-count' => '{{PLURAL:$1|Un arzultà trovà|$1 arzultà trovà}}',
	'tux-sst-facet-language' => 'Lenghe',
	'tux-sst-facet-group' => 'Partìe ëd mëssagi',
	'tux-sst-facet-orphan' => '(orfanin)',
	'tux-sst-nosolr-title' => 'Arserca nen disponìbil',
	'tux-sst-nosolr-body' => "La wiki a l'ha pa un servissi d'arserca ëd tradussion.",
);

/** Brazilian Portuguese (português do Brasil)
 * @author Luckas
 */
$messages['pt-br'] = array(
	'tux-sst-edit' => 'Editar tradução',
);

/** Romanian (română)
 * @author Minisarm
 */
$messages['ro'] = array(
	'searchtranslations' => 'Căutare traduceri',
	'tux-sst-edit' => 'Modifică traducerea',
	'tux-sst-search' => 'Caută',
	'tux-sst-search-ph' => 'Căutare traduceri',
	'tux-sst-count' => '{{PLURAL:$1|Un rezultat găsit|$1 rezultate găsite|$1 de rezultate găsite}}',
	'tux-sst-facet-language' => 'Limbi',
	'tux-sst-facet-group' => 'Grupuri de mesaje',
	'tux-sst-facet-orphan' => '(orfan)',
	'tux-sst-nosolr-title' => 'Căutarea nu este disponibilă',
	'tux-sst-nosolr-body' => 'Acest wiki nu dispune de un serviciu de căutare a traducerilor.',
	'tux-sst-solr-offline-title' => 'Căutarea nu este disponibilă',
	'tux-sst-solr-offline-body' => 'Serviciul de căutare este temporar indisponibil.',
	'tux-sst-next' => 'Rezultatele următoare',
	'tux-sst-prev' => 'Rezultatele anterioare',
);

/** tarandíne (tarandíne)
 * @author Joetaras
 */
$messages['roa-tara'] = array(
	'searchtranslations' => 'Cirche le traduziune',
	'tux-sst-edit' => "Cange 'a traduzione",
	'tux-sst-search' => 'Cirche',
	'tux-sst-search-ph' => 'Cirche le traduziune',
	'tux-sst-count' => "{{PLURAL:$1|'Nu resultate acchiate|$1 resultate acchiate}}",
	'tux-sst-facet-language' => 'Lènghe',
	'tux-sst-facet-group' => 'Gruppe de messàgge',
	'tux-sst-facet-orphan' => '(orfane)',
	'tux-sst-nosolr-title' => 'Ricerche non disponibbile',
	'tux-sst-nosolr-body' => "Sta uicchi non g'ave 'nu servizie de ricerche pe le traduziune.",
	'tux-sst-solr-offline-title' => 'Ricerche non disponibbile',
	'tux-sst-solr-offline-body' => "'U servizie de ricerche jè temboraneamende indisponibbile.",
	'tux-sst-next' => 'Prossime resultate',
	'tux-sst-prev' => 'Resultate precedende',
);

/** Russian (русский)
 * @author Kaganer
 * @author Lockal
 */
$messages['ru'] = array(
	'searchtranslations' => 'Поиск переводов',
	'tux-sst-edit' => 'Редактировать перевод',
	'tux-sst-search' => 'Найти',
	'tux-sst-search-ph' => 'Поиск переводов',
	'tux-sst-count' => '{{PLURAL:$1|Найден $1 результат|Найдены $1 результата|Найдено $1 результатов}}',
	'tux-sst-facet-language' => 'Языки',
	'tux-sst-facet-group' => 'Группы сообщений',
	'tux-sst-facet-orphan' => '(сирота)',
	'tux-sst-nosolr-title' => 'Поиск недоступен',
	'tux-sst-nosolr-body' => 'В этой вики отсутствует сервис поиска переводов.',
	'tux-sst-solr-offline-title' => 'Поиск недоступен',
	'tux-sst-solr-offline-body' => 'Служба поиска временно недоступна.',
	'tux-sst-next' => 'Следующие результаты',
	'tux-sst-prev' => 'Предыдущие результаты',
);

/** Serbian (Cyrillic script) (српски (ћирилица)‎)
 * @author Милан Јелисавчић
 */
$messages['sr-ec'] = array(
	'searchtranslations' => 'Претрага превода',
	'tux-sst-edit' => 'Уреди превод',
	'tux-sst-search' => 'Претражи',
	'tux-sst-search-ph' => 'Претрага превода',
	'tux-sst-count' => '{{PLURAL:$1|Један резултат пронађен|$1 резултата пронађено}}',
	'tux-sst-facet-language' => 'Језици',
	'tux-sst-facet-group' => 'Групе порука',
	'tux-sst-facet-orphan' => '(сироче)',
	'tux-sst-nosolr-title' => 'Претрага недоступна',
	'tux-sst-nosolr-body' => 'Овај вики нема сервис за претрагу превода.',
	'tux-sst-solr-offline-title' => 'Претрага недоступна',
	'tux-sst-solr-offline-body' => 'Сервис за претрагу је привремено недоступан.',
	'tux-sst-next' => 'Следећи резултати',
	'tux-sst-prev' => 'Претходни резултати',
);

/** Swedish (svenska)
 * @author WikiPhoenix
 */
$messages['sv'] = array(
	'searchtranslations' => 'Sök översättningar',
	'tux-sst-edit' => 'Redigera översättning',
	'tux-sst-search' => 'Sök',
	'tux-sst-search-ph' => 'Sök översättningar',
	'tux-sst-count' => '{{PLURAL:$1|Ett|$1}} resultat hittades',
	'tux-sst-facet-language' => 'Språk',
	'tux-sst-facet-group' => 'Meddelandegrupper',
	'tux-sst-facet-orphan' => '(föräldralös)',
	'tux-sst-nosolr-title' => 'Sökning är inte tillgänglig',
	'tux-sst-nosolr-body' => 'Denna wiki har inte en tjänst för att söka efter översättningar.',
	'tux-sst-solr-offline-title' => 'Sökning är inte tillgänglig',
	'tux-sst-solr-offline-body' => 'Söktjänsten är inte tillgänglig för tillfället.',
	'tux-sst-next' => 'Nästa resultat',
	'tux-sst-prev' => 'Föregående resultat',
);

/** Turkish (Türkçe)
 * @author Emperyan
 */
$messages['tr'] = array(
	'searchtranslations' => 'Çevirileri ara',
	'tux-sst-edit' => 'İletiyi düzenleme', # Fuzzy
	'tux-sst-search' => 'Ara',
	'tux-sst-search-ph' => 'Çevirileri ara',
	'tux-sst-count' => '{{PLURAL:$1|Bir sonuç bulundu|$1 sonuç bulundu}}',
	'tux-sst-facet-language' => 'Diller',
	'tux-sst-facet-group' => 'İleti grupları',
	'tux-sst-facet-orphan' => '(yetim)',
);

/** Ukrainian (українська)
 * @author Base
 * @author Ата
 */
$messages['uk'] = array(
	'searchtranslations' => 'Пошук перекладів',
	'tux-sst-edit' => 'Редагувати переклад',
	'tux-sst-search' => 'Пошук',
	'tux-sst-search-ph' => 'Пошук перекладів',
	'tux-sst-count' => '{{PLURAL:$1|Знайдено один результат|Знайдено $1 результати|Знайдено $1 результатів}}',
	'tux-sst-facet-language' => 'Мови',
	'tux-sst-facet-group' => 'Групи повідомлень',
	'tux-sst-facet-orphan' => '(сирота)',
	'tux-sst-nosolr-title' => 'Пошук недоступний',
	'tux-sst-nosolr-body' => 'У цій вікі немає служби пошуку перекладів.',
	'tux-sst-solr-offline-title' => 'Пошук не доступний',
	'tux-sst-solr-offline-body' => 'Сервіс пошуку тимчасово недоступний.',
	'tux-sst-next' => 'Наступні результати',
	'tux-sst-prev' => 'Попередні результати',
);

/** Vietnamese (Tiếng Việt)
 * @author Minh Nguyen
 */
$messages['vi'] = array(
	'searchtranslations' => 'Tìm bản dịch',
	'tux-sst-edit' => 'Sửa bản dịch',
	'tux-sst-search' => 'Tìm kiếm',
	'tux-sst-search-ph' => 'Tìm bản dịch',
	'tux-sst-count' => 'Tìm thấy $1 kết quả',
	'tux-sst-facet-language' => 'Ngôn ngữ',
	'tux-sst-facet-group' => 'Nhóm thông điệp',
	'tux-sst-facet-orphan' => '(mồ côi)',
	'tux-sst-nosolr-title' => 'Công cụ tìm kiếm không có sẵn',
	'tux-sst-nosolr-body' => 'Wiki này không có công cụ tìm bản dịch.',
	'tux-sst-solr-offline-title' => 'Công cụ tìm kiếm không có sẵn',
	'tux-sst-solr-offline-body' => 'Công cụ tìm kiếm tạm thời không sẵn dùng.',
	'tux-sst-next' => 'Kết quả sau',
	'tux-sst-prev' => 'Kết quả trước',
);

/** Yiddish (ייִדיש)
 * @author פוילישער
 */
$messages['yi'] = array(
	'tux-sst-search' => 'זוכן',
	'tux-sst-facet-language' => 'שפּראַכן',
	'tux-sst-facet-group' => 'מעלדונג גרופעס',
	'tux-sst-facet-orphan' => '(יתום)',
);

/** Simplified Chinese (中文（简体）‎)
 * @author Hydra
 * @author Li3939108
 * @author Yfdyh000
 */
$messages['zh-hans'] = array(
	'searchtranslations' => '搜索翻译',
	'tux-sst-edit' => '编辑翻译',
	'tux-sst-search' => '搜索',
	'tux-sst-search-ph' => '搜索翻译',
	'tux-sst-count' => '找到$1个结果',
	'tux-sst-facet-language' => '语言',
	'tux-sst-facet-group' => '信息组',
	'tux-sst-facet-orphan' => '（孤立）',
	'tux-sst-nosolr-title' => '搜索不可用',
	'tux-sst-nosolr-body' => '此wiki没有翻译搜索服务。',
	'tux-sst-solr-offline-title' => '搜索不可用',
	'tux-sst-solr-offline-body' => '搜索服务暂时不可用。',
	'tux-sst-next' => '下一个结果',
	'tux-sst-prev' => '上一个结果',
);

/** Traditional Chinese (中文（繁體）‎)
 * @author Simon Shek
 */
$messages['zh-hant'] = array(
	'searchtranslations' => '搜尋翻譯',
	'tux-sst-edit' => '編輯翻譯',
	'tux-sst-search' => '搜尋',
	'tux-sst-search-ph' => '搜尋翻譯',
	'tux-sst-count' => '{{PLURAL:$1|找到一個結果|找到$1個結果}}',
	'tux-sst-facet-language' => '語言',
	'tux-sst-facet-group' => '訊息組',
	'tux-sst-facet-orphan' => '（孤立）',
	'tux-sst-nosolr-title' => '無法使用搜尋',
	'tux-sst-nosolr-body' => '此wiki沒有翻譯搜尋。',
	'tux-sst-solr-offline-title' => '無法使用搜尋',
	'tux-sst-solr-offline-body' => '暫時無法使用搜尋。',
	'tux-sst-next' => '下一個結果',
	'tux-sst-prev' => '上一個結果',
);
