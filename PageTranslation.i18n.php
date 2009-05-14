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
	'tpt-oldrevision' => '$2 is not the latest version of the page [[$1]].
Only latest versions can be marked for translation.',
	'tpt-notsuitable' => 'Page $1 is not suitable for translation.
Make sure it has <nowiki><translate></nowiki> tags and has a valid syntax.',
	'tpt-saveok' => 'The page "$1" has been marked up for translation with $2 translatable {{PLURAL:$2|section|sections}}.
The page can now be <span class="plainlinks">[$3 translated]</span>.',
	'tpt-badsect' => '"$1" is not a valid name for section $2.',
	'tpt-deletedsections' => 'The following {{PLURAL:$1|section|sections}} will no longer be used:',
	'tpt-showpage-intro' => 'Below new, existing and deleted sections are listed.
Before marking this version for translation, check that the changes to sections are minimised to avoid unnecessary work for translators.',
	'tpt-mark-summary' => 'Marked this version for translation',
	'tpt-edit-failed' => 'Could not update the page: $1',
	'tpt-already-marked' => 'The latest version of this page has already been marked for translation.',

	# Page list on the special page
	'tpt-list-nopages' => 'No pages are marked for translation nor ready to be marked for translation.',
	'tpt-old-pages' => 'Some version of {{PLURAL:$1|this page has|these pages have}} been marked for translation.',
	'tpt-new-pages' => '{{PLURAL:$1|This page contains|These pages contain}} text with translation tags, but no version of {{PLURAL:$1|this page is|these pages are}} currently marked for translation.',
	'tpt-rev-latest' => 'latest version',
	'tpt-rev-old' => 'version $1',
	'tpt-rev-mark-new' => 'mark this version for translation',
	'tpt-translate-this' => 'translate this page',

	# Source and translation page headers
	'translate-tag-translate-link-desc' => 'Translate this page',
	'translate-tag-markthis' => 'Mark this page for translation',
	'tpt-translation-intro' => 'This page is a <span class="plainlinks">[$1 translated version]</span> of a page [[$2]] and the translation is $3% complete and up to date.
<span class="mw-translate-fuzzy">Outdated translations are marked like this.</span>',

	'tpt-languages-legend' => 'Other languages:',

	'tpt-target-page' => 'This page cannot be updated manually.
This page is a translation of page [[$1]] and the translation can be updated using [$2 the translation tool].',
	'tpt-unknown-page' => 'This namespace is reserved for content page translations.
The page you are trying to edit does not seem to correspond any page marked for translation.',

	'tpt-install' => 'Run php maintenance/update.php or web install to enable page translation feature.',
);

/** Message documentation (Message documentation)
 * @author EugeneZelenko
 * @author Purodha
 */
$messages['qqq'] = array(
	'tpt-desc' => 'Short description of this extension, shown on [[Special:Version]]. Do not translate or change links.',
	'tpt-old-pages' => 'The words "some version" refer to "one version of the page", or "a single version of each of the pages", respectively. Each page can have either one or none of its versions marked for translaton.',
	'tpt-rev-old' => '{{Identical|Version}}',
	'tpt-saveok' => '$1 is a page title,
$2 is a count of sections which can be used with PLURAL,
$3 is an URL.',
	'tpt-deletedsections' => '$1 is a count of sections.',
);

/** Arabic (العربية)
 * @author Meno25
 */
$messages['ar'] = array(
	'pagetranslation' => 'ترجمة صفحة',
	'tpt-section' => 'القسم:',
	'tpt-section-new' => 'قسم جديد:',
	'tpt-diff-old' => 'نص سابق',
	'tpt-diff-new' => 'نص جديد',
	'tpt-rev-latest' => 'آخر نسخة',
	'tpt-rev-old' => 'النسخة $1',
	'translate-tag-translate-link-desc' => 'ترجمة هذه الصفحة',
);

/** Belarusian (Taraškievica orthography) (Беларуская (тарашкевіца))
 * @author EugeneZelenko
 * @author Jim-by
 */
$messages['be-tarask'] = array(
	'pagetranslation' => 'Пераклад старонкі',
	'right-pagetranslation' => 'пазначаць вэрсіяў старонак для перакладу',
	'tpt-desc' => 'Пашырэньне для перакладу старонак зьместу',
	'tpt-section' => 'Сэкцыя:',
	'tpt-section-new' => 'Новы сэкцыя:',
	'tpt-diff-old' => 'Папярэдні тэкст',
	'tpt-diff-new' => 'Новы тэкст',
	'tpt-submit' => 'Пазначыць гэту вэрсію для перакладу',
	'tpt-badtitle' => 'Пададзеная назва старонкі ($1) не зьяўляецца слушнай',
	'tpt-oldrevision' => '$2 не зьяўляецца апошняй вэрсіяй старонкі [[$1]].
Толькі апошнія вэрсіі могуць пазначацца для перакладу.',
	'tpt-notsuitable' => 'Старонка $1 ня можа быць перакладзеная.
Упэўніцеся, што яна ўтрымлівае тэгі <nowiki><translate></nowiki> і мае слушны сынтаксіс.',
	'tpt-saveok' => 'Старонка «$1» была пазначаная для перакладу з $2 сэкцыямі.
Зараз старонка можа быць <span class="plainlinks">[$3 перакладзеная]</span>.',
	'tpt-badsect' => '«$1» не зьяўляецца слушнай назвай для сэкцыі $2.',
	'tpt-deletedsections' => 'Наступныя сэкцыі ня будуць болей выкарыстоўвацца:',
	'tpt-showpage-intro' => 'Ніжэй знаходзяцца новыя, існуючыя і выдаленыя сэкцыі.
Перад пазначэньнем гэтай вэрсіі для перакладу, праверце зьмены ў сэкцыях для таго, каб пазьбегнуць непатрэбнай працы для перакладчыкаў.',
	'tpt-mark-summary' => 'Пазначыў гэтую вэрсію для перакладу',
	'tpt-edit-failed' => 'Немагчыма абнавіць старонку: $1',
	'tpt-already-marked' => 'Апошняя вэрсія гэтай старонкі ўжо была пазначана для перакладу.',
	'tpt-list-nopages' => 'Старонкі для перакладу не пазначаныя альбо не падрыхтаваныя.',
	'tpt-old-pages' => 'Некаторыя вэрсіі {{PLURAL:$1|гэтай старонкі|гэтых старонак}} былі пазначаны для перакладу.',
	'tpt-new-pages' => '{{PLURAL:$1|Гэта старонка ўтрымлівае|Гэтыя старонкі ўтрымліваюць}} тэкст з тэгамі перакладу, але {{PLURAL:$1|пазначанай для перакладу вэрсіі гэтай старонкі|пазначаных для перакладу вэрсіяў гэтых старонак}} няма.',
	'tpt-rev-latest' => 'апошняя вэрсія',
	'tpt-rev-old' => 'вэрсія $1',
	'tpt-rev-mark-new' => 'пазначыць гэту вэрсію для перакладу',
	'tpt-translate-this' => 'перакласьці гэту старонку',
	'translate-tag-translate-link-desc' => 'Перакласьці гэту старонку',
	'translate-tag-markthis' => 'Пазначыць гэту старонку для перакладу',
	'tpt-translation-intro' => 'Гэта старонка <span class="plainlinks">[$1 перакладзеная вэрсія]</span> старонкі [[$2]], пераклад завершаны на $3%.
<span class="mw-translate-fuzzy">Састарэлыя пераклады пазначаныя такім чынам.</span>',
	'tpt-languages-legend' => 'Іншыя мовы:',
	'tpt-target-page' => 'Гэта старонка ня можа быць абноўлена ўручную.
Гэта старонка зьяўляецца перакладам старонкі [[$1]], пераклад можа быць абноўлены з выкарыстаньнем [$2 інструмэнта перакладу].',
	'tpt-unknown-page' => 'Гэта прастора назваў зарэзэрваваная для перакладаў старонак зьместу.
Старонка, якую Вы спрабуеце рэдагаваць, верагодна не зьвязана зь якой-небудзь старонкай пазначанай для перакладу.',
);

/** Bosnian (Bosanski)
 * @author CERminator
 */
$messages['bs'] = array(
	'pagetranslation' => 'Prijevod stranice',
	'right-pagetranslation' => 'Označanje verzija stranica za prevođenje',
	'tpt-desc' => 'Proširenje za prevođenje stranica sadržaja',
	'tpt-section' => 'Sekcija:',
	'tpt-section-new' => 'Nova sekcija:',
	'tpt-diff-old' => 'Prethodni tekst',
	'tpt-diff-new' => 'Novi tekst',
	'tpt-submit' => 'Označi ovu verziju za prevođenje',
	'tpt-badtitle' => 'Zadano ime stranice ($1) nije valjan naslov',
	'tpt-notsuitable' => 'Stranica $1 nije pogodna za prevođenje.
Provjerite da postoje oznake <nowiki><translate></nowiki> i da ima valjanu sintaksu.',
	'tpt-edit-failed' => 'Nije moguće ažurirati stranicu: $1',
	'tpt-already-marked' => 'Posljednja verzija ove stranice je već označena za prevođenje.',
	'tpt-rev-latest' => 'posljednja verzija',
	'tpt-rev-old' => 'verzija $1',
	'tpt-translate-this' => 'prevedi ovu stranicu',
	'translate-tag-translate-link-desc' => 'Prevedi ovu stranicu',
	'translate-tag-markthis' => 'Označi ovu stranicu za prevođenje',
	'tpt-languages-legend' => 'Drugi jezici:',
);

/** Catalan (Català)
 * @author Jordi Roqué
 * @author SMP
 */
$messages['ca'] = array(
	'tpt-section' => 'Secció:',
	'tpt-section-new' => 'Nova secció:',
	'tpt-diff-old' => 'Text anterior',
	'tpt-diff-new' => 'Text nou',
	'tpt-badtitle' => 'El nom de pàgina donat ($1) no és un títol vàlid',
	'tpt-notsuitable' => 'La pàgina $1 no està preparada per a la seva traducció.
Assegureu-vos que té les etiquetes <nowiki><translate></nowiki> i una sintaxi vàlida.',
	'translate-tag-translate-link-desc' => 'Traduir aquesta pàgina',
	'tpt-languages-legend' => 'Altres idiomes:',
);

/** German (Deutsch)
 * @author Purodha
 * @author Umherirrender
 */
$messages['de'] = array(
	'pagetranslation' => 'Übersetzung von Seiten',
	'right-pagetranslation' => 'Seitenversionen für die Übersetzung markieren',
	'tpt-desc' => 'Erweiterung zur Übersetzung von Wikiseiten',
	'tpt-section' => 'Abschnitt:',
	'tpt-section-new' => 'Neuer Abschnitt:',
	'tpt-diff-old' => 'Vorheriger Text',
	'tpt-diff-new' => 'Neuer Text',
	'tpt-submit' => 'Diese Version zur Übersetzung markieren',
	'tpt-badtitle' => 'Der angegebene Seitenname „$1“ ist kein gültiger Titel',
	'tpt-oldrevision' => '$2 ist nicht die letzte Version der Seite [[$1]].
Nur die letzte Version kann zur Übersetzung markiert werden.',
	'tpt-notsuitable' => 'Die Seite $1 ist nicht zum Übersetzen geeignet.
Stelle sicher, das ein <nowiki><translate></nowiki>-Tag und gültige Syntax verwendet wird.',
	'tpt-saveok' => 'Die Seite „$1“ mit $2 übersetzbaren Abschnitten wurde für die Übersetzung markiert.
Diese Seite kann nun <span class="plainlinks">[$3 übersetzt]</span> werden.',
	'tpt-badsect' => '„$1“ ist kein gültiger Name für Abschnitt $2.',
	'tpt-deletedsections' => 'Die folgenden Abschnitte werden nicht länger genutzt:',
	'tpt-mark-summary' => 'Diese Seite wurde zum Übersetzen markiert',
	'tpt-edit-failed' => 'Seite kann nicht aktualisiert werden: $1',
	'tpt-already-marked' => 'Die letzte Version dieser Seite wurde bereits zur Übersetzung markiert.',
	'tpt-old-pages' => 'Eine Version dieser {{PLURAL:$1|Seite|Seiten}} wurde zur Übersetzung markiert.',
	'tpt-new-pages' => '{{PLURAL:$1|Diese Seite beinhaltet|Diese Seiten beinhalten}} Text zum Übersetzen, aber es wurde noch keine Version dieser {{PLURAL:$1|Seite|Seiten}} zur Übersetzung markiert.',
	'tpt-rev-latest' => 'Letzte Version',
	'tpt-rev-old' => 'Version $1',
	'tpt-rev-mark-new' => 'diese Version zur Übersetzung markieren',
	'tpt-translate-this' => 'diese Seite übersetzen',
	'translate-tag-translate-link-desc' => 'Diese Seite übersetzen',
	'translate-tag-markthis' => 'Diese Seite zur Übersetzung markieren',
	'tpt-translation-intro' => 'Diese Seite ist eine <span class="plainlinks">[$1 übersetzte Version]</span> der Seite [[$2]] und die Übersetzung ist zu $3 % abgeschlossen und aktuell.
<span class="mw-translate-fuzzy">Nicht aktuelle Übersetzungen werden wie dieser Text markiert.</span>',
	'tpt-languages-legend' => 'Andere Sprachen:',
	'tpt-target-page' => 'Diese Seite kann nicht manuell aktualisiert werden.
Diese Seite ist eine Übersetzung der Seite [[$1]] und die Übersetzung kann mithilfe des [$2 Übersetzungswerkzeuges] aktualisiert werden.',
	'tpt-unknown-page' => 'Dieser Namensraum ist für das Übersetzen von Wikiseiten reserviert.
Die Seite, die gerade bearbeitet wird, hat keine Verbindung zu einer übersetzbaren Seite.',
);

/** Lower Sorbian (Dolnoserbski)
 * @author Michawiki
 */
$messages['dsb'] = array(
	'pagetranslation' => 'Pśełožowanje bokow',
	'right-pagetranslation' => 'Wersije bokow za pśełožowanje markěrowaś',
	'tpt-desc' => 'Rozšyrjenje za pśełožowanje wopśimjeśowych bokow',
	'tpt-section' => 'Wótrězk:',
	'tpt-section-new' => 'Nowy wótrězk:',
	'tpt-diff-old' => 'Pśedchadny tekst',
	'tpt-diff-new' => 'Nowy tekst',
	'tpt-submit' => 'Toś tu wersiju za pśełožowanje markěrowaś',
	'tpt-badtitle' => 'Pódane bokowe mě ($1) njejo płaśiwy titel',
	'tpt-oldrevision' => '$2 njejo aktualna wersija boka [[$1]].
Jano aktualne wersije daju se za pśełožowanje markěrowaś.',
	'tpt-notsuitable' => 'Bok $1 njejo gódny za pśełožowanje.
Zawěsć, až ma toflicki <nowiki><translate></nowiki> a płaśiwu syntaksu.',
	'tpt-saveok' => 'Bok "$1" jo se markěrował za pśełožowanje z $2 {{PLURAL:$2|pśełožujobnym wótrězkom|pśełožujobnyma wótrězkoma|pśełožujobnymi wótrězkami|pśełožujobnymi wótrězkami}}. Bok móže se něnto <span class="plainlinks">[$3 pśełožowaś]</span>.',
	'tpt-badsect' => '"$1" njejo płaśiwe mě za wótrězk $2.',
	'tpt-deletedsections' => 'Slědujuce wótrězki se južo njewužywaju:',
	'tpt-showpage-intro' => 'Dołojce su nowe, eksistěrujuce a wulašowane wótrězki nalicone.
Nježli až markěrujoš toś tu wersiju za pśełožowanje, pśekontrolěruj, lěc změny na wótrězkach su zminiměrowane, aby se wobinuł njetrěbne źěło za pśełožowarjow.',
	'tpt-mark-summary' => 'Jo toś tu wersiju za pśełožowanje markěrował',
	'tpt-edit-failed' => 'Toś ten bok njejo se dał aktualizěrowaś: $1',
	'tpt-already-marked' => 'Aktualna wersija toś togo boka jo južo za pśełožowanje markěrowana.',
	'tpt-list-nopages' => 'Žedne boki njejsu za pśełožowanje markěrowane ani su gótowe, aby se za pśełožowanje markěrowali.',
	'tpt-old-pages' => 'Někaka wersija {{PLURAL:$1|toś togo boka|toś teju bokowu|toś tych bokow|toś tych bokow}} jo se za pśełožowanje markěrowała.',
	'tpt-new-pages' => '{{PLURAL:$1|Toś ten bok wopśimujo|Toś tej boka wopśumujotej|Toś te boki wopśimuju|Toś te boki wopśimuju}} tekst z pśełožowańskimi toflickami, ale žedna wersija {{PLURAL:$1|toś togo boka|toś teju bokowu|toś tych bokow|toś tych bokow}} njejo tuchylu za pśełožowanje markěrowana.',
	'tpt-rev-latest' => 'aktualna wersija',
	'tpt-rev-old' => 'wersija $1',
	'tpt-rev-mark-new' => 'toś tu wersiju za pśełožowanje markěrowaś',
	'tpt-translate-this' => 'toś ten bok pśełožyś',
	'translate-tag-translate-link-desc' => 'Toś ten bok pśełožyś',
	'translate-tag-markthis' => 'Toś ten bok za pśełožowanje markěrowaś',
	'tpt-translation-intro' => 'Toś ten bok jo <span class="plainlinks">[$1 pśełožona wersija]</span> boka [[$2]] a $3 % pśełožka jo dogótowane a pśełožk jo aktualne.
<span class="mw-translate-fuzzy">Zestarjone pśełožki se ako toś ten markěruju.</span>',
	'tpt-languages-legend' => 'Druge rěcy:',
	'tpt-target-page' => 'Toś ten bok njedajo se manuelnje aktualizěrowaś.
Toś ten bok jo pśełožk boka [[$1]] a pśełožk dajo se z pomocu [$2 Pśełožyś] aktualizěrowaś.',
	'tpt-unknown-page' => 'Toś ten mjenjowy rum jo za pśełožki wopśimjeśowych bokow wuměnjony.
Zda se, až bok, kótaryž wopytujoš wobźěłaś, njewótpowědujo bokoju, kótaryž jo za pśełožowanje markěrowany.',
	'tpt-install' => 'Wuwjeź php maintenance/update.php abo webinstalaciju, aby zmóžnił funkciju pśełožowanja bokow.',
);

/** French (Français)
 * @author Crochet.david
 * @author Grondin
 * @author IAlex
 */
$messages['fr'] = array(
	'pagetranslation' => 'Traduction de pages',
	'right-pagetranslation' => 'Marquer des versions de pages pour être traduites',
	'tpt-desc' => 'Extension pour traduire des pages de contenu',
	'tpt-section' => 'Section :',
	'tpt-section-new' => 'Nouvelle section :',
	'tpt-diff-old' => 'Texte précédent',
	'tpt-diff-new' => 'Nouveau texte',
	'tpt-submit' => 'Marquer cette version pour être traduite',
	'tpt-badtitle' => "Le nom de page donné ($1) n'est pas un titre valide",
	'tpt-oldrevision' => "$2 n'est pas la dernière version de la page [[$1]].
Seule la dernière version de la page peut être marquée pour être traduite.",
	'tpt-notsuitable' => "La page $1 n'est pas convenable pour être traduite.
Soyez sûr qu'elle contient la balise <nowiki><translate></nowiki> et qu'elle a une syntaxe correcte.",
	'tpt-saveok' => 'La page « $1 » a été marqué pour être traduite avec $2 sections traduisibles.
La page peut être <span class="plainlinks">[$3 traduite]</span> dès maintenant.',
	'tpt-badsect' => "« $1 » n'est pas un nom valide pour la section $2.",
	'tpt-deletedsections' => 'Les sections suivantes ne seront plus utilisées :',
	'tpt-showpage-intro' => 'Ci-dessous, les nouvelles traductions, celles existantes et supprimées.
Avant de marquer ces versions pour être traduites, vérifier que les modifications aux sections sont minimisées pour éviter du travail inutile aux traducteurs.',
	'tpt-mark-summary' => 'Cette version a été marqué pour être traduite',
	'tpt-edit-failed' => 'Impossible de mettre à jour la page $1',
	'tpt-already-marked' => 'La dernière version de cette page a déjà été marquée pour être traduite.',
	'tpt-list-nopages' => "Aucune page n'a été marquée pour être traduite ou prête pour l'être.",
	'tpt-old-pages' => 'Des versions de {{PLURAL:$1|cette page|ces pages}} ont été marquées pour être traduites.',
	'tpt-new-pages' => "{{PLURAL:$1|Cette page contient|Ces pages contiennent}} du texte avec des balises de traduction, mais aucune version de {{PLURAL:$1|cette page n'est marqué pour être traduite|ces page ne sont marquées pour être traduites}}.",
	'tpt-rev-latest' => 'dernière version',
	'tpt-rev-old' => 'version $1',
	'tpt-rev-mark-new' => 'marquer cette version pour être traduite',
	'tpt-translate-this' => 'traduire cette page',
	'translate-tag-translate-link-desc' => 'Traduire cette page',
	'translate-tag-markthis' => 'Marquer cette page pour être traduite',
	'tpt-translation-intro' => 'Cette page est une <span class="plainlinks">[$1 traduction]</span> de la page [[$2]] et la traduction est complétée à $3 % et à jour.
<span class="mw-translate-fuzzy">Les traductions non à jour sont marqué comme ceci.</span>',
	'tpt-languages-legend' => 'Autres langues :',
	'tpt-target-page' => "Cette page ne peut pas être mise à jour manuellement.
Elle est une version traduite de [[$1]] et la traduction peut être mise à jour en utilisant [$2 l'outil de traduction].",
	'tpt-unknown-page' => 'Cet espace de noms est réservé pour la traduction de pages.
La page que vous essayé de modifier ne semble pas correspondre à aucune page marqué pour être traduite.',
	'tpt-install' => "Lancez php maintenance/update.php ou l'installation web pour activer la fonctionnalité de traduction de pages.",
);

/** Franco-Provençal (Arpetan)
 * @author ChrisPtDe
 */
$messages['frp'] = array(
	'pagetranslation' => 'Traduccion de pâges',
	'right-pagetranslation' => 'Marcar des vèrsions de pâges por étre traduites',
	'tpt-desc' => 'Èxtension por traduire des pâges de contegnu.',
	'tpt-section' => 'Sèccion :',
	'tpt-section-new' => 'Novèla sèccion :',
	'tpt-diff-old' => 'Tèxte devant',
	'tpt-diff-new' => 'Novél tèxte',
	'tpt-submit' => 'Marcar ceta vèrsion por étre traduita',
	'tpt-badtitle' => 'Lo nom de pâge balyê ($1) est pas un titro valido',
	'tpt-oldrevision' => '$2 est pas la dèrriére vèrsion de la pâge [[$1]].
Solament la dèrriére vèrsion de la pâge pôt étre marcâ por étre traduita.',
	'tpt-notsuitable' => 'La pâge $1 est pas convegnâbla por étre traduita.
Seyâd de sûr que contint la balisa <nowiki><translate></nowiki> et qu’at una sintaxa justa.',
	'tpt-saveok' => 'La pâge « $1 » at étâ marcâ por étre traduita avouéc $2 sèccions traduisibles.
La pâge pôt étre <span class="plainlinks">[$3 traduita]</span> dês ora.',
	'tpt-badsect' => '« $1 » est pas un nom valido por la sèccion $2.',
	'tpt-deletedsections' => 'Cetes sèccions seront pas més utilisâs :',
	'tpt-showpage-intro' => 'Ce-desot, les novèles traduccions, celes ègzistentes et suprimâs.
Devant que marcar cetes vèrsions por étre traduites, controlâd que los changements a les sèccions sont petiôts por èvitar de travâly inutilo ux traductors.',
	'tpt-mark-summary' => 'Ceta vèrsion at étâ marcâ por étre traduita',
	'tpt-edit-failed' => 'Empossiblo de betar a jorn la pâge $1',
	'tpt-already-marked' => 'La dèrriére vèrsion de ceta pâge at ja étâ marcâ por étre traduita.',
	'tpt-list-nopages' => 'Niona pâge at étâ marcâ por étre traduita ou ben prèsta por l’étre.',
	'tpt-old-pages' => 'Des vèrsions de {{PLURAL:$1|ceta pâge|cetes pâges}} ont étâ marcâs por étre traduites.',
	'tpt-rev-latest' => 'dèrriére vèrsion',
	'tpt-rev-old' => 'vèrsion $1',
	'tpt-rev-mark-new' => 'marcar ceta vèrsion por étre traduita',
	'tpt-translate-this' => 'traduire ceta pâge',
	'translate-tag-translate-link-desc' => 'Traduire ceta pâge',
	'translate-tag-markthis' => 'Marcar ceta pâge por étre traduita',
	'tpt-translation-intro' => 'Ceta pâge est una <span class="plainlinks">[$1 traduccion]</span> de la pâge [[$2]] et la traduccion est complètâ a $3 % et a jorn.
<span class="mw-translate-fuzzy">Les traduccions dèpassâs sont marcâs d’ense.</span>',
	'tpt-languages-legend' => 'Ôtres lengoues :',
	'tpt-target-page' => 'Ceta pâge pôt pas étre betâ a jorn a la man.
El est una vèrsion traduita de [[$1]] et la traduccion pôt étre betâ a jorn en utilisent [$2 l’outil de traduccion].',
);

/** Galician (Galego)
 * @author Toliño
 */
$messages['gl'] = array(
	'pagetranslation' => 'Tradución da páxina',
	'right-pagetranslation' => 'Marcar as versións de páxinas para seren traducidas',
	'tpt-desc' => 'Extensión para traducir contidos de páxinas',
	'tpt-section' => 'Sección:',
	'tpt-section-new' => 'Nova sección:',
	'tpt-diff-old' => 'Texto anterior',
	'tpt-diff-new' => 'Texto novo',
	'tpt-submit' => 'Marcar esta versión para ser traducida',
	'tpt-badtitle' => 'O nome de páxina dado ("$1") non é un título válido',
	'tpt-oldrevision' => '$2 non é a última versión da páxina "[[$1]]".
Só as últimas versións poden ser marcadas para seren traducidas.',
	'tpt-notsuitable' => 'A páxina "$1" non é válida para ser traducida.
Comprobe que teña as etiquetas <nowiki><translate></nowiki> e mais unha sintaxe válida.',
	'tpt-saveok' => 'A páxina "$1" foi marcada para ser traducida, con $2 seccións traducibles.
A páxina agora pode ser <span class="plainlinks">[$3 traducida]</span>.',
	'tpt-badsect' => '"$1" non é un nome válido para a sección $2.',
	'tpt-deletedsections' => 'As seguintes seccións deixarán de ser utilizadas:',
	'tpt-showpage-intro' => 'A continuación están listadas as seccións existentes e borradas.
Antes de marcar esta versión para ser traducida, comprobe que as modificacións feitas ás seccións foron minimizadas para evitarlles traballo innecesario aos tradutores.',
	'tpt-mark-summary' => 'Marcou esta versión para ser traducida',
	'tpt-edit-failed' => 'Non se puido actualizar a páxina: $1',
	'tpt-already-marked' => 'A última versión desta páxina xa foi marcada para ser traducida.',
	'tpt-list-nopages' => 'Non hai ningunha páxina marcada para ser traducida, nin preparada para ser marcada para ser traducida.',
	'tpt-old-pages' => 'Algunha versión {{PLURAL:$1|desta páxina|destas páxinas}} ten sido marcada para ser traducida.',
	'tpt-new-pages' => '{{PLURAL:$1|Esta páxina contén|Estas páxinas conteñen}} texto con etiquetas de tradución, pero ningunha versión {{PLURAL:$1|desta páxina|destas páxinas}} está actualmente marcada para ser traducida.',
	'tpt-rev-latest' => 'última versión',
	'tpt-rev-old' => 'versión $1',
	'tpt-rev-mark-new' => 'marcar esta versión para ser traducida',
	'tpt-translate-this' => 'traducir esta páxina',
	'translate-tag-translate-link-desc' => 'Traducir esta páxina',
	'translate-tag-markthis' => 'Marcar esta páxina para ser traducida',
	'tpt-translation-intro' => 'Esta páxina é unha <span class="plainlinks">[$1 versión traducida]</span> da páxina "[[$2]]" e a tradución está completada e actualizada ao $3%.
<span class="mw-translate-fuzzy">As traducións desfasadas están marcadas coma este texto.</span>',
	'tpt-languages-legend' => 'Outras linguas:',
	'tpt-target-page' => 'Esta páxina non pode ser actualizada manualmente.
Esta páxina é unha tradución da páxina "[[$1]]" e a tradución pode ser actualizada usando [$2 a ferramenta de tradución].',
	'tpt-unknown-page' => 'Este espazo de nomes está reservado para traducións de páxinas de contido.
A páxina que está intentando editar parece non corresponder a algunha páxina marcada para ser traducida.',
	'tpt-install' => 'Executar o php maintenance/update.php ou o instalador web para activar a funcionalidade de tradución de páxinas.',
);

/** Swiss German (Alemannisch)
 * @author Als-Holder
 */
$messages['gsw'] = array(
	'pagetranslation' => 'Sytenibersetzig',
	'right-pagetranslation' => 'D Syte, wu sotte ibersetzt wäre, markiere',
	'tpt-desc' => 'Erwyterig fir d Iberstzig vu Inhaltssyte',
	'tpt-section' => 'Abschnitt:',
	'tpt-section-new' => 'Neje Abschnitt:',
	'tpt-diff-old' => 'Vorige Tekscht',
	'tpt-diff-new' => 'Neje Tekscht',
	'tpt-submit' => 'Die Version zum Ibersetze markiere',
	'tpt-badtitle' => 'Dr Sytename, wu Du aagee hesch ($1), isch kei giltige Sytename',
	'tpt-oldrevision' => '$2 isch nit di letscht Version vu dr Syte [[$1]].
Nume di letschte Versione chenne zum Iberseze markiert wäre.',
	'tpt-notsuitable' => 'D Syte $1 cha nit iberstez wäre.
Stell sicher, ass si <nowiki><translate></nowiki>-Markierige un e giltige Syntax het.',
	'tpt-saveok' => 'D Syte "$1" isch zum Ibersetze markiert wore mit $2 Abschnit, wu chenne ibersetzt wäre.
D Syte cha jetz <span class="plainlinks">[$3 ibersetzt]</span> wäre.',
	'tpt-badsect' => '"$1" isch kei giltige Name fir dr Abschnitt $2.',
	'tpt-deletedsections' => 'Die Abschnitt wäre nit lenger brucht:',
	'tpt-showpage-intro' => 'Unte sin Abschnitt ufglischtet, wu nej sin, sonigi wu s git un sonigi wu s nit git.
Voreb Du die Versione zum Ibersetze markiersch, iberprief, ass d Änderige an dr Abschnitt gring ghalte sin go uunetigi Arbed bi dr Ibersetzig vermyde.',
	'tpt-mark-summary' => 'het die Versione zum Ibersetze markiert',
	'tpt-edit-failed' => 'Cha d Syte nit aktualisiere: $1',
	'tpt-already-marked' => 'Di letscht Version vu däre Syte isch scho zum Ibersetze markiert wore.',
	'tpt-list-nopages' => 'S sin kei Syte zum Ibersetze markiert wore un sin au no keini Syte fertig, wu chennte zum Ibersetze markiert wäre',
	'tpt-old-pages' => '{{PLURAL:$1|E Version vu däre Syte isch|E paar Versione vu däne Syte sin}} zum Ibersetze markiert wore',
	'tpt-new-pages' => '{{PLURAL:$1|In däre Syte|In däne Syte}} het s Tekscht mit Ibersetzigs-Markierige, aber zur Zyt isch kei Version {{PLURAL:$1|däre Syte|däne Syte}} zum Ibersetze markiert.',
	'tpt-rev-latest' => 'letschti Version',
	'tpt-rev-old' => 'Version $1',
	'tpt-rev-mark-new' => 'die Version zum Ibersetze markiere',
	'tpt-translate-this' => 'die Syte ibersetze',
	'translate-tag-translate-link-desc' => 'Die Syte ibersetze',
	'translate-tag-markthis' => 'Die Syte zum ibersetze markiere',
	'tpt-translation-intro' => 'Die Syte isch e <span class="plainlinks">[$1 ibersetzti Version]</span> vun ere Syte [[$2]] un d Ibersetzig isch zue $3% vollständig un aktuäll.
<span class="mw-translate-fuzzy">Veralteti Ibersetzige sin eso markiert.</span>',
	'tpt-languages-legend' => 'Anderi Sproche:',
	'tpt-target-page' => 'Die Syte cha nit vu Hand aktualisiert wäre.
Die Syte isch e Ibersetzig vu dr Syte [[$1]] un d Ibersetzig cha aktualisert wäre mit em [$2 Ibersetzigstool].',
	'tpt-unknown-page' => 'Dää Namensruum isch reserviert fir Ibersetzige vu Inhaltssyte.
D Syte, wu Du witt bearbeite, ghert schyns zue keire Syte, wu zum Ibersetze markiert isch.',
	'tpt-install' => 'php maintenance/update.php oder d Webinstallation laufe loo go s Syte-Ibersetzigs-Feature megli mache.',
);

/** Upper Sorbian (Hornjoserbsce)
 * @author Michawiki
 */
$messages['hsb'] = array(
	'pagetranslation' => 'Přełožowanje strony',
	'right-pagetranslation' => 'Wersije strony za přełožowanje markěrować',
	'tpt-desc' => 'Rozšěrjenje za přełožowanje wobsahowych stronow',
	'tpt-section' => 'Wotrězk:',
	'tpt-section-new' => 'Nowy wotrězk:',
	'tpt-diff-old' => 'Předchadny tekst',
	'tpt-diff-new' => 'Nowy tekst',
	'tpt-submit' => 'Tutu wersiju za přełožowanje markěrować',
	'tpt-badtitle' => 'Podate mjeno strony ($1) płaćiwy titul njeje',
	'tpt-oldrevision' => '$2 aktualna wersija strony [[$1]] njeje.
Jenož aktualne wersije hodźa so za přełožowanje markěrować.',
	'tpt-notsuitable' => 'Strona $1 za přełožowanje přihódna njeje.
Zaswěsć, zo ma taflički <nowiki><translate></nowiki> a płaćiwu syntaksu.',
	'tpt-saveok' => 'Strona "$1" je so za přełožowanje z $2 {{PLURAL:$2|přełožujomnym wotrězkom|přełožujomnymaj wotrězkomaj|přełožujomnymi wotrězkami|přełožujomnymi wotrězkami}} markěrowała.
Strona hodźi so nětko <span class="plainlinks">[$3 přełožować]</span>.',
	'tpt-badsect' => '"$1" płaćiwe mjeno za wotrězk $2 njeje.',
	'tpt-deletedsections' => 'Slědowace wotrězki hižo njebudu so wužiwać:',
	'tpt-showpage-intro' => 'Deleka su nowe, eksistowace a wušmórnjene wotrězki nalistowane.
Prjedy hač tutu wersiju za přełožowanje markěruješ, skontroluj, hač změny wotrězkow su miniměrowane, zo by njetrěbne dźěło za přełožowarjow wobešoł.',
	'tpt-mark-summary' => 'Je tutu wersiju za přełožowanje markěrował',
	'tpt-edit-failed' => 'Strona njeda so aktualizować: $1',
	'tpt-already-marked' => 'Akutalna wersija tuteje strony je so hižo za přełožowanje markěrowała.',
	'tpt-list-nopages' => 'Strony njejsu ani za přełožowanje markěrowali ani njejsu hotowe za přełožowanje.',
	'tpt-old-pages' => 'Někajka wersija {{PLURAL:$1|tuteje strony|tuteju stronow|tutych stronow|tutych stronow}} je so za přełožowanje markěrowała.',
	'tpt-new-pages' => '{{PLURAL:$1|Tuta strona wobsahuje|Tutej stronje|Tute strony wobsahuja|Tute strony wobsahuja}} tekst z přełožowanskimi tafličkimi, ale žana wersija {{PLURAL:$1|tuteje strony|tuteju stronow|tutych stronow|tutych stronow}} njeje tuchwilu za přełožowanje markěrowana.',
	'tpt-rev-latest' => 'aktualna wersija',
	'tpt-rev-old' => 'wersija $1',
	'tpt-rev-mark-new' => 'tutu wersiju za přełožowanje markěrować',
	'tpt-translate-this' => 'tutu stronu přełožić',
	'translate-tag-translate-link-desc' => 'Tutu stronu přełožić',
	'translate-tag-markthis' => 'Tutu stronu za přełožowanje markěrować',
	'tpt-translation-intro' => 'Tuta strona je <span class="plainlinks">[$1 přełožena wersija]</span> strony [[$2]], $3 % přełožka je dokónčene a přełožk je aktualny.
<span class="mw-translate-fuzzy">Zestarjene přełožki so kaž tutón markěruja.</span>',
	'tpt-languages-legend' => 'Druhe rěče:',
	'tpt-target-page' => 'Tuta strona njeda so manulenje aktualizować.
Tuta strona je přełožk strony [[$1]] a přełožk hodźi so z pomocu [$2 Přełožić] aktualizować.',
	'tpt-unknown-page' => 'Tutón mjenowy rum je za přełožki wobsahowych stronow wuměnjeny.
Strona, kotruž pospytuješ wobdźěłać, po wšěm zdaću stronje markěrowanej za přełožowanje njewotpowěduje.',
	'tpt-install' => 'Wuwjedź php maintenance/update.php ab webinstalaciju, zo by funkcija přełožowanje stronow zmóžnił.',
);

/** Interlingua (Interlingua)
 * @author McDutchie
 */
$messages['ia'] = array(
	'pagetranslation' => 'Traduction de paginas',
	'right-pagetranslation' => 'Marcar versiones de paginas pro traduction',
	'tpt-desc' => 'Extension pro traducer paginas de contento',
	'tpt-section' => 'Section:',
	'tpt-section-new' => 'Nove section:',
	'tpt-diff-old' => 'Texto anterior',
	'tpt-diff-new' => 'Texto nove',
	'tpt-submit' => 'Marcar iste version pro traduction',
	'tpt-badtitle' => 'Le nomine de pagina specificate ($1) non es un titulo valide',
	'tpt-oldrevision' => '$2 non es le version le plus recente del pagina [[$1]].
Solmente le versiones le plus recente pote esser marcate pro traduction.',
	'tpt-notsuitable' => 'Le pagina $1 non es traducibile.
Assecura que illo contine etiquettas <nowiki><translate></nowiki> e ha un syntaxe valide.',
	'tpt-saveok' => 'Le pagina "$1" ha essite marcate pro traduction con $2 sectiones traducibile.
Le pagina pote ora esser <span class="plainlinks">[$3 traducite]</span>.',
	'tpt-badsect' => '"$1" non es un nomine valide pro le section $2.',
	'tpt-deletedsections' => 'Le sequente sectiones non essera plus usate:',
	'tpt-showpage-intro' => 'In basso es listate sectiones nove, existente e delite.
Ante de marcar iste version pro traduction, assecura que le modificationes al sectiones sia minimisate pro evitar labor innecessari pro traductores.',
	'tpt-mark-summary' => 'Marcava iste version pro traduction',
	'tpt-edit-failed' => 'Non poteva actualisar le pagina: $1',
	'tpt-already-marked' => 'Le version le plus recente de iste pagina ha jam essite marcate pro traduction.',
	'tpt-list-nopages' => 'Il non ha paginas marcate pro traduction, ni paginas preparate pro isto.',
	'tpt-old-pages' => 'Alcun {{PLURAL:$1|version de iste pagina|versiones de iste paginas}} ha essite marcate pro traduction.',
	'tpt-new-pages' => 'Iste {{PLURAL:$1|pagina|paginas}} contine texto con etiquettas de traduction, ma nulle version de iste {{PLURAL:$1|pagina|paginas}} es actualmente marcate pro traduction.',
	'tpt-rev-latest' => 'ultime version',
	'tpt-rev-old' => 'version $1',
	'tpt-rev-mark-new' => 'marcar iste version pro traduction',
	'tpt-translate-this' => 'traducer iste pagina',
	'translate-tag-translate-link-desc' => 'Traducer iste pagina',
	'translate-tag-markthis' => 'Marcar iste pagina pro traduction',
	'tpt-translation-intro' => 'Iste pagina es un <span class="plainlinks">[$1 version traducite]</span> de un pagina [[$2]] e le traduction es complete e actual a $3%.
<span class="mw-translate-fuzzy">Le traductiones obsolete es marcate assi.</span>',
	'tpt-languages-legend' => 'Altere linguas:',
	'tpt-target-page' => 'Iste pagina non pote esser actualisate manualmente.
Iste pagina es un traduction del pagina [[$1]] e le traduction pote esser actualisate con le [$2 instrumento de traduction].',
	'tpt-unknown-page' => 'Iste spatio de nomines es reservate pro traductiones de paginas de contento.
Le pagina que tu vole modificar non pare corresponder con alcun pagina marcate pro traduction.',
	'tpt-install' => 'Executa maintenance/update.php o le installation web pro activar le traduction de paginas.',
);

/** Japanese (日本語)
 * @author Aotake
 * @author Fryed-peach
 */
$messages['ja'] = array(
	'pagetranslation' => 'ページ翻訳',
	'right-pagetranslation' => 'ページの版を翻訳対象に指定する',
	'tpt-desc' => 'コンテンツページの翻訳のための拡張機能',
	'tpt-section' => 'セクション:',
	'tpt-section-new' => '新しいセクション:',
	'tpt-diff-old' => '前のテキスト',
	'tpt-diff-new' => '新しいテキスト',
	'tpt-submit' => 'この版を翻訳対象に指定する',
	'tpt-badtitle' => '指定したページ名 ($1) は無効なタイトルです',
	'tpt-oldrevision' => '$2 はページ [[$1]] の最新版ではありません。翻訳対象に指定できるのは最新版のみです。',
	'tpt-notsuitable' => 'ページ $1 は翻訳に対応していません。<nowiki><translate></nowiki>が含まれていること、またマークアップが正しいことを確認してください。',
	'tpt-saveok' => 'ページ "$1" は翻訳対象に指定されており、 $2 の翻訳可能なセクションを含んでいます。このページを<span class="plainlinks">[$3 翻訳]</span>することができます。',
	'tpt-badsect' => '「$1」はセクション $2 の名前として無効です。',
	'tpt-deletedsections' => '以下のセクションはすでに使われていません:',
	'tpt-showpage-intro' => '以下には新しいセクション、既存のセクション、そして削除されたセクションが一覧されています。この版を翻訳対象に指定する前に、セクションの変更を最小限にすることで不要な翻訳作業を回避できないか確認してください。',
	'tpt-mark-summary' => 'この版を翻訳対象に指定しました',
	'tpt-edit-failed' => 'ページを更新できませんでした: $1',
	'tpt-already-marked' => 'このページの最新版がすでに翻訳対象に指定されています。',
	'tpt-list-nopages' => '翻訳対象に指定されたページがない、または翻訳対象に指定する準備ができているページがありません。',
	'tpt-old-pages' => '{{PLURAL:$1|これらの|この}}ページには翻訳対象に指定された版があります。',
	'tpt-new-pages' => '以下のページは本文に翻訳タグを含んでいますが、翻訳対象に指定されている版がありません。',
	'tpt-rev-latest' => '最新版',
	'tpt-rev-old' => '版 $1',
	'tpt-rev-mark-new' => 'この版を翻訳対象に指定する',
	'tpt-translate-this' => 'このページを翻訳する',
	'translate-tag-translate-link-desc' => 'このページを翻訳する',
	'translate-tag-markthis' => 'このページを翻訳対象に指定する',
	'tpt-translation-intro' => 'このページはページ [[$2]] の<span class="plainlinks">[$1 翻訳版]</span> です。翻訳は $3% 完了しており、最新の状態を反映しています。<span class="mw-translate-fuzzy">更新が必要な翻訳はこのようにハイライトされます。</span>',
	'tpt-languages-legend' => '他言語での翻訳:',
	'tpt-target-page' => 'このページは手動で更新できません。このページはページ [[$1]] の翻訳で、[$2 翻訳ツール]を使用して更新します。',
	'tpt-unknown-page' => 'この名前空間はコンテンツページの翻訳のために使用します。あなたが編集しようとしているページに対応する翻訳対象ページが存在しないようです。',
	'tpt-install' => 'ページ翻訳機能を有効にするために、php maintenance/update.php またはウェブ・インストーラーを実行する。',
);

/** Khmer (ភាសាខ្មែរ)
 * @author គីមស៊្រុន
 * @author វ័ណថារិទ្ធ
 */
$messages['km'] = array(
	'pagetranslation' => 'ការ​បក​ប្រែ​ទំព័រ​',
	'tpt-rev-latest' => 'កំណែ (version) ចុង​ក្រោយ​គេ​',
	'translate-tag-translate-link-desc' => 'បកប្រែទំព័រនេះ',
);

/** Ripoarisch (Ripoarisch)
 * @author Purodha
 */
$messages['ksh'] = array(
	'pagetranslation' => 'Sigge Övversäze',
	'right-pagetranslation' => 'Donn Versione vun Sigge för et Övversäze makeere',
	'tpt-desc' => 'Projrammzohsatz för Sigge vum Enhalt vum Wiki ze övversäze.',
	'tpt-section' => 'Afschnett:',
	'tpt-section-new' => 'Ene neue Afschnett:',
	'tpt-diff-old' => 'Dä vörrijje Täx',
	'tpt-diff-new' => 'Dä neue Täx',
	'tpt-submit' => 'Donn hee di Version för et Övversäze makeere',
	'tpt-badtitle' => 'Dä Name „$1“ es keine jöltijje Tittel för en Sigg',
	'tpt-oldrevision' => '„$2“ es nit de neuste Version fun dä Sigg „[[$1]]“, ävver bloß de neuste kam_mer för et Övversäze makeere.',
	'tpt-notsuitable' => 'Di Sigg „$1“ paß nit för et Övversäze. Maach <code><nowiki><translate></nowiki></code>-Makeerunge erin, un looer dat de Süntax shtemmp.',
	'tpt-saveok' => 'De Sigg „$1“ es för zem Övversäze makeet woode. Se hät {{PLURAL:$2|eine Afschnet|$2 Afschnedde|keine Afschnet}} för zem Övversäze. Di Sigg kann jäz <span class="plainlinks">[$3 övversaz weede]</span>.',
	'tpt-badsect' => '„$1“ es kein jöltejje Name för dä Afschnett $2.',
	'tpt-deletedsections' => 'Hee di Afschnedde wähde jiz nit mieh jebruch:',
	'tpt-showpage-intro' => 'Hee dronger sin Afschnedde opjeleß, di eruß jenumme woode, un di noch doh sin. Ih dat De hee di Version för ze Övversäze makeere deihß, loor drop, dat esu winnisch wi müjjelesch Änderonge aan Afschnedde doh sin, öm dä Övversäzere et Levve leisch ze maache.',
	'tpt-mark-summary' => 'Han di Version för ze Övversäze makeet',
	'tpt-edit-failed' => 'Kunnt de Sigg „$1“ nit ändere',
	'tpt-already-marked' => 'De neuste Version vun dä Sigg es ald för zem Övversäze makeet.',
	'tpt-list-nopages' => 'Et sinn_er kein Sigge för zem Övversäze makeet, un et sin och kein doh, wo esu en Makeerunge eren künnte.',
	'tpt-old-pages' => 'En Version vun hee dä {{PLURAL:$1|Sigg|Sigge|-}} es för zem Övversäze makeet.',
	'tpt-new-pages' => '{{PLURAL:$1|Di Sigg hät|Di Sigge han|Kein Sigg hät}} ene <code lang="en">translation</code>-Befähl en sesch, ävve kei Version dofun es för ze Övversäze makeet.',
	'tpt-rev-latest' => 'Neuste Version',
	'tpt-rev-old' => 'Version $1',
	'tpt-rev-mark-new' => 'donn di Version för et Övversäze makeere',
	'tpt-translate-this' => 'donn di Sigg övversäze',
	'translate-tag-translate-link-desc' => 'Don di Sigg hee övversäze',
	'translate-tag-markthis' => 'Donn hee di Sigg för et Övversäze makeere',
	'tpt-translation-intro' => 'Hee di Sigg es en <span class="plainlinks">[$1 övversaz Version]</span> vun dä Sigg [[$2]] un es zoh $3% jedonn un om aktoälle Shtandt. <span class="mw-translate-fuzzy">Övverhollte Översäzunge sin esu wi dat hee  makeet.</span>',
	'tpt-languages-legend' => 'Ander Shprooche:',
	'tpt-target-page' => 'Hee di Sigg kam_mer nit vun Hand ändere. Dat hee es en Översäzungß_Sigg vun dä Sigg [[$1]]. De Övversäzung kam_mer övver däm Wiki sing [$2 Övversäzungß_Wärkzüsch] op der neußte Shtand bränge.',
	'tpt-unknown-page' => 'Dat Appachtemang hee es för Sigge vum Enhallt vum Wiki ze Övversäze jedaach. Di Sigg, di de jraad ze ändere versöhks, schingk ävver nit met ööhnds en Sigg ze donn ze han, di för zem Övversäze makeet es.',
	'tpt-install' => 'Lohß op Dingem Wiki singem ßööver dat Skrip <code>php maintenance/update.php</code> loufe, udder schmiiß dat Enreeschdungsprojramm övver et Web aan, öm de Müjjeleschkeit för Sigge ze övversäze en däm Wiki aan et Loufe ze bränge.',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Robby
 */
$messages['lb'] = array(
	'pagetranslation' => 'Iwwersetzung vun der Säit',
	'right-pagetranslation' => 'Versioune vu Säite fir Iwwersetzung markéieren',
	'tpt-desc' => "Erweiderung fir ihaltlech Säiten z'iwwersetzen",
	'tpt-section' => 'Abschnitt:',
	'tpt-section-new' => 'Neien Abschnitt:',
	'tpt-diff-old' => 'Viregen Text',
	'tpt-diff-new' => 'Neien Text',
	'tpt-submit' => "Dës Versioun fir d'Iwwersetze markéieren",
	'tpt-badtitle' => 'De Säitennumm deen ugi gouf ($1) ass kee valabelen Titel',
	'tpt-oldrevision' => "$2 ass net déi lescht Versioun vun der Säit [[$1]].
Nëmmen déi lescht Versioune kënne fir d'Iwwersetzung markéiert ginn.",
	'tpt-notsuitable' => "D'Säit $1 ass net geeegent fir iwwersat ze ginn.
Vergewëssert Iech ob se <nowiki><translate></nowiki>-Taggen  an eng valabel Syntax huet.",
	'tpt-deletedsections' => 'Dës Abschnitter ginn net méi benotzt:',
	'tpt-mark-summary' => "huet dës Versioun fir d'Iwwersetzung markéiert",
	'tpt-edit-failed' => "D'Säit $1 konnt net aktualiséiert ginn",
	'tpt-already-marked' => "Déilescht Versioun vun dëser Säit gouf scho fir d'Iwwersetzung markéiert.",
	'tpt-list-nopages' => "Et si keng Säite fir d'Iwwersetzung markéiert respektiv fäerdeg fir fir d'Iwersetzung markéiert ze ginn.",
	'tpt-rev-latest' => 'lescht Versioun',
	'tpt-rev-old' => 'Versioun $1',
	'tpt-rev-mark-new' => "dës Versioun fir d'Iwwersetzung markéieren",
	'tpt-translate-this' => 'dës Säit iwwersetzen',
	'translate-tag-translate-link-desc' => 'Dës Säit iwwersetzen',
	'translate-tag-markthis' => "Dës Säit fir d'Iwwersetzung markéieren",
	'tpt-languages-legend' => 'aner Sproochen:',
	'tpt-target-page' => "Dës Säit kann net manuell aktualiséiert ginn.
Dës Säit ass eng Iwwersetzung vun der Säit [[$1]] an d'Iwwersetzung ka mat Hëllef vun der [$2 Iwwersetzungs-Fonctioun] aktulaiséiert ginn.",
);

/** Dutch (Nederlands)
 * @author Siebrand
 */
$messages['nl'] = array(
	'pagetranslation' => 'Paginavertaling',
	'right-pagetranslation' => "Versies van pagina's voor de vertaling markeren",
	'tpt-desc' => "Uitbreiding voor het vertalen van wikipagina's",
	'tpt-section' => 'Sectie:',
	'tpt-section-new' => 'Nieuwe sectie:',
	'tpt-diff-old' => 'Vorige tekst',
	'tpt-diff-new' => 'Nieuwe tekst',
	'tpt-submit' => 'Deze versie voor vertaling markeren',
	'tpt-badtitle' => 'De opgegeven paginanaam ($1) is geen geldige paginanaam',
	'tpt-oldrevision' => '$2 is niet de meest recente versie van de pagina "[[$1]]".
Alleen de meest recente versie kan voor vertaling gemarkeerd worden.',
	'tpt-notsuitable' => 'De pagina "$1" kan niet voor vertaling gemarkeerd worden.
Zorg ervoor dat de labels <nowiki><translate></nowiki> geplaatst zijn en dat deze juist zijn toegevoegd.',
	'tpt-saveok' => 'De pagina "$1" is gemarkeerd voor vertaling met $2 te vertalen secties.
De pagina kan nu  <span class="plainlinks">[$3 vertaald]</span> worden.',
	'tpt-badsect' => '"$1" is geen geldige naam voor sectie $2.',
	'tpt-deletedsections' => 'De volgende secties worden niet langer gebruikt:',
	'tpt-showpage-intro' => 'Hieronder zijn nieuwe, bestaande en verwijderde secties opgenomen.
Controleer voordat u deze versie voor vertaling markeert of de wijzigingen aan de secties zo klein mogelijk zijn om onnodig werk voor vertalers te voorkomen.',
	'tpt-mark-summary' => 'Heeft deze versie voor vertaling gemarkeerd',
	'tpt-edit-failed' => 'De pagina "$1" kon niet bijgewerkt worden.',
	'tpt-already-marked' => 'De meest recente versie van deze pagina is al gemarkeerd voor vertaling.',
	'tpt-list-nopages' => "Er zijn geen pagina's gemarkeerd voor vertaling, noch klaar om gemarkeerd te worden voor vertaling.",
	'tpt-old-pages' => "Er is al een versie van deze {{PLURAL:$1|pagina|pagina's}} gemarkeerd voor vertaling.",
	'tpt-new-pages' => "Deze {{PLURAL:$1|pagina bevat|pagina's bevatten}} tekst met vertalingslabels, maar van deze {{PLURAL:$1|pagina|pagina's}} is geen versie gemarkeerd voor vertaling.",
	'tpt-rev-latest' => 'meest recente versie',
	'tpt-rev-old' => 'versie $1',
	'tpt-rev-mark-new' => 'deze versie voor vertaling markeren',
	'tpt-translate-this' => 'deze pagina vertalen',
	'translate-tag-translate-link-desc' => 'Deze pagina vertalen',
	'translate-tag-markthis' => 'Deze pagina voor vertaling markeren',
	'tpt-translation-intro' => 'Deze pagina is een <span class="plainlinks">[$1 vertaalde versie]</span> van de pagina [[$2]] en de vertaling is $3% compleet en bijgewerkt.
<span class="mw-translate-fuzzy">Verouderde vertalingen worden zo gemarkeerd.</span>',
	'tpt-languages-legend' => 'Andere talen:',
	'tpt-target-page' => 'Deze pagina kan niet handmatig worden bijgewerkt manually.
Deze pagina is een vertaling van de pagina [[$1]].
De vertaling kan bijgewerkt worden via de [$2 vertaalhulpmiddellen].',
	'tpt-unknown-page' => "Deze naamruimte is gereserveerd voor de vertalingen van van pagina's.
De pagina die u probeert te bewerken lijkt niet overeen te komen met een te vertalen pagina.",
	'tpt-install' => 'Voer php maintenance/update.php of de webinstallatie uit om de paginavertaling te activeren.',
);

/** Occitan (Occitan)
 * @author Cedric31
 */
$messages['oc'] = array(
	'pagetranslation' => 'Traduccion de paginas',
	'right-pagetranslation' => 'Marcar de versions de paginas per èsser traduchas',
	'translate-tag-translate-link-desc' => 'Traduire aquesta pagina',
);

/** Portuguese (Português)
 * @author Malafaya
 * @author Waldir
 */
$messages['pt'] = array(
	'pagetranslation' => 'Tradução de páginas',
	'right-pagetranslation' => 'Marcar versões de páginas para tradução',
	'tpt-desc' => 'Extensão para traduzir páginas de conteúdo',
	'tpt-section' => 'Secção:',
	'tpt-section-new' => 'Nova secção:',
	'tpt-diff-old' => 'Texto anterior',
	'tpt-diff-new' => 'Novo texto',
	'tpt-submit' => 'Marcar esta versão para tradução',
	'tpt-badtitle' => 'Nome de página fornecido ($1) não é um título válido',
	'tpt-oldrevision' => '$2 não é a versão mais recente da página [[$1]].
Apenas as últimas versões podem ser marcadas para tradução.',
	'tpt-notsuitable' => 'Página $1 não é adequada para tradução.
Certifique-se que a mesma contém tags <nowiki><translate></nowiki> e possui uma sintaxe válida.',
	'tpt-saveok' => 'A página "$1" foi marcada para tradução com $2 seccções traduzíveis.
A página pode agora ser <span class="plainlinks">[$3 traduzida]</span>.',
	'tpt-badsect' => '"$1" não é um nome válido para a secção $2.',
	'tpt-deletedsections' => 'As seções seguintes deixarão de ser utilizadas:',
	'tpt-showpage-intro' => 'Abaixo estão listadas secções novas, existentes e apagadas.
Antes de marcar esta versão para tradução, verifique que as alterações às secções são minimizadas para evitar trabalho desnecessário para os tradutores.',
	'tpt-mark-summary' => 'Marcada esta versão para tradução',
	'tpt-edit-failed' => 'Não foi possível atualizar a página: $1',
	'tpt-already-marked' => 'A versão mais recente desta página já foi marcada para tradução.',
	'tpt-list-nopages' => 'Não existem páginas marcadas para tradução, nem prontas a ser marcadas para tradução.',
	'tpt-new-pages' => '{{PLURAL:$1|Esta página contém|Estas páginas contêm}} texto com tags de tradução, mas nenhuma versão {{PLURAL:$1|desta página|destas páginas}} está atualmente marcada para tradução.',
	'tpt-rev-latest' => 'versão mais recente',
	'tpt-rev-old' => 'versão $1',
	'tpt-rev-mark-new' => 'marcar esta versão para tradução',
	'tpt-translate-this' => 'traduzir esta página',
	'translate-tag-translate-link-desc' => 'Traduzir esta página',
	'translate-tag-markthis' => 'Marcar esta página para tradução',
	'tpt-translation-intro' => 'Esta página é uma <span class="plainlinks">[$1 versão traduzida]</span> de uma página [[$2]], e a tradução está $3% completa e atualizada.
<span class="mw-translate-fuzzy">Traduções obsoletas são marcadas desta forma.</span>',
	'tpt-languages-legend' => 'Outras línguas:',
	'tpt-target-page' => 'Esta página não pode ser atualizada manualmente.
Esta página é uma tradução da página [[$1]], e a tradução pode ser atualizada utilizando [$2 a ferramenta de tradução].',
	'tpt-unknown-page' => 'Este domínio está reservado para traduções de páginas de conteúdo.
A página que você está a tentar editar não parece corresponder a qualquer página marcada para tradução.',
);

/** Brazilian Portuguese (Português do Brasil)
 * @author Eduardo.mps
 */
$messages['pt-br'] = array(
	'pagetranslation' => 'Tradução de páginas',
	'right-pagetranslation' => 'Marca versões de páginas para tradução',
	'tpt-desc' => 'Extensão para traduzir páginas de conteúdo',
	'tpt-section' => 'Seção:',
	'tpt-section-new' => 'Nova Seção:',
	'tpt-diff-old' => 'Texto anterior',
	'tpt-diff-new' => 'Novo texto',
	'tpt-submit' => 'Marca esta versão para tradução',
	'tpt-badtitle' => 'O nome de página dado ($1) não é um título válido',
	'tpt-oldrevision' => '$2 não é a versão atual da página [[$1]].
Apenas as versões atuais pode ser marcadas para tradução.',
	'tpt-notsuitable' => 'A página $1 não está adequada para tradução.
Tenha certeza que ela tem marcas <nowiki><translate></nowiki> e tem a sintaxe válida.',
	'tpt-saveok' => 'A página "$1" foi marcada para tradução com $2 seções traduzíveis.
A página pode ser <span class="plainlinks">[$3 traduzida]</span> agora.',
	'tpt-badsect' => '"$1" não é um nome válido para a seção $2.',
	'tpt-deletedsections' => 'As seguintes seção não mais serão utilizadas',
	'tpt-showpage-intro' => 'Abaixo estão listadas seções novas, existentes e removidas.
Antes de marcar esta versão para tradução, verifique se as mudanças nas seções foram minimizadas para evitar trabalho desnecessário para os tradutores.',
	'tpt-mark-summary' => 'Marcou esta versão para tradução',
	'tpt-edit-failed' => 'Não foi possível atualizar a página: $1',
	'tpt-already-marked' => 'A versão atual desta página já foi marcada para tradução.',
	'tpt-list-nopages' => 'Nenhuma página está marcada para tradução nem pronta para ser marcada para tradução.',
	'tpt-old-pages' => 'Algumas versões destas páginas foram marcadas para tradução.',
	'tpt-new-pages' => 'Estas páginas contém texto com marcas "translation", mas nenhuma delas está marcada para tradução atualmente.',
	'tpt-rev-latest' => 'versão atual',
	'tpt-rev-old' => 'versão $1',
	'tpt-rev-mark-new' => 'marcar esta versão para traduçao',
	'tpt-translate-this' => 'traduzir esta página',
	'translate-tag-translate-link-desc' => 'Traduzir esta página',
	'translate-tag-markthis' => 'Marcar esta página para tradução',
	'tpt-target-page' => 'Esta página não pode ser atualizada manualmente.
Esta página é uma tradução da página [[$1]] e a tradução pode ser atualizada usando [$2 a ferramenta de tradução].',
	'tpt-unknown-page' => 'Este domínio é reservado para traduções de páginas de conteúdo.
Esta página que você está tentando editar não aparenta corresponder a nenhuma página marcada para tradução.',
);

/** Russian (Русский)
 * @author Ferrer
 * @author Александр Сигачёв
 */
$messages['ru'] = array(
	'right-pagetranslation' => 'Отметка версий страниц для перевода',
	'tpt-desc' => 'Расширение для перевода содержимого страниц',
	'tpt-section' => 'Раздел:',
	'tpt-section-new' => 'Новый раздел:',
	'tpt-diff-old' => 'Предыдущий текст',
	'tpt-diff-new' => 'Новый текст',
	'tpt-submit' => 'Отметить эту версию для перевода',
	'tpt-badtitle' => 'Указанное название страницы ($1) не является верным названием',
	'tpt-oldrevision' => '$2 не является последней версией страницы [[$1]].
Только последние версии могут быть отмечены для перевода.',
	'tpt-notsuitable' => 'Страницы $1 является неподходящей для перевода.
Убедитесь, что она имеет теги <nowiki><translate></nowiki> и правильный синтаксис.',
	'tpt-badsect' => '«$1» не является верным названием для раздела $2.',
	'tpt-rev-latest' => 'последняя версия',
	'tpt-rev-old' => 'версия $1',
	'tpt-rev-mark-new' => 'отметить эту версию для перевода',
	'tpt-translate-this' => 'перевести эту страницу',
	'translate-tag-translate-link-desc' => 'Перевести эту страницу',
	'translate-tag-markthis' => 'Отметить эту страницу для перевода',
	'tpt-languages-legend' => 'Другие языки:',
);

/** Slovak (Slovenčina)
 * @author Helix84
 */
$messages['sk'] = array(
	'pagetranslation' => 'Preklad stránky',
	'right-pagetranslation' => 'Označiť verzie stránok na preklad',
	'tpt-desc' => 'Rozšírenie na preklad stránok s obsahom',
	'tpt-section' => 'Sekcia:',
	'tpt-section-new' => 'Nová sekcia:',
	'tpt-diff-old' => 'Predošlý text',
	'tpt-diff-new' => 'Nový text',
	'tpt-submit' => 'Označiť túto verziu na preklad',
	'tpt-badtitle' => 'Zadaný názov stránky ($1) nie je platný',
	'tpt-oldrevision' => '$2 nie je najnovšia verzia stránky [[$1]].
Na preklad je možné označiť iba posledné verzie stránok.',
	'tpt-notsuitable' => 'Stránka $1 nie je vhodná na preklad.
Uistite sa, že obsahuje značky <nowiki><translate></nowiki> a má platnú syntax.',
	'tpt-saveok' => 'Stránka „$1“ bola označená na preklad s $2 sekciami, ktoré možno preložiť.
Túto stránku je teraz možné <span class="plainlinks">[$3 preložiť]</span>.',
	'tpt-badsect' => '„$1“ nie je platný názov sekcie $2.',
	'tpt-deletedsections' => 'Nasledovné sekcie už nebudú využité:',
	'tpt-showpage-intro' => 'Dolu sú uvedené nové, súčasné a zmazané sekcie,
Predtým než túto verziu označíte na preklad skontrolujte, že zmeny sekcií sú minimálne aby ste zabránili zbytočnej práci prekladateľov.',
	'tpt-mark-summary' => 'Táto verzia je označená na preklad',
	'tpt-edit-failed' => 'Nebolo možné aktualizovať stránku: $1',
	'tpt-already-marked' => 'Najnovšia verzia tejto stránky už bola označená na preklad.',
	'tpt-list-nopages' => 'Žiadne stránky nie sú označené na preklad alebo na to nie sú pripravené.',
	'tpt-old-pages' => 'Niektoré verzie {{PLURAL:$1|tejto stránky|týchto stránok}} boli označené na preklad.',
	'tpt-new-pages' => '{{PLURAL:$1|Táto stránka obsahuje|Tieto stránky obsahujú}} text so značkami na preklad, ale žiadna verzia {{PLURAL:$1|tejto stránky|týchto stránok}} nie je označená na preklad.',
	'tpt-rev-latest' => 'najnovšia verzia',
	'tpt-rev-old' => 'verzia $1',
	'tpt-rev-mark-new' => 'označiť túto verziu na preklad',
	'tpt-translate-this' => 'preložiť túto stránku',
	'translate-tag-translate-link-desc' => 'Preložiť túto stránku',
	'translate-tag-markthis' => 'Označiť túto stránku na preklad',
	'tpt-translation-intro' => 'Táto stránka je <span class="plainlinks">[$1 preloženou verziou]</span> stránky [[$2]] a preklad je hotový a aktuálny na $3 %.
<span class="mw-translate-fuzzy">Zastaralé preklady sú označené takto.</span>',
	'tpt-languages-legend' => 'Iné jazyky:',
	'tpt-target-page' => 'Túto stránku nemožno aktualizovať ručne.
Táto stránka je prekladom stránky [[$1]] a preklad možno aktualizovať pomocou [$2 nástroja na preklad].',
	'tpt-unknown-page' => 'Tento menný priestor je vyhradený na preklady stránok s obsahom.
Zdá sa, že stránka, ktorú sa pokúšate upravovať nezodpovedá žiadnej stránke označenej na preklad.',
	'tpt-install' => 'Funkciu prekladu webových stránok zapnete spustením php maintenance/update.php alebo webovej inštalácie.',
);

/** Swedish (Svenska)
 * @author M.M.S.
 * @author Najami
 */
$messages['sv'] = array(
	'pagetranslation' => 'Sidöversättning',
	'right-pagetranslation' => 'Märk versioner av sidor för översättning',
	'tpt-desc' => 'Programtillägg för översättning av innehållssidor',
	'tpt-section' => 'Avsnitt:',
	'tpt-section-new' => 'Nytt avsnitt:',
	'tpt-diff-old' => 'Föregående text',
	'translate-tag-translate-link-desc' => 'Översätt den här sidan',
);

