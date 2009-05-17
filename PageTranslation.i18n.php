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
	'tpt-section' => 'Translation unit $1',
	'tpt-section-new' => 'New translation unit. Name: $1',
	'tpt-section-deleted' => 'Translation unit $1',
	'tpt-template' => 'Page template',
	'tpt-templatediff' => 'The page template has changed.',

	'tpt-diff-old' => 'Previous text',
	'tpt-diff-new' => 'New text',
	'tpt-submit' => 'Mark this version for translation',

	'tpt-sections-oldnew' => 'New and existing translation units',
	'tpt-sections-deleted' => 'Deleted translation units',
	'tpt-sections-template' => 'Translation page template',
	
	# Specific page on the special page
	'tpt-badtitle' => 'Page name given ($1) is not a valid title',
	'tpt-oldrevision' => '$2 is not the latest version of the page [[$1]].
Only latest versions can be marked for translation.',
	'tpt-notsuitable' => 'Page $1 is not suitable for translation.
Make sure it has <nowiki><translate></nowiki> tags and has a valid syntax.',
	'tpt-saveok' => 'The page [[$1]] has been marked up for translation with $2 {{PLURAL:$2|translation unit|translation units}}.
The page can now be <span class="plainlinks">[$3 translated]</span>.',
	'tpt-badsect' => '"$1" is not a valid name for a translation unit $2.',
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
	'tpt-rev-old' => 'difference to previous marked version',
	'tpt-rev-mark-new' => 'mark this version for translation',
	'tpt-translate-this' => 'translate this page',

	# Source and translation page headers
	'translate-tag-translate-link-desc' => 'Translate this page',
	'translate-tag-markthis' => 'Mark this page for translation',
	'translate-tag-markthisagain' => 'This page has <span class="plainlinks">[$1 changes]</span> since it was last <span class="plainlinks">[$2 marked for translation]</span>.',
	'translate-tag-hasnew' => 'This page contains <span class="plainlinks">[$1 changes]</span> which are not marked for translation.',
	'tpt-translation-intro' => 'This page is a <span class="plainlinks">[$1 translated version]</span> of a page [[$2]] and the translation is $3% complete and up to date.',
	'tpt-translation-intro-fuzzy' => 'Outdated translations are marked like this.',

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
	'tpt-sections-oldnew' => '"New and existing" refers to the sum of: (a) new translation units, that were added, plus (b) the already existing ones, which were retained.',
	'tpt-saveok' => '$1 is a page title,
$2 is a count of sections which can be used with PLURAL,
$3 is an URL.',
	'tpt-old-pages' => 'The words "some version" refer to "one version of the page", or "a single version of each of the pages", respectively. Each page can have either one or none of its versions marked for translaton.',
	'tpt-rev-old' => '{{Identical|Version}}',
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
	'tpt-saveok' => 'Старонка «$1» была пазначаная для перакладу з $2 {{PLURAL:$2|сэкцыяй|сэкцыямі|сэкцыямі}}.
Зараз старонка можа быць <span class="plainlinks">[$3 перакладзеная]</span>.',
	'tpt-badsect' => '«$1» не зьяўляецца слушнай назвай для сэкцыі $2.',
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
	'tpt-translation-intro' => 'Гэта старонка <span class="plainlinks">[$1 перакладзеная вэрсія]</span> старонкі [[$2]], пераклад завершаны на $3%.',
	'tpt-languages-legend' => 'Іншыя мовы:',
	'tpt-target-page' => 'Гэта старонка ня можа быць абноўлена ўручную.
Гэта старонка зьяўляецца перакладам старонкі [[$1]], пераклад можа быць абноўлены з выкарыстаньнем [$2 інструмэнта перакладу].',
	'tpt-unknown-page' => 'Гэта прастора назваў зарэзэрваваная для перакладаў старонак зьместу.
Старонка, якую Вы спрабуеце рэдагаваць, верагодна не зьвязана зь якой-небудзь старонкай пазначанай для перакладу.',
	'tpt-install' => 'Запусьціце php maintenance/update.php альбо усталюйце праз вэб-інтэрфэйс для актывізацыі інструмэнтаў перакладу старонак.',
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
	'tpt-oldrevision' => '$2 nije posljednja verzija stranice [[$1]].
Jedino posljednje verzije se mogu označiti za prevođenje.',
	'tpt-notsuitable' => 'Stranica $1 nije pogodna za prevođenje.
Provjerite da postoje oznake <nowiki><translate></nowiki> i da ima valjanu sintaksu.',
	'tpt-badsect' => '"$1" nije valjano ime za sekciju $2.',
	'tpt-mark-summary' => 'Ova vezija označena za prevođenje',
	'tpt-edit-failed' => 'Nije moguće ažurirati stranicu: $1',
	'tpt-already-marked' => 'Posljednja verzija ove stranice je već označena za prevođenje.',
	'tpt-old-pages' => 'Neke verzije {{PLURAL:$1|ove stranice|ovih stranica}} su označene za prevođenje.',
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
	'tpt-template' => 'Seitenvorlage',
	'tpt-templatediff' => 'Die Seitenvorlage hat sich geändert.',
	'tpt-diff-old' => 'Vorheriger Text',
	'tpt-diff-new' => 'Neuer Text',
	'tpt-submit' => 'Diese Version zur Übersetzung markieren',
	'tpt-badtitle' => 'Der angegebene Seitenname „$1“ ist kein gültiger Titel',
	'tpt-oldrevision' => '$2 ist nicht die letzte Version der Seite [[$1]].
Nur die letzte Version kann zur Übersetzung markiert werden.',
	'tpt-notsuitable' => 'Die Seite $1 ist nicht zum Übersetzen geeignet.
Stelle sicher, das ein <nowiki><translate></nowiki>-Tag und gültige Syntax verwendet wird.',
	'tpt-saveok' => 'Die Seite „$1“ mit $2 {{PLURAL:$2|übersetzbarem Abschnitt|übersetzbaren Abschnitten}} wurde für die Übersetzung markiert.
Diese Seite kann nun <span class="plainlinks">[$3 übersetzt]</span> werden.',
	'tpt-badsect' => '„$1“ ist kein gültiger Name für Abschnitt $2.',
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
	'tpt-translation-intro' => 'Diese Seite ist eine <span class="plainlinks">[$1 übersetzte Version]</span> der Seite [[$2]] und die Übersetzung ist zu $3 % abgeschlossen und aktuell.',
	'tpt-translation-intro-fuzzy' => 'Nicht aktuelle Übersetzungen werden wie dieser Text markiert.',
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
	'tpt-badtitle' => 'Pódane bokowe mě ($1) njejo płaśiwy titel',
	'tpt-oldrevision' => '$2 njejo aktualna wersija boka [[$1]].
Jano aktualne wersije daju se za pśełožowanje markěrowaś.',
	'tpt-notsuitable' => 'Bok $1 njejo gódny za pśełožowanje.
Zawěsć, až ma toflicki <nowiki><translate></nowiki> a płaśiwu syntaksu.',
	'tpt-saveok' => 'Bok [[$1]] jo se markěrował za pśełožowanje z $2 {{PLURAL:$2|pśełožujobneju jadnotku|pśełožujobnyma jadnotkoma|pśełožujobnymi jadnotkami|pśełožujobnymi jadnotkami}}. Bok móže se něnto <span class="plainlinks">[$3 pśełožowaś]</span>.',
	'tpt-badsect' => '"$1" njejo płaśiwe mě za pśełožowańsku jadnotku $2.',
	'tpt-showpage-intro' => 'Dołojce su nowe, eksistěrujuce a wulašowane wótrězki nalicone.
Nježli až markěrujoš toś tu wersiju za pśełožowanje, pśekontrolěruj, lěc změny na wótrězkach su zminiměrowane, aby se wobinuł njetrěbne źěło za pśełožowarjow.',
	'tpt-mark-summary' => 'Jo toś tu wersiju za pśełožowanje markěrował',
	'tpt-edit-failed' => 'Toś ten bok njejo se dał aktualizěrowaś: $1',
	'tpt-already-marked' => 'Aktualna wersija toś togo boka jo južo za pśełožowanje markěrowana.',
	'tpt-list-nopages' => 'Žedne boki njejsu za pśełožowanje markěrowane ani su gótowe, aby se za pśełožowanje markěrowali.',
	'tpt-old-pages' => 'Někaka wersija {{PLURAL:$1|toś togo boka|toś teju bokowu|toś tych bokow|toś tych bokow}} jo se za pśełožowanje markěrowała.',
	'tpt-new-pages' => '{{PLURAL:$1|Toś ten bok wopśimujo|Toś tej boka wopśumujotej|Toś te boki wopśimuju|Toś te boki wopśimuju}} tekst z pśełožowańskimi toflickami, ale žedna wersija {{PLURAL:$1|toś togo boka|toś teju bokowu|toś tych bokow|toś tych bokow}} njejo tuchylu za pśełožowanje markěrowana.',
	'tpt-rev-latest' => 'aktualna wersija',
	'tpt-rev-old' => 'rozdźěl k pjerwjejšnej markěrowanej wersiji',
	'tpt-rev-mark-new' => 'toś tu wersiju za pśełožowanje markěrowaś',
	'tpt-translate-this' => 'toś ten bok pśełožyś',
	'translate-tag-translate-link-desc' => 'Toś ten bok pśełožyś',
	'translate-tag-markthis' => 'Toś ten bok za pśełožowanje markěrowaś',
	'translate-tag-markthisagain' => 'Toś ten bok ma <span class="plainlinks">[$1 {{PLURAL:$1|změnu|změnje|změny|změnow}}]</span>, wót togo casa, ako jo se slědny raz <span class="plainlinks">[$2 za pśełožowanje markěrował]</span>.',
	'translate-tag-hasnew' => 'Toś ten bok wopśimujo <span class="plainlinks">[$1 {{PLURAL:$1|změnu, kótaraž njejo markěrowana|změnje, kótarejž njejstej markěrowanej|změny, kótare njejsu markěrowane|změnow, kótarež njejsu markěrowane}}]</span> za pśełožowanje.',
	'tpt-translation-intro' => 'Toś ten bok jo <span class="plainlinks">[$1 pśełožona wersija]</span> boka [[$2]] a $3 % pśełožka jo dogótowane a pśełožk jo aktualne.',
	'tpt-translation-intro-fuzzy' => 'Zestarjone pśełožki su kaž toś ten markěrowany.',
	'tpt-languages-legend' => 'Druge rěcy:',
	'tpt-target-page' => 'Toś ten bok njedajo se manuelnje aktualizěrowaś.
Toś ten bok jo pśełožk boka [[$1]] a pśełožk dajo se z pomocu [$2 Pśełožyś] aktualizěrowaś.',
	'tpt-unknown-page' => 'Toś ten mjenjowy rum jo za pśełožki wopśimjeśowych bokow wuměnjony.
Zda se, až bok, kótaryž wopytujoš wobźěłaś, njewótpowědujo bokoju, kótaryž jo za pśełožowanje markěrowany.',
	'tpt-install' => 'Wuwjeź php maintenance/update.php abo webinstalaciju, aby zmóžnił funkciju pśełožowanja bokow.',
);

/** Spanish (Español)
 * @author Crazymadlover
 * @author Sanbec
 */
$messages['es'] = array(
	'pagetranslation' => 'Traducción de página',
	'right-pagetranslation' => 'Marcar versiones de páginas para traducción',
	'tpt-desc' => 'Extensiones para traducir páginas de contenido',
	'tpt-section' => 'Sección:',
	'tpt-section-new' => 'Nueva sección:',
	'tpt-diff-old' => 'Texto previo',
	'tpt-diff-new' => 'Nuevo texto',
	'tpt-submit' => 'Marcar esta versión para traducción',
	'tpt-badtitle' => 'Nombre de página dado ($1) no es un título válido',
	'tpt-oldrevision' => '$2 no es la última versión de la página [[$1]].
Solamente las últimas versiones pueden ser marcadas para traducción',
	'tpt-notsuitable' => 'La página $1 no es adecuada para traducción.
Asegúrate que tiene etiquetas <nowiki><translate></nowiki> y tiene una sintaxis válida.',
	'tpt-saveok' => 'La página "$1" ha sido marcada para traducción con $2 {{PLURAL:$2|sección|secciones}} traducibles.
La página puede ser ahora <span class="plainlinks">[$3 traducida]</span>.',
	'tpt-badsect' => '"$1" no es un nombre válido para la sección $2.',
	'tpt-showpage-intro' => 'Debajo secciones nuevas, existentes y borradas están listadas.
Antes de marcar esta versión para traducción, verifica que los cambios a las secciones son mínimos para evitar trabajo innecesario a los traductores.',
	'tpt-mark-summary' => 'Marcada esta sección para traducción',
	'tpt-edit-failed' => 'No pudo actualizar la página : $1',
	'tpt-already-marked' => 'La última versión de esta página ya ha sido marcada para traducción.',
	'tpt-list-nopages' => 'Ninguna página está marcada para traducción ni lista para ser marcada para traducción.',
	'tpt-old-pages' => 'Alguna versión de {{PLURAL:$1|esta página|estas páginas han}} sido marcadas para traducción.',
	'tpt-new-pages' => '{{PLURAL:$1|Esta página contiene|Estas páginas contienen}} texto con etiquetas de traducción, pero ninguna versión de {{PLURAL:$1|esta página est|estas páginas están}} actualmente marcadas para traducción.',
	'tpt-rev-latest' => 'última versión',
	'tpt-rev-old' => 'versión $1',
	'tpt-rev-mark-new' => 'marcar esta versión para traducción',
	'tpt-translate-this' => 'traducir esta página',
	'translate-tag-translate-link-desc' => 'Traducir esta página',
	'translate-tag-markthis' => 'Marcar esta página para traducción',
	'tpt-translation-intro' => 'Esta página es una <span class="plainlinks">[$1 versión traducida]</span> de una página [[$2]] y la traducción está $3% completa y actualizada.',
	'tpt-languages-legend' => 'Otros idiomas:',
	'tpt-target-page' => 'Esta página no puede ser actualizada manualmente.
Esta página es una traducción de la página [[$1]] y la traducción puede ser actualizada usando [$2 la herramienta de traducción].',
	'tpt-unknown-page' => 'Este espacio de nombre está reservado para traducciones de páginas de contenido.
La página que estás tratando de editar no parece corresponder con alguna página marcada para traducción.',
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
	'tpt-saveok' => 'La page « $1 » a été marqué pour être traduite avec $2 {{PLURAL:$2|section traduisible|sections traduisibles}}.
La page peut être <span class="plainlinks">[$3 traduite]</span> dès maintenant.',
	'tpt-badsect' => "« $1 » n'est pas un nom valide pour la section $2.",
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
	'tpt-translation-intro' => 'Cette page est une <span class="plainlinks">[$1 traduction]</span> de la page [[$2]] et la traduction est complétée à $3 % et à jour.',
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
	'tpt-saveok' => 'La pâge « $1 » at étâ marcâ por étre traduita avouéc $2 {{PLURAL:$2|sèccion traduisibla|sèccions traduisibles}}.
La pâge pôt étre <span class="plainlinks">[$3 traduita]</span> dês ora.',
	'tpt-badsect' => '« $1 » est pas un nom valido por la sèccion $2.',
	'tpt-showpage-intro' => 'Ce-desot, les novèles traduccions, celes ègzistentes et suprimâs.
Devant que marcar cetes vèrsions por étre traduites, controlâd que los changements a les sèccions sont petiôts por èvitar de travâly inutilo ux traductors.',
	'tpt-mark-summary' => 'Ceta vèrsion at étâ marcâ por étre traduita',
	'tpt-edit-failed' => 'Empossiblo de betar a jorn la pâge $1',
	'tpt-already-marked' => 'La dèrriére vèrsion de ceta pâge at ja étâ marcâ por étre traduita.',
	'tpt-list-nopages' => 'Niona pâge at étâ marcâ por étre traduita ou ben prèsta por l’étre.',
	'tpt-old-pages' => 'Des vèrsions de {{PLURAL:$1|ceta pâge|cetes pâges}} ont étâ marcâs por étre traduites.',
	'tpt-new-pages' => '{{PLURAL:$1|Ceta pâge contint|Cetes pâges contegnont}} de tèxte avouéc des balises de traduccion, mas niona vèrsion de {{PLURAL:$1|ceta pâge est marcâ por étre traduita|cetes pâges sont marcâs por étre traduites}}.',
	'tpt-rev-latest' => 'dèrriére vèrsion',
	'tpt-rev-old' => 'vèrsion $1',
	'tpt-rev-mark-new' => 'marcar ceta vèrsion por étre traduita',
	'tpt-translate-this' => 'traduire ceta pâge',
	'translate-tag-translate-link-desc' => 'Traduire ceta pâge',
	'translate-tag-markthis' => 'Marcar ceta pâge por étre traduita',
	'tpt-translation-intro' => 'Ceta pâge est una <span class="plainlinks">[$1 traduccion]</span> de la pâge [[$2]] et la traduccion est complètâ a $3 % et a jorn.',
	'tpt-languages-legend' => 'Ôtres lengoues :',
	'tpt-target-page' => 'Ceta pâge pôt pas étre betâ a jorn a la man.
El est una vèrsion traduita de [[$1]] et la traduccion pôt étre betâ a jorn en utilisent [$2 l’outil de traduccion].',
	'tpt-unknown-page' => 'Ceti èspâço de nom est resèrvâ por la traduccion de pâges.
La pâge que vos tâchiéd de changiér semble pas corrèspondre a gins de pâge marcâ por étre traduita.',
	'tpt-install' => 'Lanciéd php maintenance/update.php ou ben l’enstalacion Vouèbe por activar la fonccionalitât de traduccion de pâges.',
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
	'tpt-saveok' => 'A páxina "$1" foi marcada para ser traducida, {{PLURAL:$2|cunha sección traducible|con $2 seccións traducibles}}.
A páxina agora pode ser <span class="plainlinks">[$3 traducida]</span>.',
	'tpt-badsect' => '"$1" non é un nome válido para a sección $2.',
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
	'tpt-translation-intro' => 'Esta páxina é unha <span class="plainlinks">[$1 versión traducida]</span> da páxina "[[$2]]" e a tradución está completada e actualizada ao $3%.',
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
	'tpt-badtitle' => 'Dr Sytename, wu Du aagee hesch ($1), isch kei giltige Sytename',
	'tpt-oldrevision' => '$2 isch nit di letscht Version vu dr Syte [[$1]].
Nume di letschte Versione chenne zum Iberseze markiert wäre.',
	'tpt-notsuitable' => 'D Syte $1 cha nit iberstez wäre.
Stell sicher, ass si <nowiki><translate></nowiki>-Markierige un e giltige Syntax het.',
	'tpt-saveok' => 'D Syte [[$1]] isch zum Ibersetze markiert wore mit $2 {{PLURAL:$2|Ibersetzigs-Abschnitt|Ibersetzigs-Abschnitt}}.
D Syte cha jetz <span class="plainlinks">[$3 ibersetzt]</span> wäre.',
	'tpt-badsect' => '"$1" isch kei giltige Name fir dr Iberstzigs-Abschnitt $2.',
	'tpt-showpage-intro' => 'Unte sin Abschnitt ufglischtet, wu nej sin, sonigi wu s git un sonigi wu s nit git.
Voreb Du die Versione zum Ibersetze markiersch, iberprief, ass d Änderige an dr Abschnitt gring ghalte sin go uunetigi Arbed bi dr Ibersetzig vermyde.',
	'tpt-mark-summary' => 'het die Versione zum Ibersetze markiert',
	'tpt-edit-failed' => 'Cha d Syte nit aktualisiere: $1',
	'tpt-already-marked' => 'Di letscht Version vu däre Syte isch scho zum Ibersetze markiert wore.',
	'tpt-list-nopages' => 'S sin kei Syte zum Ibersetze markiert wore un sin au no keini Syte fertig, wu chennte zum Ibersetze markiert wäre',
	'tpt-old-pages' => '{{PLURAL:$1|E Version vu däre Syte isch|E paar Versione vu däne Syte sin}} zum Ibersetze markiert wore',
	'tpt-new-pages' => '{{PLURAL:$1|In däre Syte|In däne Syte}} het s Tekscht mit Ibersetzigs-Markierige, aber zur Zyt isch kei Version {{PLURAL:$1|däre Syte|däne Syte}} zum Ibersetze markiert.',
	'tpt-rev-latest' => 'letschti Version',
	'tpt-rev-old' => 'Unterschid zue dr letschte markierte Version',
	'tpt-rev-mark-new' => 'die Version zum Ibersetze markiere',
	'tpt-translate-this' => 'die Syte ibersetze',
	'translate-tag-translate-link-desc' => 'Die Syte ibersetze',
	'translate-tag-markthis' => 'Die Syte zum ibersetze markiere',
	'translate-tag-markthisagain' => 'An däre Syte het s <span class="plainlinks">[$1 Änderige]</span> gee, syt si s lescht Mol <span class="plainlinks">[$2 zum Ibersetze markiert wore isch]</span>.',
	'translate-tag-hasnew' => 'In däre Syte het s <span class="plainlinks">[$1 Änderige]</span>, wu nit zum Ibersetze markiert sin.',
	'tpt-translation-intro' => 'Die Syte isch e <span class="plainlinks">[$1 ibersetzti Version]</span> vun ere Syte [[$2]] un d Ibersetzig isch zue $3% vollständig un aktuäll.',
	'tpt-translation-intro-fuzzy' => 'Nit aktuälli Ibersetzige wäre wie dää Tekscht markiert.',
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
	'tpt-badtitle' => 'Podate mjeno strony ($1) płaćiwy titul njeje',
	'tpt-oldrevision' => '$2 aktualna wersija strony [[$1]] njeje.
Jenož aktualne wersije hodźa so za přełožowanje markěrować.',
	'tpt-notsuitable' => 'Strona $1 za přełožowanje přihódna njeje.
Zaswěsć, zo ma taflički <nowiki><translate></nowiki> a płaćiwu syntaksu.',
	'tpt-saveok' => 'Strona [[$1]] je so za přełožowanje z $2 {{PLURAL:$2|přełožujomnej jednotku|přełožujomnej jednotkomaj|přełožujomnymi jednotkami|přełožujomnymi jednotkami}} markěrowała.
Strona hodźi so nětko <span class="plainlinks">[$3 přełožować]</span>.',
	'tpt-badsect' => '"$1" płaćiwe mjeno za přełožowansku jednotku $2 njeje.',
	'tpt-showpage-intro' => 'Deleka su nowe, eksistowace a wušmórnjene wotrězki nalistowane.
Prjedy hač tutu wersiju za přełožowanje markěruješ, skontroluj, hač změny wotrězkow su miniměrowane, zo by njetrěbne dźěło za přełožowarjow wobešoł.',
	'tpt-mark-summary' => 'Je tutu wersiju za přełožowanje markěrował',
	'tpt-edit-failed' => 'Strona njeda so aktualizować: $1',
	'tpt-already-marked' => 'Akutalna wersija tuteje strony je so hižo za přełožowanje markěrowała.',
	'tpt-list-nopages' => 'Strony njejsu ani za přełožowanje markěrowali ani njejsu hotowe za přełožowanje.',
	'tpt-old-pages' => 'Někajka wersija {{PLURAL:$1|tuteje strony|tuteju stronow|tutych stronow|tutych stronow}} je so za přełožowanje markěrowała.',
	'tpt-new-pages' => '{{PLURAL:$1|Tuta strona wobsahuje|Tutej stronje|Tute strony wobsahuja|Tute strony wobsahuja}} tekst z přełožowanskimi tafličkimi, ale žana wersija {{PLURAL:$1|tuteje strony|tuteju stronow|tutych stronow|tutych stronow}} njeje tuchwilu za přełožowanje markěrowana.',
	'tpt-rev-latest' => 'aktualna wersija',
	'tpt-rev-old' => 'rozdźěl k předchadnej markěrowanej wersiji',
	'tpt-rev-mark-new' => 'tutu wersiju za přełožowanje markěrować',
	'tpt-translate-this' => 'tutu stronu přełožić',
	'translate-tag-translate-link-desc' => 'Tutu stronu přełožić',
	'translate-tag-markthis' => 'Tutu stronu za přełožowanje markěrować',
	'translate-tag-markthisagain' => 'Tuta strona ma <span class="plainlinks">[$1 {{PLURAL:$1|změnu|změnje|změny|změnow}}]</span>, wot toho zo, bu posledni raz <span class="plainlinks">[$2 za přełožowanje markěrowana]</span>.',
	'translate-tag-hasnew' => 'Tuta strona wobsahuje <span class="plainlinks">[$1 {{PLURAL:$1|změna, kotraž njeje markěrowana|změnje, kotrejž njejstej markěrowanej|změny, kotrež njejsu markěrowane|změnow, kotrež njejsu markěrowane}}]</span> za přełožowanje.',
	'tpt-translation-intro' => 'Tuta strona je <span class="plainlinks">[$1 přełožena wersija]</span> strony [[$2]] a $3 % přełožka je dokónčene a přełožk je aktualny.',
	'tpt-translation-intro-fuzzy' => 'Zestarjene přełožki su kaž tutón markěrowane.',
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
	'tpt-saveok' => 'Le pagina "$1" ha essite marcate pro traduction con $2 {{PLURAL:$2|section|sectiones}} traducibile.
Le pagina pote ora esser <span class="plainlinks">[$3 traducite]</span>.',
	'tpt-badsect' => '"$1" non es un nomine valide pro le section $2.',
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
	'tpt-translation-intro' => 'Iste pagina es un <span class="plainlinks">[$1 version traducite]</span> de un pagina [[$2]] e le traduction es complete e actual a $3%.',
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
	'tpt-saveok' => 'ページ「$1」は翻訳対象に指定されており、$2個の翻訳可能なセクションを含んでいます。このページを<span class="plainlinks">[$3 翻訳]</span>することができます。',
	'tpt-badsect' => '「$1」はセクション $2 の名前として無効です。',
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
	'tpt-translation-intro' => 'このページはページ [[$2]] の<span class="plainlinks">[$1 翻訳版]</span> です。翻訳は $3% 完了しており、最新の状態を反映しています。',
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

/** Korean (한국어)
 * @author Kwj2772
 */
$messages['ko'] = array(
	'translate-tag-translate-link-desc' => '이 문서 번역하기',
	'tpt-languages-legend' => '다른 언어:',
);

/** Ripoarisch (Ripoarisch)
 * @author Purodha
 */
$messages['ksh'] = array(
	'pagetranslation' => 'Sigge Övversäze',
	'right-pagetranslation' => 'Donn Versione vun Sigge för et Övversäze makeere',
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
	'tpt-badtitle' => 'Dä Name „$1“ es keine jöltijje Tittel för en Sigg',
	'tpt-oldrevision' => '„$2“ es nit de neuste Version fun dä Sigg „[[$1]]“, ävver bloß de neuste kam_mer för et Övversäze makeere.',
	'tpt-notsuitable' => 'Di Sigg „$1“ paß nit för et Övversäze. Maach <code><nowiki><translate></nowiki></code>-Makeerunge erin, un looer dat de Süntax shtemmp.',
	'tpt-saveok' => 'De Sigg „$1“ es för ze Övversäze makeet. Doh dren {{PLURAL:$2|es eine Knubbel|sinn_er $2 Knubbelle|es ävver keine Knubbel}} för ze Övversäze. Di Sigg kam_mer <span class="plainlinks">[$3 jäz övversäze]</span>.',
	'tpt-badsect' => '„$1“ es kein jöltejje Name för dä Knubbel zom Övversäze $2.',
	'tpt-showpage-intro' => 'Hee dronger sin Afschnedde opjeleß, di eruß jenumme woode, un di noch doh sin. Ih dat De hee di Version för ze Övversäze makeere deihß, loor drop, dat esu winnisch wi müjjelesch Änderonge aan Afschnedde doh sin, öm dä Övversäzere et Levve leisch ze maache.',
	'tpt-mark-summary' => 'Han di Version för ze Övversäze makeet',
	'tpt-edit-failed' => 'Kunnt de Sigg „$1“ nit ändere',
	'tpt-already-marked' => 'De neuste Version vun dä Sigg es ald för zem Övversäze makeet.',
	'tpt-list-nopages' => 'Et sinn_er kein Sigge för zem Övversäze makeet, un et sin och kein doh, wo esu en Makeerunge eren künnte.',
	'tpt-old-pages' => 'En Version vun hee dä {{PLURAL:$1|Sigg|Sigge|-}} es för zem Övversäze makeet.',
	'tpt-new-pages' => '{{PLURAL:$1|Di Sigg hät|Di Sigge han|Kein Sigg hät}} ene <code lang="en">translation</code>-Befähl en sesch, ävve kei Version dofun es för ze Övversäze makeet.',
	'tpt-rev-latest' => 'Neuste Version',
	'tpt-rev-old' => 'Ongerscheid zor vörijje makeete Version',
	'tpt-rev-mark-new' => 'donn di Version för et Övversäze makeere',
	'tpt-translate-this' => 'donn di Sigg övversäze',
	'translate-tag-translate-link-desc' => 'Don di Sigg hee övversäze',
	'translate-tag-markthis' => 'Donn hee di Sigg för et Övversäze makeere',
	'translate-tag-markthisagain' => 'En dä Sigg <span class="plainlinks">[{{PLURAL:$3|es ein Änderung|sinn_er $1 Änderung|es kei Änderung}}]</span> jemaat woode zick se et läz <span class="plainlinks">[$2 för ze Övversäze]</span> makeet wood.',
	'translate-tag-hasnew' => 'Aan dä Sigg <span class="plainlinks">[{{PLURAL:$3|es ein Änderung|sinn_er $1 Änderunge|es kei Änderung}}]</span> jemaat woode{{PLURAL:$3|, di ävver nit för ze Övversäze makeet wood.|, di ävver nit för ze Övversäze makeet woodte.|.}}',
	'tpt-translation-intro' => 'Hee di Sigg es en <span class="plainlinks">[$1 övversaz Version]</span> vun dä Sigg [[$2]] un es zoh $3% jedonn un om aktoälle Shtandt.',
	'tpt-translation-intro-fuzzy' => 'Övverhollte Övversäzunge wäde su makeet, wi hee dä Täx.',
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
	'tpt-badtitle' => 'De Säitennumm deen ugi gouf ($1) ass kee valabelen Titel',
	'tpt-oldrevision' => "$2 ass net déi lescht Versioun vun der Säit [[$1]].
Nëmmen déi lescht Versioune kënne fir d'Iwwersetzung markéiert ginn.",
	'tpt-notsuitable' => "D'Säit $1 ass net geeegent fir iwwersat ze ginn.
Vergewëssert Iech ob se <nowiki><translate></nowiki>-Taggen  an eng valabel Syntax huet.",
	'tpt-mark-summary' => "huet dës Versioun fir d'Iwwersetzung markéiert",
	'tpt-edit-failed' => "D'Säit $1 konnt net aktualiséiert ginn",
	'tpt-already-marked' => "Déilescht Versioun vun dëser Säit gouf scho fir d'Iwwersetzung markéiert.",
	'tpt-list-nopages' => "Et si keng Säite fir d'Iwwersetzung markéiert respektiv fäerdeg fir fir d'Iwersetzung markéiert ze ginn.",
	'tpt-old-pages' => "Eng Versioun vun {{PLURAL:$1|dëser Säit|dëse Säite}} gouf fir d'Iwwersetze markéiert.",
	'tpt-rev-latest' => 'lescht Versioun',
	'tpt-rev-old' => 'Ënnerscheed zu der vireger markéierter Versioun',
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
	'tpt-badtitle' => 'De opgegeven paginanaam ($1) is geen geldige paginanaam',
	'tpt-oldrevision' => '$2 is niet de meest recente versie van de pagina "[[$1]]".
Alleen de meest recente versie kan voor vertaling gemarkeerd worden.',
	'tpt-notsuitable' => 'De pagina "$1" kan niet voor vertaling gemarkeerd worden.
Zorg ervoor dat de labels <nowiki><translate></nowiki> geplaatst zijn en dat deze juist zijn toegevoegd.',
	'tpt-saveok' => 'De pagina [[$1]] is gemarkeerd voor vertaling met $2 te vertalen {{PLURAL:$2|vertaaleenheid|vertaaleenheden}}.
De pagina kan nu  <span class="plainlinks">[$3 vertaald]</span> worden.',
	'tpt-badsect' => '"$1" is geen geldige naam voor vertaaleenheid $2.',
	'tpt-showpage-intro' => 'Hieronder zijn nieuwe, bestaande en verwijderde secties opgenomen.
Controleer voordat u deze versie voor vertaling markeert of de wijzigingen aan de secties zo klein mogelijk zijn om onnodig werk voor vertalers te voorkomen.',
	'tpt-mark-summary' => 'Heeft deze versie voor vertaling gemarkeerd',
	'tpt-edit-failed' => 'De pagina "$1" kon niet bijgewerkt worden.',
	'tpt-already-marked' => 'De meest recente versie van deze pagina is al gemarkeerd voor vertaling.',
	'tpt-list-nopages' => "Er zijn geen pagina's gemarkeerd voor vertaling, noch klaar om gemarkeerd te worden voor vertaling.",
	'tpt-old-pages' => "Er is al een versie van deze {{PLURAL:$1|pagina|pagina's}} gemarkeerd voor vertaling.",
	'tpt-new-pages' => "Deze {{PLURAL:$1|pagina bevat|pagina's bevatten}} tekst met vertalingslabels, maar van deze {{PLURAL:$1|pagina|pagina's}} is geen versie gemarkeerd voor vertaling.",
	'tpt-rev-latest' => 'meest recente versie',
	'tpt-rev-old' => 'verschil met de vorige gemarkeerde versie',
	'tpt-rev-mark-new' => 'deze versie voor vertaling markeren',
	'tpt-translate-this' => 'deze pagina vertalen',
	'translate-tag-translate-link-desc' => 'Deze pagina vertalen',
	'translate-tag-markthis' => 'Deze pagina voor vertaling markeren',
	'translate-tag-markthisagain' => 'Deze pagina is <span class="plainlinks">[$1 gewijzigd]</span> sinds deze voor het laatst <span class="plainlinks">[$2 voor vertaling gemarkeerd]</span> is geweest.',
	'translate-tag-hasnew' => 'Aan deze pagina zijn <span class="plainlinks">[$1 wijzigingen]</span> gemaakt die niet voor vertaling zijn gemarkeerd.',
	'tpt-translation-intro' => 'Deze pagina is een <span class="plainlinks">[$1 vertaalde versie]</span> van de pagina [[$2]] en de vertaling is $3% compleet en bijgewerkt.',
	'tpt-translation-intro-fuzzy' => 'Verouderde vertaling worden zo weergegeven.',
	'tpt-languages-legend' => 'Andere talen:',
	'tpt-target-page' => 'Deze pagina kan niet handmatig worden bijgewerkt manually.
Deze pagina is een vertaling van de pagina [[$1]].
De vertaling kan bijgewerkt worden via de [$2 vertaalhulpmiddellen].',
	'tpt-unknown-page' => "Deze naamruimte is gereserveerd voor de vertalingen van van pagina's.
De pagina die u probeert te bewerken lijkt niet overeen te komen met een te vertalen pagina.",
	'tpt-install' => 'Voer php maintenance/update.php of de webinstallatie uit om de paginavertaling te activeren.',
);

/** Norwegian Nynorsk (‪Norsk (nynorsk)‬)
 * @author Frokor
 * @author Gunnernett
 */
$messages['nn'] = array(
	'tpt-diff-new' => 'Ny tekst',
	'translate-tag-translate-link-desc' => 'Set om denne sida',
);

/** Occitan (Occitan)
 * @author Cedric31
 */
$messages['oc'] = array(
	'pagetranslation' => 'Traduccion de paginas',
	'right-pagetranslation' => 'Marcar de versions de paginas per èsser traduchas',
	'tpt-desc' => 'Extension per traduire de paginas de contengut',
	'tpt-section' => 'Seccion:',
	'tpt-section-new' => 'Seccion novèla :',
	'tpt-diff-old' => 'Tèxte precedent',
	'tpt-diff-new' => 'Tèxte novèl',
	'tpt-submit' => 'Marcar aquesta version per èsser traducha',
	'tpt-badtitle' => 'Lo nom de pagina donada ($1) es pas un títol valid',
	'tpt-oldrevision' => '$2 es pas la darrièra version de la pagina [[$1]].
Sola la darrièra version de la pagina pòt èsser marcada per èsser traducha.',
	'tpt-notsuitable' => "La pagina $1 conven pas per èsser traducha.
Siatz segur(a) que conten la balisa <nowiki><translate></nowiki> e qu'a una sintaxi corrècta.",
	'tpt-saveok' => 'La pagina « $1 » es estada marcada per èsser traducha amb $2 seccions tradusiblas.
La pagina pòt èsser <span class="plainlinks">[$3 traducha]</span> tre ara.',
	'tpt-badsect' => '« $1 » es pas un nom valid per la seccion $2.',
	'tpt-showpage-intro' => "Çaijós, las traduccions novèlas, las qu'existisson e las suprimidas.
Abans de marcar aquestas versions per èsser traduchas, verificatz que las modificacions a las seccions son minimizadas per evitar de trabalh inutil als traductors.",
	'tpt-mark-summary' => 'Aquesta version es estada marcada per èsser traducha',
	'tpt-edit-failed' => 'Impossible de metre a jorn la pagina $1',
	'tpt-already-marked' => "La darrièra version d'aquesta pagina ja es estada marcada per èsser traducha.",
	'tpt-list-nopages' => "Cap de pagina es pas estada marcada per èsser traducha o prèsta per l'èsser.",
	'tpt-old-pages' => "De versions d'{{PLURAL:$1|aquesta pagina|aquestas paginas}} son estadas marcadas per èsser traduchas.",
	'tpt-new-pages' => "{{PLURAL:$1|Aquesta pagina conten|Aquestas paginas contenon}} de tèxte amb de balisas de traduccion, mas cap de version d'{{PLURAL:$1|aquesta pagina es pas marcada per èsser traducha|aquestas paginas son pas marcadas per èsser traduchas}}.",
	'tpt-rev-latest' => 'darrièra version',
	'tpt-rev-old' => 'version $1',
	'tpt-rev-mark-new' => 'marcar aquesta version per èsser traducha',
	'tpt-translate-this' => 'traduire aquesta pagina',
	'translate-tag-translate-link-desc' => 'Traduire aquesta pagina',
	'translate-tag-markthis' => 'Marcar aquesta pagina per èsser traducha',
	'tpt-translation-intro' => 'Aquesta pagina es una <span class="plainlinks">[$1 traduccion]</span> de la pagina [[$2]] e la traduccion es completada a $3 % e a jorn.',
	'tpt-languages-legend' => 'Autras lengas :',
	'tpt-target-page' => "Aquesta pagina pòt pas èsser mesa a jorn manualament.
Es una version traducha de [[$1]] e la traduccion pòt èsser mesa a jorn en utilizant [$2 l'esplech de traduccion].",
	'tpt-unknown-page' => "Aqueste espaci de noms es reservat per la traduccion de paginas.
La pagina qu'ensajatz de modificar sembla pas correspondre a cap de pagina marcada per èsser traducha.",
	'tpt-install' => "Aviatz php maintenance/update.php o l'installacion web per activar la foncionalitat de traduccion de paginas.",
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
	'tpt-translation-intro' => 'Esta página é uma <span class="plainlinks">[$1 versão traduzida]</span> de uma página [[$2]], e a tradução está $3% completa e atualizada.',
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
	'tpt-saveok' => 'Stránka „$1“ bola označená na preklad s $2 {{PLURAL:$2|sekciou|sekciami}}, ktoré možno preložiť.
Túto stránku je teraz možné <span class="plainlinks">[$3 preložiť]</span>.',
	'tpt-badsect' => '„$1“ nie je platný názov sekcie $2.',
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
	'tpt-translation-intro' => 'Táto stránka je <span class="plainlinks">[$1 preloženou verziou]</span> stránky [[$2]] a preklad je hotový a aktuálny na $3 %.',
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

/** Telugu (తెలుగు)
 * @author Veeven
 */
$messages['te'] = array(
	'tpt-section' => 'విభాగం:',
	'tpt-section-new' => 'కొత్త విభాగం:',
	'tpt-diff-new' => 'కొత్త పాఠ్యం',
	'tpt-translate-this' => 'ఈ పేజీని అనువదించండి',
	'translate-tag-translate-link-desc' => 'ఈ పేజీని అనువదించండి',
	'tpt-languages-legend' => 'ఇతర భాషలు:',
);

